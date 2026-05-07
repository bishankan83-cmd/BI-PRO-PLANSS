<?php
/**
 * Mini Email Sender — PHPMailer SMTP
 * Requires PHPMailer. Two options:
 *   A) Composer:  composer require phpmailer/phpmailer
 *   B) Manual:    download https://github.com/PHPMailer/PHPMailer and place the
 *                 /src folder next to this file as /PHPMailer/src/
 */

// ── Load PHPMailer ────────────────────────────────────────────────────────────
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';           // Composer
} else {
    require __DIR__ . '/phpmailer/src/Exception.php';  // Manual
    require __DIR__ . '/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/phpmailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── SMTP Config — EDIT THESE ──────────────────────────────────────────────────
define('SMTP_HOST',     'plan.atire.com');    // Gmail / or your host's SMTP
define('SMTP_PORT',     587);                 // 587=TLS  465=SSL  25=plain
define('SMTP_SECURE',   PHPMailer::ENCRYPTION_STARTTLS); // or ENCRYPTION_SMTPS
define('SMTP_USER',     'planningtool@plan.atire.com');     // your login email
define('SMTP_PASS',     'Bishan@1919'); // Gmail: use App Password (not account pw)
define('SMTP_FROM_NAME','Mini Mailer');
// ─────────────────────────────────────────────────────────────────────────────

$status = '';
$statusType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to       = trim($_POST['to']        ?? '');
    $subject  = trim($_POST['subject']   ?? '');
    $body     = trim($_POST['message']   ?? '');
    $replyTo  = trim($_POST['from']      ?? SMTP_USER);
    $fromName = trim($_POST['from_name'] ?? SMTP_FROM_NAME);

    if (empty($to) || empty($subject) || empty($body)) {
        $status = 'To, Subject, and Message are required.';
        $statusType = 'error';
    } elseif (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $status = 'Invalid recipient email address.';
        $statusType = 'error';
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_USER, $fromName);
            if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo, $fromName);
            }
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(false);

            $mail->send();
            $status = 'Email sent successfully!';
            $statusType = 'success';
        } catch (Exception $e) {
            $status = 'Send failed: ' . $mail->ErrorInfo;
            $statusType = 'error';
        }
    }
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Send Email</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #0e0e0e; --surface: #161616; --border: #2a2a2a;
    --accent: #c8ff57; --accent-dim: rgba(200,255,87,0.12);
    --text: #f0ede6; --muted: #6b6b6b;
    --error: #ff6b6b; --success: #c8ff57;
  }
  body {
    background: var(--bg); color: var(--text);
    font-family: 'DM Mono', monospace;
    min-height: 100vh; display: flex;
    align-items: center; justify-content: center; padding: 2rem 1rem;
  }
  body::before {
    content: ''; position: fixed; inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
    background-size: 40px 40px; pointer-events: none; z-index: 0;
  }
  .card {
    position: relative; z-index: 1; width: 100%; max-width: 540px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 4px; overflow: hidden;
  }
  .card-header {
    padding: 1.75rem 2rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 1rem;
  }
  .icon-wrap {
    width: 40px; height: 40px; background: var(--accent-dim);
    border: 1px solid var(--accent); border-radius: 4px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .icon-wrap svg { width: 20px; height: 20px; fill: var(--accent); }
  .card-title { font-family: 'DM Serif Display', serif; font-size: 1.4rem; font-weight: 400; }
  .card-sub { font-size: 0.65rem; color: var(--muted); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.12em; }
  .card-body { padding: 1.75rem 2rem 2rem; }
  .field { margin-bottom: 1.2rem; }
  label { display: block; font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.14em; color: var(--muted); margin-bottom: 0.4rem; }
  input, textarea {
    width: 100%; background: var(--bg); border: 1px solid var(--border);
    border-radius: 3px; color: var(--text); font-family: 'DM Mono', monospace;
    font-size: 0.85rem; padding: 0.65rem 0.85rem; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  input:focus, textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-dim); }
  textarea { resize: vertical; min-height: 130px; line-height: 1.6; }
  .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .config-note {
    font-size: 0.7rem; color: var(--muted);
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 3px; padding: 0.65rem 0.85rem; margin-bottom: 1.25rem; line-height: 1.7;
  }
  .config-note strong { color: var(--accent); }
  .btn {
    width: 100%; margin-top: 0.25rem; padding: 0.8rem 1.5rem;
    background: var(--accent); color: #0e0e0e; font-family: 'DM Mono', monospace;
    font-size: 0.8rem; font-weight: 500; text-transform: uppercase;
    letter-spacing: 0.12em; border: none; border-radius: 3px;
    cursor: pointer; transition: opacity 0.2s, transform 0.15s;
  }
  .btn:hover { opacity: 0.88; } .btn:active { transform: scale(0.98); }
  .status {
    margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 3px;
    font-size: 0.78rem; display: flex; align-items: flex-start; gap: 0.5rem;
    animation: fadeIn 0.3s ease; word-break: break-word; line-height: 1.5;
  }
  .status.success { background: rgba(200,255,87,0.08); border: 1px solid rgba(200,255,87,0.3); color: var(--success); }
  .status.error   { background: rgba(255,107,107,0.08); border: 1px solid rgba(255,107,107,0.3); color: var(--error); }
  .status svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }
  @media (max-width: 480px) {
    .row { grid-template-columns: 1fr; }
    .card-header, .card-body { padding-left: 1.25rem; padding-right: 1.25rem; }
  }
</style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <div class="icon-wrap">
      <svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4.7-8 5-8-5V6l8 5 8-5v2.7z"/></svg>
    </div>
    <div>
      <div class="card-title">Send Email</div>
      <div class="card-sub">PHPMailer · SMTP</div>
    </div>
  </div>

  <div class="card-body">
    <div class="config-note">
      ⚙️ Open this file and fill in the <strong>SMTP constants</strong> at the top before use.<br>
      Gmail: enable <strong>2-Step Verification</strong> → create an <strong>App Password</strong> at
      <strong>myaccount.google.com/apppasswords</strong> and paste it as <strong>SMTP_PASS</strong>.
    </div>

    <form method="POST" action="">
      <div class="row">
        <div class="field">
          <label for="from_name">Your Name</label>
          <input type="text" id="from_name" name="from_name"
                 placeholder="John Doe"
                 value="<?= h($_POST['from_name'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="from">Reply-To</label>
          <input type="email" id="from" name="from"
                 placeholder="you@example.com"
                 value="<?= h($_POST['from'] ?? '') ?>">
        </div>
      </div>

      <div class="field">
        <label for="to">Recipient *</label>
        <input type="email" id="to" name="to"
               placeholder="recipient@example.com"
               value="<?= h($_POST['to'] ?? '') ?>" required>
      </div>

      <div class="field">
        <label for="subject">Subject *</label>
        <input type="text" id="subject" name="subject"
               placeholder="What's this about?"
               value="<?= h($_POST['subject'] ?? '') ?>" required>
      </div>

      <div class="field">
        <label for="message">Message *</label>
        <textarea id="message" name="message"
                  placeholder="Type your message here…" required><?= h($_POST['message'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn">Send Message →</button>

      <?php if ($status): ?>
        <div class="status <?= $statusType ?>">
          <?php if ($statusType === 'success'): ?>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
          <?php else: ?>
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
          <?php endif; ?>
          <?= h($status) ?>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>
</body>
</html>