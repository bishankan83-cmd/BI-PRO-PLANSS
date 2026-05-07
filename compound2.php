
<!DOCTYPE html>
<html>



<head>
<!-- Create a form for entering the date range -->
<form method="POST"  action="compound2_search.php">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" required>
    
    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" required>
    
    <input type="submit" value="Submit">
</form>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        .totals-row {
            font-weight: bold;
        }
    </style>
</head>
<body>


<?php
// Replace these variables with your database credentials
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT
            md.start_date,
            md.icode,
            SUM(md.id_count) AS total_id_count,
            bn.*
        FROM merged_data md
        JOIN bom_new bn ON md.icode = bn.icode
        GROUP BY md.start_date, md.icode
        ORDER BY md.start_date ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Initialize an associative array to store date-wise totals
    $dateTotals = [];

    // Output table headers
    echo "<table><tr><th>Date</th>";

    // Define the columns for which you want to calculate the total
    $numericColumns = [
        "B-ATS 15",
        "B-BNS 24",
        "BG-BLS 12",
        "CG - BS 901",
        "C - SMS 501",
        "C-ATS 20",
        "C-SMS 702",
        "T - TRS 102",
        "T-ATNM S",
        "T-ATS 30",
        "T-ATS 35",
        "T-KS 40",
        "T-TRNMS 402",
        "T-TRNMS 402G",
        "T-TRS 202",
    ];

    // Output table headers for numeric columns
    foreach ($numericColumns as $column) {
        echo "<th>$column</th>";
    }

    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        $date = $row["start_date"];
        $total_id_count = $row["total_id_count"];

        // Initialize date total if it doesn't exist
        if (!isset($dateTotals[$date])) {
            $dateTotals[$date] = array_fill_keys($numericColumns, 0);
        }

        // Calculate and accumulate the values for each numeric column
        foreach ($numericColumns as $column) {
            if (is_numeric($row[$column])) {
                $value = $row[$column] * $total_id_count;
                $dateTotals[$date][$column] += $value;
            }
        }
    }

    // Output date-wise totals
    foreach ($dateTotals as $date => $totals) {
        echo "<tr><td>$date</td>";
        foreach ($numericColumns as $column) {
            echo "<td>" . $totals[$column] . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No results found.";
}

// Close the database connection
$conn->close();
?>
</body>
</html>