<?php
session_start();

if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header( "Location: signin.php" );
    exit();
  }

require_once('controller.php');
define('INCLUDED', true);
define('PAGE_LOG', true);

// Pages
// Get total number of logs
$sql = "SELECT COUNT(*) as count FROM `activity_log`";
$result = $con->query($sql);
$row = $result->fetch_assoc();
$totalLogs = $row['count'];

// Set number of logs per page
$logsPerPage = 10;

// Calculate total pages
$totalPages = ceil($totalLogs / $logsPerPage);

// Get current page from URL parameter, default to 1 if not set
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Generate pagination links
function generatePageLinks($totalPages, $currentPage, $search, $start_date, $end_date) {
    $links = '';
    $params = [];

    if (!empty($search)) {
        $params['search'] = $search;
    }
    if (!empty($start_date)) {
        $params['start_date'] = $start_date;
    }
    if (!empty($end_date)) {
        $params['end_date'] = $end_date;
    }

    // Number of page links to show before and after ellipsis
    $numLinksBeforeEllipsis = 3;
    $numLinksAfterEllipsis = 3;

    // First page link
    $params['page'] = 1;
    $queryString = http_build_query($params);
    $links .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '"><a class="page-link" href="?' . $queryString . '"><<</a></li>';

    // Previous page link
    $params['page'] = max(1, $currentPage - 1);
    $queryString = http_build_query($params);
    $links .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '"><a class="page-link" href="?' . $queryString . '"><</a></li>';

    // Calculate start and end page numbers
    $startPage = max(1, $currentPage - $numLinksBeforeEllipsis);
    $endPage = min($totalPages, $currentPage + $numLinksAfterEllipsis);

    // Page numbers before ellipsis
    for ($i = 1; $i <= min($numLinksBeforeEllipsis, $startPage - 1); $i++) {
        $params['page'] = $i;
        $queryString = http_build_query($params);
        $links .= '<li class="page-item"><a class="page-link" href="?' . $queryString . '">' . $i . '</a></li>';
    }

    // Ellipsis if there are more pages before the startPage
    if ($startPage - 1 > $numLinksBeforeEllipsis) {
        $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Page numbers between startPage and endPage
    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $params['page'] = $i;
        $queryString = http_build_query($params);
        $links .= '<li class="page-item ' . $active . '"><a class="page-link" href="?' . $queryString . '">' . $i . '</a></li>';
    }

    // Ellipsis if there are more pages after the endPage
    if ($totalPages - $endPage > $numLinksAfterEllipsis) {
        $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Page numbers after ellipsis
    for ($i = max($totalPages - $numLinksAfterEllipsis + 1, $endPage + 1); $i <= $totalPages; $i++) {
        $params['page'] = $i;
        $queryString = http_build_query($params);
        $links .= '<li class="page-item"><a class="page-link" href="?' . $queryString . '">' . $i . '</a></li>';
    }

    // Next page link
    $params['page'] = min($totalPages, $currentPage + 1);
    $queryString = http_build_query($params);
    $links .= '<li class="page-item ' . ($currentPage == $totalPages ? 'disabled' : '') . '"><a class="page-link" href="?' . $queryString . '">></a></li>';

    // Last page link
    $params['page'] = $totalPages;
    $queryString = http_build_query($params);
    $links .= '<li class="page-item ' . ($currentPage == $totalPages ? 'disabled' : '') . '"><a class="page-link" href="?' . $queryString . '">>></a></li>';

    return $links;
}


// Search
$sql = "SELECT activity_log.*, user.username FROM `activity_log` 
        JOIN `user` ON activity_log.id_user = user.id_user";
$revealDetails = false;

$search = isset($_GET['search']) ? $_GET['search'] : "";
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : "";
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : "";

$conditions = [];
$params = [];
$types = "";

// Filter Search
if (!empty($search)) {
    $conditions[] = "((deskripsi LIKE ?) OR (user.username LIKE ?))";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
    $revealDetails = true;
}

// Filter Rentang Waktu
if (!empty($start_date) && !empty($end_date)) {
    $conditions[] = "(timestamp BETWEEN ? AND ?)";
    $params[] = $start_date . " 00:00:00";
    $params[] = $end_date . " 23:59:59";
    $types .= "ss";
} elseif (!empty($start_date)) {
    $conditions[] = "(timestamp >= ?)";
    $params[] = $start_date . " 00:00:00";
    $types .= "s";
} elseif (!empty($end_date)) {
    $conditions[] = "(timestamp <= ?)";
    $params[] = $end_date . " 23:59:59";
    $types .= "s";
}

// $filters = [];
$filters = isset($_SESSION['filters']) ? $_SESSION['filters'] : [];

// Filter Buttons
if (isset($_POST['filters'])) {
    // Sanitize and prepare the filters
    $filters = $_POST['filters'];

    // Check if "MISC" is present
    if (count($filters) === 1 && $filters[0] === 'MISC') {
        // If "MISC" is the only filter, set filters to an empty array
        $filters = [];
    } else {
        // Otherwise, remove "MISC" if it's present
        $filters = array_filter($filters, function($filter) {
            return $filter !== 'MISC';
        });

        // Escape and quote the remaining filters
        $filters = array_map(function($filter) use ($con) {
            return "'" . $con->real_escape_string($filter) . "'";
        }, $filters);

        $conditions[] = "(type IN (" . implode(',', $filters) . "))";
    }

    $_SESSION['filters'] = $filters;

} elseif (!empty($filters)) {
    $conditions[] = "(type IN (" . implode(',', $filters) . "))";
}

// Tambahkan filter
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);

    if (!empty($types)) {
        $countStmt = $con->prepare($sql);
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_all(MYSQLI_ASSOC);
        $countStmt->close();
    } else {
        $countResult = $con->query($sql);
        $countRow = $countResult->fetch_all(MYSQLI_ASSOC);
    }

    $totalLogs = count($countRow);
    $totalPages = ceil($totalLogs / $logsPerPage);
}

