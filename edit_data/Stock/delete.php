<?php
include 'db.php';

$id = $_GET['id'];

$conn->query("DELETE FROM stock WHERE id = $id");

header('Location: index.php');
?>
