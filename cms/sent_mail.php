<?php
// Check if autoloader exists
if (file_exists('vendor/autoload.php')) {
    echo "Autoloader found!";
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

// Create an instance
$mail = new PHPMailer(true);


// Check if PHPMailer class exists
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("Error: PHPMailer class not found. Please ensure PHPMailer is installed correctly via Composer ('composer require phpmailer/phpmailer').");
}


try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'plan.atire.com';                     // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'planningtool@plan.atire.com';                     // SMTP username
    $mail->Password   = 'Bishan@1919';                               // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
    $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    // Recipients
    $mail->setFrom('bishankan83@gamil.com', 'Bi Pro Plan s');
            // Add a recipient
  
       // Optional name

    // Content
    $mail->isHTML(true);                                        // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}      