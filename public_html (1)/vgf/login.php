<?php
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (attempt_login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Wrong username or password.';
}

$page_title = 'Team login | VG Digital Falcon';
$flash = $error;
$flash_type = $error ? 'err' : null;
require __DIR__ . '/includes/header.php';
?>
<div class="login-box">
  <section class="card">
    <img src="assets/img/dflogo-black.png" alt="VG Digital Falcon" class="logo-sm">
    <h2>Team login</h2>
    <p class="hint">Plain-text password is stored in config.php. Change it before sharing the client link.</p>
    <form method="post">
      <label class="field">Username</label>
      <input type="text" name="username" value="admin" required>
      <label class="field">Password</label>
      <input type="password" name="password" required>
      <div style="margin-top:16px">
        <button class="btn btn-orange" type="submit">Open dashboard</button>
      </div>
    </form>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
