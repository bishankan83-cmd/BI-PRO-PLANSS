


<marquee direction="right" style="background: #F28018; color: black;" onmouseover="this.stop();" onmouseout="this.start();">
    <span class="breadcrumb-item" style="cursor: pointer;">
    
        <span style="font-weight: bold; color: black;">FG Stock: <?php echo $totalCStock; ?></span> || 
        <span style="font-weight: bold; color: black;">Total Requirement: <?php echo $totalnew; ?></span> ||
        <span style="font-weight: bold; color: black;">Free Stock: <?php echo $totalCStockk; ?></span> || 
        <span style="font-weight: bold; color: black;">Tobe produce: <?php echo $totaltobe; ?></span> ||
        <span style="font-weight: bold; color: black;">On Hand Work Orders: <?php echo $totalcount; ?></span> || 
        <span style="font-weight: bold; color: black;">Production complete work orders: <?php echo $result; ?></span> || 
        <span style="font-weight: bold; color: black;">Tobe Produce Work Orders: <?php echo ($erpCount ); ?></span>  ||
        <span style="font-weight: bold; color: black;">Cavity Utilization: 29</span> || 
        
        <span style="font-weight: bold; color: black;">Current Month Dispatched Order: <?php echo ($totalcountt );?></span> || 

    </span>
</marquee>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Styling for the Dashboard elements */
        .element-content {
    background-color: #F28018;
    padding: 0;
    text-align: center;
    box-shadow: 2px 2px 4px rgba(0, 0, 10, 100);
    
    /* Make the element fill the full page */
    position: right;
    top: 0;
    left: 5px;
    width: 100%;
    height: 100%;
}



        .element-box {
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 60px;
            margin: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            
        }

        .element-header {
            color: #000000;
            font-size: 24px;
            font-weight: bold;
        }

        .element-box a {
            text-decoration: none;
            color: #F28018;
        }

        /* Styling for the specific elements with id="myDIV" */
        #myDIV {
            color: #000000;
            font-weight: bold;
            font-size: 20px;
            margin-top: 10px;
        }

        
    body {
        background-color: #FFFFFF;
    }


    </style>
    <title>Your Dashboard</title>
</head>
<body>
    
    <div class="element-content">
    <h6 style="background-color: #000000; color: #fff; border-radius: 50px; padding: 3px; text-align: center; font-weight: bold; font-family: 'Cantarell', sans-serif;">Dashboard - Repots</h6>
    <div style="text-align: center;">
    <label style="background: white; color: black; font-weight: bold;" onmouseover="this.stop();" onmouseout="this.start();">
       
                </label>
</div>



<style>
@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}
</style>
        <div class="row">
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="work_order_show.php">
                    <div id="myDIV">Work Order</div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="stock_button.php">
                    <div id="myDIV">Stock report</div>
                </a>
            </div>
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="dispatch_view.php">
                    <div id="myDIV">Dispatched work order</div>
                </a>
            </div>
         
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="match.php">
                    <div id="myDIV">Mold changing</div>
                </a>
            </div>
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="order_quantity.php">
                    <div id="myDIV">On hand orders -Item vice</div>
                </a>
            </div>
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="daily_production.php">
                    <div id="myDIV">Daily Production</div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="rejectbutton.php">
                    <div id="myDIV">Daily Reject</div>
                </a>
            </div>


            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="bom_all.php">
                    <div id="myDIV">Green Tire Weight</div>
                </a>
            </div>

           
           
            <div class="col-sm-4 col-xxxl-3">
                <a class="element-box el-tablo" href="planbuttoon.php">
                    <div id="myDIV">Planing Reports</div>
                </a>
            </div>
            <div class="col-sm-4 col-xxxl-4">
                <a class="element-box el-tablo" href="show_mixing.php">
                    <div id="myDIV">Compund Production</div>
                </a>
            </div>

            <div class="col-sm-4 col-xxxl-4">
                <a class="element-box el-tablo" href="lab_qr_details.php">
                    <div id="myDIV">QR Code Details</div>
                </a>
            </div>

        </div>
    </div>
</body>
</html>

