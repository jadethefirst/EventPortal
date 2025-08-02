<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

// Ensure only admins can access this page
require_admin();

// =======================
// HANDLE POST ACTIONS
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($user_id && in_array($action, ['approve', 'decline'])) {
        if ($action === 'approve') {
            // Set user status to active
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->execute([$user_id]);
        } elseif ($action === 'decline') {
            // Delete user from database
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }

        // Redirect to avoid form resubmission
        header("Location: manage_users.php");
        exit;
    }
}

// =======================
// FETCH USERS WITH GUEST COUNT
// =======================
try {
    $sql = "
        SELECT 
            u.id,
            u.username,
            u.email,
            u.status,
            u.created_at,
            r.name AS role,
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

<div class="container">
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
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['status']) ?></td>
                <td><?= htmlspecialchars($user['guest_count']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td>
                    <?php if ($user['status'] === 'pending'): ?>
                        <!-- Approve Button -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="action" value="approve">✅ Approve</button>
                        </form>

                        <!-- Decline Button -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="action" value="decline" onclick="return confirm('Are you sure you want to decline this user?');">❌ Decline</button>
                        </form>
                    <?php else: ?>
                        <!-- Standard Actions -->
                        <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a> |
                        <a href="toggle_user_status.php?id=<?= $user['id'] ?>">Toggle Status</a> |
                        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include("../includes/footer.php"); ?>

</body>
</html>
