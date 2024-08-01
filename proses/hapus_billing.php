<?php
session_start();

if (!isset($_GET['id'])) {
    header( "Location: ../index.php" );
    exit();
} else {
    $no_meja = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if($no_meja == FALSE) {
        header( "Location: ../index.php" );
        exit();
    }

    $billing_id = "";

    require_once('../controller.php');

    //Update harga
    $sql = "SELECT * FROM billing WHERE no_meja=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $no_meja);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        $billing_id = $data['id'];
        $namaPenyewa = $data['nama_penyewa'];
        $waktuMulai = $data['waktu_mulai'];
        $waktuSelesai = $data['waktu_selesai'];
        $sisaWaktu = $data['durasi'];
        $harga = $data['harga'];

        $startTime = strtotime($data['waktu_mulai']);
        $endTime = strtotime($data['waktu_selesai']);
        $currentTime = time();
        if ($data['waktu_selesai'] === NULL) { //Jika mode OPEN
            // // Menghitung jumlah jam yang telah lewat
            // $hoursPassed = round((time() - $startTime) / (3600));
            // // Menghitung harga
            // $current_harga = getHarga();
            // $harga = $hoursPassed * $current_harga;

            $minutesPassed = floor((time() - $startTime) / (60));
            $harga = floor(($minutesPassed * getHarga()) / 60);
            
            $waktuSelesai = date('Y-m-d H:i:s');
            $endTime = strtotime($waktuSelesai);
            $sisaWaktu = formatSeconds($endTime - $startTime);
            $sql = "UPDATE billing SET waktu_selesai = ?, durasi = ?, harga = ? WHERE no_meja = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ssii", $waktuSelesai, $sisaWaktu, $harga, $no_meja);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "BERHASIL UPDATE DATA.";
                } else {
                    echo "Tidak ada data yang diupdate.";
                }
            } else {
                echo "GAGAL UPDATE HARGA.";
            }
            $stmt->close();

        } else {
            echo "BILLING YANG DIHAPUS TIDAK OPEN.";

            
                // Koneksi Serial
                // require_once('PhpSerial.php');
                serialStopTable($no_meja);

        }
    } else {
        echo "DATA TIDAK DIDAPAT.";
        header( "Location: ../index.php" );
        exit();
    }

    $sql = "DELETE FROM billing WHERE no_meja=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $no_meja);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "BERHASIL MENGHAPUS.";

            //Stop meja
            // Koneksi Serial
            // require_once('PhpSerial.php');
            serialStopTable($no_meja);

            //Hapus event pada SQL
            $event_name = "delete_billing_$billing_id";
            $drop_event_sql = "DROP EVENT IF EXISTS $event_name";
            if ($con->query($drop_event_sql) === TRUE) {
                echo " Event dropped successfully.";
            } else {
                echo " Error dropping event: " . $con->error;
            }

            //Log
            $textHarga = number_format($harga,0,",",".");
            $deskripsiLog = "mematikan Meja $no_meja
                            \nNama Penyewa: $namaPenyewa
                            \nBilling ID: $billing_id
                            \nMulai: $waktuMulai
                            \nSelesai: $waktuSelesai
                            \nDurasi: $sisaWaktu
                            \nHarga: Rp $textHarga";
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

