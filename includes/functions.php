<?php
require_once "db.php";

/**
 * ========== USER FUNCTIONS ==========
 */

/**
 * Get role ID by role name.
 */
function getRoleId(PDO $pdo, string $roleName): ?int {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
    $stmt->execute([$roleName]);
    $roleId = $stmt->fetchColumn();
    return $roleId !== false ? (int)$roleId : null;
}

/**
 * Check if username or email exists in users table.
 */
function userExists(PDO $pdo, string $username, string $email): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Add a new user (client, staff, admin).
 */
function addUser(PDO $pdo, string $username, string $email, string $password, string $roleName, string $status = 'pending'): bool {
    $roleId = getRoleId($pdo, $roleName);
    if ($roleId === null) return false;

    if (userExists($pdo, $username, $email)) return false;

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role_id, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    return $stmt->execute([$username, $email, $passwordHash, $roleId, $status]);
}

/**
 * Fetch users by role and status (optional).
 */
function fetchUsers(PDO $pdo, string $roleName, ?string $status = null): array {
    $query = "SELECT u.id, u.username, u.email, u.status, u.created_at 
              FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = ?";
    $params = [$roleName];
    if ($status !== null) {
        $query .= " AND u.status = ?";
        $params[] = $status;
    }
    $query .= " ORDER BY u.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update user status.
 */
function updateUserStatus(PDO $pdo, int $userId, string $status): bool {
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $userId]);
}

/**
 * Delete a user.
 */
function deleteUser(PDO $pdo, int $userId): bool {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$userId]);
}

/**
 * Get count of guests associated with a client.
 */
function getGuestCountByClient(PDO $pdo, int $clientId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE client_id = ?");
    $stmt->execute([$clientId]);
    return (int)$stmt->fetchColumn();
}

/**
 * ========== EVENT FUNCTIONS ==========
 */

/**
 * Fetch all events with optional filters.
 */
function fetchEvents(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY start_date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get event by ID.
 */
function getEventById(PDO $pdo, int $eventId): ?array {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    return $event ?: null;
}

/**
 * Add or update event.
 * If $eventId is null, add new event; otherwise update existing.
 */
function saveEvent(PDO $pdo, ?int $eventId, array $data): bool {
    if ($eventId === null) {
        // Insert new event
        $stmt = $pdo->prepare("INSERT INTO events (name, description, start_date, end_date, guests_allowed, max_guests_per_client) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['start_date'],
            $data['end_date'],
            $data['guests_allowed'],
            $data['max_guests_per_client']
        ]);
    } else {
        // Update existing event
        $stmt = $pdo->prepare("UPDATE events SET name = ?, description = ?, start_date = ?, end_date = ?, guests_allowed = ?, max_guests_per_client = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['start_date'],
            $data['end_date'],
            $data['guests_allowed'],
            $data['max_guests_per_client'],
            $eventId
        ]);
    }
}

/**
 * Delete event by ID.
 */
function deleteEvent(PDO $pdo, int $eventId): bool {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    return $stmt->execute([$eventId]);
}

/**
 * Fetch guests associated with an event.
 */
function fetchGuestsByEvent(PDO $pdo, int $eventId): array {
    $stmt = $pdo->prepare("
        SELECT g.*, u.username AS client_username 
        FROM guests g 
        LEFT JOIN users u ON g.client_id = u.id
        WHERE g.event_id = ?
        ORDER BY g.checked_in DESC, g.name ASC
    ");
    $stmt->execute([$eventId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * ========== ATTENDANCE FUNCTIONS ==========
 */

/**
 * Get attendance stats for clients.
 */
function getClientAttendanceStats(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) AS total,
            SUM(attended) AS attended
        FROM attendance
    ");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: ['total' => 0, 'attended' => 0];
}

/**
 * Get attendance stats for guests.
 */
function getGuestAttendanceStats(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN checked_in = TRUE THEN 1 ELSE 0 END) AS checked_in
        FROM guests
    ");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: ['total' => 0, 'checked_in' => 0];
}

/**
 * Get attendance rate % safely.
 */
function getAttendanceRate(int $attended, int $total): float {
    if ($total === 0) return 0.0;
    return round(($attended / $total) * 100, 2);
}

/**
 * ========== QR CODE FUNCTIONS ==========
 * (Assuming you store QR scan logs in qr_scans table)
 */

/**
 * Get QR scan success/failure counts.
 */
function getQrScanStats(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN success = TRUE THEN 1 ELSE 0 END) AS success_count,
            SUM(CASE WHEN success = FALSE THEN 1 ELSE 0 END) AS failure_count
        FROM qr_scans
    ");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: ['success_count' => 0, 'failure_count' => 0];
}

/**
 * ========== SETTINGS FUNCTIONS ==========
 */

/**
 * Get site-wide setting by key.
 */
function getSetting(PDO $pdo, string $key): ?string {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ? LIMIT 1");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? $value : null;
}

/**
 * Update or insert site-wide setting.
 */
function saveSetting(PDO $pdo, string $key, string $value): bool {
    // Check if exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $exists = $stmt->fetchColumn() > 0;

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
        return $stmt->execute([$value, $key]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
        return $stmt->execute([$key, $value]);
    }
}

/**
 * ========== REPORT FUNCTIONS ==========
 */

/**
 * Get guests per event report.
 */
function getGuestsPerEvent(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT e.id AS event_id, e.name AS event_name, COUNT(g.id) AS guest_count
        FROM events e
        LEFT JOIN guests g ON e.id = g.event_id
        GROUP BY e.id
        ORDER BY guest_count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get guests per client report.
 */
function getGuestsPerClient(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT u.id AS client_id, u.username, COUNT(g.id) AS guest_count
        FROM users u
        LEFT JOIN guests g ON u.id = g.client_id
        JOIN roles r ON u.role_id = r.id
        WHERE r.name = 'client'
        GROUP BY u.id
        ORDER BY guest_count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get attendance rates including guests and clients.
 */
function getAttendanceRates(PDO $pdo): array {
    $clientStats = getClientAttendanceStats($pdo);
    $guestStats = getGuestAttendanceStats($pdo);

    return [
        'client_attendance_rate' => getAttendanceRate((int)$clientStats['attended'], (int)$clientStats['total']),
        'guest_attendance_rate' => getAttendanceRate((int)$guestStats['checked_in'], (int)$guestStats['total'])
    ];
}

/**
 * Get guests checked-in vs not checked-in counts.
 */
function getGuestCheckInStatus(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT
            SUM(CASE WHEN checked_in = TRUE THEN 1 ELSE 0 END) AS checked_in_count,
            SUM(CASE WHEN checked_in = FALSE THEN 1 ELSE 0 END) AS not_checked_in_count
        FROM guests
    ");
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['checked_in_count' => 0, 'not_checked_in_count' => 0];
}

/**
 * SUPPORTING FUNCTIONS
 */
function generate_token($length = 16) {
    return bin2hex(random_bytes($length));
}

