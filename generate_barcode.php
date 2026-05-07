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
$sql = "SELECT batch, inputDate FROM another_table_name"; // Select both id and inputDate columns
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch data row by row
    while($row = $result->fetch_assoc()) {
        // Concatenate id and inputDate values to form barcode content
        $barcodeText = $row['batch'] . '-' . $row['inputDate']; // Concatenate id and inputDate with a separator

        // Set barcode format
        $barcodeFormat = 'C39'; // Barcode format, in this case, Code 39

        // Set barcode dimensions
        $barcodeWidth = 100;
        $barcodeHeight = 30;

        // Set barcode position
        $barcodeX = 50;
        $barcodeY = 50;

        // Output the barcode
        $pdf->write1DBarcode($barcodeText, $barcodeFormat, $barcodeX, $barcodeY, '', $barcodeWidth, $barcodeHeight);

        // Output the data associated with the barcode (optional)
       // $pdf->Text($barcodeX, $barcodeY + $barcodeHeight + 10, "Data: " . $barcodeText);

        // Add a new page for each row (optional)
        $pdf->AddPage();
    }
} else {
    echo "0 results";
}

// Output the PDF to the browser
$pdf->Output('barcode_example.pdf', 'I');

// Close MySQL connection
$conn->close();
?>
