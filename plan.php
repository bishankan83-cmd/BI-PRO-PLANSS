
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Funda Of Web IT</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h3>PLAN</h3>
                        <hr style="border-top:1px dotted #ccc;"/>
                        <div class="col-md-6">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="work-order-id" class="form-label">Work Order ID:</label>
                                    <input type="text" class="form-control" id="erp" name="erp" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>

                    
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header">
                       

                        <h3>PLAN </h3>
		<hr style="border-top:1px dotted #ccc;"/>
		<div class="col-md-6">
	
			<br> <form action="subtract.php" method="post" >
         <input type="submit" value="Perform subtraction">
      </form>
			<br />
 
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">
                                <!-- Display the tire information here -->
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="header">
                                        <th>erp</th>
                                            <th>Item code</th>
                                            <th>Tire Size</th>
                                            <th>Brand</th>
                                         
                                            <th>Fit</th>
                                            <th>Colour</th>
                                            <th>Rim</th>
                                            <th>Construction</th>
                                            <th>Average Finish Tyre weight - kgs</th>
                                            <th>Per Volume/cbm</th>
                                            <th>Qty New pcs</th>
                                            <th>Total Volume cbm</th>
                                            <th>Total Tones kgs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            // Check if the form is submitted
                                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                                // Retrieve the entered work order ID
                                                $erp = $_POST['erp'];
                                                
                                                // Create a database connection
                                                $con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen");

                                                // Prepare the query to retrieve tire information based on the work order ID
                                                $query = "SELECT * FROM worder WHERE erp = '$erp'";
                                                $query_run = mysqli_query($con, $query);

                                                if (mysqli_num_rows($query_run) > 0) {
                                                    foreach ($query_run as $items) {
                                                        ?>
                                                        <tr>
                                                            <td><?= $items['erp']; ?></td>
                                                            <td><?= $items['icode']; ?></td>
                                                            <td><?= $items['t_size']; ?></td>
                                                            <td><?= $items['brand']; ?></td>
                                                            <td><?= $items['col']; ?></td>
                                                            <td><?= $items['fit']; ?></td>
                                                            <td><?= $items['rim']; ?></td>
                                                            <td><?= $items['cons']; ?></td>
                                                            <td><?= $items['fweight']; ?></td>
                                                            <td><?= $items['ptv']; ?></td>
<td><?= $items['new']; ?></td>
<td><?= $items['cbm']; ?></td>
<td><?= $items['kgs']; ?></td>
</tr>
<?php
}
} else {
    echo "<tr><td colspan='12'>No tire information found for the provided work order ID.</td></tr>";
}
}
?>
</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
