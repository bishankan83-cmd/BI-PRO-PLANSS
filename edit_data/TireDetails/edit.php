<?php
include 'db.php'; // Database connection file
include 'templates/header.php'; // Header file

// Get the icode from the query string
$icode = $_GET['icode'] ?? null;

// Fetch the item details if icode is provided
if ($icode) {
    $stmt = $conn->prepare("SELECT * FROM tire_details WHERE icode = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('s', $icode);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    if (!$item) {
        die("No item found with the provided icode.");
    }
    $stmt->close();
} else {
    die("Icode is missing in the URL.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icode = $_POST['icode'];
    $Description = $_POST['Description'];
    $Brand = $_POST['Brand'];
    $Type = $_POST['Type'];
    $Spec = $_POST['Spec'];
    $Colour = $_POST['Colour'];
    $Rim = $_POST['Rim'] ?? null;
    $greenweight = $_POST['greenweight'] ?? null;
    $stgreenweight = $_POST['stgreenweight'] ?? null;
    $fweight = $_POST['fweight'];
    $cbm = $_POST['cbm'];
    $maxload = $_POST['maxload'];

    // Validate decimal inputs
    if (!empty($Rim) && !is_numeric($Rim)) {
        die("Rim must be a valid decimal number.");
    }
    if (!empty($greenweight) && !is_numeric($greenweight)) {
        die("Greenweight must be a valid decimal number.");
    }
    if (!empty($stgreenweight) && !is_numeric($stgreenweight)) {
        die("Stgreenweight must be a valid decimal number.");
    }
    if (!is_numeric($fweight)) {
        die("Fweight must be a valid decimal number.");
    }
    if (!is_numeric($cbm)) {
        die("CBM must be a valid decimal number.");
    }
    if (!is_numeric($maxload)) {
        die("Maxload must be a valid decimal number.");
    }

    // Prepare the update query
    $stmt = $conn->prepare("UPDATE tire_details SET icode = ?, Description = ?, Brand = ?, Type = ?, Spec = ?, Colour = ?, Rim = ?, greenweight = ?, stgreenweight = ?, fweight = ?, cbm = ?, maxload = ? WHERE icode = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters to the query - all as strings since table uses varchar
    $stmt->bind_param(
        'sssssssssssss', 
        $icode, $Description, $Brand, $Type, $Spec, $Colour, $Rim, $greenweight, $stgreenweight, $fweight, $cbm, $maxload, $item['icode']
    );

    // Execute the query and handle errors
    if ($stmt->execute()) {
        header('Location: index.php'); // Redirect to index after successful update
        exit;
    } else {
        die("Execution failed: " . $stmt->error);
    }
}
?>

<!-- HTML Form -->
<form method="POST" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code:</label>
    <input type="text" name="icode" id="icode" value="<?php echo htmlspecialchars($item['icode']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Description" style="font-weight: bold; margin-bottom: 5px;">Description:</label>
    <input type="text" name="Description" id="Description" value="<?php echo htmlspecialchars($item['Description']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Brand" style="font-weight: bold; margin-bottom: 5px;">Brand:</label>
    <input type="text" name="Brand" id="Brand" value="<?php echo htmlspecialchars($item['Brand']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Type" style="font-weight: bold; margin-bottom: 5px;">Type:</label>
    <input type="text" name="Type" id="Type" value="<?php echo htmlspecialchars($item['Type']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Spec" style="font-weight: bold; margin-bottom: 5px;">Spec:</label>
    <input type="text" name="Spec" id="Spec" value="<?php echo htmlspecialchars($item['Spec']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Colour" style="font-weight: bold; margin-bottom: 5px;">Colour:</label>
    <input type="text" name="Colour" id="Colour" value="<?php echo htmlspecialchars($item['Colour']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Rim" style="font-weight: bold; margin-bottom: 5px;">Rim:</label>
    <input type="number" name="Rim" id="Rim" value="<?php echo htmlspecialchars($item['Rim']); ?>" step="0.01" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="greenweight" style="font-weight: bold; margin-bottom: 5px;">Greenweight:</label>
    <input type="number" name="greenweight" id="greenweight" value="<?php echo htmlspecialchars($item['greenweight']); ?>" step="0.01" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="stgreenweight" style="font-weight: bold; margin-bottom: 5px;">Stgreenweight:</label>
    <input type="number" name="stgreenweight" id="stgreenweight" value="<?php echo htmlspecialchars($item['stgreenweight']); ?>" step="0.01" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="fweight" style="font-weight: bold; margin-bottom: 5px;">Fweight:</label>
    <input type="number" name="fweight" id="fweight" value="<?php echo htmlspecialchars($item['fweight']); ?>" step="0.01" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="cbm" style="font-weight: bold; margin-bottom: 5px;">CBM:</label>
    <input type="number" name="cbm" id="cbm" value="<?php echo htmlspecialchars($item['cbm']); ?>" step="0.01" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="maxload" style="font-weight: bold; margin-bottom: 5px;">Maxload:</label>
    <input type="number" name="maxload" id="maxload" value="<?php echo htmlspecialchars($item['maxload']); ?>" step="0.01" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Update
        </button>
    </div>
</form>