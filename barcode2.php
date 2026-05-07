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

// Create a new TCPDF instance
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('TCPDF');
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Barcode Example');
$pdf->SetSubject('Generating Barcode using TCPDF');
$pdf->SetKeywords('TCPDF, PDF, barcode');

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Add a page
$pdf->AddPage();




// SQL query to fetch data from the database
$sql = "SELECT * FROM another_table_name2"; // Adjust the table name as per your database structure
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch data row by row
    while($row = $result->fetch_assoc()) {
        // Concatenate values from multiple columns to form barcode content
        $barcodeText = $row['shift'].','.$row['inputDate'] . ',CN-' . $row['compound_name'] . ',ERP-' . $row['description']. ',DS-' . $row['cstock'] .
        ',BN-' . $row['batch'] . ',P-' . $row['pallet']. ',W-' . $row['weight']
        . ',QD-' . $row['quality_approved'] . ',ED-' . $row['expire_date']. ',LS-' . $row['staff_name'] . ',SG-' . $row['sg_value']
        . ',H-' . $row['hardness'] . ',MH-' . $row['mh']. ',ML-' . $row['ml'] . ',T10-' . $row['t10']
        . ',T90-' . $row['t90'] .',RE-'. $row['rebound'];

       // Set barcode format

        // Set barcode format
        $barcodeFormat = 'C128'; // Barcode format, in this case, Code 39

      // Set barcode dimensions
      $barcodeWidth = 100;
      $barcodeHeight = 50;


        // Set barcode position
        $barcodeX = 50;
        $barcodeY = 50;
// Output the barcode
$pdf->write1DBarcode($barcodeText, $barcodeFormat, $barcodeX, $barcodeY, '', $barcodeWidth, $barcodeHeight);

// Concatenate values
//$details = 'CN' . $row['compound_name'] . ' | Date: ' . $row['inputDate'] . ' | BN: ' . $row['batch'] . ' | QA: ' . $row['quality_approved'] . ' | ED: ' . $row['expire_date'];

// Add details text under the barcode
$pdf->SetFont('helvetica', '', 20);
$pdf->SetXY($barcodeX, $barcodeY + $barcodeHeight + 5); // Adjust Y position as needed
$pdf->Cell(0, 0, $details, 0, 1, 'C');


        
    }
} else {
    echo "0 results";
}

// Output the PDF to the browser
$pdf->Output('barcode_example.pdf', 'I');

// Close MySQL connection
$conn->close();
?>
