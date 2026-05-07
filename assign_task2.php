<?php
include './includes/admin_header.php';
?>
<!--------------------
START - Breadcrumbs
-------------------->
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><span>alert</span></li>
</ul>
<!--------------------
END - Breadcrumbs
-------------------->
<div class="content-panel-toggler"><i class="os-icon os-icon-grid-squares-22"></i><span>Sidebar</span></div>
<div class="content-i">
    <div class="content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <div class="row">
                    <div class="col-md-6">
            
                    <head>
    <title>Item Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        form {
            max-width: 400px;
            margin: 20px 0;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #56a048;
        }
    </style>
</head>


    <?php
    if (isset($_POST['submit'])) {
        $icode = $_POST['icode'];

        // Connect to MySQL database
        $conn = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Fetch item details from the 'items' table
        $sql = "SELECT icode, t_size, brand, col, rim FROM realstock WHERE icode = '$icode'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);

                // Display the fetched item details
                echo '<form method="POST" action="store_data.php">';
                echo ' <div class="form-group"> <label for="icode">icode</label>';
                echo '<input type="text" name="icode" id="icode" value="' . $row['icode'] . '" required><br>';
                echo '<label for="t_size">Tire Size:</label>';
                echo '<input type="text" name="t_size" id="t_size" value="' . $row['t_size'] . '" required><br>';
                echo '<label for="brand">Brand:</label>';
                echo '<input type="text" name="brand" id="brand" value="' . $row['brand'] . '" required><br>';
                echo '<label for="col">Color:</label>';
                echo '<input type="text" name="col" id="col" value="' . $row['col'] . '" required><br>';
                echo '<label for="rim">RIM:</label>';
                echo '<input type="text" name="rim" id="rim" value="' . $row['rim'] . '" required><br>';
                echo '<label for="cstock">Quantity in Stock:</label>';
                echo '<input type="text" name="cstock" id="cstock" required><br>';
                echo '<input type="hidden" name="icode" value="' . $icode . '">';
                echo '<input type="submit" name="submit" value="Submit">';
                echo '</form>';
            } else {
                echo 'Item not found!';
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }

        mysqli_close($conn);
    }
    ?> 
                          
            
        
<?php include './includes/Plugin.php'; ?>
        <?php include './includes/admin_footer.php'; ?>
