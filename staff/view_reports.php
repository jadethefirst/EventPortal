<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

// Only staff can access this page
require_role('staff');

$staff_id = $_SESSION['user_id'];
$error = "";

// Fetch events for this staff (if applicable, assuming all events for now)
try {
    // If you have a staff_events relation, filter accordingly; else fetch all
    $stmt = $pdo->query("SELECT id, name, date FROM events ORDER BY date DESC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to fetch events: " . $e->getMessage();
}

$selected_event_id = $_GET['event_id'] ?? null;
$attendance_records = [];

if ($selected_event_id) {
    // Fetch attendance data for clients and guests for selected event
    try {
        $stmt = $pdo->prepare("
            SELECT 
                u.id AS client_id, u.name AS client_name,
                a.checked_in AS client_checked_in,
                g.id AS guest_id, g.name AS guest_name, g.checked_in AS guest_checked_in
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN attendance a ON a.user_id = u.id AND a.event_id = ?
            LEFT JOIN guests g ON g.client_id = u.id AND g.event_id = ?
            WHERE r.name = 'client' AND u.status = 'active'
            ORDER BY u.name, g.name
        ");
        $stmt->execute([$selected_event_id, $selected_event_id]);
        $rows = $stmt->fetchAll();

        // Group by client for easier display
        foreach ($rows as $row) {
            $clientId = $row['client_id'];
            if (!isset($attendance_records[$clientId])) {
                $attendance_records[$clientId] = [
                    'client_name' => $row['client_name'],
                    'checked_in' => $row['client_checked_in'],
                    'guests' => [],
                ];
            }
            if ($row['guest_id']) {
                $attendance_records[$clientId]['guests'][] = [
                    'guest_name' => $row['guest_name'],
                    'checked_in' => $row['guest_checked_in'],
                ];
            }
        }
    } catch (PDOException $e) {
        $error = "Failed to fetch attendance records: " . $e->getMessage();
    }
}

require_once "../includes/header.php";
?>

<div class="container">
    <h1>Staff Attendance Reports</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="get" action="">
        <label for="event_id">Select Event:</label>
        <select id="event_id" name="event_id" required>
            <option value="">-- Choose an event --</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= $event['id'] ?>" <?= ($selected_event_id == $event['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($event['name']) ?> (<?= date('F j, Y', strtotime($event['date'])) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Report</button>
    </form>

    <?php if ($selected_event_id && !empty($attendance_records)): ?>
        <h2>Attendance for <?= htmlspecialchars($events[array_search($selected_event_id, array_column($events, 'id'))]['name']) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Checked In</th>
                    <th>Guests</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $client): ?>
                    <tr>
                        <td><?= htmlspecialchars($client['client_name']) ?></td>
                        <td><?= $client['checked_in'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <?php if (!empty($client['guests'])): ?>
                                <ul>
                                <?php foreach ($client['guests'] as $guest): ?>
                                    <li>
                                        <?= htmlspecialchars($guest['guest_name']) ?> - 
                                        <?= $guest['checked_in'] ? 'Checked In' : 'Not Checked In' ?>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                No guests
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($selected_event_id): ?>
        <p>No attendance data found for this event.</p>
    <?php endif; ?>
</div>

<?php require_once "../includes/footer.php"; ?>
