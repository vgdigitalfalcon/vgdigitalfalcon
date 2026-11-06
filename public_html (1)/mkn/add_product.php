
<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_code = $_POST['product_code'];
    $hsn_code = $_POST['hsn_code'];
    $product_name = $_POST['product_name'];
    $piece_weight = $_POST['piece_weight'];
    $packing_mode = $_POST['packing_mode'];
    $basic_price = $_POST['basic_price'];
    $gst = $_POST['gst'];
    $gross_price = $_POST['gross_price']; // This will now be base price + GST
    $margin = $_POST['margin']; // Store pre-GST amount as margin instead of percentage
    $mrp = $_POST['mrp'];
    $product_category = $_POST['product_category'];

    $sql = "INSERT INTO products (product_code, hsn_code, product_name, product_category, piece_weight, packing_mode, basic_price, gst, gross_price, margin, mrp)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $product_code, $hsn_code, $product_name, $product_category,  $piece_weight, $packing_mode, $basic_price, $gst, $gross_price, $margin, $mrp);
    if ($stmt->execute()) {
        $message = "✅ Product added successfully!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Financial Management System</title>
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
            max-width: 800px;
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
        
        .calculation-box {
            background: rgba(6, 122, 191, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .calculation-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(6, 122, 191, 0.2);
        }
        
        .calculation-label {
            font-weight: 500;
            color: #555;
        }
        
        .calculation-value {
            font-weight: 600;
            color: var(--primary-medium);
        }
        
        .rupee-symbol {
            font-family: Arial, sans-serif;
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
                    <li><a href="add_sales.php" ><i class="fas fa-cart-plus"></i> <span>Add Sales</span></a></li>
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
                    <li><a href="add_product.php" class="active"><i class="fas fa-cube"></i> <span>Add Product</span></a></li>
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
            <h1 class="page-title"><i class="fas fa-cube"></i> Add Product</h1>
            
            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title"><i class="fas fa-cube"></i> Add New Product</h2>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="productForm">
                    
                    <div class="col-md-4 mb-3">
    <label class="form-label">Product Category</label>
    <select name="product_category" class="form-control" required>
        <option value="">Select Category</option>
        <option value="Kadalaimittai Bits">Kadalaimittai Bits</option>
        <option value="Koko Mittai Bits">Koko Mittai Bits</option>
        <option value="Black Ellu Bits">Black Ellu Bits</option>
        <option value="White Ellu Bits">White Ellu Bits</option>
        <option value="White Ellu Balls">White Ellu Balls</option>
        <option value="Black Ellu Balls">Black Ellu Balls</option>
        <option value="Porikadalai Balls">Porikadalai Balls</option>
        <option value="Kadalaimittai Balls">Kadalaimittai Balls</option>
        <option value="Mixture Mittai Bits">Mixture Mittai Bits</option>
        <option value="Corn Mittai Bits">Corn Mittai Bits</option>
        <option value="Masala Kadalaimittai Bits">Masala Kadalaimittai Bits</option>
        <option value="Karupatti Kadalaimittai Bits">Karupatti Kadalaimittai Bits</option>
        <option value="Kadalaimittai 5rs PC">Kadalaimittai 5rs PC</option>
        <option value="Kadalaimittai 10rs PC">Kadalaimittai 5rs PC</option>
        <option value="Koko Mittai 5rs PC">Koko Mittai 5rs PC</option>        
        <option value="Mixture Mittai 5rs PC">Mixture Mittai 5rs PC</option>
      
        
    </select>
</div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="product_code">Product Code</label>
                                <input type="text" class="form-input" id="product_code" name="product_code" placeholder="Enter product code" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="hsn_code">HSN Code</label>
                                <input type="text" class="form-input" id="hsn_code" name="hsn_code" placeholder="Enter HSN code">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="product_name">Product Name</label>
                        <input type="text" class="form-input" id="product_name" name="product_name" placeholder="Enter product name" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="piece_weight">Piece Weight (grams)</label>
                                <input type="number" step="0.01" min="0" class="form-input" id="piece_weight" name="piece_weight" placeholder="Enter weight">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="packing_mode">Packing Mode</label>
                                <input type="text" class="form-input" id="packing_mode" name="packing_mode" placeholder="Enter packing mode">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="basic_price">Base Price (₹)</label>
                        <input type="number" step="0.01" min="0" class="form-input" id="basic_price" name="basic_price" placeholder="Enter base price" oninput="calculatePrices()">
                    </div>
                    
                    <div class="calculation-box">
                        <h3 style="margin-bottom: 15px; color: var(--primary-dark);">Price Calculation</h3>
                        <div class="calculation-row">
                            <span class="calculation-label">Base Price:</span>
                            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="display_base_price">0.00</span></span>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">Seller Margin (45%):</span>
                            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="display_margin">0.00</span></span>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">Pre-GST Selling Price:</span>
                            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="display_pre_gst">0.00</span></span>
                        </div>
                        <div class="calculation-row">
                            <span class="calculation-label">GST (5%):</span>
                            <span class="calculation-value"><span class="rupee-symbol">₹</span><span id="display_gst">0.00</span></span>
                        </div>
                        <div class="calculation-row" style="border-bottom: none; padding-bottom: 0;">
                            <span class="calculation-label" style="font-weight: 700;">Final MRP:</span>
                            <span class="calculation-value" style="font-weight: 700; color: var(--primary-dark);"><span class="rupee-symbol">₹</span><span id="display_mrp">0.00</span></span>
                        </div>
                    </div>
                    
                    <!-- Hidden fields to store calculated values -->
                    <input type="hidden" id="gst" name="gst" value="0">
                    <input type="hidden" id="gross_price" name="gross_price" value="0">
                    <input type="hidden" id="margin" name="margin" value="45">
                    <input type="hidden" id="mrp" name="mrp" value="0">
                    
                    <button type="submit" class="form-btn" style="margin-top: 20px;">
                        <i class="fas fa-plus-circle"></i> Add Product
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
        
        function calculatePrices() {
            const basePrice = parseFloat(document.getElementById('basic_price').value) || 0;
            
            // Calculate seller margin (45% of base price)
            const marginAmount = basePrice * 0.45;
            
            // Calculate pre-GST selling price
            const preGstPrice = basePrice + marginAmount;
            
            // Calculate GST (5% of pre-GST price)
            let gstAmount = basePrice * 0.05;
            
            // Calculate gross price (base price + GST)
            let grossPrice = basePrice + gstAmount;
            
            // Calculate final MRP
            let mrp = preGstPrice + gstAmount;
            
            // Round all values to 2 decimal places for display
            const displayBasePrice = basePrice.toFixed(2);
            const displayMargin = marginAmount.toFixed(2);
            const displayPreGst = preGstPrice.toFixed(2);
            const displayGst = gstAmount.toFixed(2);
            const displayMrp = mrp.toFixed(2);
            
            // Update display values
            document.getElementById('display_base_price').textContent = displayBasePrice;
            document.getElementById('display_margin').textContent = displayMargin;
            document.getElementById('display_pre_gst').textContent = displayPreGst;
            document.getElementById('display_gst').textContent = displayGst;
            document.getElementById('display_mrp').textContent = displayMrp;
            
            // Update hidden form fields with precise values
            document.getElementById('gst').value = gstAmount;
            document.getElementById('gross_price').value = grossPrice; // Now storing base + GST
            document.getElementById('margin').value = marginAmount;
            document.getElementById('mrp').value = mrp;
        }
        
        // Initialize calculation on page load if base price exists
        document.addEventListener('DOMContentLoaded', function() {
            const basePriceField = document.getElementById('basic_price');
            if (basePriceField && basePriceField.value) {
                calculatePrices();
            }
        });
    </script>
</body>
</html>