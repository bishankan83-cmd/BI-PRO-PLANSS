<?php
// update_profile.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// Include database configuration
require_once('include/config.php');

$user_id = $_SESSION['id'];
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle signature upload
    $signature_path = null;
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $file_type = $_FILES['signature']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/signatures/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
            $new_filename = 'signature_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['signature']['tmp_name'], $upload_path)) {
                $signature_path = $upload_path;
                
                // Delete old signature if exists
                if (!empty($user['signature']) && file_exists($user['signature'])) {
                    unlink($user['signature']);
                }
            } else {
                $error = "Failed to upload signature image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }
    
    // Prepare update query
    if ($signature_path) {
        $sql = "UPDATE users SET 
                    fullName = ?,
                    company_rn = ?,
                    Country = ?,
                    registerd_Address = ?,
                    userEmail = ?,
                    delivery_address = ?,
                    owner1_name = ?,
                    owner1_email1 = ?,
                    owner1_email2 = ?,
                    owner1_contact1 = ?,
                    owner1_contact2 = ?,
                    owner1_contact3 = ?,
                    owner2_name = ?,
                    owner2_email1 = ?,
                    owner2_email2 = ?,
                    owner2_contact1 = ?,
                    owner2_contact2 = ?,
                    owner2_contact3 = ?,
                    contact_person_name = ?,
                    contact_person_email1 = ?,
                    contact_person_email2 = ?,
                    contact_person_contact1 = ?,
                    contact_person_contact2 = ?,
                    contact_person_contact3 = ?,
                    bank_details = ?,
                    signature = ?,
                    payment_term = ?,
                    inco_term_delivery = ?,
                    updationDate = NOW()
                    WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssssssssi",
                $_POST['fullName'],
                $_POST['company_rn'],
                $_POST['Country'],
                $_POST['registerd_Address'],
                $_POST['userEmail'],
                $_POST['delivery_address'],
                $_POST['owner1_name'],
                $_POST['owner1_email1'],
                $_POST['owner1_email2'],
                $_POST['owner1_contact1'],
                $_POST['owner1_contact2'],
                $_POST['owner1_contact3'],
                $_POST['owner2_name'],
                $_POST['owner2_email1'],
                $_POST['owner2_email2'],
                $_POST['owner2_contact1'],
                $_POST['owner2_contact2'],
                $_POST['owner2_contact3'],
                $_POST['contact_person_name'],
                $_POST['contact_person_email1'],
                $_POST['contact_person_email2'],
                $_POST['contact_person_contact1'],
                $_POST['contact_person_contact2'],
                $_POST['contact_person_contact3'],
                $_POST['bank_details'],
                $signature_path,
                $_POST['payment_term'],
                $_POST['inco_term_delivery'],
                $user_id
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Profile updated successfully!";
                // Refresh user data
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $error = "Error updating profile: " . mysqli_error($con);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparing statement: " . mysqli_error($con);
        }
    } else {
        // Update without signature
        $sql = "UPDATE users SET 
                    fullName = ?,
                    company_rn = ?,
                    Country = ?,
                    registerd_Address = ?,
                    userEmail = ?,
                    delivery_address = ?,
                    owner1_name = ?,
                    owner1_email1 = ?,
                    owner1_email2 = ?,
                    owner1_contact1 = ?,
                    owner1_contact2 = ?,
                    owner1_contact3 = ?,
                    owner2_name = ?,
                    owner2_email1 = ?,
                    owner2_email2 = ?,
                    owner2_contact1 = ?,
                    owner2_contact2 = ?,
                    owner2_contact3 = ?,
                    contact_person_name = ?,
                    contact_person_email1 = ?,
                    contact_person_email2 = ?,
                    contact_person_contact1 = ?,
                    contact_person_contact2 = ?,
                    contact_person_contact3 = ?,
                    bank_details = ?,
                    payment_term = ?,
                    inco_term_delivery = ?,
                    updationDate = NOW()
                    WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssssssssi",
                $_POST['fullName'],
                $_POST['company_rn'],
                $_POST['Country'],
                $_POST['registerd_Address'],
                $_POST['userEmail'],
                $_POST['delivery_address'],
                $_POST['owner1_name'],
                $_POST['owner1_email1'],
                $_POST['owner1_email2'],
                $_POST['owner1_contact1'],
                $_POST['owner1_contact2'],
                $_POST['owner1_contact3'],
                $_POST['owner2_name'],
                $_POST['owner2_email1'],
                $_POST['owner2_email2'],
                $_POST['owner2_contact1'],
                $_POST['owner2_contact2'],
                $_POST['owner2_contact3'],
                $_POST['contact_person_name'],
                $_POST['contact_person_email1'],
                $_POST['contact_person_email2'],
                $_POST['contact_person_contact1'],
                $_POST['contact_person_contact2'],
                $_POST['contact_person_contact3'],
                $_POST['bank_details'],
                $_POST['payment_term'],
                $_POST['inco_term_delivery'],
                $user_id
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Profile updated successfully!";
            } else {
                $error = "Error updating profile: " . mysqli_error($con);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparing statement: " . mysqli_error($con);
        }
    }
}

