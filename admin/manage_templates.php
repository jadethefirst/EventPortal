<?php
// Load shared functions and authentication checks
require_once "../includes/functions.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

// Enforce admin-only access
require_admin();

// Define 8 base themes (seasonal + special occasions)
$templates = [
    'spring'      => 'Spring',
    'summer'      => 'Summer',
    'fall'        => 'Fall',
    'winter'      => 'Winter',
    'newyear'     => 'New Year',
    'halloween'   => 'Halloween',
    'thanksgiving'=> 'Thanksgiving',
    'christmas'   => 'Christmas'
];

// Process form submission to update site template and dark mode setting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedTemplate = $_POST['template'] ?? null;
    $darkModeEnabled = isset($_POST['dark_mode']) ? '1' : '0';

    // Validate selected template is one of the allowed templates
    if (array_key_exists($selectedTemplate, $templates)) {
        // Save settings in the database (assumes saveSetting() in functions.php)
        $successTemplate = saveSetting($pdo, 'site_template', $selectedTemplate);
        $successDarkMode = saveSetting($pdo, 'dark_mode_enabled', $darkModeEnabled);

        if ($successTemplate && $successDarkMode) {
            $message = "Template and Dark Mode settings updated successfully!";
        } else {
            $message = "Failed to update settings. Please try again.";
        }
    } else {
        $message = "Invalid template selected.";
    }
}

// Retrieve current settings or set defaults
$currentTemplate = getSetting($pdo, 'site_template') ?? 'spring';
$currentDarkMode = getSetting($pdo, 'dark_mode_enabled') ?? '0';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Site Templates - Admin</title>
    <!-- Load the currently selected base theme CSS -->
    <link rel="stylesheet" href="/css/<?php echo htmlspecialchars($currentTemplate); ?>.css" />
    <!-- If dark mode enabled globally, load dark CSS -->
    <?php if ($currentDarkMode === '1'): ?>
        <link rel="stylesheet" href="/css/<?php echo htmlspecialchars($currentTemplate); ?>-dark.css" />
    <?php endif; ?>
</head>
<body>

<?php include("../includes/header.php"); ?>

<h1>Manage Site Templates</h1>

<?php if (isset($message)): ?>
    <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
<?php endif; ?>

<!-- Template selection form -->
<form method="post" action="manage_templates.php">
    <label for="template">Select Base Theme:</label>
    <select name="template" id="template">
        <?php foreach ($templates as $key => $label): ?>
            <option value="<?php echo htmlspecialchars($key); ?>" <?php if ($currentTemplate === $key) echo "selected"; ?>>
                <?php echo htmlspecialchars($label); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br /><br />

    <label for="dark_mode">
        <input type="checkbox" name="dark_mode" id="dark_mode" value="1" <?php if ($currentDarkMode === '1') echo 'checked'; ?> />
        Enable Dark Mode Globally
    </label>

    <br /><br />

    <button type="submit">Update Settings</button>
</form>

<?php include("../includes/footer.php"); ?>

</body>
</html>
