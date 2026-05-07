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
        $sql = "SELECT icode, t_size, brand, col, fit, rim, fweight FROM worder WHERE icode = '$icode'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);

                // Display the fetched item details
                
                echo '<form method="POST" action="production_store.php">';

                echo '<div class="form-group"><label for="date">Date:</label>';
                echo '<input type="date" class="form-control"id="date" name="date"><br>';

                echo '<div class="form-group"><label for="shift">shift:</label>';
               echo' <select name="shift" id="shift" required>
                <option value="DAY A">DAY A</option>
                <option value="NIGHT B">NIGHT A</option>
                <option value="DAY B">DAY B</option>
                <!-- Add more options as needed -->
            </select>';

                echo '<div class="form-group"><label for="icode">icode</label>';
                echo '<input type="text" class="form-control" name="icode" id="icode" value="' . $row['icode'] . '" required><br>';
                echo '<label for="t_size">Tire Size:</label>';
                echo '<input type="text" name="t_size" id="t_size" value="' . $row['t_size'] . '" required><br>';
                echo '<label for="brand">Brand:</label>';
                echo '<input type="text" name="brand" id="brand" value="' . $row['brand'] . '" required><br>';

                echo '<label for="fit">FIT:</label>';
                echo '<input type="text" name="fit" id="fit" value="' . $row['fit'] . '" required><br>';

                echo '<label for="col">Color:</label>';
                echo '<input type="text" name="col" id="col" value="' . $row['col'] . '" required><br>';
                
                echo '<label for="rim">RIM:</label>';
                echo '<input type="text" name="rim" id="rim" value="' . $row['rim'] . '" required><br>';

                echo '<label for="fweight">fweight:</label>';
                echo '<input type="text" name="fweight" id="fweight" value="' . $row['fweight'] . '" required><br>';


                echo '<label for="cstock">Production:</label>';
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
                          
            