<?php
/**
 * EmailHandler.php
 * Separate email handling class for complaint management system
 * Place this file in your include/ directory
 */

class EmailHandler {
    
    // Email configuration constants
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $smtpFromEmail;
    private $smtpFromName;
    private $logFile;
    
    /**
     * Constructor - Initialize email configuration
     */
    public function __construct($config = []) {
        // Set default configuration
        $this->smtpHost = $config['smtp_host'] ?? 'plan.atire.com';
        $this->smtpPort = $config['smtp_port'] ?? 465;
        $this->smtpUsername = $config['smtp_username'] ?? 'planningtool@plan.atire.com';
        $this->smtpPassword = $config['smtp_password'] ?? 'Bishan@1919';
        $this->smtpFromEmail = $config['smtp_from_email'] ?? 'planningtool@plan.atire.com';
        $this->smtpFromName = $config['smtp_from_name'] ?? 'Tire Complaint Management System';
        $this->logFile = $config['log_file'] ?? 'email_logs.txt';
    }
    
    /**
     * Main function to send status update email
     * 
     * @param string $userEmail - Recipient email address
     * @param string $userName - Recipient name
     * @param string $complaintNumber - Complaint number
     * @param string $status - New status
     * @param string $remark - Admin remark
     * @return bool - True if email sent/logged successfully, false otherwise
     */
    public function sendStatusUpdateEmail($userEmail, $userName, $complaintNumber, $status, $remark) {
        try {
            // Validate inputs
            if (!$this->validateInputs($userEmail, $userName, $complaintNumber, $status, $remark)) {
                error_log("Email validation failed for complaint #$complaintNumber");
                return false;
            }
            
            // Check if PHPMailer is available
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendViaPHPMailer($userEmail, $userName, $complaintNumber, $status, $remark);
            } else {
                // Fallback: Log email to file
                return $this->logEmailToFile($userEmail, $userName, $complaintNumber, $status, $remark);
            }
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            // Still log the email even if sending fails
            return $this->logEmailToFile($userEmail, $userName, $complaintNumber, $status, $remark);
        }
    }
    
    /**
     * Validate email inputs
     */
    private function validateInputs($userEmail, $userName, $complaintNumber, $status, $remark) {
        if (empty($userEmail) || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (empty($userName) || empty($complaintNumber) || empty($status) || empty($remark)) {
            return false;
        }
        return true;
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendViaPHPMailer($userEmail, $userName, $complaintNumber, $status, $remark) {
        try {
            require_once 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->smtpPort;
            
            // Optional: Disable SSL verification (use only if having certificate issues)
            // $mail->SMTPOptions = array(
            //     'ssl' => array(
            //         'verify_peer' => false,
            //         'verify_peer_name' => false,
            //         'allow_self_signed' => true
            //     )
            // );
            
            // Recipients
            $mail->setFrom($this->smtpFromEmail, $this->smtpFromName);
            $mail->addAddress($userEmail, $userName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->getEmailSubject($status, $complaintNumber);
            $mail->Body = $this->getEmailBody($userName, $complaintNumber, $status, $remark);
            $mail->AltBody = $this->getPlainTextBody($userName, $complaintNumber, $status, $remark);
            
            $mail->send();
            
            // Also log the email
            $this->logEmailToFile($userEmail, $userName, $complaintNumber, $status, $remark, 'SENT');
            
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            // Log the failed attempt
            $this->logEmailToFile($userEmail, $userName, $complaintNumber, $status, $remark, 'FAILED');
            return false;
        }
    }
    
    /**
     * Log email to file (fallback or backup)
     */
    private function logEmailToFile($userEmail, $userName, $complaintNumber, $status, $remark, $sendStatus = 'LOGGED') {
        try {
            $emailLog = sprintf(
                "%s\n" .
                "Status: %s\n" .
                "To: %s (%s)\n" .
                "Complaint #: %s\n" .
                "Subject: %s\n" .
                "New Status: %s\n" .
                "Remark: %s\n" .
                "%s\n\n",
                str_repeat('=', 80),
                $sendStatus,
                $userName,
                $userEmail,
                $complaintNumber,
                $this->getEmailSubject($status, $complaintNumber),
                $status,
                $remark,
                date('Y-m-d H:i:s')
            );
            
            file_put_contents($this->logFile, $emailLog, FILE_APPEND);
            return true;
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get email subject based on status
     */
    private function getEmailSubject($status, $complaintNumber) {
        $statusSubjects = [
            'In process' => 'Your Complaint is Being Processed',
            'Proceed with Decision' => 'Action Required on Your Complaint',
            'closed' => 'Your Complaint Has Been Resolved'
        ];
        
        $subject = isset($statusSubjects[$status]) ? $statusSubjects[$status] : 'Complaint Status Update';
        return $subject . ' - Complaint #' . $complaintNumber;
    }
    
    /**
     * Get HTML email body
     */
    private function getEmailBody($userName, $complaintNumber, $status, $remark) {
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
                    <h1>🔔 Complaint Status Update</h1>
                </div>
                
                <div class="email-body">
                    <p>Dear <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                    
                    <p>We are writing to inform you about an update to your tire complaint.</p>
                    
                    <div class="info-box">
                        <strong>Complaint Number:</strong> ' . htmlspecialchars($complaintNumber) . '<br>
                        <strong>Date:</strong> ' . date('F j, Y g:i A') . '
                    </div>
                    
                    <p><strong>Current Status:</strong></p>
                    <div class="status-badge">' . htmlspecialchars($status) . '</div>
                    
                    <div class="remark-box">
                        <h3>📝 Admin Remarks:</h3>
                        <p>' . nl2br(htmlspecialchars($remark)) . '</p>
                    </div>
                    
                    ' . $this->getStatusMessage($status) . '
                    
                    <p>If you have any questions or concerns, please don\'t hesitate to contact us.</p>
                    
                    <p>Best regards,<br>
                    <strong>Customer Support Team</strong><br>
                    Tire Complaint Management System</p>
                </div>
                
                <div class="email-footer">
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; ' . date('Y') . ' Tire Complaint Management System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        return $message;
    }
    
    /**
     * Get plain text email body (alternative)
     */
    private function getPlainTextBody($userName, $complaintNumber, $status, $remark) {
        $message = "Complaint Status Update\n\n";
        $message .= "Dear " . $userName . ",\n\n";
        $message .= "We are writing to inform you about an update to your tire complaint.\n\n";
        $message .= "Complaint Number: " . $complaintNumber . "\n";
        $message .= "Date: " . date('F j, Y g:i A') . "\n\n";
        $message .= "Current Status: " . $status . "\n\n";
        $message .= "Admin Remarks:\n" . $remark . "\n\n";
        $message .= $this->getStatusMessagePlain($status) . "\n\n";
        $message .= "If you have any questions or concerns, please don't hesitate to contact us.\n\n";
        $message .= "Best regards,\n";
        $message .= "Customer Support Team\n";
        $message .= "Tire Complaint Management System\n\n";
        $message .= "---\n";
        $message .= "This is an automated message. Please do not reply to this email.\n";
        $message .= "© " . date('Y') . " Tire Complaint Management System. All rights reserved.";
        
        return $message;
    }
    
    /**
     * Get status-specific HTML message
     */
    private function getStatusMessage($status) {
        switch($status) {
            case 'In process':
                return '<p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                        <strong>⏳ In Process:</strong> Your complaint is currently being reviewed by our team. 
                        We are investigating the matter and will update you with our findings soon.
                        </p>';
            case 'Proceed with Decision':
                return '<p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #f39c12;">
                        <strong>⚠️ Action Required:</strong> We have reviewed your complaint and are proceeding with a decision. 
                        Please check your complaint details for more information and any required action from your side.
                        </p>';
            case 'closed':
                return '<p style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #27ae60;">
                        <strong>✅ Closed:</strong> Your complaint has been resolved. 
                        Thank you for your patience. If you have any further concerns, please feel free to submit a new complaint.
                        </p>';
            default:
                return '<p>Your complaint status has been updated. Please review the details above.</p>';
        }
    }
    
    /**
     * Get status-specific plain text message
     */
    private function getStatusMessagePlain($status) {
        switch($status) {
            case 'In process':
                return "In Process: Your complaint is currently being reviewed by our team. We are investigating the matter and will update you with our findings soon.";
            case 'Proceed with Decision':
                return "Action Required: We have reviewed your complaint and are proceeding with a decision. Please check your complaint details for more information and any required action from your side.";
            case 'closed':
                return "Closed: Your complaint has been resolved. Thank you for your patience. If you have any further concerns, please feel free to submit a new complaint.";
            default:
                return "Your complaint status has been updated. Please review the details above.";
        }
    }
    
    /**
     * Test email configuration
     * 
     * @return array - Result with success status and message
     */
    public function testEmailConfiguration() {
        try {
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return [
                    'success' => false,
                    'message' => 'PHPMailer not found. Emails will be logged to file instead.'
                ];
            }
            
            require_once 'vendor/autoload.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $this->smtpPort;
            
            // Try to connect
            $mail->smtpConnect();
            
            return [
                'success' => true,
                'message' => 'SMTP configuration is valid and connection successful.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP configuration test failed: ' . $e->getMessage()
            ];
        }
    }
}
?>