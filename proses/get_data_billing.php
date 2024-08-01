<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

if (isset($_POST['no_meja'])) {
    $no_meja = filter_input(INPUT_POST, 'no_meja', FILTER_VALIDATE_INT);
    if ($no_meja === false) {
        echo json_encode(["status" => "error", "message" => "Invalid no_meja"]);
        header("Location: ../index.php");
        exit();
    }

    // Include the database connection
    header('Content-Type: application/json');
    require_once('../controller.php');

    // Fetch billing data for the given no_meja
    $sql = "SELECT * FROM billing WHERE no_meja=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $no_meja);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        $response = [
            "status" => "success",
            "data" => [
                "nama_penyewa" => $data['nama_penyewa'],
                "waktu_mulai" => $data['waktu_mulai'],
                "waktu_selesai" => $data['waktu_selesai'],
                "durasi" => $data['durasi'],
                "harga" => $data['harga'],
                "no_meja" => $data['no_meja']
            ]
        ];
        echo json_encode($response);
    } else {
        // Redirect to an error page or back to the main page
        echo json_encode(["status" => "error", "message" => "No data found for the given no_meja"]);
        echo "Get Data Fail";
    }
} else {
    // Redirect to the main page if no id is set
    echo json_encode(["status" => "error", "message" => "no_meja not set"]);
    exit();
}
exit();
?>
