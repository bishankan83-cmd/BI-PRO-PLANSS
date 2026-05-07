<?php
// sent_mail6.php - FIXED VERSION WITH PROPER ORDER ID HANDLING AND UPDATED STYLING

ob_start(); // Prevent any accidental output before redirect

// Include your existing config.php (uses mysqli $con)
require 'include/config.php'; // Adjust path if needed (usually ../include/config.php)

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

// === HELPER FUNCTION ===
/**
 * Get color code based on order status
 * @param string $status Order status
 * @return string Hex color code
 */
function getStatusColor($status) {
    $status = strtolower(trim($status));
    
    switch ($status) {
        case 'pending':
            return '#ffc107'; // Yellow/Amber
        case 'processing':
        case 'in_progress':
            return '#17a2b8'; // Cyan/Info
        case 'confirmed':
            return '#007bff'; // Blue
        case 'shipped':
        case 'dispatched':
            return '#6f42c1'; // Purple
        case 'delivered':
        case 'completed':
            return '#28a745'; // Green/Success
        case 'cancelled':
        case 'canceled':
            return '#dc3545'; // Red/Danger
        case 'on_hold':
        case 'hold':
            return '#fd7e14'; // Orange
        case 'refunded':
            return '#6c757d'; // Gray
        default:
            return '#6c757d'; // Default Gray
    }
}

// === CONFIGURATION ===
$smtp_host     = 'plan.atire.com';
$smtp_port     = 465;
$smtp_username = 'planningtool@plan.atire.com';
$smtp_password = 'Bishan@1919';
$from_email    = 'planningtool@plan.atire.com';
$from_name     = 'A-Tire Planning Tool';
$redirect_page = 'gate_order.php'; // Page to redirect after success

// Initialize $order_id early to avoid undefined variable error
$order_id = null;

