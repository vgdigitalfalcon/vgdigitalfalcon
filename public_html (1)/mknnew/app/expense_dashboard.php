<?php
require_once 'config.php';
requireLogin();
$page_title = 'Expense Dashboard';
$msg = '';

// Delete expense
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM expenses WHERE id=$id");
    $msg = '<div class="alert alert-success">✅ Expense deleted successfully.</div>';
}

// Inline payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id     = intval($_POST['edit_id']);
    $amount = floatval($_POST['amount']);
    $reason = sanitize($conn, $_POST['reason']);
    $spent  = intval($_POST['spent_by']);
    $status = sanitize($conn, $_POST['payment_status']);
    $partial = ($status === 'Partial Paid') ? floatval($_POST['partial_amount']) : 0;
    $date   = sanitize($conn, $_POST['expense_date']);
    $rem    = sanitize($conn, $_POST['remarks']);
    $conn->query("UPDATE expenses SET amount=$amount,reason='$reason',spent_by=$spent,
                  payment_status='$status',partial_amount=$partial,expense_date='$date',remarks='$rem'
                  WHERE id=$id");
    $msg = '<div class="alert alert-success">✅ Expense updated successfully.</div>';
}

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM expenses WHERE id=$edit_id");
    if ($r) $edit_data = $r->fetch_assoc();
}

$users = $conn->query("SELECT id, full_name FROM users ORDER BY full_name");
$users_arr = [];
while ($u = $users->fetch_assoc()) $users_arr[$u['id']] = $u['full_name'];

// Get filter parameters
$filter_status = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';
$filter_user = isset($_GET['user']) ? intval($_GET['user']) : '';
$filter_month = isset($_GET['month']) ? sanitize($conn, $_GET['month']) : '';
$search_term = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

// Build WHERE clause for filters
$where_clauses = [];
if ($filter_status) $where_clauses[] = "e.payment_status = '$filter_status'";
if ($filter_user) $where_clauses[] = "e.spent_by = $filter_user";
if ($filter_month) $where_clauses[] = "DATE_FORMAT(e.expense_date, '%Y-%m') = '$filter_month'";
if ($search_term) $where_clauses[] = "(e.reason LIKE '%$search_term%' OR e.remarks LIKE '%$search_term%' OR u.full_name LIKE '%$search_term%')";

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch expenses with filters
$res = $conn->query("SELECT e.*, u.full_name FROM expenses e 
                     LEFT JOIN users u ON e.spent_by = u.id 
                     $where_sql 
                     ORDER BY e.expense_date DESC, e.id DESC");

// Calculate statistics
$stats_query = $conn->query("SELECT 
    COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN amount ELSE 0 END), 0) as total_paid,
    COALESCE(SUM(CASE WHEN payment_status = 'Unpaid' THEN amount ELSE 0 END), 0) as total_unpaid,
    COALESCE(SUM(CASE WHEN payment_status = 'Partial Paid' THEN amount ELSE 0 END), 0) as total_partial,
    COALESCE(SUM(CASE WHEN payment_status = 'Partial Paid' THEN (amount - partial_amount) ELSE 0 END), 0) as total_balance_due,
    COALESCE(SUM(amount), 0) as total_expense,
    COALESCE(SUM(CASE WHEN expense_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN amount ELSE 0 END), 0) as last_30_days,
    COUNT(*) as total_count,
    COUNT(CASE WHEN payment_status = 'Unpaid' THEN 1 END) as unpaid_count
FROM expenses e");
$stats = $stats_query->fetch_assoc();

