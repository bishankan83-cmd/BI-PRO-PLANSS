<?php
include 'db.php';

// Get the ID from the URL
$id = $_GET['id'];

// Delete the record from the `realstock` table
$conn->query("DELETE FROM realstock WHERE id = $id");

// Delete the corresponding record from the `stock` table
$conn->query("DELETE FROM stock WHERE id = $id");

// Redirect to the index page after deletion
header('Location: index.php');
exit;
?>
