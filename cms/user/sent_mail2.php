<?php
// Include the database configuration
require 'include/config.php';

// Manually include PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check connection
if (!$con) {
    error_log("Database connection failed: " . mysqli_connect_error());
    header('Location: error.php?message=Database+connection+failed');
    exit();
}

// Get order_id from URL parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    error_log("Invalid order ID provided.");
    header('Location: error.php?message=Invalid+order+ID');
    exit();
}

// Fetch order details
$orderQuery = "SELECT * FROM tire_orders WHERE order_id = ?";
$stmt = mysqli_prepare($con, $orderQuery);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$orderResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($orderResult) === 0) {
    error_log("Order not found: " . $order_id);
    header('Location: error.php?message=Order+not+found');
    exit();
}

$order = mysqli_fetch_assoc($orderResult);
mysqli_stmt_close($stmt);

// Fetch order items with tire descriptions
$itemsQuery = "SELECT toi.*, td.Description, td.tire_size, td.Brand
               FROM tire_order_items toi
               LEFT JOIN tire_details td ON toi.icode = td.icode
               WHERE toi.order_id = ?";
$stmt = mysqli_prepare($con, $itemsQuery);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$itemsResult = mysqli_stmt_get_result($stmt);

$orderItems = [];
while ($item = mysqli_fetch_assoc($itemsResult)) {
    $orderItems[] = $item;
}
mysqli_stmt_close($stmt);

$customer_id       = $order['customer_id'];
$allItemsHavePrice = false;
$itemPriceMap      = [];

if (!empty($orderItems)) {
    $allHavePrice = true;
    foreach ($orderItems as $item) {
        $icode = $item['icode'];
        $priceCheckQuery = "SELECT price FROM customer_items WHERE cus_id = ? AND icode = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $priceCheckQuery);
        mysqli_stmt_bind_param($stmt, "is", $customer_id, $icode);
        mysqli_stmt_execute($stmt);
        $priceCheckResult = mysqli_stmt_get_result($stmt);
        if ($priceCheckResult && mysqli_num_rows($priceCheckResult) > 0) {
            $priceRow             = mysqli_fetch_assoc($priceCheckResult);
            $itemPriceMap[$icode] = $priceRow['price'];
        } else {
            $itemPriceMap[$icode] = null;
            $allHavePrice         = false;
        }
        mysqli_stmt_close($stmt);
    }
    $allItemsHavePrice = $allHavePrice;
}

$recipients  = [];
$seenEmails  = [];

$customerQuery = "SELECT am.acm_email, am.acm_name
                  FROM users u
                  INNER JOIN account_managers am ON u.acm_ref = am.id
                  WHERE u.id = ? AND am.status = 'active'";
$stmt = mysqli_prepare($con, $customerQuery);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$customerResult = mysqli_stmt_get_result($stmt);
if ($customerResult && mysqli_num_rows($customerResult) > 0) {
    $customerData = mysqli_fetch_assoc($customerResult);
    $acmEmail     = trim($customerData['acm_email'] ?? '');
    if (filter_var($acmEmail, FILTER_VALIDATE_EMAIL)) {
        $recipients[] = ['email' => $acmEmail, 'name' => htmlspecialchars($customerData['acm_name'] ?? 'Account Manager'), 'role' => 'account_manager'];
        $seenEmails[] = strtolower($acmEmail);
    }
}
mysqli_stmt_close($stmt);

