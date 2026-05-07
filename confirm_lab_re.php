<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// MySQL database credentials
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

// Function to fetch all data from bcompound98 table
function fetchAllData($conn) {
    $sql = "SELECT * FROM bcompound98";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    return $data;
}

// Function to delete a specific row from bcompound98 table
function deleteRow($conn, $id) {
    $sql = "DELETE FROM bcompound98 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Check if a form is submitted for row deletion
if (isset($_POST['delete_row'])) {
    $idToDelete = $_POST['id_to_delete'];
    if (deleteRow($conn, $idToDelete)) {
        echo "Row with ID '$idToDelete' deleted successfully.";
    } else {
        echo "Error deleting row with ID '$idToDelete': " . $conn->error;
    }
}

// Fetch all data from bcompound98 table
$data = fetchAllData($conn);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
  
    <style>
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
        th {
            background-color: #F28018;
            color: #000000;
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 100;
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
        td input, td select {
            width: 100%;
        }
    </style>
</head>
<body>
    <h2>Data from bcompound98 Table</h2>
    
    <table>
        <tr>
            <th>id</th>
            <th>InputDate</th>
            <th>Shift</th>
            <th>Compound_name</th>
            <th>Mixning Sup</th>
         
            <th>Batch</th>
            <th>Batch2</th>
            <th>Pallet</th>
            <th>Created_at</th>
            <th>Weight</th>
            <th>Batch number</th>
            <th>Action</th>
        </tr>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['inputDate']; ?></td>
            <td><?php echo $row['shift']; ?></td>
            <td><?php echo $row['compound_name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            
            <td><?php echo $row['batch']; ?></td>
            <td><?php echo $row['batch2']; ?></td>
            <td><?php echo $row['pallet']; ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo $row['weight']; ?></td>
            <td><?php echo $row['serial_number']; ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Are you sure you want to delete row <?php echo $row['id']; ?>?');">
                    <input type="hidden" name="id_to_delete" value="<?php echo $row['id']; ?>">
                    <input type="submit" name="delete_row" value="Delete">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
