<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/fields.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$data = collect_form($_POST);
if ($data['contact_name'] === '' || $data['contact_email'] === '') {
    $page_title = 'Missing details';
    $flash = 'Name and email are required.';
    $flash_type = 'err';
    require __DIR__ . '/includes/header.php';
    echo '<p><a class="btn btn-blue" href="index.php">Back to form</a></p>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = db()->prepare(
    'INSERT INTO submissions (contact_name, contact_email, contact_phone, contact_role, architecture, package, budget, payload)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $data['contact_name'],
    $data['contact_email'],
    $data['contact_phone'],
    $data['contact_role'],
    $data['q9_architecture'],
    $data['q31_package'],
    $data['q32_budget'],
    json_encode($data, JSON_UNESCAPED_UNICODE),
]);

header('Location: thanks.php?n=' . urlencode($data['contact_name']));
exit;
