<?php
include 'db.php';
include 'templates/header.php';

// Validate and retrieve the ID from the GET request
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    die("Invalid ID provided.");
}

// Fetch the record for the given ID
$result = $conn->query("SELECT * FROM bom_new WHERE id = $id");
if ($result && $result->num_rows > 0) {
    $item = $result->fetch_assoc();
} else {
    die("Record not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input data
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

    // Prepare the SQL statement
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
        
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sssssssssssssssssssssssssssi",
        $Item, $icode, $t_size, $Item_Description, 
        $a, $b, $c, $d, $e, $f, $g, $h, 
        $i, $j, $k, $l, $m, $n, $o, $p, 
        $q, $r, $Grand_Totalcompound_weight, $Color, 
        $Brand, $Green_Tire_weight, $PBWeight, $id
    );

    // Execute the statement
    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>




<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-Item Descriptionor: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">

    <label for="Item" style="font-weight: bold; margin-bottom: 5px;">Item:</label>
    <input type="text" name="Item" id="Item" value="<?php echo htmlspecialchars($item['Item']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode" value="<?php echo htmlspecialchars($item['icode']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="t_size" style="font-weight: bold; margin-bottom: 5px;">Tire Size:</label>
    <input type="text" name="t_size" id="t_size" value="<?php echo htmlspecialchars($item['t_size']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Item_Description" style="font-weight: bold; margin-bottom: 5px;">Item Description:</label>
    <input type="text" name="Item_Description" id="Item_Description" value="<?php echo htmlspecialchars($item['Item_Description']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="a" style="font-weight: bold; margin-bottom: 5px;">A:</label>
    <input type="text" name="a" id="a" value="<?php echo htmlspecialchars($item['a']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="b" style="font-weight: bold; margin-bottom: 5px;">B:</label>
    <input type="text" name="b" id="b" value="<?php echo htmlspecialchars($item['b']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="c" style="font-weight: bold; margin-bottom: 5px;">C:</label>
    <input type="text" name="c" id="c" value="<?php echo htmlspecialchars($item['c']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="d" style="font-weight: bold; margin-bottom: 5px;">D:</label>
    <input type="text" name="d" id="d" value="<?php echo htmlspecialchars($item['d']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="e" style="font-weight: bold; margin-bottom: 5px;">E:</label>
    <input type="text" name="e" id="e" value="<?php echo htmlspecialchars($item['e']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="f" style="font-weight: bold; margin-bottom: 5px;">F:</label>
    <input type="text" name="f" id="f" value="<?php echo htmlspecialchars($item['f']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="g" style="font-weight: bold; margin-bottom: 5px;">G:</label>
    <input type="text" name="g" id="g" value="<?php echo htmlspecialchars($item['g']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="h" style="font-weight: bold; margin-bottom: 5px;">H:</label>
    <input type="text" name="h" id="h" value="<?php echo htmlspecialchars($item['h']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="i" style="font-weight: bold; margin-bottom: 5px;">I:</label>
    <input type="text" name="i" id="i" value="<?php echo htmlspecialchars($item['i']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="j" style="font-weight: bold; margin-bottom: 5px;">J:</label>
    <input type="text" name="j" id="j" value="<?php echo htmlspecialchars($item['j']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="k" style="font-weight: bold; margin-bottom: 5px;">K:</label>
    <input type="text" name="k" id="k" value="<?php echo htmlspecialchars($item['k']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="l" style="font-weight: bold; margin-bottom: 5px;">L:</label>
    <input type="text" name="l" id="l" value="<?php echo htmlspecialchars($item['l']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="m" style="font-weight: bold; margin-bottom: 5px;">M:</label>
    <input type="text" name="m" id="m" value="<?php echo htmlspecialchars($item['m']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="n" style="font-weight: bold; margin-bottom: 5px;">N:</label>
    <input type="text" name="n" id="n" value="<?php echo htmlspecialchars($item['n']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="o" style="font-weight: bold; margin-bottom: 5px;">O:</label>
    <input type="text" name="o" id="o" value="<?php echo htmlspecialchars($item['o']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="p" style="font-weight: bold; margin-bottom: 5px;">P:</label>
    <input type="text" name="p" id="p" value="<?php echo htmlspecialchars($item['p']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="q" style="font-weight: bold; margin-bottom: 5px;">Q:</label>
    <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($item['q']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="r" style="font-weight: bold; margin-bottom: 5px;">R:</label>
    <input type="text" name="r" id="r" value="<?php echo htmlspecialchars($item['r']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Grand_Totalcompound_weight" style="font-weight: bold; margin-bottom: 5px;">Grand Total Compound Weight:</label>
    <input type="text" name="Grand_Totalcompound_weight" id="Grand_Totalcompound_weight" value="<?php echo htmlspecialchars($item['Grand_Totalcompound_weight']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Color" style="font-weight: bold; margin-bottom: 5px;">Color:</label>
    <input type="text" name="Color" id="Color" value="<?php echo htmlspecialchars($item['Color']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Brand" style="font-weight: bold; margin-bottom: 5px;">Brand:</label>
    <input type="text" name="Brand" id="Brand" value="<?php echo htmlspecialchars($item['Brand']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Green_Tire_weight" style="font-weight: bold; margin-bottom: 5px;">Green Tire weight:</label>
    <input type="text" name="Green_Tire_weight" id="Green_Tire_weight" value="<?php echo htmlspecialchars($item['Green_Tire_weight']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="PBweight" style="font-weight: bold; margin-bottom: 5px;">PBweight:</label>
    <input type="text" name="PBweight" id="PBweight" value="<?php echo htmlspecialchars($item['PBweight']); ?>" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>
    
    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-Item Descriptionor: #007bff; Item Descriptionor: white; border: none; border-radius: 5px; cursor: pointer;">
            Update
        </button>
    </div>
</form>
