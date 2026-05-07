<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
include './includes/App_Code.php';
$app_code_obj=new App_Code();
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
    
    $employee_id = $_POST['empid'];
           $task  = $_POST['task'];
           //  = $_POST['file_attachment'];
    $query = "INSERT INTO `assign_task`( `emp_id`, `task`, `assignby`, `task_doc`, `work_assign_date`, `status`)";
     $query .= " VALUES ('$employee_id','$task','Admin','$task_doc',now(),'Open')";
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
                                    <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Assign Task List</h5>                                   
                                </div>  
                            </div>
                                <div class="element-box">
   <table id="example" style="width: 100%;" class="display table table-bordered table-responsive" style="width:100%">
        <thead>
                    <tr>
                        <th>S No.</th>
                        <th>Employee Name</th>
                        <th>Task</th>
                         <th>Assign By</th>
                          <th>Download File</th>
                           <th>Assign Work Date</th>
                            <th>Work Complete Date</th>
                             <th>Status</th>
                              
<!--                               <th>Edit</th>-->
                          <th>Change Status/Transfer Task</th>
                    </tr>
        </thead>
        <tbody>
                                     <?php
                                                            $emp_id=  $_SESSION['user'];
                 $qry = mysqli_query($connection, "SELECT * FROM assign_task where emp_id='$emp_id' order by work_assign_date desc") or die("select query fail" . mysqli_error());
$count = 0;
while ($row = mysqli_fetch_assoc($qry)) {
    $count = $count + 1;
  
   // $id = $row['id'];
//            $emp_code = $row['emp_code'];
//            $emp_name = $row['emp_name'];
//            $user_id = $row['user_id'];
//            $pswd = $row['pswd'];
//            $status = $row['status'];
//            $created = $row['created'];
//            $user_role = $row['user_role'];
//            $emp_pro = $row['emp_pro'];
//            $email_id = $row['email_id'];
//            $emp_mob = $row['emp_mob'];
//                                                                             $status = '';
//    $btnClass = '';
//    if ($row['status'] == '1') {
//        $btnClass = "btn  btn-success btn-sm";
//        $status = "Active";
//    } else {
//        $status = "Deactive";
//        $btnClass = "btn btn-danger btn-sm";
//    }
    $task_id = $row['task_id'];
            $emp_id1 = $row['emp_id']; 
            $task = $row['task'];
            $assignby = $row['assignby'];
            $task_doc = $row['task_doc'];
            $work_assign_date = $row['work_assign_date'];
            $work_com_date = $row['work_com_date'];
           $status  = $row['status'];
               $remark  = $row['remark'];
    ?>
                    <tr>
  <td><?php echo $count;?></td>
  <td> <?php echo $app_code_obj->getName($emp_id1);?></td>
  <td><?php echo $task;?></td>
  <td><?php echo $assignby;?></td> 
   <td>
      <?php if($task_doc !='')
      {?>
      <a href="task_doc/<?php echo $task_doc;?>" class="btn btn-primary">Download</a>  
      <?php }?>
  </td> 
    <td><?php echo $work_assign_date;?></td> 
  <td><?php echo $work_com_date;?></td> 
  <td><a href="#" class="btn btn-success"> <?php echo $status;?></a> <br><?php echo $remark;?></td> 

    
<!--    <td> <img src="user_profile/<?php echo $emp_pro;?>" height="80px" width="80px"></td> 
      <td><?php echo $created;?></td> 
      <td><a href="employee.php?id=<?php echo $row['task_id']; ?>&Status=<?php echo $row['status']; ?>" class="<?php echo $btnClass; ?> " ><?php echo $status; ?></a></td>
    <td><a class="btn btn-primary" href="employee.php?source=update_emp&emp_id=<?php echo $id;?>">Edit</a></td>-->
                              <td>
                                  <a style="width: 100%;" class="btn btn-danger" href="emp_change_status.php?task_id=<?php echo $task_id;?>">Change Status</a>
                                  <br>
                                  <br>
                                  <a style="width: 100%;" class="btn btn-danger" href="tran_assign_task.php?task_id=<?php echo $task_id;?>">Transfer Task</a>
                              </td>
                    </tr>
<?php }?>
        </tbody> </table>
   </div>
                            </div>
           
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