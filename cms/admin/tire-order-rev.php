<?php
// Database configuration - UPDATE THESE WITH YOUR ACTUAL DATABASE CREDENTIALS
$db_host = 'localhost';
$db_name = 'planatir_cms';
$db_user = 'planatir_task_managemen';
$db_pass = 'Bishan@1919';

// Autoload PHPMailer
require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send revision acceptance email to user from users table
function sendRevisionAcceptanceEmail($orderId, $cusId, $conn) {
    $mail = new PHPMailer(true);
    
    try {
        // Get user details from users table using customer_id
        $userStmt = $conn->prepare("
            SELECT 
               
                fullName,
                customer_name,
                userEmail,
                email_address1,
                email_address2,
                acm_name,
                acm_ref,
                contact_person_name,
                contact_person1_name,
                contact_person2_name
            FROM users 
           
        ");
        
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            throw new Exception("User not found with Customer ID: {$cusId}");
        }
        
        // Determine the primary email to use (priority: userEmail > email_address1 > email_address2)
        $primaryEmail = '';
        if (!empty($userData['userEmail']) && filter_var($userData['userEmail'], FILTER_VALIDATE_EMAIL)) {
            $primaryEmail = $userData['userEmail'];
        } elseif (!empty($userData['email_address1']) && filter_var($userData['email_address1'], FILTER_VALIDATE_EMAIL)) {
            $primaryEmail = $userData['email_address1'];
        } elseif (!empty($userData['email_address2']) && filter_var($userData['email_address2'], FILTER_VALIDATE_EMAIL)) {
            $primaryEmail = $userData['email_address2'];
        }
        
        if (empty($primaryEmail)) {
            throw new Exception("No valid email address found for user");
        }
        
        // Determine user name (priority: fullName > customer_name > contact_person_name)
        $userName = $userData['fullName'] ?? $userData['customer_name'] ?? $userData['contact_person_name'] ?? $userData['contact_person1_name'] ?? 'Valued Customer';
        
        // Get reference
        $reference = $userData['acm_ref'] ?? $userData['customer_id'] ?? 'N/A';
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'plan.atire.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'planningtool@plan.atire.com';
        $mail->Password   = 'Bishan@1919';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        $mail->setFrom('planningtool@plan.atire.com', 'ATIRE Order System');
        
        // Add primary recipient
        $mail->addAddress($primaryEmail, $userName);
        
        // Add CC recipients if available (other email addresses)
        if (!empty($userData['email_address1']) && 
            filter_var($userData['email_address1'], FILTER_VALIDATE_EMAIL) && 
            $userData['email_address1'] !== $primaryEmail) {
            $mail->addCC($userData['email_address1']);
        }
        if (!empty($userData['email_address2']) && 
            filter_var($userData['email_address2'], FILTER_VALIDATE_EMAIL) && 
            $userData['email_address2'] !== $primaryEmail) {
            $mail->addCC($userData['email_address2']);
        }
        
        // Add logo if exists
        $logoPath = 'atire.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'company_logo');
        }
        
        // Email subject
        $mail->Subject = '✅ Revision Request Accepted - Order ID: ' . $orderId;
        
        // Email body (HTML)
        $mail->isHTML(true);
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;">
                ' . (file_exists($logoPath) ? '<div style="text-align: center; margin-bottom: 20px;">
                    <img src="cid:company_logo" alt="ATIRE Logo" width="150" />
                </div>' : '') . '
                
                <div style="background: linear-gradient(135deg, #F28018 0%, #e67e22 100%); padding: 20px; border-radius: 10px 10px 0 0;">
                    <h2 style="color: white; margin: 0; text-align: center;">✅ Revision Request Accepted</h2>
                </div>
                
                <div style="background: #f8f9fa; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;">
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">
                        Dear <strong>' . htmlspecialchars($userName) . '</strong>,
                    </p>
                    
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">
                        Great news! Your revision request has been <strong style="color: #F28018;">accepted</strong> by the ATIRE admin team.
                    </p>
                    
                    <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #F28018;">
                        <h3 style="color: #F28018; margin-top: 0;">Order Details</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                                    <strong style="color: #333;">Order ID:</strong>
                                </td>
                                <td style="padding: 10px 0; border-bottom: 1px solid #e0e0e0; text-align: right;">
                                    <span style="color: #F28018; font-size: 18px; font-weight: bold;">' . htmlspecialchars($orderId) . '</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                                    <strong style="color: #333;">Customer Name:</strong>
                                </td>
                                <td style="padding: 10px 0; border-bottom: 1px solid #e0e0e0; text-align: right;">
                                    ' . htmlspecialchars($userName) . '
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;">
                                    <strong style="color: #333;">Reference Number:</strong>
                                </td>
                                <td style="padding: 10px 0; text-align: right;">
                                    ' . htmlspecialchars($reference) . '
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style="background: rgba(242, 128, 24, 0.1); border: 1px solid #F28018; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <p style="margin: 0; color: #333;">
                            <strong>✓ Next Steps:</strong> You can now proceed with revising your order. Please log into the system to make the necessary changes.
                        </p>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="https://plan.atire.com" 
                           style="background: linear-gradient(135deg, #F28018 0%, #e67e22 100%); 
                                  color: white; 
                                  padding: 15px 40px; 
                                  text-decoration: none; 
                                  border-radius: 8px; 
                                  font-weight: bold; 
                                  display: inline-block;
                                  box-shadow: 0 4px 15px rgba(242, 128, 24, 0.4);">
                            Access ATIRE System
                        </a>
                    </div>
                    
                    <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                    
                    <p style="font-size: 12px; color: #888; text-align: center; margin: 0;">
                        This is an automated notification from the ATIRE Order Management System.<br>
                        Please do not reply directly to this email. For assistance, contact our support team.
                    </p>
                </div>
            </div>
        ';
        
        // Plain text version
        $mail->AltBody = "Revision Request Accepted\n\n" .
                         "Dear {$userName},\n\n" .
                         "Your revision request has been accepted by the ATIRE admin team.\n\n" .
                         "Order ID: {$orderId}\n" .
                         "Reference Number: {$reference}\n\n" .
                         "You can now proceed with revising your order at https://plan.atire.com\n\n" .
                         "This is an automated message. Please do not reply directly.";
        
        // Send email
        $mail->send();
        
        return [
            'success' => true,
            'email' => $primaryEmail,
            'user_name' => $userName,
            'customer_id' => $userData['customer_id'] ?? 'N/A',
            'reference' => $reference
        ];
        
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Check if this is an AJAX request (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get raw input
    $rawInput = file_get_contents('php://input');
    
    // Check if it's JSON data
    if (!empty($rawInput)) {
        // Allow cross-origin requests
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        try {
            // Create database connection
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Decode JSON input
            $input = json_decode($rawInput, true);
            
            // Check for JSON errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
            
            if (!isset($input['order_id'])) {
                throw new Exception('Order ID is required');
            }
            
            $orderId = $input['order_id'];
            $requestStatus = 'accepted';
            
            // Get order details from tire_orders table
            $orderStmt = $conn->prepare("
                SELECT order_id, customer_id
                FROM tire_orders 
                WHERE order_id = :order_id
            ");
            $orderStmt->execute([':order_id' => $orderId]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$orderData) {
                throw new Exception('Order not found with ID: ' . $orderId);
            }
            
            $cusId = $orderData['customer_id'] ?? null;
            
            if (empty($cusId)) {
                throw new Exception('No customer ID associated with this order');
            }
            
            // Update the tire_orders table
            $stmt = $conn->prepare("
                UPDATE tire_orders 
                SET request_status = :request_status,
                    updated_at = CURRENT_TIMESTAMP,
                    accepted_at = CURRENT_TIMESTAMP,
                    accepted_by = :accepted_by
                WHERE order_id = :order_id
            ");
            
            // Get admin username from session or set default
            session_start();
            $acceptedBy = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
            
            $stmt->execute([
                ':request_status' => $requestStatus,
                ':accepted_by' => $acceptedBy,
                ':order_id' => $orderId
            ]);
            
            if ($stmt->rowCount() > 0) {
                // Send email notification to user
                $emailResult = sendRevisionAcceptanceEmail($orderId, $cusId, $conn);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Revision request accepted successfully',
                    'order_id' => $orderId,
                    'request_status' => $requestStatus,
                    'email_sent' => $emailResult['success'],
                    'user_name' => $userData['fullName'] ?? $userData['customer_name'] ?? 'N/A',
                    'user_email' => $emailResult['email'] ?? 'N/A',
                    'reference' => $userData['acm_ref'] ?? $userData['customer_id'] ?? 'N/A',
                    'accepted_by' => $acceptedBy
                ]);
            } else {
                // Check if order already has this status
                $checkStmt = $conn->prepare("
                    SELECT order_id, request_status 
                    FROM tire_orders 
                    WHERE order_id = :order_id
                ");
                $checkStmt->execute([':order_id' => $orderId]);
                $existingOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingOrder && $existingOrder['request_status'] === $requestStatus) {
                    // Still send email even if status unchanged
                    $emailResult = sendRevisionAcceptanceEmail($orderId, $cusId, $conn);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Order already accepted, notification resent',
                        'order_id' => $orderId,
                        'request_status' => $requestStatus,
                        'email_sent' => $emailResult['success'],
                        'user_name' => $userData['fullName'] ?? $userData['customer_name'] ?? 'N/A',
                        'user_email' => $emailResult['email'] ?? 'N/A',
                        'accepted_by' => $acceptedBy
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update order status'
                    ]);
                }
            }
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
}

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit();
}

