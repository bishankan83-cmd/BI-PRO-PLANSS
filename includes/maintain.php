<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock System - Maintenance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .maintenance-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 600px;
            text-align: center;
        }
        .maintenance-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ff9800;
        }
        h1 {
            color: #333;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .contact-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .return-link {
            display: inline-block;
            margin-top: 20px;
            color: #2196F3;
            text-decoration: none;
        }
        .return-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    // You can customize these variables as needed
    $maintenanceTitle = "Stock System Maintenance";
    $maintenanceMessage = "Our stock management system is currently undergoing maintenance to improve performance and reliability.";
    $expectedCompletion = "Expected completion: " . date('F j, Y', strtotime('+2 days'));
    $contactEmail = "admin@example.com";
    $contactPhone = "555-123-4567";
    $returnUrl = "../index.php"; // Change this to your main page URL
    ?>

    <div class="maintenance-container">
        <div class="maintenance-icon">⚙️</div>
        <h1><?php echo $maintenanceTitle; ?></h1>
        <p><?php echo $maintenanceMessage; ?></p>
        <p><?php echo $expectedCompletion; ?></p>
        
        <div class="contact-info">
            <h3>Need immediate assistance?</h3>
            <p>Please contact the system administrator:</p>
            <p>Email: <?php echo $contactEmail; ?></p>
            <p>Phone: <?php echo $contactPhone; ?></p>
        </div>
        
        <a href="<?php echo $returnUrl; ?>" class="return-link">Return to Homepage</a>
    </div>
</body>
</html>