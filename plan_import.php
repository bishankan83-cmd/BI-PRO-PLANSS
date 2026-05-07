<?php
// Autoload PHPMailer (Composer)
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    die("Autoloader not found. Please run 'composer require phpmailer/phpmailer'.");
}

require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

$servername = "localhost";
$username   = "planatir_task_managemen";
$password   = "Bishan@1919";
$database   = "planatir_task_managemen";

$emailConfig = [
    'smtp_host'       => 'plan.atire.com',
    'smtp_port'       => 465,
    'smtp_user'       => 'planningtool@plan.atire.com',
    'smtp_password'   => 'Bishan@1919',
    'from_email'      => 'planningtool@plan.atire.com',
    'from_name'       => 'Bi Pro Plan S',
    'recipient_email' => 'gihan.k@atire.com',
];

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function sendImportSuccessEmail($emailConfig, $manualDate, $manualShift, $recordCount) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $emailConfig['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $emailConfig['smtp_user'];
        $mail->Password   = $emailConfig['smtp_password'];
        $mail->SMTPSecure = ($emailConfig['smtp_port'] == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $emailConfig['smtp_port'];
        $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
        $mail->addAddress($emailConfig['recipient_email']);
        $mail->isHTML(true);
        $mail->Subject = 'Daily Plan Import Successful';
        $mail->Body = "<html><body style='font-family:Arial;background:#f5f5f5;'>
            <div style='max-width:600px;margin:0 auto;padding:20px;'>
            <div style='background:#F28018;color:#fff;padding:18px;border-radius:8px;text-align:center;'><h2>&#10003; Daily Plan Import Successful</h2></div>
            <div style='background:#fff;padding:20px;margin-top:16px;border-radius:8px;border-left:4px solid #F28018;'>
            <p>Hello,</p><p>Data successfully imported into <strong>Bi Pro Plan S</strong>.</p>
            <div style='background:#fef6ee;padding:14px;border-radius:6px;margin-top:14px;'>
            <p><strong>Import Date:</strong> ".date('Y-m-d H:i:s')."</p>
            <p><strong>Plan Date:</strong> ".htmlspecialchars($manualDate)."</p>
            <p><strong>Shift:</strong> ".htmlspecialchars($manualShift)."</p>
            <p><strong>Records:</strong> <span style='color:#F28018;font-size:16px;font-weight:bold;'>".$recordCount."</span></p>
            </div></div>
            <div style='margin-top:18px;font-size:11px;color:#aaa;text-align:center;'><p>Automated email. Do not reply. &copy; 2024 Bi Pro Plan S</p></div>
            </div></body></html>";
        $mail->AltBody = "Import Successful. Records: ".$recordCount;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

$message = '';
if (isset($_POST['submit'])) {
    $manualDate  = $_POST['date'];
    $manualShift = $_POST['shift'];
    if (isset($_FILES['excel_file']['name']) && $_FILES['excel_file']['name'] != "") {
        $file_name = $_FILES['excel_file']['tmp_name'];
        try {
            $spreadsheet   = IOFactory::load($file_name);
            $sheet         = $spreadsheet->getActiveSheet();
            $rows          = $sheet->toArray();
            $importedCount = 0;
            $errorCount    = 0;
            foreach ($rows as $index => $row) {
                if ($index == 0) continue;
                if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) continue;
                $icode = $row[0] ?? ''; $moldName = $row[1] ?? ''; $cavityName = $row[2] ?? ''; $plan = $row[3] ?? '';
                $sql  = "INSERT INTO daily_plan (Date, Shift, Icode, MoldName, CavityName, Plan) VALUES (?,?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) { $errorCount++; continue; }
                $stmt->bind_param("ssssss", $manualDate, $manualShift, $icode, $moldName, $cavityName, $plan);
                if ($stmt->execute()) $importedCount++; else $errorCount++;
                $stmt->close();
            }
            if ($importedCount > 0) {
                $emailSent = sendImportSuccessEmail($emailConfig, $manualDate, $manualShift, $importedCount);
                $message = $emailSent
                    ? "<strong>&#10003; Data imported successfully!</strong><br>Records: <strong>".$importedCount."</strong><br>Email sent to <strong>".htmlspecialchars($emailConfig['recipient_email'])."</strong>"
                    : "<strong>&#10003; Imported!</strong><br>Records: <strong>".$importedCount."</strong><br><span style='color:#e67e22;'>&#9888; Email could not be sent, but data was imported.</span>";
                if ($errorCount > 0) $message .= "<br>Errors: ".$errorCount;
            } else { $message = "No valid records found to import."; }
        } catch (Exception $e) { $message = "Error loading file: " . $e->getMessage(); }
    } else { $message = "Please upload an Excel file."; }
}

$conn->close();
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message_view = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM daily_plan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $message_view = $stmt->execute() ? "Record deleted successfully." : "Error: " . $stmt->error;
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $message_view = $conn->query("TRUNCATE TABLE daily_plan") ? "All records deleted successfully." : "Error: " . $conn->error;
}

