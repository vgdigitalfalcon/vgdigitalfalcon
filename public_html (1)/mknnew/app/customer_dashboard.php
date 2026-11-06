<?php
require_once 'config.php';
requireLogin();
$page_title = 'Customer Dashboard';
$msg = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if customer has any sales/transactions before deleting
    $check_sales = $conn->query("SELECT id FROM sales WHERE customer_id = $id LIMIT 1");
    if ($check_sales && $check_sales->num_rows > 0) {
        $msg = '<div class="alert alert-error">❌ Cannot delete customer. They have existing sales records.</div>';
    } else {
        $conn->query("DELETE FROM customers WHERE id=$id");
        $msg = '<div class="alert alert-success">✅ Customer deleted successfully.</div>';
    }
}

// Get edit data
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM customers WHERE id=$edit_id");
    if ($r) $edit_data = $r->fetch_assoc();
}

// Handle edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id    = intval($_POST['edit_id']);
    $name  = sanitize($conn, $_POST['customer_name']);
    $shop  = sanitize($conn, $_POST['shop_name']);
    $addr  = sanitize($conn, $_POST['address']);
    $city  = sanitize($conn, $_POST['city']);
    $state = sanitize($conn, $_POST['state']);
    $gst   = sanitize($conn, $_POST['gst_no']);
    $phone = sanitize($conn, $_POST['phone']);
    $email = sanitize($conn, $_POST['email']);
    
    if (!empty($name)) {
        $conn->query("UPDATE customers SET customer_name='$name',shop_name='$shop',address='$addr',
                      city='$city',state='$state',gst_no='$gst',phone='$phone',email='$email' WHERE id=$id");
        $msg = '<div class="alert alert-success">✅ Customer updated successfully.</div>';
        $edit_id = 0;
        $edit_data = null;
    } else {
        $msg = '<div class="alert alert-error">❌ Customer name is required.</div>';
    }
}

// View modal data
$view_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
$view_data = null;
if ($view_id) {
    $r = $conn->query("SELECT * FROM customers WHERE id=$view_id");
    if ($r) $view_data = $r->fetch_assoc();
}

