<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

require_admin();

// Handle form submission to update guest policy and QR settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allow_guest_registration = isset($_POST['allow_guest_registration']) ? 1 : 0;
    $qr_code_expiry_days = (int)$_POST['qr_code_expiry_days'];
    $qr_code_format = $_POST['qr_code_format'];

    try {
        // Save settings in a settings table or a config file
        // Example: assume a 'settings' table with key/value pairs
        $stmt = $pdo->prepare("REPLACE INTO settings (`key`, `value`) VALUES (?, ?), (?, ?), (?, ?)");
        $stmt->execute([
            'allow_guest_registration', $allow_guest_registration,
            'qr_code_expiry_days', $qr_code_expiry_days,
            'qr_code_format', $qr_code_format
        ]);
        $message = "Settings updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Fetch current settings
try {
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
    $settings_rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    die("Error fetching settings: " . $e->getMessage());
}

// Defaults if not set
$allow_guest_registration = $settings_rows['allow_guest_registration'] ?? 1;
$qr_code_expiry_days = $settings_rows['qr_code_expiry_days'] ?? 7;
$qr_code_format = $settings_rows['qr_code_format'] ?? 'png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Settings - Admin</title>
<link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

<?php include("../includes/header.php"); ?>

<h1>Site Settings</h1>

<?php if (isset($message)): ?>
    <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if (isset($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post" action="">
    <fieldset>
        <legend>Guest Registration Policy</legend>
        <label>
            <input type="checkbox" name="allow_guest_registration" value="1" <?php echo $allow_guest_registration ? 'checked' : ''; ?> />
            Enable guest/plus-one registration site-wide
        </label>
    </fieldset>
    <fieldset>
        <legend>QR Code Settings</legend>
        <label>
            Ticket Expiration (days):
            <input type="number" name="qr_code_expiry_days" min="1" max="365" value="<?php echo htmlspecialchars($qr_code_expiry_days); ?>" />
        </label>
        <br />
        <label>
            QR Code Format:
            <select name="qr_code_format">
                <option value="png" <?php if ($qr_code_format == 'png') echo 'selected'; ?>>PNG</option>
                <option value="svg" <?php if ($qr_code_format == 'svg') echo 'selected'; ?>>SVG</option>
                <option value="jpg" <?php if ($qr_code_format == 'jpg') echo 'selected'; ?>>JPG</option>
            </select>
        </label>
    </fieldset>
    <button type="submit">Save Settings</button>
</form>

<?php include("../includes/footer.php"); ?>

</body>
</html>
