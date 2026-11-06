<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch all customers from the database
$sql = "SELECT * FROM customers ORDER BY customer_name ASC";
$result = $conn->query($sql);

// Display success/error messages
$success_message = '';
$error_message = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers List - Financial Management System</title>
    <?php if (!empty($success_message)): ?>
    <div class="success-message" style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 6px; margin-bottom: 20px; display: flex; align-items: center;">
        <i class="fas fa-check-circle" style="margin-right: 10px;"></i> <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="error-message" style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 20px; display: flex; align-items: center;">
        <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i> <?php echo $error_message; ?>
    </div>
<?php endif; ?>
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
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-bottom: var(--section-border);
        }
        
        .sidebar-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--light-text);
        }
        
        .sidebar-menu {
            padding: 15px 0;
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
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        /* Top Bar */
        .top-bar {
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            padding: 30px;
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
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .table-title {
            color: var(--primary-dark);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .table-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
        .add-btn {
            background: var(--primary-medium);
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: background 0.3s;
        }
        
        .add-btn:hover {
            background: var(--primary-light);
        }
        
        .add-btn i {
            margin-right: 5px;
        }
        
        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .customer-table th, .customer-table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .customer-table th {
            background-color: var(--primary-medium);
            color: white;
            font-weight: 600;
        }
        
        .customer-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .customer-table tr:hover {
            background-color: #e9e9e9;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
            font-style: italic;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .edit-btn, .delete-btn {
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .edit-btn {
            background: var(--primary-light);
            color: white;
        }
        
        .edit-btn:hover {
            background: var(--primary-medium);
        }
        
        .delete-btn {
            background: var(--danger);
            color: white;
        }
        
        .delete-btn:hover {
            background: #d32f2f;
        }
        
        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
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
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                text-align: center;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .menu-title {
                display: none;
            }
            
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a {
                padding: 12px;
                justify-content: center;
            }
            
            .sidebar-menu i {
                margin-right: 0;
                font-size: 1.5rem;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -80px;
                width: 80px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: flex;
            }
            
            .content {
                padding: 15px;
            }
            
            .table-container {
                padding: 20px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .customer-table {
                font-size: 14px;
            }
            
            .customer-table th, 
            .customer-table td {
                padding: 8px;
            }
        }
        
        @media (max-width: 576px) {
            .top-bar {
                padding: 15px;
            }
            
            .user-info span {
                display: none;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>MKN Bro's</h3>
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
                    <li><a href="customers_list.php" class="active"><i class="fas fa-users"></i> <span>Customers List</span></a></li>
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
                
                <div class="menu-section">
                    <div class="menu-title">Purchase Management</div>
                    <li><a href="add_purchase.php"><i class="fas fa-shopping-cart"></i> <span>Add Purchase</span></a></li>
                    <li><a href="purchases_list.php"><i class="fas fa-shopping-basket"></i> <span>Purchases List</span></a></li>
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
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1 class="page-title"><i class="fas fa-users"></i> Customers List</h1>
            
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title"><i class="fas fa-list"></i> All Customers</h2>
                    <a href="add_customer.php" class="add-btn">
                        <i class="fas fa-plus"></i> Add New Customer
                    </a>
                </div>
                
                <?php if (!empty($customers)): ?>
                    <table class="customer-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Shop Name</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['shop_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['city']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['state']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="edit-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_customer.php?id=<?php echo $customer['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this customer?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">No customers found. <a href="add_customer.php">Add your first customer</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth < 768 && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>