<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'Categories';
$page_subtitle = 'Organize your blog posts';

$errors = [];
$edit_id = (int)($_GET['edit'] ?? 0);
$edit_cat = null;

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_cat = $stmt->fetch();
}

// Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $description = trim($_POST['description'] ?? '');
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $id = (int)($_POST['id'] ?? 0);
        
        if (empty($name)) $errors[] = 'Name is required.';
        
        if (empty($errors)) {
            if ($id > 0) {
                $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, meta_title=?, meta_description=? WHERE id=?")
                    ->execute([$name, slugify($slug), $description, $meta_title ?: null, $meta_description ?: null, $id]);
                set_flash('success', 'Category updated.');
            } else {
                $pdo->prepare("INSERT INTO categories (name, slug, description, meta_title, meta_description) VALUES (?,?,?,?,?)")
                    ->execute([$name, slugify($slug), $description, $meta_title ?: null, $meta_description ?: null]);
                set_flash('success', 'Category created.');
            }
            header('Location: categories.php');
            exit;
        }
    }
}

// Delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if (verify_csrf($_GET['csrf'] ?? '')) {
        $pdo->prepare("UPDATE posts SET category_id = NULL WHERE category_id = ?")->execute([$del_id]);
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
        set_flash('success', 'Category deleted.');
    }
    header('Location: categories.php');
    exit;
}

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as post_count 
    FROM categories c 
    LEFT JOIN posts p ON p.category_id = c.id 
    GROUP BY c.id 
    ORDER BY c.name
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div style="display:grid; grid-template-columns: 1fr 360px; gap:24px;">
    <div class="card">
        <div class="card-header">
            <h3>All Categories</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Posts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= e($cat['name']) ?></strong></td>
                        <td style="color:#64748B;font-size:13px;"><?= e($cat['slug']) ?></td>
                        <td><?= $cat['post_count'] ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="?edit=<?= $cat['id'] ?>" class="btn-icon"><i class="fas fa-pen"></i></a>
                                <a href="?delete=<?= $cat['id'] ?>&csrf=<?= e(generate_csrf()) ?>" class="btn-icon danger" onclick="return confirm('Delete this category?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><?= $edit_cat ? 'Edit Category' : 'Add Category' ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= e(implode(', ', $errors)) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
                <input type="hidden" name="id" value="<?= $edit_cat['id'] ?? 0 ?>">
                
                <div class="form-group">
                    <label class="form-label">Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" required value="<?= e($edit_cat['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= e($edit_cat['slug'] ?? '') ?>" placeholder="auto-from-name">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($edit_cat['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= e($edit_cat['meta_title'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2"><?= e($edit_cat['meta_description'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <?= $edit_cat ? 'Update Category' : 'Add Category' ?>
                </button>
                
                <?php if ($edit_cat): ?>
                <a href="categories.php" class="btn btn-outline btn-block" style="margin-top:8px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
