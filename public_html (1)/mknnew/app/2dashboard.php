<?php
require_once 'config.php';
requireLogin();
$page_title = 'Dashboard';

// --- Stats ---
// Total Sales
$r = $conn->query("SELECT COALESCE(SUM(grand_total),0) as total FROM sales");
$total_sales = $r->fetch_assoc()['total'];

// Total Expenses
$r = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM expenses");
$total_expenses = $r->fetch_assoc()['total'];

// Current Balance = Total Sales - Total Expenses
$current_balance = $total_sales - $total_expenses;

// Amount Yet to Pay (pending dues from sales)
$r = $conn->query("SELECT COALESCE(SUM(amount_due),0) as total FROM sales WHERE payment_status != 'Paid'");
$amount_due = $r->fetch_assoc()['total'];

// Current Month Sales
$r = $conn->query("SELECT COALESCE(SUM(grand_total),0) as total FROM sales WHERE MONTH(bill_date)=MONTH(NOW()) AND YEAR(bill_date)=YEAR(NOW())");
$month_sales = $r->fetch_assoc()['total'];

// Current Month Expenses
$r = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE MONTH(expense_date)=MONTH(NOW()) AND YEAR(expense_date)=YEAR(NOW())");
$month_expenses = $r->fetch_assoc()['total'];

