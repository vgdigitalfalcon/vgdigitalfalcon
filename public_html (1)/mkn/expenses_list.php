<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Date filters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$period = isset($_GET['period']) ? $_GET['period'] : '';

// Build date filter conditions
$dateConditions = [];
if ($year) {
    $dateConditions[] = "YEAR(date) = $year";
}
if ($month) {
    $dateConditions[] = "MONTH(date) = $month";
}

// Handle predefined periods
if ($period) {
    $today = date('Y-m-d');
    switch ($period) {
        case 'current_month':
            $dateConditions[] = "YEAR(date) = YEAR('$today') AND MONTH(date) = MONTH('$today')";
            break;
        case 'last_month':
            $lastMonth = date('Y-m-d', strtotime('-1 month'));
            $dateConditions[] = "YEAR(date) = YEAR('$lastMonth') AND MONTH(date) = MONTH('$lastMonth')";
            break;
        case 'last_3_months':
            $threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));
            $dateConditions[] = "date >= '$threeMonthsAgo'";
            break;
        case 'last_6_months':
            $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
            $dateConditions[] = "date >= '$sixMonthsAgo'";
            break;
        case 'last_12_months':
            $yearAgo = date('Y-m-d', strtotime('-12 months'));
            $dateConditions[] = "date >= '$yearAgo'";
            break;
    }
}

// Build WHERE clause
$whereConditions = [];
if ($search) {
    $whereConditions[] = "(reason LIKE '%$search%' OR added_by LIKE '%$search%' OR expense_by LIKE '%$search%')";
}
if (!empty($dateConditions)) {
    $whereConditions[] = "(" . implode(" AND ", $dateConditions) . ")";
}

$whereClause = "";
if (!empty($whereConditions)) {
    $whereClause = "WHERE " . implode(" AND ", $whereConditions);
}

// Main query
$query = "SELECT * FROM expenses $whereClause ORDER BY id DESC LIMIT $start, $limit";

$result = $conn->query($query);

// Total count for pagination
$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM expenses $whereClause");
$total = $totalQuery->fetch_assoc()['total'];
$pages = ceil($total / $limit);

// Get total expenses for display
$total_expense_query = $conn->query("SELECT SUM(amount) AS total FROM expenses $whereClause");
$total_expense = $total_expense_query->fetch_assoc()['total'];

// Get statistics
// Last 3 months total
$threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));
$last_3_months_total = $conn->query("SELECT SUM(amount) AS total FROM expenses WHERE date >= '$threeMonthsAgo'")->fetch_assoc()['total'];

// Current month total
$currentMonth = date('Y-m');
$current_month_total = $conn->query("SELECT SUM(amount) AS total FROM expenses WHERE DATE_FORMAT(date, '%Y-%m') = '$currentMonth'")->fetch_assoc()['total'];

// Most expensive reason
$most_expensive_reason = $conn->query("SELECT reason, SUM(amount) AS total FROM expenses $whereClause GROUP BY reason ORDER BY total DESC LIMIT 1")->fetch_assoc();

// Get expense details for view/edit if ID is provided
$expense_details = null;
if (isset($_GET['view_id'])) {
    $view_id = (int)$_GET['view_id'];
    $expense_details = $conn->query("SELECT * FROM expenses WHERE id = $view_id")->fetch_assoc();
}

if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $expense_details = $conn->query("SELECT * FROM expenses WHERE id = $edit_id")->fetch_assoc();
}

// Get all users for dropdowns
$users_result = $conn->query("SELECT username FROM users");
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row['username'];
}

