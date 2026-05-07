<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['icode'])) {
    $icode = $_GET['icode'];

    $stmt = $conn->prepare("DELETE FROM bom_new WHERE icode = ?");
    $stmt->bind_param('s', $icode);

    if ($stmt->execute()) {
        echo '<p style="color: #28a745;">Record deleted successfully.</p>';
    } else {
        echo 'Error deleting record: ' . $stmt->error;
    }

    $stmt->close();
}

header('Location: index.php');
?>
