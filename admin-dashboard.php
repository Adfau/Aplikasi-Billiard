<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header( "Location: signin.php" );
    exit();
}

define('INCLUDED', true);
define('PAGE_DASHBOARD', true);
?>

<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>
<body>
    <div class="container-fluid">
        <div class="row no-padding">
            <?php include("header.php"); ?>
            <div class="col no-padding d-flex">
                <?php include("sidebar-admin.php"); ?>
<div class="container main-content">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Main Content -->
            <h1>Dashboard</h1>
            <hr>

<?php
require_once('controller.php');
// Function to fetch billing data from yesterday onwards
function fetchBillingData($con) {

    // Calculate yesterday's date
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $query = "SELECT * FROM billing_history WHERE is_paid = 1 AND deleted_at >= ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

// Function to fetch F&B data from yesterday onwards
function fetchFnbData($con) {

    // Calculate yesterday's date
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $query = "SELECT * FROM fnb_orders WHERE timestamp >= ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

// Function to fetch F&B data from yesterday onwards
function fetchLogData($con) {

    // Calculate yesterday's date
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $query = "SELECT * FROM activity_log WHERE timestamp >= ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $yesterday);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

function convertDurationToMinutes($duration) {
    $parts = explode(':', $duration);
    $hours = (int) $parts[0];
    $minutes = (int) $parts[1];
    $seconds = (int) $parts[2];
    return $hours * 60 + $minutes + $seconds / 60;
}

// Fetch data from the database
$billingData = fetchBillingData($con);
$fnbData = fetchFnbData($con);
$logData = fetchLogData($con);

$totalPengunjung = 0;
$totalPenghasilan = 0;
$totalDurasi = 0;
$totalPelanggan = count($billingData);

foreach ($billingData as $row) {
    $durasi = convertDurationToMinutes($row['durasi']);
    $harga = $row['harga'];

    $totalDurasi += $durasi / 60;
    $totalPenghasilan += $harga;
    $totalPengunjung++;
}

$totalJumlah = 0;
$totalPendapatan = 0;

foreach ($fnbData as $row) {
    $jumlah = $row['jumlah_fnb'];
    $hargaTotal = $row['total_fnb'];

    $totalJumlah += $jumlah;
    $totalPendapatan += $hargaTotal;
    $totalPenghasilan += $hargaTotal;
}

$totalAktivitas = 0;
foreach ($logData as $row) {
    $totalAktivitas++;
}

$formattedTotalPenghasilan = number_format($totalPenghasilan,0,",",".");

?>


            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <!-- Big Icons -->
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5 class="card-title text-muted">Pengunjung</h5>
                            <h2><?php echo $totalPengunjung ?></h2> <!-- Big number for count -->
                        </div>
                        <div class="card-footer text-muted text-center">
                            24 Jam Terakhir
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <!-- Big Icons -->
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h5 class="card-title text-muted">Penghasilan</h5>
                            <h2>Rp <?php echo $formattedTotalPenghasilan ?></h2> <!-- Big number for count -->
                        </div>
                        <div class="card-footer text-muted text-center">
                            24 Jam Terakhir
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <!-- Big Icons -->
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <h5 class="card-title text-muted">Aktivitas</h5>
                            <h2><?php echo $totalAktivitas ?></h2> <!-- Big number for count -->
                        </div>
                        <div class="card-footer text-muted text-center">
                            24 Jam Terakhir
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div style="margin-left: 40px;"></div>
            </div>
        </div>
    </div>
    
    <?php
        function displayTimer() {
            // JavaScript code to update countdown dynamically
            $jsCode = "<script>";
            $timeZone = date_default_timezone_get();
            $jsCode .= "let options = {timeZone: '$timeZone'};";
            $jsCode .= "var textWaktu = 'Sisa Waktu: ';";

            $jsCode .= "function updateTimer() {";

                //Loop
                $jsCode .= "requestAnimationFrame(updateTimer);";
                
                //Live Clock
                $jsCode .= "var now = new Date();";
                $jsCode .= "var datetimeElement = document.getElementById('datetime');";
                $jsCode .= "datetimeElement.textContent = now.toLocaleString('en-GB', options);";
                
            $jsCode .= "}";

            $jsCode .= "updateTimer();"; //Update every frame

            $jsCode .= "</script>";
            
            // Output JavaScript code
            echo $jsCode;
        }

        displayTimer();

    ?>

</body>
</html>