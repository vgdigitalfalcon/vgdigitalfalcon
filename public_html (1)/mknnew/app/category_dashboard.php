<?php
require_once 'config.php';
requireLogin();
$page_title = 'Category Dashboard';
$msg = '';

// Handle delete with safety check
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if category has any products
    $check_products = $conn->query("SELECT id FROM products WHERE category_id = $id LIMIT 1");
    if ($check_products && $check_products->num_rows > 0) {
        $msg = '<div class="alert alert-error">❌ Cannot delete category. It has products associated with it.</div>';
    } else {
        $conn->query("DELETE FROM product_categories WHERE id=$id");
        $msg = '<div class="alert alert-success">✅ Category deleted successfully.</div>';
    }
}

// Get edit data
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM product_categories WHERE id=$edit_id");
    if ($r) $edit_data = $r->fetch_assoc();
}

// Handle edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id    = intval($_POST['edit_id']);
    $name  = sanitize($conn, $_POST['category_name']);
    $per   = sanitize($conn, $_POST['price_per']);
    $price = floatval($_POST['price']);
    
    if (!empty($name) && $price > 0) {
        $conn->query("UPDATE product_categories SET category_name='$name', price_per='$per', price=$price WHERE id=$id");
        $msg = '<div class="alert alert-success">✅ Category updated successfully.</div>';
        $edit_id = 0;
        $edit_data = null;
    } else {
        $msg = '<div class="alert alert-error">❌ Category name and valid price are required.</div>';
    }
}

