<?php
// === DATABASE CONNECTION ===
$servername = "localhost";
$username = "planatir_task_managemen";
$password = "Bishan@1919";
$dbname = "planatir_cms";

try {
    $dbh = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// PHPMailer
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === HELPER FUNCTION - Must be defined BEFORE use ===
function getStatusColor($status) {
    return match(strtolower($status)) {
      
        'confirmed','cus_confirmed','acm_confirm','cus_confirm' => '#28a745',
        'cancelled','canceled' => '#dc3545',
        'processing' => '#2196F3',
        'shipped' => '#9C27B0',
        'delivered' => '#4CAF50',
        default => '#6c757d',
    };
}

// === CONFIGURATION ===
$smtp_host = 'plan.atire.com';
$smtp_port = 465;
$smtp_username = 'planningtool@plan.atire.com';
$smtp_password = 'Bishan@1919';
$from_email = 'planningtool@plan.atire.com';
$from_name = 'Atire Customer Service';
$redirect_page = 'account-manager-dashboard.php';

$order_id = null;

try {
    // Validate order ID
    if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
        throw new Exception("Invalid or missing Order ID");
    }

    $order_id = trim($_GET['id']);
    $is_numeric = ctype_digit($order_id);

    if ($is_numeric) {
        $order_id = (int)$order_id;
        $sql = "SELECT o.*, u.userEmail, u.fullName, u.acm_name, u.cus_id, u.company_rn 
                FROM tire_orders o 
                INNER JOIN users u ON o.customer_id = u.cus_id 
                WHERE o.order_id = :order_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    } else {
        $order_id = preg_replace('/[^\w\-,]/', '', $order_id);
        $sql = "SELECT o.*, u.userEmail, u.fullName, u.acm_name, u.cus_id, u.company_rn 
                FROM tire_orders o 
                INNER JOIN users u ON o.customer_id = u.cus_id 
                WHERE o.order_id = :order_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_STR);
    }

    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$order) {
        throw new Exception("Order '$order_id' not found in the database");
    }

    if (empty($order->userEmail)) {
        throw new Exception("Customer email is missing for this order");
    }

    $customer_name = htmlspecialchars($order->fullName ?: $order->acm_name ?: 'Valued Customer');
    $display_order_id = htmlspecialchars($order->order_id);
    $status_color = getStatusColor($order->status);
    $formatted_date = date('F d, Y \a\t h:i A', strtotime($order->order_date));
    $formatted_status = ucfirst(str_replace('_', ' ', $order->status));
    $company_row = !empty($order->company_rn) ? '<div class="detail-row">
                        <span class="detail-label">Company</span>
                        <span class="detail-value">' . htmlspecialchars($order->company_rn) . '</span>
                    </div>' : '';
    $notes_section = !empty($order->order_notes) ? '<div class="info-box">
                    <div class="info-box-title">📝 Order Notes</div>
                    <p>' . nl2br(htmlspecialchars($order->order_notes)) . '</p>
                </div>' : '';

    // === SEND EMAIL ===
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtp_port;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($from_email, $from_name);
    $mail->addAddress($order->userEmail, $customer_name);
    $mail->addReplyTo($from_email, $from_name);
    $mail->isHTML(true);
    $mail->Subject = "Order Confirmation - Order #$display_order_id";

    $mail->Body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f7fa;
            color: #2c3e50;
            line-height: 1.6;
        }
        .email-wrapper {
            width: 100%;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 50px 30px;
            text-align: center;
            position: relative;
        }
        .header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #F28018 0%, #ffa940 50%, #F28018 100%);
        }
        .logo-text {
            color: #ffffff;
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .logo-tagline {
            color: #F28018;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        /* Success Banner */
        .success-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 25px;
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .success-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .success-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
        }
        
        /* Content */
        .content {
            padding: 45px 40px;
        }
        .greeting {
            font-size: 22px;
            color: #1a1a1a;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .intro-text {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 35px;
            line-height: 1.7;
        }
        
        /* Order Card */
        .order-card {
            background: linear-gradient(135deg, #fff8f0 0%, #ffe8d6 100%);
            border: 3px solid #F28018;
            border-radius: 16px;
            padding: 35px;
            margin-bottom: 35px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(242, 128, 24, 0.15);
        }
        .order-label {
            color: #F28018;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .order-number {
            font-size: 52px;
            color: #1a1a1a;
            font-weight: 900;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        .order-date {
            color: #6c757d;
            font-size: 15px;
            font-weight: 500;
        }
        
        /* Details Grid */
        .details-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .section-title {
            color: #1a1a1a;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 3px solid #F28018;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 16px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            color: #1a1a1a;
            font-size: 16px;
            font-weight: 600;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 5px solid #2196F3;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .info-box-title {
            color: #2196F3;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .info-box p {
            color: #1a1a1a;
            font-size: 15px;
            line-height: 1.8;
            margin: 8px 0;
        }
        
        /* CTA Button */
        .cta-section {
            text-align: center;
            padding: 35px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .cta-text {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #F28018 0%, #ff9933 100%);
            color: #ffffff;
            padding: 18px 50px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 8px 25px rgba(242, 128, 24, 0.4);
        }
        
        /* Support Box */
        .support-box {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left: 5px solid #ff9800;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
        }
        .support-title {
            color: #ff9800;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .support-text {
            color: #663c00;
            font-size: 15px;
            line-height: 1.7;
        }
        .support-email {
            color: #F28018;
            font-weight: 700;
            text-decoration: none;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 40px 30px;
            text-align: center;
            color: #b0b0b0;
        }
        .footer-brand {
            color: #F28018;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        .footer-text {
            font-size: 14px;
            margin: 8px 0;
        }
        .footer-link {
            color: #F28018;
            text-decoration: none;
            font-weight: 600;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #F28018, transparent);
            margin: 20px auto;
            max-width: 300px;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            .content {
                padding: 30px 25px;
            }
            .header {
                padding: 40px 20px;
            }
            .logo-text {
                font-size: 28px;
            }
            .order-number {
                font-size: 40px;
            }
            .detail-row {
                flex-direction: column;
                gap: 8px;
            }
            .detail-value {
                text-align: left;
            }
            .cta-button {
                padding: 16px 40px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
            
                <div class="logo-tagline">Atire Customer Service</div>
            </div>
            
            <!-- Success Banner -->
            <div class="success-banner">
                <div class="success-icon">✓</div>
                <div class="success-title">Order Confirmed!</div>
                <div class="success-subtitle">We\'ve received your order and started processing it</div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="greeting">Hello ' . $customer_name . ',</div>
                <div class="intro-text">
                    Thank you for your order! We\'re excited to serve you. Your order has been confirmed and is now being processed by our team.
                </div>
                
                <!-- Order Card -->
                <div class="order-card">
                    <div class="order-label">Order Number</div>
                    <div class="order-number">#' . $display_order_id . '</div>
                    <div class="order-date">Placed on ' . $formatted_date . '</div>
                </div>
                
                <!-- Details Section -->
                <div class="details-section">
                    <div class="section-title">📦 Order Summary</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">
                            <span class="status-badge" style="background-color: ' . $status_color . ';">
                                ' . $formatted_status . '
                            </span>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Items</span>
                        <span class="detail-value">' . $order->total_items . '</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Quantity</span>
                        <span class="detail-value">' . $order->total_quantity . ' units</span>
                    </div>
                    
                    ' . $company_row . '
                </div>
                
                ' . $notes_section . '
                
                <!-- CTA Section -->
                <div class="cta-section">
                    <div class="cta-text">Track your order status and view details</div>
                    <a href="#" class="cta-button">View Order Details</a>
                </div>
                
                <!-- Support Box -->
                <div class="support-box">
                    <div class="support-title">Need Help?</div>
                    <div class="support-text">
                        Our support team is here to assist you.<br>
                        Contact us at <a href="mailto:' . $from_email . '" class="support-email">' . $from_email . '</a>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
               
                <div class="footer-text">Atire Customer Service</div>
                <div class="divider"></div>
                <div class="footer-text">© ' . date('Y') . ' Atire. All rights reserved.</div>
                <div class="footer-text">
                    <a href="mailto:' . $from_email . '" class="footer-link">' . $from_email . '</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

    $mail->AltBody = "Order #$display_order_id Confirmed\n\nDear $customer_name,\n\nThank you for your order! We've received it and are processing it now.\n\nOrder Details:\n- Order ID: #$display_order_id\n- Date: $formatted_date\n- Status: $formatted_status\n- Total Items: {$order->total_items}\n- Total Quantity: {$order->total_quantity} units\n\nAtire Customer Service\n{$from_email}";

    $mail->send();

    // SUCCESS → REDIRECT
    $redirect_url = $redirect_page . '?id=' . urlencode($order_id);
    header("Location: $redirect_url");
    exit;

} catch (Exception $e) {
    // ERROR PAGE
    $safe_order_id = isset($order_id) && !empty($order_id) ? htmlspecialchars($order_id) : '';
    $back_link = !empty($safe_order_id) 
        ? '<a href="' . $redirect_page . '?id=' . urlencode($safe_order_id) . '" style="color: #F28018; text-decoration: none; font-weight: 600;">← Back to Order</a>'
        : '<a href="' . $redirect_page . '" style="color: #F28018; text-decoration: none; font-weight: 600;">← Back to Orders</a>';
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Email Could Not Be Sent</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            max-width: 600px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        .error-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            padding: 40px;
            text-align: center;
            color: #ffffff;
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .error-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
        .error-content {
            padding: 40px;
        }
        .error-message {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .error-message-title {
            color: #721c24;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .error-message-text {
            color: #721c24;
            font-size: 14px;
            line-height: 1.6;
        }
        .error-actions {
            text-align: center;
            padding: 20px 0;
        }
        .back-button {
            display: inline-block;
            background: linear-gradient(135deg, #F28018 0%, #ff9933 100%);
            color: #ffffff;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 8px 25px rgba(242, 128, 24, 0.4);
        }
        .error-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <div class="error-icon">⚠️</div>
            <div class="error-title">Email Could Not Be Sent</div>
            <div class="error-subtitle">We encountered an issue while sending your confirmation email</div>
        </div>
        
        <div class="error-content">
            <div class="error-message">
                <div class="error-message-title">Error Details:</div>
                <div class="error-message-text">' . htmlspecialchars($e->getMessage()) . '</div>
            </div>
            
            <div class="error-actions">
                ' . $back_link . '
            </div>
        </div>
        
        <div class="error-footer">
            ' . date('F d, Y \a\t h:i:s A') . '
        </div>
    </div>
</body>
</html>';
}
?>