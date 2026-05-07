<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, 3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper function to safely escape HTML
function safe_html($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper function to safely format numbers
function safe_number($value, $decimals = 0) {
    return number_format($value ?? 0, $decimals, '.', '');
}

// Check if export button was clicked
if(isset($_POST['export'])) {
    // Set headers for Excel CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="results_summary_export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create file pointer connected to output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add headers to CSV
    fputcsv($output, array(
        'Icode',
        'Description',
        'Brand',
        'Production Qty',
        'Green Tire Weight'
    ));

    // Get the filtered SQL query
    $sql = "SELECT * FROM results_summary WHERE 1=1";
    
    if (!empty($_POST['icode'])) {
        $icodeFilter = $conn->real_escape_string($_POST['icode']);
        $sql .= " AND Icode LIKE '%$icodeFilter%'";
    }

    if (!empty($_POST['description'])) {
        $descriptionFilter = $conn->real_escape_string($_POST['description']);
        $sql .= " AND Description LIKE '%$descriptionFilter%'";
    }

    if (!empty($_POST['brand'])) {
        $brandFilter = $conn->real_escape_string($_POST['brand']);
        $sql .= " AND Brand LIKE '%$brandFilter%'";
    }

    // Execute the query
    $result = $conn->query($sql);

    // Output each row to CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['Icode'] ?? '',
            $row['Description'] ?? '',
            $row['Brand'] ?? '',
            (int)($row['TotalAdditionalData'] ?? 0),
            safe_number($row['CalculatedValue'] ?? 0, 2)
        ));
    }

    // Close output stream
    fclose($output);
    exit();
}

// Initialize filter variables
$icodeFilter = isset($_GET['icode']) ? $_GET['icode'] : '';
$descriptionFilter = isset($_GET['description']) ? $_GET['description'] : '';
$brandFilter = isset($_GET['brand']) ? $_GET['brand'] : '';

// Fetch distinct values for select options
$icodeOptions = $conn->query("SELECT DISTINCT Icode FROM results_summary WHERE Icode IS NOT NULL");
$descriptionOptions = $conn->query("SELECT DISTINCT Description FROM results_summary WHERE Description IS NOT NULL");
$brandOptions = $conn->query("SELECT DISTINCT Brand FROM results_summary WHERE Brand IS NOT NULL");

// SQL query with filtering
$sql = "SELECT * FROM results_summary WHERE 1=1";

if (!empty($icodeFilter)) {
    $icodeFilter = $conn->real_escape_string($icodeFilter);
    $sql .= " AND Icode LIKE '%$icodeFilter%'";
}

if (!empty($descriptionFilter)) {
    $descriptionFilter = $conn->real_escape_string($descriptionFilter);
    $sql .= " AND Description LIKE '%$descriptionFilter%'";
}

if (!empty($brandFilter)) {
    $brandFilter = $conn->real_escape_string($brandFilter);
    $sql .= " AND Brand LIKE '%$brandFilter%'";
}

// Query to calculate the sum totals
$sumSql = "SELECT SUM(TotalAdditionalData) AS TotalAdditionalDataSum, SUM(CalculatedValue) AS CalculatedValueSum FROM results_summary WHERE 1=1";

if (!empty($icodeFilter)) {
    $sumSql .= " AND Icode LIKE '%$icodeFilter%'";
}

if (!empty($descriptionFilter)) {
    $sumSql .= " AND Description LIKE '%$descriptionFilter%'";
}

if (!empty($brandFilter)) {
    $sumSql .= " AND Brand LIKE '%$brandFilter%'";
}

// Execute sum query
$sumResult = $conn->query($sumSql);
$sumRow = $sumResult->fetch_assoc();

$totalAdditionalData = $sumRow['TotalAdditionalDataSum'] ?? 0;
$totalCalculatedValue = $sumRow['CalculatedValueSum'] ?? 0;

