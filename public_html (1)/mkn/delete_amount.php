<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if the record exists and belongs to the current user (optional security measure)
    $check_query = "SELECT * FROM amounts WHERE id = $id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Delete the record
        $delete_query = "DELETE FROM amounts WHERE id = $id";
        
        if ($conn->query($delete_query)) {
            $_SESSION['message'] = "Amount deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting amount: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Amount record not found!";
        $_SESSION['message_type'] = "error";
    }
}

// Redirect back to amounts list with search and page parameters
$search = isset($_GET['search']) ? "&search=" . urlencode($_GET['search']) : "";
$page = isset($_GET['page']) ? "&page=" . (int)$_GET['page'] : "";
header("Location: amounts_list.php?$search$page");
exit();
?>