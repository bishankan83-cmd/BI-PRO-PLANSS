<?php
// Database configuration file
// Filename: config.php

// Define database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'planatir_task_managemen');
define('DB_PASSWORD', 'Bishan@1919');
define('DB_NAME', 'planatir_task_managemen');

// Attempt to connect to MySQL database using PDO
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set character set
    $pdo->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}