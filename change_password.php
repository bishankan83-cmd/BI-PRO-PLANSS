<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
$msg = '';
$AppCodeObj = new databaseSave();
if (isset($_POST['submit'])) {
  //  $msg = $AppCodeObj->Insert_pan_data("pan_mst");
    $userID = $_SESSION['user'];
    $NewPSWD = $_POST['NewPSWD'];
    $oldPSWD = $_POST['oldPSWD'];
    $update_psqd = "UPDATE `emp_login` SET pswd='$NewPSWD' where  `id`='$userID' and pswd='$oldPSWD'  ";
    $update_password = mysqli_query($connection, $update_psqd);
    if (!$update_password) {
        die('QUERY FAILD change pashword' . mysqli_error($connection));
    } else {

        echo "<script>alert('password change successfully');</script>";
       // return 'pass';
    }
}
?>
<!--------------------
START - Breadcrumbs
-------------------->
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><span>Change Password</span></li>
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
                                 <div class="col-md-12">
                                    <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Change Password</h5>                                   
                                </div>  
                            </div>
                                  <form class="container" action="#" method="post" enctype="multipart/form-data">


                            <div class="row">

<!--                          
                                <fieldset class="col-md-12">
                                    <legend>Company Details
                                        <hr></legend>
                                </fieldset>-->

                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">Old Password </label>
                                        <input class="form-control" name="oldPSWD" placeholder="Old Password" type="password">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">New Password</label>
                                        <input class="form-control" name="NewPSWD" placeholder="New Password" type="password">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">Conform Password</label>
                                        <input class="form-control" name="CPSWD" placeholder="Conform Password" type="password">
                                    </div>
                                </div>




                                <div class="form-buttons-w text-right">
                                    <input class="btn btn-primary" type="submit" value="Change Password" name="submit">
                                </div>
                            </div>
                        </form>
                            </div>
           
            </div>
        </div>
    </div>
</div>

                                
                                
<?php include './includes/Plugin.php'; ?>
        <?php include './includes/admin_footer.php'; ?>
                                