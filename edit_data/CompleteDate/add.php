<?php
include 'db.php';
include 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $erp = $_POST['erp'];
    $com_date = $_POST['com_date'];

    // Basic validation
    if (empty($erp) || empty($com_date)) {
        echo '<p style="color: #dc3545; text-align: center;">Please fill in all fields.</p>';
    } else {
        $stmt = $conn->prepare("INSERT INTO complete_date (erp, com_date) VALUES (?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param('ss', $erp, $com_date);  // 'ss' for two string parameters

        if ($stmt->execute()) {
            echo '<p style="color: #28a745; text-align: center;">Data inserted successfully.</p>';
        } else {
            echo "Error inserting data: " . $stmt->error;
        }
    }
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="erp" style="font-weight: bold; margin-bottom: 5px;">ERP:</label>
    <input type="text" name="erp" id="erp" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="com_date" style="font-weight: bold; margin-bottom: 5px;">Complete Date:</label>
    <input type="text" name="com_date" id="com_date" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Add
        </button>
    </div>
</form>
