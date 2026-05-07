<!DOCTYPE html>
<html>
<head>
    <title>Matching Mold, Cavity, Press, and Cavity Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th {
            padding: 10px;
            text-align: left;
            background-color: #007BFF; /* Blue background color */
            color: white; /* White text color */
        }

        td {
            padding: 10px;
            text-align: left;
        }

        /* Style for the button */
        #gotoButton {
            background-color: #007BFF; /* Blue background color */
            color: #fff; /* White text color */
            padding: 10px 20px; /* Padding for the button */
            border: none; /* Remove button border */
            cursor: pointer; /* Add a pointer cursor on hover */
            border-radius: 5px; /* Rounded corners */
        }

        /* Hover effect for the button */
        #gotoButton:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
    </style>
 
    </style>
    
</head>
<body>

<div class="container">
    <h1>First Start Mold List</h1>
<body>
<div style="text-align: center;">
        <button id="gotoButton">View Mold Changing List</button>
    </div>

    <script>
        // JavaScript code to handle button click event
        document.getElementById("gotoButton").addEventListener("click", function() {
            // Navigate to another page
            window.location.href = "match3.php";
        });
    </script>
</body>
    <?php
    // Database connection parameters
    $servername = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $database = "planatir_task_managemen";

    // Create a connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
// SQL query to retrieve matching mold_ids, cavity_ids, pressids, their corresponding first_start_date, press_name, and mold_name
$sql = "
SELECT DISTINCT m1.mold_id, m1.cavity_id, m1.icode, pc.press_id, p.press_name, m1.first_start_date, c.cavity_name, m.mold_name, sp.description
FROM match_table m1
JOIN match_table m2 ON m1.cavity_id = m2.cavity_id
JOIN cavity c ON m1.cavity_id = c.cavity_id
JOIN press_cavity pc ON m1.cavity_id = pc.cavity_id
JOIN press p ON pc.press_id = p.press_id
JOIN mold m ON m1.mold_id = m.mold_id
JOIN selectpress sp ON m1.icode = sp.icode
WHERE m1.id != m2.id;
";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo"<br>";
    echo "<table>";
    echo "<tr><th>icode</th><th>Description</th><th>Press Name</th><th>Cavity Name</th><th>Mold Name</th><th>First Start Date</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["icode"] . "</td>";
        echo "<td>" . $row["description"] . "</td>";
        echo "<td>" . $row["press_name"] . "</td>";
        echo "<td>" . $row["cavity_name"] . "</td>";
       
        echo "<td>" . $row["mold_name"] . "</td>";
        echo "<td>" . $row["first_start_date"] . "</td>";
        // Display the description from selectpress table
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No matching mold_ids, cavity_ids, pressids, and first_start_dates found.";
}

// Close the database connection
$conn->close();
?>