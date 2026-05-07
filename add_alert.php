<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
$msg = '';
$AppCodeObj = new databaseSave();
if (isset($_POST['submit'])) {
    $msg = $AppCodeObj->alert("news_and_update");
}
if(isset($_GET['delete']))
{
    $id=$_GET['delete'];
    $delete= mysqli_query($connection, "delete from news_and_update where news_id='$id'");
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
                    <div class="col-md-6">
                <form action="#" method="post" enctype="multipart/form-data">

                    
                            <div class="row">
                                 <div class="col-md-12">
                                    <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">alert</h5>                                   
                                </div>  
                                <div class="col-sm-6">
                                    <div class="form-group"><label for="">alert</label>
                                        <input class="form-control" name="news" placeholder="add alert" type="text">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group"><label for="">Remark</label>
                                        <input class="form-control" name="Remark" placeholder="Remark" type="text">
                                    </div>
                                </div>
                     
                           
                            <div class="form-buttons-w text-right">
                                <input class="btn btn-primary" type="submit" value="Submit Now" name="submit">
                            </div>
                        </div>
                </form>
                    </div>
                      <div class="col-md-6">
                          <br>
                <table class="dataTable table table-responsive">
                    <tr>
                        <th>S No.</th>
                        <th>alert</th>
                         <th>remark</th>
                          <th>Date</th>
                          <th>Delete</th>
                    </tr>
                                                               <?php
                 $qry = mysqli_query($connection, "SELECT * FROM news_and_update where news_type='alert'") or die("select query fail" . mysqli_error());
$count = 0;
while ($row = mysqli_fetch_assoc($qry)) {
    $count = $count + 1;
    $news_id = $row['news_id'];
    $news_title = $row['news_title']; 
    $remark = $row['remark']; 
    $created = $row['created'];
    ?>
                    <tr>
                        <td><?php echo $count;?></td>
                          <td><?php echo $news_title;?></td>
                            <td><?php echo $remark;?></td>
                              <td><?php echo $created;?></td> 
                              <td><a href="add_alert.php?delete=<?php echo $news_id;?>">Delete</a></td>
                    </tr>
<?php }?>
                </table>
                      </div>
            </div>
        </div></div>
<?php include './includes/Plugin.php'; ?>
        <?php include './includes/admin_footer.php'; ?>