<?php
// Database connection
$host = 'localhost';
$username = 'planatir_task_managemen';
$password = 'Bishan@1919';
$database = 'planatir_task_managemen';

$connection = mysqli_connect($host, $username, $password, $database);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $notice_text = mysqli_real_escape_string($connection, $_POST['notice_text']);
                $notice_type = mysqli_real_escape_string($connection, $_POST['notice_type']);
                $priority = (int)$_POST['priority'];
                $created_by = mysqli_real_escape_string($connection, $_POST['created_by']);
                $expiry_date = !empty($_POST['expiry_date']) ? "'" . $_POST['expiry_date'] . "'" : "NULL";

                $query = "INSERT INTO system_notices (notice_text, notice_type, priority, created_by, expiry_date) 
                         VALUES ('$notice_text', '$notice_type', $priority, '$created_by', $expiry_date)";
                
                if (mysqli_query($connection, $query)) {
                    $message = '<div class="alert success">Notice added successfully!</div>';
                } else {
                    $message = '<div class="alert error">Error: ' . mysqli_error($connection) . '</div>';
                }
                break;

            case 'toggle':
                $id = (int)$_POST['notice_id'];
                $query = "UPDATE system_notices SET is_active = NOT is_active WHERE id = $id";
                mysqli_query($connection, $query);
                break;

            case 'delete':
                $id = (int)$_POST['notice_id'];
                $query = "DELETE FROM system_notices WHERE id = $id";
                mysqli_query($connection, $query);
                break;
        }
    }
}

// Fetch existing notices
$query = "SELECT * FROM system_notices ORDER BY created_at DESC";
$notices = mysqli_query($connection, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage System Notices</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --text-color: #333333;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --warning-color: #ff9800;
        }

        body {
            font-family: 'Cantarell', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: var(--text-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
        }

        .form-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .submit-button {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: var(--secondary-color);
        }

        .notice-list {
            margin-top: 40px;
        }

        .notice-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notice-content {
            flex-grow: 1;
        }

        .notice-actions {
            display: flex;
            gap: 10px;
        }

        .notice-actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .toggle-button {
            background-color: var(--warning-color);
            color: white;
        }

        .delete-button {
            background-color: var(--error-color);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            background-color: var(--success-color);
            color: white;
        }

        .error {
            background-color: var(--error-color);
            color: white;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
        }

        .status-active {
            background-color: var(--success-color);
        }

        .status-inactive {
            background-color: var(--error-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .notice-item {
                flex-direction: column;
                gap: 10px;
            }

            .notice-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage System Notices</h1>
        
        <?php echo $message; ?>

        <div class="form-section">
            <form method="post" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="notice_text">Notice Text:</label>
                    <textarea name="notice_text" required></textarea>
                </div>

                <div class="form-group">
                    <label for="notice_type">Notice Type:</label>
                    <select name="notice_type" required>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority (1-5):</label>
                    <input type="number" name="priority" min="1" max="5" value="1" required>
                </div>

                <div class="form-group">
                    <label for="created_by">Created By:</label>
                    <input type="text" name="created_by" required>
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date (optional):</label>
                    <input type="datetime-local" name="expiry_date">
                </div>

                <button type="submit" class="submit-button">Add Notice</button>
            </form>
        </div>

        <div class="notice-list">
            <h2>Existing Notices</h2>
            <?php while ($notice = mysqli_fetch_assoc($notices)): ?>
                <div class="notice-item">
                    <div class="notice-content">
                        <strong><?php echo htmlspecialchars($notice['notice_text']); ?></strong>
                        <br>
                        <small>
                            Type: <?php echo htmlspecialchars($notice['notice_type']); ?> |
                            Priority: <?php echo htmlspecialchars($notice['priority']); ?> |
                            Created by: <?php echo htmlspecialchars($notice['created_by']); ?> |
                            Created at: <?php echo htmlspecialchars($notice['created_at']); ?>
                            <span class="status-badge <?php echo $notice['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $notice['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </small>
                    </div>
                    <div class="notice-actions">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                            <button type="submit" class="toggle-button">
                                <?php echo $notice['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </form>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                            <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this notice?')">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => alert.style.display = 'none', 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>