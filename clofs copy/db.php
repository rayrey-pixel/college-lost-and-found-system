<?php
$host = 'localhost';
$db   = 'clofs';
$user = 'root';        // use your DB username here
$pass = '';            // use your DB password if any
$charset = 'utf8mb4';

// Set DSN
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Enable exceptions for error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

// Try connecting
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
