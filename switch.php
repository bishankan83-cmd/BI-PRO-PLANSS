

<div class="button-container">
        <button>
            <a href="dashboard.php" style="text-decoration: none; color: #FFFFFF;">Click To dashboard</a>
        </button>
    </div>




<?php
// Database connection settings
$servername = "localhost"; // Database host and port
$username = "planatir_task_managemen"; // Database username
$password = "Bishan@1919"; // Database password
$dbname = "planatir_task_managemen";

// Feature key to toggle (you can pass this as a parameter)
$featureKey = isset($_GET['feature']) ? $_GET['feature'] : 'system_update';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the features table exists, if not create it
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_features (
        id INT AUTO_INCREMENT PRIMARY KEY,
        feature_key VARCHAR(50) UNIQUE NOT NULL,
        feature_name VARCHAR(100) NOT NULL,
        is_enabled TINYINT(1) DEFAULT 0,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Check if the feature exists, if not create it
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_features WHERE feature_key = ?");
    $stmt->execute([$featureKey]);
    if ($stmt->fetchColumn() == 0) {
        $insertStmt = $pdo->prepare("INSERT INTO system_features (feature_key, feature_name, is_enabled) VALUES (?, ?, 0)");
        $insertStmt->execute([$featureKey, ucfirst(str_replace('_', ' ', $featureKey))]);
    }
    
    // Process toggle action
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_action'])) {
        $action = $_POST['toggle_action'];
        
        // Set value based on action (enable=1, disable=0)
        $newState = ($action === 'enable') ? 1 : 0;
        
        // Update the database
        $updateStmt = $pdo->prepare("UPDATE system_features SET is_enabled = ? WHERE feature_key = ?");
        $updateStmt->execute([$newState, $featureKey]);
        
        $message = "Feature \"" . ucfirst(str_replace('_', ' ', $featureKey)) . "\" has been " . 
                  ($newState ? 'enabled' : 'disabled') . ".";
    }
    
    // Get current feature state
    $stmt = $pdo->prepare("SELECT feature_name, is_enabled FROM system_features WHERE feature_key = ?");
    $stmt->execute([$featureKey]);
    $feature = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feature Toggle Button</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        .feature-status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
        }
        .enabled {
            background-color: #d4edda;
            color: #155724;
        }
        .disabled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }
        .btn-enable {
            background-color: #28a745;
            color: white;
        }
        .btn-enable:hover {
            background-color: #218838;
        }
        .btn-disable {
            background-color: #dc3545;
            color: white;
        }
        .btn-disable:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Feature Toggle: <?php echo htmlspecialchars($feature['feature_name']); ?></h1>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="feature-status <?php echo $feature['is_enabled'] ? 'enabled' : 'disabled'; ?>">
            Current Status: <?php echo $feature['is_enabled'] ? 'ENABLED (1)' : 'DISABLED (0)'; ?>
        </div>
        
        <form method="post" action="">
            <?php if ($feature['is_enabled']): ?>
                <button type="submit" name="toggle_action" value="disable" class="btn btn-disable">DISABLE (Set to 0)</button>
            <?php else: ?>
                <button type="submit" name="toggle_action" value="enable" class="btn btn-enable">ENABLE (Set to 1)</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>