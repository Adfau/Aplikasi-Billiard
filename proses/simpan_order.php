<?php
session_start();

if (isset($_GET['id']) && isset($_POST['namaPenyewa'])) {
    // Validasi
    $no_meja = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    require_once('../controller.php');
    $jumlah_meja = $GLOBALS['$jumlah_meja'];
    if ($no_meja === false || $no_meja === NULL || $no_meja > $jumlah_meja || $no_meja <= 0) {
        echo "No Meja salah";
        header("Location: ../index.php");
        exit();
    }
    if ($jumlah_meja <= 0) {
        echo "Tidak ada meja";
        header("Location: ../index.php");
        exit();
    }

    // Capture posted data
    $namaPenyewa = filter_input(INPUT_POST, 'namaPenyewa', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

    $noMeja = filter_input(INPUT_POST, 'transferMeja', FILTER_VALIDATE_INT);
    if ($noMeja === false || $noMeja === NULL || $noMeja > $jumlah_meja || $noMeja <= 0) {
        echo "Input No Meja salah";
        $noMeja = $no_meja;

        // header("Location: ../index.php");
        // exit();
    }

    // Initialize billing_id
    $billing_id = "";

    // Fetch billing data for the given no_meja
    $sql = "SELECT * FROM billing WHERE no_meja=?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $no_meja);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $billing_id = $data['id'];
        $nama_penyewa = $data['nama_penyewa'];

        // Cek apakah meja tersedia
        if ($noMeja !== $no_meja) {
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $noMeja);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                echo "Target meja sudah dibilling";
                header("Location: ../index.php");
                exit();
            }
        }

        if (true) {
            // Sql Here
            

            // Execute the statement
            if ($stmt === false) {
                header("Location: ../index.php");
                die("Error preparing statement: " . $con->error);
            }

            if ($stmt->execute() === false) {
                header("Location: ../index.php");
                die("Error executing statement: " . $stmt->error);
            }

            $stmt->close();
            echo "Edit Success";
        }

        // Redirect to a success page or back to the main page
        header("Location: ../index.php");
    } else {
        // Redirect to an error page or back to the main page
        echo "Edit Fail";
        header("Location: ../index.php");
    }
} else {
    // Redirect to the main page if no id is set
    header("Location: ../index.php");
    exit();
}
exit();
?>
