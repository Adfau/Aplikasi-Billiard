<?php
// Check if the constant is defined, if not, redirect or display an error message
if (!defined('INCLUDED')) {
    header("HTTP/1.1 404 Not Found");
    exit; // Stop further execution
}
?>

<!--
<div id="sidebar-show"></div>
<div id="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <a href="index.php">Beranda</a>
    <a href="katalog.php">Katalog Buku</a>
    <a href="peminjaman.php">Peminjaman</a>
    <a href="anggota.php">Anggota</a>

     <div id="sidebarFooter">
        <p>&copy; 2024 - Aditya Prasetyo Yogatama</p>
    </div>
</div>
<script>
        //Sidebar
        $(document).ready(function(){
            // Sidebar toggle functionality
            $('.sidebar-toggle').hover(
                function() {
                    $('.sidebar').addClass('show'); // Show sidebar on hover
                },
                function() {
                    $('.sidebar').removeClass('show'); // Hide sidebar when not hovered
                }
            );
        });

        function openNav() {
        document.getElementById("sidebar").style.width = "250px";
        document.getElementById("main").style.marginLeft = "250px";
        }

        function closeNav() {
        document.getElementById("sidebar").style.width = "50px";
        document.getElementById("main").style.marginLeft = "0";
        document.getElementById("openNav").style.display = "block";
        }
</script>
-->