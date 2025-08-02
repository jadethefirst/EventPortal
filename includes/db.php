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

// Data Source Name (DSN) string for PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options array for PDO, enabling exceptions and fetch mode
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepares if possible (safer)
];

// Try to create PDO object for database connection
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, terminate script and show error message
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
