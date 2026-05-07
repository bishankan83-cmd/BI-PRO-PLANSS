



<?php
// Database configuration
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_task_managemen";

// Initialize variables
$totalMolds = 0;
$activeMolds = 0;
$errorMessage = null;

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        $errorMessage = 'Database connection failed: ' . $conn->connect_error;
    } else {
        // Query to get total mold count from mold table
        $sqlTotal = "SELECT COUNT(*) as totalMolds FROM mold";
        $resultTotal = $conn->query($sqlTotal);

        if ($resultTotal) {
            $rowTotal = $resultTotal->fetch_assoc();
            $totalMolds = (int)$rowTotal['totalMolds'];
        } else {
            $errorMessage = 'Total molds query failed: ' . $conn->error;
        }

        // Query to get count of distinct mold_id from plannew table as Active Molds
        $sqlActive = "SELECT COUNT(DISTINCT mold_id) as activeMolds FROM plannew";
        $resultActive = $conn->query($sqlActive);

        if ($resultActive) {
            $rowActive = $resultActive->fetch_assoc();
            $activeMolds = (int)$rowActive['activeMolds'];
        } else {
            $errorMessage = $errorMessage ? $errorMessage . '; Active molds query failed: ' . $conn->error : 'Active molds query failed: ' . $conn->error;
        }

        // Close connection
        $conn->close();
    }
} catch (Exception $e) {
    $errorMessage = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dashboard - Tire Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        .dashboard-container {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin: 20px;
            min-height: calc(100vh - 40px);
            position: relative;
            overflow: hidden;
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float 6s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .header {
            background: linear-gradient(135deg, #F28018 0%, #ff6b35 100%);
            padding: 20px 30px;
            border-radius: 20px 20px 0 0;
            position: relative;
            z-index: 10;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo::before {
            content: "⚙️";
            font-size: 24px;
        }

        .header-title {
            color: white;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin-top: 2px;
        }

        .user-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

       
.logout-btn {
    color: white;
    background-color: black;
    border: none;
    padding: 12px 24px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}
        .logout-btn:hover {
            background: rgba(233, 145, 14, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .main-content {
            padding: 30px;
            position: relative;
            z-index: 10;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #F28018, #ff6b35);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            background: linear-gradient(135deg, #F28018, #ff6b35);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #28a745;
            margin-top: 10px;
        }

        .trend-up::before {
            content: "↗️";
        }

        .action-section {
            margin-top: 30px;
        }

        .section-title {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }

        .action-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(242, 128, 24, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .action-card:hover::after {
            left: 100%;
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            background: linear-gradient(135deg, #F28018, #ff6b35);
            margin-bottom: 20px;
        }

        .action-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .action-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }

        .live-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .user-section {
                align-items: center;
                gap: 10px;
            }

            .main-content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-grid {
                grid-template-columns: 1fr;
            }
        }

        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .message {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .message.show {
            transform: translateX(0);
        }

        .message.success {
            background: rgba(40, 167, 69, 0.9);
        }

        .message.error {
            background: rgba(220, 53, 69, 0.9);
        }
    </style>
</head>
<body>
   

    <div class="dashboard-container">
        <div class="particles" id="particles"></div>

        <header class="header">
            <div class="header-content">
                <div class="logo-section">
                    <div class="logo"></div>
                    <div>
                        <h1 class="header-title">Tire Mold Dashboard</h1>
                        <p class="header-subtitle">Real-time Manufacturing Control</p>
                    </div>
                </div>
              
        </header>

        <main class="main-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">🔧</div>
                    </div>
                    <div class="stat-value" id="totalMolds">0</div>
                    <div class="stat-label">Total Tire Molds</div>
                    <div class="stat-trend trend-up">
                        <span>+5.2% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-value" id="activeMolds">0</div>
                    <div class="stat-label">Active Molds</div>
                    <div class="stat-trend trend-up">
                        <span>+2.1% efficiency</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">⚠️</div>
                    </div>
                    <div class="stat-value" id="maintenanceMolds">0</div>
                    <div class="stat-label">Under Maintenance</div>
                    <div class="stat-label">(Commig Soon new update)</div>
                    <div class="stat-trend trend-up">
                        <span>-1.3% downtime</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📊</div>
                    </div>
                    <div class="stat-value" id="productionRate">0%</div>
                    <div class="stat-label">Production Rate</div>
                      <div class="stat-label">(Commig Soon new update)</div>
                    <div class="stat-trend trend-up">
                        <span>+3.7% today</span>
                    </div>
                </div>
            </div>

            <div class="action-section">
                <h2 class="section-title">Quick Actions</h2>
                <div class="action-grid">
                    <a href="dis_mold.php" class="action-card">
                        <div class="action-icon">🔍</div>
                        <h3 class="action-title">View Mold Details</h3>
                        <p class="action-description">Access detailed information about tire molds, including specifications, status, and maintenance history.</p>
                    </a>

                

                    <a href="maintenance.php" class="action-card">
                        <div class="action-icon">🔧</div>
                        <h3 class="action-title">Maintenance Schedule</h3>
                        <p class="action-description">View and manage maintenance schedules for all tire molds to ensure optimal performance.</p>
                    </a>

                   
                </div>
            </div>
        </main>
    </div>

    <div id="messageContainer"></div>

    <script>
        // Pass PHP variables to JavaScript
        const initialTotalMolds = <?php echo json_encode($totalMolds); ?>;
        const initialActiveMolds = <?php echo json_encode($activeMolds); ?>;
        const initialErrorMessage = <?php echo json_encode($errorMessage); ?>;

        // Initialize particles
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                container.appendChild(particle);
            }
        }

        // Update dashboard data
        function updateDashboardData() {
            let totalMolds = initialTotalMolds;
            let activeMolds = initialActiveMolds;
            if (initialErrorMessage) {
                showMessage(initialErrorMessage, 'error');
                totalMolds = 0;
                activeMolds = 0;
            }
            const maintenanceMolds = 0 ;
            const productionRate = 0;

            animateCounter('totalMolds', totalMolds);
            animateCounter('activeMolds', activeMolds);
            animateCounter('maintenanceMolds', maintenanceMolds);
            animateCounter('productionRate', productionRate, '%');
        }

        // Animate counter with easing
        function animateCounter(elementId, targetValue, suffix = '') {
            const element = document.getElementById(elementId);
            const currentValue = parseInt(element.textContent) || 0;
            const increment = (targetValue - currentValue) / 20;
            let current = currentValue;

            const timer = setInterval(() => {
                current += increment;
                if ((increment > 0 && current >= targetValue) || (increment < 0 && current <= targetValue)) {
                    current = targetValue;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current) + suffix;
            }, 50);
        }

        // Show message
        function showMessage(message, type = 'success') {
            const messageContainer = document.getElementById('messageContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            messageContainer.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.classList.add('show');
            }, 100);

            setTimeout(() => {
                messageDiv.classList.remove('show');
                setTimeout(() => {
                    messageContainer.removeChild(messageDiv);
                }, 300);
            }, 3000);
        }

        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                showMessage('Logging out...', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1500);
            }
        }

        // Initialize dashboard
        function initializeDashboard() {
            createParticles();
            updateDashboardData();
            
            // Simulate periodic updates
            setInterval(updateDashboardData, 30000);
            
            setTimeout(() => {
                if (!initialErrorMessage) {
                    showMessage('Dashboard loaded successfully!', 'success');
                }
            }, 1000);
        }

        window.addEventListener('load', initializeDashboard);

        window.addEventListener('online', () => {
            showMessage('Connection restored', 'success');
        });

        window.addEventListener('offline', () => {
            showMessage('Connection lost', 'error');
        });
    </script>
</body>
</html>