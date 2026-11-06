<?php
require_once 'config.php';
requireLogin();
$page_title = 'Sales Dashboard';
$msg = '';

// Delete sale
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM sale_items WHERE sale_id=$id");
    $conn->query("DELETE FROM sales WHERE id=$id");
    $msg = '<div class="alert alert-success">✅ Sale deleted successfully.</div>';
}

// Inline payment status update (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $id      = intval($_POST['sale_id']);
    $status  = sanitize($conn, $_POST['payment_status']);
    $paid    = floatval($_POST['amount_paid']);
    $r       = $conn->query("SELECT grand_total FROM sales WHERE id=$id");
    $grand   = $r ? $r->fetch_assoc()['grand_total'] : 0;
    $due     = ($status === 'Paid') ? 0 : ($status === 'Not Paid' ? $grand : $grand - $paid);
    $ap      = ($status === 'Paid') ? $grand : $paid;
    $conn->query("UPDATE sales SET payment_status='$status',amount_paid=$ap,amount_due=$due WHERE id=$id");
    echo json_encode(['success'=>true, 'due'=>number_format($due,2), 'paid'=>number_format($ap,2)]);
    exit();
}

// Get filter parameters
$search_term = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_status = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';
$filter_date_from = isset($_GET['date_from']) ? sanitize($conn, $_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? sanitize($conn, $_GET['date_to']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where_clauses = [];
if ($search_term) {
    $where_clauses[] = "(s.invoice_no LIKE '%$search_term%' OR c.customer_name LIKE '%$search_term%' OR c.shop_name LIKE '%$search_term%')";
}
if ($filter_status) {
    $where_clauses[] = "s.payment_status = '$filter_status'";
}
if ($filter_date_from) {
    $where_clauses[] = "s.bill_date >= '$filter_date_from'";
}
if ($filter_date_to) {
    $where_clauses[] = "s.bill_date <= '$filter_date_to'";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM sales s 
                              JOIN customers c ON s.customer_id = c.id 
                              $where_sql");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch sales with pagination
$res = $conn->query("SELECT s.*, c.customer_name, c.shop_name, c.phone, c.city 
                     FROM sales s 
                     JOIN customers c ON s.customer_id = c.id 
                     $where_sql 
                     ORDER BY s.bill_date DESC, s.id DESC 
                     LIMIT $limit OFFSET $offset");

// Calculate totals for filtered data
$totals_query = $conn->query("SELECT 
    COALESCE(SUM(s.grand_total), 0) as total_sales,
    COALESCE(SUM(s.amount_due), 0) as total_due,
    COUNT(*) as sale_count
    FROM sales s 
    JOIN customers c ON s.customer_id = c.id 
    $where_sql");
$totals = $totals_query->fetch_assoc();

// View sale modal data
$view_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
$view_data = null; 
$view_items = [];
if ($view_id) {
    $r = $conn->query("SELECT s.*, c.customer_name, c.shop_name, c.city, c.phone, c.gst_no, c.address, c.state
                       FROM sales s 
                       JOIN customers c ON s.customer_id = c.id 
                       WHERE s.id = $view_id");
    if ($r) $view_data = $r->fetch_assoc();
    $ri = $conn->query("SELECT si.*, p.product_name, p.product_code, p.hsn_code, pc.category_name 
                        FROM sale_items si 
                        JOIN products p ON si.product_id = p.id 
                        JOIN product_categories pc ON si.category_id = pc.id
                        WHERE si.sale_id = $view_id");
    while ($row = $ri->fetch_assoc()) $view_items[] = $row;
}

include 'sidebar.php';
?>

<!-- Main Content -->
<div class="sales-dashboard">
    <?php echo $msg; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-label">Total Sales</span>
                <span class="stat-value">₹<?php echo number_format($totals['total_sales'], 2); ?></span>
                <span class="stat-sub"><?php echo $totals['sale_count']; ?> transactions</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-icon">✅</div>
            <div class="stat-details">
                <span class="stat-label">Amount Collected</span>
                <span class="stat-value">₹<?php echo number_format($totals['total_sales'] - $totals['total_due'], 2); ?></span>
                <span class="stat-sub">Total received</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">⚠️</div>
            <div class="stat-details">
                <span class="stat-label">Amount Due</span>
                <span class="stat-value">₹<?php echo number_format($totals['total_due'], 2); ?></span>
                <span class="stat-sub">Pending collection</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-icon">📊</div>
            <div class="stat-details">
                <span class="stat-label">Collection Rate</span>
                <span class="stat-value"><?php echo $totals['total_sales'] > 0 ? round((($totals['total_sales'] - $totals['total_due']) / $totals['total_sales']) * 100, 1) : 0; ?>%</span>
                <span class="stat-sub">Payment efficiency</span>
            </div>
        </div>
    </div>

    <!-- View Sale Modal -->
    <?php if ($view_data && $view_items): ?>
    <div class="modal-overlay" id="viewModal">
        <div class="modal-container modal-large">
            <div class="modal-header">
                <div class="modal-icon">🧾</div>
                <div>
                    <h3>Sale Details</h3>
                    <p class="modal-subtitle">Invoice: <?php echo htmlspecialchars($view_data['invoice_no']); ?></p>
                </div>
                <a href="sales_dashboard.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <!-- Customer Info -->
                <div class="info-section">
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-icon">👤</div>
                            <div class="info-content">
                                <span class="info-label">Customer</span>
                                <span class="info-value"><?php echo htmlspecialchars($view_data['customer_name']); ?></span>
                                <span class="info-sub"><?php echo htmlspecialchars($view_data['shop_name']); ?></span>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">📍</div>
                            <div class="info-content">
                                <span class="info-label">Location</span>
                                <span class="info-value"><?php echo htmlspecialchars($view_data['city']); ?></span>
                                <span class="info-sub"><?php echo htmlspecialchars($view_data['phone']); ?></span>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">📅</div>
                            <div class="info-content">
                                <span class="info-label">Bill Date</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($view_data['bill_date'])); ?></span>
                                <span class="info-sub"><?php echo htmlspecialchars($view_data['transport_mode']); ?></span>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">💳</div>
                            <div class="info-content">
                                <span class="info-label">Payment Status</span>
                                <span class="info-value badge <?php echo ['Paid'=>'badge-success','Not Paid'=>'badge-danger','Partial Paid'=>'badge-warning'][$view_data['payment_status']]??'badge-info'; ?>">
                                    <?php echo $view_data['payment_status']; ?>
                                </span>
                                <span class="info-sub">GST: <?php echo $view_data['gst_no'] ?: 'N/A'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="products-section">
                    <h4>🛒 Products in this Bill</h4>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr><th>#</th><th>Product</th><th>Category</th><th>HSN</th><th>Qty</th><th>Rate</th><th>Disc%</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($view_items as $i => $item): ?>
                                <tr>
                                    <td data-label="#"><?php echo $i+1; ?></td>
                                    <td data-label="Product">
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        <small><?php echo htmlspecialchars($item['product_code']); ?></small>
                                    </td>
                                    <td data-label="Category"><?php echo htmlspecialchars($item['category_name']); ?></td>
                                    <td data-label="HSN"><?php echo htmlspecialchars($item['hsn_code']) ?: '-'; ?></td>
                                    <td data-label="Qty"><?php echo $item['quantity']; ?> <?php echo $item['weight'] ? '('.$item['weight'].'kg)' : ''; ?></td>
                                    <td data-label="Rate">₹<?php echo number_format($item['rate'], 2); ?></td>
                                    <td data-label="Disc%"><?php echo $item['discount_pct']; ?>%</td>
                                    <td data-label="Total" class="price-cell">₹<?php echo number_format($item['line_total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals -->
                <div class="totals-modal">
                    <div class="totals-card">
                        <div class="totals-row">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($view_data['subtotal'], 2); ?></span>
                        </div>
                        <?php if ($view_data['apply_gst']): ?>
                        <div class="totals-row">
                            <span>CGST (2.5%)</span>
                            <span>₹<?php echo number_format($view_data['cgst'], 2); ?></span>
                        </div>
                        <div class="totals-row">
                            <span>SGST (2.5%)</span>
                            <span>₹<?php echo number_format($view_data['sgst'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="totals-row">
                            <span>Transport Cost</span>
                            <span>₹<?php echo number_format($view_data['transport_cost'], 2); ?></span>
                        </div>
                        <div class="totals-row grand">
                            <span>Grand Total</span>
                            <span>₹<?php echo number_format($view_data['grand_total'], 2); ?></span>
                        </div>
                        <div class="totals-row">
                            <span>Amount Paid</span>
                            <span class="paid">₹<?php echo number_format($view_data['amount_paid'], 2); ?></span>
                        </div>
                        <div class="totals-row">
                            <span>Amount Due</span>
                            <span class="due">₹<?php echo number_format($view_data['amount_due'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="sales_print.php?id=<?php echo $view_id; ?>" target="_blank" class="btn btn-success">🖨️ Print Invoice</a>
                <a href="sales_dashboard.php" class="btn btn-secondary">Close</a>
            </div>
        </div>
    </div>
    <style>.modal-overlay{display:flex;}</style>
    <?php endif; ?>

    <!-- Sales Table Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">💰</div>
            <div>
                <h3>All Sales</h3>
                <p class="card-subtitle">Manage and track all sales transactions</p>
            </div>
            <div class="card-header-badge">
                <span class="total-badge"><?php echo $totals['sale_count']; ?> Sales</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form" id="filterForm">
                <div class="filter-group">
                    <div class="filter-icon">🔍</div>
                    <input type="text" name="search" placeholder="Search invoice, customer, shop..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Paid" <?php echo $filter_status === 'Paid' ? 'selected' : ''; ?>>✅ Paid</option>
                        <option value="Not Paid" <?php echo $filter_status === 'Not Paid' ? 'selected' : ''; ?>>⏳ Not Paid</option>
                        <option value="Partial Paid" <?php echo $filter_status === 'Partial Paid' ? 'selected' : ''; ?>>🔸 Partial Paid</option>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="date" name="date_from" placeholder="From Date" 
                           value="<?php echo $filter_date_from; ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <input type="date" name="date_to" placeholder="To Date" 
                           value="<?php echo $filter_date_to; ?>" class="filter-input">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span>🔍</span> Apply
                    </button>
                    <a href="sales_dashboard.php" class="btn btn-outline btn-sm">
                        <span>🔄</span> Clear
                    </a>
                    <a href="sales_add.php" class="btn btn-success btn-sm">
                        <span>➕</span> New Sale
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <?php if ($res && $res->num_rows > 0): ?>
            <table class="data-table" id="saleTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Grand Total</th>
                        <th>Payment Status</th>
                        <th>Amt Paid</th>
                        <th>Amt Due</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()):
                        $badge_class = match($row['payment_status']) {
                            'Paid' => 'badge-success',
                            'Not Paid' => 'badge-danger',
                            'Partial Paid' => 'badge-warning',
                            default => 'badge-info'
                        };
                    ?>
                    <tr data-status="<?php echo $row['payment_status']; ?>">
                        <td data-label="ID"><?php echo $row['id']; ?></td>
                        <td data-label="Invoice No">
                            <strong class="invoice-no"><?php echo htmlspecialchars($row['invoice_no']); ?></strong>
                        </td>
                        <td data-label="Date">
                            <span class="date-badge"><?php echo date('d M Y', strtotime($row['bill_date'])); ?></span>
                        </td>
                        <td data-label="Customer">
                            <div class="customer-info">
                                <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                                <small><?php echo htmlspecialchars($row['shop_name']); ?></small>
                            </div>
                        </td>
                        <td data-label="Grand Total" class="price-cell">
                            <strong>₹<?php echo number_format($row['grand_total'], 2); ?></strong>
                        </td>
                        <td data-label="Payment Status">
                            <select class="pay-status-sel" data-id="<?php echo $row['id']; ?>" 
                                    data-grand="<?php echo $row['grand_total']; ?>" 
                                    style="padding:4px 8px;border-radius:8px;font-size:12px;border:1px solid #e2e8f0;">
                                <?php foreach (['Paid', 'Not Paid', 'Partial Paid'] as $ps): ?>
                                    <option <?php echo $row['payment_status'] === $ps ? 'selected' : ''; ?>>
                                        <?php echo $ps === 'Paid' ? '✅ Paid' : ($ps === 'Not Paid' ? '⏳ Not Paid' : '🔸 Partial Paid'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td data-label="Amt Paid" id="paid_<?php echo $row['id']; ?>" class="paid-cell">
                            ₹<?php echo number_format($row['amount_paid'], 2); ?>
                        </td>
                        <td data-label="Amt Due" id="due_<?php echo $row['id']; ?>" class="due-cell">
                            ₹<?php echo number_format($row['amount_due'], 2); ?>
                        </td>
                        <td data-label="Actions" class="actions-cell">
                            <a href="?view=<?php echo $row['id']; ?>" class="action-btn view-btn" title="View Details">👁️</a>
                            <a href="sales_print.php?id=<?php echo $row['id']; ?>" target="_blank" class="action-btn print-btn" title="Print Invoice">🖨️</a>
                            <a href="?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" title="Delete" 
                               onclick="return confirm('Delete this sale? This action cannot be undone.')">🗑️</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-footer">
                        <td colspan="4" class="footer-label">Totals:</td>
                        <td class="footer-total">₹<?php echo number_format($totals['total_sales'], 2); ?></td>
                        <td></td>
                        <td></td>
                        <td class="footer-due">₹<?php echo number_format($totals['total_due'], 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No sales found</p>
                <a href="sales_add.php" class="btn btn-primary btn-sm">Create Your First Sale</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <strong><?php echo $offset + 1; ?></strong> to <strong><?php echo min($offset + $limit, $total_records); ?></strong> 
                of <strong><?php echo $total_records; ?></strong> sales
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_date_from ? '&date_from=' . $filter_date_from : ''; ?><?php echo $filter_date_to ? '&date_to=' . $filter_date_to : ''; ?>" class="page-link prev">
                        ← Previous
                    </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_date_from ? '&date_from=' . $filter_date_from : ''; ?><?php echo $filter_date_to ? '&date_to=' . $filter_date_to : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_date_from ? '&date_from=' . $filter_date_from : ''; ?><?php echo $filter_date_to ? '&date_to=' . $filter_date_to : ''; ?>" class="page-link next">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-card">
        <div class="quick-actions-header">
            <span class="quick-actions-icon">⚡</span>
            <span class="quick-actions-title">Quick Actions</span>
        </div>
        <div class="action-buttons">
            <a href="sales_add.php" class="quick-action-btn">
                <span>➕</span> New Sale
            </a>
            <a href="customer_dashboard.php" class="quick-action-btn">
                <span>👥</span> Manage Customers
            </a>
            <a href="stock_dashboard.php" class="quick-action-btn">
                <span>📦</span> Check Stock
            </a>
        </div>
    </div>
</div>

<style>
/* ========== SALES DASHBOARD ENHANCED STYLES ========== */

.sales-dashboard {
    max-width: 1600px;
    margin: 0 auto;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.stat-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid #eef2f8;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
}

.stat-card-primary .stat-icon { background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%); }
.stat-card-success .stat-icon { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); }
.stat-card-info .stat-icon { background: linear-gradient(135deg, #0288d1 0%, #03a9f4 100%); }

.stat-details {
    flex: 1;
}

.stat-label {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 24px;
    font-weight: 800;
    color: #1e293b;
    display: block;
    line-height: 1.2;
}

.stat-sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
}

/* Premium Card */
.premium-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #eef2f8;
    overflow: hidden;
    padding: 0;
    margin-bottom: 24px;
}

.card-header {
    padding: 22px 28px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #eef2f8;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.card-header-icon {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: white;
}

.card-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1e4663;
    margin: 0 0 4px 0;
    padding: 0;
    border: none;
}

.card-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.card-header-badge {
    margin-left: auto;
}

.total-badge {
    background: #e8f0fe;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    color: #1e4663;
}

/* Filter Section */
.filter-section {
    padding: 20px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #eef2f8;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 160px;
    position: relative;
}

.filter-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #94a3b8;
}

.filter-input {
    width: 100%;
    padding: 10px 14px 10px 36px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s;
}

.filter-select {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 13px;
    background: white;
    cursor: pointer;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
}

.filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Table Styles */
.table-container {
    overflow-x: auto;
    padding: 0 24px 24px 24px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.data-table th {
    background: #f8fafc;
    padding: 14px 12px;
    text-align: left;
    font-weight: 700;
    color: #1e4663;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}

.data-table tr:hover td {
    background: #fafcff;
}

.invoice-no {
    color: #1e4663;
    font-family: monospace;
    font-size: 13px;
}

.date-badge {
    background: #f1f5f9;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 12px;
    display: inline-block;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-info strong {
    color: #1e4663;
}

.customer-info small {
    font-size: 11px;
    color: #64748b;
}

.price-cell strong {
    color: #1e4663;
    font-size: 14px;
}

.paid-cell {
    color: #2e7d32;
    font-weight: 600;
}

.due-cell {
    color: #c62828;
    font-weight: 600;
}

.table-footer {
    background: #f8fafc;
    font-weight: 700;
}

.table-footer td {
    padding: 12px;
    border-top: 2px solid #e2e8f0;
}

.footer-label {
    text-align: right;
    color: #1e4663;
}

.footer-total {
    color: #1e4663;
    font-weight: 800;
}

.footer-due {
    color: #c62828;
    font-weight: 800;
}

/* Action Buttons */
.actions-cell {
    white-space: nowrap;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    margin: 0 2px;
    transition: all 0.2s;
}

.view-btn {
    background: #e3f2fd;
    color: #0288d1;
}

.view-btn:hover {
    background: #0288d1;
    color: white;
}

.print-btn {
    background: #e8f5e9;
    color: #2e7d32;
}

.print-btn:hover {
    background: #2e7d32;
    color: white;
}

.delete-btn {
    background: #ffebee;
    color: #c62828;
}

.delete-btn:hover {
    background: #c62828;
    color: white;
}

/* Badge Styles */
.badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

.badge-success { background: #e8f5e9; color: #2e7d32; }
.badge-warning { background: #fff3e0; color: #f57c00; }
.badge-danger { background: #ffebee; color: #c62828; }
.badge-info { background: #e3f2fd; color: #0288d1; }

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-top: 1px solid #eef2f8;
    flex-wrap: wrap;
    gap: 16px;
    background: #f8fafc;
}

.pagination-info {
    font-size: 13px;
    color: #64748b;
}

.pagination-info strong {
    color: #1e4663;
}

.pagination {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    color: #1e4663;
    background: white;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.page-link:hover {
    background: #1e4663;
    color: white;
    border-color: #1e4663;
}

.page-link.active {
    background: #1e4663;
    color: white;
    border-color: #1e4663;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-container {
    background: white;
    border-radius: 28px;
    max-width: 900px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideIn 0.3s ease;
}

.modal-large {
    max-width: 1000px;
}

.modal-header {
    padding: 20px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #eef2f8;
    display: flex;
    align-items: center;
    gap: 14px;
    position: relative;
}

.modal-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 28px;
    color: #94a3b8;
    text-decoration: none;
}

.modal-close:hover {
    color: #c62828;
}

.modal-subtitle {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #eef2f8;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

/* Info Section in Modal */
.info-section {
    margin-bottom: 24px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.info-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 14px;
}

.info-icon {
    font-size: 20px;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    display: block;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: #1e4663;
    display: block;
}

.info-sub {
    font-size: 11px;
    color: #94a3b8;
    display: block;
}

/* Products Section in Modal */
.products-section {
    margin-bottom: 24px;
}

.products-section h4 {
    font-size: 16px;
    font-weight: 700;
    color: #1e4663;
    margin-bottom: 16px;
}

/* Totals Modal */
.totals-modal {
    display: flex;
    justify-content: flex-end;
}

.totals-card {
    background: #f8fafc;
    border-radius: 16px;
    padding: 16px 20px;
    min-width: 280px;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 13px;
}

.totals-row.grand {
    border-top: 2px solid #e2e8f0;
    margin-top: 8px;
    padding-top: 12px;
    font-size: 18px;
    font-weight: 800;
    color: #1e4663;
}

.totals-row .paid {
    color: #2e7d32;
    font-weight: 700;
}

.totals-row .due {
    color: #c62828;
    font-weight: 700;
}

/* Quick Actions */
.quick-actions-card {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 20px;
    padding: 20px 24px;
    margin-top: 24px;
    border: 1px solid #eef2f8;
}

.quick-actions-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eef2f8;
}

.quick-actions-icon {
    font-size: 20px;
}

.quick-actions-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e4663;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.quick-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    color: #1e4663;
    transition: all 0.2s;
}

.quick-action-btn:hover {
    background: #1e4663;
    border-color: #1e4663;
    color: white;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    color: #94a3b8;
    margin-bottom: 20px;
    font-size: 14px;
}

/* Alert Styles */
.alert {
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 20px;
    font-size: 13px;
    animation: slideIn 0.3s ease;
}

.alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border-left: 4px solid #2e7d32;
}

.alert-error {
    background: #ffebee;
    color: #c62828;
    border-left: 4px solid #c62828;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border: none;
}

.btn-sm {
    padding: 8px 14px;
    font-size: 12px;
}

.btn-primary {
    background: #1e4663;
    color: white;
}

.btn-primary:hover {
    background: #0f2b3d;
    transform: translateY(-1px);
}

.btn-success {
    background: #2e7d32;
    color: white;
}

.btn-success:hover {
    background: #1b5e20;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f1f5f9;
    color: #1e4663;
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.btn-outline {
    background: transparent;
    color: #64748b;
    border: 1px solid #cbd5e1;
}

.btn-outline:hover {
    background: #f8fafc;
    border-color: #1e4663;
    color: #1e4663;
}

/* ========== RESPONSIVE DESIGN ========== */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat-card {
        padding: 14px 18px;
    }
    
    .stat-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
    }
    
    .stat-value {
        font-size: 20px;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .filter-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .card-header {
        flex-direction: row;
        flex-wrap: wrap;
    }
    
    .card-header-badge {
        margin-left: 0;
    }
    
    /* Responsive Tables */
    .data-table thead {
        display: none;
    }
    
    .data-table tbody tr {
        display: block;
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: white;
        overflow: hidden;
    }
    
    .data-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .data-table tbody td:before {
        content: attr(data-label);
        font-weight: 700;
        color: #1e4663;
        width: 40%;
        font-size: 12px;
    }
    
    .data-table tbody td:last-child {
        border-bottom: none;
    }
    
    .actions-cell {
        justify-content: flex-end;
    }
    
    .table-container {
        padding: 0 16px 16px 16px;
    }
    
    .pagination-container {
        flex-direction: column;
        text-align: center;
    }
    
    .pagination {
        justify-content: center;
    }
    
    .card-header-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
    }
    
    .card-header h3 {
        font-size: 18px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .totals-card {
        width: 100%;
        min-width: auto;
    }
    
    .modal-container {
        margin: 16px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .quick-action-btn {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 12px 16px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .stat-value {
        font-size: 18px;
    }
    
    .filter-section {
        padding: 16px;
    }
    
    .page-link {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    .data-table tbody td {
        font-size: 12px;
        padding: 8px 12px;
    }
    
    .data-table tbody td:before {
        font-size: 11px;
    }
}
</style>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Auto-submit filters when dropdown changes
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Search input with debounce
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
}

// Inline payment status update
document.querySelectorAll('.pay-status-sel').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var id = this.getAttribute('data-id');
        var grand = parseFloat(this.getAttribute('data-grand'));
        var statusText = this.value;
        var status = statusText.replace('✅ ', '').replace('⏳ ', '').replace('🔸 ', '');
        var paid = 0;
        
        if (status === 'Partial Paid') {
            paid = parseFloat(prompt('Enter amount paid (Grand Total: ₹' + grand.toFixed(2) + '):', (grand/2).toFixed(2))) || 0;
            if (paid > grand) {
                alert('Amount paid cannot exceed grand total!');
                return;
            }
        } else if (status === 'Paid') {
            paid = grand;
        }
        
        var fd = new FormData();
        fd.append('update_payment', '1');
        fd.append('sale_id', id);
        fd.append('payment_status', status);
        fd.append('amount_paid', paid);
        
        fetch('sales_dashboard.php', {method: 'POST', body: fd})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('paid_' + id).textContent = '₹' + data.paid;
                    document.getElementById('due_' + id).textContent = '₹' + data.due;
                    // Update the row's data-status attribute for filtering
                    const row = this.closest('tr');
                    if (row) row.setAttribute('data-status', status);
                }
            })
            .catch(err => console.error('Error:', err));
    });
});
</script>

<?php include 'sidebar_close.php'; ?>