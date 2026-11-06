<?php
require_once 'config.php';
requireLogin();
$page_title = 'Stock Report';

// Filters
$filter_cat   = isset($_GET['category'])   ? intval($_GET['category'])            : 0;
$filter_from  = isset($_GET['date_from'])  ? sanitize($conn, $_GET['date_from'])  : '';
$filter_to    = isset($_GET['date_to'])    ? sanitize($conn, $_GET['date_to'])    : '';
$filter_stock = isset($_GET['stock_status']) ? sanitize($conn, $_GET['stock_status']) : '';

// Build WHERE
$where = [];
if ($filter_cat)   $where[] = "s.category_id = $filter_cat";
if ($filter_from)  $where[] = "s.batch_date >= '$filter_from'";
if ($filter_to)    $where[] = "s.batch_date <= '$filter_to'";
if ($filter_stock === 'in_stock')    $where[] = "s.remaining_stock > 0";
if ($filter_stock === 'out')         $where[] = "s.remaining_stock = 0";
if ($filter_stock === 'low')         $where[] = "s.remaining_stock > 0 AND s.remaining_stock < (s.new_stock * 0.20)";
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Category summary (aggregate view)
$cat_summary = [];
$cs = $conn->query("SELECT pc.id, pc.category_name, pc.price_per,
    COUNT(s.id)              AS batch_count,
    SUM(s.new_stock)         AS total_added,
    SUM(s.remaining_stock)   AS total_remaining,
    SUM(s.new_stock - s.remaining_stock) AS total_sold,
    SUM(s.total_cost)        AS total_cost,
    MIN(s.batch_date)        AS first_batch,
    MAX(s.batch_date)        AS last_batch
    FROM stock s
    JOIN product_categories pc ON s.category_id = pc.id
    " . ($filter_cat ? "WHERE s.category_id=$filter_cat" : "") . "
    GROUP BY s.category_id
    ORDER BY pc.category_name");
while ($r = $cs->fetch_assoc()) $cat_summary[] = $r;

// Grand totals
$gt = $conn->query("SELECT
    COUNT(*)                      AS total_batches,
    SUM(s.new_stock)              AS grand_added,
    SUM(s.remaining_stock)        AS grand_remaining,
    SUM(s.new_stock - s.remaining_stock) AS grand_sold,
    SUM(s.total_cost)             AS grand_cost
    FROM stock s
    $where_sql");
$grand = $gt->fetch_assoc();

// Batch-level detail
$batch_rows = [];
$br = $conn->query("SELECT s.*, pc.category_name, pc.price_per
    FROM stock s
    JOIN product_categories pc ON s.category_id = pc.id
    $where_sql
    ORDER BY s.batch_date DESC, s.id DESC");
while ($r = $br->fetch_assoc()) $batch_rows[] = $r;

// Stock movement (sold per category per month — last 6 months)
$movement = [];
for ($i = 5; $i >= 0; $i--) {
    $r = $conn->query("SELECT pc.category_name,
        COALESCE(SUM(si.weight * si.quantity), 0) AS kg_sold,
        DATE_FORMAT(NOW() - INTERVAL $i MONTH, '%b %Y') AS label
        FROM sale_items si
        JOIN product_categories pc ON si.category_id = pc.id
        WHERE MONTH(si.sale_id IN (SELECT id FROM sales WHERE MONTH(bill_date)=MONTH(NOW()-INTERVAL $i MONTH) AND YEAR(bill_date)=YEAR(NOW()-INTERVAL $i MONTH)))
        GROUP BY si.category_id");
    // Simplified: just get total KG sold in that month across all categories
    $r2 = $conn->query("SELECT
        COALESCE(SUM(si.weight * si.quantity), 0) AS kg_sold,
        DATE_FORMAT(s.bill_date, '%b %Y') AS label
        FROM sale_items si
        JOIN sales s ON si.sale_id = s.id
        WHERE MONTH(s.bill_date)=MONTH(NOW() - INTERVAL $i MONTH)
          AND YEAR(s.bill_date)=YEAR(NOW() - INTERVAL $i MONTH)");
    $row2 = $r2->fetch_assoc();
    $movement[] = [
        'label'   => $row2['label'] ?: date('M Y', strtotime("-$i months")),
        'kg_sold' => round(floatval($row2['kg_sold']), 3)
    ];
}

// Categories for filter dropdown
$cats_dd = [];
$rcd = $conn->query("SELECT id, category_name FROM product_categories ORDER BY category_name");
while ($r = $rcd->fetch_assoc()) $cats_dd[] = $r;

include 'sidebar.php';
?>

<div class="report-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-icon">📦</div>
            <div>
                <h2>Stock Report</h2>
                <p>Inventory status, batch details &amp; movement analysis</p>
            </div>
        </div>
        <div class="page-header-right">
            <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print Report</button>
            <a href="stock_dashboard.php" class="btn btn-secondary btn-sm">📈 Stock Dashboard</a>
            <a href="stock_add.php" class="btn btn-primary btn-sm">➕ Add Stock</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card no-print">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($cats_dd as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_cat == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Batch Date From</label>
                    <input type="date" name="date_from" value="<?php echo $filter_from; ?>" class="form-input">
                </div>
                <div class="filter-group">
                    <label>Batch Date To</label>
                    <input type="date" name="date_to" value="<?php echo $filter_to; ?>" class="form-input">
                </div>
                <div class="filter-group">
                    <label>Stock Status</label>
                    <select name="stock_status" class="form-select">
                        <option value="">All</option>
                        <option value="in_stock"  <?php echo $filter_stock === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="low"       <?php echo $filter_stock === 'low'      ? 'selected' : ''; ?>>Low Stock (&lt;20%)</option>
                        <option value="out"       <?php echo $filter_stock === 'out'      ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                    <a href="stock_report.php" class="btn btn-outline btn-sm">✖ Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Grand Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <div class="stat-icon-wrap">📥</div>
            <div class="stat-body">
                <span class="stat-lbl">Total Stock Added</span>
                <span class="stat-val"><?php echo number_format($grand['grand_added'] ?? 0, 3); ?></span>
                <span class="stat-sub">KG / units across all batches</span>
            </div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-icon-wrap">✅</div>
            <div class="stat-body">
                <span class="stat-lbl">Remaining Stock</span>
                <span class="stat-val"><?php echo number_format($grand['grand_remaining'] ?? 0, 3); ?></span>
                <span class="stat-sub">KG currently available</span>
            </div>
        </div>
        <div class="stat-card stat-orange">
            <div class="stat-icon-wrap">📤</div>
            <div class="stat-body">
                <span class="stat-lbl">Total Sold</span>
                <span class="stat-val"><?php echo number_format($grand['grand_sold'] ?? 0, 3); ?></span>
                <span class="stat-sub">KG dispatched via sales</span>
            </div>
        </div>
        <div class="stat-card stat-purple">
            <div class="stat-icon-wrap">💰</div>
            <div class="stat-body">
                <span class="stat-lbl">Total Stock Cost</span>
                <span class="stat-val">₹<?php echo number_format($grand['grand_cost'] ?? 0, 2); ?></span>
                <span class="stat-sub">Purchase investment</span>
            </div>
        </div>
        <div class="stat-card stat-teal">
            <div class="stat-icon-wrap">📦</div>
            <div class="stat-body">
                <span class="stat-lbl">Total Batches</span>
                <span class="stat-val"><?php echo number_format($grand['total_batches'] ?? 0); ?></span>
                <span class="stat-sub">Stock entries recorded</span>
            </div>
        </div>
    </div>

    <!-- Category Summary Table -->
    <div class="report-card">
        <div class="report-card-header">
            <span class="rch-icon">🏷️</span>
            <div>
                <h3>Category-wise Summary</h3>
                <p>Stock status grouped by category</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Batches</th>
                        <th>Added</th>
                        <th>Sold</th>
                        <th>Remaining</th>
                        <th>Usage %</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cat_summary as $i => $cs):
                    $added = floatval($cs['total_added']);
                    $rem   = floatval($cs['total_remaining']);
                    $sold  = floatval($cs['total_sold']);
                    $usage = $added > 0 ? round($sold / $added * 100, 1) : 0;
                    $rem_pct = $added > 0 ? round($rem / $added * 100, 1) : 0;
                    if ($rem == 0) { $status = 'out'; $slabel = 'Out of Stock'; }
                    elseif ($rem_pct < 20) { $status = 'low'; $slabel = 'Low Stock'; }
                    else { $status = 'ok'; $slabel = 'In Stock'; }
                ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><strong><?php echo htmlspecialchars($cs['category_name']); ?></strong></td>
                    <td><?php echo $cs['price_per']; ?></td>
                    <td><?php echo $cs['batch_count']; ?></td>
                    <td class="num-cell"><?php echo number_format($added, 3); ?></td>
                    <td class="num-cell sold-cell"><?php echo number_format($sold, 3); ?></td>
                    <td class="num-cell rem-cell <?php echo $status === 'out' ? 'zero' : ''; ?>"><?php echo number_format($rem, 3); ?></td>
                    <td>
                        <div class="prog-wrap">
                            <div class="prog-bar">
                                <div class="prog-fill" style="width:<?php echo min($usage,100); ?>%;background:<?php echo $usage>=80 ? '#2e7d32' : ($usage>=40 ? '#f57c00' : '#c62828'); ?>;"></div>
                            </div>
                            <span class="prog-pct"><?php echo $usage; ?>%</span>
                        </div>
                    </td>
                    <td class="num-cell">₹<?php echo number_format($cs['total_cost'], 2); ?></td>
                    <td><span class="badge badge-<?php echo $status === 'ok' ? 'success' : ($status === 'low' ? 'warning' : 'danger'); ?>"><?php echo $slabel; ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cat_summary)): ?>
                    <tr><td colspan="10" class="empty-row">No stock data found.</td></tr>
                <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="tfoot-row">
                        <td colspan="4"><strong>TOTAL</strong></td>
                        <td class="num-cell"><strong><?php echo number_format($grand['grand_added'] ?? 0, 3); ?></strong></td>
                        <td class="num-cell sold-cell"><strong><?php echo number_format($grand['grand_sold'] ?? 0, 3); ?></strong></td>
                        <td class="num-cell"><strong><?php echo number_format($grand['grand_remaining'] ?? 0, 3); ?></strong></td>
                        <td></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['grand_cost'] ?? 0, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Monthly Stock Movement Chart -->
    <div class="report-card no-print">
        <div class="report-card-header">
            <span class="rch-icon">📉</span>
            <div>
                <h3>Monthly Stock Movement</h3>
                <p>Total KG sold per month (last 6 months)</p>
            </div>
        </div>
        <div style="padding:20px;">
            <canvas id="movementChart" height="80"></canvas>
        </div>
    </div>

    <!-- Batch-level Detail -->
    <div class="report-card">
        <div class="report-card-header">
            <span class="rch-icon">📋</span>
            <div>
                <h3>Batch-wise Stock Details</h3>
                <p>All stock batches with FIFO order</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Batch Date</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Added (Stock In)</th>
                        <th>Cost/Unit</th>
                        <th>Total Cost</th>
                        <th>Sold</th>
                        <th>Remaining</th>
                        <th>Usage %</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($batch_rows as $i => $b):
                    $added = floatval($b['new_stock']);
                    $rem   = floatval($b['remaining_stock']);
                    $sold  = $added - $rem;
                    $usage = $added > 0 ? round($sold / $added * 100, 1) : 0;
                    if ($rem == 0)             { $st = 'danger'; $sl = 'Depleted'; }
                    elseif ($rem < $added*0.2) { $st = 'warning'; $sl = 'Low'; }
                    else                       { $st = 'success'; $sl = 'Available'; }
                ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><?php echo date('d M Y', strtotime($b['batch_date'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($b['category_name']); ?></strong></td>
                    <td><?php echo $b['price_per']; ?></td>
                    <td class="num-cell"><?php echo number_format($added, 3); ?></td>
                    <td class="num-cell">₹<?php echo number_format($b['cost_per_unit'], 2); ?></td>
                    <td class="num-cell">₹<?php echo number_format($b['total_cost'], 2); ?></td>
                    <td class="num-cell sold-cell"><?php echo number_format($sold, 3); ?></td>
                    <td class="num-cell rem-cell <?php echo $rem==0?'zero':''; ?>"><?php echo number_format($rem, 3); ?></td>
                    <td>
                        <div class="prog-wrap">
                            <div class="prog-bar">
                                <div class="prog-fill" style="width:<?php echo min($usage,100); ?>%;background:<?php echo $usage>=80?'#2e7d32':($usage>=40?'#f57c00':'#c62828'); ?>;"></div>
                            </div>
                            <span class="prog-pct"><?php echo $usage; ?>%</span>
                        </div>
                    </td>
                    <td><span class="badge badge-<?php echo $st; ?>"><?php echo $sl; ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($batch_rows)): ?>
                    <tr><td colspan="11" class="empty-row">No batch records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="report-footer">Report generated on <?php echo date('d M Y, h:i A'); ?> | MKN Bro</p>
</div>

<style>
/* ===== REPORT PAGE ===== */
.report-page { max-width: 1500px; margin: 0 auto; }

.page-header {
    display: flex; justify-content: space-between; align-items: center;
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 20px; padding: 22px 28px; margin-bottom: 24px;
    color: white; flex-wrap: wrap; gap: 14px;
}
.page-header-left { display: flex; align-items: center; gap: 14px; }
.page-icon { font-size: 36px; }
.page-header h2 { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
.page-header p  { font-size: 13px; opacity: .85; margin: 0; }
.page-header-right { display: flex; gap: 10px; flex-wrap: wrap; }

/* Filter Card */
.filter-card {
    background: #fff; border-radius: 16px; padding: 20px 24px;
    margin-bottom: 24px; border: 1px solid #eef2f8;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.filter-form .filter-row { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 5px; min-width: 150px; flex: 1; }
.filter-group label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; }
.filter-actions { display: flex; gap: 8px; }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 16px; margin-bottom: 24px; }
.stat-card {
    border-radius: 18px; padding: 18px 20px; display: flex; align-items: center;
    gap: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.04); border: 1px solid #eef2f8;
    background: #fff;
}
.stat-icon-wrap { font-size: 28px; width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; }
.stat-blue  .stat-icon-wrap { background: linear-gradient(135deg,#1e4663,#2c6288); }
.stat-green .stat-icon-wrap { background: linear-gradient(135deg,#2e7d32,#43a047); }
.stat-orange.stat-icon-wrap, .stat-orange .stat-icon-wrap { background: linear-gradient(135deg,#f57c00,#ff9800); }
.stat-purple.stat-icon-wrap, .stat-purple .stat-icon-wrap { background: linear-gradient(135deg,#7b1fa2,#9c27b0); }
.stat-teal  .stat-icon-wrap { background: linear-gradient(135deg,#00695c,#00897b); }
.stat-body { flex: 1; }
.stat-lbl { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; display: block; }
.stat-val { font-size: 22px; font-weight: 800; color: #1e293b; display: block; line-height: 1.2; }
.stat-sub { font-size: 11px; color: #94a3b8; display: block; margin-top: 2px; }

/* Report Card */
.report-card {
    background: #fff; border-radius: 20px; margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,.05); border: 1px solid #eef2f8; overflow: hidden;
}
.report-card-header {
    display: flex; align-items: center; gap: 14px;
    padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #eef2f8;
}
.rch-icon { font-size: 24px; }
.report-card-header h3 { font-size: 17px; font-weight: 700; color: #1e4663; margin: 0 0 2px; }
.report-card-header p  { font-size: 12px; color: #64748b; margin: 0; }

/* Table */
.table-wrap { overflow-x: auto; }
.rep-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rep-table th {
    background: #f8fafc; padding: 11px 14px; text-align: left;
    font-weight: 700; color: #1e4663; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.rep-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.rep-table tr:hover td { background: #fafcff; }
.num-cell { text-align: right; font-weight: 600; }
.sold-cell { color: #c62828; }
.rem-cell  { color: #2e7d32; }
.rem-cell.zero { color: #c62828; }
.tfoot-row td { background: #f0f4f8; border-top: 2px solid #e2e8f0; }
.empty-row { text-align: center; padding: 30px !important; color: #94a3b8; }

/* Progress bar */
.prog-wrap { display: flex; align-items: center; gap: 6px; }
.prog-bar  { background: #e2e8f0; border-radius: 8px; height: 8px; width: 70px; overflow: hidden; }
.prog-fill { height: 100%; border-radius: 8px; }
.prog-pct  { font-size: 11px; color: #64748b; white-space: nowrap; }

/* Badges */
.badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-success { background: #e8f5e9; color: #2e7d32; }
.badge-warning  { background: #fff3e0; color: #f57c00; }
.badge-danger   { background: #ffebee; color: #c62828; }

/* Buttons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px;
    border-radius: 10px; font-size: 12px; font-weight: 500; cursor: pointer;
    transition: all .2s; text-decoration: none; border: none; }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-primary  { background: #1e4663; color: #fff; }
.btn-primary:hover { background: #16364f; }
.btn-secondary { background: #f1f5f9; color: #1e4663; border: 1px solid #e2e8f0; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-outline  { background: transparent; color: #1e4663; border: 1px solid #cbd5e1; }
.btn-outline:hover { background: #f8fafc; }

.form-input, .form-select {
    padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; width: 100%;
}
.form-input:focus, .form-select:focus { outline: none; border-color: #1e4663; }

.report-footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 8px; padding-bottom: 20px; }

@media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 700px)  { .stats-grid { grid-template-columns: 1fr 1fr; } .page-header { flex-direction: column; } }

/* Print */
@media print {
    .no-print { display: none !important; }
    #sidebar, .toggle-btn { display: none !important; }
    #main-content { margin-left: 0 !important; }
    .report-card { box-shadow: none; border: 1px solid #ccc; page-break-inside: avoid; }
    .page-header { background: #1e4663 !important; -webkit-print-color-adjust: exact; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var labels  = <?php echo json_encode(array_column($movement, 'label')); ?>;
    var kgSold  = <?php echo json_encode(array_column($movement, 'kg_sold')); ?>;
    var ctx = document.getElementById('movementChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'KG Sold',
                data: kgSold,
                backgroundColor: 'rgba(30,70,99,0.75)',
                borderColor: '#1e4663',
                borderRadius: 8,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.raw.toFixed(3) + ' KG' } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v + ' KG', font: { size: 10 } }, grid: { color: '#e2e8f0' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
});
</script>

<?php include 'sidebar_close.php'; ?>