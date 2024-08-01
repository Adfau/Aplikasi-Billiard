<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

session_start();
// Pastikan level pengguna adalah ADMIN
if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
    header("Location: ../signin.php");
    exit();
}

if (isset($_POST['id_user'])) {
    $id_user = filter_input(INPUT_POST, 'id_user', FILTER_VALIDATE_INT);
    if ($id_user === false) {
        echo json_encode(["status" => "error", "message" => "Invalid id_user"]);
        header("Location: ../index.php");
        exit();
    }

    // KEY! PENTING!!! JANGAN DIUBAH
    $key = "4FE961AD538FE21CC27F519235834B12";

    // Decryption function
    function decrypt($data, $key) {
        $cipher = "aes-256-cbc";
        $data = base64_decode($data);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($data, 0, $ivlen);
        $data = substr($data, $ivlen);
        return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    }

    // Include the database connection
    header('Content-Type: application/json');
    require_once('../controller.php');

    // Fetch billing data for the given id_user
    $sql = "SELECT * FROM user WHERE id_user=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        $response = [
            "status" => "success",
            "data" => [
                "nama_user" => $data['nama_user'],
                "username" => $data['username'],
                "password" => decrypt($data['dgt000x2_pin'], $key),
                "catatan" => $data['catatan'],
                "id_user" => $data['id_user']
            ]
        ];
        echo json_encode($response);
    } else {
        // Redirect to an error page or back to the main page
        echo json_encode(["status" => "error", "message" => "No data found for the given id_user"]);
        echo "Get Data Fail";
    }
} else {
    // Redirect to the main page if no id is set
    echo json_encode(["status" => "error", "message" => "id_user not set"]);
    exit();
}
exit();
?>
