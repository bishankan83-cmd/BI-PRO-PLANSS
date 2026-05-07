<?php
// Include the database configuration
require 'include/config.php';

// Check if autoloader exists
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    die("Autoloader not found. Please run 'composer require phpmailer/phpmailer' in the project directory.");
}

// Manually include PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Query to fetch the most recent complaint data
$query = "SELECT * FROM tbl_tire_complaints ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($con, $query);
if (!$result) {
    error_log("Error fetching complaint: " . mysqli_error($con));
    header('Location: error.php?message=Error+fetching+complaint');
    exit();
}

$complaint = mysqli_fetch_assoc($result);

if (!$complaint) {
    header('Location: error.php?message=No+complaints+found');
    exit();
}

// Query to fetch email addresses from the new table
$emailQuery = "SELECT email, name FROM tbl_email_recipient WHERE role IN ('admin', 'customer_service') AND status = 'active'";
$emailResult = mysqli_query($con, $emailQuery);
if (!$emailResult) {
    error_log("Error fetching email addresses: " . mysqli_error($con));
    header('Location: error.php?message=Error+fetching+email+addresses');
    exit();
}

// Collect valid email addresses
$recipients = [];
while ($row = mysqli_fetch_assoc($emailResult)) {
    $email = filter_var($row['email'], FILTER_VALIDATE_EMAIL);
    if ($email) {
        $recipients[] = [
            'email' => $email,
            'name' => htmlspecialchars($row['name'] ?? 'Recipient')
        ];
    }
}

if (empty($recipients)) {
    error_log("No valid email addresses found in the database.");
    header('Location: error.php?message=No+valid+email+addresses+found');
    exit();
}

