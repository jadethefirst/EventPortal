<?php
/**
 * logout.php - Logs the user out of the session securely
 */

// Start the session if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = []; // Clear session array
session_unset(); // Unregister all session variables

// Destroy the session cookie (for extra security)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session completely
session_destroy();

// Redirect user to homepage or login page
header("Location: index.php");
exit;
