<?php
// Suppress PHP notices/warnings from polluting JSON responses
error_reporting(0);
ini_set('display_errors', '0');
ob_start(); // Buffer all output so stray whitespace/errors don't break JSON

// Database configuration
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

function sendRevisionRequestEmail($orderId, $curatorName, $curatorNumber, $conn) {
    $mail = new PHPMailer(true);
    try {
        $emailQuery = "SELECT email, name FROM tbl_email_recipient 
                       WHERE role IN ('admin', 'customer_service') AND status = 'active'";
        $emailResult = $conn->query($emailQuery);
        if (!$emailResult) throw new Exception("Error fetching email addresses");
        $recipients = [];
        while ($row = $emailResult->fetch(PDO::FETCH_ASSOC)) {
            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $recipients[] = ['email' => $row['email'], 'name' => htmlspecialchars($row['name'] ?? 'Recipient')];
            }
        }
        if (empty($recipients)) throw new Exception("No valid recipients found");
        $mail->isSMTP();
        $mail->Host       = 'plan.atire.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'planningtool@plan.atire.com';
        $mail->Password   = 'Bishan@1919';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('planningtool@plan.atire.com', 'ATIRE Order System');
        foreach ($recipients as $recipient) $mail->addAddress($recipient['email'], $recipient['name']);
        $logoPath = 'atire.png';
        if (file_exists($logoPath)) $mail->addEmbeddedImage($logoPath, 'company_logo');
        $mail->Subject = '🔄 Order Revision Request - Order ID: ' . $orderId . ' | Action Required';
        $mail->isHTML(true);
        $mail->Body = '
            <div style="font-family:\'SF UI Display\',-apple-system,BlinkMacSystemFont,sans-serif;color:#333;max-width:600px;margin:0 auto;">
                ' . (file_exists($logoPath) ? '<div style="text-align:center;margin-bottom:20px;"><img src="cid:company_logo" alt="ATIRE Logo" width="150"/></div>' : '') . '
                <div style="background:#f28018;padding:20px;border-radius:12px 12px 0 0;">
                    <h2 style="color:white;margin:0;text-align:center;font-weight:900;letter-spacing:-.02em;">🔄 New Order Revision Request</h2>
                </div>
                <div style="background:#f9f9f9;padding:30px;border:1px solid #e4e4e4;border-top:none;border-radius:0 0 12px 12px;">
                    <p style="font-size:15px;line-height:1.6;color:#555;">A customer has requested a revision for their tire order. Please review and process this request as soon as possible.</p>
                    <div style="background:white;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #f28018;">
                        <h3 style="color:#f28018;margin-top:0;font-weight:800;">Order Details</h3>
                        <table style="width:100%;border-collapse:collapse;">
                            <tr><td style="padding:10px 0;border-bottom:1px solid #e4e4e4;"><strong>Order ID:</strong></td><td style="padding:10px 0;border-bottom:1px solid #e4e4e4;text-align:right;color:#f28018;font-size:18px;font-weight:900;">'.htmlspecialchars($orderId).'</td></tr>
                            <tr><td style="padding:10px 0;border-bottom:1px solid #e4e4e4;"><strong>Curator Name:</strong></td><td style="padding:10px 0;border-bottom:1px solid #e4e4e4;text-align:right;">'.htmlspecialchars($curatorName).'</td></tr>
                            <tr><td style="padding:10px 0;"><strong>Curator Number:</strong></td><td style="padding:10px 0;text-align:right;">'.htmlspecialchars($curatorNumber).'</td></tr>
                        </table>
                    </div>
                    <div style="background:rgba(242,128,24,0.08);border:1px solid rgba(242,128,24,0.25);padding:15px;border-radius:8px;margin:20px 0;">
                        <p style="margin:0;color:#7a4400;font-weight:600;"><strong>⚠️ Action Required:</strong> Please log into the system to review and approve this revision request.</p>
                    </div>
                    <div style="text-align:center;margin:30px 0;">
                        <a href="https://plan.atire.com" style="background:#f28018;color:white;padding:14px 38px;text-decoration:none;border-radius:8px;font-weight:900;display:inline-block;letter-spacing:.04em;text-transform:uppercase;font-size:13px;">Access ATIRE System</a>
                    </div>
                    <hr style="border:none;border-top:1px solid #e4e4e4;margin:30px 0;">
                    <p style="font-size:12px;color:#aaa;text-align:center;margin:0;">This is an automated notification from the ATIRE Order Management System.<br>Please do not reply directly to this email.</p>
                </div>
            </div>';
        $mail->AltBody = "New Order Revision Request Received\n\nOrder ID: {$orderId}\nCurator Name: {$curatorName}\nCurator Number: {$curatorNumber}\n\nPlease log into the ATIRE system at https://plan.atire.com to review this request.\n\nThis is an automated message. Please do not reply directly.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        ob_clean(); // Discard any stray output before JSON
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        try {
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Invalid JSON: ' . json_last_error_msg());
            if (!isset($input['order_id'])) throw new Exception('Order ID is required');
            $orderId = $input['order_id'];
            $requestStatus = 'request_revise';
            $orderStmt = $conn->prepare("SELECT order_id FROM tire_orders WHERE order_id = :order_id");
            $orderStmt->execute([':order_id' => $orderId]);
            $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
            if (!$orderData) throw new Exception('Order not found with ID: ' . $orderId);
            $curatorName   = $orderData['curator_name']   ?? 'N/A';
            $curatorNumber = $orderData['curator_number'] ?? 'N/A';
            $stmt = $conn->prepare("UPDATE tire_orders SET request_status = :request_status, updated_at = CURRENT_TIMESTAMP WHERE order_id = :order_id");
            $stmt->execute([':request_status' => $requestStatus, ':order_id' => $orderId]);
            if ($stmt->rowCount() > 0) {
                $emailSent = sendRevisionRequestEmail($orderId, $curatorName, $curatorNumber, $conn);
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully', 'order_id' => $orderId, 'request_status' => $requestStatus, 'email_sent' => $emailSent, 'curator_name' => $curatorName, 'curator_number' => $curatorNumber]);
            } else {
                $checkStmt = $conn->prepare("SELECT order_id, request_status FROM tire_orders WHERE order_id = :order_id");
                $checkStmt->execute([':order_id' => $orderId]);
                $existingOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($existingOrder && $existingOrder['request_status'] === $requestStatus) {
                    $emailSent = sendRevisionRequestEmail($orderId, $curatorName, $curatorNumber, $conn);
                    echo json_encode(['success' => true, 'message' => 'Order already has this status, notification sent', 'order_id' => $orderId, 'request_status' => $requestStatus, 'email_sent' => $emailSent, 'curator_name' => $curatorName, 'curator_number' => $curatorNumber]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Order not found with ID: ' . $orderId]);
                }
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Order Revision — ATIRE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ─── SF UI DISPLAY FONT FACES ───────────────────────────────────────────── */
@font-face { font-family:'SF UI Display'; font-weight:500; font-style:normal; src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:600; font-style:normal; src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:700; font-style:normal; src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:800; font-style:normal; src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype'); }
@font-face { font-family:'SF UI Display'; font-weight:900; font-style:normal; src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype'); }

/* ─── CSS VARIABLES ──────────────────────────────────────────────────────── */
:root {
    --orange:      #f28018;
    --orange-dk:   #d06e10;
    --orange-lt:   rgba(242,128,24,0.10);
    --orange-glow: rgba(242,128,24,0.18);
    --gray-50:     #f9f9f9;
    --gray-100:    #f2f2f2;
    --gray-200:    #e4e4e4;
    --gray-300:    #d0d0d0;
    --gray-400:    #b0b0b0;
    --gray-500:    #888888;
    --gray-700:    #444444;
    --gray-900:    #1a1a1a;
    --white:       #ffffff;
    --bg:          #f3f4f6;
    --font:       'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --radius-xs:   4px;
    --radius-sm:   8px;
    --radius-md:   12px;
    --radius-lg:   16px;
    --shadow-sm:   0 1px 6px rgba(0,0,0,0.06);
    --shadow:      0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:   0 6px 28px rgba(0,0,0,0.12);
    --shadow-lg:   0 12px 48px rgba(0,0,0,0.14);
    --trans:       0.18s cubic-bezier(0.4,0,0.2,1);
    --hdr-h:       60px;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--gray-700);
    min-height: 100vh;
    overflow-x: hidden;
    font-size: 13.5px;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
}

/* ─── SCROLLBAR ──────────────────────────────────────────────────────────── */
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--gray-300); border-radius:99px; }
::-webkit-scrollbar-thumb:hover { background:var(--orange); }

/* ─── HEADER ─────────────────────────────────────────────────────────────── */
.hdr {
    position:sticky; top:0; z-index:400;
    background: var(--white);
    border-bottom: 2.5px solid var(--orange);
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    height: var(--hdr-h);
}
.hdr-inner {
    max-width:1800px; margin:0 auto;
    padding:0 1.8rem;
    height:100%;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
.brand-logo { height:30px; width:auto; }
.hdr-right { display:flex; align-items:center; gap:8px; }
.hdr-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 15px; border-radius:var(--radius-sm);
    font-family:var(--font); font-weight:700; font-size:12px; letter-spacing:.03em;
    text-decoration:none;
    border:1.5px solid var(--gray-200);
    background:var(--white); color:var(--gray-500);
    cursor:pointer; transition:var(--trans);
}
.hdr-btn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }

/* ─── PAGE SHELL ─────────────────────────────────────────────────────────── */
.page-shell {
    min-height: calc(100vh - var(--hdr-h));
    display:flex; align-items:center; justify-content:center;
    padding: 2.5rem 1.5rem 4rem;
}

/* ─── CARD ───────────────────────────────────────────────────────────────── */
.card {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    width: 100%;
    max-width: 520px;
    overflow: hidden;
    border: 1.5px solid var(--gray-200);
    animation: fadeUp .4s cubic-bezier(.4,0,.2,1);
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}

/* ─── CARD HERO ──────────────────────────────────────────────────────────── */
.card-hero {
    background: var(--orange);
    padding: 2rem 2rem 1.6rem;
    position: relative;
    overflow: hidden;
}
.card-hero::before {
    content:'';
    position:absolute; inset:0;
    background: radial-gradient(ellipse at top right, rgba(255,255,255,0.12) 0%, transparent 65%);
}
.card-hero-eyebrow {
    font-size:9px; font-weight:800; color:rgba(255,255,255,0.70);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:6px;
    display:flex; align-items:center; gap:6px; position:relative;
}
.card-hero-eyebrow::before {
    content:''; width:16px; height:2px;
    background:rgba(255,255,255,0.6); border-radius:2px;
}
.card-hero-title {
    font-size:clamp(22px,4vw,30px); font-weight:900;
    color:var(--white); letter-spacing:-.02em; line-height:1.1;
    position:relative;
}
.card-hero-sub {
    font-size:12px; font-weight:500;
    color:rgba(255,255,255,0.72); margin-top:6px; position:relative;
}
.card-hero-icon {
    position:absolute; right:1.8rem; top:50%; transform:translateY(-50%);
    opacity:0.12; font-size:5rem; color:var(--white);
    pointer-events:none;
}

/* ─── CARD BODY ──────────────────────────────────────────────────────────── */
.card-body { padding:1.8rem 2rem 2rem; }

/* ─── ORDER ID BADGE ─────────────────────────────────────────────────────── */
.order-id-row {
    display:flex; align-items:center; justify-content:space-between;
    background: var(--gray-50);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 1rem 1.2rem;
    margin-bottom: 1.4rem;
}
.order-id-label {
    font-size:9.5px; font-weight:800; color:var(--gray-400);
    text-transform:uppercase; letter-spacing:.12em;
    display:flex; align-items:center; gap:5px;
    margin-bottom:2px;
}
.order-id-label i { color:var(--orange); font-size:9px; }
.order-id-value {
    font-size:22px; font-weight:900;
    color:var(--orange); letter-spacing:-.01em; line-height:1;
}
.order-id-dot {
    width:10px; height:10px; border-radius:50%;
    background:var(--gray-200); flex-shrink:0;
    animation:pulse 2s infinite;
}
.order-id-dot.active { background:var(--orange); }
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }

/* ─── INFO STRIP ─────────────────────────────────────────────────────────── */
.info-strip {
    background: rgba(242,128,24,0.07);
    border: 1px solid rgba(242,128,24,0.20);
    border-left: 3px solid var(--orange);
    border-radius: var(--radius-sm);
    padding: 10px 13px;
    font-size:12px; font-weight:600; color:#7a4400;
    margin-bottom: 1.4rem;
    display:flex; align-items:flex-start; gap:8px; line-height:1.5;
}
.info-strip i { color:var(--orange); margin-top:1px; flex-shrink:0; font-size:11px; }

/* ─── SUBMIT BUTTON ──────────────────────────────────────────────────────── */
.btn-submit {
    width:100%; padding:13px 18px;
    border:none; border-radius:var(--radius-sm);
    background: var(--orange); color:var(--white);
    font-family:var(--font); font-size:13.5px; font-weight:900;
    letter-spacing:.07em; text-transform:uppercase;
    cursor:pointer;
    display:flex; align-items:center; justify-content:center; gap:9px;
    transition:var(--trans);
    box-shadow: 0 4px 18px rgba(242,128,24,0.32);
}
.btn-submit:hover:not(:disabled) {
    background: var(--orange-dk);
    transform: translateY(-2px);
    box-shadow: 0 7px 24px rgba(242,128,24,0.42);
}
.btn-submit:disabled {
    background: var(--gray-300); color:var(--gray-500);
    cursor:not-allowed; transform:none; box-shadow:none;
}
.btn-submit.success {
    background:#16a34a; box-shadow:0 4px 18px rgba(22,163,74,0.28);
}
.btn-submit.success:hover { background:#15803d; }

/* ─── SPINNER ────────────────────────────────────────────────────────────── */
.spinner {
    width:16px; height:16px;
    border:2.5px solid rgba(255,255,255,0.35);
    border-top-color:var(--white);
    border-radius:50%;
    animation:spin .65s linear infinite; flex-shrink:0;
}
@keyframes spin { to { transform:rotate(360deg); } }

/* ─── SUCCESS STATE ──────────────────────────────────────────────────────── */
.success-panel {
    display:none;
    margin-top:1.2rem;
    border-radius: var(--radius-sm);
    overflow:hidden;
    border: 1.5px solid #bbf7d0;
    animation: fadeUp .35s ease;
}
.success-panel.show { display:block; }
.success-panel-hdr {
    background:#dcfce7; padding:10px 14px;
    display:flex; align-items:center; gap:8px;
    font-size:12.5px; font-weight:800; color:#166534;
}
.success-panel-hdr i { color:#16a34a; font-size:14px; }
.success-panel-body {
    background:#f0fdf4; padding:12px 14px;
    font-size:12px; font-weight:600; color:#166534; line-height:1.65;
}
.success-detail-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:5px 0; border-bottom:1px solid #bbf7d0;
    font-size:11.5px;
}
.success-detail-row:last-child { border-bottom:none; }
.success-detail-row span:first-child { color:#4ade80; font-weight:700; }
.success-detail-row span:last-child  { color:#166534; font-weight:800; }
.email-badge {
    display:inline-flex; align-items:center; gap:5px;
    background:#dcfce7; border:1px solid #86efac;
    border-radius:20px; padding:3px 10px;
    font-size:10px; font-weight:800; color:#15803d;
    margin-top:8px;
}
.redirect-bar {
    background:#f0fdf4; border-top:1px solid #bbf7d0;
    padding:8px 14px;
    display:flex; align-items:center; gap:6px;
    font-size:11.5px; font-weight:600; color:#166534;
}
.redirect-bar i { color:#4ade80; font-size:10px; }

/* ─── ERROR STATE ────────────────────────────────────────────────────────── */
.error-panel {
    display:none;
    margin-bottom:1.2rem;
    border-radius: var(--radius-sm);
    background:rgba(200,50,50,0.06);
    border: 1px solid rgba(200,50,50,0.20);
    border-left:3px solid #e05555;
    padding:10px 13px;
    font-size:12.5px; font-weight:600; color:#7a1a1a;
    display:none; align-items:center; gap:8px; line-height:1.5;
    animation: fadeUp .3s ease;
}
.error-panel.show { display:flex; }
.error-panel i { color:#e05555; flex-shrink:0; }

/* ─── DIVIDER ────────────────────────────────────────────────────────────── */
.sb-divider { height:1px; background:var(--gray-100); margin:1.2rem 0; }

/* ─── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:600px) {
    .card-body { padding:1.4rem 1.2rem 1.6rem; }
    .card-hero { padding:1.6rem 1.2rem 1.3rem; }
    .card-hero-icon { display:none; }
}
</style>
</head>
<body>

<!-- ════════════════════════════════ HEADER ═══════════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-right">
            <a href="dashboard.php" class="hdr-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </div>
</header>

<!-- ════════════════════════════════ PAGE SHELL ═══════════════════════════ -->
<div class="page-shell">
    <div class="card">

        <!-- Hero -->
        <div class="card-hero">
            <div class="card-hero-eyebrow">Order Management</div>
            <div class="card-hero-title">Request Revision</div>
            <div class="card-hero-sub">Submit a revision request — our team will be notified immediately.</div>
            <i class="fas fa-rotate card-hero-icon"></i>
        </div>

        <!-- Body -->
        <div class="card-body">

            <!-- Error panel -->
            <div class="error-panel" id="errorPanel">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText"></span>
            </div>

            <!-- Order ID display -->
            <div class="order-id-row">
                <div>
                    <div class="order-id-label"><i class="fas fa-hashtag"></i>Order ID</div>
                    <div class="order-id-value" id="orderIdDisplay">—</div>
                </div>
                <div class="order-id-dot" id="orderDot"></div>
            </div>

            <!-- Info notice -->
            <div class="info-strip">
                <i class="fas fa-info-circle"></i>
                Submitting this request will notify the ATIRE marketing team. A final confirmed price will be
                provided in your <strong>Proforma Invoice</strong>.
            </div>

            <div class="sb-divider"></div>

            <!-- Submit button -->
            <button class="btn-submit" id="revisionButton" onclick="requestRevision()">
                <i class="fas fa-rotate" id="btnIcon"></i>
                <span id="btnLabel">Request Revision</span>
            </button>

            <!-- Success panel -->
            <div class="success-panel" id="successPanel">
                <div class="success-panel-hdr">
                    <i class="fas fa-circle-check"></i>
                    Revision Request Received Successfully
                </div>
                <div class="success-panel-body">
                    <p style="margin-bottom:10px;">The ATIRE team has been notified and will process your request shortly.</p>
                    <div id="curatorDetails" style="display:none;">
                        <div class="success-detail-row">
                            <span>Curator</span>
                            <span id="curatorNameVal">—</span>
                        </div>
                        <div class="success-detail-row">
                            <span>Reference</span>
                            <span id="curatorNumberVal">—</span>
                        </div>
                    </div>
                    <div id="emailBadge" style="display:none;">
                        <span class="email-badge"><i class="fas fa-envelope"></i> Email notification sent to ATIRE team</span>
                    </div>
                </div>
                <div class="redirect-bar" id="redirectBar" style="display:none;">
                    <i class="fas fa-arrow-right"></i>
                    <span id="redirectText">Redirecting to dashboard…</span>
                </div>
            </div>

        </div><!-- /card-body -->
    </div><!-- /card -->
</div><!-- /page-shell -->

<!-- ════════════════════════════════════ JS ══════════════════════════════ -->
<script>
function getOrderIdFromUrl() {
    return new URLSearchParams(window.location.search).get('oid');
}

window.addEventListener('DOMContentLoaded', function () {
    const orderId    = getOrderIdFromUrl();
    const errorPanel = document.getElementById('errorPanel');
    const errorText  = document.getElementById('errorText');
    const dot        = document.getElementById('orderDot');

    if (orderId) {
        document.getElementById('orderIdDisplay').textContent = '#' + orderId;
        dot.classList.add('active');
    } else {
        errorText.textContent = 'No order ID found in URL. Please access this page with ?oid=ORDER_ID';
        errorPanel.classList.add('show');
        document.getElementById('revisionButton').disabled = true;
        dot.classList.remove('active');
    }
});

async function requestRevision() {
    const button       = document.getElementById('revisionButton');
    const btnIcon      = document.getElementById('btnIcon');
    const btnLabel     = document.getElementById('btnLabel');
    const successPanel = document.getElementById('successPanel');
    const errorPanel   = document.getElementById('errorPanel');
    const errorText    = document.getElementById('errorText');
    const orderId      = getOrderIdFromUrl();

    // Reset
    errorPanel.classList.remove('show');
    successPanel.classList.remove('show');

    if (!orderId) {
        errorText.textContent = 'Order ID is missing.';
        errorPanel.classList.add('show');
        return;
    }

    // Loading state
    button.disabled = true;
    btnIcon.className = '';
    btnIcon.innerHTML = '<span class="spinner" style="display:inline-block;"></span>';
    btnLabel.textContent = 'Processing…';

    try {
        const response = await fetch(window.location.href.split('?')[0], {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, request_status: 'request_revise' })
        });

        let data;
        try { data = await response.json(); }
        catch (e) {
            // Show the raw server response to help diagnose the problem
            const raw = await response.text().catch(() => '(unreadable)');
            throw new Error('Server returned non-JSON. Raw response: ' + raw.substring(0, 300));
        }

        if (data.success) {
            // Populate curator details
            if (data.curator_name && data.curator_number) {
                document.getElementById('curatorNameVal').textContent   = data.curator_name;
                document.getElementById('curatorNumberVal').textContent = data.curator_number;
                document.getElementById('curatorDetails').style.display = 'block';
            }
            if (data.email_sent) {
                document.getElementById('emailBadge').style.display = 'block';
            }

            // Success button state
            button.classList.add('success');
            button.disabled = false;
            btnIcon.innerHTML = '';
            btnIcon.className = 'fas fa-circle-check';
            btnLabel.textContent = 'Request Sent Successfully';

            successPanel.classList.add('show');

            // Countdown redirect
            const redirectBar  = document.getElementById('redirectBar');
            const redirectText = document.getElementById('redirectText');
            redirectBar.style.display = 'flex';
            let countdown = 7;

            const tick = setInterval(() => {
                countdown--;
                if (countdown > 0) {
                    redirectText.textContent = `Redirecting to dashboard in ${countdown} second${countdown !== 1 ? 's' : ''}…`;
                } else {
                    clearInterval(tick);
                    redirectText.textContent = 'Redirecting now…';
                    window.location.href = 'dashboard.php';
                }
            }, 1000);
        } else {
            throw new Error(data.message || 'An unexpected error occurred.');
        }
    } catch (err) {
        errorText.textContent = err.message;
        errorPanel.classList.add('show');
        button.disabled = false;
        button.classList.remove('success');
        btnIcon.innerHTML = '';
        btnIcon.className = 'fas fa-rotate';
        btnLabel.textContent = 'Request Revision';
    }
}
</script>
</body>
</html>