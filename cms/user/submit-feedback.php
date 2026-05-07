<?php
session_start();
include('include/config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   LOGIN CHECK
========================= */
if (!isset($_SESSION['id']) || $_SESSION['id'] == '') {
    header('location:index.php');
    exit;
}

$userId = (int) $_SESSION['id'];

/* =========================
   GET USER DATA
========================= */
$userData = ['fullName'=>'User','userEmail'=>''];
$q = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id='$userId'");
if ($q && mysqli_num_rows($q) > 0) {
    $userData = mysqli_fetch_assoc($q);
}

/* =========================
   FORM HANDLING
========================= */
$submitSuccess = false;
$submitError   = false;
$errorMessage  = '';

if (isset($_POST['submit_feedback'])) {

    if (
        empty($_POST['feedback_type']) ||
        empty($_POST['category']) ||
        empty($_POST['rating']) ||
        empty($_POST['subject']) ||
        empty($_POST['comments'])
    ) {
        $submitError = true;
        $errorMessage = "Please fill all required fields.";
    } else {

        $feedbackType = mysqli_real_escape_string($con, $_POST['feedback_type']);
        $category     = mysqli_real_escape_string($con, $_POST['category']);
        $rating       = (int) $_POST['rating'];
        $subject      = mysqli_real_escape_string($con, $_POST['subject']);
        $comments     = mysqli_real_escape_string($con, $_POST['comments']);

        $recommend    = isset($_POST['recommend']) ? 1 : 0;
        $contactBack  = isset($_POST['contact_back']) ? 1 : 0;
        $anonymous    = isset($_POST['anonymous']) ? 1 : 0;

        if ($rating < 1 || $rating > 5) {
            $submitError = true;
            $errorMessage = "Invalid rating value.";
        } else {

            $sql = "
            INSERT INTO tbl_customer_feedback
            (userId, feedback_type, category, rating, subject, comments, recommend, contact_back, anonymous, status, created_at)
            VALUES
            (
                '$userId',
                '$feedbackType',
                '$category',
                '$rating',
                '$subject',
                '$comments',
                '$recommend',
                '$contactBack',
                '$anonymous',
                'pending',
                NOW()
            )";

            if (mysqli_query($con, $sql)) {
                $submitSuccess = true;
            } else {
                $submitError = true;
                $errorMessage = mysqli_error($con);
            }
        }
    }
}

/* =========================
   USER INITIALS
========================= */
$initials = '';
if (!empty($userData['fullName'])) {
    $p = explode(' ', $userData['fullName']);
    $initials = strtoupper(substr($p[0],0,1));
    if (count($p) > 1) {
        $initials .= strtoupper(substr(end($p),0,1));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Feedback</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ============================================================
   CSS VARIABLES & RESET
   ============================================================ */
:root {
    --primary-orange: #F28018;
    --secondary-orange: #e67e22;
    --dark-gray: #1e1e2e;
    --light-gray: #f0f0f0;
    --border-gray: #e0e0e0;
    --bg-light: #f0f2f5;
    --success: #27ae60;
    --warning: #f39c12;
    --error: #e74c3c;
    --text-gray: #64748b;
    --orange-light: rgba(242, 128, 24, 0.08);
    --orange-medium: rgba(242, 128, 24, 0.15);
    --success-light: rgba(39, 174, 96, 0.1);
    --white: #ffffff;
    --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
    --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    --gradient-bg: linear-gradient(160deg, #fff7ee 0%, #f0f2f5 50%, #eef2ff 100%);
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --ring-orange: 0 0 0 3px rgba(242, 128, 24, 0.25);
    --radius: 0.85rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--gradient-bg);
    min-height: 100vh;
    color: var(--dark-gray);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
}

/* ============================================================
   BACKGROUND DECORATION (floating blobs)
   ============================================================ */
body::before,
body::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.35;
    pointer-events: none;
    z-index: 0;
}
body::before {
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, #fde68a, #F28018);
    top: -150px;
    right: -150px;
}
body::after {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, #c7d2fe, #a5b4fc);
    bottom: -120px;
    left: -120px;
}

/* ============================================================
   HEADER / NAVBAR
   ============================================================ */
.header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-bottom: 1px solid rgba(255,255,255,0.6);
    padding: 0.9rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 20px rgba(0,0,0,0.06);
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 42px;
    height: 42px;
    background: var(--gradient-1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.2rem;
    box-shadow: 0 4px 14px rgba(242,128,24,0.35);
}

.brand-text {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--dark-gray);
    letter-spacing: -0.5px;
}
.brand-text span {
    background: var(--gradient-1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.user-name-text {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--dark-gray);
    text-align: right;
}
.user-name-text small {
    display: block;
    font-weight: 400;
    color: var(--text-gray);
    font-size: 0.78rem;
}

.user-avatar {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 50%;
    background: var(--gradient-1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: 700;
    font-size: 0.95rem;
    box-shadow: 0 3px 12px rgba(242,128,24,0.3);
    border: 2.5px solid #fff;
}

/* ============================================================
   PAGE WRAPPER
   ============================================================ */
.page-wrapper {
    position: relative;
    z-index: 1;
    max-width: 920px;
    margin: 0 auto;
    padding: 2.4rem 1.8rem 3rem;
}

/* ============================================================
   PAGE HEADER
   ============================================================ */
.page-header {
    text-align: center;
    margin-bottom: 2.2rem;
}

.page-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 20px;
    background: var(--gradient-1);
    color: #fff;
    font-size: 1.7rem;
    margin-bottom: 1rem;
    box-shadow: 0 6px 24px rgba(242,128,24,0.35);
}

.page-title {
    font-family: 'DM Serif Display', serif;
    font-size: 2.3rem;
    font-weight: 400;
    color: var(--dark-gray);
    letter-spacing: -0.5px;
    margin-bottom: 0.35rem;
}

.page-subtitle {
    font-size: 1rem;
    color: var(--text-gray);
    font-weight: 400;
}

/* ============================================================
   BREADCRUMB
   ============================================================ */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 1.6rem;
    color: var(--text-gray);
    font-size: 0.85rem;
    font-weight: 500;
}
.breadcrumb a {
    color: var(--primary-orange);
    text-decoration: none;
    transition: color 0.2s;
}
.breadcrumb a:hover { color: var(--secondary-orange); }
.breadcrumb .sep { color: #ccc; font-size: 0.7rem; }

/* ============================================================
   PROGRESS STEPS
   ============================================================ */
.progress-steps {
    display: flex;
    align-items: stretch;
    justify-content: center;
    margin-bottom: 2rem;
    position: relative;
}
.progress-track {
    position: absolute;
    top: 19px;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 3px;
    background: var(--border-gray);
    border-radius: 2px;
    z-index: 0;
}
.progress-track-fill {
    height: 100%;
    width: 100%;
    background: var(--gradient-1);
    border-radius: 2px;
    transition: width 0.5s ease;
}
.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    position: relative;
    z-index: 1;
    flex: 0 0 auto;
    width: 110px;
}
.step-dot {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid var(--border-gray);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-gray);
    transition: all 0.3s;
    box-shadow: var(--shadow-sm);
}
.step.active .step-dot {
    border-color: var(--primary-orange);
    background: var(--gradient-1);
    color: #fff;
    box-shadow: 0 4px 14px rgba(242,128,24,0.4);
}
.step-label {
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--text-gray);
    text-transform: uppercase;
    letter-spacing: 0.6px;
}
.step.active .step-label { color: var(--primary-orange); }

