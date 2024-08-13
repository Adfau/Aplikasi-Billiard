<?php

function sendToDaemon($message) {
    try {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            echo "socket_create() failed: " . socket_strerror(socket_last_error()) . "\n";
            return;
        }

        $result = socket_connect($socket, 'localhost', 65432);
        if ($result === false) {
            echo "socket_connect() failed: " . socket_strerror(socket_last_error($socket)) . "\n";
            return;
        }

        socket_write($socket, $message, strlen($message));
        socket_close($socket);

        usleep(100000);
        
    } catch (Exception $e) {
        // Handle the exception and output the error message
        echo "Error: " . $e->getMessage() . "\n";
    }
}

function serialSetTable($table, $duration = "00:00:00") {

    $sendMsg = "set $table $duration";
    sendToDaemon($sendMsg);
}

function serialStopTable($table) {
    $sendMsg = "stop $table";
    sendToDaemon($sendMsg);
}

function serialUpdateTable($oldTable, $newTable) {
    $sendMsg = "move $oldTable $newTable";
    sendToDaemon($sendMsg);
}

//status meja db: 0 = OFF, 1 = ON, 2 = RESERVED, 3 = PAUSED, 4 = UNFUNCTIONAL

function formatSeconds($seconds) {
    // Calculate hours, minutes, and remaining seconds
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    // Format the time string
    $timeString = sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);

    return $timeString;
}

function timeToSeconds($timeString) {
    // Use strtotime to parse the time string and convert it to a timestamp
    $timestamp = strtotime($timeString);
    
    // If strtotime fails, return false
    if ($timestamp === false) {
        return false;
    }
    
    // Extract hours, minutes, and seconds from the timestamp
    $hours = date('H', $timestamp);
    $minutes = date('i', $timestamp);
    $seconds = date('s', $timestamp);
    
    // Convert hours and minutes to seconds and sum them up
    $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
    
    return $totalSeconds;
}

$servername = "localhost";
$username = "root";
$password = "Per-sepulchra";
$dbname = "db_billiard";

$con = new mysqli($servername, $username, $password, $dbname);

// Cek Koneksi
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Prepare and bind select statement
$stmt = $con->prepare("SELECT timezone, harga_weekdays, harga_weekends FROM config WHERE id_config = 1");
$stmt->execute();
$stmt->bind_result($timezone, $harga_weekdays, $harga_weekends);
$stmt->fetch();
$stmt->close();

// Cek jumlah meja
$GLOBALS['$jumlah_meja'] = 0;
$result = mysqli_query($con, "SELECT * FROM meja");
if ($result) {
    // Get the number of rows returned by the query
    $GLOBALS['$jumlah_meja'] = mysqli_num_rows($result);
}

$GLOBALS['$current_timezone'] = $timezone;
$GLOBALS['$current_hargaWeekdays'] = $harga_weekdays;
$GLOBALS['$current_hargaWeekends'] = $harga_weekends;

if ($GLOBALS['$current_timezone'] == 'WITA') {
    date_default_timezone_set('Asia/Makassar');
} else if ($GLOBALS['$current_timezone'] == 'WIT') {
    date_default_timezone_set('Asia/Jayapura');
} else { //Default WIB
    $GLOBALS['$current_timezone'] = "WIB";
    date_default_timezone_set('Asia/Jakarta');
}
//date_default_timezone_set('Asia/Bangkok');        // UTC+7 (WIB)
//date_default_timezone_set('Asia/Kuala_Lumpur');   // UTC+8 (WITA)
//date_default_timezone_set('Asia/Tokyo');          // UTC+9 (WIT)

// Mengambil harga sesuai hari weekend / weekday
function getHarga() {
    $currentDay = date('w'); // 0 (for Sunday) through 6 (for Saturday)
    if ($currentDay == 0 || $currentDay == 6) { // Sunday or Saturday
        return $GLOBALS['$current_hargaWeekends'];
    } else {
        return $GLOBALS['$current_hargaWeekdays'];
    }
}

function logActivity($con, $id_user, $deskripsi, $type, $id_billing = null) {
    $sql = "INSERT INTO `activity_log` (`id_user`, `id_billing`, `deskripsi`, `type`) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);

    // Convert id_billing to null if it's 0
    if ($id_billing == 0) {
        $id_billing = null;
    }

    $stmt->bind_param("iiss", $id_user, $id_billing, $deskripsi, $type);

    $result = $stmt->execute();

    if ($result === false) {
        echo 'Error: ' . $stmt->error;
    }

    $stmt->close();
}

function baseUrl() {
    // Get the full path to the current script
    $scriptPath = $_SERVER['PHP_SELF'];

    // Find the second occurrence of '/'
    $position = strpos($scriptPath, '/', 1);

    // If there is a second '/', trim the path up to that point
    if ($position !== false) {
        $baseDir = substr($scriptPath, 0, $position);
    } else {
        $baseDir = $scriptPath; // In case there's no second '/', return the full path
    }

    // Normalize the base directory to ensure it ends with a slash
    $baseDir = rtrim($baseDir, '/') . '/';

    return $baseDir;
}


?>