// Get filter and pagination parameters
$search_term = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_per = isset($_GET['per']) ? sanitize($conn, $_GET['per']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where_clauses = [];
if ($search_term) {
    $where_clauses[] = "(category_name LIKE '%$search_term%')";
}
if ($filter_per) {
    $where_clauses[] = "price_per = '$filter_per'";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM product_categories $where_sql");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch categories with pagination
$res = $conn->query("SELECT * FROM product_categories $where_sql ORDER BY category_name LIMIT $limit OFFSET $offset");

// Get statistics
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_categories,
    COUNT(CASE WHEN price_per = 'KG' THEN 1 END) as kg_count,
    COUNT(CASE WHEN price_per = 'PC' THEN 1 END) as pc_count,
    COUNT(CASE WHEN price_per = 'GM' THEN 1 END) as gm_count,
    COUNT(CASE WHEN price_per = 'LTR' THEN 1 END) as ltr_count,
    COUNT(CASE WHEN price_per = 'DOZEN' THEN 1 END) as dozen_count,
    MIN(price) as min_price,
    MAX(price) as max_price,
    AVG(price) as avg_price,
    SUM(price) as total_value
FROM product_categories");
$stats = $stats_query->fetch_assoc();

include 'sidebar.php';
?>

<!-- Main Content -->
<div class="category-dashboard">
    <?php echo $msg; ?>

    <!-- Edit Modal -->
    <?php if ($edit_data): ?>
    <div class="modal-overlay" id="editModal">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-icon">✏️</div>
                <div>
                    <h3>Edit Category</h3>
                    <p class="modal-subtitle">Update category information</p>
                </div>
                <a href="category_dashboard.php" class="modal-close">&times;</a>
            </div>
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">📝 Category Name *</label>
                        <input type="text" name="category_name" value="<?php echo htmlspecialchars($edit_data['category_name']); ?>" required class="form-input">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">⚖️ Price Per *</label>
                            <select name="price_per" class="form-select">
                                <option value="KG" <?php echo $edit_data['price_per'] === 'KG' ? 'selected' : ''; ?>>KG (Kilogram)</option>
                                <option value="PC" <?php echo $edit_data['price_per'] === 'PC' ? 'selected' : ''; ?>>PC (Piece)</option>
                                <option value="GM" <?php echo $edit_data['price_per'] === 'GM' ? 'selected' : ''; ?>>GM (Gram)</option>
                                <option value="LTR" <?php echo $edit_data['price_per'] === 'LTR' ? 'selected' : ''; ?>>LTR (Liter)</option>
                                <option value="DOZEN" <?php echo $edit_data['price_per'] === 'DOZEN' ? 'selected' : ''; ?>>DOZEN (Dozen)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">💰 Price (₹) *</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $edit_data['price']; ?>" required class="form-input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Update Category</button>
                    <a href="category_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <style>.modal-overlay{display:flex;}</style>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">🏷️</div>
            <div class="stat-details">
                <span class="stat-label">Total Categories</span>
                <span class="stat-value"><?php echo number_format($stats['total_categories']); ?></span>
                <span class="stat-sub">Product categories</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-icon">⚖️</div>
            <div class="stat-details">
                <span class="stat-label">KG / PC</span>
                <span class="stat-value"><?php echo $stats['kg_count'] + $stats['pc_count']; ?></span>
                <span class="stat-sub">Most common units</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-label">Avg. Price</span>
                <span class="stat-value">₹<?php echo number_format($stats['avg_price'] ?? 0, 2); ?></span>
                <span class="stat-sub">Per unit</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">📊</div>
            <div class="stat-details">
                <span class="stat-label">Price Range</span>
                <span class="stat-value">₹<?php echo number_format($stats['min_price'] ?? 0, 2); ?> - ₹<?php echo number_format($stats['max_price'] ?? 0, 2); ?></span>
                <span class="stat-sub">Min to Max</span>
            </div>
        </div>
    </div>

    <!-- Categories Table Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">📂</div>
            <div>
                <h3>All Product Categories</h3>
                <p class="card-subtitle">Manage your product categories</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form" id="filterForm">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="🔍 Search by category name..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="per" class="filter-select">
                        <option value="">All Units</option>
                        <option value="KG" <?php echo $filter_per === 'KG' ? 'selected' : ''; ?>>⚖️ KG (Kilogram)</option>
                        <option value="PC" <?php echo $filter_per === 'PC' ? 'selected' : ''; ?>>📦 PC (Piece)</option>
                        <option value="GM" <?php echo $filter_per === 'GM' ? 'selected' : ''; ?>>⚖️ GM (Gram)</option>
                        <option value="LTR" <?php echo $filter_per === 'LTR' ? 'selected' : ''; ?>>💧 LTR (Liter)</option>
                        <option value="DOZEN" <?php echo $filter_per === 'DOZEN' ? 'selected' : ''; ?>>🥚 DOZEN (Dozen)</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Apply Filters</button>
                    <a href="category_dashboard.php" class="btn btn-outline btn-sm">🔄 Clear All</a>
                    <a href="category_add.php" class="btn btn-success btn-sm">➕ Add Category</a>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="data-table" id="catTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Unit</th>
                        <th>Price (₹)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res && $res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()): 
                            $unit_icon = match($row['price_per']) {
                                'KG' => '⚖️',
                                'PC' => '📦',
                                'GM' => '⚖️',
                                'LTR' => '💧',
                                'DOZEN' => '🥚',
                                default => '📏'
                            };
                        ?>
                            <tr>
                                <td data-label="#"><?php echo $row['id']; ?> </td>
                                <td data-label="Category Name" class="category-name">
                                    <?php echo htmlspecialchars($row['category_name']); ?>
                                 </td>
                                <td data-label="Unit">
                                    <span class="unit-badge"><?php echo $unit_icon; ?> <?php echo $row['price_per']; ?></span>
                                 </td>
                                <td data-label="Price" class="price-cell">
                                    <strong>₹<?php echo number_format($row['price'], 2); ?></strong>
                                    <span class="per-unit">/<?php echo strtolower($row['price_per']); ?></span>
                                 </td>
                                <td data-label="Actions" class="actions-cell">
                                    <a href="?edit=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_per ? '&per=' . urlencode($filter_per) : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn edit-btn" title="Edit">✏️</a>
                                    <a href="?delete=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_per ? '&per=' . urlencode($filter_per) : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Delete this category? This may affect products under it.')">🗑️</a>
                                 </td>
                             </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-row">
                                📭 No categories found. 
                                <a href="category_add.php">Add your first category</a>
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
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> categories
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_per ? '&per=' . urlencode($filter_per) : ''; ?>" class="page-link">← Previous</a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_per ? '&per=' . urlencode($filter_per) : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_per ? '&per=' . urlencode($filter_per) : ''; ?>" class="page-link">Next →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats Table -->
    <div class="quick-stats-card">
        <div class="quick-stats-header">
            <span class="quick-stats-icon">📊</span>
            <span class="quick-stats-title">Category Distribution by Unit</span>
        </div>
        <div class="unit-distribution">
            <div class="unit-item">
                <span class="unit-name">⚖️ KG</span>
                <div class="unit-bar-container">
                    <div class="unit-bar" style="width: <?php echo $stats['total_categories'] > 0 ? ($stats['kg_count'] / $stats['total_categories'] * 100) : 0; ?>%; background: #1e4663;"></div>
                </div>
                <span class="unit-count"><?php echo $stats['kg_count']; ?></span>
            </div>
            <div class="unit-item">
                <span class="unit-name">📦 PC</span>
                <div class="unit-bar-container">
                    <div class="unit-bar" style="width: <?php echo $stats['total_categories'] > 0 ? ($stats['pc_count'] / $stats['total_categories'] * 100) : 0; ?>%; background: #2e7d32;"></div>
                </div>
                <span class="unit-count"><?php echo $stats['pc_count']; ?></span>
            </div>
            <div class="unit-item">
                <span class="unit-name">⚖️ GM</span>
                <div class="unit-bar-container">
                    <div class="unit-bar" style="width: <?php echo $stats['total_categories'] > 0 ? ($stats['gm_count'] / $stats['total_categories'] * 100) : 0; ?>%; background: #0288d1;"></div>
                </div>
                <span class="unit-count"><?php echo $stats['gm_count']; ?></span>
            </div>
            <div class="unit-item">
                <span class="unit-name">💧 LTR</span>
                <div class="unit-bar-container">
                    <div class="unit-bar" style="width: <?php echo $stats['total_categories'] > 0 ? ($stats['ltr_count'] / $stats['total_categories'] * 100) : 0; ?>%; background: #f57c00;"></div>
                </div>
                <span class="unit-count"><?php echo $stats['ltr_count']; ?></span>
            </div>
            <div class="unit-item">
                <span class="unit-name">🥚 DOZEN</span>
                <div class="unit-bar-container">
                    <div class="unit-bar" style="width: <?php echo $stats['total_categories'] > 0 ? ($stats['dozen_count'] / $stats['total_categories'] * 100) : 0; ?>%; background: #c62828;"></div>
                </div>
                <span class="unit-count"><?php echo $stats['dozen_count']; ?></span>
            </div>
        </div>
    </div>
</div>

<style>
/* ========== CATEGORY DASHBOARD SPECIFIC STYLES ========== */

.category-dashboard {
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

.category-name {
    font-weight: 600;
    color: #1e4663;
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

.price-cell strong {
    color: #1e4663;
    font-size: 14px;
}

.per-unit {
    font-size: 10px;
    color: #94a3b8;
    margin-left: 2px;
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

/* Quick Stats Card */
.quick-stats-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 20px 24px;
    margin-top: 24px;
    border: 1px solid #eef2f8;
}

.quick-stats-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eef2f8;
}

.quick-stats-icon {
    font-size: 20px;
}

.quick-stats-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e4663;
}

.unit-distribution {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.unit-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.unit-name {
    width: 70px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.unit-bar-container {
    flex: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.unit-bar {
    height: 100%;
    border-radius: 10px;
    transition: width 0.5s ease;
}

.unit-count {
    width: 40px;
    font-size: 13px;
    font-weight: 600;
    color: #1e4663;
    text-align: right;
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
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideIn 0.3s ease;
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

.form-group {
    margin-bottom: 16px;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
    display: block;
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
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

/* Premium Card */
.premium-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #eef2f8;
    overflow: hidden;
    padding: 0;
}

.card-header {
    padding: 22px 28px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #eef2f8;
    display: flex;
    align-items: center;
    gap: 14px;
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

.btn {
    text-decoration: none !important;
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
    
    .card-header {
        flex-direction: row;
        padding: 16px 20px;
    }
    
    .card-header-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
    }
    
    .card-header h3 {
        font-size: 18px;
    }
    
    .modal-container {
        margin: 16px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .unit-distribution {
        gap: 10px;
    }
    
    .unit-name {
        width: 60px;
        font-size: 12px;
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
    
    .quick-stats-card {
        padding: 16px 20px;
    }
    
    .unit-name {
        width: 55px;
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