// Create an instance of PHPMailer
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 0; // Disable debug output in production
    $mail->isSMTP();
    $mail->Host       = 'plan.atire.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'planningtool@plan.atire.com';
    $mail->Password   = 'Bishan@1919';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Set sender
    $mail->setFrom('bishan.k@atire.com', 'Atire Customer Service');

    // Add recipients from the database
    foreach ($recipients as $recipient) {
        $mail->addAddress($recipient['email'], $recipient['name']);
    }

    // Add profile picture as embedded image (optional)
    $profilePicPath = 'atire.png'; // Update this path to your logo/profile image
    if (file_exists($profilePicPath)) {
        $mail->addEmbeddedImage($profilePicPath, 'company_logo');
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Tire Complaint - ID: ' . $complaint['id'] . ' | Urgent Response Required';

    // Build beautiful HTML email body with custom brand colors
    $mail->Body = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tire Complaint Notification</title>
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
                background-color: #F28018;
                color: #ffffff;
                padding: 15px;
                text-align: center;
                font-weight: bold;
                font-size: 18px;
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0% { opacity: 1; }
                50% { opacity: 0.8; }
                100% { opacity: 1; }
            }
            .content {
                padding: 30px;
            }
            .complaint-id {
                background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                margin-bottom: 30px;
                border-left: 5px solid #F28018;
                border: 2px solid #F28018;
            }
            .complaint-id h2 {
                margin: 0;
                color: #000000;
                font-size: 24px;
            }
            .complaint-id p {
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
            .description-box {
                background-color: #ffffff;
                border: 2px solid #F28018;
                padding: 20px;
                border-radius: 8px;
                margin: 15px 0;
            }
            .status-badge {
                display: inline-block;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .status-new {
                background-color: #F28018;
                color: #ffffff;
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
                        <h1>ATIRE</h1>
                        <p>Customer Service Department</p>
                    </div>
                </div>
            </div>
            
            <div class="urgent-banner">
                ⚠️ URGENT: New Customer Complaint Requires Immediate Attention
            </div>
            
            <div class="content">
                <div class="complaint-id">
                    <h2>🎫 Complaint ID: ' . htmlspecialchars($complaint['id']) . '</h2>
                    <p class="secondary-text">Submitted: ' . date('F j, Y \a\t g:i A', strtotime($complaint['created_at'])) . '</p>
                </div>
                
                <div class="section">
                    <h3><span class="section-icon">👤</span>Customer Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">User ID</div>
                            <div class="info-value">' . htmlspecialchars($complaint['userId']) . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3><span class="section-icon">🚗</span>Tire & Vehicle Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Serial Number</div>
                            <div class="info-value highlight-text">' . htmlspecialchars($complaint['serial_number']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tire Size</div>
                            <div class="info-value">' . htmlspecialchars($complaint['tire_size']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Vehicle Make/Model</div>
                            <div class="info-value">' . htmlspecialchars($complaint['vehicle_make_model']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Vehicle Year</div>
                            <div class="info-value">' . htmlspecialchars($complaint['vehicle_year'] ?? 'Not specified') . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3><span class="section-icon">🛒</span>Purchase Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Purchase Date</div>
                            <div class="info-value highlight-text">' . htmlspecialchars($complaint['purchase_date']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Purchase Location</div>
                            <div class="info-value">' . htmlspecialchars($complaint['purchase_location'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Invoice Number</div>
                            <div class="info-value">' . htmlspecialchars($complaint['invoice_number'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Warranty Period</div>
                            <div class="info-value">' . htmlspecialchars($complaint['warranty_period'] ?? 'Not specified') . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3><span class="section-icon">⚠️</span>Complaint Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Nature of Complaint</div>
                            <div class="info-value highlight-text">' . htmlspecialchars($complaint['nature_complaint']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Usage Type</div>
                            <div class="info-value">' . htmlspecialchars($complaint['usage_type']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Impact</div>
                            <div class="info-value">' . htmlspecialchars($complaint['impact'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="status-badge status-new">' . htmlspecialchars($complaint['status'] ?? 'New') . '</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="description-box">
                        <div class="info-label">Detailed Description</div>
                        <div class="info-value">' . nl2br(htmlspecialchars($complaint['detailed_description'])) . '</div>
                    </div>
                </div>
                
                <div class="section">
                    <h3><span class="section-icon">🔧</span>Technical Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Mileage/Hours</div>
                            <div class="info-value">' . htmlspecialchars($complaint['mileage_hours'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Duration Before Problem</div>
                            <div class="info-value">' . htmlspecialchars($complaint['duration_before_problem'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Operating Conditions</div>
                            <div class="info-value">' . htmlspecialchars($complaint['operating_conditions']) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Surface Conditions</div>
                            <div class="info-value">' . htmlspecialchars($complaint['surface_conditions'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Temperature Conditions</div>
                            <div class="info-value">' . htmlspecialchars($complaint['temperature_conditions'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Speed Operation</div>
                            <div class="info-value">' . htmlspecialchars($complaint['speed_operation'] ?? 'Not specified') . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3><span class="section-icon">💡</span>Resolution Request</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Resolution Requested</div>
                            <div class="info-value">' . htmlspecialchars($complaint['resolution_requested'] ?? 'Not specified') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Previous Actions</div>
                            <div class="info-value">' . htmlspecialchars($complaint['previous_actions'] ?? 'None') . '</div>
                        </div>
                    </div>
                    
                    ' . (!empty($complaint['additional_comments']) ? '
                    <div class="description-box">
                        <div class="info-label">Additional Comments</div>
                        <div class="info-value">' . nl2br(htmlspecialchars($complaint['additional_comments'])) . '</div>
                    </div>
                    ' : '') . '
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="https://plan.atire.com/respond?complaint_id=' . htmlspecialchars($complaint['id']) . '" class="action-button">🚀 Respond Now</a>
                    <a href="https://plan.atire.com/dashboard" class="action-button">📋 View Dashboard</a>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>ATIRE Customer Service Team</strong></p>
                <p>📧 <span class="highlight">planningtool@plan.atire.com</span> | 🌐 <span class="highlight">www.atire.com</span></p>
                <p style="font-size: 12px; opacity: 0.8; margin-top: 15px;" class="secondary-text">
                    This is an automated notification. Please respond promptly to maintain our service excellence standards.
                </p>
            </div>
        </div>
    </body>
    </html>';

    // Plain text version for email clients that don't support HTML
    $mail->AltBody = "URGENT: New Tire Complaint Notification\n\n" .
                    "Complaint ID: " . $complaint['id'] . "\n" .
                    "User ID: " . $complaint['userId'] . "\n" .
                    "Serial Number: " . $complaint['serial_number'] . "\n" .
                    "Nature: " . $complaint['nature_complaint'] . "\n" .
                    "Description: " . $complaint['detailed_description'] . "\n" .
                    "Created: " . $complaint['created_at'] . "\n\n" .
                    "Please log in to the dashboard immediately to respond to this complaint.";

    // Attach complaint file if it exists
    if (!empty($complaint['complaint_file']) && file_exists($complaint['complaint_file'])) {
        $mail->addAttachment($complaint['complaint_file'], 'Complaint_Document_' . $complaint['id'] . '.pdf');
    }

    // Send the email
    $mail->send();
    
    // Log successful email sending
    error_log("Complaint notification email sent successfully for ID: " . $complaint['id']);
    
    // Redirect to success page after email is sent
    header('Location: dashboard.php?message=Email+sent+successfully');
    exit();

} catch (Exception $e) {
    // Log the error instead of echoing
    error_log("Mailer Error: {$mail->ErrorInfo}");
    
    // Redirect to error page
    header('Location: error.php?message=Mailer+Error:+' . urlencode($mail->ErrorInfo));
    exit();

} finally {
    // Close database connection
    mysqli_close($con);
}
?>