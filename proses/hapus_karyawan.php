<?php
session_start();

// Pastikan level pengguna adalah ADMIN
if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header("Location: ../signin.php");
    exit();
}

if (!isset($_GET['id'])) {
    header( "Location: ../index.php" );
    exit();
} else {
    $id_user = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if($id_user == FALSE) {
        header( "Location: ../index.php" );
        exit();
    }

    require_once('../controller.php');

    $sql = "DELETE FROM user WHERE id_user=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_user);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "BERHASIL MENGHAPUS.";

            //Log
            $textHarga = number_format($harga,0,",",".");
            $deskripsiLog = "menghapus karyawan $id_user";
            logActivity($con, $_SESSION['id_user'], $deskripsiLog, "DELETE");
        } else {
            echo "Tidak ada data yang dihapus.";
        }
    } else {
        echo "GAGAL MENGHAPUS.";
    }

    $stmt->close();
    mysqli_close($con);
    header( "Location: ../index.php" );
    exit();
}


?>

