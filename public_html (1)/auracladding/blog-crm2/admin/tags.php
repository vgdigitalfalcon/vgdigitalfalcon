<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'Tags';
$page_subtitle = 'Manage post tags';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $name = trim($_POST['name'] ?? '');
    if ($name) {
        $slug = slugify($name);
        try {
            $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)")->execute([$name, $slug]);
            set_flash('success', 'Tag created.');
        } catch (PDOException $e) {
            set_flash('danger', 'Tag already exists.');
        }
    }
    header('Location: tags.php');
    exit;
}

if (isset($_GET['delete']) && verify_csrf($_GET['csrf'] ?? '')) {
    $pdo->prepare("DELETE FROM tags WHERE id = ?")->execute([(int)$_GET['delete']]);
    set_flash('success', 'Tag deleted.');
    header('Location: tags.php');
    exit;
}

$tags = $pdo->query("
    SELECT t.*, COUNT(pt.post_id) as post_count 
    FROM tags t 
    LEFT JOIN post_tags pt ON pt.tag_id = t.id 
    GROUP BY t.id 
    ORDER BY t.name
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div style="display:grid; grid-template-columns: 1fr 320px; gap:24px;">
    <div class="card">
        <div class="card-header"><h3>All Tags</h3></div>
        <div class="card-body" style="padding:0;">
            <table>
                <thead>
                    <tr><th>Name</th><th>Slug</th><th>Posts</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td><strong><?= e($tag['name']) ?></strong></td>
                        <td style="color:#64748B;"><?= e($tag['slug']) ?></td>
                        <td><?= $tag['post_count'] ?></td>
                        <td>
                            <a href="?delete=<?= $tag['id'] ?>&csrf=<?= e(generate_csrf()) ?>" class="btn-icon danger" onclick="return confirm('Delete tag?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tags)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:#94A3B8;">No tags yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3>Add Tag</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
                <div class="form-group">
                    <label class="form-label">Tag Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. ACP Cladding">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Add Tag</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
