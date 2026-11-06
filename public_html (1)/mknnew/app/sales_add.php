<?php
require_once 'config.php';
requireLogin();
$page_title = 'Add Sale';
$msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_sale'])) {
    $cust_id        = intval($_POST['customer_id']);
    $bill_date      = sanitize($conn, $_POST['bill_date']);
    $transport_mode = sanitize($conn, $_POST['transport_mode']);
    $invoice_no     = sanitize($conn, $_POST['invoice_no']);
    $transport_cost = floatval($_POST['transport_cost']);
    $apply_gst      = isset($_POST['apply_gst']) ? 1 : 0;
    $payment_status = sanitize($conn, $_POST['payment_status']);
    $amount_paid    = floatval($_POST['amount_paid']);

    $product_ids    = $_POST['product_id'] ?? [];
    $category_ids   = $_POST['category_id_item'] ?? [];
    $weights        = $_POST['weight'] ?? [];
    $mrps           = $_POST['mrp'] ?? [];
    $quantities     = $_POST['quantity'] ?? [];
    $rates          = $_POST['rate'] ?? [];
    $discounts      = $_POST['discount_pct'] ?? [];

    if (!$cust_id || !$invoice_no || empty($product_ids)) {
        $msg = '<div class="alert alert-error">❌ Please fill all required fields and add at least one product.</div>';
    } else {
        // Check duplicate invoice
        $chk = $conn->query("SELECT id FROM sales WHERE invoice_no='$invoice_no'");
        if ($chk && $chk->num_rows > 0) {
            $msg = '<div class="alert alert-error">❌ Invoice No already exists!</div>';
        } else {
            $subtotal = 0;
            $line_totals = [];
            for ($i = 0; $i < count($product_ids); $i++) {
                $qty  = floatval($quantities[$i]);
                $rate = floatval($rates[$i]);
                $disc = floatval($discounts[$i]);
                $lt   = $qty * $rate * (1 - $disc / 100);
                $line_totals[] = $lt;
                $subtotal += $lt;
            }

            $cgst  = $apply_gst ? round($subtotal * 0.025, 2) : 0;
            $sgst  = $apply_gst ? round($subtotal * 0.025, 2) : 0;
            $grand = $subtotal + $cgst + $sgst + $transport_cost;

            // Fix: correctly set amount_paid and amount_due based on payment status
            if ($payment_status === 'Paid') {
                $amount_paid = $grand;   // fully paid → amount_paid = grand total
                $amount_due  = 0;
            } elseif ($payment_status === 'Not Paid') {
                $amount_paid = 0;        // nothing paid
                $amount_due  = $grand;
            } else {
                // Partial Paid: use the entered amount_paid
                $amount_due  = $grand - $amount_paid;
            }

            $conn->query("INSERT INTO sales (customer_id, bill_date, transport_mode, invoice_no, transport_cost, subtotal, apply_gst, cgst, sgst, grand_total, payment_status, amount_paid, amount_due)
                          VALUES ($cust_id, '$bill_date', '$transport_mode', '$invoice_no', $transport_cost, $subtotal, $apply_gst, $cgst, $sgst, $grand, '$payment_status', $amount_paid, $amount_due)");
            $sale_id = $conn->insert_id;

            // Insert sale items & deduct stock FIFO
            for ($i = 0; $i < count($product_ids); $i++) {
                $pid    = intval($product_ids[$i]);
                $cid    = intval($category_ids[$i]);
                $wt     = floatval($weights[$i]);
                $mrp    = floatval($mrps[$i]);
                $qty    = floatval($quantities[$i]);
                $rate   = floatval($rates[$i]);
                $disc   = floatval($discounts[$i]);
                $lt     = $line_totals[$i];

                $conn->query("INSERT INTO sale_items (sale_id, category_id, product_id, weight, mrp, quantity, rate, discount_pct, line_total)
                              VALUES ($sale_id, $cid, $pid, $wt, $mrp, $qty, $rate, $disc, $lt)");

                // FIFO stock deduction: total_kg = piece_weight(kg) × number_of_pieces
                // weight field = weight per piece in KG, quantity = number of pieces
                $total_kg_to_deduct = round($wt * $qty, 3);
                $deduct = $total_kg_to_deduct;
                if ($deduct > 0) {
                    $batches = $conn->query("SELECT * FROM stock WHERE category_id=$cid AND remaining_stock > 0 ORDER BY batch_date ASC, id ASC");
                    while ($batch = $batches->fetch_assoc()) {
                        if ($deduct <= 0) break;
                        if ($batch['remaining_stock'] >= $deduct) {
                            $new_rem = round($batch['remaining_stock'] - $deduct, 3);
                            $conn->query("UPDATE stock SET remaining_stock=$new_rem WHERE id={$batch['id']}");
                            $deduct = 0;
                        } else {
                            $deduct = round($deduct - $batch['remaining_stock'], 3);
                            $conn->query("UPDATE stock SET remaining_stock=0 WHERE id={$batch['id']}");
                        }
                    }
                }
            }

            $msg = '<div class="alert alert-success">✅ Sale saved! Invoice: <strong>' . $invoice_no . '</strong> | Grand Total: ₹' . number_format($grand, 2) . ' <a href="sales_print.php?id=' . $sale_id . '" target="_blank" class="btn btn-info btn-sm" style="margin-left:10px;">🖨️ Print Invoice</a></div>';
        }
    }
}

// Fetch customers for autocomplete
$all_customers = [];
$rc = $conn->query("SELECT id, customer_name, shop_name, city, phone, gst_no FROM customers ORDER BY customer_name");
while ($c = $rc->fetch_assoc()) $all_customers[] = $c;

// Fetch categories
$all_cats = [];
$rcat = $conn->query("SELECT * FROM product_categories ORDER BY category_name");
while ($c = $rcat->fetch_assoc()) $all_cats[] = $c;

// Invoice auto-suggest
$last_inv = $conn->query("SELECT invoice_no FROM sales ORDER BY id DESC LIMIT 1");
$next_inv = 'INV-0001';
if ($last_inv && $last_inv->num_rows > 0) {
    $li = $last_inv->fetch_assoc();
    preg_match('/(\d+)$/', $li['invoice_no'], $m);
    if ($m) $next_inv = 'INV-' . str_pad(intval($m[1]) + 1, 4, '0', STR_PAD_LEFT);
}

// Get sales count for stats
$sales_count = $conn->query("SELECT COUNT(*) as total FROM sales")->fetch_assoc()['total'];

include 'sidebar.php';
?>

<!-- Main Content -->
<div class="sales-add-page">
    <?php echo $msg; ?>

    <!-- Summary Cards -->
    <div class="summary-row">
        <div class="summary-card">
            <div class="summary-icon">🧾</div>
            <div class="summary-info">
                <span class="summary-label">Total Sales</span>
                <span class="summary-value"><?php echo number_format($sales_count); ?></span>
                <span class="summary-sub">All time</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">🏷️</div>
            <div class="summary-info">
                <span class="summary-label">Invoice No</span>
                <span class="summary-value"><?php echo $next_inv; ?></span>
                <span class="summary-sub">Next invoice</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">📅</div>
            <div class="summary-info">
                <span class="summary-label">Today</span>
                <span class="summary-value"><?php echo date('d M Y'); ?></span>
                <span class="summary-sub">Billing date</span>
            </div>
        </div>
    </div>

    <!-- Add Sale Form Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">🧾</div>
            <div>
                <h3>Add New Sale</h3>
                <p class="card-subtitle">Create a new sales invoice</p>
            </div>
        </div>

        <form method="POST" id="saleForm" class="premium-form">
            <input type="hidden" name="submit_sale" value="1">
            <input type="hidden" name="customer_id" id="customer_id">

            <!-- Customer Search & Invoice -->
            <div class="form-grid">
                <div class="form-group" style="position:relative;">
                    <label class="form-label">
                        <span class="label-icon">👤</span>
                        Select Customer *
                    </label>
                    <input type="text" id="custSearch" autocomplete="off" 
                           placeholder="Type customer name or shop name..." 
                           oninput="searchCustomer(this.value)" 
                           class="form-input" required>
                    <div id="custDropdown" class="customer-dropdown"></div>
                    <small class="form-hint">Search by name or shop name</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🔢</span>
                        Invoice No *
                    </label>
                    <input type="text" name="invoice_no" id="invoice_no" 
                           value="<?php echo $next_inv; ?>" required class="form-input">
                </div>
            </div>

            <!-- Customer Info Display -->
            <div id="custInfo" class="customer-info" style="display:none;"></div>

            <!-- Billing Details -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📅</span>
                        Bill Date *
                    </label>
                    <input type="date" name="bill_date" value="<?php echo date('Y-m-d'); ?>" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🚚</span>
                        Transport Mode *
                    </label>
                    <select name="transport_mode" required class="form-select">
                        <option value="">-- Select --</option>
                        <option>🚚 By Hand</option>
                        <option>🚛 Lorry</option>
                        <option>🚌 Bus Parcel</option>
                        <option>🚂 Train Parcel</option>
                        <option>📦 Courier</option>
                        <option>🚗 Own Vehicle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">💰</span>
                        Transport Cost (₹)
                    </label>
                    <input type="number" step="0.01" name="transport_cost" id="transport_cost" 
                           value="0" oninput="calcTotal()" min="0" class="form-input">
                </div>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <div class="products-header">
                    <span class="products-icon">🛒</span>
                    <h4>Products</h4>
                    <button type="button" class="btn btn-success btn-sm" onclick="addItem()">
                        ➕ Add Product
                    </button>
                </div>
                <div id="itemsContainer" class="items-container"></div>
            </div>

            <!-- GST Checkbox -->
            <div class="gst-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="apply_gst" id="applyGst" onchange="calcTotal()">
                    <span class="checkbox-text">Apply GST (CGST 2.5% + SGST 2.5%)</span>
                </label>
            </div>

            <!-- Totals Section -->
            <div class="totals-section">
                <div class="totals-card">
                    <div class="totals-row">
                        <span class="totals-label">Subtotal</span>
                        <span class="totals-value" id="disp_sub">₹0.00</span>
                    </div>
                    <div class="totals-row gst-row" id="gstRow" style="display:none;">
                        <span class="totals-label">CGST (2.5%)</span>
                        <span class="totals-value" id="disp_cgst">₹0.00</span>
                    </div>
                    <div class="totals-row gst-row" id="sgstRow" style="display:none;">
                        <span class="totals-label">SGST (2.5%)</span>
                        <span class="totals-value" id="disp_sgst">₹0.00</span>
                    </div>
                    <div class="totals-row">
                        <span class="totals-label">Transport Cost</span>
                        <span class="totals-value" id="disp_transport">₹0.00</span>
                    </div>
                    <div class="totals-row grand-total">
                        <span class="totals-label">Grand Total</span>
                        <span class="totals-value" id="disp_grand">₹0.00</span>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="payment-section">
                <div class="payment-header">
                    <span class="payment-icon">💳</span>
                    <h4>Payment Details</h4>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Payment Status *</label>
                        <select name="payment_status" id="salePayStatus" 
                                onchange="toggleSalePartial(this.value)" required class="form-select">
                            <option value="Not Paid">⏳ Not Paid</option>
                            <option value="Paid">✅ Paid</option>
                            <option value="Partial Paid">🔸 Partial Paid</option>
                        </select>
                    </div>
                    <div class="form-group" id="salePartialDiv" style="display:none;">
                        <label class="form-label">Amount Paid (₹)</label>
                        <input type="number" step="0.01" name="amount_paid" id="saleAmtPaid" 
                               value="0" oninput="calcTotal()" min="0" class="form-input">
                    </div>
                    <div class="form-group" id="saleBalanceDiv" style="display:none;">
                        <label class="form-label">Balance Due (₹)</label>
                        <input type="text" id="saleBalance" readonly class="form-input balance-field">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">💾</span>
                    Submit Sale
                </button>
                <a href="sales_dashboard.php" class="btn btn-secondary">
                    <span class="btn-icon">📋</span>
                    Sales Dashboard
                </a>
                <button type="reset" class="btn btn-outline" onclick="resetForm()">
                    <span class="btn-icon">🔄</span>
                    Clear Form
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ========== SALES ADD PAGE SPECIFIC STYLES ========== */

.sales-add-page {
    max-width: 1400px;
    margin: 0 auto;
}

/* Summary Cards */
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

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

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
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
}

