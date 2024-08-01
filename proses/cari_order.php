<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

require_once('../controller.php');

$id_billing = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if($id_billing != FALSE) {

    $sql = "SELECT * FROM fnb_orders WHERE id_billing = ? ORDER BY id_order";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $id_billing);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    echo json_encode($products);

}

exit();
?>
