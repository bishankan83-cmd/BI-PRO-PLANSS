<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$msg = "";

if (isset($_POST['send'])) {

    $to_email = $_POST['to_email'];
    $subject  = $_POST['subject'];
    $message  = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'plan.atire.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'planningtool@plan.atire.com';   // 🔴 YOUR GMAIL
        $mail->Password   = 'Bishan@1919';     // 🔴 APP PASSWORD
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Email Content
        $mail->setFrom('planningtool@plan.atire.com', 'Bishan');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($message);

        $mail->send();
        $msg = "<p style='color:green;'>Email sent successfully ✅</p>";

    } catch (Exception $e) {
        $msg = "<p style='color:red;'>Email failed ❌ {$mail->ErrorInfo}</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All in One Email Sender</title>
    <style>
        body {
            background: #f2f2f2;
            font-family: Arial;
        }
        .box {
            width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Send Email</h2>

    <?php echo $msg; ?>

    <form method="post">
        <input type="email" name="to_email" placeholder="Recipient Email" required>
        <input type="text" name="subject" placeholder="Subject" required>
        <textarea name="message" rows="5" placeholder="Type message..." required></textarea>
        <button type="submit" name="send">Send Email</button>
    </form>
</div>

</body>
</html>
