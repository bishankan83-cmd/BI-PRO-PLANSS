<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_cms');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to handle longtext properly
$conn->set_charset("utf8mb4");

// ============================================
// IMPORTANT: Add these columns to your admin table if not exists
// ALTER TABLE `admin` ADD COLUMN `digital_signature` LONGTEXT NULL AFTER `updationDate`;
// ALTER TABLE `admin` ADD COLUMN `signature_date` DATETIME NULL AFTER `digital_signature`;
// ============================================

// ============================================
// SECURITY: CSRF Token Generation
// ============================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================
// GET LOGGED-IN ADMIN ID FROM SESSION
// ============================================
$logged_in_admin_id = null;
$session_identifier = '';

// Method 1: Check for direct ID in session
if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
    $logged_in_admin_id = intval($_SESSION['id']);
    $session_identifier = 'id';
} 
// Method 2: Check for admin_id
elseif (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    $logged_in_admin_id = intval($_SESSION['admin_id']);
    $session_identifier = 'admin_id';
} 
// Method 3: Check for adminid
elseif (isset($_SESSION['adminid']) && !empty($_SESSION['adminid'])) {
    $logged_in_admin_id = intval($_SESSION['adminid']);
    $session_identifier = 'adminid';
} 
// Method 4: Check for alogin (username/email)
elseif (isset($_SESSION['alogin']) && !empty($_SESSION['alogin'])) {
    $username_or_email = $_SESSION['alogin'];
    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? OR email = ? LIMIT 1");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $logged_in_admin_id = intval($row['id']);
        $session_identifier = 'alogin';
    }
    $stmt->close();
}

