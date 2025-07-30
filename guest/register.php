<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/header.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            // Insert user with 'client' role but status 'pending'
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Get client role_id from roles table
            $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'client'");
            $roleStmt->execute();
            $role_id = $roleStmt->fetchColumn();

            $insert = $pdo->prepare("INSERT INTO users (name, email, password, role_id, status) VALUES (?, ?, ?, ?, 'pending')");
            $insert->execute([$name, $email, $hashed, $role_id]);

            $success = "Registration successful! Your account is pending approval by an administrator.";
        }
    }
}
?>

<div class="container">
    <h2>Register as Client</h2>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <p><a href="/login.php">Go to login page</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="name">Full Name:</label><br>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Register</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
