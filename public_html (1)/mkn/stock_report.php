<?php
session_start();
include 'db.php';
include 'inventory_helpers.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php"); exit();
}

$user = $_SESSION['user'];

// Filter
$product_filter = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : date('Y-m-d');
$log_type  = isset($_GET['log_type'])  ? $_GET['log_type']  : '';

// Fetch all products for filter
$all_products = $conn->query("SELECT id, product_name, product_code FROM products ORDER BY product_name")->fetch_all(MYSQLI_ASSOC);

// Stock Summary
$stock_summary_sql = "
    SELECT p.id, p.product_code, p.product_name, p.packing_mode, p.basic_price, p.minimum_stock,
           COALESCE(SUM(sb.quantity_remaining),0) AS current_stock,
           COALESCE(SUM(sb.quantity_in),0) AS total_in
    FROM products p
    LEFT JOIN stock_batches sb ON sb.product_id = p.id
    " . ($product_filter ? "WHERE p.id=$product_filter" : "") . "
    GROUP BY p.id, p.product_code, p.product_name, p.packing_mode, p.basic_price, p.minimum_stock
    ORDER BY p.product_name
";
$stock_summary = $conn->query($stock_summary_sql)->fetch_all(MYSQLI_ASSOC);

// Audit Log
$audit_where = "WHERE 1=1";
if ($product_filter) $audit_where .= " AND al.product_id=$product_filter";
if (!empty($log_type)) $audit_where .= " AND al.change_type='".mysqli_real_escape_string($conn,$log_type)."'";
$audit_where .= " AND DATE(al.created_at) BETWEEN '$date_from' AND '$date_to'";

$audit_sql = "
    SELECT al.*, p.product_name, p.product_code,
           sb.batch_date
    FROM stock_audit_log al
    JOIN products p ON p.id = al.product_id
    LEFT JOIN stock_batches sb ON sb.id = al.batch_id
    $audit_where
    ORDER BY al.created_at DESC
    LIMIT 200
";
$audit_log = $conn->query($audit_sql)->fetch_all(MYSQLI_ASSOC);

