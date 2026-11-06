<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('DELETE FROM submissions WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: dashboard.php');
exit;
