<?php
// Database connection configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = "Bishan@1919";
$database = 'planatir_task_managemen';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize RM Code filter value
    $rm_code_filter = isset($_GET['rm_code']) ? $_GET['rm_code'] : '';
    $band_size_filter = isset($_GET['band_size']) ? $_GET['band_size'] : '';

    // Get current month and year for filtering
    $current_month = date('Y-m');
    $current_year = date('Y');
    $current_month_number = date('m');

    // Prepare the query to fetch all distinct RM codes
    $rm_codes_query = "SELECT DISTINCT rm_code FROM steelrim_stock WHERE rm_code IS NOT NULL";
    $rm_codes_stmt = $pdo->prepare($rm_codes_query);
    $rm_codes_stmt->execute();
    $rm_codes = $rm_codes_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all distinct Band Sizes
    $band_sizes_query = "SELECT DISTINCT band_size FROM rm_band_data WHERE band_size IS NOT NULL";
    $band_sizes_stmt = $pdo->prepare($band_sizes_query);
    $band_sizes_stmt->execute();
    $band_sizes = $band_sizes_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Main query - Apply date filter to tables that have created_at column
    $query = "
        SELECT 
            sbs.rm_code, 
            sbs.current_quantity,
            -- Only filter purchase_orders2 by date (confirmed to have created_at)
            (SELECT COALESCE(SUM(po.number_of_bands), 0) 
                FROM purchase_orders2 po 
                WHERE po.rm_code = sbs.rm_code 
                AND YEAR(po.created_at) = :current_year 
                AND MONTH(po.created_at) = :current_month_number) AS total_bands_in_purchase_orders,

            (SELECT st.current_quantity
                FROM steel_band_stock st 
                WHERE st.rm_code = sbs.rm_code) AS stss,

            -- Check if loan_inward_details_settle has created_at, otherwise remove date filter
            (SELECT COALESCE(SUM(lid.number_of_bands), 0) 
                FROM loan_inward_details_settle lid 
                WHERE lid.rm_code = sbs.rm_code) AS total_bands_in_loan_inward,

            -- Check if purchase_orders has created_at, otherwise remove date filter
            (SELECT COALESCE(SUM(poo.number_of_bands), 0) 
                FROM purchase_orders poo 
                WHERE poo.rm_code = sbs.rm_code) AS pending_purchase_orders,

            -- Check if loan_outward_details2 has created_at, otherwise remove date filter
            (SELECT COALESCE(SUM(lod.number_of_bands), 0) 
                FROM loan_outward_details_settle lod 
                WHERE lod.rm_code = sbs.rm_code) AS total_bands_in_loan_outward,

            (SELECT COALESCE(SUM(liddd.number_of_bands), 0) 
                FROM loan_outward_details2 liddd 
                WHERE liddd.rm_code = sbs.rm_code) AS lids,

            (SELECT COALESCE(SUM(liddd.number_of_bands), 0) 
                FROM loan_outward_details_settle liddd 
                WHERE liddd.rm_code = sbs.rm_code) AS lidss,

            -- Remove date filter from loan_inward_details (doesn't have created_at)
            (SELECT COALESCE(SUM(lidd.number_of_bands), 0) 
                FROM loan_inward_details lidd 
                WHERE lidd.rm_code = sbs.rm_code) AS pending_loan_inward,

            (SELECT COALESCE(SUM(liddd.number_of_bands), 0) 
                FROM loan_inward_details2 liddd 
                WHERE liddd.rm_code = sbs.rm_code) AS loan_inward_settlement,

            (SELECT COALESCE(SUM(lidddp.number_of_bands), 0) 
                FROM loan_inward_details_settle lidddp 
                WHERE lidddp.rm_code = sbs.rm_code) AS loan_inward_settlementp,

            (SELECT COALESCE(SUM(lidddpp.number_of_bands), 0) 
                FROM loan_outward_details2 lidddpp 
                WHERE lidddpp.rm_code = sbs.rm_code) AS loan_outward_settlement,

            (SELECT COALESCE(SUM(mrss.number_of_bands), 0) 
                FROM loan_outward_details_settle mrss 
                WHERE mrss.rm_code = sbs.rm_code) AS loan_outward_settlementp,

            -- ADD MONTHLY FILTER to material_request_history (has created_at column)
            (SELECT COALESCE(SUM(mrs.num_of_bands), 0) 
                FROM material_request_history mrs 
                WHERE mrs.rm_code = sbs.rm_code 
                AND YEAR(mrs.created_at) = :current_year2 
                AND MONTH(mrs.created_at) = :current_month_number2) AS material_request_history,

            -- NEW: Add material_request from main material_request table (without date filter initially)
            (SELECT COALESCE(SUM(mr.num_of_bands), 0) 
                FROM material_request mr 
                WHERE mr.rm_code = sbs.rm_code) AS material_request_main,

            -- Remove date filter from loan_outward_details (doesn't have created_at)
            (SELECT COALESCE(SUM(lodd.number_of_bands), 0) 
                FROM loan_outward_details lodd 
                WHERE lodd.rm_code = sbs.rm_code) AS pending_loan_outward,

            (SELECT COALESCE(SUM(pen.number_of_bands), 0) 
                FROM purchase_orders pen
                WHERE pen.rm_code = sbs.rm_code) AS pending_loan_outwardd,

            (SELECT COALESCE(SUM(penn.number_of_bands), 0) 
                FROM loan_inward_details penn
                WHERE penn.rm_code = sbs.rm_code) AS pending_loan_outwarddd,

            (SELECT COALESCE(SUM(pennns.number_of_bands), 0) 
                FROM loan_outward_details pennns
                WHERE pennns.rm_code = sbs.rm_code) AS pending_loan_outwardddd,

            (SELECT band_size 
                FROM rm_band_data rbd
                WHERE rbd.rm_code = sbs.rm_code LIMIT 1) AS band_size,

            -- NEW: WO Allocations - Sum of positive tobe values from tobeplan1 
            -- where tire_code matches between tire_steel_data and tobeplan1 (via icode)
            (SELECT COALESCE(SUM(tp.tobe), 0)
                FROM tobeplan1 tp
                INNER JOIN tire_steel_data tsd ON tp.icode = tsd.tire_code
                WHERE tsd.RM_code = sbs.rm_code
                AND tp.tobe > 0) AS wo_allocations

        FROM 
            steelrim_stock sbs
        WHERE 
            sbs.rm_code IS NOT NULL";

    // Add filter conditions
    if (!empty($rm_code_filter)) {
        $query .= " AND sbs.rm_code = :rm_code";
    }

    if (!empty($band_size_filter)) {
        $query .= " AND EXISTS (
            SELECT 1 FROM rm_band_data rbd
            WHERE rbd.rm_code = sbs.rm_code AND rbd.band_size = :band_size
        )";
    }

    $query .= " ORDER BY total_bands_in_purchase_orders DESC";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($query);

    // Bind the month and year parameters (including new ones for material_request_history)
    $stmt->bindParam(':current_year', $current_year, PDO::PARAM_INT);
    $stmt->bindParam(':current_month_number', $current_month_number, PDO::PARAM_INT);
    $stmt->bindParam(':current_year2', $current_year, PDO::PARAM_INT);
    $stmt->bindParam(':current_month_number2', $current_month_number, PDO::PARAM_INT);

    if (!empty($rm_code_filter)) {
        $stmt->bindParam(':rm_code', $rm_code_filter);
    }

    if (!empty($band_size_filter)) {
        $stmt->bindParam(':band_size', $band_size_filter);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // HTML Output with enhanced styling
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Monthly Stock Summary Report</title>
        <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap' rel='stylesheet'>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            :root {
                --inward-color: #e6f2ff;
                --inward-text: #0056b3;
                --loan-taken-color: #e6f9f0;
                --loan-taken-text: #007E33;
                --loan-given-color: #fff3e6;
                --loan-given-text: #8B4513;
                --available-stocks-color: #ffe6f2;
                --available-stocks-text: #9C27B0;
                --pending-order-color: #f0f9e6;
                --pending-order-text: #2E7D32;
                --pending-stocks-color: #e6e6ff;
                --pending-stocks-text: #3F51B5;
                --material-request-color: #fff9e6;
                --material-request-text: #FF8C00;
                --wo-allocation-color: #f0f0f0;
                --wo-allocation-text: #424242;
            }
            body {
                font-family: 'Inter', sans-serif;
                background-color:black;
                color: orange;
            }
            .report-container {
                background-color: white;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                padding: 30px;
                margin-top: 30px;
            }
            .report-header {
                background: linear-gradient(to right, black, #F28018);
                color: white;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 25px;
            }
            .report-header h2 {
                margin: 0;
                font-weight: 700;
            }
            .month-indicator {
                background-color: #e3f2fd;
                color: #1976d2;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 20px;
                text-align: center;
                font-weight: 600;
            }
            .filter-section {
                display: flex;
                gap: 15px;
                margin-bottom: 20px;
            }
            .filter-section select, .filter-section .btn {
                transition: all 0.3s ease;
            }
            .filter-section select:focus, .filter-section .btn:focus {
                box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            }

        table {
            border-collapse: collapse;
            width: 100%;
            
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f4f4f4;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        td {
            background-color: #fff;
        }

    th.sticky-rm-code {
    vertical-align: middle;
    text-align: center;
    font-weight: 600;
    color: white;
    padding: 12px;
    position: sticky;
    left: 0;
    top: 0;
    background-color: #F28018;
    z-index: 2;
}
    td.sticky-rm-code {
    vertical-align: middle;
    text-align: center;
    font-weight: 600;
    padding: 12px;
    position: sticky;
    left: 0;
    top: 0;
    background-color: rgb(252, 251, 250);
    z-index: 2;
}

th.sticky-band-size {
    vertical-align: middle;
    text-align: center;
    font-weight: 600;
    color: white;
    padding: 12px;
    position: sticky;
    top: 0;
    left: 100px;
    background-color: #F28018;
    z-index: 2;
}
    td.sticky-band-size {
    vertical-align: middle;
    text-align: center;
    font-weight: 600;
    padding: 12px;
    position: sticky;
    top: 0;
    left: 100px;
    background-color: rgb(252, 251, 250);
    z-index: 2;
}

th.sticky-rm-code {
    z-index: 4;
}

th.sticky-band-size {
    z-index: 4;
}

            
            /* Colored Group Styles */
            .inward-group { 
                background-color: var(--inward-color) !important; 
                color: var(--inward-text) !important; 
            }
            .loan-taken-group { 
                background-color: var(--loan-taken-color) !important; 
                color: var(--loan-taken-text) !important; 
            }
            .loan-given-group { 
                background-color: var(--loan-given-color) !important; 
                color: var(--loan-given-text) !important; 
            }
            .available-stocks-group { 
                background-color: var(--available-stocks-color) !important; 
                color: var(--available-stocks-text) !important; 
            }
            .pending-order-group { 
                background-color: var(--pending-order-color) !important; 
                color: var(--pending-order-text) !important; 
            }
            .pending-stocks-group { 
                background-color: var(--pending-stocks-color) !important; 
                color: var(--pending-stocks-text) !important; 
            }
            .material-request-group { 
                background-color: var(--material-request-color) !important; 
                color: var(--material-request-text) !important; 
            }
            .wo-allocation-group { 
                background-color: var(--wo-allocation-color) !important; 
                color: var(--wo-allocation-text) !important; 
            }
        </style>
    </head>
    <body>
    <div class='container-fluid'>
        <div class='row'>
            <div class='col-12'>
                <div class='report-container'>
                    <div class='report-header'>
                        <h2 class='text-center'>Monthly Stock and Band Summary Report</h2>
                    </div>
                    
                    <div class='month-indicator'>
                        <strong>Monthly Filter Applied: " . date('F Y') . "</strong>
                        <br><small>purchase_orders2 and material_request_history tables filtered by current month. Other tables show all historical data.</small>
                    </div>
                    
                    <form method='get' class='filter-section'>
                        <select id='rm_code' name='rm_code' class='form-select'>
                            <option value=''>Select RM Code</option>";
    
    foreach ($rm_codes as $rm_code) {
        $selected = ($rm_code['rm_code'] == $rm_code_filter) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($rm_code['rm_code']) . "' $selected>" . 
             htmlspecialchars($rm_code['rm_code']) . "</option>";
    }
    
    echo "</select>
                        
                        <select id='band_size' name='band_size' class='form-select'>
                            <option value=''>Select Band Size</option>";
    
    foreach ($band_sizes as $band_size) {
        $selected = ($band_size['band_size'] == $band_size_filter) ? 'selected' : '';
        echo "<option value='" . htmlspecialchars($band_size['band_size']) . "' $selected>" . 
             htmlspecialchars($band_size['band_size']) . "</option>";
    }
    
    echo "</select>
                        <button type='submit' class='btn btn-primary'>Apply Filter</button>
                    </form>
                    
                    <div class='table-responsive'>
                        <table class='table table-bordered table-striped'>
                            <thead>
                                <tr>
                               <th class='sticky-rm-code'>RM Code</th>
                                  <th class='sticky-band-size'>Band Size</th>
                                <th class='inward-group'> Opening Stocks</th>
                                   <th class='inward-group'>Inward</th>
                                   <th class='inward-group'> Outward(MRN ISSUE)</th>
                                       
                                <th class='inward-group'>Closing Stock</th>


                                   <th class='loan-taken-group'> Loan Taken</th>
                                 <th class='loan-taken-group'>Loan Settlement</th>
                                   <th class='loan-taken-group'> Loan Balance to Settle</th>

                                     <th class='loan-given-group'> Loan Given</th>
                                    <th class='loan-given-group'>Loan Given Receive</th>
                                    <th class='loan-given-group'>Loan balance to receive</th>

                                    <th class='available-stocks-group'> Available Stocks with Loan Balance</th>
                                    <th class='pending-order-group'> Pending Purchase Order</th>
                                   <th class='pending-order-group'>Pending Loan Inward</th>
                                        <th class='pending-order-group'> Pending Loan Outward</th>
                                 <th class='pending-order-group'>Total Pending Order</th>
                                   <th class='pending-stocks-group'> Stocks with Pending Orders</th>

                                   
                                     <th class='material-request-group'> Pending Outward (MRN ISSUE)</th>
                                  
                                  <th class='wo-allocation-group'> WO Allocations</th>
                                  <th class='wo-allocation-group'> Balance After WO Allocations</th>
                                </tr>
                            </thead>
                            <tbody>";
                         
    foreach ($results as $row) {
        // Calculations remain the same as previous script, but now use material_request_history instead of material_request
        $remaining_bandss =  $row['material_request_history'] - $row['total_bands_in_purchase_orders']   ;
        $remaining_bands= $remaining_bandss +  $row['current_quantity'];
        $loan_inward_difference =  $row['loan_inward_settlement'];
        $loan_balance =   $row['total_bands_in_loan_outward'] ;
        $total_pending = $row['pending_loan_outwardd'] + $row['pending_loan_outwarddd'] + $row['pending_loan_outwardddd'] ;
        
        $combined_bandss = $loan_balance - $loan_inward_difference;
        
        $combined_bands = $remaining_bands - $combined_bandss ;
        $combined_bandsss = $total_pending + $combined_bands;

       
        $ltaken = $row['loan_inward_settlementp'] +  $row['loan_inward_settlement'];

        $otaken = $row['loan_outward_settlement'] +  $row['loan_outward_settlementp'];

        $real= $row['current_quantity'] - $remaining_bandss;

           $acct =  abs($real) -  abs($combined_bandss);

           $bis =  $total_pending + $acct; 

        // Calculate Balance After WO Allocations
        $balance_after_wo = $acct - $row['wo_allocations'];

        echo "<tr>
          <td class='sticky-rm-code'>" . htmlspecialchars($row['rm_code']) . "</td>
         <td class='sticky-band-size' >" . htmlspecialchars($row['band_size']) . "</td>
         <td class='inward-group'>" . htmlspecialchars($row['current_quantity']) . "</td>
          <td class='inward-group'>" . htmlspecialchars($row['total_bands_in_purchase_orders']) . "</td>
           <td class='inward-group'>" . htmlspecialchars($row['material_request_history']) . "</td>
    
         <td class='inward-group'>" . htmlspecialchars($real) . "</td>


           
           <td class='loan-taken-group'>" . htmlspecialchars($ltaken) . "</td>
           <td class='loan-taken-group'>" . htmlspecialchars($row['total_bands_in_loan_inward']) . "</td>
          <td class='loan-taken-group'>" . htmlspecialchars($loan_inward_difference) . "</td>

             <td class='loan-given-group'>" . htmlspecialchars($otaken) . "</td>
         <td class='loan-given-group'>" . htmlspecialchars($row['total_bands_in_loan_outward']) . "</td>
          <td class='loan-given-group'>" . htmlspecialchars($row['loan_outward_settlement']) ."</td>
          <td class='available-stocks-group'>" . htmlspecialchars($acct) . "</td>
 


            <td class='pending-order-group'>" . htmlspecialchars($row['pending_loan_outwardd']) . "</td>
            <td class='pending-order-group'>" . htmlspecialchars($row['pending_loan_outwarddd']) . "</td>
               <td class='pending-order-group'>" . htmlspecialchars($row['pending_loan_outwardddd']) . "</td>
         <td class='pending-order-group'>" . htmlspecialchars($total_pending) . "</td>
           
          <td class='pending-stocks-group'>" . htmlspecialchars($bis) . "</td>
         
                   <td class='material-request-group'>" . htmlspecialchars($row['material_request_main']) . "</td>
            
          <td class='wo-allocation-group'>" . htmlspecialchars($row['wo_allocations']) . "</td>
           <td class='wo-allocation-group'>" . htmlspecialchars($balance_after_wo) . "</td>
        </tr>";
    }

    echo "</tbody>
    </table>
    </div>
    </div>
    </div>
    </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>