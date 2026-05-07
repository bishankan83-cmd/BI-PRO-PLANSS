<?php
include 'db.php';
include 'templates/header.php';

// Capture 'id' from the query string
$icode = $_GET['id'];

// Use $icode instead of $id in the query
$result = $conn->query("SELECT * FROM complete_date WHERE id = '$icode'");

// Fetch the record
$item = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $erp = $_POST['erp'];
    $com_date = $_POST['com_date'];

    // Prepare the statement with proper binding
    $stmt = $conn->prepare("UPDATE complete_date SET erp = ?, com_date = ? WHERE id = ?");
    $stmt->bind_param('ssi', $erp, $com_date, $icode); // 'i' for integer id
    $stmt->execute();

    // Redirect after update
    header('Location: index.php');
    exit; // Ensure the script stops here
}
?>

<!-- HTML Form -->
<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="erp" style="font-weight: bold; margin-bottom: 5px;">ERP:</label>
    <input type="text" name="erp" id="erp" value="<?php echo htmlspecialchars($item['erp']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="com_date" style="font-weight: bold; margin-bottom: 5px;">Complete Date:</label>
    <input type="text" name="com_date" id="com_date" value="<?php echo htmlspecialchars($item['com_date']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Update
        </button>
    </div>
</form>