.form-hint {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
    display: block;
}

/* Customer Dropdown */
.customer-dropdown {
    display: none;
    position: absolute;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    width: 100%;
    z-index: 200;
    max-height: 250px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    top: calc(100% + 4px);
    left: 0;
}

.customer-dropdown div {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.customer-dropdown div:hover {
    background: #f8fafc;
}

/* Customer Info */
.customer-info {
    background: #e8f0fe;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 13px;
    border-left: 4px solid #1e4663;
}

/* Products Section */
.products-section {
    margin: 24px 0;
}

.products-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eef2f8;
}

.products-icon {
    font-size: 20px;
}

.products-header h4 {
    flex: 1;
    font-size: 16px;
    font-weight: 700;
    color: #1e4663;
    margin: 0;
}

.items-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.item-card {
    background: #f8fafc;
    border: 1px solid #eef2f8;
    border-radius: 16px;
    padding: 16px;
    transition: all 0.2s;
}

.item-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}

.item-title {
    font-weight: 700;
    color: #1e4663;
    font-size: 14px;
}

.remove-item-btn {
    background: #c62828;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 4px 12px;
    cursor: pointer;
    font-size: 12px;
    transition: background 0.2s;
}

.remove-item-btn:hover {
    background: #b71c1c;
}

.item-fields {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
}

