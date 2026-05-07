<?php
include 'db.php';
include 'templates/header.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM tire_mold WHERE id = $id");
$item = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = $_POST['icode'];
    $mold_id = $_POST['mold_id'];

    $stmt = $conn->prepare("UPDATE tire_mold SET icode = ?, mold_id = ? WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('ssi', $icode, $mold_id, $id);
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }

    header('Location: index.php');
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode" value="<?php echo htmlspecialchars($item['icode']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="mold_id" style="font-weight: bold; margin-bottom: 5px;">Mold ID:</label>
    <input type="text" name="mold_id" id="mold_id" value="<?php echo htmlspecialchars($item['mold_id']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Update
        </button>
    </div>
</form>
