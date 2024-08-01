<?php
$scrollPosition = $_POST['scrollPosition'];

session_start();
$_SESSION['scrollPosition'] = $scrollPosition;

/*Taruh di script index.php jika ingin posisi scroll balik ke semula setelah menambah/hapus data.
        window.addEventListener('beforeunload', function(event) {
            // Get the current scroll position
            var scrollPosition = window.scrollY || window.pageYOffset;

            // Send the scroll position to the server via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "proses/scroll_position.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("scrollPosition=" + scrollPosition);
        });

        document.addEventListener('DOMContentLoaded', function() {
            var scrollPosition = parseInt(<?php echo $_SESSION['scrollPosition']?>);
            if (!isNaN(scrollPosition)) {
                window.scrollTo({
                    top: scrollPosition,
                    left: 0,
                    behavior: 'instant',
                });
            }
        });
*/
?>