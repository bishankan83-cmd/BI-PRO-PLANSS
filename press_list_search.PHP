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
                        <h4>PRESS LIST</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">

                                <form action="" method="GET">
                                    <div class="input-group mb-3">
                                        <input type="text" name="search" required value="<?php if(isset($_GET['search'])){echo $_GET['search']; } ?>" class="form-control" placeholder="Enter icode">
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
                        <th>icode</th>
			<th>Tire Size</th>
			<th>Brand</th>

            <th>FIT</th>
            <th>Colour</th>
			<th>Rim Width</th>
            <th>green tyre weight with steel</th>
            <th>NO OF MOULDS</th>
            <th>AVAILABLE CAVITY PER PRESS</th>
            <th>Curing Group</th>
			<th>Press-01</th>
            <th>Press-02</th>
            <th>Press-03</th>       			
            <th>Press-04</th>
            <th>Press-05</th>
            <th>Press-06</th>
            <th>Press-07</th>


            
            

            
            

		</tr>
                            <tbody>
                                <?php 
                                    $con = mysqli_connect("localhost","planatir_task_managemen","Bishan@1919","planatir_task_managemen");

                                    if(isset($_GET['search']))
                                    {
                                        $filtervalues = $_GET['search'];
                                        $query = "SELECT * FROM selectpress WHERE CONCAT(icode) LIKE '%$filtervalues%' ";
                                        $query_run = mysqli_query($con, $query);

                                        if(mysqli_num_rows($query_run) > 0)
                                        {
                                            foreach($query_run as $items)
                                            {
                                                ?>
                                                <tr>
                                                <td><?= $items['icode']; ?></td>
                                                    <td><?= $items['t_size']; ?></td>
                                                    <td><?= $items['brand']; ?></td>
                                                    <td><?= $items['fit']; ?></td>
                                                    <td><?= $items['col']; ?></td>
                                                    <td><?= $items['rim']; ?></td>
                                              
                                                    <td><?= $items['gweight']; ?></td>
                                                    <td><?= $items['nmould']; ?></td>
                                                    <td><?= $items['cpress']; ?></td>
                                                    <td><?= $items['curing_group']; ?></td>
                                                    <td><?= $items['Press-01']; ?></td>
                                                    <td><?= $items['Press-02']; ?></td>
                                                    <td><?= $items['Press-03']; ?></td>
                                                    <td><?= $items['Press-04']; ?></td>
                                                    <td><?= $items['Press-05']; ?></td>
                                                    <td><?= $items['Press-06']; ?></td>
                                                    <td><?= $items['Press-07']; ?></td>
                                                   
                                                   
                                                  
                                                    
                                                    

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