.item-fields .field-group {
    display: flex;
    flex-direction: column;
}

.item-fields .field-group label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.item-fields .field-group input,
.item-fields .field-group select {
    padding: 8px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 13px;
}

.line-total-field {
    background: #e8f0fe;
    font-weight: 700;
    color: #1e4663;
}

/* GST Checkbox */
.gst-checkbox {
    margin: 20px 0;
}

.checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    color: #334155;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Totals Section */
.totals-section {
    display: flex;
    justify-content: flex-end;
    margin: 20px 0;
}

.totals-card {
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px;
    min-width: 280px;
    border: 1px solid #eef2f8;
}

.totals-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
}

.totals-label {
    color: #64748b;
}

.totals-value {
    font-weight: 600;
    color: #334155;
}

.btn {
    text-decoration: none !important;
}

.grand-total {
    border-top: 2px solid #e2e8f0;
    margin-top: 8px;
    padding-top: 12px;
    font-size: 18px;
}

.grand-total .totals-label,
.grand-total .totals-value {
    font-weight: 800;
    color: #1e4663;
}

/* Payment Section */
.payment-section {
    margin: 24px 0;
    padding: 20px;
    background: #f8fafc;
    border-radius: 16px;
}

.payment-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.payment-icon {
    font-size: 18px;
}

