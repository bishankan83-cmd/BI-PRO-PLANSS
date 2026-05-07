
<?php
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

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Batch Range</title>
</head>
<body>
    <h2>Enter Batch Range</h2>
    <label for="start_batch">Start Batch:</label>
    <input type="text" id="start_batch">
    <br>
    <label for="end_batch">End Batch:</label>
    <input type="text" id="end_batch">
    <br>
    <button onclick="fetchData()">Fetch Data</button>
    <div id="result"></div>

    <script>
        function fetchData() {
            var startBatch = document.getElementById("start_batch").value;
            var endBatch = document.getElementById("end_batch").value;

            // Send an AJAX request to fetch data
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        document.getElementById("result").innerHTML = xhr.responseText;
                    } else {
                        document.getElementById("result").innerHTML = "Error fetching data.";
                    }
                }
            };
            xhr.open("POST", "fetch_data2.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("start_batch=" + startBatch + "&end_batch=" + endBatch);
        }
    </script>
</body>
</html>
