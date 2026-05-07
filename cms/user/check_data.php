<?php
session_start();
include('include/config.php');
error_reporting(0); // Suppress errors for production

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Please log in to upload files']);
        exit;
    }
    $showLoginMessage = true;
} else {
    $showLoginMessage = false;
}

// Handle file upload if POST request and user is authenticated
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');

    // Configuration
    $uploadDir = 'uploads/';
    $maxFileSize = 1024 * 1024 * 1024; // 1GB max file size
    $allowedTypes = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'];

    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Check for required POST parameters
    if (!isset($_FILES['video']) || !isset($_POST['chunk']) || !isset($_POST['totalChunks']) || !isset($_POST['fileName'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }

    $chunk = (int)$_POST['chunk'];
    $totalChunks = (int)$_POST['totalChunks'];
    $fileName = basename(preg_replace('/[^a-zA-Z0-9._-]/', '', $_POST['fileName'])); // Sanitize filename
    $tempFile = $uploadDir . $fileName . '.part' . $chunk;
    $finalFile = $uploadDir . $fileName;

    // Validate file type
    $fileType = mime_content_type($_FILES['video']['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only MP4, MPEG, MOV, AVI allowed.']);
        exit;
    }

    // Validate file size (for the chunk)
    if ($_FILES['video']['size'] > $maxFileSize) {
        echo json_encode(['status' => 'error', 'message' => 'File chunk too large']);
        exit;
    }

    // Move uploaded chunk to temporary file
    if (!move_uploaded_file($_FILES['video']['tmp_name'], $tempFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save chunk']);
        exit;
    }

    // If this is the last chunk, combine all chunks and save to database
    if ($chunk == $totalChunks - 1) {
        $fileHandle = fopen($finalFile, 'wb');
        if (!$fileHandle) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create final file']);
            exit;
        }
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = $uploadDir . $fileName . '.part' . $i;
            if (!file_exists($chunkFile)) {
                fclose($fileHandle);
                echo json_encode(['status' => 'error', 'message' => 'Missing chunk file']);
                exit;
            }
            $chunkHandle = fopen($chunkFile, 'rb');
            while (!feof($chunkHandle)) {
                fwrite($fileHandle, fread($chunkHandle, 8192));
            }
            fclose($chunkHandle);
            unlink($chunkFile); // Delete chunk file
        }
        fclose($fileHandle);

        // Create table if it doesn't exist
        $conn->query("
            CREATE TABLE IF NOT EXISTS videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert metadata
        $userId = $_SESSION['user_id'];
        $filePath = $finalFile;
        $stmt = $conn->prepare("INSERT INTO videos (user_id, filename, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $fileName, $filePath);
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save metadata to database']);
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'message' => 'Chunk uploaded successfully']);
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Large Video File with Database</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .progress { width: 100%; height: 20px; }
        .error { color: red; }
        .success { color: green; }
        button { padding: 10px 20px; cursor: pointer; }
        input[type="file"] { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Upload Large Video File</h2>
    <?php if ($showLoginMessage): ?>
        <p class="error">Please <a href="login.php">log in</a> to upload files.</p>
    <?php else: ?>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" name="video" id="videoInput" accept="video/*" required>
            <button type="submit">Upload</button>
        </form>
        <div id="progressContainer" style="display: none;">
            <p>Upload Progress: <span id="progressText">0%</span></p>
            <progress id="progressBar" value="0" max="100" class="progress"></progress>
        </div>
        <div id="message"></div>
    <?php endif; ?>

    <script>
        <?php if (!$showLoginMessage): ?>
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const fileInput = document.getElementById('videoInput');
            const file = fileInput.files[0];
            if (!file) {
                document.getElementById('message').innerHTML = '<p class="error">Please select a file.</p>';
                return;
            }

            const chunkSize = 1024 * 1024 * 5; // 5MB chunks
            const totalChunks = Math.ceil(file.size / chunkSize);
            let currentChunk = 0;

            const uploadChunk = async (start, end) => {
                const formData = new FormData();
                formData.append('video', file.slice(start, end));
                formData.append('chunk', currentChunk);
                formData.append('totalChunks', totalChunks);
                formData.append('fileName', file.name);

                try {
                    const response = await fetch('<?php echo basename(__FILE__); ?>', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        currentChunk++;
                        const progress = Math.round((currentChunk / totalChunks) * 100);
                        document.getElementById('progressBar').value = progress;
                        document.getElementById('progressText').textContent = `${progress}%`;
                        if (currentChunk < totalChunks) {
                            uploadChunk(currentChunk * chunkSize, (currentChunk + 1) * chunkSize);
                        } else {
                            document.getElementById('message').innerHTML = '<p class="success">File uploaded successfully!</p>';
                            document.getElementById('progressContainer').style.display = 'none';
                        }
                    } else {
                        document.getElementById('message').innerHTML = `<p class="error">${result.message}</p>`;
                        document.getElementById('progressContainer').style.display = 'none';
                    }
                } catch (error) {
                    document.getElementById('message').innerHTML = '<p class="error">Upload failed: ' + error.message + '</p>';
                    document.getElementById('progressContainer').style.display = 'none';
                }
            };

            document.getElementById('progressContainer').style.display = 'block';
            document.getElementById('progressBar').value = 0;
            document.getElementById('progressText').textContent = '0%';
            uploadChunk(0, chunkSize);
        });
        <?php endif; ?>
    </script>
</body>
</html>