<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/header.php";

require_role('client');

$client_id = $_SESSION['user_id'];

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    exit("Invalid event ID.");
}

$event_id = intval($_GET['event_id']);

// Check if client already registered
$stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE client_id = ? AND event_id = ?");
$stmt->execute([$client_id, $event_id]);
if ($stmt->fetchColumn() > 0) {
    exit("You are already registered for this event.");
}

// Get event details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    exit("Event not found.");
}

$max_guests = $event['allow_guests'] ? (int)$event['max_guests'] : 0;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_guests = min((int)($_POST['num_guests'] ?? 0), $max_guests);
    $guest_names = $_POST['guest_names'] ?? [];

    // Insert main registration
    $registration_token = generate_token(); // Custom function
    $stmt = $pdo->prepare("INSERT INTO registrations (client_id, event_id, qr_code_token) VALUES (?, ?, ?)");
    $stmt->execute([$client_id, $event_id, $registration_token]);
    $registration_id = $pdo->lastInsertId();

    // Insert guests
    for ($i = 0; $i < $num_guests; $i++) {
        $name = trim($guest_names[$i] ?? "");
        $guest_token = generate_token();
        $stmt = $pdo->prepare("INSERT INTO guests (registration_id, name, qr_code_token) VALUES (?, ?, ?)");
        $stmt->execute([$registration_id, $name, $guest_token]);
    }

    // Redirect to confirmation
    header("Location: registration_confirm.php?registration_id=" . $registration_id);
    exit;
}
?>

<div class="container">
    <h2>Register for: <?= htmlspecialchars($event['name']) ?></h2>
    <p><strong>Date:</strong> <?= date("F j, Y", strtotime($event['date'])) ?></p>
    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>

    <form method="POST">
        <?php if ($max_guests > 0): ?>
            <label for="num_guests">How many guests will you bring? (Max: <?= $max_guests ?>)</label>
            <input type="number" id="num_guests" name="num_guests" min="0" max="<?= $max_guests ?>" value="0" onchange="renderGuestFields(this.value)" required>

            <div id="guest_fields"></div>
        <?php else: ?>
            <p><em>This event does not allow guests.</em></p>
        <?php endif; ?>

        <button type="submit" class="btn">Confirm Registration</button>
    </form>
</div>

<script>
function renderGuestFields(count) {
    let container = document.getElementById('guest_fields');
    container.innerHTML = "";
    count = parseInt(count);
    if (isNaN(count) || count < 1) return;

    for (let i = 0; i < count; i++) {
        container.innerHTML += `
            <label for="guest_${i}">Guest ${i + 1} Name (optional):</label>
            <input type="text" name="guest_names[]" id="guest_${i}" />
        `;
    }
}
</script>

<?php require_once "../includes/footer.php"; ?>
