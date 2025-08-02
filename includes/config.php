<?php
// Database server hostname (often 'localhost' on MyWeb)
$host = 'localhost';

// Database name on MyWeb
$db   = 'chang11v_event_portal';

// MyWeb database username
$user = 'chang11v_event_portal_user';

// MyWeb database password — this is the password you set for the MyWeb database user, NOT your MyWeb login password!
$pass = 'admin123';

// Character set to use for connection and queries
$charset = 'utf8mb4';

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
