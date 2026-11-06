<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}



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

// Generate a new invoice number
$invoice_result = $conn->query("SELECT MAX(CAST(SUBSTRING(invoice_no, 5) AS UNSIGNED)) as max_invoice FROM sales");
$invoice_number = 1;
if ($invoice_result->num_rows > 0) {
    $row = $invoice_result->fetch_assoc();
    if (!empty($row['max_invoice'])) {
        $invoice_number = $row['max_invoice'] + 1;
    }
}
$invoice_no = "INV-" . str_pad($invoice_number, 5, '0', STR_PAD_LEFT);

// Process form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $billing_date = $_POST['billing_date'];
    $transport_mode = $_POST['transport_mode'];
    $custom_transport = $_POST['custom_transport'] ?? '';
    $invoice_no = $_POST['invoice_no'];
    $transport_cost = $_POST['transport_cost'] ?? 0;
    $apply_gst = isset($_POST['apply_gst']) ? 1 : 0;
    
    // Payment status fields
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
        // Insert sale
        $sql = "INSERT INTO sales (customer_id, billing_date, transport_mode, custom_transport, invoice_no, transport_cost, apply_gst, payment_status, amount_paid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssdisd", $customer_id, $billing_date, $transport_mode, $custom_transport, $invoice_no, $transport_cost, $apply_gst, $payment_status, $amount_paid);
        
        if ($stmt->execute()) {
            $sale_id = $conn->insert_id;
            
            // Insert sale items
            $item_sql = "INSERT INTO sale_items (sale_id, product_id, quantity, rate, mrp, discount, taxable_value) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            
            for ($i = 0; $i < count($product_ids); $i++) {
                if (!empty($product_ids[$i]) && $quantities[$i] > 0) {
                    $taxable_value = ($rates[$i] * $quantities[$i]) - (($rates[$i] * $quantities[$i]) * ($discounts[$i] / 100));
                    $total_taxable_value += $taxable_value;
                    
                    $item_stmt->bind_param("iiidddd", $sale_id, $product_ids[$i], $quantities[$i], $rates[$i], $mrps[$i], $discounts[$i], $taxable_value);
                    $item_stmt->execute();
                }
            }
            
            // Calculate final totals
            $cgst = $apply_gst ? ($total_taxable_value * 2.5) / 100 : 0;
            $sgst = $apply_gst ? ($total_taxable_value * 2.5) / 100 : 0;
            $grand_total = $total_taxable_value + $cgst + $sgst + $transport_cost;
            
            // Calculate amount due
            $amount_due = $grand_total - $amount_paid;
            
            // Update sale with totals
            $update_sql = "UPDATE sales SET total_taxable_value = ?, cgst = ?, sgst = ?, grand_total = ?, amount_due = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("dddddi", $total_taxable_value, $cgst, $sgst, $grand_total, $amount_due, $sale_id);
            $update_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $message = "✅ Sale added successfully! Invoice No: " . $invoice_no;
        } else {
            throw new Exception("Error adding sale: " . $stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sales - Financial Management System</title>
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
        
        /* Customer Details Box */
        .customer-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-medium);
            display: none;
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
        
        /* Product Table */
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
        
        /* Summary Section */
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
        
        /* Search Dropdown */
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
            background-color: #f0f8ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--primary-medium);
        }
        
        .payment-status-section h3 {
            margin-bottom: 15px;
            color: var(--primary-dark);
        }
        
        .payment-fields {
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }
        
        .payment-field {
            flex: 1;
        }
        
        .amount-paid-container {
            display: none;
        }
        
        .amount-due-display {
            font-weight: bold;
            color: var(--danger);
            margin-top: 10px;
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
            
            .payment-fields {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .top-bar {
                padding: 15px;
            }
            
            .user-info span {
                display: none;
            }
            
            .customer-info-grid {
                grid-template-columns: 1fr;
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
                    <li><a href="add_sales.php" class="active"><i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
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
            <h1 class="page-title"><i class="fas fa-cart-plus"></i> Add Sales</h1>
            
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title"><i class="fas fa-cart-plus"></i> Create New Sale</h2>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="salesForm">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="customer_search">Select Customer *</label>
                                <div class="search-container">
                                    <input type="text" class="form-input" id="customer_search" placeholder="Search customer by name..." autocomplete="off">
                                    <div class="search-results" id="customer_results"></div>
                                </div>
                                <input type="hidden" id="customer_id" name="customer_id" required>
                                <div class="customer-details" id="customer_details">
                                    <h3>Customer Details</h3>
                                    <div class="customer-info-grid">
                                        <div class="info-item">
                                            <span class="info-label">Shop Name</span>
                                            <span class="info-value" id="detail_shop"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">City</span>
                                            <span class="info-value" id="detail_city"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">State</span>
                                            <span class="info-value" id="detail_state"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Phone</span>
                                            <span class="info-value" id="detail_phone"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email</span>
                                            <span class="info-value" id="detail_email"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="billing_date">Date of Billing *</label>
                                <input type="date" class="form-input" id="billing_date" name="billing_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="transport_mode">Mode of Transport *</label>
                                <select class="form-select" id="transport_mode" name="transport_mode" required>
                                    <option value="">Select Transport Mode</option>
                                    <option value="Motor Cycle">Motor Cycle</option>
                                    <option value="Corrier">Corrier</option>
                                    <option value="Speed Post">Speed Post</option>
                                    <option value="Parcel Service">Parcel Service</option>
                                    <option value="In Person">In Person</option>
                                    <option value="Door Delivery">Door Delivery</option>
                                    <option value="Others">Others (Please specify)</option>
                                </select>
                                <input type="text" class="form-input mt-2" id="custom_transport" name="custom_transport" placeholder="Specify transport mode" style="display: none;">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="invoice_no">Invoice No *</label>
                                <input type="text" class="form-input" id="invoice_no" name="invoice_no" value="<?php echo $invoice_no; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="transport_cost">Transportation Cost (if any)</label>
                        <input type="number" class="form-input" id="transport_cost" name="transport_cost" placeholder="Enter transportation cost" min="0" step="0.01" value="0">
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
                        </tbody>
                    </table>
                    
                    <div class="summary-section">
                        <div class="summary-row">
                            <span class="summary-label">Total Taxable Value:</span>
                            <span class="summary-value" id="totalTaxableValue">0.00</span>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="apply_gst" name="apply_gst" checked>
                                Apply GST
                            </label>
                        </div>
                        
                        <div class="summary-row" id="cgstRow">
                            <span class="summary-label">CGST (2.5%):</span>
                            <span class="summary-value" id="cgstValue">0.00</span>
                        </div>
                        
                        <div class="summary-row" id="sgstRow">
                            <span class="summary-label">SGST (2.5%):</span>
                            <span class="summary-value" id="sgstValue">0.00</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Transportation Cost:</span>
                            <span class="summary-value" id="transportCostDisplay">0.00</span>
                        </div>
                        
                        <div class="summary-row grand-total">
                            <span class="summary-label">Grand Total:</span>
                            <span class="summary-value" id="grandTotal">0.00</span>
                        </div>
                    </div>
                    
                    <!-- Payment Status Section -->
                    <div class="payment-status-section">
                        <h3>Payment Status</h3>
                        <div class="payment-fields">
                            <div class="payment-field">
                                <label class="form-label" for="payment_status">Payment Status *</label>
                                <select class="form-select" id="payment_status" name="payment_status" required>
                                    <option value="not_paid">Not Paid</option>
                                    <option value="partially_paid">Partially Paid</option>
                                    <option value="paid">Paid</option>
                                </select>
                            </div>
                            <div class="payment-field amount-paid-container" id="amountPaidContainer">
                                <label class="form-label" for="amount_paid">Amount Paid *</label>
                                <input type="number" class="form-input" id="amount_paid" name="amount_paid" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="amount-due-display" id="amountDueDisplay">
                            Amount Due: <span id="amountDueValue">0.00</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="form-btn" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Save Sale
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
            document.getElementById('transportCostDisplay').textContent = transportCost.toFixed(2);
            document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
            
            // Show/hide GST rows
            document.getElementById('cgstRow').style.display = applyGst ? 'flex' : 'none';
            document.getElementById('sgstRow').style.display = applyGst ? 'flex' : 'none';
            
            // Update payment status calculations
            updatePaymentStatus(grandTotal);
        }
        
        // Payment status functionality
        const paymentStatus = document.getElementById('payment_status');
        const amountPaidContainer = document.getElementById('amountPaidContainer');
        const amountPaid = document.getElementById('amount_paid');
        const amountDueDisplay = document.getElementById('amountDueDisplay');
        const amountDueValue = document.getElementById('amountDueValue');
        
        paymentStatus.addEventListener('change', function() {
            updatePaymentStatus();
        });
        
        amountPaid.addEventListener('input', function() {
            updatePaymentStatus();
        });
        
        function updatePaymentStatus(grandTotal = null) {
            if (grandTotal === null) {
                grandTotal = parseFloat(document.getElementById('grandTotal').textContent) || 0;
            }
            
            const status = paymentStatus.value;
            let amountPaidValue = parseFloat(amountPaid.value) || 0;
            
            // Show/hide amount paid field based on status
            if (status === 'partially_paid') {
                amountPaidContainer.style.display = 'block';
                amountPaid.setAttribute('required', 'required');
            } else {
                amountPaidContainer.style.display = 'none';
                amountPaid.removeAttribute('required');
                
                // Set amount paid based on status
                if (status === 'paid') {
                    amountPaidValue = grandTotal;
                    amountPaid.value = grandTotal.toFixed(2);
                } else if (status === 'not_paid') {
                    amountPaidValue = 0;
                    amountPaid.value = '0';
                }
            }
            
            // Calculate amount due
            const amountDue = grandTotal - amountPaidValue;
            
            // Update amount due display
            amountDueValue.textContent = amountDue.toFixed(2);
            
            // Show amount due if not fully paid
            if (amountDue > 0) {
                amountDueDisplay.style.display = 'block';
            } else {
                amountDueDisplay.style.display = 'none';
            }
        }
        
        // Add event listeners to existing rows
        document.querySelectorAll('.product-row').forEach(row => {
            addProductEventListeners(row);
        });
        
        // Add event listeners to other inputs that affect totals
        document.getElementById('apply_gst').addEventListener('change', updateTotals);
        document.getElementById('transport_cost').addEventListener('input', updateTotals);
        
        // Initial calculation
        updateTotals();
        
        // Form validation
        document.getElementById('salesForm').addEventListener('submit', function(e) {
            const customerId = document.getElementById('customer_id').value;
            const productRows = document.querySelectorAll('.product-row');
            let hasProducts = false;
            
            productRows.forEach(row => {
                const productId = row.querySelector('.product-select').value;
                if (productId) hasProducts = true;
            });
            
            if (!customerId) {
                e.preventDefault();
                alert('Please select a customer');
                return false;
            }
            
            if (!hasProducts) {
                e.preventDefault();
                alert('Please add at least one product');
                return false;
            }
            
            // Validate payment status
            const paymentStatus = document.getElementById('payment_status').value;
            const amountPaid = parseFloat(document.getElementById('amount_paid').value) || 0;
            const grandTotal = parseFloat(document.getElementById('grandTotal').textContent) || 0;
            
            if (paymentStatus === 'partially_paid' && amountPaid <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount paid for partially paid status');
                return false;
            }
            
            if (paymentStatus === 'partially_paid' && amountPaid >= grandTotal) {
                e.preventDefault();
                alert('Amount paid cannot be equal to or greater than grand total for partially paid status');
                return false;
            }
        });
    </script>
</body>
</html>