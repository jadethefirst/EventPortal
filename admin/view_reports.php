<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

require_admin();

// Example: fetch attendance rates for clients and guests
try {
    // Total events count for context
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $total_events = $stmt->fetchColumn();

    // Guests per event
    $stmt = $pdo->query("
        SELECT 
            e.title,
            COUNT(g.id) AS total_guests,
            SUM(CASE WHEN g.checked_in = TRUE THEN 1 ELSE 0 END) AS guests_checked_in
        FROM events e
        LEFT JOIN guests g ON e.id = g.event_id
        GROUP BY e.id, e.title
        ORDER BY e.event_date DESC
    ");
    $guests_per_event = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attendance rate for clients
    $stmt = $pdo->query("
        SELECT 
            r.name AS role,
            COUNT(u.id) AS user_count,
            SUM(CASE WHEN a.attended = TRUE THEN 1 ELSE 0 END) AS attended_count
        FROM users u
        JOIN roles r ON u.role_id = r.id
        LEFT JOIN attendance a ON u.id = a.user_id
        WHERE r.name IN ('client', 'staff', 'admin')
        GROUP BY r.name
    ");
    $attendance_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching reports: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Reports - Admin</title>
<link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

<?php include("../includes/header.php"); ?>

<h1>Attendance Reports</h1>

<h2>Guests Per Event</h2>
<table border="1" cellpadding="8" cellspacing="0" width="100%">
<thead>
<tr>
    <th>Event Title</th>
    <th>Total Guests</th>
    <th>Guests Checked In</th>
</tr>
</thead>
<tbody>
<?php foreach ($guests_per_event as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row['title']); ?></td>
    <td><?php echo htmlspecialchars($row['total_guests']); ?></td>
    <td><?php echo htmlspecialchars($row['guests_checked_in']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2>Attendance Summary by Role</h2>
<table border="1" cellpadding="8" cellspacing="0" width="100%">
<thead>
<tr>
    <th>Role</th>
    <th>Number of Users</th>
    <th>Number Attended</th>
</tr>
</thead>
<tbody>
<?php foreach ($attendance_summary as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row['role']); ?></td>
    <td><?php echo htmlspecialchars($row['user_count']); ?></td>
    <td><?php echo htmlspecialchars($row['attended_count']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php include("../includes/footer.php"); ?>

</body>
</html>
