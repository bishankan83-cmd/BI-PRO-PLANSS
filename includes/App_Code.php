<?php

class App_Code {

    function Post_Read_more($post_id, $post_Content) {
        // echo "<script>alert(".$post_id.")</script>";
        $post_id1 = $post_id;
        $this->$post_Content = $post_Content;
        $string = strip_tags($post_Content);
        if (strlen($string) > 500) {
            // truncate string
            $stringCut = substr($string, 0, 500);
            $endPoint = strrpos($stringCut, ' ');

            //if the string doesn't contain any space then it will cut without word basis.
            $string = $endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
            $string .= "... <a href='news-details.php?post=$post_id1'>Read More</a>";
        }
        return $string;
    }

    function Post_heding_read_more($post_id, $News_title) {
        $post_id1 = $post_id;
        $this->$News_title = $News_title;
        $string = strip_tags($News_title);
        if (strlen($string) > 110) {
            // truncate string
            $stringCut = substr($string, 0, 110);
            $endPoint = strrpos($stringCut, ' ');

            //if the string doesn't contain any space then it will cut without word basis.
            $string = $endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
            $string .= "... <a href='news-details.php?post=$post_id1'>Read More</a>";
        }
        return $string;
    }

    function ShowMsg($MSG) {
        echo "<script>alert(" . $MSG . ")</script>";
    }
        function getTransaction(){
            mt_srand((double)microtime()*10000);
            $charid = md5(uniqid(rand(), true));
            $c = unpack("C*",$charid);
            $c = implode("",$c);

            return substr($c,0,20);
    }
     
       function getName($ID) {
        //    echo '<script>alert("Hello");</script>';
       $con = mysqli_connect("localhost", "planatir_task_managemen", "Bishan@1919", "planatir_task_managemen") or die(mysqli_connect_error());
        $UserRGID = $ID;

        $client = "select * from emp_login where id='$UserRGID'";
        $getNameQuery = mysqli_query($con, $client) or die(mysqli_error());

        while ($row = mysqli_fetch_assoc($getNameQuery)) {
            $emp_name = $row['emp_name'];
            $emp_code = $row['emp_code'];
         //  $User_type = $row['User_type'];   //    echo '<script>alert('.$UserMob.');</script>';
            return $emp_code . '/' . $emp_name;//. '/' . $UserMob;
        }
    }
    function convert_date($created)
    {
       // echo "<script>alert(".$created.");</script>";
               try {
    $date = new DateTime($created);
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}

return $tempDate=  $date->format('d-m-Y');
    }

}

?>