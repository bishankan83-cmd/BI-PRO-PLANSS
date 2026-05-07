<?php
// Include the PhpSpreadsheet library
require 'vendor/autoload.php'; // Path to PhpSpreadsheet autoload
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["excel_file"])) {
    $uploadedFile = $_FILES["excel_file"]["tmp_name"];

    // Create a new connection to your MySQL database
    $conn = new mysqli("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Load the Excel file using PhpSpreadsheet
    $spreadsheet = IOFactory::load($uploadedFile);
    $worksheet = $spreadsheet->getActiveSheet();

    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(FALSE);

        $data = [];
        foreach ($cellIterator as $cell) {
            $data[] = $cell->getValue();
        }

        if (!empty($data)) {
            $icode = $data[0];
            $amount = $data[2]; // Assuming the amount is in the second column

            // Update cstock based on icode
            $sql = "UPDATE realstock SET cstock = cstock + ? WHERE icode = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $amount, $icode);
            $stmt->execute();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Import Excel</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="excel_file">
        <input type="submit" value="Import">
    </form>
</body>
</html>
