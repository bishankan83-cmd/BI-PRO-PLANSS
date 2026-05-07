<form action="#" method="post" enctype="multipart/form-data">
<?php 
  $id=$_GET['edit'];
                 $qry = mysqli_query($connection, "SELECT * FROM advertiesment where id='$id'") or die("select query fail" . mysqli_error());
                                         $count=0;
                                            while ($row = mysqli_fetch_assoc($qry)) {
                                                $count=$count+1;
                                                $title=$row['title'];
                                                        $descr=$row['descr'];
                                                        $image=$row['image'];  
                                                        $id=$row['id'];
?>
                    
                            <div class="row">
                                 <div class="col-md-12">
                                    <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Advetiesment    <a href="create_advetiesment.php" class="btn btn-sm btn-primary">add new</a></h5>                                   
                                </div>  
                                <div class="col-sm-6">
                                    <div class="form-group"><label for="">Title</label>
                                        <input class="form-control" name="title" value="<?php echo $title;?>" placeholder="Title" type="text">
                                    </div>
                                </div>
                                    <div class="col-sm-6">
                                    <div class="form-group"><label for="">Description</label>
                                        <input class="form-control" name="desc" value="<?php echo $descr;?>" placeholder="Description" type="text">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group"><label for="">Image <span style="color:red;font-size: 12px">350*250</span></label>
                                        <img src="../adsimg/<?php echo $image;?>" height="80px" width="80px">
                                        <input name="adsIMG" type="file">
                                    </div>
                                </div>
                     
                           
                            <div class="form-buttons-w text-right">
                                <input class="btn btn-primary" type="submit" value="Submit Now" name="update_ads">
                            </div>
                        </div>
    <?php 
                                            }?>
                </form>