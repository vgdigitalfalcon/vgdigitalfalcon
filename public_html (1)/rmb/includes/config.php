<?php
// ============================================
// Database Configuration
// Update these values to match your hosting/MySQL setup
// ============================================
define('DB_HOST', '127.0.0.1:3306');
define('DB_USER', 'u670120666_rmb');
define('DB_PASS', 'RMBmember2026');
define('DB_NAME', 'u670120666_rmb');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Base URL of the CRM (used for building upload paths). Adjust if needed.
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');

session_start();
