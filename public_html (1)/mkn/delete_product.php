<?php
// delete_product.php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("HTTP/1.1 401 Unauthorized");
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

$id = (int)$_GET['id'];

// Check if product exists
$check_stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header("HTTP/1.1 404 Not Found");
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>