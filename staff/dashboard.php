<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

// Enforce staff login
require_role('staff');

// Get current logged in staff ID
$staff_id = $_SESSION['user_id'];

try {
    // Fetch events assigned/managed by this staff member
    $stmt = $pdo->prepare("
        SELECT e.id, e.name, e.date 
        FROM events e
        JOIN event_staff es ON e.id = es.event_id
        WHERE es.staff_id = ?
        ORDER BY e.date DESC
        LIMIT 10
    ");
    $stmt->execute([$staff_id]);
    $events = $stmt->fetchAll();

    // For each event, get attendance stats (clients and guests)
    $attendanceStats = [];

    foreach ($events as $event) {
        $eventId = $event['id'];

        // Count total clients registered for this event
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $totalClients = $stmt->fetchColumn();

        // Count clients checked in
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE event_id = ? AND checked_in = TRUE");
        $stmt->execute([$eventId]);
        $clientsCheckedIn = $stmt->fetchColumn();

        // Count total guests linked to this event
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $totalGuests = $stmt->fetchColumn();

        // Count guests checked in
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE event_id = ? AND checked_in = TRUE");
        $stmt->execute([$eventId]);
        $guestsCheckedIn = $stmt->fetchColumn();

        $attendanceStats[$eventId] = [
            'total_clients' => $totalClients,
            'clients_checked_in' => $clientsCheckedIn,
            'total_guests' => $totalGuests,
            'guests_checked_in' => $guestsCheckedIn,
        ];
    }

} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

require_once "../includes/header.php";
?>

<div class="container">
    <h1>Staff Dashboard</h1>
    <h2>Your Managed Events</h2>

    <?php if (empty($events)): ?>
        <p>You are not assigned to manage any events currently.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Clients Registered</th>
                    <th>Clients Checked In</th>
                    <th>Guests Registered</th>
                    <th>Guests Checked In</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): 
                    $stats = $attendanceStats[$event['id']];
                ?>
                <tr>
                    <td><?= htmlspecialchars($event['name']) ?></td>
                    <td><?= date('F j, Y', strtotime($event['date'])) ?></td>
                    <td><?= $stats['total_clients'] ?></td>
                    <td><?= $stats['clients_checked_in'] ?></td>
                    <td><?= $stats['total_guests'] ?></td>
                    <td><?= $stats['guests_checked_in'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once "../includes/footer.php"; ?>
