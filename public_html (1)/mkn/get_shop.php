<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit();
}

$shop_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM shops WHERE id = ?");
$stmt->bind_param("i", $shop_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Shop deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting shop: ' . $stmt->error]);
}
?>