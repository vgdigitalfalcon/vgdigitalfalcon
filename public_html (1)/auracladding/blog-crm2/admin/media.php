<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'Media Library';
$page_subtitle = 'Uploaded images for your blogs';

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file']) && verify_csrf($_POST['csrf'] ?? '')) {
    $file = $_FILES['media_file'];
    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= MAX_IMAGE_SIZE) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (in_array($mime, ALLOWED_IMAGE_TYPES)) {
            $ext = match($mime) {
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', default => 'jpg'
            };
            $filename = 'media_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            
            if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) {
                $size = getimagesize(UPLOAD_PATH . $filename);
                $pdo->prepare("INSERT INTO media (filename, original_name, file_path, mime_type, file_size, width, height, alt_text, uploaded_by) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([
                        $filename, $file['name'], $filename, $mime, $file['size'],
                        $size[0] ?? null, $size[1] ?? null,
                        trim($_POST['alt_text'] ?? ''), $_SESSION['user_id']
                    ]);
                set_flash('success', 'Image uploaded successfully.');
            }
        } else {
            set_flash('danger', 'Invalid file type.');
        }
    }
    header('Location: media.php');
    exit;
}

// Delete
if (isset($_GET['delete']) && verify_csrf($_GET['csrf'] ?? '')) {
    $mid = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT file_path FROM media WHERE id = ?");
    $stmt->execute([$mid]);
    $m = $stmt->fetch();
    if ($m) {
        if (file_exists(UPLOAD_PATH . $m['file_path'])) @unlink(UPLOAD_PATH . $m['file_path']);
        $pdo->prepare("DELETE FROM media WHERE id = ?")->execute([$mid]);
        set_flash('success', 'Media deleted.');
    }
    header('Location: media.php');
    exit;
}

$media = $pdo->query("SELECT m.*, u.full_name as uploader FROM media m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY m.created_at DESC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div style="display:grid; grid-template-columns: 1fr 320px; gap:24px;">
    <div class="card">
        <div class="card-header"><h3>Uploaded Media (<?= count($media) ?>)</h3></div>
        <div class="card-body">
            <?php if (empty($media)): ?>
            <div class="empty-state"><div class="icon"><i class="fas fa-images"></i></div><p>No media uploaded yet.</p></div>
            <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:16px;">
                <?php foreach ($media as $m): ?>
                <div style="border:1px solid var(--gray-200); border-radius:8px; overflow:hidden; background:white;">
                    <img src="<?= e(UPLOAD_URL . $m['file_path']) ?>" alt="<?= e($m['alt_text']) ?>" 
                         style="width:100%; height:120px; object-fit:cover; display:block;">
                    <div style="padding:10px;">
                        <div style="font-size:12px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($m['original_name']) ?></div>
                        <div style="font-size:11px; color:#94A3B8; margin-top:2px;"><?= $m['width'] ?>×<?= $m['height'] ?> • <?= round($m['file_size']/1024) ?>KB</div>
                        <div style="margin-top:8px; display:flex; gap:6px;">
                            <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px;font-size:11px;" 
                                    onclick="navigator.clipboard.writeText('<?= e(UPLOAD_URL . $m['file_path']) ?>'); this.textContent='Copied!';">Copy URL</button>
                            <a href="?delete=<?= $m['id'] ?>&csrf=<?= e(generate_csrf()) ?>" class="btn-icon danger" style="width:28px;height:28px;" onclick="return confirm('Delete?')"><i class="fas fa-trash" style="font-size:11px;"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Upload New</h3></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
                <div class="form-group">
                    <label class="form-label">Image File</label>
                    <input type="file" name="media_file" class="form-control" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label class="form-label">ALT Text</label>
                    <input type="text" name="alt_text" class="form-control" placeholder="Describe the image">
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-upload"></i> Upload</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
