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
}
echo 'Installed. Admin portal login: admin / Admin@123. Delete install.php after use.';
