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
        $sisaWaktu = formatToHis($_POST['sisaWaktu']);
        $harga = $_POST['harga'];
        $hargaPerJam = NULL;

        $billingMode = "TIMER";

        if(isset($_POST['billingMode'])) { // Checkbox checked
            $formattedWaktuMulai = formatToHis($_POST['waktuMulai']);
            $formattedWaktuSelesai = formatToHis($_POST['waktuSelesai']);

            $waktuMulai = $tgl . " " . $formattedWaktuMulai;
            if ($formattedWaktuSelesai <= $formattedWaktuMulai) {
                $waktuSelesai = $tglBesok . " " . $formattedWaktuSelesai;
            } else {
                $waktuSelesai = $tgl . " " . $formattedWaktuSelesai;
            }

            // Add seconds to $waktuSelesai
            // $timestamp = strtotime($waktuSelesai);
            // $timestamp += 2;
            // $waktuSelesai = date('Y-m-d H:i:s', $timestamp);

            $currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time

            // Compare and update $waktuMulai if it is less than the current date and time
            if ($waktuMulai <= $currentDateTime) {
                $waktuMulai = $currentDateTime;
                // Convert to DateTime objects
                $start = new DateTime($waktuMulai);
                $end = new DateTime($waktuSelesai);

                // Calculate the interval
                $interval = $start->diff($end);

                // Format the interval to H:i:s
                $sisaWaktu = $interval->format('%H:%I:%S');

                                
            } else {

                $billingMode = "RESERVED";
            }

        } else {
            $waktuMulai = date('Y-m-d H:i:s'); // Current date and time in YYYY-MM-DD HH:MM:SS format

            if ($sisaWaktu === NULL || empty($sisaWaktu)) { // Mode Open
                $waktuSelesai = NULL;
                $hargaPerJam = getHarga();

                $billingMode = "OPEN";

            } else { // Mode timer
                // Convert sisa waktu to seconds and add it to the current timestamp
                $endTimestamp = strtotime($waktuMulai) + strtotime($sisaWaktu) - strtotime('TODAY');

                // Convert the calculated timestamp back to a readable datetime format
                $waktuSelesai = date('Y-m-d H:i:s', $endTimestamp);

                // Add seconds to $waktuSelesai
                // $timestamp = strtotime($waktuSelesai);
                // $timestamp += 2;
                // $waktuSelesai = date('Y-m-d H:i:s', $timestamp);

                // echo $endTimestamp;
                // echo "<br>";
            }           
            
            // echo $waktuMulai;
            // echo "<br>";
            // echo $sisaWaktu;
            // echo "<br>";
            // echo $waktuSelesai;
        }
        $endTime = strtotime($waktuSelesai);
        $currentTime = time();

        // Stopper
        if ($currentTime === $endTime) {
            header( "Location: ../index.php?status=fail" );
            exit();
        }
        
                    
        if ($billingMode == "TIMER") {
            // Koneksi Serial
            //require_once('PhpSerial.php');
            serialSetTable($noMeja, $sisaWaktu);
            sleep(0.1);

        } elseif ($billingMode == "OPEN") {
            serialSetTable($noMeja);
            sleep(0.1);
        }
        //elseif ($billingMode == "RESERVED") {
        //     serialSetTable($noMeja, $sisaWaktu);
        // }

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

                    if ($billingMode == "RESERVED") {
                        // Calculate the remaining time ($sisaWaktu) and format it as HH:MM:SS
                        // $startTime = strtotime($waktuMulai);
                        // $remTime = $endTime - $startTime;
                        // if ($remTime < 0) {
                        //     $remTime = 0; // Ensure non-negative time
                        // }
                        // $sisaWaktuFormatted = gmdate("H:i:s", $remTime);

                        // Schedule the activation event
                        $event_name_activate = "activate_billing_$billing_id";
                        $event_sql_activate = "CREATE EVENT $event_name_activate
                            ON SCHEDULE AT '$waktuMulai'
                            DO
                            BEGIN
                                CALL billiard_serial(1, (SELECT no_meja FROM billing WHERE id = $billing_id LIMIT 1), '$sisaWaktu');
                                DROP EVENT $event_name_activate;
                            END";
                        if ($con->query($event_sql_activate) === TRUE) {
                            echo "Activation event scheduled successfully.";
                        } else {
                            echo "Error scheduling activation event: " . $con->error;
                        }

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

    function formatToHis($input) {
        // Extract only the numbers from the input
        $numbers = preg_replace('/\D/', '', $input);
    
        // If no numbers found, return NULL
        if (empty($numbers)) {
            return '';
        }
        $length = strlen($numbers);
        if ($length == 1) $numbers = '0' . $numbers;
    
        // Pad the number string to at least 6 characters
        $numbers = str_pad($numbers, 6, '0');
    
        // Extract hours, minutes, and seconds
        $hours = substr($numbers, 0, 2);
        $minutes = substr($numbers, 2, 2);
        $seconds = substr($numbers, 4, 2);
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    
    }
?>

