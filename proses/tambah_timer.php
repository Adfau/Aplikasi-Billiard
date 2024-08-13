<?php
session_start();

if (isset($_GET['id'])) {
    $test = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if($test == FALSE) {
        header( "Location: ../index.php" );
        exit();
    }
    
    if (isset($_POST['sisaWaktu'])) {
        require_once('../controller.php');

        if (isset($_POST['tglMulai'])) {
            $tgl = $_POST['tglMulai'];
        } else {
            $tgl = date("Y-m-d");
        }
        $tglBesok = date("Y-m-d", strtotime($tgl . " +1 day"));
        $noMeja = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $namaPenyewa = filter_input(INPUT_POST, 'namaPenyewa', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        $sisaWaktu = $_POST['sisaWaktu'];
        $harga = $_POST['harga'];
        $hargaPerJam = NULL;

        $billingMode = "TIMER";

        try {
            $sql = "INSERT INTO `billing` (`harga`, `waktu_mulai`, `waktu_selesai`, `durasi`, `nama_penyewa`, `no_meja`, `harga_perjam`) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $con->prepare($sql);
            $stmt->bind_param("issssii", $harga, $waktuMulai, $waktuSelesai, $sisaWaktu, $namaPenyewa, $noMeja, $hargaPerJam);

            $result = $stmt->execute();

            if ($result) {
                echo "BERHASIL MENYEWA.";
                $_SESSION['status'] = "Success";
                
                // Ambil ID data yang baru di insert
                $billing_id = $stmt->insert_id;
                $deskripsiLog = "menyalakan Meja $noMeja";
                if ($waktuSelesai === NULL) $deskripsiLog .= " dengan mode OPEN";
                $deskripsiLog .= "\nNama Penyewa: $namaPenyewa
                                \nBilling ID: $billing_id
                                \nMulai: $waktuMulai";
                if ($waktuSelesai !== NULL) {
                    $textHarga = number_format($harga,0,",",".");
                    $deskripsiLog .= "\nSelesai: $waktuSelesai
                                    \nDurasi: $sisaWaktu
                                    \nHarga: Rp $textHarga";
                }
                logActivity($con, $_SESSION['id_user'], $deskripsiLog, "CREATE", $billing_id);

                //Debug
                // echo $endTime;
                // echo "||";
                // echo ($currentTime);
                // echo "||";
                // if ($currentTime < $endTime) {echo "True";} else {echo "False";}

                // Check jika bukan mode OPEN.

                if ($currentTime < $endTime) {

                    // Jadwalkan penghapusan di jam $waktuSelesai
                    $event_name = "delete_billing_$billing_id";
                    $event_sql = "CREATE EVENT $event_name
                        ON SCHEDULE AT '$waktuSelesai'
                        DO
                        BEGIN
                            DELETE FROM billing WHERE id = $billing_id;
                            UPDATE event_log SET executed = TRUE WHERE event_name = '$event_name';
                            DROP EVENT $event_name;
                        END";

                    if ($con->query($event_sql) === TRUE) {
                        echo " Event scheduled successfully.";
                    } else {
                        echo " Error scheduling event: " . $con->error;
                    }
                }

            } else {
                echo "GAGAL TAMBAH";
            }

            $stmt->close();
            mysqli_close($con);
            
        } catch (Exception $e) {
            // Log or handle the error as needed
            echo "Error: " . $e->getMessage();
        }
        
    } else {
        echo "TIDAK ADA DATA";
    }

}

    // Delay untuk mengatasi restart yang terjadi ketika memasukkan data meja baru.
    // sleep(1);

    header( "Location: ../index.php" );
    exit();
?>

