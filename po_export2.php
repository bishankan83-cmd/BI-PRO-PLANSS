<!DOCTYPE html>
<html>
<head>
    <title>Upload Excel File</title>
</head>
<body>
    <h2>Upload Excel File</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xls, .xlsx" required>
        <button type="submit" name="submit">Upload</button>
    </form>
</body>
</html>




<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert purchase order data from the Excel file
if (isset($_POST['submit'])) {
    $file = $_FILES['file']['tmp_name'];

    // Load the Excel file
    try {
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Skip the header row and insert data
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header row

            // Escape values to prevent SQL injection
            $po_number = $conn->real_escape_string($row[0]); // Column 1
            $po_date = $conn->real_escape_string($row[1]); // Column 2
            $expected_date = $conn->real_escape_string($row[2]); // Column 3
            $supplier_code = $conn->real_escape_string($row[3]); // Column 4
            $supplier_name = $conn->real_escape_string($row[4]); // Column 5
            $rm_code = $conn->real_escape_string($row[5]); // Column 6
            $descriptions = $conn->real_escape_string($row[6]); // Column 7
            $number_of_bands = (int)$row[7]; // Column 8 (integer)

            // Insert data into the table
            $sql = "INSERT INTO purchase_orders3 
                    (po_number, po_date, expected_deliver_inhouse_date, supplier_code, supplier_name, rm_code, descriptions, number_of_bands) 
                    VALUES ('$po_number', '$po_date', '$expected_date', '$supplier_code', '$supplier_name', '$rm_code', '$descriptions', $number_of_bands)";
            if (!$conn->query($sql)) {
                echo "Error inserting row: " . $conn->error . "<br>";
            }
        }
        echo "<p style='color: green;'>Data imported successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error loading file: " . $e->getMessage() . "</p>";
    }
}

// Displaying the current data from purchase_orders3
$sql = "SELECT * FROM purchase_orders3";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h3>Current Purchase Orders</h3>";
    echo "<table border='1'>";
    echo "<tr>
            <th>PO Number</th>
            <th>PO Date</th>
            <th>Expected Date</th>
            <th>Supplier Code</th>
            <th>Supplier Name</th>
            <th>RM Code</th>
            <th>Description</th>
            <th>Number of Bands</th>
            <th>Actions</th>
          </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['po_number'] . "</td>
                <td>" . $row['po_date'] . "</td>
                <td>" . $row['expected_deliver_inhouse_date'] . "</td>
                <td>" . $row['supplier_code'] . "</td>
                <td>" . $row['supplier_name'] . "</td>
                <td>" . $row['rm_code'] . "</td>
                <td>" . $row['descriptions'] . "</td>
                <td>" . $row['number_of_bands'] . "</td>
                <td><a href='?edit=" . $row['po_number'] . "'>Edit</a> | 
                    <a href='?delete=" . $row['po_number'] . "'>Delete</a></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found in the table.";
}

// Handle update and delete actions
if (isset($_GET['edit'])) {
    $po_number = $_GET['edit'];
    $sql = "SELECT * FROM purchase_orders3 WHERE po_number = '$po_number'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        // Get the updated values from the form
        $po_date = $_POST['po_date'];
        $expected_date = $_POST['expected_date'];
        $supplier_code = $_POST['supplier_code'];
        $supplier_name = $_POST['supplier_name'];
        $rm_code = $_POST['rm_code'];
        $descriptions = $_POST['descriptions'];
        $number_of_bands = (int)$_POST['number_of_bands'];

        // Update the database
        $update_sql = "UPDATE purchase_orders3 SET 
                        po_date = '$po_date', 
                        expected_deliver_inhouse_date = '$expected_date', 
                        supplier_code = '$supplier_code', 
                        supplier_name = '$supplier_name', 
                        rm_code = '$rm_code', 
                        descriptions = '$descriptions', 
                        number_of_bands = $number_of_bands
                       WHERE po_number = '$po_number'";

        if ($conn->query($update_sql)) {
            echo "<p style='color: green;'>Record updated successfully!</p>";
            header('Location: ' . $_SERVER['PHP_SELF']); // Redirect to refresh the page
        } else {
            echo "<p style='color: red;'>Error updating record: " . $conn->error . "</p>";
        }
    }

    echo "<h3>Edit Purchase Order</h3>";
    echo "<form method='post'>
            PO Date: <input type='text' name='po_date' value='" . $row['po_date'] . "' required><br>
            Expected Date: <input type='text' name='expected_date' value='" . $row['expected_deliver_inhouse_date'] . "' required><br>
            Supplier Code: <input type='text' name='supplier_code' value='" . $row['supplier_code'] . "' required><br>
            Supplier Name: <input type='text' name='supplier_name' value='" . $row['supplier_name'] . "' required><br>
            RM Code: <input type='text' name='rm_code' value='" . $row['rm_code'] . "' required><br>
            Descriptions: <input type='text' name='descriptions' value='" . $row['descriptions'] . "' required><br>
            Number of Bands: <input type='number' name='number_of_bands' value='" . $row['number_of_bands'] . "' required><br>
            <button type='submit' name='update'>Update</button>
          </form>";
}

// Handle delete action
if (isset($_GET['delete'])) {
    $po_number = $_GET['delete'];
    $sql = "DELETE FROM purchase_orders3 WHERE po_number = '$po_number'";

    if ($conn->query($sql)) {
        echo "<p style='color: green;'>Record deleted successfully!</p>";
        header('Location: ' . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    } else {
        echo "<p style='color: red;'>Error deleting record: " . $conn->error . "</p>";
    }
}

// Close the database connection
$conn->close();
?>




<?php
// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Establish a connection to the database
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// When the button is clicked
if (isset($_POST['insert_delete'])) {
    // Insert data from purchase_orders3 into purchase_orders
    $insertQuery = "INSERT INTO purchase_orders (po_number, po_date, expected_deliver_inhouse_date, supplier_code, supplier_name, rm_code, descriptions, number_of_bands, created_at)
                    SELECT po_number, po_date, expected_deliver_inhouse_date, supplier_code, supplier_name, rm_code, descriptions, number_of_bands, created_at
                    FROM purchase_orders3";

    if ($mysqli->query($insertQuery) === TRUE) {
        // Delete all data from the purchase_orders3 table
        $deleteQuery = "DELETE FROM purchase_orders3";
        if ($mysqli->query($deleteQuery) === TRUE) {
            // Redirect to dashboard.php
            header("Location: dashboard.php");
            exit(); // Ensure no further code is executed
        } else {
            echo "Error deleting data from purchase_orders3 table: " . $mysqli->error;
        }
    } else {
        echo "Error inserting data into purchase_orders: " . $mysqli->error;
    }
}

?>

<!-- HTML Button to trigger the insert and delete operation -->
<form method="POST">
    <button type="submit" name="insert_delete" onclick="return confirm('Are you sure you want to insert data and delete the purchase_orders3 table data?')">Insert Data</button>
</form>
