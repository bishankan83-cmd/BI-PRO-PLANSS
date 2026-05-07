<?php
include 'db.php';
include 'templates/header.php';

// Capture 'id' from the query string safely
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the record using a prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM rm_band_data WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $RM_code = $_POST['RM_code'];
    $band_size = $_POST['band_size'];
    $ard = $_POST['ard'];

    // Update query with the correct binding order
    $stmt = $conn->prepare("UPDATE rm_band_data SET RM_code = ?, band_size = ?, ard = ? WHERE id = ?");
    $stmt->bind_param('sssi', $RM_code, $band_size, $ard, $id); // Correct order: RM_code, band_size, ard, id
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<p style='color:green;'>Record updated successfully!</p>";
    } else {
        echo "<p style='color:red;'>Update failed or no changes made.</p>";
    }

    $stmt->close();

    // Redirect after update
    header('Location: index.php');
    exit; 
}
?>

<!-- HTML Form -->
<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="RM_code" style="font-weight: bold; margin-bottom: 5px;">RM_code:</label>
    <input type="text" name="RM_code" id="RM_code" value="<?php echo htmlspecialchars($item['RM_code'] ?? ''); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="band_size" style="font-weight: bold; margin-bottom: 5px;">Band Size:</label>
    <input type="text" name="band_size" id="band_size" value="<?php echo htmlspecialchars($item['band_size'] ?? ''); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="ard" style="font-weight: bold; margin-bottom: 5px;">ARD:</label>
    <input type="text" name="ard" id="ard" value="<?php echo htmlspecialchars($item['ard'] ?? ''); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color:rgb(8, 8, 8); color: white; border: none; border-radius: 5px; cursor: pointer;">
            Update
        </button>
    </div>
</form>
