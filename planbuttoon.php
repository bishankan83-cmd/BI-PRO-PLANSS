
<?php
// Database connection
$sourceHost = 'localhost';
$sourceUser = 'planatir_task_managemen';
$sourcePass = 'Bishan@1919';
$sourceDb = 'planatir_task_managemen';

// Create connection
$conn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDb);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if data exists in the process table
$sql = "SELECT COUNT(*) as count FROM process";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    // If data exists, display the message with improved styling
    if ($count > 0) {
        echo '
        <div style="max-width: 600px; margin: 20px auto; background-color: #f8f9fa; border-left: 5px solid #F28018; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center;">
                <div style="margin-right: 15px;">
                    <i class="fas fa-sync fa-spin" style="font-size: 24px; color:rgb(0, 13, 15);"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #F28018; font-weight: 600;">System Notice</h4>
                    <p style="margin: 10px 0 0; font-size: 16px;">The Planning Department is currently updating the Work Order Plan. Please check back soon for the latest information. Thank you for your patience</p>
                </div>
            </div>
        </div>';
    }
}

// Close connection
$conn->close();
?>

<!-- Include Font Awesome for the spinning icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


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

// Get the current notice from the database
$noticeQuery = "SELECT notice_text, notice_type FROM system_notices WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1";
$noticeResult = mysqli_query($connection, $noticeQuery);
$notice = mysqli_fetch_assoc($noticeResult);

// Handle button clicks
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $redirects = [
        'button10' => 'report.php',
        'button1' => 'planning.php',
        'button2' => 'erprange.php',
        'button3' => 'indwork.php',
        'button4' => 'rangeerp.php',
        'button5' => 'time_range2.php',
    //'button5' => 'plan_plan.php',
        'button7' => 'time_range.php',
        'button8' => 'test56.php',
        'button9' => 'check_date4.php',
        'button11' => 'press_button.php',
        'button12' => 'get_mold.php',
        'button13' => 'get_com.php'
    ];

    foreach ($redirects as $button => $location) {
        if (isset($_POST[$button])) {
            header("Location: $location");
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Cantarell:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #F28018;
            --secondary-color: #000000;
            --text-color: #FFFFFF;
            --notice-success: #4CAF50;
            --notice-warning: #ff9800;
            --notice-error: #f44336;
        }

        body {
            font-family: 'Cantarell', sans-serif;
            background: url('atire2.jpg') center/cover no-repeat fixed;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .notice {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            color: var(--text-color);
            text-align: center;
            animation: slideDown 0.5s ease-out;
            position: relative;
            display: block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            font-size: 18px;
        }

        .notice-success { background-color: var(--notice-success); }
        .notice-warning { background-color: var(--notice-warning); }
        .notice-error { background-color: var(--notice-error); }

        .notice i {
            font-size: 24px;
            margin-right: 10px;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 1200px;
            padding: 20px;
        }

        .gradient-button {
            background: var(--primary-color);
            color: var(--text-color);
            font-family: 'Cantarell', sans-serif;
            font-weight: bold;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 60px;
            text-transform: uppercase;
            padding: 15px 30px;
            width: 100%;
            height: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .gradient-button:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        form {
            width: 100%;
            margin: 5px 0;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .gradient-button {
                height: 60px;
            }
        }
    </style>

    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                window.location.href = 'dashboard.php';
            }
        });

        // Auto-hide notice after 5 seconds
        window.addEventListener('load', function() {
            const notice = document.querySelector('.notice');
            if (notice) {
                setTimeout(() => {
                    notice.style.opacity = '0';
                    notice.style.transition = 'opacity 0.5s ease-out';
                    setTimeout(() => notice.style.display = 'none', 500);
                }, 50000000);
            }
        });
    </script>
</head>
<body>
    <?php if ($notice): ?>
    <div class="notice notice-<?php echo htmlspecialchars($notice['notice_type']); ?>">
        <i class="fas fa-info-circle"></i>
        <?php echo htmlspecialchars($notice['notice_text']); ?>
    </div>
    <?php endif; ?>

    <div class="container">
        <form method="post"><input type="submit" name="button10" value="Order Summary" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button1" value="To be Produced Work orders" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button2" value="All Work orders" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button3" value="Work order - Individual" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button4" value="Work order - Range" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button5" value="Production Plan" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button8" value="Compound plan - All" class="gradient-button"></form>
      
<form method="post"><input type="submit" name="button9" value="Cavity Utilization Summary" class="gradient-button"></form>

        <form method="post"><input type="submit" name="button11" value="Old Press Utilization Summary" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button12" value="Mold Utilization Summary" class="gradient-button"></form>
        <form method="post"><input type="submit" name="button13" value="Compound Plan - Order Wise" class="gradient-button"></form>
    </div>
</body>
</html>
