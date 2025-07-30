<?php
// Include DB, authentication, functions, header
require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/functions.php";
require_once "includes/header.php";

// Initialize variables for form input and errors
$email = $password = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim input
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    // Validate required fields
    if (!$email || !$password) {
        $error = "Please enter both email and password.";
    } else {
        // Retrieve user record by email
        $user = get_user_by_email($pdo, $email);

        // If user found and password matches
        if ($user && password_verify($password, $user['password'])) {
            // Check if user status is active (approved)
            if ($user['status'] !== 'active') {
                $error = "Your account is not active. Please wait for admin approval.";
            } else {
                // Store user info in session for authentication
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['name'] = $user['name'];

                // Redirect user based on role
                switch ($user['role_name']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        exit;
                    case 'staff':
                        header("Location: staff/dashboard.php");
                        exit;
                    case 'client':
                        header("Location: client/dashboard.php");
                        exit;
                    default:
                        $error = "Unknown user role. Contact support.";
                }
            }
        } else {
            // User not found or password mismatch
            $error = "Invalid email or password.";
        }
    }
}

// Helper function to get user info and role by email
function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.password, u.status, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    return $stmt->fetch();
}
?>

<div class="container">
    <h1>Login</h1>

    <!-- Show error if any -->
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="post" action="">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Log In</button>
    </form>

    <!-- Link to registration -->
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

<?php
require_once "includes/footer.php";
?>
