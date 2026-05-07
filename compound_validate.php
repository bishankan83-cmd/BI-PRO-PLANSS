




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
$sql = "DELETE FROM another_table_name2";

if ($conn->query($sql) === TRUE) {
   
} else {
    echo "Error deleting data: " . $conn->error;
}

// Close connection
$conn->close();
?>



<?php

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the search inputs from the form
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $batch = isset($_POST['batch']) ? $_POST['batch'] : null;
    $compound_name = isset($_POST['compound_name']) ? $_POST['compound_name'] : null;

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

    // Prepare base SQL statement
    $sql_select = "SELECT * FROM another_table_name1 WHERE 1";

    // Prepare parameters array for binding
    $params = array();

    // Prepare SQL statement based on individual search criteria
    if (!empty($date)) {
        $sql_select .= " AND inputDate = ?";
        $params[] = $date;
    }
    if (!empty($batch)) {
        $sql_select .= " AND batch = ?";
        $params[] = $batch;
    }
    if (!empty($compound_name)) {
        $sql_select .= " AND compound_name = ?";
        $params[] = $compound_name;
    }

    // Prepare and bind parameters
    $stmt_select = $conn->prepare($sql_select);
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Generate type string dynamically
        $stmt_select->bind_param($types, ...$params);
    }

    // Execute the query
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    // Check if there are any rows
    if ($result->num_rows > 0) {
        // Prepare SQL statement to insert data into another_table_name
        $sql_insert = "INSERT INTO another_table_name2 (id, inputDate, shift, compound_name, description, cstock, batch, pallet, created_at, weight, quality_approved, expire_date, staff_name, sg_value, hardness, mh, ml, t10, t90, rebound) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        // Bind parameters for insertion
        $stmt_insert->bind_param("isssssssssssssssssss", $id, $inputDate, $shift, $compound_name, $description, $cstock, $batch, $pallet, $created_at, $weight, $quality_approved, $expire_date, $staff_name, $sg_value, $hardness, $mh, $ml, $t10, $t90, $rebound);

        // Fetch data from result set and insert into another_table_name
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $inputDate = $row['inputDate'];
            $shift = $row['shift'];
            $compound_name = $row['compound_name'];
            $description = $row['description'];
            $cstock = $row['cstock'];
            $batch = $row['batch'];
            $pallet = $row['pallet'];
            $created_at = $row['created_at'];
            $weight = $row['weight'];
            $quality_approved = $row['quality_approved'];
            $expire_date = $row['expire_date'];
            $staff_name = $row['staff_name'];
            $sg_value = $row['sg_value'];
            $hardness = $row['hardness'];
            $mh = $row['mh'];
            $ml = $row['ml'];
            $t10 = $row['t10'];
            $t90 = $row['t90'];
            $rebound = $row['rebound'];

            // Execute insertion
            $stmt_insert->execute();
        }

        echo "Data inserted successfully into another_table_name.";

        // Redirect to another page
        header("Location: compound_validate2.php");
        exit(); 
    } else {
        echo "No data found for the given criteria.";
    }

    // Close statements and connection
    $stmt_select->close();
    $stmt_insert->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert Data on Button Click</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h2 {
            font-weight: bold;
            font-size: 24px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        .alert {
            background-color: #FFD700;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="date"],
        select,
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Enter Search Criteria</h2>
    <form id="searchForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div class="form-group">
            <label for="batch">Batch:</label>
            <select id="batch" name="batch">
                <option value="">Select Batch</option>
            </select>
        </div>
        <div class="form-group">
            <label for="compound_name">Compound Name:</label>
            <select id="compound_name" name="compound_name">
                <option value="">Select Compound Name</option>
            </select>
        </div>
        <input type="submit" value="Fetch Data">
    </form>
</div>


<script>
document.getElementById("date").addEventListener("change", function() {
    var selectedDate = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "get_compound_name2.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var compoundNames = JSON.parse(xhr.responseText);
            var compoundNameSelect = document.getElementById("compound_name");
            compoundNameSelect.innerHTML = "<option value=''>Select Compound Name</option>";
            compoundNames.forEach(function(compoundName) {
                var option = document.createElement("option");
                option.value = compoundName;
                option.text = compoundName;
                compoundNameSelect.appendChild(option);
            });
        }
    };
    xhr.send("date=" + selectedDate);
});


// Function to fetch and populate batch options based on selected date
function populateBatchOptions(selectedDate) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "get_batch2.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var batches = JSON.parse(xhr.responseText);
            var batchSelect = document.getElementById("batch");
            batchSelect.innerHTML = "<option value=''>Select Batch</option>";
            batches.forEach(function(batch) {
                var option = document.createElement("option");
                option.value = batch;
                option.text = batch;
                batchSelect.appendChild(option);
            });
        }
    };
    xhr.send("date=" + selectedDate);
}  
   
 
// Event listener for input date change
document.getElementById("date").addEventListener("change", function() {
    var selectedDate = this.value;
    // Call the function to populate batch options based on selected date
    populateBatchOptions(selectedDate);
});

</script>

</body>
</html>
