<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $portal = $_POST['portal'] ?? 'admin';
    if (!in_array($portal, ['admin', 'employee', 'client'], true)) {
        $portal = 'admin';
    }
    if (login_user(trim($_POST['username'] ?? ''), $_POST['password'] ?? '', $portal)) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    $error = 'Wrong portal, username, or password.';
}
$base = BASE_URL;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#1A1A1A">
<link rel="manifest" href="<?= h($base) ?>/manifest.webmanifest">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= h($base) ?>/assets/css/app.css">
<title>Login · Digital Falcon</title>
</head>
<body class="d-flex align-items-center" style="min-height:100vh;background:#1A1A1A">
<div class="container" style="max-width:460px">
  <div class="card-box text-center">
    <img src="<?= h($base) ?>/assets/img/logo.png" alt="" width="84" height="84">
    <h2 class="mt-2">Digital Falcon</h2>
    <p class="text-muted">Growth Accelerator · Group of Viivaa Guru</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="text-start">
      <label class="form-label">Portal</label>
      <select class="form-select mb-2" name="portal">
        <option value="admin">Admin portal</option>
        <option value="employee">Employee portal</option>
        <option value="client">Client portal</option>
      </select>
      <label class="form-label">Username</label>
      <input class="form-control mb-2" name="username" required>
      <label class="form-label">Password</label>
      <input class="form-control mb-3" type="password" name="password" required>
      <button class="btn btn-red w-100">Log in</button>
    </form>
  </div>
</div>
<script>if('serviceWorker' in navigator){navigator.serviceWorker.register('<?= h($base) ?>/sw.js');}</script>
</body></html>
