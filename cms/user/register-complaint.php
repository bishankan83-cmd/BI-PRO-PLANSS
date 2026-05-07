<?php
session_start();
include('include/config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(1200);
ini_set('max_input_time', 1200);
ini_set('upload_max_filesize', '1G');
ini_set('post_max_size', '1G');

if (!file_exists('complaintdocs')) { mkdir('complaintdocs', 0777, true); }
if (!file_exists('uploads/videos')) { mkdir('uploads/videos', 0777, true); }
if (!file_exists('uploads/tire_photos')) { mkdir('uploads/tire_photos', 0777, true); }
if (!file_exists('uploads/additional_photos')) { mkdir('uploads/additional_photos', 0777, true); }

if (empty($_SESSION['id'])) { header('location:index.php'); exit; }

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('Y-m-d H:i:s', time());
$userId = $_SESSION['id'];
$queryUser = mysqli_query($con, "SELECT fullName, userEmail FROM users WHERE id = '$userId'");
$userData = mysqli_fetch_assoc($queryUser);
if (!$userData) { header('location:index.php'); exit; }

$dealerCompanyName = $userData['fullName'];
$initials = strtoupper(substr($userData['fullName'], 0, 1));
if (strpos($userData['fullName'], ' ') !== false) {
    $initials .= strtoupper(substr($userData['fullName'], strpos($userData['fullName'], ' ') + 1, 1));
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE: return "The uploaded file exceeds the upload_max_filesize directive.";
        case UPLOAD_ERR_FORM_SIZE: return "The uploaded file exceeds the MAX_FILE_SIZE directive.";
        case UPLOAD_ERR_PARTIAL: return "The uploaded file was only partially uploaded.";
        case UPLOAD_ERR_NO_FILE: return "No file was uploaded.";
        case UPLOAD_ERR_NO_TMP_DIR: return "Missing a temporary folder.";
        case UPLOAD_ERR_CANT_WRITE: return "Failed to write file to disk.";
        case UPLOAD_ERR_EXTENSION: return "A PHP extension stopped the file upload.";
        default: return "Unknown upload error.";
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'get_tire_info') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'description' => '', 'invoice_numbers' => [], 'dispatch_date' => '', 'message' => ''];
    $serial_number = trim($_POST['serial_number'] ?? '');
    if (empty($serial_number)) { $response['message'] = 'Serial number is required.'; echo json_encode($response); exit; }
    $stmt = $con->prepare("SELECT description, erp FROM dwork_ser WHERE serial_number = ? LIMIT 1");
    $stmt->bind_param("s", $serial_number); $stmt->execute(); $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $response['success'] = true; $response['description'] = $row['description'] ?? ''; $erp = $row['erp'];
        $stmt2 = $con->prepare("SELECT invoice_number FROM order_invoice WHERE erp = ?");
        $stmt2->bind_param("s", $erp); $stmt2->execute(); $result2 = $stmt2->get_result();
        $invoice_numbers = [];
        while ($invoice_row = $result2->fetch_assoc()) { $invoice_numbers[] = $invoice_row['invoice_number']; }
        $response['invoice_numbers'] = $invoice_numbers; $stmt2->close();
        $stmt3 = $con->prepare("SELECT dispatch_date FROM pros WHERE erp_number = ? ORDER BY dispatch_date DESC LIMIT 1");
        $stmt3->bind_param("s", $erp); $stmt3->execute(); $result3 = $stmt3->get_result();
        if ($dispatch_row = $result3->fetch_assoc()) { $response['dispatch_date'] = $dispatch_row['dispatch_date']; }
        $stmt3->close();
        if (count($invoice_numbers) > 0 && !empty($response['dispatch_date'])) { $response['message'] = 'Tire information, invoice(s), and dispatch date found successfully.'; }
        elseif (count($invoice_numbers) > 0) { $response['message'] = 'Tire information and invoice(s) found, but no dispatch date found.'; }
        elseif (!empty($response['dispatch_date'])) { $response['message'] = 'Tire information and dispatch date found, but no invoices found.'; }
        else { $response['message'] = 'Tire information found, but no invoices or dispatch date found.'; }
    } else { $response['message'] = 'No tire information found for this serial number.'; }
    $stmt->close(); echo json_encode($response); exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'file_id' => 0];
    if (!isset($_FILES['file'])) { $response['message'] = 'No file uploaded.'; echo json_encode($response); exit; }
    $file = $_FILES['file']; $file_type = $_POST['file_type'] ?? ''; $tire_position = $_POST['tire_position'] ?? '';
    if ($file['error'] !== UPLOAD_ERR_OK) { $response['message'] = getUploadErrorMessage($file['error']); echo json_encode($response); exit; }
    $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime_type = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
    $allowed_extensions = ["jpg","jpeg","png","gif","pdf","doc","docx","mp4","mov","avi","mkv","zip"];
    $allowed_mimes = ['image/jpeg','image/png','image/gif','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','video/mp4','video/quicktime','video/x-msvideo','video/x-matroska','application/zip'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $max_file_size = ($file_type === 'video') ? 500 * 1024 * 1024 : 1073741824;
    if (!in_array($extension, $allowed_extensions) || !in_array($mime_type, $allowed_mimes)) { $response['message'] = 'Invalid file format or type.'; echo json_encode($response); exit; }
    if ($file['size'] > $max_file_size) { $response['message'] = $file_type === 'video' ? 'Video file size exceeds 500MB limit.' : 'File size exceeds 1GB limit.'; echo json_encode($response); exit; }
    if ($file_type === 'video') { $destination_dir = 'uploads/videos/'; }
    elseif ($file_type === 'tire_photo') { $destination_dir = 'uploads/tire_photos/'; }
    elseif ($file_type === 'additional_photo') { $destination_dir = 'uploads/additional_photos/'; }
    else { $destination_dir = 'complaintdocs/'; }
    $new_filename = md5($file['name'] . time() . $userId . session_id() . $tire_position) . '.' . $extension;
    $file_path = $destination_dir . $new_filename;
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $stmt = $con->prepare("INSERT INTO tbl_temp_uploads (user_id, file_name, file_path, file_type, file_size, mime_type, upload_time, session_id, tire_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $session_id = session_id();
        $stmt->bind_param("isssissss", $userId, $file['name'], $file_path, $file_type, $file['size'], $mime_type, $currentTime, $session_id, $tire_position);
        if ($stmt->execute()) { $response['success'] = true; $response['file_id'] = $con->insert_id; $response['message'] = 'File uploaded successfully.'; }
        else { $response['message'] = 'Error saving file info to database: ' . $stmt->error; unlink($file_path); }
        $stmt->close();
    } else { $response['message'] = 'Error moving uploaded file.'; }
    echo json_encode($response); exit;
}

