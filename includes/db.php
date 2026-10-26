<?php
// Enable strict error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("localhost", "root", "", "exam_system");
$conn->set_charset("utf8mb4");

// Start secure session
session_start();

// Regenerate session ID to prevent fixation (call after login)
function regenerate_session() {
    session_regenerate_id(true);
}

// CSRF token management
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Output encoding
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>