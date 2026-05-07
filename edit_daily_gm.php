<!DOCTYPE html>
<html>

<head>
    <style>
        /* Your existing CSS styles */
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
    </style>
</head>

<body>

<div class="button-container">
    <button>
        <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a>
    </button>
</div>

<!-- Form for input fields -->
<div class="container">
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

    // Handle form submissions for editing and updating
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["edit_id"])) {
            // Editing mode - display the form with pre-filled values
            $editId = $_POST["edit_id"];
            $editDate = $_POST["edit_date"];

            echo "<form method='post' action='{$_SERVER['PHP_SELF']}'>";
            echo "<input type='hidden' name='update_id' value='$editId'>";
            echo "<label for='edit_date'>Edit Date:</label>";
            echo "<input type='date' name='edit_date' id='edit_date' value='$editDate' required>";
            // Add other input fields for editing

            echo "<input type='submit' value='Update Data'>";
            echo "</form>";
        } elseif (isset($_POST["update_id"])) {
            // Update the data in the database
            $updateId = $_POST["update_id"];
            $updateDate = $_POST["edit_date"];
            // Add other fields as needed

            $updateSql = "UPDATE daily_plan_data SET Date='$updateDate' WHERE ID='$updateId'";
            // Add other fields in the update query

            if ($conn->query($updateSql) === TRUE) {
                echo "Record updated successfully";
            } else {
                echo "Error updating record: " . $conn->error;
            }
        }
    }

    // Fetch and display the data
    $sql = "SELECT * FROM daily_plan_data";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Date</th><th>Shift</th><th>Icode</th><th>Plan</th><th>Actual</th>LossReason<th></th><th>Remark</th><th>Edit</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["Date"] . "</td>";
            echo "<td>" . $row["Shift"] . "</td>";
            echo "<td>" . $row["Icode"] . "</td>";
            echo "<td>" . $row["Plan"] . "</td>";
            echo "<td>" . $row["AdditionalData"] . "</td>";
            echo "<td>" . $row["LossReason"] . "</td>";
            echo "<td>" . $row["Remark"] . "</td>";
            echo "<td><form method='post' action='{$_SERVER['PHP_SELF']}'>";
            echo "<input type='hidden' name='edit_id' value='{$row['ID']}'>";
            echo "<input type='hidden' name='edit_date' value='{$row['Date']}'>";
            echo "<input type='submit' value='Edit'>";
            echo "</form></td>";
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

</body>
</html>
