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

$query = "SELECT * FROM amounts WHERE date LIKE '%$search%' OR added_by LIKE '%$search%' ORDER BY date DESC LIMIT $start, $limit";
$result = $conn->query($query);

$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM amounts WHERE date LIKE '%$search%' OR added_by LIKE '%$search%'");
$total = $totalQuery->fetch_assoc()['total'];
$pages = ceil($total / $limit);

// Get total amounts for display
$total_amount = $conn->query("SELECT SUM(amount) AS total FROM amounts")->fetch_assoc()['total'];

// Get amount details for view/edit if ID is provided
$amount_details = null;
if (isset($_GET['view_id'])) {
    $view_id = (int)$_GET['view_id'];
    $amount_details = $conn->query("SELECT * FROM amounts WHERE id = $view_id")->fetch_assoc();
}

if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $amount_details = $conn->query("SELECT * FROM amounts WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amounts List - Financial Management System</title>
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
            min-width: 600px;
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
            max-width: 500px;
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
        
        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        
        .form-input:focus {
            border-color: var(--primary-medium);
            outline: none;
            box-shadow: 0 0 0 3px rgba(6, 122, 191, 0.1);
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
            background: rgba(6, 122, 191, 0.1);
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
            color: var(--primary-medium);
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
        
        .mobile-amount-card {
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
                    <li><a href="amounts_list.php" class="active"><i class="fas fa-list"></i> <span>Amounts List</span></a></li>
                    <li><a href="add_expense.php"><i class="fas fa-minus-circle"></i> <span>Add Expense</span></a></li>
                    <li><a href="expenses_list.php"><i class="fas fa-file-invoice-dollar"></i> <span>Expenses List</span></a></li>
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
            <h1 class="page-title"><i class="fas fa-list"></i> Amounts List</h1>
            
            <div class="table-container">
                <div class="table-header">
                    <h2>All Amounts</h2>
                    <form method="get" class="search-form">
                        <input type="text" name="search" class="search-input" placeholder="Search by date or user..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> <span class="search-text">Search</span>
                        </button>
                    </form>
                </div>
                
                <!-- Desktop Table -->
                <div class="desktop-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount (₹)</th>
                                <th>Date</th>
                                <th>Added By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>₹<?= number_format($row['amount'], 2) ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $row['added_by'] ?></td>
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
                    while ($row = $result->fetch_assoc()) { ?>
                    <div class="mobile-amount-card">
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">ID</div>
                            <div class="mobile-card-value"><?= $row['id'] ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Amount</div>
                            <div class="mobile-card-value">₹<?= number_format($row['amount'], 2) ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Date</div>
                            <div class="mobile-card-value"><?= $row['date'] ?></div>
                        </div>
                        <div class="mobile-card-row">
                            <div class="mobile-card-label">Added By</div>
                            <div class="mobile-card-value"><?= $row['added_by'] ?></div>
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
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            
            <div class="total-amount">
                <div class="total-label">Total Amount Added</div>
                <div class="total-value"><span class="rupee-symbol">₹</span><?php echo number_format($total_amount ?: 0, 2); ?></div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-eye"></i> Amount Details</h2>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?php if ($amount_details && isset($_GET['view_id'])) { ?>
                <div class="detail-row">
                    <div class="detail-label">ID:</div>
                    <div class="detail-value"><?= $amount_details['id'] ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Amount:</div>
                    <div class="detail-value">₹<?= number_format($amount_details['amount'], 2) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value"><?= $amount_details['date'] ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Added By:</div>
                    <div class="detail-value"><?= $amount_details['added_by'] ?></div>
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
                <h2 class="modal-title"><i class="fas fa-edit"></i> Edit Amount</h2>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="post" action="update_amount.php">
                <div class="modal-body">
                    <?php if ($amount_details && isset($_GET['edit_id'])) { ?>
                    <input type="hidden" name="id" value="<?= $amount_details['id'] ?>">
                    <div class="form-group">
                        <label class="form-label" for="edit_amount">Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-input" id="edit_amount" name="amount" value="<?= $amount_details['amount'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit_date">Date</label>
                        <input type="date" class="form-input" id="edit_date" name="date" value="<?= $amount_details['date'] ?>" required>
                    </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="form-btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="form-btn btn-primary">Update Amount</button>
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
                <p>Are you sure you want to delete this amount record?</p>
                <div class="detail-row">
                    <div class="detail-label">ID:</div>
                    <div class="detail-value" id="delete_id"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Amount:</div>
                    <div class="detail-value" id="delete_amount"></div>
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
        
        // Modal functions
        function openModal(type, id) {
            if (type === 'view') {
                window.location.href = `?view_id=${id}&search=<?= $search ?>&page=<?= $page ?>`;
            } else if (type === 'edit') {
                window.location.href = `?edit_id=${id}&search=<?= $search ?>&page=<?= $page ?>`;
            } else if (type === 'delete') {
                // Get row data for deletion confirmation
                const row = event.target.closest('.mobile-amount-card') || event.target.closest('tr');
                let id, amount, date;
                
                if (row.classList.contains('mobile-amount-card')) {
                    // Mobile view
                    const rows = row.querySelectorAll('.mobile-card-row');
                    id = rows[0].querySelector('.mobile-card-value').textContent.trim();
                    amount = rows[1].querySelector('.mobile-card-value').textContent.trim();
                    date = rows[2].querySelector('.mobile-card-value').textContent.trim();
                } else {
                    // Desktop view
                    const cells = row.querySelectorAll('td');
                    id = cells[0].textContent;
                    amount = cells[1].textContent;
                    date = cells[2].textContent;
                }
                
                document.getElementById('delete_id').textContent = id;
                document.getElementById('delete_amount').textContent = amount;
                document.getElementById('delete_date').textContent = date;
                
                document.getElementById('deleteConfirmBtn').href = `delete_amount.php?id=${id}&search=<?= $search ?>&page=<?= $page ?>`;
                
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