try {
    // Validate order ID - Accept both numeric and alphanumeric (with commas, letters, etc.)
    if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
        throw new Exception("Invalid or missing Order ID");
    }
    
    // Get the raw order ID and sanitize it
    $order_id = trim($_GET['id']);
    
    // Determine if order_id is numeric or alphanumeric
    $is_numeric = ctype_digit($order_id);
    
    if ($is_numeric) {
        // Numeric order ID
        $order_id = (int)$order_id;
        $sql = "SELECT o.*, u.userEmail, u.fullName, u.acm_name, u.cus_id, u.company_rn
                FROM tire_orders o
                INNER JOIN users u ON o.customer_id = u.cus_id
                WHERE o.order_id = :order_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    } else {
        // Alphanumeric order ID (e.g., "A-123", "ORD,456", etc.)
        // Sanitize to prevent SQL injection
        $order_id = preg_replace('/[^\w\-,]/', '', $order_id); // Allow alphanumeric, dash, comma
        
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
    $statusColor = getStatusColor($order->status);
    $statusText = ucfirst(str_replace('_', ' ', $order->status));

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

    $mail->isHTML(true);
    $mail->Subject = "Order Confirmation - Order #$display_order_id";

    $mail->Body = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                margin: 20px auto;
                background-color: #ffffff;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                border-radius: 12px;
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #000000 0%, #333333 100%);
                color: #ffffff;
                padding: 30px;
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
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 700;
                color: #ffffff;
            }
            .header p {
                margin: 10px 0 0 0;
                font-size: 16px;
                opacity: 0.9;
                color: #F28018;
            }
            .urgent-banner {
                background-color: {$statusColor};
                color: #ffffff;
                padding: 15px;
                text-align: center;
                font-weight: bold;
                font-size: 18px;
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
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            .info-item {
                background-color: #ffffff;
                padding: 15px;
                border-radius: 6px;
                border: 1px solid #e9ecef;
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
                background-color: {$statusColor};
                color: #ffffff;
            }
            .highlight-box {
                background: #fff3cd;
                border-left: 5px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
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
                <h1>Order Confirmation</h1>
                <p>A-Tire Planning Tool</p>
            </div>
            
            <div class="urgent-banner">
                Order Status: {$statusText}
            </div>
            
            <div class="content">
                <p><strong>Dear {$customer_name},</strong></p>
                
                <div class="highlight-box">
                    <strong>Thank you for your order!</strong> We have received it and are processing it now.
                </div>

                <div class="order-id">
                    <h2>Order #{$display_order_id}</h2>
                    <p>Placed on " . date('d M Y, h:i A', strtotime($order->order_date)) . "</p>
                </div>

                <div class="section">
                    <h3><span class="section-icon">📋</span> Order Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Order ID</div>
                            <div class="info-value">#{$display_order_id}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date</div>
                            <div class="info-value">" . date('d M Y, h:i A', strtotime($order->order_date)) . "</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value"><span class="status-badge">{$statusText}</span></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Items</div>
                            <div class="info-value"><strong>{$order->total_items}</strong></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Total Quantity</div>
                            <div class="info-value"><strong>{$order->total_quantity} units</strong></div>
                        </div>
                        " . (!empty($order->company_rn) ? 
                        "<div class='info-item'>
                            <div class='info-label'>Company</div>
                            <div class='info-value'>" . htmlspecialchars($order->company_rn) . "</div>
                        </div>" : "") . "
                    </div>
                    " . (!empty($order->order_notes) ? 
                    "<div class='info-item' style='margin-top: 15px;'>
                        <div class='info-label'>Order Notes</div>
                        <div class='info-value'><em>" . nl2br(htmlspecialchars($order->order_notes)) . "</em></div>
                    </div>" : "") . "
                </div>

                <p style='color: #333333;'>We'll notify you when your order status changes. Thank you for choosing A-Tire!</p>
            </div>
            
            <div class="footer">
                <p><strong>Customer Service</strong></p>
                <p class="highlight">&copy; " . date('Y') . " A-Tire • All rights reserved</p>
                <p>Contact: {$from_email}</p>
            </div>
        </div>
    </body>
    </html>
    HTML;

    $mail->AltBody = "Order #$display_order_id Confirmed\n\nDear $customer_name,\n\nThank you! Your order is being processed.\n\nDetails:\n- Order ID: #$display_order_id\n- Date: " . date('d M Y, h:i A', strtotime($order->order_date)) . "\n- Status: $statusText\n- Total Items: {$order->total_items}\n- Total Quantity: {$order->total_quantity} units\n\nWe'll notify you when your order status changes.\n\nThank you for choosing A-Tire!\n\nA-Tire Planning Tool\n$from_email";

    $mail->send();

    // SUCCESS → REDIRECT TO gate_order.php WITH SAME ID
    $redirect_url = $redirect_page . '?id=' . urlencode($order_id);
    header("Location: $redirect_url");
    exit;

} catch (Exception $e) {
    // ERROR → SHOW NICE ERROR PAGE (no redirect)
    // Ensure $order_id has a value for the back link
    $safe_order_id = isset($order_id) && !empty($order_id) ? htmlspecialchars($order_id) : '';
    $back_link = !empty($safe_order_id) ? '<p><a href="gate_order.php?id=' . urlencode($safe_order_id) . '" style="color:#F28018;text-decoration:underline">← Back to Order</a></p>' : '<p><a href="gate_order.php" style="color:#F28018;text-decoration:underline">← Back to Orders</a></p>';
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Email Error</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                background: linear-gradient(135deg, #000000, #333333);
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .box {
                background: #ffffff;
                padding: 40px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                text-align: center;
                max-width: 500px;
                width: 100%;
                border-top: 4px solid #F28018;
            }
            .icon {
                font-size: 80px;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc3545;
                margin: 0 0 20px 0;
                font-size: 24px;
            }
            p {
                color: #333333;
                line-height: 1.6;
                margin: 15px 0;
            }
            strong {
                color: #F28018;
            }
            a {
                color: #F28018;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
            }
            a:hover {
                color: #000000;
                text-decoration: underline;
            }
            small {
                color: #808080;
                display: block;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="icon">⚠️</div>
            <h1>Email Could Not Be Sent</h1>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            ' . $back_link . '
            <small>' . date('d M Y, h:i:s A') . '</small>
        </div>
    </body>
    </html>';
}
?>