// Get available years for filter
$years_result = $conn->query("SELECT DISTINCT YEAR(date) as year FROM expenses ORDER BY year DESC");
$years = [];
while ($row = $years_result->fetch_assoc()) {
    $years[] = $row['year'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses List - Financial Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #0F1440;
            --primary-medium: #067ABF;
            --primary-light: #4A90E2;
            --dark-text: #212121;
            --light-text: #FFFFFF;
            --gray-light: #F5F5F5;
            --gray-medium: #E0E0E0;
            --success: #4CAF50;
            --warning: #FFC107;
            --danger: #F44336;
            --section-border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--dark-text);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Mobile Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary-dark);
            color: var(--light-text);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: var(--section-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--light-text);
        }
        
        .close-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }
        
        .sidebar-menu {
            padding: 15px 0;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }
        
        .menu-section {
            margin-bottom: 20px;
        }
        
        .menu-title {
            padding: 10px 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.7);
            border-bottom: var(--section-border);
            margin-bottom: 10px;
        }
        
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 3px 0;
        }
        
        .sidebar-menu a {
            color: var(--light-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px 12px 30px;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--primary-medium);
            border-left: 4px solid var(--primary-light);
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            transition: margin-left 0.3s ease;
            width: 100%;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            background: var(--primary-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .logout-btn {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background 0.3s;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: var(--primary-light);
        }
        
        .logout-btn i {
            margin-right: 5px;
        }
        
        /* Content Area */
        .content {
            padding: 20px;
        }
        
        .page-title {
            margin-bottom: 25px;
            color: var(--primary-dark);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        /* Statistics Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #757575;
        }
        
        .stat-card.total {
            border-left: 4px solid var(--danger);
        }
        
        .stat-card.current {
            border-left: 4px solid var(--primary-medium);
        }
        
        .stat-card.period {
            border-left: 4px solid var(--warning);
        }
        
        .stat-card.reason {
            border-left: 4px solid var(--success);
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filters-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 150px;
        }
        
        .filter-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #555;
        }
        
        .filter-select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
        }
        
        .search-form {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
        
        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            flex: 1;
        }
        
        .search-btn {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background 0.3s;
            white-space: nowrap;
        }
        
        .search-btn:hover {
            background: var(--primary-light);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .table th {
            background-color: var(--primary-dark);
            color: white;
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
        }
        
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .table tr:hover {
            background-color: #f1f7ff;
        }
        
        .payment-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-paid {
            background: #e8f5e8;
            color: var(--success);
        }
        
        .status-unpaid {
            background: #ffebee;
            color: var(--danger);
        }
        
        .status-partial {
            background: #fff8e1;
            color: var(--warning);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 13px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-view {
            background-color: var(--primary-medium);
            color: white;
        }
        
        .btn-view:hover {
            background-color: var(--primary-light);
        }
        
        .btn-edit {
            background-color: var(--warning);
            color: var(--dark-text);
        }
        
        .btn-edit:hover {
            background-color: #e0a800;
        }
        
        .btn-delete {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #e53935;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            list-style: none;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .page-item {
            display: inline-block;
        }
        
        .page-link {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: var(--primary-medium);
            text-decoration: none;
            transition: all 0.3s;
            display: block;
        }
        
        .page-link:hover {
            background-color: #f1f7ff;
            border-color: var(--primary-medium);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-medium);
            color: white;
            border-color: var(--primary-medium);
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.active .modal {
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }
        
        .modal-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: var(--danger);
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .detail-label {
            font-weight: 600;
            min-width: 120px;
            color: var(--dark-text);
            margin-bottom: 5px;
        }
        
        .detail-value {
            flex: 1;
            color: #555;
            min-width: 150px;
        }
        
        /* Form Elements in Modal */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            border-color: var(--primary-medium);
            outline: none;
            box-shadow: 0 0 0 3px rgba(6, 122, 191, 0.1);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .payment-status-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .partial-payment-group {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 6px;
            border-left: 4px solid var(--warning);
        }
        
        .partial-payment-group.active {
            display: block;
        }
        
        .balance-amount {
            margin-top: 10px;
            padding: 10px;
            background-color: #fff3cd;
            border-radius: 4px;
            font-weight: 500;
            color: #856404;
        }
        
        .form-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary-medium);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-light);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53935;
        }
        
        /* Total Amount Display */
        .total-amount {
            background: rgba(244, 67, 54, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        
        .total-label {
            font-size: 0.9rem;
            color: #757575;
            margin-bottom: 5px;
        }
        
        .total-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--danger);
        }
        
        .rupee-symbol {
            font-family: Arial, sans-serif;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: flex;
            background: var(--primary-medium);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 5px;
            font-size: 1.5rem;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }
        
        /* Mobile responsive table */
        .mobile-table {
            display: none;
        }
        
        .mobile-expense-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .mobile-card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .mobile-card-label {
            font-weight: 600;
            color: #555;
        }
        
        .mobile-card-value {
            color: #333;
            text-align: right;
        }
        
        .mobile-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .mobile-actions .btn {
            flex: 1;
            justify-content: center;
            padding: 10px;
        }
        
        /* Responsive Styles */
        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0);
                position: fixed;
            }
            
            .main-content {
                margin-left: 280px;
            }
            
            .menu-toggle {
                display: none;
            }
            
            .close-sidebar {
                display: none;
            }
        }
        
        @media (max-width: 991px) {
            .close-sidebar {
                display: block;
            }
            
            .sidebar-menu a span {
                display: inline;
            }
            
            .sidebar-menu a {
                padding: 12px 20px 12px 30px;
                justify-content: flex-start;
            }
            
            .sidebar-menu i {
                margin-right: 12px;
                font-size: 18px;
            }
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            .table-container {
                padding: 15px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filters-container {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .search-form {
                width: 100%;
            }
            
            .search-input {
                flex: 1;
                min-width: auto;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .desktop-table {
                display: none;
            }
            
            .mobile-table {
                display: block;
            }
        }
        
        @media (max-width: 576px) {
            .top-bar {
                padding: 15px;
            }
            
            .user-info span {
                display: none;
            }
            
            .modal {
                margin: 10px;
                width: calc(100% - 20px);
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                margin-bottom: 5px;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .form-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>MKN Bro's</h3>
            <button class="close-sidebar" id="closeSidebar">&times;</button>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                
                <div class="menu-section">
                    <div class="menu-title">Sales Management</div>
                    <li><a href="add_sales.php"><i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
                    <li><a href="sales_list.php"><i class="fas fa-list"></i> <span>Sales List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Customer Management</div>
                    <li><a href="add_customer.php"><i class="fas fa-user-plus"></i> <span>Add Customer</span></a></li>
                    <li><a href="customers_list.php"><i class="fas fa-users"></i> <span>Customers List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Expense Tracking</div>
                    <li><a href="add_amount.php"><i class="fas fa-plus-circle"></i> <span>Add Amount</span></a></li>
                    <li><a href="amounts_list.php"><i class="fas fa-list"></i> <span>Amounts List</span></a></li>
                    <li><a href="add_expense.php"><i class="fas fa-minus-circle"></i> <span>Add Expense</span></a></li>
                    <li><a href="expenses_list.php" class="active"><i class="fas fa-file-invoice-dollar"></i> <span>Expenses List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Product Management</div>
                    <li><a href="add_product.php"><i class="fas fa-cube"></i> <span>Add Product</span></a></li>
                    <li><a href="products_list.php"><i class="fas fa-cubes"></i> <span>Products List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">User Management</div>
                    <li><a href="users_list.php"><i class="fas fa-users"></i> <span>Users List</span></a></li>
                </div>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZiI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgM2MyLjIxIDAgNCA1Ljc5IDQgNHMtMS43OSA0LTQgNC00LTEuNzktNC00IDEuNzktNCA0LTR6bTAgMTcuMDJjLTMuMzMgMC02LjI4LTEuNzEtOC02LjAyIDIuMDUtMy4xNiA1LjI5LTUgOC41LTUgMy4yMSAwIDYuNDUgMS44NCA4LjUgNSAwLTQuMzEtMy41LTgtOC01LTguMDJ6Ii8+PC9zdmc+" alt="User">
                <span>Welcome, <?php echo $_SESSION['user']; ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <span class="logout-text">Logout</span>
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1 class="page-title"><i class="fas fa-file-invoice-dollar"></i> Expenses List</h1>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card total">
                    <div class="stat-icon" style="color: var(--danger);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-value">₹<?= number_format($total_expense ?: 0, 2) ?></div>
                    <div class="stat-label">Total Expenses</div>
                </div>
                
                <div class="stat-card current">
                    <div class="stat-icon" style="color: var(--primary-medium);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value">₹<?= number_format($current_month_total ?: 0, 2) ?></div>
                    <div class="stat-label">Current Month</div>
                </div>
                
                <div class="stat-card period">
                    <div class="stat-icon" style="color: var(--warning);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">₹<?= number_format($last_3_months_total ?: 0, 2) ?></div>
                    <div class="stat-label">Last 3 Months</div>
                </div>
                
                <div class="stat-card reason">
                    <?php if ($most_expensive_reason): ?>
                    <div class="stat-icon" style="color: var(--success);">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="stat-value">₹<?= number_format($most_expensive_reason['total'], 2) ?></div>
                    <div class="stat-label"><?= htmlspecialchars($most_expensive_reason['reason']) ?></div>
                    <?php else: ?>
                    <div class="stat-icon" style="color: var(--success);">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="stat-value">₹0.00</div>
                    <div class="stat-label">No Expenses</div>
                    <?php endif; ?>
                </div>
            </div>
            
           <!-- Replace the whole search + filters block with this -->

<div class="table-header">
    <h2>All Expenses</h2>
    
    <form method="get" class="search-and-filters">
        <!-- Search -->
        <div class="search-form">
            <input type="text" name="search" class="search-input" placeholder="Search by reason, added by, expense by..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i> Search
            </button>
        </div>

        <!-- Filters row -->
        <div class="filters-container">
            <div class="filter-group">
                <div class="filter-label">Year</div>
                <select name="year" class="filter-select">
                    <option value="">All Years</option>
                    <?php foreach($years as $y): ?>
                        <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-label">Month</div>
                <select name="month" class="filter-select">
                    <option value="0">All Months</option>
                    <?php foreach($months as $num => $name): ?>
                        <option value="<?= $num ?>" <?= ($month == $num) ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-label">Period</div>
                <select name="period" class="filter-select">
                    <option value="">All Periods</option>
                    <option value="current_month" <?= ($period == 'current_month') ? 'selected' : '' ?>>Current Month</option>
                    <option value="last_month"    <?= ($period == 'last_month')    ? 'selected' : '' ?>>Last Month</option>
                    <option value="last_3_months" <?= ($period == 'last_3_months') ? 'selected' : '' ?>>Last 3 Months</option>
                    <option value="last_6_months" <?= ($period == 'last_6_months') ? 'selected' : '' ?>>Last 6 Months</option>
                    <option value="last_12_months"<?= ($period == 'last_12_months')? 'selected' : '' ?>>Last 12 Months</option>
                </select>
            </div>

            <!-- Hidden inputs to preserve current page -->
            <input type="hidden" name="page" value="1">
        </div>
    </form>
</div>
                
                <!-- Desktop Table -->
                <div class="desktop-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount (₹)</th>
                                <th>Reason</th>
                                <th>Date</th>
                                <th>Added By</th>
                                <th>Expense By</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { 
                                $status_class = '';
                                switch($row['payment_status']) {
                                    case 'paid': $status_class = 'status-paid'; break;
                                    case 'unpaid': $status_class = 'status-unpaid'; break;
                                    case 'partial': $status_class = 'status-partial'; break;
                                }
                            ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>₹<?= number_format($row['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($row['reason']) ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= htmlspecialchars($row['added_by']) ?></td>
                                <td><?= htmlspecialchars($row['expense_by']) ?></td>
                                <td>
                                    <span class="payment-status <?= $status_class ?>">
                                        <?= ucfirst($row['payment_status']) ?>
                                        <?php if($row['payment_status'] == 'partial'): ?>
                                            (₹<?= number_format($row['partial_amount'], 2) ?>)
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-view" onclick="openModal('view', <?= $row['id'] ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-edit" onclick="openModal('edit', <?= $row['id'] ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-delete" onclick="openModal('delete', <?= $row['id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Table -->
                <div class="mobile-table">
                    <?php 
                    // Reset result pointer for mobile view
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()) { 
                        $status_class = '';
                        switch($row['payment_status']) {
                            case 'paid': $status_class = 'status-paid'; break;
                            case 'unpaid': $status_class = 'status-unpaid'; break;
                            case 'partial': $status_class = 'status-partial'; break;
                        }
                    ?>
                    <div class="mobile-expense-card">
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">ID</div>
                            <div class="mobile-card-value"><?= $row['id'] ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Amount</div>
                            <div class="mobile-card-value">₹<?= number_format($row['amount'], 2) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Reason</div>
                            <div class="mobile-card-value"><?= htmlspecialchars($row['reason']) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Date</div>
                            <div class="mobile-card-value"><?= $row['date'] ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Added By</div>
                            <div class="mobile-card-value"><?= htmlspecialchars($row['added_by']) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Expense By</div>
                            <div class="mobile-card-value"><?= htmlspecialchars($row['expense_by']) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Status</div>
                            <div class="mobile-card-value">
                                <span class="payment-status <?= $status_class ?>">
                                    <?= ucfirst($row['payment_status']) ?>
                                    <?php if($row['payment_status'] == 'partial'): ?>
                                        (₹<?= number_format($row['partial_amount'], 2) ?>)
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="mobile-actions">
                            <button class="btn btn-view" onclick="openModal('view', <?= $row['id'] ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-edit" onclick="openModal('edit', <?= $row['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-delete" onclick="openModal('delete', <?= $row['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                
                <!-- Pagination -->
                <ul class="pagination">
                    <?php for($i=1;$i<=$pages;$i++){ ?>
                        <li class="page-item <?= ($i==$page)?'active':'' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&year=<?= $year ?>&month=<?= $month ?>&period=<?= $period ?>"><?= $i ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-eye"></i> Expense Details</h2>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?php if ($expense_details && isset($_GET['view_id'])) { 
                    $status_class = '';
                    switch($expense_details['payment_status']) {
                        case 'paid': $status_class = 'status-paid'; break;
                        case 'unpaid': $status_class = 'status-unpaid'; break;
                        case 'partial': $status_class = 'status-partial'; break;
                    }
                ?>
                <div class="detail-row">
                    <div class="detail-label">ID:</div>
                    <div class="detail-value"><?= $expense_details['id'] ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Amount:</div>
                    <div class="detail-value">₹<?= number_format($expense_details['amount'], 2) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Reason:</div>
                    <div class="detail-value"><?= htmlspecialchars($expense_details['reason']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value"><?= $expense_details['date'] ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Added By:</div>
                    <div class="detail-value"><?= htmlspecialchars($expense_details['added_by']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Expense By:</div>
                    <div class="detail-value"><?= htmlspecialchars($expense_details['expense_by']) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Payment Status:</div>
                    <div class="detail-value">
                        <span class="payment-status <?= $status_class ?>">
                            <?= ucfirst($expense_details['payment_status']) ?>
                            <?php if($expense_details['payment_status'] == 'partial'): ?>
                                (₹<?= number_format($expense_details['partial_amount'], 2) ?> paid)
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <?php if($expense_details['payment_status'] == 'partial'): ?>
                <div class="detail-row">
                    <div class="detail-label">Balance Due:</div>
                    <div class="detail-value">₹<?= number_format($expense_details['amount'] - $expense_details['partial_amount'], 2) ?></div>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <div class="detail-label">Remarks:</div>
                    <div class="detail-value"><?= htmlspecialchars($expense_details['remarks']) ?></div>
                </div>
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button class="form-btn btn-secondary" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-edit"></i> Edit Expense</h2>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="post" action="update_expense.php">
                <div class="modal-body">
                    <?php if ($expense_details && isset($_GET['edit_id'])) { ?>
                    <input type="hidden" name="id" value="<?= $expense_details['id'] ?>">
                    <div class="form-group">
                        <label class="form-label" for="edit_amount">Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-input" id="edit_amount" name="amount" value="<?= $expense_details['amount'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_reason">Reason</label>
                        <input type="text" class="form-input" id="edit_reason" name="reason" value="<?= htmlspecialchars($expense_details['reason']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_expense_by">Expense Spend By</label>
                        <select class="form-select" id="edit_expense_by" name="expense_by" required>
                            <option value="">Select User</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?= $user ?>" <?= $expense_details['expense_by'] == $user ? 'selected' : '' ?>><?= $user ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group payment-status-group">
                        <label class="form-label" for="edit_payment_status">Payment Status</label>
                        <select class="form-select" id="edit_payment_status" name="payment_status" required>
                            <option value="paid" <?= $expense_details['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="unpaid" <?= $expense_details['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="partial" <?= $expense_details['payment_status'] == 'partial' ? 'selected' : '' ?>>Partial Paid</option>
                        </select>
                        
                        <div class="partial-payment-group <?= $expense_details['payment_status'] == 'partial' ? 'active' : '' ?>" id="editPartialPaymentGroup">
                            <label class="form-label" for="edit_partial_amount">Partial Amount Paid (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-input" id="edit_partial_amount" name="partial_amount" value="<?= $expense_details['partial_amount'] ?>" placeholder="Enter partial amount paid">
                            <div class="balance-amount" id="editBalanceAmount">
                                Balance to Pay: ₹<?= number_format($expense_details['amount'] - $expense_details['partial_amount'], 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_date">Date</label>
                        <input type="date" class="form-input" id="edit_date" name="date" value="<?= $expense_details['date'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_remarks">Remarks</label>
                        <textarea class="form-textarea" id="edit_remarks" name="remarks"><?= htmlspecialchars($expense_details['remarks']) ?></textarea>
                    </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="form-btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="form-btn btn-primary">Update Expense</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-trash"></i> Confirm Deletion</h2>
                <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this expense record?</p>
                <div class="detail-row">
                    <div class="detail-label">ID:</div>
                    <div class="detail-value" id="delete_id"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Amount:</div>
                    <div class="detail-value" id="delete_amount"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Reason:</div>
                    <div class="detail-value" id="delete_reason"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value" id="delete_date"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="form-btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <a id="deleteConfirmBtn" href="#" class="form-btn btn-danger">Delete</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('active');
        });
        
        // Close sidebar when clicking the close button
        document.getElementById('closeSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth < 992 && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Payment status functionality for edit modal
        document.addEventListener('DOMContentLoaded', function() {
            const paymentStatus = document.getElementById('edit_payment_status');
            const partialPaymentGroup = document.getElementById('editPartialPaymentGroup');
            const amountInput = document.getElementById('edit_amount');
            const partialAmountInput = document.getElementById('edit_partial_amount');
            const balanceAmount = document.getElementById('editBalanceAmount');
            
            if (paymentStatus) {
                paymentStatus.addEventListener('change', function() {
                    if (this.value === 'partial') {
                        partialPaymentGroup.classList.add('active');
                        calculateEditBalance();
                    } else {
                        partialPaymentGroup.classList.remove('active');
                    }
                });
                
                // Calculate balance when amount or partial amount changes
                if (amountInput) amountInput.addEventListener('input', calculateEditBalance);
                if (partialAmountInput) partialAmountInput.addEventListener('input', calculateEditBalance);
                
                function calculateEditBalance() {
                    const amount = parseFloat(amountInput.value) || 0;
                    const partialAmount = parseFloat(partialAmountInput.value) || 0;
                    const balance = amount - partialAmount;
                    
                    if (balance >= 0) {
                        balanceAmount.textContent = `Balance to Pay: ₹${balance.toFixed(2)}`;
                        balanceAmount.style.color = '#856404';
                    } else {
                        balanceAmount.textContent = 'Partial amount cannot exceed total amount';
                        balanceAmount.style.color = 'var(--danger)';
                    }
                }
            }
        });
        
        // Modal functions
        function openModal(type, id) {
            if (type === 'view') {
                window.location.href = `?view_id=${id}&search=<?= $search ?>&page=<?= $page ?>&year=<?= $year ?>&month=<?= $month ?>&period=<?= $period ?>`;
            } else if (type === 'edit') {
                window.location.href = `?edit_id=${id}&search=<?= $search ?>&page=<?= $page ?>&year=<?= $year ?>&month=<?= $month ?>&period=<?= $period ?>`;
            } else if (type === 'delete') {
                // Get row data for deletion confirmation
                const row = event.target.closest('.mobile-expense-card') || event.target.closest('tr');
                let id, amount, reason, date;
                
                if (row.classList.contains('mobile-expense-card')) {
                    // Mobile view
                    const rows = row.querySelectorAll('.mobile-card-row');
                    id = rows[0].querySelector('.mobile-card-value').textContent.trim();
                    amount = rows[1].querySelector('.mobile-card-value').textContent.trim();
                    reason = rows[2].querySelector('.mobile-card-value').textContent.trim();
                    date = rows[3].querySelector('.mobile-card-value').textContent.trim();
                } else {
                    // Desktop view
                    const cells = row.querySelectorAll('td');
                    id = cells[0].textContent;
                    amount = cells[1].textContent;
                    reason = cells[2].textContent;
                    date = cells[3].textContent;
                }
                
                document.getElementById('delete_id').textContent = id;
                document.getElementById('delete_amount').textContent = amount;
                document.getElementById('delete_reason').textContent = reason;
                document.getElementById('delete_date').textContent = date;
                
                document.getElementById('deleteConfirmBtn').href = `delete_expense.php?id=${id}&search=<?= $search ?>&page=<?= $page ?>&year=<?= $year ?>&month=<?= $month ?>&period=<?= $period ?>`;
                
                document.getElementById('deleteModal').classList.add('active');
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            
            // Clean up URL parameters when closing modals
            if (modalId === 'viewModal' || modalId === 'editModal') {
                const url = new URL(window.location);
                url.searchParams.delete('view_id');
                url.searchParams.delete('edit_id');
                window.history.replaceState({}, '', url);
            }
        }
        
        // Open modals if URL parameters are present
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('view_id')) {
                document.getElementById('viewModal').classList.add('active');
            }
            
            if (urlParams.has('edit_id')) {
                document.getElementById('editModal').classList.add('active');
            }
        });
        
        // Close modal when clicking outside
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });
        
        // Close sidebar on window resize if it becomes desktop view
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                document.getElementById('sidebar').classList.remove('active');
            }
        });
    </script>
</body>
</html>