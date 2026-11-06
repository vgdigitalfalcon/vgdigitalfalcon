<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login('admin');
$services = ['seo', 'smo', 'ads', 'ppc', 'performance'];

if (isset($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    $row = client_row($cid);
    if ($row) {
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$row['user_id']]);
        $_SESSION['flash'] = 'Client deleted.';
    }
    header('Location: clients.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)post('id');
    $name = trim(post('name'));
    $username = trim(post('username'));
    $password = post('password');
    $website = trim(post('website'));
    $cost = (float)post('package_cost');
    $day = max(1, min(28, (int)post('retention_day')));
    $status = post('status') === 'inactive' ? 'inactive' : 'active';
    $picked = array_values(array_intersect($services, (array)post('services', [])));
    if ($id) {
        $row = client_row($id);
        db()->prepare('UPDATE users SET name = ?, username = ? WHERE id = ?')->execute([$name, $username, $row['user_id']]);
        if ($password !== '') {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $row['user_id']]);
        }
        db()->prepare('UPDATE clients SET website = ?, package_cost = ?, retention_day = ?, status = ? WHERE id = ?')->execute([$website, $cost, $day, $status, $id]);
        db()->prepare('DELETE FROM client_services WHERE client_id = ?')->execute([$id]);
        $cid = $id;
    } else {
        db()->prepare('INSERT INTO users (role, name, username, password_hash) VALUES ("client", ?, ?, ?)')->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT)]);
        $uid = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO clients (user_id, website, package_cost, retention_day, status) VALUES (?, ?, ?, ?, ?)')->execute([$uid, $website, $cost, $day, $status]);
        $cid = (int)db()->lastInsertId();
    }
    $ins = db()->prepare('INSERT INTO client_services (client_id, service) VALUES (?, ?)');
    foreach ($picked as $s) $ins->execute([$cid, $s]);
    $_SESSION['flash'] = 'Client saved.';
    header('Location: clients.php');
    exit;
}

$edit = isset($_GET['edit']) ? client_row((int)$_GET['edit']) : null;
$editServices = $edit ? services_of((int)$edit['id']) : [];
layout_start('Clients', $user);
flash();
echo '<div class="card-box"><h2>' . ($edit ? 'Edit client' : 'Add client') . '</h2><form method="post" class="row g-2">';
if ($edit) echo '<input type="hidden" name="id" value="' . (int)$edit['id'] . '">';
echo '<div class="col-md-4"><label>Client name</label><input class="form-control" name="name" required value="' . h($edit['name'] ?? '') . '"></div>';
echo '<div class="col-md-4"><label>Website</label><input class="form-control" name="website" value="' . h($edit['website'] ?? '') . '"></div>';
echo '<div class="col-md-4"><label>Username</label><input class="form-control" name="username" required value="' . h($edit['username'] ?? '') . '"></div>';
echo '<div class="col-md-4"><label>Password' . ($edit ? ' (blank keeps current)' : '') . '</label><input class="form-control" type="text" name="password" ' . ($edit ? '' : 'required') . '></div>';
echo '<div class="col-md-3"><label>Package cost INR</label><input class="form-control" name="package_cost" value="' . h($edit['package_cost'] ?? '') . '"></div>';
echo '<div class="col-md-2"><label>Retention day</label><input class="form-control" type="number" min="1" max="28" name="retention_day" value="' . h($edit['retention_day'] ?? '1') . '"></div>';
echo '<div class="col-md-3"><label>Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive" ' . (($edit['status'] ?? '') === 'inactive' ? 'selected' : '') . '>Not Active</option></select></div>';
echo '<div class="col-12 checks">Services ';
foreach ($services as $s) {
    $on = in_array($s, $editServices, true) ? 'checked' : '';
    echo '<label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="services[]" value="' . h($s) . '" ' . $on . '> ' . h(strtoupper($s)) . '</label>';
}
echo '</div><div class="col-12"><button class="btn btn-red">Save client</button></div></form></div>';
$rows = db()->query('SELECT c.*, u.name, u.username FROM clients c JOIN users u ON u.id = c.user_id ORDER BY u.name')->fetchAll();
echo '<div class="card-box"><table class="table"><thead><tr><th>Name</th><th>Login</th><th>Status</th><th>INR</th><th>Retention</th><th></th></tr></thead><tbody>';
foreach ($rows as $r) {
    echo '<tr><td>' . h($r['name']) . '</td><td>' . h($r['username']) . '</td><td>' . h($r['status']) . '</td><td>' . number_format((float)$r['package_cost'], 2) . '</td><td>' . (int)$r['retention_day'] . '</td>';
    echo '<td><a href="clients.php?edit=' . (int)$r['id'] . '">Edit</a> <a href="clients.php?delete=' . (int)$r['id'] . '" onclick="return confirm(\'Delete client?\')">Delete</a></td></tr>';
}
echo '</tbody></table></div>';
layout_end();
