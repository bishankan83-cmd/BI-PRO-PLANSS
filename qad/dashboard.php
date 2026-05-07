<?php
session_start();

// Sample data - in real implementation, this would come from database
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Quality Inspector';
$total_inspections = 1247;
$pending_reviews = 23;
$defects_today = 5;
$quality_score = 98.5;

// Get current date and time
$current_date = date('Y-m-d');
$current_time = date('H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Control Dashboard - Modern UI</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #343a40;
            --accent-color: #667eea;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #F28018 0%, #ff6b35 100%);
            --gradient-3: linear-gradient(135deg, #667eea 0%, #F28018 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-bg);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-1);
            opacity: 0.05;
            z-index: -2;
            animation: gradientShift 20s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background: var(--gradient-1); }
            33% { background: var(--gradient-2); }
            66% { background: var(--gradient-3); }
        }

        /* Floating particles */
        .particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 0.3; }
        }

        /* Top Navigation */
        .navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

         .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient-3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-details p {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Hero Section */
        .hero-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: var(--gradient-2);
            opacity: 0.1;
            border-radius: 50%;
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: var(--gradient-2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Action Buttons */
        .action-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .action-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .action-card.primary {
            background: var(--gradient-2);
            border-color: transparent;
        }

        .action-card.secondary {
            background: var(--gradient-1);
            border-color: transparent;
        }

        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(242, 128, 24, 0.3);
        }

        .action-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .action-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .action-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        /* Modules Grid */
        .modules-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .modules-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .modules-title h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .module-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .module-card:hover {
            transform: translateX(8px);
            border-color: var(--primary-color);
            background: rgba(242, 128, 24, 0.1);
        }

        .module-card:hover::before {
            transform: scaleY(1);
        }

        .module-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .module-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .module-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .module-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .module-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 1rem;
            background: var(--success-color);
            color: white;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            margin-top: 2rem;
        }

        .footer-message {
            font-size: 1.1rem;
            font-weight: 600;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .action-section {
                grid-template-columns: 1fr;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            .navbar {
                padding: 1rem;
            }
        }

        /* Loading Animation */
        .loading {
            opacity: 0;
            animation: fadeIn 1s ease forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        /* Glassmorphism hover effects */
        .glass-hover:hover {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(25px);
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particle" style="top: 10%; left: 10%; width: 4px; height: 4px; background: var(--primary-color); animation-delay: 0s;"></div>
    <div class="particle" style="top: 20%; left: 80%; width: 6px; height: 6px; background: var(--accent-color); animation-delay: 2s;"></div>
    <div class="particle" style="top: 60%; left: 20%; width: 3px; height: 3px; background: var(--info-color); animation-delay: 4s;"></div>
    <div class="particle" style="top: 80%; left: 70%; width: 5px; height: 5px; background: var(--success-color); animation-delay: 1s;"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-content">
           <div class="logo">
      
         <div class="d-flex align-items-center">
                    <img src="atire.png" alt="ATIRE Logo" class="header-logo me-3">
                    
                </div>
        <span> Quality Dashboard</span>
    </div>
            <div class="user-info">
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($user_name); ?></h4>
                    <p><?php echo $current_date . ' | ' . $current_time; ?></p>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Hero Section -->
        <section class="hero-section loading">
            <div class="hero-content">
                <h1 class="hero-title">ATIRE Quality Control Center</h1>
                <p class="hero-subtitle">Advanced Tire Manufacturing Quality Management System</p>
            </div>
        </section>

        <!-- Statistics -->
        <div class="stats-grid loading">
            <div class="stat-card glass-hover">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_inspections); ?></div>
                <div class="stat-label">Total Inspections</div>
            </div>
            <div class="stat-card glass-hover">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $pending_reviews; ?></div>
                <div class="stat-label">Pending Reviews</div>
            </div>
            <div class="stat-card glass-hover">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-number"><?php echo $defects_today; ?></div>
                <div class="stat-label">Defects Today</div>
            </div>
            <div class="stat-card glass-hover">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number"><?php echo $quality_score; ?>%</div>
                <div class="stat-label">Quality Score</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-section loading">
            <div class="action-card primary" onclick="toggleModules('entry')">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3 class="action-title">Data Entry</h3>
                <p class="action-description">Enter new quality control data, defect reports, and inspection results</p>
                <button class="action-btn">Start Entry Process</button>
            </div>
            
            <div class="action-card secondary" onclick="toggleModules('view')">
                <div class="action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="action-title">Analytics & Reports</h3>
                <p class="action-description">View comprehensive reports, analytics, and quality metrics dashboard</p>
                <button class="action-btn">View Reports</button>
            </div>
        </div>

        <!-- Modules Section -->
        <div class="modules-section loading" id="modulesSection" style="display: none;">
            <div class="modules-title">
                <h2 id="modulesTitle">Available Quality Modules</h2>
                <p id="modulesSubtitle">Select a module to access specific quality control functions</p>
            </div>
            
            <div class="modules-grid">
                <!-- Dynamic content will be loaded here based on mode -->
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer loading">
            <div class="footer-message">
                ATIRE - Excellence in Quality • Innovation • Precision Manufacturing
            </div>
        </footer>
    </div>

    <script>
        let currentMode = 'none';

        function toggleModules(mode) {
            const modulesSection = document.getElementById('modulesSection');
            const modulesTitle = document.getElementById('modulesTitle');
            const modulesSubtitle = document.getElementById('modulesSubtitle');
            const modulesGrid = document.querySelector('.modules-grid');

            if (currentMode === mode) {
                // Hide modules
                modulesSection.style.display = 'none';
                currentMode = 'none';
            } else {
                // Show modules with appropriate content
                modulesSection.style.display = 'block';
                currentMode = mode;
                
                if (mode === 'entry') {
                    modulesTitle.textContent = 'Data Entry Modules';
                    modulesSubtitle.textContent = 'Select a module to start entering quality control data';
                    showDataEntryModules();
                } else if (mode === 'view') {
                    modulesTitle.textContent = 'Data Viewing & Analytics';
                    modulesSubtitle.textContent = 'Choose a module to view reports and analyze quality data';
                    showViewingModules();
                }

                // Smooth scroll to modules
                modulesSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function showDataEntryModules() {
            const modulesGrid = document.querySelector('.modules-grid');
            modulesGrid.innerHTML = `

                 <div class="module-card" onclick="navigateToModule('green_tire.php', 'Green Tire - Entry')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="module-title">Add Green Tire Data</div>
                    </div>
                    <div class="module-description">
                        Enter quality control and inspection data for uncured (green) tire inspection and validation
                    </div>
                    <span class="module-status" style="background: var(--info-color);">Data Entry</span>
                </div>


   <div class="module-card" onclick="navigateToModule('curing_section.php', 'Curing Section - Entry')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="module-title">Add Curing Section Data</div>
                    </div>
                    <div class="module-description">
                        Enter tire curing process quality parameters with real-time data tracking and monitoring
                    </div>
                    <span class="module-status" style="background: var(--info-color);">Data Entry</span>
                </div>


                <div class="module-card" onclick="navigateToModule('daily_defect.php', 'Daily Defect BLACK - Entry')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="module-title">Daily Defect</div>
                    </div>
                    <div class="module-description">
                        Enter and record new daily defects in black and Nm tire production with comprehensive defect classification
                    </div>
                    <span class="module-status" style="background: var(--info-color);">Data Entry</span>
                </div>

           

                <div class="module-card" onclick="navigateToModule('f_us_hard.php', 'US & Hardness - Entry')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-plus-square"></i>
                        </div>
                        <div class="module-title">Add US & Hardness Data</div>
                    </div>
                    <div class="module-description">
                        Enter ultrasonic testing and hardness measurement data for resilient POB tires (BLACK / NY variants)
                    </div>
                    <span class="module-status" style="background: var(--info-color);">Data Entry</span>
                </div>


                    
                <div class="module-card" onclick="navigateToModule('pro_serial_final.php', 'Final Inspection - Entry')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="module-title">Add Final Inspection POB</div>
                    </div>
                    <div class="module-description">
                        Enter comprehensive final quality inspection data for POB tire products with detailed checkpoints
                    </div>
                    <span class="module-status" style="background: var(--info-color);">Data Entry</span>
                </div>

           
               

             
            `;
        }

        function showViewingModules() {
            const modulesGrid = document.querySelector('.modules-grid');
            modulesGrid.innerHTML = `
                <div class="module-card" onclick="navigateToModule('view_daily_defect.php', 'Daily Defect BLACK - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="module-title">Daily Defect</div>
                    </div>
                    <div class="module-description">
                        View reports and analytics for daily defects in black tire production with trend analysis
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>

                <div class="module-card" onclick="navigateToModule('view_daily_defect_nm.php', 'POB Tyres Grinding - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="module-title">View POB Tyres Grinding</div>
                    </div>
                    <div class="module-description">
                        Analyze POB tire grinding operations data with comprehensive performance metrics and trends
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>

                <div class="module-card" onclick="navigateToModule('view_pro_serial_final.php', 'Final Inspection - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="module-title">View Final Inspection POB</div>
                    </div>
                    <div class="module-description">
                        Review final inspection reports for POB tire products with detailed quality analysis
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>

                <div class="module-card" onclick="navigateToModule('view_f_us_hard.php', 'US & Hardness - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                        <div class="module-title">View US & Hardness Reports</div>
                    </div>
                    <div class="module-description">
                        Analyze ultrasonic testing and hardness measurement reports with statistical analysis
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>

                <div class="module-card" onclick="navigateToModule('view_green_tire.php', 'Green Tire - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-chart-gantt"></i>
                        </div>
                        <div class="module-title">View Green Tire Reports</div>
                    </div>
                    <div class="module-description">
                        Review green tire quality control reports with inspection history and quality trends
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>

                <div class="module-card" onclick="navigateToModule('view_daily_defect_nm_nm.php', 'Daily Defect NM - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-analytics"></i>
                        </div>
                        <div class="module-title">View Daily Defect NM</div>
                    </div>
                    <div class="module-description">
                        Analyze defect patterns in non-metallic components with advanced categorization reports
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>

                <div class="module-card" onclick="navigateToModule('view_curing_section.php', 'Curing Section - View')">
                    <div class="module-header">
                        <div class="module-icon">
                            <i class="fas fa-chart-scatter"></i>
                        </div>
                        <div class="module-title">View Curing Section Reports</div>
                    </div>
                    <div class="module-description">
                        Monitor curing process analytics with real-time dashboards and performance metrics
                    </div>
                    <span class="module-status" style="background: var(--success-color);">View Reports</span>
                </div>
            `;
        }

        function navigateToModule(url, moduleName) {
            // Show loading state
            const clickedCard = event.currentTarget;
            clickedCard.style.transform = 'scale(0.95)';
            clickedCard.style.opacity = '0.7';

            setTimeout(() => {
                // Navigate to the actual PHP page
                window.location.href = url;
            }, 200);
        }

        // Add loading animation
        document.addEventListener('DOMContentLoaded', function() {
            const loadingElements = document.querySelectorAll('.loading');
            loadingElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '1';
                }, index * 200);
            });

            // Update time every second
            setInterval(updateTime, 1000);
        });

        function updateTime() {
            const now = new Date();
            const timeString = now.toTimeString().split(' ')[0];
            const userDetails = document.querySelector('.user-details p');
            if (userDetails) {
                userDetails.textContent = `<?php echo $current_date; ?> | ${timeString}`;
            }
        }

        // Add smooth hover effects
        document.addEventListener('mouseover', function(e) {
            if (e.target.classList.contains('module-card')) {
                e.target.style.transform = 'translateX(8px) translateY(-2px)';
            }
        });

        document.addEventListener('mouseout', function(e) {
            if (e.target.classList.contains('module-card')) {
                e.target.style.transform = '';
            }
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && currentMode !== 'none') {
                toggleModules(currentMode);
            }
        });
    </script>
</body>
</html>