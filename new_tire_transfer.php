<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire Management System</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            color: #333333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        h2 {
            color: #F28018;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333333;
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="file"]:focus {
            border-color: #F28018;
            outline: none;
            box-shadow: 0 0 5px rgba(242, 128, 24, 0.4);
        }
        button {
            padding: 10px 20px;
            background-color: #F28018;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #e67e22;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #343a40;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #f0f0f0;
        }
        tr:hover {
            background-color: rgba(242, 128, 24, 0.1);
        }
        .error {
            color: #e74c3c;
            margin-top: 10px;
            font-weight: bold;
        }
        .success {
            color: #27ae60;
            margin-top: 10px;
            font-weight: bold;
        }
        .status-low {
            color: #f39c12;
            font-weight: bold;
        }
        .status-out {
            color: #e74c3c;
            font-weight: bold;
        }
        .status-in {
            color: #27ae60;
            font-weight: bold;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        select {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        select:focus {
            border-color: #F28018;
            outline: none;
            box-shadow: 0 0 5px rgba(242, 128, 24, 0.4);
        }
        .select-all-container {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .auto-selected {
            color: #27ae60;
            font-weight: bold;
            font-style: italic;
        }
    </style>
    <script>
        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }
        
        // Auto-select all checkboxes when results are loaded
        window.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            // Also check the "Select All" checkbox if it exists
            const selectAllCheckbox = document.getElementById('select_all');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = true;
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Tire Management System</h2>
        
        <!-- Serial Number Search Form -->
        <div class="form-group">
            <form method="post" action="">
                <label for="serial_number">Enter or Select Serial Number:</label>
                <input type="text" name="serial_number" id="serial_number" list="serial_numbers" required placeholder="Type or select a serial number">
                <datalist id="serial_numbers">
                    <?php
                    // Start session for caching
                    session_start();
                    
                    // Database connection
                    $conn = new mysqli("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Check if serial numbers are cached in session
                    if (!isset($_SESSION['serial_numbers'])) {
                        $sql = "SELECT DISTINCT serial_number FROM stock_erp ORDER BY serial_number";
                        $result = $conn->query($sql);
                        $serial_numbers = [];
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $serial_numbers[] = $row['serial_number'];
                            }
                        }
                        // Cache the serial numbers in session
                        $_SESSION['serial_numbers'] = $serial_numbers;
                    } else {
                        // Use cached serial numbers
                        $serial_numbers = $_SESSION['serial_numbers'];
                    }

                    // Populate datalist with serial numbers
                    foreach ($serial_numbers as $serial) {
                        echo "<option value='" . htmlspecialchars($serial) . "'>";
                    }
                    ?>
                </datalist>
                <button type="submit" name="search_serial">Search</button>
            </form>
        </div>

        <!-- Excel Upload Form -->
        <div class="form-group">
            <form method="post" action="" enctype="multipart/form-data">
                <label for="excel_file">Upload Excel File (Serial Number, Tire Code):</label>
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required>
                <button type="submit" name="upload_excel">Upload</button>
            </form>
        </div>

        <?php
        require 'vendor/autoload.php';
        use PhpOffice\PhpSpreadsheet\IOFactory;

        $message = '';
        $results = [];

        if (isset($_POST['search_serial']) && !empty($_POST['serial_number'])) {
            $serial_number = $conn->real_escape_string($_POST['serial_number']);
            $sql = "SELECT * FROM stock_erp WHERE serial_number = '$serial_number'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                $results = $result->fetch_all(MYSQLI_ASSOC);
            } else {
                $message = "<p class='error'>No records found for serial number: " . htmlspecialchars($serial_number) . "</p>";
            }
        }

        if (isset($_POST['upload_excel']) && isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['excel_file']['tmp_name'];
            try {
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();

                $results = [];
                for ($row = 2; $row <= $highestRow; $row++) {
                    $serial_number = $sheet->getCell('A' . $row)->getValue();
                    $tyre_code = $sheet->getCell('B' . $row)->getValue();
                    
                    if (!empty($serial_number) && !empty($tyre_code)) {
                        $serial_number = $conn->real_escape_string($serial_number);
                        $tyre_code = $conn->real_escape_string($tyre_code);
                        $sql = "SELECT * FROM stock_erp WHERE serial_number = '$serial_number' AND tyre_code = '$tyre_code'";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            $results = array_merge($results, $result->fetch_all(MYSQLI_ASSOC));
                        }
                    }
                }
                if (empty($results)) {
                    $message = "<p class='error'>No matching records found in the uploaded file.</p>";
                }
            } catch (Exception $e) {
                $message = "<p class='error'>Error processing Excel file: " . $e->getMessage() . "</p>";
            }
        } elseif (isset($_POST['upload_excel'])) {
            $message = "<p class='error'>Please upload a valid Excel file.</p>";
        }

        function getMonthName($monthNum) {
            $months = [
                '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
            ];
            return isset($months[$monthNum]) ? $months[$monthNum] : '';
        }

        if (isset($_POST['move_to']) && isset($_POST['selected_ids'])) {
            $selected_ids = $_POST['selected_ids'];
            $move_to = $_POST['move_to'];
            $target_table = ($move_to == 'non_moving') ? 'non_moveing_tire' : 'over_age';
            
            foreach ($selected_ids as $id) {
                $sql = "SELECT * FROM stock_erp WHERE id = " . (int)$id;
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    
                    $serial_number = $conn->real_escape_string($row['serial_number']);
                    $date = $row['date'];
                    $tyre_code = $conn->real_escape_string($row['tyre_code']);
                    $description = $conn->real_escape_string($row['description']);
                    $number_of_tires = (int)$row['qty'];

                    $serial_parts = explode('-', $row['serial_number']);
                    $year = '';
                    $month = '';
                    if (isset($serial_parts[0]) && strlen($serial_parts[0]) >= 6) {
                        $month = substr($serial_parts[0], 0, 2);
                        $year = substr($serial_parts[0], 2, 4);
                        $month = getMonthName($month);
                    }

                    $brand = '';
                    $color = '';
                    $tyre_code_escaped = $conn->real_escape_string($row['tyre_code']);
                    $tire_sql = "SELECT Brand, Colour FROM tire_details WHERE icode = '$tyre_code_escaped'";
                    $tire_result = $conn->query($tire_sql);
                    if ($tire_result && $tire_result->num_rows > 0) {
                        $tire_row = $tire_result->fetch_assoc();
                        $brand = $conn->real_escape_string($tire_row['Brand'] ?? '');
                        $color = $conn->real_escape_string($tire_row['Colour'] ?? '');
                    }
                    
                    $insert_sql = "INSERT INTO $target_table (serial_number, date, tyre_code, description, number_of_tires, year, month, brand, color)
                                  VALUES ('$serial_number', '$date', '$tyre_code', '$description', $number_of_tires, '$year', '$month', '$brand', '$color')";
                    if ($conn->query($insert_sql)) {
                        $delete_sql = "DELETE FROM stock_erp WHERE id = " . (int)$id;
                        if ($conn->query($delete_sql)) {
                            $message = "<p class='success'>Selected records moved to $target_table successfully.</p>";
                            // Clear cache if records are modified
                            unset($_SESSION['serial_numbers']);
                        } else {
                            $message = "<p class='error'>Error deleting record from stock_erp: " . $conn->error . "</p>";
                        }
                    } else {
                        $message = "<p class='error'>Error inserting into $target_table: " . $conn->error . "</p>";
                    }
                }
            }
        }

        if (!empty($results)) {
            echo "<form method='post' action=''>";
            echo "<h3>Search Results <span class='auto-selected'>(All items auto-selected)</span></h3>";
            
            // Add select all checkbox for manual control
            echo "<div class='select-all-container'>";
            echo "<label><input type='checkbox' id='select_all' onchange='toggleAll(this)' checked> Select/Deselect All</label>";
            echo "</div>";
            
            echo "<table>";
            echo "<tr><th>Select</th><th>Serial Number</th><th>Tire Code</th><th>Description</th><th>Quantity</th></tr>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td><input type='checkbox' name='selected_ids[]' value='{$row['id']}' checked></td>";
                echo "<td>" . htmlspecialchars($row['serial_number']) . "</td>";
               
                echo "<td>" . htmlspecialchars($row['tyre_code']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . htmlspecialchars($row['qty']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<div class='form-group form-actions'>";
            echo "<label>Move selected to:</label>";
            echo "<select name='move_to'>";
            echo "<option value='non_moving' selected>Non-Moving Tire (Default)</option>";
            echo "<option value='over_age'>Over Age</option>";
            echo "</select>";
            echo "<button type='submit'>Move Selected</button>";
            echo "</div>";
            echo "</form>";
        }

        echo $message;

        $conn->close();
        ?>
    </div>
</body>
</html>