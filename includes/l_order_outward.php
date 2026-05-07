<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted and get the user input
$selected_po_number = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['po_number'])) {
    $selected_po_number = $_POST['po_number'];
}

// Insert data and update stock when button is clicked
if (isset($_POST['insert_data']) && $selected_po_number) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get the updated number of bands from POST
        $updated_number_of_bands = isset($_POST['number_of_bands']) ? (int)$_POST['number_of_bands'] : 0;

        // First fetch the current data
        $select_sql = "SELECT loan_date, expected_delivery_date, suppliers_code, suppliers_name, 
                              rm_code, descriptions, number_of_bands
                       FROM loan_outward_details 
                       WHERE loan_number = ?";
        
        $select_stmt = $conn->prepare($select_sql);
        if (!$select_stmt) {
            throw new Exception("Error preparing select statement: " . $conn->error);
        }
        
        $select_stmt->bind_param("i", $selected_po_number); // Changed to integer binding
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Check if PO number already exists in loan_inward_details2
            $check_sql = "SELECT loan_number FROM loan_inward_details2 WHERE loan_number = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Error preparing check statement: " . $conn->error);
            }
            
            $check_stmt->bind_param("i", $selected_po_number); // Changed to integer binding
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                throw new Exception("This Loan number already exists in loan_inward_details2!");
            }

            // Insert into loan_inward_details2
            $insert_sql = "INSERT INTO loan_inward_details2 
                          (loan_number, loan_date, expected_delivery_date, suppliers_code, 
                           suppliers_name, rm_code, descriptions, number_of_bands)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception("Error preparing insert statement: " . $conn->error);
            }
            
            $insert_stmt->bind_param("issssssi", 
                $selected_po_number,
                $row['loan_date'],
                $row['expected_delivery_date'],
                $row['suppliers_code'],
                $row['suppliers_name'],
                $row['rm_code'],
                $row['descriptions'],
                $updated_number_of_bands
            );
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Error inserting into loan_inward_details2: " . $insert_stmt->error);
            }

            // Update steel_band_stock current_quantity
            $update_stock_sql = "UPDATE steel_band_stock 
                                SET current_quantity = current_quantity + ? 
                                WHERE rm_code = ?";
            
            $update_stock_stmt = $conn->prepare($update_stock_sql);
            if (!$update_stock_stmt) {
                throw new Exception("Error preparing stock update statement: " . $conn->error);
            }
            
            $update_stock_stmt->bind_param("is", $updated_number_of_bands, $row['rm_code']);
            
            if (!$update_stock_stmt->execute()) {
                throw new Exception("Error updating steel_band_stock: " . $update_stock_stmt->error);
            }

            // If no rows were affected, the rm_code might not exist in steel_band_stock
            if ($update_stock_stmt->affected_rows == 0) {
                throw new Exception("No matching rm_code found in steel_band_stock!");
            }

            // If everything is successful, commit the transaction
            $conn->commit();
            echo "<div style='color: green; margin: 10px 0;'>
                    Data successfully inserted into loan_inward_details2 and stock updated!</div>";

            // Close statements
            $update_stock_stmt->close();
            $insert_stmt->close();
            $check_stmt->close();
            $select_stmt->close();
            
        } else {
            throw new Exception("No record found for Loan Number: " . htmlspecialchars($selected_po_number));
        }
        
    } catch (Exception $e) {
        // If there's an error, rollback the transaction
        $conn->rollback();
        echo "<div style='color: red; margin: 10px 0;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Inward Details</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
        select, button { padding: 5px 10px; margin: 5px; }
        input[type="number"] { width: 100px; padding: 5px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Select a Loan Number</h2>
<form method="post" action="">
    <label for="po_number">Choose Loan Number:</label>
    <select id="po_number" name="po_number" required>
        <option value="">-- Select Loan Number --</option>
        <?php
        $result = $conn->query("SELECT loan_number FROM loan_inward_details ORDER BY loan_number");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $po_number = $row['loan_number'];
                $selected = ($po_number == $selected_po_number) ? "selected" : ""; // Changed to == for integer comparison
                echo "<option value='" . htmlspecialchars($po_number) . "' $selected>" . 
                     htmlspecialchars($po_number) . "</option>";
            }
        }
        ?>
    </select>
    <button type="submit">Search</button>
</form>

<?php
if ($selected_po_number) {
    $sql = "SELECT p.*, s.current_quantity as current_stock
            FROM loan_inward_details p
            LEFT JOIN steel_band_stock s ON p.rm_code = s.rm_code
            WHERE p.loan_number = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $selected_po_number); // Changed to integer binding
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo "<h2>Loan Inward Details</h2>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='po_number' value='" . htmlspecialchars($selected_po_number) . "'>";
            echo "<table>";
            echo "<tr>
                    <th>Loan Number</th>
                    <th>Loan Date</th>
                    <th>Expected Delivery Date</th>
                    <th>Supplier Code</th>
                    <th>Supplier Name</th>
                    <th>RM Code</th>
                    <th>Descriptions</th>
                    <th>Number of Bands</th>
                    <th>Current Stock</th>
                  </tr>";
            echo "<tr>
                    <td>" . htmlspecialchars($row['loan_number'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['loan_date'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['expected_delivery_date'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['suppliers_code'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['suppliers_name'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['rm_code'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['descriptions'] ?? '') . "</td>
                    <td><input type='number' name='number_of_bands' value='" . 
                        htmlspecialchars($row['number_of_bands'] ?? '') . "' required></td>
                    <td>" . htmlspecialchars($row['current_stock'] ?? '0') . "</td>
                  </tr>";
            echo "</table>";
            echo "<button type='submit' name='insert_data'>Insert Into loan_inward_details2 and Update Stock</button>";
            echo "</form>";
        } else {
            echo "<div class='error'>No record found for Loan Number: " . 
                 htmlspecialchars($selected_po_number) . "</div>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

</body>
</html>