/* ============================================================
   MAIN CARD
   ============================================================ */
.form-card {
    background: var(--white);
    border-radius: 1.6rem;
    padding: 2.8rem 3rem;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255,255,255,0.9);
    position: relative;
    overflow: hidden;
    animation: slideUp 0.55s cubic-bezier(.22,.68,0,1.2) both;
}
.form-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: var(--gradient-1);
    border-radius: 1.6rem 1.6rem 0 0;
}

/* ============================================================
   ALERTS
   ============================================================ */
.alert {
    padding: 1rem 1.4rem;
    border-radius: 0.85rem;
    margin-bottom: 1.8rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    font-weight: 500;
    font-size: 0.93rem;
    animation: fadeIn 0.35s ease;
}
.alert-icon {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.alert-success {
    background: #edfbf0;
    color: var(--success);
    border: 1px solid #b8ecc8;
}
.alert-success .alert-icon { background: #d4f5df; }
.alert-error {
    background: #fef2f2;
    color: var(--error);
    border: 1px solid #fca5a5;
}
.alert-error .alert-icon { background: #fee2e2; }

/* ============================================================
   FORM SECTIONS
   ============================================================ */
.form-section {
    margin-bottom: 2.2rem;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.4rem;
    padding-bottom: 0.8rem;
    border-bottom: 2px solid #f3f4f6;
}

.section-icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.7rem;
    background: var(--orange-light);
    color: var(--primary-orange);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark-gray);
    letter-spacing: -0.3px;
}

/* ============================================================
   FORM GRID
   ============================================================ */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.4rem 1.6rem;
}
.form-group { }
.form-group.full-width { grid-column: span 2; }

