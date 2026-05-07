<?php
include 'db.php'; // Database connection file
include 'templates/header.php'; // Header file

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from form inputs
    $icode = isset($_POST['icode']) ? trim($_POST['icode']) : '';
    $Description = isset($_POST['Description']) ? trim($_POST['Description']) : '';
    $Brand = isset($_POST['Brand']) ? trim($_POST['Brand']) : '';
    $Type = isset($_POST['Type']) ? trim($_POST['Type']) : '';
    $Colour = isset($_POST['Colour']) ? trim($_POST['Colour']) : '';
    $Rim = isset($_POST['Rim']) ? trim($_POST['Rim']) : '';
    $greenweight = isset($_POST['greenweight']) ? trim($_POST['greenweight']) : '';
    $stgreenweight = isset($_POST['stgreenweight']) ? trim($_POST['stgreenweight']) : '';
    $fweight = isset($_POST['fweight']) ? trim($_POST['fweight']) : '';
    $cbm = isset($_POST['cbm']) ? trim($_POST['cbm']) : '';
    $maxload = isset($_POST['maxload']) ? trim($_POST['maxload']) : '';

    // Validate required inputs
    if ($icode === '' || $Description === '' || $Brand === '' || $Type === '' || $Colour === '' || $fweight === '' || $cbm === '' || $maxload === '') {
        echo "<p style='color: red; font-weight: bold; text-align: center;'>All required fields must be filled.</p>";
    } else {
        // Validate numeric inputs (only if not empty)
        $errors = [];
        
        if ($Rim !== '' && !is_numeric($Rim)) {
            $errors[] = "Rim must be a valid number.";
        }
        if ($greenweight !== '' && !is_numeric($greenweight)) {
            $errors[] = "Greenweight must be a valid number.";
        }
        if ($stgreenweight !== '' && !is_numeric($stgreenweight)) {
            $errors[] = "Stgreenweight must be a valid number.";
        }
        if (!is_numeric($fweight)) {
            $errors[] = "Fweight must be a valid number.";
        }
        if (!is_numeric($cbm)) {
            $errors[] = "CBM must be a valid number.";
        }
        if (!is_numeric($maxload)) {
            $errors[] = "Maxload must be a valid number.";
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p style='color: red; font-weight: bold; text-align: center;'>$error</p>";
            }
        } else {
            // Prepare the insert query
            $stmt = $conn->prepare("INSERT INTO tire_details (icode, Description, Brand, Type, Colour, Rim, greenweight, stgreenweight, fweight, cbm, maxload) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                echo "<p style='color: red; font-weight: bold; text-align: center;'>Prepare failed: " . $conn->error . "</p>";
            } else {
                // Bind parameters - 11 parameters
                $stmt->bind_param(
                    'sssssssssss',
                    $icode, 
                    $Description, 
                    $Brand, 
                    $Type, 
                    $Colour, 
                    $Rim, 
                    $greenweight, 
                    $stgreenweight, 
                    $fweight, 
                    $cbm, 
                    $maxload
                );

                // Execute the query and handle errors
                if ($stmt->execute()) {
                    echo "<p style='color: green; font-weight: bold; text-align: center;'>Record successfully inserted.</p>";
                    // Optional: Clear form by redirecting
                    // header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                    // exit;
                } else {
                    echo "<p style='color: red; font-weight: bold; text-align: center;'>Execution failed: " . $stmt->error . "</p>";
                }

                // Close the statement
                $stmt->close();
            }
        }
    }
}
?>

<!-- HTML Form -->
<form method="POST" action="" style="max-width: 1200px; margin: 0 auto; padding: 50px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">
    <label for="icode" style="font-weight: bold; margin-bottom: 5px;">I code: <span style="color: red;">*</span></label>
    <input type="text" name="icode" id="icode" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Description" style="font-weight: bold; margin-bottom: 5px;">Description: <span style="color: red;">*</span></label>
    <input type="text" name="Description" id="Description" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Brand" style="font-weight: bold; margin-bottom: 5px;">Brand: <span style="color: red;">*</span></label>
    <input type="text" name="Brand" id="Brand" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Type" style="font-weight: bold; margin-bottom: 5px;">Type: <span style="color: red;">*</span></label>
    <input type="text" name="Type" id="Type" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Colour" style="font-weight: bold; margin-bottom: 5px;">Colour: <span style="color: red;">*</span></label>
    <input type="text" name="Colour" id="Colour" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="Rim" style="font-weight: bold; margin-bottom: 5px;">Rim:</label>
    <input type="text" name="Rim" id="Rim" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="greenweight" style="font-weight: bold; margin-bottom: 5px;">Greenweight:</label>
    <input type="text" name="greenweight" id="greenweight" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="stgreenweight" style="font-weight: bold; margin-bottom: 5px;">Stgreenweight:</label>
    <input type="text" name="stgreenweight" id="stgreenweight" style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="fweight" style="font-weight: bold; margin-bottom: 5px;">Fweight: <span style="color: red;">*</span></label>
    <input type="text" name="fweight" id="fweight" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="cbm" style="font-weight: bold; margin-bottom: 5px;">CBM: <span style="color: red;">*</span></label>
    <input type="text" name="cbm" id="cbm" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <label for="maxload" style="font-weight: bold; margin-bottom: 5px;">Maxload: <span style="color: red;">*</span></label>
    <input type="text" name="maxload" id="maxload" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><br>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" style="padding: 10px 100px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Insert
        </button>
    </div>
</form>