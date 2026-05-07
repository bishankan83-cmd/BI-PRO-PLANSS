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

    // Fetch distinct brands from tire_details
    $brandsQuery = mysqli_query($con, "SELECT DISTINCT Brand FROM tire_details WHERE Brand IS NOT NULL AND Brand != '' ORDER BY Brand ASC");
    $brands = [];
    while ($brandRow = mysqli_fetch_array($brandsQuery)) {
        $brands[] = $brandRow['Brand'];
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
        $password = md5($_POST['password']);
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
        $payment_rate_status = mysqli_real_escape_string($con, $_POST['payment_rate_status']);
        
        // Account Manager
        $account_manager_name = mysqli_real_escape_string($con, $_POST['account_manager_name']);
        $account_manager_code = mysqli_real_escape_string($con, $_POST['account_manager_code']);

        // Check if email already exists
        $checkEmail = mysqli_query($con, "SELECT * FROM users WHERE userEmail='$userEmail'");
        if (mysqli_num_rows($checkEmail) > 0) {
            echo '<script>alert("Email already exists. Please use a different email.")</script>';
        } else {
            $query = mysqli_query($con, "INSERT INTO users (
                cus_id, acm_name, acm_ref, fullName, company_rn, Country, 
                registerd_Address, userEmail, password, delivery_address, status, 
                customer_code, contact_person1_name, contact_number1_office, 
                contact_number1_mobile, email_address1, business_registration_number, 
                tin_number, standard_payment_term, functional_currency, incoterm, 
                port_of_discharge, required_certificate, payment_rate, payment_rate_status,
                account_manager_name, account_manager_code, regDate
            ) VALUES (
                '$cus_id', '$acm_name', '$acm_ref', '$fullName', '$company_rn', '$country',
                '$registerd_Address', '$userEmail', '$password', '$delivery_address', '$status',
                '$customer_code', '$contact_person1_name', '$contact_number1_office',
                '$contact_number1_mobile', '$email_address1', '$business_registration_number',
                '$tin_number', '$standard_payment_term', '$functional_currency', '$incoterm',
                '$port_of_discharge', '$required_certificate', '$payment_rate', '$payment_rate_status',
                '$account_manager_name', '$account_manager_code', NOW()
            )");

            if ($query) {
                $new_user_id = mysqli_insert_id($con);
                // Get the inserted cus_id for brand rates
                $new_cus_id = $cus_id;

                // Handle dif_rate brand-specific rates
                if ($payment_rate_status === 'dif_rate') {
                    if (!empty($_POST['brand_rates']) && is_array($_POST['brand_rates'])) {
                        foreach ($_POST['brand_rates'] as $brand => $rate) {
                            if ($rate !== '' && is_numeric($rate)) {
                                $brand_esc = mysqli_real_escape_string($con, $brand);
                                $rate_esc = mysqli_real_escape_string($con, $rate);
                                mysqli_query($con, "INSERT INTO customer_rate (cus_id, brand, payment_rate) 
                                    VALUES ('$new_cus_id', '$brand_esc', '$rate_esc')
                                    ON DUPLICATE KEY UPDATE payment_rate='$rate_esc'");
                            }
                        }
                    }
                }

                echo '<script>alert("User added successfully")</script>';
                echo "<script>window.location.href='manage-users.php'</script>";
            } else {
                echo '<script>alert("Something went wrong. Please try again")</script>';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS | Add New User</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            color: var(--dark-gray);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 2rem;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: var(--white);
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid var(--border-gray);
            transition: all 0.2s;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .back-button:hover {
            background: var(--primary-orange);
            color: var(--white);
            border-color: var(--primary-orange);
            transform: translateX(-4px);
            box-shadow: var(--shadow);
        }

        .back-button i {
            font-size: 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--text-gray);
            font-size: 1rem;
        }

        .breadcrumb {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-gray);
            flex-wrap: wrap;
        }

        .breadcrumb-item a {
            color: var(--text-gray);
            text-decoration: none;
            transition: all 0.2s;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-orange);
        }

        .breadcrumb-item i {
            margin-right: 0.5rem;
        }

        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            background: var(--gradient-1);
            color: var(--white);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .required {
            color: var(--error);
        }

        .form-input,
        .form-select,
        .form-textarea {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            background: var(--white);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-help {
            font-size: 0.8rem;
            color: var(--text-gray);
            font-style: italic;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--gradient-1);
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-secondary:hover {
            background: var(--border-gray);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-gray);
        }

        .info-banner {
            background: var(--orange-light);
            border-left: 4px solid var(--primary-orange);
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-banner i {
            color: var(--primary-orange);
            font-size: 1.5rem;
        }

        /* ── Payment Rate Toggle ── */
        .rate-toggle-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .rate-toggle-btn {
            flex: 1;
            padding: 0.85rem 1.5rem;
            border: 2px solid var(--border-gray);
            border-radius: 0.75rem;
            background: var(--white);
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .rate-toggle-btn:hover {
            border-color: var(--primary-orange);
            color: var(--primary-orange);
        }

        .rate-toggle-btn.active {
            background: var(--gradient-1);
            border-color: var(--primary-orange);
            color: var(--white);
            box-shadow: var(--shadow-md);
        }

        .rate-toggle-btn.active-green {
            background: var(--gradient-2);
            border-color: var(--success);
            color: var(--white);
            box-shadow: var(--shadow-md);
        }

        /* Brand Rates Table */
        .brand-rates-container {
            display: none;
            margin-top: 1rem;
        }

        .brand-rates-container.visible {
            display: block;
        }

        .brand-rates-info {
            background: #fff8f0;
            border: 1px solid #ffd199;
            border-radius: 0.5rem;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-rates-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-gray);
        }

        .brand-rates-table thead {
            background: var(--light-gray);
        }

        .brand-rates-table th {
            padding: 0.85rem 1.25rem;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--dark-gray);
            border-bottom: 1px solid var(--border-gray);
        }

        .brand-rates-table td {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--border-gray);
            font-size: 0.9rem;
        }

        .brand-rates-table tr:last-child td {
            border-bottom: none;
        }

        .brand-rates-table tr:hover td {
            background: #fafafa;
        }

        .brand-rate-input {
            width: 140px;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            outline: none;
        }

        .brand-rate-input:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.3rem 0.75rem;
            background: var(--orange-light);
            color: var(--primary-orange);
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .one-rate-container {
            display: none;
        }

        .one-rate-container.visible {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .back-button {
                width: 100%;
                justify-content: center;
            }

            .rate-toggle-group {
                flex-direction: column;
            }

            .brand-rate-input {
                width: 100%;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: slideIn 0.6s ease-out forwards;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }
    </style>
</head>
<body>
    <main class="main-content">
        <!-- Back Button -->
        <a href="manage-users.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Manage Users
        </a>

        <div class="page-header">
            <div>
                <h1 class="page-title">Add New User</h1>
                <p class="page-subtitle">Create a new user account in the system.</p>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="manage-users.php">Manage Users</a></li>
                <li class="breadcrumb-item">Add New User</li>
            </ul>
        </div>

        <div class="info-banner animate-in">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Important:</strong> Fields marked with <span class="required">*</span> are required. Please fill in all mandatory information to create a new user account.
            </div>
        </div>

        <form method="post" onsubmit="return validateForm()">
            <!-- Hidden: payment_rate_status -->
            <input type="hidden" name="payment_rate_status" id="payment_rate_status_input" value="one_rate">

            <!-- Basic Information -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user"></i>
                        Basic Information
                    </h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Customer ID <span class="required">*</span></label>
                            <input type="text" name="cus_id" class="form-input" required placeholder="Enter customer ID">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Customer Code</label>
                            <input type="text" name="customer_code" class="form-input" placeholder="Enter customer code">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Name / Company Name <span class="required">*</span></label>
                            <input type="text" name="fullName" class="form-input" required placeholder="Enter full name or company name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="userEmail" class="form-input" required placeholder="user@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <input type="password" name="password" id="password" class="form-input" required placeholder="Enter password">
                            <span class="form-help">Minimum 6 characters recommended</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password <span class="required">*</span></label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input" required placeholder="Re-enter password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Manager Information -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user-tie"></i>
                        Account Manager Information
                    </h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Account Manager Name (Legacy)</label>
                            <input type="text" name="acm_name" class="form-input" placeholder="Enter account manager name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Manager Reference (Legacy)</label>
                            <input type="text" name="acm_ref" class="form-input" placeholder="Enter reference">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Manager Name</label>
                            <input type="text" name="account_manager_name" class="form-input" placeholder="Enter account manager name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Manager Code</label>
                            <input type="text" name="account_manager_code" class="form-input" placeholder="Enter manager code">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-building"></i>
                        Company Information
                    </h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Company Registration Number</label>
                            <input type="text" name="company_rn" class="form-input" placeholder="Enter registration number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Business Registration Number</label>
                            <input type="text" name="business_registration_number" class="form-input" placeholder="Enter business reg. number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">TIN Number</label>
                            <input type="text" name="tin_number" class="form-input" placeholder="Enter TIN">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-input" placeholder="Enter country">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Registered Address</label>
                            <input type="text" name="registerd_Address" class="form-input" placeholder="Enter registered address">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Delivery Address</label>
                            <input type="text" name="delivery_address" class="form-input" placeholder="Enter delivery address">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Person Information -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-address-card"></i>
                        Contact Person Information
                    </h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Contact Person Name</label>
                            <input type="text" name="contact_person1_name" class="form-input" placeholder="Enter contact person name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email_address1" class="form-input" placeholder="contact@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Office Contact Number</label>
                            <input type="text" name="contact_number1_office" class="form-input" placeholder="+1 234 567 8900">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile Contact Number</label>
                            <input type="text" name="contact_number1_mobile" class="form-input" placeholder="+1 234 567 8900">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial & Payment Information -->
            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Financial & Payment Information
                    </h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Standard Payment Term</label>
                            <input type="text" name="standard_payment_term" class="form-input" placeholder="e.g., Net 30 days">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Functional Currency</label>
                            <input type="text" name="functional_currency" class="form-input" placeholder="e.g., USD, EUR">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Incoterm</label>
                            <input type="text" name="incoterm" class="form-input" placeholder="e.g., FOB, CIF, EXW">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Port of Discharge</label>
                            <input type="text" name="port_of_discharge" class="form-input" placeholder="e.g., Port of Colombo">
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Required Certificates</label>
                            <textarea name="required_certificate" class="form-textarea" placeholder="Enter required certificates (e.g., ISO, CE, FDA)"></textarea>
                        </div>
                    </div>

                    <!-- Payment Rate Configuration -->
                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border-gray);">

                    <div>
                        <label class="form-label" style="margin-bottom: 1rem; font-size: 1rem;">
                            <i class="fas fa-percentage" style="color: var(--primary-orange);"></i>
                            &nbsp;Payment Rate Configuration <span class="required">*</span>
                        </label>

                        <!-- Rate Type Toggle -->
                        <div class="rate-toggle-group">
                            <button type="button" class="rate-toggle-btn active-green"
                                    id="btn_one_rate" onclick="setRateType('one_rate')">
                                <i class="fas fa-equals"></i>
                                One Rate
                                <small style="font-weight:400; font-size:0.8rem;">(Single rate for all brands)</small>
                            </button>
                            <button type="button" class="rate-toggle-btn"
                                    id="btn_dif_rate" onclick="setRateType('dif_rate')">
                                <i class="fas fa-sliders-h"></i>
                                Different Rate
                                <small style="font-weight:400; font-size:0.8rem;">(Set rate per brand)</small>
                            </button>
                        </div>

                        <!-- One Rate -->
                        <div class="one-rate-container visible" id="one_rate_section">
                            <div class="form-group" style="max-width: 360px;">
                                <label class="form-label">Payment Rate (%)</label>
                                <input type="number" step="0.01" name="payment_rate" id="payment_rate_one" class="form-input"
                                       placeholder="e.g., 2.50">
                                <span class="form-help">This rate applies to all brands for this customer.</span>
                            </div>
                        </div>

                        <!-- Different Rate per Brand -->
                        <div class="brand-rates-container" id="dif_rate_section">
                            <div class="brand-rates-info">
                                <i class="fas fa-info-circle"></i>
                                Set individual payment rates (%) for each brand below. Leave blank to skip a brand.
                            </div>
                            <table class="brand-rates-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50%;">Brand</th>
                                        <th>Payment Rate (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($brands)): ?>
                                    <tr>
                                        <td colspan="2" style="text-align:center; color: var(--text-gray); padding: 2rem;">
                                            <i class="fas fa-exclamation-triangle"></i> No brands found in tire_details table.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($brands as $brand): ?>
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
                                                   placeholder="e.g., 2.5000">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Hidden payment_rate field for dif_rate mode -->
                            <input type="hidden" name="payment_rate" id="payment_rate_hidden" value="">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="card animate-in">
                <div class="card-body">
                    <div class="form-actions">
                        <a href="manage-users.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Add User
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script>
        // ── Rate Type Toggle ──────────────────────────────────────────────
        function setRateType(type) {
            const hiddenInput  = document.getElementById('payment_rate_status_input');
            const oneSection   = document.getElementById('one_rate_section');
            const difSection   = document.getElementById('dif_rate_section');
            const btnOne       = document.getElementById('btn_one_rate');
            const btnDif       = document.getElementById('btn_dif_rate');
            const rateOneInput = document.getElementById('payment_rate_one');
            const rateHidden   = document.getElementById('payment_rate_hidden');

            hiddenInput.value = type;

            if (type === 'dif_rate') {
                difSection.classList.add('visible', 'fade-in');
                oneSection.classList.remove('visible');

                btnDif.classList.add('active');
                btnDif.classList.remove('active-green');
                btnOne.classList.remove('active', 'active-green');

                // Disable the one-rate input so it doesn't submit a value
                rateOneInput.disabled = true;
                rateHidden.disabled   = false;
            } else {
                oneSection.classList.add('visible', 'fade-in');
                difSection.classList.remove('visible');

                btnOne.classList.add('active-green');
                btnOne.classList.remove('active');
                btnDif.classList.remove('active', 'active-green');

                rateOneInput.disabled = false;
                rateHidden.disabled   = true;
            }
        }

        // ── Form Validation ───────────────────────────────────────────────
        function validateForm() {
            const cusId           = document.querySelector('input[name="cus_id"]').value.trim();
            const fullName        = document.querySelector('input[name="fullName"]').value.trim();
            const email           = document.querySelector('input[name="userEmail"]').value.trim();
            const password        = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!cusId || !fullName || !email || !password) {
                alert('Please fill in all required fields (Customer ID, Full Name, Email, and Password)');
                return false;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Please enter a valid email address');
                return false;
            }

            if (password.length < 6) {
                alert('Password must be at least 6 characters long');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }

            const rateStatus = document.getElementById('payment_rate_status_input').value;
            if (rateStatus === 'dif_rate') {
                const brandInputs = document.querySelectorAll('.brand-rate-input');
                let hasRate = false;
                brandInputs.forEach(function(input) {
                    if (input.value.trim() !== '') hasRate = true;
                });
                if (!hasRate) {
                    if (!confirm('No brand rates have been entered. Are you sure you want to save with empty rates?')) {
                        return false;
                    }
                }
            }

            if (confirm('Are you sure you want to add this user?')) {
                return true;
            }
            return false;
        }

        // ── Real-time password match check ────────────────────────────────
        document.getElementById('confirm_password').addEventListener('keyup', function() {
            const password        = document.getElementById('password').value;
            const confirmPassword = this.value;
            if (password !== confirmPassword && confirmPassword.length > 0) {
                this.style.borderColor = 'var(--error)';
            } else {
                this.style.borderColor = 'var(--border-gray)';
            }
        });
    </script>
</body>
</html>
<?php
    mysqli_close($con);
}
?>