// Get filter parameters
$search_term = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_city = isset($_GET['city']) ? sanitize($conn, $_GET['city']) : '';
$filter_state = isset($_GET['state']) ? sanitize($conn, $_GET['state']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause for filters
$where_clauses = [];
if ($search_term) {
    $where_clauses[] = "(customer_name LIKE '%$search_term%' OR shop_name LIKE '%$search_term%' OR phone LIKE '%$search_term%' OR email LIKE '%$search_term%')";
}
if ($filter_city) {
    $where_clauses[] = "city LIKE '%$filter_city%'";
}
if ($filter_state) {
    $where_clauses[] = "state LIKE '%$filter_state%'";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM customers $where_sql");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch customers with pagination
$customers_query = $conn->query("SELECT * FROM customers $where_sql ORDER BY customer_name LIMIT $limit OFFSET $offset");

// Get unique cities and states for filters
$cities_result = $conn->query("SELECT DISTINCT city FROM customers WHERE city != '' AND city IS NOT NULL ORDER BY city");
$states_result = $conn->query("SELECT DISTINCT state FROM customers WHERE state != '' AND state IS NOT NULL ORDER BY state");

// Get statistics
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_customers,
    COUNT(CASE WHEN shop_name IS NOT NULL AND shop_name != '' THEN 1 END) as business_customers,
    COUNT(CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as has_email,
    COUNT(CASE WHEN phone IS NOT NULL AND phone != '' THEN 1 END) as has_phone,
    COUNT(CASE WHEN gst_no IS NOT NULL AND gst_no != '' THEN 1 END) as has_gst
FROM customers");
$stats = $stats_query->fetch_assoc();

include 'sidebar.php';
?>

<!-- Main Content -->
<div class="customer-dashboard">
    <?php echo $msg; ?>

    <!-- View Modal -->
    <?php if ($view_data): ?>
    <div class="modal-overlay" id="viewModal">
        <div class="modal-container modal-view">
            <div class="modal-header">
                <div class="modal-icon">👁️</div>
                <div>
                    <h3>Customer Details</h3>
                    <p class="modal-subtitle">Complete customer information</p>
                </div>
                <a href="customer_dashboard.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">👤 Customer Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['customer_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">🏪 Shop Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['shop_name']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item full-width">
                        <span class="detail-label">📍 Address</span>
                        <span class="detail-value"><?php echo nl2br(htmlspecialchars($view_data['address'])) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">🏙️ City</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['city']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">🗺️ State</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['state']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">🏷️ GST Number</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['gst_no']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📞 Phone</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['phone']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">✉️ Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['email']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📅 Added On</span>
                        <span class="detail-value"><?php echo date('d M Y, h:i A', strtotime($view_data['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="?edit=<?php echo $view_data['id']; ?>" class="btn btn-warning">✏️ Edit Customer</a>
                <a href="customer_dashboard.php" class="btn btn-secondary">Close</a>
            </div>
        </div>
    </div>
    <style>.modal-overlay{display:flex;}</style>
    <?php endif; ?>

    <!-- Edit Modal -->
    <?php if ($edit_data): ?>
    <div class="modal-overlay" id="editModal">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-icon">✏️</div>
                <div>
                    <h3>Edit Customer #<?php echo $edit_id; ?></h3>
                    <p class="modal-subtitle">Update customer information</p>
                </div>
                <a href="customer_dashboard.php" class="modal-close">&times;</a>
            </div>
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">👤 Customer Name *</label>
                            <input type="text" name="customer_name" value="<?php echo htmlspecialchars($edit_data['customer_name']); ?>" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🏪 Shop Name</label>
                            <input type="text" name="shop_name" value="<?php echo htmlspecialchars($edit_data['shop_name']); ?>" class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📍 Address</label>
                        <textarea name="address" class="form-textarea" rows="2"><?php echo htmlspecialchars($edit_data['address']); ?></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">🏙️ City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($edit_data['city']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🗺️ State</label>
                            <input type="text" name="state" value="<?php echo htmlspecialchars($edit_data['state']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">🏷️ GST No</label>
                            <input type="text" name="gst_no" value="<?php echo htmlspecialchars($edit_data['gst_no']); ?>" class="form-input">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">📞 Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($edit_data['phone']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">✉️ Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($edit_data['email']); ?>" class="form-input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Update Customer</button>
                    <a href="customer_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <style>.modal-overlay{display:flex;}</style>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">👥</div>
            <div class="stat-details">
                <span class="stat-label">Total Customers</span>
                <span class="stat-value"><?php echo number_format($stats['total_customers']); ?></span>
                <span class="stat-sub">Registered customers</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-icon">🏪</div>
            <div class="stat-details">
                <span class="stat-label">Business Customers</span>
                <span class="stat-value"><?php echo number_format($stats['business_customers']); ?></span>
                <span class="stat-sub">With shop name</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-icon">📧</div>
            <div class="stat-details">
                <span class="stat-label">With Email</span>
                <span class="stat-value"><?php echo number_format($stats['has_email']); ?></span>
                <span class="stat-sub">Email available</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">📞</div>
            <div class="stat-details">
                <span class="stat-label">With Phone</span>
                <span class="stat-value"><?php echo number_format($stats['has_phone']); ?></span>
                <span class="stat-sub">Contact available</span>
            </div>
        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">👥</div>
            <div>
                <h3>All Customers</h3>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form" id="filterForm">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="🔍 Search by name, shop, phone, email..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="city" class="filter-select">
                        <option value="">All Cities</option>
                        <?php while($city = $cities_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($city['city']); ?>" <?php echo $filter_city === $city['city'] ? 'selected' : ''; ?>>
                                🏙️ <?php echo htmlspecialchars($city['city']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="state" class="filter-select">
                        <option value="">All States</option>
                        <?php 
                        $states_result->data_seek(0);
                        while($state = $states_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo htmlspecialchars($state['state']); ?>" <?php echo $filter_state === $state['state'] ? 'selected' : ''; ?>>
                                🗺️ <?php echo htmlspecialchars($state['state']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Apply Filters</button>
                    <a href="customer_dashboard.php" class="btn btn-outline btn-sm">🔄 Clear All</a>
                    <a href="customer_add.php" class="btn btn-success btn-sm">➕ Add Customer</a>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="data-table" id="custTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Shop Name</th>
                        <th>City</th>
                        <th>State</th>
                        <th>GST No</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers_query && $customers_query->num_rows > 0): ?>
                        <?php while ($row = $customers_query->fetch_assoc()): ?>
                            <tr>
                                <td data-label="#"><?php echo $row['id']; ?></td>
                                <td data-label="Customer Name" class="customer-name">
                                    <?php echo htmlspecialchars($row['customer_name']); ?>
                                </td>
                                <td data-label="Shop Name"><?php echo htmlspecialchars($row['shop_name']) ?: '-'; ?></td>
                                <td data-label="City"><?php echo htmlspecialchars($row['city']) ?: '-'; ?></td>
                                <td data-label="State"><?php echo htmlspecialchars($row['state']) ?: '-'; ?></td>
                                <td data-label="GST No"><?php echo htmlspecialchars($row['gst_no']) ?: '-'; ?></td>
                                <td data-label="Phone"><?php echo htmlspecialchars($row['phone']) ?: '-'; ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($row['email']) ?: '-'; ?></td>
                                <td data-label="Actions" class="actions-cell">
                                    <a href="?view=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_city ? '&city=' . urlencode($filter_city) : ''; ?><?php echo $filter_state ? '&state=' . urlencode($filter_state) : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn view-btn" title="View Details">👁️</a>
                                    <a href="?edit=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_city ? '&city=' . urlencode($filter_city) : ''; ?><?php echo $filter_state ? '&state=' . urlencode($filter_state) : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn edit-btn" title="Edit">✏️</a>
                                    <a href="?delete=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_city ? '&city=' . urlencode($filter_city) : ''; ?><?php echo $filter_state ? '&state=' . urlencode($filter_state) : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')">🗑️</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-row">
                                📭 No customers found. 
                                <a href="customer_add.php">Add your first customer</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> customers
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_city ? '&city=' . urlencode($filter_city) : ''; ?><?php echo $filter_state ? '&state=' . urlencode($filter_state) : ''; ?>" class="page-link">← Previous</a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_city ? '&city=' . urlencode($filter_city) : ''; ?><?php echo $filter_state ? '&state=' . urlencode($filter_state) : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_city ? '&city=' . urlencode($filter_city) : ''; ?><?php echo $filter_state ? '&state=' . urlencode($filter_state) : ''; ?>" class="page-link">Next →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* ========== CUSTOMER DASHBOARD SPECIFIC STYLES ========== */

.customer-dashboard {
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

.btn {
    text-decoration: none !important;
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
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s;
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

.customer-name {
    font-weight: 600;
    color: #1e4663;
}

/* Action Buttons */
.actions-cell {
    white-space: nowrap;
}

.action-btn {
    display: inline-block;
    padding: 6px 10px;
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

.edit-btn {
    background: #fff3e0;
    color: #f57c00;
}

.edit-btn:hover {
    background: #f57c00;
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

.empty-row {
    text-align: center;
    padding: 40px !important;
    color: #94a3b8;
}

.empty-row a {
    color: #1e4663;
    text-decoration: none;
    font-weight: 600;
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
}

.pagination-info {
    font-size: 13px;
    color: #64748b;
}

.pagination {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.page-link {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    color: #1e4663;
    background: #f1f5f9;
    transition: all 0.2s;
}

.page-link:hover {
    background: #1e4663;
    color: white;
}

.page-link.active {
    background: #1e4663;
    color: white;
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
    max-width: 700px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideIn 0.3s ease;
}

.modal-view {
    max-width: 600px;
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

/* Detail Grid for View Modal */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-item.full-width {
    grid-column: span 2;
}

.detail-label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 500;
    word-break: break-word;
}

/* Form Grid inside Modal */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}

.form-input,
.form-textarea {
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
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
        text-align: center;
    }
    
    /* Responsive Table */
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
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-item.full-width {
        grid-column: span 1;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .modal-container {
        margin: 16px;
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
    
    .card-header {
        flex-direction: row;
        padding: 16px 20px;
    }
    
    .page-link {
        padding: 6px 12px;
        font-size: 12px;
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
</script>

<?php include 'sidebar_close.php'; ?>