<?php
// Start output buffering to prevent header issues
ob_start();

// Database configuration
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

// Initialize variables
$connection = null;
$error_message = '';
$success_message = '';

// Create database connection
try {
    $connection = mysqli_connect($host, $username, $password, $database);
    
    if (!$connection) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to UTF-8
    mysqli_set_charset($connection, "utf8");
    
} catch (Exception $e) {
    $error_message = "Database connection error: " . $e->getMessage();
}

// Function to execute MySQL queries safely
function executeQuery($query, $connection) {
    if (!$connection) {
        return false;
    }
    
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        error_log("Query execution failed: " . mysqli_error($connection));
        return false;
    }
    
    return $result;
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Handle form submissions BEFORE any HTML output
if ($_SERVER["REQUEST_METHOD"] == "POST" && $connection) {
    try {
        // Clean any potential output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Log the button press (optional)
        $timestamp = date('Y-m-d H:i:s');
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        if (isset($_POST['button1'])) {
            // Saleable Stock
            header("Location: stock.php");
            exit();
            
        } elseif (isset($_POST['button2'])) {
            // Hold Stock
            header("Location: stockb.php");
            exit();
            
        } elseif (isset($_POST['button3'])) {
            // Hold Stock Serial
            header("Location: stock_change.php");
            exit();
            
        } elseif (isset($_POST['button4'])) {
            // Tire Transfer
            header("Location: dis_hold_to_a.php");
            exit();
            
        } elseif (isset($_POST['button5'])) {
            // B Grade Stock
            header("Location: stockrb.php");
            exit();
            
        } elseif (isset($_POST['button6'])) {
            // Over Age Tires
            header("Location: show_over_age_stock.php");
            exit();
            
        } elseif (isset($_POST['button7'])) {
            // Non-Moving Tires
            header("Location: show_non_moveing.php");
            exit();
            
        } elseif (isset($_POST['button8'])) {
            // Saleable Stock Serial
            header("Location: show_serial_stock.php");
            exit();
            
        } elseif (isset($_POST['button16'])) {
            // All Stock Serial
            header("Location: stock_transfer.php");
            exit();
        }
        
    } catch (Exception $e) {
        $error_message = "Error processing request: " . $e->getMessage();
    }
}

// Get some basic stats (optional - you can customize these queries)
$total_reports = 10;
$categories = 5;
$system_status = "Online";

if ($connection) {
    // You can add actual database queries here to get real statistics
    // Example:
    // $result = executeQuery("SELECT COUNT(*) as total FROM your_inventory_table", $connection);
    // if ($result) {
    //     $row = mysqli_fetch_assoc($result);
    //     $total_items = $row['total'];
    // }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
           
            min-height: 100vh;
            padding: 20px;
            overflow-x: hidden;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #F28018 0%, #ff6b35 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='5' cy='5' r='5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.1;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 40px;
        }
        
        .category-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(242, 128, 24, 0.1);
        }
        
        .category-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(242, 128, 24, 0.15);
        }
        
        .category-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .category-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #F28018, #ff6b35);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .button-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .action-button {
            background: linear-gradient(135deg, #F28018 0%, #ff6b35 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: none;
            letter-spacing: 0.5px;
            width: 100%;
        }
        
        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .action-button:hover::before {
            left: 100%;
        }
        
        .action-button:hover {
            background: linear-gradient(135deg, #333 0%, #555 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(242, 128, 24, 0.3);
        }
        
        .action-button:active {
            transform: translateY(0);
        }
        
        .stats-bar {
            background: rgba(242, 128, 24, 0.05);
            padding: 20px 40px;
            display: flex;
            justify-content: space-around;
            border-top: 1px solid rgba(242, 128, 24, 0.1);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #F28018;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 4px;
        }
        
        .error-message {
            background: #ff4757;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 40px;
            text-align: center;
        }
        
        .success-message {
            background: #2ed573;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 40px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                margin: 10px;
                border-radius: 16px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 20px;
            }
            
            .stats-bar {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }
        }
        
        /* Loading state for buttons */
        .action-button.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .action-button.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            display: inline-block;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>FULL STOCK REPORT</h1>
            <p>Comprehensive tire stock control and reporting dashboard</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message">
                <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-grid">
            <!-- Full Inventory Section -->
            <div class="category-section">
                <div class="category-title">
                    <div class="category-icon">📦</div>
                    Full Inventory
                </div>
                <div class="button-list">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button16" class="action-button">
                            📋 All Stock Serial Numbers
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Saleable Inventory Section -->
            <div class="category-section">
                <div class="category-title">
                    <div class="category-icon">✅</div>
                    Saleable Inventory
                </div>
                <div class="button-list">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button1" class="action-button">
                            🟢 View Saleable Stock
                        </button>
                    </form>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button8" class="action-button">
                            🔢 Saleable Stock with Serial
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- B Grade & Hold Section -->
            <div class="category-section">
                <div class="category-title">
                    <div class="category-icon">⚠️</div>
                    B Grade & Hold Stock
                </div>
                <div class="button-list">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button5" class="action-button">
                            🟡 B Grade Stock Report
                        </button>
                    </form>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button2" class="action-button">
                            🔴 Hold Stock Overview
                        </button>
                    </form>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button3" class="action-button">
                            🔢 Hold Stock Serial Numbers
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Inventory Management Section -->
            <div class="category-section">
                <div class="category-title">
                    <div class="category-icon">🔄</div>
                    Inventory Management
                </div>
                <div class="button-list">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button4" class="action-button">
                            🚚 Tire Transfer System
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Special Categories Section -->
            <div class="category-section">
                <div class="category-title">
                    <div class="category-icon">🎯</div>
                    Special Categories
                </div>
                <div class="button-list">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button6" class="action-button">
                            📅 Over Age Tire Analysis
                        </button>
                    </form>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <button type="submit" name="button7" class="action-button">
                            📊 Non-Moving Stock Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number"><?php echo $categories; ?></span>
                <div class="stat-label">Report Categories</div>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $total_reports; ?></span>
                <div class="stat-label">Available Reports</div>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $connection ? 'Online' : 'Offline'; ?></span>
                <div class="stat-label">System Status</div>
            </div>
            
        </div>
    </div>

    <script>
        // Add loading states to buttons
        document.querySelectorAll('.action-button').forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent double submission
                if (this.classList.contains('loading')) {
                    e.preventDefault();
                    return false;
                }
                
                this.classList.add('loading');
                this.style.pointerEvents = 'none';
                
                // Add loading text
                const originalText = this.textContent;
                this.setAttribute('data-original-text', originalText);
                this.innerHTML = originalText + ' <span style="margin-left: 8px;">⏳</span>';
                
                // Reset after 5 seconds if redirect doesn't happen
                setTimeout(() => {
                    this.classList.remove('loading');
                    this.style.pointerEvents = 'auto';
                    this.innerHTML = originalText;
                }, 5000);
            });
        });
        
        // Add smooth reveal animation on page load
        window.addEventListener('load', () => {
            const sections = document.querySelectorAll('.category-section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });
        
        // Add keyboard navigation support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && e.target.classList.contains('action-button')) {
                e.target.click();
            }
        });
        
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.error-message, .success-message');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);
    </script>

<?php
// Clean up database connection
if ($connection) {
    mysqli_close($connection);
}

// Flush output buffer
ob_end_flush();
?>
</body>
</html>