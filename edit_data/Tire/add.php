<?php
include 'db.php';
include 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = trim($_POST['icode']);
    $description = trim($_POST['description']);
    $time_taken = trim($_POST['time_taken']);
    $is_available = $_POST['is_available'] == '1' ? 1 : 0;
    $availability_date = trim($_POST['availability_date']);
    $cuing_group_id = 0; // Always set cuing_group_id to 0
    $cuing_group_name = trim($_POST['cuing_group_name']);

    // Convert availability_date to correct format for MySQL
    if (!empty($availability_date)) {
        $availability_date = date('Y-m-d H:i:s', strtotime($availability_date));
    }

    // Validation
    $errors = array();
    if (empty($icode)) {
        $errors[] = 'I code is required';
    }
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    if (empty($time_taken) || !is_numeric($time_taken)) {
        $errors[] = 'Time taken must be a number';
    }
    if (empty($availability_date)) {
        $errors[] = 'Availability date is required';
    }
    if (empty($cuing_group_name)) {
        $errors[] = 'Cuing group name is required';
    }

    if (count($errors) == 0) {
        $stmt = $conn->prepare("INSERT INTO tire (icode, description, time_taken, is_available, availability_date, cuing_group_id, cuing_group_name) VALUES (?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param('ssissis', $icode, $description, $time_taken, $is_available, $availability_date, $cuing_group_id, $cuing_group_name);

        if ($stmt->execute()) {
            echo '<p style="color: #28a745; text-align: center;">Data inserted successfully.</p>';
        } else {
            echo "Error inserting data: " . $stmt->error;
        }
    } else {
        echo '<p style="color: #dc3545; text-align: center;">The following errors occurred:</p>';
        foreach ($errors as $error) {
            echo '<p style="color: #dc3545; text-align: center;">' . $error . '</p>';
        }
    }
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <div class="form-group">
        <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
        <input type="text" name="icode" id="icode" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div class="form-group">
        <label for="description" style="font-weight: bold; margin-bottom: 5px;">Description:</label>
        <textarea name="description" id="description" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
    </div>
    <div class="form-group">
        <label for="time_taken" style="font-weight: bold; margin-bottom: 5px;">Time taken:</label>
        <input type="number" name="time_taken" id="time_taken" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div class="form-group">
        <label for="is_available" style="font-weight: bold; margin-bottom: 5px;">Is available:</label>
        <select name="is_available" id="is_available" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
    <div class="form-group">
        <label for="availability_date" style="font-weight: bold; margin-bottom: 5px;">Availability date:</label>
        <input type="datetime-local" name="availability_date" id="availability_date" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div class="form-group">
        <label for="cuing_group_name" style="font-weight: bold; margin-bottom: 5px;">Cuing group name:</label>
        <input type="text" name="cuing_group_name" id="cuing_group_name" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Add
        </button>
    </div>
</form>
