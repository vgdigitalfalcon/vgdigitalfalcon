<?php
require_once __DIR__ . '/../config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf'] ?? '';
    
    if (!verify_csrf($csrf)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            
            // Log activity
            $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'login', ?)")
                ->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Aura Cladding Blog CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon"><i class="fas fa-building"></i></div>
            <h1>Aura Cladding</h1>
            <p>Blog Content Management System</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:20px;">
            <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
            
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="admin" required autofocus
                       value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px;">
                <i class="fas fa-sign-in-alt"></i> Sign In to CRM
            </button>
        </form>
        
        <p style="text-align:center; margin-top:24px; font-size:13px; color:#94A3B8;">
            &copy; <?= date('Y') ?> Aura Cladding. All rights reserved.
        </p>
    </div>
</div>
</body>
</html>
