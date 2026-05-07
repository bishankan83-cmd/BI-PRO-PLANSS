<?php
define('DB_SERVER','localhost');
define('DB_USER','planatir_task_managemen');
define('DB_PASS' ,'Bishan@1919');
define('DB_NAME','planatir_newsportal');
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>       