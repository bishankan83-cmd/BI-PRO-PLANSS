<div class="button-container">
    <button class="styled-button">
        <a href="planbuttoon.php" style="text-decoration: none; color: #FFFFFF;">Click To Back</a>
    </button>
</div>

<style>
    .button-container {
        text-align: center;
        margin-top: 20px;
    }

    .styled-button {
        background-color:black; /* Green background color */
        border: none;
        color: white;
        padding: 15px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        cursor: pointer;
        border-radius: 8px; /* Rounded corners */
        transition: background-color 0.3s ease;
    }

    .styled-button:hover {
        background-color:black; /* Darker green on hover */
    }

    .styled-button a {
        color: white;
        text-decoration: none;
    }

    .styled-button:active {
        background-color: #3e8e41; /* Darker green when button is clicked */
    }
</style>



<?php
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Main logic to determine which part of the script to execute
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $result = $conn->query("SELECT * FROM worder WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $record = $result->fetch_assoc();
    } else {
        die("Record not found.");
    }

    $currentErp = $record['erp'];
    $currentWono = $record['wono'];
    $currentRef = $record['ref'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $newErp = htmlspecialchars(trim($_POST['erp']));
        $newWono = htmlspecialchars(trim($_POST['wono']));
        $newRef = htmlspecialchars(trim($_POST['ref']));

        $stmt = $conn->prepare("UPDATE worder SET erp = ?, wono = ?, ref = ? WHERE erp = ? OR wono = ? OR ref = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssss", $newErp, $newWono, $newRef, $currentErp, $currentWono, $currentRef);

        if ($stmt->execute()) {
            header('Location: ?');
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    echo "<html><head><title>Update Record</title></head><body>";
    echo "<form method='POST' style='max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;'>
        <h1 style='text-align: center;'>Update Record</h1>
        <label for='erp' style='font-weight: bold; margin-bottom: 5px;'>ERP:</label>
        <input type='text' name='erp' id='erp' value='" . htmlspecialchars($record['erp']) . "' style='width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;'><br>
        <label for='wono' style='font-weight: bold; margin-bottom: 5px;'>WONO:</label>
        <input type='text' name='wono' id='wono' value='" . htmlspecialchars($record['wono']) . "' style='width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;'><br>
        <label for='ref' style='font-weight: bold; margin-bottom: 5px;'>Ref:</label>
        <input type='text' name='ref' id='ref' value='" . htmlspecialchars($record['ref']) . "' style='width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;'><br>
        <div style='display: flex; justify-content: space-between;'>
            <a href='?' style='padding: 10px 20px; background-color:rgb(241, 140, 51); color: white; text-decoration: none; border: none; border-radius: 5px;'>Home</a>
            <button type='submit' style='padding: 10px 100px; background-color:rgb(8, 8, 8); color: white; border: none; border-radius: 5px; cursor: pointer;'>Update</button>
        </div>
    </form>";
    echo "</body></html>";
} else {
    $result = $conn->query("SELECT erp, MIN(wono) as wono, MIN(ref) as ref, MIN(id) as id FROM worder GROUP BY erp");

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Worder</title>
        <style>
            body { background-color: #f0f0f0; font-family: Arial, sans-serif; }
            .container { width: 80%; margin: 0 auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
            h1 { text-align: center; color: rgb(11, 11, 11); font-size: 60px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000000; padding: 8px; text-align: left; }
            th { background-color: #F28018; color: #000000; position: sticky; top: 0; z-index: 100; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            td a { display: inline-block; padding: 5px 10px; background-color: rgb(3, 3, 3); color: white; border: 2px solid black; border-radius: 5px; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Worder</h1>
            <table>
                <tr>
                    <th style='width: 200px; text-align: center;'>ERP</th>
                    <th style='width: 250px; text-align: center;'>WONO</th>
                    <th style='width: 250px; text-align: center;'>REF</th>
                    <th style='width: 150px; text-align: center;'>Action</th>
                </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td style='text-align: center;'>{$row['erp']}</td>
            <td style='text-align: center;'>{$row['wono']}</td>
            <td style='text-align: center;'>{$row['ref']}</td>
            <td style='text-align: center;'><a href='?action=edit&id={$row['id']}'>Edit</a></td>
        </tr>";
    }

    echo "</table>
        </div>
    </body>
    </html>";
}

$conn->close();
?>