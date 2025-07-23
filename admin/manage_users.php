<?php
// Start session and include common files
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

// Ensure only admin can access this page
require_admin();

// Fetch all users and include guest counts (for clients)
try {
    $sql = "
        SELECT 
            u.id,
            u.username,
            u.email,
            r.name AS role,
            u.status,
            u.created_at,
            -- Subquery counts guests linked to client user
            (SELECT COUNT(*) FROM guests g WHERE g.user_id = u.id) AS guest_count
        FROM users u
        JOIN roles r ON u.role_id = r.id
        ORDER BY u.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Users - Admin</title>
<link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

<?php include("../includes/header.php"); ?>

<h1>Manage Users</h1>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Guest Count</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['role']); ?></td>
            <td><?php echo htmlspecialchars($user['status']); ?></td>
            <td><?php echo htmlspecialchars($user['guest_count']); ?></td>
            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
            <td>
                <!-- Example action links - implement with proper security & confirmation -->
                <a href="edit_user.php?id=<?php echo $user['id']; ?>">Edit</a> | 
                <a href="toggle_user_status.php?id=<?php echo $user['id']; ?>">Toggle Status</a> | 
                <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Delete this user?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include("../includes/footer.php"); ?>

</body>
</html>
