<!DOCTYPE html>
<html>
<head>
    <style>
        /* Primary typeface - Cantarell */
        body {
            font-family: 'Cantarell Regular', sans-serif;
        }

        h1, h2, h3 {
            font-family: 'Cantarell Bold', sans-serif;
        }

        /* Secondary typeface - Open Sans */
        p {
            font-family: 'Open Sans Regular', sans-serif;
        }

        /* Import button styles */
        input[type="file"] {
            background-color: #F28018;
            color: #000000;
            padding: 10px;
            border: none;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #F28018;
            color: #FFFFFF;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background */
            border-radius: 50px;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
        }


        .centered-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('atire3.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body>
    <?php
    include './includes/data_base_save_update.php';
    $msg = '';
    $AppCodeObj = new databaseSave();
    if (isset($_POST['submit'])) {
        $msg = $AppCodeObj->addw("worder");
    }
    ?>
 <div class="centered-form">
    <div class="container">
       
            <h2>Please Import Work Order</h2> <!-- Centered heading -->
            <form method="post" action="import.php" enctype="multipart/form-data">
                <input type="file" name="excel_file" accept=".csv">
                <input type="submit" name="import" value="Import work order">
            </form>
        </div>
    </div>
</body>
</html>
