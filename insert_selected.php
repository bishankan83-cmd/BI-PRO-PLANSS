<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if selectedRows array is set and not empty
if (isset($_POST['selectedRows']) && !empty($_POST['selectedRows'])) {
    // Prepare an array to store selected SQs
    $selectedSQs = array_map('intval', $_POST['selectedRows']); // Ensure all values are integers

    // Escape SQ values for SQL query
    $escapedSQs = implode(',', $selectedSQs);

    // Insert selected rows into another table
    $sqlInsert = "INSERT INTO selected_stocks (SQ, SerialNumber, icode, Description, LocationNumber, Month, Year, Brand, Color)
                  SELECT SQ, SerialNumber, icode, Description, LocationNumber, Month, Year, Brand, Color
                  FROM stocks
                  WHERE SQ IN ($escapedSQs)";

    if (mysqli_query($conn, $sqlInsert)) {
       // echo "Selected rows successfully inserted into 'selected_stocks' table.";
    } else {
        //echo "Error inserting rows: " . mysqli_error($conn);
    }
} else {
    echo "No rows selected.";
}

// Close connection
mysqli_close($conn);
?>



<?php
// Database connection parameters
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if selectedRows array is set and not empty
if (isset($_POST['selectedRows']) && !empty($_POST['selectedRows'])) {
    // Prepare an array to store selected SQs
    $selectedSQs = array_map('intval', $_POST['selectedRows']); // Ensure all values are integers

    // Escape SQ values for SQL query
    $escapedSQs = implode(',', $selectedSQs);

    // Insert selected rows into another table
    $sqlInsert = "INSERT INTO selected_stocks2 (SQ, SerialNumber, icode, Description, LocationNumber, Month, Year, Brand, Color)
                  SELECT SQ, SerialNumber, icode, Description, LocationNumber, Month, Year, Brand, Color
                  FROM stocks
                  WHERE SQ IN ($escapedSQs)";

    if (mysqli_query($conn, $sqlInsert)) {
       // echo "Selected rows successfully inserted into 'selected_stocks' table.";
    } else {
        echo "Error inserting rows: " . mysqli_error($conn);
    }
} else {
    echo "No rows selected.";
}

// Close connection
mysqli_close($conn);
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Selected Stocks</title>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        #deleteButton {
            background-color: #F28018;
            color: #fff;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.3s;
        }
        #deleteButton:hover {
            background-color: #e06800;
            transform: scale(1.05);
        }
        #deleteButton:active {
            background-color: #c85600;
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <button id="deleteButton">CLICK TO NEXT</button>

</body>
</html>


    <script>
    document.getElementById('deleteButton').addEventListener('click', function() {
        // Send an AJAX request to trigger the deletion process
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'stock_change3.php', true); // Assuming this script is named insert_selected.php
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Display the response from server (optional)
                // alert(xhr.responseText);

                // Redirect to another page upon success
                window.location.href = 'stock_change2.php'; // Replace 'another_page.php' with your desired page
            } else {
                // Display error message
                alert('Error deleting selected stocks. Please try again.');
            }
        };
        xhr.send('action=delete_selected_stocks'); // Send a simple POST parameter to indicate action
    });
</script>


    <?php
    // Check if the action parameter is set and equal to 'delete_selected_stocks'
    if (isset($_POST['action']) && $_POST['action'] === 'delete_selected_stocks') {
        // Database connection parameters
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

        // Delete selected stocks from 'stocks' table based on 'selected_stocks' data
        $sql = "DELETE FROM stocks WHERE SQ IN (
            SELECT SQ FROM selected_stocks
        )";

        if ($conn->query($sql) === TRUE) {
            echo "Selected stocks deleted successfully.";
        } else {
            echo "Error deleting selected stocks: " . $conn->error;
        }

        $conn->close();
    }
    ?>
</body>
</html>



