<?php
session_start();
include('include/config.php');
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
} else {
    date_default_timezone_set('Asia/Kolkata');
    $currentTime = date('d-m-Y h:i:s A', time());

    // Fetch admin details
    $adminId = intval($_SESSION["aid"]);
    $adminQuery = mysqli_query($con, "SELECT * FROM admin WHERE id='$adminId'");
    $adminData = mysqli_fetch_array($adminQuery);

    $uid = intval($_GET['uid']);

    // Fetch distinct brands from tire_details
    $brandsQuery = mysqli_query($con, "SELECT DISTINCT Brand FROM tire_details WHERE Brand IS NOT NULL AND Brand != '' ORDER BY Brand ASC");
    $allBrands = [];
    while ($brandRow = mysqli_fetch_array($brandsQuery)) {
        $allBrands[] = $brandRow['Brand'];
    }

    if (isset($_POST['submit'])) {
        $cus_id = mysqli_real_escape_string($con, $_POST['cus_id']);
        $acm_name = mysqli_real_escape_string($con, $_POST['acm_name']);
        $acm_ref = mysqli_real_escape_string($con, $_POST['acm_ref']);
        $fullName = mysqli_real_escape_string($con, $_POST['fullName']);
        $company_rn = mysqli_real_escape_string($con, $_POST['company_rn']);
        $country = mysqli_real_escape_string($con, $_POST['country']);
        $registerd_Address = mysqli_real_escape_string($con, $_POST['registerd_Address']);
        $userEmail = mysqli_real_escape_string($con, $_POST['userEmail']);
        $delivery_address = mysqli_real_escape_string($con, $_POST['delivery_address']);
        $status = intval($_POST['status']);
        $customer_code = mysqli_real_escape_string($con, $_POST['customer_code']);

        // Contact Person 1
        $contact_person1_name = mysqli_real_escape_string($con, $_POST['contact_person1_name']);
        $contact_number1_office = mysqli_real_escape_string($con, $_POST['contact_number1_office']);
        $contact_number1_mobile = mysqli_real_escape_string($con, $_POST['contact_number1_mobile']);
        $email_address1 = mysqli_real_escape_string($con, $_POST['email_address1']);

        // Business Details
        $business_registration_number = mysqli_real_escape_string($con, $_POST['business_registration_number']);
        $tin_number = mysqli_real_escape_string($con, $_POST['tin_number']);

        // Financial
        $standard_payment_term = mysqli_real_escape_string($con, $_POST['standard_payment_term']);
        $functional_currency = mysqli_real_escape_string($con, $_POST['functional_currency']);
        $incoterm = mysqli_real_escape_string($con, $_POST['incoterm']);
        $port_of_discharge = mysqli_real_escape_string($con, $_POST['port_of_discharge']);
        $required_certificate = mysqli_real_escape_string($con, $_POST['required_certificate']);
        $payment_rate = mysqli_real_escape_string($con, $_POST['payment_rate']);

        // Account Manager
        $account_manager_name = mysqli_real_escape_string($con, $_POST['account_manager_name']);
        $account_manager_code = mysqli_real_escape_string($con, $_POST['account_manager_code']);

        // Payment Rate Status
        $payment_rate_status = mysqli_real_escape_string($con, $_POST['payment_rate_status']);

        // Update password only if provided
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $query = mysqli_query($con, "UPDATE users SET 
                cus_id='$cus_id',
                acm_name='$acm_name',
                acm_ref='$acm_ref',
                fullName='$fullName',
                company_rn='$company_rn',
                Country='$country',
                registerd_Address='$registerd_Address',
                userEmail='$userEmail',
                password='$password',
                delivery_address='$delivery_address',
                status='$status',
                customer_code='$customer_code',
                contact_person1_name='$contact_person1_name',
                contact_number1_office='$contact_number1_office',
                contact_number1_mobile='$contact_number1_mobile',
                email_address1='$email_address1',
                business_registration_number='$business_registration_number',
                tin_number='$tin_number',
                standard_payment_term='$standard_payment_term',
                functional_currency='$functional_currency',
                incoterm='$incoterm',
                port_of_discharge='$port_of_discharge',
                required_certificate='$required_certificate',
                payment_rate='$payment_rate',
                payment_rate_status='$payment_rate_status',
                account_manager_name='$account_manager_name',
                account_manager_code='$account_manager_code',
                updationDate=NOW()
                WHERE id='$uid'");
        } else {
            $query = mysqli_query($con, "UPDATE users SET 
                cus_id='$cus_id',
                acm_name='$acm_name',
                acm_ref='$acm_ref',
                fullName='$fullName',
                company_rn='$company_rn',
                Country='$country',
                registerd_Address='$registerd_Address',
                userEmail='$userEmail',
                delivery_address='$delivery_address',
                status='$status',
                customer_code='$customer_code',
                contact_person1_name='$contact_person1_name',
                contact_number1_office='$contact_number1_office',
                contact_number1_mobile='$contact_number1_mobile',
                email_address1='$email_address1',
                business_registration_number='$business_registration_number',
                tin_number='$tin_number',
                standard_payment_term='$standard_payment_term',
                functional_currency='$functional_currency',
                incoterm='$incoterm',
                port_of_discharge='$port_of_discharge',
                required_certificate='$required_certificate',
                payment_rate='$payment_rate',
                payment_rate_status='$payment_rate_status',
                account_manager_name='$account_manager_name',
                account_manager_code='$account_manager_code',
                updationDate=NOW()
                WHERE id='$uid'");
        }

        if ($query) {
            // Handle dif_rate brand-specific rates
            if ($payment_rate_status === 'dif_rate') {
                mysqli_query($con, "DELETE FROM customer_rate WHERE cus_id='$cus_id'");
                if (!empty($_POST['brand_rates']) && is_array($_POST['brand_rates'])) {
                    foreach ($_POST['brand_rates'] as $brand => $rate) {
                        if ($rate !== '' && is_numeric($rate)) {
                            $brand_esc = mysqli_real_escape_string($con, $brand);
                            $rate_esc = mysqli_real_escape_string($con, $rate);
                            mysqli_query($con, "INSERT INTO customer_rate (cus_id, brand, payment_rate) 
                                VALUES ('$cus_id', '$brand_esc', '$rate_esc')
                                ON DUPLICATE KEY UPDATE payment_rate='$rate_esc'");
                        }
                    }
                }
            } else {
                mysqli_query($con, "DELETE FROM customer_rate WHERE cus_id='$cus_id'");
            }

            // ── Handle customers_brand assignment ──────────────────────────
            // Convert cus_id (varchar in customers_brand) — use numeric uid for int cus_id
            $cus_id_int = intval($cus_id);

            // Delete all existing brand assignments for this customer
            mysqli_query($con, "DELETE FROM customers_brand WHERE cus_id='$cus_id_int'");

            // Re-insert selected brands
            if (!empty($_POST['assigned_brands']) && is_array($_POST['assigned_brands'])) {
                foreach ($_POST['assigned_brands'] as $ab) {
                    $ab_esc = mysqli_real_escape_string($con, $ab);
                    mysqli_query($con, "INSERT INTO customers_brand (cus_id, brand) VALUES ('$cus_id_int', '$ab_esc')");
                }
            }

            echo '<script>alert("User details updated successfully")</script>';
            echo "<script>window.location.href='manage-users.php'</script>";
        } else {
            echo '<script>alert("Something went wrong. Please try again")</script>';
        }
    }

    $userQuery = mysqli_query($con, "SELECT * FROM users WHERE id='$uid'");
    $userData = mysqli_fetch_array($userQuery);

    // Fetch existing brand rates for this customer
    $existingRates = [];
    if (!empty($userData['cus_id'])) {
        $cus_id_safe = mysqli_real_escape_string($con, $userData['cus_id']);
        $ratesQuery = mysqli_query($con, "SELECT * FROM customer_rate WHERE cus_id='$cus_id_safe'");
        while ($rateRow = mysqli_fetch_array($ratesQuery)) {
            $existingRates[$rateRow['brand']] = $rateRow['payment_rate'];
        }
    }

    // Fetch existing assigned brands from customers_brand
    $assignedBrands = [];
    if (!empty($userData['cus_id'])) {
        $cus_id_int_fetch = intval($userData['cus_id']);
        $cbQuery = mysqli_query($con, "SELECT brand FROM customers_brand WHERE cus_id='$cus_id_int_fetch'");
        while ($cbRow = mysqli_fetch_array($cbQuery)) {
            $assignedBrands[] = $cbRow['brand'];
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - CMS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --medium-gray: #343a40;
            --black: #000000;
            --red: #FF0000;
            --red-accent: #ff4757;
            --border-gray: #e0e0e0;
            --light-border: #CCCCCC;
            --bg-light: #f8fafc;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #e74c3c;
            --text-gray: #64748b;
            --orange-light: rgba(242, 128, 24, 0.1);
            --success-light: rgba(39, 174, 96, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --error-light: rgba(231, 76, 60, 0.1);
            --white: #ffffff;
            --gradient-1: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-2: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            --gradient-3: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --ring-orange: 0 0 0 3px rgba(242, 128, 24, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            padding: 2rem;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .back-button {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1.5rem; background: var(--white); color: var(--dark-gray);
            text-decoration: none; border-radius: 0.75rem; font-weight: 600;
            transition: all 0.2s; box-shadow: var(--shadow); margin-bottom: 2rem;
            border: 1px solid var(--border-gray);
        }
        .back-button:hover { background: var(--primary-orange); color: var(--white); transform: translateX(-4px); box-shadow: var(--shadow-md); }

        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 2rem; font-weight: 800; color: var(--dark-gray); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .page-title i { color: var(--primary-orange); }
        .page-subtitle { color: var(--text-gray); font-size: 1rem; }

        .info-banner {
            background: var(--orange-light); border-left: 4px solid var(--primary-orange);
            padding: 1rem 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .info-banner i { color: var(--primary-orange); font-size: 1.5rem; }

        .card {
            background: var(--white); border-radius: 1rem; border: 1px solid var(--border-gray);
            overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 2rem;
        }
        .card-header {
            padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-gray);
            background: var(--gradient-1); color: var(--white);
        }
        .card-title { font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem; }
        .card-body { padding: 2rem; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-label { font-size: 0.9rem; font-weight: 600; color: var(--dark-gray); display: flex; align-items: center; gap: 0.25rem; }
        .required { color: var(--error); }
        .form-input, .form-select, .form-textarea {
            padding: 0.75rem 1rem; border: 1px solid var(--border-gray); border-radius: 0.5rem;
            background: var(--white); font-size: 0.9rem; font-family: inherit; transition: all 0.2s; outline: none;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--primary-orange); box-shadow: var(--ring-orange); }
        .form-textarea { resize: vertical; min-height: 100px; }
        .form-help { font-size: 0.8rem; color: var(--text-gray); font-style: italic; }

        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.75rem 1.5rem; border: none; border-radius: 0.75rem;
            font-weight: 600; text-decoration: none; cursor: pointer; transition: all 0.2s; font-size: 0.9rem;
        }
        .btn-primary { background: var(--gradient-1); color: var(--white); box-shadow: var(--shadow); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .btn-secondary { background: var(--light-gray); color: var(--dark-gray); border: 1px solid var(--border-gray); }
        .btn-secondary:hover { background: var(--border-gray); }
        .btn-sm { padding: 0.4rem 0.85rem; font-size: 0.8rem; border-radius: 0.5rem; }
        .btn-danger { background: var(--gradient-3); color: var(--white); }
        .btn-danger:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .btn-success { background: var(--gradient-2); color: var(--white); }
        .btn-success:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }

        .form-actions {
            display: flex; gap: 1rem; justify-content: flex-end;
            margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-gray);
        }

        /* Payment Rate Toggle */
        .rate-toggle-group { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .rate-toggle-btn {
            flex: 1; padding: 0.85rem 1.5rem; border: 2px solid var(--border-gray); border-radius: 0.75rem;
            background: var(--white); color: var(--text-gray); font-weight: 600; font-size: 0.95rem;
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .rate-toggle-btn:hover { border-color: var(--primary-orange); color: var(--primary-orange); }
        .rate-toggle-btn.active { background: var(--gradient-1); border-color: var(--primary-orange); color: var(--white); box-shadow: var(--shadow-md); }
        .rate-toggle-btn.active-green { background: var(--gradient-2); border-color: var(--success); color: var(--white); box-shadow: var(--shadow-md); }

        .brand-rates-container { display: none; margin-top: 1rem; }
        .brand-rates-container.visible { display: block; }
        .one-rate-container { display: none; }
        .one-rate-container.visible { display: block; }

        .brand-rates-info {
            background: #fff8f0; border: 1px solid #ffd199; border-radius: 0.5rem;
            padding: 0.85rem 1.25rem; margin-bottom: 1rem; font-size: 0.875rem; color: #92400e;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .brand-rates-table { width: 100%; border-collapse: collapse; border-radius: 0.75rem; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--border-gray); }
        .brand-rates-table thead { background: var(--light-gray); }
        .brand-rates-table th { padding: 0.85rem 1.25rem; text-align: left; font-size: 0.85rem; font-weight: 700; color: var(--dark-gray); border-bottom: 1px solid var(--border-gray); }
        .brand-rates-table td { padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border-gray); font-size: 0.9rem; }
        .brand-rates-table tr:last-child td { border-bottom: none; }
        .brand-rates-table tr:hover td { background: #fafafa; }

        .brand-rate-input {
            width: 140px; padding: 0.5rem 0.75rem; border: 1px solid var(--border-gray);
            border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s; outline: none;
        }
        .brand-rate-input:focus { border-color: var(--primary-orange); box-shadow: var(--ring-orange); }

        .brand-badge {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.75rem; background: var(--orange-light); color: var(--primary-orange);
            border-radius: 9999px; font-size: 0.8rem; font-weight: 600;
        }

        /* ── Customers Brand Section ── */
        .brands-assignment-wrapper { display: flex; gap: 1.5rem; flex-wrap: wrap; }

        .brand-pool-box, .brand-assigned-box {
            flex: 1; min-width: 260px; border: 1px solid var(--border-gray);
            border-radius: 0.75rem; overflow: hidden; background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .brand-pool-header, .brand-assigned-header {
            padding: 0.85rem 1.25rem; font-weight: 700; font-size: 0.9rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .brand-pool-header { background: #f1f5f9; color: var(--dark-gray); border-bottom: 1px solid var(--border-gray); }
        .brand-assigned-header { background: #ecfdf5; color: var(--success); border-bottom: 1px solid #bbf7d0; }

        .brand-pool-search {
            padding: 0.65rem 1rem; border: none; border-bottom: 1px solid var(--border-gray);
            width: 100%; font-size: 0.85rem; outline: none; font-family: inherit;
        }
        .brand-pool-search:focus { background: #fffbf5; }

        .brand-list {
            max-height: 260px; overflow-y: auto; padding: 0.5rem;
            display: flex; flex-direction: column; gap: 0.35rem;
        }

        .brand-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem;
            background: var(--bg-light); border: 1px solid transparent;
            transition: all 0.15s; cursor: default;
        }
        .brand-item:hover { border-color: var(--primary-orange); background: var(--orange-light); }

        .brand-item-name { display: flex; align-items: center; gap: 0.4rem; font-weight: 500; }
        .brand-item-name i { color: var(--primary-orange); font-size: 0.75rem; }

        .brand-assign-btn, .brand-remove-btn {
            border: none; cursor: pointer; border-radius: 0.4rem;
            padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 600; transition: all 0.15s;
        }
        .brand-assign-btn { background: var(--primary-orange); color: var(--white); }
        .brand-assign-btn:hover { background: var(--secondary-orange); transform: scale(1.05); }
        .brand-remove-btn { background: #fee2e2; color: var(--error); }
        .brand-remove-btn:hover { background: var(--error); color: var(--white); transform: scale(1.05); }

        .brand-item.assigned-item { background: #f0fdf4; border-color: #bbf7d0; }
        .brand-item.assigned-item:hover { border-color: var(--success); background: #dcfce7; }
        .brand-item.assigned-item .brand-item-name i { color: var(--success); }

        .brands-empty { text-align: center; color: var(--text-gray); font-size: 0.85rem; padding: 1.5rem; }
        .brands-empty i { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.4; }

        .brand-count-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 22px; height: 22px; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;
            background: var(--primary-orange); color: var(--white; padding: 0 5px;
        }
        .brand-count-badge.green { background: var(--success); }

        .brands-action-bar {
            display: flex; gap: 0.5rem; padding: 0.75rem 1rem;
            border-top: 1px solid var(--border-gray); background: var(--bg-light);
            justify-content: flex-end;
        }

        /* Animations */
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: slideIn 0.6s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.3s ease-out forwards; }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-light); }
        ::-webkit-scrollbar-thumb { background: var(--primary-orange); border-radius: 4px; }

        @media (max-width: 768px) {
            body { padding: 1rem; }
            .page-title { font-size: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
            .card-body { padding: 1.5rem; }
            .rate-toggle-group { flex-direction: column; }
            .brand-rate-input { width: 100%; }
            .brands-assignment-wrapper { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Back Button -->
    <a href="manage-users.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Manage Users
    </a>

    <!-- Page Header -->
    <div class="page-header animate-in">
        <h1 class="page-title"><i class="fas fa-user-edit"></i> Edit User</h1>
        <p class="page-subtitle">Update user information and details</p>
    </div>

    <!-- Info Banner -->
    <div class="info-banner animate-in">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Important:</strong> Fields marked with <span class="required">*</span> are required.
            Leave password field empty if you don't want to change it.
        </div>
    </div>

    <!-- Form -->
    <form method="post" onsubmit="return validateForm()">
        <input type="hidden" name="payment_rate_status" id="payment_rate_status_input"
               value="<?php echo htmlentities($userData['payment_rate_status'] ?? 'one_rate'); ?>">

        <!-- Basic Information -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user"></i> Basic Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Customer ID <span class="required">*</span></label>
                        <input type="text" name="cus_id" id="cus_id_field" class="form-input"
                               value="<?php echo htmlentities($userData['cus_id']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Customer Code</label>
                        <input type="text" name="customer_code" class="form-input"
                               value="<?php echo htmlentities($userData['customer_code']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Name / Company Name <span class="required">*</span></label>
                        <input type="text" name="fullName" class="form-input"
                               value="<?php echo htmlentities($userData['fullName']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="userEmail" class="form-input"
                               value="<?php echo htmlentities($userData['userEmail']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input"
                               placeholder="Leave empty to keep current password">
                        <span class="form-help">Only enter a new password if you want to change it</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status <span class="required">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="1" <?php if($userData['status'] == 1) echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if($userData['status'] == 0) echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Manager Information -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-tie"></i> Account Manager Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Account Manager Name (Legacy)</label>
                        <input type="text" name="acm_name" class="form-input"
                               value="<?php echo htmlentities($userData['acm_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Manager Reference (Legacy)</label>
                        <input type="text" name="acm_ref" class="form-input"
                               value="<?php echo htmlentities($userData['acm_ref']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Manager Name</label>
                        <input type="text" name="account_manager_name" class="form-input"
                               value="<?php echo htmlentities($userData['account_manager_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Manager Code</label>
                        <input type="text" name="account_manager_code" class="form-input"
                               value="<?php echo htmlentities($userData['account_manager_code']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Information -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-building"></i> Company Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Company Registration Number</label>
                        <input type="text" name="company_rn" class="form-input"
                               value="<?php echo htmlentities($userData['company_rn']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Business Registration Number</label>
                        <input type="text" name="business_registration_number" class="form-input"
                               value="<?php echo htmlentities($userData['business_registration_number']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">TIN Number</label>
                        <input type="text" name="tin_number" class="form-input"
                               value="<?php echo htmlentities($userData['tin_number']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-input"
                               value="<?php echo htmlentities($userData['Country']); ?>">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Registered Address</label>
                        <input type="text" name="registerd_Address" class="form-input"
                               value="<?php echo htmlentities($userData['registerd_Address']); ?>">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Delivery Address</label>
                        <input type="text" name="delivery_address" class="form-input"
                               value="<?php echo htmlentities($userData['delivery_address']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Person Information -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-address-card"></i> Contact Person Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Contact Person Name</label>
                        <input type="text" name="contact_person1_name" class="form-input"
                               value="<?php echo htmlentities($userData['contact_person1_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email_address1" class="form-input"
                               value="<?php echo htmlentities($userData['email_address1']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Office Contact Number</label>
                        <input type="text" name="contact_number1_office" class="form-input"
                               value="<?php echo htmlentities($userData['contact_number1_office']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile Contact Number</label>
                        <input type="text" name="contact_number1_mobile" class="form-input"
                               value="<?php echo htmlentities($userData['contact_number1_mobile']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Brand Assignment ──────────────────────────────────── -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-tags"></i> Assigned Brands</h2>
            </div>
            <div class="card-body">
                <p style="font-size:0.9rem; color:var(--text-gray); margin-bottom:1.25rem;">
                    Select which tire brands this customer has access to.
                    Brands shown here come from the <strong>tire_details</strong> table.
                </p>

                <!-- Hidden inputs — synced by JS before submit -->
                <div id="assigned_brands_inputs"></div>

                <div class="brands-assignment-wrapper">

                    <!-- Pool (Available) -->
                    <div class="brand-pool-box">
                        <div class="brand-pool-header">
                            <i class="fas fa-layer-group" style="color:var(--primary-orange);"></i>
                            Available Brands
                            <span class="brand-count-badge" id="pool_count">0</span>
                        </div>
                        <input type="text" class="brand-pool-search" id="pool_search"
                               placeholder="&#xf002; Search brands…" oninput="filterPool(this.value)">
                        <div class="brand-list" id="pool_list">
                            <!-- Populated by JS -->
                        </div>
                        <div class="brands-action-bar">
                            <button type="button" class="btn btn-sm btn-success" onclick="assignAll()">
                                <i class="fas fa-check-double"></i> Assign All
                            </button>
                        </div>
                    </div>

                    <!-- Assigned -->
                    <div class="brand-assigned-box">
                        <div class="brand-assigned-header">
                            <i class="fas fa-check-circle"></i>
                            Assigned Brands
                            <span class="brand-count-badge green" id="assigned_count">0</span>
                        </div>
                        <input type="text" class="brand-pool-search" id="assigned_search"
                               placeholder="&#xf002; Search assigned…" oninput="filterAssigned(this.value)"
                               style="border-bottom-color:#bbf7d0;">
                        <div class="brand-list" id="assigned_list">
                            <!-- Populated by JS -->
                        </div>
                        <div class="brands-action-bar" style="border-top-color:#bbf7d0; background:#f0fdf4;">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeAll()">
                                <i class="fas fa-times-circle"></i> Remove All
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Financial & Payment Information -->
        <div class="card animate-in">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-money-bill-wave"></i> Financial & Payment Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Standard Payment Term</label>
                        <input type="text" name="standard_payment_term" class="form-input"
                               value="<?php echo htmlentities($userData['standard_payment_term']); ?>"
                               placeholder="e.g., Net 30 days">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Functional Currency</label>
                        <input type="text" name="functional_currency" class="form-input"
                               value="<?php echo htmlentities($userData['functional_currency']); ?>"
                               placeholder="e.g., USD, EUR">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Incoterm</label>
                        <input type="text" name="incoterm" class="form-input"
                               value="<?php echo htmlentities($userData['incoterm']); ?>"
                               placeholder="e.g., FOB, CIF, EXW">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port of Discharge</label>
                        <input type="text" name="port_of_discharge" class="form-input"
                               value="<?php echo htmlentities($userData['port_of_discharge']); ?>"
                               placeholder="e.g., Port of Colombo">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Required Certificates</label>
                        <textarea name="required_certificate" class="form-textarea"
                                  placeholder="Enter required certificates"><?php echo htmlentities($userData['required_certificate']); ?></textarea>
                    </div>
                </div>

                <hr style="margin:2rem 0; border:none; border-top:1px solid var(--border-gray);">

                <!-- Payment Rate Section -->
                <div>
                    <label class="form-label" style="margin-bottom:1rem; font-size:1rem;">
                        <i class="fas fa-percentage" style="color:var(--primary-orange);"></i>
                        &nbsp;Payment Rate Configuration <span class="required">*</span>
                    </label>

                    <div class="rate-toggle-group">
                        <button type="button"
                                class="rate-toggle-btn <?php echo ($userData['payment_rate_status'] != 'dif_rate') ? 'active-green' : ''; ?>"
                                id="btn_one_rate" onclick="setRateType('one_rate')">
                            <i class="fas fa-equals"></i>
                            One Rate
                            <small style="font-weight:400;font-size:0.8rem;">(Single rate for all brands)</small>
                        </button>
                        <button type="button"
                                class="rate-toggle-btn <?php echo ($userData['payment_rate_status'] == 'dif_rate') ? 'active' : ''; ?>"
                                id="btn_dif_rate" onclick="setRateType('dif_rate')">
                            <i class="fas fa-sliders-h"></i>
                            Different Rate
                            <small style="font-weight:400;font-size:0.8rem;">(Set rate per brand)</small>
                        </button>
                    </div>

                    <!-- One Rate -->
                    <div class="one-rate-container <?php echo ($userData['payment_rate_status'] != 'dif_rate') ? 'visible' : ''; ?>"
                         id="one_rate_section">
                        <div class="form-group" style="max-width:360px;">
                            <label class="form-label">Payment Rate (%)</label>
                            <input type="number" step="0.01" name="payment_rate" class="form-input"
                                   value="<?php echo htmlentities($userData['payment_rate']); ?>"
                                   placeholder="e.g., 2.50">
                            <span class="form-help">This rate applies to all brands for this customer.</span>
                        </div>
                    </div>

                    <!-- Different Rate per Brand -->
                    <div class="brand-rates-container <?php echo ($userData['payment_rate_status'] == 'dif_rate') ? 'visible' : ''; ?>"
                         id="dif_rate_section">
                        <div class="brand-rates-info">
                            <i class="fas fa-info-circle"></i>
                            Set individual payment rates (%) for each brand below. Leave blank to skip a brand.
                        </div>
                        <table class="brand-rates-table">
                            <thead>
                                <tr>
                                    <th style="width:50%;">Brand</th>
                                    <th>Payment Rate (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allBrands)): ?>
                                <tr>
                                    <td colspan="2" style="text-align:center;color:var(--text-gray);padding:2rem;">
                                        <i class="fas fa-exclamation-triangle"></i> No brands found in tire_details table.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($allBrands as $brand):
                                    $existingRate = isset($existingRates[$brand]) ? $existingRates[$brand] : '';
                                ?>
                                <tr>
                                    <td>
                                        <span class="brand-badge">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlentities($brand); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" min="0" max="100"
                                               name="brand_rates[<?php echo htmlentities($brand); ?>]"
                                               class="brand-rate-input"
                                               value="<?php echo htmlentities($existingRate); ?>"
                                               placeholder="e.g., 2.5000">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <input type="hidden" name="payment_rate"
                               value="<?php echo htmlentities($userData['payment_rate']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card animate-in">
            <div class="card-body">
                <div class="form-actions">
                    <a href="manage-users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// ── Brand Assignment State ──────────────────────────────────────────────
const ALL_BRANDS = <?php echo json_encode($allBrands); ?>;
let assignedSet  = new Set(<?php echo json_encode($assignedBrands); ?>);

function renderBrandLists(poolFilter, assignedFilter) {
    poolFilter      = (poolFilter || '').toLowerCase();
    assignedFilter  = (assignedFilter || '').toLowerCase();

    const poolList     = document.getElementById('pool_list');
    const assignedList = document.getElementById('assigned_list');
    const poolCount    = document.getElementById('pool_count');
    const assCount     = document.getElementById('assigned_count');

    poolList.innerHTML     = '';
    assignedList.innerHTML = '';

    let poolItems = 0, assItems = 0;

    ALL_BRANDS.forEach(brand => {
        const lc = brand.toLowerCase();
        if (assignedSet.has(brand)) {
            assItems++;
            if (!lc.includes(assignedFilter)) return;
            const div = document.createElement('div');
            div.className = 'brand-item assigned-item';
            div.innerHTML = `
                <span class="brand-item-name"><i class="fas fa-check-circle"></i>${escHtml(brand)}</span>
                <button type="button" class="brand-remove-btn" onclick="removeBrand('${escHtml(brand)}')">
                    <i class="fas fa-times"></i> Remove
                </button>`;
            assignedList.appendChild(div);
        } else {
            poolItems++;
            if (!lc.includes(poolFilter)) return;
            const div = document.createElement('div');
            div.className = 'brand-item';
            div.innerHTML = `
                <span class="brand-item-name"><i class="fas fa-tag"></i>${escHtml(brand)}</span>
                <button type="button" class="brand-assign-btn" onclick="assignBrand('${escHtml(brand)}')">
                    <i class="fas fa-plus"></i> Add
                </button>`;
            poolList.appendChild(div);
        }
    });

    if (poolList.innerHTML === '') {
        poolList.innerHTML = poolItems === 0
            ? `<div class="brands-empty"><i class="fas fa-check-double"></i>All brands assigned</div>`
            : `<div class="brands-empty"><i class="fas fa-search"></i>No brands match</div>`;
    }
    if (assignedList.innerHTML === '') {
        assignedList.innerHTML = `<div class="brands-empty"><i class="fas fa-tag"></i>No brands assigned yet</div>`;
    }

    poolCount.textContent = poolItems;
    assCount.textContent  = assItems;

    syncHiddenInputs();
}

function assignBrand(brand) {
    assignedSet.add(brand);
    renderBrandLists(
        document.getElementById('pool_search').value,
        document.getElementById('assigned_search').value
    );
}

function removeBrand(brand) {
    assignedSet.delete(brand);
    renderBrandLists(
        document.getElementById('pool_search').value,
        document.getElementById('assigned_search').value
    );
}

function assignAll() {
    ALL_BRANDS.forEach(b => assignedSet.add(b));
    renderBrandLists();
}

function removeAll() {
    if (!confirm('Remove all brand assignments from this customer?')) return;
    assignedSet.clear();
    renderBrandLists();
}

function filterPool(val) {
    renderBrandLists(val, document.getElementById('assigned_search').value);
}

function filterAssigned(val) {
    renderBrandLists(document.getElementById('pool_search').value, val);
}

function syncHiddenInputs() {
    const container = document.getElementById('assigned_brands_inputs');
    container.innerHTML = '';
    assignedSet.forEach(brand => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'assigned_brands[]';
        inp.value = brand;
        container.appendChild(inp);
    });
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Rate Type Toggle ──────────────────────────────────────────────────
function setRateType(type) {
    const hiddenInput = document.getElementById('payment_rate_status_input');
    const oneSection  = document.getElementById('one_rate_section');
    const difSection  = document.getElementById('dif_rate_section');
    const btnOne      = document.getElementById('btn_one_rate');
    const btnDif      = document.getElementById('btn_dif_rate');
    hiddenInput.value = type;
    if (type === 'dif_rate') {
        difSection.classList.add('visible','fade-in');
        oneSection.classList.remove('visible');
        btnDif.classList.add('active');
        btnDif.classList.remove('active-green');
        btnOne.classList.remove('active','active-green');
    } else {
        oneSection.classList.add('visible','fade-in');
        difSection.classList.remove('visible');
        btnOne.classList.add('active-green');
        btnOne.classList.remove('active');
        btnDif.classList.remove('active','active-green');
    }
}

// ── Form Validation ───────────────────────────────────────────────────
function validateForm() {
    const cusId    = document.querySelector('input[name="cus_id"]').value.trim();
    const fullName = document.querySelector('input[name="fullName"]').value.trim();
    const email    = document.querySelector('input[name="userEmail"]').value.trim();
    if (!cusId || !fullName || !email) {
        alert('Please fill in all required fields (Customer ID, Full Name, and Email)');
        return false;
    }
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('Please enter a valid email address');
        return false;
    }
    const rateStatus = document.getElementById('payment_rate_status_input').value;
    if (rateStatus === 'dif_rate') {
        const brandInputs = document.querySelectorAll('.brand-rate-input');
        let hasRate = false;
        brandInputs.forEach(i => { if (i.value.trim() !== '') hasRate = true; });
        if (!hasRate) {
            if (!confirm('No brand rates have been entered. Are you sure you want to save with empty rates?')) return false;
        }
    }
    syncHiddenInputs();
    if (confirm('Are you sure you want to update this user information?')) return true;
    return false;
}

// ── Init ──────────────────────────────────────────────────────────────
window.addEventListener('load', function () {
    renderBrandLists();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>