<?php
// Database connection
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch metrics
$queries = [
    "SELECT SUM(current_quantity) AS total_bands FROM steel_band_stock",
    "SELECT COUNT(*) AS total_orders FROM purchase_orders",
    "SELECT COUNT(*) AS inward_loan FROM loan_inward_details",
    "SELECT COUNT(*) AS outward_loan FROM loan_outward_details",
    "SELECT COUNT(*) AS po_su FROM po_suppliers",
    "SELECT COUNT(*) AS inward_su FROM loan_inward_suppliers",
    "SELECT COUNT(*) AS outward_su FROM loan_outward_suppliers",
    // New queries for additional metrics
    
    "SELECT COUNT(*) AS complete_purchase FROM purchase_orders2",
    "SELECT COUNT(*) AS complete_loan_inward FROM loan_inward_details2",
    "SELECT COUNT(*) AS complete_loan_outward FROM loan_outward_details2",
    "SELECT COUNT(*) AS mrn_issue FROM material_request",
    "SELECT COUNT(*) AS mrn_issue2 FROM material_request_history",  // Assuming MRN is related to inward loan
    "SELECT COUNT(*) AS loan_settlement FROM loan_inward_details_settle",
    "SELECT COUNT(*) AS loan_given_settlement FROM loan_outward_details_settle",
];

$metrics = [];
foreach ($queries as $query) {
    $result = $conn->query($query);
    $metrics[] = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #2C3E50;
            --background-color: #F5F6FA;
            --card-background: #FFFFFF;
            --text-primary: #2C3E50;
            --text-secondary: #7F8C8D;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--background-color);
            padding: 2rem;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: var(--card-background);
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .dashboard-title {
            color: var(--text-primary);
            font-size: 1.8rem;
            font-weight: 600;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background-color: var(--card-background);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary-color);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(242, 128, 24, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .card-icon i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .card-title {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-top: 0.5rem;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Raw Material Department</h1>
            <div class="date"><?php echo date('l, F j, Y'); ?></div>
        </div>

        <div class="cards-grid">
            <!-- Existing Cards -->
            <a href="band_summery.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h3 class="card-title">Steel Band Stock</h3>
                    </div>
                    
                </div>
            </a>

            <a href="view_purchase_orders.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="card-title">Purchase Orders</h3>
                    </div>
                   
                </div>
            </a>

            <a href="loan_inward_display.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <h3 class="card-title">Loan Inward</h3>
                    </div>
                   
                </div>
            </a>

            <a href="loan_outward_display.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <h3 class="card-title">Loan Outward</h3>
                    </div>
                  
                </div>
            </a>

         
        

            
            <!-- New Cards -->
            <a href="inward_pending.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="card-title">Complete Purchase</h3>
                    </div>
                    
                </div>
            </a>

            <a href="inward_loan.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3 class="card-title">Complete Loan Inward</h3>
                    </div>
                   
                </div>
            </a>

            <a href="outward_loan.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="card-title">Complete Loan Outward</h3>
                    </div>
                   
                    
                </div>
            </a>

            <a href="mrn_details_pen.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="card-title">MRN Issue</h3>
                    </div>
                   
                </div>
            </a>

          

            <a href="mrn_details.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="card-title">Complete MRN Issue</h3>
                    </div>
                   
                </div>
            </a>

            <a href="display_loan_inward_details_settle.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 class="card-title">Loan Settlement</h3>
                    </div>
                    
                </div>
            </a>

            <a href="display_loan_outward_details_settle.php" class="card-link">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3 class="card-title">Loan Given Settlement</h3>
                    </div>
                   
                </div>
            </a>
        </div>
    </div>
</body>
</html>