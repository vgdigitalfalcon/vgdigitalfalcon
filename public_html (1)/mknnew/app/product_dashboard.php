<?php
require_once 'config.php';
requireLogin();
$page_title = 'Product Dashboard';
$msg = '';

// Handle delete with safety check
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if product has stock entries
    $check_stock = $conn->query("SELECT id FROM stock WHERE product_id = $id LIMIT 1");
    if ($check_stock && $check_stock->num_rows > 0) {
        $msg = '<div class="alert alert-error">❌ Cannot delete product. It has stock records associated.</div>';
    } else {
        $conn->query("DELETE FROM products WHERE id=$id");
        $msg = '<div class="alert alert-success">✅ Product deleted successfully.</div>';
    }
}

// Get edit data
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_data = null;
if ($edit_id) {
    $r = $conn->query("SELECT * FROM products WHERE id=$edit_id");
    if ($r) $edit_data = $r->fetch_assoc();
}

// Handle edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id      = intval($_POST['edit_id']);
    $cat     = intval($_POST['category_id']);
    $code    = sanitize($conn, $_POST['product_code']);
    $hsn     = sanitize($conn, $_POST['hsn_code']);
    $name    = sanitize($conn, $_POST['product_name']);
    $weight  = floatval($_POST['piece_weight']);
    $packing = sanitize($conn, $_POST['packing_mode']);
    $price   = floatval($_POST['price']);
    
    if (!empty($name) && $price > 0 && $cat > 0) {
        $conn->query("UPDATE products SET category_id=$cat, product_code='$code', hsn_code='$hsn',
                      product_name='$name', piece_weight=$weight, packing_mode='$packing', price=$price WHERE id=$id");
        $msg = '<div class="alert alert-success">✅ Product updated successfully.</div>';
        $edit_id = 0;
        $edit_data = null;
    } else {
        $msg = '<div class="alert alert-error">❌ Category, Product Name, and valid price are required.</div>';
    }
}

