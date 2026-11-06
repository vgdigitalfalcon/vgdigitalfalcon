<?php
require_once 'config.php';
requireLogin();
$page_title = 'Add Stock';
$msg = '';

// Fetch categories for dropdown
$cats = $conn->query("SELECT * FROM product_categories ORDER BY category_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id     = intval($_POST['category_id']);
    $stock_per  = sanitize($conn, $_POST['stock_per']);
    $new_stock  = floatval($_POST['new_stock']);
    $batch_date = sanitize($conn, $_POST['batch_date']);
    $purchase_price = isset($_POST['purchase_price']) ? floatval($_POST['purchase_price']) : 0;

    // Get category details
    $cr = $conn->query("SELECT * FROM product_categories WHERE id=$cat_id");
    if ($cr && $cr->num_rows > 0) {
        $cat = $cr->fetch_assoc();
        
        // Use custom purchase price if provided, otherwise use category price
        $cost_per_unit = $purchase_price > 0 ? $purchase_price : $cat['price'];
        $total_cost    = $cost_per_unit * $new_stock;
        
        $conn->query("INSERT INTO stock (category_id, stock_per, new_stock, batch_date, cost_per_unit, total_cost, remaining_stock)
                      VALUES ($cat_id, '$stock_per', $new_stock, '$batch_date', $cost_per_unit, $total_cost, $new_stock)");
        
        $msg = '<div class="alert alert-success">✅ Stock added successfully! 
                <br><small>Batch: ' . date('d M Y', strtotime($batch_date)) . ' | Qty: ' . number_format($new_stock, 2) . ' ' . $stock_per . ' | Cost: ₹' . number_format($total_cost, 2) . '</small>
                <a href="stock_dashboard.php" style="color:#2e7d32;text-decoration:underline;display:inline-block;margin-top:5px;">View all stock →</a></div>';
        
        // Clear form values
        $_POST = array();
    } else {
        $msg = '<div class="alert alert-error">❌ Invalid category selected.</div>';
    }
}

// Get total stock count
$total_stock = $conn->query("SELECT SUM(remaining_stock) as total FROM stock")->fetch_assoc()['total'] ?? 0;

include 'sidebar.php';
?>

<!-- Main Content Area -->
<div class="content-wrapper">
    <?php echo $msg; ?>
    
   

    <!-- Add Stock Form Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">📥</div>
            <div>
                <h3>Add New Stock</h3>
                <p class="card-subtitle">Record new inventory stock with batch details</p>
            </div>
        </div>
        
        <form method="POST" class="premium-form" id="stockForm" autocomplete="off">
            <!-- Row 1: Category Selection -->
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">
                        <span class="label-icon">🏷️</span>
                        Select Product Category *
                    </label>
                    <select name="category_id" id="catSelect" required class="form-select" onchange="updateStockCalc()">
                        <option value="">-- Select Category --</option>
                        <?php
                        $cats->data_seek(0);
                        while ($c = $cats->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" 
                                    data-price="<?php echo $c['price']; ?>" 
                                    data-per="<?php echo $c['price_per']; ?>"
                                    data-name="<?php echo htmlspecialchars($c['category_name']); ?>"
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['category_name']); ?> (₹<?php echo number_format($c['price'], 2); ?>/<?php echo $c['price_per']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-hint">Select the product category for this stock</small>
                </div>
            </div>

            <!-- Row 2: Stock Per & Batch Date -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">⚖️</span>
                        Unit Type *
                    </label>
                    <select name="stock_per" id="stockPer" required class="form-select">
                        <option value="KG" <?php echo (isset($_POST['stock_per']) && $_POST['stock_per'] == 'KG') ? 'selected' : ''; ?>>⚖️ KG (Kilogram)</option>
                        <option value="PC" <?php echo (isset($_POST['stock_per']) && $_POST['stock_per'] == 'PC') ? 'selected' : ''; ?>>📦 PC (Piece)</option>
                        <option value="GM" <?php echo (isset($_POST['stock_per']) && $_POST['stock_per'] == 'GM') ? 'selected' : ''; ?>>⚖️ GM (Gram)</option>
                        <option value="LTR" <?php echo (isset($_POST['stock_per']) && $_POST['stock_per'] == 'LTR') ? 'selected' : ''; ?>>💧 LTR (Liter)</option>
                    </select>
                    <small class="form-hint">Unit of measurement for this stock</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📅</span>
                        Batch Date *
                    </label>
                    <input type="date" name="batch_date" required 
                           value="<?php echo isset($_POST['batch_date']) ? $_POST['batch_date'] : date('Y-m-d'); ?>" 
                           class="form-input">
                    <small class="form-hint">Date of manufacturing/batch</small>
                </div>
            </div>

            <!-- Row 3: New Stock Quantity & Purchase Price -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📊</span>
                        Stock Quantity *
                    </label>
                    <input type="number" step="0.001" name="new_stock" id="newStock" required 
                           value="<?php echo isset($_POST['new_stock']) ? htmlspecialchars($_POST['new_stock']) : ''; ?>"
                           placeholder="0.000" min="0.001" class="form-input" oninput="updateStockCalc()">
                    <small class="form-hint">Enter the quantity being added</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">💰</span>
                        Purchase Price (₹ per unit)
                    </label>
                    <input type="number" step="0.01" name="purchase_price" id="purchasePrice"
                           value="<?php echo isset($_POST['purchase_price']) ? htmlspecialchars($_POST['purchase_price']) : ''; ?>"
                           placeholder="Auto from category" min="0" class="form-input" oninput="updateStockCalc()">
                    <small class="form-hint">Optional - Override category price</small>
                </div>
            </div>

            <!-- Row 4: Cost Calculation Display -->
            <div class="cost-calculation" id="costCalc" style="display: none;">
                <div class="calculation-card">
                    <div class="calc-header">
                        <span>💰 Cost Calculation</span>
                    </div>
                    <div class="calc-details">
                        <div class="calc-item">
                            <span class="calc-label">Unit Price:</span>
                            <span class="calc-value" id="unitPriceDisplay">₹0.00</span>
                        </div>
                        <div class="calc-item">
                            <span class="calc-label">Quantity:</span>
                            <span class="calc-value" id="qtyDisplay">0</span>
                        </div>
                        <div class="calc-item calc-total">
                            <span class="calc-label">Total Cost:</span>
                            <span class="calc-value" id="totalCostDisplay">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">📥</span>
                    Add Stock
                </button>
                <a href="stock_dashboard.php" class="btn btn-secondary">
                    <span class="btn-icon">📊</span>
                    View Stock Dashboard
                </a>
                <button type="reset" class="btn btn-outline" onclick="resetForm()">
                    <span class="btn-icon">🔄</span>
                    Clear Form
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Stock Card (shows when category selected) -->
    <div class="card existing-stock-card" id="oldStockCard" style="display:none;">
        <div class="card-header-small">
            <div class="card-header-icon-small">📦</div>
            <div>
                <h4>Existing Stock for Selected Category</h4>
                <p class="card-subtitle-small">Current inventory levels</p>
            </div>
        </div>
        <div id="oldStockList" class="stock-list-container"></div>
    </div>

    <!-- Quick Tips Card -->
    <div class="tips-card">
        <div class="tips-icon">💡</div>
        <div class="tips-content">
            <strong>Stock Management Tips:</strong>
            <ul>
                <li>Always verify the batch date before adding stock</li>
                <li>Purchase price overrides the category default price if provided</li>
                <li>Stock is added to remaining inventory automatically</li>
                <li>Track multiple batches for better inventory management</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* ========== STOCK ADD PAGE SPECIFIC STYLES ========== */

/* Content Wrapper */
.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}

/* Summary Cards Row */
.summary-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}

