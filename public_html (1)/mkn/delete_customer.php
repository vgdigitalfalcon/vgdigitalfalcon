<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if ID parameter is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: customers_list.php");
    exit();
}

$customer_id = $_GET['id'];

// Check if customer exists
$check_sql = "SELECT * FROM customers WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $customer_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    // Customer not found
    $_SESSION['error_message'] = "Customer not found!";
    header("Location: customers_list.php");
    exit();
}

// Delete customer from database
$delete_sql = "DELETE FROM customers WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $customer_id);

if ($delete_stmt->execute()) {
    $_SESSION['success_message'] = "Customer deleted successfully!";
} else {
    $_SESSION['error_message'] = "Error deleting customer: " . $conn->error;
}

$delete_stmt->close();
$check_stmt->close();
header("Location: customers_list.php");
exit();
?>