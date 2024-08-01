<?php
session_start();
// Check if the user is an admin
if ($_SESSION['level'] !== "ADMIN" || !isset($_POST['hargaWeekdays']) || !isset($_POST['hargaWeekends'])) {
    // If not an admin, return a 404 error (or perform any other action as desired)
    http_response_code(404);
    exit; // Stop further execution of the script
}

// Function to convert price format to integer
function priceToInteger($price) {
    // Remove non-numeric characters
    $price = preg_replace("/[^0-9]/", "", $price);
    // Convert to integer
    return (int)$price;
}

echo priceToInteger($_POST['hargaWeekdays']);
echo priceToInteger($_POST['hargaWeekends']);

// Get the submitted values from the form
$hargaWeekday = priceToInteger($_POST['hargaWeekdays']);
$hargaWeekend = priceToInteger($_POST['hargaWeekends']);

require_once('../controller.php');
// Prepare and bind update statement
$sql = "UPDATE config SET harga_weekdays = ?, harga_weekends = ? WHERE id_config = 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $hargaWeekday, $hargaWeekend);

// Execute the update
if ($stmt->execute()) {
  echo "Harga updated successfully";
  $deskripsiLog = "mengubah harga
  \nHarga Weekdays: $hargaWeekday
  \nHarga Weekends: $hargaWeekend";
  logActivity($con, $_SESSION['id_user'], $deskripsiLog, "UPDATE");
} else {
  echo "Error updating harga: " . $con->error;
}

// Close statement and connection
$stmt->close();
$con->close();

header( "Location: ../admin-settings" );
exit();

?>
