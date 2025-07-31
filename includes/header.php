<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL for assets (adjust as needed)
$base_url = "https://myweb.cs.uwindsor.ca/~chang11v/EventPortal/";

// Determine seasonal theme if not already set in session
if (!isset($_SESSION['theme'])) {
    $month = date('n'); // Numeric month without leading zero (1-12)
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

// Determine dark mode based on time if not set
if (!isset($_SESSION['dark_mode'])) {
    $hour = date('G'); // 24-hour format without leading zeros
    $_SESSION['dark_mode'] = ($hour >= 19 || $hour < 6);
}

// Store current theme and dark mode state in variables for convenience
$theme = $_SESSION['theme'];
$darkMode = $_SESSION['dark_mode'];
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8" />
    <title>EventPortal</title>

    <!-- Load base CSS variables -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/variables.css" />

    <!-- Load seasonal CSS variables, activated by class on <html> -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/variables-seasonal.css" />

    <!-- Conditionally load dark mode CSS with an ID for JS toggling -->
    <?php if ($darkMode): ?>
        <link rel="stylesheet" href="<?php echo $base_url; ?>css/variables-dark.css" id="darkModeStylesheet" />
    <?php else: ?>
        <link rel="stylesheet" href="" id="darkModeStylesheet" disabled />
    <?php endif; ?>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/main.css" />

    <script>
    /*
     * JavaScript for live theme and dark mode switching:
     * - Syncs with localStorage for persistent user preferences
     * - Applies seasonal theme by toggling class on <html>
     * - Dynamically enables/disables dark mode stylesheet
     * - Updates UI controls for theme selection and dark mode toggle
     */
    document.addEventListener('DOMContentLoaded', () => {
        const baseUrl = "<?php echo $base_url; ?>";

        // Get references to UI controls (expected to exist in DOM)
        const themeSelect = document.getElementById('themeSelect');
        const darkCheckbox = document.getElementById('darkModeCheckbox');

        // <html> element where theme class will be toggled
        const htmlEl = document.documentElement;

        // Dark mode stylesheet link element for toggling enable/disable
        const darkModeStylesheet = document.getElementById('darkModeStylesheet');

        // Load preferences from localStorage or fallback to PHP session values
        const storedTheme = localStorage.getItem('theme') || htmlEl.className || 'spring';
        const storedDark = (localStorage.getItem('dark_mode') === 'true') || <?php echo json_encode($darkMode); ?>;

        // Initialize UI controls to match loaded preferences
        if (themeSelect) themeSelect.value = storedTheme;
        if (darkCheckbox) darkCheckbox.checked = storedDark;

        // Apply theme class on <html> element
        htmlEl.className = storedTheme;

        // Function to enable or disable dark mode stylesheet dynamically
        function enableDarkMode(enable) {
            if (!darkModeStylesheet) return;
            if (enable) {
                darkModeStylesheet.href = baseUrl + "css/variables-dark.css";
                darkModeStylesheet.disabled = false;
            } else {
                darkModeStylesheet.href = "";
                darkModeStylesheet.disabled = true;
            }
        }

        // Apply dark mode on load
        enableDarkMode(storedDark);

        // Save preferences to localStorage
        function savePreferences(theme, darkMode) {
            localStorage.setItem('theme', theme);
            localStorage.setItem('dark_mode', darkMode);
        }

        // Listen for changes to theme dropdown if it exists
        if (themeSelect) {
            themeSelect.addEventListener('change', (e) => {
                const selectedTheme = e.target.value;
                htmlEl.className = selectedTheme;
                savePreferences(selectedTheme, darkCheckbox ? darkCheckbox.checked : false);
            });
        }

        // Listen for changes to dark mode toggle checkbox if it exists
        if (darkCheckbox) {
            darkCheckbox.addEventListener('change', (e) => {
                const darkEnabled = e.target.checked;
                enableDarkMode(darkEnabled);
                savePreferences(themeSelect ? themeSelect.value : htmlEl.className, darkEnabled);
            });
        }
    });
    </script>
</head>
<body>

<header>
    <div class="container header-content" style="
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 1rem;
        gap: 1rem;
    ">
        <!-- Dark Mode Toggle -->
        <label for="darkModeCheckbox" style="
            cursor: pointer;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--color-text);
            font-family: var(--font-primary);
        ">
            <input
                type="checkbox"
                id="darkModeCheckbox"
                name="dark_mode"
                style="width: 1.5rem; height: 1.5rem; cursor: pointer;"
                aria-label="Toggle dark mode"
            />
            Dark Mode
        </label>
    </div>
</header>
