<?php
session_start();
$id_user = $_SESSION['id_user'];
session_destroy();
echo "Anda telah sukses keluar sistem <b>LOGOUT</b>";

require_once('../controller.php');
$deskripsiLog = "keluar dari aplikasi";
logActivity($con, $id_user, $deskripsiLog, "LOGOUT");

header( "Location: ../index.php?logout=success" );
exit();