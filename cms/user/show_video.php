<?php
session_start();
include('include/config.php');
error_reporting(0);

// Set timeouts for file uploads and script execution (20 minutes = 1200 seconds)
set_time_limit(1200);
ini_set('max_input_time', 1200);
ini_set('upload_max_filesize', '1G');
ini_set('post_max_size', '1G');

// Ensure upload directory exists
if (!file_exists('complaintdocs')) {z
    mkdir('complaintdocs', 0777, true);
}

if (strlen($_SESSION['id']) == 0) {
    header('location:index.php');
    exit;
}

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('Y-m-d H:i:s', time());

// Fetch user data
$userId = $_SESSION['id'];
$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) {
    header('location:index.php');
    exit;
}

// Calculate initials for avatar
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}

// Fetch serial numbers for the dropdown
$serial_query = "SELECT DISTINCT serial_number, tyre_code, description FROM stock_erp ORDER BY serial_number";
$serial_result = mysqli_query($con, $serial_query);

// Function to handle upload errors
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "The uploaded file exceeds the upload_max_filesize directive.";
        case UPLOAD_ERR_FORM_SIZE:
            return "The uploaded file exceeds the MAX_FILE_SIZE directive.";
        case UPLOAD_ERR_PARTIAL:
            return "The uploaded file was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE:
            return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Missing a temporary folder.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION:
            return "A PHP extension stopped the file upload.";
        default:
            return "Unknown upload error.";
    }
}

// AJAX handler for getting tire details by serial number
if (isset($_GET['action']) && $_GET['action'] == 'get_tire_details') {
    $serial_number = mysqli_real_escape_string($con, $_GET['serial_number']);
    $query = "SELECT tyre_code, description FROM stock_erp WHERE serial_number = '$serial_number' LIMIT 1";
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'tire_size' => $row['description'],
            'description' => $row['description']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Serial number not found'
        ]);
    }
    exit;
}