$result    = $conn->query("SELECT * FROM daily_plan ORDER BY Date DESC, Shift");
$totalRows = $result ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bi Pro Plan S — Import Data</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary:        #F28018;
            --primary-light:  #ff9a3c;
            --primary-pale:   #fef6ee;
            --secondary:      #2c3e50;
            --secondary-dark: #1a252f;
            --bg:             #f0f2f5;
            --card-bg:        #ffffff;
            --text-dark:      #1a1a1a;
            --text-muted:     #6c757d;
            --border:         #e8ecf0;
            --shadow-sm:      0 2px 8px rgba(0,0,0,0.07);
            --shadow-md:      0 4px 16px rgba(0,0,0,0.11);
            --radius:         14px;
            --radius-sm:      8px;
            --transition:     all 0.25s ease;
        }
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            height: 68px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            box-shadow: 0 3px 16px rgba(0,0,0,0.28);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            text-decoration: none;
        }
        .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; color: #fff;
            box-shadow: 0 3px 10px rgba(242,128,24,.4);
            flex-shrink: 0;
        }
        .brand-name { font-size: 1.12rem; font-weight: 800; color: #fff; line-height: 1.2; }
        .brand-sub  { font-size: .7rem; color: rgba(255,255,255,.5); }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-divider { width:1px; height:28px; background:rgba(255,255,255,.15); margin:0 2px; }

        .btn-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff;
            text-decoration: none;
            padding: 9px 20px;
            border-radius: var(--radius-sm);
            font-size: .88rem;
            font-weight: 700;
            box-shadow: 0 3px 12px rgba(242,128,24,.42);
            transition: var(--transition);
            border: none;
            cursor: pointer;
            letter-spacing: .01em;
        }
        .btn-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(242,128,24,.55);
            color: #fff;
        }
        .btn-dashboard:active { transform: translateY(0); }

        .nav-user {
            display: flex; align-items: center; gap: 9px;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            background: rgba(255,255,255,.08);
        }
        .nav-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex; align-items: center; justify-content: center;
            font-size: .9rem; color: #fff; font-weight: 700; flex-shrink: 0;
        }
        .nav-uname { font-size: .85rem; font-weight: 600; color: #fff; }
        .nav-urole { font-size: .7rem; color: rgba(255,255,255,.48); }

        .btn-logout {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.09);
            color: rgba(255,255,255,.8);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-size: .83rem; font-weight: 600;
            transition: var(--transition);
            border: 1px solid rgba(255,255,255,.12);
        }
        .btn-logout:hover { background: rgba(231,76,60,.7); color: #fff; border-color: transparent; }

        /* ── TICKER ── */
        .ticker {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 10px 32px;
            font-size: .83rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── PAGE TITLE BAR ── */
        .page-title-bar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .page-title-bar h1 {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--secondary);
            display: flex; align-items: center; gap: 10px;
        }
        .page-title-bar h1 i { color: var(--primary); }
        .breadcrumb {
            display: flex; align-items: center; gap: 7px;
            font-size: .82rem; color: var(--text-muted);
        }
        .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .bc-sep { font-size: .6rem; opacity: .4; }

        /* ── MAIN ── */
        .main {
            max-width: 1300px;
            margin: 28px auto;
            padding: 0 28px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── STATS STRIP ── */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 18px 22px;
            box-shadow: var(--shadow-sm);
            display: flex; align-items: center; gap: 16px;
            border-left: 4px solid var(--primary);
            transition: var(--transition);
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 12px;
            background: var(--primary-pale);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem; color: var(--primary);
            flex-shrink: 0;
        }
        .stat-num   { font-size: 1.7rem; font-weight: 800; color: var(--secondary); line-height: 1; }
        .stat-label { font-size: .76rem; color: var(--text-muted); margin-top: 3px; }
        .stat-email { font-size: .82rem; font-weight: 700; color: var(--secondary); margin-top: 2px; word-break: break-all; }

        /* ── TWO COL ── */
        .two-col {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── CARD ── */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            padding: 15px 22px;
            display: flex; align-items: center; gap: 10px;
        }
        .card-header i  { color: var(--primary); font-size: 1rem; }
        .card-header h2 { color: #fff; font-size: .95rem; font-weight: 700; }
        .card-header-badge {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: .74rem;
            font-weight: 700;
        }
        .card-body { padding: 24px; }

        /* ── FORM ── */
        .form-group { margin-bottom: 18px; }
        label {
            display: block; margin-bottom: 7px;
            font-weight: 700; color: var(--secondary); font-size: .85rem;
        }
        label i { color: var(--primary); margin-right: 5px; }

        input[type="date"], select, input[type="file"] {
            width: 100%; padding: 11px 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: .9rem; font-family: inherit; color: var(--text-dark);
            background: #fafbfc; transition: var(--transition);
        }
        input[type="date"]:focus, select:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(242,128,24,.15); background: #fff;
        }
        input[type="file"] { padding: 9px 12px; cursor: pointer; background: #fff; }
        input[type="file"]:focus { outline: none; border-color: var(--primary); }
        .file-hint { font-size: .74rem; color: #bbb; margin-top: 5px; }

        .btn-submit {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 13px 22px; border: none;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff; font-size: .95rem; font-weight: 700;
            cursor: pointer; transition: var(--transition); font-family: inherit;
            box-shadow: 0 4px 14px rgba(242,128,24,.35);
            margin-top: 6px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 7px 20px rgba(242,128,24,.48); }
        .btn-submit:active { transform: translateY(0); }

        .btn-danger {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border: none;
            border-radius: var(--radius-sm);
            background: #e74c3c; color: #fff;
            font-size: .83rem; font-weight: 700;
            cursor: pointer; transition: var(--transition); font-family: inherit;
            box-shadow: 0 3px 10px rgba(231,76,60,.25);
        }
        .btn-danger:hover { background: #c0392b; transform: translateY(-1px); }

        /* ── ALERTS ── */
        .alert {
            padding: 13px 16px;
            border-radius: var(--radius-sm);
            font-size: .88rem; line-height: 1.6;
            margin-bottom: 18px;
            border-left: 4px solid;
            animation: slideIn .3s ease;
        }
        .alert-success { background: var(--primary-pale); border-color: var(--primary); color: #7a3e00; }
        .alert-error   { background: #fdf0ef; border-color: #e74c3c; color: #721c24; }
        .alert-warning { background: #fffbeb; border-color: #f39c12; color: #7d5a00; }
        @keyframes slideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }

        .notify-badge {
            display: flex; align-items: center; gap: 8px;
            background: var(--primary-pale);
            border: 1px solid rgba(242,128,24,.22);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: .81rem; color: var(--secondary); margin-top: 14px;
        }
        .notify-badge i { color: var(--primary); }

        /* ── STEPS ── */
        .step {
            display: flex; gap: 14px; align-items: flex-start;
            padding: 14px 0; border-bottom: 1px solid var(--border);
        }
        .step:last-of-type { border-bottom: none; padding-bottom: 0; }
        .step-num {
            width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .88rem;
            box-shadow: 0 3px 8px rgba(242,128,24,.3);
        }
        .step-title { font-weight: 700; color: var(--secondary); margin-bottom: 3px; font-size: .88rem; }
        .step-desc  { font-size: .82rem; color: var(--text-muted); line-height: 1.5; }
        .info-box {
            background: var(--primary-pale);
            border: 1px solid rgba(242,128,24,.18);
            border-radius: var(--radius-sm);
            padding: 11px 14px; margin-top: 16px;
            font-size: .8rem; color: #7a3e00; line-height: 1.5;
        }
        .info-box i { color: var(--primary); margin-right: 5px; }
        .shift-tag {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff; padding: 4px 12px; border-radius: 20px;
            font-size: .74rem; font-weight: 700;
        }

        /* ── TABLE ── */
        .table-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
        }
        .table-info { font-size: .84rem; color: var(--text-muted); }
        .table-info strong { color: var(--primary); font-size: 1rem; }
        .table-wrapper { overflow-x: auto; border-radius: var(--radius-sm); border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, var(--secondary), var(--secondary-dark)); }
        th {
            padding: 13px 16px; text-align: left;
            font-size: .76rem; font-weight: 700;
            letter-spacing: .07em; text-transform: uppercase;
            color: rgba(255,255,255,.8); white-space: nowrap;
        }
        td { padding: 11px 16px; border-bottom: 1px solid var(--border); font-size: .87rem; color: #333; }
        tbody tr { transition: background .1s; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--primary-pale); }
        tbody tr:nth-child(even) { background: #fafbfc; }
        tbody tr:nth-child(even):hover { background: var(--primary-pale); }
        .id-cell { color: #c5c5c5; font-size: .8rem; }
        .plan-val { font-weight: 700; color: var(--secondary); }
        .empty-cell { text-align: center; padding: 50px; color: #ccc; }
        .empty-cell i { font-size: 2.5rem; display: block; margin-bottom: 12px; }
        .empty-cell p { font-size: .9rem; }

        /* ── FOOTER ── */
        .footer {
            text-align: center; padding: 22px;
            font-size: .77rem; color: #bbb;
            border-top: 1px solid var(--border);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 960px) { .two-col { grid-template-columns: 1fr; } }
        @media (max-width: 640px) {
            .navbar { padding: 0 16px; }
            .main { padding: 0 14px; margin: 18px auto; }
            .page-title-bar, .ticker { padding-left: 16px; padding-right: 16px; }
            .nav-uname, .nav-urole, .brand-sub { display: none; }
        }
    </style>
</head>
<body>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav class="navbar">
   

    <div class="navbar-right">
        <a href="dashboard.php" class="btn-dashboard">
            <i class="fa-solid fa-gauge-high"></i>
            Dashboard
        </a>

        <div class="nav-divider"></div>

        <div class="nav-user">
            <div class="nav-avatar">A</div>
            <div>
                <div class="nav-uname">Admin User</div>
                <div class="nav-urole">Administrator</div>
            </div>
        </div>

        <a href="logout.php" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>



<!-- ══════════════ PAGE TITLE BAR ══════════════ -->
<div class="page-title-bar">
    <h1><i class="fa-solid fa-file-arrow-up"></i> Excel Data Import</h1>
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa-solid fa-gauge-high" style="margin-right:4px;"></i>Dashboard</a>
        <span class="bc-sep"><i class="fa-solid fa-chevron-right"></i></span>
        <span>Import Data</span>
    </div>
</div>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">

    <!-- Stats Strip -->
    <div class="stats-strip">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-database"></i></div>
            <div>
                <div class="stat-num"><?= $totalRows ?></div>
                <div class="stat-label">Total Records</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <div class="stat-num"><?= date('d M') ?></div>
                <div class="stat-label">Today's Date &mdash; <?= date('Y') ?></div>
            </div>
        </div>
       
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-rotate"></i></div>
            <div>
                <div class="stat-num">6</div>
                <div class="stat-label">Available Shifts</div>
            </div>
        </div>
    </div>

    <!-- Two-column: Form + Instructions -->
    <div class="two-col">

        <!-- IMPORT FORM -->
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-upload"></i>
                <h2>Import Excel File</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <?php
                        $ac = 'alert-success';
                        if (strpos($message,'Error') !== false || strpos($message,'Please') !== false) $ac = 'alert-error';
                        elseif (strpos($message,'could not') !== false) $ac = 'alert-warning';
                    ?>
                    <div class="alert <?= $ac ?>"><?= $message ?></div>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="date"><i class="fa-solid fa-calendar"></i> Plan Date</label>
                        <input type="date" name="date" id="date" required>
                    </div>
                    <div class="form-group">
                        <label for="shift"><i class="fa-solid fa-rotate"></i> Shift</label>
                        <select name="shift" id="shift" required>
                            <option value="">&#8212; Select Shift &#8212;</option>
                            <option value="DAY A">DAY A</option>
                            <option value="DAY B">DAY B</option>
                            <option value="DAY C">DAY C</option>
                            <option value="NIGHT A">NIGHT A</option>
                            <option value="NIGHT B">NIGHT B</option>
                            <option value="NIGHT C">NIGHT C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="excel_file"><i class="fa-solid fa-file-excel"></i> Excel File</label>
                        <input type="file" name="excel_file" id="excel_file" accept=".xls,.xlsx" required>
                        <div class="file-hint">Accepted: .xls, .xlsx &nbsp;&middot;&nbsp; Header row is skipped automatically</div>
                    </div>

                    <button type="submit" name="submit" class="btn-submit">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        Upload &amp; Import Data
                    </button>

                    <div class="notify-badge">
                        <i class="fa-solid fa-envelope"></i>
                        Email notification &rarr; <strong style="margin-left:4px;"><?= htmlspecialchars($emailConfig['recipient_email']) ?></strong>
                    </div>
                </form>
            </div>
        </div>

        <!-- INSTRUCTIONS -->
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-circle-info"></i>
                <h2>Import Instructions</h2>
            </div>
            <div class="card-body">
                <div class="step">
                    <div class="step-num">1</div>
                    <div>
                        <div class="step-title">Prepare your Excel file</div>
                        <div class="step-desc">Your file must have a header row followed by data rows. Columns must follow this order: <strong>Icode</strong>, <strong>MoldName</strong>, <strong>CavityName</strong>, <strong>Plan</strong>.</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div>
                        <div class="step-title">Select date &amp; shift</div>
                        <div class="step-desc">Pick the correct plan date and select the matching shift. This will be applied to all rows in the file.</div>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div>
                        <div class="step-title">Upload &amp; confirm</div>
                        <div class="step-desc">Click <strong>Upload &amp; Import Data</strong>. An import summary will appear and a confirmation email is sent automatically.</div>
                    </div>
                </div>

                <div class="info-box">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <strong>Note:</strong> The header row and empty rows are skipped automatically. All database inserts use prepared statements to prevent SQL injection.
                </div>

                <div style="margin-top:20px;">
                    <div style="font-size:.82rem;font-weight:700;color:var(--secondary);margin-bottom:9px;">
                        <i class="fa-solid fa-rotate" style="color:var(--primary);margin-right:5px;"></i>Available Shifts
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:7px;">
                        <?php foreach(['DAY A','DAY B','DAY C','NIGHT A','NIGHT B','NIGHT C'] as $s): ?>
                            <span class="shift-tag"><?= $s ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DATA TABLE -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-table-list"></i>
            <h2>Daily Plan Records</h2>
            <?php if ($totalRows > 0): ?>
                <span class="card-header-badge"><?= $totalRows ?> Records</span>
            <?php endif; ?>
        </div>
        <div class="card-body">

            <?php if (!empty($message_view)): ?>
                <div class="alert <?= strpos($message_view,'Error') !== false ? 'alert-error' : 'alert-success' ?>">
                    <?= htmlspecialchars($message_view) ?>
                </div>
            <?php endif; ?>

            <div class="table-toolbar">
                <div class="table-info">
                    Total: <strong><?= $totalRows ?></strong> record<?= $totalRows !== 1 ? 's' : '' ?>
                </div>
                <?php if ($totalRows > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="delete_all" value="1">
                        <button type="submit" class="btn-danger"
                            onclick="return confirm('Are you sure you want to delete ALL records? This cannot be undone.');">
                            <i class="fa-solid fa-trash-can"></i> Delete All Records
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Shift</th>
                            <th>Icode</th>
                            <th>Mold Name</th>
                            <th>Cavity Name</th>
                            <th>Plan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalRows > 0):
                            $result->data_seek(0);
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="id-cell">#<?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['Date']) ?></td>
                                    <td><span class="shift-tag" style="font-size:.75rem;"><?= htmlspecialchars($row['Shift']) ?></span></td>
                                    <td><?= htmlspecialchars($row['Icode']) ?></td>
                                    <td><?= htmlspecialchars($row['MoldName']) ?></td>
                                    <td><?= htmlspecialchars($row['CavityName']) ?></td>
                                    <td class="plan-val"><?= htmlspecialchars($row['Plan']) ?></td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="empty-cell">
                                    <i class="fa-solid fa-inbox"></i>
                                    <p>No records found. Import an Excel file to get started.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">
        &copy; <?= date('Y') ?> Bi Pro Plan S &nbsp;&middot;&nbsp; All rights reserved &nbsp;&middot;&nbsp; Planning &amp; Management System
    </div>

</div><!-- /.main -->
</body>
</html>
<?php $conn->close(); ?>