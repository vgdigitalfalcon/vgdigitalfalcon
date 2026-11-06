<?php
// ============================================================
// SETUP.PHP - Run this ONCE to create the database and tables
// ============================================================
$host = 'localhost';
$user = 'u670120666_newdb';
$pass = 'newdb@ABD123';
$dbname = 'u670120666_newdb';



$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($dbname);

$tables = [];

// Users
$tables[] = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(100) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `role` ENUM('admin','staff') DEFAULT 'staff',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Expenses
$tables[] = "CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `amount` DECIMAL(12,2) NOT NULL,
    `reason` TEXT NOT NULL,
    `spent_by` INT NOT NULL,
    `payment_status` ENUM('Paid','Unpaid','Partial Paid') NOT NULL,
    `partial_amount` DECIMAL(12,2) DEFAULT 0,
    `expense_date` DATE NOT NULL,
    `remarks` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`spent_by`) REFERENCES `users`(`id`)
)";

// Customers
$tables[] = "CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(200) NOT NULL,
    `shop_name` VARCHAR(200),
    `address` TEXT,
    `city` VARCHAR(100),
    `state` VARCHAR(100),
    `gst_no` VARCHAR(50),
    `phone` VARCHAR(20),
    `email` VARCHAR(150),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Product Categories
$tables[] = "CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(200) NOT NULL,
    `price_per` ENUM('KG','PC') NOT NULL,
    `price` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Products
$tables[] = "CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `product_code` VARCHAR(100),
    `hsn_code` VARCHAR(100),
    `product_name` VARCHAR(200) NOT NULL,
    `piece_weight` DECIMAL(10,3) DEFAULT 0,
    `packing_mode` VARCHAR(100),
    `price` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`)
)";

// Stock
$tables[] = "CREATE TABLE IF NOT EXISTS `stock` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `stock_per` ENUM('KG','PC') NOT NULL,
    `new_stock` DECIMAL(12,3) NOT NULL,
    `batch_date` DATE NOT NULL,
    `cost_per_unit` DECIMAL(12,2) NOT NULL,
    `total_cost` DECIMAL(12,2) NOT NULL,
    `remaining_stock` DECIMAL(12,3) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`)
)";

// Sales
$tables[] = "CREATE TABLE IF NOT EXISTS `sales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `bill_date` DATE NOT NULL,
    `transport_mode` VARCHAR(100),
    `invoice_no` VARCHAR(100) UNIQUE,
    `transport_cost` DECIMAL(12,2) DEFAULT 0,
    `subtotal` DECIMAL(12,2) DEFAULT 0,
    `apply_gst` TINYINT(1) DEFAULT 0,
    `cgst` DECIMAL(12,2) DEFAULT 0,
    `sgst` DECIMAL(12,2) DEFAULT 0,
    `grand_total` DECIMAL(12,2) DEFAULT 0,
    `payment_status` ENUM('Paid','Not Paid','Partial Paid') DEFAULT 'Not Paid',
    `amount_paid` DECIMAL(12,2) DEFAULT 0,
    `amount_due` DECIMAL(12,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`)
)";

// Sale Items
$tables[] = "CREATE TABLE IF NOT EXISTS `sale_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sale_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `weight` DECIMAL(12,3) DEFAULT 0,
    `mrp` DECIMAL(12,2) DEFAULT 0,
    `quantity` DECIMAL(12,3) DEFAULT 0,
    `rate` DECIMAL(12,2) DEFAULT 0,
    `discount_pct` DECIMAL(5,2) DEFAULT 0,
    `line_total` DECIMAL(12,2) DEFAULT 0,
    FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`)
)";

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        echo "<p style='color:red'>Error: " . $conn->error . "</p>";
    }
}

// Default admin user
$conn->query("INSERT IGNORE INTO `users` (`username`,`password`,`full_name`,`role`) VALUES ('admin','admin123','Administrator','admin')");
$conn->query("INSERT IGNORE INTO `users` (`username`,`password`,`full_name`,`role`) VALUES ('staff1','staff123','Staff One','staff')");

echo "<!DOCTYPE html><html><head><title>Setup Complete</title>
<style>body{font-family:Arial,sans-serif;max-width:600px;margin:60px auto;padding:20px;}
.box{background:#d4edda;border:1px solid #c3e6cb;padding:20px;border-radius:8px;color:#155724;}
a{display:inline-block;margin-top:15px;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:5px;}
</style></head><body>
<div class='box'>
<h2>✅ Database Setup Complete!</h2>
<p>Database <strong>inventory_billing</strong> and all tables created successfully.</p>
<p><strong>Default Login:</strong><br>
Username: <code>admin</code> | Password: <code>admin123</code></p>
<a href='login.php'>Go to Login →</a>
</div></body></html>";
?>
