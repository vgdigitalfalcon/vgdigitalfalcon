<?php
/**
 * VG Digital Falcon - Mathematical CAPTCHA API
 * Generates lightweight anti-bot math challenges without external dependencies.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = trim($_POST['captcha_answer'] ?? '');
    $expected = isset($_SESSION['captcha_answer']) ? (string)$_SESSION['captcha_answer'] : null;

    if ($expected !== null && $submitted === $expected) {
        echo json_encode(['valid' => true, 'message' => 'CAPTCHA verified successfully.']);
    } else {
        echo json_encode(['valid' => false, 'message' => 'Incorrect CAPTCHA answer. Please try again.']);
    }
    exit;
}

// Generate new math problem
$num1 = rand(1, 9);
$num2 = rand(1, 9);
$sum = $num1 + $num2;

$_SESSION['captcha_answer'] = (string)$sum;

echo json_encode([
    'num1'     => $num1,
    'num2'     => $num2,
    'question' => "What is {$num1} + {$num2}?",
    'status'   => 'ready'
]);
exit;
