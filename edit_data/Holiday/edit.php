<?php
include 'db.php';
include 'templates/header.php';

// Validate and sanitize the GET parameter
if (!isset($_GET['holiday_id']) || empty($_GET['holiday_id'])) {
    die('Holiday ID is required.');
}

$icode = htmlspecialchars($_GET['holiday_id']); // Sanitize the input

// Fetch holiday details
$result = $conn->query("SELECT * FROM holidays WHERE holiday_id = '$icode'");

if (!$result || $result->num_rows == 0) {
    die('Holiday not found.');
}

$item = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $holiday_id = $_POST['holiday_id'];
    $holiday_date = $_POST['holiday_date'];

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE holidays SET holiday_id = ?, holiday_date = ? WHERE holiday_id = ?");
    $stmt->bind_param('iss', $holiday_id, $holiday_date, $icode); // Use $icode to reference the original holiday ID
    $stmt->execute();

    header('Location: index.php');
    exit;
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="holiday_id" style="font-weight: bold; margin-bottom: 5px;">Holiday ID:</label>
    <input type="text" name="holiday_id" id="holiday_id" value="<?php echo htmlspecialchars($item['holiday_id']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="holiday_date" style="font-weight: bold; margin-bottom: 5px;">Holiday Date:</label>
    <input type="text" name="holiday_date" id="holiday_date" value="<?php echo htmlspecialchars($item['holiday_date']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Update
        </button>
    </div>
</form>