if (isset($_POST['submit'])) {
    $uid = $_SESSION['id']; $session_id = session_id();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) { echo '<script>alert("Invalid security token. Please refresh and try again.");</script>'; exit; }
    $serial_number = trim($_POST['serial_number'] ?? ''); $tire_size = trim($_POST['tire_size'] ?? ''); $purchase_date = trim($_POST['purchase_date'] ?? ''); $purchase_location = trim($_POST['purchase_location'] ?? ''); $invoice_number = trim($_POST['invoice_number'] ?? ''); $warranty_period = trim($_POST['warranty_period'] ?? ''); $equipment_type = trim($_POST['equipment_type'] ?? ''); $equipment_type_other = trim($_POST['equipment_type_other'] ?? ''); $vehicle_make_model = trim($_POST['vehicle_make_model'] ?? ''); $vehicle_year = trim($_POST['vehicle_year'] ?? ''); $usage_type = trim($_POST['usage_type'] ?? ''); $usage_type_other = trim($_POST['usage_type_other'] ?? ''); $usage_pattern = isset($_POST['usage_pattern']) ? implode(',', array_map('trim', $_POST['usage_pattern'])) : ''; $usage_pattern_other = trim($_POST['usage_pattern_other'] ?? ''); $nature_Claim = trim($_POST['nature_complaint'] ?? ''); $nature_other = trim($_POST['nature_other'] ?? ''); $detailed_description = trim($_POST['detailed_description'] ?? ''); $mileage_hours = trim($_POST['mileage_hours'] ?? ''); $duration_before_problem = trim($_POST['duration_before_problem'] ?? ''); $operating_conditions = ''; $impact = trim($_POST['impact'] ?? ''); $impact_other = trim($_POST['impact_other'] ?? ''); $daily_usage = trim($_POST['daily_usage'] ?? ''); $load_capacity = trim($_POST['load_capacity'] ?? ''); $surface_conditions = isset($_POST['surface_conditions']) ? implode(',', array_map('trim', $_POST['surface_conditions'])) : ''; $temperature_conditions = trim($_POST['temperature_conditions'] ?? ''); $speed_operation = trim($_POST['speed_operation'] ?? ''); $documentation = isset($_POST['documentation']) ? implode(',', array_map('trim', $_POST['documentation'])) : ''; $other_documentation = trim($_POST['other_documentation'] ?? ''); $previous_actions = trim($_POST['previous_actions'] ?? ''); $resolution_requested = trim($_POST['resolution_requested'] ?? ''); $resolution_other = trim($_POST['resolution_other'] ?? ''); $additional_comments = trim($_POST['additional_comments'] ?? ''); $document_file_id = intval($_POST['document_file_id'] ?? 0); $video_file_id = intval($_POST['video_file_id'] ?? 0); $front_left_file_id = intval($_POST['front_left_file_id'] ?? 0); $Thread_patternn_file_id = intval($_POST['Thread_patternn_file_id'] ?? 0); $falk1_file_id = intval($_POST['falk1_file_id'] ?? 0); $falk3_file_id = intval($_POST['falk3_file_id'] ?? 0); $additional_1_file_id = intval($_POST['additional_photo_1_file_id'] ?? 0); $additional_photo_2_file_id = intval($_POST['additional_photo_2_file_id'] ?? 0); $additional_photo_3_file_id = intval($_POST['additional_photo_3_file_id'] ?? 0); $additional_photo_4_file_id = intval($_POST['additional_photo_4_file_id'] ?? 0);
    $errors = [];
    if (empty($serial_number)) $errors[] = 'Serial Number is required';
    if (empty($tire_size)) $errors[] = 'Tire Size is required';
    if (empty($purchase_date)) $errors[] = 'Purchase Date is required';
    if (empty($equipment_type)) $errors[] = 'Equipment Type is required';
    if (empty($vehicle_make_model)) $errors[] = 'Vehicle Make/Model is required';
    if (empty($usage_type)) $errors[] = 'Usage Type is required';
    if (empty($usage_pattern)) $errors[] = 'At least one Usage Pattern is required';
    if (empty($nature_Claim)) $errors[] = 'Nature of Claim is required';
    if (empty($detailed_description)) $errors[] = 'Detailed Description is required';
    if ($equipment_type === 'Other' && empty($equipment_type_other)) $errors[] = 'Please specify the other equipment type';
    if ($usage_type === 'Other' && empty($usage_type_other)) $errors[] = 'Please specify the other usage type';
    $usage_pattern_array = isset($_POST['usage_pattern']) ? $_POST['usage_pattern'] : [];
    if (in_array('Other', $usage_pattern_array) && empty($usage_pattern_other)) $errors[] = 'Please specify the other usage pattern';
    if ($nature_Claim === 'Other' && empty($nature_other)) $errors[] = 'Please specify the other nature of complaint';
    if ($impact === 'Other' && empty($impact_other)) $errors[] = 'Please specify the other impact';
    if ($resolution_requested === 'Other' && empty($resolution_other)) $errors[] = 'Please specify the other resolution requested';
    if (!empty($purchase_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchase_date)) $errors[] = 'Invalid Purchase Date format';
    if (!empty($vehicle_year) && (!is_numeric($vehicle_year) || $vehicle_year < 1900 || $vehicle_year > 2030)) $errors[] = 'Invalid Vehicle Year';
    if (!empty($daily_usage) && (!is_numeric($daily_usage) || $daily_usage < 0)) $errors[] = 'Invalid Daily Usage';
    if (!empty($errors)) {
        echo '<script>alert("Validation errors:\\n' . implode('\\n', array_map('addslashes', $errors)) . '");</script>';
    } else {
        $con->begin_transaction();
        try {
            $compfile_path = $video_filename = $video_path = $video_size = $video_mime = null;
            $front_left_photo = $Thread_patternn_photo = $falk1_photo = $falk3_photo = null;
            $additional_photo_1 = $additional_photo_2 = $additional_photo_3 = $additional_photo_4 = null;
            if ($document_file_id > 0) { $stmt = $con->prepare("SELECT file_path FROM tbl_temp_uploads WHERE id = ? AND user_id = ? AND file_type = 'document' AND session_id = ?"); $stmt->bind_param("iis", $document_file_id, $uid, $session_id); $stmt->execute(); $result = $stmt->get_result(); if ($row = $result->fetch_assoc()) { $compfile_path = $row['file_path']; } $stmt->close(); }
            if ($video_file_id > 0) { $stmt = $con->prepare("SELECT file_name, file_path, file_size, mime_type FROM tbl_temp_uploads WHERE id = ? AND user_id = ? AND file_type = 'video' AND session_id = ?"); $stmt->bind_param("iis", $video_file_id, $uid, $session_id); $stmt->execute(); $result = $stmt->get_result(); if ($row = $result->fetch_assoc()) { $video_filename = $row['file_name']; $video_path = $row['file_path']; $video_size = $row['file_size']; $video_mime = $row['mime_type']; } $stmt->close(); }
            $tire_positions = ['front_left' => $front_left_file_id, 'Thread_patternn' => $Thread_patternn_file_id, 'falk1' => $falk1_file_id, 'falk3' => $falk3_file_id];
            foreach ($tire_positions as $position => $file_id) { if ($file_id > 0) { $stmt = $con->prepare("SELECT file_path FROM tbl_temp_uploads WHERE id = ? AND user_id = ? AND file_type = 'tire_photo' AND session_id = ? AND tire_position = ?"); $stmt->bind_param("iiss", $file_id, $uid, $session_id, $position); $stmt->execute(); $result = $stmt->get_result(); if ($row = $result->fetch_assoc()) { ${$position . '_photo'} = $row['file_path']; } $stmt->close(); } }
            $add_positions = [1 => $additional_1_file_id, 2 => $additional_photo_2_file_id, 3 => $additional_photo_3_file_id, 4 => $additional_photo_4_file_id];
            foreach ($add_positions as $num => $file_id) { if ($file_id > 0) { $pos = 'additional_' . $num; $stmt = $con->prepare("SELECT file_path FROM tbl_temp_uploads WHERE id = ? AND user_id = ? AND file_type = 'additional_photo' AND session_id = ? AND tire_position = ?"); $stmt->bind_param("iiss", $file_id, $uid, $session_id, $pos); $stmt->execute(); $result = $stmt->get_result(); if ($row = $result->fetch_assoc()) { ${'additional_photo_' . $num} = $row['file_path']; } $stmt->close(); } }
            $serial_number = !empty($serial_number) ? $serial_number : null; $tire_size = !empty($tire_size) ? $tire_size : null; $purchase_date = !empty($purchase_date) ? $purchase_date : null; $purchase_location = !empty($purchase_location) ? $purchase_location : null; $invoice_number = !empty($invoice_number) ? $invoice_number : null; $warranty_period = !empty($warranty_period) ? $warranty_period : null; $equipment_type = !empty($equipment_type) ? $equipment_type : null; $equipment_type_other = !empty($equipment_type_other) ? $equipment_type_other : null; $vehicle_make_model = !empty($vehicle_make_model) ? $vehicle_make_model : null; $vehicle_year = !empty($vehicle_year) ? $vehicle_year : null; $usage_type = !empty($usage_type) ? $usage_type : null; $usage_type_other = !empty($usage_type_other) ? $usage_type_other : null; $usage_pattern = !empty($usage_pattern) ? $usage_pattern : null; $usage_pattern_other = !empty($usage_pattern_other) ? $usage_pattern_other : null; $nature_Claim = !empty($nature_Claim) ? $nature_Claim : null; $nature_other = !empty($nature_other) ? $nature_other : null; $detailed_description = !empty($detailed_description) ? $detailed_description : null; $mileage_hours = !empty($mileage_hours) ? $mileage_hours : null; $duration_before_problem = !empty($duration_before_problem) ? $duration_before_problem : null; $operating_conditions = !empty($operating_conditions) ? $operating_conditions : null; $impact = !empty($impact) ? $impact : null; $impact_other = !empty($impact_other) ? $impact_other : null; $daily_usage = !empty($daily_usage) ? $daily_usage : null; $load_capacity = !empty($load_capacity) ? $load_capacity : null; $surface_conditions = !empty($surface_conditions) ? $surface_conditions : null; $temperature_conditions = !empty($temperature_conditions) ? $temperature_conditions : null; $speed_operation = !empty($speed_operation) ? $speed_operation : null; $documentation = !empty($documentation) ? $documentation : null; $other_documentation = !empty($other_documentation) ? $other_documentation : null; $previous_actions = !empty($previous_actions) ? $previous_actions : null; $resolution_requested = !empty($resolution_requested) ? $resolution_requested : null; $resolution_other = !empty($resolution_other) ? $resolution_other : null; $additional_comments = !empty($additional_comments) ? $additional_comments : null;
            $sql = "INSERT INTO tbl_tire_complaints (userId, serial_number, tire_size, purchase_date, purchase_location, invoice_number, warranty_period, equipment_type, equipment_type_other, vehicle_make_model, vehicle_year, usage_type, usage_type_other, usage_pattern, usage_pattern_other, nature_complaint, nature_other, detailed_description, mileage_hours, duration_before_problem, operating_conditions, impact, impact_other, daily_usage, load_capacity, surface_conditions, temperature_conditions, speed_operation, documentation, other_documentation, previous_actions, resolution_requested, resolution_other, additional_comments, complaint_file, video_filename, video_path, video_size, video_mime, front_left_photo, front_right_photo, rear_left_photo, rear_right_photo, additional_photo_1, additional_photo_2, additional_photo_3, additional_photo_4, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            if (!$stmt) { throw new Exception("Prepare failed: " . $con->error); }
            $status = 'Pending';
            $stmt->bind_param("issssssssssssssssssssssssssssssssssssssssssssssss", $uid, $serial_number, $tire_size, $purchase_date, $purchase_location, $invoice_number, $warranty_period, $equipment_type, $equipment_type_other, $vehicle_make_model, $vehicle_year, $usage_type, $usage_type_other, $usage_pattern, $usage_pattern_other, $nature_Claim, $nature_other, $detailed_description, $mileage_hours, $duration_before_problem, $operating_conditions, $impact, $impact_other, $daily_usage, $load_capacity, $surface_conditions, $temperature_conditions, $speed_operation, $documentation, $other_documentation, $previous_actions, $resolution_requested, $resolution_other, $additional_comments, $compfile_path, $video_filename, $video_path, $video_size, $video_mime, $front_left_photo, $Thread_patternn_photo, $falk1_photo, $falk3_photo, $additional_photo_1, $additional_photo_2, $additional_photo_3, $additional_photo_4, $currentTime, $status);
            if (!$stmt->execute()) { throw new Exception("Execute failed: " . $stmt->error); }
            $complaint_id = $con->insert_id; $stmt->close();
            foreach ($tire_positions as $position => $file_id) { if ($file_id > 0 && ${$position . '_photo'}) { $stmt_photo = $con->prepare("INSERT INTO tbl_tire_photos (complaint_id, user_id, tire_position, photo_path, upload_time, created_at) VALUES (?, ?, ?, ?, ?, ?)"); $photo_path = ${$position . '_photo'}; $stmt_photo->bind_param("iissss", $complaint_id, $uid, $position, $photo_path, $currentTime, $currentTime); $stmt_photo->execute(); $stmt_photo->close(); } }
            foreach ([1 => $additional_photo_1, 2 => $additional_photo_2, 3 => $additional_photo_3, 4 => $additional_photo_4] as $num => $photo) { if ($photo) { $stmt_photo = $con->prepare("INSERT INTO tbl_tire_photos (complaint_id, user_id, tire_position, photo_path, upload_time, created_at) VALUES (?, ?, ?, ?, ?, ?)"); $pos_name = 'additional_' . $num; $stmt_photo->bind_param("iissss", $complaint_id, $uid, $pos_name, $photo, $currentTime, $currentTime); $stmt_photo->execute(); $stmt_photo->close(); } }
            $stmt_clean = $con->prepare("DELETE FROM tbl_temp_uploads WHERE user_id = ? AND session_id = ?"); $stmt_clean->bind_param("is", $uid, $session_id); $stmt_clean->execute(); $stmt_clean->close();
            $con->commit();
            echo '<script>alert("Your tire Claim has been successfully submitted. Claim ID: ' . $complaint_id . '");</script>';
            echo '<script>window.location.href = "sent_mail.php";</script>';
        } catch (Exception $e) {
            $con->rollback(); error_log("Submission error: " . $e->getMessage());
            echo '<script>alert("Error submitting complaint: ' . addslashes($e->getMessage()) . '\\n\\nPlease contact support if this persists.");</script>';
        }
    }
}

