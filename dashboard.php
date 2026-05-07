


 



<?php

error_reporting(0);
ini_set('display_errors', 0);
// Database connection
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$database = "planatir_task_managemen";

// Create a connection to the database
$conn = mysqli_connect($servername, $username, $password, $database);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to retrieve distinct ERP numbers
$query = "SELECT DISTINCT erp FROM plannew";

// Execute the query
$result = mysqli_query($conn, $query);

// Check if the query was successful
if ($result) {
    // Display the distinct ERP numbers
    $erpCount = mysqli_num_rows($result); // Get the count of ERP numbers

   
}

// Close the database connection
mysqli_close($conn);
?>  

 









<?php
include './includes/admin_header.php';
include './includes/data_base_save_update.php';
include 'includes/App_Code.php';
$AppCodeObj = new App_Code();


$totalCStock = 0;
$totalCStockk = 0;
$totalcount = 0;
$totalcountt = 0;
$totaltobe = 0;
$totalnew = 0;
$totalCavityID = 0;

// Execute a SQL query to calculate the sum of cstock
$query = "SELECT SUM(cstock) AS total_cstock FROM realstock";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCStock = $row['total_cstock'];
    mysqli_free_result($result);
}

// Execute a SQL query to calculate the sum of cstock
$query = "SELECT SUM(cstock) AS total_cstockk FROM stock";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCStockk = $row['total_cstockk'];
    mysqli_free_result($result);
}


// Execute a SQL query to calculate the sum of cstock
$query = "SELECT count(id) AS total_count FROM work_order";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalcount = $row['total_count'];
    mysqli_free_result($result);
}





// SQL query to get the total number of erp for the current month
$sql = "SELECT COUNT(DISTINCT erp_number) AS total_erp
        FROM pros
        WHERE MONTH(dispatch_date) = MONTH(CURDATE()) AND YEAR(dispatch_date) = YEAR(CURDATE())";

$result = $connection->query($sql);
if ($result) {
   
    $row = mysqli_fetch_assoc($result);
        $totalcountt= $row['total_erp'];
        mysqli_free_result($result);
      
} 

$query = "SELECT SUM(tobe) AS total_tobe FROM tobeplan1 WHERE  tobe > 0";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totaltobe = $row['total_tobe'];
    mysqli_free_result($result);
}

$query = "SELECT SUM(new) AS total_new FROM worder";
$result = mysqli_query($connection, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalnew = $row['total_new'];
    mysqli_free_result($result);
}


// Subtract On Hand Work Orders from Running Order
$result =$totalcount - $erpCount;





?>



<!--------------------
START - Breadcrumbs
-------------------->
<!--------------------
END - Breadcrumbs
-------------------->

<div class="content-i">
    <div class="content-box">
        <marquee direction="left" style="background: #000000;">
            <span class="breadcrumb-item">
            <img src="atire.png" alt="Logo" style="height: 50px; margin-right: 20px;">
                <?php
                $qry = mysqli_query($connection, "SELECT * FROM news_and_update where news_type='alert' order by created desc") or die("select query fail" . mysqli_error());
                while ($row = mysqli_fetch_assoc($qry)) {
                    $news_title = $row['news_title'];
                    ?>
                    <a href="#" style="color:#f28018; font-size: 18px;"><?php echo $news_title; ?>&nbsp;<strong></strong></a>
                <?php } ?>
               
            </span>
        </marquee>
        

        









        <?php
        if ($_SESSION['User_type'] == 'admin') {
            include './includes/admin_dashboard.php';
        } elseif ($_SESSION['User_type'] == 'fmanager') {
            include './includes/fmanager.php';
        } elseif ($_SESSION['User_type'] == 'qmanager') {
            include './includes/qad_manager.php';
        }elseif ($_SESSION['User_type'] == 'qad') {
            include './includes/qad.php';
        }
        elseif ($_SESSION['User_type'] == 'stock') {
            include './includes/stockceper.php';
        }
        elseif ($_SESSION['User_type'] == 'Planning') {
            include './includes/planning_dashboard.php';
        }
        elseif ($_SESSION['User_type'] == 'mixing') {
            include './includes/mixing_dashboard.php';
        }

        elseif ($_SESSION['User_type'] == 'mixing2') {
            include './includes/mixing_dashboard2.php';
        }
     
     
        elseif ($_SESSION['User_type'] == 'lab') {
            include './includes/lab_dashboard.php';
        }


         elseif ($_SESSION['User_type'] == 'lab_ndr') {
            include './includes/lab_dashboard_b.php';
        }
        elseif ($_SESSION['User_type'] == 'mold') {
            include './includes/mold_dashboard.php';
        }
        

        elseif ($_SESSION['User_type'] == 'uk') {
            include './includes/uk_dashboard.php';
            //include './includes/break.php';
        }
        elseif ($_SESSION['User_type'] == 'MIX_MAN') {
            include './includes/mix_man_dashboard.php';
        }
        elseif ($_SESSION['User_type'] == 'Director') {
            include './includes/dir_dashboard.php';
            //include './includes/break.php';
        }

        elseif ($_SESSION['User_type'] == 'audit') {
            include './includes/audit_dashboard.php';
            //include './includes/break.php';
        }

            elseif ($_SESSION['User_type'] == 'General Manager') {
            include './includes/gm_dashboard.php';
            //include './includes/break.php';
        }
        elseif ($_SESSION['User_type'] == 'Source department') {
            include './includes/source_dep_dashboard.php';
            //include './includes/break.php';
        }

        elseif ($_SESSION['User_type'] == 'rm stores') {
            include './includes/rm_stores_dashboard.php';
            //include './includes/break.php';
        }

        elseif ($_SESSION['User_type'] == 'pro_sup') {
            include './includes/pro_sup.php';
            //include './includes/break.php';
        }


         elseif ($_SESSION['User_type'] == 'Erp_user') {
            include './includes/Erp_user_dash.php';
            //include './includes/break.php';
        }

        elseif ($_SESSION['User_type'] == 'production') {
            include './includes/emp_dashboard.php';
            //include './includes/break.php';
        }

          elseif ($_SESSION['User_type'] == 'production') {
            include './includes/emp_dashboard.php';
            //include './includes/break.php';
        }
        else {
           // include './includes/emp_dashboard.php';
        }
        
        ?>
    </div>

    
</div> 



<?php include './includes/Plugin.php'; ?>


<script>
$(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'pdfHtml5'
        ]
    } );
});
</script>


    

