<?php
$host = 'localhost';
$dbname = 'chang11v_event_portal';
$username = 'chang11v_event_portal_user';
$password = 'xxeNFAXC9teEhsLeurP3';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
