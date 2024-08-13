<?php
require_once('../controller.php');

// Display initial message
//echo "<h2>SINKRONISASI. JANGAN GANTI ATAU KELUAR DARI TAB.</h2>" . "<br>\n";

if (isset($_GET['no_meja'])) {
    $no_meja = filter_input(INPUT_GET, 'no_meja', FILTER_VALIDATE_INT);
    $sql = "SELECT waktu_mulai, waktu_selesai, no_meja FROM billing WHERE no_meja=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $no_meja);

    serialStopTable($no_meja);
    echo $no_meja . " Dimatikan" . "\n";
    usleep(100000);

} else {
    $sql = "SELECT waktu_mulai, waktu_selesai, no_meja FROM billing";
    $stmt = $con->prepare($sql);

    // Turn off all tables using serialStopTable
    $jumlah_meja = $GLOBALS['$jumlah_meja'];
    for ($i = 1; $i <= $jumlah_meja; $i++) {
        serialStopTable($i);
        echo $i . " Dimatikan" . "\n";

        usleep(100000);
    }

}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$data = $result->fetch_all(MYSQLI_ASSOC);

// Function to calculate the remaining time
function calculateRemainingTime($endTime) {
    $currentDateTime = new DateTime();
    $endDateTime = new DateTime($endTime);
    
    if ($endDateTime > $currentDateTime) {
        $interval = $currentDateTime->diff($endDateTime);
        return $interval->format('%H:%I:%S');
    }
    return null;
}

// Turn back on the tables with the specific table numbers and end times
foreach ($data as $row) {
    $noMeja = $row['no_meja'];
    $waktuSelesai = $row['waktu_selesai'];

        if ($waktuSelesai !== NULL) {
            $waktuMulai = $row['waktu_mulai'];
            $startTime = strtotime($waktuMulai);
            $currentTime = time();
            if ($currentTime >= $startTime) {
                $remainingTime = calculateRemainingTime($waktuSelesai);
                serialSetTable($noMeja, $remainingTime);

                echo $noMeja . " Dinyalakan dengan timer " . $remainingTime . "\n";
                usleep(100000);
            }
        } else {
            serialSetTable($noMeja);

            echo $noMeja . " Dinyalakan tanpa timer" . "\n";
            usleep(100000);
            
        }
}

//echo  "<br>\n" . "<h2>PROSES SELESAI</h2>";

header( "Location: " .  baseUrl());
exit();
?>