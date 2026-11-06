<?php
require_once __DIR__ . '/../includes/config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | A.R. Waterproofing</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <style>
    /* ----- RESET & GLOBALS ----- */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: #19152c;
      line-height: 1.5;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background: #0f0a1f;
      position: relative;
      overflow: hidden;
    }
    :root {
      --primary: #5422c8;
      --primary-light: #7c4dff;
      --primary-soft: #ede7ff;
      --surface: #ffffff;
      --border: #e8e2f4;
      --text-muted: #716c81;
      --danger: #c0392b;
      --radius-lg: 24px;
      --radius-md: 14px;
    }

    /* ----- ANIMATED BACKGROUND ----- */
    body::before,
    body::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      filter: blur(90px);
      opacity: 0.55;
      z-index: 0;
      pointer-events: none;
    }
    body::before {
      width: 520px;
      height: 520px;
      background: radial-gradient(circle, #5422c8 0%, transparent 70%);
      top: -180px;
      left: -160px;
      animation: floatOne 14s ease-in-out infinite alternate;
    }
    body::after {
      width: 560px;
      height: 560px;
      background: radial-gradient(circle, #00b8a9 0%, transparent 70%);
      bottom: -220px;
      right: -180px;
      animation: floatTwo 16s ease-in-out infinite alternate;
    }
    @keyframes floatOne {
      from { transform: translate(0, 0) scale(1); }
      to   { transform: translate(60px, 40px) scale(1.1); }
    }
    @keyframes floatTwo {
      from { transform: translate(0, 0) scale(1); }
      to   { transform: translate(-50px, -40px) scale(1.15); }
    }

    /* ----- SHELL (SPLIT PANEL) ----- */
    .login-shell {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1.05fr 1fr;
      width: 100%;
      max-width: 940px;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: 0 40px 100px rgba(0, 0, 0, 0.45);
      animation: shellIn 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }
    @keyframes shellIn {
      from { opacity: 0; transform: translateY(24px) scale(0.98); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* ----- BRAND PANEL ----- */
    .brand-panel {
      background: linear-gradient(155deg, #5422c8 0%, #3d1696 55%, #2a0e6e 100%);
      padding: 52px 44px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
      color: #fff;
    }
    .brand-panel::before {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      top: -110px;
      right: -110px;
    }
    .brand-panel::after {
      content: '';
      position: absolute;
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
      bottom: -90px;
      left: -70px;
    }
    .brand-top {
      position: relative;
      z-index: 1;
    }
    .brand-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 40px;
    }
    .brand-mark {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 18px;
      letter-spacing: -0.5px;
      backdrop-filter: blur(6px);
    }
    .brand-logo-text {
      font-weight: 700;
      font-size: 15px;
      letter-spacing: 1.4px;
      text-transform: uppercase;
      opacity: 0.92;
    }
    .brand-headline {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -0.8px;
      line-height: 1.2;
      margin-bottom: 14px;
    }
    .brand-headline em {
      font-style: normal;
      color: #b9a6ff;
    }
    .brand-sub {
      font-size: 14.5px;
      color: rgba(255, 255, 255, 0.72);
      max-width: 320px;
      line-height: 1.6;
    }
    .brand-features {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-top: 36px;
    }
    .feature-row {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 13.5px;
      color: rgba(255, 255, 255, 0.85);
    }
    .feature-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.18);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      flex-shrink: 0;
    }
    .brand-footer {
      position: relative;
      z-index: 1;
      font-size: 12px;
      color: rgba(255, 255, 255, 0.55);
      letter-spacing: 0.4px;
    }

    /* ----- FORM PANEL ----- */
    .form-panel {
      background: var(--surface);
      padding: 52px 46px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .form-panel h1 {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.6px;
      color: #19152c;
      margin-bottom: 6px;
    }
    .form-panel h1 em {
      font-style: normal;
      color: var(--primary);
    }
    .form-panel .form-subtitle {
      font-size: 14px;
      color: var(--text-muted);
      margin-bottom: 30px;
    }

    /* ----- FORM ----- */
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      font-weight: 600;
      color: #19152c;
      font-size: 13.5px;
      display: block;
      margin-bottom: 8px;
      letter-spacing: 0.1px;
    }
    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-wrap .input-icon {
      position: absolute;
      left: 14px;
      font-size: 15px;
      opacity: 0.45;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }
    .form-group input {
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 13px 14px 13px 42px;
      font-size: 14px;
      width: 100%;
      box-sizing: border-box;
      background: #faf9ff;
      transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      font-family: inherit;
      color: #19152c;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(84, 34, 200, 0.1);
      background: #fff;
    }
    .form-group input:focus + .input-icon,
    .input-wrap:focus-within .input-icon {
      opacity: 0.9;
    }
    .form-group input::placeholder {
      color: #b0a8c0;
    }

    /* ----- PASSWORD TOGGLE ----- */
    .toggle-password {
      position: absolute;
      right: 10px;
      background: transparent;
      border: none;
      cursor: pointer;
      font-size: 15px;
      padding: 6px 8px;
      border-radius: 8px;
      opacity: 0.5;
      transition: opacity 0.15s ease, background 0.15s ease;
      line-height: 1;
    }
    .toggle-password:hover {
      opacity: 0.9;
      background: var(--primary-soft);
    }

    /* ----- SUBMIT BUTTON ----- */
    .button-primary {
      width: 100%;
      justify-content: center;
      padding: 14px 22px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: #fff;
      border: none;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(84, 34, 200, 0.3);
      transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
      letter-spacing: 0.2px;
      font-family: inherit;
      margin-top: 6px;
    }
    .button-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(84, 34, 200, 0.4);
      filter: brightness(1.05);
    }
    .button-primary:active {
      transform: translateY(0);
    }
    .button-primary:focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: 3px;
    }

    /* ----- ALERT ----- */
    .alert {
      border-radius: 12px;
      padding: 13px 16px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: alertIn 0.3s ease;
    }
    @keyframes alertIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .alert-error {
      background: #fdecea;
      color: var(--danger);
      border: 1px solid #f5c6c0;
    }
    .alert-error::before {
      content: '!';
      background: var(--danger);
      color: #fff;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 700;
      flex-shrink: 0;
    }

    /* ----- HINT ----- */
    .login-hint {
      margin-top: 26px;
      padding-top: 20px;
      border-top: 1px dashed var(--border);
      font-size: 12.5px;
      color: var(--text-muted);
      text-align: center;
      line-height: 1.7;
    }
    .login-hint code {
      background: #f7f5fc;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 2px 7px;
      font-size: 12px;
      color: var(--primary);
      font-weight: 600;
      font-family: 'SFMono-Regular', Consolas, monospace;
    }

    /* ----- RESPONSIVE ----- */
    @media (max-width: 820px) {
      .login-shell {
        grid-template-columns: 1fr;
        max-width: 440px;
      }
      .brand-panel {
        padding: 32px 30px;
      }
      .brand-headline {
        font-size: 24px;
      }
      .brand-features {
        display: none;
      }
      .brand-logo {
        margin-bottom: 20px;
      }
      .form-panel {
        padding: 36px 30px;
      }
    }
    @media (max-width: 420px) {
      body { padding: 14px; }
      .form-panel { padding: 30px 22px; }
      .brand-panel { padding: 26px 22px; }
      .form-panel h1 { font-size: 22px; }
    }
  </style>
