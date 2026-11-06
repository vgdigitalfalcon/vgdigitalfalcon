<?php
// get_members.php

// Allow cross-origin requests (useful for local development)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Database configuration
// Update these with your actual database credentials
$host = '127.0.0.1:3306'; 
$dbname = 'u670120666_rmb';
$username = 'u670120666_rmb'; // replace with your db username
$password = 'RMBmember2026'; // replace with your db password


try {
    // Create PDO Connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all members from the table
    $stmt = $pdo->prepare("SELECT * FROM members ORDER BY created_at DESC");
    $stmt->execute();
    
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    echo json_encode($members);

} catch(PDOException $e) {
    // Return error if connection fails
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
}


?>