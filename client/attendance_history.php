<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/header.php";

require_role('client');

$user_id = $_SESSION['user_id'];

// Fetch past events the client registered for
$stmt = $pdo->prepare("
    SELECT 
        e.id AS event_id,
        e.name AS event_name,
        e.date,
        r.qr_code_token AS client_qr,
        r.checked_in AS client_checked_in,
        g.name AS guest_name,
        g.qr_code_token AS guest_qr,
        g.checked_in AS guest_checked_in
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    LEFT JOIN guests g ON g.registration_id = r.id
    WHERE r.client_id = ? AND e.date <= NOW()
    ORDER BY e.date DESC
");
$stmt->execute([$user_id]);
$records = $stmt->fetchAll(PDO::FETCH_GROUP);
?>

<div class="container">
    <h2>Your Attendance History</h2>

    <?php if (empty($records)): ?>
        <p>No past event records found.</p>
    <?php else: ?>
        <?php foreach ($records as $event_id => $rows): 
            $event = $rows[0];
        ?>
        <div class="event-card">
            <h3><?= htmlspecialchars($event['event_name']) ?>
                <small>(<?= date("F j, Y", strtotime($event['date'])) ?>)</small>
            </h3>

            <p><strong>Your Check-In Status:</strong> 
                <?= $event['client_checked_in'] ? "<span class='checked'>✔ Checked In</span>" : "<span class='not-checked'>✘ Not Checked In</span>" ?>
            </p>

            <p><strong>Your QR Code:</strong></p>
            <?php if (!empty($event['client_qr'])): ?>
                <img src="../images/qrcodes/<?= htmlspecialchars($event['client_qr']) ?>.png" alt="Your QR Code" class="qr-thumbnail">
            <?php else: ?>
                <em>No QR code available</em>
            <?php endif; ?>

            <?php 
                $guest_rows = array_filter($rows, fn($r) => !empty($r['guest_name']));
            ?>
            <?php if (!empty($guest_rows)): ?>
                <h4>Your Guests</h4>
                <ul class="guest-list">
                    <?php foreach ($guest_rows as $guest): ?>
                        <li>
                            <?= htmlspecialchars($guest['guest_name']) ?> – 
                            <?= $guest['guest_checked_in'] ? "<span class='checked'>✔ Checked In</span>" : "<span class='not-checked'>✘ Not Checked In</span>" ?> – 
                            <?php if (!empty($guest['guest_qr'])): ?>
                                <img src="../images/qrcodes/<?= htmlspecialchars($guest['guest_qr']) ?>.png" alt="Guest QR" class="qr-mini">
                            <?php else: ?>
                                <em>No QR</em>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once "../includes/footer.php"; ?>
