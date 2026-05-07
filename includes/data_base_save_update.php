
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
         $emp_id=  $_SESSION['user'];
        $this->table_name = $tableNM;
        $dname = $_POST['dname'];

        $download_file = $_FILES['download_file']['name'];
        $download_file_temp = $_FILES['download_file']['tmp_name'];
        move_uploaded_file($download_file_temp, "download/$download_file");
        $query = "INSERT INTO " . $this->table_name . "(`download_name`, `download_file`,empID) VALUES ('$dname','$download_file','$emp_id')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_download_file = mysqli_query($connection, $query);
        if (!$insert_download_file) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function addcom($tableNM) {
        $date = $_POST['date'];
        $Ctype = $_POST['Ctype'];
        $plan = $_POST['plan'];
        $this->table_name = $tableNM;
        $query = "INSERT INTO compound_planning(date, Ctype, plan) VALUES ('$date','$Ctype','$plan')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD alert' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function addwork($tableNM) {
      
        $date = $_POST['datetime'];
        $take=$_POST['take_datetime'];
        $erp = $_POST['erp'];
   
        $this->table_name = $tableNM;
        $query = "INSERT INTO work_order(datetime,take_datetime,erp) VALUES ('$date','$take','$erp')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('please enter correct no');
        } else {
            return 'pass';
        }
    }
    function addw($tableNM) {
        $db = mysqli_connect('localhost','planatir_task_managemen','Bishan@1919','planatir_task_managemen');
        $query="SELECT * FROM worder";
        $row = mysqli_query($db,$query);
    }
    function addstock($tableNM) {
        
        $icode = $_POST['icode'];
        $t_size = $_POST['t_size'];
        $brand = $_POST['brand'];
        $col = $_POST['col'];
        $fit = $_POST['fit'];
        $rim = $_POST['rim'];

        $cstock = $_POST['cstock'];

        $this->table_name = $tableNM;
        $query = "INSERT INTO stock(icode,t_size,brand,col,fit,rim,cons,fweight,ptv,cbm,kgs,cstock)
         VALUES ('$icode','$t_size','$brand','$col','$fit','$rim','$cons','$fweight','$ptv','$cbm','$kgs','$cstock')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD alert' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function addorder($tableNM) {
       
        $Ctype = $_POST['Ctype'];
        $corder = $_POST['corder'];
        $this->table_name = $tableNM;
        $query = "INSERT INTO torder(Ctype, corder) VALUES ('$Ctype','$corder')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD alert' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }
   
    function add_bom($tableNM) {
       
        $pid = $_POST['pid'];
        $Description = $_POST['Description'];
        $Tsize = $_POST['Tsize'];
        $brand = $_POST['brand'];
        $type = $_POST['type'];
        $colour = $_POST['colour'];
        $rwidth = $_POST['rwidth'];
        $comweight = $_POST['comweight'];
        $tweight = $_POST['tweight'];
        $withsteel = $_POST['withsteel'];
        $bead = $_POST['bead'];
        $finishweight = $_POST['finishweight'];
        $sbtype = $_POST['sbtype'];
        $sbweight = $_POST['sbweight'];
        $beadtype = $_POST['beadtype'];
        $Nbead = $_POST['Nbead'];
        $ptype = $_POST['ptype'];
        $pweight = $_POST['pweight'];
        $btype = $_POST['btype'];
        $bweight = $_POST['bweight'];
        $bontype = $_POST['bontype'];
        $bonweight = $_POST['bonweight'];
        $ctype = $_POST['ctype'];
        $cweight = $_POST['cweight'];
        $threat = $_POST['threat'];
        $thweight = $_POST['thweight'];

       $this->table_name = $tableNM;
     
        $query = "INSERT INTO bom(pid, Description,Tsize,brand,type,colour,rwidth,comweight,tweight,withsteel,bead,finishweight,sbtype,sbweight,beadtype,Nbead,ptype,pweight,btype,bweight,bontype,bonweight,ctype,cweight,threat,thweight) VALUES ('$pid','$Description','$Tsize', '$brand','$type','$colour', '$rwidth', '$comweight', '$tweight','$withsteel','$bead','$finishweight','$sbtype','$sbweight', '$beadtype', '$Nbead', '$ptype','$pweight','$btype','$bweight','$bontype','$bonweight','$ctype','$cweight','$threat','$thweight')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_service = mysqli_query($connection, $query);
        if (!$insert_service) {
            die('QUERY FAILD alert' . mysqli_error($connection));
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
         $emp_id=  $_SESSION['user'];
        $this->table_name = $tableNM;
        $bname = $_POST['bname'];
        $acno = $_POST['acno'];
        $ifsc = $_POST['ifsc'];
        $acno = $_POST['acno'];
        $acHN=$_POST['acHN'];
        $Query = "INSERT INTO " . $this->table_name . "(`bank_name`, `ifscf_code`, `acno`, `acHN`, `createOn`, empID) VALUES ('$bname','$ifsc','$acno','$acHN',now(),'$emp_id')";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_bank_details = mysqli_query($connection, $Query);
        if (!$insert_bank_details) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }
       function update_bank_details($tableNM,$id) {
        $this->table_name = $tableNM;
        $bname = $_POST['bname'];
        //$acno = $_POST['acno'];
        $ifsc = $_POST['ifsc'];
        $acno = $_POST['acno'];
        $acHN=$_POST['acHN'];
       // $Query = "INSERT INTO " . $this->table_name . "(`bank_name`, `ifscf_code`, `acno`, `acHN`, `createOn`) VALUES ('$bname','$ifsc','$acno','$acHN',now())";
      
        $query="UPDATE `bank_details` SET `bank_name`='$bname',`ifscf_code`='$ifsc',`acno`='$acno',`acHN`='$acHN' WHERE id='$id' ";
        $connection = mysqli_connect('localhost', 'planatir_task_managemen', 'Bishan@1919', 'planatir_task_managemen');
        $insert_bank_details = mysqli_query($connection, $query);
        if (!$insert_bank_details) {
            die('QUERY FAILD' . mysqli_error($connection));
        } else {
            return 'pass';
        }
    }

    function gen_image_code_unique() {

        $today = date('YmdHi');
        $startDate = date('YmdHi', strtotime('-10 days'));
        $range = $today - $startDate;
        $rand = rand(0, $range);
        return ($startDate + $rand);
    }


function imageResize($imageResourceId,$width,$height) {


    $targetWidth =900;
    $targetHeight =548;


    $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
    imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);


    return $targetLayer;
}
  
}

?>