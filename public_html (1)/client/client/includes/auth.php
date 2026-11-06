<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(string $role = ''): array {
    $u = current_user();
    if (!$u || ($role && $u['role'] !== $role)) {
        $target = BASE_URL . '/index.php';
        if ($role === 'admin') $target = BASE_URL . '/admin/index.php';
        if ($role === 'employee') $target = BASE_URL . '/employee/index.php';
        if ($role === 'client') $target = BASE_URL . '/client/index.php';
        header('Location: ' . $target);
        exit;
    }
    return $u;
}

function login_user(string $username, string $password, string $portal): bool {
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND role = ? LIMIT 1');
    $stmt->execute([$username, $portal]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    return true;
}

function logout_user(): void {
    $_SESSION = [];
    session_destroy();
}