// View modal data
$view_id = isset($_GET['view']) ? intval($_GET['view']) : 0;
$view_data = null;
if ($view_id) {
    $r = $conn->query("SELECT p.*, pc.category_name, pc.price_per, pc.price as category_price 
                       FROM products p 
                       JOIN product_categories pc ON p.category_id = pc.id 
                       WHERE p.id = $view_id");
    if ($r) $view_data = $r->fetch_assoc();
}

// Get filter parameters
$search_term = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where_clauses = [];
if ($search_term) {
    $where_clauses[] = "(p.product_name LIKE '%$search_term%' OR p.product_code LIKE '%$search_term%' OR p.hsn_code LIKE '%$search_term%')";
}
if ($filter_category) {
    $where_clauses[] = "p.category_id = $filter_category";
}
$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count for pagination
$count_result = $conn->query("SELECT COUNT(*) as total FROM products p $where_sql");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Fetch products with pagination
$res = $conn->query("SELECT p.*, pc.category_name, pc.price_per 
                     FROM products p 
                     LEFT JOIN product_categories pc ON p.category_id = pc.id 
                     $where_sql 
                     ORDER BY pc.category_name, p.product_name 
                     LIMIT $limit OFFSET $offset");

// Get categories for filters
$cats = $conn->query("SELECT * FROM product_categories ORDER BY category_name");
$cats_arr = [];
while ($c = $cats->fetch_assoc()) {
    $cats_arr[$c['id']] = $c;
}

// Get statistics
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_products,
    COUNT(DISTINCT category_id) as total_categories,
    SUM(price) as total_value,
    AVG(price) as avg_price,
    MIN(price) as min_price,
    MAX(price) as max_price
FROM products");
$stats = $stats_query->fetch_assoc();



include 'sidebar.php';
?>

<!-- Main Content -->
<div class="product-dashboard">
    <?php echo $msg; ?>

    <!-- View Modal -->
    <?php if ($view_data): ?>
    <div class="modal-overlay" id="viewModal">
        <div class="modal-container modal-view">
            <div class="modal-header">
                <div class="modal-icon">👁️</div>
                <div>
                    <h3>Product Details</h3>
                    <p class="modal-subtitle">Complete product information</p>
                </div>
                <a href="product_dashboard.php" class="modal-close">&times;</a>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">📦 Category</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['category_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">🔢 Product Code</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['product_code']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📋 HSN Code</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['hsn_code']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item full-width">
                        <span class="detail-label">📝 Product Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['product_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">⚖️ Piece Weight</span>
                        <span class="detail-value"><?php echo $view_data['piece_weight'] ? number_format($view_data['piece_weight'], 3) . ' g' : '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📦 Packing Mode</span>
                        <span class="detail-value"><?php echo htmlspecialchars($view_data['packing_mode']) ?: '-'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">💰 Selling Price</span>
                        <span class="detail-value price-highlight">₹<?php echo number_format($view_data['price'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">🏷️ Category Price</span>
                        <span class="detail-value">₹<?php echo number_format($view_data['category_price'], 2); ?>/<?php echo $view_data['price_per']; ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="?edit=<?php echo $view_data['id']; ?>" class="btn btn-warning">✏️ Edit Product</a>
                <a href="product_dashboard.php" class="btn btn-secondary">Close</a>
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
                    <h3>Edit Product</h3>
                    <p class="modal-subtitle">Update product information</p>
                </div>
                <a href="product_dashboard.php" class="modal-close">&times;</a>
            </div>
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">🏷️ Category *</label>
                        <select name="category_id" required class="form-select">
                            <?php foreach ($cats_arr as $cid => $c): ?>
                                <option value="<?php echo $cid; ?>" <?php echo $edit_data['category_id'] == $cid ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">🔢 Product Code</label>
                            <input type="text" name="product_code" value="<?php echo htmlspecialchars($edit_data['product_code']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">📋 HSN Code</label>
                            <input type="text" name="hsn_code" value="<?php echo htmlspecialchars($edit_data['hsn_code']); ?>" class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">📝 Product Name *</label>
                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($edit_data['product_name']); ?>" required class="form-input">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">⚖️ Piece Weight (g)</label>
                            <input type="number" step="0.001" name="piece_weight" value="<?php echo $edit_data['piece_weight']; ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">📦 Packing Mode</label>
                            <input type="text" name="packing_mode" value="<?php echo htmlspecialchars($edit_data['packing_mode']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">💰 Price (₹) *</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $edit_data['price']; ?>" required class="form-input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">💾 Update Product</button>
                    <a href="product_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <style>.modal-overlay{display:flex;}</style>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">🧴</div>
            <div class="stat-details">
                <span class="stat-label">Total Products</span>
                <span class="stat-value"><?php echo number_format($stats['total_products']); ?></span>
                <span class="stat-sub">In inventory</span>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-icon">🏷️</div>
            <div class="stat-details">
                <span class="stat-label">Categories</span>
                <span class="stat-value"><?php echo number_format($stats['total_categories']); ?></span>
                <span class="stat-sub">Product categories</span>
            </div>
        </div>
        <div class="stat-card stat-card-info">
            <div class="stat-icon">💰</div>
            <div class="stat-details">
                <span class="stat-label">Total Value</span>
                <span class="stat-value">₹<?php echo number_format($stats['total_value'] ?? 0, 2); ?></span>
                <span class="stat-sub">Inventory value</span>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">📊</div>
            <div class="stat-details">
                <span class="stat-label">Avg. Price</span>
                <span class="stat-value">₹<?php echo number_format($stats['avg_price'] ?? 0, 2); ?></span>
                <span class="stat-sub">Per product</span>
            </div>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">📋</div>
            <div>
                <h3>All Products</h3>
                <p class="card-subtitle">Manage your product catalog</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-section">
            <form method="GET" action="" class="filter-form" id="filterForm">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="🔍 Search by name, code, HSN..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="category" class="filter-select">
                        <option value="0">All Categories</option>
                        <?php foreach ($cats_arr as $cid => $c): ?>
                            <option value="<?php echo $cid; ?>" <?php echo $filter_category == $cid ? 'selected' : ''; ?>>
                                🏷️ <?php echo htmlspecialchars($c['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Apply Filters</button>
                    <a href="product_dashboard.php" class="btn btn-outline btn-sm">🔄 Clear All</a>
                    <a href="product_add.php" class="btn btn-success btn-sm">➕ Add Product</a>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="data-table" id="prodTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>HSN</th>
                        <th>Product Name</th>
                        <th>Weight(g)</th>
                        <th>Packing</th>
                        <th>Price (₹)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res && $res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td data-label="#"><?php echo $row['id']; ?> </td>
                                <td data-label="Category">
                                    <span class="category-badge"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                 </td>
                                <td data-label="Code"><?php echo htmlspecialchars($row['product_code']) ?: '-'; ?> </td>
                                <td data-label="HSN"><?php echo htmlspecialchars($row['hsn_code']) ?: '-'; ?> </td>
                                <td data-label="Product Name" class="product-name">
                                    <?php echo htmlspecialchars($row['product_name']); ?>
                                 </td>
                                <td data-label="Weight(g)"><?php echo $row['piece_weight'] ? number_format($row['piece_weight'], 2) : '-'; ?> </td>
                                <td data-label="Packing"><?php echo htmlspecialchars($row['packing_mode']) ?: '-'; ?> </td>
                                <td data-label="Price" class="price-cell">
                                    <strong>₹<?php echo number_format($row['price'], 2); ?></strong>
                                    <span class="per-unit">/ Pkt</span>
                                 </td>
                                <td data-label="Actions" class="actions-cell">
                                    <a href="?view=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn view-btn" title="View Details">👁️</a>
                                    <a href="?edit=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn edit-btn" title="Edit">✏️</a>
                                    <a href="?delete=<?php echo $row['id']; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Delete this product? This action cannot be undone.')">🗑️</a>
                                 </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-row">
                                📭 No products found. 
                                <a href="product_add.php">Add your first product</a>
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
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> products
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?>" class="page-link">← Previous</a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                for ($i = $start_page; $i <= $end_page; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?><?php echo $filter_category ? '&category=' . $filter_category : ''; ?>" class="page-link">Next →</a>
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
            <a href="product_add.php" class="quick-action-btn">
                <span>➕</span> Add New Product
            </a>
            <a href="stock_add.php" class="quick-action-btn">
                <span>📥</span> Add Stock
            </a>
            <a href="stock_dashboard.php" class="quick-action-btn">
                <span>📊</span> View Stock
            </a>
            <a href="sales_add.php" class="quick-action-btn">
                <span>🧾</span> Create Sale
            </a>
        </div>
    </div>
</div>

<style>
/* ========== PRODUCT DASHBOARD SPECIFIC STYLES ========== */

.product-dashboard {
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

.btn {
    text-decoration: none !important;
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
    min-width: 180px;
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

.product-name {
    font-weight: 600;
    color: #1e4663;
}

.category-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #e8f0fe;
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

/* Quick Actions Card */
.quick-actions-card {
    background: #ffffff;
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
    background: #f8fafc;
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
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideIn 0.3s ease;
}

.modal-view {
    max-width: 550px;
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

.price-highlight {
    color: #1e4663;
    font-weight: 800;
    font-size: 16px;
}

/* Form Grid inside Modal */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 16px;
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