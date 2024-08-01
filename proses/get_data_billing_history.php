<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

if (isset($_POST['billing_id'])) {
    $billing_id = filter_input(INPUT_POST, 'billing_id', FILTER_VALIDATE_INT);
    if ($billing_id === false) {
        echo json_encode(["status" => "error", "message" => "Invalid billing_id"]);
        header("Location: ../index.php");
        exit();
    }

    // Include the database connection
    header('Content-Type: application/json');
    require_once('../controller.php');

    // Fetch billing data for the given billing_id
    $sql = "SELECT * FROM billing_history WHERE billing_id=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $billing_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $harga_perjam = getHarga();
        $response = [
            "status" => "success",
            "data" => [
                "nama_penyewa" => $data['nama_penyewa'],
                "waktu_mulai" => $data['waktu_mulai'],
                "waktu_selesai" => $data['waktu_selesai'],
                "durasi" => $data['durasi'],
                "harga" => $data['harga'],
                "billing_id" => $data['billing_id'],
                "no_meja" => $data['no_meja'],
                "harga_perjam" => $harga_perjam
            ]
        ];
        echo json_encode($response);
    } else {
        // Redirect to an error page or back to the main page
        echo json_encode(["status" => "error", "message" => "No data found for the given billing_id"]);
        echo "Get Data Fail";
    }
} else {
    // Redirect to the main page if no id is set
    echo json_encode(["status" => "error", "message" => "billing_id not set"]);
    exit();
}
exit();
?>
