<?php
require_once 'config.php';
requireLogin();
$page_title = 'Stock Dashboard';

// Handle delete with safety check
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if stock has been used in sales
    $check_sales = $conn->query("SELECT id FROM sale_items WHERE stock_id = $id LIMIT 1");
    if ($check_sales && $check_sales->num_rows > 0) {
        $_SESSION['stock_msg'] = '<div class="alert alert-error">❌ Cannot delete stock batch. It has been used in sales.</div>';
    } else {
        $conn->query("DELETE FROM stock WHERE id=$id");
        $_SESSION['stock_msg'] = '<div class="alert alert-success">✅ Stock batch deleted successfully.</div>';
    }
    echo '<script>location.href="stock_dashboard.php";</script>';
    exit;
}

// Get filter parameters
$search_term = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$filter_status = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause for stock batches
$where_clauses = [];
if ($search_term) {
    $where_clauses[] = "(pc.category_name LIKE '%$search_term%')";
}
if ($filter_category) {
    $where_clauses[] = "s.category_id = $filter_category";
}
if ($filter_status === 'in_stock') {
    $where_clauses[] = "s.remaining_stock > 0";
} elseif ($filter_status === 'low_stock') {
    $where_clauses[] = "s.remaining_stock > 0 AND s.remaining_stock < 10";
} elseif ($filter_status === 'out_of_stock') {
    $where_clauses[] = "s.remaining_stock = 0";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM stock s 
                              JOIN product_categories pc ON s.category_id = pc.id 
                              $where_sql");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch stock batches with pagination
$res = $conn->query("SELECT s.*, pc.category_name, pc.price_per 
                     FROM stock s 
                     JOIN product_categories pc ON s.category_id = pc.id 
                     $where_sql 
                     ORDER BY s.batch_date DESC, s.id DESC 
                     LIMIT $limit OFFSET $offset");

// Summary by category
$summary = $conn->query("SELECT pc.id, pc.category_name, pc.price_per, 
    SUM(s.new_stock) as total_added,
    SUM(s.remaining_stock) as total_remaining,
    SUM(s.total_cost) as total_value,
    SUM((s.new_stock - s.remaining_stock)) as sold_qty
    FROM stock s 
    JOIN product_categories pc ON s.category_id = pc.id
    GROUP BY s.category_id ORDER BY pc.category_name");

// Get categories for filter
$cats = $conn->query("SELECT id, category_name FROM product_categories ORDER BY category_name");

include 'sidebar.php';

// Display message from session
if (isset($_SESSION['stock_msg'])) {
    echo $_SESSION['stock_msg'];
    unset($_SESSION['stock_msg']);
}

// Calculate grand totals
$grand_rem = 0;
$grand_val = 0;
$sum_rows = [];
while ($r = $summary->fetch_assoc()) { 
    $sum_rows[] = $r; 
    $grand_rem += $r['total_remaining']; 
    $grand_val += $r['total_value']; 
}

// Calculate low stock count (remaining < 10)
$low_stock_count = 0;
foreach ($sum_rows as $r) {
    if ($r['total_remaining'] < 10 && $r['total_remaining'] > 0) $low_stock_count++;
}

// Get total batches count
$total_batches = $conn->query("SELECT COUNT(*) as total FROM stock")->fetch_assoc()['total'];
?>

<!-- Main Content -->
<div class="stock-dashboard">
    <!-- Statistics Cards Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">📦</div>
            <div class="stat-details">
                <span class="stat-label">Categories with Stock</span>
                <span class="stat-value"><?php echo count($sum_rows); ?></span>
                <span class="stat-sub">Active categories</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-label">Total Stock Value</span>
                <span class="stat-value">₹<?php echo number_format($grand_val, 2); ?></span>
                <span class="stat-sub">Inventory worth</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-icon">⚖️</div>
            <div class="stat-details">
                <span class="stat-label">Total Remaining</span>
                <span class="stat-value"><?php echo number_format($grand_rem, 2); ?></span>
                <span class="stat-sub">Units in stock</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">⚠️</div>
            <div class="stat-details">
                <span class="stat-label">Low Stock Alerts</span>
                <span class="stat-value"><?php echo $low_stock_count; ?></span>
                <span class="stat-sub">Need restocking</span>
            </div>
        </div>
    </div>

    <!-- Stock Summary by Category Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">📊</div>
            <div>
                <h3>Stock Summary by Category</h3>
                <p class="card-subtitle">Current inventory levels and values</p>
            </div>
            <div class="card-header-badge">
                <span class="total-badge"><?php echo count($sum_rows); ?> Categories</span>
            </div>
        </div>
        <div class="table-container">
            <?php if (!empty($sum_rows)): ?>
            <table class="data-table summary-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Total Added</th>
                        <th>Total Sold</th>
                        <th>Remaining</th>
                        <th>Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sum_rows as $r): 
                        $pct = $r['total_added'] > 0 ? ($r['total_remaining'] / $r['total_added'] * 100) : 0;
                        $status_cls = $pct <= 10 ? 'badge-danger' : ($pct <= 30 ? 'badge-warning' : 'badge-success');
                        $status_lbl = $pct <= 10 ? 'Low Stock' : ($pct <= 30 ? 'Medium' : 'Adequate');
                    ?>
                        <tr>
                            <td data-label="Category">
                                <strong class="category-name"><?php echo htmlspecialchars($r['category_name']); ?></strong>
                             </td>
                            <td data-label="Unit"><span class="unit-badge"><?php echo $r['price_per']; ?></span></td>
                            <td data-label="Total Added"><?php echo number_format($r['total_added'], 2); ?></td>
                            <td data-label="Total Sold"><?php echo number_format($r['sold_qty'], 2); ?></td>
                            <td data-label="Remaining">
                                <strong class="remaining-qty"><?php echo number_format($r['total_remaining'], 2); ?></strong>
                             </td>
                            <td data-label="Stock Value">₹<?php echo number_format($r['total_value'], 2); ?></td>
                            <td data-label="Status"><span class="badge <?php echo $status_cls; ?>"><?php echo $status_lbl; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No stock data available</p>
                <a href="stock_add.php" class="btn btn-primary btn-sm">Add Your First Stock</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock Batches Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">📋</div>
            <div>
                <h3>All Stock Batches</h3>
                <p class="card-subtitle">Track individual stock entries</p>
            </div>
            <div class="card-header-badge">
                <span class="total-badge"><?php echo $total_batches; ?> Batches</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form" id="filterForm">
                <div class="filter-group">
                    <div class="filter-icon">🔍</div>
                    <input type="text" name="search" placeholder="Search by category..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="category" class="filter-select">
                        <option value="0">All Categories</option>
                        <?php 
                        $cats->data_seek(0);
                        while ($c = $cats->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_category == $c['id'] ? 'selected' : ''; ?>>
                                🏷️ <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="in_stock" <?php echo $filter_status === 'in_stock' ? 'selected' : ''; ?>>✅ In Stock</option>
                        <option value="low_stock" <?php echo $filter_status === 'low_stock' ? 'selected' : ''; ?>>⚠️ Low Stock (&lt;10)</option>
                        <option value="out_of_stock" <?php echo $filter_status === 'out_of_stock' ? 'selected' : ''; ?>>❌ Out of Stock</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span>🔍</span> Apply
                    </button>
                    <a href="stock_dashboard.php" class="btn btn-outline btn-sm">
                        <span>🔄</span> Clear
                    </a>
                    <a href="stock_add.php" class="btn btn-success btn-sm">
                        <span>📥</span> Add Stock
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <?php if ($res && $res->num_rows > 0): ?>
            <table class="data-table" id="stockTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Batch Date</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Added</th>
                        <th>Remaining</th>
                        <th>Cost/Unit</th>
                        <th>Total Cost</th>
                        <th>Sold</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()):
                        $sold = $row['new_stock'] - $row['remaining_stock'];
                        $remaining_class = $row['remaining_stock'] > 0 ? ($row['remaining_stock'] < 10 ? 'low-stock' : 'in-stock') : 'out-of-stock';
                        $remaining_text = $row['remaining_stock'] > 0 ? ($row['remaining_stock'] < 10 ? 'Low Stock' : 'In Stock') : 'Out of Stock';
                    ?>
                        <tr>
                            <td data-label="ID"><?php echo $row['id']; ?></td>
                            <td data-label="Batch Date">
                                <span class="batch-date"><?php echo date('d M Y', strtotime($row['batch_date'])); ?></span>
                             </td>
                            <td data-label="Category"><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td data-label="Unit"><span class="unit-badge"><?php echo $row['stock_per']; ?></span></td>
                            <td data-label="Added"><?php echo number_format($row['new_stock'], 2); ?></td>
                            <td data-label="Remaining">
                                <span class="remaining-badge <?php echo $remaining_class; ?>" title="<?php echo $remaining_text; ?>">
                                    <?php echo number_format($row['remaining_stock'], 2); ?>
                                </span>
                             </td>
                            <td data-label="Cost/Unit">₹<?php echo number_format($row['cost_per_unit'], 2); ?></td>
                            <td data-label="Total Cost">₹<?php echo number_format($row['total_cost'], 2); ?></td>
                            <td data-label="Sold"><?php echo number_format($sold, 2); ?></td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="?delete=<?php echo $row['id']; ?>&<?php echo http_build_query(array_filter(['search' => $search_term, 'category' => $filter_category, 'status' => $filter_status, 'page' => $page])) ?>" 
                                   class="action-btn delete-btn" 
                                   title="Delete Batch" 
                                   onclick="return confirm('Delete this stock batch? This action cannot be undone.')">
                                    🗑️
                                </a>
                             </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No stock batches found</p>
                <a href="stock_add.php" class="btn btn-primary btn-sm">Add Your First Stock Batch</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <strong><?php echo $offset + 1; ?></strong> to <strong><?php echo min($offset + $limit, $total_records); ?></strong> of <strong><?php echo $total_records; ?></strong> batches
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?>" class="page-link prev">
                        ← Previous
                    </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?>" class="page-link next">
                        Next →
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions Card -->
    <div class="quick-actions-card">
        <div class="quick-actions-header">
            <span class="quick-actions-icon">⚡</span>
            <span class="quick-actions-title">Quick Actions</span>
        </div>
        <div class="action-buttons">
            <a href="stock_add.php" class="quick-action-btn">
                <span>📥</span> Add New Stock
            </a>
            <a href="sales_add.php" class="quick-action-btn">
                <span>🧾</span> Create Sale
            </a>
            <a href="product_dashboard.php" class="quick-action-btn">
                <span>📦</span> Manage Products
            </a>
            <a href="stock_report.php" class="quick-action-btn">
                <span>📊</span> Stock Report
            </a>
        </div>
    </div>
</div>

<style>
/* ========== STOCK DASHBOARD ENHANCED STYLES ========== */

.stock-dashboard {
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
.stat-card-info .stat-icon { background: linear-gradient(135deg, #0288d1 0%, #03a9f4 100%); }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); }

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

/* Filter Section Enhanced */
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
    min-width: 180px;
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
    font-family: 'Inter', sans-serif;
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

/* Summary Table Specific */
.summary-table .category-name {
    color: #1e4663;
    font-weight: 600;
}

.remaining-qty {
    color: #2e7d32;
    font-weight: 600;
}

.unit-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #f1f5f9;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: #1e4663;
}

.batch-date {
    font-family: monospace;
    font-size: 12px;
    background: #f1f5f9;
    padding: 4px 8px;
    border-radius: 8px;
    display: inline-block;
}

/* Remaining Stock Badges */
.remaining-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.in-stock {
    background: #e8f5e9;
    color: #2e7d32;
}

.low-stock {
    background: #fff3e0;
    color: #f57c00;
}

.out-of-stock {
    background: #ffebee;
    color: #c62828;
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
    transition: all 0.2s;
}

.delete-btn {
    background: #ffebee;
    color: #c62828;
}

.delete-btn:hover {
    background: #c62828;
    color: white;
    transform: scale(1.05);
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

.page-link.prev,
.page-link.next {
    padding: 8px 16px;
}

/* Quick Actions Card */
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

.quick-action-btn span {
    font-size: 16px;
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

/* ========== RESPONSIVE DESIGN ========== */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
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
</script>

<?php include 'sidebar_close.php'; ?>