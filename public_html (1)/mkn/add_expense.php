<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $remarks = $_POST['remarks'];
    $expense_by = $_POST['expense_by'];
    $payment_status = $_POST['payment_status'];
    $partial_amount = $_POST['partial_amount'] ?: 0;
    $user = $_SESSION['user'];

    $conn->query("INSERT INTO expenses (amount, reason, date, added_by, remarks, expense_by, payment_status, partial_amount) 
                  VALUES ('$amount', '$reason', '$date', '$user', '$remarks', '$expense_by', '$payment_status', '$partial_amount')");
    header("Location: dashboard.php");
    exit();
}

// Get total expenses for display
$total_expense = $conn->query("SELECT SUM(amount) AS total FROM expenses")->fetch_assoc()['total'];

// Get all users for the dropdown
$users_result = $conn->query("SELECT username FROM users");
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row['username'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense - Financial Management System</title>
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
        
        /* Form Container */
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .form-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }
        
        .form-title i {
            margin-right: 10px;
            color: var(--primary-medium);
        }
        
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
            padding: 14px;
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
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-btn {
            background: var(--primary-medium);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        
        .form-btn:hover {
            background: var(--primary-light);
        }
        
        .form-btn i {
            margin-right: 8px;
        }
        
        .total-expense {
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
        
        /* Payment Status Styles */
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
                width: 280px;
                text-align: left;
            }
            
            .sidebar-header h3 {
                display: block;
            }
            
            .menu-title {
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
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar {
                left: -280px;
            }
            
            .sidebar.active {
                left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }
            
            .content {
                padding: 15px;
            }
            
            .form-container {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .top-bar {
                padding: 15px;
            }
            
            .user-info span {
                display: none;
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
                    <li><a href="customers_list.php"><i class="fas fa-users"></i> <span>Customers List</span></a></li>
                </div>
                
                <div class="menu-section">
                    <div class="menu-title">Expense Tracking</div>
                    <li><a href="add_amount.php"><i class="fas fa-plus-circle"></i> <span>Add Amount</span></a></li>
                    <li><a href="amounts_list.php"><i class="fas fa-list"></i> <span>Amounts List</span></a></li>
                    <li><a href="add_expense.php" class="active"><i class="fas fa-minus-circle"></i> <span>Add Expense</span></a></li>
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
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <h1 class="page-title"><i class="fas fa-minus-circle"></i> Add Expense</h1>
            
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title"><i class="fas fa-money-bill-wave"></i> Add New Expense</h2>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label class="form-label" for="amount">Expense Amount (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-input" id="amount" name="amount" placeholder="Enter expense amount" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="reason">Reason</label>
                        <input type="text" class="form-input" id="reason" name="reason" placeholder="Enter reason for expense" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="expense_by">Expense Spend By</label>
                        <select class="form-input" id="expense_by" name="expense_by" required>
                            <option value="">Select User</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?php echo $user; ?>"><?php echo $user; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group payment-status-group">
                        <label class="form-label" for="payment_status">Payment Status</label>
                        <select class="form-input" id="payment_status" name="payment_status" required>
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial Paid</option>
                        </select>
                        
                        <div class="partial-payment-group" id="partialPaymentGroup">
                            <label class="form-label" for="partial_amount">Partial Amount Paid (₹)</label>
                            <input type="number" step="0.01" min="0" class="form-input" id="partial_amount" name="partial_amount" placeholder="Enter partial amount paid">
                            <div class="balance-amount" id="balanceAmount">Balance to Pay: ₹0.00</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="date">Date</label>
                        <input type="date" class="form-input" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="remarks">Remarks</label>
                        <textarea class="form-input form-textarea" id="remarks" name="remarks" placeholder="Enter any additional remarks"></textarea>
                    </div>
                    
                    <button type="submit" class="form-btn">
                        <i class="fas fa-plus-circle"></i> Add Expense
                    </button>
                </form>
                
                <div class="total-expense">
                    <div class="total-label">Total Expenses</div>
                    <div class="total-value"><span class="rupee-symbol">₹</span><?php echo number_format($total_expense ?: 0, 2); ?></div>
                </div>
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
            
            if (window.innerWidth < 992 && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Set default date to today if not set
        document.addEventListener('DOMContentLoaded', function() {
            const dateField = document.getElementById('date');
            if (dateField && !dateField.value) {
                dateField.valueAsDate = new Date();
            }
            
            // Payment status change handler
            const paymentStatus = document.getElementById('payment_status');
            const partialPaymentGroup = document.getElementById('partialPaymentGroup');
            const amountInput = document.getElementById('amount');
            const partialAmountInput = document.getElementById('partial_amount');
            const balanceAmount = document.getElementById('balanceAmount');
            
            paymentStatus.addEventListener('change', function() {
                if (this.value === 'partial') {
                    partialPaymentGroup.classList.add('active');
                    calculateBalance();
                } else {
                    partialPaymentGroup.classList.remove('active');
                }
            });
            
            // Calculate balance when amount or partial amount changes
            amountInput.addEventListener('input', calculateBalance);
            partialAmountInput.addEventListener('input', calculateBalance);
            
            function calculateBalance() {
                const amount = parseFloat(amountInput.value) || 0;
                const partialAmount = parseFloat(partialAmountInput.value) || 0;
                const balance = amount - partialAmount;
                
                if (balance >= 0) {
                    balanceAmount.textContent = `Balance to Pay: ₹${balance.toFixed(2)}`;
                } else {
                    balanceAmount.textContent = 'Partial amount cannot exceed total amount';
                    balanceAmount.style.color = 'var(--danger)';
                }
            }
        });
    </script>
</body>
</html>