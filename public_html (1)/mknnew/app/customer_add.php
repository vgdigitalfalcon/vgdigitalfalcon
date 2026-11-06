<?php
require_once 'config.php';
requireLogin();
$page_title = 'Add Customer';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($conn, $_POST['customer_name']);
    $shop  = sanitize($conn, $_POST['shop_name']);
    $addr  = sanitize($conn, $_POST['address']);
    $city  = sanitize($conn, $_POST['city']);
    $state = sanitize($conn, $_POST['state']);
    $gst   = sanitize($conn, $_POST['gst_no']);
    $phone = sanitize($conn, $_POST['phone']);
    $email = sanitize($conn, $_POST['email']);

    // Additional validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Customer Name is required.";
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!empty($phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
        $errors[] = "Invalid phone number format.";
    }

    if (empty($errors)) {
        $conn->query("INSERT INTO customers (customer_name, shop_name, address, city, state, gst_no, phone, email)
                      VALUES ('$name','$shop','$addr','$city','$state','$gst','$phone','$email')");
        $msg = '<div class="alert alert-success">✅ Customer added successfully! <a href="customer_dashboard.php" style="color:#2e7d32;text-decoration:underline;">View all customers →</a></div>';
        
        // Clear form on success (optional - uncomment if needed)
        // $_POST = array();
    } else {
        $error_msg = '<div class="alert alert-error">❌ ' . implode('<br>', $errors) . '</div>';
        $msg = $error_msg;
    }
}

// Get customer count for stats
$count_result = $conn->query("SELECT COUNT(*) as total FROM customers");
$total_customers = $count_result->fetch_assoc()['total'];

include 'sidebar.php';
?>

<!-- Main Content Area -->
<div class="content-wrapper">
    <?php echo $msg; ?>
    
   

    <!-- Add Customer Form Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">👤</div>
            <div>
                <h3>Add New Customer</h3>
                <p class="card-subtitle">Enter customer details to create a new record</p>
            </div>
        </div>
        
        <form method="POST" class="premium-form" id="customerForm">
            <!-- Row 1: Customer Name & Shop Name -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">👤</span>
                        Customer Name *
                    </label>
                    <input type="text" name="customer_name" required 
                           value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>"
                           placeholder="Enter full name" class="form-input" autocomplete="off">
                    <small class="form-hint">Full name of the customer</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🏪</span>
                        Shop / Business Name
                    </label>
                    <input type="text" name="shop_name" 
                           value="<?php echo isset($_POST['shop_name']) ? htmlspecialchars($_POST['shop_name']) : ''; ?>"
                           placeholder="Enter shop or business name" class="form-input" autocomplete="off">
                    <small class="form-hint">Optional - for business customers</small>
                </div>
            </div>

            <!-- Row 2: Address (Full Width) -->
            <div class="form-grid single-col">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📍</span>
                        Address
                    </label>
                    <textarea name="address" placeholder="Street address, landmark, etc." 
                              class="form-textarea" rows="2"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    <small class="form-hint">Complete address for delivery/billing</small>
                </div>
            </div>

            <!-- Row 3: City, State, GST No -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🏙️</span>
                        City
                    </label>
                    <input type="text" name="city" 
                           value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>"
                           placeholder="Enter city name" class="form-input" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🗺️</span>
                        State
                    </label>
                    <input type="text" name="state" 
                           value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>"
                           placeholder="Enter state name" class="form-input" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🏷️</span>
                        GST Number
                    </label>
                    <input type="text" name="gst_no" 
                           value="<?php echo isset($_POST['gst_no']) ? htmlspecialchars($_POST['gst_no']) : ''; ?>"
                           placeholder="XX-XXXX-XXXX" class="form-input" autocomplete="off">
                    <small class="form-hint">Required for GST billing</small>
                </div>
            </div>

            <!-- Row 4: Phone & Email -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📞</span>
                        Phone Number
                    </label>
                    <input type="tel" name="phone" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                           placeholder="+91 XXXXXXXXXX" class="form-input" autocomplete="off">
                    <small class="form-hint">10-digit mobile number</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">✉️</span>
                        Email Address
                    </label>
                    <input type="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="customer@example.com" class="form-input" autocomplete="off">
                    <small class="form-hint">For invoices and communication</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">👤</span>
                    Add Customer
                </button>
                <a href="customer_dashboard.php" class="btn btn-secondary">
                    <span class="btn-icon">📋</span>
                    View All Customers
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
            <strong>Quick Tips:</strong>
            <ul>
                <li>Customer Name is mandatory - all other fields are optional</li>
                <li>GST Number helps with tax invoices for business customers</li>
                <li>You can add more details later by editing the customer profile</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* ========== CUSTOMER ADD PAGE SPECIFIC STYLES ========== */

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
.form-select,
.form-textarea {
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
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
}

.form-input:hover,
.form-select:hover,
.form-textarea:hover {
    border-color: #94a3b8;
}

.form-hint {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
    display: block;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 32px;
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
    .form-select,
    .form-textarea {
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
    .form-select,
    .form-textarea {
        padding: 10px 12px;
        font-size: 14px;
    }
    
    .alert {
        padding: 12px 16px;
        font-size: 13px;
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

/* Required field indicator */
.form-label:has(.label-icon):after {
    content: " *";
    color: #c62828;
    font-weight: 700;
}

/* Remove asterisk for non-required fields */
.form-group:not(:first-child) .form-label:after {
    content: "";
}

/* But first child (Customer Name) is required */
.form-group:first-child .form-label:after {
    content: " *";
    color: #c62828;
}

/* Adjust for the specific selector - customer name field */
.form-group:first-child .form-label:after {
    content: " *";
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

// Reset form function
function resetForm() {
    if (confirm('Clear all form fields?')) {
        document.getElementById('customerForm').reset();
    }
}

// Optional: GST Number formatting (auto-uppercase)
document.addEventListener('DOMContentLoaded', function() {
    const gstInput = document.querySelector('input[name="gst_no"]');
    if (gstInput) {
        gstInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }
    
    // Phone number validation on input
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
        });
    }
});
</script>

<?php include 'sidebar_close.php'; ?>