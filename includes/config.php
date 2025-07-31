<?php
$host = 'localhost'; // Or whatever is shown in MyWeb’s phpMyAdmin
$db   = 'chang11v_event_portal';
$user = 'admin';  // Usually the same as your cPanel/MyWeb login
$pass = 'admin123';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("❌ Database connection failed: " . $e->getMessage());
}
?>
