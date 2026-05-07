<?php
header('Content-Type: application/json');

// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'planatir_task_managemen');
define('DB_PASSWORD', 'Bishan@1919');
define('DB_NAME', 'planatir_task_managemen');

/**
 * Send JSON response and exit
 * @param bool $success
 * @param string $message
 */
function sendResponse($success, $message) {
    echo json_encode([
        $success ? 'message' : 'error' => $message
    ]);
    exit();
}

/**
 * Create database connection
 * @return mysqli
 */
function createConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        sendResponse(false, "Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

/**
 * Delete all records from bom_new table
 * @param mysqli $conn
 */
function deleteAllRecords($conn) {
    $sql = "DELETE FROM bom_new";
    
    if ($conn->query($sql) === TRUE) {
        sendResponse(true, "All records deleted successfully.");
    } else {
        sendResponse(false, "Error deleting records: " . $conn->error);
    }
}

/**
 * Import CSV data to database
 * @param mysqli $conn
 * @param string $file
 */
function importCSV($conn, $file) {
    if (!is_uploaded_file($file)) {
        sendResponse(false, "No file was uploaded.");
    }

    $csv = fopen($file, 'r');
    if (!$csv) {
        sendResponse(false, "Failed to open CSV file.");
    }

    // Skip header row
    $header = fgetcsv($csv);
    
    // SQL query for inserting data
    $sql = "INSERT INTO bom_new (
        Item, icode, t_size, `Item Description`, 
        a, b, c, d, e, f, g, h, i, j, 
        k, l, m, n, o, p, q, r, 
        `Grand Totalcompound weight`, Color, Brand, 
        `Green Tire weight`, PBweight
    ) VALUES (" . str_repeat('?,', 26) . "?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        fclose($csv);
        sendResponse(false, "Error preparing SQL query: " . $conn->error);
    }

    // Process CSV rows
    $rowCount = 0;
    $errorCount = 0;

    while (($row = fgetcsv($csv)) !== FALSE) {
        $rowCount++;

        if (count($row) != 27) {
            $errorCount++;
            continue;
        }

        $stmt->bind_param(str_repeat('s', 27),
            $row[0], $row[1], $row[2], $row[3], $row[4], 
            $row[5], $row[6], $row[7], $row[8], $row[9],
            $row[10], $row[11], $row[12], $row[13], $row[14],
            $row[15], $row[16], $row[17], $row[18], $row[19],
            $row[20], $row[21], $row[22], $row[23], $row[24],
            $row[25], $row[26]
        );

        if (!$stmt->execute()) {
            $errorCount++;
        }
    }

    // Clean up
    $stmt->close();
    fclose($csv);

    // Report results
    if ($errorCount > 0) {
        sendResponse(false, "Import completed with $errorCount errors out of $rowCount rows.");
    } else {
        sendResponse(true, "Successfully imported $rowCount rows.");
    }
}

// Main execution
try {
    $conn = createConnection();

    if (isset($_POST['delete'])) {
        deleteAllRecords($conn);
    } elseif (isset($_FILES['file'])) {
        if ($_FILES['file']['type'] !== 'text/csv') {
            sendResponse(false, "Only CSV files are allowed.");
        }
        importCSV($conn, $_FILES['file']['tmp_name']);
    } else {
        sendResponse(false, "No valid action specified.");
    }
} catch (Exception $e) {
    sendResponse(false, "An error occurred: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>