<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Page Title</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
    /* Add this style for highlighted rows */
    .highlighted-row {
        background-color: #FFFF99; /* Change the background color to your preferred highlight color */
    }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            top: 0;
            z-index: 100;

        }

        th {
            background-color: #F28018;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .custom-button {
    background-color: #000000;
    color: white;
    padding: 10px 15px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 20px;
    cursor: pointer;
    transition: background-color 0.3s ease; /* Adding transition for a smooth effect */
}

.custom-button:hover {
            background-color: #F28018;
   
}


        input[type="text"] {
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }

        select {
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            background-color: #000000;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
        }

        .hidden-td {
            display: none;
        }


        .highlighted-row {
        background-color: #FFFF00; /* Set your desired background color for highlighted rows */
        font-weight: bold; /* Set your desired font weight or any other styling */
    }

    .hidden-column {
    display: none;
}

    </style>
</head>
<body>



    <?php

    // Your PHP code here

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

    // SQL query to update cavity_name in the process table
    $sql = "UPDATE `process` p
            JOIN `cavity` c ON p.`cavity_id` = c.`cavity_id`
            SET p.`cavity_name` = c.`cavity_name`";

    if ($conn->query($sql) === TRUE) {
       // echo "Cavity names updated successfully";
    } else {
        echo "Error updating cavity names: " . $conn->error;
    }

    // Close the connection
    $conn->close();





    ?>

    <a href="plannew562new.php">
        <button class="custom-button">Generate Plan</button>
    </a>

<input type="text" id="icodeeSearch" placeholder="Search by Cavity Name" oninput="searchByCavityName()">



<input type="text" id="icodeSearch" placeholder="Search by Icode" oninput="searchByIcode()">

<input type="text" id="icodeDescriptionSearch" placeholder="Search by ICODE or Description" oninput="searchByIcodeDescription()">



<input type="text" id="moldNameSearch" placeholder="Search by Mold Name" oninput="searchByMoldName()">

<input type="text" id="refErpSearch" placeholder="Search by ERP or REF" oninput="searchByRefErp()">






<?php
// PHP code for handling updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $hostname = 'localhost';
    $username = 'planatir_task_managemen';
    $password = 'Bishan@1919';
    $database = 'planatir_task_managemen';

    $conn = new mysqli($hostname, $username, $password, $database);

    $id = $_POST['id'];
    $new_value = $_POST['new_value'];
    $column_name = $_POST['column_name'];

    if ($column_name === 'cavity_name') {
        // If updating cavity_name, also update cavity_id based on the selected cavity_name
        $getCavityIdQuery = "SELECT `cavity_id` FROM `cavity` WHERE `cavity_name` = ?";
        $getCavityIdStmt = $conn->prepare($getCavityIdQuery);
        $getCavityIdStmt->bind_param("s", $new_value);
        $getCavityIdStmt->execute();
        $cavityIdResult = $getCavityIdStmt->get_result();

        if ($cavityIdResult->num_rows > 0) {
            $cavityIdRow = $cavityIdResult->fetch_assoc();
            $cavity_id = $cavityIdRow['cavity_id'];

            $updateQuery = "UPDATE `process` SET `$column_name` = ?, `cavity_id` = ? WHERE `id` = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("sii", $new_value, $cavity_id, $id);
            $updateStmt->execute();
        }
    } else {
        // For other columns, update directly
        $updateQuery = "UPDATE `process` SET `$column_name` = ? WHERE `id` = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $new_value, $id);
        $updateStmt->execute();
    }

    $conn->close();

    echo json_encode(['success' => true]);
    exit;
}

// Function to get tire description by icode
function getTireDescriptionByIcode($conn, $icode) {
    $descriptionQuery = "SELECT `description` FROM `tire` WHERE `icode` = ?";
    $descriptionStmt = $conn->prepare($descriptionQuery);
    $descriptionStmt->bind_param("s", $icode);
    $descriptionStmt->execute();
    $descriptionResult = $descriptionStmt->get_result();

    if ($descriptionResult->num_rows > 0) {
        $descriptionRow = $descriptionResult->fetch_assoc();
        return $descriptionRow['description'];
    }

    return null;
}

