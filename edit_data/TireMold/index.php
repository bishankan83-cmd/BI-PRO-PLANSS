<?php
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen"; 
$password = "Bishan@1919"; 
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = '';

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Get common form data
        $icode = isset($_POST['icode']) ? $_POST['icode'] : '';
        $mold_id = isset($_POST['mold_id']) ? $_POST['mold_id'] : '';
        $press_id = isset($_POST['press_id']) ? $_POST['press_id'] : '';
        $mold_name = isset($_POST['mold_name']) ? $_POST['mold_name'] : '';
        $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 0;
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $availability_date = isset($_POST['availability_date']) ? $_POST['availability_date'] : null;
        $mold_size = isset($_POST['mold_size']) ? $_POST['mold_size'] : '';
        $per_day = isset($_POST['per_day']) ? $_POST['per_day'] : 0;

        // Start transaction
        $conn->begin_transaction();

        try {
            switch ($action) {
                case 'insert':
                    // Insert into mold table
                    $sql1 = "INSERT INTO mold (mold_id, mold_name, quantity, is_available, availability_date) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt1 = $conn->prepare($sql1);
                    $stmt1->bind_param("ssiis", $mold_id, $mold_name, $quantity, $is_available, $availability_date);
                    $stmt1->execute();

                    // Insert into tire_mold table
                    $sql2 = "INSERT INTO tire_mold (icode, mold_id) VALUES (?, ?)";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("is", $icode, $mold_id);
                    $stmt2->execute();

                    // Insert into mold_list table
                    $sql3 = "INSERT INTO mold_list (icode, mold_size, mold_id, per_day) VALUES (?, ?, ?, ?)";
                    $stmt3 = $conn->prepare($sql3);
                    $stmt3->bind_param("issi", $icode, $mold_size, $mold_id, $per_day);
                    $stmt3->execute();

                    // Insert into mold_press table
                    if (!empty($press_id)) {
                        $sql4 = "INSERT INTO mold_press (mold_id, press_id) VALUES (?, ?)";
                        $stmt4 = $conn->prepare($sql4);
                        $stmt4->bind_param("si", $mold_id, $press_id);
                        $stmt4->execute();
                    }

                    $message = "Record inserted successfully!";
                    break;

                case 'update':
                    // Update mold table
                    $sql1 = "UPDATE mold SET mold_name=?, quantity=?, is_available=?, availability_date=? 
                            WHERE mold_id=?";
                    $stmt1 = $conn->prepare($sql1);
                    $stmt1->bind_param("siiss", $mold_name, $quantity, $is_available, $availability_date, $mold_id);
                    $stmt1->execute();

                    // Update tire_mold table
                    $sql2 = "UPDATE tire_mold SET icode=? WHERE mold_id=?";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("is", $icode, $mold_id);
                    $stmt2->execute();

                    // Update mold_list table
                    $sql3 = "UPDATE mold_list SET mold_size=?, per_day=? WHERE mold_id=? AND icode=?";
                    $stmt3 = $conn->prepare($sql3);
                    $stmt3->bind_param("sisi", $mold_size, $per_day, $mold_id, $icode);
                    $stmt3->execute();

                    // Update mold_press table
                    if (!empty($press_id)) {
                        // Check if record exists
                        $check_sql = "SELECT 1 FROM mold_press WHERE mold_id = ?";
                        $check_stmt = $conn->prepare($check_sql);
                        $check_stmt->bind_param("s", $mold_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();

                        if ($check_result->num_rows > 0) {
                            // Update existing record
                            $sql4 = "UPDATE mold_press SET press_id = ? WHERE mold_id = ?";
                            $stmt4 = $conn->prepare($sql4);
                            $stmt4->bind_param("is", $press_id, $mold_id);
                            $stmt4->execute();
                        } else {
                            // Insert new record
                            $sql4 = "INSERT INTO mold_press (mold_id, press_id) VALUES (?, ?)";
                            $stmt4 = $conn->prepare($sql4);
                            $stmt4->bind_param("si", $mold_id, $press_id);
                            $stmt4->execute();
                        }
                    }

                    $message = "Record updated successfully!";
                    break;

                case 'delete':
                    // Delete from mold_press table first (foreign key constraint)
                    $sql1 = "DELETE FROM mold_press WHERE mold_id = ?";
                    $stmt1 = $conn->prepare($sql1);
                    $stmt1->bind_param("s", $mold_id);
                    $stmt1->execute();

                    // Delete from mold_list table
                    $sql2 = "DELETE FROM mold_list WHERE mold_id = ?";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param("s", $mold_id);
                    $stmt2->execute();

                    // Delete from tire_mold table
                    $sql3 = "DELETE FROM tire_mold WHERE mold_id = ?";
                    $stmt3 = $conn->prepare($sql3);
                    $stmt3->bind_param("s", $mold_id);
                    $stmt3->execute();

                    // Delete from mold table
                    $sql4 = "DELETE FROM mold WHERE mold_id = ?";
                    $stmt4 = $conn->prepare($sql4);
                    $stmt4->bind_param("s", $mold_id);
                    $stmt4->execute();

                    $message = "Record deleted successfully!";
                    break;
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch existing records with filter
$filters = [];
$sql = "SELECT m.mold_id, m.mold_name, m.quantity, m.is_available, m.availability_date,
        tm.icode, ml.mold_size, ml.per_day, mp.press_id
        FROM mold m 
        LEFT JOIN tire_mold tm USING(mold_id)
        LEFT JOIN mold_list ml USING(mold_id)
        LEFT JOIN mold_press mp USING(mold_id)";

// Apply filters if selected
if (isset($_GET['filter_mold_id']) && $_GET['filter_mold_id'] != '') {
    $filters[] = "m.mold_id = '" . $_GET['filter_mold_id'] . "'";
}
if (isset($_GET['filter_icode']) && $_GET['filter_icode'] != '') {
    $filters[] = "tm.icode = '" . $_GET['filter_icode'] . "'";
}
if (isset($_GET['filter_press_id']) && $_GET['filter_press_id'] != '') {
    $filters[] = "mp.press_id = " . $_GET['filter_press_id'];
}
if (isset($_GET['filter_mold_name']) && $_GET['filter_mold_name'] != '') {
    $filters[] = "m.mold_name = '" . $_GET['filter_mold_name'] . "'";
}
if (isset($_GET['filter_mold_size']) && $_GET['filter_mold_size'] != '') {
    $filters[] = "ml.mold_size = '" . $_GET['filter_mold_size'] . "'";
}

if (count($filters) > 0) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

$sql .= " ORDER BY m.mold_id LIMIT 100";

$result = $conn->query($sql);
$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mold Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-group {
            margin: 20px 0;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin: 0 5px;
            transition: background-color 0.3s;
        }

        .btn-insert {
            background-color: rgb(246, 82, 6);
            color: white;
        }

        .btn-filter {
            background-color: rgb(9, 3, 0);
            color: white;
        }

        .btn-update {
            background-color: rgb(173, 171, 166);
            color: white;
        }

        .btn-delete {
            background-color: rgb(137, 11, 2);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: rgb(248, 106, 5);
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 0 2px;
        }

        .edit-btn {
            background-color: rgb(173, 171, 166);
            color: black;
        }

        .delete-btn {
            background-color: rgb(8, 0, 1);
            color: white;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group.col {
            flex: 1;
            padding: 5px;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 10px;
            }

            .form-group.col {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mold Management System</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-row">
                <div class="form-group col">
                    <label for="icode">Item Code:</label>
                    <input type="number" id="icode" name="icode" required>
                </div>

                <div class="form-group col">
                    <label for="mold_id">Mold ID:</label>
                    <input type="text" id="mold_id" name="mold_id" required>
                </div>

                <div class="form-group col">
                    <label for="press_id">Press ID:</label>
                    <input type="number" id="press_id" name="press_id">
                </div>
            </div>

            <div class="form-row"><div class="form-group col">
                    <label for="mold_name">Mold Name:</label>
                    <input type="text" id="mold_name" name="mold_name">
                </div>

                <div class="form-group col">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="0">
                </div>

                <div class="form-group col">
                    <label for="mold_size">Mold Size:</label>
                    <input type="text" id="mold_size" name="mold_size" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col">
                    <label for="is_available">Available:</label>
                    <input type="checkbox" id="is_available" name="is_available" checked>
                </div>

                <div class="form-group col">
                    <label for="availability_date">Availability Date:</label>
                    <input type="datetime-local" id="availability_date" name="availability_date">
                </div>

                <div class="form-group col">
                    <label for="per_day">Per Day:</label>
                    <input type="number" id="per_day" name="per_day" required>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" name="action" value="insert" class="btn btn-insert">
                    <i class="fas fa-plus"></i> Insert
                </button>
                <button type="submit" name="action" value="update" class="btn btn-update">
                    <i class="fas fa-edit"></i> Update
                </button>
                <button type="submit" name="action" value="delete" class="btn btn-delete">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </form>

        <div class="container">
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-row">
                    <div class="form-group col">
                        <label for="filter_mold_id">Mold ID:</label>
                        <select id="filter_mold_id" name="filter_mold_id">
                            <option value="">Select Mold ID</option>
                            <?php
                            $sql1 = "SELECT DISTINCT mold_id FROM mold ORDER BY mold_id";
                            $result1 = $conn->query($sql1);
                            while ($row = $result1->fetch_assoc()) {
                                $selected = (isset($_GET['filter_mold_id']) && $_GET['filter_mold_id'] == $row['mold_id']) ? 'selected' : '';
                                echo '<option value="' . $row['mold_id'] . '" ' . $selected . '>' . $row['mold_id'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group col">
                        <label for="filter_icode">Item Code:</label>
                        <select id="filter_icode" name="filter_icode">
                            <option value="">Select Item Code</option>
                            <?php
                            $sql2 = "SELECT DISTINCT icode FROM tire_mold ORDER BY icode";
                            $result2 = $conn->query($sql2);
                            while ($row = $result2->fetch_assoc()) {
                                $selected = (isset($_GET['filter_icode']) && $_GET['filter_icode'] == $row['icode']) ? 'selected' : '';
                                echo '<option value="' . $row['icode'] . '" ' . $selected . '>' . $row['icode'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group col">
                        <label for="filter_press_id">Press ID:</label>
                        <select id="filter_press_id" name="filter_press_id">
                            <option value="">Select Press ID</option>
                            <?php
                            $sql3 = "SELECT DISTINCT press_id FROM mold_press ORDER BY press_id";
                            $result3 = $conn->query($sql3);
                            while ($row = $result3->fetch_assoc()) {
                                $selected = (isset($_GET['filter_press_id']) && $_GET['filter_press_id'] == $row['press_id']) ? 'selected' : '';
                                echo '<option value="' . $row['press_id'] . '" ' . $selected . '>' . $row['press_id'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col">
                        <label for="filter_mold_name">Mold Name:</label>
                        <select id="filter_mold_name" name="filter_mold_name">
                            <option value="">Select Mold Name</option>
                            <?php
                            $sql4 = "SELECT DISTINCT mold_name FROM mold ORDER BY mold_name";
                            $result4 = $conn->query($sql4);
                            while ($row = $result4->fetch_assoc()) {
                                $selected = (isset($_GET['filter_mold_name']) && $_GET['filter_mold_name'] == $row['mold_name']) ? 'selected' : '';
                                echo '<option value="' . $row['mold_name'] . '" ' . $selected . '>' . $row['mold_name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group col">
                        <label for="filter_mold_size">Mold Size:</label>
                        <select id="filter_mold_size" name="filter_mold_size">
                            <option value="">Select Mold Size</option>
                            <?php
                            $sql5 = "SELECT DISTINCT mold_size FROM mold_list ORDER BY mold_size";
                            $result5 = $conn->query($sql5);
                            while ($row = $result5->fetch_assoc()) {
                                $selected = (isset($_GET['filter_mold_size']) && $_GET['filter_mold_size'] == $row['mold_size']) ? 'selected' : '';
                                echo '<option value="' . $row['mold_size'] . '" ' . $selected . '>' . $row['mold_size'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Mold ID</th>
                    <th>Item Code</th>
                    <th>Press ID</th>
                    <th>Mold Name</th>
                    <th>Quantity</th>
                    <th>Available</th>
                    <th>Availability Date</th>
                    <th>Mold Size</th>
                    <th>Per Day</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['mold_id']); ?></td>
                    <td><?php echo htmlspecialchars($record['icode']); ?></td>
                    <td><?php echo htmlspecialchars($record['press_id']); ?></td>
                    <td><?php echo htmlspecialchars($record['mold_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                    <td><?php echo $record['is_available'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo htmlspecialchars($record['availability_date']); ?></td>
                    <td><?php echo htmlspecialchars($record['mold_size']); ?></td>
                    <td><?php echo htmlspecialchars($record['per_day']); ?></td>
                    <td>
                        <button class="action-btn edit-btn" onclick='fillForm(<?php echo json_encode($record); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-btn" onclick="confirmDelete('<?php echo $record['mold_id']; ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function fillForm(record) {
        document.getElementById('icode').value = record.icode;
        document.getElementById('mold_id').value = record.mold_id;
        document.getElementById('press_id').value = record.press_id;
        document.getElementById('mold_name').value = record.mold_name;
        document.getElementById('quantity').value = record.quantity;
        document.getElementById('is_available').checked = record.is_available == 1;
        document.getElementById('availability_date').value = record.availability_date;
        document.getElementById('mold_size').value = record.mold_size;
        document.getElementById('per_day').value = record.per_day;
    }

    function confirmDelete(moldId) {
        if (confirm('Are you sure you want to delete this record?')) {
            document.getElementById('mold_id').value = moldId;
            const form = document.querySelector('form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'action';
            input.value = 'delete';
            form.appendChild(input);
            form.submit();
        }
    }
    </script>
</body>
</html>