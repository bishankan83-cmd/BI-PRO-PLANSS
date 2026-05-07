
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
    padding: 15px;
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

table {
        border-collapse: collapse;
        width: 100%;
    }

    td {
        padding: 8px;
        text-align: left;
        border: none;
    }

    input[readonly] {
        border: none;
        background-color: transparent;
        width: 100%;
    }


    </style>


<?php
// Include your database connection code here
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function fetchAllData($conn) {
    $sql = "SELECT daily_plan_data1.*, tire_details.Description AS TireDescription
            FROM daily_plan_data1 
            LEFT JOIN tire_details ON daily_plan_data1.Icode = tire_details.icode
            ORDER BY daily_plan_data1.ID ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}


// Function to update data
function updateData($conn, $id, $data) {
    $updateQuery = "UPDATE daily_plan_data1 SET ";
    foreach ($data as $column => $value) {
        $updateQuery .= "$column = '$value', ";
    }
    $updateQuery = rtrim($updateQuery, ", ");
    $updateQuery .= " WHERE ID = $id";

    $conn->query($updateQuery);
}

// Function to generate options for LossReason dynamically
function generateLossReasonOptions($selectedValue) {
    $options = [
        "-",
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
    ];

    $html = '';

    foreach ($options as $option) {
        $selected = ($option === $selectedValue) ? 'selected' : '';
        $html .= "<option value=\"$option\" $selected>$option</option>";
    }

    return $html;
}

// Display and edit data
$data = fetchAllData($conn);


// Set update success flag
$updateSuccess = true;

$updateSuccess = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST["data"] as $id => $rowData) {
        updateData($conn, $id, $rowData);
    }

    // Refresh data after update
    $data = fetchAllData($conn);
    // Set update success flag
$updateSuccess = true;

}
?>

<!DOCTYPE html>
<html lang="en">
<head>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Plan Data</title>

    <script>
        function checkConfirmation() {
            var lossReasons = document.getElementsByName("data[][LossReason]");
            var remarks = document.getElementsByName("data[][Remark]");
            var confirmButton = document.getElementById("confirmButton");

            for (var i = 0; i < lossReasons.length; i++) {
                if (lossReasons[i].value !== "-" && remarks[i].value.trim() === "") {
                    confirmButton.disabled = true;
                    return;
                }
            }
            confirmButton.disabled = false;
        }
    </script>
</head>
<body>
    <h2>Daily Plan Data</h2>

    <?php if ($updateSuccess): ?>
        <p style="color: green;">Update successful!</p>
    <?php endif; ?>
    <form method="post" action="">
        <table class='table'>
            <tr>
               
                <th>Date</th>
                <th>Shift</th>
                <th>Press</th>
                <th>Icode</th>
                <th>Tire Description</th>
                <th>Plan</th>
                <th>Acctual</th>
                <th>LossReason</th>
                <th>Remark</th>
            </tr>
            <?php foreach ($data as $row): ?>
                <tr>
             
    
    <td><?= $row['Date'] ?></td>
    
    
    <td><?= $row['Shift'] ?></td>
    <td><input type="text" name="data[<?= $row['ID'] ?>][CavityName]" value="<?= $row['CavityName'] ?>"></td>
    <td><?= $row['Icode'] ?></td>
    <td><?= $row['TireDescription'] ?></td>
    <td><input type="text" name="data[<?= $row['ID'] ?>][Plan]" value="<?= $row['Plan'] ?>"></td>
    <td><input type="text" name="data[<?= $row['ID'] ?>][AdditionalData]" value="<?= $row['AdditionalData'] ?>"></td>
    <td>
        <select name="data[<?= $row['ID'] ?>][LossReason]">
            <?= generateLossReasonOptions($row['LossReason']) ?>
        </select>
    </td>
    <td><input type="text" name="data[<?= $row['ID'] ?>][Remark]" value="<?= $row['Remark'] ?>"></td>
</tr>
            <?php endforeach; ?>
        </table>
        <br>
        <input type="submit" value="Update All">
    </form>

     <!-- Display the total of plan and additional values -->
     <div>
        <h3>Total Plan: <?php echo calculateTotal($data, 'Plan'); ?></h3>
        <h3>Total Acctual: <?php echo calculateTotal($data, 'AdditionalData'); ?></h3>
    </div>
    <?php
    // Close connection
    $conn->close();

     // Function to calculate the total of a specific column
     function calculateTotal($data, $column) {
        $total = 0;
        foreach ($data as $row) {
            $total += intval($row[$column]);
        }
        return $total;
    }
    ?>
</body>
</html>

<div class="">
    <button onclick="window.location.href='check_confirm_daily.php'" style="background-color: #F28018; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
  border-radius: 8px;">Add New Tire</button>
</div>

<div class="">
    <button onclick="window.location.href='check_daily_production.php'">Confirm Data</button>
</div>
