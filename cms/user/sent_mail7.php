<?php
// sent_mail7.php - IMPROVED VERSION WITH ENHANCED DESIGN & ERROR FIXES

ob_start(); // Prevent any accidental output before redirect
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

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
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
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
$from_email    = 'planningtool@plan.atire.com';
$from_name     = 'A-Tire Planning Tool';
$redirect_page = 'dashboard.php'; // Page to redirect after success

// Helper function for status colors
function getStatusColor($status) {
    $colors = [
        'pending' => '#F28018',
        'confirmed' => '#28a745',
        'pi_confirm' => '#007bff',
        'cus_confirmed' => '#17a2b8',
        'acm_confirm' => '#6610f2',
        'completed' => '#28a745',
        'cancelled' => '#dc3545'
    ];
    return $colors[strtolower($status)] ?? '#6c757d';
}

// Helper function to safely format dates
function formatDate($date) {
    if (empty($date)) return 'N/A';
    try {
        $timestamp = strtotime($date);
        return $timestamp ? date('d M Y, h:i A', $timestamp) : 'N/A';
    } catch (Exception $e) {
        return 'N/A';
    }
}

try {
    // Validate order ID
    if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
        throw new Exception("Missing Order ID");
    }
    
    $order_id = trim($_GET['id']);
    
    // Validate format: only alphanumeric characters allowed
    if (!preg_match('/^[a-zA-Z0-9]+$/', $order_id)) {
        throw new Exception("Invalid Order ID format. Only letters and numbers are allowed.");
    }

    // Fetch order + customer details
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
    if (empty($order->userEmail) || !filter_var($order->userEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Valid customer email is required for this order");
    }

    // Prepare customer data with proper escaping
    $customer_name = htmlspecialchars($order->fullName ?: $order->acm_name ?: 'Valued Customer', ENT_QUOTES, 'UTF-8');
    $order_date_formatted = formatDate($order->order_date);
    $status_display = ucfirst(str_replace('_', ' ', htmlspecialchars($order->status ?? 'pending', ENT_QUOTES, 'UTF-8')));
    $total_items = intval($order->total_items ?? 0);
    $total_quantity = intval($order->total_quantity ?? 0);
    $company_name = !empty($order->company_rn) ? htmlspecialchars($order->company_rn, ENT_QUOTES, 'UTF-8') : '';
    $order_notes = !empty($order->order_notes) ? nl2br(htmlspecialchars($order->order_notes, ENT_QUOTES, 'UTF-8')) : '';

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
    $mail->Subject = "Proforma Invoice Confirmation - Order #$order_id";

    // Email HTML Body
    $mail->Body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
            max-width: 680px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #F28018 0%, #ffa940 50%, #F28018 100%);
        }
        .logo-section img {
            max-width: 240px;
            height: auto;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            margin: 15px 0 10px 0;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #F28018;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Success Banner */
        .success-banner {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.5px;
        }
        
        /* Content Area */
        .content {
            padding: 45px 40px;
        }
        .greeting {
            font-size: 20px;
            color: #1a1a1a;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        /* Order Highlight Box */
        .order-highlight {
            background: linear-gradient(135deg, #fff8f0 0%, #ffe8d6 100%);
            padding: 35px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 40px;
            border: 3px solid #F28018;
            box-shadow: 0 8px 24px rgba(242, 128, 24, 0.15);
        }
        .order-highlight h2 {
            color: #1a1a1a;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .order-number {
            font-size: 48px;
            color: #F28018;
            font-weight: 800;
            margin: 15px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            letter-spacing: 1px;
        }
        .order-date {
            color: #6c757d;
            font-size: 15px;
            margin-top: 12px;
        }
        
        /* Success Message Box */
        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 35px;
            border-left: 6px solid #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.15);
        }
        .success-message-title {
            color: #28a745;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .success-message-icon {
            margin-right: 10px;
            font-size: 24px;
        }
        .success-message p {
            color: #155724;
            margin: 8px 0;
            line-height: 1.8;
            font-size: 15px;
        }
        
        /* Info Section */
        .section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 6px solid #F28018;
        }
        .section-title {
            color: #1a1a1a;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #F28018;
            display: flex;
            align-items: center;
        }
        .section-icon {
            margin-right: 12px;
            font-size: 26px;
        }
        
        /* Info Grid */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 16px 0;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        .info-row:last-child .info-cell {
            border-bottom: none;
        }
        .info-label {
            font-weight: 700;
            color: #F28018;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 40%;
            padding-right: 20px;
        }
        .info-value {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        /* CTA Section */
        .cta-section {
            text-align: center;
            margin: 40px 0;
            padding: 35px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
        }
        .cta-text {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #F28018 0%, #ff9933 100%);
            color: #ffffff !important;
            padding: 18px 45px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 8px 25px rgba(242, 128, 24, 0.4);
            transition: all 0.3s ease;
        }
        
        /* Next Steps Box */
        .steps-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 30px;
            border-radius: 12px;
            margin-top: 35px;
            border-left: 6px solid #2196F3;
        }
        .steps-title {
            color: #2196F3;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .steps-box ul {
            margin: 15px 0;
            padding-left: 30px;
        }
        .steps-box li {
            margin: 12px 0;
            color: #1a1a1a;
            font-weight: 500;
            line-height: 1.8;
            font-size: 15px;
        }
        .steps-box li strong {
            color: #2196F3;
        }
        
        /* Support Box */
        .support-box {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            padding: 25px;
            border-radius: 12px;
            margin-top: 30px;
            border-left: 6px solid #ff9800;
            text-align: center;
        }
        .support-title {
            color: #ff9800;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .support-box p {
            color: #663c00;
            line-height: 1.7;
            font-size: 15px;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .footer-content {
            max-width: 500px;
            margin: 0 auto;
        }
        .footer p {
            margin: 10px 0;
            font-size: 14px;
            color: #b0b0b0;
        }
        .footer strong {
            color: #F28018;
            font-size: 18px;
        }
        .footer a {
            color: #F28018;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .footer a:hover {
            color: #ffa940;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #F28018, transparent);
            margin: 20px 0;
        }
        
        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            .content {
                padding: 30px 25px;
            }
            .header {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 26px;
            }
            .order-number {
                font-size: 36px;
            }
            .section {
                padding: 25px 20px;
            }
            .info-label {
                display: block;
                width: 100%;
                margin-bottom: 8px;
            }
            .info-value {
                display: block;
                width: 100%;
            }
            .action-button {
                padding: 16px 35px;
                font-size: 15px;
            }
            .cta-section {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="logo-section">
                    <img src="https://plan.atire.com/cms/admin/assets/images/atire-logo.png" alt="A-Tire Logo">
                </div>
                <h1>✅ Order Confirmed</h1>
                <p>Professional Planning Tool</p>
            </div>
            
            <!-- Success Banner -->
            <div class="success-banner">
                🎉 Your Proforma Invoice Has Been Successfully Confirmed!
            </div>
            
            <!-- Content -->
            <div class="content">
                <p class="greeting">Dear <strong>' . $customer_name . '</strong>,</p>
                
                <!-- Success Message -->
                <div class="success-message">
                    <div class="success-message-title">
                        <span class="success-message-icon">🎊</span>
                        Excellent News!
                    </div>
                    <p>Your Proforma Invoice has been <strong>successfully confirmed</strong>! Our team has received your order and is now processing it with the highest priority. You can expect regular updates as your order progresses through each stage.</p>
                </div>

                <!-- Order Highlight -->
                <div class="order-highlight">
                    <h2>✓ Order Confirmed</h2>
                    <div class="order-number">#' . htmlspecialchars($order_id) . '</div>
                    <p class="order-date">📅 Placed on ' . $order_date_formatted . '</p>
                </div>

                <!-- Order Summary Section -->
                <div class="section">
                    <div class="section-title">
                        <span class="section-icon">📋</span>
                        Order Summary
                    </div>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Order ID:</div>
                            <div class="info-cell info-value"><strong>#' . htmlspecialchars($order_id) . '</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Order Date:</div>
                            <div class="info-cell info-value">' . $order_date_formatted . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Status:</div>
                            <div class="info-cell info-value">
                                <span class="status-badge" style="background-color: ' . getStatusColor($order->status) . ';">' . $status_display . '</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Total Items:</div>
                            <div class="info-cell info-value"><strong>' . $total_items . '</strong> item(s)</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Total Quantity:</div>
                            <div class="info-cell info-value"><strong>' . $total_quantity . '</strong> units</div>
                        </div>
                        ' . ($company_name ? '
                        <div class="info-row">
                            <div class="info-cell info-label">Company:</div>
                            <div class="info-cell info-value">' . $company_name . '</div>
                        </div>' : '') . '
                        ' . ($order_notes ? '
                        <div class="info-row">
                            <div class="info-cell info-label">Order Notes:</div>
                            <div class="info-cell info-value"><em>' . $order_notes . '</em></div>
                        </div>' : '') . '
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="cta-section">
                    <p class="cta-text">📄 Click below to view your complete invoice and order details</p>
                    <a href="https://plan.atire.com/cms/admin/gate_order.php?id=' . urlencode($order_id) . '" class="action-button">
                        View Full Invoice →
                    </a>
                </div>

                <div class="divider"></div>

                <!-- Next Steps -->
                <div class="steps-box">
                    <div class="steps-title">
                        <span style="margin-right: 10px;">📌</span>
                        What Happens Next?
                    </div>
                    <ul>
                        <li><strong>Step 1:</strong> Review your invoice carefully and verify all order details</li>
                        <li><strong>Step 2:</strong> Complete payment according to the agreed terms</li>
                        <li><strong>Step 3:</strong> Your order will be processed immediately after payment confirmation</li>
                        <li><strong>Step 4:</strong> Receive regular email updates for any status changes</li>
                        <li><strong>Step 5:</strong> Track your delivery and receive notifications</li>
                    </ul>
                </div>

                <!-- Support Box -->
                <div class="support-box">
                    <div class="support-title">💬 Need Help or Have Questions?</div>
                    <p>Our dedicated customer support team is always ready to assist you! Don\'t hesitate to reach out if you have any questions about your order, payment, or delivery.</p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="footer-content">
                    <p><strong>A-Tire Planning Tool</strong></p>
                    <p>© ' . date('Y') . ' All Rights Reserved</p>
                    <div class="divider"></div>
                    <p>📧 Email: <a href="mailto:' . $from_email . '">' . $from_email . '</a></p>
                    <p>🌐 Website: <a href="https://plan.atire.com">plan.atire.com</a></p>
                    <p style="margin-top: 20px; font-size: 12px; color: #888;">
                        This is an automated message. Please do not reply directly to this email.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

    // Plain text alternative
    $mail->AltBody = "PROFORMA INVOICE CONFIRMED - ORDER #$order_id\n\n" .
                     "Dear $customer_name,\n\n" .
                     "Excellent News! Your Proforma Invoice has been successfully confirmed!\n\n" .
                     "=" . str_repeat("=", 50) . "\n" .
                     "ORDER DETAILS:\n" .
                     "=" . str_repeat("=", 50) . "\n" .
                     "Order ID: #$order_id\n" .
                     "Order Date: $order_date_formatted\n" .
                     "Status: $status_display\n" .
                     "Total Items: $total_items\n" .
                     "Total Quantity: $total_quantity units\n" .
                     ($company_name ? "Company: $company_name\n" : "") .
                     ($order_notes ? "Notes: " . strip_tags($order_notes) . "\n" : "") .
                     "\n" . str_repeat("=", 50) . "\n" .
                     "WHAT'S NEXT?\n" .
                     str_repeat("=", 50) . "\n" .
                     "1. Review your invoice carefully\n" .
                     "2. Complete payment as per agreed terms\n" .
                     "3. We'll process your order after payment confirmation\n" .
                     "4. Receive regular email updates\n" .
                     "5. Track your delivery\n\n" .
                     "View your complete invoice at:\n" .
                     "https://plan.atire.com/cms/admin/gate_order.php?id=$order_id\n\n" .
                     str_repeat("=", 50) . "\n" .
                     "NEED HELP?\n" .
                     str_repeat("=", 50) . "\n" .
                     "Our customer support team is here to help!\n" .
                     "Email: $from_email\n" .
                     "Website: https://plan.atire.com\n\n" .
                     "Thank you for choosing A-Tire!\n\n" .
                     "Best regards,\n" .
                     "A-Tire Planning Tool Team\n" .
                     str_repeat("=", 50);

    // Send the email
    $mail->send();

    // SUCCESS → REDIRECT with success message
    $redirect_url = $redirect_page . '?id=' . urlencode($order_id) . '&email_sent=1';
    header("Location: $redirect_url");
    exit;

} catch (Exception $e) {
    // Log the error
    error_log("Email Error for Order #" . ($order_id ?? 'UNKNOWN') . ": " . $e->getMessage());
    
    // ERROR PAGE WITH MODERN DESIGN
    $safe_order_id = isset($order_id) ? htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8') : '';
    $error_message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Delivery Error - A-Tire</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-container {
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .icon {
            font-size: 90px;
            margin-bottom: 25px;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-12px);
            }
        }
        h1 {
            color: #e74c3c;
            margin-bottom: 20px;
            font-size: 32px;
            font-weight: 700;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-details {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            padding: 25px;
            border-radius: 16px;
            margin: 30px 0;
            text-align: left;
            border-left: 6px solid #e74c3c;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.1);
        }
        .error-details strong {
            color: #e74c3c;
            display: block;
            margin-bottom: 12px;
            font-size: 16px;
            font-weight: 700;
        }
        .error-details p {
            color: #2c3e50;
            line-height: 1.7;
            font-size: 14px;
            font-family: monospace;
            background: rgba(255, 255, 255, 0.5);
            padding: 12px;
            border-radius: 8px;
        }
        .info-box {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
            border-left: 6px solid #4caf50;
        }
        .info-box strong {
            color: #2e7d32;
            display: block;
            margin-bottom: 10px;
            font-size: 15px;
        }
        .info-box p {
            color: #1b5e20;
            line-height: 1.6;
            font-size: 14px;
        }
        .order-info {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            border-left: 6px solid #F28018;
        }
        .order-info-label {
            color: #F28018;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .order-info-value {
            color: #1a1a1a;
            font-weight: 700;
            font-size: 24px;
        }
        .button-group {
            margin-top: 35px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 16px 36px;
            background: linear-gradient(135deg, #F28018 0%, #ff9933 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(242, 128, 24, 0.3);
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(242, 128, 24, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
        }
        .btn-secondary:hover {
            box-shadow: 0 10px 30px rgba(108, 117, 125, 0.4);
        }
        .timestamp {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #e9ecef;
            color: #95a5a6;
            font-size: 13px;
        }
        .timestamp strong {
            color: #7f8c8d;
        }
        @media (max-width: 600px) {
            .error-container {
                padding: 35px 25px;
            }
            h1 {
                font-size: 26px;
            }
            .icon {
                font-size: 70px;
            }
            .button-group {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <h1>Email Delivery Issue</h1>
        <p class="subtitle">We encountered a problem while sending your confirmation email</p>
        
        <div class="info-box">
            <strong>✅ Your Order is Safe!</strong>
            <p>Don\'t worry - your order has been successfully recorded in our system. Only the email notification failed to send.</p>
        </div>
        
        <div class="error-details">
            <strong>🔍 Technical Details:</strong>
            <p>' . $error_message . '</p>
        </div>
        
        ' . ($safe_order_id ? '
        <div class="order-info">
            <div class="order-info-label">Your Order ID</div>
            <div class="order-info-value">#' . $safe_order_id . '</div>
        </div>
        ' : '') . '
        
        <div class="button-group">
            ' . ($safe_order_id ? 
            '<a href="confirm_pi.php?id=' . urlencode($safe_order_id) . '" class="btn">
                📄 View Your Order
            </a>' : '') . '
            <a href="dashboard.php" class="btn btn-secondary">
                🏠 Go to Dashboard
            </a>
        </div>
        
        <div class="timestamp">
            <strong>Timestamp:</strong> ' . date('d M Y, h:i:s A') . ' | <strong>Error ID:</strong> ' . uniqid() . '
        </div>
    </div>
</body>
</html>';
    exit;
}

ob_end_flush();
?>