<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare statement
    $stmt = $conn->prepare("DELETE FROM complete_date WHERE id = ?");
    $stmt->bind_param('i', $id);  // 'i' for integer

    if ($stmt->execute()) {
        echo '<p style="color: #28a745;">Record deleted successfully.</p>';
    } else {
        echo 'Error deleting record: ' . $stmt->error;
    }

    $stmt->close();
}

header('Location: index.php');
?> 
