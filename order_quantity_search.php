<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Funda Of Web IT</title>
</head>
<body>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>ORDER Quantity</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">

                                <form action="" method="GET">
                                    <div class="input-group mb-3">
                                        <input type="text" name="search" required value="<?php if(isset($_GET['search'])){echo $_GET['search']; } ?>" class="form-control" placeholder="Enter icode">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </div>
                                </form>
                                <?php
// Database connection details
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
$search_icode = "";
if (isset($_GET['search'])) {
    $search_icode = $_GET['search'];
    // Add search condition to the query
    $search_condition = "WHERE icode = '$search_icode'";
} else {
    $search_condition = "";
}

// Query to retrieve work orders data and calculate total quantity
$sql = "SELECT icode, SUM(new) AS total_quantity, t_size
        FROM worder
        $search_condition
        GROUP BY icode";
$result = $conn->query($sql);

// Check if any records were found
if ($result->num_rows > 0) {
    // Output search form
   
    echo "<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
</style>";
    echo "<table>
            <tr>
            <th>Work Order</th>
            <th>t_size</th>
            <th>Total Quantity (pcs)</th>
            </tr>";

    // Output data for each row
    while ($row = $result->fetch_assoc()) {
        $icode = $row['icode'];
        $totalQuantity = $row['total_quantity'];
        $otherData = $row['t_size'];

        echo "<tr>
                <td>$icode</td>
                <td>$otherData</td>
                <td>$totalQuantity</td>
            </tr>";
    }

    // Close table
    echo "</table>";
} else {
    echo "No work orders found.";
}

// Close the connection
$conn->close();
?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>