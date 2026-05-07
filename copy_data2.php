<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $erp_number = $_POST["erp_number"];

    $host = "localhost";
    $username = "planatir_task_managemen";
    $password = "Bishan@1919";
    $database = "planatir_task_managemen";

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 1. Select data from "worder" based on the "erp_number"
    $selectQuery = "SELECT * FROM worder WHERE erp = ?";
    $stmt = $conn->prepare($selectQuery);
    $stmt->bind_param("s", $erp_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 2. Insert the data into "dwork" table
        $insertQuery = "INSERT INTO dwork (date, Customer, wono, ref, erp, icode, t_size, brand, col, fit, rim, cons, fweight, ptv, new, cbm, kgs, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);

        while ($row = $result->fetch_assoc()) {
            // Modify the bind_param statement to match the number of columns
            $insertStmt->bind_param("ssssssssssssssssss", $row["date"], $row["Customer"], $row["wono"], $row["ref"], $row["erp"], $row["icode"], $row["t_size"], $row["brand"], $row["col"], $row["fit"], $row["rim"], $row["cons"], $row["fweight"], $row["ptv"], $row["new"], $row["cbm"], $row["kgs"], $row["new"]);
            $insertStmt->execute();
        }

        $insertStmt->close();

        // 3. Delete the corresponding records from "worder" table
        $deleteQuery = "DELETE FROM worder WHERE erp = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("s", $erp_number);
        $deleteStmt->execute();
        $deleteStmt->close();

         // Delete the corresponding records from "work_order" table
         $deleteQuery = "DELETE FROM work_order WHERE erp = ?";
         $deleteStmt = $conn->prepare($deleteQuery);
         $deleteStmt->bind_param("s", $erp_number);
 
         if ($deleteStmt->execute()) {
             echo "Data for ERP Number $erp_number has been deleted from 'work_order' successfully.";
         } else {
             echo "Error deleting data from 'work_order'.";
         }
 

           // Delete corresponding records from "plan_new" table
        $deleteQueryPlanNew = "DELETE FROM plannew WHERE erp = ?";
        $deleteStmtPlanNew = $conn->prepare($deleteQueryPlanNew);
        $deleteStmtPlanNew->bind_param("s", $erp_number);

        // Delete corresponding records from "tobeplan" table
        $deleteQueryToBePlan = "DELETE FROM tobeplan1 WHERE erp = ?";
        $deleteStmtToBePlan = $conn->prepare($deleteQueryToBePlan);
        $deleteStmtToBePlan->bind_param("s", $erp_number);

        // Execute both delete statements
        $resultPlanNew = $deleteStmtPlanNew->execute();
        $resultToBePlan = $deleteStmtToBePlan->execute();


    }


    $stmt->close();
    $conn->close();

    // 4. Redirect back to the search results page or a confirmation page
    header("Location: dwork.php");
    exit();
}
?>
