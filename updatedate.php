<!DOCTYPE html>
<html>
<head>
    <title>Work Order Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Your CSS styles */
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stock-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .button-container {
            text-align: left;
            margin: 10px;
            border-radius: 4px;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 40px;
        }

        .search-form {
            text-align: center;
            margin: 10px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            width: 200px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
        }

        .button-container button:hover {
            background-color: #333333; /* Change the background color on hover */
        }

        .stock-row td {
            font-family: 'Open Sans', sans-serif;
            font-weight: regular;
        }

        /* Add a fixed position to the table header */
        .stock-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Add a background color and some padding to the header row */
        .stock-table .header {
            background-color: #F28018;
            padding: 10px;
        }

        /* Adjust the position of the body cells to make room for the fixed header */
        .stock-table td {
            padding-top: 30px; /* Adjust this value based on your header height */
        }

        /* Style the select box */
        .select-container {
            margin: 10px;
            text-align: center;
        }

        select {
            padding: 10px;
            border: 1px solid #CCCCCC;
            border-radius: 4px;
            font-family: 'Cantarell', sans-serif;
            font-weight: regular;
        }
    </style>
</head>
<body>
    <h2>Work Order Management</h2>
    <?php
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $work_order_id = $_POST["work_order_id"];
        $new_date = $_POST["new_date"];
        $new_erp_value = $_POST["new_erp"];

        // Update data
        $sql = "UPDATE work_order SET datetime = '$new_date', erp = '$new_erp_value' WHERE id = $work_order_id";

        if ($conn->query($sql) === TRUE) {
            echo "Data updated successfully.";
        } else {
            echo "Error updating data: " . $conn->error;
        }
    }

     // Fetch data with JOIN operation and GROUP BY erp, ordered by datetime
$sql = "SELECT work_order.id, MAX(work_order.datetime) AS datetime, work_order.erp, MAX(worder.ref) AS ref
FROM work_order
JOIN worder ON work_order.erp = worder.erp
GROUP BY work_order.erp
ORDER BY MAX(work_order.datetime) DESC";  // Order by datetime in descending order
$result = $conn->query($sql);



     if ($result->num_rows > 0) {
         echo "<table>
             <tr>
                 <th>Datetime</th>
                 <th>ERP</th>
                 <th>Reference Number</th>
                 <th>Action</th>
             </tr>";
 
         while ($row = $result->fetch_assoc()) {
             echo "<tr>
                 <td>
                     <input type='datetime-local' id='date_" . $row["id"] . "' value='" . date("Y-m-d\TH:i", strtotime($row["datetime"])) . "'>
                 </td>
                 <td>
                     <input type='text' id='erp_" . $row["id"] . "' value='" . $row["erp"] . "'>
                 </td>
                 <td>
                     <input type='text' id='ref_number_" . $row["id"] . "' value='" . $row["ref"] . "'> <!-- Added line for reference number -->
                 </td>
                 <td>
                     <button onclick='updateData(" . $row["id"] . ")'>Save</button>
                 </td>
             </tr>";
         }
 
         echo "</table>";
     } else {
         echo "No results found.";
     }
 
     // Close connection
     $conn->close();
     ?>
 
     <script>
     function updateData(id) {
         var newDate = document.getElementById('date_' + id).value;
         var newERP = document.getElementById('erp_' + id).value;
         var newRefNumber = document.getElementById('ref_number_' + id).value; // Added line for reference number
 
         $.ajax({
             type: "POST",
             url: "updatedate.php",
             data: { work_order_id: id, new_date: newDate, new_erp: newERP },
             success: function() {
                 alert("Data updated successfully.");
             }
         });
     }

     function goToAnotherPage() {
        window.location.href = "import22.php"; // Replace with the actual PHP page URL
    }
     </script>

    <button onclick="goToAnotherPage()">Re-generate Plan</button>
 </body>
 </html>
