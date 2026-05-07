<?php
// Establish a connection to your database
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

// Function to generate QR code and add text data
function generateQRCode($pdf, $row, $x, $y, $qrCodeSize) {
    // Important data
    $importantData = 
        '|TC-' . $row['tireCode'] . 
        '|SN-' . $row['serialNumber'] . 
        '|PN-' . $row['pressNumber'] . 
        '|TW-' . $row['tireWeight'];

    // Additional data
    $allData = 
        '|BR-' . $row['brand'] . 
        '|CA-' . date('d-m-y', strtotime($row['created_at']));

    // Generate QR code
    $pdf->write2DBarcode($importantData . '|' . $allData, 'QRCODE,M', $x, $y, $qrCodeSize, $qrCodeSize);
    
    // Add Serial Number text to the right of QR code
    $textX = $x + 35;
    $textY = $y + 161.9; // Adjust Y position if needed

    $pdf->SetXY($textX, $textY);
    $pdf->MultiCell(0, 0, "" . $row['serialNumber'], 0, 'L');
}

// Create a new TCPDF instance
$pdf = new TCPDF('L', 'mm', 'letter', true, 'UTF-8', false);

// Disable header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set font
$pdf->SetFont('helvetica', '', 50); // Adjust font size if needed

// SQL query to fetch only the LAST record from the database
$sql = "SELECT * FROM saved_tires ORDER BY id DESC LIMIT 1"; // Using 'id' as the primary key
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc(); // Fetch the last row

    // Add a new page for the last record
    $pdf->AddPage();

    // Set QR code position
    $qrCodeX = 60;
    $qrCodeY = 6;
    $qrCodeSize = 163;

    // Generate QR code for the last row
    generateQRCode($pdf, $row, $qrCodeX, $qrCodeY, $qrCodeSize);
} else {
    echo "0 results";
}

// Output the PDF
$pdf->Output('last_tire_qrcode.pdf', 'I');

// Close MySQL connection
$conn->close();
?>
