<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";


// Enforce admin-only access
require_admin();

// Initialize variables for feedback messages
$message = '';
$error = '';

// Handle form submissions: Add, Edit, Enable, Disable, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new staff member
    if (isset($_POST['add_staff'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            $error = "Username, email, and password are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username or email already exists.";
            } else {
                // Insert new staff user with 'staff' role and 'active' status
                // Assume role_id for staff is fetched
                $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'staff' LIMIT 1");
                $roleStmt->execute();
                $role_id = $roleStmt->fetchColumn();

                // Hash password securely
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role_id, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                if ($insertStmt->execute([$username, $email, $password_hash, $role_id])) {
                    $message = "Staff member added successfully.";
                } else {
                    $error = "Error adding staff member.";
                }
            }
        }
    }

    // Enable or Disable or Delete staff account
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $action = $_POST['action'];

        // Prevent admin from disabling or deleting their own account (optional)
        if ($user_id === $_SESSION['user_id']) {
            $error = "You cannot perform this action on your own account.";
        } else {
            if ($action === 'disable') {
                $stmt = $pdo->prepare("UPDATE users SET status = 'disabled' WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = 'staff')");
                if ($stmt->execute([$user_id])) {
                    $message = "Staff account disabled.";
                } else {
                    $error = "Failed to disable staff account.";
                }
            } elseif ($action === 'enable') {
                $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = 'staff')");
                if ($stmt->execute([$user_id])) {
                    $message = "Staff account enabled.";
                } else {
                    $error = "Failed to enable staff account.";
                }
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role_id = (SELECT id FROM roles WHERE name = 'staff')");
                if ($stmt->execute([$user_id])) {
                    $message = "Staff account deleted.";
                } else {
                    $error = "Failed to delete staff account.";
                }
            }
        }
    }
}

// Fetch all staff users for display
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.status, u.created_at
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'staff'
    ORDER BY u.created_at DESC
");
$stmt->execute();
$staffUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Staff - Admin - Event Portal</title>
<link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

<?php include("../includes/header.php"); ?>

<h1>Manage Staff Accounts</h1>

<?php if ($message): ?>
    <p class="success"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<!-- Add New Staff Member Form -->
<section>
    <h2>Add New Staff Member</h2>
    <form method="POST" action="manage_staff.php">
        <input type="hidden" name="add_staff" value="1" />
        <label>
            Username:
            <input type="text" name="username" required />
        </label><br />
        <label>
            Email:
            <input type="email" name="email" required />
        </label><br />
        <label>
            Password:
            <input type="password" name="password" required />
        </label><br />
        <label>
            Confirm Password:
            <input type="password" name="confirm_password" required />
        </label><br />
        <button type="submit">Add Staff Member</button>
    </form>
</section>

<!-- Staff List Table -->
<section>
    <h2>Existing Staff Members</h2>
    <?php if (count($staffUsers) === 0): ?>
        <p>No staff accounts found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffUsers as $staff): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($staff['username']); ?></td>
                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($staff['status'])); ?></td>
                        <td><?php echo htmlspecialchars($staff['created_at']); ?></td>
                        <td>
                            <?php if ($staff['status'] === 'active'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $staff['id']; ?>" />
                                    <input type="hidden" name="action" value="disable" />
                                    <button type="submit" onclick="return confirm('Disable this staff account?')">Disable</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $staff['id']; ?>" />
                                    <input type="hidden" name="action" value="enable" />
                                    <button type="submit" onclick="return confirm('Enable this staff account?')">Enable</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline; margin-left:10px;">
                                <input type="hidden" name="user_id" value="<?php echo $staff['id']; ?>" />
                                <input type="hidden" name="action" value="delete" />
                                <button type="submit" onclick="return confirm('Delete this staff account? This action is irreversible.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include("../includes/footer.php"); ?>

</body>
</html>
