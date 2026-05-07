<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
$msg = '';

?>
<!--------------------
START - Breadcrumbs
-------------------->
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><span>Employee</span></li>
</ul>
<!--------------------
END - Breadcrumbs
-------------------->
<div class="content-panel-toggler"><i class="os-icon os-icon-grid-squares-22"></i><span>Sidebar</span></div>
<div class="content-i">
    <div class="content-box">
        <div class="element-wrapper">
            <?php
            if (isset($_GET['source'])) {

                $source = $_GET['source'];
            } else {

                $source = '';
            }

            switch ($source) {

                case 'add_emp';
                    include "includes/add_compund.php";
                    break;

                case 'update_emp';
                    include "includes/edit_emp.php";
                    break;

                default:
                    include "includes/compund_list.php";
                    break;
            }
            ?>
            <!--            <div class="element-box">
            
                                        <div class="row">
                                             <div class="col-md-12">
                                                <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Add New Employee</h5>                                   
                                            </div>  
                                        </div>
                                              <form class="container" action="#" method="post" enctype="multipart/form-data">
            
            
                                        <div class="row">
            
                                      
                                            <fieldset class="col-md-12">
                                                <legend>Company Details
                                                    <hr></legend>
                                            </fieldset>
            
                                            <div class="col-sm-3">
                                                <div class="form-group"><label for="">Employee Code</label>
                                                    <input class="form-control" name="emp_code" placeholder="Employee Code" type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group"><label for="">Name</label>
                                                    <input class="form-control" name="Name" placeholder="Name" type="text">
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group"><label for="">Email ID</label>
                                                    <input class="form-control" name="emailid" placeholder="Email ID" type="email">
                                                </div>
                                            </div>
             <div class="col-sm-3">
                                                <div class="form-group"><label for="">Mobile No.</label>
                                                    <input class="form-control" name="mobile" placeholder="Mobile No." type="text">
                                                </div>
                                            </div>
             <div class="col-sm-3">
                                                <div class="form-group"><label for="">Profile</label>
                                                    <input name="profile" type="file">
                                                </div>
                                            </div>
             <div class="col-sm-3">
                                                <div class="form-group"><label for="">User ID</label>
                                                    <input class="form-control" name="userid" placeholder="User ID" type="text">
                                                </div>
                                            </div>
            
             <div class="col-sm-3">
                                                <div class="form-group"><label for="">Password</label>
                                                    <input class="form-control" name="pswd" placeholder="password" type="password">
                                                </div>
                                            </div>
            
            
            
            
                                            <div class="form-buttons-w text-right">
                                                <input class="btn btn-primary" type="submit" value="Add Employee" name="submit">
                                            </div>
                                        </div>
                                    </form>
                                        </div>-->

        </div>
    </div>
</div>
</div>



<?php include './includes/Plugin.php'; ?>
<?php include './includes/admin_footer.php'; ?>
                                <script>
$(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'pdfHtml5'
        ]
    } );
} );
        </script> 