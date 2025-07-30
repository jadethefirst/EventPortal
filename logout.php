<?php
// Start session to access session variables
session_start();

// Unset all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect user back to homepage after logout
header("Location: index.php");
exit;
