<?php
// Check if the constant is defined, if not, redirect or display an error message
if (!defined('INCLUDED')) {
    header("HTTP/1.1 404 Not Found");
    exit; // Stop further execution
}
?>

<div class="top-bar d-flex align-items-center justify-content-between px-3">

    <div class="d-flex align-items-center green-shadow-text fsbig">
        <!--
        <div class="d-flex align-items-center" style="padding-right: 20px; font-size: 25px;">
            <a onclick="openNav()"><i class="sidebar-toggle fa fa-bars"></i></a>
        </div>
        -->
        <!-- <a href="https://www.vecteezy.com/free-png/billiard">Billiard PNGs by Vecteezy</a> -->
        <a href="index"><div class="d-flex align-items-center">
            <div class="logo"></div>
            <span class="brand-name">Billiard</span>
        </div></a>
    </div>

    <div id="datetime" class="green-shadow-text">31/12/2020, 00:00:00</div>
    
    <!--Social Icons
    <div class="social-icons green-shadow-text d-flex align-items-center justify-content-between">
        <a href="https://www.facebook.com/" target="_blank"><i class="fa fa-facebook"></i></a>
        <a href="https://twitter.com/" target="_blank"><i class="fa fa-twitter"></i></a>
        <a href="https://www.instagram.com/" target="_blank"><i class="fa fa-instagram"></i></a>
        <a href="https://api.whatsapp.com/send?phone=6281234567890/" target="_blank"><i class="fa fa-whatsapp"></i></a>
    </div>
    -->

    <div class="dropdown fsbig">
        <a class="dropdown-toggle account-dropdown-toggle" href="#" id="accountDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-user-circle green-shadow-text"></i>
            <span class="ml-2 green-shadow-text fsbig"><?= isset($_SESSION['name']) ? "Hi, {$_SESSION['name']}" : "Profile" ?></span>    
        </a>
        <div class="dropdown-menu" aria-labelledby="accountDropdown">
            <?php if (isset($_SESSION['username'])) : ?>
                <?php if ($_SESSION['level'] == 'ADMIN') : ?>
                    <a class="dropdown-item" href="admin-dashboard">Administrasi</a>
                <?php endif ?>
                <a class="dropdown-item" href="proses/logout">Logout</a>
            <?php else : ?>
                <a class="dropdown-item" href="signin">Login</a>
            <?php endif ?>
        </div>
    </div>

</div>