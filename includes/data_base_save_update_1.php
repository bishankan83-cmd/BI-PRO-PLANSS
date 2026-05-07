
<?php

//include 'includes/checkauthenticator.php';
//include 'includes/db.php';

class databaseSave {
    function get_User_Details($user_ID) {
       // echo "<script>alert(".$user_ID.");</script>";
        $Name = '';
        $mobile = '';
        $User_type = '';
        $str='';
      // $this->$user_ID = $user_ID;
        $UID=$user_ID;
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $query = "SELECT * FROM `user_details` where User_ID='$UID'";
        $select_User_details = mysqli_query($connection, $query);
        while ($row = mysqli_fetch_assoc($select_User_details)) {
            $Name = $row['FullName'];
            $mobile = $row['mobile1'];
            $User_type = $row['User_type'];
           
        }
       $str=$Name."/".$mobile."/".$User_type;
        return $str;
    }
    function download($tableNM) {
        $this->table_name = $tableNM;
        $dname = $_POST['dname'];

        $download_file = $_FILES['download_file']['name'];
        $download_file_temp = $_FILES['download_file']['tmp_name'];
        move_uploaded_file($download_file_temp, "download/$download_file");
        $query = "INSERT INTO " . $this->table_name . "(`download_name`, `download_file`) VALUES ('$dname','$download_file')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_download_file = mysqli_query($connection, $query);
        if (!$insert_download_file) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function alert($tableNM) {
        $news = $_POST['news'];
        $Remark = $_POST['Remark'];
        $this->table_name = $tableNM;
        $query = "INSERT INTO `news_and_update`(`news_title`, `remark`,created,news_type) VALUES ('$news','$Remark',now(),'alert')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD alert' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function add_bank_details($tableNM) {
        $this->table_name = $tableNM;
        $bname = $_POST['bname'];
        $acno = $_POST['acno'];
        $ifsc = $_POST['ifsc'];
        $acno = $_POST['acno'];
        $acHN=$_POST['acHN'];
        $Query = "INSERT INTO " . $this->table_name . "(`bank_name`, `ifscf_code`, `acno`, `acHN`, `createOn`) VALUES ('$bname','$ifsc','$acno','$acHN',now())";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_bank_details = mysqli_query($connection, $Query);
        if (!$insert_bank_details) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function retailer_User_id_create($tableNM) {
        $empID = $_SESSION['user'];

        $this->table_name = $tableNM;
        $fullname = $_POST['fullname'];
        $fathername = $_POST['fathername'];
        $Mothername = $_POST['Mothername'];
        $Email_id = $_POST['Email_id'];
        $user_id = $_POST['user_id'];
        $password = $_POST['pswd'];
        // $user_pro = $_POST['user_pro'];
        
        
//        $user_pro = $_FILES['user_pro']['name'];
//        $user_pro_temp = $_FILES['user_pro']['tmp_name'];
//        move_uploaded_file($user_pro_temp, "UserProfile/$user_pro");
        
         $fecha         = date('Y/n/j h:i:s');
    $uploaddir   = 'UserProfile/';
    $uploadfile  = $uploaddir . basename($_FILES['user_pro']['name']);
    move_uploaded_file($_FILES['user_pro']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir . $Date2 . $Extension);
  $user_pro= $Date2 . $Extension;
        
        
        $panprice = $_POST['panprice'];
        $proMob = $_POST['proMob'];
        $sroMob = $_POST['sroMob'];
        $state = $_POST['state'];
        $City = $_POST['city'];
//        $pincode = $_POST['pincode'];
        $shopnm = $_POST['shopnm'];
        $address = $_POST['address'];
        $officeAdd = $_POST['officeAdd'];
        $bname = $_POST['bname'];
        $AcHnm = $_POST['AcHnm'];
        $ifsccode = $_POST['ifsccode'];
        $ACno = $_POST['ACno'];
        // $aadhare_img_front = $_POST['aadhare_img_front'];
        // $aadhare_img_back = $_POST['aadhare_img_back'];

          
             $fecha         = date('Y/n/j h:i:s');
    $uploaddir1   = 'aadhare_img/';
    $uploadfile  = $uploaddir1 . basename($_FILES['aadhare_img_front']['name']);
    move_uploaded_file($_FILES['aadhare_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir1 . $Date2 . $Extension);
  $aadhare_img_front= $Date2 . $Extension;  
  
  
//        $aadhare_img_back = $_FILES['aadhare_img_back']['name'];
//        $aadhare_img_back_temp = $_FILES['aadhare_img_back']['tmp_name'];
//        move_uploaded_file($aadhare_img_back_temp, "aadhare_img/$aadhare_img_back");

                    $fecha         = date('Y/n/j h:i:s');
    $uploaddir2   = 'aadhare_img/';
    $uploadfile  = $uploaddir2 . basename($_FILES['aadhare_img_back']['name']);
    move_uploaded_file($_FILES['aadhare_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir2 . $Date2 . $Extension);
  $aadhare_img_back= $Date2 . $Extension;  
  
        
        
     //   $panprice = $_POST['panprice'];
//        $Pancard_img_back = $_POST['Pancard_img_back'];

//        $Pancard_img_front = $_FILES['Pancard_img_front']['name'];
//        $Pancard_img_front_temp = $_FILES['Pancard_img_front']['tmp_name'];
//        move_uploaded_file($Pancard_img_front_temp, "pan_img/$Pancard_img_front");
                      $fecha         = date('Y/n/j h:i:s');
    $uploaddir3   = 'pan_img/';
    $uploadfile  = $uploaddir3 . basename($_FILES['Pancard_img_front']['name']);
    move_uploaded_file($_FILES['Pancard_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir3 . $Date2 . $Extension);
  $Pancard_img_front= $Date2 . $Extension;        
        

//        $Pancard_img_back = $_FILES['Pancard_img_back']['name'];
//        $Pancard_img_back_temp = $_FILES['Pancard_img_back']['tmp_name'];
//        move_uploaded_file($Pancard_img_back_temp, "pan_img/$Pancard_img_back");
                      $fecha         = date('Y/n/j h:i:s');
    $uploaddir4   = 'pan_img/';
    $uploadfile  = $uploaddir4 . basename($_FILES['Pancard_img_back']['name']);
    move_uploaded_file($_FILES['Pancard_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir4 . $Date2 . $Extension);
  $Pancard_img_back= $Date2 . $Extension;  
//   if(empty($aadhare_img_back))
        
        $sql_query = "INSERT INTO `user_details`(`FullName`, `motherNM`, `fatherNM`, `mobile1`, `mobile2`, `EmailID`, `User_Name`, `Pswd`, `Inactive`, `ActiveDate`, `User_type`, `distributor_id`, `bankName`, `ifsccode`, `acountHNM`, `accountno`, `shop_nm`, `user_pro`, `panPrice`, `pan_img1`, `pan_img2`, `adhare_img1`, `adhare_img2`, `address1`, `address2`, `state`, `country`, `city`, `created`, `Userprofile`)";
        $sql_query .= " VALUES ('$fullname', ";
        $sql_query .= "'$Mothername', ";
        $sql_query .= "'$fathername', ";
        $sql_query .= "'$proMob', ";
        $sql_query .= "'$sroMob', ";
        $sql_query .= "'$Email_id', ";
        $sql_query .= "'$user_id', ";
        $sql_query .= "'$password', ";
        $sql_query .= "'1', ";
        $sql_query .= "now(), ";
        $sql_query .= "'Retailer', ";
        $sql_query .= "'$empID', ";
        $sql_query .= "'$bname', ";
        $sql_query .= "'$ifsccode', ";
        $sql_query .= "'$AcHnm', ";
        $sql_query .= "'$ACno', ";
        $sql_query .= "'$shopnm', ";
        $sql_query .= "'$user_pro', ";
        $sql_query .= "'$panprice', ";
        $sql_query .= "'$Pancard_img_front', ";
        $sql_query .= "'$Pancard_img_back', ";
        $sql_query .= "'$aadhare_img_front', ";
        $sql_query .= "'$aadhare_img_back', ";
        $sql_query .= "'$address', ";
        $sql_query .= "'$officeAdd', ";
        $sql_query .= "'$state', ";
        $sql_query .= "'india', ";
        $sql_query .= "'$City', ";
        $sql_query .= "now(), ";     
        $sql_query .= "'1')";

//        print_r($sql_query);
//        die();
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_data_retailer = mysqli_query($connection, $sql_query);
        if (!$insert_data_retailer) {
            die('QUERY FAILD retaler Account Create' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function retailer_User_id_update($tableNM, $userid) {
        $empID = $_SESSION['user'];

        $this->table_name = $tableNM;
        $fullname = $_POST['fullname'];
        $fathername = $_POST['fathername'];
        $Mothername = $_POST['Mothername'];
        $Email_id = $_POST['Email_id'];
        $user_id = $_POST['user_id'];
        $password = $_POST['pswd'];
        // $user_pro = $_POST['user_pro'];
        $user_pro = $_FILES['user_pro']['name'];
        $user_pro_temp = $_FILES['user_pro']['tmp_name'];
        move_uploaded_file($user_pro_temp, "UserProfile/$user_pro");

//  $fecha         = date('Y/n/j h:i:s');
//    $uploaddir   = 'UserProfile/';
//    $uploadfile  = $uploaddir . basename($_FILES['user_pro']['name']);
//    move_uploaded_file($_FILES['user_pro']['tmp_name'], $uploadfile);

//    $Date2       = date('YmdHis');
//    $Parts        = explode('.', $uploadfile);
//    $Extension = '.' . $Parts[(count($Parts) - 1)];
//    rename($uploadfile, $uploaddir . $Date2 . $Extension);
//  $user_pro= $Date2 . $Extension;

        $proMob = $_POST['proMob'];
        $sroMob = $_POST['sroMob'];
        $state = $_POST['state'];
        $City = $_POST['city'];
     //   $pincode = $_POST['pincode'];
        $shopnm = $_POST['shopnm'];
        $address = $_POST['address'];
        $officeAdd = $_POST['officeAdd'];
        $bname = $_POST['bname'];
        $AcHnm = $_POST['AcHnm'];
        $ifsccode = $_POST['ifsccode'];
        $ACno = $_POST['ACno'];

   $ranNM2 = rand(10, 100);
        $ranNM3 = rand(10, 100);
        $ranNM4 = rand(10, 100);
        $ranNM5 = rand(10, 100);
          
             $fecha         = date('Y/n/j h:i:s');
    $uploaddir1   = 'aadhare_img/';
    $uploadfile  = $uploaddir1 . basename($_FILES['aadhare_img_front']['name']);
    move_uploaded_file($_FILES['aadhare_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir1 . $Date2.$ranNM2 . $Extension);
  $aadhare_img_front= $Date2.$ranNM2 . $Extension;  
  
  
//        $aadhare_img_back = $_FILES['aadhare_img_back']['name'];
//        $aadhare_img_back_temp = $_FILES['aadhare_img_back']['tmp_name'];
//        move_uploaded_file($aadhare_img_back_temp, "aadhare_img/$aadhare_img_back");

                    $fecha         = date('Y/n/j h:i:s');
    $uploaddir2   = 'aadhare_img/';
    $uploadfile  = $uploaddir2 . basename($_FILES['aadhare_img_back']['name']);
    move_uploaded_file($_FILES['aadhare_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir2 . $Date2.$ranNM3 . $Extension);
  $aadhare_img_back= $Date2.$ranNM3 . $Extension;  
  
        
        
        $panprice = $_POST['panprice'];
//        $Pancard_img_back = $_POST['Pancard_img_back'];

//        $Pancard_img_front = $_FILES['Pancard_img_front']['name'];
//        $Pancard_img_front_temp = $_FILES['Pancard_img_front']['tmp_name'];
//        move_uploaded_file($Pancard_img_front_temp, "pan_img/$Pancard_img_front");
                      $fecha         = date('Y/n/j h:i:s');
    $uploaddir3   = 'pan_img/';
    $uploadfile  = $uploaddir3 . basename($_FILES['Pancard_img_front']['name']);
    move_uploaded_file($_FILES['Pancard_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir3 . $Date2.$ranNM4 . $Extension);
  $Pancard_img_front= $Date2.$ranNM4 . $Extension;        
        

//        $Pancard_img_back = $_FILES['Pancard_img_back']['name'];
//        $Pancard_img_back_temp = $_FILES['Pancard_img_back']['tmp_name'];
//        move_uploaded_file($Pancard_img_back_temp, "pan_img/$Pancard_img_back");
                      $fecha         = date('Y/n/j h:i:s');
    $uploaddir4   = 'pan_img/';
    $uploadfile  = $uploaddir4 . basename($_FILES['Pancard_img_back']['name']);
    move_uploaded_file($_FILES['Pancard_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir4 . $Date2.$ranNM5 . $Extension);
  $Pancard_img_back= $Date2.$ranNM5 . $Extension;  
//   if(empty($aadhare_img_back))
//        {
//            $query1 ="select * from user_details where User_ID=".$user_id."";
//            $select_aadhare_image2=mysqli_query($connection,$query1);
//            while($row1 =mysqli_fetch_array($select_aadhare_image2))
//            {
//                $aadhare_img_back=$row1['adhare_img2'];
//            }
//        }
//        $Pancard_img_front = $_FILES['Pancard_img_front']['name'];
//        $Pancard_img_front_temp = $_FILES['Pancard_img_front']['tmp_name'];
//        move_uploaded_file($Pancard_img_front_temp, "pan_img/$Pancard_img_front");
//        if(empty($Pancard_img_front))
//        {
//            $query1 ="select * from user_details where User_ID=".$user_id."";
//            $select_pancard_image1=mysqli_query($connection,$query1);
//            while($row1 =mysqli_fetch_array($select_pancard_image1))
//            {
//                $Pancard_img_front=$row1['pan_img1'];
//            }
//        }
//        $Pancard_img_back = $_FILES['Pancard_img_back']['name'];
//        $Pancard_img_back_temp = $_FILES['Pancard_img_back']['tmp_name'];
//        move_uploaded_file($Pancard_img_back_temp, "pan_img/$Pancard_img_back");
//         if(empty($Pancard_img_back))
//        {
//            $query1 ="select * from user_details where User_ID=".$user_id."";
//            $select_pancard_image2=mysqli_query($connection,$query1);
//            while($row1 =mysqli_fetch_array($select_pancard_image2))
//            {
//                $Pancard_img_back=$row1['pan_img2'];
//            }
//        }

        $panprice=$_POST['panprice'];
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $query1 = "select * from user_details where User_ID=" . $user_id . "";
        $select_userprofile_image1 = mysqli_query($connection, $query1);
        while ($row1 = mysqli_fetch_array($select_userprofile_image1)) {
            if (empty($user_pro)) {
                $user_pro = $row1['user_pro'];
            }
            if (empty($aadhare_img_front)) {
                $aadhare_img_front = $row1['adhare_img1'];
            }
            if (empty($aadhare_img_back)) {
                $aadhare_img_back = $row1['adhare_img2'];
            }
            if (empty($Pancard_img_front)) {
                $Pancard_img_front = $row1['pan_img1'];
            }
            if (empty($Pancard_img_back)) {
                $Pancard_img_back = $row1['pan_img2'];
            } 
            if (empty($panprice)) {
                $panprice = $row1['panPrice'];
            }
        }
        $update_query = "UPDATE `user_details` SET ";
        $update_query .="`FullName`='$fullname',";
        $update_query .= "`motherNM`='$Mothername',";
        $update_query .= "`fatherNM`='$fathername',";
        $update_query .= "`mobile1`='$proMob',";
        $update_query .= "`mobile2`='$sroMob',";
        $update_query .= "`EmailID`='$empID',";
        $update_query .="`User_Name`='$user_id',";
        $update_query .= "`Pswd`='$password',";
        // $update_query .= "`Inactive`=[value-10],";
        //$update_query .= "`ActiveDate`=[value-11],";
        //$update_query .= "`User_type`=[value-12],";
        //$update_query .= "`distributor_id`=[value-13],";
        $update_query .= "`bankName`='$bname',";
        $update_query .= "`ifsccode`='$ifsccode',";
        $update_query .= "`acountHNM`='$AcHnm',";
        $update_query .= "`accountno`='$ACno',";
        // $update_query .= "`aadhareno`='',";
        $update_query .= "`shop_nm`='$shopnm',";
        $update_query .= "`user_pro`='$user_pro',";
        $update_query .= "`panPrice`='$panprice',";
        $update_query .= "`pan_img1`='$Pancard_img_front',";
        $update_query .= "`pan_img2`='$Pancard_img_back',";
        $update_query .= "`adhare_img1`='$aadhare_img_front',";
        $update_query .= "`adhare_img2`='$aadhare_img_back',";
        //   $update_query .= "`User_code`=[value-26],";
        $update_query .= "`address1`='$address',";
        $update_query .= "`address2`='$officeAdd',";
        $update_query .= "`state`='$state',";
        $update_query .= "`country`='india',";
         $update_query .= "`country`='india',";
        $update_query .= "`city`='$City'";
        $update_query .= " WHERE User_ID=" . $userid . "";
        // $sql_query.= "'$pincode',";

        $insert_data_retailer = mysqli_query($connection, $update_query);
        if (!$insert_data_retailer) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function service_create($tableNM) {
        $serviceNM = $_POST['servicenm'];
        $remark = $_POST['Remark'];
        $this->table_name = $tableNM;
        $query = "INSERT INTO `service_mst`(`service_nm`, `remark`, `created`) VALUES ('$serviceNM','$remark',now())";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }
  function advertiesment_create($tableNM) {
     $title = $_POST['title'];
     $desc = $_POST['desc'];

     $adsIMG = $_FILES['adsIMG']['name'];
        $adsIMG_temp = $_FILES['adsIMG']['tmp_name'];
        move_uploaded_file($adsIMG_temp, "adsimg/$adsIMG");
        $this->table_name = $tableNM;
        $query = "INSERT INTO `advertiesment`(`title`, `descr`, `image`, `created`) VALUES ('$title','$desc','$adsIMG',now())";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD' . mysqli_error($connection));
        }
          else
            {
                echo "<script>alert('record save successfully');</script>";
            }
}
    
    function news_and_update($tableNM) {
        $news = $_POST['news'];
        $Remark = $_POST['Remark'];
        $this->table_name = $tableNM;
        $query = "INSERT INTO `news_and_update`(`news_title`, `remark`,created,news_type) VALUES ('$news','$Remark',now(),'news_update')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function distributor_User_id_create($tableNM) {
        $empID = $_SESSION['user'];
        $this->table_name = $tableNM;
        $fullname = $_POST['fullname'];
        $fathername = $_POST['fathername'];
        $Mothername = $_POST['Mothername'];
        $Email_id = $_POST['Email_id'];
        $user_id = $_POST['user_id'];
        $password = $_POST['pswd'];
          $ACno = $_POST['ACno'];
        // $user_pro = $_POST['user_pro'];
        $ranNM= rand(10,100);
        $ranNM2= rand(10,100);
          $ranNM3= rand(10,100);
          $ranNM4= rand(10,100);
          $ranNM5= rand(10,100);
//        $user_pro = $_FILES['user_pro']['name'];
//        $user_pro_temp = $_FILES['user_pro']['tmp_name'];
//        move_uploaded_file($user_pro_temp, "UserProfile/$user_pro");
               $fecha         = date('Y/n/j h:i:s');
    $uploaddir   = 'UserProfile/';
    $uploadfile  = $uploaddir . basename($_FILES['user_pro']['name']);
    move_uploaded_file($_FILES['user_pro']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir.$Date2.$ranNM.$Extension);
  $user_pro= $Date2.$ranNM.$Extension;  


  //User Profile
    $user_pro = $_FILES['user_pro']['tmp_name']; 
    if(!empty(user_pro)) {


        $file = $_FILES['user_pro']['tmp_name']; 
        $sourceProperties = getimagesize($file);
        $fileNewName = time();
        $folderPath = "UserProfile/";
        $ext = pathinfo($_FILES['user_pro']['name'], PATHINFO_EXTENSION);
        $imageType = $sourceProperties[2];
        $user_pro='';

        switch ($imageType) {


            case IMAGETYPE_PNG:
                $imageResourceId = imagecreatefrompng($file); 
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
            //    imagepng($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                 imagepng($targetLayer,$folderPath. $fileNewName.".".$ext);
                 $user_pro=$fileNewName.".".$ext;
                break;


            case IMAGETYPE_GIF:
                $imageResourceId = imagecreatefromgif($file); 
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                imagegif($targetLayer,$folderPath. $fileNewName. ".". $ext);
                 $user_pro=$fileNewName.".".$ext;
                break;


            case IMAGETYPE_JPEG:
                $imageResourceId = imagecreatefromjpeg($file); 
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                imagejpeg($targetLayer,$folderPath. $fileNewName. ".". $ext);
               $user_pro=$fileNewName.".".$ext;
                break;


            default:
                echo "Invalid Image type.";
                exit;
                break;
        }


       // move_uploaded_file($file, $folderPath. $fileNewName. ".". $ext);
     //   echo "Image Resize Successfully.".   $post_image;
    }
        //End User Profile
        
$panprice=$_POST['panprice'];
        $proMob = $_POST['proMob'];
        $sroMob = $_POST['sroMob'];
        $state = $_POST['state'];
        $City = $_POST['city'];
        $pincode = $_POST['pincode'];
        $shopnm = $_POST['shopnm'];
        $address = $_POST['address'];
        $officeAdd = $_POST['officeAdd'];
        $bname = $_POST['bname'];
        $AcHnm = $_POST['AcHnm'];
        $ifsccode = $_POST['ifsccode'];
        // $aadhare_img_front = $_POST['aadhare_img_front'];
        // $aadhare_img_back = $_POST['aadhare_img_back'];

//        $aadhare_img_front = $_FILES['aadhare_img_front']['name'];
//        $aadhare_img_front_temp = $_FILES['aadhare_img_front']['tmp_name'];
//        move_uploaded_file($aadhare_img_front_temp, "aadhare_img/$aadhare_img_front");
        
             $fecha         = date('Y/n/j h:i:s');
    $uploaddir1   = 'aadhare_img/';
    $uploadfile  = $uploaddir1 . basename($_FILES['aadhare_img_front']['name']);
    move_uploaded_file($_FILES['aadhare_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir1 . $Date2.$ranNM2 . $Extension);
  $aadhare_img_front= $Date2.$ranNM2 . $Extension;  
  
  
//        $aadhare_img_back = $_FILES['aadhare_img_back']['name'];
//        $aadhare_img_back_temp = $_FILES['aadhare_img_back']['tmp_name'];
//        move_uploaded_file($aadhare_img_back_temp, "aadhare_img/$aadhare_img_back");

                    $fecha         = date('Y/n/j h:i:s');
    $uploaddir2   = 'aadhare_img/';
    $uploadfile  = $uploaddir2 . basename($_FILES['aadhare_img_back']['name']);
    move_uploaded_file($_FILES['aadhare_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir2 . $Date2.$ranNM3 . $Extension);
  $aadhare_img_back= $Date2.$ranNM3 . $Extension;  
  
        
        
        $panprice = $_POST['panprice'];
//        $Pancard_img_back = $_POST['Pancard_img_back'];

//        $Pancard_img_front = $_FILES['Pancard_img_front']['name'];
//        $Pancard_img_front_temp = $_FILES['Pancard_img_front']['tmp_name'];
//        move_uploaded_file($Pancard_img_front_temp, "pan_img/$Pancard_img_front");
                      $fecha         = date('Y/n/j h:i:s');
    $uploaddir3   = 'pan_img/';
    $uploadfile  = $uploaddir3 . basename($_FILES['Pancard_img_front']['name']);
    move_uploaded_file($_FILES['Pancard_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir3 . $Date2.$ranNM4 . $Extension);
  $Pancard_img_front= $Date2.$ranNM4 . $Extension;        
        

//        $Pancard_img_back = $_FILES['Pancard_img_back']['name'];
//        $Pancard_img_back_temp = $_FILES['Pancard_img_back']['tmp_name'];
//        move_uploaded_file($Pancard_img_back_temp, "pan_img/$Pancard_img_back");
                      $fecha         = date('Y/n/j h:i:s');
    $uploaddir4   = 'pan_img/';
    $uploadfile  = $uploaddir4 . basename($_FILES['Pancard_img_back']['name']);
    move_uploaded_file($_FILES['Pancard_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir4 . $Date2.$ranNM5 . $Extension);
  $Pancard_img_back= $Date2.$ranNM5. $Extension;    
        
        
        $sql_query = "INSERT INTO " . $this->table_name . "(`FullName`, `motherNM`, `fatherNM`, `mobile1`, `mobile2`, `EmailID`, `User_Name`, `Pswd`, `Inactive`, `ActiveDate`, `User_type`, `distributor_id`, `bankName`, `ifsccode`, `acountHNM`, `accountno`, `aadhareno`, `shop_nm`, `user_pro`, `pan_img1`, `pan_img2`, `adhare_img1`, `adhare_img2`, `User_code`, `address1`, `address2`, `state`, `country`, `city`, `remark`, `created`, `Userprofile`, panPrice)";
        $sql_query.="VALUES ('$fullname',";
        $sql_query.= "'$Mothername',";
        $sql_query.= "'$fathername',";
        $sql_query.= "'$proMob',";
        $sql_query.= "'$sroMob',";
        $sql_query.= "'$Email_id',";
        $sql_query.= "'$user_id',";
        $sql_query.= "'$password',";
        $sql_query.= "'1',";
        $sql_query.= "now(),";
        $sql_query.= "'Distributor',";
        $sql_query.= "'$empID',"; //Distributore ID 
        $sql_query.= "'$bname',";
        $sql_query.= "'$ifsccode ',";
        $sql_query.= "'$AcHnm',";
        $sql_query.= "'$ACno',"; //account no.
        $sql_query.= "'',"; //adadhre no.
        $sql_query.= "'$shopnm',";
        $sql_query.= "'$user_pro',";
        $sql_query.= "'$Pancard_img_front',";
        $sql_query.= "'$Pancard_img_back',";
        $sql_query.= "'$aadhare_img_front',";
        $sql_query.= "'$aadhare_img_back',";
        $sql_query.= "'',"; //User Code Genrate
        $sql_query.= "'$address',";
        $sql_query.= "'$officeAdd',";
        $sql_query.= "'$state',";
        $sql_query.= "'india',";
        $sql_query.= "'$City',";
        $sql_query.= "'',";
        $sql_query.= "now(),";
        $sql_query.= "'1', "; 
        $sql_query.= "'$panprice')";
        // $sql_query.= "'$pincode',";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_data_retailer = mysqli_query($connection, $sql_query);
        if (!$insert_data_retailer) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }
        function retailer_pan_card_price($tableNM) {
        $empID = $_SESSION['user'];
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $this->table_name = $tableNM;
        $user_id = $_POST['user_id'];
        $pan_price = $_POST['pan_price'];
        $pan_price_save = "UPDATE `user_details` SET `panPrice`='$pan_price' WHERE `User_ID`='$user_id'";
        $save_pan_price = mysqli_query($connection, $pan_price_save);
        if (!$save_pan_price) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }
function imageResize($imageResourceId,$width,$height) {


    $targetWidth =900;
    $targetHeight =548;


    $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
    imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);


    return $targetLayer;
}
    function distributor_User_id_update($tableNM, $userid) {
        $empID = $_SESSION['user'];

        $this->table_name = $tableNM;
        $fullname = $_POST['fullname'];
        $fathername = $_POST['fathername'];
        $Mothername = $_POST['Mothername'];
        $Email_id = $_POST['Email_id'];
        $user_id = $_POST['user_id'];
        $password = $_POST['pswd'];
        // $user_pro = $_POST['user_pro'];
        $user_pro = $_FILES['user_pro']['name'];
        $user_pro_temp = $_FILES['user_pro']['tmp_name'];
        move_uploaded_file($user_pro_temp, "UserProfile/$user_pro");

        $panprice = $_POST['panprice'];

        $proMob = $_POST['proMob'];
        $sroMob = $_POST['sroMob'];
        $state = $_POST['state'];
        $City = $_POST['city'];
        $pincode = $_POST['pincode'];
        $shopnm = $_POST['shopnm'];
        $address = $_POST['address'];
        $officeAdd = $_POST['officeAdd'];
        $bname = $_POST['bname'];
        $AcHnm = $_POST['AcHnm'];
        $ifsccode = $_POST['ifsccode'];
        $ACno = $_POST['ACno'];

   $ranNM2 = rand(10, 100);
        $ranNM3 = rand(10, 100);
        $ranNM4 = rand(10, 100);
        $ranNM5 = rand(10, 100);
   
             $fecha         = date('Y/n/j h:i:s');
    $uploaddir1   = 'aadhare_img/';
    $uploadfile  = $uploaddir1 . basename($_FILES['aadhare_img_front']['name']);
    move_uploaded_file($_FILES['aadhare_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir1 . $Date2.$ranNM2 . $Extension);
  $aadhare_img_front= $Date2.$ranNM2 . $Extension;  
  
  
//        $aadhare_img_back = $_FILES['aadhare_img_back']['name'];
//        $aadhare_img_back_temp = $_FILES['aadhare_img_back']['tmp_name'];
//        move_uploaded_file($aadhare_img_back_temp, "aadhare_img/$aadhare_img_back");

                    $fecha         = date('Y/n/j h:i:s');
    $uploaddir2   = 'aadhare_img/';
    $uploadfile  = $uploaddir2 . basename($_FILES['aadhare_img_back']['name']);
    move_uploaded_file($_FILES['aadhare_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir2 . $Date2.$ranNM3 . $Extension);
  $aadhare_img_back= $Date2.$ranNM3 . $Extension;  

//   if(empty($aadhare_img_back))
//        {
//            $query1 ="select * from user_details where User_ID=".$user_id."";
//            $select_aadhare_image2=mysqli_query($connection,$query1);
//            while($row1 =mysqli_fetch_array($select_aadhare_image2))
//            {
//                $aadhare_img_back=$row1['adhare_img2'];
//            }
//        }
//        $Pancard_img_front = $_FILES['Pancard_img_front']['name'];
//        $Pancard_img_front_temp = $_FILES['Pancard_img_front']['tmp_name'];
//        move_uploaded_file($Pancard_img_front_temp, "pan_img/$Pancard_img_front");
//        if(empty($Pancard_img_front))
//        {
//            $query1 ="select * from user_details where User_ID=".$user_id."";
//            $select_pancard_image1=mysqli_query($connection,$query1);
//            while($row1 =mysqli_fetch_array($select_pancard_image1))
//            {
//                $Pancard_img_front=$row1['pan_img1'];
//            }
//        }
//        $Pancard_img_back = $_FILES['Pancard_img_back']['name'];
//        $Pancard_img_back_temp = $_FILES['Pancard_img_back']['tmp_name'];
//        move_uploaded_file($Pancard_img_back_temp, "pan_img/$Pancard_img_back");
//         if(empty($Pancard_img_back))
//        {
//            $query1 ="select * from user_details where User_ID=".$user_id."";
//            $select_pancard_image2=mysqli_query($connection,$query1);
//            while($row1 =mysqli_fetch_array($select_pancard_image2))
//            {
//                $Pancard_img_back=$row1['pan_img2'];
//            }
//        }
                              $fecha         = date('Y/n/j h:i:s');
    $uploaddir3   = 'pan_img/';
    $uploadfile  = $uploaddir3 . basename($_FILES['Pancard_img_front']['name']);
    move_uploaded_file($_FILES['Pancard_img_front']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir3 . $Date2.$ranNM4 . $Extension);
  $Pancard_img_front= $Date2.$ranNM4 . $Extension;        
        
        
        
        
                           $fecha         = date('Y/n/j h:i:s');
    $uploaddir4   = 'pan_img/';
    $uploadfile  = $uploaddir4 . basename($_FILES['Pancard_img_back']['name']);
    move_uploaded_file($_FILES['Pancard_img_back']['tmp_name'], $uploadfile);
# Our date...
    $Date2       = date('YmdHis');
    $Parts        = explode('.', $uploadfile);
    $Extension = '.' . $Parts[(count($Parts) - 1)];
    rename($uploadfile, $uploaddir4 . $Date2.$ranNM5 . $Extension);
  $Pancard_img_back= $Date2.$ranNM5 . $Extension;    
        
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $query1 = "select * from user_details where User_ID=" . $user_id . "";
        $select_userprofile_image1 = mysqli_query($connection, $query1);
        while ($row1 = mysqli_fetch_array($select_userprofile_image1)) {
            if (empty($user_pro)) {
                $user_pro = $row1['user_pro'];
            }
            if (empty($aadhare_img_front)) {
                $aadhare_img_front = $row1['adhare_img1'];
            }
            if (empty($aadhare_img_back)) {
                $aadhare_img_back = $row1['adhare_img2'];
            }
            if (empty($Pancard_img_front)) {
                $Pancard_img_front = $row1['pan_img1'];
            }
            if (empty($Pancard_img_back)) {
                $Pancard_img_back = $row1['pan_img2'];
            }
        }
        $update_query = "UPDATE `user_details` SET ";
        $update_query .="`FullName`='$fullname',";
        $update_query .= "`motherNM`='$Mothername',";
        $update_query .= "`fatherNM`='$fathername',";
        $update_query .= "`mobile1`='$proMob',";
        $update_query .= "`mobile2`='$sroMob',";
        $update_query .= "`EmailID`='$empID',";
        $update_query .="`User_Name`='$user_id',";
        $update_query .= "`Pswd`='$password',";
        // $update_query .= "`Inactive`=[value-10],";
        //$update_query .= "`ActiveDate`=[value-11],";
        //$update_query .= "`User_type`=[value-12],";
        //$update_query .= "`distributor_id`=[value-13],";
        $update_query .= "`bankName`='$bname',";
        $update_query .= "`ifsccode`='$ifsccode',";
        $update_query .= "`acountHNM`='$AcHnm',";
        $update_query .= "`accountno`='$ACno',";
        // $update_query .= "`aadhareno`='',";
        $update_query .= "`shop_nm`='$shopnm',";
        $update_query .= "`user_pro`='$user_pro',";
        //  $update_query .= "`panPrice`=[value-21],";
        $update_query .= "`pan_img1`='$Pancard_img_front',";
        $update_query .= "`pan_img2`='$Pancard_img_back',";
        $update_query .= "`adhare_img1`='$aadhare_img_front',";
        $update_query .= "`adhare_img2`='$aadhare_img_back',";
        //   $update_query .= "`User_code`=[value-26],";
        $update_query .= "`address1`='$address',";
        $update_query .= "`address2`='$officeAdd',";
        $update_query .= "`state`='$state',";
        $update_query .= "`country`='india',";
        $update_query .= "`city`='$City', ";
        $update_query .= "`panPrice`='$panprice'";
        $update_query .= " WHERE User_ID=" . $userid . "";
        // $sql_query.= "'$pincode',";

        $insert_data_retailer = mysqli_query($connection, $update_query);
        if (!$insert_data_retailer) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

}

?>