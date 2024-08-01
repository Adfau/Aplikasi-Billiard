<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header( "Location: signin.php" );
    exit();
}

require_once('controller.php');
define('INCLUDED', true);
define('PAGE_SETTING', true);

$current_timezone = $GLOBALS['$current_timezone'];
$hargaWeekdays = $GLOBALS['$current_hargaWeekdays'];
$hargaWeekends = $GLOBALS['$current_hargaWeekends'];

$hWdays = number_format($hargaWeekdays, 0, ',', '.');
$hWends = number_format($hargaWeekends, 0, ',', '.');

?>

<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>
<head>
<style>
    .main-content {
        margin-top: 50px;
    }
    .settings-header {
        text-align: left;
    }
    .settings-content {
        display: flex;
        align-items: center;
        margin-top: 20px;
    }
    .settings-content .left {
        flex: 1;
    }
    .settings-content .right {
        margin-left: 20px;
    }

    .form-inline {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .form-inline label {
        margin-left: 10px;
    }
    .form-inline .form-control {
        width: auto;
    }
    .hidden {
        display: none;
    }
    .warning {
        color: red;
        margin-top: 10px;
        display: none;
    }

    /* Disable hover effect*/
    .toggle-effect:hover {
        color: inherit;
        background-color: transparent;
        border-color: inherit;
    }
    
</style>
</head>
<body>
    <div class="container-fluid">
        <div class="row no-padding">
            <?php include("header.php"); ?>
            <div class="col no-padding d-flex">
                <?php include("sidebar-admin.php"); ?>
                
                <div class="container main-content">
                    <div class="row">
                        <div class="col-12 settings-header">
                            <h1>Settings</h1>
                            <hr>
                        </div>
                        <div class="col-5 settings-content">
                            <div class="left">
                                <h2><?php echo date("j F Y"); ?></h2>
                            </div>
                            <div class="right">
                                <h2><i class="fa fa-clock-o" aria-hidden="true"></i> Time Zone</h2>
                                <select class="form-control w-auto" id="timezoneSelect" onchange="updateTimeZone()">
                                    <option value="WIB" <?php echo ($current_timezone === 'WIB') ? 'selected' : ''; ?>>UTC+7 (WIB)</option>
                                    <option value="WITA" <?php echo ($current_timezone === 'WITA') ? 'selected' : ''; ?>>UTC+8 (WITA)</option>
                                    <option value="WIT" <?php echo ($current_timezone === 'WIT') ? 'selected' : ''; ?>>UTC+9 (WIT)</option>
                                </select>
                            </div>
                        </div>
                        <div><hr></div>
                        <form id="priceForm" action="proses/update_harga.php" method="POST" class="col-12">
                            <div class="col-12 settings-content">
                                <div class="justify-content-center aligns-item-center text-center">
                                    <h3>Setel Harga<h3>
                                </div>
                            </div>
                            <div class="settings-content">
                                <div class="right"; style="max-width: 20vh;">
                                    <h5 id="hargaLabel">Harga Per Jam</h5>
                                </div>
                                <div class="right">
                                    <h5>Rp.</h5>
                                </div>
                                <div style="margin-left: 10px;">
                                    <div class="form-inline" id="weekdaysGroup">
                                        <input type="text" class="form-control w-auto synced-input" name="hargaWeekdays" id="hargaWeekdays" value="<?php echo $hWdays; ?>" placeholder="Harga Weekdays" oninput="showWarning()" pattern="[0-9]+(\.[0-9]+)*" required>
                                        <label for="hargaWeekdays">(Senin - Jum'at)</label>
                                    </div>
                                    <div class="form-inline" id="weekendsGroup">
                                        <input type="text" class="form-control w-auto synced-input" name="hargaWeekends" id="hargaWeekends" value="<?php echo $hWends; ?>" placeholder="Harga Weekends" oninput="showWarning()" pattern="[0-9]+(\.[0-9]+)*" required>
                                        <label for="hargaWeekends">(Sabtu - Minggu)</label>
                                    </div>
                                </div>
                                <div style="margin-left: 20px;">
                                    <button type="button" id="toggleButton" class="btn btn-outline-secondary toggle-effect" data-toggle="button" aria-pressed="false" autocomplete="off">
                                    <i class="fa fa-link" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="settings-content">
                                <div class="right">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <p class="warning" id="warningText"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Harga belum disave!</p>
                                </div>
                            </div>
                        </form>
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
    <script>
        function toggleHargaFields() {
            var isChecked = document.getElementById('hargaUniversal').checked;
            var hargaPerJam = document.getElementById('hargaPerJam');
            var hargaWeekends = document.getElementById('hargaWeekends');
            var hargaWeekdays = document.getElementById('hargaWeekdays');
            var universalGroup = document.getElementById('universalGroup');
            var weekendsGroup = document.getElementById('weekendsGroup');
            var weekdaysGroup = document.getElementById('weekdaysGroup');
            var hargaLabel = document.getElementById('hargaLabel');

            if (isChecked) {
                universalGroup.classList.add('hidden');
                weekendsGroup.classList.remove('hidden');
                weekdaysGroup.classList.remove('hidden');
                hargaLabel.textContent = 'Harga Per Jam (Weekends & Weekdays)';

                hargaPerJam.removeAttribute('required');
                hargaWeekends.setAttribute('required', 'required');
                hargaWeekdays.setAttribute('required', 'required');
            } else {
                universalGroup.classList.remove('hidden');
                weekendsGroup.classList.add('hidden');
                weekdaysGroup.classList.add('hidden');
                hargaLabel.textContent = 'Harga Per Jam';

                hargaPerJam.setAttribute('required', 'required');
                hargaWeekends.removeAttribute('required');
                hargaWeekdays.removeAttribute('required');
            }
        }

        function updateTimeZone() {
            var selectedTimezone = document.getElementById('timezoneSelect').value;
            var current_timezone = '<?php echo $current_timezone; ?>'; // PHP variable containing the current timezone

            if (selectedTimezone !== current_timezone) {
                // Make AJAX request to update config.json
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'proses/update_timezone.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log('Timezone updated successfully.');
                    }
                };
                xhr.send('timezone=' + selectedTimezone);
                setTimeout(function() {location.reload();}, 100);
            }
        }

        function showWarning() {
            document.getElementById('warningText').style.display = 'block';
        }

        function syncInputs(inputValue) {
            $('.synced-input').val(inputValue);
        }

        var $form = $( "#priceForm" );
        var $input = $form.find( "input" );
        $input.on( "input", function( event ) {
            // 1. 
            var selection = window.getSelection().toString();
            if ( selection !== '' ) {
                return;
            }
            // 2. 
            if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 ) {
                return;
            }

            // 1 
            var $this = $( this );
            var input = $this.val();
            // 2 
            var input = input.replace(/[\D\s\._\-]+/g, "");
            
            // 3 
            input = input ? parseInt( input, 10 ) : 0;
            // 4 
            $this.val( function() {
                return ( input === 0 ) ? "" : input.toLocaleString( "en-US" ).replace(/\./g, '_').replace(/,/g, '.').replace(/_/g, ',');
            } );

            // Check if the button is toggled
            if ($('#toggleButton').hasClass('active')) {
                syncInputs($(this).val());
            }
            
        } );


        // JavaScript to toggle the 'no-hover' class
        $('#toggleButton').click(function() {
            $(this).toggleClass('toggle-effect');
        });

    </script>

</body>
</html>