<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
if (current_user()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}
$base = BASE_URL;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#1A1A1A">
<link rel="manifest" href="<?= h($base) ?>/manifest.webmanifest">
<link rel="icon" href="<?= h($base) ?>/assets/img/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= h($base) ?>/assets/css/app.css">
<title>Digital Falcon CRM</title>
</head>
<body class="login-body">
<div class="portal-wrap">
  <img src="<?= h($base) ?>/assets/img/logo.png" alt="Digital Falcon" width="88" height="88">
  <h1>Digital Falcon</h1>
  <p>Growth Accelerator · Group of Viivaa Guru</p>
  <div class="portal-grid">
    <a class="portal-card" href="<?= h($base) ?>/admin/index.php"><span>Admin portal</span><strong>Admin login</strong><small>All clients, costs, and staff</small></a>
    <a class="portal-card" href="<?= h($base) ?>/employee/index.php"><span>Employee portal</span><strong>Employee login</strong><small>Assigned clients and logs</small></a>
    <a class="portal-card" href="<?= h($base) ?>/client/index.php"><span>Client portal</span><strong>Client login</strong><small>Approved monthly reports</small></a>
  </div>
</div>
</body></html>
