<?php
/*
 * Order Selection Page - Step 1
 * Place this file in: /home/planatir/public_html/cms/admin/select_order.php
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================
class Database {
    private $host = "localhost";
    private $db_name = "planatir_cms";
    private $username = "planatir_task_managemen";
    private $password = "Bishan@1919";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

// Get all tire orders excluding 'revised' status
$query = "SELECT order_id, plate, customer_id, order_date, status, destination_port, 
                 container_size, total_payment, shipping_method 
          FROM tire_orders 
          WHERE LOWER(status) != 'revised'
          ORDER BY order_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Order - Shipping Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --black: #000000;
            --red: #FF0000;
            --red-accent: #ff4757;
            --border-gray: #e0e0e0;
            --light-border: #CCCCCC;
            --bg-light: #f9f9f9;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #555555;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(45deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-active: 0 12px 40px rgba(242, 128, 24, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, rgba(242, 128, 24, 0.05) 100%);
            min-height: 100vh;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--border-gray);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 30px;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-1);
            border-radius: 2px;
        }
        
        .header h1 {
            color: var(--dark-gray);
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }
        
        .header h1 i {
            color: var(--primary-orange);
        }
        
        .header p {
            color: var(--text-gray);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            padding: 30px;
            background: var(--bg-light);
            border-radius: 15px;
            border: 1px solid var(--border-gray);
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-1);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            box-shadow: var(--shadow-soft);
        }
        
        .step-number.inactive {
            background: var(--light-gray);
            color: var(--text-gray);
            box-shadow: none;
        }
        
        .step-text {
            font-weight: 700;
            color: var(--primary-orange);
            font-size: 1.1rem;
        }
        
        .step-text.inactive {
            color: var(--text-gray);
            font-weight: 600;
        }
        
        .step-arrow {
            margin: 0 30px;
            color: var(--border-gray);
            font-size: 28px;
            font-weight: bold;
        }
        
        .search-box {
            margin-bottom: 30px;
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            font-size: 1.1rem;
        }
        
        .search-box input {
            width: 100%;
            padding: 18px 20px 18px 55px;
            border: 2px solid var(--border-gray);
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            background: var(--white);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px var(--orange-light);
        }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .order-card {
            border: 2px solid var(--border-gray);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--white);
            position: relative;
            overflow: hidden;
        }
        
        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-1);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .order-card:hover::before {
            transform: scaleX(1);
        }
        
        .order-card:hover {
            border-color: var(--primary-orange);
            box-shadow: var(--shadow-hover);
            transform: translateY(-5px);
        }
        
        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .order-id {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--dark-gray);
            letter-spacing: -0.02em;
        }
        
        .order-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }
        
        .status-pending {
            background: var(--warning-light);
            color: var(--warning);
        }
        
        .status-completed {
            background: var(--success-light);
            color: var(--success);
        }
        
        .status-processing {
            background: var(--orange-light);
            color: var(--primary-orange);
        }
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            font-size: 14px;
        }
        
        .detail-label {
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .detail-value {
            color: var(--dark-gray);
            font-weight: 700;
            font-size: 0.95rem;
        }
        
        .btn-select {
            width: 100%;
            padding: 14px;
            background: var(--gradient-1);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-soft);
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-select:hover {
            background: var(--secondary-orange);
            transform: translateY(-2px);
            box-shadow: var(--shadow-active);
        }
        
        .btn-select:active {
            transform: translateY(0);
        }
        
        .no-orders {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-gray);
            grid-column: 1 / -1;
        }
        
        .no-orders i {
            font-size: 80px;
            margin-bottom: 25px;
            display: block;
            color: var(--border-gray);
        }
        
        .no-orders h2 {
            color: var(--dark-gray);
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .no-orders p {
            font-size: 1.1rem;
            color: var(--text-gray);
        }
        
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding: 25px;
            background: var(--bg-light);
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid var(--border-gray);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 900;
            color: var(--primary-orange);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-gray);
            font-weight: 600;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 25px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .step-indicator {
                flex-direction: column;
                gap: 20px;
            }
            
            .step-arrow {
                transform: rotate(90deg);
                margin: 0;
            }
            
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-bar {
                flex-direction: column;
                gap: 20px;
            }
        }
        
        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .order-card {
            animation: fadeIn 0.5s ease-out;
        }
        
        .order-card:nth-child(1) { animation-delay: 0.05s; }
        .order-card:nth-child(2) { animation-delay: 0.1s; }
        .order-card:nth-child(3) { animation-delay: 0.15s; }
        .order-card:nth-child(4) { animation-delay: 0.2s; }
        .order-card:nth-child(5) { animation-delay: 0.25s; }
        .order-card:nth-child(6) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shipping-fast"></i> Shipping Management</h1>
            <p>Select a tire order to add shipping information</p>
        </div>
        
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">1</div>
                <span class="step-text">Select Order</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step">
                <div class="step-number inactive">2</div>
                <span class="step-text inactive">Enter Shipping Data</span>
            </div>
        </div>
        
        <?php
        $allOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalOrders = count($allOrders);
        
        $statusCounts = ['pending' => 0, 'processing' => 0, 'completed' => 0];
        foreach ($allOrders as $order) {
            $status = strtolower($order['status']);
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        ?>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by Order ID, Plate, or Destination..." onkeyup="filterOrders()">
        </div>
        
        <div class="orders-grid" id="ordersGrid">
            <?php
            if (count($allOrders) > 0) {
                foreach ($allOrders as $order) {
                    $statusClass = '';
                    switch (strtolower($order['status'])) {
                        case 'completed':
                            $statusClass = 'status-completed';
                            break;
                        case 'processing':
                            $statusClass = 'status-processing';
                            break;
                        default:
                            $statusClass = 'status-pending';
                    }
                    ?>
                    <div class="order-card" data-search="<?php echo strtolower(htmlspecialchars($order['order_id'] . ' ' . $order['plate'] . ' ' . $order['destination_port'])); ?>">
                        <div class="order-card-header">
                            <div class="order-id"><?php echo htmlspecialchars($order['order_id']); ?></div>
                            <div class="order-status <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-car"></i> Plate</div>
                                <div class="detail-value"><?php echo htmlspecialchars($order['plate']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-calendar"></i> Order Date</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Destination</div>
                                <div class="detail-value"><?php echo htmlspecialchars($order['destination_port']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-box"></i> Container</div>
                                <div class="detail-value"><?php echo htmlspecialchars($order['container_size']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-ship"></i> Method</div>
                                <div class="detail-value"><?php echo htmlspecialchars($order['shipping_method']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-dollar-sign"></i> Total</div>
                                <div class="detail-value">$<?php echo number_format(floatval($order['total_payment'] ?? 0), 2); ?></div>
                            </div>
                        </div>
                        
                        <button class="btn-select" onclick="selectOrder('<?php echo htmlspecialchars($order['order_id']); ?>')">
                            Select This Order <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-orders">';
                echo '<i class="fas fa-inbox"></i>';
                echo '<h2>No Orders Found</h2>';
                echo '<p>There are no tire orders available in the system.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    
    <script>
        function selectOrder(orderId) {
            window.location.href = 'data_enter_mar2.php?order_id=' + encodeURIComponent(orderId);
        }
        
        function filterOrders() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const orderCards = document.querySelectorAll('.order-card');
            let visibleCount = 0;
            
            orderCards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchInput)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (visibleCount === 0 && searchInput !== '') {
                if (!document.querySelector('.no-results')) {
                    const ordersGrid = document.getElementById('ordersGrid');
                    const noResults = document.createElement('div');
                    noResults.className = 'no-orders no-results';
                    noResults.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h2>No Results Found</h2>
                        <p>No orders match your search criteria. Try a different search term.</p>
                    `;
                    ordersGrid.appendChild(noResults);
                }
            } else {
                const noResults = document.querySelector('.no-results');
                if (noResults) {
                    noResults.remove();
                }
            }
        }
        
        window.onscroll = function() {
            const scrollBtn = document.getElementById('scrollTopBtn');
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                if (scrollBtn) scrollBtn.style.display = 'block';
            } else {
                if (scrollBtn) scrollBtn.style.display = 'none';
            }
        };
    </script>
</body>
</html>