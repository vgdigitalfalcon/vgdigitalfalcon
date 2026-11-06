<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if a sale ID is provided
if (!isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sales_list.php?message=❌ No sale ID provided.");
    exit();
}

$message = "";

// Fetch customers for the dropdown
$customers = [];
$customer_result = $conn->query("SELECT id, customer_name, shop_name, city, state, phone, email FROM customers ORDER BY customer_name ASC");
if ($customer_result->num_rows > 0) {
    while ($row = $customer_result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch products for the product dropdown
$products = [];
$product_result = $conn->query("SELECT id, product_name, piece_weight FROM products ORDER BY product_name ASC");
if ($product_result->num_rows > 0) {
    while ($row = $product_result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Initialize variables
$sale = [];
$sale_items = [];
$sale_id = $_GET['id'] ?? ($_POST['sale_id'] ?? 0);

// Fetch existing sale data for the form
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $sale_id = $_GET['id'];
    $sale_sql = "SELECT * FROM sales WHERE id = ?";
    $sale_stmt = $conn->prepare($sale_sql);
    $sale_stmt->bind_param("i", $sale_id);
    $sale_stmt->execute();
    $sale = $sale_stmt->get_result()->fetch_assoc();

    if (!$sale) {
        header("Location: sales_list.php?message=❌ Sale not found.");
        exit();
    }

    $sale_items_sql = "SELECT * FROM sale_items WHERE sale_id = ?";
    $sale_items_stmt = $conn->prepare($sale_items_sql);
    $sale_items_stmt->bind_param("i", $sale_id);
    $sale_items_stmt->execute();
    $sale_items = $sale_items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id = $_POST['sale_id'];
    $customer_id = $_POST['customer_id'];
    $billing_date = $_POST['billing_date'];
    $transport_mode = $_POST['transport_mode'];
    $custom_transport = $_POST['custom_transport'] ?? '';
    $transport_cost = $_POST['transport_cost'] ?? 0;
    $apply_gst = isset($_POST['apply_gst']) ? 1 : 0;
    $payment_status = $_POST['payment_status'];
    $amount_paid = $_POST['amount_paid'] ?? 0;
    
    // Get product details
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $rates = $_POST['rate'];
    $mrps = $_POST['mrp'];
    $discounts = $_POST['discount'];
    
    // Calculate totals
    $total_taxable_value = 0;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First calculate taxable value from all items
        for ($i = 0; $i < count($product_ids); $i++) {
            if (!empty($product_ids[$i]) && $quantities[$i] != 0) {
                $taxable_value = ($rates[$i] * $quantities[$i]) - (($rates[$i] * $quantities[$i]) * ($discounts[$i] / 100));
                $total_taxable_value += $taxable_value;
            }
        }
        
        // Now calculate GST and grand total
        $cgst = $apply_gst ? ($total_taxable_value * 2.5) / 100 : 0;
        $sgst = $apply_gst ? ($total_taxable_value * 2.5) / 100 : 0;
        $grand_total = $total_taxable_value + $cgst + $sgst + $transport_cost;
        
        // Auto-calculate amount paid based on status if not provided
        if ($payment_status === 'paid') {
            $amount_paid = $grand_total;
        } elseif ($payment_status === 'not_paid') {
            $amount_paid = 0;
        }
        
        $amount_due = $grand_total - $amount_paid;
        
        // Update sale
        $update_sale_sql = "UPDATE sales SET customer_id = ?, billing_date = ?, transport_mode = ?, custom_transport = ?, transport_cost = ?, apply_gst = ?, payment_status = ?, amount_paid = ?, amount_due = ?, total_taxable_value = ?, cgst = ?, sgst = ?, grand_total = ? WHERE id = ?";
        $update_sale_stmt = $conn->prepare($update_sale_sql);
        $update_sale_stmt->bind_param("isssdissdddddi", 
            $customer_id, 
            $billing_date, 
            $transport_mode, 
            $custom_transport, 
            $transport_cost, 
            $apply_gst, 
            $payment_status, 
            $amount_paid, 
            $amount_due,
            $total_taxable_value,
            $cgst,
            $sgst,
            $grand_total,
            $sale_id
        );
        $update_sale_stmt->execute();

        // Delete existing sale items
        $delete_items_sql = "DELETE FROM sale_items WHERE sale_id = ?";
        $delete_items_stmt = $conn->prepare($delete_items_sql);
        $delete_items_stmt->bind_param("i", $sale_id);
        $delete_items_stmt->execute();

        // Insert new sale items
        $item_sql = "INSERT INTO sale_items (sale_id, product_id, quantity, rate, mrp, discount, taxable_value)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        
        for ($i = 0; $i < count($product_ids); $i++) {
            if (!empty($product_ids[$i]) && $quantities[$i] != 0) {
                $taxable_value = ($rates[$i] * $quantities[$i]) - (($rates[$i] * $quantities[$i]) * ($discounts[$i] / 100));
                
                $item_stmt->bind_param("iiidddd", $sale_id, $product_ids[$i], $quantities[$i], $rates[$i], $mrps[$i], $discounts[$i], $taxable_value);
                $item_stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        $message = "✅ Sale updated successfully!";
        
        // Refetch data to display the updated values
        $sale_sql = "SELECT * FROM sales WHERE id = ?";
        $sale_stmt = $conn->prepare($sale_sql);
        $sale_stmt->bind_param("i", $sale_id);
        $sale_stmt->execute();
        $sale = $sale_stmt->get_result()->fetch_assoc();
        
        $sale_items_sql = "SELECT * FROM sale_items WHERE sale_id = ?";
        $sale_items_stmt = $conn->prepare($sale_items_sql);
        $sale_items_stmt->bind_param("i", $sale_id);
        $sale_items_stmt->execute();
        $sale_items = $sale_items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
    } catch (Exception $e) {
        $conn->rollback();
        $message = "❌ Error updating sale: " . $e->getMessage();
        error_log("Sale update error: " . $e->getMessage());
    }
}

// Get customer details for display
$sale_customer = [];
if (!empty($sale) && isset($sale['customer_id'])) {
    foreach ($customers as $customer) {
        if ($customer['id'] == $sale['customer_id']) {
            $sale_customer = $customer;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sale - Financial Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS styles remain the same */
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
        
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            padding: 30px;
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
        
        .form-input, .form-select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        
        .form-input:focus, .form-select:focus {
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
        }
        
        .form-btn:hover {
            background: var(--primary-light);
        }
        
        .form-btn i {
            margin-right: 8px;
        }
        
        .message {
            padding: 15px;
            margin-top: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }
        
        .success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-col {
            flex: 1;
        }
        
        .customer-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-medium);
        }
        
        .customer-details h3 {
            margin-bottom: 15px;
            color: var(--primary-dark);
        }
        
        .customer-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .product-table th {
            background-color: var(--primary-medium);
            color: white;
            text-align: left;
            padding: 12px;
        }
        
        .product-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .product-table input[type="number"] {
            width: 80px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .add-product-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .add-product-btn i {
            margin-right: 5px;
        }
        
        .add-product-btn:hover {
            background: #3d8b40;
        }
        
        .remove-product-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .remove-product-btn:hover {
            background: #d32f2f;
        }
        
        .summary-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-label {
            font-weight: 500;
        }
        
        .summary-value {
            font-weight: 600;
        }
        
        .grand-total {
            font-size: 1.2rem;
            color: var(--primary-dark);
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid var(--primary-medium);
        }
        
        .search-container {
            position: relative;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 6px 6px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
        }
        
        .search-result-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        
        .search-result-item:hover {
            background-color: #f0f0f0;
        }
        
        /* Payment Status Styles */
        .payment-status-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--primary-medium);
        }
        
        .payment-status-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .payment-status-select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .amount-paid-input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .amount-due-display {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            font-weight: 500;
        }
        
        .payment-status-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }
        
        .status-not-paid {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        
        .status-partially-paid {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
            border: 1px solid var(--warning);
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
        
        /* Mobile Responsive Styles */
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
            
            .form-container {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .product-table {
                font-size: 14px;
            }
            
            .product-table input[type="number"] {
                width: 60px;
            }
            
            .customer-info-grid {
                grid-template-columns: 1fr;
            }
            
            .payment-status-form {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .top-bar {
                padding: 15px;
            }
            
            .user-info span {
                display: none;
            }
            
            .product-table {
                display: block;
                overflow-x: auto;
            }
            
            .product-table th, .product-table td {
                padding: 8px;
                font-size: 13px;
            }
            
            .form-btn {
                padding: 12px 18px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 576px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-title {
                font-size: 1.3rem;
            }
            
            .form-input, .form-select {
                padding: 12px;
                font-size: 14px;
            }
            
            .customer-details, .payment-status-section {
                padding: 15px;
            }
            
            .summary-section {
                padding: 15px;
            }
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
                
                <!-- Purchase Management section removed as requested -->
                
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
            <h1 class="page-title"><i class="fas fa-edit"></i> Edit Sale</h1>
            
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">Edit Invoice No: <?php echo htmlspecialchars($sale['invoice_no'] ?? ''); ?></h2>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="salesForm">
                    <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['id'] ?? ''); ?>">
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="customer_search">Select Customer *</label>
                                <div class="search-container">
                                    <input type="text" class="form-input" id="customer_search" placeholder="Search customer by name..." autocomplete="off" value="<?php echo htmlspecialchars($sale_customer['customer_name'] ?? ''); ?>">
                                    <div class="search-results" id="customer_results"></div>
                                </div>
                                <input type="hidden" id="customer_id" name="customer_id" value="<?php echo htmlspecialchars($sale['customer_id'] ?? ''); ?>" required>
                                <div class="customer-details" id="customer_details" style="<?php echo !empty($sale_customer) ? 'display: block;' : 'display: none;'; ?>">
                                    <h3>Customer Details</h3>
                                    <div class="customer-info-grid">
                                        <div class="info-item">
                                            <span class="info-label">Shop Name</span>
                                            <span class="info-value" id="detail_shop"><?php echo htmlspecialchars($sale_customer['shop_name'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">City</span>
                                            <span class="info-value" id="detail_city"><?php echo htmlspecialchars($sale_customer['city'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">State</span>
                                            <span class="info-value" id="detail_state"><?php echo htmlspecialchars($sale_customer['state'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Phone</span>
                                            <span class="info-value" id="detail_phone"><?php echo htmlspecialchars($sale_customer['phone'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email</span>
                                            <span class="info-value" id="detail_email"><?php echo htmlspecialchars($sale_customer['email'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="billing_date">Date of Billing *</label>
                                <input type="date" class="form-input" id="billing_date" name="billing_date" value="<?php echo htmlspecialchars($sale['billing_date'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="transport_mode">Mode of Transport *</label>
                                <select class="form-select" id="transport_mode" name="transport_mode" required>
                                    <option value="">Select Transport Mode</option>
                                    <option value="Motor Cycle" <?php echo ($sale['transport_mode'] ?? '') == 'Motor Cycle' ? 'selected' : ''; ?>>Motor Cycle</option>
                                    <option value="Corrier" <?php echo ($sale['transport_mode'] ?? '') == 'Corrier' ? 'selected' : ''; ?>>Corrier</option>
                                    <option value="Speed Post" <?php echo ($sale['transport_mode'] ?? '') == 'Speed Post' ? 'selected' : ''; ?>>Speed Post</option>
                                    <option value="Parcel Service" <?php echo ($sale['transport_mode'] ?? '') == 'Parcel Service' ? 'selected' : ''; ?>>Parcel Service</option>
                                    <option value="In Person" <?php echo ($sale['transport_mode'] ?? '') == 'In Person' ? 'selected' : ''; ?>>In Person</option>
                                    <option value="Door Delivery" <?php echo ($sale['transport_mode'] ?? '') == 'Door Delivery' ? 'selected' : ''; ?>>Door Delivery</option>
                                    <option value="Others" <?php echo ($sale['transport_mode'] ?? '') == 'Others' ? 'selected' : ''; ?>>Others (Please specify)</option>
                                </select>
                                <input type="text" class="form-input mt-2" id="custom_transport" name="custom_transport" placeholder="Specify transport mode" value="<?php echo htmlspecialchars($sale['custom_transport'] ?? ''); ?>" style="display: <?php echo ($sale['transport_mode'] ?? '') === 'Others' ? 'block' : 'none'; ?>;">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="invoice_no">Invoice No *</label>
                                <input type="text" class="form-input" id="invoice_no" name="invoice_no" value="<?php echo htmlspecialchars($sale['invoice_no'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="transport_cost">Transportation Cost (if any)</label>
                        <input type="number" class="form-input" id="transport_cost" name="transport_cost" placeholder="Enter transportation cost" min="0" step="0.01" value="<?php echo htmlspecialchars($sale['transport_cost'] ?? 0); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">HSN Code</label>
                        <input type="text" class="form-input" value="1704" readonly>
                    </div>
                    
                    <h3>Products</h3>
                    <button type="button" class="add-product-btn" id="addProduct">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                    
                    <table class="product-table" id="productsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Weight</th>
                                <th>MRP</th>
                                <th>Quantity</th>
                                <th>Rate</th>
                                <th>Discount (%)</th>
                                <th>Taxable Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="productRows">
                            <?php if (!empty($sale_items)): ?>
                                <?php foreach ($sale_items as $item): ?>
                                    <?php 
                                    $product = null;
                                    foreach ($products as $p) {
                                        if ($p['id'] == $item['product_id']) {
                                            $product = $p;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr class="product-row">
                                        <td>
                                            <select class="form-select product-select" name="product_id[]" required>
                                                <option value="">Select Product</option>
                                                <?php foreach ($products as $product_option): ?>
                                                    <option value="<?php echo $product_option['id']; ?>" data-weight="<?php echo $product_option['piece_weight']; ?>" <?php echo $item['product_id'] == $product_option['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($product_option['product_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-input weight-display" value="<?php echo htmlspecialchars($product['piece_weight'] ?? 'N/A'); ?>" readonly>
                                        </td>
                                        <td>
                                            <input type="number" class="form-input mrp-input" name="mrp[]" min="0" step="0.01" value="<?php echo htmlspecialchars($item['mrp']); ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-input quantity-input" name="quantity[]" min="-20" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-input rate-input" name="rate[]" min="0" step="0.01" value="<?php echo htmlspecialchars($item['rate']); ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-input discount-input" name="discount[]" min="0" max="100" value="<?php echo htmlspecialchars($item['discount']); ?>" step="0.01">
                                        </td>
                                        <td>
                                            <span class="taxable-value"><?php echo number_format($item['taxable_value'], 2); ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="remove-product-btn" <?php echo count($sale_items) === 1 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="product-row">
                                    <td>
                                        <select class="form-select product-select" name="product_id[]" required>
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>" data-weight="<?php echo $product['piece_weight']; ?>">
                                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-input weight-display" readonly>
                                    </td>
                                    <td>
                                        <input type="number" class="form-input mrp-input" name="mrp[]" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-input quantity-input" name="quantity[]" min="-20" value="1" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-input rate-input" name="rate[]" min="0" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-input discount-input" name="discount[]" min="0" max="100" value="0" step="0.01">
                                    </td>
                                    <td>
                                        <span class="taxable-value">0.00</span>
                                    </td>
                                    <td>
                                        <button type="button" class="remove-product-btn" disabled>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div class="summary-section">
                        <div class="summary-row">
                            <span class="summary-label">Total Taxable Value:</span>
                            <span class="summary-value" id="totalTaxableValue"><?php echo number_format($sale['total_taxable_value'] ?? 0, 2); ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="apply_gst" name="apply_gst" <?php echo ($sale['apply_gst'] ?? 0) ? 'checked' : ''; ?>>
                                Apply GST
                            </label>
                        </div>
                        
                        <div class="summary-row" id="cgstRow" style="display: <?php echo ($sale['apply_gst'] ?? 0) ? 'flex' : 'none'; ?>;">
                            <span class="summary-label">CGST (2.5%):</span>
                            <span class="summary-value" id="cgstValue"><?php echo number_format($sale['cgst'] ?? 0, 2); ?></span>
                        </div>
                        
                        <div class="summary-row" id="sgstRow" style="display: <?php echo ($sale['apply_gst'] ?? 0) ? 'flex' : 'none'; ?>;">
                            <span class="summary-label">SGST (2.5%):</span>
                            <span class="summary-value" id="sgstValue"><?php echo number_format($sale['sgst'] ?? 0, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Transportation Cost:</span>
                            <span class="summary-value" id="transportCostDisplay">₹<?php echo number_format($sale['transport_cost'] ?? 0, 2); ?></span>
                        </div>
                        
                        <div class="summary-row grand-total">
                            <span class="summary-label">Grand Total:</span>
                            <span class="summary-value" id="grandTotal">₹<?php echo number_format($sale['grand_total'] ?? 0, 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Payment Status Section -->
                    <div class="payment-status-section">
                        <h3>Payment Status</h3>
                        <div class="payment-status-form">
                            <div class="form-group">
                                <label class="form-label" for="payment_status">Payment Status *</label>
                                <select class="payment-status-select" id="payment_status" name="payment_status" required onchange="toggleAmountPaid()">
                                    <option value="not_paid" <?php echo ($sale['payment_status'] ?? '') == 'not_paid' ? 'selected' : ''; ?>>Not Paid</option>
                                    <option value="partially_paid" <?php echo ($sale['payment_status'] ?? '') == 'partially_paid' ? 'selected' : ''; ?>>Partially Paid</option>
                                    <option value="paid" <?php echo ($sale['payment_status'] ?? '') == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="amount_paid">Amount Paid *</label>
                                <input type="number" class="amount-paid-input" id="amount_paid" name="amount_paid" 
                                       placeholder="Enter amount paid" 
                                       value="<?php echo htmlspecialchars($sale['amount_paid'] ?? 0); ?>"
                                       min="0" max="<?php echo $sale['grand_total'] ?? 0; ?>" step="0.01" required>
                                <div class="amount-due-display">
                                    Amount Due: ₹<span id="amount_due_display"><?php echo number_format($sale['amount_due'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Current Status</label>
                                <div>
                                    <span class="payment-status-badge <?php echo 'status-' . str_replace('_', '-', $sale['payment_status'] ?? 'not_paid'); ?>">
                                        <?php 
                                        $status_text = [
                                            'paid' => 'Paid',
                                            'not_paid' => 'Not Paid', 
                                            'partially_paid' => 'Partially Paid'
                                        ];
                                        echo $status_text[$sale['payment_status'] ?? 'not_paid'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="form-btn" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Update Sale
                    </button>
                </form>
                
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
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
        
        // Customer search functionality
        const customerSearch = document.getElementById('customer_search');
        const customerResults = document.getElementById('customer_results');
        const customerId = document.getElementById('customer_id');
        const customerDetails = document.getElementById('customer_details');
        
        const customers = <?php echo json_encode($customers); ?>;
        
        customerSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            customerResults.innerHTML = '';
            
            if (searchTerm.length < 2) {
                customerResults.style.display = 'none';
                return;
            }
            
            const filteredCustomers = customers.filter(customer =>
                customer.customer_name.toLowerCase().includes(searchTerm) ||
                (customer.shop_name && customer.shop_name.toLowerCase().includes(searchTerm))
            );
            
            if (filteredCustomers.length > 0) {
                filteredCustomers.forEach(customer => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    item.textContent = `${customer.customer_name}${customer.shop_name ? ' - ' + customer.shop_name : ''}`;
                    item.addEventListener('click', function() {
                        customerSearch.value = customer.customer_name;
                        customerId.value = customer.id;
                        customerResults.style.display = 'none';
                        
                        // Show customer details
                        document.getElementById('detail_shop').textContent = customer.shop_name || 'N/A';
                        document.getElementById('detail_city').textContent = customer.city || 'N/A';
                        document.getElementById('detail_state').textContent = customer.state || 'N/A';
                        document.getElementById('detail_phone').textContent = customer.phone || 'N/A';
                        document.getElementById('detail_email').textContent = customer.email || 'N/A';
                        customerDetails.style.display = 'block';
                    });
                    customerResults.appendChild(item);
                });
                customerResults.style.display = 'block';
            } else {
                customerResults.style.display = 'none';
            }
        });
        
        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
                customerResults.style.display = 'none';
            }
        });
        
        // Show custom transport field when "Others" is selected
        const transportMode = document.getElementById('transport_mode');
        const customTransport = document.getElementById('custom_transport');
        
        transportMode.addEventListener('change', function() {
            if (this.value === 'Others') {
                customTransport.style.display = 'block';
                customTransport.setAttribute('required', 'required');
            } else {
                customTransport.style.display = 'none';
                customTransport.removeAttribute('required');
            }
        });
        
        // Add product row
        const addProductBtn = document.getElementById('addProduct');
        const productRows = document.getElementById('productRows');
        const products = <?php echo json_encode($products); ?>;
        
        addProductBtn.addEventListener('click', function() {
            const newRow = document.createElement('tr');
            newRow.className = 'product-row';
            
            // Product select
            let productOptions = '<option value="">Select Product</option>';
            products.forEach(product => {
                productOptions += `<option value="${product.id}" data-weight="${product.piece_weight}">${product.product_name}</option>`;
            });
            
            newRow.innerHTML = `
                <td>
                    <select class="form-select product-select" name="product_id[]" required>
                        ${productOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-input weight-display" readonly>
                </td>
                <td>
                    <input type="number" class="form-input mrp-input" name="mrp[]" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="form-input quantity-input" name="quantity[]" min="-20" value="1" required>
                </td>
                <td>
                    <input type="number" class="form-input rate-input" name="rate[]" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="form-input discount-input" name="discount[]" min="0" max="100" value="0" step="0.01">
                </td>
                <td>
                    <span class="taxable-value">0.00</span>
                </td>
                <td>
                    <button type="button" class="remove-product-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            productRows.appendChild(newRow);
            
            // Enable remove buttons if there's more than one row
            const removeButtons = document.querySelectorAll('.remove-product-btn');
            if (removeButtons.length > 1) {
                removeButtons.forEach(btn => btn.disabled = false);
            }
            
            // Add event listeners to the new row
            addProductEventListeners(newRow);
        });
        
        // Function to add event listeners to product row inputs
        function addProductEventListeners(row) {
            const productSelect = row.querySelector('.product-select');
            const weightDisplay = row.querySelector('.weight-display');
            const quantityInput = row.querySelector('.quantity-input');
            const rateInput = row.querySelector('.rate-input');
            const discountInput = row.querySelector('.discount-input');
            const taxableValueSpan = row.querySelector('.taxable-value');
            const removeBtn = row.querySelector('.remove-product-btn');
            
            // Update weight when product is selected
            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                weightDisplay.value = selectedOption.getAttribute('data-weight') || '';
                calculateRowTotal(this);
                updateTotals();
            });
            
            // Calculate row total when inputs change
            quantityInput.addEventListener('input', function() {
                calculateRowTotal(this);
                updateTotals();
            });
            
            rateInput.addEventListener('input', function() {
                calculateRowTotal(this);
                updateTotals();
            });
            
            discountInput.addEventListener('input', function() {
                calculateRowTotal(this);
                updateTotals();
            });
            
            // Remove row
            removeBtn.addEventListener('click', function() {
                if (document.querySelectorAll('.product-row').length > 1) {
                    row.remove();
                    updateTotals();
                    
                    // Disable remove button if only one row remains
                    const removeButtons = document.querySelectorAll('.remove-product-btn');
                    if (removeButtons.length === 1) {
                        removeButtons[0].disabled = true;
                    }
                }
            });
            
            // Calculate initial row total
            calculateRowTotal(row);
        }
        
        // Calculate row total
        function calculateRowTotal(element) {
            const row = element.closest('.product-row');
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
            
            const amount = quantity * rate;
            const discountAmount = amount * (discount / 100);
            const taxableValue = amount - discountAmount;
            
            row.querySelector('.taxable-value').textContent = taxableValue.toFixed(2);
        }
        
        // Update totals
        function updateTotals() {
            let totalTaxableValue = 0;
            
            document.querySelectorAll('.product-row').forEach(row => {
                const taxableValue = parseFloat(row.querySelector('.taxable-value').textContent) || 0;
                totalTaxableValue += taxableValue;
            });
            
            const applyGst = document.getElementById('apply_gst').checked;
            const transportCost = parseFloat(document.getElementById('transport_cost').value) || 0;
            
            const cgst = applyGst ? totalTaxableValue * 0.025 : 0;
            const sgst = applyGst ? totalTaxableValue * 0.025 : 0;
            const grandTotal = totalTaxableValue + cgst + sgst + transportCost;
            
            document.getElementById('totalTaxableValue').textContent = totalTaxableValue.toFixed(2);
            document.getElementById('cgstValue').textContent = cgst.toFixed(2);
            document.getElementById('sgstValue').textContent = sgst.toFixed(2);
            document.getElementById('transportCostDisplay').textContent = '₹' + transportCost.toFixed(2);
            document.getElementById('grandTotal').textContent = '₹' + grandTotal.toFixed(2);
            
            // Show/hide GST rows
            document.getElementById('cgstRow').style.display = applyGst ? 'flex' : 'none';
            document.getElementById('sgstRow').style.display = applyGst ? 'flex' : 'none';
            
            // Update payment amount limits
            const amountPaidInput = document.getElementById('amount_paid');
            amountPaidInput.max = grandTotal;
            
            // Auto-update amount paid if status is paid
            const paymentStatus = document.getElementById('payment_status').value;
            if (paymentStatus === 'paid') {
                amountPaidInput.value = grandTotal;
                updateAmountDue();
            }
        }
        
        // Payment status functionality
        function toggleAmountPaid() {
            const paymentStatus = document.getElementById('payment_status').value;
            const amountPaidInput = document.getElementById('amount_paid');
            const grandTotal = parseFloat(document.getElementById('grandTotal').textContent.replace('₹', '')) || 0;
            
            if (paymentStatus === 'paid') {
                amountPaidInput.value = grandTotal;
                amountPaidInput.disabled = true;
            } else if (paymentStatus === 'not_paid') {
                amountPaidInput.value = 0;
                amountPaidInput.disabled = false;
            } else {
                amountPaidInput.disabled = false;
            }
            
            updateAmountDue();
        }
        
        // Update amount due display
        function updateAmountDue() {
            const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
            const grandTotal = parseFloat(document.getElementById('grandTotal').textContent.replace('₹', '')) || 0;
            const amountDue = grandTotal - amountPaid;
            
            document.getElementById('amount_due_display').textContent = amountDue.toFixed(2);
        }
        
        // Add event listeners to existing rows
        document.querySelectorAll('.product-row').forEach(row => {
            addProductEventListeners(row);
        });
        
        // Add event listeners to other inputs that affect totals
        document.getElementById('apply_gst').addEventListener('change', updateTotals);
        document.getElementById('transport_cost').addEventListener('input', updateTotals);
        document.getElementById('payment_status').addEventListener('change', toggleAmountPaid);
        document.getElementById('amount_paid').addEventListener('input', updateAmountDue);
        
        // Initial calculations
        updateTotals();
        toggleAmountPaid();
        
        // Form validation
        document.getElementById('salesForm').addEventListener('submit', function(e) {
            const customerId = document.getElementById('customer_id').value;
            const productRows = document.querySelectorAll('.product-row');
            let hasProducts = false;
            
            productRows.forEach(row => {
                const productId = row.querySelector('.product-select').value;
                const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
                if (productId && quantity !== 0) hasProducts = true;
            });
            
            if (!customerId) {
                e.preventDefault();
                alert('Please select a customer');
                return false;
            }
            
            if (!hasProducts) {
                e.preventDefault();
                alert('Please add at least one product with non-zero quantity');
                return false;
            }
        });
    </script>
</body>
</html>