$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Tire Claim Registration — ATIRE</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@font-face{font-family:'SF UI Display';font-weight:500;font-style:normal;src:url('font/sf-ui-display-medium-58646be638f96.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:600;font-style:normal;src:url('font/sf-ui-display-semibold-58646eddcae92.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:700;font-style:normal;src:url('font/sf-ui-display-bold-58646a511e3d9.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:800;font-style:normal;src:url('font/sf-ui-display-heavy-586470160b9e5.otf') format('opentype')}
@font-face{font-family:'SF UI Display';font-weight:900;font-style:normal;src:url('font/sf-ui-display-black-58646a6b80d5a.otf') format('opentype')}

:root{
    --orange:#f28018;--orange-dk:#d06e10;--orange-lt:rgba(242,128,24,0.10);
    --orange-glow:rgba(242,128,24,0.18);--gray-50:#f9f9f9;--gray-100:#f2f2f2;
    --gray-200:#e4e4e4;--gray-300:#d0d0d0;--gray-400:#b0b0b0;--gray-500:#888888;
    --gray-700:#444444;--gray-900:#1a1a1a;--white:#ffffff;--bg:#f3f4f6;
    --font:'SF UI Display',-apple-system,BlinkMacSystemFont,sans-serif;
    --radius-xs:4px;--radius-sm:8px;--radius-md:12px;--radius-lg:16px;
    --shadow-sm:0 1px 6px rgba(0,0,0,0.06);--shadow:0 2px 14px rgba(0,0,0,0.08);
    --shadow-md:0 6px 28px rgba(0,0,0,0.12);--shadow-lg:0 12px 48px rgba(0,0,0,0.14);
    --trans:0.18s cubic-bezier(0.4,0,0.2,1);--hdr-h:56px;
    --success:#16a34a;--error:#dc2626;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--font);background:var(--bg);color:var(--gray-700);min-height:100vh;font-size:13.5px;line-height:1.5;-webkit-font-smoothing:antialiased;-webkit-tap-highlight-color:transparent}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:99px}
::-webkit-scrollbar-thumb:hover{background:var(--orange)}

/* ═══ HEADER ═══════════════════════════════════════════════════════════ */
.hdr{position:sticky;top:0;z-index:500;background:var(--white);border-bottom:2.5px solid var(--orange);box-shadow:0 2px 20px rgba(0,0,0,0.08);height:var(--hdr-h)}
.hdr-inner{max-width:1200px;margin:0 auto;padding:0 1rem;height:100%;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0}
.brand-logo{height:28px;width:auto}
.hdr-right{display:flex;align-items:center;gap:6px}
.hdr-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:var(--radius-sm);font-weight:700;font-size:11.5px;letter-spacing:.03em;text-decoration:none;border:1.5px solid var(--gray-200);background:var(--white);color:var(--gray-500);cursor:pointer;transition:var(--trans);white-space:nowrap;font-family:var(--font)}
.hdr-btn:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}
.avatar{width:32px;height:32px;border-radius:50%;background:var(--orange);color:var(--white);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:11px;box-shadow:0 2px 8px rgba(242,128,24,0.35);flex-shrink:0}

/* ═══ PAGE LAYOUT ═══════════════════════════════════════════════════════ */
.page-wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1rem 4rem}

/* ═══ HERO ══════════════════════════════════════════════════════════════ */
.page-hero{margin-bottom:1.5rem}
.page-hero-eyebrow{font-size:9px;font-weight:800;color:var(--orange);letter-spacing:.22em;text-transform:uppercase;margin-bottom:6px;display:flex;align-items:center;gap:6px}
.page-hero-eyebrow::before{content:'';width:14px;height:2px;background:var(--orange);border-radius:2px}
.page-hero-title{font-size:clamp(24px,4vw,36px);font-weight:900;color:var(--gray-900);letter-spacing:-.02em;line-height:1}
.page-hero-title span{color:var(--orange)}
.page-hero-sub{font-size:11.5px;font-weight:500;color:var(--gray-400);margin-top:5px}

/* ═══ FORM CARD ═════════════════════════════════════════════════════════ */
.form-card{background:var(--white);border-radius:var(--radius-lg);border:1.5px solid var(--gray-200);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1.2rem}

/* ═══ SECTION HEADER ════════════════════════════════════════════════════ */
.section-hdr{padding:.75rem 1.2rem;background:linear-gradient(90deg,rgba(242,128,24,0.08) 0%,rgba(242,128,24,0.02) 100%);border-bottom:1.5px solid rgba(242,128,24,0.20);display:flex;align-items:center;gap:10px}
.section-hdr-icon{width:30px;height:30px;border-radius:var(--radius-xs);background:var(--orange);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
.section-hdr-title{font-size:12px;font-weight:900;color:var(--gray-900);letter-spacing:.06em;text-transform:uppercase}
.section-hdr-sub{font-size:10.5px;font-weight:500;color:var(--gray-500);margin-top:1px}
.section-body{padding:1.2rem}

/* ═══ FORM GRID ═════════════════════════════════════════════════════════ */
.fg-row{display:grid;gap:.9rem;margin-bottom:.9rem}
.fg-row.cols-2{grid-template-columns:1fr 1fr}
.fg-row.cols-3{grid-template-columns:1fr 1fr 1fr}
.fg-row.cols-1{grid-template-columns:1fr}
.fg{display:flex;flex-direction:column;gap:4px}
.fg label{font-size:9.5px;font-weight:800;color:var(--gray-500);text-transform:uppercase;letter-spacing:.09em;display:flex;align-items:center;gap:4px}
.fg label i{color:var(--orange);font-size:8.5px}
.fg label .req{color:var(--error);font-size:10px;font-weight:900}
.fg input[type="text"],.fg input[type="date"],.fg input[type="number"],.fg select,.fg textarea{width:100%;padding:9px 11px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);font-family:var(--font);font-size:13px;font-weight:600;color:var(--gray-700);background:var(--white);transition:var(--trans);outline:none;appearance:none;-webkit-appearance:none}
.fg select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='6' viewBox='0 0 11 6'%3E%3Cpath d='M1 1l4.5 4 4.5-4' stroke='%23aaa' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;padding-right:28px}
.fg textarea{resize:vertical;min-height:90px;line-height:1.5}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--orange);box-shadow:0 0 0 3px var(--orange-glow)}
.fg input[readonly]{background:rgba(242,128,24,0.04);border-color:rgba(242,128,24,0.30);color:var(--gray-700);cursor:default}
.fg .form-hint{font-size:10px;font-weight:500;color:var(--gray-400);display:flex;align-items:center;gap:4px;margin-top:1px}
.fg .form-hint i{color:var(--orange);font-size:9px}

