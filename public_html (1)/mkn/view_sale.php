<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if a sale ID is provided
if (!isset($_GET['id'])) {
    header("Location: sales_list.php?message=❌ No sale ID provided.");
    exit();
}

$sale_id = $_GET['id'];

// Fetch sale details
$sql = "SELECT s.*, c.customer_name, c.shop_name, c.city, c.state, c.phone, c.email
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$sale_result = $stmt->get_result();

if ($sale_result->num_rows === 0) {
    header("Location: sales_list.php?message=❌ Sale not found.");
    exit();
}

$sale = $sale_result->fetch_assoc();

// Fetch sale items
$items_sql = "SELECT si.quantity, si.rate, si.mrp, si.discount, si.taxable_value, p.product_name, p.piece_weight
              FROM sale_items si
              LEFT JOIN products p ON si.product_id = p.id
              WHERE si.sale_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$sale_items = [];
while ($row = $items_result->fetch_assoc()) {
    $sale_items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sale - Financial Management System</title>
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
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
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
        
        .sale-details-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px;
            margin: 0 auto;
        }
        
        .sale-details-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sale-details-header h2 {
            font-size: 1.8rem;
            color: var(--primary-dark);
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .detail-section {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid var(--primary-medium);
        }
        
        .detail-section h3 {
            margin-bottom: 10px;
            color: var(--primary-dark);
            font-size: 1.2rem;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-weight: 600;
            color: var(--dark-text);
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .product-table th, .product-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .product-table th {
            background-color: var(--primary-medium);
            color: white;
        }

        .product-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .summary-section {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-label {
            font-weight: 500;
            color: var(--dark-text);
        }

        .summary-value {
            font-weight: 700;
            color: var(--primary-dark);
        }

        .grand-total {
            font-size: 1.4rem;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid var(--primary-medium);
        }

        .print-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }
        .print-btn:hover {
            background: #5a6268;
        }

        .print-btn i {
            margin-right: 8px;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                text-align: center;
            }
            .sidebar-header h3, .menu-title, .sidebar-menu a span {
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
            .sale-details-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
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
                    <li><a href="sales_list.php" class="active"><i class="fas fa-list"></i> <span>Sales List</span></a></li>
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

    <div class="main-content">
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

        <div class="content">
            <h1 class="page-title"><i class="fas fa-eye"></i> View Sale</h1>

            <div class="sale-details-container">
                <div class="sale-details-header">
                    <h2>Invoice: <?php echo htmlspecialchars($sale['invoice_no']); ?></h2>
                    <a href="sales_list.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to List</a>
                </div>

                <div class="detail-section">
                    <h3>Sale Details</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Invoice No:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['invoice_no']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Billing Date:</span>
                            <span class="detail-value"><?php echo date('d M Y', strtotime($sale['billing_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Mode of Transport:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['transport_mode']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Custom Transport:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['custom_transport'] ?: 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Transportation Cost:</span>
                            <span class="detail-value">₹<?php echo number_format($sale['transport_cost'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">GST Applied:</span>
                            <span class="detail-value"><?php echo $sale['apply_gst'] ? 'Yes' : 'No'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Customer Details</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Customer Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['customer_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Shop Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['shop_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">City:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['city']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">State:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['state']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['phone']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($sale['email']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Products Sold</h3>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Weight (g)</th>
                                <th>MRP</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Discount (%)</th>
                                <th>Taxable Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sale_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['piece_weight']); ?></td>
                                    <td>₹<?php echo number_format($item['mrp'], 2); ?></td>
                                    <td><?php echo intval($item['quantity']); ?></td>
                                    <td>₹<?php echo number_format($item['rate'], 2); ?></td>
                                    <td><?php echo intval($item['discount']); ?>%</td>
                                    <td>₹<?php echo number_format($item['taxable_value'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-section">
                    <div class="summary-row">
                        <span class="summary-label">Total Taxable Value:</span>
                        <span class="summary-value">₹<?php echo number_format($sale['total_taxable_value'], 2); ?></span>
                    </div>
                    <?php if ($sale['apply_gst']): ?>
                    <div class="summary-row">
                        <span class="summary-label">CGST (2.5%):</span>
                        <span class="summary-value">₹<?php echo number_format($sale['cgst'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">SGST (2.5%):</span>
                        <span class="summary-value">₹<?php echo number_format($sale['sgst'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span class="summary-label">Transportation Cost:</span>
                        <span class="summary-value">₹<?php echo number_format($sale['transport_cost'], 2); ?></span>
                    </div>
                    <div class="summary-row grand-total">
                        <span class="summary-label">Grand Total:</span>
                        <span class="summary-value">₹<?php echo number_format($sale['grand_total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth < 768 && !sidebar.contains(event.target) && !menuToggle.contains(event.target) && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>