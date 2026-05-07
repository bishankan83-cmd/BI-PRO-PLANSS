<style>       /* Your CSS styles */
        .container {
            margin: 0 auto;
            max-width: 1200px;
            padding: 20px;
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif; /* Use Cantarell as the default font */
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stock-table th,
        .stock-table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .stock-table th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
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
        }

        .search-form button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        body {
            background-color: #f0f0f0;
            font-family: 'Cantarell', sans-serif; /* Set the default font for the entire page */
            text-align: center;
        }

        h4 {
            color: #F28018;
            font-family: 'Cantarell', sans-serif; /* Apply the Cantarell font to the h4 element */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #000000;
            padding: 10px;
            text-align: left;
        }

        .table th {
            background-color: #F28018;
            color: #000000;
            font-weight: bold;
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
        .button {
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
        }

        
        /* Add a fixed position to the table header */
.table th {
    background-color: #F28018;
    color: #000000;
    font-family: 'Cantarell', sans-serif;
    font-weight: bold;
    position: sticky;
    top: 0;
    z-index: 100;
}

/* Add a background color and some padding to the header row */
.table .header {
    background-color: #F28018;
    padding: 10px;
}

/* Adjust the position of the body cells to make room for the fixed header */
.table td {
    padding-top: 30px; /* Adjust this value based on your header height */
}


/* Add a fixed position to the table header */
.table th {
    background-color: #F28018;
    color: #000000;
    font-family: 'Cantarell', sans-serif;
    font-weight: bold;
    position: sticky;
    top: 0;
    z-index: 100;
}

/* Add a background color and some padding to the header row */
.table .header {
    background-color: #F28018;
    padding: 10px;
}

/* Adjust the position of the body cells to make room for the fixed header */
.table td {
    padding-top: 30px; /* Adjust this value based on your header height */
}

/* Set the text-align property to right for the specified columns */
.table td:nth-child(9),
.table td:nth-child(10),
.table td:nth-child(11),
.table td:nth-child(12),
.table td:nth-child(13) {
    text-align: right;
}


    </style>
 


<?php

error_reporting(0);
ini_set('display_errors', 0);

// Replace these values with your actual database credentials
$servername = "localhost:3306";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT dpd.*, (dpd.Plan - dpd.AdditionalData) AS Loss, dpd.Remark, tt.description
        FROM daily_plan_data1_tem dpd
        LEFT JOIN tire_details tt ON dpd.Icode = tt.Icode
        ORDER BY dpd.id ASC";


$result = $conn->query($sql);

// Define an array of possible loss reasons
$lossReasons = array(
    "Planning Stop",
    "Black Tire Prodution",
    "Over Production",
    "Not Matching The Unloading time",
    "Power cut",
    "Press Break Down",
    "Mill Break Down",
    "N/A Steam",
    "Steam Pipe Break Down",
    "Mill Cutter Set Break Down",
    "Mill Chuck Break Down",
    "Weighing Scale Break Down",
    "Mold Repair / Damage",
    "Mold Cleaning",
    "Mould Heating Issue",
    "Mold Changing Delay",
    "Tire Building Delay",
    "Tire Delay For Line",
    "BAND DEFORM",
    "Poor loading / unloading",
    "N/A Compound",
    "N/A Steel Band/Wheel",
    "N/A Bead",
    "N/A Profile",
    "MANPOWER LOSS",
    "TIRE BUILDING ISSUE",
    "OTHERS",
    "QA Stop",
    "Enerpac breakdown",
    "N/A Enerpac",
    "Planning Issue"
   
);
$totalPlan = 0;
$totalActual = 0;


if ($result->num_rows > 0) {
    echo "<form action='#' method='post'>
            <table class='table'>
            <tr>
            <th>Press</th>
            <th>Icode</th>
            <th>Description</th>
            <th>Plan</th>
            <th>Actual</th>
            <th>Loss</th>
            <th>Loss Reason</th>
            <th>Remark</th>
        </tr>
        ";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['CavityName']}</td>
                    <td>{$row['Icode']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['Plan']}</td>
                    <td>{$row['AdditionalData']}</td>
                    <td>{$row['Loss']}</td>
                    <td>";

                    
                
                    // Display the Loss Reason dropdown only when the loss is not zero
                    if ($row['Loss'] != 0) {
                        echo "<select name='loss_reason[{$row['ID']}]'>
                                <option value=''>Select Reason</option>";
                        foreach ($lossReasons as $reason) {
                            // Check if the current option matches the one stored in the database
                            $selected = ($reason == $row['LossReason']) ? 'selected' : '';
                            echo "<option value='{$reason}' {$selected}>{$reason}</option>";
                        }
                        echo "</select>";
                    } else {
                        echo "N/A"; // Display N/A if Loss is zero
                    }
                
                    echo "</td>
                            <td>
                                <input type='text' name='remark[{$row['ID']}]' value='{$row['Remark']}' placeholder='Enter Remark'>
                            </td>
                        </tr>";
                        // Update sum variables
        $totalPlan += $row['Plan'];
        $totalActual += $row['AdditionalData'];
                }
 // Add a row to display the sums
 echo "<tr class='header'>
 <td colspan='3'>Total:</td>
 <td>{$totalPlan}</td>
 <td>{$totalActual}</td>
 <td></td>
 <td></td>
 <td></td>
</tr>";

    echo "</table>
        <input type='submit' class='button' name='submit' value='Submit'>
        </form>";

       // Add a blinking button to redirect to another PHP page
    echo "<style>
    @keyframes blink {
        0%, 50%, 100% {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    button.blinking {
        animation: blink 2s infinite;

       
            background-color: #000000;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            
            cursor: pointer;
            transition: background-color 0.3s; /* Add a smooth transition for the background color change */
    

        
            background-color: #333333; /* Change the background color on hover */
        

    }
  </style>";

echo "<form action='public/dashboard.php' method='get'>
    <button type='submit' class='blinking'>Update Data after click this button</button>
  </form>";
    if (isset($_POST['submit'])) {
        $selectedLossReasons = $_POST['loss_reason'];
        $userRemarks = $_POST['remark'];

        foreach ($selectedLossReasons as $id => $selectedReason) {
            // Check if Loss is not zero for the selected row
            $selectLossSql = "SELECT (Plan - AdditionalData) AS Loss FROM daily_plan_data1_tem WHERE ID = '{$id}'";
            $lossResult = $conn->query($selectLossSql);
            $lossRow = $lossResult->fetch_assoc();
            $loss = $lossRow['Loss'];

            if ($loss != 0) {
                $remark = isset($userRemarks[$id]) ? $userRemarks[$id] : '';
                $updateSql = "UPDATE daily_plan_data1_tem SET LossReason = '{$selectedReason}', Remark = '{$remark}' WHERE ID = '{$id}'";
                $conn->query($updateSql);
            }
        }

        echo "Loss reasons and remarks updated successfully!";
         // Redirect to another PHP page after the message
   
    }
} else {
    echo "0 results";
}

// Close connection
$conn->close();

?>