/* ═══ RADIO / CHECKBOX ══════════════════════════════════════════════════ */
.choice-group{display:flex;flex-wrap:wrap;gap:6px;margin-top:2px}
.choice-item{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;border:1.5px solid var(--gray-200);background:var(--white);cursor:pointer;transition:var(--trans);font-size:12px;font-weight:600;color:var(--gray-700);user-select:none;-webkit-tap-highlight-color:transparent}
.choice-item:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}
.choice-item input[type="radio"],.choice-item input[type="checkbox"]{position:absolute;opacity:0;pointer-events:none;width:0;height:0}
.choice-item.selected{background:var(--orange);border-color:var(--orange-dk);color:var(--white)}
.choice-item.selected .choice-dot{background:rgba(255,255,255,0.4);border-color:rgba(255,255,255,0.6)}
.choice-dot{width:14px;height:14px;border-radius:50%;border:1.5px solid var(--gray-300);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:var(--trans)}
.choice-check{width:14px;height:14px;border-radius:3px;border:1.5px solid var(--gray-300);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:var(--trans);font-size:8px;color:var(--white)}
.choice-item.selected .choice-check{background:rgba(255,255,255,0.25);border-color:rgba(255,255,255,0.5)}

/* ═══ FILE UPLOAD ═══════════════════════════════════════════════════════ */
.file-upload-zone{border:2px dashed var(--gray-200);border-radius:var(--radius-md);padding:1rem;text-align:center;cursor:pointer;transition:var(--trans);background:var(--gray-50);position:relative;min-height:80px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px}
.file-upload-zone:hover{border-color:var(--orange);background:var(--orange-lt)}
.file-upload-zone.has-file{border-color:var(--orange);border-style:solid;background:rgba(242,128,24,0.04)}
.file-upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.file-upload-ico{font-size:22px;color:var(--gray-300)}
.file-upload-zone.has-file .file-upload-ico{color:var(--orange)}
.file-upload-label{font-size:11px;font-weight:700;color:var(--gray-500)}
.file-upload-zone.has-file .file-upload-label{color:var(--orange)}
.file-upload-hint{font-size:9.5px;font-weight:500;color:var(--gray-400)}
.upload-progress-bar{display:none;margin-top:.75rem;background:var(--orange-lt);border:1.5px solid rgba(242,128,24,0.25);border-radius:var(--radius-sm);padding:.65rem .9rem;display:none;align-items:center;gap:8px}
.upload-progress-bar.on{display:flex}
.upload-spinner{width:16px;height:16px;border:2px solid rgba(242,128,24,0.25);border-top-color:var(--orange);border-radius:50%;animation:spin .65s linear infinite;flex-shrink:0}
@keyframes spin{to{transform:rotate(360deg)}}
.upload-progress-text{font-size:11.5px;font-weight:700;color:#7a4400}
.upload-success{display:none;margin-top:.5rem;align-items:center;gap:6px;padding:6px 10px;background:#dcfce7;border:1.5px solid #86efac;border-radius:var(--radius-sm)}
.upload-success.show{display:flex}
.upload-success i{color:var(--success);font-size:12px}
.upload-success span{font-size:11.5px;font-weight:700;color:#14532d}

/* ═══ TIRE PHOTO GRID ═══════════════════════════════════════════════════ */
.tire-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.9rem}
.tire-photo-card{background:var(--white);border:1.5px solid var(--gray-200);border-radius:var(--radius-md);overflow:hidden;transition:border-color var(--trans)}
.tire-photo-card:hover{border-color:rgba(242,128,24,0.35)}
.tire-photo-card-hdr{padding:.55rem .8rem;background:var(--gray-50);border-bottom:1px solid var(--gray-100);display:flex;align-items:center;gap:6px}
.tire-photo-card-hdr i{color:var(--orange);font-size:10px}
.tire-photo-card-hdr span{font-size:10px;font-weight:800;color:var(--gray-700);letter-spacing:.05em;text-transform:uppercase}
.tire-photo-card-body{padding:.8rem}
.example-thumb{width:100%;max-height:100px;object-fit:cover;border-radius:var(--radius-xs);border:1px solid var(--gray-200);cursor:pointer;transition:var(--trans);margin-top:.6rem;display:block}
.example-thumb:hover{border-color:var(--orange);transform:scale(1.02);box-shadow:var(--shadow)}

/* ═══ BADGES / STATUS ═══════════════════════════════════════════════════ */
.badge-success{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:#dcfce7;border:1px solid #86efac;border-radius:20px;font-size:10.5px;font-weight:700;color:#14532d}
.badge-orange{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:var(--orange-lt);border:1px solid rgba(242,128,24,0.30);border-radius:20px;font-size:10.5px;font-weight:700;color:#7a4400;cursor:pointer;transition:var(--trans)}
.badge-orange:hover{background:var(--orange);color:var(--white)}
.badge-blue{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:#dbeafe;border:1px solid #93c5fd;border-radius:20px;font-size:10.5px;font-weight:700;color:#1d4ed8}
.badge-row{display:flex;flex-wrap:wrap;gap:4px;margin-top:5px}
.serial-loading{display:none;align-items:center;gap:6px;margin-top:6px;font-size:11px;font-weight:600;color:var(--orange)}
.serial-loading.on{display:flex}
.serial-info{margin-top:5px;display:none}
.serial-info.show{display:block}

/* ═══ FORM ACTIONS ══════════════════════════════════════════════════════ */
.form-actions{display:flex;gap:10px;justify-content:center;padding:1.5rem 1.2rem;background:var(--white);border-top:1.5px solid var(--gray-100);flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:var(--radius-sm);font-family:var(--font);font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;transition:var(--trans);border:none;text-decoration:none;white-space:nowrap;min-height:44px;touch-action:manipulation}
.btn-submit{background:var(--orange);color:var(--white);box-shadow:0 3px 12px rgba(242,128,24,0.30)}
.btn-submit:hover{background:var(--orange-dk);transform:translateY(-1px)}
.btn-submit:active{transform:scale(.98)}
.btn-cancel{background:var(--white);color:var(--gray-500);border:1.5px solid var(--gray-200)}
.btn-cancel:hover{border-color:var(--orange);color:var(--orange);background:var(--orange-lt)}

/* ═══ MODAL ═════════════════════════════════════════════════════════════ */
.photo-modal{display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.90);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1rem}
.photo-modal.active{display:flex;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-inner{position:relative;max-width:88vw;max-height:88vh}
.modal-inner img{width:100%;height:auto;max-height:80vh;object-fit:contain;border-radius:var(--radius-md);box-shadow:0 20px 60px rgba(0,0,0,0.5)}
.modal-close{position:absolute;top:-40px;right:0;width:36px;height:36px;border-radius:50%;background:var(--orange);border:none;color:var(--white);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--trans)}
.modal-close:hover{background:var(--orange-dk);transform:rotate(90deg)}
.modal-caption{text-align:center;color:rgba(255,255,255,.75);font-size:12px;font-weight:600;margin-top:10px}
.modal-nav{position:absolute;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;background:rgba(242,128,24,0.80);border:none;color:var(--white);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--trans)}
.modal-nav:hover{background:var(--orange)}
.modal-prev{left:-52px}
.modal-next{right:-52px}

/* ═══ HIDDEN ════════════════════════════════════════════════════════════ */
.hidden{display:none!important}

/* ═══ DIVIDER ═══════════════════════════════════════════════════════════ */
.sb-divider{height:1px;background:var(--gray-100);margin:.75rem 0}

/* ═══ RESPONSIVE ════════════════════════════════════════════════════════ */
@media(max-width:768px){
    .fg-row.cols-2,.fg-row.cols-3{grid-template-columns:1fr}
    .tire-grid{grid-template-columns:1fr}
    .hdr-btn span{display:none}
    .modal-prev{left:-44px}
    .modal-nav{width:32px;height:32px;font-size:13px}
}
@media(max-width:480px){
    .choice-item{font-size:11.5px;padding:5px 10px}
    .page-wrap{padding:1rem .75rem 4rem}
    .section-body{padding:.9rem}
}
</style>
</head>
<body>

<!-- ═══ HEADER ═══════════════════════════════════════════════════════════ -->
<header class="hdr">
    <div class="hdr-inner">
        <a href="dashboard.php" class="brand">
            <img src="atire.png" alt="ATIRE" class="brand-logo">
        </a>
        <div class="hdr-right">
            <a href="sent_mail.php" class="hdr-btn"><i class="fas fa-arrow-left"></i><span> My Claims</span></a>
            <a href="dashboard.php" class="hdr-btn"><i class="fas fa-home"></i><span> Dashboard</span></a>
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        </div>
    </div>
</header>

<!-- ═══ PHOTO MODAL ══════════════════════════════════════════════════════ -->
<div id="photoModal" class="photo-modal">
    <div class="modal-inner">
        <button class="modal-close" id="modalClose"><i class="fas fa-times"></i></button>
        <button class="modal-nav modal-prev" id="modalPrev"><i class="fas fa-chevron-left"></i></button>
        <img id="modalImage" src="" alt="">
        <button class="modal-nav modal-next" id="modalNext"><i class="fas fa-chevron-right"></i></button>
        <div class="modal-caption" id="modalCaption"></div>
    </div>
</div>

<!-- ═══ PAGE ═════════════════════════════════════════════════════════════ -->
<div class="page-wrap">

    <div class="page-hero">
    
        <div class="page-hero-title">Tire <span>Claim</span> Registration</div>
        <div class="page-hero-sub">Complete the form below to submit your tire claim. All fields marked * are required.</div>
    </div>

    <form method="post" name="tire_complaint" enctype="multipart/form-data" id="complaintForm">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="document_file_id"            id="document_file_id"        value="0">
        <input type="hidden" name="video_file_id"               id="video_file_id"           value="0">
        <input type="hidden" name="front_left_file_id"          id="front_left_file_id"      value="0">
        <input type="hidden" name="Thread_patternn_file_id"     id="Thread_patternn_file_id" value="0">
        <input type="hidden" name="falk1_file_id"               id="falk1_file_id"           value="0">
        <input type="hidden" name="falk3_file_id"               id="falk3_file_id"           value="0">
        <input type="hidden" name="additional_photo_1_file_id"  id="additional_1_file_id"    value="0">
        <input type="hidden" name="additional_photo_2_file_id"  id="additional_2_file_id"    value="0">
        <input type="hidden" name="additional_photo_3_file_id"  id="additional_3_file_id"    value="0">
        <input type="hidden" name="additional_photo_4_file_id"  id="additional_4_file_id"    value="0">

        <!-- ── PRODUCT DETAILS ─────────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-barcode"></i></div>
                <div>
                    <div class="section-hdr-title">Product Details</div>
                    <div class="section-hdr-sub">Tire identification and purchase information</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-barcode"></i> Serial Number <span class="req">*</span></label>
                        <input type="text" name="serial_number" id="serial_number" placeholder="Enter tire serial number" required>
                        <div class="form-hint"><i class="fas fa-info-circle"></i> Found printed on the tire sidewall</div>
                        <div class="serial-loading" id="serial_loading">
                            <div class="upload-spinner"></div> Looking up tire information…
                        </div>
                        <div class="serial-info" id="serial_info"></div>
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-ruler"></i> Tire Size (Description) <span class="req">*</span></label>
                        <input type="text" name="tire_size" id="tire_size" placeholder="Auto-filled from serial number" readonly required>
                        <div class="form-hint"><i class="fas fa-magic"></i> Automatically populated</div>
                    </div>
                </div>
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-calendar-alt"></i> Purchase Date <span class="req">*</span></label>
                        <input type="date" name="purchase_date" id="purchase_date" required>
                        <div id="dispatch_date_container" style="display:none;margin-top:5px;">
                            <span class="badge-blue"><i class="fas fa-truck"></i> Dispatch: <span id="dispatch_date_text"></span></span>
                        </div>
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-building"></i> Dealer Company</label>
                        <input type="text" name="purchase_location" id="purchase_location" value="<?php echo htmlspecialchars($dealerCompanyName); ?>" readonly>
                        <div class="form-hint"><i class="fas fa-lock"></i> Auto-filled from your account</div>
                    </div>
                </div>
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-industry"></i> End User Company</label>
                        <input type="text" name="warranty_period" placeholder="Your company name">
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-file-invoice"></i> Invoice / Receipt Number</label>
                        <input type="text" name="invoice_number" id="invoice_number" placeholder="Invoice or receipt number">
                        <div class="badge-row" id="invoice_container"></div>
                        <div class="form-hint hidden" id="invoice_help"><i class="fas fa-mouse-pointer"></i> Click an invoice badge above to auto-fill</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── VEHICLE INFORMATION ─────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-truck"></i></div>
                <div>
                    <div class="section-hdr-title">Vehicle Information</div>
                    <div class="section-hdr-sub">Equipment and operating context</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-cogs"></i> Equipment Type <span class="req">*</span></label>
                        <div class="choice-group" id="equipment_group">
                            <?php foreach (['Forklift','Scissor Lift','Other'] as $i => $eq): ?>
                            <label class="choice-item" id="eq_lbl_<?php echo $i; ?>">
                                <input type="radio" name="equipment_type" value="<?php echo htmlspecialchars($eq); ?>" required>
                                <span class="choice-dot"></span><?php echo htmlspecialchars($eq); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="equipment_type_other" id="equipment_type_other" class="hidden" placeholder="Specify equipment type" style="margin-top:8px;">
                    </div>
                </div>
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-car"></i> Vehicle Make / Model <span class="req">*</span></label>
                        <input type="text" name="vehicle_make_model" placeholder="e.g., Toyota 8FGCU25" required>
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-calendar"></i> Year of Vehicle</label>
                        <input type="number" name="vehicle_year" min="1900" max="2030" placeholder="e.g., 2020">
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-map-marker-alt"></i> Usage Type <span class="req">*</span></label>
                        <div class="choice-group" id="usage_type_group">
                            <?php foreach (['Ware house/Logistics','Port Terminal','Ground Support equipment(GSE)','Construction Site','Outdoor Yard','Manufacturing Factory','Other'] as $i => $ut): ?>
                            <label class="choice-item" id="ut_lbl_<?php echo $i; ?>">
                                <input type="radio" name="usage_type" value="<?php echo htmlspecialchars($ut); ?>" required>
                                <span class="choice-dot"></span><?php echo htmlspecialchars($ut); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="usage_type_other" id="usage_type_other" class="hidden" placeholder="Specify usage type" style="margin-top:8px;">
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-route"></i> Usage Pattern <span class="req">*</span></label>
                        <div class="choice-group" id="usage_pattern_group">
                            <?php foreach (['Continuous Operation','Frequent Turning','Long Straight Runs','Frequent Stops/Starts','Other'] as $i => $up): ?>
                            <label class="choice-item" id="up_lbl_<?php echo $i; ?>">
                                <input type="checkbox" name="usage_pattern[]" value="<?php echo htmlspecialchars($up); ?>">
                                <span class="choice-check"></span><?php echo htmlspecialchars($up); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="usage_pattern_other" id="usage_pattern_other" class="hidden" placeholder="Specify other usage pattern" style="margin-top:8px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TIRE POSITION PHOTOS ────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-camera"></i></div>
                <div>
                    <div class="section-hdr-title">Tire Position Photos</div>
                    <div class="section-hdr-sub">JPG, PNG, GIF — max 1 GB each. Uploaded immediately on selection. Click examples to enlarge.</div>
                </div>
            </div>
            <div class="section-body">
                <div class="tire-grid">
                    <?php
                    $tire_positions = [
                        'front_left'       => ['label'=>'Tire Front',                'icon'=>'fa-image'],
                        'Thread_patternn'  => ['label'=>'Tire Tread Patterns',        'icon'=>'fa-grip-lines'],
                        'falk1'            => ['label'=>'Tire Mounted on Vehicle',    'icon'=>'fa-truck'],
                        'falk3'            => ['label'=>'Highlight Serial Number',    'icon'=>'fa-barcode'],
                    ];
                    foreach ($tire_positions as $key => $info):
                    ?>
                    <div class="tire-photo-card">
                        <div class="tire-photo-card-hdr">
                            <i class="fas <?php echo $info['icon']; ?>"></i>
                            <span><?php echo htmlspecialchars($info['label']); ?></span>
                        </div>
                        <div class="tire-photo-card-body">
                            <div class="file-upload-zone" id="zone_<?php echo $key; ?>">
                                <input type="file" class="tire-photo" accept="image/jpeg,image/jpg,image/png,image/gif" data-position="<?php echo $key; ?>" id="<?php echo $key; ?>_photo">
                                <i class="fas fa-cloud-upload-alt file-upload-ico"></i>
                                <div class="file-upload-label">Click or drag to upload</div>
                                <div class="file-upload-hint">JPG, PNG, GIF · Max 1 GB</div>
                            </div>
                            <div class="upload-progress-bar" id="<?php echo $key; ?>_progress">
                                <div class="upload-spinner"></div>
                                <span class="upload-progress-text">Uploading <?php echo htmlspecialchars($info['label']); ?>…</span>
                            </div>
                            <div class="upload-success" id="<?php echo $key; ?>_success">
                                <i class="fas fa-check-circle"></i>
                                <span>Uploaded successfully</span>
                            </div>
                            <img src="assets/examples/<?php echo $key; ?>.jpg" alt="Example <?php echo htmlspecialchars($info['label']); ?>" class="example-thumb" data-caption="Example: <?php echo htmlspecialchars($info['label']); ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ── ADDITIONAL PHOTOS ───────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-images"></i></div>
                <div>
                    <div class="section-hdr-title">Additional Photos <span style="font-weight:500;font-size:10px;color:rgba(255,255,255,.6);text-transform:none;letter-spacing:0;">(Optional)</span></div>
                    <div class="section-hdr-sub">Up to 4 supplementary photos — JPG, PNG, GIF · max 1 GB each</div>
                </div>
            </div>
            <div class="section-body">
                <div class="tire-grid">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="tire-photo-card">
                        <div class="tire-photo-card-hdr">
                            <i class="fas fa-image"></i>
                            <span>Additional Photo <?php echo $i; ?></span>
                        </div>
                        <div class="tire-photo-card-body">
                            <div class="file-upload-zone" id="zone_additional_<?php echo $i; ?>">
                                <input type="file" class="additional-photo" accept="image/jpeg,image/jpg,image/png,image/gif" data-position="additional_<?php echo $i; ?>" id="additional_photo_<?php echo $i; ?>">
                                <i class="fas fa-cloud-upload-alt file-upload-ico"></i>
                                <div class="file-upload-label">Click or drag to upload</div>
                                <div class="file-upload-hint">JPG, PNG, GIF · Max 1 GB</div>
                            </div>
                            <div class="upload-progress-bar" id="additional_<?php echo $i; ?>_progress">
                                <div class="upload-spinner"></div>
                                <span class="upload-progress-text">Uploading Additional Photo <?php echo $i; ?>…</span>
                            </div>
                            <div class="upload-success" id="additional_<?php echo $i; ?>_success">
                                <i class="fas fa-check-circle"></i>
                                <span>Uploaded successfully</span>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- ── VIDEO UPLOAD ────────────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-video"></i></div>
                <div>
                    <div class="section-hdr-title">Upload Video <span style="font-weight:500;font-size:10px;color:rgba(255,255,255,.6);text-transform:none;letter-spacing:0;">(Optional)</span></div>
                    <div class="section-hdr-sub">MP4, MOV, AVI, MKV — max 500 MB</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-film"></i> Video File</label>
                        <div class="file-upload-zone" id="zone_video">
                            <input type="file" id="complaint_video" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska">
                            <i class="fas fa-play-circle file-upload-ico"></i>
                            <div class="file-upload-label">Click or drag to upload video</div>
                            <div class="file-upload-hint">MP4, MOV, AVI, MKV · Max 500 MB</div>
                        </div>
                        <div class="upload-progress-bar" id="video_progress">
                            <div class="upload-spinner"></div>
                            <span class="upload-progress-text">Uploading video… This may take a few minutes.</span>
                        </div>
                        <div class="upload-success" id="video_success">
                            <i class="fas fa-check-circle"></i>
                            <span>Video uploaded successfully</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── CLAIM DETAILS ───────────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="section-hdr-title">Claim Details</div>
                    <div class="section-hdr-sub">Describe the issue and its nature</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-list-alt"></i> Nature of Claim <span class="req">*</span></label>
                        <div class="choice-group" id="nature_group">
                            <?php foreach (['Premature Wear','Structural Defect','Manufacturing Defect','Performance Issue','Safety Concern','Other'] as $i => $nc): ?>
                            <label class="choice-item" id="nc_lbl_<?php echo $i; ?>">
                                <input type="radio" name="nature_complaint" value="<?php echo htmlspecialchars($nc); ?>" required>
                                <span class="choice-dot"></span><?php echo htmlspecialchars($nc); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="nature_other" id="nature_other" class="hidden" placeholder="Specify other nature of complaint" style="margin-top:8px;">
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-align-left"></i> Detailed Description <span class="req">*</span></label>
                        <textarea name="detailed_description" rows="5" placeholder="Describe the problem in detail — when and how you noticed it" required></textarea>
                    </div>
                </div>
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-tachometer-alt"></i> Mileage / Hours at Time of Issue</label>
                        <input type="text" name="mileage_hours" placeholder="e.g., 5000 km or 500 hours">
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-clock"></i> Duration Before Problem Occurred</label>
                        <input type="text" name="duration_before_problem" placeholder="e.g., 6 months, 1 year">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── IMPACT ASSESSMENT ───────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div class="section-hdr-title">Impact Assessment</div>
                    <div class="section-hdr-sub">How this affected your operations</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-exclamation-circle"></i> Impact on Operations</label>
                        <div class="choice-group" id="impact_group">
                            <?php foreach (['Severe - Operations Halted','Moderate - Reduced Efficiency','Minor - Minimal Impact','Other'] as $i => $im): ?>
                            <label class="choice-item" id="im_lbl_<?php echo $i; ?>">
                                <input type="radio" name="impact" value="<?php echo htmlspecialchars($im); ?>">
                                <span class="choice-dot"></span><?php echo htmlspecialchars($im); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="impact_other" id="impact_other" class="hidden" placeholder="Specify other impact" style="margin-top:8px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── OPERATING CONDITIONS ───────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-thermometer-half"></i></div>
                <div>
                    <div class="section-hdr-title">Operating Conditions</div>
                    <div class="section-hdr-sub">Environment and load details during operation</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-hourglass-half"></i> Daily Usage (hours/day)</label>
                        <input type="number" name="daily_usage" min="0" step="0.1" placeholder="e.g., 8">
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-weight"></i> Load Capacity Usually Carried</label>
                        <input type="text" name="load_capacity" placeholder="e.g., 2000 kg">
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-road"></i> Surface Conditions</label>
                        <div class="choice-group" id="surface_group">
                            <?php foreach (['Smooth Concrete','Rough Concrete','Asphalt','Gravel','Uneven Terrain','Indoor Only','Outdoor Only','Mixed'] as $i => $sc): ?>
                            <label class="choice-item" id="sc_lbl_<?php echo $i; ?>">
                                <input type="checkbox" name="surface_conditions[]" value="<?php echo htmlspecialchars($sc); ?>">
                                <span class="choice-check"></span><?php echo htmlspecialchars($sc); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="fg-row cols-2">
                    <div class="fg">
                        <label><i class="fas fa-temperature-low"></i> Temperature Conditions</label>
                        <input type="text" name="temperature_conditions" placeholder="e.g., -10°C to 40°C">
                    </div>
                    <div class="fg">
                        <label><i class="fas fa-tachometer-alt"></i> Speed of Operation</label>
                        <input type="text" name="speed_operation" placeholder="e.g., up to 20 km/h">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── DOCUMENTATION ──────────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <div class="section-hdr-title">Documentation</div>
                    <div class="section-hdr-sub">Supporting documents and available evidence</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-check-square"></i> Available Documentation</label>
                        <div class="choice-group" id="doc_group">
                            <?php foreach (['Purchase Invoice','Maintenance Records','Photos','Videos','Other'] as $i => $dt): ?>
                            <label class="choice-item" id="dt_lbl_<?php echo $i; ?>">
                                <input type="checkbox" name="documentation[]" value="<?php echo htmlspecialchars($dt); ?>">
                                <span class="choice-check"></span><?php echo htmlspecialchars($dt); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="other_documentation" id="other_documentation" class="hidden" placeholder="Specify other documentation" style="margin-top:8px;">
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-paperclip"></i> Upload Supporting Documents</label>
                        <div class="file-upload-zone" id="zone_document">
                            <input type="file" id="complaint_file" accept=".pdf,.doc,.docx,.zip">
                            <i class="fas fa-file-upload file-upload-ico"></i>
                            <div class="file-upload-label">Click or drag to upload document</div>
                            <div class="file-upload-hint">PDF, DOC, DOCX, ZIP · Max 1 GB</div>
                        </div>
                        <div class="upload-progress-bar" id="document_progress">
                            <div class="upload-spinner"></div>
                            <span class="upload-progress-text">Uploading document…</span>
                        </div>
                        <div class="upload-success" id="document_success">
                            <i class="fas fa-check-circle"></i>
                            <span>Document uploaded successfully</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── RESOLUTION ─────────────────────────────────────────────── -->
        <div class="form-card">
            <div class="section-hdr">
                <div class="section-hdr-icon"><i class="fas fa-handshake"></i></div>
                <div>
                    <div class="section-hdr-title">Resolution</div>
                    <div class="section-hdr-sub">Previous actions taken and desired outcome</div>
                </div>
            </div>
            <div class="section-body">
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-history"></i> Previous Actions Taken</label>
                        <textarea name="previous_actions" rows="3" placeholder="Describe any actions you've already taken to address the issue"></textarea>
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-bullseye"></i> Resolution Requested</label>
                        <div class="choice-group" id="resolution_group">
                            <?php foreach (['Replacement','Repair','Refund','Technical Support','Other'] as $i => $rs): ?>
                            <label class="choice-item" id="rs_lbl_<?php echo $i; ?>">
                                <input type="radio" name="resolution_requested" value="<?php echo htmlspecialchars($rs); ?>">
                                <span class="choice-dot"></span><?php echo htmlspecialchars($rs); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" name="resolution_other" id="resolution_other" class="hidden" placeholder="Specify other resolution" style="margin-top:8px;">
                    </div>
                </div>
                <div class="fg-row cols-1">
                    <div class="fg">
                        <label><i class="fas fa-comment-alt"></i> Additional Comments</label>
                        <textarea name="additional_comments" rows="4" placeholder="Any additional information or comments"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Claim
                </button>
                <a href="sent_mail.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>

    </form>
</div><!-- /page-wrap -->

<script>
/* ── Modal ─────────────────────────────────────────────────────────── */
const modal = document.getElementById('photoModal');
const modalImg = document.getElementById('modalImage');
const modalCaption = document.getElementById('modalCaption');
let currentImages = [], currentIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    const thumbs = document.querySelectorAll('.example-thumb');
    thumbs.forEach((img, idx) => {
        currentImages.push({ src: img.src, caption: img.getAttribute('data-caption') });
        img.addEventListener('click', () => { currentIndex = idx; showModal(); });
    });
});
function showModal() { modal.classList.add('active'); modalImg.src = currentImages[currentIndex].src; modalCaption.textContent = currentImages[currentIndex].caption; document.body.style.overflow = 'hidden'; }
function hideModal() { modal.classList.remove('active'); document.body.style.overflow = ''; }
document.getElementById('modalClose').addEventListener('click', hideModal);
modal.addEventListener('click', e => { if (e.target === modal) hideModal(); });
document.getElementById('modalPrev').addEventListener('click', e => { e.stopPropagation(); currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length; modalImg.src = currentImages[currentIndex].src; modalCaption.textContent = currentImages[currentIndex].caption; });
document.getElementById('modalNext').addEventListener('click', e => { e.stopPropagation(); currentIndex = (currentIndex + 1) % currentImages.length; modalImg.src = currentImages[currentIndex].src; modalCaption.textContent = currentImages[currentIndex].caption; });
document.addEventListener('keydown', e => { if (!modal.classList.contains('active')) return; if (e.key === 'Escape') hideModal(); if (e.key === 'ArrowLeft') document.getElementById('modalPrev').click(); if (e.key === 'ArrowRight') document.getElementById('modalNext').click(); });

/* ── Custom choice items (radio/checkbox styled as pills) ─────────── */
function initChoiceGroup(groupId, type) {
    const group = document.getElementById(groupId);
    if (!group) return;
    group.querySelectorAll('.choice-item').forEach(item => {
        const inp = item.querySelector('input');
        if (!inp) return;
        function refresh() {
            if (type === 'radio') {
                group.querySelectorAll('.choice-item').forEach(ci => ci.classList.remove('selected'));
            }
            if (inp.checked) item.classList.add('selected');
            else item.classList.remove('selected');
        }
        inp.addEventListener('change', refresh);
        item.addEventListener('click', () => {
            if (type === 'checkbox') { inp.checked = !inp.checked; }
            else { inp.checked = true; }
            inp.dispatchEvent(new Event('change', { bubbles: true }));
        });
        inp.addEventListener('click', e => e.stopPropagation());
    });
}
['equipment_group','usage_type_group','nature_group','impact_group','resolution_group'].forEach(id => initChoiceGroup(id, 'radio'));
['usage_pattern_group','surface_group','doc_group'].forEach(id => initChoiceGroup(id, 'checkbox'));

/* ── Show/hide "Other" fields ─────────────────────────────────────── */
function watchOther(groupId, triggerVal, targetId) {
    const group = document.getElementById(groupId);
    const target = document.getElementById(targetId);
    if (!group || !target) return;
    group.addEventListener('change', () => {
        const inputs = group.querySelectorAll('input:checked');
        const hasOther = Array.from(inputs).some(i => i.value === triggerVal);
        target.classList.toggle('hidden', !hasOther);
    });
}
watchOther('equipment_group', 'Other', 'equipment_type_other');
watchOther('usage_type_group', 'Other', 'usage_type_other');
watchOther('usage_pattern_group', 'Other', 'usage_pattern_other');
watchOther('nature_group', 'Other', 'nature_other');
watchOther('impact_group', 'Other', 'impact_other');
watchOther('doc_group', 'Other', 'other_documentation');
watchOther('resolution_group', 'Other', 'resolution_other');

/* ── Serial number lookup ─────────────────────────────────────────── */
let serialTimer;
document.getElementById('serial_number').addEventListener('input', function() {
    clearTimeout(serialTimer);
    const val = this.value.trim();
    const loadEl = document.getElementById('serial_loading');
    const infoEl = document.getElementById('serial_info');
    if (val.length < 3) {
        loadEl.classList.remove('on'); infoEl.classList.remove('show'); infoEl.innerHTML = '';
        document.getElementById('tire_size').value = '';
        document.getElementById('invoice_container').innerHTML = '';
        document.getElementById('invoice_help').classList.add('hidden');
        document.getElementById('dispatch_date_container').style.display = 'none';
        document.getElementById('purchase_date').value = '';
        return;
    }
    serialTimer = setTimeout(() => {
        loadEl.classList.add('on'); infoEl.classList.remove('show');
        const fd = new FormData();
        fd.append('action', 'get_tire_info'); fd.append('serial_number', val);
        fetch('', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                loadEl.classList.remove('on');
                if (data.success) {
                    document.getElementById('tire_size').value = data.description;
                    infoEl.innerHTML = '<span class="badge-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
                    infoEl.classList.add('show');
                    const invCont = document.getElementById('invoice_container');
                    invCont.innerHTML = '';
                    if (data.invoice_numbers && data.invoice_numbers.length > 0) {
                        data.invoice_numbers.forEach(inv => {
                            const b = document.createElement('span');
                            b.className = 'badge-orange';
                            b.innerHTML = '<i class="fas fa-file-invoice"></i> ' + inv;
                            b.onclick = () => { document.getElementById('invoice_number').value = inv; };
                            invCont.appendChild(b);
                        });
                        document.getElementById('invoice_help').classList.remove('hidden');
                    }
                    if (data.dispatch_date) {
                        document.getElementById('dispatch_date_text').textContent = data.dispatch_date;
                        document.getElementById('dispatch_date_container').style.display = 'block';
                        document.getElementById('purchase_date').value = data.dispatch_date;
                    }
                } else {
                    document.getElementById('tire_size').value = '';
                    infoEl.innerHTML = '<span style="color:var(--error);font-size:11px;font-weight:700;"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
                    infoEl.classList.add('show');
                    document.getElementById('invoice_container').innerHTML = '';
                    document.getElementById('invoice_help').classList.add('hidden');
                    document.getElementById('dispatch_date_container').style.display = 'none';
                    document.getElementById('purchase_date').value = '';
                }
            })
            .catch(() => { loadEl.classList.remove('on'); });
    }, 500);
});

