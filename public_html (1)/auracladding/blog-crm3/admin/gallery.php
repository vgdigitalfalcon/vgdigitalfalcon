<?php
/**
 * Gallery / Our Works – Project List Dashboard
 * Completely separate from Blog posts
 */
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'Gallery Projects';
$page_subtitle = 'Manage projects shown on the Our Works page';

$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

$where = ["1=1"];
$params = [];

if ($status && in_array($status, ['draft', 'published'])) {
    $where[] = "status = ?";
    $params[] = $status;
}
if ($search) {
    $where[] = "(title LIKE ? OR location LIKE ? OR area LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_projects WHERE $where_sql");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM gallery_images gi WHERE gi.project_id = p.id) AS image_count,
        (SELECT image_path FROM gallery_images gi WHERE gi.project_id = p.id ORDER BY gi.sort_order ASC, gi.id ASC LIMIT 1) AS thumb
        FROM gallery_projects p
        WHERE $where_sql
        ORDER BY p.sort_order ASC, p.updated_at DESC
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        set_flash('danger', 'Invalid security token.');
    } else {
        $ids = array_map('intval', $_POST['ids'] ?? []);
        if ($_POST['bulk_action'] === 'delete' && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            // Delete image files
            $img_stmt = $pdo->prepare("SELECT image_path FROM gallery_images WHERE project_id IN ($placeholders)");
            $img_stmt->execute($ids);
            foreach ($img_stmt->fetchAll(PDO::FETCH_COLUMN) as $path) {
                if ($path && file_exists(UPLOAD_PATH . $path)) @unlink(UPLOAD_PATH . $path);
            }
            $pdo->prepare("DELETE FROM gallery_projects WHERE id IN ($placeholders)")->execute($ids);
            set_flash('success', count($ids) . ' project(s) deleted.');
        } elseif ($_POST['bulk_action'] === 'publish' && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("UPDATE gallery_projects SET status = 'published' WHERE id IN ($placeholders)")->execute($ids);
            set_flash('success', count($ids) . ' project(s) published.');
        } elseif ($_POST['bulk_action'] === 'draft' && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("UPDATE gallery_projects SET status = 'draft' WHERE id IN ($placeholders)")->execute($ids);
            set_flash('success', count($ids) . ' project(s) set to draft.');
        }
        header('Location: gallery.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-body" style="padding:16px 24px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" class="form-control" placeholder="Search projects..." value="<?= e($search) ?>" style="max-width:240px;">
            <select name="status" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                <option value="published" <?= $status==='published'?'selected':'' ?>>Published</option>
                <option value="draft" <?= $status==='draft'?'selected':'' ?>>Draft</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($search || $status): ?>
            <a href="gallery.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
            <div style="margin-left:auto;">
                <a href="gallery-edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Project</a>
            </div>
        </form>
    </div>
</div>

<form method="POST">
    <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3><i class="fas fa-th" style="margin-right:8px;color:var(--orange);"></i> Projects (<?= $total ?>)</h3>
            <div style="display:flex;gap:8px;align-items:center;">
                <select name="bulk_action" class="form-select" style="max-width:140px;">
                    <option value="">Bulk Action</option>
                    <option value="publish">Publish</option>
                    <option value="draft">Set Draft</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Apply bulk action to selected?')">Apply</button>
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($projects)): ?>
            <div style="padding:60px 20px;text-align:center;color:#94A3B8;">
                <i class="fas fa-images" style="font-size:48px;opacity:0.4;margin-bottom:16px;"></i>
                <p>No projects yet. <a href="gallery-edit.php">Add your first project</a>.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="check-all" onclick="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
                            <th style="width:70px;">Image</th>
                            <th>Project Title</th>
                            <th>Location</th>
                            <th>Area</th>
                            <th>Materials</th>
                            <th>Photos</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p):
                            $mats = [];
                            if (!empty($p['materials'])) {
                                $mats = is_string($p['materials']) ? json_decode($p['materials'], true) : $p['materials'];
                                if (!is_array($mats)) $mats = [];
                            }
                            $thumb = $p['thumb'] ? upload_url($p['thumb']) : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=120&h=80&fit=crop';
                        ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?= $p['id'] ?>" class="row-check"></td>
                            <td>
                                <img src="<?= e($thumb) ?>" alt="" style="width:56px;height:40px;object-fit:cover;border-radius:6px;">
                            </td>
                            <td>
                                <strong><?= e($p['title']) ?></strong>
                                <?php if ($p['is_featured']): ?>
                                <span style="font-size:11px;background:#FEF3C7;color:#92400E;padding:2px 6px;border-radius:4px;margin-left:6px;">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($p['location'] ?: '—') ?></td>
                            <td><?= e($p['area'] ?: '—') ?></td>
                            <td>
                                <?php if ($mats): ?>
                                    <?php foreach ($mats as $m): ?>
                                    <span style="font-size:11px;background:#F1F5F9;padding:2px 8px;border-radius:4px;margin:1px;display:inline-block;"><?= e($m) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$p['image_count'] ?></td>
                            <td>
                                <?php if ($p['status'] === 'published'): ?>
                                <span class="badge badge-success">Published</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$p['sort_order'] ?></td>
                            <td>
                                <a href="gallery-edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="gallery-delete.php?id=<?= $p['id'] ?>&csrf=<?= e(generate_csrf()) ?>" class="btn btn-outline btn-sm" style="color:#EF4444;" title="Delete" onclick="return confirm('Delete this project and all its images?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="card-footer" style="padding:16px 24px;display:flex;justify-content:center;gap:8px;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="btn btn-primary btn-sm"><?= $i ?></span>
                <?php else: ?>
                <a href="?page=<?= $i ?>&status=<?= e($status) ?>&q=<?= e(urlencode($search)) ?>" class="btn btn-outline btn-sm"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
