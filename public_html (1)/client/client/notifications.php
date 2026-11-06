<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login();
if ($user['role'] === 'client') {
    header('Location: dashboard.php');
    exit;
}
if (isset($_GET['read'])) {
    db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$user['id']]);
    header('Location: notifications.php');
    exit;
}
sync_notifications($user);
layout_start('Notifications', $user);
echo '<p><a class="btn btn-blue" href="notifications.php?read=1">Mark all read</a></p>';
$stmt = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 50');
$stmt->execute([$user['id']]);
echo '<div class="card-box"><ul class="mb-0">';
foreach ($stmt->fetchAll() as $n) {
    echo '<li>' . h($n['message']) . ' <small>' . h($n['created_at']) . '</small></li>';
}
echo '</ul></div>';
layout_end();
