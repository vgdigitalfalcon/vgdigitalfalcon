<?php
require_once __DIR__ . '/includes/auth.php';
$role = current_user()['role'] ?? 'admin';
logout_user();
$map = ['admin' => '/admin/index.php', 'employee' => '/employee/index.php', 'client' => '/client/index.php'];
header('Location: ' . BASE_URL . ($map[$role] ?? '/index.php'));
