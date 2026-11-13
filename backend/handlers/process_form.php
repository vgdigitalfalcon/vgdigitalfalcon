<?php
header('Content-Type: application/json');

// Database configuration
$servername = "your_database_server";
$username = "your_database_username";
$password = "your_database_password";
$dbname = "your_database_name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get form data
$name = $_POST['name'] ?? '';
$channel = $_POST['channel'] ?? '';
$whatsapp = $_POST['whatsapp'] ?? '';

// Basic validation
if (empty($name) || empty($channel) || empty($whatsapp)) {
    die(json_encode(['success' => false, 'message' => 'All fields are required']));
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO youtube_seo_audit (name, channel, whatsapp, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $name, $channel, $whatsapp);

// Execute and respond
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Form submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>