.payment-header h4 {
    font-size: 16px;
    font-weight: 700;
    color: #1e4663;
    margin: 0;
}

.balance-field {
    background: #fff3e0;
    font-weight: 700;
    color: #c62828;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 24px;
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

/* ========== MOBILE RESPONSIVE ========== */
@media (max-width: 768px) {
    .summary-row {
        grid-template-columns: 1fr;
        gap: 12px;
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
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .premium-form {
        padding: 20px;
    }
    
    .card-header {
        padding: 18px 20px;
    }
    
    .card-header-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
    }
    
    .card-header h3 {
        font-size: 18px;
    }
    
    .item-fields {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .totals-section {
        justify-content: stretch;
    }
    
    .totals-card {
        width: 100%;
        min-width: auto;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        text-align: center;
        justify-content: center;
        display: flex;
        align-items: center;
    }
    
    .form-input,
    .form-select {
        padding: 12px 14px;
        font-size: 16px;
    }
    
    .products-header {
        flex-wrap: wrap;
    }
    
    .products-header button {
        width: 100%;
        text-align: center;
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
    
    .premium-form {
        padding: 16px;
    }
    
    .card-header {
        padding: 14px 16px;
    }
    
    .totals-card {
        padding: 16px;
    }
    
    .grand-total {
        font-size: 16px;
    }
}

/* Animation */
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
var customers = <?php echo json_encode($all_customers); ?>;
var categories = <?php echo json_encode($all_cats); ?>;
var itemCount = 0;

function searchCustomer(val) {
    var dd = document.getElementById('custDropdown');
    if (!val) { dd.style.display = 'none'; return; }
    var matches = customers.filter(c => 
        c.customer_name.toLowerCase().includes(val.toLowerCase()) || 
        (c.shop_name && c.shop_name.toLowerCase().includes(val.toLowerCase()))
    );
    if (!matches.length) { dd.style.display = 'none'; return; }
    dd.innerHTML = matches.map(c => `<div onclick="selectCustomer(${c.id},'${c.customer_name.replace(/'/g, "\\'")}','${(c.shop_name || '').replace(/'/g, "\\'")}','${(c.city || '')}','${(c.phone || '')}','${(c.gst_no || '')}')">
        <strong>${escapeHtml(c.customer_name)}</strong>
        ${c.shop_name ? `<small style="color:#888;margin-left:6px;">${escapeHtml(c.shop_name)}</small>` : ''}
        ${c.city ? `<small style="color:#aaa;margin-left:6px;">📍 ${escapeHtml(c.city)}</small>` : ''}
    </div>`).join('');
    dd.style.display = 'block';
}

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function selectCustomer(id, name, shop, city, phone, gst) {
    document.getElementById('customer_id').value = id;
    document.getElementById('custSearch').value = name;
    document.getElementById('custDropdown').style.display = 'none';
    var info = document.getElementById('custInfo');
    info.style.display = 'block';
    info.innerHTML = `<strong>📍 ${escapeHtml(name)}</strong> &nbsp;|&nbsp; Shop: ${escapeHtml(shop)} &nbsp;|&nbsp; City: ${escapeHtml(city)} &nbsp;|&nbsp; 📞 ${escapeHtml(phone)} &nbsp;|&nbsp; GST: ${escapeHtml(gst) || 'N/A'}`;
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#custDropdown') && e.target.id !== 'custSearch') {
        document.getElementById('custDropdown').style.display = 'none';
    }
});

function addItem() {
    itemCount++;
    var div = document.createElement('div');
    div.id = 'item_' + itemCount;
    div.className = 'item-card';
    div.innerHTML = `
        <div class="item-header">
            <span class="item-title">Item #${itemCount}</span>
            <button type="button" class="remove-item-btn" onclick="removeItem('item_${itemCount}')">✖ Remove</button>
        </div>
        <div class="item-fields">
            <div class="field-group">
                <label>CATEGORY *</label>
                <select name="category_id_item[]" onchange="loadProducts(this, ${itemCount})" required>
                    <option value="">-- Select --</option>
                    ${categories.map(c => `<option value="${c.id}">${escapeHtml(c.category_name)}</option>`).join('')}
                </select>
            </div>
            <div class="field-group">
                <label>PRODUCT *</label>
                <select name="product_id[]" id="prod_${itemCount}" required>
                    <option value="">-- Select Category First --</option>
                </select>
            </div>
            <div class="field-group">
                <label>WEIGHT (KG)</label>
                <input type="number" step="0.001" name="weight[]" placeholder="0.000" min="0">
            </div>
            <div class="field-group">
                <label>MRP (₹)</label>
                <input type="number" step="0.01" name="mrp[]" placeholder="0.00" min="0">
            </div>
            <div class="field-group">
                <label>QUANTITY *</label>
                <input type="number" step="0.001" name="quantity[]" placeholder="0" min="0" oninput="calcTotal()" required>
            </div>
            <div class="field-group">
                <label>RATE (₹) *</label>
                <input type="number" step="0.01" name="rate[]" placeholder="0.00" min="0" oninput="calcTotal()" required>
            </div>
            <div class="field-group">
                <label>DISCOUNT %</label>
                <input type="number" step="0.01" name="discount_pct[]" value="0" min="0" max="100" oninput="calcTotal()">
            </div>
            <div class="field-group">
                <label>LINE TOTAL</label>
                <input type="text" id="lt_${itemCount}" readonly class="line-total-field">
            </div>
        </div>
    `;
    document.getElementById('itemsContainer').appendChild(div);
}

function removeItem(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
    calcTotal();
}

function loadProducts(sel, idx) {
    var catId = sel.value;
    var prodSel = document.getElementById('prod_' + idx);
    if (!catId) { 
        prodSel.innerHTML = '<option value="">-- Select Category First --</option>'; 
        return; 
    }
    fetch('ajax_products.php?cat_id=' + catId)
        .then(r => r.json())
        .then(prods => {
            prodSel.innerHTML = '<option value="">-- Select Product --</option>' + 
                prods.map(p => `<option value="${p.id}">${escapeHtml(p.product_name)} (₹${p.price})</option>`).join('');
        });
}

function calcTotal() {
    var qtys = document.getElementsByName('quantity[]');
    var rates = document.getElementsByName('rate[]');
    var discs = document.getElementsByName('discount_pct[]');
    var subtotal = 0;
    
    for (var i = 0; i < qtys.length; i++) {
        var q = parseFloat(qtys[i].value) || 0;
        var r = parseFloat(rates[i].value) || 0;
        var d = parseFloat(discs[i].value) || 0;
        var lt = q * r * (1 - d / 100);
        subtotal += lt;
        
        var itemDiv = qtys[i].closest('.item-card');
        if (itemDiv) {
            var ltInput = itemDiv.querySelector('input[readonly]');
            if (ltInput && ltInput.id) ltInput.value = '₹' + lt.toFixed(2);
        }
    }
    
    var gst = document.getElementById('applyGst').checked;
    var cgst = gst ? subtotal * 0.025 : 0;
    var sgst = gst ? subtotal * 0.025 : 0;
    var tc = parseFloat(document.getElementById('transport_cost').value) || 0;
    var grand = subtotal + cgst + sgst + tc;
    
    document.getElementById('disp_sub').textContent = '₹' + subtotal.toFixed(2);
    document.getElementById('disp_cgst').textContent = '₹' + cgst.toFixed(2);
    document.getElementById('disp_sgst').textContent = '₹' + sgst.toFixed(2);
    document.getElementById('disp_transport').textContent = '₹' + tc.toFixed(2);
    document.getElementById('disp_grand').textContent = '₹' + grand.toFixed(2);
    document.getElementById('gstRow').style.display = gst ? 'flex' : 'none';
    document.getElementById('sgstRow').style.display = gst ? 'flex' : 'none';

    // Sync amount_paid & balance based on payment status
    var ps = document.getElementById('salePayStatus').value;
    var amtPaidInput = document.getElementById('saleAmtPaid');
    if (ps === 'Paid') {
        // Keep amount_paid in sync with grand total
        amtPaidInput.value = grand.toFixed(2);
    } else if (ps === 'Not Paid') {
        amtPaidInput.value = '0.00';
    } else if (ps === 'Partial Paid') {
        var paid = parseFloat(amtPaidInput.value) || 0;
        document.getElementById('saleBalance').value = '₹' + (grand - paid).toFixed(2);
    }
}

function toggleSalePartial(v) {
    var partialDiv = document.getElementById('salePartialDiv');
    var balanceDiv = document.getElementById('saleBalanceDiv');
    var amtPaidInput = document.getElementById('saleAmtPaid');

    if (partialDiv) partialDiv.style.display = v === 'Partial Paid' ? 'block' : 'none';
    if (balanceDiv) balanceDiv.style.display = v === 'Partial Paid' ? 'block' : 'none';

    // Auto-set amount_paid based on status
    if (v === 'Paid') {
        // Get current grand total and set amount_paid to it
        var grandText = document.getElementById('disp_grand').textContent.replace('₹', '').replace(/,/g, '');
        var grand = parseFloat(grandText) || 0;
        amtPaidInput.value = grand.toFixed(2);
    } else if (v === 'Not Paid') {
        amtPaidInput.value = '0.00';
    }
    // For 'Partial Paid', leave it as-is for the user to enter

    calcTotal();
}

function resetForm() {
    if (confirm('Clear all form fields?')) {
        document.getElementById('saleForm').reset();
        document.getElementById('itemsContainer').innerHTML = '';
        itemCount = 0;
        addItem();
        document.getElementById('custInfo').style.display = 'none';
        document.getElementById('custSearch').value = '';
        document.getElementById('customer_id').value = '';
        calcTotal();
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    // Add first item
    addItem();
});
</script>

<?php include 'sidebar_close.php'; ?>