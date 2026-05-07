<?php
include 'db.php';
include 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $RM_code = $_POST['RM_code'];
    $band_size = $_POST['band_size'];
    $ard = $_POST['ard'];

    // Basic validation
    if (empty($RM_code) || empty($band_size) || empty($ard)) {
        echo '<p style="color: #dc3545; text-align: center;">Please fill in all fields.</p>';
    } else {
        $stmt = $conn->prepare("INSERT INTO rm_band_data (RM_code, band_size, ard) VALUES (?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param('sss', $RM_code, $band_size, $ard);  // 'sss' for two string parameters

        if ($stmt->execute()) {
            echo '<p style="color: #28a745; text-align: center;">Data inserted successfully.</p>';
        } else {
            echo "Error inserting data: " . $stmt->error;
        }
    }
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="RM_code" style="font-weight: bold; margin-bottom: 5px;">RM Code:</label>
    <input type="text" name="RM_code" id="RM_code" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="band_size" style="font-weight: bold; margin-bottom: 5px;">Band Size:</label>
    <input type="text" name="band_size" id="band_size" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="ard" style="font-weight: bold; margin-bottom: 5px;">ARD:</label>
    <input type="text" name="ard" id="ard" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color:rgb(6, 6, 6); color: white; border: none; border-radius: 5px; cursor: pointer;">
            Add
        </button>
    </div>
</form>