// If not an AJAX request, display the HTML page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATIRE Admin - Accept Revision Request</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --border-gray: #e0e0e0;
            --bg-light: #f8fafc;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: var(--white);
            max-width: 700px;
            width: 100%;
            padding: 2.5rem;
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            box-shadow: var(--shadow-lg);
        }

        .admin-badge {
            background: var(--gradient-1);
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            display: inline-block;
            margin-bottom: 1.25rem;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        h2 {
            color: var(--dark-gray);
            margin-bottom: 0.35rem;
            font-size: 1.75rem;
            font-weight: 800;
        }

        .subtitle {
            color: var(--text-gray);
            margin-bottom: 1.75rem;
            font-size: 0.95rem;
        }

        .order-info {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.75rem;
            border-left: 4px solid var(--primary-orange);
            border: 1px solid var(--border-gray);
            border-left: 4px solid var(--primary-orange);
        }

        .order-info h3 {
            color: var(--primary-orange);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-gray);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .info-value {
            color: var(--dark-gray);
            font-weight: 500;
            text-align: right;
            font-size: 0.9rem;
        }

        .order-id-highlight {
            font-size: 1.2rem;
            color: var(--primary-orange);
            font-weight: 800;
        }

        .status-badge {
            padding: 0.35rem 0.85rem;
            border-radius: 0.6rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: var(--warning-light);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.accepted {
            background: var(--success-light);
            color: #155724;
            border: 1px solid #b8dfc4;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.75rem;
        }

        .accept-button {
            background: var(--gradient-1);
            color: white;
            padding: 0.9rem 1.75rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            flex: 1;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(242, 128, 24, 0.35);
        }

        .accept-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(242, 128, 24, 0.5);
        }

        .accept-button:disabled {
            background: var(--text-gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .reject-button {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 0.9rem 1.75rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            flex: 1;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.35);
        }

        .reject-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.5);
        }

        .reject-button:disabled {
            background: var(--text-gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .message {
            display: none;
            margin-top: 1.5rem;
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem;
            animation: slideDown 0.4s ease;
        }

        .message.success {
            background-color: var(--success-light);
            border: 1px solid #b8dfc4;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .message.show {
            display: block;
        }

        .message-icon {
            font-size: 1.4rem;
            margin-right: 0.5rem;
        }

        .message-text {
            font-size: 0.95rem;
            line-height: 1.6;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-15px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.25rem;
            display: none;
            font-size: 0.9rem;
        }

        .error-message.show { display: block; }

        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 0.5rem;
            vertical-align: middle;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .detail-info {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(0,0,0,0.08);
        }

        .detail-info p {
            margin: 0.35rem 0;
            font-size: 0.88rem;
        }

        .redirect-notice {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            font-style: italic;
            opacity: 0.8;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 1.5rem;
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .back-link:hover {
            color: var(--secondary-orange);
            transform: translateX(-4px);
        }

        .user-info {
            background: var(--orange-light);
            padding: 1rem 1.25rem;
            border-radius: 0.6rem;
            margin-top: 1rem;
            border-left: 3px solid var(--primary-orange);
        }

        .user-info p {
            margin: 0.3rem 0;
            font-size: 0.88rem;
            color: var(--dark-gray);
        }

        @media (max-width: 768px) {
            body { padding: 1rem; }
            .container { padding: 1.5rem; }
            .button-group { flex-direction: column; }
            h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <span class="admin-badge">👤 ADMIN PANEL</span>
        <h2>✅ Accept Revision Request</h2>
        <p class="subtitle">Review and approve the order revision request</p>
        
        <div class="error-message" id="errorMessage"></div>
        
        <div class="order-info">
            <h3>📋 Order Information</h3>
            
            <div class="info-row">
                <span class="info-label">Order ID:</span>
                <span class="info-value order-id-highlight" id="orderIdDisplay">Loading...</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Current Status:</span>
                <span class="info-value">
                    <span class="status-badge pending" id="currentStatus">Pending Review</span>
                </span>
            </div>
            
            <div class="user-info" id="userInfo" style="display: none;">
                <p><strong>👤 Customer Name:</strong> <span id="userNameDisplay">N/A</span></p>
                <p><strong>📧 Email:</strong> <span id="userEmailDisplay">N/A</span></p>
                <p><strong>📱 Reference:</strong> <span id="referenceDisplay">N/A</span></p>
            </div>
        </div>
        
        <div class="button-group">
            <button class="accept-button" id="acceptButton" onclick="acceptRevision()">
                ✓ Accept Request
            </button>
            <button class="reject-button" id="rejectButton" onclick="rejectRevision()" style="display: none;">
                ✗ Reject Request
            </button>
        </div>
        
        <div class="message" id="statusMessage">
            <span class="message-icon" id="messageIcon">✅</span>
            <span class="message-text" id="messageText">
                <strong>Request accepted successfully!</strong><br>
                The customer has been notified via email and can now proceed with revisions.
                <div class="detail-info" id="requestDetails" style="display: none;">
                    <p><strong>Accepted by:</strong> <span id="acceptedBy"></span></p>
                    <p><strong>Email notification:</strong> <span id="emailStatus"></span></p>
                </div>
                <div class="redirect-notice" id="redirectNotice"></div>
            </span>
        </div>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

    <script>
        // Get order_id from URL parameter
        function getOrderIdFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('oid');
        }

        // Display order ID on page load
        window.onload = function() {
            const orderId = getOrderIdFromUrl();
            const errorMsg = document.getElementById('errorMessage');
            
            if (orderId) {
                document.getElementById('orderIdDisplay').textContent = orderId;
                document.getElementById('userInfo').style.display = 'block';
            } else {
                errorMsg.textContent = '⚠️ No order ID found in URL. Please access this page with ?oid=ORDER_ID parameter.';
                errorMsg.classList.add('show');
                document.getElementById('acceptButton').disabled = true;
            }
        };

        async function acceptRevision() {
            const button = document.getElementById('acceptButton');
            const message = document.getElementById('statusMessage');
            const errorMsg = document.getElementById('errorMessage');
            const requestDetails = document.getElementById('requestDetails');
            const redirectNotice = document.getElementById('redirectNotice');
            
            errorMsg.classList.remove('show');
            message.classList.remove('show', 'success', 'error');
            requestDetails.style.display = 'none';
            redirectNotice.textContent = '';
            
            const orderId = getOrderIdFromUrl();
            
            if (!orderId) {
                errorMsg.textContent = '⚠️ Order ID is missing';
                errorMsg.classList.add('show');
                return;
            }
            
            button.disabled = true;
            button.innerHTML = 'Processing... <span class="loading"></span>';
            
            try {
                const requestData = { order_id: orderId, action: 'accept' };
                
                const response = await fetch(window.location.href.split('?')[0], {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(requestData)
                });
                
                const responseText = await response.text();
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    throw new Error('Server returned invalid response. Please check database configuration.');
                }
                
                if (data.success) {
                    if (data.user_name) document.getElementById('userNameDisplay').textContent = data.user_name;
                    if (data.user_email) document.getElementById('userEmailDisplay').textContent = data.user_email;
                    if (data.reference) document.getElementById('referenceDisplay').textContent = data.reference;
                    
                    document.getElementById('acceptedBy').textContent = data.accepted_by || 'Admin';
                    document.getElementById('emailStatus').textContent = data.email_sent
                        ? '✓ Sent successfully to ' + data.user_email
                        : '✗ Failed to send';
                    document.getElementById('emailStatus').style.color = data.email_sent ? '#27ae60' : '#dc3545';
                    
                    requestDetails.style.display = 'block';
                    message.classList.add('show', 'success');
                    
                    button.innerHTML = '✓ Request Accepted';
                    button.style.background = 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)';
                    
                    const statusBadge = document.getElementById('currentStatus');
                    statusBadge.textContent = 'Accepted';
                    statusBadge.classList.remove('pending');
                    statusBadge.classList.add('accepted');
                    
                    let countdown = 5;
                    redirectNotice.textContent = `Redirecting to dashboard in ${countdown} seconds...`;
                    
                    const countdownInterval = setInterval(() => {
                        countdown--;
                        redirectNotice.textContent = countdown > 0
                            ? `Redirecting to dashboard in ${countdown} seconds...`
                            : 'Redirecting now...';
                    }, 1000);
                    
                    setTimeout(function() {
                        clearInterval(countdownInterval);
                        window.location.href = 'dashboard.php';
                    }, 5000);
                } else {
                    message.classList.add('show', 'error');
                    document.getElementById('messageIcon').textContent = '❌';
                    document.getElementById('messageText').innerHTML = '<strong>Error:</strong><br>' + data.message;
                    button.disabled = false;
                    button.innerHTML = '✓ Accept Request';
                }
            } catch (error) {
                console.error('Error:', error);
                message.classList.add('show', 'error');
                document.getElementById('messageIcon').textContent = '❌';
                document.getElementById('messageText').innerHTML = '<strong>Error:</strong><br>' + error.message;
                button.disabled = false;
                button.innerHTML = '✓ Accept Request';
            }
        }

        async function rejectRevision() {
            alert('Reject functionality - implement based on your requirements');
        }
    </script>
</body>
</html>