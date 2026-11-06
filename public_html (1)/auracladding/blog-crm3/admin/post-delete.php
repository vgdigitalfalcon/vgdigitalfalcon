<?php
require_once __DIR__ . '/../config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$csrf = $_GET['csrf'] ?? '';

if (!$id || !verify_csrf($csrf)) {
    set_flash('danger', 'Invalid request.');
    header('Location: posts.php');
    exit;
}

$stmt = $pdo->prepare("SELECT featured_image, social_image FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if ($post) {
    // Delete images
    if ($post['featured_image'] && file_exists(UPLOAD_PATH . $post['featured_image'])) {
        @unlink(UPLOAD_PATH . $post['featured_image']);
    }
    if ($post['social_image'] && file_exists(UPLOAD_PATH . $post['social_image'])) {
        @unlink(UPLOAD_PATH . $post['social_image']);
    }
    
    // Delete post images
    $imgs = $pdo->prepare("SELECT image_path FROM post_images WHERE post_id = ?");
    $imgs->execute([$id]);
    foreach ($imgs->fetchAll() as $img) {
        if (file_exists(UPLOAD_PATH . $img['image_path'])) {
            @unlink(UPLOAD_PATH . $img['image_path']);
        }
    }
    
    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    
    regenerate_sitemap();
    
    $pdo->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?,?,?,?,?)")
        ->execute([$_SESSION['user_id'], 'delete_post', 'post', $id, $_SERVER['REMOTE_ADDR'] ?? '']);
    
    set_flash('success', 'Blog post deleted and sitemap updated.');
} else {
    set_flash('danger', 'Post not found.');
}

header('Location: posts.php');
exit;
