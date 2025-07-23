<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

require_admin();

// Handle CRUD actions here (simplified for example)
// e.g., create, update, delete events (omitted for brevity)

// Fetch events and guest settings for each
try {
    $sql = "
        SELECT 
            e.id,
            e.title,
            e.event_date,
            e.location,
            e.event_type,
            e.allow_guests,       -- boolean field to enable guest registration
            e.max_guests_per_client -- max guests per client allowed
        FROM events e
        ORDER BY e.event_date DESC
    ";
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching events: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Events - Admin</title>
<link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

<?php include("../includes/header.php"); ?>

<h1>Manage Events</h1>

<!-- Button or link to create new event -->
<p><a href="create_event.php">Add New Event</a></p>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Location</th>
            <th>Type</th>
            <th>Guests Allowed</th>
            <th>Max Guests per Client</th>
            <th>Guests List</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $event): ?>
        <tr>
            <td><?php echo htmlspecialchars($event['title']); ?></td>
            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
            <td><?php echo htmlspecialchars($event['location']); ?></td>
            <td><?php echo htmlspecialchars($event['event_type']); ?></td>
            <td><?php echo $event['allow_guests'] ? 'Yes' : 'No'; ?></td>
            <td><?php echo htmlspecialchars($event['max_guests_per_client']); ?></td>
            <td>
                <!-- Link to view/edit guests of this event -->
                <a href="event_guests.php?event_id=<?php echo $event['id']; ?>">View Guests</a>
            </td>
            <td>
                <a href="edit_event.php?id=<?php echo $event['id']; ?>">Edit</a> | 
                <a href="delete_event.php?id=<?php echo $event['id']; ?>" onclick="return confirm('Delete this event?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include("../includes/footer.php"); ?>

</body>
</html>
