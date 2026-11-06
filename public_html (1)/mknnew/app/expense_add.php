<?php
require_once 'config.php';
requireLogin();
$page_title = 'Add Expense';
$msg = '';

// Handle form submission with validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount         = floatval($_POST['amount']);
    $reason         = sanitize($conn, $_POST['reason']);
    $spent_by       = intval($_POST['spent_by']);
    $status         = sanitize($conn, $_POST['payment_status']);
    $partial_amount = ($status === 'Partial Paid') ? floatval($_POST['partial_amount']) : 0;
    $exp_date       = sanitize($conn, $_POST['expense_date']);
    $remarks        = sanitize($conn, $_POST['remarks']);
    
    // Additional validation
    if ($amount <= 0) {
        $msg = '<div class="alert alert-error">❌ Amount must be greater than zero!</div>';
    } elseif ($status === 'Partial Paid' && $partial_amount > $amount) {
        $msg = '<div class="alert alert-error">❌ Partial amount cannot exceed total amount!</div>';
    } else {
        $conn->query("INSERT INTO expenses (amount,reason,spent_by,payment_status,partial_amount,expense_date,remarks)
                      VALUES ($amount,'$reason',$spent_by,'$status',$partial_amount,'$exp_date','$remarks')");
        $msg = '<div class="alert alert-success">✅ Expense added successfully! <a href="expense_dashboard.php" style="color:#2e7d32;text-decoration:underline;">View all expenses →</a></div>';
    }
}

$users = $conn->query("SELECT id, full_name FROM users ORDER BY full_name");
include 'sidebar.php';
?>

<!-- Main Content Area -->
<div class="content-wrapper">
    <?php echo $msg; ?>
    
   

    <!-- Add Expense Form Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">➕</div>
            <div>
                <h3>Add New Expense</h3>
                <p class="card-subtitle">Record expense details accurately</p>
            </div>
        </div>
        
        <form method="POST" id="expForm" class="premium-form">
            <!-- Row 1: Amount & Expense Date -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">₹</span>
                        Expense Amount *
                    </label>
                    <input type="number" step="0.01" name="amount" id="amt" required min="0.01" 
                           placeholder="0.00" class="form-input" autocomplete="off">
                    <small class="form-hint">Enter the total expense amount</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📅</span>
                        Expense Date *
                    </label>
                    <input type="date" name="expense_date" required value="<?php echo date('Y-m-d'); ?>" 
                           class="form-input">
                </div>
            </div>

            <!-- Row 2: Reason & Spent By -->
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">
                        <span class="label-icon">📝</span>
                        Reason / Description *
                    </label>
                    <input type="text" name="reason" required placeholder="e.g., Office Supplies, Travel, Maintenance..." 
                           class="form-input" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">👤</span>
                        Expense Spent By *
                    </label>
                    <select name="spent_by" required class="form-select">
                        <option value="">-- Select User --</option>
                        <?php while($u = $users->fetch_assoc()): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- Row 3: Payment Status with dynamic fields -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">💳</span>
                        Payment Status *
                    </label>
                    <select name="payment_status" id="payStatus" required class="form-select" onchange="togglePartial(this.value)">
                        <option value="Paid">✅ Paid - Fully Settled</option>
                        <option value="Unpaid">⏳ Unpaid - Pending</option>
                        <option value="Partial Paid">🔸 Partial Paid - Installment</option>
                    </select>
                </div>
                
                <div id="partialDiv" class="form-group" style="display:none;">
                    <label class="form-label">
                        <span class="label-icon">💸</span>
                        Partial Amount Paid (₹)
                    </label>
                    <input type="number" step="0.01" name="partial_amount" id="partAmt" min="0" 
                           placeholder="0.00" class="form-input" oninput="calcBalance()">
                </div>
                
                <div id="balanceDiv" class="form-group" style="display:none;">
                    <label class="form-label">
                        <span class="label-icon">⚖️</span>
                        Balance Amount
                    </label>
                    <input type="text" id="balanceAmt" readonly class="form-input balance-field" 
                           placeholder="₹ 0.00">
                </div>
            </div>

            <!-- Row 4: Remarks (full width) -->
            <div class="form-grid single-col">
                <div class="form-group form-group-full">
                    <label class="form-label">
                        <span class="label-icon">📌</span>
                        Additional Remarks
                    </label>
                    <textarea name="remarks" placeholder="Any additional notes or comments about this expense..." 
                              class="form-textarea" rows="3"></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">➕</span>
                    Add Expense
                </button>
                <a href="expense_dashboard.php" class="btn btn-secondary">
                    <span class="btn-icon">📋</span>
                    View All Expenses
                </a>
                <button type="reset" class="btn btn-outline" onclick="resetForm()">
                    <span class="btn-icon">🔄</span>
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ========== EXPENSE ADD PAGE SPECIFIC STYLES ========== */

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
    font-size: 22px;
    font-weight: 700;
    color: #1e4663;
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

.form-group-full {
    grid-column: span 1;
}

/* Form Elements */
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

.balance-field {
    background: #f1f5f9;
    font-weight: 700;
    color: #c0392b;
    border-color: #fee2e2;
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

/* Alert Styles */
.alert {
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
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
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .summary-value {
        font-size: 18px;
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
    }
    
    .card-header-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
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
}

@media (max-width: 480px) {
    .summary-card {
        flex-direction: row;
        align-items: center;
    }
    
    .card-header {
        flex-direction: column;
        text-align: center;
    }
    
    .form-label {
        font-size: 12px;
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
function togglePartial(val) {
    const partialDiv = document.getElementById('partialDiv');
    const balanceDiv = document.getElementById('balanceDiv');
    const isPartial = (val === 'Partial Paid');
    
    partialDiv.style.display = isPartial ? 'block' : 'none';
    balanceDiv.style.display = isPartial ? 'block' : 'none';
    
    if (isPartial) {
        document.getElementById('partAmt').required = true;
    } else {
        document.getElementById('partAmt').required = false;
        document.getElementById('partAmt').value = '';
        document.getElementById('balanceAmt').value = '';
    }
    calcBalance();
}

function calcBalance() {
    var total = parseFloat(document.getElementById('amt').value) || 0;
    var partial = parseFloat(document.getElementById('partAmt').value) || 0;
    var balance = total - partial;
    
    document.getElementById('balanceAmt').value = '₹ ' + balance.toFixed(2);
    
    // Add visual feedback if partial exceeds total
    const balanceField = document.getElementById('balanceAmt');
    if (partial > total && total > 0) {
        balanceField.style.color = '#c62828';
        balanceField.style.fontWeight = 'bold';
    } else {
        balanceField.style.color = '#1e4663';
    }
}

function resetForm() {
    document.getElementById('expForm').reset();
    document.getElementById('partialDiv').style.display = 'none';
    document.getElementById('balanceDiv').style.display = 'none';
    document.getElementById('balanceAmt').value = '';
    document.getElementById('payStatus').value = 'Paid';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('amt').addEventListener('input', calcBalance);
    // Set default date to today if empty
    const dateInput = document.querySelector('input[name="expense_date"]');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>

<?php include 'sidebar_close.php'; ?>