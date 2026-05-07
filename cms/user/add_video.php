<?php
// config.php - Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USER', 'planatir_task_managemen');
define('DB_PASS', 'Bishan@1919');
define('DB_NAME', 'planatir_cms');

// Start session to access id (if stored in session)
session_start();

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Clear the videos table on page visit (GET request)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $conn = getDBConnection();
    $sql = "DELETE FROM videos";
    $conn->query($sql);
    $conn->close();
}

// Handle upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $conn = getDBConnection();
    
    // Get id (from session or form data)
    $id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : null);
    
    // Validate id
    if ($id === null || $id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing user ID.']);
        $conn->close();
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/videos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file
    $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv'];
    $maxSize = 500 * 1024 * 1024; // 500MB
    
    if (!in_array($_FILES['video']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only video files are allowed.']);
        $conn->close();
        exit;
    }
    
    if ($_FILES['video']['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 500MB limit.']);
        $conn->close();
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('video_') . '.' . $extension;
    $filePath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['video']['tmp_name'], $filePath)) {
        // Insert into database with id
        $title = '';
        $description = '';
        $fileSize = $_FILES['video']['size'];
        $mimeType = $_FILES['video']['type'];
        
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO videos (id, title, description, filename, file_path, file_size, mime_type, upload_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issssis", $id, $title, $description, $filename, $filePath, $fileSize, $mimeType);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Video uploaded successfully!']);
        } else {
            unlink($filePath); // Delete file if database insert fails
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
    }
    
    $conn->close();
    exit;
}

// HTML Interface
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Upload</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; }
        .upload-section { background: #f9f9f9; padding: 20px; border-radius: 6px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="file"], input[type="hidden"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #0056b3; }
        .message { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📹 Video Upload</h1>
        
        <div class="upload-section">
            <h2>Upload Video</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="video">Video File (Max 500MB):</label>
                    <input type="file" id="video" name="video" accept="video/*" required>
                </div>
                <!-- Hidden input for id (replace 123 with actual user ID) -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($_SESSION['id']) ? $_SESSION['id'] : '123'); ?>">
                <button type="submit">Upload Video</button>
            </form>
        </div>

        <div id="message"></div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const messageDiv = document.getElementById('message');
            
            messageDiv.innerHTML = '<div class="message">Uploading...</div>';
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="message success">' + result.message + '</div>';
                    setTimeout(() => {
                        window.location.href = 'register-complaint.php';
                    }, 1500);
                } else {
                    messageDiv.innerHTML = '<div class="message error">' + result.message + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="message error">Upload failed: ' + error.message + '</div>';
            }
        });
    </script>
</body>
</html>