// Get distinct months for filter
$months_query = $conn->query("SELECT DISTINCT DATE_FORMAT(expense_date, '%Y-%m') as month_val, 
                              DATE_FORMAT(expense_date, '%M %Y') as month_name 
                              FROM expenses ORDER BY expense_date DESC");
$months_list = $months_query->fetch_all(MYSQLI_ASSOC);

// ── CHART DATA ──────────────────────────────────────────────────────────────

// Monthly Expense Trend (last 12 months)
$trend_query = $conn->query("
    SELECT 
        DATE_FORMAT(expense_date, '%b %Y') as month_label,
        DATE_FORMAT(expense_date, '%Y-%m')  as month_val,
        COALESCE(SUM(amount), 0)            as total,
        COALESCE(SUM(CASE WHEN payment_status='Paid'         THEN amount ELSE 0 END), 0) as paid,
        COALESCE(SUM(CASE WHEN payment_status='Unpaid'       THEN amount ELSE 0 END), 0) as unpaid,
        COALESCE(SUM(CASE WHEN payment_status='Partial Paid' THEN amount ELSE 0 END), 0) as partial,
        COUNT(*) as count
    FROM expenses
    WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month_val, month_label
    ORDER BY month_val ASC
");
$trend_labels = [];
$trend_totals = [];
$trend_paid   = [];
$trend_unpaid = [];
$trend_counts = [];
while ($row = $trend_query->fetch_assoc()) {
    $trend_labels[] = $row['month_label'];
    $trend_totals[] = (float)$row['total'];
    $trend_paid[]   = (float)$row['paid'];
    $trend_unpaid[] = (float)$row['unpaid'];
    $trend_counts[] = (int)$row['count'];
}

// Month-on-Month comparison (current vs previous month)
$mom_query = $conn->query("
    SELECT 
        DATE_FORMAT(expense_date, '%Y-%m') as month_val,
        DATE_FORMAT(expense_date, '%b %Y') as month_label,
        SUM(amount) as total
    FROM expenses
    WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
    GROUP BY month_val, month_label
    ORDER BY month_val DESC
    LIMIT 2
");
$mom_data = $mom_query->fetch_all(MYSQLI_ASSOC);
$current_month_total  = isset($mom_data[0]) ? (float)$mom_data[0]['total'] : 0;
$previous_month_total = isset($mom_data[1]) ? (float)$mom_data[1]['total'] : 0;
$mom_change = $previous_month_total > 0
    ? round((($current_month_total - $previous_month_total) / $previous_month_total) * 100, 1)
    : 0;
$current_month_label  = isset($mom_data[0]) ? $mom_data[0]['month_label'] : 'This Month';
$previous_month_label = isset($mom_data[1]) ? $mom_data[1]['month_label'] : 'Last Month';

// Top Expense Reasons (last 12 months, top 8)
$reasons_query = $conn->query("
    SELECT 
        reason,
        SUM(amount)  as total,
        COUNT(*)     as count,
        AVG(amount)  as avg_amount,
        MAX(expense_date) as last_date
    FROM expenses
    WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY reason
    ORDER BY total DESC
    LIMIT 8
");
$reason_labels  = [];
$reason_totals  = [];
$reason_counts  = [];
$reason_avgs    = [];
$reason_dates   = [];
while ($row = $reasons_query->fetch_assoc()) {
    $reason_labels[] = $row['reason'];
    $reason_totals[] = (float)$row['total'];
    $reason_counts[] = (int)$row['count'];
    $reason_avgs[]   = round((float)$row['avg_amount'], 2);
    $reason_dates[]  = $row['last_date'];
}

// Monthly breakdown by reason (top 5 reasons across months)
$reason_trend_query = $conn->query("
    SELECT 
        DATE_FORMAT(expense_date, '%b %Y') as month_label,
        DATE_FORMAT(expense_date, '%Y-%m')  as month_val,
        reason,
        SUM(amount) as total
    FROM expenses
    WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_val, month_label, reason
    ORDER BY month_val ASC
");
$reason_trend_raw = $reason_trend_query->fetch_all(MYSQLI_ASSOC);

// Average per month stats
$avg_query = $conn->query("
    SELECT AVG(monthly_total) as avg_monthly
    FROM (
        SELECT DATE_FORMAT(expense_date,'%Y-%m') as m, SUM(amount) as monthly_total
        FROM expenses GROUP BY m
    ) t
");
$avg_monthly = $avg_query ? (float)$avg_query->fetch_assoc()['avg_monthly'] : 0;

// Highest single expense month
$peak_query = $conn->query("
    SELECT DATE_FORMAT(expense_date,'%b %Y') as label, SUM(amount) as total
    FROM expenses
    GROUP BY DATE_FORMAT(expense_date,'%Y-%m')
    ORDER BY total DESC LIMIT 1
");
$peak_month = $peak_query ? $peak_query->fetch_assoc() : null;

include 'sidebar.php';
?>

<!-- Main Content -->
<div class="expense-dashboard">
    <?php echo $msg; ?>

    <!-- Edit Modal / Inline Edit Form -->
    <?php if ($edit_data): ?>
    <div class="modal-overlay" id="editModal">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-icon">✏️</div>
                <div>
                    <h3>Edit Expense #<?php echo $edit_id; ?></h3>
                    <p class="modal-subtitle">Update expense details</p>
                </div>
                <a href="expense_dashboard.php" class="modal-close">&times;</a>
            </div>
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">💰 Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" value="<?php echo $edit_data['amount']; ?>" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">📅 Expense Date *</label>
                        <input type="date" name="expense_date" value="<?php echo $edit_data['expense_date']; ?>" required class="form-input">
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label class="form-label">📝 Reason *</label>
                        <input type="text" name="reason" value="<?php echo htmlspecialchars($edit_data['reason']); ?>" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">👤 Spent By *</label>
                        <select name="spent_by" required class="form-select">
                            <?php foreach ($users_arr as $uid => $uname): ?>
                                <option value="<?php echo $uid; ?>" <?php echo ($edit_data['spent_by'] == $uid) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($uname); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">💳 Payment Status *</label>
                        <select name="payment_status" id="editPayStatus" class="form-select" onchange="toggleEditPartial(this.value)">
                            <?php foreach (['Paid', 'Unpaid', 'Partial Paid'] as $s): ?>
                                <option <?php echo $edit_data['payment_status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="editPartialDiv" style="<?php echo $edit_data['payment_status'] === 'Partial Paid' ? '' : 'display:none;'; ?>">
                        <label class="form-label">💸 Partial Amount Paid (₹)</label>
                        <input type="number" step="0.01" name="partial_amount" value="<?php echo $edit_data['partial_amount']; ?>" id="editPartAmt" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">📌 Remarks</label>
                    <textarea name="remarks" class="form-textarea" rows="2"><?php echo htmlspecialchars($edit_data['remarks']); ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Update Expense</button>
                    <a href="expense_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <style>.modal-overlay{display:flex;}</style>
    <script>
    function toggleEditPartial(v){
        document.getElementById('editPartialDiv').style.display = v === 'Partial Paid' ? 'flex' : 'none';
    }
    </script>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-label">Total Expenses</span>
                <span class="stat-value">₹<?php echo number_format($stats['total_expense'], 2); ?></span>
                <span class="stat-sub"><?php echo $stats['total_count']; ?> transactions</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-icon">✅</div>
            <div class="stat-details">
                <span class="stat-label">Paid</span>
                <span class="stat-value">₹<?php echo number_format($stats['total_paid'], 2); ?></span>
                <span class="stat-sub">Settled expenses</span>
            </div>
        </div>
        <div class="stat-card stat-card-danger">
            <div class="stat-icon">⏳</div>
            <div class="stat-details">
                <span class="stat-label">Unpaid</span>
                <span class="stat-value">₹<?php echo number_format($stats['total_unpaid'], 2); ?></span>
                <span class="stat-sub"><?php echo $stats['unpaid_count']; ?> pending</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">⚖️</div>
            <div class="stat-details">
                <span class="stat-label">Balance Due</span>
                <span class="stat-value">₹<?php echo number_format($stats['total_balance_due'], 2); ?></span>
                <span class="stat-sub">From partial payments</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-icon">📅</div>
            <div class="stat-details">
                <span class="stat-label">Last 30 Days</span>
                <span class="stat-value">₹<?php echo number_format($stats['last_30_days'], 2); ?></span>
                <span class="stat-sub">Recent activity</span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         ANALYTICS SECTION
    ════════════════════════════════════════════════════════════════ -->

    <!-- Month-on-Month Insight Cards -->
    <div class="analytics-insight-row">
        <div class="insight-card <?php echo $mom_change > 0 ? 'insight-up' : ($mom_change < 0 ? 'insight-down' : 'insight-neutral'); ?>">
            <div class="insight-icon"><?php echo $mom_change > 0 ? '📈' : ($mom_change < 0 ? '📉' : '➡️'); ?></div>
            <div class="insight-body">
                <span class="insight-label">Month-on-Month Change</span>
                <span class="insight-value"><?php echo ($mom_change > 0 ? '+' : '') . $mom_change; ?>%</span>
                <span class="insight-sub">
                    <?php echo $previous_month_label; ?>: ₹<?php echo number_format($previous_month_total, 0); ?> →
                    <?php echo $current_month_label; ?>: ₹<?php echo number_format($current_month_total, 0); ?>
                </span>
            </div>
        </div>
        <div class="insight-card insight-avg">
            <div class="insight-icon">📊</div>
            <div class="insight-body">
                <span class="insight-label">Avg Monthly Spend</span>
                <span class="insight-value">₹<?php echo number_format($avg_monthly, 0); ?></span>
                <span class="insight-sub">Based on all recorded months</span>
            </div>
        </div>
        <?php if ($peak_month): ?>
        <div class="insight-card insight-peak">
            <div class="insight-icon">🔥</div>
            <div class="insight-body">
                <span class="insight-label">Peak Expense Month</span>
                <span class="insight-value">₹<?php echo number_format($peak_month['total'], 0); ?></span>
                <span class="insight-sub"><?php echo htmlspecialchars($peak_month['label']); ?></span>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($reason_labels)): ?>
        <div class="insight-card insight-top">
            <div class="insight-icon">🏆</div>
            <div class="insight-body">
                <span class="insight-label">Top Expense Reason</span>
                <span class="insight-value insight-value-sm"><?php echo htmlspecialchars($reason_labels[0]); ?></span>
                <span class="insight-sub">₹<?php echo number_format($reason_totals[0], 0); ?> total · <?php echo $reason_counts[0]; ?> times</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">

        <!-- Monthly Trend Line Chart -->
        <div class="chart-card chart-card-wide">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">📈 Monthly Expense Trend</h3>
                    <p class="chart-subtitle">Last 12 months · Total vs Paid vs Unpaid</p>
                </div>
                <div class="chart-legend">
                    <span class="legend-dot" style="background:#1e4663"></span><span>Total</span>
                    <span class="legend-dot" style="background:#2e7d32"></span><span>Paid</span>
                    <span class="legend-dot" style="background:#c62828"></span><span>Unpaid</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="trendChart" height="90"></canvas>
            </div>
        </div>

        <!-- Top Reasons Horizontal Bar Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <h3 class="chart-title">🏷️ Top Expense Reasons</h3>
                    <p class="chart-subtitle">Last 12 months · By total spend</p>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="reasonsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Reasons Detail Table -->
    <?php if (!empty($reason_labels)): ?>
    <div class="card premium-card reasons-detail-card">
        <div class="card-header">
            <div class="card-header-icon">🔍</div>
            <div>
                <h3>Reason-wise Expense Breakdown</h3>
                <p class="card-subtitle">Month-on-month analysis by expense category</p>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table reasons-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reason</th>
                        <th>Total Spent</th>
                        <th>No. of Times</th>
                        <th>Avg per Entry</th>
                        <th>Last Occurrence</th>
                        <th>Share %</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $grand_total = array_sum($reason_totals);
                foreach ($reason_labels as $i => $label):
                    $share = $grand_total > 0 ? round(($reason_totals[$i] / $grand_total) * 100, 1) : 0;
                    $bar_width = $grand_total > 0 ? ($reason_totals[$i] / max($reason_totals)) * 100 : 0;
                    $colors = ['#1e4663','#2e7d32','#c62828','#f57c00','#0288d1','#6a1b9a','#00695c','#ad1457'];
                    $color = $colors[$i % count($colors)];
                ?>
                <tr>
                    <td data-label="#">
                        <span class="rank-badge" style="background:<?php echo $color; ?>"><?php echo $i+1; ?></span>
                    </td>
                    <td data-label="Reason"><strong><?php echo htmlspecialchars($label); ?></strong></td>
                    <td data-label="Total" class="amount-cell">₹<?php echo number_format($reason_totals[$i], 2); ?></td>
                    <td data-label="Count"><span class="count-chip"><?php echo $reason_counts[$i]; ?> entries</span></td>
                    <td data-label="Avg">₹<?php echo number_format($reason_avgs[$i], 2); ?></td>
                    <td data-label="Last"><?php echo date('d M Y', strtotime($reason_dates[$i])); ?></td>
                    <td data-label="Share%" style="min-width:120px">
                        <div class="share-bar-wrap">
                            <div class="share-bar" style="width:<?php echo $bar_width; ?>%;background:<?php echo $color; ?>"></div>
                            <span class="share-pct"><?php echo $share; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Expenses Table Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">💸</div>
            <div>
                <h3>All Expenses</h3>
                <p class="card-subtitle">Manage and track all expense records</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form" id="filterForm">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="🔍 Search by reason, user, remarks..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Paid" <?php echo $filter_status === 'Paid' ? 'selected' : ''; ?>>✅ Paid</option>
                        <option value="Unpaid" <?php echo $filter_status === 'Unpaid' ? 'selected' : ''; ?>>⏳ Unpaid</option>
                        <option value="Partial Paid" <?php echo $filter_status === 'Partial Paid' ? 'selected' : ''; ?>>🔸 Partial Paid</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="user" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Users</option>
                        <?php foreach ($users_arr as $uid => $uname): ?>
                            <option value="<?php echo $uid; ?>" <?php echo $filter_user == $uid ? 'selected' : ''; ?>>
                                👤 <?php echo htmlspecialchars($uname); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="month" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Months</option>
                        <?php foreach ($months_list as $month): ?>
                            <option value="<?php echo $month['month_val']; ?>" <?php echo $filter_month === $month['month_val'] ? 'selected' : ''; ?>>
                                📆 <?php echo $month['month_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <a href="expense_dashboard.php" class="btn btn-outline btn-sm">🔄 Clear Filters</a>
                    <a href="expense_add.php" class="btn btn-primary btn-sm">➕ Add New Expense</a>
                </div>
            </form>
        </div>

        <!-- Table Container with Horizontal Scroll for Mobile -->
        <div class="table-container">
            <table class="data-table" id="expTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Reason</th>
                        <th>Spent By</th>
                        <th>Amount (₹)</th>
                        <th>Status</th>
                        <th>Partial (₹)</th>
                        <th>Balance (₹)</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $displayed_expense = 0;
                    if ($res && $res->num_rows > 0):
                        while ($row = $res->fetch_assoc()):
                            $displayed_expense += $row['amount'];
                            $balance = ($row['payment_status'] === 'Partial Paid') ? $row['amount'] - $row['partial_amount'] : 0;
                            $badge_class = [
                                'Paid' => 'badge-success',
                                'Unpaid' => 'badge-danger',
                                'Partial Paid' => 'badge-warning'
                            ][$row['payment_status']] ?? 'badge-info';
                    ?>
                        <tr>
                            <td data-label="#"><?php echo $row['id']; ?></td>
                            <td data-label="Date"><?php echo date('d M Y', strtotime($row['expense_date'])); ?></td>
                            <td data-label="Reason" class="reason-cell"><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td data-label="Spent By"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td data-label="Amount" class="amount-cell">₹<?php echo number_format($row['amount'], 2); ?></td>
                            <td data-label="Status"><span class="badge <?php echo $badge_class; ?>"><?php echo $row['payment_status']; ?></span></td>
                            <td data-label="Partial"><?php echo $row['payment_status'] === 'Partial Paid' ? '₹' . number_format($row['partial_amount'], 2) : '-'; ?></td>
                            <td data-label="Balance"><?php echo $balance > 0 ? '<span class="balance-due">₹' . number_format($balance, 2) . '</span>' : '-'; ?></td>
                            <td data-label="Remarks" class="remarks-cell"><?php echo htmlspecialchars($row['remarks']) ?: '-'; ?></td>
                            <td data-label="Actions" class="actions-cell">
                                <a href="?edit=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_user ? '&user=' . $filter_user : ''; ?><?php echo $filter_month ? '&month=' . $filter_month : ''; ?>" class="action-btn edit-btn" title="Edit">✏️</a>
                                <a href="?delete=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_user ? '&user=' . $filter_user : ''; ?><?php echo $filter_month ? '&month=' . $filter_month : ''; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Delete this expense?')">🗑️</a>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="10" class="empty-row">📭 No expenses found. <a href="expense_add.php">Add your first expense</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if ($res && $res->num_rows > 0): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" class="footer-total">Total (Filtered):</td>
                        <td class="footer-amount">₹<?php echo number_format($displayed_expense, 2); ?></td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<style>
/* ========== EXPENSE DASHBOARD SPECIFIC STYLES ========== */

.expense-dashboard {
    max-width: 1600px;
    margin: 0 auto;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); }

.stat-icon {
    width: 52px; height: 52px; border-radius: 18px;
    display: flex; align-items: center; justify-content: center; font-size: 26px;
}
.stat-card-primary .stat-icon { background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%); }
.stat-card-success .stat-icon { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
.stat-card-danger  .stat-icon { background: linear-gradient(135deg, #c62828 0%, #e53935 100%); }
.stat-card-warning .stat-icon { background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%); }
.stat-card-info    .stat-icon { background: linear-gradient(135deg, #0288d1 0%, #03a9f4 100%); }

.stat-details { flex: 1; }
.stat-label { font-size: 12px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
.stat-value { font-size: 22px; font-weight: 800; color: #1e293b; display: block; line-height: 1.2; }
.stat-sub   { font-size: 11px; color: #94a3b8; margin-top: 4px; display: block; }

/* ── ANALYTICS INSIGHT ROW ── */
.analytics-insight-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.btn {
    text-decoration: none !important;
}

.insight-card {
    background: #fff;
    border-radius: 18px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1.5px solid #eef2f8;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.3s;
}
.insight-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.07); }

.insight-card.insight-up    { border-left: 4px solid #c62828; }
.insight-card.insight-down  { border-left: 4px solid #2e7d32; }
.insight-card.insight-neutral { border-left: 4px solid #0288d1; }
.insight-card.insight-avg   { border-left: 4px solid #0288d1; }
.insight-card.insight-peak  { border-left: 4px solid #f57c00; }
.insight-card.insight-top   { border-left: 4px solid #6a1b9a; }

.insight-icon { font-size: 32px; line-height: 1; }
.insight-body { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.insight-label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; }
.insight-value { font-size: 24px; font-weight: 800; color: #1e293b; line-height: 1.3; }
.insight-value-sm { font-size: 15px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.insight-sub   { font-size: 11px; color: #94a3b8; margin-top: 2px; }

/* ── CHARTS ROW ── */
.charts-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.chart-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid #eef2f8;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    overflow: hidden;
}

.chart-card-wide { grid-column: span 1; }

.chart-header {
    padding: 20px 24px 12px 24px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 8px;
}

.chart-title   { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0; }
.chart-subtitle { font-size: 12px; color: #94a3b8; margin: 4px 0 0 0; }

.chart-legend {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #64748b;
    flex-wrap: wrap;
}
.legend-dot {
    width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 4px;
}

.chart-body {
    padding: 16px 20px 20px 20px;
    position: relative;
}

/* ── REASONS DETAIL TABLE ── */
.reasons-detail-card { margin-bottom: 24px; }

.reasons-table .rank-badge {
    width: 26px; height: 26px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 12px;
}

.count-chip {
    background: #f1f5f9; color: #475569;
    padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
}

.share-bar-wrap {
    display: flex; align-items: center; gap: 8px;
}
.share-bar {
    height: 8px; border-radius: 4px; min-width: 4px; transition: width 0.6s ease;
}
.share-pct {
    font-size: 12px; font-weight: 600; color: #475569; white-space: nowrap;
}

/* Filter Section */
.filter-section {
    padding: 20px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #eef2f8;
}
.filter-form { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.filter-group { flex: 1; min-width: 160px; }
.filter-input, .filter-select {
    width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 12px; font-size: 13px; font-family: 'Inter', sans-serif; transition: all 0.2s;
}
.filter-input:focus, .filter-select:focus {
    outline: none; border-color: #1e4663; box-shadow: 0 0 0 3px rgba(30,70,99,0.1);
}
.filter-actions { display: flex; gap: 10px; align-items: center; }

/* Table Styles */
.table-container { overflow-x: auto; padding: 0 24px 24px 24px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th {
    background: #f8fafc; padding: 14px 12px; text-align: left;
    font-weight: 700; color: #1e4663; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.data-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
.data-table tr:hover td { background: #fafcff; }
.reason-cell  { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.remarks-cell { max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.amount-cell  { font-weight: 700; color: #1e4663; }
.balance-due  { color: #c62828; font-weight: 600; }
.actions-cell { white-space: nowrap; }

.action-btn { display: inline-block; padding: 6px 10px; border-radius: 8px; text-decoration: none; font-size: 14px; margin: 0 2px; transition: all 0.2s; }
.edit-btn   { background: #fff3e0; color: #f57c00; }
.edit-btn:hover { background: #f57c00; color: white; }
.delete-btn { background: #ffebee; color: #c62828; }
.delete-btn:hover { background: #c62828; color: white; }

.empty-row { text-align: center; padding: 40px !important; color: #94a3b8; }
.empty-row a { color: #1e4663; text-decoration: none; font-weight: 600; }
.footer-total  { text-align: right; font-weight: 700; background: #f8fafc; }
.footer-amount { font-weight: 800; color: #1e4663; background: #f8fafc; }

/* Modal Styles */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px;
}
.modal-container {
    background: white; border-radius: 28px; max-width: 700px; width: 100%;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    animation: modalSlideIn 0.3s ease;
}
@keyframes modalSlideIn { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
.modal-header {
    padding: 20px 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #eef2f8; display: flex; align-items: center; gap: 14px; position: relative;
}
.modal-icon {
    width: 48px; height: 48px; background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
}
.modal-close { position: absolute; right: 20px; top: 20px; font-size: 28px; color: #94a3b8; text-decoration: none; }
.modal-close:hover { color: #c62828; }
.modal-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
.modal-container .form-grid   { padding: 24px; }
.modal-container .form-actions { padding: 0 24px 24px 24px; margin-top: 0; }

/* Badge Styles */
.badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
.badge-success { background: #e8f5e9; color: #2e7d32; }
.badge-danger  { background: #ffebee; color: #c62828; }
.badge-warning { background: #fff3e0; color: #e65100; }
.badge-info    { background: #e3f2fd; color: #0277bd; }

/* Responsive */
@media (max-width: 1024px) {
    .charts-row { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 15px; }
    .stat-value { font-size: 18px; }
}
@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2,1fr); gap: 12px; }
    .analytics-insight-row { grid-template-columns: repeat(2,1fr); }
    .stat-card { padding: 14px 16px; }
    .stat-icon { width: 44px; height: 44px; font-size: 22px; }
    .stat-value { font-size: 16px; }
    .stat-label { font-size: 10px; }
    .filter-form { flex-direction: column; }
    .filter-group { width: 100%; }
    .filter-actions { flex-direction: column; width: 100%; }
    .filter-actions .btn { width: 100%; text-align: center; }
    .data-table thead { display: none; }
    .data-table tbody tr { display: block; margin-bottom: 16px; border: 1px solid #e2e8f0; border-radius: 16px; background: white; overflow: hidden; }
    .data-table tbody td { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; white-space: normal; }
    .data-table tbody td:before { content: attr(data-label); font-weight: 700; color: #1e4663; width: 40%; font-size: 12px; }
    .data-table tbody td:last-child { border-bottom: none; }
    .reason-cell, .remarks-cell { max-width: none; white-space: normal; }
    .actions-cell { justify-content: flex-end; }
    .table-container { padding: 0 16px 16px 16px; }
    .chart-header { flex-direction: column; }
    .modal-container { margin: 16px; max-height: 85vh; }
}
@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .analytics-insight-row { grid-template-columns: 1fr; }
    .filter-section { padding: 16px; }
    .data-table tbody td { font-size: 12px; padding: 8px 12px; }
    .data-table tbody td:before { font-size: 11px; }
    .action-btn { padding: 4px 8px; font-size: 12px; }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ── PHP DATA → JS ─────────────────────────────────────────────────────────
const trendLabels  = <?php echo json_encode($trend_labels); ?>;
const trendTotals  = <?php echo json_encode($trend_totals); ?>;
const trendPaid    = <?php echo json_encode($trend_paid); ?>;
const trendUnpaid  = <?php echo json_encode($trend_unpaid); ?>;
const trendCounts  = <?php echo json_encode($trend_counts); ?>;

const reasonLabels = <?php echo json_encode($reason_labels); ?>;
const reasonTotals = <?php echo json_encode($reason_totals); ?>;
const reasonCounts = <?php echo json_encode($reason_counts); ?>;

// ── SHARED CHART DEFAULTS ─────────────────────────────────────────────────
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#64748b';

// ── 1. MONTHLY TREND LINE CHART ───────────────────────────────────────────
const trendCtx = document.getElementById('trendChart').getContext('2d');

// Gradient fills
const gradTotal = trendCtx.createLinearGradient(0, 0, 0, 300);
gradTotal.addColorStop(0, 'rgba(30,70,99,0.18)');
gradTotal.addColorStop(1, 'rgba(30,70,99,0)');

const gradPaid = trendCtx.createLinearGradient(0, 0, 0, 300);
gradPaid.addColorStop(0, 'rgba(46,125,50,0.15)');
gradPaid.addColorStop(1, 'rgba(46,125,50,0)');

const gradUnpaid = trendCtx.createLinearGradient(0, 0, 0, 300);
gradUnpaid.addColorStop(0, 'rgba(198,40,40,0.12)');
gradUnpaid.addColorStop(1, 'rgba(198,40,40,0)');

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [
            {
                label: 'Total',
                data: trendTotals,
                borderColor: '#1e4663',
                backgroundColor: gradTotal,
                borderWidth: 2.5,
                pointBackgroundColor: '#1e4663',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Paid',
                data: trendPaid,
                borderColor: '#2e7d32',
                backgroundColor: gradPaid,
                borderWidth: 2,
                pointBackgroundColor: '#2e7d32',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
                borderDash: [],
            },
            {
                label: 'Unpaid',
                data: trendUnpaid,
                borderColor: '#c62828',
                backgroundColor: gradUnpaid,
                borderWidth: 2,
                pointBackgroundColor: '#c62828',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
                borderDash: [5, 3],
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                titleColor: '#f1f5f9',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 10,
                callbacks: {
                    label: ctx => ` ₹${ctx.parsed.y.toLocaleString('en-IN', {minimumFractionDigits:2})}`,
                    afterBody: (items) => {
                        const idx = items[0].dataIndex;
                        return [`  Transactions: ${trendCounts[idx]}`];
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { color: '#f1f5f9', drawBorder: false },
                ticks: { font: { size: 11 } }
            },
            y: {
                grid: { color: '#f1f5f9', drawBorder: false },
                beginAtZero: true,
                ticks: {
                    callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v),
                    font: { size: 11 }
                }
            }
        }
    }
});

// ── 2. TOP REASONS HORIZONTAL BAR CHART ──────────────────────────────────
const reasonColors = [
    '#1e4663','#2e7d32','#c62828','#f57c00',
    '#0288d1','#6a1b9a','#00695c','#ad1457'
];

const reasonsCtx = document.getElementById('reasonsChart').getContext('2d');
new Chart(reasonsCtx, {
    type: 'bar',
    data: {
        labels: reasonLabels.map(l => l.length > 22 ? l.slice(0,22)+'…' : l),
        datasets: [{
            label: 'Total Spent (₹)',
            data: reasonTotals,
            backgroundColor: reasonColors.map(c => c + 'cc'),
            borderColor: reasonColors,
            borderWidth: 1.5,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e293b',
                titleColor: '#f1f5f9',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 10,
                callbacks: {
                    label: ctx => ` ₹${ctx.parsed.x.toLocaleString('en-IN', {minimumFractionDigits:2})}`,
                    afterLabel: ctx => ` Entries: ${reasonCounts[ctx.dataIndex]}`
                }
            }
        },
        scales: {
            x: {
                grid: { color: '#f1f5f9', drawBorder: false },
                beginAtZero: true,
                ticks: {
                    callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v),
                    font: { size: 11 }
                }
            },
            y: {
                grid: { display: false },
                ticks: { font: { size: 11 } }
            }
        }
    }
});

// ── AUTO-HIDE ALERTS ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

function toggleEditPartial(value) {
    const partialDiv = document.getElementById('editPartialDiv');
    if (partialDiv) partialDiv.style.display = value === 'Partial Paid' ? 'flex' : 'none';
}
</script>

<?php include 'sidebar_close.php'; ?>