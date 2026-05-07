<?php
// sent_mail7.php - UPDATED VERSION WITH MODERN DESIGN FOR ALPHANUMERIC ORDER IDs

ob_start(); // Prevent any accidental output before redirect

// Include your existing config.php (uses mysqli $con)
require 'include/config.php'; // Adjust path if needed

// Convert mysqli $con → PDO $dbh (only if not already exists)
if (!isset($dbh) && isset($con) && $con instanceof mysqli) {
    try {
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $dbh = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// PHPMailer
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === CONFIGURATION ===
$smtp_host     = 'plan.atire.com';
$smtp_port     = 465;
$smtp_username = 'planningtool@plan.atire.com';
$smtp_password = 'Bishan@1919';
$from_email    = 'bishan.k@atire.com';
$from_name     = 'Atire Order Notifications';
$redirect_page = 'account-manager-dashboard.php'; // Page to redirect after success

// Helper function for status colors
function getStatusColor($status) {
    $colors = [
        'pending' => '#ffc107',
        'confirmed' => '#28a745',
        'pi_confirm' => '#17a2b8',
        'cus_confirmed' => '#007bff',
        'acm_confirm' => '#6610f2',
        'completed' => '#007bff',
        'delivered' => '#28a745',
        'cancelled' => '#dc3545'
    ];
    return $colors[strtolower($status)] ?? '#6c757d';
}

try {
    // Validate order ID - FIXED: Accept alphanumeric IDs like 1R1, 2R1, etc.
    if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
        throw new Exception("Missing Order ID");
    }
    
    $order_id = trim($_GET['id']);
    
    // Validate format: only alphanumeric characters allowed
    if (!preg_match('/^[a-zA-Z0-9]+$/', $order_id)) {
        throw new Exception("Invalid Order ID format. Only letters and numbers are allowed.");
    }

    // Fetch order + customer details - FIXED: Use string parameter
    $sql = "SELECT o.*, u.userEmail, u.fullName, u.acm_name, u.cus_id, u.company_rn
            FROM tire_orders o
            INNER JOIN users u ON o.customer_id = u.cus_id
            WHERE CAST(o.order_id AS CHAR) = :order_id";

    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_STR);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$order) {
        throw new Exception("Order #$order_id not found");
    }
    if (empty($order->userEmail)) {
        throw new Exception("Customer email is missing for this order");
    }

    $customer_name = htmlspecialchars($order->fullName ?: $order->acm_name ?: 'Valued Customer');
    $display_order_id = htmlspecialchars($order->order_id);
    $statusText = strtoupper(str_replace('_', ' ', $order->status));
    $statusColor = getStatusColor($order->status);

    // === SEND EMAIL ===
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_username;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $smtp_port;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($from_email, $from_name);
    $mail->addAddress($order->userEmail, $customer_name);
    $mail->addReplyTo($from_email, $from_name);

    // Add company logo
    $profilePicPath = 'atire.png';
    if (file_exists($profilePicPath)) {
        $mail->addEmbeddedImage($profilePicPath, 'company_logo');
    }

    $mail->isHTML(true);
    $mail->Subject = "Proforma Invoice Confirmation - Order #$display_order_id";

    // Determine banner class and text based on status
    $bannerClass = '';
    $bannerText = '✓ PROFORMA INVOICE CONFIRMED';
    
    if (strtolower($order->status) === 'pi_confirm') {
        $bannerClass = 'confirmed';
        $bannerText = '✓ PROFORMA INVOICE CONFIRMED';
    } elseif (in_array(strtolower($order->status), ['completed', 'delivered'])) {
        $bannerClass = 'completed';
        $bannerText = '✓ ORDER COMPLETED';
    } elseif (in_array(strtolower($order->status), ['confirmed', 'cus_confirmed', 'acm_confirm'])) {
        $bannerClass = 'confirmed';
        $bannerText = '✓ ORDER CONFIRMED';
    }

    $mail->Body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proforma Invoice Confirmation</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #000000 0%, #333333 100%);
            color: #ffffff;
            padding: 5px;
            text-align: center;
            position: relative;
        }
        .header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #F28018 0%, #F28018 100%);
        }
        .profile-section {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            gap: 15px;
        }
        .profile-section img {
            width: 250px;
            height: 150px;
            border-radius: 8px;
            object-fit: contain;
            padding: 5px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
        }
        .company-info p {
            margin: 5px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
            color: #F28018;
        }
        .urgent-banner {
            background-color: #17a2b8;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            animation: pulse 2s infinite;
        }
        .urgent-banner.confirmed {
            background-color: #28a745;
        }
        .urgent-banner.completed {
            background-color: #007bff;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        .content {
            padding: 30px;
        }
        .order-id {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
            border: 2px solid #F28018;
        }
        .order-id h2 {
            margin: 0;
            color: #000000;
            font-size: 24px;
        }
        .order-id p {
            color: #808080;
            margin: 10px 0 0 0;
        }
        .section {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #F28018;
            border: 1px solid #e9ecef;
        }
        .section h3 {
            color: #000000;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        .section-icon {
            margin-right: 10px;
            font-size: 24px;
            color: #F28018;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        .info-item {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            transition: transform 0.2s ease;
        }
        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(242, 128, 24, 0.2);
            border-color: #F28018;
        }
        .info-label {
            font-weight: 600;
            color: #F28018;
            font-size: 14px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            color: #333333;
            font-size: 16px;
            word-break: break-word;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: ' . $statusColor . ';
            color: #ffffff;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #F28018, transparent);
            margin: 25px 0;
        }
        .highlight-text {
            color: #F28018;
            font-weight: 600;
        }
        .secondary-text {
            color: #808080;
        }
        .highlight-box {
            background: #e3f2fd;
            border-left: 5px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .next-steps {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid #ffc107;
        }
        .next-steps ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
            color: #333;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #F28018 0%, #e6730f 100%);
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            margin: 10px;
            transition: transform 0.2s ease;
            border: 2px solid transparent;
        }
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(242, 128, 24, 0.4);
            background: #000000;
            border: 2px solid #F28018;
        }
        .action-button:visited,
        .action-button:link {
            color: #ffffff;
            text-decoration: none;
        }
        .footer {
            background-color: #000000;
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer .highlight {
            color: #F28018;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="profile-section">';
    
    // Add profile picture if exists
    if (file_exists($profilePicPath)) {
        $mail->Body .= '<img src="cid:company_logo" alt="Atire Logo" />';
    }
    
    $mail->Body .= '
                <div class="company-info">
                    <p>Customer Service</p>
                </div>
            </div>
        </div>
        
        <div class="urgent-banner ' . $bannerClass . '">
            ' . $bannerText . '
        </div>
        
        <div class="content">
            <div class="order-id">
                <h2>📄 Proforma Invoice - Order #' . $display_order_id . '</h2>
                <p class="secondary-text">Confirmed: ' . date('F j, Y \a\t g:i A', strtotime($order->order_date)) . '</p>
            </div>
            
            <p><strong>Dear ' . $customer_name . ',</strong></p>
            <div class="highlight-box">
                <strong>✓ Your Proforma Invoice has been confirmed!</strong><br>
                We have received your order confirmation and are processing it now.
            </div>
            
            <div class="section">
                <h3><span class="section-icon">📦</span>Order Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Order ID</div>
                        <div class="info-value highlight-text">#' . $display_order_id . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Customer ID</div>
                        <div class="info-value">' . htmlspecialchars($order->cus_id) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Order Date</div>
                        <div class="info-value">' . date('F j, Y \a\t g:i A', strtotime($order->order_date)) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge">' . $statusText . '</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Items</div>
                        <div class="info-value">' . htmlspecialchars($order->total_items) . '</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total Quantity</div>
                        <div class="info-value highlight-text">' . htmlspecialchars($order->total_quantity) . ' units</div>
                    </div>';
    
    if (!empty($order->company_rn)) {
        $mail->Body .= '
                    <div class="info-item">
                        <div class="info-label">Company</div>
                        <div class="info-value">' . htmlspecialchars($order->company_rn) . '</div>
                    </div>';
    }
    
    $mail->Body .= '
                </div>';
    
    if (!empty($order->order_notes)) {
        $mail->Body .= '
                <div class="divider"></div>
                <div class="info-item">
                    <div class="info-label">Order Notes</div>
                    <div class="info-value">' . nl2br(htmlspecialchars($order->order_notes)) . '</div>
                </div>';
    }
    
    $mail->Body .= '
            </div>
            
            <div class="next-steps">
                <strong style="color:#000;font-size:18px;">📋 Next Steps:</strong>
                <ul>
                    <li><strong>Review your proforma invoice</strong> - Check all details carefully</li>
                    <li><strong>Proceed with payment</strong> - As per the agreed terms</li>
                    <li><strong>Order processing</strong> - We\'ll begin upon payment confirmation</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="https://plan.atire.com/cms/admin/gate_order.php?id=' . urlencode($order->order_id) . '" class="action-button">📄 View Invoice</a>
                <a href="https://plan.atire.com/dashboard.php" class="action-button">🚀 Go to Dashboard</a>
            </div>
            
            <p style="text-align:center;color:#808080;margin-top:25px;">We\'ll notify you when your order status changes. Thank you for choosing A-Tire!</p>
        </div>
        
        <div class="footer">
            <p><strong>ATIRE Customer Service</strong></p>
            <p>Contact: ' . $from_email . '</p>
            <p style="font-size: 12px; opacity: 0.8; margin-top: 15px;">
                This is an automated notification. Please do not reply to this email.
            </p>
            <p><strong class="highlight">A-Tire Planning Tool</strong> &copy; ' . date('Y') . ' • All rights reserved</p>
        </div>
    </div>
</body>
</html>';

    $mail->AltBody = "PROFORMA INVOICE CONFIRMATION\n\n";
    $mail->AltBody .= "Dear $customer_name,\n\n";
    $mail->AltBody .= "Your Proforma Invoice has been confirmed! We are processing your order now.\n\n";
    $mail->AltBody .= "ORDER DETAILS:\n";
    $mail->AltBody .= "Order ID: #$display_order_id\n";
    $mail->AltBody .= "Customer ID: {$order->cus_id}\n";
    $mail->AltBody .= "Order Date: " . date('F j, Y \a\t g:i A', strtotime($order->order_date)) . "\n";
    $mail->AltBody .= "Status: $statusText\n";
    $mail->AltBody .= "Total Items: {$order->total_items}\n";
    $mail->AltBody .= "Total Quantity: {$order->total_quantity} units\n\n";
    
    if (!empty($order->company_rn)) {
        $mail->AltBody .= "Company: {$order->company_rn}\n\n";
    }
    
    if (!empty($order->order_notes)) {
        $mail->AltBody .= "Notes: {$order->order_notes}\n\n";
    }
    
    $mail->AltBody .= "NEXT STEPS:\n";
    $mail->AltBody .= "1. Review your proforma invoice\n";
    $mail->AltBody .= "2. Proceed with payment as per the terms\n";
    $mail->AltBody .= "3. We'll process your order upon payment confirmation\n\n";
    $mail->AltBody .= "View your invoice at: https://plan.atire.com/cms/admin/gate_order.php?id={$order->order_id}\n\n";
    $mail->AltBody .= "Thank you for choosing A-Tire!\n\n";
    $mail->AltBody .= "A-Tire Planning Tool";

    $mail->send();

    // SUCCESS → REDIRECT TO account-manager-dashboard.php WITH SAME ID
    $redirect_url = $redirect_page . '?id=' . urlencode($order_id);
    header("Location: $redirect_url");
    exit;

} catch (Exception $e) {
    // ERROR → SHOW NICE ERROR PAGE (no redirect)
    $safe_order_id = isset($order_id) ? htmlspecialchars($order_id) : '';
    
    echo '<!DOCTYPE html><html><head><title>Email Error</title><meta charset="utf-8"><style>
        body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
        .box{background:#fff;padding:40px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.2);text-align:center;max-width:500px}
        h1{color:#dc3545;margin-bottom:20px;font-size:28px}.icon{font-size:80px;margin-bottom:20px}
        .error-msg{background:#f8f9fa;padding:15px;border-radius:8px;margin:20px 0;border-left:4px solid #dc3545;text-align:left}
        .btn{display:inline-block;padding:12px 30px;background:linear-gradient(135deg,#F28018 0%,#e6730f 100%);color:#fff;text-decoration:none;border-radius:30px;font-weight:600;margin:10px;transition:all 0.3s}
        .btn:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(242,128,24,0.4);background:#000;border:2px solid #F28018}
        .order-info{background:#fff3cd;padding:12px;border-radius:6px;margin:15px 0;border-left:4px solid #ffc107}
    </style></head><body>
        <div class="box">
            <div class="icon">⚠️</div>
            <h1>Email Could Not Be Sent</h1>
            <div class="error-msg">
                <strong style="color:#F28018">Error:</strong> ' . htmlspecialchars($e->getMessage()) . '
            </div>
            <p>We encountered an issue while trying to send the confirmation email.</p>';
    
    if (!empty($safe_order_id)) {
        echo '<div class="order-info">
                <strong>Order ID:</strong> ' . $safe_order_id . '
            </div>
            <a href="confirm_pi.php?id=' . urlencode($safe_order_id) . '" class="btn">Continue to Order (' . $safe_order_id . ')</a>';
    } else {
        echo '<a href="dashboard.php" class="btn">← Back to Dashboard</a>';
    }
    
    echo '
            <p style="margin-top:20px;"><small style="color:#808080">' . date('F j, Y \a\t g:i:s A') . '</small></p>
        </div>
    </body></html>';
}
?>