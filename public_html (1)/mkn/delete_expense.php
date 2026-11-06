<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if ID parameter is provided
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get search and page parameters to redirect back correctly
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Expense deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting expense: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    
    // Redirect back to expenses list with search and page parameters
    $redirect_url = "expenses_list.php?page=$page";
    if (!empty($search)) {
        $redirect_url .= "&search=" . urlencode($search);
    }
    header("Location: $redirect_url");
    exit();
} else {
    $_SESSION['message'] = "No expense ID provided!";
    $_SESSION['message_type'] = "error";
    header("Location: expenses_list.php");
    exit();
}
?>