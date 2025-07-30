<?php
// Include common header
require_once "includes/header.php";

// Initialize variables to store form input and messages
$name = $email = $message = "";
$success = $error = "";

// Handle POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim input to remove leading/trailing spaces
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $message = trim($_POST['message'] ?? "");

    // Basic form validation
    if (!$name || !$email || !$message) {
        $error = "Please fill in all fields."; // All fields required
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address."; // Validate email format
    } else {
        // Normally here you would send an email or save message to DB
        // For demonstration, just show a success message

        $success = "Thank you for your message. We will get back to you soon.";

        // Clear the form inputs after successful submission
        $name = $email = $message = "";
    }
}
?>

<div class="container">
    <h1>Contact Us</h1>

    <!-- Show success or error messages -->
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Contact form -->
    <form method="post" action="">
        <label for="name">Name:</label><br>
        <!-- Use htmlspecialchars to prevent HTML injection -->
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

        <label for="message">Message:</label><br>
        <!-- Preserve line breaks with textarea -->
        <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea><br><br>

        <button type="submit">Send Message</button>
    </form>
</div>

<?php
// Include common footer
require_once "includes/footer.php";
?>
