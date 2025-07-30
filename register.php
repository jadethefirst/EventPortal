<?php
// Include database connection and shared functions
require_once "includes/db.php";
require_once "includes/functions.php";

// Include site header (HTML head, nav)
require_once "includes/header.php";

// Initialize variables
$name = $email = $password = $confirm_password = "";
$error = $success = "";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim inputs
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    // Validate presence of all fields
    if (!$name || !$email || !$password || !$confirm_password) {
        $error = "Please fill in all fields.";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }
    // Validate matching passwords
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    // Check if email already registered
    elseif (user_exists($pdo, $email)) {
        $error = "Email already registered.";
    }
    else {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user with role client and status pending approval
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role_id, status)
            VALUES (?, ?, ?, (SELECT id FROM roles WHERE name = 'client'), 'pending')
        ");
        $stmt->execute([$name, $email, $hashed_password]);

        // Set success message and clear form
        $success = "Registration successful! Await admin approval before you can log in.";
        $name = $email = $password = $confirm_password = "";
    }
}

// Helper function to check if user exists by email
function user_exists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    // Returns true if count > 0
    return $stmt->fetchColumn() > 0;
}
?>

<div class="container">
    <h1>Register as a Client</h1>

    <!-- Show success or error -->
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Registration form -->
    <form method="post" action="">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <button type="submit">Register</button>
    </form>
</div>

<?php
// Include common footer
require_once "includes/footer.php";
?>
