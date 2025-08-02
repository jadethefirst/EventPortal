<?php
// Enable error reporting for development/debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started, needed for user state and theme persistence
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL for all asset links, adjust to your actual deployment URL if needed
$base_url = "https://myweb.cs.uwindsor.ca/~chang11v/EventPortal/";

// === Theme Logic: Seasonal and Dark Mode ===
// Set seasonal theme based on current month if not already set in session
if (!isset($_SESSION['theme'])) {
    $month = date('n'); // Numeric month 1-12
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

// Set dark mode automatically based on current hour (7pm to 6am)
if (!isset($_SESSION['dark_mode'])) {
    $hour = date('G');
    $_SESSION['dark_mode'] = ($hour >= 19 || $hour < 6);
}

// Cache theme and dark mode in local variables for easier use in template
$theme = $_SESSION['theme'];
$darkMode = $_SESSION['dark_mode'];

// === User Role Logic ===
// Assuming you set user role upon login in session as 'admin', 'staff', 'client', or 'guest' (default)
$role = $_SESSION['role'] ?? 'guest';

?>
<!DOCTYPE html>
<html lang="en" class="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EventPortal</title>

    <!-- Base CSS variables for colors, fonts, etc -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/variables.css" />
    <!-- Font Awesome icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      integrity="sha512-pVnBzO+j6S2mFfhZVL5qvPbZr75+RPl3LpF9PAwb7AfYn1Jz9EB1n8/T0D5sGQAP9k7wh59LgJ6T0Vz6BvjtNQ=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />

    <!-- Seasonal CSS variables, activated via <html> class -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/variables-seasonal.css" />

    <!-- Conditionally load dark mode CSS with ID for JS toggling -->
    <?php if ($darkMode): ?>
        <link rel="stylesheet" href="<?php echo $base_url; ?>css/variables-dark.css" id="darkModeStylesheet" />
    <?php else: ?>
        <link rel="stylesheet" href="" id="darkModeStylesheet" disabled />
    <?php endif; ?>

    <!-- Main stylesheet -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/main.css" />

    <script>
    /*
     * Theme & Dark Mode Switcher Script
     * - Syncs theme and dark mode settings between PHP session and localStorage
     * - Allows user to change theme and toggle dark mode on the fly
     */
    document.addEventListener('DOMContentLoaded', () => {
        const baseUrl = "<?php echo $base_url; ?>";
        const themeSelect = document.getElementById('themeSelect');
        const darkCheckbox = document.getElementById('darkModeCheckbox');
        const htmlEl = document.documentElement;
        const darkModeStylesheet = document.getElementById('darkModeStylesheet');

        // Load saved preferences or fallback to PHP session values
        const storedTheme = localStorage.getItem('theme') || htmlEl.className || 'spring';
        const storedDark = (localStorage.getItem('dark_mode') === 'true') || <?php echo json_encode($darkMode); ?>;

        // Initialize UI controls if present
        if (themeSelect) themeSelect.value = storedTheme;
        if (darkCheckbox) darkCheckbox.checked = storedDark;

        // Apply theme class on <html>
        htmlEl.className = storedTheme;

        // Enable/disable dark mode stylesheet dynamically
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
        enableDarkMode(storedDark);

        // Save preferences to localStorage
        function savePreferences(theme, darkMode) {
            localStorage.setItem('theme', theme);
            localStorage.setItem('dark_mode', darkMode);
        }

        // Theme selector change listener
        if (themeSelect) {
            themeSelect.addEventListener('change', (e) => {
                const selectedTheme = e.target.value;
                htmlEl.className = selectedTheme;
                savePreferences(selectedTheme, darkCheckbox ? darkCheckbox.checked : false);
            });
        }

        // Dark mode toggle listener
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
        justify-content: space-between; /* spread content */
        align-items: center;
        padding: 1rem;
        gap: 1rem;
        flex-wrap: wrap; /* wrap on small screens */
    ">

        <!-- Site Logo or Title on the left -->
        <div class="site-logo" style="font-weight: 700; font-size: 1.5rem; color: var(--color-primary);">
            <a href="/index.php" style="text-decoration: none; color: inherit;">EventPortal</a>
        </div>

        <!-- Navigation Menu (center or right) -->
        <nav class="main-menu" aria-label="Primary Navigation" style="flex-grow: 1; min-width: 200px;">
            <ul style="
                list-style: none;
                display: flex;
                justify-content: center;
                gap: 1.5rem;
                margin: 0;
                padding: 0;
                flex-wrap: wrap;
            ">
                <!-- Home always visible -->
                <li>
                    <a href="/index.php" title="Home" aria-label="Home" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span class="menu-text">Home</span>
                    </a>
                </li>

                <!-- Catalogue available to logged in roles except guests -->
                <?php if (in_array($role, ['admin', 'staff', 'client'])): ?>
                <li>
                    <a href="/client/events.php" title="Events Catalogue" aria-label="Catalogue" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        <span class="menu-text">Catalogue</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Attendance available to staff and admin -->
                <?php if (in_array($role, ['admin', 'staff'])): ?>
                <li>
                    <a href="/staff/attendance.php" title="Attendance" aria-label="Attendance" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        <span class="menu-text">Attendance</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Clients management for staff/admin -->
                <?php if (in_array($role, ['admin', 'staff'])): ?>
                <li>
                    <a href="/admin/manage_clients.php" title="Clients" aria-label="Clients" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        <span class="menu-text">Clients</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Reporting for staff/admin -->
                <?php if (in_array($role, ['admin', 'staff'])): ?>
                <li>
                    <a href="/admin/view_reports.php" title="Reporting" aria-label="Reporting" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-chart-bar" aria-hidden="true"></i>
                        <span class="menu-text">Reports</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Admin dashboard only for admin -->
                <?php if ($role === 'admin'): ?>
                <li>
                    <a href="/admin/dashboard.php" title="Admin Dashboard" aria-label="Admin" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-cogs" aria-hidden="true"></i>
                        <span class="menu-text">Admin</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Help always visible -->
                <li>
                    <a href="/help/help_about.php" title="Help" aria-label="Help" style="display: flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-question-circle" aria-hidden="true"></i>
                        <span class="menu-text">Help</span>
                    </a>
                </li>

            </ul>
        </nav>

        <!-- Right side: Dark mode toggle + User Profile/Login -->
        <div style="display: flex; align-items: center; gap: 1rem;">

            <!-- Dark Mode Toggle -->
            <label for="darkModeCheckbox" style="
                cursor: pointer;
                user-select: none;
                display: flex;
                align-items: center;
                gap: 0.4rem;
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

            <!-- User Profile / Login Links -->
            <?php if ($role !== 'guest'): ?>
                <a href="/profile.php" title="Your Profile" style="display: flex; align-items: center; gap: 0.3rem; color: var(--color-text); text-decoration: none;">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span class="menu-text">Profile</span>
                </a>
                <a href="/logout.php" title="Logout" style="display: flex; align-items: center; gap: 0.3rem; color: var(--color-text); text-decoration: none;">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span class="menu-text">Logout</span>
                </a>
            <?php else: ?>
                <a href="/login.php" title="Login" style="display: flex; align-items: center; gap: 0.3rem; color: var(--color-text); text-decoration: none;">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                    <span class="menu-text">Login</span>
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>
