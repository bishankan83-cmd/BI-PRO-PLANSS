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

// SQL query to delete all data from the table
$sql = "DELETE FROM results_summary";

// Execute the query
if ($conn->query($sql) === TRUE) {
    // Records deleted successfully
} else {
    // Handle the error
    die("Error deleting records: " . $conn->error);
}

// Close the connection
$conn->close();

?>





<!-- Link to return to the filter form -->
<p><a href="p_summery_filter.php">Return to Filter Form</a></p>

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

// Initialize variables
$start_date = '';
$end_date = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // SQL query to get the sum of AdditionalData, Description, stgreenweight, and Brand for each Icode
    $sql = "
        SELECT 
            d.Icode,
            t.Description,
            t.stgreenweight,
            t.Brand,
            SUM(CAST(d.AdditionalData AS UNSIGNED)) AS TotalAdditionalData
        FROM 
            daily_plan_data d
        JOIN 
            tire_details t ON d.Icode = t.Icode
        WHERE 
            d.Date BETWEEN '$start_date' AND '$end_date'
        GROUP BY 
            d.Icode, t.Description, t.stgreenweight, t.Brand
    ";

    // Execute the query
    $result = $conn->query($sql);

    // Initialize totals
    $grand_total = 0;
    $grand_calculated_total = 0;

    // Check if there are results
    if ($result === false) {
        echo "Error in SQL query: " . $conn->error;
    } elseif ($result->num_rows > 0) {
        // First pass: Calculate totals and insert data
        while ($row = $result->fetch_assoc()) {
            // Cast to appropriate numeric types
            $total_additional_data = (int)($row['TotalAdditionalData'] ?? 0);
            $stgreenweight = (float)($row['stgreenweight'] ?? 0);
            $calculated_value = $stgreenweight * $total_additional_data;

            // Accumulate totals
            $grand_total += $total_additional_data;
            $grand_calculated_total += $calculated_value;

            // Insert data into results_summary table
            $insert_sql = "
                INSERT INTO results_summary (Icode, Description, Brand, stgreenweight, TotalAdditionalData, CalculatedValue, DateFrom, DateTo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt) {
                $stmt->bind_param("sssdddds", $row['Icode'], $row['Description'], $row['Brand'], $stgreenweight, $total_additional_data, $calculated_value, $start_date, $end_date);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        }

        // Reset the result pointer to fetch data again
        $result->data_seek(0);

        // Display the totals at the top (currently commented out)
        // echo "<h3>Total Sum of Production: " . htmlspecialchars($grand_total) . "</h3>";
        // echo "<h3>Total Sum of Green Tire Weights: " . htmlspecialchars(number_format($grand_calculated_total, 2)) . "</h3>";

        // Second pass: Display data (if needed)
        while ($row = $result->fetch_assoc()) {
            $total_additional_data = (int)($row['TotalAdditionalData'] ?? 0);
            $stgreenweight = (float)($row['stgreenweight'] ?? 0);
            $calculated_value = $stgreenweight * $total_additional_data;
            
            // Add your display logic here if needed
        }

        echo "</table>";
    } else {
        echo "No results found for the given date range.";
    }
}

// Close the connection
$conn->close();

// Redirect to another page
header("Location: prodution_summery.php");
exit(); // Ensure no further code is executed
?>