.summary-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid #eef2f8;
    transition: all 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.summary-icon {
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

.summary-info {
    flex: 1;
}

.summary-label {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 4px;
}

.summary-value {
    font-size: 24px;
    font-weight: 800;
    color: #1e4663;
    display: block;
    line-height: 1.2;
}

.summary-sub {
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

/* Premium Form */
.premium-form {
    padding: 28px;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}

.form-group-full {
    grid-column: span 2;
}

/* Form Elements */
.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    letter-spacing: 0.3px;
}

.label-icon {
    font-size: 14px;
}

.form-input,
.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    font-family: 'Inter', 'Segoe UI', sans-serif;
    transition: all 0.2s ease;
    background: #ffffff;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
}

.form-input:hover,
.form-select:hover {
    border-color: #94a3b8;
}

.form-hint {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
    display: block;
}

/* Cost Calculation Card */
.cost-calculation {
    margin-bottom: 24px;
}

.calculation-card {
    background: linear-gradient(135deg, #e8f0fe 0%, #f0f4fa 100%);
    border-radius: 16px;
    padding: 16px 20px;
    border-left: 4px solid #1e4663;
}

.calc-header {
    font-size: 14px;
    font-weight: 700;
    color: #1e4663;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(30, 70, 99, 0.2);
}

.calc-details {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.calc-item {
    flex: 1;
    min-width: 120px;
}

.calc-label {
    font-size: 11px;
    color: #64748b;
    display: block;
    margin-bottom: 4px;
}

.calc-value {
    font-size: 16px;
    font-weight: 700;
    color: #1e4663;
}

.calc-total .calc-value {
    font-size: 20px;
    color: #c62828;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 8px;
    padding-top: 24px;
    border-top: 1px solid #eef2f8;
}

.btn-large {
    padding: 12px 28px;
    font-size: 14px;
}

.btn-icon {
    margin-right: 8px;
}

.btn-secondary {
    background: #f1f5f9;
    color: #1e4663;
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
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

/* Existing Stock Card */
.existing-stock-card {
    margin-top: 0;
    margin-bottom: 24px;
    border: 1px solid #eef2f8;
    border-radius: 20px;
    overflow: hidden;
}

.card-header-small {
    padding: 16px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #eef2f8;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header-icon-small {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #1e4663 0%, #2c6288 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.card-header-small h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1e4663;
    margin: 0 0 2px 0;
}

.card-subtitle-small {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.stock-list-container {
    padding: 16px 20px;
}

.stock-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #eef2f8;
    flex-wrap: wrap;
    gap: 10px;
}

.stock-item:last-child {
    border-bottom: none;
}

.stock-batch {
    font-weight: 600;
    color: #1e4663;
}

.stock-qty {
    background: #e8f5e9;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #2e7d32;
}

.stock-date {
    font-size: 12px;
    color: #64748b;
}

.empty-stock {
    text-align: center;
    padding: 20px;
    color: #94a3b8;
}

/* Tips Card */
.tips-card {
    background: linear-gradient(135deg, #e8f0fe 0%, #f0f4fa 100%);
    border-radius: 20px;
    padding: 18px 24px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    border-left: 4px solid #ffd966;
}

.tips-icon {
    font-size: 28px;
}

.tips-content {
    flex: 1;
}

.btn {
    text-decoration: none !important;
}
.tips-content strong {
    display: block;
    color: #1e4663;
    margin-bottom: 8px;
    font-size: 14px;
}

.tips-content ul {
    margin: 0;
    padding-left: 20px;
}

.tips-content li {
    font-size: 13px;
    color: #475569;
    margin-bottom: 4px;
}

/* Alert Styles */
.alert {
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 24px;
    font-size: 14px;
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

.alert a {
    color: inherit;
    text-decoration: underline;
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

/* Required field indicator */
select[required] + .form-label:after,
input[required] + .form-label:after {
    content: " *";
    color: #c62828;
}

/* ========== MOBILE RESPONSIVE ========== */
@media (max-width: 768px) {
    /* Summary cards stack vertically */
    .summary-row {
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .summary-card {
        padding: 14px 18px;
    }
    
    .summary-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
    }
    
    .summary-value {
        font-size: 20px;
    }
    
    /* Form becomes single column */
    .form-grid {
        grid-template-columns: 1fr;
        gap: 18px;
        margin-bottom: 18px;
    }
    
    .form-group-full {
        grid-column: span 1;
    }
    
    .premium-form {
        padding: 20px;
    }
    
    .card-header {
        padding: 18px 20px;
        flex-direction: row;
    }
    
    .card-header-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
    }
    
    .card-header h3 {
        font-size: 18px;
    }
    
    /* Cost calculation responsive */
    .calc-details {
        flex-direction: column;
        gap: 10px;
    }
    
    .calc-item {
        min-width: auto;
    }
    
    /* Form actions stack on mobile */
    .form-actions {
        flex-direction: column;
        gap: 12px;
    }
    
    .form-actions .btn {
        width: 100%;
        text-align: center;
        justify-content: center;
        display: flex;
        align-items: center;
    }
    
    /* Input fields larger touch targets */
    .form-input,
    .form-select {
        padding: 12px 14px;
        font-size: 16px;
    }
    
    .btn-large {
        padding: 14px 20px;
    }
    
    /* Tips card responsive */
    .tips-card {
        flex-direction: column;
        padding: 16px 20px;
    }
    
    .tips-icon {
        font-size: 24px;
    }
    
    .tips-content ul {
        margin-top: 8px;
    }
    
    /* Stock list responsive */
    .stock-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}

@media (max-width: 480px) {
    .summary-card {
        padding: 12px 16px;
    }
    
    .summary-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .summary-value {
        font-size: 18px;
    }
    
    .card-header {
        padding: 14px 16px;
    }
    
    .premium-form {
        padding: 16px;
    }
    
    .form-label {
        font-size: 12px;
    }
    
    .form-input,
    .form-select {
        padding: 10px 12px;
        font-size: 14px;
    }
    
    .alert {
        padding: 12px 16px;
        font-size: 13px;
    }
    
    .calculation-card {
        padding: 12px 16px;
    }
    
    .calc-value {
        font-size: 14px;
    }
    
    .calc-total .calc-value {
        font-size: 18px;
    }
}

/* Animation for form submission */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.premium-card {
    animation: fadeIn 0.3s ease-out;
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

// Update stock calculation
function updateStockCalc() {
    var sel = document.getElementById('catSelect');
    var opt = sel.options[sel.selectedIndex];
    var categoryPrice = parseFloat(opt.getAttribute('data-price')) || 0;
    var categoryPer = opt.getAttribute('data-per') || '';
    var qty = parseFloat(document.getElementById('newStock').value) || 0;
    var purchasePrice = parseFloat(document.getElementById('purchasePrice').value) || 0;
    
    // Use purchase price if provided, otherwise use category price
    var unitPrice = purchasePrice > 0 ? purchasePrice : categoryPrice;
    var total = unitPrice * qty;
    
    // Update display
    var unitPriceDisplay = document.getElementById('unitPriceDisplay');
    var qtyDisplay = document.getElementById('qtyDisplay');
    var totalCostDisplay = document.getElementById('totalCostDisplay');
    var costCalc = document.getElementById('costCalc');
    
    if (sel.value && qty > 0) {
        costCalc.style.display = 'block';
        unitPriceDisplay.innerHTML = '₹' + unitPrice.toFixed(2) + (categoryPer ? '/' + categoryPer : '');
        qtyDisplay.innerHTML = qty.toFixed(3) + ' ' + (document.getElementById('stockPer').value || 'units');
        totalCostDisplay.innerHTML = '₹' + total.toFixed(2);
        
        // Highlight if using custom price
        if (purchasePrice > 0) {
            unitPriceDisplay.style.color = '#f57c00';
            unitPriceDisplay.style.fontWeight = 'bold';
        } else {
            unitPriceDisplay.style.color = '#1e4663';
            unitPriceDisplay.style.fontWeight = 'normal';
        }
    } else {
        costCalc.style.display = 'none';
    }
    
    // Load old stock
    var catId = sel.value;
    if (catId) {
        fetch('ajax_old_stock.php?cat_id=' + catId)
            .then(r => r.text())
            .then(html => {
                document.getElementById('oldStockCard').style.display = 'block';
                document.getElementById('oldStockList').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('oldStockCard').style.display = 'block';
                document.getElementById('oldStockList').innerHTML = '<div class="empty-stock">📭 No existing stock found for this category.</div>';
            });
    } else {
        document.getElementById('oldStockCard').style.display = 'none';
    }
}

// Reset form function
function resetForm() {
    if (confirm('Clear all form fields?')) {
        document.getElementById('stockForm').reset();
        document.getElementById('costCalc').style.display = 'none';
        document.getElementById('oldStockCard').style.display = 'none';
        document.getElementById('catSelect').value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set default batch date
    const batchDateInput = document.querySelector('input[name="batch_date"]');
    if (batchDateInput && !batchDateInput.value) {
        batchDateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Trigger calculation if category is pre-selected
    if (document.getElementById('catSelect').value) {
        updateStockCalc();
    }
    
    // Add event listeners for real-time calculation
    document.getElementById('newStock').addEventListener('input', updateStockCalc);
    document.getElementById('purchasePrice').addEventListener('input', updateStockCalc);
    document.getElementById('stockPer').addEventListener('change', updateStockCalc);
});
</script>

<?php include 'sidebar_close.php'; ?>