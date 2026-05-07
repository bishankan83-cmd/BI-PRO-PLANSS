<?php
// Database connection
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

// Check if the form data is set
if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
    // Retrieve the start_date from the form
    $start_date = $_POST['start_date'];

    // Check if the date exists in the `new_process` table
    $check_date_sql = "SELECT COUNT(*) AS date_count FROM new_process WHERE DATE(start_date) = '$start_date'";
    $check_date_result = $conn->query($check_date_sql);
    $row = $check_date_result->fetch_assoc();

    if ($row['date_count'] > 0) {
        // Date exists, proceed with the main query
        $sql = "
            SELECT 
                np.id, 
                np.icode, 
                np.mold_id, 
                np.tires_per_mold, 
                np.cavity_id, 
                np.mold_name, 
                np.cavity_name, 
                pc.press_id AS matching_press_id, 
                p.press_name AS matching_press_name, 
                np.erp, 
                np.serial, 
                np.is_completed, 
                np.is_highlighted, 
                np.first_tobe, 
                DATE(np.start_date) AS start_date, 
                c.cavity_group_id AS matching_cavity_group_id, 
                DATE(c.availability_date) AS previous_date 
            FROM 
                new_process np
            LEFT JOIN 
                press_cavity pc ON np.cavity_id = pc.cavity_id
            LEFT JOIN 
                press p ON pc.press_id = p.press_id
            LEFT JOIN 
                cavity c ON pc.press_id = c.cavity_group_id AND np.cavity_id = c.cavity_id
            WHERE 
                DATE(c.availability_date) < '$start_date';
        ";

        // Execute the query
        $result = $conn->query($sql);

        // Check if any rows are returned
        if ($result->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>ICODE</th>
                        <th>Mold ID</th>
                        <th>Tires Per Mold</th>
                        <th>Cavity ID</th>
                        <th>Mold Name</th>
                        <th>Cavity Name</th>
                        <th>Matching Press ID</th>
                        <th>Matching Press Name</th>
                        <th>ERP</th>
                        <th>Serial</th>
                        <th>Is Completed</th>
                        <th>Is Highlighted</th>
                        <th>First Tobe</th>
                        <th>Start Date</th>
                        <th>Matching Cavity Group ID</th>
                        <th>Previous Date</th>
                    </tr>";

            // Fetch and display each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['icode']}</td>
                        <td>{$row['mold_id']}</td>
                        <td>{$row['tires_per_mold']}</td>
                        <td>{$row['cavity_id']}</td>
                        <td>{$row['mold_name']}</td>
                        <td>{$row['cavity_name']}</td>
                        <td>{$row['matching_press_id']}</td>
                        <td>{$row['matching_press_name']}</td>
                        <td>{$row['erp']}</td>
                        <td>{$row['serial']}</td>
                        <td>{$row['is_completed']}</td>
                        <td>{$row['is_highlighted']}</td>
                        <td>{$row['first_tobe']}</td>
                        <td>{$row['start_date']}</td>
                        <td>{$row['matching_cavity_group_id']}</td>
                        <td>{$row['previous_date']}</td>
                    </tr>";
            }

            echo "</table>";
        } else {
            echo "No records found for the given date.";
        }
    } else {
        // Date does not exist
        echo "The provided date does not exist in the `new_process` table.";
    }
} else {
    echo "Please provide a valid start date.";
}

// Close the connection
$conn->close();
?>
