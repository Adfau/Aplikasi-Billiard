<?php
require_once('../fpdf/fpdf.php');
require_once('../controller.php');
session_start();

if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$billing_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($billing_id === false) {
    header("Location: ../index.php");
    exit();
}

// Helper Functions
function getFirstWord($text) {
    $words = explode(' ', $text);
    return $words[0];
}
function truncateText($text, $length) {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length - 2) . '..';
    }
    return $text;
}
function formatNumberTo9Digits($number) {
    // Convert number to string
    $numberStr = strval($number);
    
    // Calculate number of leading zeros needed
    $leadingZeros = str_repeat('0', 9 - strlen($numberStr));
    
    // Concatenate leading zeros with the number
    return $leadingZeros . $numberStr;
}
function removeTimeFromDate($datetime) {
    // Split datetime string into date and time parts
    $parts = explode(' ', $datetime);
    
    // Return only the date part (first part)
    return $parts[0];
}
function formatToRupiah($number) {
    return number_format($number,0,",",".");
}
function revertFormat($formattedRupiah) {
    // Remove thousand separators (`,`) and decimal separators (`.`)
    $unformatted = str_replace(['.', ','], '', $formattedRupiah);
    
    // Convert back to integer
    return (int) $unformatted;
}
function customMultiplication($n) { // 6 6 6 6 5
    $result = 0;
    for ($i = 1; $i <= $n; $i++) {
        if ($i % 10 == 0) {
            $result += 5;
        } else {
            $result += 6;
        }
    }
    return $result;
}

// Fetch Billing Data
$query = "SELECT * FROM billing_history WHERE billing_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $billing_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$currentDay = date('w'); // 0 (for Sunday) through 6 (for Saturday)
$billingDataPaket = "Weekday";
if ($currentDay == 0 || $currentDay == 6) { // Sunday or Saturday
    $billingDataPaket = "Weekend";
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $data = [
        'Tanggal' => removeTimeFromDate($row['waktu_mulai']),
        'No. Nota' => formatNumberTo9Digits($row['billing_id']),
        'Kasir' => $_SESSION['name'],
        'Nama Tamu' => $row['nama_penyewa'],
        'No Meja' => $row['no_meja'],
        'Mulai' => $row['waktu_mulai'],
        'Selesai' => $row['waktu_selesai'],
        'Durasi' => $row['durasi'],
        'Harga/Jam ('. $billingDataPaket .')' => formatToRupiah(getHarga()),
        'Total Billing' => formatToRupiah($row['harga'])
    ];
} else {
    header("Location: ../index.php");
    exit();
}

// Fetch F&B Orders
$items = [];
$query = "SELECT nama_fnb AS item, harga_fnb AS harga, jumlah_fnb AS qty, total_fnb AS jumlah FROM fnb_orders WHERE id_billing = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $billing_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

while ($row = $result->fetch_assoc()) {
    $items[] = [
        'item' => $row['item'],
        'harga' => formatToRupiah($row['harga']),
        'qty' => $row['qty'],
        'jumlah' => formatToRupiah($row['jumlah'])
    ];
}

// Calculate Totals
$total_fnb = array_sum(array_map(function($item) {
    return revertFormat($item['jumlah']);
}, $items));
$tunai = revertFormat($data['Total Billing']) + $total_fnb;
$totals = [
    'Grand Total' => formatToRupiah(revertFormat($data['Total Billing']) + $total_fnb),
    'Diskon' => '0',
    'Tunai' => formatToRupiah($tunai),
    'Kembalian' => formatToRupiah($tunai - (revertFormat($data['Total Billing']) + $total_fnb))
];

// Calculate PDF height
$base_height = 136;
if (!empty($items)) {
    $base_height = 159;
    $item_count = count($items);
    // $pdf_height = $base_height + (6 * ($item_count - 1));
    $pdf_height = $base_height + customMultiplication($item_count - 1);
} else {
    $pdf_height = $base_height;
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Coups de billiard', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, 'BILLIARD SPORT', 0, 1, 'C');
    }

    function Footer() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, '-- TERIMA KASIH --', 0, 1, 'C');
    }

    function TransactionInfo($data)
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, '-----------------------------------------------------------', 0, 1, 'C');

        foreach ($data as $key => $value) {
            $this->Cell(30, 6, $key, 0, 0, 'L');
            $this->Cell(0, 6, $value, 0, 1, 'R');
        }

        $this->Cell(0, 6, '-----------------------------------------------------------', 0, 1, 'C');
    }

    function FnbInfo($items)
    {
        $this->SetFont('Arial', '', 10);
        $this->Cell(40, 6, 'F&B', 0, 0, 'L');
        $this->Cell(10, 6, 'Harga', 0, 0, 'R');
        $this->Cell(6, 6, 'Kts', 0, 0, 'R');
        $this->Cell(0, 6, 'Total', 0, 1, 'R');
        
        $total_fnb = 0;
        foreach ($items as $item) {
            $this->Cell(40, 6, truncateText($item['item'], 15), 0, 0, 'L');
            $this->Cell(10, 6, $item['harga'], 0, 0, 'R');
            $this->Cell(6, 6, $item['qty'], 0, 0, 'R');
            $this->Cell(0, 6, $item['jumlah'], 0, 1, 'R');
            $total_fnb += revertFormat($item['jumlah']);
        }

        $this->Cell(30, 6, 'Total F&B', 0, 0, 'L');
        $this->Cell(0, 6, formatToRupiah($total_fnb), 0, 1, 'R');
        $this->Cell(0, 6, '-----------------------------------------------------------', 0, 1, 'C');
    }

    function TotalInfo($totals)
    {
        $this->SetFont('Arial', '', 10);
        foreach ($totals as $key => $value) {
            $this->Cell(30, 6, $key, 0, 0, 'L');
            $this->Cell(0, 6, $value, 0, 1, 'R');
        }

        $this->Cell(0, 6, '-----------------------------------------------------------', 0, 1, 'C');
    }
}

// Height Size. FnB: 159 base, +6 per item. Non FnB: 136
$pdf = new PDF('P', 'mm', array(72, $pdf_height));
$pdf->AliasNbPages();
$pdf->SetMargins(1, 1, 1);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->TransactionInfo($data);
if (!empty($items)) {
    $pdf->FnbInfo($items);
}
$pdf->TotalInfo($totals);
$pdf->Output();
?>
