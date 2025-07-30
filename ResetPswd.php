<?php
// Include database connection and header
require_once "includes/db.php";
require_once "includes/header.php";

// Initialize variables
$email = "";
$message = "";
$error = "";

// Handle POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input email
    $email = trim($_POST['email'] ?? "");

    // Validate email presence
    if (!$email) {
        $error = "Please enter your registered email.";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if user exists and is active
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // If user exists, here you would generate a reset token and send email
        // For now, show generic success message for security reasons
        $message = "If the email is registered, a password reset link will be sent shortly.";
    }
}
?>

<div class="container">
    <h1>Reset Password</h1>

    <!-- Show messages -->
    <?php if ($message): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Reset password form -->
    <form method="post" action="">
        <label for="email">Registered Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <button type="submit">Request Password Reset</button>
    </form>
</div>

<?php
// Include footer
require_once "includes/footer.php";
?>
