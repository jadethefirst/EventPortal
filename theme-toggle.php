<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? null;
    $dark_mode = isset($_POST['dark_mode']) ? filter_var($_POST['dark_mode'], FILTER_VALIDATE_BOOLEAN) : null;

    // Validate theme string (allow only known themes)
    $allowedThemes = ['spring', 'summer', 'autumn', 'winter'];
    if (in_array($theme, $allowedThemes, true)) {
        $_SESSION['theme'] = $theme;
    }
    if ($dark_mode !== null) {
        $_SESSION['dark_mode'] = $dark_mode;
    }

    echo json_encode(['status' => 'success']);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