/* ── File upload helper ───────────────────────────────────────────── */
function uploadFile(file, fileType, position = '') {
    return new Promise((res, rej) => {
        const fd = new FormData();
        fd.append('action', 'upload_file'); fd.append('file', file);
        fd.append('file_type', fileType); fd.append('tire_position', position);
        fetch('', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { if (d.success) res(d); else rej(new Error(d.message)); })
            .catch(rej);
    });
}

function showProgress(id) { const el = document.getElementById(id + '_progress'); if (el) el.classList.add('on'); }
function hideProgress(id) { const el = document.getElementById(id + '_progress'); if (el) el.classList.remove('on'); }
function showSuccess(id) { const el = document.getElementById(id + '_success'); if (el) el.classList.add('show'); }
function setZoneUploaded(zoneId) { const z = document.getElementById('zone_' + zoneId); if (z) { z.classList.add('has-file'); z.querySelector('.file-upload-label').textContent = 'File ready'; } }

/* ── Document upload ─────────────────────────────────────────────── */
document.getElementById('complaint_file').addEventListener('change', function() {
    if (!this.files.length) return;
    showProgress('document'); setZoneUploaded('document');
    uploadFile(this.files[0], 'document')
        .then(d => { hideProgress('document'); document.getElementById('document_file_id').value = d.file_id; showSuccess('document'); })
        .catch(err => { hideProgress('document'); alert('Error uploading document: ' + err.message); this.value = ''; document.getElementById('zone_document').classList.remove('has-file'); });
});

