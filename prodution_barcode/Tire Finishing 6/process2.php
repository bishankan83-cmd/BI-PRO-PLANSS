<?php
require 'phpqrcode/phpqrcode/qrlib.php'; // Include the library

// Database connection details
$host = 'localhost';
$db = 'planatir_task_managemen';
$user = 'planatir_task_managemen';
$password = 'Bishan@1919';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'yes') {
            // Get the values from the form
            $serialNumber = $_POST['serialNumber'] ?? '';
            $tireCode = $_POST['tireCode'] ?? '';
            $brand = $_POST['brand'] ?? '';
            $tireWeight = $_POST['tireWeight'] ?? '';
            $pressNumber = $_POST['pressNumber'] ?? '';

            if (empty($serialNumber)) {
                die("Serial number is required.");
            }


          
            // Format serial number for display
            $formattedSerialNumber = substr($serialNumber, 0, 6) . '-' . substr($serialNumber, 6);

            // Prepare data for the QR code
            $qrData = json_encode([
                'serialNumber' => $formattedSerialNumber,
                'tireCode' => $tireCode,
                'brand' => $brand,
                'tireWeight' => $tireWeight,
                'pressNumber' => $pressNumber
            ]);

            // Insert into the esaved_tires table (for historical records)
            $stmt = $pdo->prepare("INSERT INTO esaved_tires (serialNumber, tireCode, brand, tireWeight, pressNumber) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$serialNumber, $tireCode, $brand, $tireWeight, $pressNumber]);

            // Set the path for the QR code image
            $qrFilePath = 'qrcodes/' . $formattedSerialNumber . '.png';

            // Ensure the 'qrcodes/' folder exists and is writable
            if (!is_dir('qrcodes')) {
                mkdir('qrcodes', 0777, true);
            }

            // Generate the QR code
            QRcode::png($qrData, $qrFilePath);

            // Check if the QR code file was created
            if (file_exists($qrFilePath)) {
                echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>QR Code Display</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color:rgb(255, 102, 0);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(250, 77, 2);
        }
        img {
            width: 200px;
            margin-bottom: 20px;
        }
        strong {
            font-size: 18px;
        }
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden; /* Hide everything */
            }
            img, strong {
                visibility: visible; /* Show QR code and serial number */
                position: relative; /* Keep them in flow */
                display: block;
                margin: 0 auto;
                text-align: center;
            }
            img {
                margin-bottom: 10px; /* Space between QR code and serial number */
            }
        }
    </style>
    <script>
        function printQRCode() {
            window.print();
        }
    </script>
</head>
<body>
    <img src='$qrFilePath' alt='QR Code' />
    <strong>$formattedSerialNumber</strong>
    <br>
    <button onclick='printQRCode()'>Print QR Code</button>
    <br></br>
    <br></br>
    <br></br>
    <button 
        onclick=\"window.location.href='index.php';\" 
        style=\"background-color: #2C3E4F; color: white; font-size: 30px; font-weight: bold; padding: 10px 350px; border: none; border-radius: 5px; cursor: pointer;\">
        New Update
    </button>
</body>
</html>";
            } else {
                echo "Failed to generate QR code.";
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'no') {
            header("Location: index.php");
            exit;
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>