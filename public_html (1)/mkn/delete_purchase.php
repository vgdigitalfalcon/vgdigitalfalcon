<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];

// Delete record
$sql = "DELETE FROM purchases WHERE id = $id";
if ($conn->query($sql)) {
    header("Location: purchases_list.php?msg=deleted");
    exit();
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