// Handle signature deletion
if (isset($_GET['delete_signature']) && $_GET['delete_signature'] == 1) {
    $query = "SELECT signature FROM users WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_data = mysqli_fetch_assoc($result);
        
        if (!empty($user_data['signature']) && file_exists($user_data['signature'])) {
            unlink($user_data['signature']);
        }
        
        $update_sql = "UPDATE users SET signature = NULL WHERE id = ?";
        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $user_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
        mysqli_stmt_close($stmt);
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($con, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) {
        die("User not found");
    }
    
    mysqli_stmt_close($stmt);
} else {
    die("Error fetching user data: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-section { margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px; }
        .form-section h2 { color: #495057; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #495057; font-weight: 500; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; }
        input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px; background: white; }
        textarea { resize: vertical; min-height: 80px; font-family: Arial, sans-serif; }
        .btn { background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; margin-left: 10px; }
        .btn-danger:hover { background: #c82333; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 768px) { .row { grid-template-columns: 1fr; } }
        
        /* Signature Preview Styles */
        .signature-preview { 
            margin-top: 15px; 
            padding: 15px; 
            background: white; 
            border: 2px dashed #007bff; 
            border-radius: 4px; 
            text-align: center;
        }
        .signature-preview img { 
            max-width: 300px; 
            max-height: 150px; 
            border: 1px solid #ddd; 
            padding: 5px; 
            background: white;
            display: inline-block;
        }
        .signature-preview p { 
            margin-top: 10px; 
            color: #666; 
            font-size: 14px;
        }
        .signature-actions {
            margin-top: 10px;
        }
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .file-upload-info {
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }
        #preview-img {
            display: none;
            margin-top: 15px;
            max-width: 300px;
            max-height: 150px;
            border: 2px solid #007bff;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Profile</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="form-section">
                <h2>Basic Information</h2>
                <div class="form-group">
                    <label for="fullName">Full Name / Company Name</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($user['fullName'] ?? ''); ?>">
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="company_rn">Company Registration Number</label>
                        <input type="text" id="company_rn" name="company_rn" value="<?php echo htmlspecialchars($user['company_rn'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="Country">Country</label>
                        <input type="text" id="Country" name="Country" value="<?php echo htmlspecialchars($user['Country'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="registerd_Address">Registered Address</label>
                    <input type="text" id="registerd_Address" name="registerd_Address" value="<?php echo htmlspecialchars($user['registerd_Address'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input type="email" id="userEmail" name="userEmail" value="<?php echo htmlspecialchars($user['userEmail'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="delivery_address">Delivery Address</label>
                    <input type="text" id="delivery_address" name="delivery_address" value="<?php echo htmlspecialchars($user['delivery_address'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Owner 1 Information -->
            <div class="form-section">
                <h2>Owner 1 Information</h2>
                <div class="form-group">
                    <label for="owner1_name">Name</label>
                    <input type="text" id="owner1_name" name="owner1_name" value="<?php echo htmlspecialchars($user['owner1_name'] ?? ''); ?>">
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="owner1_email1">Email 1</label>
                        <input type="email" id="owner1_email1" name="owner1_email1" value="<?php echo htmlspecialchars($user['owner1_email1'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="owner1_email2">Email 2</label>
                        <input type="email" id="owner1_email2" name="owner1_email2" value="<?php echo htmlspecialchars($user['owner1_email2'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="owner1_contact1">Contact 1</label>
                        <input type="text" id="owner1_contact1" name="owner1_contact1" value="<?php echo htmlspecialchars($user['owner1_contact1'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="owner1_contact2">Contact 2</label>
                        <input type="text" id="owner1_contact2" name="owner1_contact2" value="<?php echo htmlspecialchars($user['owner1_contact2'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="owner1_contact3">Contact 3</label>
                    <input type="text" id="owner1_contact3" name="owner1_contact3" value="<?php echo htmlspecialchars($user['owner1_contact3'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Owner 2 Information -->
            <div class="form-section">
                <h2>Owner 2 Information</h2>
                <div class="form-group">
                    <label for="owner2_name">Name</label>
                    <input type="text" id="owner2_name" name="owner2_name" value="<?php echo htmlspecialchars($user['owner2_name'] ?? ''); ?>">
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="owner2_email1">Email 1</label>
                        <input type="email" id="owner2_email1" name="owner2_email1" value="<?php echo htmlspecialchars($user['owner2_email1'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="owner2_email2">Email 2</label>
                        <input type="email" id="owner2_email2" name="owner2_email2" value="<?php echo htmlspecialchars($user['owner2_email2'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="owner2_contact1">Contact 1</label>
                        <input type="text" id="owner2_contact1" name="owner2_contact1" value="<?php echo htmlspecialchars($user['owner2_contact1'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="owner2_contact2">Contact 2</label>
                        <input type="text" id="owner2_contact2" name="owner2_contact2" value="<?php echo htmlspecialchars($user['owner2_contact2'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="owner2_contact3">Contact 3</label>
                    <input type="text" id="owner2_contact3" name="owner2_contact3" value="<?php echo htmlspecialchars($user['owner2_contact3'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Contact Person Information -->
            <div class="form-section">
                <h2>Contact Person Information</h2>
                <div class="form-group">
                    <label for="contact_person_name">Name</label>
                    <input type="text" id="contact_person_name" name="contact_person_name" value="<?php echo htmlspecialchars($user['contact_person_name'] ?? ''); ?>">
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="contact_person_email1">Email 1</label>
                        <input type="email" id="contact_person_email1" name="contact_person_email1" value="<?php echo htmlspecialchars($user['contact_person_email1'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person_email2">Email 2</label>
                        <input type="email" id="contact_person_email2" name="contact_person_email2" value="<?php echo htmlspecialchars($user['contact_person_email2'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="contact_person_contact1">Contact 1</label>
                        <input type="text" id="contact_person_contact1" name="contact_person_contact1" value="<?php echo htmlspecialchars($user['contact_person_contact1'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_person_contact2">Contact 2</label>
                        <input type="text" id="contact_person_contact2" name="contact_person_contact2" value="<?php echo htmlspecialchars($user['contact_person_contact2'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contact_person_contact3">Contact 3</label>
                    <input type="text" id="contact_person_contact3" name="contact_person_contact3" value="<?php echo htmlspecialchars($user['contact_person_contact3'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="form-section">
                <h2>Additional Information</h2>
                <div class="form-group">
                    <label for="bank_details">Bank Details</label>
                    <textarea id="bank_details" name="bank_details"><?php echo htmlspecialchars($user['bank_details'] ?? ''); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="payment_term">Payment Term</label>
                        <input type="text" id="payment_term" name="payment_term" value="<?php echo htmlspecialchars($user['payment_term'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="inco_term_delivery">Inco Term Delivery</label>
                        <input type="text" id="inco_term_delivery" name="inco_term_delivery" value="<?php echo htmlspecialchars($user['inco_term_delivery'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Signature Upload Section -->
            <div class="form-section">
                <h2>Signature</h2>
                <div class="form-group">
                    <label for="signature">Upload Signature Image</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="signature" name="signature" accept="image/jpeg,image/jpg,image/png,image/gif" onchange="previewImage(event)">
                        <div class="file-upload-info">Accepted formats: JPG, JPEG, PNG, GIF (Max size: 2MB recommended)</div>
                    </div>
                    
                    <!-- Preview for new upload -->
                    <img id="preview-img" alt="Signature Preview">
                    
                    <!-- Current signature display -->
                    <?php if (!empty($user['signature']) && file_exists($user['signature'])): ?>
                        <div class="signature-preview">
                            <p><strong>Current Signature:</strong></p>
                            <img src="<?php echo htmlspecialchars($user['signature']); ?>" alt="Current Signature">
                            <div class="signature-actions">
                                <a href="?delete_signature=1" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete your signature?');">Delete Signature</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="signature-preview">
                            <p style="color: #999;">No signature uploaded yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>
    
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview-img');
            
            if (file) {
                // Check file size (2MB = 2097152 bytes)
                if (file.size > 2097152) {
                    alert('File size should not exceed 2MB');
                    event.target.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please upload only JPG, JPEG, PNG, or GIF images');
                    event.target.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>