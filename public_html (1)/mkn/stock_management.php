<?php
session_start();
include 'db.php';
include 'inventory_helpers.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php"); exit();
}

$user = $_SESSION['user'];
$message = '';
$msg_type = '';

// ── Handle Manual Stock Adjustment ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'adjust') {
        $product_id = (int)$_POST['product_id'];
        $adjustment = (int)$_POST['adjustment'];
        $reason = $conn->real_escape_string(trim($_POST['reason']));
        if ($product_id && $adjustment != 0 && $reason) {
            adjustStock($conn, $product_id, $adjustment, $reason, $user);
            $message = "Stock adjusted successfully!";
            $msg_type = 'success';
        } else {
            $message = "Please fill all fields correctly.";
            $msg_type = 'danger';
        }
    } elseif ($_POST['action'] === 'add_batch') {
        $product_id = (int)$_POST['product_id'];
        $quantity   = (int)$_POST['quantity'];
        $batch_date = $conn->real_escape_string($_POST['batch_date']);
        $remarks    = $conn->real_escape_string(trim($_POST['remarks'] ?? ''));
        if ($product_id && $quantity > 0 && $batch_date) {
            addStockBatch($conn, $product_id, $quantity, $batch_date, $user, null, $remarks);
            $message = "Stock batch added successfully!";
            $msg_type = 'success';
        } else {
            $message = "Please fill all required fields.";
            $msg_type = 'danger';
        }
    } elseif ($_POST['action'] === 'update_minimum') {
        $product_id  = (int)$_POST['product_id'];
        $min_stock   = (int)$_POST['minimum_stock'];
        if ($product_id && $min_stock >= 0) {
            $conn->query("UPDATE products SET minimum_stock=$min_stock WHERE id=$product_id");
            $message = "Minimum stock level updated!";
            $msg_type = 'success';
        }
    }
}

// ── Fetch all products with stock info ───────────────────────────────────────
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';

$where = "1=1";
if (!empty($search)) {
    $where .= " AND (p.product_name LIKE '%$search%' OR p.product_code LIKE '%$search%')";
}
if ($filter === 'low') {
    $where .= " AND COALESCE(SUM(sb.quantity_remaining),0) <= p.minimum_stock";
} elseif ($filter === 'out') {
    $where .= " AND COALESCE(SUM(sb.quantity_remaining),0) = 0";
} elseif ($filter === 'ok') {
    $where .= " AND COALESCE(SUM(sb.quantity_remaining),0) > p.minimum_stock";
}

$products_sql = "
    SELECT p.id, p.product_code, p.product_name, p.packing_mode, p.minimum_stock, p.mrp, p.basic_price,
           COALESCE(SUM(sb.quantity_remaining),0) AS current_stock
    FROM products p
    LEFT JOIN stock_batches sb ON sb.product_id = p.id AND sb.quantity_remaining > 0
    WHERE $where
    GROUP BY p.id, p.product_code, p.product_name, p.packing_mode, p.minimum_stock, p.mrp, p.basic_price
    ORDER BY current_stock ASC, p.product_name ASC
";
$products_result = $conn->query($products_sql);
$products = [];
while ($row = $products_result->fetch_assoc()) { $products[] = $row; }

// Counts for filter tabs
$counts = $conn->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN COALESCE(s.stock,0)=0 THEN 1 ELSE 0 END) as out_count,
        SUM(CASE WHEN COALESCE(s.stock,0)>0 AND COALESCE(s.stock,0)<=p.minimum_stock THEN 1 ELSE 0 END) as low_count
    FROM products p
    LEFT JOIN (SELECT product_id, SUM(quantity_remaining) as stock FROM stock_batches WHERE quantity_remaining>0 GROUP BY product_id) s ON s.product_id=p.id
")->fetch_assoc();

$low_stock_count = countLowStock($conn);

