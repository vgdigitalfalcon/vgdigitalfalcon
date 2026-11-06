<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Initialize variables to prevent undefined variable errors
$total_investment = 0;
$expense = 0;
$current_balance = 0;
$total_amount = 0;
$recent_activities = [];
$yet_to_pay = 0;
$current_month_sales = 0;
$last_month_sales = 0;
$current_month_expense = 0;
$last_month_expense = 0;
$sales_data = [];
$expense_data = [];

try {
    // Get total investment (previously total balance)
    $total_result = $conn->query("SELECT SUM(amount) AS total FROM amounts");
    if ($total_result && $total_row = $total_result->fetch_assoc()) {
        $total_investment = $total_row['total'] ?: 0;
    }

    // Get total expenses
    $expense_result = $conn->query("SELECT SUM(amount) AS total FROM expenses");
    if ($expense_result && $expense_row = $expense_result->fetch_assoc()) {
        $expense = $expense_row['total'] ?: 0;
    }

    // Get total paid sales amount
    $paid_sales_result = $conn->query("SELECT SUM(amount_paid) AS total FROM sales WHERE payment_status IN ('paid', 'partially_paid')");
    if ($paid_sales_result && $paid_sales_row = $paid_sales_result->fetch_assoc()) {
        $paid_sales = $paid_sales_row['total'] ?: 0;
    }

    // Calculate total amount (Investment + Paid Sales)
    $total_amount = $total_investment + $paid_sales;

    // Calculate current balance (Total Amount - Expenses)
    $current_balance = $total_amount - $expense;

    // Get amount yet to pay
    $yet_to_pay_result = $conn->query("SELECT SUM(amount_due) AS total FROM sales WHERE payment_status IN ('not_paid', 'partially_paid')");
    if ($yet_to_pay_result && $yet_to_pay_row = $yet_to_pay_result->fetch_assoc()) {
        $yet_to_pay = $yet_to_pay_row['total'] ?: 0;
    }

    // Get current month sales
    $current_month_sales_result = $conn->query("
        SELECT SUM(grand_total) AS total 
        FROM sales 
        WHERE MONTH(billing_date) = MONTH(CURRENT_DATE()) 
        AND YEAR(billing_date) = YEAR(CURRENT_DATE())
    ");
    if ($current_month_sales_result && $current_month_sales_row = $current_month_sales_result->fetch_assoc()) {
        $current_month_sales = $current_month_sales_row['total'] ?: 0;
    }

    // Get last month sales
    $last_month_sales_result = $conn->query("
        SELECT SUM(grand_total) AS total 
        FROM sales 
        WHERE MONTH(billing_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
        AND YEAR(billing_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
    ");
    if ($last_month_sales_result && $last_month_sales_row = $last_month_sales_result->fetch_assoc()) {
        $last_month_sales = $last_month_sales_row['total'] ?: 0;
    }

    // Get current month expense
    $current_month_expense_result = $conn->query("
        SELECT SUM(amount) AS total 
        FROM expenses 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    if ($current_month_expense_result && $current_month_expense_row = $current_month_expense_result->fetch_assoc()) {
        $current_month_expense = $current_month_expense_row['total'] ?: 0;
    }

    // Get last month expense
    $last_month_expense_result = $conn->query("
        SELECT SUM(amount) AS total 
        FROM expenses 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
    ");
    if ($last_month_expense_result && $last_month_expense_row = $last_month_expense_result->fetch_assoc()) {
        $last_month_expense = $last_month_expense_row['total'] ?: 0;
    }

    // Get sales data for last 6 months for graph
    $sales_graph_result = $conn->query("
        SELECT 
            DATE_FORMAT(billing_date, '%Y-%m') as month,
            SUM(grand_total) as total_sales
        FROM sales 
        WHERE billing_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(billing_date, '%Y-%m')
        ORDER BY month ASC
        LIMIT 6
    ");
    
    $sales_data = [];
    if ($sales_graph_result) {
        while($row = $sales_graph_result->fetch_assoc()) {
            $sales_data[] = $row;
        }
    }

    // Get expense data for last 6 months for graph
    $expense_graph_result = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(amount) as total_expense
        FROM expenses 
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
        LIMIT 6
    ");
    
    $expense_data = [];
    if ($expense_graph_result) {
        while($row = $expense_graph_result->fetch_assoc()) {
            $expense_data[] = $row;
        }
    }

} catch (Exception $e) {
    // Log error but don't display to user for security
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Financial Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --info: #2196F3;
            --purple: #9C27B0;
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
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: var(--primary-dark);
            color: var(--light-text);
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transform: translateX(-100%);
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: var(--section-border);
            position: relative;
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--light-text);
        }
        
        .close-sidebar {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
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
        
        /* Dashboard Cards - 3x3 Grid */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 25px;
            transition: transform 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1.1rem;
            color: var(--dark-text);
            font-weight: 500;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .card-amount {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .rupee-symbol {
            font-family: Arial, sans-serif;
        }
        
        .card-desc {
            color: #757575;
            font-size: 0.9rem;
        }
        
        .card-trend {
            display: flex;
            align-items: center;
            margin-top: 10px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .trend-up {
            color: var(--success);
        }
        
        .trend-down {
            color: var(--danger);
        }
        
        .trend-icon {
            margin-right: 5px;
        }
        
        .total-investment .card-icon {
            background: rgba(33, 150, 243, 0.2);
            color: var(--info);
        }
        
        .total-amount .card-icon {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }
        
        .total-expense .card-icon {
            background: rgba(255, 193, 7, 0.2);
            color: var(--warning);
        }
        
        .current-balance .card-icon {
            background: rgba(6, 122, 191, 0.2);
            color: var(--primary-medium);
        }
        
        .yet-to-pay .card-icon {
            background: rgba(156, 39, 176, 0.2);
            color: var(--purple);
        }
        
        .current-month-sales .card-icon {
            background: rgba(76, 175, 80, 0.2);
            color: var(--success);
        }
        
        .current-month-expense .card-icon {
            background: rgba(255, 193, 7, 0.2);
            color: var(--warning);
        }
        
        .last-month-sales .card-icon {
            background: rgba(33, 150, 243, 0.2);
            color: var(--info);
        }
        
        .last-month-expense .card-icon {
            background: rgba(244, 67, 54, 0.2);
            color: var(--danger);
        }
        
        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 25px;
        }
        
        .chart-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }
        
        .chart-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        /* Quick Actions - Extended */
        .quick-actions {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--gray-light);
            padding: 20px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark-text);
            transition: all 0.3s;
            text-align: center;
        }
        
        .action-btn:hover {
            background: var(--primary-medium);
            color: white;
            transform: translateY(-3px);
        }
        
        .action-btn i {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .action-btn span {
            font-weight: 500;
            font-size: 0.9rem;
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
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 280px;
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .close-sidebar {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content {
                padding: 15px;
            }
            
            .dashboard-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                padding: 15px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .chart-container {
                padding: 15px;
            }
            
            .chart-wrapper {
                height: 250px;
            }
            
            .charts-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .user-info span {
                display: none;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .card {
                padding: 20px;
            }
            
            .card-amount {
                font-size: 1.5rem;
            }
            
            .chart-container {
                padding: 10px;
            }
            
            .chart-wrapper {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>MKN Bro's</h3>
            <button class="close-sidebar" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                
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
                    <li><a href="expenses_list.php"><i class="fas fa-file-invoice-dollar"></i> <span>Expenses List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Product Management</div>
                    <li><a href="add_product.php"><i class="fas fa-cube"></i> <span>Add Product</span></a></li>
                    <li><a href="products_list.php"><i class="fas fa-cubes"></i> <span>Products List</span></a></li>
                </div>
                
                <!-- Purchase Management section removed as requested -->
                
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
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2ZmZiI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgM2MyLjIxIDAgNCAxLjc5IDQgNHMtMS43OSA0LTQgNC00LTEuNzktNC00IDEuNzktNCA0LTR6bTAgMTcuMDJjLTMuMzMgMC02LjI4LTEuNzEtOC02LjAyIDIuMDUtMy4xNiA1LjI5LTUgOC41LTUgMy4yMSAwIDYuNDUgMS44NCA4LjUgNSAwLTQuMzEtMy41LTgtOC01LTguMDJ6Ii8+PC9zdmc+" alt="User">
                <span>Welcome, <?php echo $_SESSION['user']; ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1 class="page-title"><i class="fas fa-home"></i> Dashboard</h1>
            
            <!-- Financial Summary Cards - 3x3 Grid -->
            <div class="dashboard-cards">
                <div class="card total-investment">
                    <div class="card-header">
                        <h3 class="card-title">Total Investment</h3>
                        <div class="card-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <div class="card-amount"><span class="rupee-symbol">₹</span><?php echo number_format($total_investment ?: 0, 2); ?></div>
                    <p class="card-desc">Total funds invested</p>
                </div>
                
                <div class="card total-amount">
                    <div class="card-header">
                        <h3 class="card-title">Total Amount</h3>
                        <div class="card-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="card-amount"><span class="rupee-symbol">₹</span><?php echo number_format($total_amount ?: 0, 2); ?></div>
                    <p class="card-desc">Investment + Paid Sales</p>
                </div>
                
                <div class="card total-expense">
                    <div class="card-header">
                        <h3 class="card-title">Total Expenses</h3>
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="card-amount"><span class="rupee-symbol">₹</span><?php echo number_format($expense ?: 0, 2); ?></div>
                    <p class="card-desc">Total amount spent</p>
                </div>
                
                <div class="card current-balance">
                    <div class="card-header">
                        <h3 class="card-title">Current Balance</h3>
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="card-amount"><span class="rupee-symbol">₹</span><?php echo number_format($current_balance ?: 0, 2); ?></div>
                    <p class="card-desc">Total Amount - Expenses</p>
                </div>

                <div class="card yet-to-pay">
                    <div class="card-header">
                        <h3 class="card-title">Amount Yet to Pay</h3>
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="card-amount"><span class="rupee-symbol">₹</span><?php echo number_format($yet_to_pay ?: 0, 2); ?></div>
                    <p class="card-desc">Pending payments from customers</p>
                </div>

                <div class="card current-month-sales">
                    <div class="card-header">
                        <h3 class="card-title">Current Month Sales</h3>
                        <div class="card-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="card-amount"><span class="rupee-symbol">₹</span><?php echo number_format($current_month_sales ?: 0, 2); ?></div>
                    <div class="card-trend <?php echo $current_month_sales > $last_month_sales ? 'trend-up' : 'trend-down'; ?>">
                        <i class="fas fa-arrow-<?php echo $current_month_sales > $last_month_sales ? 'up' : 'down'; ?> trend-icon"></i>
                        <?php 
                        if ($last_month_sales > 0) {
                            $change = (($current_month_sales - $last_month_sales) / $last_month_sales) * 100;
                            echo abs(round($change, 1)) . '% from last month';
                        } else {
                            echo 'No previous data';
                        }
                        ?>
                    </div>
                </div>

             

               
                
            </div>

            
            <!-- Extended Quick Actions -->
            <div class="quick-actions">
                <h2 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="action-buttons">
                    <a href="add_amount.php" class="action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Amount</span>
                    </a>
                    <a href="add_expense.php" class="action-btn">
                        <i class="fas fa-minus-circle"></i>
                        <span>Add Expense</span>
                    </a>
                    <a href="add_sales.php" class="action-btn">
                        <i class="fas fa-cart-plus"></i>
                        <span>Add Sales</span>
                    </a>
                    <a href="add_product.php" class="action-btn">
                        <i class="fas fa-cube"></i>
                        <span>Add Product</span>
                    </a>
                    <a href="sales_list.php" class="action-btn">
                        <i class="fas fa-list"></i>
                        <span>View Sales</span>
                    </a>
                    <a href="expenses_list.php" class="action-btn">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>View Expenses</span>
                    </a>
                   
                    <a href="products_list.php" class="action-btn">
                        <i class="fas fa-cubes"></i>
                        <span>View Products</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar functionality
        const menuToggle = document.getElementById('menuToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
        });
        
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Charts Data
        const salesData = <?php echo json_encode($sales_data); ?>;
        const expenseData = <?php echo json_encode($expense_data); ?>;

        // Format month names for display
        function formatMonth(monthStr) {
            const date = new Date(monthStr + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
        }

        // Prepare chart data
        const salesLabels = salesData.map(item => formatMonth(item.month));
        const salesValues = salesData.map(item => parseFloat(item.total_sales) || 0);

        const expenseLabels = expenseData.map(item => formatMonth(item.month));
        const expenseValues = expenseData.map(item => parseFloat(item.total_expense) || 0);

        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Sales Amount',
                    data: salesValues,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4CAF50',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(expenseCtx, {
            type: 'bar',
            data: {
                labels: expenseLabels,
                datasets: [{
                    label: 'Expense Amount',
                    data: expenseValues,
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 2,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Expense: ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>