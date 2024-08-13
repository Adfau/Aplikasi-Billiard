<?php

function fetchBillingData($con) {
    // Calculate the start of the current month
    $startOfMonth = date('Y-m-01');

    $query = "SELECT *, deleted_at AS timestamp FROM billing_history WHERE is_paid = 1 AND deleted_at >= ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $startOfMonth);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

function fetchFnbData($con) {
    // Calculate the start of the current month
    $startOfMonth = date('Y-m-01');

    $query = "SELECT * FROM fnb_orders WHERE timestamp >= ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $startOfMonth);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    return $rows;
}

// Fetch data and output as JSON
header('Content-Type: application/json');
require_once('../controller.php');

$billingData = fetchBillingData($con);
$fnbData = fetchFnbData($con);

echo json_encode([
    'billingData' => $billingData,
    'fnbData' => $fnbData,
]);
?>