?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Results Summary</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            text-align: center;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
        }

        h3 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #F28018;
            font-size: 28px;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        h6 {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
            color: #000000;
            font-size: 12px;
        }

        h5 {
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            color: #000000;
            font-size: 15px;
            padding: 5px;
            background-color: rgba(0,0,0,0.05);
            animation: blink 1s infinite;
        }

        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 20px 0;
            padding: 20px;
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(242,128,24,0.1);
            transition: transform 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-label {
            color: #F28018;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .filter-section {
            background: linear-gradient(145deg, #F28018, #ff9642);
            padding: 20px;
            border-radius: 15px;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(242,128,24,0.2);
        }

        .filter-title {
            color: white;
            font-size: 20px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filter-form {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        label {
            color: white;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: white;
            min-width: 200px;
            font-family: 'Cantarell', sans-serif;
            cursor: pointer;
        }

        select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(255,255,255,0.5);
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }

        .top-button, .export-button {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }

        .top-button {
            background-color: #000000;
            color: white;
        }

        .export-button {
            background-color: #4CAF50;
            color: white;
        }

        .top-button:hover, .export-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .production-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .production-table th {
            background: #F28018;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }

        .production-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .production-table tr:last-child td {
            border-bottom: none;
        }

        .production-table tr:hover td {
            background-color: #f8f8f8;
        }

        .blinking-text {
            animation: blink 1s infinite;
            color: #F28018;
            font-weight: bold;
            padding: 20px;
            background: rgba(242,128,24,0.1);
            border-radius: 10px;
            display: inline-block;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @media (max-width: 768px) {
            .summary-stats {
                flex-direction: column;
                gap: 20px;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            select {
                width: 100%;
            }

            .button-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <h3>Production Summary</h3>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">TOTAL Production</div>
            <div class="stat-value"><?php echo safe_number($totalAdditionalData, 2); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Green Tire Weight</div>
            <div class="stat-value"><?php echo safe_number($totalCalculatedValue, 2); ?></div>
        </div>
    </div>

    <div class="filter-section">
        <div class="filter-title">Filter Results</div>
        <form method='get' action='' class="filter-form">
            <div class="filter-group">
                <label for='icode'>Icode:</label>
                <select id='icode' name='icode'>
                    <option value=''>--Select Icode--</option>
                    <?php
                    if ($icodeOptions) {
                        while ($row = $icodeOptions->fetch_assoc()) {
                            $selected = ($row['Icode'] == $icodeFilter) ? 'selected' : '';
                            echo "<option value='" . safe_html($row['Icode']) . "' $selected>" . safe_html($row['Icode']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for='description'>Description:</label>
                <select id='description' name='description'>
                    <option value=''>--Select Description--</option>
                    <?php
                    if ($descriptionOptions) {
                        while ($row = $descriptionOptions->fetch_assoc()) {
                            $selected = ($row['Description'] == $descriptionFilter) ? 'selected' : '';
                            echo "<option value='" . safe_html($row['Description']) . "' $selected>" . safe_html($row['Description']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for='brand'>Brand:</label>
                <select id='brand' name='brand'>
                    <option value=''>--Select Brand--</option>
                    <?php
                    if ($brandOptions) {
                        while ($row = $brandOptions->fetch_assoc()) {
                            $selected = ($row['Brand'] == $brandFilter) ? 'selected' : '';
                            echo "<option value='" . safe_html($row['Brand']) . "' $selected>" . safe_html($row['Brand']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="button-container">
                <input type='submit' value='Apply Filters' class='top-button'>
            </div>
        </form>
    </div>

    <form method="post" class="button-container">
        <input type="hidden" name="icode" value="<?php echo safe_html($icodeFilter); ?>">
        <input type="hidden" name="description" value="<?php echo safe_html($descriptionFilter); ?>">
        <input type="hidden" name="brand" value="<?php echo safe_html($brandFilter); ?>">
        <button type="submit" name="export" class="export-button">Export to Excel</button>
    </form>

    <?php
    // Execute the main query for filtered results
    $result = $conn->query($sql);

    // Check if there are results
    if ($result && $result->num_rows > 0) {
        // Display the results
        echo "<table class='production-table'>
                <tr>
                    <th>Icode</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Production Qty</th>
                    <th>Green Tire Weight</th>
                </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . safe_html($row['Icode']) . "</td>
                    <td>" . safe_html($row['Description']) . "</td>
                    <td>" . safe_html($row['Brand']) . "</td>
                    <td>" . safe_html((int)($row['TotalAdditionalData'] ?? 0)) . "</td>
                    <td>" . safe_html(safe_number($row['CalculatedValue'], 2)) . "</td>
                  </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='blinking-text'>No records found.</p>";
    }

    $conn->close();
    ?>
</body>
</html>