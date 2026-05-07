<?php
include 'db.php';
include 'templates/header.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Item = htmlspecialchars(trim($_POST['Item']));
    $icode = htmlspecialchars(trim($_POST['icode']));
    $t_size = htmlspecialchars(trim($_POST['t_size']));
    $Item_Description = htmlspecialchars(trim($_POST['Item_Description']));
    $a = htmlspecialchars(trim($_POST['a']));
    $b = htmlspecialchars(trim($_POST['b']));
    $c = htmlspecialchars(trim($_POST['c']));
    $d = htmlspecialchars(trim($_POST['d']));
    $e = htmlspecialchars(trim($_POST['e']));
    $f = htmlspecialchars(trim($_POST['f']));
    $g = htmlspecialchars(trim($_POST['g']));
    $h = htmlspecialchars(trim($_POST['h']));
    $i = htmlspecialchars(trim($_POST['i']));
    $j = htmlspecialchars(trim($_POST['j']));
    $k = htmlspecialchars(trim($_POST['k']));
    $l = htmlspecialchars(trim($_POST['l']));
    $m = htmlspecialchars(trim($_POST['m']));
    $n = htmlspecialchars(trim($_POST['n']));
    $o = htmlspecialchars(trim($_POST['o']));
    $p = htmlspecialchars(trim($_POST['p']));
    $q = htmlspecialchars(trim($_POST['q']));
    $r = htmlspecialchars(trim($_POST['r']));
    $Grand_Totalcompound_weight = htmlspecialchars(trim($_POST['Grand_Totalcompound_weight']));
    $Color = htmlspecialchars(trim($_POST['Color']));
    $Brand = htmlspecialchars(trim($_POST['Brand']));
    $Green_Tire_weight = htmlspecialchars(trim($_POST['Green_Tire_weight']));
    $PBWeight = is_numeric($_POST['PBweight']) ? (float)$_POST['PBweight'] : 0;

    $stmt = $conn->prepare("UPDATE bom_new SET 
    `Item` = ?, 
    `icode` = ?, 
    `t_size` = ?, 
    `Item_Description` = ?, 
    `a` = ?, 
    `b` = ?, 
    `c` = ?, 
    `d` = ?, 
    `e` = ?, 
    `f` = ?, 
    `g` = ?, 
    `h` = ?, 
    `i` = ?, 
    `j` = ?, 
    `k` = ?, 
    `l` = ?, 
    `m` = ?, 
    `n` = ?, 
    `o` = ?, 
    `p` = ?, 
    `q` = ?, 
    `r` = ?, 
    `Grand_Totalcompound_weight` = ?, 
    `Color` = ?, 
    `Brand` = ?, 
    `Green_Tire_weight` = ?, 
    `PBweight` = ? 
    WHERE `id` = ?");

$stmt = $conn->prepare("INSERT INTO bom_new 
        (`Item`, `icode`, `t_size`, `Item_Description`, `a`, `b`, `c`, `d`, `e`, `f`, `g`, `h`, `i`, `j`, `k`, `l`, `m`, `n`, `o`, `p`, `q`, `r`, `Grand_Totalcompound_weight`, `Color`, `Brand`, `Green_Tire_weight`, `PBweight`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}



    // Bind parameters
    $stmt->bind_param(
        "sssssssssssssssssssssssssss",
        $Item, $icode, $t_size, $Item_Description, 
        $a, $b, $c, $d, $e, $f, $g, $h, 
        $i, $j, $k, $l, $m, $n, $o, $p, 
        $q, $r, $Grand_Totalcompound_weight, $Color, 
        $Brand, $Green_Tire_weight, $PBWeight
    );

    if ($stmt->execute()) {
        echo '<p style=" color: #28a745; text-align: center;">Data inserted successfully.</p>';
    
    
    } else {
        echo "Error inserting data: " . $stmt->error;
    }

}
?>

<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    

    <label for="Item" style="font-weight: bold; margin-bottom: 5px;">Item:</label>
    <input type="text" name="Item" id="Item" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="t_size" style="font-weight: bold; margin-bottom: 5px;">Tire Size:</label>
    <input type="text" name="t_size" id="t_size" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Item_Description" style="font-weight: bold; margin-bottom: 5px;">Item Description:</label>
    <input type="text" name="Item_Description" id="Item_Description" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="a" style="font-weight: bold; margin-bottom: 5px;">A:</label>
    <input type="text" name="a" id="a" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="b" style="font-weight: bold; margin-bottom: 5px;">B:</label>
    <input type="text" name="b" id="b" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="c" style="font-weight: bold; margin-bottom: 5px;">C:</label>
    <input type="text" name="c" id="c" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="d" style="font-weight: bold; margin-bottom: 5px;">D:</label>
    <input type="text" name="d" id="d" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="e" style="font-weight: bold; margin-bottom: 5px;">E:</label>
    <input type="text" name="e" id="e" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="f" style="font-weight: bold; margin-bottom: 5px;">F:</label>
    <input type="text" name="f" id="f" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="g" style="font-weight: bold; margin-bottom: 5px;">G:</label>
    <input type="text" name="g" id="g" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="h" style="font-weight: bold; margin-bottom: 5px;">H:</label>
    <input type="text" name="h" id="h" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="i" style="font-weight: bold; margin-bottom: 5px;">I:</label>
    <input type="text" name="i" id="i" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="j" style="font-weight: bold; margin-bottom: 5px;">J:</label>
    <input type="text" name="j" id="j" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="k" style="font-weight: bold; margin-bottom: 5px;">K:</label>
    <input type="text" name="k" id="k" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="l" style="font-weight: bold; margin-bottom: 5px;">L:</label>
    <input type="text" name="l" id="l" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="m" style="font-weight: bold; margin-bottom: 5px;">M:</label>
    <input type="text" name="m" id="m" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="n" style="font-weight: bold; margin-bottom: 5px;">N:</label>
    <input type="text" name="n" id="n" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="o" style="font-weight: bold; margin-bottom: 5px;">O:</label>
    <input type="text" name="o" id="o" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="p" style="font-weight: bold; margin-bottom: 5px;">P:</label>
    <input type="text" name="p" id="p" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="q" style="font-weight: bold; margin-bottom: 5px;">Q:</label>
    <input type="text" name="q" id="q" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="r" style="font-weight: bold; margin-bottom: 5px;">R:</label>
    <input type="text" name="r" id="r" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Grand_Totalcompound_weight" style="font-weight: bold; margin-bottom: 5px;">Grand Total Compound Weight:</label>
    <input type="text" name="Grand_Totalcompound_weight" id="Grand_Totalcompound_weight" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Color" style="font-weight: bold; margin-bottom: 5px;">Color:</label>
    <input type="text" name="Color" id="Color" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Brand" style="font-weight: bold; margin-bottom: 5px;">Brand:</label>
    <input type="text" name="Brand" id="Brand" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Green_Tire_weight" style="font-weight: bold; margin-bottom: 5px;">Green tire weight:</label>
    <input type="text" name="Green_Tire_weight" id="Green_Tire_weight" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="PBweight" style="font-weight: bold; margin-bottom: 5px;">PBweight:</label>
    <input type="text" name="PBweight" id="PBweight" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>
<br></br>
<div style="display: flex; justify-content: flex-end;">
    <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        Add
    </button>
</div>
</form>