/* ============================================================
   LABELS
   ============================================================ */
.form-label {
    display: block;
    font-weight: 600;
    color: var(--dark-gray);
    margin-bottom: 0.45rem;
    font-size: 0.9rem;
    letter-spacing: 0.1px;
}
.required { color: var(--error); margin-left: 2px; }

/* ============================================================
   INPUTS / SELECTS / TEXTAREAS
   ============================================================ */
.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.82rem 1rem;
    border: 2px solid var(--border-gray);
    border-radius: var(--radius);
    font-size: 0.92rem;
    font-family: inherit;
    color: var(--dark-gray);
    background: #fafafa;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary-orange);
    box-shadow: var(--ring-orange);
    background: #fff;
}
.form-input::placeholder,
.form-textarea::placeholder {
    color: #aaa;
}

.form-select {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 2.4rem;
    cursor: pointer;
}

.form-textarea {
    resize: vertical;
    min-height: 110px;
    line-height: 1.55;
}

.form-help {
    font-size: 0.8rem;
    color: var(--text-gray);
    margin-top: 0.38rem;
}

/* ============================================================
   STAR RATING
   ============================================================ */
.rating-wrapper {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    flex-wrap: wrap;
}
.stars {
    display: flex;
    gap: 4px;
    flex-direction: row-reverse;
}
.stars input[type="radio"] {
    display: none;
}
.stars label {
    font-size: 2.2rem;
    color: #d1d5db;
    cursor: pointer;
    transition: color 0.18s, transform 0.15s;
    line-height: 1;
}
.stars label:hover,
.stars label:hover ~ label,
.stars input[type="radio"]:checked ~ label {
    color: #fbbf24;
}
.stars label:hover {
    transform: scale(1.15);
}
.rating-badge {
    background: var(--orange-light);
    color: var(--primary-orange);
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.25rem 0.7rem;
    border-radius: 20px;
    display: none;
    white-space: nowrap;
    border: 1px solid rgba(242,128,24,0.2);
}
.rating-badge.visible { display: inline-block; animation: fadeIn 0.25s ease; }

/* ============================================================
   CHECKBOX / OPTION CARDS
   ============================================================ */
.options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.9rem;
}

.option-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.5rem;
    padding: 1.2rem 0.8rem 1rem;
    border: 2px solid var(--border-gray);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.22s ease;
    background: #fafafa;
    position: relative;
}
.option-card:hover {
    border-color: var(--primary-orange);
    background: var(--orange-light);
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}
.option-card.checked {
    border-color: var(--primary-orange);
    background: var(--orange-medium);
    box-shadow: 0 0 0 3px rgba(242,128,24,0.15);
    transform: translateY(-2px);
}
.option-card input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0; height: 0;
}
.option-card-icon {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: #fff;
    border: 2px solid var(--border-gray);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    color: var(--text-gray);
    transition: all 0.22s;
}
.option-card.checked .option-card-icon {
    background: var(--gradient-1);
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(242,128,24,0.35);
}
.option-card-title {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--dark-gray);
}
.option-card-desc {
    font-size: 0.76rem;
    color: var(--text-gray);
    line-height: 1.35;
}

/* tick badge */
.option-tick {
    position: absolute;
    top: -8px; right: -8px;
    width: 22px; height: 22px;
    border-radius: 50%;
    background: var(--gradient-1);
    color: #fff;
    font-size: 0.68rem;
    display: flex; align-items: center; justify-content: center;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.22s;
    box-shadow: 0 2px 6px rgba(242,128,24,0.4);
}
.option-card.checked .option-tick {
    opacity: 1;
    transform: scale(1);
}

/* ============================================================
   BUTTONS
   ============================================================ */
.btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2.2rem;
    padding-top: 1.8rem;
    border-top: 2px solid #f3f4f6;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.9rem 1.8rem;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 0.95rem;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.22s ease;
    text-decoration: none;
    letter-spacing: 0.1px;
}

.btn-primary {
    background: var(--gradient-1);
    color: var(--white);
    box-shadow: 0 4px 18px rgba(242,128,24,0.35);
    flex: 1;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(242,128,24,0.42);
}
.btn-primary:active { transform: translateY(0); }