// Handle form submission
if (isset($_POST['submit'])) {
    $uid = $_SESSION['id'];
    
    // Validate CSRF token (basic implementation)
    if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
        echo '<script>alert("Security token missing. Please refresh and try again.");</script>';
        exit;
    }
    
    // Product Details
    $serial_number = trim($_POST['serial_number'] ?? '');
    $tire_size = trim($_POST['tire_size'] ?? '');
    $purchase_date = trim($_POST['purchase_date'] ?? '');
    $purchase_location = trim($_POST['purchase_location'] ?? '');
    $invoice_number = trim($_POST['invoice_number'] ?? '');      
    $warranty_period = trim($_POST['warranty_period'] ?? '');
    
    // Vehicle Information
    $vehicle_make_model = trim($_POST['vehicle_make_model'] ?? '');
    $vehicle_year = trim($_POST['vehicle_year'] ?? '');
    $usage_type = trim($_POST['usage_type'] ?? '');
    $usage_type_other = trim($_POST['usage_type_other'] ?? '');
    
    // Complaint Details
    $nature_complaint = isset($_POST['nature_complaint']) ? implode(',', array_map('trim', $_POST['nature_complaint'])) : '';
    $nature_other = trim($_POST['nature_other'] ?? '');
    $detailed_description = trim($_POST['detailed_description'] ?? '');
    
    // Problem Timeline
    $mileage_hours = trim($_POST['mileage_hours'] ?? '');
    $duration_before_problem = trim($_POST['duration_before_problem'] ?? '');
    $operating_conditions = isset($_POST['operating_conditions']) ? implode(',', array_map('trim', $_POST['operating_conditions'])) : '';
    
    // Impact
    $impact = isset($_POST['impact']) ? implode(',', array_map('trim', $_POST['impact'])) : '';
    $impact_other = trim($_POST['impact_other'] ?? '');
    
    // Operating Conditions Details
    $daily_usage = trim($_POST['daily_usage'] ?? '');
    $load_capacity = trim($_POST['load_capacity'] ?? '');
    $surface_conditions = isset($_POST['surface_conditions']) ? implode(',', array_map('trim', $_POST['surface_conditions'])) : '';
    $temperature_conditions = trim($_POST['temperature_conditions'] ?? '');
    $speed_operation = trim($_POST['speed_operation'] ?? '');
    
    // Documentation
    $documentation = isset($_POST['documentation']) ? implode(',', array_map('trim', $_POST['documentation'])) : '';
    $other_documentation = trim($_POST['other_documentation'] ?? '');
    
    // Actions and Resolution
    $previous_actions = trim($_POST['previous_actions'] ?? '');
    $resolution_requested = isset($_POST['resolution_requested']) ? implode(',', array_map('trim', $_POST['resolution_requested'])) : '';
    $resolution_other = trim($_POST['resolution_other'] ?? '');
    $additional_comments = trim($_POST['additional_comments'] ?? '');
    
    // Server-side validation
    $errors = [];
    if (empty($serial_number)) $errors[] = 'Serial Number is required';
    if (empty($tire_size)) $errors[] = 'Tire Size is required';
    if (empty($purchase_date)) $errors[] = 'Purchase Date is required';
    if (empty($vehicle_make_model)) $errors[] = 'Vehicle Make/Model is required';
    if (empty($usage_type)) $errors[] = 'Usage Type is required';
    if (empty($nature_complaint)) $errors[] = 'At least one Nature of Complaint is required';
    if (empty($detailed_description)) $errors[] = 'Detailed Description is required';
    if (empty($operating_conditions)) $errors[] = 'At least one Operating Condition is required';
    
    // Check for 'Other' fields
    if ($usage_type === 'Other' && empty($usage_type_other)) $errors[] = 'Please specify the other usage type';
    if (strpos($nature_complaint, 'Other') !== false && empty($nature_other)) $errors[] = 'Please specify the other nature of complaint';
    if (strpos($impact, 'Other') !== false && empty($impact_other)) $errors[] = 'Please specify the other impact';
    if (strpos($resolution_requested, 'Other') !== false && empty($resolution_other)) $errors[] = 'Please specify the other resolution requested';
    
    // Validate purchase_date format (YYYY-MM-DD)
    if (!empty($purchase_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchase_date)) $errors[] = 'Invalid Purchase Date format';
    
    // Validate vehicle_year if provided
    if (!empty($vehicle_year) && (!is_numeric($vehicle_year) || $vehicle_year < 1900 || $vehicle_year > 2030)) $errors[] = 'Invalid Vehicle Year';
    
    // Validate daily_usage if provided
    if (!empty($daily_usage) && (!is_numeric($daily_usage) || $daily_usage < 0)) $errors[] = 'Invalid Daily Usage';
    
    if (!empty($errors)) {
        echo '<script>alert("Validation errors:\\n' . implode('\\n', array_map('addslashes', $errors)) . '");</script>';
    } else {
        // File handling for complaint file
        $compfile = $_FILES["compfile"]["name"] ?? '';
        $compfilenew = '';
        
        if (!empty($compfile) && $_FILES["compfile"]["error"] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($compfile, PATHINFO_EXTENSION));
            $allowed_extensions = array("jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "mp4", "mov", "avi", "mkv", "zip");
            $max_file_size = 1073741824; // 1GB in bytes
            
            // Additional MIME type validation for security
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES["compfile"]["tmp_name"]);
            finfo_close($finfo);
            $allowed_mimes = array(
                'image/jpeg', 'image/png', 'image/gif',
                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska',
                'application/zip'
            );
            
            if (!in_array($extension, $allowed_extensions) || !in_array($mime_type, $allowed_mimes)) {
                echo "<script>alert('Invalid file format or type for complaint file. Only specified formats allowed.');</script>";
            }
            elseif ($_FILES["compfile"]["size"] > $max_file_size) {
                echo "<script>alert('Complaint file size exceeds 1GB limit.');</script>";
            }
            else {
                $compfilenew = md5($compfile . time()) . '.' . $extension;
                if (!move_uploaded_file($_FILES["compfile"]["tmp_name"], "complaintdocs/" . $compfilenew)) {
                    echo "<script>alert('Error uploading complaint file. Please try again.');</script>";
                    $compfilenew = '';
                }
            }
        } elseif (!empty($compfile) && $_FILES["compfile"]["error"] !== UPLOAD_ERR_NO_FILE) {
            echo "<script>alert('File upload error: " . getUploadErrorMessage($_FILES["compfile"]["error"]) . "');</script>";
        }
        
        // Video file handling
        $com_video = $_FILES['com_video']['name'] ?? '';
        $video_data = null;
        $video_mime = '';
        $allowed_video_types = [
            'video/mp4' => 'mp4',
            'video/avi' => 'avi',
            'video/mpeg' => 'mpeg',
            'video/quicktime' => 'mov',
            'video/webm' => 'webm'
        ];
        $max_video_size = 100 * 1024 * 1024; // 100MB
        
        if (!empty($com_video) && $_FILES['com_video']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $actual_mime = $finfo->file($_FILES['com_video']['tmp_name']);
            if (array_key_exists($actual_mime, $allowed_video_types) && $_FILES['com_video']['size'] <= $max_video_size) {
                $video_data = file_get_contents($_FILES['com_video']['tmp_name']);
                if ($video_data === false) {
                    echo "<script>alert('Error reading video file.');</script>";
                    $video_data = null;
                    $video_mime = '';
                } else {
                    $video_mime = $actual_mime;
                }
            } else {
                echo "<script>alert('Unsupported video file type: " . htmlspecialchars($actual_mime) . " or size exceeded. Allowed formats: " . implode(', ', array_values($allowed_video_types)) . ". Max size: 100MB.');</script>";
            }
        } elseif ($_FILES['com_video']['error'] !== UPLOAD_ERR_NO_FILE) {
            echo "<script>alert('Video upload error: " . getUploadErrorMessage($_FILES['com_video']['error']) . "');</script>";
        }

        // Insert into database using prepared statement
        $stmt = $con->prepare("
            INSERT INTO tbl_tire_complaints (
                userId, serial_number, tire_size, purchase_date, purchase_location, invoice_number, warranty_period,
                vehicle_make_model, vehicle_year, usage_type, usage_type_other,
                nature_complaint, nature_other, detailed_description,
                mileage_hours, duration_before_problem, operating_conditions,
                impact, impact_other,
                daily_usage, load_capacity, surface_conditions, temperature_conditions, speed_operation,
                documentation, other_documentation,
                previous_actions, resolution_requested, resolution_other, additional_comments,
                complaint_file, com_video, video_mime, created_at, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $status = 'Pending';
        $stmt->bind_param(
            "isssssssssssssssssssssssssssssssbss",
            $uid, $serial_number, $tire_size, $purchase_date, $purchase_location, $invoice_number, $warranty_period,
            $vehicle_make_model, $vehicle_year, $usage_type, $usage_type_other,
            $nature_complaint, $nature_other, $detailed_description,
            $mileage_hours, $duration_before_problem, $operating_conditions,
            $impact, $impact_other,
            $daily_usage, $load_capacity, $surface_conditions, $temperature_conditions, $speed_operation,
            $documentation, $other_documentation,
            $previous_actions, $resolution_requested, $resolution_other, $additional_comments,
            $compfilenew, $video_data, $video_mime, $currentTime, $status
        );
        
        if ($stmt->execute()) {
            $complaint_id = $con->insert_id;
            echo '<script>alert("Your tire complaint has been successfully submitted. Complaint ID: ' . $complaint_id . '");</script>';
            echo '<script>window.location.href = "sent_mail.php";</script>';
        } else {
            error_log("Database error: " . $con->error);
            echo '<script>alert("Error submitting complaint. Please try again.");</script>';
        }
        $stmt->close();
    }
}

