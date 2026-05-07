<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 50px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        h1 {
            color: #000000;
            font-weight: bold;
            font-size: 24px;
        }

        form {
            margin-top: 20px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Cantarell Bold', sans-serif;
        }

        input[type="submit"]:hover {
            background-color: #FFA726;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Enter Erp Number</h1>

        <?php
ob_start(); // Start output buffering

include './includes/data_base_save_update.php';
include 'includes/App_Code.php';
$AppCodeObj = new App_Code();

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



if (isset($_POST['submit'])) {
    // Get the user-provided work order ID
    $erp = $_POST['erp'];

    // Check if the work order already exists in tobeplan table
    $existingSql = "SELECT COUNT(*) as count FROM tobeplan1 WHERE erp = '$erp'";
    $existingResult = $conn->query($existingSql);
    $existingRow = $existingResult->fetch_assoc();
    $count = $existingRow['count'];

    if ($count > 0) {
        echo "Work order with ERP number $erp already exists.";
    } 
               
                header("Location: display.php?erp=$erp");
                exit;
            } 


$conn->close();
ob_end_flush(); // Send output buffer and turn off output buffering
?>

        <form method="POST" action="display2.php">
            <label for="erp"></label>
            <input type="text" name="erp" id="erp" required>
            <input type="submit" name="submit" value="Click Next">
        </form>
    </div>
</body>
</html>
