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
                        <h4>BOM</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">

                                <form action="" method="GET">
                                    <div class="input-group mb-3">
                                        <input type="text" name="search" required value="<?php if(isset($_GET['search'])){echo $_GET['search']; } ?>" class="form-control" placeholder="Enter pid">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


                
                    <div class="card-body">
                        <table class="table table-bordered">
                        <tr class="header">
                        <th>PID</th>
			<th>Description</th>
            <th>Tire Size</th>
            <th>Brand</th>
            <th>Type</th>
            <th>Colour</th>
            <th>Rim Width</th>
            <th>Compound Weight</th>

            <th>With steel weight</th>
            <th>Bead weight</th>
            <th>Total Weigth (with steel + Bead)</th>
           

			<th>Finished tire weight</th>
            <th>Steel band type</th>
            <th>Steel band weight</th>
            <th>Bead type</th>
            <th>Noof Bead</th>
            <th>Profile Type</th>
            <th>Profile weight</th>
            <th>Base type</th>
            <th>Base weight</th>
            <th>Bonding Type</th>
            <th>Bonding weight</th>
            <th>Cushion type</th>
            <th>Cushion weight</th>
            <th>threat</th>
            <th>threat weight</th>

            
            

            
            

		</tr>
                            <tbody>
                                <?php 
                                    $con = mysqli_connect("localhost","planatir_task_managemen","Bishan@1919","planatir_task_managemen");

                                    if(isset($_GET['search']))
                                    {
                                        $filtervalues = $_GET['search'];
                                        $query = "SELECT * FROM bom WHERE CONCAT(pid) LIKE '%$filtervalues%' ";
                                        $query_run = mysqli_query($con, $query);

                                        if(mysqli_num_rows($query_run) > 0)
                                        {
                                            foreach($query_run as $items)
                                            {
                                                ?>
                                                <tr>
                                                    <td><?= $items['pid']; ?></td>
                                                    <td><?= $items['Description']; ?></td>
                                                    <td><?= $items['Tsize']; ?></td>
                                                    <td><?= $items['brand']; ?></td>
                                                    <td><?= $items['type']; ?></td>
                                                    <td><?= $items['colour']; ?></td>
                                                    <td><?= $items['rwidth']; ?></td>
                                                    <td><?= $items['comweight']; ?></td>
                                                    <td><?= $items['tweight']; ?></td>
                                                    <td><?= $items['withsteel']; ?></td>
                                                    <td><?= $items['bead']; ?></td>
                                                    <td><?= $items['finishweight']; ?></td>
                                                    <td><?= $items['sbtype']; ?></td>
                                                    <td><?= $items['sbweight']; ?></td>
                                                    <td><?= $items['beadtype']; ?></td>
                                                    <td><?= $items['Nbead']; ?></td>
                                                    <td><?= $items['ptype']; ?></td>
                                                    <td><?= $items['pweight']; ?></td>
                                                    <td><?= $items['btype']; ?></td>
                                                    <td><?= $items['bweight']; ?></td>
                                                    <td><?= $items['bontype']; ?></td>
                                                    <td><?= $items['bonweight']; ?></td>
                                                    <td><?= $items['ctype']; ?></td>
                                                    <td><?= $items['cweight']; ?></td>
                                                    <td><?= $items['threat']; ?></td>
                                                    <td><?= $items['thweight']; ?></td>
                                                  
                                                    

                                                <?php
                                            }
                                        }
                                        else
                                        {
                                            ?>
                                                <tr>
                                                    <td colspan="4">No Record Found</td>
                                                </tr>
                                            <?php
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

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>