// If no admin ID found, show error and redirect
if (!$logged_in_admin_id) {
    // Log session data for debugging (remove in production)
    error_log("Digital Signature Access Denied - No Admin Session Found");
    error_log("Available Session Keys: " . implode(', ', array_keys($_SESSION)));
    
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Authentication Required</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .error-box {
                background: white;
                padding: 50px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
                max-width: 450px;
                animation: slideUp 0.5s ease-out;
            }
            @keyframes slideUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .error-box h2 {
                color: #dc3545;
                margin-bottom: 20px;
                font-size: 28px;
            }
            .error-box p {
                color: #666;
                margin-bottom: 25px;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                padding: 15px 40px;
                background: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
                color: white;
                text-decoration: none;
                border-radius: 10px;
                font-weight: 600;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(242, 128, 24, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>🔒 Authentication Required</h2>
            <p>You must be logged in as an administrator to access the digital signature page.</p>
            <p>Your session has expired or you are not logged in.</p>
            <a href="index.php" class="btn">Go to Admin Login</a>
        </div>
    </body>
    </html>
    ');
}

// ============================================
// HANDLE AJAX REQUESTS
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    
    header('Content-Type: application/json');
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page.']);
        exit;
    }
    
    // Save signature
    if (isset($_POST['action']) && $_POST['action'] === 'save_signature') {
        $signature_data = $_POST['signature_data'] ?? '';
        
        if (empty($signature_data)) {
            echo json_encode(['success' => false, 'message' => 'No signature data provided']);
            exit;
        }
        
        // Validate signature format
        if (strpos($signature_data, 'data:image/png;base64,') !== 0 && 
            strpos($signature_data, 'data:image/jpeg;base64,') !== 0 &&
            strpos($signature_data, 'data:image/jpg;base64,') !== 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid signature format']);
            exit;
        }
        
        // Validate base64 data
        $base64_data = substr($signature_data, strpos($signature_data, ',') + 1);
        if (!base64_decode($base64_data, true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid base64 encoding']);
            exit;
        }
        
        // Update signature ONLY for the logged-in admin
        $sql = "UPDATE admin SET digital_signature = ?, signature_date = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("si", $signature_data, $logged_in_admin_id);
        
        if ($stmt->execute()) {
            // Check if update was successful
            if ($stmt->affected_rows > 0) {
                // Verify the save by retrieving the updated record
                $verify_stmt = $conn->prepare("SELECT LENGTH(digital_signature) as sig_length, signature_date, fullname FROM admin WHERE id = ?");
                $verify_stmt->bind_param("i", $logged_in_admin_id);
                $verify_stmt->execute();
                $verify_result = $verify_stmt->get_result();
                $verified = $verify_result->fetch_assoc();
                $verify_stmt->close();
                
                if (!empty($verified['sig_length']) && $verified['sig_length'] > 0) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Signature saved successfully for ' . htmlspecialchars($verified['fullname']) . '!',
                        'signature_date' => date('F d, Y h:i A', strtotime($verified['signature_date'])),
                        'signature_size' => number_format($verified['sig_length']),
                        'signature_data' => $signature_data,
                        'admin_id' => $logged_in_admin_id
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Signature save verification failed']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made. Signature may already exist or admin ID not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving signature: ' . $stmt->error]);
        }
        $stmt->close();
        exit;
    }
    
    // Clear signature
    if (isset($_POST['action']) && $_POST['action'] === 'clear_signature') {
        // Clear signature ONLY for the logged-in admin
        $stmt = $conn->prepare("UPDATE admin SET digital_signature = NULL, signature_date = NULL WHERE id = ?");
        $stmt->bind_param("i", $logged_in_admin_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Signature removed successfully!',
                    'admin_id' => $logged_in_admin_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No signature found to remove.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error removing signature: ' . $stmt->error]);
        }
        $stmt->close();
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// ============================================
// FETCH LOGGED-IN ADMIN DETAILS
// ============================================
$stmt = $conn->prepare("SELECT id, acm_ref, fullname, role, mobilenumber, email, username, creationDate, digital_signature, signature_date FROM admin WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $logged_in_admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    die("
    <div style='text-align:center; padding:50px; font-family:Arial;'>
        <h2 style='color:#dc3545;'>Error: Admin Record Not Found</h2>
        <p>Admin ID {$logged_in_admin_id} does not exist in the database.</p>
        <p>Session Identifier: {$session_identifier}</p>
        <a href='index.php' style='padding:10px 20px; background:#F28018; color:white; text-decoration:none; border-radius:5px;'>Back to Login</a>
    </div>
    ");
}

// Store admin ID in session for future use
$_SESSION['current_admin_id'] = $admin['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Signature - <?php echo htmlspecialchars($admin['fullname']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-orange: #F28018;
            --secondary-orange: #e67e22;
            --dark-gray: #333333;
            --light-gray: #f0f0f0;
            --border-gray: #e0e0e0;
            --bg-light: #f8fafc;
            --text-gray: #64748b;
            --success-color: #27ae60;
            --success-light: rgba(39, 174, 96, 0.1);
            --danger-color: #e74c3c;
            --danger-light: rgba(231, 76, 60, 0.1);
            --warning-light: rgba(241, 196, 15, 0.1);
            --info-color: #0c5460;
            --info-light: #d1ecf1;
            --orange-light: rgba(242, 128, 24, 0.1);
            --white: #ffffff;
            --gradient-orange: linear-gradient(135deg, #F28018 0%, #e67e22 100%);
            --gradient-success: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--gradient-orange);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            padding: 2rem;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-gray);
        }

        h1 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        h1 i {
            color: var(--primary-orange);
        }

        .subtitle {
            color: var(--text-gray);
            font-size: 1rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 0.6rem 1.2rem;
            background: var(--light-gray);
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 600;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .back-link:hover {
            background: var(--border-gray);
            transform: translateX(-5px);
        }

        .session-info {
            background: var(--info-light);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: var(--info-color);
            border-left: 4px solid #17a2b8;
        }

        .admin-card {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary-orange);
            border: 1px solid var(--border-gray);
        }

        .admin-card h3 {
            color: var(--dark-gray);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-card h3 i {
            color: var(--primary-orange);
        }

        .admin-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            color: var(--text-gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .info-value {
            color: var(--dark-gray);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .role-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            background: var(--gradient-orange);
            color: white;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert i {
            font-size: 20px;
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success-color);
            border: 1px solid #a3e4b7;
        }

        .alert-error {
            background: var(--danger-light);
            color: var(--danger-color);
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: var(--info-light);
            color: var(--info-color);
            border: 1px solid #bee5eb;
        }

        .section {
            margin-bottom: 2.5rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-gray);
        }

        .section-header h2 {
            color: var(--dark-gray);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .section-header i {
            color: var(--primary-orange);
            font-size: 1.5rem;
        }

        .signature-display {
            background: white;
            padding: 2rem;
            border-radius: 0.75rem;
            border: 2px solid var(--success-color);
            box-shadow: var(--shadow);
        }

        .signature-display img {
            max-width: 100%;
            height: auto;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            background: white;
            padding: 1rem;
            display: block;
            margin: 0 auto;
        }

        .signature-meta {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-gray);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-gray);
            font-size: 0.95rem;
        }

        .meta-item i {
            color: var(--primary-orange);
        }

        .no-signature {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-light);
            border-radius: 0.75rem;
            border: 2px dashed var(--border-gray);
        }

        .no-signature i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .no-signature p {
            color: var(--text-gray);
            font-size: 1rem;
        }

        /* Method Selection Tabs */
        .method-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-gray);
        }

        .method-tab {
            flex: 1;
            padding: 1rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-gray);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .method-tab:hover {
            color: var(--primary-orange);
            background: var(--orange-light);
        }

        .method-tab.active {
            color: var(--primary-orange);
            border-bottom-color: var(--primary-orange);
            background: var(--orange-light);
        }

        .method-tab i {
            font-size: 1.25rem;
        }

        .method-content {
            display: none;
        }

        .method-content.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Canvas Styles */
        .canvas-container {
            position: relative;
            background: white;
            border: 3px solid var(--primary-orange);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        #signature-pad {
            display: block;
            width: 100%;
            height: 300px;
            cursor: crosshair;
            touch-action: none;
            background: white;
        }

        .canvas-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #e0e0e0;
            font-size: 1.25rem;
            font-weight: 600;
            pointer-events: none;
            text-align: center;
            z-index: 0;
        }

        .canvas-instructions {
            background: var(--orange-light);
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--dark-gray);
            border: 1px solid rgba(242, 128, 24, 0.2);
        }

        .canvas-instructions i {
            color: var(--primary-orange);
            font-size: 1.25rem;
        }

        /* Upload Styles */
        .upload-area {
            border: 3px dashed var(--primary-orange);
            border-radius: 0.75rem;
            padding: 3rem 2rem;
            text-align: center;
            background: var(--bg-light);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-area:hover {
            background: var(--orange-light);
            border-color: var(--secondary-orange);
        }

        .upload-area.dragover {
            background: var(--orange-light);
            border-color: var(--secondary-orange);
            transform: scale(1.02);
        }

        .upload-area i {
            font-size: 3rem;
            color: var(--primary-orange);
            margin-bottom: 1rem;
        }

        .upload-area p {
            color: var(--dark-gray);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .upload-area .file-types {
            color: var(--text-gray);
            font-size: 0.85rem;
        }

        #imageUpload {
            display: none;
        }

        .upload-preview {
            margin-top: 1.5rem;
            display: none;
        }

        .upload-preview.active {
            display: block;
        }

        .upload-preview img {
            max-width: 100%;
            height: auto;
            border: 2px solid var(--border-gray);
            border-radius: 0.5rem;
            background: white;
            padding: 1rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow);
            font-family: 'Inter', sans-serif;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        button i {
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--gradient-orange);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--border-gray);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-info {
            background: var(--gradient-success);
            color: white;
        }

        .btn-info:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .button-group {
                flex-direction: column;
            }

            button {
                width: 100%;
                justify-content: center;
            }

            .admin-info-grid {
                grid-template-columns: 1fr;
            }

            #signature-pad {
                height: 250px;
            }

            .method-tabs {
                flex-direction: column;
                gap: 0;
            }

            .method-tab {
                border-bottom: 1px solid var(--border-gray);
            }

            .method-tab.active {
                border-left: 3px solid var(--primary-orange);
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 1rem;
                border-radius: 0.75rem;
            }

            .section-header h2 {
                font-size: 1.25rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .upload-area {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="container">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Session Debug Info (Remove in production) -->
        <div class="session-info">
            <i class="fas fa-info-circle"></i>
            <strong>Session Status:</strong> Logged in as Admin ID: <?php echo $admin['id']; ?> 
            (Session Key: <?php echo htmlspecialchars($session_identifier); ?>)
        </div>
        
        <div class="header">
            <h1><i class="fas fa-signature"></i> Administrator Digital Signature</h1>
            <p class="subtitle">Create and manage your electronic signature</p>
        </div>
        
        <div class="admin-card">
            <h3><i class="fas fa-user-shield"></i> Logged-In Administrator Information</h3>
            <div class="admin-info-grid">
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($admin['fullname']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email Address</span>
                    <span class="info-value"><?php echo htmlspecialchars($admin['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Username</span>
                    <span class="info-value"><?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Role</span>
                    <span class="role-badge"><?php echo htmlspecialchars($admin['role']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ACM Reference</span>
                    <span class="info-value"><?php echo htmlspecialchars($admin['acm_ref']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Admin ID</span>
                    <span class="info-value">#<?php echo htmlspecialchars($admin['id']); ?></span>
                </div>
                <?php if (!empty($admin['mobilenumber'])): ?>
                <div class="info-item">
                    <span class="info-label">Mobile Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($admin['mobilenumber']); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <span class="info-label">Account Created</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($admin['creationDate'])); ?></span>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>

        <!-- Current Signature Section -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-check-circle"></i>
                <h2>Current Signature (Admin ID: #<?php echo $admin['id']; ?>)</h2>
            </div>
            
            <div id="signatureDisplay">
                <?php if (!empty($admin['digital_signature'])): ?>
                    <div class="signature-display">
                        <img src="<?php echo htmlspecialchars($admin['digital_signature']); ?>" alt="Digital Signature" id="currentSignatureImg">
                        <div class="signature-meta">
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span>Signed by: <strong><?php echo htmlspecialchars($admin['fullname']); ?></strong></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar-check"></i>
                                <span>Signed on: <strong><?php echo $admin['signature_date'] ? date('F d, Y h:i A', strtotime($admin['signature_date'])) : 'Date unavailable'; ?></strong></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Status: <strong>Verified</strong></span>
                            </div>
                        </div>
                        <div class="button-group" style="margin-top: 1.5rem;">
                            <button type="button" class="btn-info" onclick="downloadSignature()">
                                <i class="fas fa-download"></i> Download Signature
                            </button>
                            <button type="button" class="btn-danger" onclick="confirmClearSignature()">
                                <i class="fas fa-trash-alt"></i> Remove Signature
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-signature">
                        <i class="fas fa-file-signature"></i>
                        <p>No signature on file for <strong><?php echo htmlspecialchars($admin['fullname']); ?></strong></p>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem;">Create your digital signature below.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Create/Update Signature Section -->
        <div class="section">
            <div class="section-header">
                <i class="fas fa-pen-fancy"></i>
                <h2>Create/Update Signature</h2>
            </div>
            
            <!-- Method Selection Tabs -->
            <div class="method-tabs">
                <button class="method-tab active" onclick="switchMethod('draw')" id="drawTab">
                    <i class="fas fa-pen"></i> Draw Signature
                </button>
                <button class="method-tab" onclick="switchMethod('upload')" id="uploadTab">
                    <i class="fas fa-upload"></i> Upload Image
                </button>
            </div>

            <!-- Draw Method -->
            <div class="method-content active" id="drawMethod">
                <div class="canvas-instructions">
                    <i class="fas fa-info-circle"></i>
                    <span>Draw your signature in the box below using your mouse, trackpad, or finger on touch devices.</span>
                </div>

                <div class="canvas-container">
                    <div class="canvas-watermark">Sign Here</div>
                    <canvas id="signature-pad"></canvas>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-secondary" onclick="clearCanvas()">
                        <i class="fas fa-eraser"></i> Clear Canvas
                    </button>
                    <button type="button" class="btn-primary" id="saveCanvasBtn" onclick="saveCanvasSignature()">
                        <i class="fas fa-save"></i> Save Signature
                    </button>
                    <button type="button" class="btn-secondary" onclick="undoLastStroke()">
                        <i class="fas fa-undo"></i> Undo
                    </button>
                </div>
            </div>

            <!-- Upload Method -->
            <div class="method-content" id="uploadMethod">
                <div class="canvas-instructions">
                    <i class="fas fa-info-circle"></i>
                    <span>Upload a clear image of your signature. Supported formats: PNG, JPG, JPEG (Max size: 5MB)</span>
                </div>

                <div class="upload-area" id="uploadArea" onclick="document.getElementById('imageUpload').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Click to upload</strong> or drag and drop</p>
                    <p class="file-types">PNG, JPG, JPEG (max. 5MB)</p>
                </div>
                
                <input type="file" id="imageUpload" accept="image/png,image/jpeg,image/jpg" onchange="handleImageUpload(event)">
                
                <div class="upload-preview" id="uploadPreview">
                    <img id="previewImage" alt="Signature Preview">
                </div>

                <div class="button-group">
                    <button type="button" class="btn-secondary" onclick="clearUpload()">
                        <i class="fas fa-times"></i> Clear Image
                    </button>
                    <button type="button" class="btn-primary" id="saveUploadBtn" onclick="saveUploadedSignature()" disabled>
                        <i class="fas fa-save"></i> Save Signature
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store admin info for JavaScript
        const ADMIN_INFO = {
            id: <?php echo $admin['id']; ?>,
            name: '<?php echo addslashes($admin['fullname']); ?>',
            email: '<?php echo addslashes($admin['email']); ?>'
        };

        console.log('Logged in as:', ADMIN_INFO);

        // ============================================
        // METHOD SWITCHING
        // ============================================
        function switchMethod(method) {
            document.querySelectorAll('.method-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.method-content').forEach(content => content.classList.remove('active'));
            
            if (method === 'draw') {
                document.getElementById('drawTab').classList.add('active');
                document.getElementById('drawMethod').classList.add('active');
            } else {
                document.getElementById('uploadTab').classList.add('active');
                document.getElementById('uploadMethod').classList.add('active');
            }
        }

        // ============================================
        // CANVAS SIGNATURE (Draw Method)
        // ============================================
        const canvas = document.getElementById('signature-pad');
        const ctx = canvas.getContext('2d');
        
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        let hasDrawn = false;
        let strokes = [];
        let currentStroke = [];

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            redrawCanvas();
        }
        
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getMousePos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            currentStroke = [];
            const pos = getMousePos(e);
            lastX = pos.x;
            lastY = pos.y;
            currentStroke.push({x: pos.x, y: pos.y});
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            
            const pos = getMousePos(e);
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            
            currentStroke.push({x: pos.x, y: pos.y});
            lastX = pos.x;
            lastY = pos.y;
            hasDrawn = true;
            e.preventDefault();
        }

        function stopDrawing() {
            if (isDrawing && currentStroke.length > 0) {
                strokes.push([...currentStroke]);
            }
            isDrawing = false;
            currentStroke = [];
        }

        function redrawCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            strokes.forEach(stroke => {
                if (stroke.length < 2) return;
                
                ctx.beginPath();
                ctx.moveTo(stroke[0].x, stroke[0].y);
                
                for (let i = 1; i < stroke.length; i++) {
                    ctx.lineTo(stroke[i].x, stroke[i].y);
                }
                ctx.stroke();
            });
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
            strokes = [];
            currentStroke = [];
        }

        function undoLastStroke() {
            if (strokes.length > 0) {
                strokes.pop();
                redrawCanvas();
                hasDrawn = strokes.length > 0;
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        // ============================================
        // IMAGE UPLOAD METHOD
        // ============================================
        let uploadedImageData = null;

        const uploadArea = document.getElementById('uploadArea');
        const imageUpload = document.getElementById('imageUpload');
        const uploadPreview = document.getElementById('uploadPreview');
        const previewImage = document.getElementById('previewImage');
        const saveUploadBtn = document.getElementById('saveUploadBtn');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleImageFile(files[0]);
            }
        });

        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                handleImageFile(file);
            }
        }

        function handleImageFile(file) {
            const validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                showAlert('Please upload a valid image file (PNG, JPG, JPEG)', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showAlert('File size must be less than 5MB', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                uploadedImageData = e.target.result;
                previewImage.src = uploadedImageData;
                uploadPreview.classList.add('active');
                saveUploadBtn.disabled = false;
            };
            reader.readAsDataURL(file);
        }

        function clearUpload() {
            uploadedImageData = null;
            previewImage.src = '';
            uploadPreview.classList.remove('active');
            imageUpload.value = '';
            saveUploadBtn.disabled = true;
        }

        // ============================================
        // SAVE FUNCTIONS
        // ============================================
        async function saveCanvasSignature() {
            if (!hasDrawn || strokes.length === 0) {
                showAlert('Please draw your signature before saving.', 'error');
                return;
            }
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const isCanvasEmpty = !imageData.data.some(channel => channel !== 0);
            
            if (isCanvasEmpty) {
                showAlert('Please provide a signature before saving.', 'error');
                return;
            }
            
            const signatureData = canvas.toDataURL('image/png');
            await saveSignature(signatureData, 'saveCanvasBtn');
        }

        async function saveUploadedSignature() {
            if (!uploadedImageData) {
                showAlert('Please upload an image before saving.', 'error');
                return;
            }
            
            await saveSignature(uploadedImageData, 'saveUploadBtn');
        }

        async function saveSignature(signatureData, buttonId) {
            const saveBtn = document.getElementById(buttonId);
            const originalHTML = saveBtn.innerHTML;
            
            showLoading(true);
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving for Admin #' + ADMIN_INFO.id + '...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'save_signature');
                formData.append('signature_data', signatureData);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    console.log('Signature saved for Admin ID:', data.admin_id);
                    
                    document.getElementById('signatureDisplay').innerHTML = `
                        <div class="signature-display">
                            <img src="${data.signature_data}" alt="Digital Signature" id="currentSignatureImg">
                            <div class="signature-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>Signed by: <strong>${ADMIN_INFO.name}</strong></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>Signed on: <strong>${data.signature_date}</strong></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Status: <strong>Verified</strong></span>
                                </div>
                            </div>
                            <div class="button-group" style="margin-top: 1.5rem;">
                                <button type="button" class="btn-info" onclick="downloadSignature()">
                                    <i class="fas fa-download"></i> Download Signature
                                </button>
                                <button type="button" class="btn-danger" onclick="confirmClearSignature()">
                                    <i class="fas fa-trash-alt"></i> Remove Signature
                                </button>
                            </div>
                        </div>
                    `;
                    
                    clearCanvas();
                    clearUpload();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred while saving the signature.', 'error');
                console.error('Error:', error);
            } finally {
                showLoading(false);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalHTML;
            }
        }

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="fas ${icon}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
            
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function showLoading(show) {
            document.getElementById('loadingOverlay').classList.toggle('active', show);
        }

        async function confirmClearSignature() {
            if (!confirm(`Are you sure you want to remove the signature for ${ADMIN_INFO.name} (Admin #${ADMIN_INFO.id})? This action cannot be undone.`)) {
                return;
            }
            
            showLoading(true);
            
            try {
                const formData = new FormData();
                formData.append('action', 'clear_signature');
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    console.log('Signature removed for Admin ID:', data.admin_id);
                    
                    document.getElementById('signatureDisplay').innerHTML = `
                        <div class="no-signature">
                            <i class="fas fa-file-signature"></i>
                            <p>No signature on file for <strong>${ADMIN_INFO.name}</strong></p>
                            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Create your digital signature below.</p>
                        </div>
                    `;
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('An error occurred while removing the signature.', 'error');
                console.error('Error:', error);
            } finally {
                showLoading(false);
            }
        }

        function downloadSignature() {
            const img = document.getElementById('currentSignatureImg');
            if (!img) return;
            
            const link = document.createElement('a');
            link.download = `signature_${ADMIN_INFO.name.replace(/\s+/g, '_')}_admin${ADMIN_INFO.id}_${new Date().getTime()}.png`;
            link.href = img.src;
            link.click();
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
                e.preventDefault();
                undoLastStroke();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const activeMethod = document.querySelector('.method-content.active');
                if (activeMethod.id === 'drawMethod') {
                    saveCanvasSignature();
                } else {
                    saveUploadedSignature();
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>