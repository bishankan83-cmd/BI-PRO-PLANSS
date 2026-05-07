<?php
include 'db.php';
include 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = $_POST['icode'];
    $t_size = $_POST['t_size'];
    $brand = $_POST['brand'];
    $col = $_POST['col'];
    $rim = $_POST['rim'];
    $gweight = $_POST['gweight'];
    $cstock = $_POST['cstock'];

    $stmt = $conn->prepare("INSERT INTO stock (icode, t_size, brand, col, rim, gweight, cstock) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('sssssss', $icode, $t_size, $brand, $col, $rim, $gweight, $cstock);

    if ($stmt->execute()) {
        echo '<p style="color: #28a745; text-align: center;">Data inserted successfully.</p>';
        header('Location: index.php');
    } else {
        echo "Error inserting data: " . $stmt->error;
    }
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="t_size" style="font-weight: bold; margin-bottom: 5px;">Tire size:</label>
    <input type="text" name="t_size" id="t_size" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="brand" style="font-weight: bold; margin-bottom: 5px;">Brand:</label>
    <input type="text" name="brand" id="brand" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="col" style="font-weight: bold; margin-bottom: 5px;">Color:</label>
    <input type="text" name="col" id="col" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="rim" style="font-weight: bold; margin-bottom: 5px;">Rim:</label>
    <input type="text" name="rim" id="rim" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="gweight" style="font-weight: bold; margin-bottom: 5px;">G weight:</label>
    <input type="text" name="gweight" id="gweight" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="cstock" style="font-weight: bold; margin-bottom: 5px;">C stock:</label>
    <input type="text" name="cstock" id="cstock" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>
    <br></br>
    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Add
        </button>
    </div>
</form>
