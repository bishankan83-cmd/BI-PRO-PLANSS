<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
$msg = '';
$AppCodeObj = new databaseSave();
if (isset($_POST['submit'])) {
    $emp_id = $_SESSION['user'];
    $task_id = $_GET['task_id'];
    // $employee_id =$emp_id; 
    $status = $_POST['status'];
    $remark = $_POST['remark'];
    //$status  = $_POST['status'];
//    $query = "INSERT INTO `assign_task`( `emp_id`, `task`, `assignby`, `task_doc`, `work_assign_date`, `status`)";
//     $query .= " VALUES ('$employee_id','$task','Employee','$task_doc',now(),'Open')";
    $query = "UPDATE `assign_task` SET ";
//                  $query .=  "`task_id`='',";
//                  $query .=  "`emp_id`='',";
    //   $query .=  "`task`='',";
    //    $query .=  "`assignby`='',";
    //   $query .=  "`task_doc`='',";
    //    $query .=  "`work_assign_date`='',";
    if ($status=='Close') {
        $query .= "`work_com_date`=now(),";
    }

    $query .= "`status`='$status',";
    $query .= "`remark`='$remark' WHERE `task_id`='$task_id' and `emp_id`='$emp_id'";
    $update_password = mysqli_query($connection, $query);
    if (!$update_password) {
        die('QUERY FAILD change pashword' . mysqli_error($connection));
    } else {

        echo "<script>alert('Record Save Successfully'); window.location.href='emp_assign_task_list.php';</script>";
        // return 'pass';
    }
}
?>
<!--------------------
START - Breadcrumbs
-------------------->
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><span>Change Status</span></li>
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
                        <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Change Status</h5>                                   
                    </div>  
                </div>
                <form class="container" action="#" method="post" enctype="multipart/form-data">


                    <div class="row">


                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Change Status</label>
                                <select name="status" class="form-control" >
                                    <option>--Select--</option>
                                    <option value="Open">Open</option>
                                    <option value="WIP">WIP</option>
                                    <option value="Close">Close</option>  
                                    <option value="Cancel">Cancel</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Remark</label>
                                <input type="text" name="remark" class="form-control" placeholder="Remark">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <br>
                                <input class="btn btn-primary" type="submit" value="Change Status" name="submit">
                                <!--<label for="">Conform Password</label>-->
                                <!--<input class="form-control" name="CPSWD" placeholder="Conform Password" type="password">-->
                            </div>
                        </div>




                        <!--                                <div class="form-buttons-w text-right">
                                                            <input class="btn btn-primary" type="submit" value="Change Password" name="submit">
                                                        </div>-->
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
</div>



<?php include './includes/Plugin.php'; ?>
<?php include './includes/admin_footer.php'; ?>
                                