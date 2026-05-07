<?php
include 'db.php';
include 'templates/header.php';

$icode = $_GET['icode'];
// Add prepared statement for initial select to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM tire WHERE icode = ?");
$stmt->bind_param('s', $icode);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = $_POST['icode'];
    $description = $_POST['description'];
    $time_taken = $_POST['time_taken'];
    $is_available = $_POST['is_available'];
    $availability_date = $_POST['availability_date'];
    $cuing_group_id = $_POST['cuing_group_id'];
    $cuing_group_name = $_POST['cuing_group_name'];

    $stmt = $conn->prepare("UPDATE tire SET icode = ?, description = ?, time_taken = ?, is_available = ?, availability_date = ?, cuing_group_id = ?, cuing_group_name = ? WHERE icode = ?");
    $stmt->bind_param('ssiiisis', $icode, $description, $time_taken, $is_available, $availability_date, $cuing_group_id, $cuing_group_name, $icode);
    
    if ($stmt->execute()) {
        header('Location: index.php');
        exit();
    } else {
        $error = "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Tire Information</title>
    <style>
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-height: 100px;
            box-sizing: border-box;
        }
        .form-button {
            padding: 10px 100px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        .button-container {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label" for="icode">I Code:</label>
                <input class="form-input" type="text" name="icode" id="icode" value="<?php echo htmlspecialchars($item['icode']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description:</label>
                <textarea class="form-textarea" name="description" id="description" required><?php echo htmlspecialchars($item['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="time_taken">Time Taken:</label>
                <input class="form-input" type="number" name="time_taken" id="time_taken" value="<?php echo htmlspecialchars($item['time_taken']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="is_available">Is Available:</label>
                <select class="form-input" name="is_available" id="is_available" required>
                    <option value="1" <?php echo $item['is_available'] == 1 ? 'selected' : ''; ?>>Yes</option>
                    <option value="0" <?php echo $item['is_available'] == 0 ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="form-group">
    <label class="form-label" for="availability_date">Availability Date:</label>
    <input 
        class="form-input" 
        type="date" 
        name="availability_date" 
        id="availability_date" 
        value="<?php 
            $date = new DateTime($item['availability_date']);
            echo $date->format('Y-m-d'); 
        ?>"
    >
</div>

<div class="form-group">
    <label class="form-label" for="time_taken">Time:</label>
    <input 
        class="form-input" 
        type="time" 
        name="time_nm" 
        id="time_nm" 
        value="<?php 
            $date = new DateTime($item['time_nm']);
            echo $date->format('H:i'); 
        ?>"
    >
</div>

            <div class="form-group">
                <label class="form-label" for="cuing_group_id">Cuing Group ID:</label>
                <input class="form-input" type="number" name="cuing_group_id" id="cuing_group_id" value="<?php echo htmlspecialchars($item['cuing_group_id']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="cuing_group_name">Cuing Group Name:</label>
                <input class="form-input" type="text" name="cuing_group_name" id="cuing_group_name" value="<?php echo htmlspecialchars($item['cuing_group_name']); ?>" required>
            </div>

            <div class="button-container">
                <button type="submit" class="form-button">Update</button>
            </div>
        </form>
    </div>
</body>
</html>