// Monthly sales for chart (last 6 months)
$monthly_data = [];
$monthly_exp = [];
for ($i = 5; $i >= 0; $i--) {
    $r = $conn->query("SELECT COALESCE(SUM(grand_total),0) as total, DATE_FORMAT(NOW() - INTERVAL $i MONTH, '%b %Y') as label 
                       FROM sales WHERE MONTH(bill_date)=MONTH(NOW() - INTERVAL $i MONTH) AND YEAR(bill_date)=YEAR(NOW() - INTERVAL $i MONTH)");
    $row = $r->fetch_assoc();
    $monthly_data[] = ['label' => $row['label'], 'total' => floatval($row['total'])];
    
    $r2 = $conn->query("SELECT COALESCE(SUM(amount),0) as total FROM expenses 
                        WHERE MONTH(expense_date)=MONTH(NOW() - INTERVAL $i MONTH) AND YEAR(expense_date)=YEAR(NOW() - INTERVAL $i MONTH)");
    $monthly_exp[] = floatval($r2->fetch_assoc()['total']);
}

// Stock data for chart
$stock_data = [];
$rs = $conn->query("SELECT pc.category_name, SUM(s.remaining_stock) as remaining, SUM(s.new_stock) as total
                    FROM stock s JOIN product_categories pc ON s.category_id=pc.id
                    GROUP BY s.category_id ORDER BY pc.category_name LIMIT 8");
while ($row = $rs->fetch_assoc()) $stock_data[] = $row;

// Low stock products (remaining < 20% of added)


// Recent sales
$recent_sales = [];
$rrs = $conn->query("SELECT s.invoice_no, s.bill_date, s.grand_total, s.payment_status, c.customer_name 
                     FROM sales s JOIN customers c ON s.customer_id=c.id ORDER BY s.id DESC LIMIT 5");
while ($row = $rrs->fetch_assoc()) $recent_sales[] = $row;

// Recent expenses
$recent_exp = [];
$rre = $conn->query("SELECT e.reason, e.amount, e.expense_date, e.payment_status, u.full_name 
                     FROM expenses e LEFT JOIN users u ON e.spent_by=u.id ORDER BY e.id DESC LIMIT 5");
while ($row = $rre->fetch_assoc()) $recent_exp[] = $row;

// Total customers & products
$r = $conn->query("SELECT COUNT(*) as c FROM customers"); $total_customers = $r->fetch_assoc()['c'];
$r = $conn->query("SELECT COUNT(*) as c FROM products");  $total_products  = $r->fetch_assoc()['c'];

include 'sidebar.php';
?>

<!-- Main Dashboard Content -->
<div class="dashboard-container">

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</h2>
            <p>Here's what's happening with your business today.</p>
        </div>
        <div class="welcome-date">
            <span class="date-badge">📅 <?php echo date('l, d M Y'); ?></span>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-label">Current Balance</span>
                <span class="stat-value <?php echo $current_balance >= 0 ? 'positive' : 'negative'; ?>">
                    ₹<?php echo number_format(abs($current_balance), 2); ?><?php echo $current_balance < 0 ? ' (-)' : ''; ?>
                </span>
                <span class="stat-sub">Sales - Expenses</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">⏳</div>
            <div class="stat-details">
                <span class="stat-label">Pending Dues</span>
                <span class="stat-value">₹<?php echo number_format($amount_due, 2); ?></span>
                <span class="stat-sub">Amount to receive</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-info">
            <div class="stat-icon">📅</div>
            <div class="stat-details">
                <span class="stat-label">This Month Sales</span>
                <span class="stat-value">₹<?php echo number_format($month_sales, 2); ?></span>
                <span class="stat-sub">vs ₹<?php echo number_format($month_expenses, 2); ?> expenses</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-success">
            <div class="stat-icon">✅</div>
            <div class="stat-details">
                <span class="stat-label">Collection Rate</span>
                <span class="stat-value"><?php echo $total_sales > 0 ? round((($total_sales - $amount_due) / $total_sales) * 100, 1) : 0; ?>%</span>
                <span class="stat-sub">Payment efficiency</span>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="stats-grid secondary">
        <div class="stat-card stat-card-secondary">
            <div class="stat-icon">🛒</div>
            <div class="stat-details">
                <span class="stat-label">Total Sales</span>
                <span class="stat-value">₹<?php echo number_format($total_sales, 2); ?></span>
                <span class="stat-sub">All time revenue</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-secondary">
            <div class="stat-icon">💸</div>
            <div class="stat-details">
                <span class="stat-label">Total Expenses</span>
                <span class="stat-value">₹<?php echo number_format($total_expenses, 2); ?></span>
                <span class="stat-sub">All time costs</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-secondary">
            <div class="stat-icon">👥</div>
            <div class="stat-details">
                <span class="stat-label">Customers</span>
                <span class="stat-value"><?php echo number_format($total_customers); ?></span>
                <span class="stat-sub">Registered</span>
            </div>
        </div>
        
        <div class="stat-card stat-card-secondary">
            <div class="stat-icon">🧴</div>
            <div class="stat-details">
                <span class="stat-label">Products</span>
                <span class="stat-value"><?php echo number_format($total_products); ?></span>
                <span class="stat-sub">In catalog</span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="card premium-card">
            <div class="card-header">
                <div class="card-header-icon">📈</div>
                <div>
                    <h3>Sales vs Expenses</h3>
                    <p class="card-subtitle">Last 6 months trend analysis</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="card premium-card">
            <div class="card-header">
                <div class="card-header-icon">📊</div>
                <div>
                    <h3>Stock Distribution</h3>
                    <p class="card-subtitle">Remaining stock by category</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="stockChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Low Stock & Recent Activity Row -->
    <div class="activity-row">
        <!-- Low Stock Alert -->
        <div class="card premium-card">
            <div class="card-header">
                <div class="card-header-icon warning">⚠️</div>
                <div>
                    <h3>Low Stock Alert</h3>
                    <p class="card-subtitle">Categories needing attention</p>
                </div>
                <?php if (!empty($low_stock)): ?>
                <div class="alert-badge"><?php echo count($low_stock); ?> Alerts</div>
                <?php endif; ?>
            </div>
            <div class="card-content">
                <?php if (empty($low_stock)): ?>
                    <div class="empty-state small">
                        <div class="empty-icon">✅</div>
                        <p>All stock levels are adequate!</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table compact">
                            <thead>
                                <tr><th>Category</th><th>Total Added</th><th>Remaining</th><th>Stock Level</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($low_stock as $ls):
                                $pct = $ls['total_added'] > 0 ? round($ls['remaining'] / $ls['total_added'] * 100, 1) : 0;
                                $bar_color = $pct <= 10 ? '#c62828' : '#f57c00';
                            ?>
                                <tr>
                                    <td data-label="Category"><?php echo htmlspecialchars($ls['category_name']); ?></td>
                                    <td data-label="Total Added"><?php echo number_format($ls['total_added'], 2); ?></td>
                                    <td data-label="Remaining" class="stock-remaining"><?php echo number_format($ls['remaining'], 2); ?></td>
                                    <td data-label="Stock Level">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $pct; ?>%; background: <?php echo $bar_color; ?>;"></div>
                                        </div>
                                        <span class="progress-label"><?php echo $pct; ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-action">
                        <a href="stock_dashboard.php" class="btn btn-outline btn-sm">View All Stock →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="card premium-card">
            <div class="card-header">
                <div class="card-header-icon success">🧾</div>
                <div>
                    <h3>Recent Sales</h3>
                    <p class="card-subtitle">Latest transactions</p>
                </div>
            </div>
            <div class="card-content">
                <div class="table-container">
                    <table class="data-table compact">
                        <thead>
                            <tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_sales as $rs):
                            $badge_class = match($rs['payment_status']) {
                                'Paid' => 'badge-success',
                                'Not Paid' => 'badge-danger',
                                'Partial Paid' => 'badge-warning',
                                default => 'badge-info'
                            };
                        ?>
                            <tr>
                                <td data-label="Invoice">
                                    <strong><?php echo htmlspecialchars($rs['invoice_no']); ?></strong>
                                    <small><?php echo date('d M Y', strtotime($rs['bill_date'])); ?></small>
                                </td>
                                <td data-label="Customer"><?php echo htmlspecialchars($rs['customer_name']); ?></td>
                                <td data-label="Amount" class="price-cell">₹<?php echo number_format($rs['grand_total'], 2); ?></td>
                                <td data-label="Status"><span class="badge <?php echo $badge_class; ?>"><?php echo $rs['payment_status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_sales)): ?>
                            <tr><td colspan="4" class="empty-row">No sales yet</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-action">
                    <a href="sales_dashboard.php" class="btn btn-outline btn-sm">View All Sales →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="card premium-card full-width">
        <div class="card-header">
            <div class="card-header-icon danger">💸</div>
            <div>
                <h3>Recent Expenses</h3>
                <p class="card-subtitle">Latest outgoing payments</p>
            </div>
        </div>
        <div class="card-content">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr><th>Date</th><th>Reason</th><th>Spent By</th><th>Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent_exp as $re):
                        $badge_class = match($re['payment_status']) {
                            'Paid' => 'badge-success',
                            'Unpaid' => 'badge-danger',
                            'Partial Paid' => 'badge-warning',
                            default => 'badge-info'
                        };
                    ?>
                        <tr>
                            <td data-label="Date"><?php echo date('d M Y', strtotime($re['expense_date'])); ?></td>
                            <td data-label="Reason"><?php echo htmlspecialchars($re['reason']); ?></td>
                            <td data-label="Spent By"><?php echo htmlspecialchars($re['full_name']); ?></td>
                            <td data-label="Amount" class="price-cell danger">₹<?php echo number_format($re['amount'], 2); ?></td>
                            <td data-label="Status"><span class="badge <?php echo $badge_class; ?>"><?php echo $re['payment_status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_exp)): ?>
                        <tr><td colspan="5" class="empty-row">No expenses recorded</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-action">
                <a href="expense_dashboard.php" class="btn btn-outline btn-sm">View All Expenses →</a>
            </div>
        </div>
    </div>

    <!-- Quick Links Section -->
    <div class="quick-actions-card">
        <div class="quick-actions-header">
            <span class="quick-actions-icon">⚡</span>
            <span class="quick-actions-title">Quick Actions</span>
        </div>
        <div class="action-buttons">
            <a href="sales_add.php" class="quick-action-btn"><span>🧾</span> New Sale</a>
            <a href="expense_add.php" class="quick-action-btn"><span>💸</span> Add Expense</a>
            <a href="customer_add.php" class="quick-action-btn"><span>👤</span> Add Customer</a>
            <a href="product_add.php" class="quick-action-btn"><span>🧴</span> Add Product</a>
            <a href="stock_add.php" class="quick-action-btn"><span>📥</span> Add Stock</a>
            <a href="category_add.php" class="quick-action-btn"><span>🏷️</span> Add Category</a>
            <a href="sales_dashboard.php" class="quick-action-btn"><span>💰</span> View Sales</a>
            <a href="stock_dashboard.php" class="quick-action-btn"><span>📊</span> View Stock</a>
        </div>
    </div>
</div>

<style>
/* ========== DASHBOARD ENHANCED STYLES ========== */

.dashboard-container {
    max-width: 1600px;
    margin: 0 auto;
}

/* Welcome Section */
.welcome-section {
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 24px;
    padding: 24px 28px;
    margin-bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    color: white;
}

.welcome-text h2 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.welcome-text p {
    font-size: 14px;
    opacity: 0.85;
    margin: 0;
}

.welcome-date .date-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 500;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}

.stats-grid.secondary {
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
.stat-card-secondary .stat-icon { background: linear-gradient(135deg, #7b1fa2 0%, #9c27b0 100%); }

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

.stat-value.positive {
    color: #2e7d32;
}

.stat-value.negative {
    color: #c62828;
}

.stat-sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
}

/* Charts Row */
.charts-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 28px;
}

/* Activity Row */
.activity-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 28px;
}

/* Premium Card */
.premium-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #eef2f8;
    overflow: hidden;
    padding: 0;
}

.premium-card.full-width {
    grid-column: span 2;
}

.card-header {
    padding: 18px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #eef2f8;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.card-header-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: white;
}

.card-header-icon.warning { background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); }
.card-header-icon.success { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
.card-header-icon.danger { background: linear-gradient(135deg, #c62828 0%, #e53935 100%); }

.card-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e4663;
    margin: 0 0 2px 0;
    padding: 0;
    border: none;
}

.card-subtitle {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.alert-badge {
    background: #ffebee;
    color: #c62828;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    margin-left: auto;
}

.card-content {
    padding: 20px 24px;
}

.card-action {
    padding: 12px 24px 20px 24px;
    border-top: 1px solid #eef2f8;
    text-align: right;
}

/* Chart Container */
.chart-container {
    padding: 20px;
    min-height: 280px;
}

/* Table Styles */
.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.data-table th {
    background: #f8fafc;
    padding: 12px;
    text-align: left;
    font-weight: 700;
    color: #1e4663;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}

.data-table.compact td,
.data-table.compact th {
    padding: 10px 12px;
}

.data-table tr:hover td {
    background: #fafcff;
}

.price-cell {
    font-weight: 700;
    color: #1e4663;
}

.price-cell.danger {
    color: #c62828;
}

.stock-remaining {
    font-weight: 700;
    color: #c62828;
}

/* Progress Bar */
.progress-bar {
    background: #e2e8f0;
    border-radius: 10px;
    height: 8px;
    width: 80px;
    overflow: hidden;
    display: inline-block;
    margin-right: 8px;
    vertical-align: middle;
}

.progress-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.progress-label {
    font-size: 11px;
    color: #64748b;
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state.small {
    padding: 30px 20px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-state p {
    color: #94a3b8;
    font-size: 14px;
}

.empty-row {
    text-align: center;
    padding: 30px !important;
    color: #94a3b8;
}

/* Quick Actions */
.quick-actions-card {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 24px;
    padding: 20px 24px;
    margin-top: 8px;
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
    font-size: 16px;
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
    padding: 10px 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 40px;
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

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border: none;
}

.btn-sm {
    padding: 6px 14px;
    font-size: 12px;
}

.btn-outline {
    background: transparent;
    color: #1e4663;
    border: 1px solid #cbd5e1;
}

.btn-outline:hover {
    background: #1e4663;
    border-color: #1e4663;
    color: white;
}

/* ========== RESPONSIVE DESIGN ========== */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .charts-row {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .activity-row {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .premium-card.full-width {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stats-grid.secondary {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .welcome-section {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .welcome-text h2 {
        font-size: 18px;
    }
    
    .welcome-text p {
        font-size: 13px;
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
    
    .card-header {
        padding: 14px 18px;
    }
    
    .card-header-icon {
        width: 38px;
        height: 38px;
        font-size: 18px;
    }
    
    .card-header h3 {
        font-size: 16px;
    }
    
    .card-content {
        padding: 16px 18px;
    }
    
    /* Responsive Tables */
    .data-table thead {
        display: none;
    }
    
    .data-table tbody tr {
        display: block;
        margin-bottom: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
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
    
    .action-buttons {
        justify-content: center;
    }
    
    .quick-action-btn {
        padding: 8px 16px;
        font-size: 12px;
    }
    
    .chart-container {
        min-height: 220px;
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .stats-grid.secondary {
        grid-template-columns: 1fr;
    }
    
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
    
    .welcome-section {
        padding: 16px;
    }
    
    .card-header {
        padding: 12px 16px;
    }
    
    .card-content {
        padding: 12px 16px;
    }
    
    .quick-action-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales vs Expenses Chart
    var ctx1 = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($monthly_data, 'label')); ?>,
            datasets: [
                {
                    label: 'Sales (₹)',
                    data: <?php echo json_encode(array_column($monthly_data, 'total')); ?>,
                    backgroundColor: 'rgba(30, 70, 99, 0.8)',
                    borderColor: '#1e4663',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.65
                },
                {
                    label: 'Expenses (₹)',
                    data: <?php echo json_encode($monthly_exp); ?>,
                    backgroundColor: 'rgba(198, 40, 40, 0.7)',
                    borderColor: '#c62828',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.65
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { font: { size: 11 }, usePointStyle: true, boxWidth: 8 }
                },
                tooltip: { 
                    callbacks: { label: function(ctx) { return ctx.dataset.label + ': ₹' + ctx.raw.toLocaleString(); } }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: function(v) { return '₹' + v.toLocaleString(); }, font: { size: 10 } },
                    grid: { color: '#e2e8f0' }
                },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // Stock Chart (Doughnut)
    var stockRemain = <?php echo json_encode(array_map(fn($r) => floatval($r['remaining']), $stock_data)); ?>;
    var stockLabels = <?php echo json_encode(array_column($stock_data, 'category_name')); ?>;
    
    if (stockRemain.length > 0) {
        var ctx2 = document.getElementById('stockChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: stockLabels,
                datasets: [{
                    data: stockRemain,
                    backgroundColor: [
                        '#1e4663', '#2e7d32', '#c62828', '#f57c00', 
                        '#0288d1', '#7b1fa2', '#00838f', '#558b2f',
                        '#4527a0', '#ad1457', '#00695c', '#bf360c'
                    ],
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { font: { size: 10 }, boxWidth: 10, usePointStyle: true },
                        maxHeight: 100
                    },
                    tooltip: { 
                        callbacks: { label: function(ctx) { return ctx.label + ': ' + ctx.raw.toFixed(2) + ' units'; } }
                    }
                },
                cutout: '55%'
            }
        });
    } else {
        document.getElementById('stockChart').parentElement.innerHTML = '<div class="empty-state"><div class="empty-icon">📊</div><p>No stock data available</p></div>';
    }
});
</script>

<?php include 'sidebar_close.php'; ?>