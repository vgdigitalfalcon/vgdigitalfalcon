<?php
require_once 'config.php';
requireLogin();
$page_title = 'Add Product Category';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($conn, $_POST['category_name']);
    $per   = sanitize($conn, $_POST['price_per']);
    $price = floatval($_POST['price']);
    
    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Category name is required.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than zero.";
    }
    
    if (empty($errors)) {
        // Check if category already exists
        $check = $conn->query("SELECT id FROM product_categories WHERE category_name = '$name'");
        if ($check && $check->num_rows > 0) {
            $msg = '<div class="alert alert-error">❌ Category "' . htmlspecialchars($name) . '" already exists!</div>';
        } else {
            $conn->query("INSERT INTO product_categories (category_name, price_per, price) VALUES ('$name','$per',$price)");
            $msg = '<div class="alert alert-success">✅ Product Category added successfully! <a href="category_dashboard.php" style="color:#2e7d32;text-decoration:underline;">View all categories →</a></div>';
            // Clear POST to reset form
            $_POST = array();
        }
    } else {
        $msg = '<div class="alert alert-error">❌ ' . implode('<br>', $errors) . '</div>';
    }
}

// Get category count for stats
$count_result = $conn->query("SELECT COUNT(*) as total FROM product_categories");
$total_categories = $count_result->fetch_assoc()['total'];

// Get price stats
$price_stats = $conn->query("SELECT 
    MIN(price) as min_price, 
    MAX(price) as max_price, 
    AVG(price) as avg_price 
    FROM product_categories");
$price_data = $price_stats->fetch_assoc();

include 'sidebar.php';
?>

<!-- Main Content Area -->
<div class="content-wrapper">
    <?php echo $msg; ?>
    
   

    <!-- Add Category Form Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">🏷️</div>
            <div>
                <h3>Add Product Category</h3>
                <p class="card-subtitle">Create a new product category for your inventory</p>
            </div>
        </div>
        
        <form method="POST" class="premium-form" id="categoryForm">
            <!-- Row 1: Category Name (Full Width) -->
            <div class="form-grid single-col">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📝</span>
                        Category Name *
                    </label>
                    <input type="text" name="category_name" required 
                           value="<?php echo isset($_POST['category_name']) ? htmlspecialchars($_POST['category_name']) : ''; ?>"
                           placeholder="e.g., Kadalaimittai Bits, Fried Snacks, Sweets, Bakery Items" 
                           class="form-input" autocomplete="off">
                    <small class="form-hint">Enter a unique name for the product category</small>
                </div>
            </div>

            <!-- Row 2: Price Per & Price -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">⚖️</span>
                        Price Per *
                    </label>
                    <select name="price_per" required class="form-select">
                        <option value="KG" <?php echo (isset($_POST['price_per']) && $_POST['price_per'] == 'KG') ? 'selected' : ''; ?>>⚖️ KG (Kilogram)</option>
                        <option value="PC" <?php echo (isset($_POST['price_per']) && $_POST['price_per'] == 'PC') ? 'selected' : ''; ?>>📦 PC (Piece)</option>
                        <option value="GM" <?php echo (isset($_POST['price_per']) && $_POST['price_per'] == 'GM') ? 'selected' : ''; ?>>⚖️ GM (Gram)</option>
                        <option value="LTR" <?php echo (isset($_POST['price_per']) && $_POST['price_per'] == 'LTR') ? 'selected' : ''; ?>>💧 LTR (Liter)</option>
                        <option value="DOZEN" <?php echo (isset($_POST['price_per']) && $_POST['price_per'] == 'DOZEN') ? 'selected' : ''; ?>>🥚 DOZEN (Dozen)</option>
                    </select>
                    <small class="form-hint">Unit of measurement for pricing</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">💰</span>
                        Price (₹) *
                    </label>
                    <input type="number" step="0.01" name="price" required 
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                           placeholder="0.00" min="0" class="form-input" autocomplete="off">
                    <small class="form-hint">Price per unit (KG/PC/GM/LTR/DOZEN)</small>
                </div>
            </div>

            <!-- Quick Price Suggestions -->
            <div class="price-suggestions">
                <span class="suggest-label">Quick suggestions:</span>
                <button type="button" class="suggest-btn" data-price="50">₹50</button>
                <button type="button" class="suggest-btn" data-price="100">₹100</button>
                <button type="button" class="suggest-btn" data-price="150">₹150</button>
                <button type="button" class="suggest-btn" data-price="200">₹200</button>
                <button type="button" class="suggest-btn" data-price="250">₹250</button>
                <button type="button" class="suggest-btn" data-price="500">₹500</button>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">🏷️</span>
                    Add Category
                </button>
                <a href="category_dashboard.php" class="btn btn-secondary">
                    <span class="btn-icon">📋</span>
                    View All Categories
                </a>
                <button type="reset" class="btn btn-outline" onclick="resetForm()">
                    <span class="btn-icon">🔄</span>
                    Clear Form
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Tips Card -->
    <div class="tips-card">
        <div class="tips-icon">💡</div>
        <div class="tips-content">
            <strong>Category Management Tips:</strong>
            <ul>
                <li>Create categories based on product types (Sweets, Snacks, Bakery, Beverages)</li>
                <li>Use "KG" for bulk items, "PC" for individual products</li>
                <li>Categories help organize your inventory and sales reports</li>
                <li>You can add products under these categories later</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* ========== CATEGORY ADD PAGE SPECIFIC STYLES ========== */

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

.btn {
    text-decoration: none !important;
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

/* Form Grid - Two columns on desktop */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}

.single-col {
    grid-template-columns: 1fr;
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

/* Price Suggestions */
.price-suggestions {
    background: #f8fafc;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.suggest-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
}

.suggest-btn {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 30px;
    padding: 6px 16px;
    font-size: 12px;
    font-weight: 600;
    color: #1e4663;
    cursor: pointer;
    transition: all 0.2s ease;
}

.suggest-btn:hover {
    background: #1e4663;
    border-color: #1e4663;
    color: white;
    transform: translateY(-1px);
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
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease;
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

/* Required field indicator */
.form-group:first-child .form-label:after {
    content: " *";
    color: #c62828;
    font-weight: 700;
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
    
    /* Price suggestions responsive */
    .price-suggestions {
        padding: 12px 16px;
    }
    
    .suggest-btn {
        padding: 5px 12px;
        font-size: 11px;
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
        font-size: 16px; /* Prevents zoom on iOS */
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
    
    .price-suggestions {
        gap: 8px;
    }
    
    .suggest-btn {
        padding: 4px 10px;
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

// Price suggestion buttons
document.querySelectorAll('.suggest-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const price = this.getAttribute('data-price');
        const priceInput = document.querySelector('input[name="price"]');
        if (priceInput) {
            priceInput.value = price;
            priceInput.focus();
            // Add a subtle highlight effect
            priceInput.style.backgroundColor = '#e8f0fe';
            setTimeout(() => {
                priceInput.style.backgroundColor = '';
            }, 500);
        }
    });
});

// Reset form function
function resetForm() {
    if (confirm('Clear all form fields?')) {
        document.getElementById('categoryForm').reset();
    }
}

// Capitalize first letter of category name
document.addEventListener('DOMContentLoaded', function() {
    const categoryInput = document.querySelector('input[name="category_name"]');
    if (categoryInput) {
        categoryInput.addEventListener('blur', function() {
            if (this.value) {
                this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            }
        });
    }
});
</script>

<?php include 'sidebar_close.php'; ?>