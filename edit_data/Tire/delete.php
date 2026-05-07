<?php
include 'db.php';

$id = $_GET['icode'];

$conn->query("DELETE FROM tire WHERE icode = $id");

header('Location: index.php');
?>
