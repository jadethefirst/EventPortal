<?php
require_once "../includes/auth.php";
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/header.php";

ensure_role('client');

$userId = $_SESSION['user_id'];
$upcomingEvents = getClientUpcomingEvents($userId);
$guestSummary = getClientGuestAttendanceSummary($userId);
?>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

    <!-- Upcoming Events Section -->
    <section class="card">
        <h2>Upcoming Events</h2>
        <?php if (empty($upcomingEvents)) : ?>
            <p>No upcoming events.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($upcomingEvents as $event): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                        Date: <?php echo htmlspecialchars($event['event_date']); ?><br>
                        Location: <?php echo htmlspecialchars($event['location']); ?><br>
                        Guests Registered: <?php echo htmlspecialchars($event['guest_count']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- Guest Attendance Summary -->
    <section class="card">
        <h2>Guest Attendance Summary</h2>
        <p>Events Attended: <?php echo $guestSummary['events_attended']; ?></p>
        <p>Total Guests Checked-In: <?php echo $guestSummary['guests_checked_in']; ?></p>
        <p>Total Guests Registered: <?php echo $guestSummary['guests_total']; ?></p>
    </section>

    <!-- Quick Links to History & Profile -->
    <section class="card">
        <h2>Quick Access</h2>
        <ul>
            <li><a href="attendance_history.php">📅 View Attendance History</a></li>
            <li><a href="profile.php">👤 Manage Profile</a></li>
        </ul>
    </section>
</div>

<?php require_once "../includes/footer.php"; ?>
