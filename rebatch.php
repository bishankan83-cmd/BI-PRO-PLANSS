
<?php
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

// SQL to delete all data
$sql = "TRUNCATE TABLE target_table2";

if ($conn->query($sql) === TRUE) {
    echo "All data deleted successfully from target_table2.";
} else {
    echo "Error deleting data: " . $conn->error;
}

$conn->close();
?>





<?php
// Database connection parameters
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

// Initialize variables
$searchType = "either";
$serialNumber = "";
$batchNumber = "";
$searchResults = [];
$message = "";
$batches = []; // Array to store batch numbers for dropdown
$serialNumbers = []; // Array to store serial numbers for dropdown

// Handle AJAX requests for filtering dropdowns
if (isset($_GET['action']) && $_GET['action'] == 'get_batches') {
    $serialNumber = $_GET['serial_number'];
    $sql = "SELECT DISTINCT batch FROM another_table_name1 WHERE serial_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serialNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $batches = [];
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row['batch'];
    }
    echo json_encode($batches);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'get_serials') {
    $batchNumber = $_GET['batch_number'];
    $sql = "SELECT DISTINCT serial_number FROM another_table_name1 WHERE batch = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $batchNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $serials = [];
    while ($row = $result->fetch_assoc()) {
        $serials[] = $row['serial_number'];
    }
    echo json_encode($serials);
    exit;
}

// Get all unique batch numbers from the database for the dropdown
$batchSql = "SELECT DISTINCT batch FROM another_table_name1 ORDER BY batch";
$batchResult = $conn->query($batchSql);
if ($batchResult->num_rows > 0) {
    while ($batchRow = $batchResult->fetch_assoc()) {
        $batches[] = $batchRow['batch'];
    }
}

// Get all unique serial numbers from the database for the dropdown
$serialSql = "SELECT DISTINCT serial_number FROM another_table_name1 ORDER BY serial_number";
$serialResult = $conn->query($serialSql);
if ($serialResult->num_rows > 0) {
    while ($serialRow = $serialResult->fetch_assoc()) {
        $serialNumbers[] = $serialRow['serial_number'];
    }
}

// Process search form submission
if (isset($_POST['search'])) {
    $searchType = $_POST['search_type'];
    $serialNumber = isset($_POST['serial_number']) ? $_POST['serial_number'] : "";
    $batchNumber = isset($_POST['batch_number']) ? $_POST['batch_number'] : "";
    
    // Validate input
    if (empty($serialNumber) && empty($batchNumber)) {
        $message = "Please enter at least one search value.";
    } else {
        // Prepare and execute search query based on search type
        if ($searchType == "both" && !empty($serialNumber) && !empty($batchNumber)) {
            // Search by both serial number and batch
            $sql = "SELECT * FROM another_table_name1 WHERE serial_number = ? AND batch = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $serialNumber, $batchNumber);
        } elseif ($searchType == "either" && !empty($serialNumber) && !empty($batchNumber)) {
            // Search by either serial number or batch
            $sql = "SELECT * FROM another_table_name1 WHERE serial_number = ? OR batch = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $serialNumber, $batchNumber);
        } elseif (!empty($serialNumber)) {
            // Search by serial number only
            $sql = "SELECT * FROM another_table_name1 WHERE serial_number = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $serialNumber);
        } else {
            // Search by batch only
            $sql = "SELECT * FROM another_table_name1 WHERE batch = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $batchNumber);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch results
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $searchResults[] = $row;
            }
        } else {
            $message = "No results found.";
        }
        $stmt->close();
    }
}

