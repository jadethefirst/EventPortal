<?php
// Include the database connection
require_once "includes/db.php";

// Include the common header (includes HTML head, CSS/JS, navigation)
require_once "includes/header.php";

// Prepare a SQL statement to get the next 5 upcoming events from today onward
// Selecting event id, name, date, and description for displaying brief info
$stmt = $pdo->prepare("SELECT id, name, date, description FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 5");

// Execute the query
$stmt->execute();

// Fetch all events as an associative array
$events = $stmt->fetchAll();
?>

<div class="container">
    <!-- Main welcome heading -->
    <h1>Welcome to EventPortal</h1>

    <p>Explore upcoming events and register to join.</p>

    <?php if (empty($events)): ?>
        <!-- No upcoming events message -->
        <p>No upcoming events at this time. Check back later!</p>
    <?php else: ?>
        <h2>Upcoming Events</h2>
        <ul class="event-list">
            <!-- Loop through each event -->
            <?php foreach ($events as $event): ?>
                <li>
                    <!-- Link to public events page anchored to this event -->
                    <a href="guest/events.php#event-<?php echo $event['id']; ?>">
                        <!-- Escape HTML special chars to prevent XSS -->
                        <strong><?php echo htmlspecialchars($event['name']); ?></strong>
                    </a> 
                    - 
                    <!-- Format the date to a human readable string -->
                    <?php echo date('F j, Y', strtotime($event['date'])); ?>
                    <p>
                        <!-- Show a shortened description snippet with ellipsis -->
                        <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>...
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
// Include the common footer to close page structure
require_once "includes/footer.php";
?>
