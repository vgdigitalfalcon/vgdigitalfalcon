<?php
require_once __DIR__ . '/../config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function h($value): string {
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function field($data, string $key): string {
    $v = $data[$key] ?? '';
    if (is_array($v)) {
        return implode(', ', $v);
    }
    $v = trim((string) $v);
    return $v === '' ? '—' : $v;
}
?>
