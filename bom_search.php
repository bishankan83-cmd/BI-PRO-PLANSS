
<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $icode = $_POST["icode"];
    
    // SQL query to retrieve data for the given icode
    $sql = "SELECT * FROM `bom_new` WHERE `icode` = '$icode'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Output data in a table format
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Retrieve Data by icode</title>
            <style>
                body {
                    font-family: 'Cantarell', sans-serif;
                    background-image: url('your-background-image.jpg'); /* Specify your image URL here */
                    background-size: cover;
                    background-repeat: no-repeat;
                    background-attachment: fixed;
                    color: #FFFFFF;
                    margin: 0;
                }
                h2 {
                    font-family: 'Cantarell Bold', sans-serif;
                    text-align: center;
                    padding: 20px;
                }
                table {
                    font-family: 'Open Sans', sans-serif;
                    background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent black background */
                    border-collapse: collapse;
                    width: 100%;
                    color: #FFFFFF;
                }
                th, td {
                    border: 1px solid #F28018; /* Orange color */
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #F28018;
                }
                tr:nth-child(even) {
                    background-color: rgba(242, 128, 24, 0.3); /* Semi-transparent orange background for even rows */
                }
            </style>
        </head>
        <body>
            <h2>Retrieve Data by icode</h2>";

        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Item</th><th>icode</th><th>t_size</th><th>Item Description</th><th>ATPRS</th><th>B-ATS 15</th><th>B-BNS 24</th><th>BG-BLS 12</th><th>CG - BS 901</th><th>C - SMS 501</th><th>C-ATS 20</th><th>C-SMS 702</th><th>T - TRS 102</th><th>T-ATNM S</th><th>T-ATS 30</th><th>T-ATS 35</th><th>T-KS 40</th><th>T-TRNMS 402</th><th>T-TRNMS 402G</th><th>T-TRS 202</th><th>Grand Totalcompound weight</th><th>Color</th><th>Brand</th><th>Green Tire weight</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$row["id"]."</td>";
            echo "<td>".$row["Item"]."</td>";
            echo "<td>".$row["icode"]."</td>";
            echo "<td>".$row["t_size"]."</td>";
            echo "<td>".$row["Item Description"]."</td>";
            echo "<td>".$row["a"]."</td>";
            echo "<td>".$row["b"]."</td>";
            echo "<td>".$row["c"]."</td>";
            echo "<td>".$row["d"]."</td>";
            echo "<td>".$row["e"]."</td>";
            echo "<td>".$row["f"]."</td>";
            echo "<td>".$row["g"]."</td>";
            echo "<td>".$row["h"]."</td>";
            echo "<td>".$row["i"]."</td>";
            echo "<td>".$row["j"]."</td>";
            echo "<td>".$row["k"]."</td>";
            echo "<td>".$row["l"]."</td>";
            echo "<td>".$row["m"]."</td>";
            echo "<td>".$row["n"]."</td>";
            echo "<td>".$row["o"]."</td>";
            echo "<td>".$row["p"]."</td>";
           
            echo "<td>".$row["Grand Totalcompound weight"]."</td>";
            echo "<td>".$row["Color"]."</td>";
            echo "<td>".$row["Brand"]."</td>";
            echo "<td>".$row["Green Tire weight"]."</td>";
            echo "</tr>";
        }

        echo "</table>
        </body>
        </html>";
    } else {
        echo "No records found for icode: $icode";
    }
}

// Close the database connection
$conn->close();
?>
