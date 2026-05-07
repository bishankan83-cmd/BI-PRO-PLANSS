<!DOCTYPE html>
<html>
<head>
    <title>Task Management Data</title>

    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        th:first-child, td:first-child {
            border-left: 1px solid #ddd;
        }

        th:last-child, td:last-child {
            border-right: 1px solid #ddd;
        }

        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
<form method="POST" action="compound_search.php">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" id="start_date" >
    <input type="submit" name="submit" value="Filter">
</form>

<?php
if (!empty($start_date)) {
    echo "<p>Showing data for Start Date: $start_date</p>";
}
?>
<table border="2">
    <thead>
        <tr>
        
            <th>Item</th>
            <th>icode</th>
            <th>t_size</th>
            <th>tobe</th>
            <th>Item Description</th>
            <th>B-ATS 15</th>
            <th>B-BNS 24</th>
            <th>BG-BLS 12</th>
            <th>CG - BS 901</th>
            <th>C - SMS 501</th>
            <th>C-ATS 20</th>
            <th>C-SMS 702</th>
            <th>T - TRS 102</th>
            <th>T-ATNM S</th>
            <th>T-ATS 30</th>
            <th>T-ATS 35</th>
            <th>T-KS 40</th>
            <th>T-TRNMS 402</th>
            <th>T-TRNMS 402G</th>
            <th>T-TRS 202</th>
            <th>Grand Totalcompound weight</th>
            <th>Green Tire weight</th>
            <th>Color</th>
            <th>Brand</th>
            <th>start_date</th>
            
        </tr>
    </thead>
    <tbody>

<?php

// Replace with your database connection details
$hostname = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a database connection
$connection = new mysqli($hostname, $username, $password, $database);

// Check if the connection was successful
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize the start_date variable
$start_date = "";

// Check if the form has been submitted
if (isset($_POST['submit'])) {
    // Get the start date entered by the user
    $start_date = $_POST['start_date'];
    
    // Define the SQL query with the start date filter
    $sqlQuery = "
        SELECT m.*,
               b.*
        FROM  merged_data AS b
        LEFT JOIN bom_new  AS m ON m.icode = b.icode
        WHERE start_date = '$start_date';
    ";
} else {
    // If the form has not been submitted, display all data
    $sqlQuery = "
        SELECT m.*,
               b.*
        FROM  merged_data AS b
        LEFT JOIN bom_new  AS m ON m.icode = b.icode;
    ";
}

// Execute the query
$result = $connection->query($sqlQuery);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $connection->error);
}

// Loop through the result set and display the data
while ($row = $result->fetch_assoc()) {
    echo "<tr>";

    // Output your table data here as before
    echo "<td>" . $row["Item"] . "</td>";
    echo "<td>" . $row["icode"] . "</td>";
    echo "<td>" . $row["t_size"] . "</td>";
    echo "<td>" . $row["id_count"] . "</td>";
    echo "<td>" . $row["Item Description"] . "</td>";
    
    // Multiply each numeric column by the "tobe" value
    $columnsToMultiply = ["B-ATS 15", "B-BNS 24", "BG-BLS 12", "CG - BS 901", "C - SMS 501", "C-ATS 20", "C-SMS 702", "T - TRS 102", "T-ATNM S", "T-ATS 30", "T-ATS 35", "T-KS 40", "T-TRNMS 402", "T-TRNMS 402G", "T-TRS 202", "Grand Totalcompound weight", "Green Tire weight"];
    
    foreach ($columnsToMultiply as $columnName) {
        $valueToMultiply = (float) $row[$columnName]; // Convert to float
        $tobeValue = (float) $row["id_count"]; // Convert to float
        $resultValue = $valueToMultiply * $tobeValue;
        echo "<td>" . $resultValue . "</td>";
    }
    
    echo "<td>" . $row["Color"] . "</td>";
    echo "<td>" . $row["Brand"] . "</td>";
    echo "<td>" . $row["start_date"] . "</td>";

    echo "</tr>";
}
