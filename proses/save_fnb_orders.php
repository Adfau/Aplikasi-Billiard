<?php
require_once('../controller.php');

$billingId = $_POST['billing_id'];
$items = $_POST['items'];
$deletedItems = $_POST['deleted_items'];

// Save new and updated items
foreach ($items as $item) {
    if (empty($item['id_order'])) {
        $idOrder = $item['id_order'];
        $namaFnb = $item['nama_fnb'];
        $hargaFnb = $item['harga_fnb'];
        $jumlahFnb = $item['jumlah_fnb'];
        $totalFnb = $item['total_fnb'];

        // Insert new order without specifying id_order (it will auto-increment)
        $sql = "INSERT INTO fnb_orders (id_billing, nama_fnb, harga_fnb, jumlah_fnb, total_fnb) 
                VALUES ('$billingId', '$namaFnb', '$hargaFnb', '$jumlahFnb', '$totalFnb')";

        mysqli_query($con, $sql);
    }

    // $sql = "INSERT INTO fnb_orders (id_order, id_billing, nama_fnb, harga_fnb, jumlah_fnb, total_fnb) 
    //         VALUES ('$idOrder', '$billingId', '$namaFnb', '$hargaFnb', '$jumlahFnb', '$totalFnb')
    //         ON DUPLICATE KEY UPDATE 
    //         nama_fnb = '$namaFnb', harga_fnb = '$hargaFnb', jumlah_fnb = '$jumlahFnb', total_fnb = '$totalFnb'";

    
}

// Delete items
if (!empty($deletedItems)) {
    $deletedIds = implode(',', array_map('intval', $deletedItems));
    $deleteSql = "DELETE FROM fnb_orders WHERE id_order IN ($deletedIds)";
    mysqli_query($con, $deleteSql);
}

echo json_encode(['status' => 'success']);
?>