$low_stock_count = countLowStock($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stock Report - MKN Bro's</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
:root { --primary:#e67e22; --primary-dark:#d35400; --success:#27ae60; --danger:#e74c3c; --warning:#f39c12; --info:#3498db; --text:#333; --text-light:#777; --sidebar-width:250px; }
body { background:#f5f5f5; color:var(--text); }
.dashboard-container { display:flex; min-height:100vh; }
.sidebar { width:var(--sidebar-width); background:linear-gradient(135deg,var(--primary-dark),var(--primary)); color:#fff; position:fixed; height:100vh; z-index:1000; overflow-y:auto; }
.sidebar-header { padding:1.5rem 1rem; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:10px; }
.sidebar-logo { font-size:1.8rem; } .sidebar-company { font-size:1rem; font-weight:600; }
.sidebar-nav { padding:1rem 0; }
.nav-section { padding:.5rem 1rem .3rem; font-size:.7rem; text-transform:uppercase; letter-spacing:1px; opacity:.6; }
.nav-item { padding:.75rem 1.5rem; display:flex; align-items:center; gap:12px; text-decoration:none; color:#fff; transition:all .2s; position:relative; }
.nav-item:hover { background:rgba(255,255,255,.1); } .nav-item.active { background:rgba(255,255,255,.2); border-left:4px solid #fff; }
.nav-icon { width:20px; text-align:center; }
.badge-count { background:#e74c3c; color:#fff; border-radius:10px; padding:1px 6px; font-size:.7rem; margin-left:auto; }
.main-content { flex:1; margin-left:var(--sidebar-width); }
.header { background:#fff; height:60px; box-shadow:0 2px 5px rgba(0,0,0,.1); display:flex; justify-content:space-between; align-items:center; padding:0 1.5rem; position:sticky; top:0; z-index:100; }
.header-title { font-size:1.1rem; font-weight:600; display:flex; align-items:center; gap:8px; }
.user-info { display:flex; align-items:center; gap:12px; }
.logout-btn { background:var(--primary); color:#fff; border:none; padding:.4rem .9rem; border-radius:4px; cursor:pointer; text-decoration:none; font-size:.9rem; }
.content { padding:1.5rem; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
.page-title { font-size:1.5rem; font-weight:700; }
.btn { padding:.5rem 1rem; border-radius:5px; border:none; cursor:pointer; font-size:.875rem; font-weight:500; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:all .2s; }
.btn-primary { background:var(--primary); color:#fff; } .btn-primary:hover { background:var(--primary-dark); }
.card { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:1.5rem; }
.card-header { padding:1rem 1.5rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
.card-header h3 { font-size:1rem; font-weight:600; }
.card-body { padding:1.5rem; }
.filter-form { display:flex; gap:.75rem; flex-wrap:wrap; align-items:flex-end; }
.filter-form .fg { display:flex; flex-direction:column; gap:.3rem; }
.filter-form label { font-size:.8rem; font-weight:500; color:var(--text-light); }
.filter-form input, .filter-form select { padding:.45rem .8rem; border:1px solid #ddd; border-radius:5px; font-size:.875rem; }
table { width:100%; border-collapse:collapse; }
th { background:#f8f9fa; padding:.75rem 1rem; text-align:left; font-size:.78rem; text-transform:uppercase; letter-spacing:.5px; color:var(--text-light); border-bottom:2px solid #eee; white-space:nowrap; }
td { padding:.75rem 1rem; border-bottom:1px solid #f0f0f0; font-size:.875rem; vertical-align:middle; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#fafafa; }
.stock-badge { padding:.2rem .6rem; border-radius:12px; font-size:.75rem; font-weight:600; }
.stock-ok { background:#d4edda; color:#155724; } .stock-low { background:#fff3cd; color:#856404; } .stock-out { background:#f8d7da; color:#721c24; }
.log-in { color:var(--success); } .log-out { color:var(--danger); } .log-adj { color:var(--warning); }
.type-badge { padding:.2rem .6rem; border-radius:12px; font-size:.75rem; font-weight:600; }
.type-stock_in { background:#d4edda; color:#155724; }
.type-sale_deduction { background:#f8d7da; color:#721c24; }
.type-manual_adjust { background:#fff3cd; color:#856404; }
.type-return { background:#d1ecf1; color:#0c5460; }
.summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.summary-card { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.07); padding:1.2rem; border-left:4px solid var(--primary); }
.summary-card h4 { font-size:.85rem; color:var(--text-light); margin-bottom:.5rem; }
.summary-val { font-size:1.6rem; font-weight:700; }
@media(max-width:992px) { .sidebar{transform:translateX(-100%);transition:transform .3s;} .sidebar.open{transform:translateX(0);} .main-content{margin-left:0;} }
.menu-toggle { display:none; font-size:1.4rem; cursor:pointer; }
@media(max-width:992px) { .menu-toggle{display:block;} }
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;} .overlay.open{display:block;}
@media print { .sidebar,.header,.filter-form,.btn { display:none!important; } .main-content{margin:0;} }
</style>
</head>
<body>
<div class="dashboard-container">
<div class="sidebar" id="sidebar">
    <div class="sidebar-header"><div class="sidebar-logo">🥜</div><div class="sidebar-company">MKN Bro's</div></div>
    <div class="sidebar-nav">
        <div class="nav-section">Main</div>
        <a href="dashboard.php" class="nav-item"><span class="nav-icon"><i class="fas fa-chart-pie"></i></span> Dashboard</a>
        <div class="nav-section">Operations</div>
        <a href="add_sales.php" class="nav-item"><span class="nav-icon"><i class="fas fa-file-invoice"></i></span> New Sale</a>
        <a href="sales_list.php" class="nav-item"><span class="nav-icon"><i class="fas fa-list"></i></span> Sales</a>
        <a href="purchases_list.php" class="nav-item"><span class="nav-icon"><i class="fas fa-shopping-bag"></i></span> Purchases</a>
        <div class="nav-section">Inventory</div>
        <a href="stock_management.php" class="nav-item"><span class="nav-icon"><i class="fas fa-boxes"></i></span> Stock Management
            <?php if($low_stock_count>0): ?><span class="badge-count"><?= $low_stock_count ?></span><?php endif; ?>
        </a>
        <a href="stock_report.php" class="nav-item active"><span class="nav-icon"><i class="fas fa-chart-bar"></i></span> Stock Report</a>
        <div class="nav-section">Records</div>
        <a href="products_list.php" class="nav-item"><span class="nav-icon"><i class="fas fa-tag"></i></span> Products</a>
        <a href="customers_list.php" class="nav-item"><span class="nav-icon"><i class="fas fa-users"></i></span> Customers</a>
        <a href="expenses_list.php" class="nav-item"><span class="nav-icon"><i class="fas fa-receipt"></i></span> Expenses</a>
        <a href="users_list.php" class="nav-item"><span class="nav-icon"><i class="fas fa-user-cog"></i></span> Users</a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <div style="display:flex;align-items:center;gap:1rem;">
            <span class="menu-toggle" id="menuToggle">☰</span>
            <div class="header-title"><i class="fas fa-chart-bar" style="color:var(--primary)"></i> Stock Report</div>
        </div>
        <div class="user-info">
            <span style="font-size:.9rem;color:var(--text-light)">👋 <?= htmlspecialchars($user) ?></span>
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div class="page-title">📊 Stock Report</div>
            <a href="stock_management.php" class="btn btn-primary"><i class="fas fa-boxes"></i> Manage Stock</a>
        </div>

        <!-- Summary Cards -->
        <?php
        $total_products = count($stock_summary);
        $total_stock_value = array_sum(array_map(fn($r) => $r['current_stock'] * $r['basic_price'], $stock_summary));
        $low_count = count(array_filter($stock_summary, fn($r) => $r['current_stock'] > 0 && $r['current_stock'] <= $r['minimum_stock']));
        $out_count = count(array_filter($stock_summary, fn($r) => $r['current_stock'] == 0));
        ?>
        <div class="summary-grid">
            <div class="summary-card">
                <h4>Total Products</h4>
                <div class="summary-val"><?= $total_products ?></div>
            </div>
            <div class="summary-card" style="border-color:var(--success)">
                <h4>Total Stock Value</h4>
                <div class="summary-val" style="color:var(--success)">₹<?= number_format($total_stock_value,2) ?></div>
            </div>
            <div class="summary-card" style="border-color:var(--warning)">
                <h4>Low Stock Items</h4>
                <div class="summary-val" style="color:var(--warning)"><?= $low_count ?></div>
            </div>
            <div class="summary-card" style="border-color:var(--danger)">
                <h4>Out of Stock</h4>
                <div class="summary-val" style="color:var(--danger)"><?= $out_count ?></div>
            </div>
        </div>

        <!-- Stock Summary Table -->
        <div class="card">
            <div class="card-header">
                <h3>📦 Product-wise Stock Summary</h3>
                <?php if ($product_filter): ?>
                <a href="stock_report.php" style="font-size:.85rem;color:var(--info)">Clear Filter</a>
                <?php endif; ?>
            </div>
            <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Code</th><th>Product Name</th><th>Mode</th>
                        <th>Total Stock In</th><th>Current Stock</th><th>Min Stock</th>
                        <th>Stock Value</th><th>Status</th><th>Batch Detail</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($stock_summary as $i => $p):
                    $s = (int)$p['current_stock']; $m = (int)$p['minimum_stock'];
                    $st = $s===0?'out':($s<=$m?'low':'ok');
                    $lbl = $st==='out'?'🚨 Out':($st==='low'?'⚠️ Low':'✅ OK');
                    $val = $s * (float)$p['basic_price'];

                    // Get batches
                    $br = $conn->query("SELECT batch_date, quantity_in, quantity_remaining FROM stock_batches WHERE product_id={$p['id']} AND quantity_remaining>0 ORDER BY batch_date ASC");
                    $bp = $br ? $br->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code><?= htmlspecialchars($p['product_code']) ?></code></td>
                    <td style="font-weight:500"><?= htmlspecialchars($p['product_name']) ?></td>
                    <td><?= $p['packing_mode'] ?></td>
                    <td><?= number_format($p['total_in']) ?></td>
                    <td style="font-weight:700"><?= number_format($s) ?></td>
                    <td><?= $m ?></td>
                    <td>₹<?= number_format($val,2) ?></td>
                    <td><span class="stock-badge stock-<?= $st ?>"><?= $lbl ?></span></td>
                    <td>
                        <?php if (!empty($bp)): ?>
                        <details style="font-size:.8rem">
                            <summary style="cursor:pointer;color:var(--info)"><?= count($bp) ?> batch(es)</summary>
                            <div style="padding:.5rem 0">
                            <?php foreach ($bp as $b): ?>
                            <div style="padding:2px 0;color:var(--text)">
                                📅 <?= date('d M Y',strtotime($b['batch_date'])) ?>: <strong><?= number_format($b['quantity_remaining']) ?></strong> / <?= number_format($b['quantity_in']) ?> pcs
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </details>
                        <?php else: echo '—'; endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Audit Log -->
        <div class="card">
            <div class="card-header"><h3>📋 Stock Audit Log</h3></div>
            <div class="card-body">
                <form method="GET" class="filter-form" style="margin-bottom:1.2rem">
                    <div class="fg">
                        <label>Product</label>
                        <select name="product_id">
                            <option value="">All Products</option>
                            <?php foreach ($all_products as $ap): ?>
                            <option value="<?= $ap['id'] ?>" <?= $product_filter==$ap['id']?'selected':'' ?>><?= htmlspecialchars($ap['product_code'].' - '.$ap['product_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Type</label>
                        <select name="log_type">
                            <option value="">All Types</option>
                            <option value="stock_in" <?= $log_type==='stock_in'?'selected':'' ?>>Stock In</option>
                            <option value="sale_deduction" <?= $log_type==='sale_deduction'?'selected':'' ?>>Sale Deduction</option>
                            <option value="manual_adjust" <?= $log_type==='manual_adjust'?'selected':'' ?>>Manual Adjust</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label>From</label>
                        <input type="date" name="date_from" value="<?= $date_from ?>">
                    </div>
                    <div class="fg">
                        <label>To</label>
                        <input type="date" name="date_to" value="<?= $date_to ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                </form>
            </div>
            <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>#</th><th>Date & Time</th><th>Product</th><th>Type</th><th>Qty Change</th><th>Batch Date</th><th>Reason</th><th>Done By</th></tr>
                </thead>
                <tbody>
                <?php if (empty($audit_log)): ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--text-light)">No records found for selected filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($audit_log as $i => $log): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></td>
                    <td style="font-weight:500"><?= htmlspecialchars($log['product_name']) ?></td>
                    <td><span class="type-badge type-<?= $log['change_type'] ?>"><?= str_replace('_',' ',ucfirst($log['change_type'])) ?></span></td>
                    <td style="font-weight:700;color:<?= $log['quantity']>0?'var(--success)':'var(--danger)' ?>">
                        <?= $log['quantity']>0?'+':'' ?><?= number_format($log['quantity']) ?>
                    </td>
                    <td><?= $log['batch_date'] ? date('d M Y',strtotime($log['batch_date'])) : '—' ?></td>
                    <td><?= htmlspecialchars($log['reason'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($log['done_by'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="overlay"></div>
</div>
<script>
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
menuToggle.addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('open');});
overlay.addEventListener('click',()=>{sidebar.classList.remove('open');overlay.classList.remove('open');});
</script>
</body>
</html>
