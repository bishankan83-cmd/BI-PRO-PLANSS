<?php
// Include the database configuration
require 'include/config.php';

// Autoload PHPMailer (Composer)
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    die("Autoloader not found. Please run 'composer require phpmailer/phpmailer'.");
}

// If not using Composer, include manually
require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get the latest complaint
$query = "SELECT * FROM tbl_tire_complaints ORDER BY created_at DESC LIMIT 1";
$result = mysqli_query($con, $query);
if (!$result) {
    error_log("Error fetching complaint: " . mysqli_error($con));
    exit("Error fetching complaint.");
}

$complaint = mysqli_fetch_assoc($result);
if (!$complaint) {
    exit("No complaints found.");
}

// Get recipients (admins + customer service)
$emailQuery = "SELECT email, name FROM tbl_email_recipient 
               WHERE role IN ('admin', 'customer_service') AND status = 'active'";
$emailResult = mysqli_query($con, $emailQuery);
if (!$emailResult) {
    error_log("Error fetching email addresses: " . mysqli_error($con));
    exit("Error fetching email addresses.");
}

// Build recipient list
$recipients = [];
while ($row = mysqli_fetch_assoc($emailResult)) {
    if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        $recipients[] = [
            'email' => $row['email'],
            'name'  => htmlspecialchars($row['name'] ?? 'Recipient')
        ];
    }
}

if (empty($recipients)) {
    error_log("No valid email addresses found.");
    exit("No valid recipients found.");
}

// Prepare email
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'plan.atire.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'planningtool@plan.atire.com';
    $mail->Password   = 'Bishan@1919';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('planningtool@plan.atire.com', 'Atire Customer Service');

    // Add recipients
    foreach ($recipients as $recipient) {
        $mail->addAddress($recipient['email'], $recipient['name']);
    }

    // Add logo
    $logoPath = 'atire.png';
    if (file_exists($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'company_logo');
    }

    // Email subject
    $mail->Subject = '🛠 New Tire Complaint - ID: ' . $complaint['id'] . ' | Urgent Response Required';

    // Email body (HTML)
    $mail->isHTML(true);
    $mail->Body = '
        <div style="font-family: Arial, sans-serif; color: #333;">
            <h2><img src="cid:company_logo" alt="ATIRE Logo" width="120" /></h2>
            <h3>New Tire Complaint Received</h3>
            <p><strong>Complaint ID:</strong> ' . $complaint['id'] . '</p>
            <p><strong>Customer Name:</strong> ' . htmlspecialchars($complaint['customer_name']) . '</p>
            <p><strong>Tire Type:</strong> ' . htmlspecialchars($complaint['tire_type']) . '</p>
            <p><strong>Description:</strong><br>' . nl2br(htmlspecialchars($complaint['description'])) . '</p>
            <p><strong>Date:</strong> ' . $complaint['created_at'] . '</p>
            <hr>
            <p>Please log into the <a href="https://plan.atire.com">ATIRE System</a> to review this complaint.</p>
            <p style="color:#888;">This is an automated message. Please do not reply directly.</p>
        </div>
    ';

    // Optional plain text version
    $mail->AltBody = "New tire complaint received.\n\n" .
                     "Complaint ID: {$complaint['id']}\n" .
                     "Customer: {$complaint['customer_name']}\n" .
                     "Tire Type: {$complaint['tire_type']}\n" .
                     "Description: {$complaint['description']}\n";

    // Send email
    $mail->send();

    echo "✅ Complaint email sent successfully!";
} catch (Exception $e) {
    error_log("Mail Error: {$mail->ErrorInfo}");
    echo "❌ Failed to send email: {$mail->ErrorInfo}";
}
?>
