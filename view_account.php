<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
$msg = '';
$AppCodeObj = new databaseSave();
if (isset($_POST['submit'])) {
     //$user_id = $_GET['edit'];
   // $msg = $AppCodeObj->retailer_User_id_update("user_details",$user_id);
}
?>
<!--------------------
START - Breadcrumbs
-------------------->

<ul class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Home</a></li>
    <li class="breadcrumb-item"><span>Update Retailer Account</span></li>
</ul>
<!--------------------
END - Breadcrumbs
-------------------->
<div class="content-panel-toggler"><i class="os-icon os-icon-grid-squares-22"></i><span>Sidebar</span></div>
<div class="content-i">
    <div class="content-box">
        <div class="element-wrapper">
            <div class="element-box">
                <form action="#" method="post" enctype="multipart/form-data">

                    <?php
                    if ($_GET['uid']) {
                        $user_id = $_GET['uid'];
                        $select_details = mysqli_query($connection, "SELECT * FROM user_details where User_ID=" . $user_id . "") or die("select query fail" . mysqli_error());

                        while ($row = mysqli_fetch_assoc($select_details)) {
                            $FullName = $row['FullName'];
                            $motherNM = $row['motherNM'];
                            $fatherNM = $row['fatherNM'];
                            $mobile1 = $row['mobile1'];
                            $mobile2 = $row['mobile2'];
                            $EmailID = $row['EmailID'];
                            $User_Name = $row['User_Name'];
                            $Pswd = $row['Pswd'];
                            $bankName = $row['bankName'];
                            $ifsccode = $row['ifsccode'];
                            $acountHNM = $row['acountHNM'];
                            $accountno = $row['accountno'];
                            $aadhareno = $row['aadhareno'];
                            $shop_nm = $row['shop_nm'];
                            $user_pro = $row['user_pro'];
                            //$row['panPrice']
                            $pan_img1 = $row['pan_img1'];
                            $pan_img2 = $row['pan_img2'];
                            $adhare_img1 = $row['adhare_img1'];
                            $adhare_img2 = $row['adhare_img2'];
                            //$row['User_code']
                            $address1 = $row['address1'];
                            $address2 = $row['address2'];
                            $state = $row['state'];
                            $city = $row['city'];
                            $panPrice = $row['panPrice'];
                            ?>
                          
                    <div class="row">
                        <div class="col-md-12">
                            <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Basic Details</h5>                                   
                        </div>  
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Full Name</label>
                                 <input class="form-control" readonly name="fullname" value="<?php echo $FullName;?>" placeholder="Full Name" type="text">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Father Name</label>
                                 <input class="form-control" readonly name="fathername" value="<?php echo $fatherNM;?>" placeholder="Father Name" type="text">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Mother Name</label>
                                 <input class="form-control" readonly name="Mothername" value="<?php echo $motherNM;?>" placeholder="Mother Name" type="text">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Email ID</label>
                                 <input class="form-control" readonly name="Email_id" value="<?php echo $EmailID;?>" placeholder="Email ID" type="email">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group"><label for="">User ID</label>
                                 <input class="form-control" readonly name="user_id" value="<?php echo $User_Name;?>" placeholder="User ID" type="text">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Password</label>
                                 <input class="form-control" readonly name="pswd" value="<?php echo $Pswd;?>" id="password" placeholder="Password" type="text">
                            </div>


                        </div> 
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">User Profile</label>
                                <a href="javascript:;" onclick="myFunction('../UserProfile/','<?php echo $user_pro;?>');" >    <img src="../UserProfile/<?php echo $user_pro;?>" style="width: 100px;"></a>

                            </div>
                        </div>

                        <div class="col-md-12">
                            <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Contact Details</h5>                                   
                        </div>  
                        <div class="col-sm-3">
                            <div class="form-group"><label for=""> Primary Mobile No.</label>
                                 <input class="form-control" readonly name="proMob" value="<?php echo $mobile1;?>" placeholder=" Primary Mobile No." type="text"></div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for=""> Secondary Mobile No.</label>
                                 <input class="form-control" readonly name="sroMob" value="<?php echo $mobile2;?>" placeholder="Secondary Mobile No." type="text"></div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">State</label>
                                <input class="form-control" readonly name="sroMob" value="<?php echo $state;?>">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">City</label>

                                 <input class="form-control" readonly name="city" value="<?php echo $city;?>" placeholder="City" type="text">
                            </div>
                        </div>
<!--                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Pin Code</label>
                                 <input class="form-control" readonly name="pincode" value="<?php echo $FullName;?>" placeholder="Pin Code" type="text">
                            </div>
                        </div>-->
   <div class="col-sm-3">
                                    <div class="form-group"><label for="">Pan Price</label>
                                         <input class="form-control" readonly name="panprice" value="<?php echo $panPrice;?>" placeholder="Pan Price" type="number">
                                    </div>
                                </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Shop Name</label>
                                 <input class="form-control" readonly name="shopnm" value="<?php echo $shop_nm;?>" placeholder="Shop Name" type="text">
                            </div>
                        </div>  

                        <div class="col-sm-12">
                            <div class="form-group"><label for=""> Address</label>
                                <textarea readonly class="form-control" name="address" placeholder="Address"><?php echo $address1;?></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group"><label for=""> Office Address</label>
                                <textarea readonly class="form-control" name="officeAdd" placeholder="office Address"><?php echo $address2;?></textarea>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Bank Details</h5>

                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for=""> Bank Name</label>
                                 <input class="form-control" readonly name="bname" value="<?php echo $bankName;?>" placeholder="Bank Name" type="text">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Account Holder Name</label>
                                 <input class="form-control" readonly value="<?php echo $acountHNM;?>" name="AcHnm" placeholder="Account Holder Name" type="text">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group"><label for="">Account No.</label>
                                 <input class="form-control" readonly value="<?php echo $accountno;?>" name="ACno"  placeholder="Account No." type="number">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group"><label for="">IFSC Code</label>
                                 <input class="form-control" readonly value="<?php echo $ifsccode;?>" name="ifsccode" placeholder="IFSC Code" type="text">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <h5 style="color: blue;border-bottom: 1px solid blue;padding: 10px;">Attachment</h5>

                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="">Aadhar Card Front Side</label>
                                <a href="javascript:;" onclick="myFunction('../aadhare_img/','<?php echo $adhare_img1;?>');" > <img src="../aadhare_img/<?php echo $adhare_img1;?>" style="width: 100px"></a>
<!--                       data-toggle="modal" data-target="#myModal"           <input class="form-control" readonly  name="aadhare_img_front" type="file">-->
                            </div>
                        </div>
<script>
function myFunction(img_pat,img_view) {
   // alert(img_view);
    //var imageShow="../aadhare_img/" + img_view +"";
    //alert(imageShow);
 //   document.getElementById("img_view").src = "'../aadhare_img/" + img_view +"'";
 //document.getElementById("img_view").src="../aadhare_img/" + img_view +"";
 var image = document.getElementById("img_view");
image.src = img_pat+img_view +"";
openModal();
}
</script>
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <!--<h4 class="modal-title">Modal Header</h4>-->
      </div>
      <div class="modal-body">
          <img id="img_view" style="width:100%;height: auto;">
        <!--<p>Some text in the modal.</p>-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="">Aadhar Card Front Side</label>
                                <a href="javascript:;" onclick="myFunction('../aadhare_img/','<?php echo $adhare_img2;?>');" >    <img src="../aadhare_img/<?php echo $adhare_img2;?>" style="width: 100px"></a>
<!--                                 <input class="form-control" readonly  name="aadhare_img_back"  type="file">-->
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="">Pan Card Front Side</label>
                                <a href="javascript:;" onclick="myFunction('../pan_img/','<?php echo $pan_img1;?>');" >   <img src="../pan_img/<?php echo $pan_img1;?>" style="width: 100px"></a>
<!--                                 <input class="form-control" readonly name="Pancard_img_front" type="file">-->
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="">Pan Card Back Side</label>
                          
                                <a href="javascript:;" onclick="myFunction('../pan_img/','<?php echo $pan_img2;?>');" >  <img src="../pan_img/<?php echo $pan_img2;?>" style="width: 100px"></a>
                           
      
<!--                                 <input class="form-control" readonly name="Pancard_img_back" type="file">-->
                            </div>
                        </div>

<!--                        <div class="form-buttons-w text-right">-->
<!--                            <input class="btn btn-primary" type="submit" value="Submit Now" name="submit">-->
<!--                        </div>-->
                    </div>
                      <?php    }
                    }
                    ?>
                </form>
            </div>
        </div>
<?php include './includes/Plugin.php'; ?>
        <?php include './includes/admin_footer.php'; ?>
        <script>
        function openModal(){

    $('#myModal').modal();
}      
        </script>