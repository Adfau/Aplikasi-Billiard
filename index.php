<?php
session_start();

if (!isset($_SESSION['level'])) {
    header( "Location: signin.php" );
    exit();
}

require_once('controller.php');
define('INCLUDED', true);

$sql = "SELECT * FROM meja";

if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql = "SELECT * FROM billing WHERE `nama_penyewa` LIKE ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = mysqli_query($con, $sql);
}

$query_bill = "SELECT no_meja FROM billing";
$status_result = mysqli_query($con, $query_bill);

$status = array();
while ($row = mysqli_fetch_array($status_result)) {
    $status[] = $row['no_meja'];
}
$active_table_count = count($status);

// $query_checkout = "SELECT no_meja FROM billing_checkout";
// $status_checkout_result = mysqli_query($con, $query_checkout);
// $statusCheckout = array();
// while ($row = mysqli_fetch_array($status_checkout_result)) {
//     $statusCheckout[] = $row['no_meja'];
// }

$timercount = 0;
$no_meja = 0;
$targets = array();

?>

<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>
<body>
    <?php include("modalform.php"); ?>
    <div class="container-fluid">
        <div class="row">
            <?php include("sidebar.php"); ?>
            <div class="col no-padding">
                <?php include("header.php"); ?>
                <div class="container main-content">
                    <div class="row justify-content-center">
                        <!-- Inisialisasi & Pembuatan Card Meja -->
                        <?php  while ($row = mysqli_fetch_array($result)) : ?>
                        <?php
                            $timer = "";
                            $billStatus = "";
                            //$statusMeja = checkStatusMeja($con, $row['no_meja']);
                            //$statusMeja = $row['status];
                            $statusMeja = NULL;
                            $statusCheckout = FALSE;
                            $id = "";
                            $penyewa = "";
                            $harga = "";
                            $waktuMulai = "";
                            $waktuSelesai = "";
                            $hargaPerJam = NULL;
                            
                            $durasi = "";
                            // $count++;

                            //Default Style
                            $type = "ON";
                            $text = $type;
                            $color = "green";
                            $textDurasi = "Durasi: ";

                            if(in_array($row['no_meja'], $status)) {
                                //Data
                                $no_meja = $row['no_meja'];
                                $data = fetchData($con, $no_meja);

                                $id = $data['id'];
                                $penyewa = $data['nama_penyewa'];
                                $harga = $data['harga'];
                                
                                $waktuMulai = $data['waktu_mulai'];
                                $waktuSelesai = $data['waktu_selesai'];

                                $sisaWaktu = $data['durasi'];
                                $durasi = $sisaWaktu;
                                $hargaPerJam = $data['harga_perjam'];
                                $pauseTime = 0;

                                $statusMeja = $data['status'];

                                $timer = ++$timercount;
                                
                                // Inisialisasi waktu
                                $startTime = strtotime($waktuMulai);
                                $endTime = strtotime($waktuSelesai);
                                $currentTime = time();

                                // Cek jika sudah waktunya mulai
                                if (($currentTime >= $startTime) && ($currentTime < $endTime)) {
                                    $targetTime = strtotime($waktuSelesai);
                                    $elementId = "timer" . $timercount;

                                    // Menjalankan timer kecuali untuk status meja PAUSE
                                    if ($statusMeja != "PAUSE") $targets[] = array("time" => $targetTime, "elementId" => $elementId, "durasi" => $pauseTime, "status" => "");

                                } elseif ($currentTime < $startTime) { // Cek jika belum mulai tapi ada di database, maka menjadi reservasi.
                                    // Style status reservasi
                                    $billStatus = "BILLED";
                                    $color = "orange";
                                    $text = "RESERVED";

                                    $targetTime = strtotime($waktuMulai);
                                    $elementId = "timer" . $timercount;

                                    if ($statusMeja != "PAUSE") $targets[] = array("time" => $targetTime, "elementId" => $elementId, "status" => "RESERVED");

                                } elseif ($currentTime > $endTime) { // Cek jika sudah mulai tapi waktu sudah berakhir, menandakan mode open atau meja yang belum diproses event.
                                    if ($waktuSelesai === NULL) { // Double check mode open
                                        $waktuSelesai = "-";
                                        $targetTime = strtotime($waktuMulai);
                                        $elementId = "timer" . $timercount;
                                        $pauseTime = timeToSeconds($sisaWaktu);
                                        $idHarga = "hargaOpen" . $no_meja;

                                        // Menjalankan timer kecuali untuk status meja PAUSE
                                        if ($statusMeja != "PAUSE") $targets[] = array("time" => $targetTime, "elementId" => $elementId, "durasi" => $pauseTime, "status" => "OPEN", "hargaId" => $idHarga, "harga" => $hargaPerJam);

                                        // Kalkulasi jam yang terlewat
                                        // $hoursPassed = round((time() - $startTime) / (3600));

                                        // // Menambah harga untuk tiap jam yang terlewat
                                        // $harga = $hoursPassed * getHarga();

                                        $minutesPassed = floor((time() - $startTime) / (60));
                                        //$harga = ceil(($minutesPassed * getHarga()) / 60);
                                        $harga = ceil(($minutesPassed * $hargaPerJam) / 60);
                                    } else {
                                        // Berikan warna hitam untuk menandakan bug.
                                        $color = "black";
                                        $text = "BUG | tekan tombol matikan";

                                        // Bug fix, paksa hapus
                                        echo "showLoading();";
                                        echo '<script>window.location.href = "proses/hapus_billing.php?id=' . $no_meja . '";</script>';
                                    }
                                }

                                // Style untuk status meja PAUSE
                                if ($statusMeja == "PAUSE") {
                                    if (($currentTime >= $startTime) && ($currentTime < $endTime)) {
                                        $textDurasi = "Sisa Waktu: ";
                                        $durasi = formatSeconds($endTime - $currentTime);
                                    } elseif ($currentTime > $endTime) {
                                        $textDurasi = "OPEN ";
                                        $durasi = formatSeconds(abs($startTime - $currentTime));
                                    }
                                    $color = "orange";
                                    $text = "PAUSE";
                                }

                                // Format harga
                                $harga = number_format($harga,0,",",".");

                            } else {
                                // Set style untuk meja mati
                                $type = "OFF";
                                $text = $type;
                                $color = "red";

                                // Cek history data
                                $no_meja = $row['no_meja'];  
                                
                                $data = fetchDataHistory($con, $no_meja);
                                if ($data != NULL) {
                                    $id = $data['billing_id'];
                                    $penyewa = $data['nama_penyewa'];
                                    $harga = number_format($data['harga'],0,",",".");
                                    
                                    $waktuMulai = $data['waktu_mulai'];
                                    $waktuSelesai = $data['waktu_selesai'];
                                    // $startTime = strtotime($waktuMulai);
                                    // $endTime = strtotime($waktuSelesai);
                                    // $durasi = formatSeconds($endTime - $startTime);
                                    $durasi = $data['durasi'];
                                    $is_paid = $data['is_paid'];
                                    
                                    if (!$is_paid) {
                                        // Set style untuk meja checkout
                                        $type = "CHECKOUT";
                                        $text = $type;
                                        $color = "blue";

                                        $statusCheckout = TRUE;
                                    }
                                }
                            }
                        ?>
                        <div class="col-md-4 card-table" id="hasContextMenu" <?php echo 'data-link-id="' . $no_meja . '" data-link-type="'. $type .'"';?>>
                            <div class="card <?php echo $color ?>-shadow">
                                <div class="card-header prevent-select justify-content-between align-items-center d-flex">
                                        <h4 style="margin-bottom: 0px; margin-top: 5px;">
                                            <div class="card-title numbered-box <?php echo $color ?>-box">
                                                <?php $no = $no_meja < 10 ? "0".$no_meja : $no_meja; echo $no; //Format nomer 01-09 ?>
                                            </div>
                                            <div class="card-title <?php echo $color ?>-text">
                                                <?php echo $text; ?>
                                            </div>
                                        </h4>
                                </div>
                                <div class="card-body">

                                    <h6 id="timer<?php echo $timer; ?>"><?php echo $textDurasi . $durasi; ?> </h6>
                                    <p class="card-text"><span>Nama</span>: <?php echo $penyewa; ?></p>
                                    <p class="card-text"><span>Billing ID</span>: <?php echo $id; ?></p>
                                    <p class="card-text"><span>Mulai</span>: <?php echo $waktuMulai; ?></p>
                                    <p class="card-text"><span>Selesai</span>: <?php echo $waktuSelesai; ?></p>
                                    <div class="card-text d-flex align-items-center justify-content-between" style="margin-top: 10px; margin-bottom: 0px; overflow-y: hidden;">
                                        <h6 <?php if ($type === "ON") echo 'id=hargaOpen' . $no_meja . ' ' ?>style="margin-bottom: 0px;">Rp. <?php echo $harga ?></h6>
                                        <?php if ($hargaPerJam !== NULL) {
                                            $textHargaPerJam = number_format($hargaPerJam,0,",",".");
                                            $hargaPerMenit = number_format(ceil($hargaPerJam / 60),0,",",".");
                                            echo '<small class="text-muted" style="margin-bottom: 0px; font-size: 12px;">('.$textHargaPerJam.'/jam, ~'.$hargaPerMenit.'/menit)</small>';
                                        } ?>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <?php if(in_array($row['no_meja'], $status)) : ?>
                                        <button class="btn btn-outline-primary cashierButton" style="font-weight: bold;" id="cashierButton" data-toggle="modal" data-backdrop="static" data-target="#cashierModal" data-cashier-id="<?php echo $no_meja ?>" data-table-id="<?php echo $no_meja ?>" data-billing-id="<?php echo $id ?>"><i class="fa fa-file-text" aria-hidden="true"></i> FnB</button>
                                        <?php if($billStatus != "BILLED") : ?>
                                            <div class="text-center">
                                            <button type="button" class="btn circle-btn editButton" id="editButton" data-toggle="modal" data-target="#editModal" data-table-id="<?php echo $no_meja ?>"><i class="fa fa-cog" aria-hidden="true"></i></button>
                                            <!-- <button class="btn circle-btn" onclick="confirmPause(<?php echo $no_meja ?>)"><i class="<?php $pauseIcon = ($statusMeja == "PAUSE") ? "fas fa-play" : "fa fa-pause"; echo $pauseIcon ?>"></i></button> -->
                                            </div>
                                        <?php endif ?>
                                        <button class="btn btn-outline-danger" style="font-weight: bold;" onclick="confirmDelete(<?php echo $no_meja ?>)"><i class="fas fa-power-off"></i> Matikan</button>
                                    <?php elseif ($statusCheckout) : ?>
                                        <button class="btn btn-outline-primary cashierButton" style="font-weight: bold;" id="cashierButton" data-toggle="modal" data-backdrop="static" data-target="#cashierModal" data-cashier-id="<?php echo $no_meja ?>" data-table-id="<?php echo $no_meja ?>" data-billing-id="<?php echo $id ?>"><i class="fa fa-file-text" aria-hidden="true"></i> FnB</button>
                                        <button class="btn btn-outline-success checkoutButton" style="font-weight: bold;" id="checkoutButton" data-toggle="modal" data-target="#checkoutModal" data-table-id="<?php echo $no_meja ?>" data-billing-id="<?php echo $id ?>"><i class="fa fa-money" aria-hidden="true"></i> Bayar</button>
                                        
                                    <?php else : ?>
                                        <button class="btn btn-outline-secondary receiptButton" style="font-weight: bold;" id="receiptButton" data-toggle="modal" data-target="#receiptModal" data-receipt-id="<?php echo $no_meja ?>" data-billing-id="<?php echo $id ?>"><i class="fas fa-file"></i> Struk</button>
                                        <button class="btn btn-outline-success addButton" style="font-weight: bold;" id="addButton" data-toggle="modal" data-target="#addModal" data-table-id="<?php echo $no_meja ?>"><i class="fas fa-power-off"></i> Nyalakan</button>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p style="text-align: center;">&copy; <?php echo date("Y"); ?> Geni Tech</p>
    
    <?php
        function displayTimer($targets) {
            // JavaScript code to update countdown dynamically
            $jsCode = "<script>";
            $timeZone = date_default_timezone_get();
            $jsCode .= "let options = {timeZone: '$timeZone'};";
            $jsCode .= "var textWaktu = 'Sisa Waktu: ';";

            // $jsCode .= "var lastCheckedDay = new Date().getDay();";

            $jsCode .= "function updateTimer() {";

                //Loop
                $jsCode .= "requestAnimationFrame(updateTimer);";

                //Reload di pergantian hari weekday/weekend
                // $jsCode .= "var currentDay = new Date().getDay();";
                // $jsCode .= "if ((lastCheckedDay != 6 && currentDay == 6) || (lastCheckedDay != 1 && currentDay == 1)) {";
                //     $jsCode .= "reloadPageAfterDelay(1000);";
                // $jsCode .= "}";
                        
                //Live Clock
                $jsCode .= "var now = new Date();";
                $jsCode .= "var datetimeElement = document.getElementById('datetime');";
                $jsCode .= "datetimeElement.textContent = now.toLocaleString('en-GB', options);";

                // Get current time in milliseconds
                $jsCode .= "var currentTime = Math.floor(new Date().getTime() / 1000);";

                $jsCode .= "function updateTargetTimer(targetId, targetTime, targetStatus, targetHarga, targetHargaId) {";
                    // Calculate time difference
                    $jsCode .= "var timeDiff = targetTime - currentTime;";
        
                    // Check if it's open and count up
                    $jsCode .= "if (targetStatus === 'OPEN') {";
                        $jsCode .= "timeDiff = Math.abs(timeDiff);";
                        $jsCode .= "textWaktu = '[OPEN] ';";
                        $jsCode .= "if (timeDiff % 60 <= 0) document.getElementById(targetHargaId).textContent = 'Rp. ' + formatHarga(timeDiff, targetHarga);";
                    $jsCode .= "} else {";
                        $jsCode .= "textWaktu = (targetStatus === 'RESERVED') ? 'Mulai: ' : 'Sisa Waktu: ';";
                        // Check if time is reached
                        $jsCode .= "if (timeDiff == 0) {reloadPageAfterDelay(1000)}";
                    $jsCode .= "}";
                    
                    // Update countdown text
                    $jsCode .= "document.getElementById(targetId).textContent = textWaktu + formatTime(timeDiff);";
                $jsCode .= "}";

                // Loop through each target
                foreach ($targets as $target) {
                    // Parse target time
                    $targetId = $target['elementId'];
                    $targetTime = $target['time'];
                    $targetStatus = array_key_exists("status", $target) ? $target['status'] : '';
                    $targetHarga = array_key_exists("harga", $target) ? $target['harga'] : 50000;
                    $targetHargaId = array_key_exists("hargaId", $target) ? $target['hargaId'] : '';

                    $jsCode .= "updateTargetTimer('$targetId', $targetTime, '$targetStatus', $targetHarga, '$targetHargaId');";
                }
                
                // Function to format time
                $jsCode .= "function formatTime(timeDiff) {";
                    // If target time is in the past, adjust for the next day
                    // $jsCode .= "if (timeDiff < 0) {";
                    //$jsCode .= "timeDiff += 86400;"; // 86400 seconds = 24 hours
                    // $jsCode .= "}";
                    $jsCode .= "var hours = Math.floor(timeDiff / 3600);";
                    $jsCode .= "var minutes = Math.floor((timeDiff % 3600) / 60);";
                    $jsCode .= "var seconds = timeDiff % 60;";
                    $jsCode .= "hours = ('0' + hours).slice(-2);";
                    $jsCode .= "minutes = ('0' + minutes).slice(-2);";
                    $jsCode .= "seconds = ('0' + seconds).slice(-2);";
                    $jsCode .= "return hours + ':' + minutes + ':' + seconds;";
                $jsCode .= "}";
                
            $jsCode .= "}";

            $jsCode .= "updateTimer();"; //Update every frame

            $jsCode .= "</script>";
            
            // Output JavaScript code
            echo $jsCode;
        }

        displayTimer($targets);

    ?>

    <script>
        //Reload Page Function
        var RPF_reloadTimer;
        var RPF_mustReload = false;
        var RPF_isPaused = false;

        // Function to show the loading screen
        function showLoading() {
            document.getElementById('loadingScreen').style.display = 'flex';
        }

        // Function to hide the loading screen
        function hideLoading() {
            document.getElementById('loadingScreen').style.display = 'none';
        }

        // Function to reload the page after a specified time
        function reloadPageAfterDelay(delay) {
            RPF_mustReload = true;
            if (!RPF_isPaused) {
                showLoading();
                RPF_reloadTimer = setTimeout(function() {location.reload();}, delay);
            }
        }

        // Function to pause the reload timer
        function pauseReloadTimer() {
            if (RPF_reloadTimer) {
                clearTimeout(RPF_reloadTimer);
                RPF_reloadTimer = NULL;
                hideLoading();
            }
            RPF_isPaused = true;
        }

        // Function to resume the reload timer
        function resumeReloadTimer() {
            RPF_isPaused = false;
            if (RPF_mustReload) {
                location.reload();
            }
        }
    </script>

    <script>

        //Reload when focused
        // window.addEventListener('focus', function(){
        //     document.location.reload(); 
        //     console.log("focus");
        // });

        $(document).ready(function() {
            <?php
            // Bunyi beep ketika ada status success
            if (isset($_SESSION['status'])) {
                if ($_SESSION['status'] === "Success") {
                    unset($_SESSION['status']);
                    echo "playSoundBeep();";
                }
            }
            ?>

            // Detect when any modal is opened
            $(document).on('shown.bs.modal', '.modal', function () {
                pauseReloadTimer();
            });

            // Detect when any modal is closed
            $(document).on('hidden.bs.modal', '.modal', function () {
                resumeReloadTimer();
            });
            
            $('#addModal, #editModal').on('shown.bs.modal', function () {
                $(this).find('.focused-input').focus();
            });

            //Set modal title and form action URL based on the button clicked
            $('.addButton').click(function() {
                var tableId = $(this).data('table-id');
                var formatId = (tableId < 10) ? '0'+tableId : tableId;
                //$('#addModalLabel').text('Sewa Meja ' + tableId);
                $('#addModalLabel').html('<div class="card-title numbered-box signature-box">' + formatId + '</div>');
                $('#rentalForm').attr('method', 'POST');
                $('#rentalForm').attr('action', 'proses/tambah_billing.php?id=' + tableId);
                $('#no_meja').val(tableId);
            });

            // Digunakan event delegation karena editButton adalah elemen dinamis.
            $(document).on('click', '.editButton', function() {
                var tableId = $(this).data('table-id');
                var formatId = (tableId < 10) ? '0'+tableId : tableId;

                // Ajax untuk mengambil data nama penyewa
                $.ajax({
                    url: 'proses/get_data_billing.php',
                    type: 'POST',
                    data: { no_meja: tableId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === "success") {
                            var data = response.data;
                            $('#editNamaPenyewa').val(data.nama_penyewa);
                        } else {
                            console.error(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("An error occurred: " + error);
                    }
                });

                // $('#editModalLabel').text('Edit Meja ' + tableId);
                $('#editModalLabel').html('<div class="card-title numbered-box signature-box">' + formatId + '</div>');
                $('#editForm').attr('method', 'POST');
                $('#editForm').attr('action', 'proses/update_billing.php?id=' + tableId);
                $('#no_meja').val(tableId);
            });

            //Set modal title and form action URL based on the button clicked
            //$('.receiptButton').click(function() {
            $(document).on('click', '.receiptButton', function() {
                pauseReloadTimer();
                var tableId = $(this).data('receipt-id');
                var billingId = $(this).data('billing-id');
                //var formatId = (tableId < 10) ? '0'+tableId : tableId;

                $('#receiptModalLabel').text('Struk Meja ' + tableId);
                $('#printReceipt').attr('method', 'POST');
                $('#printReceipt').attr('action', 'proses/cetak_struk.php?id=' + billingId);
                renderPdfStruk(billingId);
            });

            // Calculate and update "Sisa Waktu" when either "Waktu Mulai" or "Waktu Selesai" changes
            $('#waktuMulai, #waktuSelesai').change(function() {
                calculateSisaWaktu();
            });
            $('#sisaWaktu').change(function() {
                 calculateHarga();
            });
        
            // Fungsi reload pada pergantian hari weekday dan weekend
            function checkDayTransition() {
                var now = new Date();
                var currentDay = now.getDay(); // 0 = Sunday, 5 = Friday
                var currentHour = now.getHours();
                var currentMinute = now.getMinutes();
                var currentSecond = now.getSeconds();

                // Calculate milliseconds until midnight
                var msUntilMidnight = (24 * 60 * 60 * 1000) - (currentHour * 60 * 60 * 1000 + currentMinute * 60 * 1000 + currentSecond * 1000);

                // Check if today is Friday (5) or Sunday (0)
                if (currentDay === 5 || currentDay === 0) {
                    setTimeout(function() { location.reload(); }, msUntilMidnight);
                } else if (currentDay === 6) {
                    // If today is Saturday, calculate time until Monday midnight
                    var msUntilMonday = msUntilMidnight + 24 * 60 * 60 * 1000; // Add one day
                    setTimeout(function() { location.reload(); }, msUntilMonday);
                } else {
                    // If today is any other day, calculate time until Saturday midnight
                    var msUntilSaturday = msUntilMidnight + (5 - currentDay) * 24 * 60 * 60 * 1000;
                    setTimeout(function() { location.reload(); }, msUntilSaturday);
                }
            }

            // Initial call to set up the timeout
            checkDayTransition();
        });

        document.addEventListener('contextmenu', event => event.preventDefault());

        // Add event listener to all elements with class 'myElement'
        document.querySelectorAll('#hasContextMenu').forEach(element => {
            element.addEventListener('contextmenu', function(event) {
                event.preventDefault();
                
                // Get the link associated with this element
                const linkId = element.getAttribute('data-link-id');
                const linkType = element.getAttribute('data-link-type');
                
                // Show custom context menu
                showCustomContextMenu(event.clientX, event.clientY, linkId, linkType);
            });
        });

        // Function to show custom context menu
        function showCustomContextMenu(x, y, linkId, linkType, previousId = 0) {

            // Remove any existing context menus
            var existingContextMenu = document.getElementById("customContextMenu");
            if (existingContextMenu && existingContextMenu.parentNode) {
                existingContextMenu.parentNode.removeChild(existingContextMenu);
            }

            // Create a div for the custom context menu
            var contextMenu = document.createElement("div");
            contextMenu.id = "customContextMenu";
            contextMenu.style.position = "fixed";
            
            // Append context menu to the document body to measure its dimensions
            document.body.appendChild(contextMenu);

                contextMenu.classList.add("btn-group-vertical"); // Add Bootstrap btn-group class
                contextMenu.setAttribute("role", "group"); // Add Bootstrap role attribute
                contextMenu.setAttribute("aria-label", "Custom Options"); // Add Bootstrap aria-label attribute

                // Add menu items as Bootstrap buttons
                if (linkType == "OFF") {
                    contextMenu.innerHTML = `
                        <h6 style="color: white; border-bottom: 1px solid;" class="prevent-select">MEJA `+linkId+`</h6>
                        <button type="button" class="btn btn-warning" onclick="quickAdd(`+linkId+`, '01:00:00', event)">1 Jam</button>
                        <button type="button" class="btn btn-secondary" onclick="quickAdd(`+linkId+`, '02:00:00', event)">2 Jam</button>
                        <button type="button" class="btn btn-success" onclick="quickAdd(`+linkId+`, '03:00:00', event)">3 Jam</button>
                        <button type="button" class="btn btn-success" onclick="quickAdd(`+linkId+`, '--:--:--', event)">Open</button>
                    `;
                } else if (linkType == "ON") {
                    contextMenu.innerHTML = `
                        <h6 style="color: white; border-bottom: 1px solid;" class="prevent-select">MEJA `+linkId+`</h6>
                        <button type="button" class="btn btn-warning editButton" id="editButton" data-toggle="modal" data-target="#editModal" data-table-id="`+linkId+`">Edit</button>
                    `; 
                } else {
                    contextMenu.innerHTML = `
                        <h6 style="color: white; border-bottom: 1px solid;" class="prevent-select">MEJA `+linkId+`</h6>
                    `; 
                }

                // Get context menu dimensions
                const contextMenuWidth = contextMenu.offsetWidth + 15;
                const contextMenuHeight = contextMenu.offsetHeight;

            // Remove context menu from the DOM
            document.body.removeChild(contextMenu);

            // Get viewport dimensions
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // Adjust position to keep the context menu within the viewport bounds
            let adjustedX = x;
            let adjustedY = y;

            if (x + contextMenuWidth > viewportWidth) {
                adjustedX = viewportWidth - contextMenuWidth;
            }

            if (y + contextMenuHeight > viewportHeight) {
                adjustedY = viewportHeight - contextMenuHeight;
            }
            
            contextMenu.style.top = adjustedY + "px";
            contextMenu.style.left = adjustedX + "px";

            // Append custom context menu to the document
            document.body.appendChild(contextMenu);

            // Close the custom context menu when clicking outside of it. Add delay so it doesn't disappear right away
            setTimeout(function() {
                document.addEventListener("click", function(event) {
                    if (!contextMenu.contains(event.target) && contextMenu.parentNode) {
                        contextMenu.parentNode.removeChild(contextMenu);
                    }
                });
            }, 100);
            
        }

        // Function to trigger modal for a specific data-table-id
        function quickAdd(tableId, durasi, event) {
            // Construct the selector based on data-table-id
            var selector = '[data-table-id="' + tableId + '"]';

            // Find the element with the specified data-table-id
            var element = document.querySelector(selector);

            // If element is found, trigger the modal
            if (element) {
                element.click();
                
                // Set value of sisaWaktu input field
                var sisaWaktuInput = document.getElementById('sisaWaktu');
                if (sisaWaktuInput) {
                    sisaWaktuInput.value = durasi;
                    calculateHarga();
                } else {
                    console.error('Element with id sisaWaktu not found.');
                }
                if (event.shiftKey) {
                    showLoading();
                    document.getElementById('namaPenyewa').value = "";
                    document.getElementById('addSubmitButton').click();
                }
            } else {
                console.error('Element with data-table-id ' + tableId + ' not found.');
            }
        }


        // Function to calculate "Sisa Waktu"
        function calculateSisaWaktu() {
            var waktuMulai = new Date("1970-01-01 " + $('#waktuMulai').val());
            var waktuSelesai = new Date("1970-01-01 " + $('#waktuSelesai').val());
            var sisaWaktuMs = waktuSelesai - waktuMulai;
            var sisaWaktuHours = Math.floor(sisaWaktuMs / (1000 * 60 * 60));
            //Wrap around
            if (sisaWaktuHours < 0) sisaWaktuHours += 24;
            var sisaWaktuFormatted = new Date(sisaWaktuMs).toISOString().substr(11, 8);
            $('#sisaWaktu').val(sisaWaktuFormatted);

            // Calculate Harga
            calculateHarga();
        }

        // Function to calculate "Sisa Waktu"
        function calculateHarga() {
            // Get the value of sisaWaktu in format HH:MM
            var sisaWaktu = $('#sisaWaktu').val();

            // Split the sisaWaktu string to extract the hours and minutes
            var sisaWaktuArray = sisaWaktu.split(":");
            var hours = parseInt(sisaWaktuArray[0]);
            var minutes = parseInt(sisaWaktuArray[1]);

            // Convert hours to minutes and add to the total minutes
            var totalMinutes = (hours * 60) + minutes;

            // Calculate the price based on the total number of minutes
            var hargaPerJam = 50000;
            <?php echo 'hargaPerJam = ' . getHarga(); ?>;
            var harga = Math.ceil((totalMinutes * hargaPerJam) / 60);

            // Set the calculated price to the harga input field
            $('#harga').val(harga);
        }

        function formatHarga(values, prices = 50000) {
            var hours = Math.floor(values / 3600);
            var minutes = Math.floor((values % 3600) / 60);
            var seconds = values % 60;

            // Convert hours to minutes and add to the total minutes
            var totalMinutes = (hours * 60) + minutes;

            // Calculate the price based on the total number of minutes
            var prices = Math.floor((totalMinutes * prices) / 60);

            var formatHarga = prices.toLocaleString('id-ID');
            return formatHarga;
        }


        function confirmDelete(id) {
            var confirmation = confirm("Matikan MEJA "+id+"?");
            
            if (confirmation) {
                showLoading();
                window.location.href = "proses/hapus_billing.php?id=" + id;
            }
        }
        function confirmPause(id) {
            var confirmation = confirm("Pause/Unpause MEJA "+id+"?");
            
            if (confirmation) {
                window.location.href = "proses/pause_billing.php?id=" + id;
            }
        }

        window.addEventListener('beforeunload', function(event) {
            // Get the current scroll position
            var scrollPosition = window.scrollY || window.pageYOffset;

            // Send the scroll position to the server via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "proses/scroll_position.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("scrollPosition=" + scrollPosition);
        });

        <?php if (isset($_SESSION['scrollPosition'])) : ?>
        document.addEventListener('DOMContentLoaded', function() {
            var scrollPosition = parseInt(<?php echo $_SESSION['scrollPosition']?>);
            if (!isNaN(scrollPosition)) {
                window.scrollTo({
                    top: scrollPosition,
                    left: 0,
                    behavior: 'instant',
                });
            }
        });
        <?php endif ?>

        document.addEventListener("visibilitychange", function() {
            if (!document.hidden) {
                console.log("Browser tab is loaded");
                reloadPageAfterDelay(100);
            }
        });

    </script>
</body>
</html>

<?php
    function fetchData($con, $id) {
        $query = "SELECT billing.*, meja.status as status_meja FROM billing,meja WHERE meja.no_meja = billing.no_meja AND billing.no_meja = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->fetch_assoc();
    }
    function fetchDataHistory($con, $id) {
        $query = "SELECT * FROM billing_history WHERE `no_meja` = ? ORDER BY deleted_at DESC LIMIT 1";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return NULL;
        }
    }
    function fetchDataCheckout($con, $id) {
        $query = "SELECT * FROM billing_checkout WHERE `no_meja` = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return NULL;
        }
    }
    function checkStatusMeja($con, $no_meja) {
        $query = "SELECT status FROM meja WHERE no_meja = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $no_meja);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $status = $data['status'];
            $stmt->close();
            return $status;
        } else {
            $stmt->close();
            return 0;
        }
    }
    
    mysqli_close($con);
?>
