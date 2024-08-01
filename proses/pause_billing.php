<?php
session_start();

if (isset($_GET['id'])) {
    // Validasi
    $no_meja = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($no_meja === false || $no_meja === NULL) {
        echo "No Meja salah";
        header("Location: ../index.php");
        exit();
    }
    
    $status = "PAUSE";

    require_once('../controller.php');

    $sql = "SELECT status FROM billing WHERE no_meja = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $no_meja);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $check = $data['status'];
        $stmt->close();

        if ($check == NULL || $check == "TIMER" || $check == "OPEN") {
            $status = "PAUSE";
        } elseif ($check == "PAUSE") {
            $status = NULL;
        } else {
            echo "GAGAL PAUSE. MEJA BELUM NYALA";
            header( "Location: ../index.php" );
            exit();
        }
        $sql = "UPDATE billing SET status = ? WHERE no_meja = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("si", $status, $no_meja);
        $result = $stmt->execute();
        $stmt->close();

    } else {
        $stmt->close();
        echo "GAGAL PAUSE";
    }
    
    mysqli_close($con);
}

    header( "Location: ../index.php" );
    exit();
?>