// Function to get ref_no by erp
function getRefNoByErp($conn, $erp) {
    $refNoQuery = "SELECT `ref` FROM `worder` WHERE `erp` = ?";
    $refNoStmt = $conn->prepare($refNoQuery);
    $refNoStmt->bind_param("s", $erp);
    $refNoStmt->execute();
    $refNoResult = $refNoStmt->get_result();

    if ($refNoResult->num_rows > 0) {
        $refNoRow = $refNoResult->fetch_assoc();
        return $refNoRow['ref'];
    }

    return null;
}

function getTotalNewNumbersByIcode($conn, $icode) {
    $totalNewNumbersQuery = "SELECT SUM(`new`) AS total_new_numbers FROM `worder` WHERE `icode` = ?";
    $totalNewNumbersStmt = $conn->prepare($totalNewNumbersQuery);
    $totalNewNumbersStmt->bind_param("s", $icode);
    $totalNewNumbersStmt->execute();
    $totalNewNumbersResult = $totalNewNumbersStmt->get_result();

    if ($totalNewNumbersResult->num_rows > 0) {
        $totalNewNumbersRow = $totalNewNumbersResult->fetch_assoc();
        return $totalNewNumbersRow['total_new_numbers'];
    }

    return 0;
}


// Displaying Data
$conn = new mysqli("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

$query = "SELECT process.*, plannew1.end_date 
          FROM process 
          LEFT JOIN plannew1 ON process.id = plannew1.id
          WHERE process.is_highlighted = 1";

$result = $conn->query($query);


echo "<h2>Process Table</h2>";

echo "<table border='1' id='editableTable'>
      <tr>
          <th>ERP</th>
          <th>REF</th>
          <th>Is Highlighted</th>
          <th>Icode</th>
          <th>Description</th>
          <th>Total Requirement</th>
          <th>To be</th>
          <th>Tires per Mold</th>
          <th>Mold Name</th>
          <th>Cavity Name</th>
        
          
   
          <th>Is Completed</th>
      
          <th>Start Date</th>
          <th>End Date</th> <!-- Add this line -->
          
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr";
    // Add a class 'highlighted-row' to rows where is_highlighted is 1
    if ($row['is_highlighted'] == 1) {
        echo " class='highlighted-row'";
    }
    echo ">";
    echo "<td contenteditable='true' onBlur='saveData({$row['id']}, \"erp\", this)'>" . $row['erp'] . "</td>";

    // Add the following code to display the corresponding ref_no
$refNo = getRefNoByErp($conn, $row['erp']);
echo "<td>{$refNo}</td>";

    
   // Checkbox for Is Highlighted
   echo "<td><input type='checkbox' onchange='saveCheckboxData({$row['id']}, \"is_highlighted\", this)'";
   if ($row['is_highlighted'] == 1) {
       echo " checked";
   }
   echo "></td>";
    echo "<td contenteditable='true' onBlur='saveData({$row['id']}, \"icode\", this)'>" . $row['icode'] . "</td>";

    // Add the following code to display the corresponding tire description
$tireDescription = getTireDescriptionByIcode($conn, $row['icode']);
echo "<td>{$tireDescription}</td>";



$icode = $row['icode'];
$totalNewNumbers = getTotalNewNumbersByIcode($conn, $icode);
echo "<td>{$totalNewNumbers}</td>";

// Fetch and display the "tobe" value
$tobeQuery = "SELECT `tobe` FROM `tobeplan` WHERE `erp` = '{$row['erp']}' AND `icode` = '{$row['icode']}'";
$tobeResult = $conn->query($tobeQuery);

if ($tobeResult->num_rows > 0) {
    $tobeRow = $tobeResult->fetch_assoc();
    echo "<td>{$tobeRow['tobe']}</td>";
} else {
    echo "<td>No Data</td>"; // You can customize this message as needed
}




    echo "<td contenteditable='true' onBlur='saveData({$row['id']}, \"tires_per_mold\", this)'>" . $row['tires_per_mold'] . "</td>";
    echo "<td contenteditable='true' onBlur='saveData({$row['id']}, \"mold_id\", this)'>" . $row['mold_id'] . "</td>";

    echo "<td class='hidden-td' contenteditable='true' onBlur='saveData({$row['id']}, \"mold_id\", this)'>" . ($row['tires_per_mold'] == 0.00 ? "-" : $row['cavity_name']) . "</td>";

    
    echo "<td>";
    echo "<select onchange='saveData({$row['id']}, \"cavity_name\", this)'>";
    
    // Fetch cavity names from the cavity table
    $cavityQuery = "SELECT `cavity_name` FROM `cavity`";
    $cavityResult = $conn->query($cavityQuery);
    while ($cavityRow = $cavityResult->fetch_assoc()) {

         echo ">{$cavityRow['cavity_name']}</option>";
        echo "<option value='{$cavityRow['cavity_name']}'";
        if ($cavityRow['cavity_name'] == $row['cavity_name']) {
            echo " selected";
        }
        echo ">{$cavityRow['cavity_name']}</option>";
    }
    echo "</select>";
    echo "</td>";

    
 

      // Checkbox for Is Completed
      echo "<td><input type='checkbox' onchange='saveCheckboxData({$row['id']}, \"is_completed\", this)'";
      if ($row['is_completed'] == 1) {
          echo " checked";
      }
      echo "></td>";
    echo "<td contenteditable='true' onBlur='saveData({$row['id']}, \"start_date\", this)'>" . $row['start_date'] . "</td>";

    echo "<td>" . $row['end_date'] . "</td>";

    echo "<td><button onclick='goToAnotherPage()'>Date Update</button></td>";
    echo "<td><button onclick='goToAnotherPage1()'>Move Main </button></td>"; 

    echo "</tr>";
}


