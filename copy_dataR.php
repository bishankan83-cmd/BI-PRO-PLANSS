<?php
// Database connection details
$host = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if "erp_number" POST key exists (for data transfer between tables)
    if (isset($_POST["erp_number"])) {
        $erp_number = $_POST["erp_number"];

        // 1. Select data from "dwork2"
        $selectQuery = "SELECT * FROM dwork2 WHERE erp = ?";
        $stmt = $conn->prepare($selectQuery);
        $stmt->bind_param("s", $erp_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Prepare the insert query for "dworkr"
            $insertQuery = "INSERT INTO dworkr (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs, quantity) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);

            while ($row = $result->fetch_assoc()) {
                // Bind parameters
                $insertStmt->bind_param(
                    "ssssssssssssssssss",
                    $row["date"], $row["Customer"], $row["wono"], $row["ref"], $row["erp"], 
                    $row["icode"], $row["t_size"], $row["brand"], $row["col"], $row["fit"], 
                    $row["rim"], $row["cons"], $row["fweight"], $row["ptv"], $row["new"], 
                    $row["cbm"], $row["kgs"], $row["quantity"]
                );
                $insertStmt->execute();
            }
            $insertStmt->close();

            // 2. Delete corresponding records from "dwork2"
            $deleteQuery = "DELETE FROM dwork2 WHERE erp = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $erp_number);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Redirect to success page after successful insert and delete
            header("Location: dworkr.php");
            exit();  // Ensure no further code is executed after redirection
        } else {
            $message = "No records found for ERP number: $erp_number.";
        }

        $stmt->close();
    }

    // Handle work order submission
    if (isset($_POST["datetime"], $_POST["take_datetime"], $_POST["erp"])) {
        $datetime = $_POST["datetime"];
        $take_datetime = $_POST["take_datetime"];
        $erp = $_POST["erp"];

        // Insert into "work_order"
        $insertQuery = "INSERT INTO work_order (datetime, take_datetime, erp) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            $stmt->bind_param("sss", $datetime, $take_datetime, $erp);
            if ($stmt->execute()) {
                // Redirect to success page after successful insert
                header("Location: dworkr.php");
                exit();  // Ensure no further code is executed after redirection
            } else {
                $message = "Error inserting work order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error preparing work order query: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Order Management</title>
</head>
<body>
    <h1>Work Order Management</h1>

    <!-- Display message -->
    <?php if (!empty($message)) { ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php } ?>



    <hr>

    <!-- Form to add a new work order -->
    <form action="" method="POST">
        <h2>Add Work Order</h2>
        <label for="datetime">Work Order Date/Time:</label>
        <input type="datetime-local" id="datetime" name="datetime" required><br><br>

        <label for="take_datetime">Take Date/Time:</label>
        <input type="datetime-local" id="take_datetime" name="take_datetime" required><br><br>

        <label for="erp">ERP Number:</label>
        <input type="text" id="erp" name="erp" required><br><br>

        <button type="submit">Submit Work Order</button>
    </form>
</body>
</html>
