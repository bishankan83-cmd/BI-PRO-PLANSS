<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// download_pdf.php
require('fpdf/fpdf.php'); // You need to install FPDF library

// Get parameters from URL
$serialNumber = isset($_GET['serial']) ? $_GET['serial'] : '';
$qrCodePath = isset($_GET['path']) ? $_GET['path'] : '';

// Validate inputs to prevent directory traversal attacks
if (!file_exists($qrCodePath) || strpos($qrCodePath, 'qrcodes/') !== 0) {
    die("Invalid QR code path");
}

if (empty($serialNumber)) {
    die("Serial number is required");
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Add title
$pdf->Cell(0, 10, 'QR Code for Tire Serial: ' . $serialNumber, 0, 1, 'C');
$pdf->Ln(10);

// Add QR Code image
if (file_exists($qrCodePath)) {
    // Get image dimensions
    list($width, $height) = getimagesize($qrCodePath);
    
    // Calculate position for centering
    $pageWidth = $pdf->GetPageWidth();
    $imageWidth = min(100, $width); // Limit width to 100 points
    $x = ($pageWidth - $imageWidth) / 2;
    
    $pdf->Image($qrCodePath, $x, $pdf->GetY(), $imageWidth);
    $pdf->Ln($imageWidth + 10); // Add space after image
}

// Add serial number
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, $serialNumber, 0, 1, 'C');

// Add tire details
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);

// Get the tire data from the database
try {


    ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    $host = 'localhost';
    $db = 'planatir_task_managemen';
    $user = 'planatir_task_managemen';
    $password = 'Bishan@1919';

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Remove hyphen from serial number for database query
    $dbSerialNumber = str_replace('-', '', $serialNumber);
    
    $stmt = $pdo->prepare("SELECT * FROM saved_tires WHERE serialNumber = ?");
    $stmt->execute([$dbSerialNumber]);
    $tire = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tire) {
        $pdf->Cell(0, 10, 'Tire Details:', 0, 1);
        $pdf->Cell(50, 10, 'Tire Code:', 0);
        $pdf->Cell(0, 10, $tire['tireCode'], 0, 1);
        
        $pdf->Cell(50, 10, 'Brand:', 0);
        $pdf->Cell(0, 10, $tire['brand'], 0, 1);
        
        $pdf->Cell(50, 10, 'Tire Weight:', 0);
        $pdf->Cell(0, 10, $tire['tireWeight'], 0, 1);
        
        $pdf->Cell(50, 10, 'Press Number:', 0);
        $pdf->Cell(0, 10, $tire['pressNumber'], 0, 1);
    }
    
} catch (PDOException $e) {
    // If database connection fails, just continue without the details
    $pdf->Cell(0, 10, 'Tire details not available', 0, 1);
}

// Output the PDF for download
$pdfFileName = 'QRCode_' . $serialNumber . '.pdf';
$pdf->Output('D', $pdfFileName);
?>