<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['holiday_id'])) {
    $holiday_id = $_GET['holiday_id'];

    // Prepare statement
    $stmt = $conn->prepare("DELETE FROM holidays WHERE holiday_id = ?");
    $stmt->bind_param('i', $holiday_id);  // 'i' for integer

    if ($stmt->execute()) {
        echo '<p style="color: #28a745;">Record deleted successfully.</p>';
    } else {
        echo 'Error deleting record: ' . $stmt->error;
    }

    $stmt->close();
}

header('Location: index.php');
?> 
