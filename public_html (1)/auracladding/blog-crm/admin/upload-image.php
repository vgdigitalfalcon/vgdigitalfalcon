<?php
/**
 * TinyMCE Image Upload Handler
 * Returns JSON for editor insertion
 */
require_once __DIR__ . '/../config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Upload error']);
    exit;
}

if ($file['size'] > MAX_IMAGE_SIZE) {
    echo json_encode(['error' => 'File too large (max 5MB)']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
    echo json_encode(['error' => 'Invalid image type']);
    exit;
}

$ext = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    default => 'jpg'
};

$filename = 'content_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) {
    // Optional: save to media library
    $size = @getimagesize(UPLOAD_PATH . $filename);
    try {
        $pdo->prepare("INSERT INTO media (filename, original_name, file_path, mime_type, file_size, width, height, uploaded_by) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$filename, $file['name'], $filename, $mime, $file['size'], $size[0] ?? null, $size[1] ?? null, $_SESSION['user_id']]);
    } catch (Exception $e) {}
    
    echo json_encode([
        'location' => UPLOAD_URL . $filename
    ]);
} else {
    echo json_encode(['error' => 'Failed to save file']);
}
