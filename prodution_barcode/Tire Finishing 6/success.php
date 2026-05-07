<?php
// Get the serial number
$serialNumber = $_GET['serialNumber'] ?? null;

// Validate serial number
if (!$serialNumber) {
    die("Invalid request.");
}

// File path for the QR code
$qrFilePath = 'qrcodes/' . $serialNumber . '.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>QR Code Generated</title>
</head>
<body>
    <h1>Details Saved Successfully!</h1>
    <p>Serial Number: <?= htmlspecialchars($serialNumber) ?></p>
    <p>QR Code:</p>
    <img src="<?= htmlspecialchars($qrFilePath) ?>" alt="QR Code">
    <br>
    <a href="index.php">Back to Home</a>
</body>
</html>
