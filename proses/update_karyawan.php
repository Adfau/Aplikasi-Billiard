<?php
session_start();
$key = "4FE961AD538FE21CC27F519235834B12";
// Pastikan permintaan datang dari metode POST atau GET
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
    // Pastikan level pengguna adalah ADMIN
    if (!isset($_SESSION['level']) || $_SESSION['level'] != "ADMIN") {
        header("Location: ../signin.php");
        exit();
    }

    // Sisipkan file koneksi
    require_once('../controller.php');

    // Tangkap id_user dari URL jika ada
    $id_user = $_GET['id']; // Assuming 'id' is the parameter name in the URL

    // Tangkap data dari formulir jika metode adalah POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama = $_POST['nama'];
        $username = $_POST['username'];
        $pin = $_POST['password'];
        $note = $_POST['note'];

        // Enkripsi PIN (jika perlu)
        function encrypt($data, $key) {
            $cipher = "aes-256-cbc";
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
            return base64_encode($iv . $encrypted);
        }

        // Jika password tidak diubah, gunakan yang lama
        if (empty($pin)) {
            // Ambil password dari database
            $stmt = $con->prepare("SELECT dgt000x2_pin FROM user WHERE id_user = ?");
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $stmt->bind_result($encrypted_pin);
            $stmt->fetch();
            $stmt->close();
        } else {
            // Enkripsi password baru
            $encrypted_pin = encrypt($pin, $key);
        }

        // Query SQL untuk menyimpan perubahan data ke dalam tabel user
        $sql = "UPDATE user SET nama_user = ?, username = ?, dgt000x2_pin = ?, catatan = ? WHERE id_user = ?";

        // Persiapkan pernyataan SQL
        $stmt = $con->prepare($sql);

        // Bind parameter ke pernyataan SQL
        $stmt->bind_param("ssssi", $nama, $username, $encrypted_pin, $note, $id_user);

        // Eksekusi pernyataan SQL
        if ($stmt->execute()) {
            header("Location: ../admin-account.php");
            $deskripsiLog = "memperbarui Karyawan $username";
            logActivity($con, $_SESSION['id_user'], $deskripsiLog, "UPDATE");
        } else {
            echo "Gagal memperbarui data pengguna: " . $stmt->error;
        }

        // Tutup pernyataan
        $stmt->close();
    }
    
    // Tutup koneksi
    mysqli_close($con);
} else {
    // Jika permintaan tidak berasal dari metode POST atau GET, arahkan kembali ke halaman sebelumnya
    header("Location: ../admin-account.php");
}
?>
