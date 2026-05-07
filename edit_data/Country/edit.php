<?php
include 'db.php';  // Ensure this includes your database connection details

// Check if ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// Fetch the existing record
$stmt = $conn->prepare("SELECT id, erp, country, pattern FROM country WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $erp = $_POST['erp'];
    $country = $_POST['country'];
    $pattern = $_POST['pattern'];

    // Basic validation
    if (empty($erp) || empty($country) || empty($pattern)) {
        echo '<p style="color: #dc3545; text-align: center;">Please fill in all fields.</p>';
    } else {
        // Prepare the SQL query to update the data in the 'country' table
        $stmt = $conn->prepare("UPDATE country SET erp = ?, country = ?, pattern = ? WHERE id = ?");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);  // Handle preparation failure
        }

        $stmt->bind_param('issi', $erp, $country, $pattern, $id);  // 'issi' for integer (erp), string (country), string (pattern), integer (id)

        if ($stmt->execute()) {
            // On successful update, redirect to index.php
            header('Location: index.php');
            exit();  // Ensure the script stops here after redirect
        } else {
            echo "Error updating data: " . $stmt->error;  // Handle execution failure
        }

        $stmt->close();  // Close the prepared statement after execution
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Country</title>
</head>
<body>
    <h1 style="text-align: center; color: rgb(11, 11, 11); font-size: 60px;">Edit Country</h1>

    <form method="POST" style="max-width: 600px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
        <label for="erp" style="font-weight: bold; margin-bottom: 5px;">ERP:</label>
        <input type="text" name="erp" id="erp" value="<?= htmlspecialchars($row['erp']) ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

        <label for="country" style="font-weight: bold; margin-bottom: 5px;">Country:</label>
        <input type="text" name="country" id="country" value="<?= htmlspecialchars($row['country']) ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

        <label for="pattern" style="font-weight: bold; margin-bottom: 5px;">Pattern:</label>
        <select name="pattern" id="pattern" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="" <?= $row['pattern'] == '' ? 'selected' : '' ?>>Select Pattern</option>
            <option value="FCL" <?= $row['pattern'] == 'FCL' ? 'selected' : '' ?>>FCL</option>
            <option value="LCL" <?= $row['pattern'] == 'LCL' ? 'selected' : '' ?>>LCL</option>
        </select><br>

        <div style="display: flex; justify-content: flex-end;">
            <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Update
            </button>
        </div>
    </form>
</body>
</html>