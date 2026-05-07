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

    // Start a transaction to ensure both inserts succeed or none.
    $conn->begin_transaction();

    try {
        // Insert into realstock table
        $stmt_realstock = $conn->prepare("INSERT INTO realstock (icode, t_size, brand, col, rim, gweight, cstock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt_realstock) {
            throw new Exception("Prepare failed for realstock: " . $conn->error);
        }
        $stmt_realstock->bind_param('sssssss', $icode, $t_size, $brand, $col, $rim, $gweight, $cstock);
        if (!$stmt_realstock->execute()) {
            throw new Exception("Execute failed for realstock: " . $stmt_realstock->error);
        }

        // Insert into stock table
        $stmt_stock = $conn->prepare("INSERT INTO stock (icode, t_size, brand, col, rim, gweight, cstock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt_stock) {
            throw new Exception("Prepare failed for stock: " . $conn->error);
        }
        $stmt_stock->bind_param('sssssss', $icode, $t_size, $brand, $col, $rim, $gweight, $cstock);
        if (!$stmt_stock->execute()) {
            throw new Exception("Execute failed for stock: " . $stmt_stock->error);
        }

        // Commit the transaction if both inserts succeed
        $conn->commit();
        echo '<p style="color: #28a745; text-align: center;">Data inserted successfully into both tables.</p>';
    } catch (Exception $e) {
        // Roll back the transaction if any insert fails
        $conn->rollback();
        echo '<p style="color: #dc3545; text-align: center;">Error inserting data: ' . $e->getMessage() . '</p>';
    }
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <!-- Form fields remain the same -->
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

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Add</button>
    </div>
</form>
