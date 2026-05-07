<body>
    <div class="container">
        <h1>Delete Data</h1>

        <form method="post" action="">
            <input type="text" name="erp" placeholder="Enter ERP Number">
            <br>
            <input type="submit" name="delete" value="Delete Data">
        </form>

        <?php
        // Remaining PHP code goes here
        ?>
    </div>
</body>
</html>

<?php
// MySQL database credentials
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

// Check if the delete button is clicked and ERP number is provided
if (isset($_POST['delete']) && !empty($_POST['erp'])) {
    // Sanitize the input to prevent SQL injection
    $erpNumber = $conn->real_escape_string($_POST['erp']);

    // Start the deletion transaction
    $conn->begin_transaction();

    try {
        // SQL query to fetch the pressid, moldid, and cavityid associated with the ERP number
        $fetchSql = "SELECT press, mold, cavity FROM plannew WHERE erp = '$erpNumber'";

        // Execute the fetch query
        $result = $conn->query($fetchSql);

        // Check if the fetch query is successful
        if ($result) {
            // Fetch the rows from the result
            $rows = $result->fetch_all(MYSQLI_ASSOC);

            

          
            // Iterate over the fetched rows
            foreach ($rows as $row) {
                $pressid = $row['press'];
                $moldid = $row['mold'];
                $cavityid = $row['cavity'];

                $deletePlannewSql = "DELETE FROM plannew WHERE erp = '$erpNumber'";
                $conn->query($deletePlannewSql);

                // Update the availability_date of the pressid in the 'press' table
                $updatePressSql = "UPDATE press SET availability_date = NOW() WHERE press_id = '$pressid'";
                $conn->query($updatePressSql);

                // Update the availability_date of the moldid in the 'mold' table
                $updateMoldSql = "UPDATE mold SET availability_date = NOW() WHERE mold_id = '$moldid'";
                $conn->query($updateMoldSql);

                // Update the availability_date of the cavityid in the 'cavity' table
                $updateCavitySql = "UPDATE cavity SET availability_date =NOW() WHERE cavity_id = '$cavityid'";
                $conn->query($updateCavitySql);
            }

            // Commit the transaction if all queries are successful
            $conn->commit();

            echo "Data with ERP number $erpNumber deleted successfully from the 'plannew' table, and the availability dates in the 'press', 'mold', and 'cavity' tables have been updated.";
        } else {
            echo "Error fetching data: " . $conn->error;
        }

        // SQL query to delete data with the specified ERP number from the tobeplan table
        $deleteTobeplanSql = "DELETE FROM tobeplan WHERE erp = '$erpNumber'";

        // Execute the deletion query for the tobeplan table
        $conn->query($deleteTobeplanSql);

        // Commit the transaction if all queries are successful
        $conn->commit();

        echo "Data with ERP number $erpNumber deleted successfully from both the plannew table and tobeplan table. The availability dates in the press, mold, and cavity tables have been updated.";
    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $conn->rollback();

        echo "Error deleting data: " . $e->getMessage();
    }
}