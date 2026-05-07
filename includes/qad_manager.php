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
        <marquee direction="right" style="background: #F28018; color: black;" onmouseover="this.stop();" onmouseout="this.start();">
    <span class="breadcrumb-item" style="cursor: pointer;">
    
        <span style="font-weight: bold; color: black;">FG Stock: <?php echo $totalCStock; ?></span> || 
        <span style="font-weight: bold; color: black;">Total Requirement: <?php echo $totalnew; ?></span> ||
        <span style="font-weight: bold; color: black;">Free Stock: <?php echo $totalCStockk; ?></span> || 
        <span style="font-weight: bold; color: black;">Tobe produce: <?php echo $totaltobe; ?></span> ||
        <span style="font-weight: bold; color: black;">On Hand Work Orders: <?php echo $totalcount; ?></span> || 
        <span style="font-weight: bold; color: black;">Production complete work orders: <?php echo $result; ?></span> || 
        <span style="font-weight: bold; color: black;">Tobe Produce Work Orders: <?php echo ($erpCount ); ?></span>  ||
        <span style="font-weight: bold; color: black;">Cavity Utilization: 71 </span> || 
        
        <span style="font-weight: bold; color: black;">Current Month Dispatched Order: <?php echo ($totalcountt );?></span> || 

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
        else {
            include './includes/emp_dashboard.php';
        }
        
        ?>
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
});
</script>
<footer>
        <!-- Your website footer content -->
        <?php include('foter.php'); ?>
    </footer>