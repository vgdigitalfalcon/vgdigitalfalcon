<?php
require_once __DIR__ . '/../config.php';

function is_admin(): bool {
    return !empty($_SESSION['vgdf_admin']);
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function attempt_login(string $user, string $pass): bool {
    if ($user === ADMIN_USERNAME && $pass === ADMIN_PASSWORD) {
        $_SESSION['vgdf_admin'] = true;
        return true;
    }
    return false;
}
?>
