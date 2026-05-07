<?php
include './includes/data_base_save_update.php';
$AppCodeObj = new databaseSave();

if (isset($_POST['submit'])) {
    // Retrieve form data
    $Datetime = $_POST['datetime'];
    $takeDatetime = $_POST['take_datetime'];
    $erp = $_POST['erp'];

    // Perform data validation and sanitization

    // Extract date and time components
    $formatDatetime = date("Y-m-d H:i:s", strtotime($Datetime));
    $formattedTakeDatetime = date("Y-m-d H:i:s", strtotime($takeDatetime));

    // Save the data to the database
    $result = $AppCodeObj->addwork("work_order", $formatDatetime, $formattedTakeDatetime, $erp);

    if ($result) {
        // Data saved successfully
        header('Location: rimport2.php');
        exit();
    } else {
        // Handle the error
        $msg = "Error occurred while saving the data.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<style>
        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire3.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h5 {
            font-weight: bold;
            font-size: 24px;
            font-family: 'Cantarell Bold', sans-serif;
            text-align: center;
            margin-bottom: 20px;
        }

        .alert {
            background-color: #FFD700;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="datetime-local"],
        input[type="text"] {
            width: 91%;
            padding: 10px;
            border: 1px solid #000000;
            border-radius: 4px;
            font-family: 'Open Sans', sans-serif;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h5>Add Work Order</h5>
        <?php if (isset($msg)) : ?>
            <div class="alert">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="datetime">Work Order Insert Date</label>
                <input name="datetime" id="datetime" type="datetime-local" required>
            </div>
            
            <div class="form-group">
                <label for="take_datetime">Work Order Take Date</label>
                <input name="take_datetime" id="take_datetime" type="datetime-local" required>
            </div>

            <div class="form-group">
                <label for="erp">Ref. ERP CO.No</label>
                <input name="erp" id="erp" placeholder="" type="text" required>
            </div>
            <input type="submit" value="Submit Now" name="submit">
        </form>
    </div>
</body>
</html>
