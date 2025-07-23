<?php
// Include database connection and authentication helper files
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once "../includes/functions.php";

// Enforce that only logged-in admins can access this page
require_admin();

try {
    // Fetch total number of events in the system
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    $total_events = $stmt->fetchColumn();

    // Fetch total number of active clients
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.name = 'client' AND u.status = 'active'
    ");
    $stmt->execute();
    $total_clients = $stmt->fetchColumn();

    // Fetch total number of guests registered across all events
    $stmt = $pdo->query("SELECT COUNT(*) FROM guests");
    $total_guests = $stmt->fetchColumn();

    // Calculate attendance rate for clients:
    // Percentage of attendance records marked as attended / total attendance records
    $stmt = $pdo->query("
        SELECT 
            ROUND(
                (SELECT COUNT(*) FROM attendance WHERE attended = TRUE) / NULLIF(COUNT(*),0) * 100, 2
            ) AS client_attendance_rate
        FROM attendance
    ");
    $client_attendance_rate = $stmt->fetchColumn();

    // Calculate attendance rate for guests:
    // Percentage of guests marked as checked-in / total guests
    $stmt = $pdo->query("
        SELECT 
            ROUND(
                (SELECT COUNT(*) FROM guests WHERE checked_in = TRUE) / NULLIF(COUNT(*),0) * 100, 2
            ) AS guest_attendance_rate
        FROM guests
    ");
    $guest_attendance_rate = $stmt->fetchColumn();

} catch (PDOException $e) {
    // If there is a database error, stop execution and display the error message
    die("Error fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Set character encoding and responsive viewport -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Event Portal</title>
    <!-- Link to the selected CSS theme -->
    <link rel="stylesheet" href="/css/theme1.css" />
</head>
<body>

    <!-- Include common header/navigation -->
    <?php include("../includes/header.php"); ?>

    <h1>Admin Dashboard</h1>

    <!-- Summary section showing key statistics -->
    <section>
        <h2>Summary</h2>
        <ul>
            <!-- Display total number of events -->
            <li>Total Events: <?php echo htmlspecialchars($total_events); ?></li>

            <!-- Display total number of active clients -->
            <li>Total Active Clients: <?php echo htmlspecialchars($total_clients); ?></li>

            <!-- Display total guests registered -->
            <li>Total Guests: <?php echo htmlspecialchars($total_guests); ?></li>

            <!-- Display client attendance rate, default 0 if null -->
            <li>Client Attendance Rate: <?php echo htmlspecialchars($client_attendance_rate ?? 0); ?>%</li>

            <!-- Display guest attendance rate, default 0 if null -->
            <li>Guest Attendance Rate: <?php echo htmlspecialchars($guest_attendance_rate ?? 0); ?>%</li>
        </ul>
    </section>

    <!-- System monitoring section with static status info -->
    <section>
        <h2>System Monitoring</h2>
        <p>Server status: <strong>Online</strong></p>
        <p>Database status: <strong>Connected</strong></p>
        <p>QR Code Service: <strong>Operational</strong></p>
    </section>

    <!-- Include common footer -->