echo "</table>";



// JavaScript function to navigate to another page
echo "<script>
        function goToAnotherPage() {
            // Specify the URL of the page you want to navigate to
            var newPageUrl = 'planewd2.php';

            // Navigate to the new page
            window.location.href = newPageUrl;
        }
      </script>";

      // JavaScript function to navigate to another page
echo "<script>
function goToAnotherPage1() {
    // Specify the URL of the page you want to navigate to
    var newPageUrl = 'planewd2.php';

    // Navigate to the new page
    window.location.href = newPageUrl;
}
</script>";

$conn->close();
?>

<script>





function searchByCavityName() {
    const searchValue = document.getElementById('icodeeSearch').value.toLowerCase();

    const table = document.getElementById('editableTable');
    const rows = table.getElementsByTagName('tr');

    // Iterate through each row and hide/show based on the search value
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const cavityNameCell = rows[i].getElementsByTagName('td')[9]; // Index 7 corresponds to the Cavity Name column
        const cavityNameText = cavityNameCell.textContent || cavityNameCell.innerText;

        if (cavityNameText.toLowerCase().includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}


function saveCheckboxData(id, column, checkbox) {
        const newValue = checkbox.checked ? 1 : 0;

        // Send the data to the server using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'plannew45new2.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Checkbox data updated successfully!');

                    // Add or remove the highlighted-row class based on the checkbox state
                    const row = checkbox.closest('tr');
                    if (newValue === 1) {
                        row.classList.add('highlighted-row');
                    } else {
                        row.classList.remove('highlighted-row');
                    }
                } else {
                    console.error('Error updating checkbox data.');
                }
            }
        };
        xhr.send(`action=updateCheckboxData&id=${id}&new_value=${newValue}&column_name=${column}`);
    }




