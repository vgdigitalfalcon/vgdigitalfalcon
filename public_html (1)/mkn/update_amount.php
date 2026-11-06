<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $user = $_SESSION['user'];
    
    // Check if the record exists
    $check_query = "SELECT * FROM amounts WHERE id = $id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Update the record
        $update_query = "UPDATE amounts SET amount = '$amount', date = '$date', added_by = '$user' WHERE id = $id";
        
        if ($conn->query($update_query)) {
            $_SESSION['message'] = "Amount updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating amount: " . $conn->error;
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