$recipientQuery  = "SELECT email, name, role FROM tbl_email_order_rep WHERE status = 'active' ORDER BY role";
$recipientResult = mysqli_query($con, $recipientQuery);
if ($recipientResult && mysqli_num_rows($recipientResult) > 0) {
    while ($row = mysqli_fetch_assoc($recipientResult)) {
        $email = trim($row['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array(strtolower($email), $seenEmails)) {
            $recipients[] = ['email' => $email, 'name' => htmlspecialchars($row['name'] ?? 'Recipient'), 'role' => htmlspecialchars($row['role'] ?? 'other')];
            $seenEmails[] = strtolower($email);
        }
    }
}

if ($allItemsHavePrice) {
    $financeQuery  = "SELECT email, fullname FROM admin WHERE role = 'finance' AND email IS NOT NULL AND email != ''";
    $financeResult = mysqli_query($con, $financeQuery);
    if ($financeResult && mysqli_num_rows($financeResult) > 0) {
        while ($financeRow = mysqli_fetch_assoc($financeResult)) {
            $financeEmail = trim($financeRow['email'] ?? '');
            if (filter_var($financeEmail, FILTER_VALIDATE_EMAIL) && !in_array(strtolower($financeEmail), $seenEmails)) {
                $recipients[] = ['email' => $financeEmail, 'name' => htmlspecialchars($financeRow['fullname'] ?? 'Finance'), 'role' => 'finance'];
                $seenEmails[] = strtolower($financeEmail);
            }
        }
    }
}

if (empty($recipients)) {
    $recipients[] = ['email' => 'planningtool@plan.atire.com', 'name' => 'Atire Admin', 'role' => 'admin'];
}

$ccRecipients = [];

if (isset($_POST['confirm_send']) && $_POST['confirm_send'] == '1') {

    if (!$allItemsHavePrice) {
        $updateStatusQuery = "UPDATE tire_orders SET status = 'price_pending' WHERE order_id = ?";
        $stmtUpdate = mysqli_prepare($con, $updateStatusQuery);
        mysqli_stmt_bind_param($stmtUpdate, "i", $order_id);
        if (mysqli_stmt_execute($stmtUpdate)) {
            $order['status'] = 'price_pending';
        }
        mysqli_stmt_close($stmtUpdate);
    }

    if (!empty($_POST['cc_emails'])) {
        $ccEmailArray = preg_split('/[,;\n\r]+/', $_POST['cc_emails']);
        foreach ($ccEmailArray as $ccEmail) {
            $ccEmail = trim($ccEmail);
            if (!empty($ccEmail) && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                $emailLower = strtolower($ccEmail);
                if (!in_array($emailLower, array_map('strtolower', array_column($recipients, 'email')))) {
                    $ccRecipients[] = ['email' => $ccEmail, 'name' => 'CC Recipient'];
                }
            }
        }
        $uniqueCC = []; $seenCCEmails = [];
        foreach ($ccRecipients as $cc) {
            $lc = strtolower($cc['email']);
            if (!in_array($lc, $seenCCEmails)) { $uniqueCC[] = $cc; $seenCCEmails[] = $lc; }
        }
        $ccRecipients = $uniqueCC;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug  = 0; $mail->isSMTP();
        $mail->Host       = 'plan.atire.com'; $mail->SMTPAuth = true;
        $mail->Username   = 'planningtool@plan.atire.com'; $mail->Password = 'Bishan@1919';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; $mail->Port = 465;
        $mail->setFrom('bishan.k@atire.com', 'Atire Order Notifications');
        $recipientCount = 0;
        foreach ($recipients as $r) { $mail->addAddress($r['email'], $r['name']); $recipientCount++; }
        $ccCount = 0;
        foreach ($ccRecipients as $cc) { $mail->addCC($cc['email'], $cc['name']); $ccCount++; }
        $profilePicPath = 'atire.png';
        if (file_exists($profilePicPath)) { $mail->addEmbeddedImage($profilePicPath, 'company_logo'); }
        $mail->Subject = 'Tire Order #' . $order['order_id'] . ' - Status: ' . strtoupper($order['status']);
        $mail->isHTML(true);

        $bannerColor = '#F28018'; $bannerText = '⚠️ NEW ORDER AWAITING CONFIRMATION';
        if ($order['status'] === 'confirmed')      { $bannerColor = '#28a745'; $bannerText = '✓ ORDER CONFIRMED'; }
        elseif ($order['status'] === 'completed')  { $bannerColor = '#007bff'; $bannerText = '✓ ORDER COMPLETED'; }
        elseif ($order['status'] === 'price_pending') { $bannerColor = '#856404'; $bannerText = '⏳ ORDER PENDING — AWAITING PRICE ASSIGNMENT'; }

        $mail->Body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body{margin:0;padding:0;font-family:"Segoe UI",sans-serif;background:#f8f9fa;color:#333;}
.container{max-width:900px;margin:0 auto;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.1);border-radius:12px;overflow:hidden;}
.header{background:linear-gradient(135deg,#000 0%,#333 100%);padding:5px;text-align:center;position:relative;}
.header::after{content:"";position:absolute;bottom:0;left:0;right:0;height:4px;background:#F28018;}
.profile-section{display:flex;align-items:center;justify-content:center;gap:15px;margin-bottom:10px;}
.profile-section img{width:250px;height:150px;border-radius:8px;object-fit:contain;padding:5px;}
.company-info p{margin:5px 0;font-size:16px;opacity:.9;color:#F28018;}
.urgent-banner{background:' . $bannerColor . ';color:#fff;padding:15px;text-align:center;font-weight:bold;font-size:18px;}
.content{padding:30px;}
.order-id{background:#f8f9fa;padding:20px;border-radius:8px;text-align:center;margin-bottom:30px;border:2px solid #F28018;}
.order-id h2{margin:0;color:#000;font-size:24px;}
.section{margin-bottom:30px;background:#f8f9fa;padding:25px;border-radius:10px;border:1px solid #e9ecef;}
.section h3{color:#000;margin-top:0;font-size:20px;}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:15px;}
.info-item{background:#fff;padding:15px;border-radius:6px;border:1px solid #e9ecef;}
.info-label{font-weight:600;color:#F28018;font-size:14px;margin-bottom:5px;text-transform:uppercase;}
.info-value{color:#333;font-size:16px;}
.items-table{width:100%;border-collapse:collapse;margin-top:15px;background:#fff;}
.items-table th{background:#000;color:#fff;padding:12px;text-align:left;font-size:13px;}
.items-table td{padding:12px;border-bottom:1px solid #e9ecef;font-size:14px;}
.footer{background:#000;color:#fff;padding:25px;text-align:center;}
.action-button{display:inline-block;background:linear-gradient(135deg,#F28018,#e6730f);color:#fff!important;padding:15px 30px;text-decoration:none;border-radius:30px;font-weight:600;margin:10px;}
.finance-notice{background:#fff3cd;border-left:4px solid #ffc107;padding:12px 15px;border-radius:6px;margin-top:15px;font-size:13px;color:#856404;}
.finance-ok-notice{background:#d4edda;border-left:4px solid #28a745;padding:12px 15px;border-radius:6px;margin-top:15px;font-size:13px;color:#155724;}
</style></head><body><div class="container">
<div class="header"><div class="profile-section">';
        if (file_exists($profilePicPath)) $mail->Body .= '<img src="cid:company_logo" alt="Atire Logo">';
        $mail->Body .= '<div class="company-info"><p>Customer Service</p></div></div></div>
<div class="urgent-banner">' . $bannerText . '</div>
<div class="content">
<div class="order-id"><h2>🎫 Order #' . htmlspecialchars($order['order_id']) . '</h2>
<p style="color:#808080;">Placed: ' . date('F j, Y \a\t g:i A', strtotime($order['order_date'])) . '</p></div>
<div class="section"><h3>📦 Order Information</h3><div class="info-grid">
<div class="info-item"><div class="info-label">Order ID</div><div class="info-value" style="color:#F28018;">' . htmlspecialchars($order['order_id']) . '</div></div>
<div class="info-item"><div class="info-label">Customer ID</div><div class="info-value">' . htmlspecialchars($order['customer_id']) . '</div></div>
<div class="info-item"><div class="info-label">Status</div><div class="info-value">' . strtoupper(htmlspecialchars($order['status'])) . '</div></div>
<div class="info-item"><div class="info-label">Total Quantity</div><div class="info-value" style="color:#F28018;">' . htmlspecialchars($order['total_quantity']) . '</div></div>
</div>';
        if ($allItemsHavePrice) $mail->Body .= '<div class="finance-ok-notice">✅ All items have fixed customer prices. Finance has been notified.</div>';
        else $mail->Body .= '<div class="finance-notice">⚠️ One or more items have no fixed price. Finance not notified. Status: PRICE_PENDING.</div>';
        $mail->Body .= '</div><div class="section"><h3>🛒 Order Items</h3><table class="items-table"><thead><tr><th>Item Code</th><th>Description</th><th>Size</th><th>Brand</th><th>Qty</th><th>Fixed Price</th></tr></thead><tbody>';
        foreach ($orderItems as $item) {
            $icode = $item['icode'];
            $priceCell = isset($itemPriceMap[$icode]) && $itemPriceMap[$icode] !== null
                ? '<span style="color:#28a745;font-weight:600;">' . number_format($itemPriceMap[$icode], 2) . '</span>'
                : '<span style="color:#dc3545;font-weight:600;">No fixed price</span>';
            $mail->Body .= '<tr><td style="color:#F28018;font-weight:600;">' . htmlspecialchars($icode) . '</td><td>' . htmlspecialchars($item['Description'] ?? 'N/A') . '</td><td>' . htmlspecialchars($item['tire_size'] ?? 'N/A') . '</td><td>' . htmlspecialchars($item['Brand'] ?? 'N/A') . '</td><td style="text-align:center;font-weight:600;">' . htmlspecialchars($item['quantity']) . '</td><td style="text-align:center;">' . $priceCell . '</td></tr>';
        }
        $mail->Body .= '</tbody></table></div><div style="text-align:center;margin:30px 0;"><a href="https://plan.atire.com/cms/admin" class="action-button">🚀 Go to Dashboard</a></div></div>
<div class="footer"><p><strong>ATIRE Customer Service</strong></p><p style="font-size:12px;opacity:.8;margin-top:15px;">Automated notification. Do not reply.</p></div></div></body></html>';

        $mail->AltBody = "TIRE ORDER NOTIFICATION\nOrder ID: " . $order['order_id'] . "\nCustomer ID: " . $order['customer_id'] . "\nStatus: " . strtoupper($order['status']) . "\n";
        $mail->send();

        $totalSent      = $recipientCount + $ccCount;
        $redirectMessage = 'Email+sent+successfully+to+' . $totalSent . '+recipients+(' . $recipientCount . '+TO,+' . $ccCount . '+CC)';
        if (!$allItemsHavePrice) $redirectMessage .= '+|+Order+status+set+to+price_pending';
        mysqli_close($con);
        header('Location: dashboard.php?message=' . $redirectMessage);
        exit();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        mysqli_close($con);
        header('Location: error.php?message=Mailer+Error:+' . urlencode($mail->ErrorInfo));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order #<?php echo htmlspecialchars($order_id); ?> — Review & Send — ATIRE</title>
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
    --orange:       #f28018;
    --orange-dk:    #d06e10;
    --orange-lt:    rgba(242,128,24,0.10);
    --orange-glow:  rgba(242,128,24,0.18);
    --gray-50:      #f9f9f9;
    --gray-100:     #f2f2f2;
    --gray-200:     #e4e4e4;
    --gray-300:     #d0d0d0;
    --gray-400:     #b0b0b0;
    --gray-500:     #888888;
    --gray-700:     #444444;
    --gray-900:     #1a1a1a;
    --white:        #ffffff;
    --bg:           #f3f4f6;
    --green:        #166534;
    --green-lt:     #dcfce7;
    --green-bd:     #86efac;
    --red:          #991b1b;
    --red-lt:       #fee2e2;
    --red-bd:       #fca5a5;
    --amber:        #92400e;
    --amber-lt:     #fef3c7;
    --amber-bd:     #fcd34d;
    --font:        'SF UI Display', -apple-system, BlinkMacSystemFont, sans-serif;
    --radius-xs:    4px;
    --radius-sm:    8px;
    --radius-md:    12px;
    --radius-lg:    16px;
    --shadow-sm:    0 1px 6px rgba(0,0,0,0.06);
    --shadow:       0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:    0 6px 28px rgba(0,0,0,0.12);
    --shadow-lg:    0 12px 48px rgba(0,0,0,0.14);
    --trans:        0.18s cubic-bezier(0.4,0,0.2,1);
    --hdr-h:        60px;
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--gray-700);
    min-height: 100vh;
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
    position: sticky; top:0; z-index:400;
    background: var(--white);
    border-bottom: 2.5px solid var(--orange);
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    height: var(--hdr-h);
}
.hdr-inner {
    max-width: 1400px; margin:0 auto;
    padding: 0 1.8rem;
    height: 100%;
    display: flex; align-items:center; justify-content:space-between; gap:1rem;
}
.brand { display:flex; align-items:center; gap:12px; text-decoration:none; }
.brand-logo { height:30px; width:auto; }
.hdr-center {
    font-size:13px; font-weight:800; color:var(--gray-700);
    letter-spacing:.05em; text-transform:uppercase;
    display:flex; align-items:center; gap:10px;
}
.hdr-center .order-badge {
    background: var(--orange); color:var(--white);
    padding:3px 12px; border-radius:20px;
    font-size:11px; font-weight:900; letter-spacing:.06em;
}
.hdr-right { display:flex; align-items:center; gap:8px; }
.hdr-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 15px; border-radius:var(--radius-sm);
    font-weight:700; font-size:12px; letter-spacing:.03em;
    text-decoration:none;
    border:1.5px solid var(--gray-200);
    background:var(--white); color:var(--gray-500);
    cursor:pointer; transition:var(--trans);
    font-family:var(--font);
}
.hdr-btn:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); }

/* ─── PAGE WRAPPER ───────────────────────────────────────────────────────── */
.page-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem 1.8rem 6rem;
}

/* ─── PAGE HERO ──────────────────────────────────────────────────────────── */
.page-hero {
    margin-bottom: 2rem;
}
.hero-eyebrow {
    font-size:9px; font-weight:800; color:var(--orange);
    letter-spacing:.22em; text-transform:uppercase;
    margin-bottom:6px; display:flex; align-items:center; gap:6px;
}
.hero-eyebrow::before { content:''; width:16px; height:2px; background:var(--orange); border-radius:2px; }
.hero-title {
    font-size:clamp(28px,3.5vw,42px); font-weight:900;
    color:var(--gray-900); letter-spacing:-.02em; line-height:1;
}
.hero-title span { color:var(--orange); }
.hero-sub { font-size:12.5px; font-weight:500; color:var(--gray-400); margin-top:6px; }

/* ─── CARDS ──────────────────────────────────────────────────────────────── */
.card {
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin-bottom: 1.4rem;
    overflow: hidden;
}
.card-hd {
    padding: .9rem 1.4rem;
    border-bottom: 1.5px solid var(--gray-100);
    display: flex; align-items:center; gap:10px;
    background: var(--white);
}
.card-hd-icon {
    width: 30px; height: 30px; border-radius: var(--radius-xs);
    background: var(--orange); color: var(--white);
    display: flex; align-items:center; justify-content:center;
    font-size: 12px; flex-shrink:0;
}
.card-hd-title {
    font-size:12px; font-weight:800; color:var(--gray-700);
    letter-spacing:.08em; text-transform:uppercase;
}
.card-hd-sub {
    font-size:11px; font-weight:500; color:var(--gray-400);
    margin-left:auto;
}
.card-body { padding:1.4rem; }

/* ─── ORDER META GRID ────────────────────────────────────────────────────── */
.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 10px;
}
.meta-tile {
    background: var(--gray-50);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    transition: var(--trans);
}
.meta-tile:hover { border-color: var(--orange); background: var(--orange-lt); }
.meta-label {
    font-size:9.5px; font-weight:800; color:var(--gray-400);
    letter-spacing:.12em; text-transform:uppercase;
    margin-bottom:5px; display:flex; align-items:center; gap:4px;
}
.meta-label i { color:var(--orange); font-size:9px; }
.meta-value {
    font-size:15px; font-weight:900; color:var(--gray-900);
    line-height:1.15; letter-spacing:-.01em;
}
.meta-value.orange { color:var(--orange); }

/* Status badges */
.status-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:20px;
    font-size:10.5px; font-weight:800; letter-spacing:.07em; text-transform:uppercase;
}
.status-pending       { background:#fff7ed; color:#c2410c; border:1.5px solid #fed7aa; }
.status-confirmed     { background:var(--green-lt); color:var(--green); border:1.5px solid var(--green-bd); }
.status-completed     { background:#dbeafe; color:#1e40af; border:1.5px solid #93c5fd; }
.status-price_pending { background:var(--amber-lt); color:var(--amber); border:1.5px solid var(--amber-bd); }

/* ─── PRICING BANNER ─────────────────────────────────────────────────────── */
.pricing-banner {
    border-radius: var(--radius-md);
    padding: 1.1rem 1.4rem;
    margin-bottom: 1.4rem;
    display: flex; align-items:flex-start; gap:14px;
    border-width: 1.5px; border-style: solid;
}
.pricing-banner.ok   { background:var(--green-lt); border-color:var(--green-bd); }
.pricing-banner.warn { background:var(--amber-lt); border-color:var(--amber-bd); }
.pricing-banner-icon {
    width:38px; height:38px; border-radius:var(--radius-sm);
    display:flex; align-items:center; justify-content:center;
    font-size:16px; flex-shrink:0;
}
.pricing-banner.ok   .pricing-banner-icon { background:var(--green); color:var(--white); }
.pricing-banner.warn .pricing-banner-icon { background:var(--amber); color:var(--white); }
.pricing-banner-title {
    font-size:13.5px; font-weight:900; margin-bottom:4px; letter-spacing:-.01em;
}
.pricing-banner.ok   .pricing-banner-title { color:var(--green); }
.pricing-banner.warn .pricing-banner-title { color:var(--amber); }
.pricing-banner-desc {
    font-size:12px; font-weight:500; line-height:1.55;
}
.pricing-banner.ok   .pricing-banner-desc { color:#166534; }
.pricing-banner.warn .pricing-banner-desc { color:var(--amber); }

/* ─── STATUS UPDATE ALERT ────────────────────────────────────────────────── */
.alert-strip {
    background: var(--red-lt);
    border: 1.5px solid var(--red-bd);
    border-left: 3px solid var(--red);
    border-radius: var(--radius-md);
    padding: .9rem 1.2rem;
    margin-bottom: 1.4rem;
    display:flex; align-items:flex-start; gap:10px;
}
.alert-strip i { color:var(--red); font-size:14px; margin-top:1px; flex-shrink:0; }
.alert-strip-title { font-size:12.5px; font-weight:800; color:var(--red); margin-bottom:3px; }
.alert-strip-desc  { font-size:12px; font-weight:500; color:#7f1d1d; line-height:1.5; }

/* ─── TABLE ──────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x:auto; }
table.data-tbl { width:100%; border-collapse:collapse; min-width:700px; }
table.data-tbl thead {
    background:#f7f7f7;
    position:sticky; top:0; z-index:5;
}
table.data-tbl thead::after {
    content:''; display:block;
    position:absolute; bottom:-2px; left:0; right:0;
    height:2px; background:var(--gray-200);
}
table.data-tbl th {
    padding:9px 12px;
    text-align:left; font-size:10px; font-weight:800; color:var(--gray-500);
    letter-spacing:.11em; text-transform:uppercase; white-space:nowrap;
    border-right:1px solid var(--gray-200); border-bottom:2px solid var(--gray-200);
    user-select:none;
}
table.data-tbl th:last-child { border-right:none; }
table.data-tbl th i { color:var(--orange); margin-right:4px; font-size:9px; }
table.data-tbl tbody tr { border-bottom:1px solid var(--gray-100); transition:background var(--trans); }
table.data-tbl tbody tr:last-child { border-bottom:none; }
table.data-tbl tbody tr:nth-child(even) { background:#fafafa; }
table.data-tbl tbody tr:hover { background:rgba(242,128,24,0.04); }
table.data-tbl td {
    padding:9px 12px; font-size:12.5px; font-weight:500;
    color:var(--gray-700); vertical-align:middle;
}
td.code-cell  { font-weight:800; font-size:13px; color:var(--orange); letter-spacing:.01em; white-space:nowrap; }
td.num-cell   { font-weight:700; text-align:center; }
td.price-cell { font-weight:700; text-align:center; white-space:nowrap; }

.badge-ok {
    display:inline-flex; align-items:center; gap:4px;
    background:var(--green-lt); color:var(--green);
    border:1px solid var(--green-bd);
    border-radius:20px; padding:2px 9px;
    font-size:9.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.badge-missing {
    display:inline-flex; align-items:center; gap:4px;
    background:var(--red-lt); color:var(--red);
    border:1px solid var(--red-bd);
    border-radius:20px; padding:2px 9px;
    font-size:9.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.price-ok      { color:var(--green); font-weight:800; }
.price-missing { color:var(--red);   font-weight:700; font-size:11.5px; }

/* ─── RECIPIENTS ─────────────────────────────────────────────────────────── */
.recipient-list { display:flex; flex-direction:column; gap:8px; }
.recipient-item {
    display:flex; align-items:center; justify-content:space-between; gap:14px;
    background:var(--gray-50); border:1.5px solid var(--gray-200);
    border-left:3px solid transparent;
    border-radius:var(--radius-sm); padding:12px 15px;
    transition:var(--trans);
}
.recipient-item:hover { border-color:var(--orange); border-left-color:var(--orange); background:rgba(242,128,24,0.03); transform:translateX(3px); }
.recipient-email { font-weight:800; color:var(--gray-900); font-size:13px; margin-bottom:3px; }
.recipient-name  { font-size:11.5px; font-weight:500; color:var(--gray-500); }
.role-chip {
    flex-shrink:0;
    padding:4px 12px; border-radius:20px;
    font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.role-account_manager { background:#dcfce7; color:#166534; border:1px solid #86efac; }
.role-finance         { background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; }
.role-admin           { background:var(--red-lt); color:var(--red); border:1px solid var(--red-bd); }
.role-sales           { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
.role-operations      { background:#f5f3ff; color:#6d28d9; border:1px solid #c4b5fd; }
.role-other           { background:var(--gray-100); color:var(--gray-500); border:1px solid var(--gray-200); }

/* Count strip */
.count-strip {
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;
    background:var(--orange); border-radius:var(--radius-sm);
    padding:11px 16px; margin-top:12px;
}
.count-strip-left { font-size:13px; font-weight:900; color:var(--white); display:flex; align-items:center; gap:8px; }
.count-strip-right { font-size:11.5px; font-weight:700; color:rgba(255,255,255,.80); }

/* ─── CC SECTION ─────────────────────────────────────────────────────────── */
.cc-card {
    background: var(--white);
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin-bottom: 1.4rem;
    overflow: hidden;
}
.cc-card-hd {
    padding: .9rem 1.4rem;
    border-bottom: 1.5px solid var(--gray-100);
    display: flex; align-items:center; gap:10px;
    background:rgba(242,128,24,0.04);
}
.cc-info-strip {
    padding: .9rem 1.4rem;
    background:rgba(242,128,24,0.04);
    border-bottom: 1px solid rgba(242,128,24,0.12);
    font-size:12px; font-weight:500; color:#7a4400; line-height:1.6;
}
.cc-info-strip ul { padding-left:18px; margin-top:5px; }
.cc-info-strip li { margin-bottom:3px; }
.cc-body { padding:1.4rem; }
.cc-label {
    display:block; margin-bottom:5px;
    font-size:9.5px; font-weight:800; color:var(--gray-500);
    text-transform:uppercase; letter-spacing:.09em;
    display:flex; align-items:center; gap:4px;
}
.cc-label i { color:var(--orange); font-size:9px; }
textarea.cc-inp {
    width:100%; min-height:110px; padding:10px 12px;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    font-family:var(--font); font-size:12.5px; font-weight:600;
    color:var(--gray-700); background:var(--white);
    resize:vertical; outline:none; transition:var(--trans);
}
textarea.cc-inp:focus { border-color:var(--orange); box-shadow:0 0 0 3px var(--orange-glow); }
.cc-example {
    margin-top:8px; padding:9px 12px;
    background:var(--gray-50); border:1px solid var(--gray-200);
    border-left:3px solid var(--orange);
    border-radius:var(--radius-sm);
    font-size:11.5px; font-family:monospace; color:var(--gray-700);
}

/* ─── ACTION ROW ─────────────────────────────────────────────────────────── */
.action-row {
    display:flex; gap:10px; flex-wrap:wrap;
    padding:1.4rem;
    background:var(--white);
    border-top:1.5px solid var(--gray-100);
}
.btn-send {
    flex:2; min-width:180px;
    padding:12px 24px; border:none; border-radius:var(--radius-sm);
    background:var(--orange); color:var(--white);
    font-family:var(--font); font-size:13px; font-weight:900;
    letter-spacing:.06em; text-transform:uppercase;
    cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;
    transition:var(--trans); box-shadow:0 3px 14px rgba(242,128,24,0.28);
}
.btn-send:hover { background:var(--orange-dk); transform:translateY(-1px); box-shadow:0 5px 20px rgba(242,128,24,0.36); }
.btn-cancel {
    flex:1; min-width:120px;
    padding:12px 18px;
    border:1.5px solid var(--gray-200); border-radius:var(--radius-sm);
    background:var(--white); color:var(--gray-500);
    font-family:var(--font); font-size:13px; font-weight:700;
    letter-spacing:.05em; text-transform:uppercase;
    cursor:pointer; text-decoration:none;
    display:flex; align-items:center; justify-content:center; gap:7px;
    transition:var(--trans);
}
.btn-cancel:hover { border-color:var(--orange); color:var(--orange); background:var(--orange-lt); transform:translateY(-1px); }

/* ─── INFO BOX ───────────────────────────────────────────────────────────── */
.info-box {
    background:rgba(242,128,24,0.06);
    border:1px solid rgba(242,128,24,0.20);
    border-left:3px solid var(--orange);
    border-radius:var(--radius-sm);
    padding:.9rem 1.1rem;
    margin-bottom:1.4rem;
    font-size:12px; font-weight:500; color:#7a4400; line-height:1.6;
}
.info-box strong { font-weight:800; }
.info-box ul { padding-left:16px; margin-top:5px; }
.info-box li  { margin-bottom:3px; }

/* ─── RESPONSIVE ─────────────────────────────────────────────────────────── */
@media(max-width:600px) {
    .page-wrap { padding:1rem 1rem 5rem; }
    .hdr-inner { padding:0 1rem; }
    .meta-grid { grid-template-columns:1fr 1fr; }
    .action-row { flex-direction:column; }
}
</style>
</head>
<body>

<!-- ════════════════════════════════════ HEADER ═══════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-center">
            <i class="fas fa-envelope" style="color:var(--orange);"></i>
            Email Review
            <span class="order-badge">#<?php echo htmlspecialchars($order_id); ?></span>
        </div>
        <div class="hdr-right">
            <a href="dashboard.php" class="hdr-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </div>
</header>

<!-- ════════════════════════════════════ BODY ════════════════════════════ -->
<div class="page-wrap">

    <!-- PAGE HERO -->
    <div class="page-hero">
        <div class="hero-eyebrow">Order Review</div>
        <div class="hero-title">Confirm &amp; <span>Send</span> Email</div>
        <div class="hero-sub">Review recipients, pricing status and CC list before dispatching the notification.</div>
    </div>

    <!-- HOW IT WORKS -->
    <div class="info-box">
        <strong><i class="fas fa-info-circle" style="color:var(--orange);margin-right:5px;"></i>How Email Distribution Works</strong>
        <ul>
            <li><strong>TO Recipients:</strong> Account manager + all active records in <code>tbl_email_order_rep</code> — always included.</li>
            <li><strong>Finance (admin table):</strong> Only included when <em>all</em> order items have a fixed price in <code>customer_items</code>.</li>
            <li><strong>CC Recipients:</strong> Optional one-time additions via the form below — not saved to the database.</li>
        </ul>
    </div>

    <!-- ═══════════════════════════════════════════════════
         ORDER SUMMARY CARD
    ════════════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-icon"><i class="fas fa-file-alt"></i></div>
            <div class="card-hd-title">Order Summary</div>
            <div class="card-hd-sub">
                <?php
                $statusClass = 'status-' . htmlspecialchars($order['status']);
                $statusIco   = 'circle';
                if ($order['status'] === 'confirmed')      $statusIco = 'check-circle';
                elseif ($order['status'] === 'completed')  $statusIco = 'check-double';
                elseif ($order['status'] === 'price_pending') $statusIco = 'clock';
                ?>
                <span class="status-badge <?php echo $statusClass; ?>">
                    <i class="fas fa-<?php echo $statusIco; ?>" style="font-size:9px;"></i>
                    <?php echo strtoupper(htmlspecialchars($order['status'])); ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="meta-grid">
                <div class="meta-tile">
                    <div class="meta-label"><i class="fas fa-hashtag"></i>Order ID</div>
                    <div class="meta-value orange">#<?php echo htmlspecialchars($order['order_id']); ?></div>
                </div>
                <div class="meta-tile">
                    <div class="meta-label"><i class="fas fa-user"></i>Customer ID</div>
                    <div class="meta-value"><?php echo htmlspecialchars($order['customer_id']); ?></div>
                </div>
                <div class="meta-tile">
                    <div class="meta-label"><i class="fas fa-calendar-alt"></i>Order Date</div>
                    <div class="meta-value" style="font-size:12px;"><?php echo date('M j, Y · g:i A', strtotime($order['order_date'])); ?></div>
                </div>
                <div class="meta-tile">
                    <div class="meta-label"><i class="fas fa-layer-group"></i>Total Items</div>
                    <div class="meta-value"><?php echo htmlspecialchars($order['total_items']); ?></div>
                </div>
                <div class="meta-tile">
                    <div class="meta-label"><i class="fas fa-boxes"></i>Total Quantity</div>
                    <div class="meta-value orange"><?php echo number_format($order['total_quantity']); ?></div>
                </div>
                <?php if (!empty($order['total_weight'])): ?>
                <div class="meta-tile">
                    <div class="meta-label"><i class="fas fa-weight"></i>Total Weight</div>
                    <div class="meta-value" style="font-size:12px;"><?php echo number_format($order['total_weight'], 2); ?> kg</div>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($order['order_notes'])): ?>
            <div style="margin-top:12px;padding:10px 13px;background:var(--gray-50);border:1.5px solid var(--gray-200);border-left:3px solid var(--orange);border-radius:var(--radius-sm);">
                <div style="font-size:9.5px;font-weight:800;color:var(--gray-400);letter-spacing:.10em;text-transform:uppercase;margin-bottom:4px;"><i class="fas fa-sticky-note" style="color:var(--orange);margin-right:3px;"></i>Order Notes</div>
                <div style="font-size:12.5px;font-weight:500;color:var(--gray-700);"><?php echo nl2br(htmlspecialchars($order['order_notes'])); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         PRICING STATUS
    ════════════════════════════════════════════════════ -->
    <?php if ($allItemsHavePrice): ?>
    <div class="pricing-banner ok">
        <div class="pricing-banner-icon"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="pricing-banner-title"><i class="fas fa-lock" style="font-size:11px;margin-right:4px;"></i>All Items Have Fixed Prices — Finance Will Be Notified</div>
            <div class="pricing-banner-desc">Every item in this order has a fixed price entry in <code>customer_items</code> for customer ID <strong><?php echo htmlspecialchars($customer_id); ?></strong>. Finance recipients have been added to the TO list.</div>
        </div>
    </div>
    <?php else: ?>
    <div class="pricing-banner warn">
        <div class="pricing-banner-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <div class="pricing-banner-title">One or More Items Have No Fixed Price — Finance Excluded</div>
            <div class="pricing-banner-desc">Finance will <strong>not</strong> receive this email because some items are missing a fixed price in <code>customer_items</code>. Items marked <strong>MISSING</strong> in the table below have no entry for this customer. Finance will only be notified once all prices are assigned.</div>
        </div>
    </div>

    <!-- Status update warning -->
    <div class="alert-strip">
        <i class="fas fa-sync-alt"></i>
        <div>
            <div class="alert-strip-title">Order Status Will Update to PRICE_PENDING on Send</div>
            <div class="alert-strip-desc">Because one or more items are missing a fixed price, clicking <strong>Confirm &amp; Send Email</strong> will automatically set the order status to <strong>PRICE_PENDING</strong>. The order remains in this state until all items have fixed prices assigned in <code>customer_items</code>.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════
         ITEM PRICING TABLE
    ════════════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-icon"><i class="fas fa-tags"></i></div>
            <div class="card-hd-title">Customer Pricing Check</div>
            <div class="card-hd-sub"><?php echo count($orderItems); ?> item<?php echo count($orderItems) !== 1 ? 's' : ''; ?></div>
        </div>
        <div class="tbl-wrap">
            <table class="data-tbl">
                <thead>
                    <tr>
                        <th><i class="fas fa-barcode"></i>Item Code</th>
                        <th><i class="fas fa-align-left"></i>Description</th>
                        <th><i class="fas fa-tag"></i>Brand</th>
                        <th><i class="fas fa-boxes"></i>Qty</th>
                        <th><i class="fas fa-dollar-sign"></i>Fixed Price</th>
                        <th><i class="fas fa-check"></i>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item):
                        $icode    = $item['icode'];
                        $priceVal = $itemPriceMap[$icode] ?? null;
                    ?>
                    <tr>
                        <td class="code-cell"><?php echo htmlspecialchars($icode); ?></td>
                        <td style="font-weight:600;font-size:12px;color:var(--gray-700);"><?php echo htmlspecialchars($item['Description'] ?? 'N/A'); ?></td>
                        <td style="font-weight:700;color:var(--gray-900);"><?php echo htmlspecialchars($item['Brand'] ?? 'N/A'); ?></td>
                        <td class="num-cell"><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td class="price-cell">
                            <?php if ($priceVal !== null): ?>
                                <span class="price-ok">$<?php echo number_format($priceVal, 2); ?></span>
                            <?php else: ?>
                                <span class="price-missing">No fixed price</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($priceVal !== null): ?>
                                <span class="badge-ok"><i class="fas fa-check" style="font-size:8px;"></i> OK</span>
                            <?php else: ?>
                                <span class="badge-missing"><i class="fas fa-times" style="font-size:8px;"></i> Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         RECIPIENTS
    ════════════════════════════════════════════════════ -->
    <div class="card">
        <div class="card-hd">
            <div class="card-hd-icon"><i class="fas fa-paper-plane"></i></div>
            <div class="card-hd-title">Primary Recipients (TO)</div>
        </div>
        <div class="card-body">
            <div class="recipient-list">
                <?php foreach ($recipients as $r):
                    $roleKey   = strtolower(str_replace([' ', '/'], '_', $r['role']));
                    $roleClass = 'role-' . $roleKey;
                    $roleIco   = 'user';
                    if ($roleKey === 'account_manager') $roleIco = 'user-tie';
                    elseif ($roleKey === 'finance')      $roleIco = 'chart-line';
                    elseif ($roleKey === 'admin')        $roleIco = 'shield-alt';
                    elseif ($roleKey === 'sales')        $roleIco = 'handshake';
                    elseif ($roleKey === 'operations')   $roleIco = 'cog';
                ?>
                <div class="recipient-item">
                    <div>
                        <div class="recipient-email"><i class="fas fa-envelope" style="color:var(--orange);font-size:10px;margin-right:5px;"></i><?php echo htmlspecialchars($r['email']); ?></div>
                        <div class="recipient-name"><?php echo htmlspecialchars($r['name']); ?></div>
                    </div>
                    <span class="role-chip <?php echo $roleClass; ?>"><i class="fas fa-<?php echo $roleIco; ?>" style="font-size:9px;margin-right:4px;"></i><?php echo htmlspecialchars($r['role']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="count-strip">
                <div class="count-strip-left">
                    <i class="fas fa-users"></i>
                    <?php echo count($recipients); ?> Primary Recipient<?php echo count($recipients) !== 1 ? 's' : ''; ?>
                </div>
                <div class="count-strip-right">
                    <?php if ($allItemsHavePrice): ?>
                        <i class="fas fa-check-circle" style="color:rgba(255,255,255,.85);margin-right:5px;"></i>Finance Included
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle" style="color:rgba(255,255,255,.85);margin-right:5px;"></i>Finance Excluded · Status → PRICE_PENDING
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════
         CC FORM
    ════════════════════════════════════════════════════ -->
    <form method="POST" id="sendForm">
        <input type="hidden" name="confirm_send" value="1">

        <div class="cc-card">
            <div class="cc-card-hd">
                <div class="card-hd-icon" style="background:var(--gray-700);"><i class="fas fa-at"></i></div>
                <div class="card-hd-title">Add CC Recipients <span style="font-weight:500;text-transform:none;font-size:10.5px;color:var(--gray-400);margin-left:4px;">(Optional)</span></div>
            </div>
            <div class="cc-info-strip">
                <strong><i class="fas fa-info-circle" style="color:var(--orange);margin-right:4px;"></i>Instructions:</strong>
                <ul>
                    <li>Enter additional emails to receive this notification as CC.</li>
                    <li>These are <strong>one-time only</strong> — not saved to the database.</li>
                    <li>Separate multiple emails with commas, semicolons, or new lines.</li>
                    <li>Leave blank if no additional CC recipients are needed.</li>
                </ul>
            </div>
            <div class="cc-body">
                <label class="cc-label" for="cc_emails">
                    <i class="fas fa-envelope-open-text"></i>CC Email Addresses
                </label>
                <textarea
                    class="cc-inp"
                    name="cc_emails"
                    id="cc_emails"
                    placeholder="Enter email addresses (one per line or comma-separated)&#10;&#10;Example:&#10;john@example.com&#10;jane@example.com"
                ></textarea>
                <div class="cc-example">
                    <strong>Valid formats:</strong>&nbsp;
                    john.doe@company.com, jane.smith@company.com &nbsp;·&nbsp; <em>or one per line</em>
                </div>
            </div>
            <div class="action-row">
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane"></i> Confirm &amp; Send Email
                </button>
                <a href="dashboard.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>

</div><!-- /page-wrap -->

<!-- ════════════════════════════════════ JS ══════════════════════════════ -->
<script>
const allPriced = <?php echo $allItemsHavePrice ? 'true' : 'false'; ?>;

// Auto-resize CC textarea
const textarea = document.getElementById('cc_emails');
textarea.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Validate before submit
document.getElementById('sendForm').addEventListener('submit', function (e) {
    const ccEmails = textarea.value.trim();
    if (ccEmails) {
        const emailArray    = ccEmails.split(/[,;\n\r]+/);
        const invalidEmails = [];
        emailArray.forEach(email => {
            email = email.trim();
            if (email && !isValidEmail(email)) invalidEmails.push(email);
        });
        if (invalidEmails.length > 0) {
            e.preventDefault();
            alert('The following email addresses are invalid:\n\n' + invalidEmails.join('\n') + '\n\nPlease correct them and try again.');
            return false;
        }
    }

    const financeNote = allPriced
        ? '✅ Finance INCLUDED — all items have fixed prices.'
        : '⚠️ Finance EXCLUDED — some items have no fixed price.';
    const statusNote  = allPriced ? '' : '\n🔄 Order status will be updated to PRICE_PENDING.';
    const totalRecipients = <?php echo count($recipients); ?>;
    const ccNote = ccEmails ? ' + CC recipients' : '';

    return confirm(
        'Send email to ' + totalRecipients + ' primary recipient(s)' + ccNote + '?\n\n' + financeNote + statusNote
    );
});

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>

</body>
</html>
<?php mysqli_close($con); ?>