// Pastikan agar parameter page tidak melebihi end page
if ($currentPage > $totalPages && $currentPage != 1) {
    $currentPage = $totalPages;
}
// Calculate the offset for the SQL query
$offset = max(0, ($currentPage - 1) * $logsPerPage);

// Pisahkan berdasarkan page
$sql .= " ORDER BY timestamp DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $logsPerPage;
$types .= "ii";

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// User and type icons
$icons = [
    'CREATE' => '<i class="fa fa-user-plus" aria-hidden="true" style="color: green;"></i>',
    'READ' => '<i class="fa fa-search" aria-hidden="true" style="color: blue;"></i>',
    'UPDATE' => '<i class="fa fa-pencil" aria-hidden="true" style="color: orange;"></i>',
    'DELETE' => '<i class="fa fa-user-times" aria-hidden="true" style="color: red;"></i>',
    'LOGIN' => '<i class="fa fa-sign-in" aria-hidden="true" style="color: green;"></i>',
    'LOGOUT' => '<i class="fa fa-sign-out" aria-hidden="true" style="color: red;"></i>',
    'MISC' => '<i class="fa fa-folder" aria-hidden="true" style="color: black;"></i>'
];

// Function to get the username
function getUsername($con, $id_user) {
    $sql = "SELECT username FROM user WHERE id_user = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
    return $username;
}

$ccount = 0;

?>


