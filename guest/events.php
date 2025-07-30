<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/header.php";

// Fetch upcoming events ordered by date
try {
    $stmt = $pdo->query("SELECT id, name, date, max_guests_per_client, allow_guests FROM events WHERE date >= CURDATE() ORDER BY date ASC");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error fetching events: " . $e->getMessage());
}
?>

<div class="container">
    <h2>Upcoming Events</h2>

    <?php if (empty($events)): ?>
        <p>No upcoming events at this time. Please check back later.</p>
    <?php else: ?>
        <ul class="event-list">
            <?php foreach ($events as $event): ?>
                <li>
                    <h3><?= htmlspecialchars($event['name']) ?></h3>
                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($event['date'])) ?></p>
                    <p><strong>Guests Allowed:</strong> <?= $event['allow_guests'] ? "Yes" : "No" ?></p>
                    <?php if ($event['allow_guests']): ?>
                        <p><strong>Max Guests per Client:</strong> <?= intval($event['max_guests_per_client']) ?></p>
                    <?php endif; ?>
                    <a href="/register.php">Sign Up to Attend</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>
