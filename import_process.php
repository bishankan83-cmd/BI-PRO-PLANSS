<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Function to extract Job Number and Batch from Test_ID
function extractJobNumberAndBatch($testID) {
    // Split the test ID using '-' as the delimiter
    $parts = explode('-', $testID);
    
    // Extract Job Number and Batch
    $jobNumber = (int) $parts[0];
    $batch = (int) $parts[1];

    return [$jobNumber, $batch];
}

// Check if a file was uploaded
if ($_FILES['excel_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
    // Database connection details
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

    // Load the Excel file
    $inputFileName = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();

    // Assuming the first row contains column headers
    $headerRow = $worksheet->getRowIterator()->current();
    $columns = [];
    foreach ($headerRow->getCellIterator() as $cell) {
        $columns[] = $cell->getValue();
    }

    // Prepare the SQL statement
    $tableName = 'importmix'; // Change to your table name
    $sql = "INSERT INTO $tableName (CompoundID, TestType, Workstation, MixDate, JobNumber, Batch, SerialNumber, MH, ML, Tc10, Tc90, Ts2, PassFail, TestTime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing SQL statement: " . $conn->error);
    }

    // Loop through rows (starting from the second row)
    foreach ($worksheet->getRowIterator(2) as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getValue();
        }

        // Extract Job Number, Batch, and Serial Number from Test_ID
        $testID = $rowData[4]; // Assuming Test_ID is at index 4 (column E)
        list($jobNumber, $batch) = extractJobNumberAndBatch($testID);
        $serialNumber = $testID;

        // Bind parameters
        $stmt->bind_param("ssssssssssssss", $rowData[0], $rowData[1], $rowData[2], $rowData[3], $jobNumber, $batch, $serialNumber, $rowData[5], $rowData[6], $rowData[7], $rowData[8], $rowData[9], $rowData[10], $rowData[11]);

        // Execute the statement
        if (!$stmt->execute()) {
            die("Error inserting data: " . $stmt->error);
        }
    }

    // Close the statement
    $stmt->close();

    // Close connection
    $conn->close();

    echo "Data imported successfully.";
} else {
    echo "Error uploading file.";
}
?>
