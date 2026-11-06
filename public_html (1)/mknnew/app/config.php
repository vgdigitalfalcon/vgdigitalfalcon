<?php
// Database Configuration - Edit these values
define('DB_HOST', 'localhost');
define('DB_USER', 'u670120666_newdb');
define('DB_PASS', 'newdb@ABD123');
define('DB_NAME', 'u670120666_newdb');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<div style='padding:20px;background:#fee;color:#c00;font-family:sans-serif;'>
        <h2>Database Connection Failed</h2>
        <p>" . $conn->connect_error . "</p>
        <p>Please run <strong>setup.php</strong> first to create the database.</p>
    </div>");
}
$conn->set_charset("utf8mb4");

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(trim($val));
}
?>
