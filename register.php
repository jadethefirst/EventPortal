<?php
// Include database connection and shared utilities
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/header.php";

// Initialize form fields and feedback messages
$full_name = $email = $password = $confirm_password = "";
$error = $success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $full_name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    // Basic form validation
    if (!$full_name || !$email || !$password || !$confirm_password) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (user_exists($pdo, $email)) {
        $error = "An account with this email already exists.";
    } else {
        // Securely hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Insert the new user into the USERS table
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, role, status)
                VALUES (?, ?, ?, 'client', 'pending')
            ");
            $stmt->execute([$full_name, $email, $hashed_password]);

            // Registration success message
            $success = "Registration successful! Please wait for admin approval before logging in.";
            $full_name = $email = $password = $confirm_password = "";
        } catch (PDOException $e) {
            // Database-level error
            $error = "An error occurred while registering. Please try again later.";
        }
    }
}

// Helper to check if a user with the given email exists
function user_exists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}
?>

<!-- ==================== HTML OUTPUT ==================== -->
<div class="container">
    <h1>Register as a Client</h1>

    <!-- Success and error feedback -->
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Registration form -->
    <form method="post" action="">
        <label for="name">Full Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($full_name); ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in here</a>.</p>
</div>

<?php require_once "includes/footer.php"; ?>
