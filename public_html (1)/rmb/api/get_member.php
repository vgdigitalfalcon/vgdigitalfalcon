<?php
// api/get_member.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$host = '127.0.0.1:3306'; 
$dbname = 'u670120666_rmb';
$username = 'u670120666_rmb'; // replace with your db username
$password = 'RMBmember2026'; // replace with your db password

// Get the ID from the URL (e.g., ?id=1)
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($member_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Member ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the specific member securely
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $member_id]);
    
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        echo json_encode($member);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Member not found']);
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>