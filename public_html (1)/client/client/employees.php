<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login('admin');
$works = ['seo', 'smo', 'ads'];

if (isset($_GET['delete'])) {
    db()->prepare('DELETE FROM users WHERE id = ? AND role = "employee"')->execute([(int)$_GET['delete']]);
    $_SESSION['flash'] = 'Employee deleted.';
    header('Location: employees.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)post('id');
    $name = trim(post('name'));
    $username = trim(post('username'));
    $password = post('password');
    $pickedWork = array_values(array_intersect($works, (array)post('work', [])));
    $pickedClients = array_map('intval', (array)post('clients', []));
    if ($id) {
        db()->prepare('UPDATE users SET name = ?, username = ? WHERE id = ?')->execute([$name, $username, $id]);
        if ($password !== '') db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        $uid = $id;
        db()->prepare('DELETE FROM employee_work WHERE user_id = ?')->execute([$uid]);
        db()->prepare('DELETE FROM employee_clients WHERE user_id = ?')->execute([$uid]);
    } else {
        db()->prepare('INSERT INTO users (role, name, username, password_hash) VALUES ("employee", ?, ?, ?)')->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
        $uid = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO employee_profiles (user_id) VALUES (?)')->execute([$uid]);
    }
    $w = db()->prepare('INSERT INTO employee_work (user_id, work_type) VALUES (?, ?)');
    foreach ($pickedWork as $item) $w->execute([$uid, $item]);
    $c = db()->prepare('INSERT IGNORE INTO employee_clients (user_id, client_id) SELECT ?, id FROM clients WHERE id = ? AND status = "active"');
    foreach ($pickedClients as $cid) $c->execute([$uid, $cid]);
    $_SESSION['flash'] = 'Employee saved.';
    header('Location: employees.php');
    exit;
}

$edit = null;
$editWork = [];
$editClients = [];
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND role = "employee"');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
    if ($edit) {
        $editWork = work_types((int)$edit['id']);
        $stmt = db()->prepare('SELECT client_id FROM employee_clients WHERE user_id = ?');
        $stmt->execute([$edit['id']]);
        $editClients = array_column($stmt->fetchAll(), 'client_id');
    }
}
$activeClients = db()->query("SELECT c.id, u.name FROM clients c JOIN users u ON u.id = c.user_id WHERE c.status = 'active' ORDER BY u.name")->fetchAll();
layout_start('Employees', $user);
flash();
echo '<div class="card-box"><h2>' . ($edit ? 'Edit employee' : 'Add employee') . '</h2><form method="post" class="row g-2">';
if ($edit) echo '<input type="hidden" name="id" value="' . (int)$edit['id'] . '">';
echo '<div class="col-md-4"><label>Name</label><input class="form-control" name="name" required value="' . h($edit['name'] ?? '') . '"></div>';
echo '<div class="col-md-4"><label>Username</label><input class="form-control" name="username" required value="' . h($edit['username'] ?? '') . '"></div>';
echo '<div class="col-md-4"><label>Password' . ($edit ? ' (blank keeps current)' : '') . '</label><input class="form-control" name="password" ' . ($edit ? '' : 'required') . '></div>';
echo '<div class="col-12">Work type ';
foreach ($works as $w) {
    $on = in_array($w, $editWork, true) ? 'checked' : '';
    echo '<label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="work[]" value="' . h($w) . '" ' . $on . '> ' . h(strtoupper($w)) . '</label>';
}
echo '</div><div class="col-12 checks">Assign active clients ';
foreach ($activeClients as $c) {
    $on = in_array($c['id'], $editClients) ? 'checked' : '';
    echo '<label class="form-check"><input class="form-check-input" type="checkbox" name="clients[]" value="' . (int)$c['id'] . '" ' . $on . '> ' . h($c['name']) . '</label>';
}
echo '</div><div class="col-12"><button class="btn btn-red">Save employee</button></div></form></div>';
$list = db()->query('SELECT * FROM users WHERE role = "employee" ORDER BY name')->fetchAll();
echo '<div class="card-box"><table class="table"><thead><tr><th>Name</th><th>Login</th><th>Work</th><th></th></tr></thead><tbody>';
foreach ($list as $e) {
    echo '<tr><td>' . h($e['name']) . '</td><td>' . h($e['username']) . '</td><td>' . h(implode(', ', work_types((int)$e['id']))) . '</td>';
    echo '<td><a href="employees.php?edit=' . (int)$e['id'] . '">Edit</a> <a href="employees.php?delete=' . (int)$e['id'] . '" onclick="return confirm(\'Delete employee?\')">Delete</a></td></tr>';
}
echo '</tbody></table></div>';
layout_end();
