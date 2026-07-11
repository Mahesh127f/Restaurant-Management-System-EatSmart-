<?php
// EatSmart - Database Configuration
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_USER', 'if0_42386102');
define('DB_PASS', 'my070805');
define('DB_NAME', 'if0_42386102_eatsmart');
define('SITE_NAME', 'EatSmart');
define('SITE_URL', 'https://eatsmart.great-site.net');

// Create connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function isKitchen() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin','kitchen']);
}
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