// Process insert form submission
if (isset($_POST['insert'])) {
    $id = $_POST['id'];
    
    // Get data from source table
    $sql = "SELECT * FROM another_table_name1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Insert data into target table
        $insertSql = "INSERT INTO target_table2 (serial_number, inputDate, shift, compound_name, 
                     description, cstock, batch, pallet, weight, quality_approved, expire_date, 
                     staff_name, sg_value, hardness, mh, ml, t10, t90, rebound, T52) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param(
            "ssssssssdsssssssssss",
            $row['serial_number'],
            $row['inputDate'],
            $row['shift'],
            $row['compound_name'],
            $row['description'],
            $row['cstock'],
            $row['batch'],
            $row['pallet'],
            $row['weight'],
            $row['quality_approved'],
            $row['expire_date'],
            $row['staff_name'],
            $row['sg_value'],
            $row['hardness'],
            $row['mh'],
            $row['ml'],
            $row['t10'],
            $row['t90'],
            $row['rebound'],
            $row['T52']
        );
        
        if ($insertStmt->execute()) {
            // Set success message in session and redirect to success page
            session_start();
            $_SESSION['success_message'] = "Record successfully inserted into target_table2.";
            header("Location: rebatch2.php");
            exit();
        } else {
            $message = "Error inserting record: " . $insertStmt->error;
        }
        
        $insertStmt->close();
    } else {
        $message = "Source record not found.";
    }
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search and Insert Data</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        
        h1, h2, h3 {
            font-family: 'Cantarell', sans-serif;
        }
        
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }
        
        .stockr-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .stockr-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
            background-color: #ffffff;
        }
        
        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }
        
        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
            transition: background-color 0.3s;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }
        
        .button-container button:hover {
            background-color: #333333;
        }
        
        .search-form {
            margin: 10px 0 20px;
        }
        
        .search-form input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }
        
        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .search-form button:hover {
            background-color: #333333;
        }
        
        .stockr-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 12px 10px;
            text-transform: uppercase;
        }
        
        .stockr-table .header {
            background-color: #F28018;
            padding: 10px;
        }
        
        .select-container {
            margin: 15px 0;
        }
        
        select {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            width: 250px;
            background-color: #ffffff;
        }
        
        .card {
            border-radius: 15px;
            border: 2px solid #F28018;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            background-color: #ffffff;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #343a40;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border-bottom: 2px solid #F28018;
            padding: 15px;
            font-family: 'Cantarell', sans-serif;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-body p {
            font-size: 18px;
            margin: 10px 0;
        }
        
        .card-body strong {
            color: #F28018;
        }
        
        .highlight-message {
            font-size: 16px;
            color: red;
            background-color: Black;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            animation: blink 10s infinite; /* Blinking effect */
        }
        
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-family: 'Cantarell', sans-serif;
        }
        
        .page-title {
            text-align: center;
            color: #343a40;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: #F28018;
        }
        
        .action-btn {
            background-color: #000000;
            color: #FFFFFF;
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .action-btn:hover {
            background-color: #333333;
        }
    </style>
    <script>
        // Function to update batch dropdown based on selected serial number
        function updateBatchDropdown(serialNumber) {
            if (!serialNumber) {
                // If no serial number selected, load all batches
                loadAllBatches();
                return;
            }
            
            // AJAX request to get corresponding batches
            fetch('?action=get_batches&serial_number=' + encodeURIComponent(serialNumber))
                .then(response => response.json())
                .then(data => {
                    const batchDropdown = document.getElementById('batch_number');
                    batchDropdown.innerHTML = '<option value="">-- Select Batch Number --</option>';
                    
                    data.forEach(batch => {
                        const option = document.createElement('option');
                        option.value = batch;
                        option.textContent = batch;
                        batchDropdown.appendChild(option);
                    });
                    
                    // If only one batch is returned, select it automatically
                    if (data.length === 1) {
                        batchDropdown.value = data[0];
                    }
                })
                .catch(error => console.error('Error fetching batch numbers:', error));
        }
        
        // Function to update serial number dropdown based on selected batch
        function updateSerialDropdown(batchNumber) {
            if (!batchNumber) {
                // If no batch selected, load all serial numbers
                loadAllSerials();
                return;
            }
            
            // AJAX request to get corresponding serial numbers
            fetch('?action=get_serials&batch_number=' + encodeURIComponent(batchNumber))
                .then(response => response.json())
                .then(data => {
                    const serialDropdown = document.getElementById('serial_number');
                    serialDropdown.innerHTML = '<option value="">-- Select Job Number --</option>';
                    
                    data.forEach(serial => {
                        const option = document.createElement('option');
                        option.value = serial;
                        option.textContent = serial;
                        serialDropdown.appendChild(option);
                    });
                    
                    // If only one serial number is returned, select it automatically
                    if (data.length === 1) {
                        serialDropdown.value = data[0];
                    }
                })
                .catch(error => console.error('Error fetching serial numbers:', error));
        }
        
        // Function to load all batches (used when no serial is selected)
        function loadAllBatches() {
            const batchDropdown = document.getElementById('batch_number');
            batchDropdown.innerHTML = '<option value="">-- Select Batch Number --</option>';
            
            <?php foreach ($batches as $batch): ?>
                const option = document.createElement('option');
                option.value = <?php echo json_encode($batch); ?>;
                option.textContent = <?php echo json_encode($batch); ?>;
                batchDropdown.appendChild(option);
            <?php endforeach; ?>
        }
        
        // Function to load all serials (used when no batch is selected)
        function loadAllSerials() {
            const serialDropdown = document.getElementById('serial_number');
            serialDropdown.innerHTML = '<option value="">-- Select Job Number --</option>';
            
            <?php foreach ($serialNumbers as $serial): ?>
                const option = document.createElement('option');
                option.value = <?php echo json_encode($serial); ?>;
                option.textContent = <?php echo json_encode($serial); ?>;
                serialDropdown.appendChild(option);
            <?php endforeach; ?>
        }
        
        // Initialize dropdowns when page loads
        window.onload = function() {
            // Set initial selected values if they exist (from previous search)
            const serialDropdown = document.getElementById('serial_number');
            const batchDropdown = document.getElementById('batch_number');
            
            <?php if (!empty($serialNumber)): ?>
                serialDropdown.value = <?php echo json_encode($serialNumber); ?>;
                updateBatchDropdown(serialDropdown.value);
                <?php if (!empty($batchNumber)): ?>
                    setTimeout(() => {
                        batchDropdown.value = <?php echo json_encode($batchNumber); ?>;
                    }, 100);
                <?php endif; ?>
            <?php elseif (!empty($batchNumber)): ?>
                batchDropdown.value = <?php echo json_encode($batchNumber); ?>;
                updateSerialDropdown(batchDropdown.value);
            <?php endif; ?>
        };
    </script>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Search and Insert Data</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                Search Records
            </div>
            <div class="card-body">
                <form method="post" class="search-form">
                    <div class="form-group">
                        <label for="search_type">Search Type:</label>
                        <select name="search_type" id="search_type">
                            <option value="either" <?php echo $searchType == "either" ? "selected" : ""; ?>>Either Job Number OR Batch</option>
                            <option value="both" <?php echo $searchType == "both" ? "selected" : ""; ?>>Both Job Number AND Batch</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="serial_number">Job Number:</label>
                        <select name="serial_number" id="serial_number" onchange="updateBatchDropdown(this.value)">
                            <option value="">-- Select Job Number --</option>
                            <?php foreach ($serialNumbers as $serial): ?>
                                <option value="<?php echo htmlspecialchars($serial); ?>" <?php echo $serialNumber == $serial ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($serial); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="batch_number">Batch Number:</label>
                        <select name="batch_number" id="batch_number" onchange="updateSerialDropdown(this.value)">
                            <option value="">-- Select Batch Number --</option>
                            <?php foreach ($batches as $batch): ?>
                                <option value="<?php echo htmlspecialchars($batch); ?>" <?php echo $batchNumber == $batch ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($batch); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="button-container">
                        <button type="submit" name="search">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($searchResults)): ?>
            <div class="card">
                <div class="card-header">
                    Search Results
                </div>
                <div class="card-body">
                    <table class="stockr-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Job Number</th>
                                <th>Batch</th>
                                <th>Compound Name</th>
                                <th>Weight</th>
                                <th>Expire Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['batch']); ?></td>
                                    <td><?php echo htmlspecialchars($row['compound_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['weight']); ?></td>
                                    <td><?php echo htmlspecialchars($row['expire_date']); ?></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="insert" class="action-btn">Edit Data</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="highlight-message">
                Data found! Click "Insert to Target Table" to proceed with the selected record.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>