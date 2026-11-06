<?php
// One-time setup. Open /install.php once, then delete or protect this file.
if (!file_exists(__DIR__ . '/config.php')) {
    exit('Copy config.sample.php to config.php and set MySQL details first.');
}
require_once __DIR__ . '/config.php';
$pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE `' . DB_NAME . '`');
$sql = file_get_contents(__DIR__ . '/sql/schema.sql');
$sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
$sql = preg_replace('/USE .*?;/', '', $sql);
foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    $pdo->exec($statement);
}
$exists = $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetch();
if (!$exists) {
    $admin = password_hash('Admin@123', PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (role, name, username, password_hash) VALUES ("admin", "Super Admin", "admin", ?)')->execute([$admin]);
    $empPass = password_hash('Emp@123', PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (role, name, username, password_hash) VALUES ("employee", "SEO Employee", "employee", ?)')->execute([$empPass]);
    $empId = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO employee_profiles (user_id) VALUES (?)')->execute([$empId]);
    foreach (['seo', 'smo', 'ads'] as $work) {
        $pdo->prepare('INSERT INTO employee_work (user_id, work_type) VALUES (?, ?)')->execute([$empId, $work]);
    }
    $clientPass = password_hash('Client@123', PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO users (role, name, username, password_hash) VALUES ("client", "Sample Client", "client", ?)')->execute([$clientPass]);
    $clientUser = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO clients (user_id, website, package_cost, retention_day, status) VALUES (?, ?, ?, ?, ?)')->execute([$clientUser, 'https://example.com', 25000, (int)date('j'), 'active']);
    $clientId = (int)$pdo->lastInsertId();
    foreach (['seo', 'smo', 'ads'] as $service) {
        $pdo->prepare('INSERT INTO client_services (client_id, service) VALUES (?, ?)')->execute([$clientId, $service]);
    }
    $pdo->prepare('INSERT INTO employee_clients (user_id, client_id) VALUES (?, ?)')->execute([$empId, $clientId]);
}
echo 'Installed.<br>Admin login: /admin/index.php · admin / Admin@123<br>Employee login: /employee/index.php · employee / Emp@123<br>Client login: /client/index.php · client / Client@123<br>Delete install.php after use.';
