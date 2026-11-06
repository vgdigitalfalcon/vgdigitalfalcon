<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch products with stock
$sql = "
SELECT 
    p.id,
    p.product_name,
    p.min_stock_level,
    COALESCE(SUM(sb.remaining_quantity), 0) AS total_stock
FROM products p
LEFT JOIN stock_batches sb ON p.id = sb.product_id
GROUP BY p.id
ORDER BY p.product_name ASC
";

$products = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: Arial;
            background: #f5f6fa;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .low-stock {
            border-left: 5px solid red;
        }

        .good-stock {
            border-left: 5px solid green;
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 16px;
        }

        .batch {
            margin-top: 8px;
            padding-left: 10px;
            font-size: 14px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 12px;
        }

        .badge-low {
            background: red;
            color: white;
        }

        .badge-ok {
            background: green;
            color: white;
        }
    </style>
</head>

<body>

<h2>📦 Stock Management</h2>

<?php while ($row = $products->fetch_assoc()) { 

    $product_id = $row['id'];
    $total_stock = $row['total_stock'];
    $min_stock = $row['min_stock_level'];

    $is_low = ($total_stock <= $min_stock);
?>

<div class="card <?= $is_low ? 'low-stock' : 'good-stock' ?>">

    <div class="product-header">
        <div>
            <?= $row['product_name'] ?>
            <?php if ($is_low): ?>
                <span class="badge badge-low">Low Stock</span>
            <?php else: ?>
                <span class="badge badge-ok">OK</span>
            <?php endif; ?>
        </div>

        <div>
            Total: <?= $total_stock ?>
        </div>
    </div>

    <!-- Batch Details -->
    <div class="batch">
        <?php
        $batch_sql = "
        SELECT remaining_quantity, batch_date 
        FROM stock_batches 
        WHERE product_id = $product_id AND remaining_quantity > 0
        ORDER BY batch_date ASC
        ";

        $batches = $conn->query($batch_sql);

        if ($batches->num_rows == 0) {
            echo "<small>No stock available</small>";
        }

        while ($b = $batches->fetch_assoc()) {
            echo "• {$b['remaining_quantity']} pcs → {$b['batch_date']} <br>";
        }
        ?>
    </div>

</div>

<?php } ?>

</body>
</html>