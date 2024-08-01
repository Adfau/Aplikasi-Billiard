<?php
session_start();
unset($_SESSION['scrollPosition']);
session_destroy();
header( "Location: ../index.php" );
exit();
?>