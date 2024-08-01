<?php
require_once('../controller.php');

$billingId = filter_input(INPUT_GET, 'billing_id', FILTER_VALIDATE_INT);
$response = ['items' => [], 'lastId' => 0];

$sql = "SELECT id_order, nama_fnb, harga_fnb, jumlah_fnb, total_fnb FROM fnb_orders WHERE id_billing = '$billingId'";
$result = mysqli_query($con, $sql);

while ($row = mysqli_fetch_array($result)) {
    $response['items'][] = $row;
}

// Get the last id_order for new items
$lastIdSql = "SELECT id_order FROM fnb_orders ORDER BY id_order DESC LIMIT 1";
$lastIdResult = mysqli_query($con, $lastIdSql);
if ($lastIdRow = mysqli_fetch_array($lastIdResult)) {
    $response['lastId'] = $lastIdRow['id_order'];
}

echo json_encode($response);
?>
