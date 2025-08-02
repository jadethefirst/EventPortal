<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require_once "includes/db.php";

// Include the common header (includes HTML head, CSS/JS, navigation)
require_once "includes/header.php";

// Prepare a SQL statement to get the next 5 upcoming events
$stmt = $pdo->prepare("
    SELECT id, title, start_time, description 
    FROM events 
    WHERE start_time >= NOW() 
    ORDER BY start_time ASC 
    LIMIT 5
");
$stmt->execute();
$events = $stmt->fetchAll();
?>

<div class="container">
    <h1>Welcome to EventPortal</h1>
    <p>Explore upcoming events and register to join.</p>

    <?php if (empty($events)): ?>
        <p>No upcoming events at this time. Check back later!</p>
    <?php else: ?>
        <h2>Upcoming Events</h2>
        <ul class="event-list">
            <?php foreach ($events as $event): ?>
                <li>
                    <a href="guest/events.php#event-<?php echo $event['id']; ?>">
                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                    </a> 
                    - 
                    <?php echo date('F j, Y g:i A', strtotime($event['start_time'])); ?>
                    <p>
                        <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
