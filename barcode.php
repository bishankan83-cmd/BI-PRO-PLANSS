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
                     '|CN-' . $row['compound_name'] . 
                     '|BN-' . $row['batch'] . 
                     '|JN-' . $row['serial_number'] .
                     '|ED-' . $row['expire_date'] . 
                     '|W-' . $row['weight'];

    // All data (normal)
    $allData = 
               '|MS-' . $row['description'] . 
               '|LS-' . $row['staff_name'] . 
               '|H-' . $row['hardness'] . 
               '|MH-' . $row['mh'] . 
               '|ML-' . $row['ml'] . 
               '|T10-' . $row['t10'] . 
               '|T90-' . $row['t90'] . 
               '|RE-' . $row['rebound'];

    // Output the QR code with special encoding for important data
    $pdf->write2DBarcode($importantData . '|' . $allData, 'QRCODE,M', $x, $y, $qrCodeSize, $qrCodeSize);
    
    // Add text data to the right of QR code
    $textX = $x + 155;
    $textY = $y ; // Adjust Y position if needed

    // Set font for Compound Name ("CN") specifically
    $pdf->SetFont('helvetica', 'B', 60); // Adjust the font size for Compound Name ("CN") here (40 is just an example)

    // Add Compound Name ("CN") text
    $pdf->SetXY($textX, $textY);
    $pdf->MultiCell(0,0, "" . $row['compound_name'], 0, 'L');

    // Reset font to the default size for other text
    $pdf->SetFont('helvetica', '', 50); // Reset to the default font size (adjust as needed)

    $pdf->SetXY($textX, $textY + 25); 
    $pdf->MultiCell(0, 0, "SG:" . $row['sg_value'], 0, 'L');
    // Add more text data

    $pdf->SetXY($textX, $textY + 45); // Adjust Y position as needed
    $pdf->MultiCell(0, 0, "AD:" . date('d-m-y', strtotime($row['quality_approved'])), 0, 'L');

    $pdf->SetFont('helvetica', 'B', 50);

    $pdf->SetXY($textX, $textY + 65); // Adjust Y position as needed
    $pdf->MultiCell(0, 0, "ED:" . date('d-m-y', strtotime($row['expire_date'])), 0, 'L');

    // Reset font to the default size for other text
    $pdf->SetFont('helvetica', '', 50); // Reset to the default font size (adjust as needed)
    // Add more text data

    $pdf->SetXY($textX, $textY + 85); // Adjust Y position as needed
    $pdf->MultiCell(0, 0, "BN:" . $row['batch'], 0, 'L');
    
    $pdf->SetXY($textX, $textY + 105); // Adjust Y position as needed
    $pdf->MultiCell(0, 0, "Pallet:" . $row['pallet'], 0, 'L');
    
    $pdf->SetXY($textX, $textY + 125); // Adjust Y position as needed
    $pdf->MultiCell(0, 0, "weight:" . $row['weight'], 0, 'L');

    $serialAndBatch = $row['serial_number'] . '-' . $row['batch'];

    $pdf->MultiCell(0, 0, "Serial No:-" . $serialAndBatch, 0, 'L');
}

// Create a new TCPDF instance
$pdf = new TCPDF('L', 'mm', 'letter', true, 'UTF-8', false);

// Disable header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);


// Set font
$pdf->SetFont('helvetica', '', 50); // Change the font size here (12 is the default size)

// SQL query to fetch data from the database
$sql = "SELECT * FROM another_table_name"; // Adjust the table name as per your database structure
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch data row by row
    while ($row = $result->fetch_assoc()) {
        // Add a new page for each row of data
        $pdf->AddPage();
        
        // Set QR code position for each page
        $qrCodeX = 10;
        $qrCodeY = 20;
        // Set QR code size
        $qrCodeSize = 150;

        // Generate QR code
        generateQRCode($pdf, $row, $qrCodeX, $qrCodeY, $qrCodeSize);

        // Add spacing between QR codes
        $qrCodeY += $qrCodeSize + 10;
    }
} else {
    echo "0 results";
}

// Output the PDF to the browser
$pdf->Output('qrcode_example.pdf', 'I');

// Close MySQL connection
$conn->close();
?>
