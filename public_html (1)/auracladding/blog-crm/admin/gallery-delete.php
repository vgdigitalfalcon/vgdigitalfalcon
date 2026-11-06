<?php
require_once __DIR__ . '/../config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf'] ?? '';

if (!$id || !verify_csrf($token)) {
    set_flash('danger', 'Invalid request.');
    header('Location: gallery.php');
    exit;
}

$stmt = $pdo->prepare("SELECT image_path FROM gallery_images WHERE project_id = ?");
$stmt->execute([$id]);
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $path) {
    if ($path && file_exists(UPLOAD_PATH . $path)) {
        @unlink(UPLOAD_PATH . $path);
    }
}

$pdo->prepare("DELETE FROM gallery_projects WHERE id = ?")->execute([$id]);
set_flash('success', 'Project deleted.');
header('Location: gallery.php');
exit;
