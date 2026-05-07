<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
$msg = '';
$AppCodeObj = new databaseSave();
if (isset($_POST['submit'])) {
  //  $msg = $AppCodeObj->Insert_pan_data("pan_mst");
//    $userID = $_SESSION['user'];
//    $NewPSWD = $_POST['NewPSWD'];
//    $oldPSWD = $_POST['oldPSWD'];
    $task_doc = $_FILES['file_attachment']['name'];
    $task_doc_temp = $_FILES['file_attachment']['tmp_name'];
    move_uploaded_file($task_doc_temp, "task_doc/$task_doc");
       $emp_id=  $_SESSION['user'];
    $employee_id =$emp_id; //$_POST['empid'];
           $task  = $_POST['task'];
           //  = $_POST['file_attachment'];
    $query = "INSERT INTO `assign_task`( `emp_id`, `task`, `assignby`, `task_doc`, `work_assign_date`, `status`)";
     $query .= " VALUES ('$employee_id','$task','Employee','$task_doc',now(),'Open')";
    $update_password = mysqli_query($connection, $query);
    if (!$update_password) {
        die('QUERY FAILD change pashword' . mysqli_error($connection));
    } else {

        echo "<script>alert('Record Save Successfully');</script>";
       // return 'pass';
    }
}
?>
<!--------------------
START - Breadcrumbs
-------------------->
<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><span>Assign Task</span></li>
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
                                    <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Assign Task</h5>                                   
                                </div>  
                            </div>
                                  <form class="container" action="#" method="post" enctype="multipart/form-data">


                            <div class="row">

<!--                          
                                <fieldset class="col-md-12">
                                    <legend>Company Details
                                        <hr></legend>
                                </fieldset>-->

<!--                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">Employee</label>
                                        <select id="emp_id" name="empid" class="form-control">
                                            <option>--select Employee--</option>
                                                                                                       <?php
                                                          
                 $qry = mysqli_query($connection, "SELECT * FROM emp_login where user_role='employee' and status='1'") or die("select query fail" . mysqli_error());
$count = 0;
while ($row = mysqli_fetch_assoc($qry)) {
    $count = $count + 1;
  
    $id = $row['id'];
            $emp_code = $row['emp_code'];
            $emp_name = $row['emp_name'];
        
            echo "<option value=".$id.">".$emp_code."/".$emp_name."</option>";
}?>
                                              
                                            
                                        </select>
                                    </div>
                                </div>-->
                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">Task</label>
                                        <input class="form-control" name="task" placeholder="Enter Task" type="text">
                                    </div>
                                </div>
  <div class="col-sm-3">
                                    <div class="form-group"><label for="">File Attachment</label>
                                        <input name="file_attachment" type="file">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <br>
                                         <input class="btn btn-primary" type="submit" value="Assign Task" name="submit">
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
                                