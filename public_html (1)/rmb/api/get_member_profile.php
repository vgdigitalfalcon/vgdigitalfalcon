<?php
// api/get_member_profile.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$host = '127.0.0.1:3306'; 
$dbname = 'u670120666_rmb';
$username = 'u670120666_rmb'; 
$password = 'RMBmember2026'; 

// Check if the slug was passed in the URL
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No member slug provided']);
    exit;
}

$slug = $_GET['slug'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // This query recreates the URL slug inside MySQL to find the match.
    // It lowercases the name, trims spaces, and replaces spaces with hyphens.
    $sql = "SELECT * FROM members WHERE REPLACE(LOWER(TRIM(name)), ' ', '-') = :slug LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['slug' => $slug]);
    
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        echo json_encode($member);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Member not found']);
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
}
?>