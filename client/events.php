<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/header.php";

require_role('client');

$user_id = $_SESSION['user_id'];

// Fetch upcoming events
$stmt = $pdo->prepare("
    SELECT e.id, e.name, e.date, e.description, e.allow_guests, e.max_guests,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.client_id = ?) AS already_registered
    FROM events e
    WHERE e.date >= CURDATE()
    ORDER BY e.date ASC
");
$stmt->execute([$user_id]);
$events = $stmt->fetchAll();
?>

<div class="container">
    <h2>Upcoming Events</h2>

    <?php if (empty($events)): ?>
        <p>No upcoming events found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Guests Allowed</th>
                    <th>Your Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['name']) ?></td>
                        <td><?= date("F j, Y", strtotime($event['date'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($event['description'])) ?></td>
                        <td>
                            <?= $event['allow_guests'] ? "Yes (Max: " . intval($event['max_guests']) . ")" : "No" ?>
                        </td>
                        <td>
                            <?= $event['already_registered'] ? "<span style='color:green;'>Registered</span>" : "<span style='color:red;'>Not registered</span>" ?>
                        </td>
                        <td>
                            <?php if (!$event['already_registered']): ?>
                                <a href="event_register.php?event_id=<?= $event['id'] ?>" class="btn">Register</a>
                            <?php else: ?>
                                <a href="attendance_history.php" class="btn btn-secondary">View Tickets</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once "../includes/footer.php"; ?>
