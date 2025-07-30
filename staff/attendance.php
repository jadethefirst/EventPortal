<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";

// Require staff login
require_role('staff');

$staff_id = $_SESSION['user_id'];
$error = "";
$success = "";

// 1. Fetch the list of events this staff member manages, so they can select which event to mark attendance for
try {
    $stmt = $pdo->prepare("
        SELECT e.id, e.name, e.date 
        FROM events e
        JOIN event_staff es ON e.id = es.event_id
        WHERE es.staff_id = ?
        ORDER BY e.date DESC
    ");
    $stmt->execute([$staff_id]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching events: " . $e->getMessage());
}

// 2. Handle event selection and attendance marking form submissions

// Selected event ID (from GET or POST)
$selected_event_id = $_GET['event_id'] ?? $_POST['event_id'] ?? null;

// Attendance marking logic triggers only if an event is selected and form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_event_id) {

    // a) Manual check-in via user ID (client or guest)
    if (isset($_POST['manual_user_type'], $_POST['manual_user_id'])) {
        $user_type = $_POST['manual_user_type']; // 'client' or 'guest'
        $manual_user_id = intval($_POST['manual_user_id']);

        try {
            if ($user_type === 'client') {
                // Mark client attendance checked_in = TRUE for the event
                $stmt = $pdo->prepare("UPDATE attendance SET checked_in = TRUE WHERE event_id = ? AND user_id = ?");
                $stmt->execute([$selected_event_id, $manual_user_id]);
                if ($stmt->rowCount() > 0) {
                    $success = "Client attendance marked successfully.";
                } else {
                    $error = "No attendance record found for that client for this event.";
                }
            } elseif ($user_type === 'guest') {
                // Mark guest attendance checked_in = TRUE for the guest record
                $stmt = $pdo->prepare("UPDATE guests SET checked_in = TRUE WHERE event_id = ? AND id = ?");
                $stmt->execute([$selected_event_id, $manual_user_id]);
                if ($stmt->rowCount() > 0) {
                    $success = "Guest attendance marked successfully.";
                } else {
                    $error = "No guest record found for this event with that ID.";
                }
            } else {
                $error = "Invalid user type specified.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }

    // b) QR code check-in (Assuming QR code string passed and matched to user attendance)
    if (isset($_POST['qr_code']) && !empty(trim($_POST['qr_code']))) {
        $qr_code = trim($_POST['qr_code']);

        try {
            // 1. Check if QR code matches a client attendance record for this event
            $stmt = $pdo->prepare("SELECT user_id FROM attendance WHERE event_id = ? AND qr_code = ?");
            $stmt->execute([$selected_event_id, $qr_code]);
            $client = $stmt->fetch();

            if ($client) {
                // Mark client checked_in
                $stmt = $pdo->prepare("UPDATE attendance SET checked_in = TRUE WHERE event_id = ? AND user_id = ?");
                $stmt->execute([$selected_event_id, $client['user_id']]);
                $success = "Client attendance checked-in via QR code.";
            } else {
                // 2. If not found, check if QR code matches a guest record for this event
                $stmt = $pdo->prepare("SELECT id FROM guests WHERE event_id = ? AND qr_code = ?");
                $stmt->execute([$selected_event_id, $qr_code]);
                $guest = $stmt->fetch();

                if ($guest) {
                    $stmt = $pdo->prepare("UPDATE guests SET checked_in = TRUE WHERE event_id = ? AND id = ?");
                    $stmt->execute([$selected_event_id, $guest['id']]);
                    $success = "Guest attendance checked-in via QR code.";
                } else {
                    $error = "QR code not recognized for this event.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error during QR check-in: " . $e->getMessage();
        }
    }
}

// 3. Fetch attendance lists for the selected event for display (clients + guests)
$clientsAttendance = [];
$guestsList = [];

if ($selected_event_id) {
    try {
        // Clients attendance for event
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, a.checked_in, a.qr_code
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            WHERE a.event_id = ?
            ORDER BY u.name
        ");
        $stmt->execute([$selected_event_id]);
        $clientsAttendance = $stmt->fetchAll();

        // Guests list for event
        $stmt = $pdo->prepare("
            SELECT g.id, g.name, g.checked_in, g.qr_code, u.name AS client_name
            FROM guests g
            JOIN users u ON g.client_id = u.id
            WHERE g.event_id = ?
            ORDER BY u.name, g.name
        ");
        $stmt->execute([$selected_event_id]);
        $guestsList = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error = "Error fetching attendance lists: " . $e->getMessage();
    }
}

require_once "../includes/header.php";
?>

<div class="container">
    <h1>Staff Attendance Management</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Event Selection -->
    <form method="get" action="">
        <label for="eventSelect">Select Event to Manage Attendance:</label>
        <select id="eventSelect" name="event_id" onchange="this.form.submit()" required>
            <option value="">-- Select Event --</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= $event['id'] ?>" <?= ($selected_event_id == $event['id']) ? "selected" : "" ?>>
                    <?= htmlspecialchars($event['name']) ?> (<?= date('F j, Y', strtotime($event['date'])) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($selected_event_id): ?>
        <!-- QR Code Check-in Form -->
        <section>
            <h2>QR Code Check-In</h2>
            <form method="post" action="">
                <input type="hidden" name="event_id" value="<?= htmlspecialchars($selected_event_id) ?>">
                <label for="qr_code">Scan or Enter QR Code:</label><br>
                <input type="text" id="qr_code" name="qr_code" placeholder="Scan or type QR code" required autofocus>
                <button type="submit">Check In</button>
            </form>
        </section>

        <!-- Manual Attendance Check-In -->
        <section>
            <h2>Manual Attendance Check-In</h2>
            <form method="post" action="">
                <input type="hidden" name="event_id" value="<?= htmlspecialchars($selected_event_id) ?>">
                <label for="manual_user_type">User Type:</label>
                <select id="manual_user_type" name="manual_user_type" required>
                    <option value="client">Client</option>
                    <option value="guest">Guest</option>
                </select><br><br>

                <label for="manual_user_id">User ID (Client or Guest ID):</label>
                <input type="number" id="manual_user_id" name="manual_user_id" min="1" required>
                <button type="submit">Mark Checked-In</button>
            </form>
            <small>Use this to manually check-in by entering Client or Guest IDs.</small>
        </section>

        <!-- Attendance Lists -->
        <section>
            <h2>Attendance Lists for <?= htmlspecialchars($events[array_search($selected_event_id, array_column($events, 'id'))]['name']) ?></h2>

            <h3>Clients</h3>
            <?php if (empty($clientsAttendance)): ?>
                <p>No clients registered for this event.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Name</th>
                            <th>Checked In</th>
                            <th>QR Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientsAttendance as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['id']) ?></td>
                                <td><?= htmlspecialchars($client['name']) ?></td>
                                <td><?= $client['checked_in'] ? "Yes" : "No" ?></td>
                                <td>
                                    <?php if ($client['qr_code']): ?>
                                        <img src="../images/qrcodes/<?= htmlspecialchars($client['qr_code']) ?>" alt="QR Code" class="qr-mini">
                                    <?php else: ?>
                                        <em>None</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Guests</h3>
            <?php if (empty($guestsList)): ?>
                <p>No guests registered for this event.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Guest ID</th>
                            <th>Name</th>
                            <th>Client Name</th>
                            <th>Checked In</th>
                            <th>QR Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guestsList as $guest): ?>
                            <tr>
                                <td><?= htmlspecialchars($guest['id']) ?></td>
                                <td><?= htmlspecialchars($guest['name']) ?></td>
                                <td><?= htmlspecialchars($guest['client_name']) ?></td>
                                <td><?= $guest['checked_in'] ? "Yes" : "No" ?></td>
                                <td>
                                    <?php if ($guest['qr_code']): ?>
                                        <img src="../images/qrcodes/<?= htmlspecialchars($guest['qr_code']) ?>" alt="QR Code" class="qr-mini">
                                    <?php else: ?>
                                        <em>None</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php require_once "../includes/footer.php"; ?>
