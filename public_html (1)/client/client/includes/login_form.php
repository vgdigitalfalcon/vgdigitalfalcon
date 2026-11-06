<?php
function render_login(string $portal, string $title, string $blurb): void {
    $base = BASE_URL;
    $error = $GLOBALS['login_error'] ?? '';
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="theme-color" content="#1A1A1A"><link rel="manifest" href="' . h($base) . '/manifest.webmanifest">';
    echo '<link rel="icon" href="' . h($base) . '/assets/img/logo.png">';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . h($base) . '/assets/css/app.css"><title>' . h($title) . '</title></head>';
    echo '<body class="login-body"><div class="login-card">';
    echo '<img src="' . h($base) . '/assets/img/logo.png" alt="Digital Falcon" width="84" height="84">';
    echo '<p class="portal-kicker">' . h(strtoupper($portal) . ' PORTAL') . '</p>';
    echo '<h1>' . h($title) . '</h1><p class="text-muted">' . h($blurb) . '</p>';
    if ($error) echo '<div class="alert alert-danger">' . h($error) . '</div>';
    echo '<form method="post"><input type="hidden" name="portal" value="' . h($portal) . '">';
    echo '<label class="form-label">Username</label><input class="form-control mb-2" name="username" required autofocus>';
    echo '<label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required>';
    echo '<button class="btn btn-red w-100">Log in</button></form>';
    echo '<p class="login-switch"><a href="' . h($base) . '/index.php">All portals</a></p>';
    echo '</div><script>if("serviceWorker" in navigator){navigator.serviceWorker.register("' . h($base) . '/sw.js");}</script></body></html>';
}

function handle_portal_login(string $portal): void {
    if (current_user()) {
        if (current_user()['role'] !== $portal) {
            logout_user();
            session_start();
        } else {
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (login_user(trim($_POST['username'] ?? ''), $_POST['password'] ?? '', $portal)) {
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
        $GLOBALS['login_error'] = 'This ' . $portal . ' login was not found. Use the ' . $portal . ' username and password.';
    }
}