.btn-secondary {
    background: var(--white);
    color: var(--text-gray);
    border: 2px solid var(--border-gray);
}
.btn-secondary:hover {
    border-color: var(--primary-orange);
    color: var(--primary-orange);
    background: var(--orange-light);
}

/* ============================================================
   CHAR COUNT
   ============================================================ */
.char-count {
    font-size: 0.77rem;
    color: var(--text-gray);
    text-align: right;
    margin-top: 0.3rem;
}

/* ============================================================
   FOOTER NOTE
   ============================================================ */
.footer-note {
    text-align: center;
    margin-top: 2rem;
    font-size: 0.82rem;
    color: var(--text-gray);
}
.footer-note a { color: var(--primary-orange); text-decoration: none; }
.footer-note a:hover { text-decoration: underline; }

/* ============================================================
   ANIMATIONS
   ============================================================ */
@keyframes slideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* ============================================================
   SCROLLBAR
   ============================================================ */
::-webkit-scrollbar { width: 7px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb {
    background: var(--primary-orange);
    border-radius: 4px;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 700px) {
    .page-wrapper { padding: 1.4rem 1rem 2.5rem; }
    .form-card { padding: 1.8rem 1.4rem; }
    .form-grid { grid-template-columns: 1fr; }
    .form-group.full-width { grid-column: span 1; }
    .options-grid { grid-template-columns: 1fr; }
    .btn-group { flex-direction: column; }
    .page-title { font-size: 1.8rem; }
    .header { padding: 0.75rem 1rem; }
    .user-name-text { display: none; }
    .progress-track { width: 50%; }
}

</style>
</head>
<body>



<!-- ========================================================
     PAGE BODY
     ======================================================== -->
<div class="page-wrapper">

    

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon"><i class="fas fa-comments"></i></div>
        <h1 class="page-title">Submit Your Feedback</h1>
        <p class="page-subtitle">We value your opinion — help us serve you better</p>
    </div>

    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="progress-track"><div class="progress-track-fill"></div></div>
        <div class="step active">
            <div class="step-dot">1</div>
            <div class="step-label">Details</div>
        </div>
        <div class="step active">
            <div class="step-dot">2</div>
            <div class="step-label">Rating</div>
        </div>
        <div class="step active">
            <div class="step-dot">3</div>
            <div class="step-label">Options</div>
        </div>
        <div class="step active">
            <div class="step-dot"><i class="fas fa-check" style="font-size:.78rem"></i></div>
            <div class="step-label">Submit</div>
        </div>
    </div>

    <!-- =====================================================
         FORM CARD
         ================================================== -->
    <div class="form-card">

        <!-- Alerts -->
        <?php if ($submitSuccess): ?>
        <div class="alert alert-success">
            <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
            <div>
                <strong>Success!</strong> Your feedback has been submitted successfully. Thank you!
            </div>
        </div>
        <?php endif; ?>

        <?php if ($submitError): ?>
        <div class="alert alert-error">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <strong>Error —</strong> <?= htmlspecialchars($errorMessage) ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="post" id="feedbackForm">

            <!-- ============================================
                 SECTION 1 — Feedback Info
                 ========================================= -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="section-title">Feedback Information</div>
                </div>

                <div class="form-grid">

                    <!-- Feedback Type -->
                    <div class="form-group">
                        <label class="form-label">Feedback Type <span class="required">*</span></label>
                        <select name="feedback_type" class="form-select" required>
                            <option value="">— Select type —</option>
                            <option value="General Feedback">General Feedback</option>
                            <option value="Service Quality">Service Quality</option>
                            <option value="Product Review">Product Review</option>
                            <option value="Suggestion">Suggestion</option>
                        </select>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">— Select category —</option>
                            <option value="Tire Quality">Tire Quality</option>
                            <option value="Customer Service">Customer Service</option>
                            <option value="Pricing">Pricing</option>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="form-group full-width">
                        <label class="form-label">Subject <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-input" placeholder="Enter a short subject line…" required>
                        <div class="form-help"><i class="fas fa-info-circle" style="font-size:.72rem"></i> A brief summary of your feedback</div>
                    </div>

                </div>
            </div>

            <!-- ============================================
                 SECTION 2 — Rating & Comments
                 ========================================= -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-star"></i></div>
                    <div class="section-title">Rating & Comments</div>
                </div>

                <!-- Star Rating -->
                <div class="form-group">
                    <label class="form-label">Your Rating <span class="required">*</span></label>
                    <div class="rating-wrapper">
                        <div class="stars" id="starContainer">
                            <input type="radio" name="rating" value="5" id="r5">
                            <label for="r5">★</label>
                            <input type="radio" name="rating" value="4" id="r4">
                            <label for="r4">★</label>
                            <input type="radio" name="rating" value="3" id="r3">
                            <label for="r3">★</label>
                            <input type="radio" name="rating" value="2" id="r2">
                            <label for="r2">★</label>
                            <input type="radio" name="rating" value="1" id="r1">
                            <label for="r1">★</label>
                        </div>
                        <span class="rating-badge" id="ratingBadge">Excellent!</span>
                    </div>
                    <div class="form-help" style="margin-top:.5rem"><i class="fas fa-info-circle" style="font-size:.72rem"></i> Click a star to rate your experience</div>
                </div>

                <!-- Comments -->
                <div class="form-group" style="margin-top:1rem">
                    <label class="form-label">Comments <span class="required">*</span></label>
                    <textarea name="comments" class="form-textarea" id="commentsArea" placeholder="Share your thoughts, suggestions or concerns…" required maxlength="1000"></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 1000 characters</div>
                </div>
            </div>

            <!-- ============================================
                 SECTION 3 — Options
                 ========================================= -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon"><i class="fas fa-sliders"></i></div>
                    <div class="section-title">Additional Options</div>
                </div>

                <div class="options-grid">

                    <!-- Recommend -->
                    <label class="option-card" id="cardRecommend" onclick="toggleCard('cardRecommend','recommend')">
                        <input type="checkbox" name="recommend" id="recommend">
                        <div class="option-tick"><i class="fas fa-check"></i></div>
                        <div class="option-card-icon"><i class="fas fa-thumbs-up"></i></div>
                        <div class="option-card-title">Recommend</div>
                        <div class="option-card-desc">I would recommend this service to others</div>
                    </label>

                    <!-- Contact Back -->
                    <label class="option-card" id="cardContactBack" onclick="toggleCard('cardContactBack','contact_back')">
                        <input type="checkbox" name="contact_back" id="contact_back">
                        <div class="option-tick"><i class="fas fa-check"></i></div>
                        <div class="option-card-icon"><i class="fas fa-envelope"></i></div>
                        <div class="option-card-title">Contact Me</div>
                        <div class="option-card-desc">I'd like someone to follow up with me</div>
                    </label>

                    <!-- Anonymous -->
                    <label class="option-card" id="cardAnonymous" onclick="toggleCard('cardAnonymous','anonymous')">
                        <input type="checkbox" name="anonymous" id="anonymous">
                        <div class="option-tick"><i class="fas fa-check"></i></div>
                        <div class="option-card-icon"><i class="fas fa-eye-slash"></i></div>
                        <div class="option-card-title">Anonymous</div>
                        <div class="option-card-desc">Submit this feedback anonymously</div>
                    </label>

                </div>
            </div>

            <!-- ============================================
                 BUTTONS
                 ========================================= -->
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('feedbackForm').reset(); resetAll();">
                    <i class="fas fa-redo"></i> Reset
                </button>
                <button type="submit" name="submit_feedback" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Feedback
                </button>
            </div>

        </form>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        Your feedback is important to us. Read our <a href="#">Privacy Policy</a> before submitting.
    </div>

</div><!-- end page-wrapper -->


<!-- ========================================================
     JAVASCRIPT
     ======================================================== -->
<script>
/* --- Character Counter --- */
const commentsArea = document.getElementById('commentsArea');
const charCount    = document.getElementById('charCount');
commentsArea.addEventListener('input', function(){
    charCount.textContent = this.value.length;
});

/* --- Rating Badge Text --- */
const ratingLabels = ['', 'Disappointing', 'Needs Improvement', 'Acceptable', 'Good', 'Excellent!'];
const ratingBadge  = document.getElementById('ratingBadge');
document.querySelectorAll('.stars input[type="radio"]').forEach(function(radio){
    radio.addEventListener('change', function(){
        const val = parseInt(this.value, 10);
        ratingBadge.textContent = ratingLabels[val];
        ratingBadge.classList.add('visible');
    });
});

/* --- Option Card Toggle --- */
function toggleCard(cardId, inputId){
    const card  = document.getElementById(cardId);
    const input = document.getElementById(inputId);
    setTimeout(function(){
        card.classList.toggle('checked', input.checked);
    }, 30);
}

/* --- Reset Helper --- */
function resetAll(){
    document.querySelectorAll('.option-card').forEach(function(c){ c.classList.remove('checked'); });
    ratingBadge.classList.remove('visible');
    charCount.textContent = '0';
}
</script>

</body>
</html>