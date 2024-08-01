<?php
session_start();
if (isset($_SESSION['username'])) {
    echo "Sudah login";
    header( "Location: ../index.php" );
    exit();
}
if (!isset($_POST['username'])) {
    echo "Username tidak ada";
    header( "Location: ../signin.php" );
    exit();
}


require_once('../controller.php');

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

$username = $_POST['username'];

$sql = "SELECT * FROM user WHERE username=?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$login = $stmt->get_result();
$ketemu = $login->num_rows;
$r = $login->fetch_array();
$stmt->close();

$password = $_POST['password'];

if ($ketemu > 0) {
    //Verifikasi
    if ($r['level'] == "ADMIN") {
        //Admin menggunakan enkripsi BCRYPT. Digunakan BCRYPT karena lebih aman dan tidak menggunakan key.
        if (!password_verify($password, $r['dgt000x2_pin']) && $r['dgt000x2_pin'] != NULL) { // Jika password admin kosong, maka masih valid
            echo "<center>Login gagal! password tidak benar (ADMIN)<br>";
            header( "Location: ../signin.php?status=fail" );
            exit();
        }
    } else {
        //Karyawan menggunakan enkripsi AES. Digunakan AES karena diperlukan key untuk store PIN karyawan pada menu admin.
        if (decrypt($r['dgt000x2_pin'], $key) != $password) {
            echo "<center>Login gagal! password tidak benar (USER)<br>";
            header( "Location: ../signin.php?status=fail" );
            exit();
        }
    }

    $_SESSION['id_user'] = $r['id_user'];
    $_SESSION['name'] = $r['nama_user'];
    $_SESSION['username'] = $r['username'];
    $_SESSION['level'] = $r['level'];

    echo "USER BERHASIL LOGIN<br>";
    echo "Username =", $_SESSION['username'], "<br>";
    echo "Level =", $_SESSION['level'], "<br>";

    $deskripsiLog = "masuk ke aplikasi";
    logActivity($con, $_SESSION['id_user'], $deskripsiLog, "LOGIN");

    if ($r['level'] == "ADMIN") {
        echo "<center>Masuk Ke Admin<br>";
        header( "Location: ../admin-dashboard.php" );
        exit();
    } else {
        echo "Masuk Ke Index";
        header( "Location: ../index.php" );
        exit();
    }
    
    exit();
} else {
    echo "<center>Login gagal! username tidak benar<br>";

    header( "Location: ../signin.php?status=fail" );
    exit();
}
?>