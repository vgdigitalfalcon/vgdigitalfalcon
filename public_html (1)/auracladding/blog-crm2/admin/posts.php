<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'All Blog Posts';
$page_subtitle = 'Manage your published and draft articles';

// Filters
$status = $_GET['status'] ?? '';
$category = (int)($_GET['category'] ?? 0);
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = POSTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$where = ["1=1"];
$params = [];

if ($status && in_array($status, ['draft','published','scheduled','archived'])) {
    $where[] = "p.status = ?";
    $params[] = $status;
}
if ($category > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}
if ($search) {
    $where[] = "(p.title LIKE ? OR p.content LIKE ? OR p.meta_keywords LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where);

// Count
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM posts p WHERE $where_sql");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

// Fetch
$sql = "SELECT p.*, c.name as category_name, u.full_name as author_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.author_id = u.id 
        WHERE $where_sql 
        ORDER BY p.updated_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Categories for filter
$cats = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        set_flash('danger', 'Invalid security token.');
    } else {
        $ids = $_POST['ids'] ?? [];
        if ($_POST['bulk_action'] === 'delete' && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM posts WHERE id IN ($placeholders)")->execute($ids);
            regenerate_sitemap();
            set_flash('success', count($ids) . ' post(s) deleted and sitemap updated.');
        } elseif ($_POST['bulk_action'] === 'publish' && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("UPDATE posts SET status = 'published', published_at = COALESCE(published_at, NOW()) WHERE id IN ($placeholders)")->execute($ids);
            regenerate_sitemap();
            set_flash('success', count($ids) . ' post(s) published.');
        }
        header('Location: posts.php');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="card" style="margin-bottom:24px;">
    <div class="card-body" style="padding:16px 24px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="q" class="form-control" placeholder="Search blogs..." value="<?= e($search) ?>" style="max-width:240px;">
            <select name="status" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                <option value="published" <?= $status==='published'?'selected':'' ?>>Published</option>
                <option value="draft" <?= $status==='draft'?'selected':'' ?>>Draft</option>
                <option value="scheduled" <?= $status==='scheduled'?'selected':'' ?>>Scheduled</option>
                <option value="archived" <?= $status==='archived'?'selected':'' ?>>Archived</option>
            </select>
            <select name="category" class="form-select" style="max-width:180px;">
                <option value="">All Categories</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $category==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($search || $status || $category): ?>
            <a href="posts.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
            <div style="margin-left:auto;">
                <a href="post-edit.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New Blog</a>
            </div>
        </form>
    </div>
</div>

<form method="POST">
<input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">

<div class="card">
    <div class="card-header">
        <h3>Showing <?= count($posts) ?> of <?= $total ?> blogs</h3>
        <div style="display:flex;gap:8px;">
            <select name="bulk_action" class="form-select" style="max-width:140px;padding:6px 10px;">
                <option value="">Bulk Actions</option>
                <option value="publish">Publish</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Apply bulk action?')">Apply</button>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" id="selectAll" onclick="document.querySelectorAll('.post-check').forEach(c=>c.checked=this.checked)"></th>
                        <th style="width:70px;">Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <div class="icon"><i class="fas fa-newspaper"></i></div>
                                <p>No blog posts found.</p>
                                <a href="post-edit.php" class="btn btn-primary btn-sm" style="margin-top:12px;">Create First Blog</a>
                            </div>
                        </td>
                    </tr>
                    <?php else: foreach ($posts as $post): ?>
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="<?= $post['id'] ?>" class="post-check"></td>
                        <td>
                            <?php if ($post['featured_image']): ?>
                            <img src="<?= e(UPLOAD_URL . $post['featured_image']) ?>" class="post-thumb" alt="">
                            <?php else: ?>
                            <div class="post-thumb" style="display:flex;align-items:center;justify-content:center;background:#E2E8F0;"><i class="fas fa-image" style="color:#94A3B8;"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="post-edit.php?id=<?= $post['id'] ?>" style="font-weight:600;color:var(--navy);">
                                <?= e(mb_strimwidth($post['title'], 0, 55, '...')) ?>
                            </a>
                            <?php if ($post['is_featured']): ?>
                                <span class="badge badge-featured" style="margin-left:6px;"><i class="fas fa-star"></i> Featured</span>
                            <?php endif; ?>
                            <div style="font-size:12px;color:#94A3B8;margin-top:2px;">post.php?slug=<?= e($post['slug']) ?></div>
                        </td>
                        <td><?= e($post['category_name'] ?? '—') ?></td>
                        <td style="font-size:13px;"><?= e($post['author_name'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= e($post['status']) ?>"><?= ucfirst($post['status']) ?></span></td>
                        <td style="font-size:13px;"><?= number_format($post['view_count']) ?></td>
                        <td style="font-size:13px;color:#64748B;">
                            <?= $post['published_at'] ? date('d M Y', strtotime($post['published_at'])) : date('d M Y', strtotime($post['created_at'])) ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="post-edit.php?id=<?= $post['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                <a href="<?= e(post_url($post['slug'])) ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                <a href="post-delete.php?id=<?= $post['id'] ?>&csrf=<?= e(generate_csrf()) ?>" class="btn-icon danger" title="Delete" onclick="return confirm('Delete this blog permanently?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</form>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
    <a href="?page=<?= $page-1 ?>&status=<?= e($status) ?>&category=<?= $category ?>&q=<?= urlencode($search) ?>">← Prev</a>
    <?php endif; ?>
    
    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <?php if ($i == $page): ?>
        <span class="active"><?= $i ?></span>
        <?php else: ?>
        <a href="?page=<?= $i ?>&status=<?= e($status) ?>&category=<?= $category ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($page < $total_pages): ?>
    <a href="?page=<?= $page+1 ?>&status=<?= e($status) ?>&category=<?= $category ?>&q=<?= urlencode($search) ?>">Next →</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
