<?php
session_start();
$key = "4FE961AD538FE21CC27F519235834B12";
// Pastikan permintaan datang dari metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pastikan level pengguna adalah ADMIN
    if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
        header("Location: ../signin.php");
        exit();
    }

    // Sisipkan file koneksi
    require_once('../controller.php');

    // Tangkap data dari formulir
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $pin = $_POST['password'];
    $note = $_POST['note'];

    // Enkripsi PIN
    function encrypt($data, $key) {
        $cipher = "aes-256-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }
    $encrypted_pin = encrypt($pin, $key);

    // Query SQL untuk menyimpan data ke dalam tabel user
    $sql = "INSERT INTO user (nama_user, username, dgt000x2_pin, catatan) VALUES (?, ?, ?, ?)";

    // Persiapkan pernyataan SQL
    $stmt = $con->prepare($sql);

    // Bind parameter ke pernyataan SQL
    $stmt->bind_param("ssss", $nama, $username, $encrypted_pin, $note);

    // Eksekusi pernyataan SQL
    if ($stmt->execute()) {
        header("Location: ../admin-account.php");
        $deskripsiLog = "menambah Karyawan $username";
        logActivity($con, $_SESSION['id_user'], $deskripsiLog, "CREATE");
    } else {
        echo "Gagal menyimpan data pengguna: " . $stmt->error;
    }

    // Tutup pernyataan dan koneksi
    $stmt->close();
    mysqli_close($con);
} else {
    // Jika permintaan tidak berasal dari metode POST, arahkan kembali ke halaman sebelumnya
    header("Location: ../admin-account.php");
}

?>
