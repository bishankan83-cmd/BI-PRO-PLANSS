<?php
// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF('L', 'mm', 'letter', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('QR Code Generator');
$pdf->SetAuthor('System');
$pdf->SetTitle('QR Code Labels');

// Disable header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Function to generate QR code and text
function generateQRCode($pdf, $row, $x, $y) {
    // QR code data
    $importantData = 'CN-' . $row['compound_name'] . 
                     '|BN-' . $row['batch'] . 
                     '|JN-' . $row['serial_number'] .
                     '|ED-' . $row['expire_date'] . 
                     '|W-' . $row['weight'];

    $allData = 'MS-' . $row['description'] . 
               '|LS-' . $row['staff_name'] . 
               '|H-' . $row['hardness'] . 
               '|MH-' . $row['mh'] . 
               '|ML-' . $row['ml'] . 
               '|T10-' . $row['t10'] . 
               '|T90-' . $row['t90'] . 
               '|RE-' . $row['rebound'];

    // Generate QR code
    $pdf->write2DBarcode($importantData . '|' . $allData, 'QRCODE,M', $x, $y, 140, 140);

    // Set text position
    $textX = $x + 155;
    $textY = $y;

    // Compound Name
    $pdf->SetFont('helvetica', 'B', 60);
    $pdf->SetXY($textX, $textY);
    $pdf->Cell(0, 0, $row['compound_name'], 0, 1);

    // Other details with consistent spacing
    $pdf->SetFont('helvetica', '', 50);
    
    // SG Value
    $pdf->SetXY($textX, $textY + 25);
    $pdf->Cell(0, 0, "SG: " . $row['sg_value'], 0, 1);

    // Approved Date
    $pdf->SetXY($textX, $textY + 45);
    $pdf->Cell(0, 0, "AD: " . date('d-m-y', strtotime($row['quality_approved'])), 0, 1);

    // Expiry Date
    $pdf->SetFont('helvetica', 'B', 50);
    $pdf->SetXY($textX, $textY + 65);
    $pdf->Cell(0, 0, "ED: " . date('d-m-y', strtotime($row['expire_date'])), 0, 1);

    // Reset font
    $pdf->SetFont('helvetica', '', 50);

    // Batch Number
    $pdf->SetXY($textX, $textY + 85);
    $pdf->Cell(0, 0, "BN: " . $row['batch'], 0, 1);

    // Pallet
    $pdf->SetXY($textX, $textY + 105);
    $pdf->Cell(0, 0, "Pallet: " . $row['pallet'], 0, 1);

    // Weight
    $pdf->SetXY($textX, $textY + 125);
    $pdf->Cell(0, 0, "Weight: " . $row['weight'], 0, 1);

    // Serial Number
    $pdf->SetXY($textX, $textY + 145);
    $pdf->Cell(0, 0, "Serial No: " . $row['serial_number'] . '-' . $row['batch'], 0, 1);
}

// SQL query
$sql = "SELECT * FROM another_table_name";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add a new page for each record
        $pdf->AddPage();
        
        // Generate QR code and text
        generateQRCode($pdf, $row, 20, 30);
    }
}

// Close database connection
$conn->close();

// Output the PDF
$pdf->Output('qr_labels.pdf', 'I');
?>