/* ── Video upload ────────────────────────────────────────────────── */
document.getElementById('complaint_video').addEventListener('change', function() {
    if (!this.files.length) return;
    showProgress('video'); setZoneUploaded('video');
    uploadFile(this.files[0], 'video')
        .then(d => { hideProgress('video'); document.getElementById('video_file_id').value = d.file_id; showSuccess('video'); })
        .catch(err => { hideProgress('video'); alert('Error uploading video: ' + err.message); this.value = ''; document.getElementById('zone_video').classList.remove('has-file'); });
});

/* ── Tire photo uploads ───────────────────────────────────────────── */
document.querySelectorAll('.tire-photo').forEach(inp => {
    inp.addEventListener('change', function() {
        if (!this.files.length) return;
        const pos = this.getAttribute('data-position');
        showProgress(pos); setZoneUploaded(pos);
        uploadFile(this.files[0], 'tire_photo', pos)
            .then(d => { hideProgress(pos); document.getElementById(pos + '_file_id').value = d.file_id; showSuccess(pos); })
            .catch(err => { hideProgress(pos); alert('Error uploading photo: ' + err.message); this.value = ''; document.getElementById('zone_' + pos).classList.remove('has-file'); });
    });
});

/* ── Additional photo uploads ────────────────────────────────────── */
document.querySelectorAll('.additional-photo').forEach(inp => {
    inp.addEventListener('change', function() {
        if (!this.files.length) return;
        const pos = this.getAttribute('data-position');
        showProgress(pos); setZoneUploaded(pos);
        uploadFile(this.files[0], 'additional_photo', pos)
            .then(d => {
                hideProgress(pos);
                const num = pos.split('_')[1];
                document.getElementById('additional_' + num + '_file_id').value = d.file_id;
                showSuccess(pos);
            })
            .catch(err => { hideProgress(pos); alert('Error uploading photo: ' + err.message); this.value = ''; document.getElementById('zone_' + pos).classList.remove('has-file'); });
    });
});

/* ── Form validation ─────────────────────────────────────────────── */
document.getElementById('complaintForm').addEventListener('submit', function(e) {
    const usagePattern = document.querySelectorAll('input[name="usage_pattern[]"]:checked');
    if (usagePattern.length === 0) { e.preventDefault(); alert('Please select at least one Usage Pattern.'); }
});
</script>
</body>
</html>