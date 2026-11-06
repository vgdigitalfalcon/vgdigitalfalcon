<?php
require_once __DIR__ . '/../config.php';
$page_title = $page_title ?? APP_TITLE;
$admin = !empty($admin);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" href="assets/img/dflogo.png">
</head>
<body>
<div class="topstrip">
  <div class="wrap">
    <span>contact@vgdigitalfalcon.com</span>
    <span>+91 86957 76922</span>
  </div>
</div>
<header class="nav">
  <div class="wrap">
    <a class="brand" href="<?= $admin ? 'dashboard.php' : 'index.php' ?>">
      <img src="assets/img/dflogo-white.png" alt="VG Digital Falcon">
    </a>
    <div class="nav-links">
      <?php if ($admin): ?>
        <a href="index.php">View form</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Team login</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="wrap">
<?php if (!empty($flash)): ?>
  <div class="flash <?= $flash_type ?? 'ok' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
