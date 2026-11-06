<?php
require_once 'config.php';
requireLogin();
$page_title = 'View Sale';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header('Location: sales_dashboard.php');
    exit();
}

// Fetch sale + customer
$r = $conn->query("SELECT s.*, c.customer_name, c.shop_name, c.city, c.phone, c.gst_no, c.address, c.state
                   FROM sales s 
                   JOIN customers c ON s.customer_id = c.id 
                   WHERE s.id = $id");
if (!$r || $r->num_rows === 0) {
    header('Location: sales_dashboard.php');
    exit();
}
$sale = $r->fetch_assoc();

// Fetch sale items
$items = [];
$ri = $conn->query("SELECT si.*, p.product_name, p.product_code, p.hsn_code, pc.category_name 
                    FROM sale_items si 
                    JOIN products p ON si.product_id = p.id 
                    JOIN product_categories pc ON si.category_id = pc.id
                    WHERE si.sale_id = $id
                    ORDER BY si.id ASC");
while ($row = $ri->fetch_assoc()) $items[] = $row;

include 'sidebar.php';
?>

<div class="view-sale-page">

    <!-- Page Header -->
    <div class="page-header-bar">
        <div class="page-header-left">
            <a href="sales_dashboard.php" class="back-btn">← Back to Sales</a>
            <div class="page-title-group">
                <h2>🧾 Sale Details</h2>
                <p class="page-subtitle">Invoice: <strong><?php echo htmlspecialchars($sale['invoice_no']); ?></strong></p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="sales_edit.php?id=<?php echo $id; ?>" class="btn btn-warning">✏️ Edit Sale</a>
            <a href="sales_print.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-success">🖨️ Print Invoice</a>
            <a href="sales_dashboard.php" class="btn btn-secondary">✖ Close</a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="view-info-grid">
        <div class="view-info-card">
            <div class="vi-icon">👤</div>
            <div class="vi-body">
                <span class="vi-label">Customer</span>
                <span class="vi-value"><?php echo htmlspecialchars($sale['customer_name']); ?></span>
                <span class="vi-sub"><?php echo htmlspecialchars($sale['shop_name']); ?></span>
            </div>
        </div>
        <div class="view-info-card">
            <div class="vi-icon">📍</div>
            <div class="vi-body">
                <span class="vi-label">Location</span>
                <span class="vi-value"><?php echo htmlspecialchars($sale['city']); ?></span>
                <span class="vi-sub">📞 <?php echo htmlspecialchars($sale['phone']); ?></span>
            </div>
        </div>
        <div class="view-info-card">
            <div class="vi-icon">📅</div>
            <div class="vi-body">
                <span class="vi-label">Bill Date</span>
                <span class="vi-value"><?php echo date('d M Y', strtotime($sale['bill_date'])); ?></span>
                <span class="vi-sub">🚚 <?php echo htmlspecialchars($sale['transport_mode']); ?></span>
            </div>
        </div>
        <div class="view-info-card">
            <div class="vi-icon">🏷️</div>
            <div class="vi-body">
                <span class="vi-label">Invoice No</span>
                <span class="vi-value"><?php echo htmlspecialchars($sale['invoice_no']); ?></span>
                <span class="vi-sub">GST: <?php echo htmlspecialchars($sale['gst_no']) ?: 'N/A'; ?></span>
            </div>
        </div>
        <div class="view-info-card">
            <div class="vi-icon">💳</div>
            <div class="vi-body">
                <span class="vi-label">Payment Status</span>
                <span class="vi-value">
                    <span class="badge <?php echo ['Paid'=>'badge-success','Not Paid'=>'badge-danger','Partial Paid'=>'badge-warning'][$sale['payment_status']] ?? 'badge-info'; ?>">
                        <?php echo $sale['payment_status']; ?>
                    </span>
                </span>
                <span class="vi-sub">Paid: ₹<?php echo number_format($sale['amount_paid'],2); ?> | Due: ₹<?php echo number_format($sale['amount_due'],2); ?></span>
            </div>
        </div>
        <div class="view-info-card">
            <div class="vi-icon">💰</div>
            <div class="vi-body">
                <span class="vi-label">Grand Total</span>
                <span class="vi-value grand-total-text">₹<?php echo number_format($sale['grand_total'],2); ?></span>
                <span class="vi-sub">GST <?php echo $sale['apply_gst'] ? 'Applied' : 'Not Applied'; ?></span>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card premium-card" style="margin-top:24px;">
        <div class="card-header">
            <div class="card-header-icon">🛒</div>
            <div>
                <h3>Products in this Bill</h3>
                <p class="card-subtitle"><?php echo count($items); ?> item(s)</p>
            </div>
        </div>
        <div class="table-container" style="padding:0 20px 20px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>HSN</th>
                        <th>Weight</th>
                        <th>MRP</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Disc%</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td data-label="#"><?php echo $i + 1; ?></td>
                        <td data-label="Product">
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                            <small style="color:#888;"><?php echo htmlspecialchars($item['product_code']); ?></small>
                        </td>
                        <td data-label="Category"><?php echo htmlspecialchars($item['category_name']); ?></td>
                        <td data-label="HSN"><?php echo htmlspecialchars($item['hsn_code']) ?: '—'; ?></td>
                        <td data-label="Weight"><?php echo $item['weight'] ? $item['weight'].' kg' : '—'; ?></td>
                        <td data-label="MRP"><?php echo $item['mrp'] ? '₹'.number_format($item['mrp'],2) : '—'; ?></td>
                        <td data-label="Qty"><?php echo number_format($item['quantity'],3); ?></td>
                        <td data-label="Rate">₹<?php echo number_format($item['rate'],2); ?></td>
                        <td data-label="Disc%"><?php echo $item['discount_pct']; ?>%</td>
                        <td data-label="Line Total" class="price-cell"><strong>₹<?php echo number_format($item['line_total'],2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals Summary -->
        <div class="view-totals-wrap">
            <div class="view-totals-card">
                <div class="vt-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($sale['subtotal'],2); ?></span>
                </div>
                <?php if ($sale['apply_gst']): ?>
                <div class="vt-row">
                    <span>CGST (2.5%)</span>
                    <span>₹<?php echo number_format($sale['cgst'],2); ?></span>
                </div>
                <div class="vt-row">
                    <span>SGST (2.5%)</span>
                    <span>₹<?php echo number_format($sale['sgst'],2); ?></span>
                </div>
                <?php endif; ?>
                <div class="vt-row">
                    <span>Transport Cost</span>
                    <span>₹<?php echo number_format($sale['transport_cost'],2); ?></span>
                </div>
                <div class="vt-row vt-grand">
                    <span>Grand Total</span>
                    <span>₹<?php echo number_format($sale['grand_total'],2); ?></span>
                </div>
                <div class="vt-row">
                    <span>Amount Paid</span>
                    <span class="vt-paid">₹<?php echo number_format($sale['amount_paid'],2); ?></span>
                </div>
                <div class="vt-row">
                    <span>Amount Due</span>
                    <span class="vt-due">₹<?php echo number_format($sale['amount_due'],2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="view-footer-actions">
        <a href="sales_edit.php?id=<?php echo $id; ?>" class="btn btn-warning">✏️ Edit Sale</a>
        <a href="sales_print.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-success">🖨️ Print Invoice</a>
        <a href="sales_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

</div>

<style>
/* ===== VIEW SALE PAGE ===== */
.view-sale-page { padding: 24px; max-width: 1200px; }

/* Page Header */
.page-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
}
.page-header-left { display: flex; align-items: center; gap: 16px; }
.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    color: #1e4663; font-weight: 600; text-decoration: none;
    font-size: 14px; padding: 6px 14px; border-radius: 8px;
    border: 1px solid #d0dbe7; background: #f4f8fb;
    transition: background 0.2s;
}
.back-btn:hover { background: #e2ecf5; }
.page-title-group h2 { margin: 0; font-size: 22px; color: #1e4663; }
.page-subtitle { margin: 2px 0 0; font-size: 13px; color: #64748b; }
.page-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

/* Info Grid */
.view-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 4px;
}
.view-info-card {
    background: #fff;
    border: 1px solid #e5edf5;
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(30,70,99,0.06);
}
.vi-icon {
    font-size: 26px;
    width: 46px; height: 46px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #e8f1f8, #d0e4f5);
    border-radius: 10px;
    flex-shrink: 0;
}
.vi-body { display: flex; flex-direction: column; gap: 2px; }
.vi-label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.vi-value { font-size: 14px; font-weight: 700; color: #1e4663; }
.vi-sub { font-size: 12px; color: #64748b; }
.grand-total-text { font-size: 18px; color: #1e7e34 !important; }

/* Totals */
.view-totals-wrap { display: flex; justify-content: flex-end; padding: 0 20px 24px; }
.view-totals-card {
    width: 320px;
    background: #f8fafc;
    border: 1px solid #e5edf5;
    border-radius: 12px;
    overflow: hidden;
}
.vt-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 18px;
    border-bottom: 1px solid #eef2f8;
    font-size: 14px; color: #475569;
}
.vt-row:last-child { border-bottom: none; }
.vt-grand {
    background: #1e4663; color: #fff !important;
    font-weight: 700; font-size: 15px;
}
.vt-grand span { color: #fff !important; }
.vt-paid { color: #1e7e34; font-weight: 700; }
.vt-due { color: #c0392b; font-weight: 700; }

/* Footer */
.view-footer-actions {
    display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;
}

/* Buttons */
.btn { 
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 8px; font-size: 13px;
    font-weight: 600; text-decoration: none; border: none; cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
}
.btn:hover { opacity: 0.88; transform: translateY(-1px); }
.btn-warning  { background: #f57c00; color: #fff; }
.btn-success  { background: #1e7e34; color: #fff; }
.btn-secondary{ background: #64748b; color: #fff; }

/* Badges */
.badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-success { background:#d4edda; color:#1e7e34; }
.badge-danger  { background:#f8d7da; color:#c0392b; }
.badge-warning { background:#fff3cd; color:#856404; }
.badge-info    { background:#d1ecf1; color:#0c5460; }

/* Price cell */
.price-cell { color: #1e4663; font-weight: 700; }

/* Table */
.data-table { width:100%; border-collapse:collapse; }
.data-table thead th {
    background: #1e4663; color: #fff;
    padding: 10px 14px; font-size: 12px; font-weight: 600; text-align: left;
}
.data-table tbody tr:nth-child(even) { background: #f8fafc; }
.data-table tbody td { padding: 10px 14px; font-size: 13px; color: #374151; border-bottom: 1px solid #eef2f8; }

@media (max-width: 768px) {
    .view-sale-page { padding: 12px; }
    .page-header-bar { flex-direction: column; align-items: flex-start; }
    .view-info-grid { grid-template-columns: 1fr 1fr; }
    .view-totals-card { width: 100%; }
    .view-totals-wrap { padding: 0 12px 16px; }
    .data-table thead { display: none; }
    .data-table tbody tr { display: block; margin-bottom: 12px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .data-table tbody td { display: flex; justify-content: space-between; padding: 8px 12px; border-bottom: 1px solid #f0f4f8; }
    .data-table tbody td::before { content: attr(data-label); font-weight: 600; color: #1e4663; font-size: 11px; }
}
@media (max-width: 480px) {
    .view-info-grid { grid-template-columns: 1fr; }
}
</style>

<?php include 'sidebar_close.php'; ?>