function searchByIcode() {
    const searchValue = document.getElementById('icodeSearch').value.toLowerCase();

    const table = document.getElementById('editableTable');
    const rows = table.getElementsByTagName('tr');

    // Iterate through each row and hide/show based on the search value
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const icodeCell = rows[i].getElementsByTagName('td')[3]; // Index 3 corresponds to the Icode column
        const icodeText = icodeCell.textContent || icodeCell.innerText;

        if (icodeText.toLowerCase().includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}



    // JavaScript function to save edited data
    function saveData(id, column, element) {
        const newValue = element.value || element.innerText.trim();
        
        // Send the data to the server using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'plannew45new2.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log('Data updated successfully!');
                    
                } else {
                    console.error('Error updating data.');
                }
            }
        };
        xhr.send(`action=updateData&id=${id}&new_value=${encodeURIComponent(newValue)}&column_name=${column}`);
    }





// Fetch and update data every second
setInterval(fetchDataAndUpdate, 200000);




function searchByMoldName() {
    const searchValue = document.getElementById('moldNameSearch').value.toLowerCase();

    const table = document.getElementById('editableTable');
    const rows = table.getElementsByTagName('tr');

    // Iterate through each row and hide/show based on the search value
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const moldNameCell = rows[i].getElementsByTagName('td')[8]; // Index 6 corresponds to the Mold Name column
        const moldNameText = moldNameCell.textContent || moldNameCell.innerText;

        if (moldNameText.toLowerCase().includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}


function searchByREF() {
    const searchValue = document.getElementById('refSearch').value.toLowerCase();

    const table = document.getElementById('editableTable');
    const rows = table.getElementsByTagName('tr');

    // Iterate through each row and hide/show based on the search value
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const refCell = rows[i].getElementsByTagName('td')[1]; // Index 1 corresponds to the REF column
        const refText = refCell.textContent || refCell.innerText;

        if (refText.toLowerCase().includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}


function searchByIcodeDescription() {
    const searchValue = document.getElementById('icodeDescriptionSearch').value.toLowerCase();

    const table = document.getElementById('editableTable');
    const rows = table.getElementsByTagName('tr');

    // Iterate through each row and hide/show based on the search value
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const icodeCell = rows[i].getElementsByTagName('td')[3]; // Index 3 corresponds to the Icode column
        const descriptionCell = rows[i].getElementsByTagName('td')[4]; // Index 4 corresponds to the Description column

        const icodeText = icodeCell.textContent || icodeCell.innerText;
        const descriptionText = descriptionCell.textContent || descriptionCell.innerText;

        if (icodeText.toLowerCase().includes(searchValue) || descriptionText.toLowerCase().includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}


function searchByRefErp() {
    const searchValue = document.getElementById('refErpSearch').value.toLowerCase();

    const table = document.getElementById('editableTable');
    const rows = table.getElementsByTagName('tr');

    // Iterate through each row and hide/show based on the search value
    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
        const erpCell = rows[i].getElementsByTagName('td')[0]; // Index 0 corresponds to the ERP column
        const refCell = rows[i].getElementsByTagName('td')[1]; // Index 1 corresponds to the REF column

        const erpText = erpCell.textContent || erpCell.innerText;
        const refText = refCell.textContent || refCell.innerText;

        if (erpText.toLowerCase().includes(searchValue) || refText.toLowerCase().includes(searchValue)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}



</script>

<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);


// SQL query to select rows from the 'process' table where 'is_highlighted' is 1, ordered by 'icode' and 'id'
$sqlSelect = "SELECT * FROM process WHERE is_highlighted = 1 ";

$result = $conn->query($sqlSelect);

$current_icode = null; // Variable to keep track of the current 'icode' value
$counter = 0; // Counter for numbering rows within each group

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];

        // Check if 'icode' value has changed
        if ($icode != $current_icode) {
            // Display a header for the new group
          
            $current_icode = $icode;

            // Reset the counter for the new group
            $counter = 0;
        }

        // Increment the counter and display it
        $counter++;
        // Update the 'serial' column in the database
        $serial = $counter;
        $updateSql = "UPDATE process SET serial = $serial WHERE id = {$row['id']}";
        $conn->query($updateSql);
    }
} else {
    echo "0 results";
}

// Close the database connection
$conn->close();