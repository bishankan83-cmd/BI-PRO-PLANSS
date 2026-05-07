<?php
include './includes/admin_header.php';

$hostname = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
                    <div class="col-md-6" id >
                <form action="assign_task2.php" method="post" enctype="multipart/form-data">

                    
                            <div class="row">
                                 <div class="col-md-12">
                                    <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Add stock</h5>                                   
                                </div>  
                                <form method="POST" action="add_production.php">
                                <div class="col-sm-6">
                                <div class="form-group"><label for="icode">Item Code:</label>
        <input class="form-control" type="text" name="icode" id="icode" required> <br>

  
        <input class="btn btn-primary" type="submit" name="submit" value="Submit">
    </form>
                
                            

      
    </form>
                            </div>
                        </div>
                </form>
                    </div>
                      <div class="col-md-6">
                          <br>
               
                                                               
                </table>
                      </div>
            </div>
        </div></div>




        
<?php include './includes/Plugin.php'; ?>
        <?php include './includes/admin_footer.php'; ?>