// Generate CSRF token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Service Portal - Tire Complaint Registration</title>
    <!-- Bootstrap CSS for layout -->
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
            --gradient-4: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-gray);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-btn {
            display: none;
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            border-radius: 0.5rem;
            color: var(--text-gray);
            transition: all 0.2s;
        }

        .menu-btn:hover {
            background: var(--orange-light);
            color: var(--primary-orange);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-img {
            height: 40px;
            width: auto;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 700;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 300px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border-gray);
            border-radius: 2rem;
            background: var(--white);
            font-size: 0.9rem;
            transition: all 0.3s;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: var(--text-gray);
            pointer-events: none;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            padding: 0.75rem;
            cursor: pointer;
            border-radius: 0.75rem;
            color: var(--text-gray);
            transition: all 0.2s;
        }

        .notification-btn:hover {
            background: var(--orange-light);
            color: var(--primary-orange);
        }

        .notification-badge {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            width: 0.5rem;
            height: 0.5rem;
            background: var(--error);
            border-radius: 50%;
        }

        .user-menu {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border: none;
            background: none;
            cursor: pointer;
            border-radius: 2rem;
            transition: all 0.2s;
        }

        .user-btn:hover {
            background: var(--orange-light);
        }

        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--gradient-1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-gray);
        }

        .user-details span {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        /* Layout */
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--border-gray);
            padding: 2rem 0;
            overflow-y: auto;
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
        }

        .nav-section {
            margin-bottom: 2rem;
            padding: 0 1.5rem;
        }

        .nav-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }

        .nav-item a:hover,
        .nav-item a.active {
            background: var(--orange-light);
            color: var(--primary-orange);
            transform: translateX(0.25rem);
        }

        .nav-item a.active::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 1.5rem;
            background: var(--primary-orange);
            border-radius: 2px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow: hidden;
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

        .header-actions-right {
            display: flex;
            gap: 1rem;
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
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--text-gray);
            border: 1px solid var(--border-gray);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
            border-color: var(--primary-orange);
            color: var(--primary-orange);
        }

        /* Form Styles */
        .card {
            background: var(--white);
            border-radius: 1rem;
            border: 1px solid var(--border-gray);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-section {
            background: var(--bg-light);
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0.75rem;
            border-left: 4px solid var(--primary-orange);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-orange);
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group label .required {
            color: var(--error);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
            outline: none;
        }

        .form-control[readonly] {
            background: var(--light-gray);
            cursor: not-allowed;
        }

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-item input[type="checkbox"],
        .checkbox-item input[type="radio"] {
            accent-color: var(--primary-orange);
        }

        .form-text {
            font-size: 0.85rem;
            color: var(--text-gray);
            margin-top: 0.25rem;
        }

        .tire-details {
            background: var(--orange-light);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            display: none;
        }

        .tire-details.show {
            display: block;
        }

        .tire-details h6 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary-orange);
            margin-bottom: 0.5rem;
        }

        .tire-details p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid var(--border-gray);
            border-radius: 0.5rem;
            background: var(--white);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
            padding-left: 12px;
            color: var(--dark-gray);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary-orange);
            box-shadow: var(--ring-orange);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .menu-btn {
                display: block;
            }

            .sidebar {
                position: fixed;
                top: 80px;
                left: 0;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                padding: 1rem;
            }

            .search-box {
                display: none;
            }

            .user-details {
                display: none;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
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
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <button class="menu-btn" id="menuBtn">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <div class="logo-container">
                <img src="atire.png" alt="Logo" class="logo-img">
                <div class="brand-text">Customer Portal</div>
            </div>
        </div>

        <div class="header-actions">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search complaints...">
            </div>
            
            <button class="notification-btn">
                <i class="fas fa-bell fa-lg"></i>
                <span class="notification-badge"></span>
            </button>

            <div class="user-menu">
                <button class="user-btn" id="userBtn">
                    <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($userData['fullName']); ?></h4>
                        <span><?php echo htmlspecialchars($userData['userEmail']); ?></span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <nav class="nav-section">
                <h3 class="nav-title">Dashboard</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php">
                            <i class="fas fa-home"></i>
                            Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="register-complaint.php" class="active">
                            <i class="fas fa-plus-circle"></i>
                            New Complaint
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="complaint-history.php">
                            <i class="fas fa-list"></i>
                            My Complaints
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i class="fas fa-chart-bar"></i>
                            Analytics
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <h3 class="nav-title">Account</h3>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="setting.php">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#">
                            <i class="fas fa-headset"></i>
                            Support
                        </a>
                    </li>
                </ul>
            </nav>

            <nav class="nav-section">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="logout.php" style="color: var(--error);">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Tire Complaint Registration</h1>
                    <p class="page-subtitle">File a new tire complaint with detailed information</p>
                </div>
                <div class="header-actions-right">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="card animate-in">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-tire"></i>
                        Tire Complaint Registration Form
                    </h2>
                </div>
                <div class="card-body">
                    <form method="post" name="tire_complaint" enctype="multipart/form-data" id="complaintForm">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <!-- Product Details Section -->
                        <div class="form-section">
                            <h6 class="section-title">Product Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="serial_number">Serial Number <span class="required">*</span></label>
                                        <select name="serial_number" id="serial_number" class="form-control searchable-select" required>
                                            <option value="">Select Serial Number</option>
                                            <?php if ($serial_result && mysqli_num_rows($serial_result) > 0): ?>
                                                <?php while ($row = mysqli_fetch_assoc($serial_result)): ?>
                                                    <option value="<?= htmlspecialchars($row['serial_number']) ?>" 
                                                            data-tire-size="<?= htmlspecialchars($row['description']) ?>"
                                                            data-description="<?= htmlspecialchars($row['description']) ?>">
                                                        <?= htmlspecialchars($row['serial_number']) ?> - <?= htmlspecialchars($row['tyre_code']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div id="tire-details" class="tire-details">
                                            <h6>Tire Information</h6>
                                            <p><strong>Tire Size/Code:</strong> <span id="tire-size-display">-</span></p>
                                            <p><strong>Description:</strong> <span id="tire-description-display">-</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tire Size <span class="required">*</span></label>
                                        <input type="text" name="tire_size" id="tire_size" class="form-control" required readonly>
                                        <small class="form-text">This field will be automatically filled when you select a serial number.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Purchase Date <span class="required">*</span></label>
                                        <input type="date" name="purchase_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Purchase Location</label>
                                        <input type="text" name="purchase_location" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Invoice/Receipt Number</label>
                                        <input type="text" name="invoice_number" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Warranty Period</label>
                                        <input type="text" name="warranty_period" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Information Section -->
                        <div class="form-section">
                            <h6 class="section-title">Vehicle Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Vehicle Make/Model <span class="required">*</span></label>
                                        <input type="text" name="vehicle_make_model" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Vehicle Year</label>
                                        <input type="number" name="vehicle_year" class="form-control" min="1900" max="2030">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Usage Type <span class="required">*</span></label>
                                        <div class="checkbox-group">
                                            <?php
                                            $usage_types = [
                                                'Personal/Private use',
                                                'Commercial/Business',
                                                'Agricultural',
                                                'Construction',
                                                'Mining',
                                                'Industrial',
                                                'Other'
                                            ];
                                            foreach ($usage_types as $index => $usage_type) {
                                                $usage_id = $index + 1;
                                                ?>
                                                <div class="checkbox-item">
                                                    <input type="radio" name="usage_type" value="<?php echo $usage_type; ?>" id="usage<?php echo $usage_id; ?>" required>
                                                    <label for="usage<?php echo $usage_id; ?>"><?php echo $usage_type; ?></label>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <input type="text" name="usage_type_other" id="usage_type_other" class="form-control" style="width: 200px; margin-left: 10px; display: none;" placeholder="Specify if Other">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Complaint Details Section -->
                        <div class="form-section">
                            <h6 class="section-title">Complaint Details</h6>
                            <div class="form-group">
                                <label>Nature of Complaint <span class="required">*</span></label>
                                <div class="checkbox-group">
                                    <?php
                                    $complaint_natures = [
                                        'Premature wear',
                                        'Sidewall damage',
                                        'Tread separation',
                                        'Blowout',
                                        'Cracking',
                                        'Bulging',
                                        'Puncture resistance',
                                        'Poor performance',
                                        'Manufacturing defect',
                                        'Other'
                                    ];
                                    foreach ($complaint_natures as $index => $nature_name) {
                                        $nature_id = $index + 1;
                                        ?>
                                        <div class="checkbox-item">
                                            <input type="checkbox" name="nature_complaint[]" value="<?php echo $nature_name; ?>" id="nature<?php echo $nature_id; ?>">
                                            <label for="nature<?php echo $nature_id; ?>"><?php echo $nature_name; ?></label>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="form-group mt-3">
                                    <label>Other (please specify):</label>
                                    <input type="text" name="nature_other" id="nature_other" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Detailed Description <span class="required">*</span></label>
                                <textarea name="detailed_description" class="form-control" rows="4" required maxlength="2000"></textarea>
                            </div>
                        </div>

                        <!-- Problem Timeline Section -->
                        <div class="form-section">
                            <h6 class="section-title">When Did the Problem Start?</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Mileage/Hours when issue began</label>
                                        <input type="text" name="mileage_hours" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Duration of use before problem</label>
                                        <input type="text" name="duration_before_problem" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Operating Conditions <span class="required">*</span></label>
                                        <div class="checkbox-group">
                                            <?php
                                            $operating_conditions = [
                                                'Highway driving',
                                                'City/Urban driving',
                                                'Off-road',
                                                'Heavy loads',
                                                'High speed',
                                                'Stop-and-go traffic',
                                                'Extreme weather'
                                            ];
                                            foreach ($operating_conditions as $index => $condition_name) {
                                                $condition_id = $index + 1;
                                                ?>
                                                <div class="checkbox-item">
                                                    <input type="checkbox" name="operating_conditions[]" value="<?php echo $condition_name; ?>" id="condition<?php echo $condition_id; ?>">
                                                    <label for="condition<?php echo $condition_id; ?>"><?php echo $condition_name; ?></label>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Impact Section -->
                        <div class="form-section">
                            <h6 class="section-title">Impact of the Problem</h6>
                            <div class="checkbox-group">
                                <?php
                                $impact_types = [
                                    'Safety concern',
                                    'Vehicle downtime',
                                    'Financial loss',
                                    'Inconvenience',
                                    'Property damage',
                                    'Other'
                                ];
                                foreach ($impact_types as $index => $impact_name) {
                                    $impact_id = $index + 1;
                                    ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="impact[]" value="<?php echo $impact_name; ?>" id="impact<?php echo $impact_id; ?>">
                                        <label for="impact<?php echo $impact_id; ?>"><?php echo $impact_name; ?></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="form-group mt-3">
                                <label>Other:</label>
                                <input type="text" name="impact_other" id="impact_other" class="form-control">
                            </div>
                        </div>

                        <!-- Operating Conditions Section -->
                        <div class="form-section">
                            <h6 class="section-title">Operating Conditions Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Average daily usage (hours)</label>
                                        <input type="number" name="daily_usage" class="form-control" step="0.1" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Load capacity typically carried</label>
                                        <input type="text" name="load_capacity" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Temperature conditions</label>
                                        <input type="text" name="temperature_conditions" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Speed of operation</label>
                                        <input type="text" name="speed_operation" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Surface Conditions</label>
                                        <div class="checkbox-group">
                                            <?php
                                            $surface_conditions = [
                                                'Paved roads',
                                                'Gravel',
                                                'Dirt/mud',
                                                'Rocky terrain',
                                                'Sand',
                                                'Mixed surfaces'
                                            ];
                                            foreach ($surface_conditions as $index => $surface_condition) {
                                                $surface_id = $index + 1;
                                                ?>
                                                <div class="checkbox-item">
                                                    <input type="checkbox" name="surface_conditions[]" value="<?php echo $surface_condition; ?>" id="surface<?php echo $surface_id; ?>">
                                                    <label for="surface<?php echo $surface_id; ?>"><?php echo $surface_condition; ?></label>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Supporting Documentation Section -->
                        <div class="form-section">
                            <h6 class="section-title">Supporting Documentation</h6>
                            <div class="checkbox-group">
                                <?php
                                $documentation_types = [
                                    'Photos of tire damage',
                                    'Purchase receipt',
                                    'Warranty documentation',
                                    'Maintenance records',
                                    'Inspection reports',
                                    'Other'
                                ];
                                foreach ($documentation_types as $index => $doc_name) {
                                    $doc_id = $index + 1;
                                    ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="documentation[]" value="<?php echo $doc_name; ?>" id="doc<?php echo $doc_id; ?>">
                                        <label for="doc<?php echo $doc_id; ?>"><?php echo $doc_name; ?></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="form-group mt-3">
                                <label>Other documentation:</label>
                                <input type="text" name="other_documentation" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Upload Supporting Documents <span class="form-text">You can upload images, PDFs, or other documents here to support your complaint.</span></label>
                                <input type="file" name="compfile" id="compfile" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.mp4,.mov,.avi,.mkv,.zip">
                                <small class="form-text">Allowed formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX, MP4, MOV, AVI, MKV, ZIP. Maximum file size: 1GB. Maximum upload time: 20 minutes.</small>
                                <div class="video-preview" id="filePreview"></div>
                            </div>
                            <div class="form-group">
                                <label>Upload Complaint Video <span class="form-text">You can upload a video to support your complaint.</span></label>
                                <input type="file" name="com_video" id="com_video" class="form-control" accept="video/mp4,video/avi,video/mpeg,video/quicktime,video/webm">
                                <small class="form-text">Allowed formats: MP4, AVI, MPEG, MOV, WEBM. Maximum file size: 100MB.</small>
                                <div class="video-preview" id="videoPreview"></div>
                                <div class="upload-progress" id="uploadProgress">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar">0%</div>
                                    </div>
                                    <small>Uploading... This may take a few minutes for large files like videos.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Previous Actions Section -->
                        <div class="form-section">
                            <h6 class="section-title">Previous Actions Taken</h6>
                            <div class="form-group">
                                <textarea name="previous_actions" class="form-control" rows="3" placeholder="Describe any actions already taken to resolve the issue..."></textarea>
                            </div>
                        </div>

                        <!-- Resolution Section -->
                        <div class="form-section">
                            <h6 class="section-title">Resolution Requested</h6>
                            <div class="checkbox-group">
                                <?php
                                $resolution_types = [
                                    'Replacement',
                                    'Refund',
                                    'Credit',
                                    'Repair',
                                    'Investigation',
                                    'Compensation',
                                    'Other'
                                ];
                                foreach ($resolution_types as $index => $resolution_name) {
                                    $resolution_id = $index + 1;
                                    ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" name="resolution_requested[]" value="<?php echo $resolution_name; ?>" id="resolution<?php echo $resolution_id; ?>">
                                        <label for="resolution<?php echo $resolution_id; ?>"><?php echo $resolution_name; ?></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="form-group mt-3">
                                <label>Other:</label>
                                <input type="text" name="resolution_other" id="resolution_other" class="form-control">
                            </div>
                        </div>

                        <!-- Additional Comments Section -->
                        <div class="form-section">
                            <h6 class="section-title">Additional Comments</h6>
                            <div class="form-group">
                                <textarea name="additional_comments" class="form-control" rows="4" maxlength="2000"></textarea>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" name="submit">
                                <i class="fas fa-paper-plane"></i> Submit Tire Complaint
                            </button>
                            <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Menu Toggle for Mobile
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        
        menuBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // User dropdown functionality
        const userBtn = document.getElementById('userBtn');
        userBtn?.addEventListener('click', () => {
            // Add dropdown functionality here
            console.log('User menu clicked');
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        searchInput?.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            console.log('Searching for:', query);
            // Add search logic here
        });

        // Initialize Select2 and form functionality
        $(document).ready(function() {
            // Initialize Select2 for serial number dropdown
            $('#serial_number').select2({
                placeholder: "Select or search for a serial number",
                allowClear: true,
                width: '100%'
            });
            
            // Handle serial number selection
            $('#serial_number').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var serialNumber = $(this).val();
                
                if (serialNumber) {
                    var tireSize = selectedOption.data('tire-size');
                    var description = selectedOption.data('description');
                    
                    $('#tire_size').val(tireSize);
                    $('#tire-size-display').text(tireSize || '-');
                    $('#tire-description-display').text(description || '-');
                    $('#tire-details').addClass('show');
                } else {
                    $('#tire_size').val('');
                    $('#tire-size-display').text('-');
                    $('#tire-description-display').text('-');
                    $('#tire-details').removeClass('show');
                }
            });
            
            // Enhanced Form validation and submission
            $('#complaintForm').on('submit', function(event) {
                // Client-side validation enhancements
                if (!$('#serial_number').val()) {
                    alert('Please select a serial number.');
                    event.preventDefault();
                    return false;
                }
                
                const natureCheckboxes = document.querySelectorAll('input[name="nature_complaint[]"]:checked');
                if (natureCheckboxes.length === 0) {
                    alert('Please select at least one Nature of Complaint.');
                    event.preventDefault();
                    return false;
                }
              
                const operatingCheckboxes = document.querySelectorAll('input[name="operating_conditions[]"]:checked');
                if (operatingCheckboxes.length === 0) {
                    alert('Please select at least one Operating Condition.');
                    event.preventDefault();
                    return false;
                }

                // Check usage_type
                const usageType = document.querySelector('input[name="usage_type"]:checked');
                if (!usageType) {
                    alert('Please select a Usage Type.');
                    event.preventDefault();
                    return false;
                }
                if (usageType.value === 'Other') {
                    const otherUsage = document.getElementById('usage_type_other');
                    if (!otherUsage.value.trim()) {
                        alert('Please specify the other usage type.');
                        event.preventDefault();
                        return false;
                    }
                }

                // Check for Other in nature_complaint
                const otherNatureChecked = Array.from(natureCheckboxes).some(cb => cb.value === 'Other');
                if (otherNatureChecked) {
                    const otherNature = document.getElementById('nature_other');
                    if (!otherNature.value.trim()) {
                        alert('Please specify the other nature of complaint.');
                        event.preventDefault();
                        return false;
                    }
                }

                // Similar checks for impact and resolution
                const impactCheckboxes = document.querySelectorAll('input[name="impact[]"]:checked');
                const otherImpactChecked = Array.from(impactCheckboxes).some(cb => cb.value === 'Other');
                if (otherImpactChecked) {
                    const otherImpact = document.getElementById('impact_other');
                    if (!otherImpact.value.trim()) {
                        alert('Please specify the other impact.');
                        event.preventDefault();
                        return false;
                    }
                }

                const resolutionCheckboxes = document.querySelectorAll('input[name="resolution_requested[]"]:checked');
                const otherResolutionChecked = Array.from(resolutionCheckboxes).some(cb => cb.value === 'Other');
                if (otherResolutionChecked) {
                    const otherResolution = document.getElementById('resolution_other');
                    if (!otherResolution.value.trim()) {
                        alert('Please specify the other resolution requested.');
                        event.preventDefault();
                        return false;
                    }
                }

                // Check for file upload feedback
                const fileInput = document.getElementById('compfile');
                const videoInput = document.getElementById('com_video');
                
                if (fileInput.files.length > 0) {
                    const fileSize = fileInput.files[0].size / 1024 / 1024; // Size in MB
                    if (fileSize > 1024) {
                        if (!confirm('The complaint file is large (over 1GB). Upload may take time. Continue?')) {
                            event.preventDefault();
                            return false;
                        }
                    }
                }
                
                if (videoInput.files.length > 0) {
                    const videoSize = videoInput.files[0].size / 1024 / 1024; // Size in MB
                    if (videoSize > 100) {
                        alert('Video file exceeds 100MB limit. Please choose a smaller file.');
                        event.preventDefault();
                        return false;
                    }
                    
                    // Show progress bar
                    $('#uploadProgress').show();
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += Math.random() * 15;
                        if (progress > 90) progress = 90;
                        $('#progressBar').css('width', progress + '%').text(Math.round(progress) + '%');
                    }, 500);
                    
                    // Clear progress on form submission
                    setTimeout(() => {
                        clearInterval(interval);
                        $('#progressBar').css('width', '100%').text('100%');
                    }, 2000);
                }

                return true;
            });
            
            // Reset form functionality
            window.resetForm = function() {
                $('#serial_number').val(null).trigger('change');
                $('#tire-details').removeClass('show');
                document.getElementById('filePreview').innerHTML = '';
                document.getElementById('videoPreview').innerHTML = '';
                $('#uploadProgress').hide();
                $('#progressBar').css('width', '0%').text('0%');
                // Reset checkboxes and radios
                $('input[type="checkbox"]').prop('checked', false);
                $('input[type="radio"]').prop('checked', false);
                $('#usage_type_other, #nature_other, #impact_other, #resolution_other').val('').hide();
            };
            
            // Show/hide "Other" input for usage type
            $('input[name="usage_type"]').on('change', function() {
                if ($(this).val() === 'Other') {
                    $('#usage_type_other').show().prop('required', true);
                } else {
                    $('#usage_type_other').hide().prop('required', false).val('');
                }
            });
            
            // Handle nature of complaint "Other" field
            $('input[name="nature_complaint[]"]').on('change', function() {
                const otherChecked = $('input[name="nature_complaint[]"][value="Other"]').is(':checked');
                $('#nature_other').prop('required', otherChecked);
                if (!otherChecked) {
                    $('#nature_other').val('');
                }
            });
            
            // Handle impact "Other" field
            $('input[name="impact[]"]').on('change', function() {
                const otherChecked = $('input[name="impact[]"][value="Other"]').is(':checked');
                $('#impact_other').prop('required', otherChecked);
                if (!otherChecked) {
                    $('#impact_other').val('');
                }
            });
            
            // Handle resolution "Other" field
            $('input[name="resolution_requested[]"]').on('change', function() {
                const otherChecked = $('input[name="resolution_requested[]"][value="Other"]').is(':checked');
                $('#resolution_other').prop('required', otherChecked);
                if (!otherChecked) {
                    $('#resolution_other').val('');
                }
            });

            // File and Video Preview functionality
            function handleFilePreview(inputId, previewId) {
                $(`#${inputId}`).on('change', function(e) {
                    const file = e.target.files[0];
                    const preview = document.getElementById(previewId);
                    preview.innerHTML = '';
                    
                    if (file) {
                        const fileSize = (file.size / 1024 / 1024).toFixed(2);
                        
                        if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = URL.createObjectURL(file);
                            video.controls = true;
                            video.style.maxWidth = '100%';
                            video.style.maxHeight = '200px';
                            preview.appendChild(video);
                            
                            const info = document.createElement('p');
                            info.textContent = `Video: ${file.name} (${fileSize} MB)`;
                            info.style.fontSize = '0.85rem';
                            info.style.color = 'var(--text-gray)';
                            info.style.marginTop = '0.5rem';
                            preview.appendChild(info);
                        } else if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.style.maxWidth = '100%';
                            img.style.maxHeight = '200px';
                            img.style.objectFit = 'contain';
                            preview.appendChild(img);
                            
                            const info = document.createElement('p');
                            info.textContent = `Image: ${file.name} (${fileSize} MB)`;
                            info.style.fontSize = '0.85rem';
                            info.style.color = 'var(--text-gray)';
                            info.style.marginTop = '0.5rem';
                            preview.appendChild(info);
                        } else {
                            const fileInfo = document.createElement('div');
                            fileInfo.innerHTML = `
                                <div style="padding: 1rem; background: var(--light-gray); border-radius: 0.5rem;">
                                    <i class="fas fa-file" style="font-size: 2rem; color: var(--primary-orange);"></i>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;"><strong>${file.name}</strong></p>
                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-gray);">Size: ${fileSize} MB</p>
                                </div>
                            `;
                            preview.appendChild(fileInfo);
                        }
                    }
                });
            }

            // Initialize file preview handlers
            handleFilePreview('compfile', 'filePreview');
            handleFilePreview('com_video', 'videoPreview');
            
            // Form field validation on blur
            $('input[required], textarea[required], select[required]').on('blur', function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Real-time character count for textareas
            $('textarea[maxlength]').on('input', function() {
                const maxLength = $(this).attr('maxlength');
                const currentLength = $(this).val().length;
                const remaining = maxLength - currentLength;
                
                let counterElement = $(this).siblings('.char-counter');
                if (counterElement.length === 0) {
                    counterElement = $('<small class="char-counter form-text"></small>');
                    $(this).after(counterElement);
                }
                
                counterElement.text(`${remaining} characters remaining`);
                if (remaining < 100) {
                    counterElement.css('color', 'var(--warning)');
                } else {
                    counterElement.css('color', 'var(--text-gray)');
                }
            });
        });

        // Animate elements on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card, .form-section').forEach(el => {
            observer.observe(el);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput?.focus();
            }
            
            // Escape to close mobile sidebar
            if (e.key === 'Escape' && window.innerWidth <= 768) {
                sidebar.classList.remove('show');
            }
            
            // Ctrl/Cmd + S to save form (submit)
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const submitBtn = document.querySelector('button[name="submit"]');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        });

        // Auto-save functionality (to localStorage for temporary storage)
        let autoSaveInterval;
        function startAutoSave() {
            autoSaveInterval = setInterval(() => {
                const formData = new FormData(document.getElementById('complaintForm'));
                const formObject = {};
                
                // Convert FormData to regular object (excluding files)
                for (let [key, value] of formData.entries()) {
                    if (key !== 'compfile' && key !== 'com_video') {
                        if (formObject[key]) {
                            if (Array.isArray(formObject[key])) {
                                formObject[key].push(value);
                            } else {
                                formObject[key] = [formObject[key], value];
                            }
                        } else {
                            formObject[key] = value;
                        }
                    }
                }
                
                try {
                    localStorage.setItem('tire_complaint_draft', JSON.stringify(formObject));
                    console.log('Form auto-saved');
                } catch (e) {
                    console.log('Auto-save failed:', e);
                }
            }, 30000); // Auto-save every 30 seconds
        }

        // Load draft on page load
        function loadDraft() {
            try {
                const draft = localStorage.getItem('tire_complaint_draft');
                if (draft) {
                    const formData = JSON.parse(draft);
                    
                    // Restore form values
                    Object.keys(formData).forEach(key => {
                        const element = document.querySelector(`[name="${key}"]`);
                        if (element) {
                            if (element.type === 'checkbox' || element.type === 'radio') {
                                const values = Array.isArray(formData[key]) ? formData[key] : [formData[key]];
                                values.forEach(value => {
                                    const specificElement = document.querySelector(`[name="${key}"][value="${value}"]`);
                                    if (specificElement) {
                                        specificElement.checked = true;
                                    }
                                });
                            } else {
                                element.value = formData[key];
                            }
                        }
                    });
                    
                    // Trigger change events
                    $('#serial_number').trigger('change');
                    $('input[name="usage_type"]:checked').trigger('change');
                    
                    console.log('Draft loaded');
                }
            } catch (e) {
                console.log('Failed to load draft:', e);
            }
        }

        // Clear draft after successful submission
        function clearDraft() {
            try {
                localStorage.removeItem('tire_complaint_draft');
                console.log('Draft cleared');
            } catch (e) {
                console.log('Failed to clear draft:', e);
            }
        }

        // Initialize auto-save and load draft
        document.addEventListener('DOMContentLoaded', () => {
            loadDraft();
            startAutoSave();
            
            // Clear auto-save interval before page unload
            window.addEventListener('beforeunload', () => {
                if (autoSaveInterval) {
                    clearInterval(autoSaveInterval);
                }
            });
        });

        console.log('Tire Complaint Registration Form loaded successfully!');
    </script>
</body>
</html>