<?php
/**
 * Users Management
 */
require_once __DIR__ . '/../config.php';
require_login();

if (!is_admin()) {
    set_flash('danger', 'Only administrators can manage users.');
    header('Location: index.php');
    exit;
}

$page_title = 'Users';
$page_subtitle = 'Manage admin and author accounts';

$errors = [];
$edit_id = (int)($_GET['edit'] ?? 0);
$edit_user = null;

if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
}

// Create or Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $action = $_POST['action'] ?? 'save';
    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'author';
    $bio = trim($_POST['bio'] ?? '');
    $expertise = trim($_POST['expertise'] ?? '');
    $password = $_POST['password'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!in_array($role, ['admin', 'editor', 'author'])) $role = 'author';

    if ($action === 'save') {
        if (empty($username) || empty($email) || empty($full_name)) {
            $errors[] = 'Username, email and full name are required.';
        }

        // Unique checks
        $check = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->execute([$username, $email, $id]);
        if ($check->fetch()) {
            $errors[] = 'Username or email already exists.';
        }

        if ($id === 0 && empty($password)) {
            $errors[] = 'Password is required for new users.';
        }

        if (empty($errors)) {
            if ($id > 0) {
                // Update
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
                    $pdo->prepare("UPDATE users SET username=?, email=?, full_name=?, role=?, bio=?, expertise=?, is_active=?, password=? WHERE id=?")
                        ->execute([$username, $email, $full_name, $role, $bio ?: null, $expertise ?: null, $is_active, $hash, $id]);
                } else {
                    $pdo->prepare("UPDATE users SET username=?, email=?, full_name=?, role=?, bio=?, expertise=?, is_active=? WHERE id=?")
                        ->execute([$username, $email, $full_name, $role, $bio ?: null, $expertise ?: null, $is_active, $id]);
                }
                set_flash('success', 'User updated successfully.');
            } else {
                // Create
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
                $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, bio, expertise, is_active) VALUES (?,?,?,?,?,?,?,?)")
                    ->execute([$username, $email, $hash, $full_name, $role, $bio ?: null, $expertise ?: null, $is_active]);
                set_flash('success', 'User created successfully.');
            }
            header('Location: users.php');
            exit;
        }
    }
}

// Delete user
if (isset($_GET['delete']) && verify_csrf($_GET['csrf'] ?? '')) {
    $del_id = (int)$_GET['delete'];
    if ($del_id === (int)$_SESSION['user_id']) {
        set_flash('danger', 'You cannot delete your own account.');
    } else {
        // Reassign posts to current admin
        $pdo->prepare("UPDATE posts SET author_id = ? WHERE author_id = ?")->execute([$_SESSION['user_id'], $del_id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
        set_flash('success', 'User deleted. Their posts were reassigned to you.');
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT u.*, 
    (SELECT COUNT(*) FROM posts WHERE author_id = u.id) as post_count 
    FROM users u ORDER BY u.role, u.full_name")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?= e(implode(' ', $errors)) ?>
</div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 380px; gap:24px;">

    <!-- Users List -->
    <div class="card">
        <div class="card-header">
            <h3>All Users (<?= count($users) ?>)</h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Posts</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--navy);"><?= e($u['full_name']) ?></strong>
                                <div style="font-size:12px;color:#94A3B8;">@<?= e($u['username']) ?> · <?= e($u['email']) ?></div>
                            </td>
                            <td>
                                <span class="badge" style="background:<?= $u['role']==='admin' ? 'rgba(245,166,35,0.15)' : 'rgba(100,116,139,0.12)' ?>;color:<?= $u['role']==='admin' ? '#B45309' : '#475569' ?>;">
                                    <?= ucfirst($u['role']) ?>
                                </span>
                            </td>
                            <td><?= (int)$u['post_count'] ?></td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge badge-published">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-draft">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:13px;color:#64748B;">
                                <?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : '—' ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?= $u['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?= $u['id'] ?>&csrf=<?= e(generate_csrf()) ?>" 
                                       class="btn-icon danger" title="Delete"
                                       onclick="return confirm('Delete this user? Their posts will be reassigned to you.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add / Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3><?= $edit_user ? 'Edit User' : 'Add New User' ?></h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= $edit_user['id'] ?? 0 ?>">

                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?= e($edit_user['full_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Username <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= e($edit_user['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= e($edit_user['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password <?= $edit_user ? '(leave blank to keep current)' : '<span class="required">*</span>' ?></label>
                    <input type="password" name="password" class="form-control" <?= $edit_user ? '' : 'required' ?>
                           placeholder="<?= $edit_user ? '••••••••' : 'Min 8 characters' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="author" <?= ($edit_user['role'] ?? '') === 'author' ? 'selected' : '' ?>>Author</option>
                        <option value="editor" <?= ($edit_user['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                        <option value="admin"  <?= ($edit_user['role'] ?? '') === 'admin'  ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Expertise (for E-E-A-T / Schema)</label>
                    <input type="text" name="expertise" class="form-control"
                           value="<?= e($edit_user['expertise'] ?? '') ?>"
                           placeholder="e.g. ACP Cladding Specialist">
                </div>

                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"
                              placeholder="Short author bio shown on posts..."><?= e($edit_user['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
                    <label class="form-label" style="margin:0;">Active Account</label>
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1"
                            <?= !isset($edit_user) || !empty($edit_user['is_active']) ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
                    <i class="fas fa-save"></i> <?= $edit_user ? 'Update User' : 'Create User' ?>
                </button>

                <?php if ($edit_user): ?>
                <a href="users.php" class="btn btn-outline btn-block" style="margin-top:8px;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
