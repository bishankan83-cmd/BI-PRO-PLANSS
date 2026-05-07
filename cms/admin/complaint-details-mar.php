<?php
session_start();
include('include/config.php');

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('d-m-Y h:i:s A', time());

// Check if admin is logged in (adjust this based on your admin authentication)
// Example: if (!isset($_SESSION['admin_id'])) { header('location:index.php'); exit; }

// Get and validate Claim ID
$cid = 0;
if (isset($_GET['cid']) && is_numeric($_GET['cid'])) {
    $cid = intval($_GET['cid']);
}

if ($cid <= 0) {
    header('location:manage-complaints.php');
    exit;
}

// Email configuration - Update these with your SMTP settings
define('SMTP_HOST', 'plan.atire.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'planningtool@plan.atire.com');
define('SMTP_PASSWORD', 'Bishan@1919');
define('SMTP_FROM_EMAIL', 'planningtool@plan.atire.com');
define('SMTP_FROM_NAME', 'Tire Claim Management System');

// Function to send email using SMTP
function sendStatusUpdateEmail($userEmail, $userName, $complaintNumber, $status, $remark) {
    try {
        // Check if PHPMailer is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            require_once 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($userEmail, $userName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = getEmailSubject($status, $complaintNumber);
            $mail->Body = getEmailBody($userName, $complaintNumber, $status, $remark);
            
            $mail->send();
            return true;
        } else {
            // Fallback: Log email
            return sendEmailViaCurl($userEmail, $userName, $complaintNumber, $status, $remark);
        }
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// Fallback email logging
function sendEmailViaCurl($userEmail, $userName, $complaintNumber, $status, $remark) {
    $logFile = 'email_logs.txt';
    $emailLog = sprintf(
        "[%s] Email to: %s (%s)\nSubject: %s\nStatus: %s\nRemark: %s\n%s\n\n",
        date('Y-m-d H:i:s'),
        $userName,
        $userEmail,
        getEmailSubject($status, $complaintNumber),
        $status,
        $remark,
        str_repeat('-', 80)
    );
    file_put_contents($logFile, $emailLog, FILE_APPEND);
    return true;
}

// Function to get email subject
function getEmailSubject($status, $complaintNumber) {
    $statusSubjects = [
        'In process' => 'Your Claim is Being Processed',
        'Proceed with Decision' => 'Action Required on Your Claim',
        'closed' => 'Your Claim Has Been Resolved'
    ];
    
    $subject = isset($statusSubjects[$status]) ? $statusSubjects[$status] : 'Claim Status Update';
    return $subject . ' - Claim #' . $complaintNumber;
} 

// Function to get email body
function getEmailBody($userName, $complaintNumber, $status, $remark) {
    $statusColors = [
        'In process' => '#e74c3c',
        'Proceed with Decision' => '#f39c12',
        'closed' => '#27ae60'
    ];
    
    $statusColor = isset($statusColors[$status]) ? $statusColors[$status] : '#333333';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333333;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .email-container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: #ffffff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .email-header { 
                background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
                color: #ffffff; 
                padding: 30px; 
                text-align: center;
            }
            .email-header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: bold;
            }
            .email-body { 
                padding: 30px;
            }
            .status-badge {
                display: inline-block;
                padding: 10px 20px;
                background: ' . $statusColor . ';
                color: #ffffff;
                border-radius: 5px;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 14px;
                margin: 15px 0;
            }
            .info-box {
                background: #f8f9fa;
                border-left: 4px solid #F28018;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .info-box strong {
                color: #F28018;
            }
            .remark-box {
                background: #fff3e0;
                border: 1px solid #F28018;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .remark-box h3 {
                color: #F28018;
                margin-top: 0;
                font-size: 16px;
            }
            .email-footer { 
                background: #f8f9fa; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px;
                color: #666666;
                border-top: 1px solid #e0e0e0;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>🔔 Claim Status Update</h1>
            </div>
            
            <div class="email-body">
                <p>Dear <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                
                <p>We are writing to inform you about an update to your tire Claim.</p>
                
                <div class="info-box">
                    <strong>Claim Number:</strong> ' . htmlspecialchars($complaintNumber) . '<br>
                    <strong>Date:</strong> ' . date('F j, Y g:i A') . '
                </div>
                
                <p><strong>Current Status:</strong></p>
                <div class="status-badge">' . htmlspecialchars($status) . '</div>
                
                <div class="remark-box">
                    <h3>📝 Admin Remarks:</h3>
                    <p>' . nl2br(htmlspecialchars($remark)) . '</p>
                </div>
                
                ' . getStatusMessage($status) . '
                
                <p>If you have any questions or concerns, please don\'t hesitate to contact us.</p>
                
                <p>Best regards,<br>
                <strong>Customer Support Team</strong><br>
                Tire Claim Management System</p>
            </div>
            
            <div class="email-footer">
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>&copy; ' . date('Y') . ' Tire Claim Management System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $message;
}

// Function to get status-specific message
function getStatusMessage($status) {
    switch($status) {
        case 'In process':
            return '<p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <strong>⏳ In Process:</strong> Your Claim is currently being reviewed by our team. 
                    We are investigating the matter and will update you with our findings soon.
                    </p>';
        case 'Proceed with Decision':
            return '<p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #f39c12;">
                    <strong>⚠️ Action Required:</strong> We have reviewed your Claim and are proceeding with a decision. 
                    Please check your Claim details for more information and any required action from your side.
                    </p>';
        case 'closed':
            return '<p style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #27ae60;">
                    <strong>✅ Closed:</strong> Your Claim has been resolved. 
                    Thank you for your patience. If you have any further concerns, please feel free to submit a new Claim.
                    </p>';
        default:
            return '<p>Your Claim status has been updated. Please review the details above.</p>';
    }
}

// Initialize variables
$msg = null;
$error = null;
$row = null;

// Handle status update submission
if (isset($_POST['update_status'])) {
    $newStatus = mysqli_real_escape_string($con, $_POST['status']);
    $remark = mysqli_real_escape_string($con, $_POST['remark']);
    
    // First, get the user's email and name
    $stmtUserInfo = mysqli_prepare($con, 
        "SELECT u.userEmail, u.fullName, tc.complaintNumber 
         FROM tbl_tire_complaints tc 
         JOIN users u ON u.id = tc.userId 
         WHERE tc.id = ?");
    
    if ($stmtUserInfo) {
        mysqli_stmt_bind_param($stmtUserInfo, "i", $cid);
        mysqli_stmt_execute($stmtUserInfo);
        $resultUserInfo = mysqli_stmt_get_result($stmtUserInfo);
        $userInfo = mysqli_fetch_array($resultUserInfo);
        mysqli_stmt_close($stmtUserInfo);
        
        if ($userInfo) {
            // Update Claim status
            $stmtUpdate = mysqli_prepare($con, 
                "UPDATE tbl_tire_complaints 
                 SET status=?, admin_remark=?, admin_remark_date=?, updated_at=? 
                 WHERE id=?");
            
            if ($stmtUpdate) {
                mysqli_stmt_bind_param($stmtUpdate, "ssssi", $newStatus, $remark, $currentTime, $currentTime, $cid);
                
                if (mysqli_stmt_execute($stmtUpdate)) {
                    // Insert into remark history
                    $stmtRemark = mysqli_prepare($con, 
                        "INSERT INTO complaintremark(complaintNumber, status, remark, remarkDate) 
                         VALUES(?, ?, ?, ?)");
                    
                    if ($stmtRemark) {
                        mysqli_stmt_bind_param($stmtRemark, "isss", $cid, $newStatus, $remark, $currentTime);
                        mysqli_stmt_execute($stmtRemark);
                        mysqli_stmt_close($stmtRemark);
                    }
                    
                    // Send email notification
                    $emailSent = sendStatusUpdateEmail(
                        $userInfo['userEmail'],
                        $userInfo['fullName'],
                        $userInfo['complaintNumber'],
                        $newStatus,
                        $remark
                    );
                    
                    if ($emailSent) {
                        $msg = "Status updated successfully! Email notification has been logged/sent to " . htmlspecialchars($userInfo['userEmail']);
                    } else {
                        $msg = "Status updated successfully! However, email notification could not be sent. Please check email_logs.txt file.";
                    }
                } else {
                    $error = "Error updating status: " . mysqli_error($con);
                }
                mysqli_stmt_close($stmtUpdate);
            } else {
                $error = "Error preparing update statement: " . mysqli_error($con);
            }
        } else {
            $error = "Error: User information not found.";
        }
    } else {
        $error = "Error preparing statement: " . mysqli_error($con);
    }
}

// Fetch Claim data
$stmtComplaint = mysqli_prepare($con, 
    "SELECT tc.*, u.fullName as name, u.userEmail 
    FROM tbl_tire_complaints tc 
    JOIN users u ON u.id = tc.userId 
    WHERE tc.id = ?");

if ($stmtComplaint) {
    mysqli_stmt_bind_param($stmtComplaint, "i", $cid);
    mysqli_stmt_execute($stmtComplaint);
    $resultComplaint = mysqli_stmt_get_result($stmtComplaint);
    $row = mysqli_fetch_array($resultComplaint);
    mysqli_stmt_close($stmtComplaint);
}

// Helper function to display values
function displayValue($value, $default = 'N/A') {
    if ($value === null || $value === '' || $value === 'NULL') {
        return '<span class="empty-value">' . htmlentities($default) . '</span>';
    }
    return '<span class="info-value">' . htmlentities($value) . '</span>';
}

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}

// Helper function to format comma-separated values
function formatCommaSeparated($value) {
    if (empty($value) || $value === 'NULL') {
        return '<span class="empty-value">N/A</span>';
    }
    $items = array_map('trim', explode(',', $value));
    $formatted = array_map(function($item) {
        return '<span class="badge-item">' . htmlentities($item) . '</span>';
    }, $items);
    return implode(' ', $formatted);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tire Claim Management System - Admin View">
    <title>CMS | Admin - Claim Details #<?php echo htmlentities($row['complaintNumber'] ?? $cid); ?></title>
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
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background: var(--gradient-1);
            color: white;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 1rem;
        }

        .user-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--orange-light);
            border: 1px solid var(--primary-orange);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .header-actions-right {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .card.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--orange-light);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-orange);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            border: 1px solid var(--border-gray);
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background: var(--bg-light);
            color: var(--dark-gray);
            font-weight: 600;
            width: 200px;
        }

        .table td {
            background: var(--white);
            color: var(--dark-gray);
        }

        .table thead th {
            width: auto;
        }

        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-danger {
            background: var(--error-light);
            color: var(--error);
        }

        .badge-warning {
            background: var(--warning-light);
            color: var(--warning);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success);
        }

        .badge-item {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: var(--orange-light);
            color: var(--primary-orange);
            border-radius: 0.4rem;
            font-size: 0.85rem;
            font-weight: 500;
            margin: 0.2rem;
        }

        .view-file-link {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .view-file-link:hover {
            text-decoration: underline;
            color: var(--secondary-orange);
        }

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .photo-item {
            position: relative;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 2px solid var(--border-gray);
            transition: all 0.3s;
            box-shadow: var(--shadow);
            cursor: pointer;
        }

        .photo-item:hover {
            border-color: var(--primary-orange);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .photo-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s;
        }

        .photo-item:hover img {
            transform: scale(1.05);
        }

        .photo-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }

        .video-container {
            margin-top: 1rem;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            background: var(--black);
        }

        .video-container video {
            width: 100%;
            max-height: 500px;
            display: block;
        }

        .info-value {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .empty-value {
            color: var(--text-gray);
            font-style: italic;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .error-message {
            text-align: center;
            color: var(--error);
            padding: 3rem;
            font-size: 1.1rem;
        }

        .error-message i {
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        .print-btn {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .print-btn:hover {
            background: var(--primary-orange);
            color: var(--white);
            border-color: var(--primary-orange);
        }

        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-1);
            color: white;
            border: none;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s;
            z-index: 1000;
        }

        .scroll-to-top:hover {
            transform: translateY(-5px) scale(1.1);
        }

        .scroll-to-top.show {
            display: flex;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label .required {
            color: var(--error);
            margin-left: 0.25rem;
        }

        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-gray);
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
            background: var(--white);
        }

        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px var(--orange-light);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-error {
            background: var(--error-light);
            color: var(--error);
            border: 1px solid var(--error);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-gray);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-orange);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-gray);
            cursor: pointer;
            padding: 0.25rem;
            line-height: 1;
            transition: all 0.2s;
        }

        .modal-close:hover {
            color: var(--error);
            transform: rotate(90deg);
        }

        .email-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196F3;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }

        .email-info i {
            color: #2196F3;
            margin-right: 0.5rem;
        }

        /* Fixed status display box */
        .status-fixed-box {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border: 2px solid var(--error);
            border-radius: 0.5rem;
            background: var(--error-light);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--error);
        }

        .status-fixed-box i {
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions-right {
                width: 100%;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }

            .table th,
            .table td {
                font-size: 0.85rem;
                padding: 0.75rem;
            }

            .table th {
                width: 150px;
            }

            .photo-gallery {
                grid-template-columns: repeat(2, 1fr);
            }

            .card-body {
                padding: 1rem;
            }

            .card-header {
                padding: 1rem 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .photo-gallery {
                grid-template-columns: 1fr;
            }

            .header-actions-right {
                flex-direction: column;
            }
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .page-header .header-actions-right,
            .btn,
            .scroll-to-top,
            .admin-badge,
            .modal {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .photo-item {
                page-break-inside: avoid;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    Tire Claim Details
                    <span class="admin-badge">
                        <i class="fas fa-shield-alt"></i> Admin View
                    </span>
                </h1>
                <p class="page-subtitle">Claim #<?php echo htmlentities($row['complaintNumber'] ?? $cid); ?> - Complete Information</p>
                <?php if ($row): ?>
                <div class="user-info-badge">
                    <i class="fas fa-user"></i>
                    <strong>Complainant:</strong> <?php echo htmlentities($row['name']); ?>
                    (<?php echo htmlentities($row['userEmail']); ?>)
                </div>
                <?php endif; ?>
            </div>
            <div class="header-actions-right">
                <button onclick="openStatusModal()" class="btn btn-success">
                    <i class="fas fa-tasks"></i>
                    Take Action
                </button>
                <button onclick="window.print()" class="btn print-btn">
                    <i class="fas fa-print"></i>
                    Print Details
                </button>
                <a href="Marketing.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back To Dashboard
                </a>
            </div>
        </div>

        <?php if ($row): ?>

        <!-- Alert Messages -->
        <?php if (isset($msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Claim ID</th>
                        <td><?php echo displayValue($row['id']); ?></td>
                        <th>Claim Number</th>
                        <td><?php echo displayValue($row['complaintNumber']); ?></td>
                    </tr>
                    <tr>
                        <th>Complainant Name</th>
                        <td><?php echo displayValue($row['name']); ?></td>
                        <th>Email Address</th>
                        <td><?php echo displayValue($row['userEmail']); ?></td>
                    </tr>
                    <tr>
                        <th>User ID</th>
                        <td><?php echo displayValue($row['userId']); ?></td>
                        <th>Registration Date</th>
                        <td><?php echo displayValue($row['created_at']); ?></td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td><?php echo displayValue($row['updated_at']); ?></td>
                        <th>Status</th>
                        <td>
                            <?php 
                            $status = $row['status'];
                            if ($status == '' || $status == 'In process'): ?>
                                <span class="badge badge-danger">In process</span>
                            <?php elseif ($status == 'Proceed with Decision'): ?>
                                <span class="badge badge-warning">Proceed with Decision</span>
                            <?php elseif ($status == 'closed'): ?>
                                <span class="badge badge-success">Closed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Tire & Purchase Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-tire"></i>
                    Tire & Purchase Information
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Serial Number</th>
                        <td><?php echo displayValue($row['serial_number']); ?></td>
                        <th>Tire Size</th>
                        <td><?php echo displayValue($row['tire_size']); ?></td>
                    </tr>
                    <tr>
                        <th>Purchase Date</th>
                        <td><?php echo displayValue($row['purchase_date']); ?></td>
                        <th>Purchase Location</th>
                        <td><?php echo displayValue($row['purchase_location']); ?></td>
                    </tr>
                    <tr>
                        <th>Invoice Number</th>
                        <td><?php echo displayValue($row['invoice_number']); ?></td>
                        <th>Warranty Period</th>
                        <td><?php echo displayValue($row['warranty_period']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Vehicle Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-car"></i>
                    Vehicle Information
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Equipment Type</th>
                        <td colspan="3">
                            <?php 
                            echo displayValue($row['equipment_type']);
                            if (!empty($row['equipment_type_other'])): 
                                echo ' <span class="badge-item">' . htmlentities($row['equipment_type_other']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Vehicle Make/Model</th>
                        <td><?php echo displayValue($row['vehicle_make_model']); ?></td>
                        <th>Vehicle Year</th>
                        <td><?php echo displayValue($row['vehicle_year']); ?></td>
                    </tr>
                    <tr>
                        <th>Usage Type</th>
                        <td colspan="3">
                            <?php 
                            echo displayValue($row['usage_type']);
                            if (!empty($row['usage_type_other'])): 
                                echo ' <span class="badge-item">' . htmlentities($row['usage_type_other']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Usage Pattern</th>
                        <td colspan="3">
                            <?php 
                            echo formatCommaSeparated($row['usage_pattern']);
                            if (!empty($row['usage_pattern_other'])): 
                                echo ' <span class="badge-item">Other: ' . htmlentities($row['usage_pattern_other']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Daily Usage</th>
                        <td><?php echo displayValue(!empty($row['daily_usage']) ? $row['daily_usage'] . ' hours/km' : null); ?></td>
                        <th>Mileage/Hours</th>
                        <td><?php echo displayValue($row['mileage_hours']); ?></td>
                    </tr>
                    <tr>
                        <th>Duration Before Problem</th>
                        <td colspan="3"><?php echo displayValue($row['duration_before_problem']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Claim Details -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Claim Details
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Nature of Claim</th>
                        <td colspan="3">
                            <?php 
                            echo displayValue($row['nature_complaint']);
                            if (!empty($row['nature_other'])): 
                                echo ' <span class="badge-item">' . htmlentities($row['nature_other']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Detailed Description</th>
                        <td colspan="3"><?php echo nl2br(displayValue($row['detailed_description'])); ?></td>
                    </tr>
                    <tr>
                        <th>Impact</th>
                        <td colspan="3">
                            <?php 
                            echo displayValue($row['impact']);
                            if (!empty($row['impact_other'])): 
                                echo ' <span class="badge-item">' . htmlentities($row['impact_other']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Previous Actions Taken</th>
                        <td colspan="3"><?php echo nl2br(displayValue($row['previous_actions'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Operating Conditions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-cloud-sun"></i>
                    Operating Conditions
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Operating Conditions</th>
                        <td colspan="3"><?php echo nl2br(displayValue($row['operating_conditions'])); ?></td>
                    </tr>
                    <tr>
                        <th>Surface Conditions</th>
                        <td><?php echo formatCommaSeparated($row['surface_conditions']); ?></td>
                        <th>Temperature Conditions</th>
                        <td><?php echo displayValue($row['temperature_conditions']); ?></td>
                    </tr>
                    <tr>
                        <th>Speed Operation</th>
                        <td><?php echo displayValue($row['speed_operation']); ?></td>
                        <th>Load Capacity</th>
                        <td><?php echo displayValue($row['load_capacity']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Resolution & Documentation -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-clipboard-check"></i>
                    Resolution & Documentation
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Resolution Requested</th>
                        <td colspan="3">
                            <?php 
                            echo displayValue($row['resolution_requested']);
                            if (!empty($row['resolution_other'])): 
                                echo ' <span class="badge-item">' . htmlentities($row['resolution_other']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Documentation Provided</th>
                        <td colspan="3">
                            <?php 
                            echo formatCommaSeparated($row['documentation']);
                            if (!empty($row['other_documentation'])): 
                                echo ' <span class="badge-item">Other: ' . htmlentities($row['other_documentation']) . '</span>';
                            endif; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Additional Comments</th>
                        <td colspan="3"><?php echo nl2br(displayValue($row['additional_comments'])); ?></td>
                    </tr>
                    <tr>
                        <th>Claim File</th>
                        <td colspan="3">
                            <?php 
                            $cfile = $row['complaint_file'];
                            if (empty($cfile) || $cfile == "NULL") {
                                echo '<span class="empty-value">No file uploaded</span>';
                            } else { 
                                $filePath = '../user/' . $cfile;
                                if (file_exists($filePath)) {
                                    $fileSize = filesize($filePath);
                            ?>
                                <a href="<?php echo htmlentities($filePath); ?>" 
                                   class="view-file-link" 
                                   target="_blank">
                                   <i class="fas fa-file-download"></i> 
                                   View/Download File (<?php echo formatFileSize($fileSize); ?>)
                                </a>
                            <?php 
                                } else {
                                    echo '<span class="empty-value">File not found</span>';
                                }
                            } 
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Video Upload -->
        <?php if (!empty($row['video_filename']) && !empty($row['video_path'])): 
            $videoPath = '../user/' .$row['video_path'];
            if (file_exists($videoPath)):
        ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-video"></i>
                    Video Documentation
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Video Filename</th>
                        <td><?php echo displayValue($row['video_filename']); ?></td>
                        <th>Video Size</th>
                        <td><?php echo displayValue(!empty($row['video_size']) ? formatFileSize($row['video_size']) : null); ?></td>
                    </tr>
                    <tr>
                        <th>Video Type</th>
                        <td colspan="3"><?php echo displayValue($row['video_mime']); ?></td>
                    </tr>
                </table>
                
                <div class="video-container">
                    <video controls preload="metadata">
                        <source src="<?php echo htmlentities($videoPath); ?>" 
                                type="<?php echo htmlentities($row['video_mime']); ?>">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
        <?php endif; endif; ?>

        <!-- Photo Documentation -->
        <?php 
        $hasPhotos = false;
        $photos = [];
        
        $photoFields = [
            'front_left_photo' => 'Front Left',
            'front_right_photo' => 'Front Right',
            'rear_left_photo' => 'Rear Left',
            'rear_right_photo' => 'Rear Right',
            'additional_photo_1' => 'Additional Photo 1',
            'additional_photo_2' => 'Additional Photo 2',
            'additional_photo_3' => 'Additional Photo 3',
            'additional_photo_4' => 'Additional Photo 4'
        ];
        
        foreach ($photoFields as $field => $label) {
            if (!empty($row[$field]) && $row[$field] != 'NULL') {
                $photoPath = '../user/' . $row[$field];
                if (file_exists($photoPath)) {
                    $hasPhotos = true;
                    $photos[] = ['path' => $photoPath, 'label' => $label, 'field' => $field];
                }
            }
        }
        
        if ($hasPhotos): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-camera"></i>
                    Photo Documentation (<?php echo count($photos); ?> Photos)
                </h2>
            </div>
            <div class="card-body">
                <div class="photo-gallery">
                    <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <a href="<?php echo htmlentities($photo['path']); ?>" target="_blank">
                            <img src="<?php echo htmlentities($photo['path']); ?>" 
                                 alt="<?php echo htmlentities($photo['label']); ?>"
                                 loading="lazy"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2214%22%3EImage Not Found%3C/text%3E%3C/svg%3E'">
                            <div class="photo-label"><?php echo htmlentities($photo['label']); ?></div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Admin Remarks -->
        <?php if (!empty($row['admin_remark'])): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user-shield"></i>
                    Admin Remarks
                </h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Admin Remark</th>
                        <td><?php echo nl2br(htmlentities($row['admin_remark'])); ?></td>
                    </tr>
                    <tr>
                        <th>Remark Date</th>
                        <td><?php echo displayValue($row['admin_remark_date']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status History -->
        <?php 
        $stmtHistory = mysqli_prepare($con, 
            "SELECT cr.remark as remark, cr.status as sstatus, cr.remarkDate as rdate 
            FROM complaintremark cr
            JOIN tbl_tire_complaints tc ON tc.id = cr.complaintNumber 
            WHERE cr.complaintNumber = ?
            ORDER BY cr.remarkDate DESC");
        
        if ($stmtHistory) {
            mysqli_stmt_bind_param($stmtHistory, "i", $cid);
            mysqli_stmt_execute($stmtHistory);
            $resultHistory = mysqli_stmt_get_result($stmtHistory);
            $count = mysqli_num_rows($resultHistory);
            
            if ($count > 0):
        ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-history"></i>
                    Status History & Remarks (<?php echo $count; ?> Entries)
                </h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">#</th>
                                <th style="width: 50%;">Remark</th>
                                <th style="width: 20%;">Status</th>
                                <th style="width: 20%;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cnt = 1;
                            while ($rw = mysqli_fetch_array($resultHistory)) { 
                            ?>
                            <tr>
                                <td><?php echo $cnt; ?></td>
                                <td><?php echo nl2br(htmlentities($rw['remark'])); ?></td>
                                <td>
                                    <?php 
                                    $sstatus = $rw['sstatus'];
                                    if ($sstatus == 'Proceed with Decision'): ?>
                                        <span class="badge badge-warning">Proceed with Decision</span>
                                    <?php elseif ($sstatus == 'closed'): ?>
                                        <span class="badge badge-success">Closed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?php echo htmlentities($sstatus); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlentities($rw['rdate']); ?></td>
                            </tr>
                            <?php $cnt++; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php 
            endif;
            mysqli_stmt_close($stmtHistory);
        }
        ?>

        <?php else: ?>
        <!-- Error Message -->
        <div class="card">
            <div class="card-body">
                <p class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    Claim not found or has been deleted from the system.
                </p>
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="manage-complaints.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Return to All Complaints
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Status Update Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-edit"></i>
                    Update Claim Status
                </h3>
                <button class="modal-close" onclick="closeStatusModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="email-info">
                <i class="fas fa-envelope"></i>
                <strong>Email Notification:</strong> An email notification will be logged to 
                <strong><?php echo htmlentities($row['userEmail'] ?? 'user'); ?></strong> 
                when you update the status. Check email_logs.txt for details.
            </div>
            
            <form method="POST" action="" id="statusUpdateForm">

                <!-- =====================================================
                     STATUS FIELD — LOCKED TO "In Process" ONLY
                     Hidden input sends the fixed value; display box shows it
                ===================================================== -->
                <div class="form-group">
                    <label class="form-label">
                        Status
                        <span class="required">*</span>
                    </label>
                    <!-- Hidden input carries the value on form submit -->
                    <input type="hidden" name="status" value="In process">
                    <!-- Visual display only (not editable) -->
                    <div class="status-fixed-box">
                        <i class="fas fa-spinner fa-spin"></i>
                        In Process
                    </div>
                </div>
                <!-- ===================================================== -->

                <div class="form-group">
                    <label class="form-label">
                        Admin Remark
                        <span class="required">*</span>
                    </label>
                    <textarea name="remark" class="form-textarea" placeholder="Enter your remarks about this status update (this will be logged in the email notification)..." required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_status" class="btn btn-success">
                        <i class="fas fa-check"></i>
                        Update Status & Log Email
                    </button>
                    <button type="button" onclick="closeStatusModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollTopBtn">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script>
        // Modal functions
        function openStatusModal() {
            document.getElementById('statusModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Close modal on outside click
        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeStatusModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeStatusModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load with staggered timing
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-in');
                }, index * 100);
            });

            // Scroll to top button functionality
            const scrollBtn = document.getElementById('scrollTopBtn');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollBtn.classList.add('show');
                } else {
                    scrollBtn.classList.remove('show');
                }
            });

            scrollBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });

            // Form validation
            const statusForm = document.getElementById('statusUpdateForm');
            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    const remark = this.querySelector('[name="remark"]').value.trim();

                    if (!remark) {
                        e.preventDefault();
                        alert('Please enter a remark');
                        return false;
                    }

                    // Confirm before submitting
                    if (!confirm('Are you sure you want to update the status of this Claim to "In Process"?\n\nAn email notification will be logged for the user.')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }

            // Video player enhancements
            const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.addEventListener('loadedmetadata', function() {
                    console.log('Video loaded: ' + Math.round(this.duration) + ' seconds');
                });

                video.addEventListener('error', function() {
                    const container = this.closest('.video-container');
                    if (container) {
                        container.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--error);"><i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i><br>Error loading video. The file may be corrupted or in an unsupported format.</div>';
                    }
                });
            });

            // Print preparation
            window.addEventListener('beforeprint', function() {
                document.querySelectorAll('video').forEach(video => {
                    video.pause();
                });
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + P to print
                if (e.key === 'p' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    window.print();
                }
                // Ctrl/Cmd + E to open status modal
                if (e.key === 'e' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    openStatusModal();
                }
            });

            console.log('Admin Claim Details page loaded successfully!');
        });

        // Handle image loading errors
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                if (!this.src.includes('data:image/svg+xml')) {
                    this.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2214%22%3EImage Not Found%3C/text%3E%3C/svg%3E';
                }
            });
        });
    </script>
</body>
</html>
<?php 
// Close database connection
if (isset($con)) {
    mysqli_close($con);
}
?>