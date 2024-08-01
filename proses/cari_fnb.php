<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

require_once('../controller.php');

$search = '%' . $_GET['search'] . '%';
$sql = "SELECT nama_menu, harga_menu FROM fnb_menu WHERE nama_menu LIKE ? ORDER BY nama_menu";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);

exit();
?>