<!DOCTYPE html>
<html lang="en">
<?php include("head.php"); ?>
<head>
    <style>
        .log-icon {
            font-size: 1.5em;
            margin-right: 10px;
        }
        .log-io {
            cursor: pointer;
        }
        .log-io:hover {
            cursor: pointer;
            background-color: #0D6EFD0D;
        }

        .filter-tag {
            color: green;
            border-color: green;
            background-color: transparent;
            cursor: pointer;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .filter-tag.active {
            color: #0D6EFD;
            border-color: #0D6EFD;
            background-color: #0D6EFD0D;
        }

        .filter-tag:not(.active) {
            color: grey;
            border-color: grey;
        }

        .filter-tag:hover {
            opacity: 0.6;
        }

        .filter-checkbox {
            position: absolute; /* Position checkboxes absolutely */
            opacity: 0; /* Make checkboxes invisible */
        }

        .icon-indicator {
            position: absolute;
            left: -18px;
            top: 20px;
            transition: transform 0.3s ease;
            opacity: 0.7;
        }

        .icon-indicator.open {
            transform: rotate(90deg);
        }

        .hidden-details {
            display: none;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            max-height: 0;
        }

        .hidden-details.show {
            display: block;
            max-height: 1000px; /* Arbitrary large value to ensure it expands */
        }

        #toggle-all-details i {
            transition: transform 0.3s ease;
        }

        #toggle-all-details.collapse i {
            transform: rotate(180deg);
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
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <h1 class="text-left">Log Aktivitas</h1>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <!-- Search form -->
                                <form class="form-inline d-flex" id="search-form" method="GET" action="">
                                    <input class="form-control mr-sm-2" type="search" name="search" placeholder="Cari nama atau detail..." aria-label="Search" id="search-input" value="<?php echo htmlspecialchars($search) ?>">
                                    <?php if (isset($_GET['start_date']) && isset($_GET['end_date'])): ?>
                                        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date) ?>">
                                        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date) ?>">
                                    <?php endif; ?>
                                    <?php if (isset($_GET['page'])): ?>
                                        <input type="hidden" name="page" value="<?php echo htmlspecialchars($currentPage) ?>">
                                    <?php endif; ?>
                                    <button class="btn btn-outline-primary my-2 my-sm-0" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
                                </form>
                                <!-- Date range button -->
                                <button class="btn btn-outline-primary" id="date-range-button" data-toggle="modal" data-target="#dateRangeModal"><i class="fa fa-calendar" aria-hidden="true"></i> Rentang Waktu</button>
                            </div>
                            
                            <div class="btn-group-toggle" data-toggle="buttons">
                                <!-- Filter tags -->
                                <?php
                                // Define the filter tags and their corresponding icons
                                $filterTags = [
                                    'CREATE' => '<i class="fa fa-user-plus" aria-hidden="true"></i> Tambah',
                                    'UPDATE' => '<i class="fa fa-pencil" aria-hidden="true"></i> Edit',
                                    'DELETE' => '<i class="fa fa-user-times" aria-hidden="true"></i> Hapus',
                                    'LOGIN' => '<i class="fa fa-sign-in" aria-hidden="true"></i> Login',
                                    'LOGOUT' => '<i class="fa fa-sign-out" aria-hidden="true"></i> Logout'
                                ];

                                // Loop through the filter tags
                                foreach ($filterTags as $filter => $icon) {
                                    // Check if the filter is present in the session
                                    $filCheck = "'$filter'";
                                    $isChecked = in_array($filCheck, $filters);
                                    ?>
                                    <label class="btn filter-tag">
                                        <input type="checkbox" class="filter-checkbox" autocomplete="off" data-filter="<?php echo $filter ?>" <?php if ($isChecked) echo 'checked' ?>>
                                        <?php echo $icon ?>
                                    </label>
                                <?php } ?>
                                    <label class="hidden filter-tag">
                                        <input type="checkbox" class="hidden" checked data-filter="MISC">
                                    </label>
                            </div>

                            <!-- Expand Collapse -->
                            <div class="text-muted mb-2">
                                <button id="toggle-all-details" class="btn btn-sm<?php $display = ($revealDetails) ? " toggleCollapse" : ""; echo $display ?>">
                                <?php if ($revealDetails) : ?>
                                    <i class="fa fa-minus-square-o" aria-hidden="true"></i> collapse all
                                <?php else : ?>
                                    <i class="fa fa-plus-square-o" aria-hidden="true"></i> expand all
                                <?php endif ?>
                                </button>
                            </div>

                            <!-- Log Aktivitas -->
                            <div class="list-group" id="log-activity">
                                <?php foreach ($logs as $log): ?>
                                    <?php $lines = explode("\n", $log['deskripsi']); // Split deskripsi into lines ?>
                                    <div class="list-group-item log-entry">
                                        <div class="row align-items-center<?php if(count($lines) > 1) echo " log-io"?>">

                                            <?php
                                                $display = ($revealDetails) ? " open" : "";
                                            
                                                if (count($lines) > 1) {
                                                    echo '<div class="col-auto icon-indicator'.$display.'">';
                                                    echo '<i class="fa fa-chevron-right"></i>';
                                                    echo '</div>';
                                                }
                                            ?>

                                            <div class="col-auto" style="width: 35px;">
                                                <span class="log-icon"><?php echo $icons[$log['type']] ?></span>
                                            </div>
                                            <div class="col">
                                                <div>
                                                    <strong><?php echo htmlspecialchars(getUsername($con, $log['id_user'])) ?></strong> <!-- Username -->
                                                    <?php echo htmlspecialchars($lines[0]); ?>
                                                </div>
                                                <div class="text-muted"> <!-- Timestamp -->
                                                    <?php echo $log['timestamp'] ?>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="log-details">
                                            <?php
                                                // If there are more lines, create a hidden div to contain them
                                                if (count($lines) > 1) {
                                                    $display = ($revealDetails) ? " show" : "";
                                                    echo '<div class="hidden-details'.$display.'">';
                                                    // Display the remaining lines
                                                    for ($i = 1; $i < count($lines); $i++) {
                                                        echo '<div>' . htmlspecialchars($lines[$i]) . '</div>';
                                                    }
                                                    echo '</div>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <!-- <?php echo '<pre>'; print_r($filters); echo '</pre><br>'; ?> -->
                                <!-- Pagination Links -->
                                <div style="margin-bottom: 10px;"></div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php echo generatePageLinks($totalPages, $currentPage, $search, $start_date, $end_date) ?>
                                    </ul>
                                </nav>
                            </div>
                                            
                        </div>
                    </div>
                </div>
                <div style="margin-left: 40px;"></div>
            </div>
        </div>
    </div>

    <!-- Date Range Modal -->
    <div class="modal fade" id="dateRangeModal" tabindex="-1" role="dialog" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dateRangeModalLabel">Pilih Rentang Waktu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="justify-content-between d-flex">
                        <div>
                        <button type="button" class="btn btn-outline-primary date-range-btn" data-range="today">Hari Ini</button>
                        <button type="button" class="btn btn-outline-primary date-range-btn" data-range="last7days">7 Hari Sebelumnya</button>
                        </div>
                        <button type="button" class="btn btn-outline-danger date-range-btn float-right" data-range="clear">Clear</button>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="start-date">Tanggal Awal</label>
                        <input type="date" id="start-date" class="form-control"<?php if (isset($_GET['start_date'])) echo ' value="'.$start_date.'"' ?>>
                    </div>
                    <div class="form-group">
                        <label for="end-date">Tanggal Akhir</label>
                        <input type="date" id="end-date" class="form-control"<?php if (isset($_GET['end_date'])) echo ' value="'.$end_date.'"' ?>>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="apply-date-range">Apply</button>
                </div>
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

        function updateLogActivity() {
            var selectedFilters = $('.filter-tag input[type="checkbox"]:checked').map(function() {
                return $(this).data('filter');
            }).get();

            var url = 'admin-log.php' + window.location.search;
            // Send selected filters to backend via AJAX
            $.ajax({
                url: url,
                type: 'POST',
                data: { filters: selectedFilters },
                success: function(response) {
                    // Update log activity list with filtered data
                    $('#log-activity').html($(response).find('#log-activity').html());
                }
            });
        }
        
        function toggleAllDetails(action) {
            var $allDetails = $('.hidden-details');
            var $allIcons = $('.icon-indicator');

            if (action === 'collapse') {
                $allDetails.removeClass('show');
                $allIcons.removeClass('open');

                $('#toggle-all-details').html('<i class="fa fa-plus-square-o" aria-hidden="true"></i> expand all');
                $('#toggle-all-details').removeClass('toggleCollapse');
            } else {
                $allDetails.addClass('show');
                $allIcons.addClass('open');

                $('#toggle-all-details').html('<i class="fa fa-minus-square-o" aria-hidden="true"></i> collapse all');
                $('#toggle-all-details').addClass('toggleCollapse');
            }
        }

        $(document).ready(function() {
            // if ($('.filter-tag input[type="checkbox"]:checked').length > 0) {
            //     updateLogActivity();
            // }

            // Use event delegation to handle click events for dynamically added .log-entry elements
            // $(document).on('click', '.log-entry', function() {
            //     $(this).find('.hidden-details').toggle();
            // });
            $(document).on('click', '.log-io', function() {
                var $logEntry = $(this).closest('.log-entry');
                var $hiddenDetails = $logEntry.find('.hidden-details');
                var $iconIndicator = $logEntry.find('.icon-indicator');

                if ($hiddenDetails.hasClass('show')) {
                    $hiddenDetails.removeClass('show');
                    $iconIndicator.removeClass('open');
                } else {
                    $hiddenDetails.addClass('show');
                    $iconIndicator.addClass('open');
                }
            });

            // Toggle all details on button click
            $('#toggle-all-details').on('click', function() {
                if ($(this).hasClass('toggleCollapse')) {
                    toggleAllDetails('collapse');
                } else {
                    toggleAllDetails('uncollapse');
                }
            });
        });

        $('.filter-tag input[type="checkbox"]').change(function() {
            updateLogActivity();
        });

        // Handle date range buttons click inside the modal
        $('.date-range-btn').click(function() {
            var range = $(this).data('range');
            var currentDate = new Date();
            var startDate = new Date();
            var endDate = new Date();

            if (range === 'clear') {
                $('#start-date').val("");
                $('#end-date').val("");
            } else {
                if (range === 'today') {
                    startDate = currentDate;
                    endDate = currentDate;
                } else if (range === 'last7days') {
                    startDate.setDate(currentDate.getDate() - 7);
                    endDate = currentDate;
                }

                $('#start-date').val(startDate.toISOString().split('T')[0]);
                $('#end-date').val(endDate.toISOString().split('T')[0]);
            }
        });

        // Handle custom date range apply button click
        $('#apply-date-range').click(function() {
            var startDate = $('#start-date').val();
            var endDate = $('#end-date').val();

            // Update URL with date range parameters
            var urlParams = new URLSearchParams(window.location.search);
            urlParams.set('start_date', startDate);
            urlParams.set('end_date', endDate);

            window.location.search = urlParams.toString();
        });
    </script>
    

</body>
</html>