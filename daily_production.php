



<!DOCTYPE html>
<html>



   
<?php
// New options added
$newOptions = 2;
?>



<!-- Rest of the PHP page content -->


<head>
    <style>
        /* Your CSS styles */
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
        }

        td {
            font-family: 'Open Sans', sans-serif;
            font-weight: normal;
        }

        /* Style the form */
        form {
            text-align: center;
            margin: 10px;
        }

        label {
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }

        select,
        input[type="date"],
        input[type="text"] {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: normal;
        }

        input[type="submit"] {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #333333;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            /* Distribute space evenly between buttons */
        }

        button {
            background-color: black;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #F28018;
        }

        .export-button {
            background-color: #e74c3c;
        }
        .export-button1 {
            background-color: #e74c3c;
        }


        .export-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>

    <div class="button-container">
        <button>
            <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a>
        </button>


        <style>
    @keyframes blink {
        0% { opacity: 1; }
        50% { opacity: 0; }
        100% { opacity: 1; }
    }

    .blink-text {
        animation: blink 1s infinite;
    }

    .button {
        background-color: black; /* Green */
        border: none;
        color: white;
        padding: 15px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
    }
</style>



<button class="button">
    <a href="p_summery_filter.php" style="text-decoration: none; color: white;">
       Check Full Production Summary
    </a>
</button>

        <button>
            <a href="check_production.php" style="text-decoration: none; color:white;">Check Production Summery Date Range</a>
        </button>

       
        <button >
            <a href="compound_button.php" style="text-decoration: none; color: #FFFFFF;">Check Production Compound Wise</a>
        </button>


        
        <button class="export-button" >
            <a href="export_daily2.php" style="text-decoration: none; color: #FFFFFF;">Export production Excel date range</a>
        </button>

        
        <button class="export-button" >
            <a href="check_compound4.php" style="text-decoration: none; color: #FFFFFF;">Export compound wise Excel  date range</a>
        </button>

        <button class="export-button" >
            <a href="check_compound34_excel.php" style="text-decoration: none; color: #FFFFFF;">Export Excel Montly Wise Compound</a>
        </button>


        <button class="export-button" >
            <a href="export_daily_shift.php" style="text-decoration: none; color: #FFFFFF;">Export Excel Shift Wise</a>
        </button>

        <button class="export-button2" >
            <a href="get_ban.php" style="text-decoration: none; color: #FFFFFF;">Month Wise Band Summery</a>
        </button>



   <!-- 
<button class="export-button" onclick="exportToExcel()">
    Export to Excel Day Shift Vise
</button> 
-->


    </div>


    <!-- Form for input fields -->
    <div class="container">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="date">Date:</label>
            <input type="date" name="date" id="date" required>
            <label for="shift">Shift:</label>
            <select name="shift" id="shift">
                <option value="DAY A">DAY A</option>
                <option value="DAY B">DAY B</option>
                <option value="DAY C">DAY C</option>
                <option value="NIGHT A">NIGHT A</option>
                <option value="NIGHT B">NIGHT B</option>
                <option value="NIGHT C">NIGHT C</option>
            </select>
            <input type="submit" value="Retrieve Data">
        </form>


        <?php
// Database connection parameters
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

// Initialize sum
$totalAdditionalData = 0;

// Check if the user submitted the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST["date"];
    $shift = $_POST["shift"];
    // SQL query to select data based on Date and Shift
    $sql = "SELECT dpd.*, td.GreenWeight FROM daily_plan_data dpd
            INNER JOIN tire_details td ON dpd.Icode = td.Icode
            WHERE dpd.Date='$date' AND dpd.Shift='$shift'";
    $result = $conn->query($sql);

    // Calculate sum of AdditionalData
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $totalAdditionalData += floatval($row["AdditionalData"]);
        }
    }
} else {
    // If no form submission, display data for the current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');
    $sql = "SELECT * FROM daily_plan_data WHERE MONTH(Date) = '$currentMonth' AND YEAR(Date) = '$currentYear'";
    $result = $conn->query($sql);

    // Calculate sum of AdditionalData for all records
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $totalAdditionalData += floatval($row["AdditionalData"]);
        }
    }
}

// Display the sum of AdditionalData at the top of the page
echo "<div style='text-align:center; margin-bottom: 20px; background-color: #F28018; color: #ffffff; padding: 10px; border-radius: 4px;'>Total Actual Production on this month: " . $totalAdditionalData . "</div>";

// Reset result pointer to beginning
$result->data_seek(0);

// Display the table
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Date</th><th>Shift</th><th>Press</th><th>Icode</th><th>Description</th><th>GreenWeight</th><th>Total GreenWeight</th><th>Plan</th><th>Actual</th><th>LossReason</th><th>Remark</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Date"] . "</td>";
        echo "<td>" . $row["Shift"] . "</td>";
        echo "<td>" . $row["CavityName"] . "</td>";
        echo "<td>" . $row["Icode"] . "</td>";

        // Fetch description based on Icode from the tire table
        $icode = $row["Icode"];
        $description_query = "SELECT Description, GreenWeight FROM tire_details WHERE Icode='$icode'";
        $description_result = $conn->query($description_query);

        if ($description_result->num_rows > 0) {
            $description_row = $description_result->fetch_assoc();
            $description = $description_row["Description"];
            $greenWeight = floatval($description_row["GreenWeight"]); // Convert to float
            echo "<td>" . $description . "</td>";
            echo "<td>" . $greenWeight . "</td>";
        } else {
            echo "<td>No description available</td>";
            echo "<td>No GreenWeight available</td>";
        }

        // Convert $row["AdditionalData"] to float
        $additionalData = floatval($row["AdditionalData"]);

        // Calculate and display the product of GreenWeight and AdditionalData in a new column
        $totalGWeight = $greenWeight * $additionalData;
        echo "<td>" . $totalGWeight . "</td>";

        echo "<td>" . $row["Plan"] . "</td>";
        echo "<td>" . $row["AdditionalData"] . "</td>";
        echo "<td>" . $row["LossReason"] . "</td>";
        echo "<td>" . $row["Remark"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "0 results";
}

// Close the database connection
$conn->close();
?>



    </div>

    <script>
        function exportToExcel() {
            // Get the selected date and shift from the form
            var date = document.getElementById('date').value;
            var shift = document.getElementById('shift').value;

            // Redirect to a PHP script that exports data to Excel
            window.location.href = 'export_daily.php?date=' + encodeURIComponent(date) + '&shift=' + encodeURIComponent(shift);
        }
    </script>
</body>

</html>
