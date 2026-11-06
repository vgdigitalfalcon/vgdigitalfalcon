<?php
/**
 * Add / Edit Gallery Project
 * Fields: Title, Location, Area, Materials (multiple), Photos (carousel)
 */
require_once __DIR__ . '/../config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$project = null;
$images = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM gallery_projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    if (!$project) {
        set_flash('danger', 'Project not found.');
        header('Location: gallery.php');
        exit;
    }
    $page_title = 'Edit Project';
    $page_subtitle = $project['title'];

    $img_stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
    $img_stmt->execute([$id]);
    $images = $img_stmt->fetchAll();
} else {
    $page_title = 'New Project';
    $page_subtitle = 'Add a project for the Our Works / Gallery page';
}

$materials = [];
if (!empty($project['materials'])) {
    $materials = is_string($project['materials']) ? json_decode($project['materials'], true) : $project['materials'];
    if (!is_array($materials)) $materials = [];
}

function handle_gallery_upload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'Upload error.'];
    if ($file['size'] > MAX_IMAGE_SIZE) return ['success' => false, 'error' => 'Image too large (max 5MB).'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) return ['success' => false, 'error' => 'Invalid image type.'];
    $ext = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => 'jpg'
    };
    $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
    if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) {
        return ['success' => true, 'filename' => $filename];
    }
    return ['success' => false, 'error' => 'Failed to save file.'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        // Materials (array of strings)
        $mat_raw = $_POST['materials'] ?? [];
        if (!is_array($mat_raw)) $mat_raw = [];
        $materials_clean = [];
        foreach ($mat_raw as $m) {
            $m = trim($m);
            if ($m !== '') $materials_clean[] = $m;
        }
        // Also support single text input with commas as fallback
        if (empty($materials_clean) && !empty($_POST['materials_text'])) {
            foreach (explode(',', $_POST['materials_text']) as $m) {
                $m = trim($m);
                if ($m !== '') $materials_clean[] = $m;
            }
        }
        $materials_json = !empty($materials_clean) ? json_encode($materials_clean, JSON_UNESCAPED_UNICODE) : null;

        if (empty($title)) $errors[] = 'Project Title is required.';
        if (!in_array($status, ['draft', 'published'])) $status = 'draft';

        if (empty($slug)) $slug = slugify($title);
        else $slug = slugify($slug);

        $slug_check = $pdo->prepare("SELECT id FROM gallery_projects WHERE slug = ? AND id != ?");
        $slug_check->execute([$slug, $id]);
        if ($slug_check->fetch()) $slug .= '-' . time();

        if (empty($errors)) {
            if ($is_edit) {
                $pdo->prepare("UPDATE gallery_projects SET title=?, slug=?, location=?, area=?, materials=?, description=?, status=?, sort_order=?, is_featured=? WHERE id=?")
                    ->execute([$title, $slug, $location ?: null, $area ?: null, $materials_json, $description ?: null, $status, $sort_order, $is_featured, $id]);
                $project_id = $id;
            } else {
                $pdo->prepare("INSERT INTO gallery_projects (title, slug, location, area, materials, description, status, sort_order, is_featured) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$title, $slug, $location ?: null, $area ?: null, $materials_json, $description ?: null, $status, $sort_order, $is_featured]);
                $project_id = (int)$pdo->lastInsertId();
            }

            // Delete marked images
            $delete_ids = array_map('intval', $_POST['delete_images'] ?? []);
            if (!empty($delete_ids)) {
                $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
                $del_stmt = $pdo->prepare("SELECT id, image_path FROM gallery_images WHERE id IN ($placeholders) AND project_id = ?");
                $del_stmt->execute([...$delete_ids, $project_id]);
                foreach ($del_stmt->fetchAll() as $row) {
                    if ($row['image_path'] && file_exists(UPLOAD_PATH . $row['image_path'])) {
                        @unlink(UPLOAD_PATH . $row['image_path']);
                    }
                }
                $pdo->prepare("DELETE FROM gallery_images WHERE id IN ($placeholders) AND project_id = ?")
                    ->execute([...$delete_ids, $project_id]);
            }

            // Update alt texts & sort for existing
            if (!empty($_POST['existing_alt']) && is_array($_POST['existing_alt'])) {
                foreach ($_POST['existing_alt'] as $img_id => $alt) {
                    $img_id = (int)$img_id;
                    $sort = (int)($_POST['existing_sort'][$img_id] ?? 0);
                    $pdo->prepare("UPDATE gallery_images SET alt_text = ?, sort_order = ? WHERE id = ? AND project_id = ?")
                        ->execute([trim($alt), $sort, $img_id, $project_id]);
                }
            }

            // New image uploads (multiple)
            if (!empty($_FILES['new_images']['name'][0])) {
                $max_sort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),0) FROM gallery_images WHERE project_id = $project_id")->fetchColumn();
                $file_count = count($_FILES['new_images']['name']);
                for ($i = 0; $i < $file_count; $i++) {
                    if (empty($_FILES['new_images']['name'][$i])) continue;
                    $file = [
                        'name' => $_FILES['new_images']['name'][$i],
                        'type' => $_FILES['new_images']['type'][$i],
                        'tmp_name' => $_FILES['new_images']['tmp_name'][$i],
                        'error' => $_FILES['new_images']['error'][$i],
                        'size' => $_FILES['new_images']['size'][$i],
                    ];
                    $upload = handle_gallery_upload($file);
                    if ($upload['success']) {
                        $max_sort++;
                        $alt = trim($_POST['new_alts'][$i] ?? $title);
                        $pdo->prepare("INSERT INTO gallery_images (project_id, image_path, alt_text, sort_order) VALUES (?,?,?,?)")
                            ->execute([$project_id, $upload['filename'], $alt, $max_sort]);
                    }
                }
            }

            set_flash('success', $is_edit ? 'Project updated successfully.' : 'Project created successfully.');
            header('Location: gallery-edit.php?id=' . $project_id);
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php if ($errors): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?= e(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">

    <div style="display:grid; grid-template-columns: 1fr 320px; gap:24px;">
        <!-- MAIN -->
        <div>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><h3>Project Details</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Project Title *</label>
                        <input type="text" name="title" id="title" class="form-control" value="<?= e($project['title'] ?? '') ?>" required placeholder="e.g. Commercial Office">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" value="<?= e($project['slug'] ?? '') ?>" placeholder="auto-generated-from-title">
                        <div class="form-hint">URL-friendly name. Leave blank to auto-generate.</div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?= e($project['location'] ?? '') ?>" placeholder="e.g. Bangalore, Karnataka">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Area</label>
                            <input type="text" name="area" class="form-control" value="<?= e($project['area'] ?? '') ?>" placeholder="e.g. 45,000 sq.ft.">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Short description of the project..."><?= e($project['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Materials (multiple) -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><h3>Materials <span style="font-weight:400;font-size:13px;color:#94A3B8;">(multiple allowed)</span></h3></div>
                <div class="card-body">
                    <div id="materials-list">
                        <?php if (empty($materials)): ?>
                        <div class="material-row" style="display:flex;gap:8px;margin-bottom:10px;">
                            <input type="text" name="materials[]" class="form-control" placeholder="e.g. Glass & Steel">
                            <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()" style="color:#EF4444;"><i class="fas fa-times"></i></button>
                        </div>
                        <?php else: ?>
                            <?php foreach ($materials as $m): ?>
                            <div class="material-row" style="display:flex;gap:8px;margin-bottom:10px;">
                                <input type="text" name="materials[]" class="form-control" value="<?= e($m) ?>">
                                <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()" style="color:#EF4444;"><i class="fas fa-times"></i></button>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addMaterial()"><i class="fas fa-plus"></i> Add Material</button>
                    <div class="form-hint" style="margin-top:10px;">Examples: Glass & Steel · ACP Cladding · Stone & Wood · Glass & Marble</div>
                </div>
            </div>

            <!-- Photos / Carousel -->
            <div class="card">
                <div class="card-header"><h3>Photos (Carousel)</h3></div>
                <div class="card-body">
                    <?php if ($images): ?>
                    <p style="font-size:13px;color:#64748B;margin-bottom:14px;">Existing photos — reorder with Sort, edit ALT, or mark for deletion.</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
                        <?php foreach ($images as $img): ?>
                        <div style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden;background:#F8FAFC;">
                            <div style="height:110px;overflow:hidden;">
                                <img src="<?= e(upload_url($img['image_path'])) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <div style="padding:10px;">
                                <input type="text" name="existing_alt[<?= $img['id'] ?>]" class="form-control" value="<?= e($img['alt_text'] ?? '') ?>" placeholder="ALT text" style="font-size:12px;margin-bottom:6px;">
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <input type="number" name="existing_sort[<?= $img['id'] ?>]" class="form-control" value="<?= (int)$img['sort_order'] ?>" style="width:60px;font-size:12px;" title="Sort order">
                                    <label style="font-size:12px;color:#EF4444;cursor:pointer;display:flex;align-items:center;gap:4px;margin:0;">
                                        <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <label class="form-label">Add New Photos</label>
                    <input type="file" name="new_images[]" class="form-control" accept="image/*" multiple id="new_images_input">
                    <div class="form-hint">Select multiple images. Recommended: 1200×800 or larger, JPG/WebP under 500 KB each. First image appears as the main card image; all images form the carousel on the card.</div>
                    <div id="new-previews" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:12px;"></div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><h3>Publish</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= ($project['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= ($project['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= (int)($project['sort_order'] ?? 0) ?>" min="0">
                        <div class="form-hint">Lower numbers appear first on the Works page.</div>
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
                        <label class="form-label" style="margin:0;">Featured</label>
                        <label class="switch">
                            <input type="checkbox" name="is_featured" value="1" <?= !empty($project['is_featured']) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> <?= $is_edit ? 'Update Project' : 'Create Project' ?>
                    </button>
                    <a href="gallery.php" class="btn btn-outline btn-block" style="margin-top:10px;">← Back to Gallery</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Tips</h3></div>
                <div class="card-body" style="font-size:13px;color:#64748B;line-height:1.6;">
                    <p style="margin-bottom:10px;"><strong>Carousel:</strong> Upload 2–6 photos per project. Visitors can swipe/click through them on the Works page.</p>
                    <p style="margin-bottom:10px;"><strong>Materials:</strong> Add as many as needed (Glass & Steel, ACP, Stone, etc.).</p>
                    <p style="margin:0;"><strong>Live page:</strong> Only <em>Published</em> projects appear on the public Our Works page.</p>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    let slugManual = false;
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugManual) slugInput.value = generateSlug(this.value);
        });
        slugInput.addEventListener('input', () => slugManual = true);
    }

    // New image previews
    const input = document.getElementById('new_images_input');
    if (input) {
        input.addEventListener('change', function() {
            const box = document.getElementById('new-previews');
            box.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement('div');
                    div.style.cssText = 'width:100px;height:70px;border-radius:8px;overflow:hidden;border:1px solid #E2E8F0;';
                    div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
                    box.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});

function addMaterial() {
    const list = document.getElementById('materials-list');
    const div = document.createElement('div');
    div.className = 'material-row';
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:10px;';
    div.innerHTML = `
        <input type="text" name="materials[]" class="form-control" placeholder="e.g. Glass & Steel">
        <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()" style="color:#EF4444;"><i class="fas fa-times"></i></button>`;
    list.appendChild(div);
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
