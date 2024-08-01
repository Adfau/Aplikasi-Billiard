<?php
session_start();
// Check if the user is an admin
if ($_SESSION['level'] !== "ADMIN" || !isset($_POST['timezone'])) {
    // If not an admin, return a 404 error (or perform any other action as desired)
    http_response_code(404);
    exit; // Stop further execution of the script
}

// Read the posted timezone value
$selectedTimezone = isset($_POST['timezone']) ? $_POST['timezone'] : '';

require_once('../controller.php');
// Prepare and bind update statement
$sql = "UPDATE config SET timezone = ? WHERE id_config = 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $selectedTimezone);

// Execute the update
if ($stmt->execute()) {
  echo "Timezone updated successfully";
  $deskripsiLog = "mengupdate Time Zone Menjadi $selectedTimezone";
  logActivity($con, $_SESSION['id_user'], $deskripsiLog, "UPDATE");
} else {
  echo "Error updating timezone: " . $con->error;
}

// Close statement and connection
$stmt->close();
$con->close();
?>
