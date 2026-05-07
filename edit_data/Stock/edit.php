<?php
include 'db.php';
include 'templates/header.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM stock WHERE id = $id");
$item = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = $_POST['icode'];
    $t_size = $_POST['t_size'];
    $brand = $_POST['brand'];
    $col = $_POST['col'];
    $rim = $_POST['rim'];
    $gweight = $_POST['gweight'];
    $cstock = $_POST['cstock'];

    $stmt = $conn->prepare("UPDATE stock SET icode = ?, t_size = ?, brand = ?, col = ?, rim = ?, gweight = ?, cstock = ? WHERE id = ?");
    $stmt->bind_param('ssssssss', $icode, $t_size, $brand, $col, $rim, $gweight, $cstock, $id);
    $stmt->execute();

    header('Location: index.php');
}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="t_size" style="font-weight: bold; margin-bottom: 5px;">Tire size:</label>
    <input type="text" name="t_size" id="t_size"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="brand" style="font-weight: bold; margin-bottom: 5px;">Brand:</label>
    <input type="text" name="brand" id="brand"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="col" style="font-weight: bold; margin-bottom: 5px;">Color:</label>
    <input type="text" name="col" id="col"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="rim" style="font-weight: bold; margin-bottom: 5px;">Rim:</label>
    <input type="text" name="rim" id="rim"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="gweight" style="font-weight: bold; margin-bottom: 5px;">G weight:</label>
    <input type="text" name="gweight" id="gweight"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="cstock" style="font-weight: bold; margin-bottom: 5px;">C stock:</label>
    <input type="text" name="cstock" id="cstock"  style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>
<br></br>
<div style="display: flex; justify-content: flex-end;">
    <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        Update
    </button>
</div>
</form>
