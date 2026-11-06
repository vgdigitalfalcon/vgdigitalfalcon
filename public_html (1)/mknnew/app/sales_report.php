<?php
require_once 'config.php';
requireLogin();
$page_title = 'Sales Report';

// Filters
$filter_customer = isset($_GET['customer'])    ? intval($_GET['customer'])             : 0;
$filter_status   = isset($_GET['status'])      ? sanitize($conn, $_GET['status'])      : '';
$filter_from     = isset($_GET['date_from'])   ? sanitize($conn, $_GET['date_from'])   : '';
$filter_to       = isset($_GET['date_to'])     ? sanitize($conn, $_GET['date_to'])     : '';
$filter_cat      = isset($_GET['category'])    ? intval($_GET['category'])             : 0;
$group_by        = isset($_GET['group_by'])    ? sanitize($conn, $_GET['group_by'])    : 'none';



// Build WHERE for sales
$where = [];
if ($filter_customer) $where[] = "s.customer_id = $filter_customer";
if ($filter_status)   $where[] = "s.payment_status = '$filter_status'";
if ($filter_from)     $where[] = "s.bill_date >= '$filter_from'";
if ($filter_to)       $where[] = "s.bill_date <= '$filter_to'";
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Grand totals
$gt = $conn->query("SELECT
    COUNT(*)                      AS total_bills,
    COALESCE(SUM(s.grand_total),0)  AS total_sales,
    COALESCE(SUM(s.amount_paid),0)  AS total_collected,
    COALESCE(SUM(s.amount_due),0)   AS total_due,
    COALESCE(SUM(s.subtotal),0)     AS total_subtotal,
    COALESCE(SUM(s.cgst+s.sgst),0)  AS total_gst,
    COALESCE(SUM(s.transport_cost),0) AS total_transport
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    $where_sql");
$grand = $gt->fetch_assoc();

// All sales rows (for detail table)
$sales_rows = [];
$sr = $conn->query("SELECT s.*, c.customer_name, c.shop_name, c.city
    FROM sales s JOIN customers c ON s.customer_id=c.id
    $where_sql
    ORDER BY s.bill_date DESC, s.id DESC");
while ($r = $sr->fetch_assoc()) $sales_rows[] = $r;

// Fetch items for all sales in result (for product breakdown)
$sale_ids = array_column($sales_rows, 'id');
$items_by_sale = [];
$product_totals = [];
if (!empty($sale_ids)) {
    $ids_str = implode(',', $sale_ids);
    $ir = $conn->query("SELECT si.sale_id, si.quantity, si.weight, si.rate, si.line_total,
        p.product_name, p.product_code, pc.category_name, pc.id as cat_id
        FROM sale_items si
        JOIN products p ON si.product_id=p.id
        JOIN product_categories pc ON si.category_id=pc.id
        WHERE si.sale_id IN ($ids_str)");
    while ($r = $ir->fetch_assoc()) {
        $items_by_sale[$r['sale_id']][] = $r;
        $key = $r['cat_id'] . '_' . $r['product_name'];
        if (!isset($product_totals[$key])) {
            $product_totals[$key] = [
                'product_name'  => $r['product_name'],
                'product_code'  => $r['product_code'],
                'category_name' => $r['category_name'],
                'total_qty'     => 0,
                'total_kg'      => 0,
                'total_amount'  => 0,
                'bill_count'    => 0,
            ];
        }
        $product_totals[$key]['total_qty']    += floatval($r['quantity']);
        $product_totals[$key]['total_kg']     += round(floatval($r['quantity']) * floatval($r['weight']), 3);
        $product_totals[$key]['total_amount'] += floatval($r['line_total']);
        $product_totals[$key]['bill_count']++;
    }
}
usort($product_totals, fn($a,$b) => $b['total_amount'] <=> $a['total_amount']);

// Customer-wise summary
$cust_summary = [];
$cs = $conn->query("SELECT c.customer_name, c.shop_name, c.city,
    COUNT(*) AS bills, SUM(s.grand_total) AS total, SUM(s.amount_paid) AS paid, SUM(s.amount_due) AS due
    FROM sales s JOIN customers c ON s.customer_id=c.id
    $where_sql GROUP BY s.customer_id ORDER BY total DESC");
while ($r = $cs->fetch_assoc()) $cust_summary[] = $r;

// Monthly breakdown (last 12 months, respects date filter if set)
$monthly = [];
for ($i = 11; $i >= 0; $i--) {
    $r = $conn->query("SELECT COALESCE(SUM(s.grand_total),0) AS total,
        COALESCE(SUM(s.amount_paid),0) AS collected,
        COUNT(*) AS bills,
        DATE_FORMAT(NOW() - INTERVAL $i MONTH,'%b %Y') AS label
        FROM sales s JOIN customers c ON s.customer_id=c.id
        WHERE MONTH(s.bill_date)=MONTH(NOW()-INTERVAL $i MONTH)
          AND YEAR(s.bill_date)=YEAR(NOW()-INTERVAL $i MONTH)
          " . ($filter_customer ? " AND s.customer_id=$filter_customer" : "") . "
          " . ($filter_status   ? " AND s.payment_status='$filter_status'" : "") . "
        ");
    $row = $r->fetch_assoc();
    $monthly[] = [
        'label'     => $row['label'],
        'total'     => floatval($row['total']),
        'collected' => floatval($row['collected']),
        'bills'     => intval($row['bills']),
    ];
}

// Dropdown data
$customers_dd = [];
$rcd = $conn->query("SELECT id, customer_name, shop_name FROM customers ORDER BY customer_name");
while ($r = $rcd->fetch_assoc()) $customers_dd[] = $r;

$cats_dd = [];
$rcat = $conn->query("SELECT id, category_name FROM product_categories ORDER BY category_name");
while ($r = $rcat->fetch_assoc()) $cats_dd[] = $r;

include 'sidebar.php';
?>

<div class="report-page">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-icon">💰</div>
            <div>
                <h2>Sales Report</h2>
                <p>Detailed sales analysis, product breakdown &amp; collection summary</p>
            </div>
        </div>
        <div class="page-header-right">
            <button onclick="window.print()" class="btn btn-outline-white btn-sm">🖨️ Print Report</button>
            <a href="sales_dashboard.php" class="btn btn-secondary btn-sm">💰 Sales Dashboard</a>
            <a href="sales_add.php"       class="btn btn-primary-light btn-sm">➕ New Sale</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card no-print">
        <form method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Customer</label>
                    <select name="customer" class="form-select">
                        <option value="">All Customers</option>
                        <?php foreach ($customers_dd as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_customer == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['customer_name']); ?><?php echo $c['shop_name'] ? ' (' . htmlspecialchars($c['shop_name']) . ')' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Payment Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="Paid"         <?php echo $filter_status === 'Paid'         ? 'selected' : ''; ?>>✅ Paid</option>
                        <option value="Not Paid"     <?php echo $filter_status === 'Not Paid'     ? 'selected' : ''; ?>>⏳ Not Paid</option>
                        <option value="Partial Paid" <?php echo $filter_status === 'Partial Paid' ? 'selected' : ''; ?>>🔸 Partial Paid</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?php echo $filter_from; ?>" class="form-input">
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?php echo $filter_to; ?>" class="form-input">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                    <a href="sales_report.php" class="btn btn-outline btn-sm">✖ Clear</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Grand Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-blue">
            <div class="stat-icon-wrap">🧾</div>
            <div class="stat-body">
                <span class="stat-lbl">Total Bills</span>
                <span class="stat-val"><?php echo number_format($grand['total_bills']); ?></span>
                <span class="stat-sub">Sales invoices</span>
            </div>
        </div>
        <div class="stat-card stat-indigo">
            <div class="stat-icon-wrap">💰</div>
            <div class="stat-body">
                <span class="stat-lbl">Total Billed</span>
                <span class="stat-val">₹<?php echo number_format($grand['total_sales'], 2); ?></span>
                <span class="stat-sub">Grand total all invoices</span>
            </div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-icon-wrap">✅</div>
            <div class="stat-body">
                <span class="stat-lbl">Collected</span>
                <span class="stat-val">₹<?php echo number_format($grand['total_collected'], 2); ?></span>
                <span class="stat-sub"><?php echo $grand['total_sales'] > 0 ? round($grand['total_collected']/$grand['total_sales']*100,1) : 0; ?>% collection rate</span>
            </div>
        </div>
        <div class="stat-card stat-orange">
            <div class="stat-icon-wrap">⏳</div>
            <div class="stat-body">
                <span class="stat-lbl">Pending Dues</span>
                <span class="stat-val">₹<?php echo number_format($grand['total_due'], 2); ?></span>
                <span class="stat-sub">Yet to collect</span>
            </div>
        </div>
        <div class="stat-card stat-teal">
            <div class="stat-icon-wrap">🚚</div>
            <div class="stat-body">
                <span class="stat-lbl">Transport Cost</span>
                <span class="stat-val">₹<?php echo number_format($grand['total_transport'], 2); ?></span>
                <span class="stat-sub">GST: ₹<?php echo number_format($grand['total_gst'], 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="report-card no-print">
        <div class="report-card-header">
            <span class="rch-icon">📈</span>
            <div>
                <h3>Monthly Sales Trend</h3>
                <p>Billed amount vs Collected — last 12 months</p>
            </div>
        </div>
        <div style="padding:20px;">
            <canvas id="monthlyChart" height="80"></canvas>
        </div>
    </div>

    <!-- Product-wise Summary -->
    <div class="report-card">
        <div class="report-card-header">
            <span class="rch-icon">📦</span>
            <div>
                <h3>Product-wise Sales Summary</h3>
                <p>Total quantity, KG &amp; revenue per product</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>No. of Entries</th>
                        <th>Total Pieces</th>
                        <th>Total KG</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($product_totals as $i => $pt): ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><strong><?php echo htmlspecialchars($pt['product_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($pt['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($pt['product_code'] ?? '—'); ?></td>
                    <td class="num-cell"><?php echo $pt['bill_count']; ?></td>
                    <td class="num-cell"><?php echo number_format($pt['total_qty'], 0); ?> pcs</td>
                    <td class="num-cell kg-cell"><?php echo number_format($pt['total_kg'], 3); ?> KG</td>
                    <td class="num-cell amt-cell">₹<?php echo number_format($pt['total_amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($product_totals)): ?>
                    <tr><td colspan="8" class="empty-row">No product data found for selected filters.</td></tr>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($product_totals)): ?>
                <tfoot>
                    <tr class="tfoot-row">
                        <td colspan="5"><strong>TOTAL</strong></td>
                        <td class="num-cell"><strong><?php echo number_format(array_sum(array_column($product_totals,'total_qty')),0); ?> pcs</strong></td>
                        <td class="num-cell kg-cell"><strong><?php echo number_format(array_sum(array_column($product_totals,'total_kg')),3); ?> KG</strong></td>
                        <td class="num-cell amt-cell"><strong>₹<?php echo number_format(array_sum(array_column($product_totals,'total_amount')),2); ?></strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Customer-wise Summary -->
    <div class="report-card">
        <div class="report-card-header">
            <span class="rch-icon">👥</span>
            <div>
                <h3>Customer-wise Summary</h3>
                <p>Sales breakdown per customer</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>City</th>
                        <th>Bills</th>
                        <th>Total Billed</th>
                        <th>Collected</th>
                        <th>Pending Due</th>
                        <th>Collection %</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cust_summary as $i => $cs):
                    $coll_pct = $cs['total'] > 0 ? round($cs['paid']/$cs['total']*100,1) : 0;
                ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($cs['customer_name']); ?></strong>
                        <?php if ($cs['shop_name']): ?>
                            <br><small style="color:#888;"><?php echo htmlspecialchars($cs['shop_name']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($cs['city'] ?? '—'); ?></td>
                    <td class="num-cell"><?php echo $cs['bills']; ?></td>
                    <td class="num-cell amt-cell">₹<?php echo number_format($cs['total'], 2); ?></td>
                    <td class="num-cell" style="color:#2e7d32;">₹<?php echo number_format($cs['paid'], 2); ?></td>
                    <td class="num-cell" style="color:<?php echo $cs['due'] > 0 ? '#c62828' : '#2e7d32'; ?>;">
                        ₹<?php echo number_format($cs['due'], 2); ?>
                    </td>
                    <td>
                        <div class="prog-wrap">
                            <div class="prog-bar">
                                <div class="prog-fill" style="width:<?php echo min($coll_pct,100); ?>%;background:<?php echo $coll_pct>=80?'#2e7d32':($coll_pct>=40?'#f57c00':'#c62828'); ?>;"></div>
                            </div>
                            <span class="prog-pct"><?php echo $coll_pct; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cust_summary)): ?>
                    <tr><td colspan="8" class="empty-row">No sales data found.</td></tr>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($cust_summary)): ?>
                <tfoot>
                    <tr class="tfoot-row">
                        <td colspan="3"><strong>TOTAL</strong></td>
                        <td class="num-cell"><strong><?php echo $grand['total_bills']; ?></strong></td>
                        <td class="num-cell amt-cell"><strong>₹<?php echo number_format($grand['total_sales'],2); ?></strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_collected'],2); ?></strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_due'],2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Detailed Bills Table -->
    <div class="report-card">
        <div class="report-card-header">
            <span class="rch-icon">🧾</span>
            <div>
                <h3>Bill-wise Detail</h3>
                <p>Every invoice with items breakdown</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Subtotal</th>
                        <th>GST</th>
                        <th>Transport</th>
                        <th>Grand Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sales_rows as $i => $sr):
                    $items = $items_by_sale[$sr['id']] ?? [];
                    $badge_cls = match($sr['payment_status']) {
                        'Paid' => 'badge-success', 'Not Paid' => 'badge-danger', default => 'badge-warning'
                    };
                ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><strong><?php echo htmlspecialchars($sr['invoice_no']); ?></strong></td>
                    <td><?php echo date('d M Y', strtotime($sr['bill_date'])); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($sr['customer_name']); ?></strong>
                        <?php if ($sr['shop_name']): ?><br><small style="color:#888;"><?php echo htmlspecialchars($sr['shop_name']); ?></small><?php endif; ?>
                    </td>
                    <td class="products-cell">
                        <?php foreach ($items as $it): ?>
                            <div class="prod-line">
                                <span class="prod-name"><?php echo htmlspecialchars($it['product_name']); ?></span>
                                <span class="prod-qty"><?php echo number_format($it['quantity'],0); ?> pcs × <?php echo number_format($it['weight'],3); ?> kg = <strong><?php echo number_format($it['quantity']*$it['weight'],3); ?> KG</strong></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?><span style="color:#aaa;font-size:11px;">—</span><?php endif; ?>
                    </td>
                    <td class="num-cell">₹<?php echo number_format($sr['subtotal'],2); ?></td>
                    <td class="num-cell"><?php echo ($sr['cgst']+$sr['sgst']) > 0 ? '₹'.number_format($sr['cgst']+$sr['sgst'],2) : '—'; ?></td>
                    <td class="num-cell"><?php echo $sr['transport_cost'] > 0 ? '₹'.number_format($sr['transport_cost'],2) : '—'; ?></td>
                    <td class="num-cell amt-cell"><strong>₹<?php echo number_format($sr['grand_total'],2); ?></strong></td>
                    <td class="num-cell" style="color:#2e7d32;">₹<?php echo number_format($sr['amount_paid'],2); ?></td>
                    <td class="num-cell" style="color:<?php echo $sr['amount_due']>0?'#c62828':'#2e7d32'; ?>;">₹<?php echo number_format($sr['amount_due'],2); ?></td>
                    <td><span class="badge <?php echo $badge_cls; ?>"><?php echo $sr['payment_status']; ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sales_rows)): ?>
                    <tr><td colspan="12" class="empty-row">No sales found for selected filters.</td></tr>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($sales_rows)): ?>
                <tfoot>
                    <tr class="tfoot-row">
                        <td colspan="5"><strong>TOTAL (<?php echo count($sales_rows); ?> bills)</strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_subtotal'],2); ?></strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_gst'],2); ?></strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_transport'],2); ?></strong></td>
                        <td class="num-cell amt-cell"><strong>₹<?php echo number_format($grand['total_sales'],2); ?></strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_collected'],2); ?></strong></td>
                        <td class="num-cell"><strong>₹<?php echo number_format($grand['total_due'],2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <p class="report-footer">Report generated on <?php echo date('d M Y, h:i A'); ?> | MKN Bro</p>
</div>

<style>
.report-page { max-width: 1600px; margin: 0 auto; }

.page-header {
    display: flex; justify-content: space-between; align-items: center;
    background: linear-gradient(135deg, #0f2b3d 0%, #1e4663 100%);
    border-radius: 20px; padding: 22px 28px; margin-bottom: 24px;
    color: white; flex-wrap: wrap; gap: 14px;
}
.page-header-left { display: flex; align-items: center; gap: 14px; }
.page-icon { font-size: 36px; }
.page-header h2 { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
.page-header p  { font-size: 13px; opacity: .85; margin: 0; }
.page-header-right { display: flex; gap: 10px; flex-wrap: wrap; }

.filter-card {
    background: #fff; border-radius: 16px; padding: 20px 24px;
    margin-bottom: 24px; border: 1px solid #eef2f8;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.filter-row { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 5px; min-width: 150px; flex: 1; }
.filter-group label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; }
.filter-actions { display: flex; gap: 8px; }

.stats-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 16px; margin-bottom: 24px; }
.stat-card {
    border-radius: 18px; padding: 18px 20px; display: flex; align-items: center;
    gap: 14px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.04); border: 1px solid #eef2f8;
}
.stat-icon-wrap { font-size: 26px; width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; }
.stat-blue   .stat-icon-wrap { background: linear-gradient(135deg,#1e4663,#2c6288); }
.stat-indigo .stat-icon-wrap { background: linear-gradient(135deg,#3949ab,#5c6bc0); }
.stat-green  .stat-icon-wrap { background: linear-gradient(135deg,#2e7d32,#43a047); }
.stat-orange .stat-icon-wrap { background: linear-gradient(135deg,#f57c00,#ff9800); }
.stat-teal   .stat-icon-wrap { background: linear-gradient(135deg,#00695c,#00897b); }
.stat-body { flex: 1; }
.stat-lbl { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; display: block; }
.stat-val { font-size: 20px; font-weight: 800; color: #1e293b; display: block; line-height: 1.2; }
.stat-sub { font-size: 11px; color: #94a3b8; display: block; margin-top: 2px; }

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

.table-wrap { overflow-x: auto; }
.rep-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.rep-table th {
    background: #f8fafc; padding: 11px 14px; text-align: left;
    font-weight: 700; color: #1e4663; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.rep-table td { padding: 11px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.rep-table tr:hover td { background: #fafcff; }
.num-cell { text-align: right; font-weight: 600; }
.amt-cell { color: #1e4663; }
.kg-cell  { color: #2e7d32; }
.tfoot-row td { background: #f0f4f8; border-top: 2px solid #e2e8f0; }
.empty-row { text-align: center; padding: 30px !important; color: #94a3b8; }

.prog-wrap { display: flex; align-items: center; gap: 6px; }
.prog-bar  { background: #e2e8f0; border-radius: 8px; height: 8px; width: 70px; overflow: hidden; }
.prog-fill { height: 100%; border-radius: 8px; }
.prog-pct  { font-size: 11px; color: #64748b; white-space: nowrap; }

.badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-success { background: #e8f5e9; color: #2e7d32; }
.badge-warning  { background: #fff3e0; color: #f57c00; }
.badge-danger   { background: #ffebee; color: #c62828; }

.products-cell { min-width: 180px; }
.prod-line { margin-bottom: 3px; padding-bottom: 3px; border-bottom: 1px dashed #eef2f8; }
.prod-line:last-child { border-bottom: none; margin-bottom: 0; }
.prod-name { font-size: 12px; font-weight: 600; color: #1e4663; display: block; }
.prod-qty  { font-size: 11px; color: #64748b; }

.btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px;
    border-radius: 10px; font-size: 12px; font-weight: 500; cursor: pointer;
    transition: all .2s; text-decoration: none; border: none; }
.btn-sm { padding: 6px 12px; font-size: 12px; }
.btn-primary  { background: #1e4663; color: #fff; }
.btn-primary:hover { background: #16364f; }
.btn-primary-light { background: rgba(255,255,255,.2); color: #fff; border: 1px solid rgba(255,255,255,.4); }
.btn-primary-light:hover { background: rgba(255,255,255,.35); }
.btn-secondary { background: #f1f5f9; color: #1e4663; border: 1px solid #e2e8f0; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-outline { background: transparent; color: #1e4663; border: 1px solid #cbd5e1; }
.btn-outline:hover { background: #f8fafc; }
.btn-outline-white { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.4); }
.btn-outline-white:hover { background: rgba(255,255,255,.3); }

.form-input, .form-select { padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 13px; width: 100%; }
.form-input:focus, .form-select:focus { outline: none; border-color: #1e4663; }

.report-footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 8px; padding-bottom: 20px; }

@media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 700px)  { .stats-grid { grid-template-columns: 1fr 1fr; } .page-header { flex-direction: column; } }

@media print {
    .no-print { display: none !important; }
    #sidebar, .toggle-btn { display: none !important; }
    #main-content { margin-left: 0 !important; }
    .report-card { box-shadow: none; border: 1px solid #ccc; page-break-inside: avoid; }
    .page-header { background: #0f2b3d !important; -webkit-print-color-adjust: exact; }
    .rep-table th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var labels    = <?php echo json_encode(array_column($monthly, 'label')); ?>;
    var billed    = <?php echo json_encode(array_column($monthly, 'total')); ?>;
    var collected = <?php echo json_encode(array_column($monthly, 'collected')); ?>;
    var billCounts= <?php echo json_encode(array_column($monthly, 'bills')); ?>;

    var ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Billed (₹)',
                    data: billed,
                    borderColor: '#1e4663',
                    backgroundColor: 'rgba(30,70,99,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#1e4663',
                    pointRadius: 4,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Collected (₹)',
                    data: collected,
                    borderColor: '#2e7d32',
                    backgroundColor: 'rgba(46,125,50,0.07)',
                    borderWidth: 2,
                    pointBackgroundColor: '#2e7d32',
                    pointRadius: 4,
                    tension: 0.35,
                    fill: false,
                    yAxisID: 'y'
                },
                {
                    label: 'No. of Bills',
                    data: billCounts,
                    borderColor: '#f57c00',
                    backgroundColor: 'rgba(245,124,0,0.06)',
                    borderWidth: 2,
                    pointBackgroundColor: '#f57c00',
                    pointRadius: 4,
                    tension: 0.35,
                    fill: false,
                    yAxisID: 'y2'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.datasetIndex === 2) return 'Bills: ' + ctx.raw;
                            return ctx.dataset.label + ': ₹' + ctx.raw.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true, position: 'left',
                    ticks: { callback: v => '₹' + v.toLocaleString('en-IN'), font: { size: 10 } },
                    grid: { color: '#e2e8f0' }
                },
                y2: {
                    beginAtZero: true, position: 'right',
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { drawOnChartArea: false }
                },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
});
</script>

<?php include 'sidebar_close.php'; ?>
