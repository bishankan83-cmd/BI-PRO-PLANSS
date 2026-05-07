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
        $select_sql = "SELECT loan_number, loan_date, expected_delivery_date, suppliers_code, suppliers_name, 
        rm_code, descriptions, number_of_bands
        FROM loan_outward_details 
        WHERE loan_number = ?";
        
        $select_stmt = $conn->prepare($select_sql);
        if (!$select_stmt) {
            throw new Exception("Error preparing select statement: " . $conn->error);
        }
        
        $select_stmt->bind_param("s", $selected_po_number);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $previous_number_of_bands = $row['number_of_bands']; // Save the previous value

            

            // Calculate the stock update amount and determine record handling
            $stock_update_amount = $updated_number_of_bands;
            $delete_record = false;

            // Determine if we should delete the record or update it
            if ($updated_number_of_bands == $previous_number_of_bands) {
                // If entered bands match previous bands, delete the record
                $delete_record = true;
                $stock_update_amount = $updated_number_of_bands;
            } else {
                // If entered bands are different, adjust the number
                $stock_update_amount = $updated_number_of_bands;
            }

            // Insert into loan_outward_details2
            $insert_sql = "INSERT INTO loan_outward_details2  
                          (loan_number, loan_date, expected_delivery_date, suppliers_code, 
                           suppliers_name, rm_code, descriptions, number_of_bands, previous_number_of_bands, created_at)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                throw new Exception("Error preparing insert statement: " . $conn->error);
            }
            
            $insert_stmt->bind_param("sssssssis", 
                $row['loan_number'],
                $row['loan_date'],
                $row['expected_delivery_date'],
                $row['suppliers_code'],
                $row['suppliers_name'],
                $row['rm_code'],
                $row['descriptions'],
                $updated_number_of_bands,
                $previous_number_of_bands
            );
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Error inserting into loan_outward_details2: " . $insert_stmt->error);
            }

            // Update steel_band_stock current_quantity
            $update_stock_sql = "UPDATE steel_band_stock 
                                SET current_quantity = current_quantity - ? 
                                WHERE rm_code = ?";
            
            $update_stock_stmt = $conn->prepare($update_stock_sql);
            if (!$update_stock_stmt) {
                throw new Exception("Error preparing stock update statement: " . $conn->error);
            }
            
            $update_stock_stmt->bind_param("is", $stock_update_amount, $row['rm_code']);
            
            if (!$update_stock_stmt->execute()) {
                throw new Exception("Error updating steel_band_stock: " . $update_stock_stmt->error);
            }

            // If no rows were affected, the rm_code might not exist in steel_band_stock
            if ($update_stock_stmt->affected_rows == 0) {
                throw new Exception("No matching rm_code found in steel_band_stock!");
            }

            // Handle record deletion or update based on the condition
            if ($delete_record) {
                // Delete the record from loan_outward_details
                $delete_sql = "DELETE FROM loan_outward_details WHERE loan_number = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                if (!$delete_stmt) {
                    throw new Exception("Error preparing delete statement: " . $conn->error);
                }
                
                $delete_stmt->bind_param("s", $selected_po_number);
                
                if (!$delete_stmt->execute()) {
                    throw new Exception("Error deleting from loan_outward_details: " . $delete_stmt->error);
                }
            } else {
                // Update the record with the new number of bands
                $update_sql = "UPDATE loan_outward_details 
                               SET number_of_bands = number_of_bands - ? 
                               WHERE loan_number = ?";
                $update_stmt = $conn->prepare($update_sql);
                if (!$update_stmt) {
                    throw new Exception("Error preparing update statement: " . $conn->error);
                }
                
                $update_stmt->bind_param("is", $updated_number_of_bands, $selected_po_number);
                
                if (!$update_stmt->execute()) {
                    throw new Exception("Error updating loan_outward_details: " . $update_stmt->error);
                }
            }

            // If everything is successful, commit the transaction
            $conn->commit();
            $success_message = "Data successfully processed!";

            // Redirect to view page
            header("Location: view_inserted_data3.php?loan_number=" . urlencode($selected_po_number));
            exit();

        } else {
            throw new Exception("No record found for Loan Number: " . htmlspecialchars($selected_po_number));
        }
        
    } catch (Exception $e) {
        // If there's an error, rollback the transaction
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan outward Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --background-color: #f0f2f5;
            --card-background: #FFFFFF;
            --text-dark: #333;
            --text-light: #FFFFFF;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .purchase-container {
            background: var(--card-background);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #d4651a);
            color: var(--text-light);
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .form-select, .btn-custom {
            background-color: #f9f9f9;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .btn-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-light);
        }

        .btn-custom:hover {
            background-color: #d4651a;
            border-color: #d4651a;
            transform: translateY(-2px);
        }

        .table-responsive {
            margin-top: 20px;
        }

        .alert-custom {
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 container-custom">
                <div class="page-header">
                    <h2 class="mb-0">Loan outward Management</h2>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-custom"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-custom"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="post" action="" class="mb-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="po_number" class="form-label">Select Loan Number</label>
                            <select id="po_number" name="po_number" class="form-select" required>
                                <option value="">-- Select Loan Number --</option>
                                <?php
                                $result = $conn->query("SELECT loan_number FROM loan_outward_details ORDER BY loan_number");
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $po_number = $row['loan_number'];
                                        $selected = ($po_number === $selected_po_number) ? "selected" : "";
                                        echo "<option value='" . htmlspecialchars($po_number) . "' $selected>" . 
                                             htmlspecialchars($po_number) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-custom w-100">Search</button>
                        </div>
                    </div>
                </form>

                <?php
                if ($selected_po_number) {
                    $sql = "SELECT p.*, s.current_quantity as current_stock
                            FROM loan_outward_details p
                            LEFT JOIN steel_band_stock s ON p.rm_code = s.rm_code
                            WHERE p.loan_number = ?";
                    
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("s", $selected_po_number);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($row = $result->fetch_assoc()) {
                            ?>
                            <div class="table-responsive">
                                <form method='post'>
                                    <input type='hidden' name='po_number' value='<?php echo htmlspecialchars($selected_po_number); ?>'>
                                    <table class="table table-striped table-hover table-custom">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Loan Number</th>
                                                <th>Loan Date</th>
                                                <th>Expected Delivery</th>
                                                <th>Supplier Code</th>
                                                <th>Supplier Name</th>
                                                <th>RM Code</th>
                                                <th>Descriptions</th>
                                                <th>Number of Bands</th>
                                                <th>Current Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['loan_number'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['loan_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['expected_delivery_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['suppliers_code'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['suppliers_name'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['rm_code'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($row['descriptions'] ?? ''); ?></td>
                                                <td>
                                                    <input type='number' class='form-control' name='number_of_bands' 
                                                           value='<?php echo htmlspecialchars($row['number_of_bands'] ?? ''); ?>' 
                                                           min='0' max='<?php echo htmlspecialchars($row['number_of_bands'] ?? '0'); ?>' required>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['current_stock'] ?? '0'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type='submit' name='insert_data' class='btn btn-success btn-custom'>
                                        Insert and Update Stock
                                    </button>
                                </form>
                            </div>
                            <?php
                        } else {
                            echo "<div class='alert alert-warning'>No record found for Loan Number: " . 
                                 htmlspecialchars($selected_po_number) . "</div>";
                        }
                        $stmt->close();
                    }
                }

                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Optional: Add client-side validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const numberInput = document.querySelector('input[name="number_of_bands"]');
        
        form.addEventListener('submit', function(e) {
            const maxValue = parseInt(numberInput.max);
            const inputValue = parseInt(numberInput.value);
            
            if (inputValue > maxValue) {
                e.preventDefault();
                alert('Number of bands cannot exceed the available quantity: ' + maxValue);
                numberInput.value = maxValue;
            }
        });
    });
</script>
</body>
</html>