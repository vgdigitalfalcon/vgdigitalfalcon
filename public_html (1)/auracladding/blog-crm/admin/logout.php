<?php
require_once __DIR__ . '/../config.php';

if (is_logged_in()) {
    $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'logout', ?)")
        ->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? '']);
}

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
