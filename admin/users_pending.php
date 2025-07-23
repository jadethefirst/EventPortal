<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

// Enforce admin login to protect this page
require_admin();

// Handle approval/denial POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_id'])) {
        $userId = (int)$_POST['approve_id'];
        // Approve user: set status to 'active'
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif (isset($_POST['deny_id'])) {
        $userId = (int)$_POST['deny_id'];
        // Deny user: delete user record or set status to 'denied'
        $stmt = $pdo->prepare("UPDATE users SET status = 'denied' WHERE id = ?");
        $stmt->execute([$userId]);
    }
    // Redirect to avoid form resubmission
    header("Location: users_pending.php");
    exit;
}

// Fetch all users with status 'pending' (waiting for approval)
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.created_at 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.status = 'pending' AND r.name = 'client'
    ORDER BY u.created_at ASC
");
$stmt->execute();
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pending User Approvals - Admin - Event Portal</title>
<link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

<?php include("
