<?php
// Database connection
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update query
$sql = "
UPDATE purchase_orders AS po
JOIN tire_steel_data AS bs
    ON po.rm_code = bs.RM_code
SET po.descriptions = bs.band_size;";

if ($conn->query($sql) === TRUE) {
    echo "Records updated successfully";
} else {
    echo "Error updating records: " . $conn->error;
}

$conn->close();
?>






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

// Handle receiving all entries for a PO number
if (isset($_POST['receive_all']) && $selected_po_number) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Select all entries for the selected PO number
        $select_sql = "SELECT po_number, rm_code, number_of_bands 
                       FROM purchase_orders 
                       WHERE po_number = ?";

        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bind_param("s", $selected_po_number);
        $select_stmt->execute();
        $result = $select_stmt->get_result();

        // Prepare statements for insertion, stock update, and purchase_orders update
        $insert_stmt = $conn->prepare("INSERT INTO purchase_orders2 
            (po_number, po_date, expected_deliver_inhouse_date, supplier_code, 
             supplier_name, rm_code, descriptions, number_of_bands, previous_number_of_bands, created_at)
            SELECT po_number, po_date, expected_deliver_inhouse_date, supplier_code, 
                   supplier_name, rm_code, descriptions, ?, number_of_bands, created_at
            FROM purchase_orders 
            WHERE po_number = ? AND rm_code = ?");

        $update_stock_stmt = $conn->prepare("UPDATE steel_band_stock 
                                             SET current_quantity = current_quantity + ? 
                                             WHERE rm_code = ?");

        $update_po_stmt = $conn->prepare("UPDATE purchase_orders 
                                          SET number_of_bands = number_of_bands - ? 
                                          WHERE po_number = ? AND rm_code = ?");

        $delete_stmt = $conn->prepare("DELETE FROM purchase_orders WHERE po_number = ? AND rm_code = ?");

        // Process each entry
        while ($row = $result->fetch_assoc()) {
            // Get the new number_of_bands value from user input
            $new_number_of_bands = isset($_POST['number_of_bands_' . $row['rm_code']]) ? $_POST['number_of_bands_' . $row['rm_code']] : $row['number_of_bands'];

            // Insert into purchase_orders2 with edited number_of_bands
            $insert_stmt->bind_param("iss", $new_number_of_bands, $selected_po_number, $row['rm_code']);
            if (!$insert_stmt->execute()) {
                throw new Exception("Error inserting record: " . $insert_stmt->error);
            }

            // Update stock
            $update_stock_stmt->bind_param("is", $new_number_of_bands, $row['rm_code']);
            if (!$update_stock_stmt->execute()) {
                throw new Exception("Error updating stock: " . $update_stock_stmt->error);
            }

            // If new value matches the original, delete the entry from purchase_orders
            if ($new_number_of_bands == $row['number_of_bands']) {
                $delete_stmt->bind_param("ss", $selected_po_number, $row['rm_code']);
                if (!$delete_stmt->execute()) {
                    throw new Exception("Error deleting record: " . $delete_stmt->error);
                }
            } else {
                // Otherwise, update number_of_bands in purchase_orders
                $decrement_value = $new_number_of_bands;
                $update_po_stmt->bind_param("iss", $decrement_value, $selected_po_number, $row['rm_code']);
                if (!$update_po_stmt->execute()) {
                    throw new Exception("Error updating purchase order: " . $update_po_stmt->error);
                }
            }
        }

        // Commit transaction
        $conn->commit();

        // Redirect to view page
        header("Location: view_inserted_data.php?po_number=" . urlencode($selected_po_number));
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Management</title>
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
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h2 class="mb-0">Purchase Order Management</h2>
                </div>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-custom"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-custom">All entries for the PO have been successfully received!</div>
        <?php endif; ?>

        <div class="purchase-container">
            <h3>Select a Purchase Order</h3>
            <form method="post" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="po_number" class="form-label">Choose PO Number</label>
                        <select id="po_number" name="po_number" class="form-select" required>
                            <option value="">-- Select PO Number --</option>
                            <?php
                            $result = $conn->query("SELECT DISTINCT po_number FROM purchase_orders ORDER BY po_number");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $po_number = $row['po_number'];
                                    $selected = ($po_number === $selected_po_number) ? "selected" : "";
                                    echo "<option value='" . htmlspecialchars($po_number) . "' $selected>" . 
                                         htmlspecialchars($po_number) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn-custom">Search</button>
                    </div>
                </div>
            </form>

            <?php
            if ($selected_po_number) {
                $sql = "SELECT p.*, s.current_quantity as current_stock
                        FROM purchase_orders p
                        LEFT JOIN steel_band_stock s ON p.rm_code = s.rm_code
                        WHERE p.po_number = ?";

                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("s", $selected_po_number);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        ?>
                        <form method='post'>
                            <input type='hidden' name='po_number' value='<?php echo htmlspecialchars($selected_po_number); ?>'>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>PO Number</th>
                                            <th>RM Code</th>
                                            <th>Description</th>
                                            <th>Current Stock</th>
                                            <th>Number of Bands</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                <td>" . htmlspecialchars($row['po_number']) . "</td>
                                                <td>" . htmlspecialchars($row['rm_code']) . "</td>
                                                <td>" . htmlspecialchars($row['descriptions']) . "</td>
                                                <td>" . htmlspecialchars($row['current_stock']) . "</td>
                                                <td><input type='number' name='number_of_bands_" . htmlspecialchars($row['rm_code']) . "' 
                                                            value='" . htmlspecialchars($row['number_of_bands']) . "' min='0' required class='form-control'></td>
                                              </tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" name="receive_all" class="btn-custom">Receive All</button>
                        </form>
                    <?php } else { ?>
                        <p>No entries found for the selected PO number.</p>
                    <?php }
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
