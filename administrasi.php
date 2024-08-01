<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header( "Location: signin.php" );
    exit();
}

require_once('controller.php');
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
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <!-- Big Icons -->
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5 class="card-title text-muted">Pengunjung</h5>
                            <h2>10</h2> <!-- Big number for count -->
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
                            <h2>Rp 500.000</h2> <!-- Big number for count -->
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
                            <h2>50</h2> <!-- Big number for count -->
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