<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

// Only allow staff access
require_role('staff');

$staff_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch clients and their guest counts
try {
    // Assuming staff can see all clients; if you want to limit to clients assigned to staff, you’d join with a staff_clients table
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email,
            (SELECT COUNT(*) FROM guests g WHERE g.client_id = u.id) AS guest_count
        FROM users u
        JOIN roles r ON u.role_id = r.id
        WHERE r.name = 'client' AND u.status = 'active'
        ORDER BY u.name
    ");
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to fetch clients: " . $e->getMessage();
}

// Handle manual guest addition form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_guest_client_id'], $_POST['guest_name'], $_POST['event_id'])) {
    $client_id = intval($_POST['add_guest_client_id']);
    $guest_name = trim($_POST['guest_name']);
    $event_id = intval($_POST['event_id']);

    if (empty($guest_name)) {
        $error = "Guest name cannot be empty.";
    } else {
        try {
            // Insert guest record
            $stmt = $pdo->prepare("INSERT INTO guests (client_id, event_id, name, checked_in) VALUES (?, ?, ?, 0)");
            $stmt->execute([$client_id, $event_id, $guest_name]);
            $success = "Guest '$guest_name' added for client ID $client_id.";
            // Refresh client list to update guest counts
            $stmt = $pdo->query("
                SELECT u.id, u.name, u.email,
                    (SELECT COUNT(*) FROM guests g WHERE g.client_id = u.id) AS guest_count
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'client' AND u.status = 'active'
                ORDER BY u.name
            ");
            $clients = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = "Failed to add guest: " . $e->getMessage();
        }
    }
}

// Fetch events list for guest addition dropdown
try {
    $stmt = $pdo->query("SELECT id, name, date FROM events ORDER BY date DESC");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to fetch events: " . $e->getMessage();
}

require_once "../includes/header.php";
?>

<div class="container">
    <h1>Manage Clients and Guests</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <h2>Clients List</h2>
    <?php if (empty($clients)): ?>
        <p>No active clients found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Guest Count</th>
                    <th>Add Guest</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= htmlspecialchars($client['id']) ?></td>
                    <td><?= htmlspecialchars($client['name']) ?></td>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td><?= $client['guest_count'] ?></td>
                    <td>
                        <form method="post" action="" style="display:inline-block;">
                            <input type="hidden" name="add_guest_client_id" value="<?= $client['id'] ?>">
                            <label for="guest_name_<?= $client['id'] ?>">Guest Name:</label>
                            <input type="text" name="guest_name" id="guest_name_<?= $client['id'] ?>" required>
                            <label for="event_id_<?= $client['id'] ?>">Event:</label>
                            <select name="event_id" id="event_id_<?= $client['id'] ?>" required>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>">
                                        <?= htmlspecialchars($event['name']) ?> (<?= date('F j, Y', strtotime($event['date'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Add Guest</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once "../includes/footer.php"; ?>
