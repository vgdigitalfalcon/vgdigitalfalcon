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
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $remarks = $_POST['remarks'];
    $expense_by = $_POST['expense_by'];
    $payment_status = $_POST['payment_status'];
    $partial_amount = ($payment_status == 'partial') ? $_POST['partial_amount'] : 0;

    // Validate partial amount
    if ($payment_status == 'partial' && $partial_amount >= $amount) {
        $_SESSION['error'] = "Partial amount cannot be greater than or equal to total amount";
        header("Location: expenses_list.php?edit_id=$id");
        exit();
    }

    // Update the expense with all fields
    $sql = "UPDATE expenses SET 
            amount = '$amount',
            reason = '$reason',
            date = '$date',
            remarks = '$remarks',
            expense_by = '$expense_by',
            payment_status = '$payment_status',
            partial_amount = '$partial_amount'
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Expense updated successfully";
    } else {
        $_SESSION['error'] = "Error updating expense: " . $conn->error;
    }

    // Redirect back to expenses list with filters preserved
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $year = isset($_GET['year']) ? $_GET['year'] : '';
    $month = isset($_GET['month']) ? $_GET['month'] : '';
    $period = isset($_GET['period']) ? $_GET['period'] : '';
    
    $redirect_url = "expenses_list.php?page=$page";
    if ($search) $redirect_url .= "&search=" . urlencode($search);
    if ($year) $redirect_url .= "&year=$year";
    if ($month) $redirect_url .= "&month=$month";
    if ($period) $redirect_url .= "&period=$period";
    
    header("Location: $redirect_url");
    exit();
} else {
    header("Location: expenses_list.php");
    exit();
}
?>