</head>
<body>
  <div class="login-shell">
    <!-- BRAND PANEL -->
    <div class="brand-panel">
      <div class="brand-top">
        <div class="brand-logo">
          <div class="brand-mark">AR</div>
          <span class="brand-logo-text">A.R. Waterproofing</span>
        </div>
        <h2 class="brand-headline">Welcome back to your <em>Admin Console</em>.</h2>
        <p class="brand-sub">Manage services, projects, and media — all from one secure control center.</p>

        <div class="brand-features">
          <div class="feature-row">
            <span class="feature-icon">🛠</span>
            <span>Manage services &amp; project galleries</span>
          </div>
          <div class="feature-row">
            <span class="feature-icon">🔒</span>
            <span>Secure, role-based admin access</span>
          </div>
          <div class="feature-row">
            <span class="feature-icon">⚡</span>
            <span>Instant updates to your public site</span>
          </div>
        </div>
      </div>
      <div class="brand-footer">© <?php echo date('Y'); ?> A.R. Waterproofing · All rights reserved</div>
    </div>

    <!-- FORM PANEL -->
    <div class="form-panel">
      <h1>A.R. <em>Admin</em></h1>
      <p class="form-subtitle">Sign in to continue to your dashboard.</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo sanitize($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" id="username" name="username" required autofocus placeholder="Enter your username" autocomplete="username">
          </div>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔑</span>
            <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Show password" id="toggleBtn">👁</button>
          </div>
        </div>
        <button type="submit" class="button-primary">Sign In →</button>
      </form>

      <p class="login-hint">
        Default: <code>admin</code> / <code>password</code><br>
      </p>
    </div>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const btn = document.getElementById('toggleBtn');
      if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
        btn.setAttribute('aria-label', 'Hide password');
      } else {
        input.type = 'password';
        btn.textContent = '👁';
        btn.setAttribute('aria-label', 'Show password');
      }
    }
  </script>
</body>
</html>