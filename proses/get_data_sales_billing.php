<?php
// if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
//     header("HTTP/1.1 404 Not Found");
//     exit();
// }

header('Content-Type: application/json');
require_once('../controller.php');

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'billing_id';
$order = $_GET['order'] ?? 'ASC';
$start_date = $_GET['startdate'] ?? '';
$end_date = $_GET['enddate'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(nama_penyewa LIKE ?)";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if (!empty($start_date) && !empty($end_date)) {
    $conditions[] = "(deleted_at BETWEEN ? AND ?)";
    $params[] = $start_date . " 00:00:00";
    $params[] = $end_date . " 23:59:59";
    $types .= 'ss';
} elseif (!empty($start_date)) {
    $conditions[] = "(deleted_at >= ?)";
    $params[] = $start_date . " 00:00:00";
    $types .= 's';
} elseif (!empty($end_date)) {
    $conditions[] = "(deleted_at <= ?)";
    $params[] = $end_date . " 23:59:59";
    $types .= 's';
}

$where = $conditions ? ' AND ' . implode(' AND ', $conditions) : '';

// Count total records
$sql_count = "SELECT COUNT(*) FROM billing_history WHERE is_paid = 1 $where";
$stmt_count = $con->prepare($sql_count);
if ($types) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$stmt_count->bind_result($total);
$stmt_count->fetch();
$stmt_count->close();

// Fetch filtered data
$sql = "SELECT * FROM billing_history WHERE is_paid = 1 $where ORDER BY $sort $order";
if ($limit !== null) {
    $sql .= " LIMIT ? OFFSET ?";
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
}
$stmt = $con->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// $sql_totals = "SELECT SUM(jumlah_fnb) AS totalJumlah, SUM(total_fnb) AS totalHarga FROM fnb_orders";
// $stmt_totals = $con->prepare($sql_totals);
// $stmt_totals->execute();
// $stmt_totals->bind_result($totalJumlah, $totalHarga);
// $stmt_totals->fetch();
// $stmt_totals->close();

$response = [
    "total" => $total,
    // "totalDurasi" => $totalDurasi,
    // "totalHarga" => $totalHarga,
    "rows" => $data
];

echo json_encode($response);
?>