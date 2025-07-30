<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/header.php";

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Here you could send an email or store message in DB for admin review
        // For now, we'll simulate success
        $success = "Thank you for contacting us. We'll get back to you shortly.";
    }
}
?>

<div class="container">
    <h2>Contact Us</h2>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required><br><br>

        <label for="message">Message:</label><br>
        <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($message ?? '') ?></textarea><br><br>

        <button type="submit">Send Message</button>
    </form>
</div>

<?php require_once "includes/footer.php"; ?>
