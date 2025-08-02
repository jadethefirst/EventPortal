<?php
// Include DB connection, auth session, helper functions, and header
require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/functions.php";
require_once "includes/header.php";

// Initialize form input and error message
$email = $password = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim email input
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";

    // Validate both fields
    if (!$email || !$password) {
        $error = "Please enter both email and password.";
    } else {
        // Look up user by email (using helper function below)
        $user = get_user_by_email($pdo, $email);

        // If user exists and password is valid
        if ($user && password_verify($password, $user['password_hash'])) {

            // Check account status
            if ($user['status'] !== 'active') {
                $error = "Your account is not active. Please wait for admin approval.";
            } else {
                // Save user info in session for authentication
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];  // Using ENUM field directly
                $_SESSION['name'] = $user['full_name'];  // Use full_name instead of name

                // Redirect to appropriate dashboard based on role
                switch ($user['role']) {
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
            // No match found or invalid password
            $error = "Invalid email or password.";
        }
    }
}

// Helper function to retrieve user by email
function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, password_hash, role, status
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <h1>Login</h1>

    <!-- Display login error message -->
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="post" action="">
        <label for="email">Email:</label><br>
        <input
            type="email"
            id="email"
            name="email"
            value="<?php echo htmlspecialchars($email); ?>"
            required
        ><br><br>

        <label for="password">Password:</label><br>
        <input
            type="password"
            id="password"
            name="password"
            required
        ><br><br>

        <button type="submit">Log In</button>
    </form>

    <!-- Link to registration -->
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

<?php
require_once "includes/footer.php";
?>
