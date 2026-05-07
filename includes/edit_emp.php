                                                             <?php
                                                             $id=$_GET['emp_id'];
                 $qry = mysqli_query($connection, "SELECT * FROM emp_login where id='$id'and user_role='employee'") or die("select query fail" . mysqli_error());
$count = 0;
while ($row = mysqli_fetch_assoc($qry)) {
    $count = $count + 1;
  
    $id = $row['id'];
            $emp_code = $row['emp_code'];
            $emp_name = $row['emp_name'];
            $user_id = $row['user_id'];
            $pswd = $row['pswd'];
            $status = $row['status'];
            $created = $row['created'];
            $user_role = $row['user_role'];
            $emp_pro = $row['emp_pro'];
            $email_id = $row['email_id'];
            $emp_mob = $row['emp_mob'];

        

    ?>
<div class="element-box">

                            <div class="row">
                                 <div class="col-md-12">
                                     <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Update Employee <a style="font-size: 14px;" href="employee.php?source=add_emp">Add New Employee</a></h5>                                   
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
                                    <div class="form-group"><label for="">Employee Code</label>
                                        <input class="form-control" value="<?php echo $emp_code;?>" name="emp_code" placeholder="Employee Code" type="text">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">Name</label>
                                        <input class="form-control" value="<?php echo $emp_name;?>" name="Name" placeholder="Name" type="text">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group"><label for="">Email ID</label>
                                        <input class="form-control" value="<?php echo $email_id;?>" name="emailid" placeholder="Email ID" type="email">
                                    </div>
                                </div>
 <div class="col-sm-3">
                                    <div class="form-group"><label for="">Mobile No.</label>
                                        <input class="form-control" value="<?php echo $emp_mob;?>" name="mobile" placeholder="Mobile No." type="text">
                                    </div>
                                </div>
 <div class="col-sm-3">
                                    <div class="form-group"><label for="">Profile</label>
                                        <img src="user_profile/<?php echo $emp_pro;?>" height="80px" width="80px">
                                        <input name="profile" type="file">
                                    </div>
                                </div>
 <div class="col-sm-3">
                                    <div class="form-group"><label for="">User ID</label>
                                        <input class="form-control" value="<?php echo $email_id;?>" name="userid" placeholder="User ID" type="text">
                                    </div>
                                </div>

 <div class="col-sm-3">
                                    <div class="form-group"><label for="">Password</label>
                                        <input class="form-control" value="<?php echo $pswd;?>" name="pswd" placeholder="password" type="text">
                                    </div>
                                </div>




                                <div class="form-buttons-w text-right">
                                    <input class="btn btn-primary" type="submit" value="Update Employee" name="update">
                                    
                                </div>
                            </div>
                        </form>
                            </div>
<?php }?>