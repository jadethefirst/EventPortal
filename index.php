<?php
// Start session for user login tracking
session_start();

// Include shared functions (adjust path because this file is in root)
require_once "includes/functions.php";

// Fetch global site template and dark mode setting
$currentTemplate = getSetting($pdo, 'site_template') ?? 'spring';
$currentDarkMode = getSetting($pdo, 'dark_mode_enabled') ?? '0';

// Load upcoming events from DB (only future events)
try {
    $stmt = $pdo->prepare("SELECT id, name, event_date, description FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 10");
    $stmt->execute();
    $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error gracefully
    $upcomingEvents = [];
    $error = "Error loading events: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome to Event Portal</title>
    
    <!-- SEO meta tags -->
    <meta name="description" content="Your go-to event portal for tracking attendance and managing clients and guests." />
    <meta name="keywords" content="events, attendance, client tracking, tickets, portal" />
    <meta name="author" content="Your Name" />
    <link rel="icon" href="/images/favicon.ico" />

    <!-- Load base CSS -->
    <link rel="stylesheet" href="/css/<?php echo htmlspecialchars($currentTemplate); ?>.css" />
    <!-- Load dark mode CSS if enabled -->
    <?php if ($currentDarkMode === '1'): ?>
        <link rel="stylesheet" href="/css/<?php echo htmlspecialchars($currentTemplate); ?>-dark.css" />
    <?php endif; ?>

    <!-- External JS file -->
    <script src="/js/main.js" defer></script>
</head>
<body>

<?php include("includes/header.php"); ?>

<main>
    <h1>Welcome to Event Portal</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <section id="upcoming-events">
        <h2>Upcoming Events</h2>
        <?php if (empty($upcomingEvents)): ?>
            <p>No upcoming events found. Please check back later.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($upcomingEvents as $event): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['name']); ?></strong> -
                        <?php echo date('F j, Y', strtotime($event['event_date'])); ?><br />
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?><br />
                        <a href="/client/event_register.php?event_id=<?php echo (int)$event['id']; ?>">Register</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section id="guest-actions">
        <h2>Get Started</h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Welcome back! <a href="/client/dashboard.php">Go to your dashboard</a>.</p>
            <p><a href="/logout.php">Logout</a></p>
        <?php else: ?>
            <p><a href="/register.php">Sign up</a> to become a client (admin approval required).</p>
            <p><a href="/login.php">Log in</a> if you already have an account.</p>
        <?php endif; ?>
    </section>

    <section id="about">
        <h2>About This Project</h2>
        <p>This portal allows event organizers to track client and guest attendance, manage events, and more.</p>
        <p><a href="/about.php">Learn more</a></p>
    </section>
</main>

<?php include("includes/footer.php"); ?>

</body>
</html>
