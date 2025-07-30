<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use session values or default seasonal/time-based
if (!isset($_SESSION['theme'])) {
    $month = date('n');
    if (in_array($month, [12, 1, 2])) {
        $_SESSION['theme'] = 'winter';
    } elseif (in_array($month, [3, 4, 5])) {
        $_SESSION['theme'] = 'spring';
    } elseif (in_array($month, [6, 7, 8])) {
        $_SESSION['theme'] = 'summer';
    } else {
        $_SESSION['theme'] = 'autumn';
    }
}

if (!isset($_SESSION['dark_mode'])) {
    $hour = date('G');
    $_SESSION['dark_mode'] = ($hour >= 19 || $hour < 6);
}

$theme = $_SESSION['theme'];
$darkMode = $_SESSION['dark_mode'];

$cssFile = $darkMode ? "{$theme}-dark.css" : "{$theme}.css";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>EventPortal</title>
<link rel="stylesheet" href="/css/<?php echo htmlspecialchars($cssFile); ?>" id="themeStylesheet" />
<script>
// On page load, sync localStorage theme/dark_mode if available to override PHP session
document.addEventListener('DOMContentLoaded', () => {
    const storedTheme = localStorage.getItem('theme');
    const storedDark = localStorage.getItem('dark_mode') === 'true';

    if (storedTheme) {
        // Update stylesheet href
        const themeStylesheet = document.getElementById('themeStylesheet');
        const cssFile = storedDark ? `${storedTheme}-dark.css` : `${storedTheme}.css`;
        themeStylesheet.href = `/css/${cssFile}`;

        // Optionally update form inputs if present
        const themeSelect = document.getElementById('themeSelect');
        if (themeSelect) themeSelect.value = storedTheme;
        const darkCheckbox = document.getElementById('darkModeCheckbox');
        if (darkCheckbox) darkCheckbox.checked = storedDark;
    }
});

// On form submission, save user choices to localStorage so JS & PHP stay in sync
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('themeForm');
    if (form) {
        form.addEventListener('submit', () => {
            const themeSelect = document.getElementById('themeSelect');
            const darkCheckbox = document.getElementById('darkModeCheckbox');
            if (themeSelect && darkCheckbox) {
                localStorage.setItem('theme', themeSelect.value);
                localStorage.setItem('dark_mode', darkCheckbox.checked);
            }
        });
    }
});
</script>
</head>
<body>