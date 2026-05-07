<?php
include 'db.php';
include 'templates/header.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = $_POST['icode'];
    $mold_id = $_POST['mold_id'];

    $stmt = $conn->prepare("INSERT INTO tire_mold (icode, mold_id ) VALUES (?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ss', $icode, $mold_id);

    if ($stmt->execute()) {
        echo '<p style=" color: #28a745; text-align: center;">Data inserted successfully.</p>';
    
    
    } else {
        echo "Error inserting data: " . $stmt->error;
    }

}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="mold_id" style="font-weight: bold; margin-bottom: 5px;">Mold Id:</label>
    <input type="text" name="mold_id" id="mold_id" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

<br></br>
<div style="display: flex; justify-content: flex-end;">
    <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        Add
    </button>
</div>
</form>