// All products for dropdowns
$all_products = $conn->query("SELECT id, product_name, product_code FROM products ORDER BY product_name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inventory Management - MKN Bro's</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
:root {
    --primary:#e67e22; --primary-dark:#d35400; --secondary:#f8f9fa;
    --text:#333; --text-light:#777; --sidebar-width:250px;
    --success:#27ae60; --danger:#e74c3c; --warning:#f39c12; --info:#3498db;
}
body { background:#f5f5f5; color:var(--text); }
.dashboard-container { display:flex; min-height:100vh; }

/* ── Sidebar ── */
.sidebar { width:var(--sidebar-width); background:linear-gradient(135deg,var(--primary-dark),var(--primary)); color:#fff; position:fixed; height:100vh; z-index:1000; overflow-y:auto; }
.sidebar-header { padding:1.5rem 1rem; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:10px; }
.sidebar-logo { font-size:1.8rem; }
.sidebar-company { font-size:1rem; font-weight:600; }
.sidebar-nav { padding:1rem 0; }
.nav-section { padding:.5rem 1rem .3rem; font-size:.7rem; text-transform:uppercase; letter-spacing:1px; opacity:.6; }
.nav-item { padding:.75rem 1.5rem; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fff; transition:all .2s; position:relative; }
.nav-item:hover { background:rgba(255,255,255,.1); }
.nav-item.active { background:rgba(255,255,255,.2); border-left:4px solid #fff; }
.nav-icon { width:20px; text-align:center; }
.badge-count { background:#e74c3c; color:#fff; border-radius:10px; padding:1px 6px; font-size:.7rem; margin-left:auto; }

/* ── Main ── */
.main-content { flex:1; margin-left:var(--sidebar-width); }
.header { background:#fff; height:60px; box-shadow:0 2px 5px rgba(0,0,0,.1); display:flex; justify-content:space-between; align-items:center; padding:0 1.5rem; position:sticky; top:0; z-index:100; }
.header-title { font-size:1.1rem; font-weight:600; display:flex; align-items:center; gap:8px; }
.user-info { display:flex; align-items:center; gap:12px; }
.logout-btn { background:var(--primary); color:#fff; border:none; padding:.4rem .9rem; border-radius:4px; cursor:pointer; text-decoration:none; font-size:.9rem; }
.content { padding:1.5rem; }

/* ── Alert ── */
.alert { padding:.9rem 1.2rem; border-radius:6px; margin-bottom:1rem; display:flex; align-items:center; gap:10px; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-danger  { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }

/* ── Page Header ── */
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
.page-title { font-size:1.5rem; font-weight:700; }
.page-actions { display:flex; gap:.75rem; flex-wrap:wrap; }
.btn { padding:.5rem 1rem; border-radius:5px; border:none; cursor:pointer; font-size:.875rem; font-weight:500; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:all .2s; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-primary:hover { background:var(--primary-dark); }
.btn-success { background:var(--success); color:#fff; }
.btn-success:hover { background:#219a52; }
.btn-warning { background:var(--warning); color:#fff; }
.btn-warning:hover { background:#d68910; }
.btn-sm { padding:.35rem .7rem; font-size:.8rem; }

/* ── Stats Row ── */
.stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.stat-mini { background:#fff; border-radius:8px; padding:1rem 1.2rem; box-shadow:0 2px 8px rgba(0,0,0,.07); text-align:center; border-top:3px solid var(--primary); }
.stat-mini.red { border-color:var(--danger); }
.stat-mini.yellow { border-color:var(--warning); }
.stat-mini.green { border-color:var(--success); }
.stat-mini .num { font-size:1.8rem; font-weight:700; }
.stat-mini .lbl { font-size:.8rem; color:var(--text-light); margin-top:.2rem; }

/* ── Filter Bar ── */
.filter-bar { background:#fff; border-radius:8px; padding:1rem; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; }
.filter-tabs { display:flex; gap:.5rem; flex-wrap:wrap; }
.filter-tab { padding:.35rem .85rem; border-radius:20px; border:2px solid #ddd; background:#fff; cursor:pointer; font-size:.85rem; text-decoration:none; color:var(--text); transition:all .2s; }
.filter-tab.active, .filter-tab:hover { border-color:var(--primary); background:var(--primary); color:#fff; }
.search-box { display:flex; gap:.5rem; }
.search-box input { padding:.4rem .8rem; border:1px solid #ddd; border-radius:5px; font-size:.875rem; width:220px; }
.search-box button { background:var(--primary); color:#fff; border:none; border-radius:5px; padding:.4rem .8rem; cursor:pointer; }

/* ── Table ── */
.table-card { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.07); overflow:hidden; }
.table-card table { width:100%; border-collapse:collapse; }
.table-card th { background:#f8f9fa; padding:.85rem 1rem; text-align:left; font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; color:var(--text-light); border-bottom:2px solid #eee; white-space:nowrap; }
.table-card td { padding:.85rem 1rem; border-bottom:1px solid #f0f0f0; font-size:.9rem; vertical-align:middle; }
.table-card tr:last-child td { border-bottom:none; }
.table-card tr:hover td { background:#fafafa; }

/* Stock badges */
.stock-badge { padding:.25rem .7rem; border-radius:20px; font-size:.78rem; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
.stock-ok     { background:#d4edda; color:#155724; }
.stock-low    { background:#fff3cd; color:#856404; }
.stock-out    { background:#f8d7da; color:#721c24; }
.stock-num    { font-weight:700; font-size:1rem; }

/* Batch detail toggle */
.batch-toggle { background:none; border:none; color:var(--info); cursor:pointer; font-size:.8rem; padding:2px 6px; }
.batch-detail { display:none; padding:.5rem 1rem .8rem; background:#f8f9fa; border-top:1px solid #eee; }
.batch-detail.open { display:block; }
.batch-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.batch-table th, .batch-table td { padding:.4rem .6rem; border-bottom:1px solid #e0e0e0; }
.batch-table th { color:var(--text-light); font-weight:600; }

/* ── Modal ── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:2000; justify-content:center; align-items:center; }
.modal-overlay.open { display:flex; }
.modal { background:#fff; border-radius:10px; width:460px; max-width:95vw; max-height:90vh; overflow-y:auto; }
.modal-header { padding:1.2rem 1.5rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
.modal-header h3 { font-size:1.1rem; font-weight:600; }
.modal-close { background:none; border:none; font-size:1.4rem; cursor:pointer; color:var(--text-light); }
.modal-body { padding:1.5rem; }
.form-group { margin-bottom:1rem; }
.form-group label { display:block; font-size:.875rem; font-weight:500; margin-bottom:.4rem; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:.5rem .8rem; border:1px solid #ddd; border-radius:5px; font-size:.9rem; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:var(--primary); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }
.modal-footer { padding:1rem 1.5rem; border-top:1px solid #eee; display:flex; justify-content:flex-end; gap:.8rem; }
.btn-outline { background:#fff; border:1px solid #ddd; color:var(--text); }
.btn-outline:hover { background:#f5f5f5; }

@media(max-width:992px) {
    .sidebar { transform:translateX(-100%); transition:transform .3s; }
    .sidebar.open { transform:translateX(0); }
    .main-content { margin-left:0; }
    .menu-toggle { display:block !important; }
}
.menu-toggle { display:none; font-size:1.4rem; cursor:pointer; }
.overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:999; }
.overlay.open { display:block; }
</style>
</head>
<body>
<div class="dashboard-container">

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">🥜</div>
        <div class="sidebar-company">MKN Bro's</div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="nav-item"><span class="nav-icon"><i class="fas fa-chart-pie"></i></span> Dashboard</a>
        <div class="nav-section">Operations</div>
        <a href="add_sales.php"       class="nav-item"><span class="nav-icon"><i class="fas fa-file-invoice"></i></span> New Sale</a>
        <a href="sales_list.php"      class="nav-item"><span class="nav-icon"><i class="fas fa-list"></i></span> Sales</a>
        <a href="add_purchase.php"    class="nav-item"><span class="nav-icon"><i class="fas fa-cart-plus"></i></span> New Purchase</a>
        <a href="purchases_list.php"  class="nav-item"><span class="nav-icon"><i class="fas fa-shopping-bag"></i></span> Purchases</a>
        <div class="nav-section">Inventory</div>
        <a href="stock_management.php" class="nav-item active"><span class="nav-icon"><i class="fas fa-boxes"></i></span> Stock Management
            <?php if($low_stock_count>0): ?><span class="badge-count"><?= $low_stock_count ?></span><?php endif; ?>
        </a>
        <a href="stock_report.php"    class="nav-item"><span class="nav-icon"><i class="fas fa-chart-bar"></i></span> Stock Report</a>
        <div class="nav-section">Records</div>
        <a href="products_list.php"   class="nav-item"><span class="nav-icon"><i class="fas fa-tag"></i></span> Products</a>
        <a href="customers_list.php"  class="nav-item"><span class="nav-icon"><i class="fas fa-users"></i></span> Customers</a>
        <a href="expenses_list.php"   class="nav-item"><span class="nav-icon"><i class="fas fa-receipt"></i></span> Expenses</a>
        <a href="amounts_list.php"    class="nav-item"><span class="nav-icon"><i class="fas fa-wallet"></i></span> Amounts</a>
        <a href="users_list.php"      class="nav-item"><span class="nav-icon"><i class="fas fa-user-cog"></i></span> Users</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <div style="display:flex;align-items:center;gap:1rem;">
            <span class="menu-toggle" id="menuToggle">☰</span>
            <div class="header-title"><i class="fas fa-boxes" style="color:var(--primary)"></i> Inventory / Stock Management</div>
        </div>
        <div class="user-info">
            <span style="font-size:.9rem;color:var(--text-light)">👋 <?= htmlspecialchars($user) ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <?php if ($message): ?>
        <div class="alert alert-<?= $msg_type ?>"><i class="fas fa-<?= $msg_type==='success'?'check-circle':'exclamation-circle' ?>"></i> <?= $message ?></div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">📦 Stock Management</div>
            <div class="page-actions">
                <button class="btn btn-success" onclick="openModal('addBatchModal')"><i class="fas fa-plus"></i> Add Stock Batch</button>
                <button class="btn btn-warning" onclick="openModal('adjustModal')"><i class="fas fa-sliders-h"></i> Adjust Stock</button>
                <a href="stock_report.php" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Full Report</a>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-mini">
                <div class="num"><?= $counts['total'] ?></div>
                <div class="lbl">Total Products</div>
            </div>
            <div class="stat-mini green">
                <div class="num"><?= max(0, $counts['total'] - $counts['out_count'] - $counts['low_count']) ?></div>
                <div class="lbl">In Stock</div>
            </div>
            <div class="stat-mini yellow">
                <div class="num"><?= $counts['low_count'] ?></div>
                <div class="lbl">Low Stock ⚠️</div>
            </div>
            <div class="stat-mini red">
                <div class="num"><?= $counts['out_count'] ?></div>
                <div class="lbl">Out of Stock 🚨</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="?filter=all<?= !empty($search)?'&search='.urlencode($search):'' ?>" class="filter-tab <?= $filter==='all'?'active':'' ?>">All (<?= $counts['total'] ?>)</a>
                <a href="?filter=ok<?= !empty($search)?'&search='.urlencode($search):'' ?>"  class="filter-tab <?= $filter==='ok'?'active':'' ?>">✅ In Stock</a>
                <a href="?filter=low<?= !empty($search)?'&search='.urlencode($search):'' ?>" class="filter-tab <?= $filter==='low'?'active':'' ?>">⚠️ Low Stock</a>
                <a href="?filter=out<?= !empty($search)?'&search='.urlencode($search):'' ?>" class="filter-tab <?= $filter==='out'?'active':'' ?>">🚨 Out of Stock</a>
            </div>
            <form method="GET" class="search-box">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search product...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <!-- Products Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Product Name</th>
                        <th>Mode</th>
                        <th>Current Stock</th>
                        <th>Min Stock</th>
                        <th>Status</th>
                        <th>Stock Value</th>
                        <th>Batches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--text-light)">No products found.</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $i => $p):
                    $stock = (int)$p['current_stock'];
                    $min   = (int)$p['minimum_stock'];
                    $status = $stock === 0 ? 'out' : ($stock <= $min ? 'low' : 'ok');
                    $statusLabel = $status === 'out' ? '🚨 Out of Stock' : ($status === 'low' ? '⚠️ Low Stock' : '✅ In Stock');
                    $stock_value = $stock * (float)$p['basic_price'];

                    // Fetch batches for this product
                    $batches_res = $conn->query("SELECT batch_date, quantity_in, quantity_remaining, added_by, remarks FROM stock_batches WHERE product_id={$p['id']} AND quantity_remaining>0 ORDER BY batch_date ASC, id ASC");
                    $batches = $batches_res ? $batches_res->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($p['product_code']) ?></code></td>
                    <td style="font-weight:500"><?= htmlspecialchars($p['product_name']) ?></td>
                    <td><?= htmlspecialchars($p['packing_mode']) ?></td>
                    <td><span class="stock-num <?= $status==='ok'?'':($status==='low'?'':'') ?>"><?= number_format($stock) ?> pcs</span></td>
                    <td>
                        <span style="cursor:pointer;color:var(--info)" title="Click to change" onclick="openMinStockModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['product_name']) ?>', <?= $min ?>)">
                            <?= $min ?> pcs <i class="fas fa-edit" style="font-size:.7rem"></i>
                        </span>
                    </td>
                    <td><span class="stock-badge stock-<?= $status ?>"><?= $statusLabel ?></span></td>
                    <td>₹<?= number_format($stock_value, 2) ?></td>
                    <td>
                        <?php if (!empty($batches)): ?>
                        <button class="batch-toggle" onclick="toggleBatch(<?= $p['id'] ?>)">
                            <i class="fas fa-layer-group"></i> <?= count($batches) ?> batch<?= count($batches)>1?'es':'' ?>
                        </button>
                        <?php else: ?>
                        <span style="color:#ccc;font-size:.8rem">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="openAddBatchForProduct(<?= $p['id'] ?>, '<?= htmlspecialchars($p['product_name'],ENT_QUOTES) ?>')"><i class="fas fa-plus"></i></button>
                        <button class="btn btn-warning btn-sm" onclick="openAdjustForProduct(<?= $p['id'] ?>, '<?= htmlspecialchars($p['product_name'],ENT_QUOTES) ?>')"><i class="fas fa-sliders-h"></i></button>
                    </td>
                </tr>
                <?php if (!empty($batches)): ?>
                <tr class="batch-row-<?= $p['id'] ?>">
                    <td colspan="10" style="padding:0">
                        <div class="batch-detail" id="batch-<?= $p['id'] ?>">
                            <table class="batch-table">
                                <thead><tr><th>Batch Date</th><th>Original Qty</th><th>Remaining</th><th>Used</th><th>Added By</th><th>Remarks</th></tr></thead>
                                <tbody>
                                <?php foreach ($batches as $b): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($b['batch_date'])) ?></td>
                                    <td><?= number_format($b['quantity_in']) ?></td>
                                    <td style="font-weight:600;color:var(--success)"><?= number_format($b['quantity_remaining']) ?></td>
                                    <td style="color:var(--danger)"><?= number_format($b['quantity_in'] - $b['quantity_remaining']) ?></td>
                                    <td><?= htmlspecialchars($b['added_by'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($b['remarks'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div><!-- /content -->
</div><!-- /main-content -->
<div class="overlay" id="overlay"></div>
</div><!-- /dashboard-container -->

<!-- Add Batch Modal -->
<div class="modal-overlay" id="addBatchModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle" style="color:var(--success)"></i> Add Stock Batch</h3>
            <button class="modal-close" onclick="closeModal('addBatchModal')">×</button>
        </div>
        <form method="POST">
        <input type="hidden" name="action" value="add_batch">
        <div class="modal-body">
            <div class="form-group">
                <label>Product *</label>
                <select name="product_id" id="addBatchProductId" required>
                    <option value="">-- Select Product --</option>
                    <?php foreach ($all_products as $ap): ?>
                    <option value="<?= $ap['id'] ?>"><?= htmlspecialchars($ap['product_code'].' - '.$ap['product_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Quantity (pcs) *</label>
                    <input type="number" name="quantity" min="1" required placeholder="e.g. 500">
                </div>
                <div class="form-group">
                    <label>Batch / Stock Date *</label>
                    <input type="date" name="batch_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Remarks</label>
                <input type="text" name="remarks" placeholder="Optional notes">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addBatchModal')">Cancel</button>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Add Stock</button>
        </div>
        </form>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal-overlay" id="adjustModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-sliders-h" style="color:var(--warning)"></i> Manual Stock Adjustment</h3>
            <button class="modal-close" onclick="closeModal('adjustModal')">×</button>
        </div>
        <form method="POST">
        <input type="hidden" name="action" value="adjust">
        <div class="modal-body">
            <div class="form-group">
                <label>Product *</label>
                <select name="product_id" id="adjustProductId" required>
                    <option value="">-- Select Product --</option>
                    <?php foreach ($all_products as $ap): ?>
                    <option value="<?= $ap['id'] ?>"><?= htmlspecialchars($ap['product_code'].' - '.$ap['product_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Adjustment Amount *</label>
                <input type="number" name="adjustment" required placeholder="Use positive (+100) to add, negative (-50) to reduce">
                <small style="color:var(--text-light)">+100 to add stock | -50 to reduce stock</small>
            </div>
            <div class="form-group">
                <label>Reason *</label>
                <input type="text" name="reason" required placeholder="e.g. Damaged goods, Stock count correction">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('adjustModal')">Cancel</button>
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Apply Adjustment</button>
        </div>
        </form>
    </div>
</div>

<!-- Update Minimum Stock Modal -->
<div class="modal-overlay" id="minStockModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-bell" style="color:var(--info)"></i> Set Minimum Stock Level</h3>
            <button class="modal-close" onclick="closeModal('minStockModal')">×</button>
        </div>
        <form method="POST">
        <input type="hidden" name="action" value="update_minimum">
        <input type="hidden" name="product_id" id="minStockProductId">
        <div class="modal-body">
            <p style="margin-bottom:1rem;color:var(--text-light)">Setting minimum stock for: <strong id="minStockProductName"></strong></p>
            <div class="form-group">
                <label>Minimum Stock Level (pcs) *</label>
                <input type="number" name="minimum_stock" id="minStockValue" min="0" required>
                <small style="color:var(--text-light)">You'll get a low-stock alert when stock drops below this level.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('minStockModal')">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openAddBatchForProduct(id, name) {
    document.getElementById('addBatchProductId').value = id;
    openModal('addBatchModal');
}
function openAdjustForProduct(id, name) {
    document.getElementById('adjustProductId').value = id;
    openModal('adjustModal');
}
function openMinStockModal(id, name, current) {
    document.getElementById('minStockProductId').value = id;
    document.getElementById('minStockProductName').textContent = name;
    document.getElementById('minStockValue').value = current;
    openModal('minStockModal');
}
function toggleBatch(id) {
    const el = document.getElementById('batch-'+id);
    el.classList.toggle('open');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

// Mobile sidebar
const menuToggle = document.getElementById('menuToggle');
const sidebar    = document.getElementById('sidebar');
const overlay    = document.getElementById('overlay');
menuToggle.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('open'); });
overlay.addEventListener('click',    () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
</script>
</body>
</html>
