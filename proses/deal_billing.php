<?php
session_start();

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
} else {
    $billing_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($billing_id !== FALSE) {
        require_once('../controller.php');
        
        // Retrieve other POST data
        
        $nama_penyewa = filter_input(INPUT_POST, 'c_NamaTamu', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ?? '';
        $keterangan = filter_input(INPUT_POST, 'c_Keterangan', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ?? '';

        // Update the billing_history table
        $sql = "UPDATE billing_history SET nama_penyewa = ?, keterangan = ?, is_paid = 1 WHERE billing_id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssi", $nama_penyewa, $keterangan, $billing_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "BERHASIL UPDATE DATA.";
            } else {
                echo "Tidak ada data yang diupdate.";
            }
        } else {
            echo "GAGAL UPDATE STATUS.";
        }

        $stmt->close();
    }

    header("Location: ../index.php");
    exit();
}
?>
