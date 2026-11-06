<?php
require_once 'includes/config.php';
require_once 'includes/auth_check.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("SELECT profile_picture FROM members WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if ($member) {
        if ($member['profile_picture'] && file_exists(UPLOAD_DIR . $member['profile_picture'])) {
            unlink(UPLOAD_DIR . $member['profile_picture']);
        }
        $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

header('Location: dashboard.php?msg=' . urlencode('Member deleted.'));
exit;
