<?php
require_once 'config.php';
requireLogin();
$page_title = 'Edit Sale';
$msg = '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header('Location: sales_dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_edit_sale'])) {
    $cust_id        = intval($_POST['customer_id']);
    $bill_date      = sanitize($conn, $_POST['bill_date']);
    $transport_mode = sanitize($conn, $_POST['transport_mode']);
    $invoice_no     = sanitize($conn, $_POST['invoice_no']);
    $transport_cost = floatval($_POST['transport_cost']);
    $apply_gst      = isset($_POST['apply_gst']) ? 1 : 0;
    $payment_status = sanitize($conn, $_POST['payment_status']);
    $amount_paid    = floatval($_POST['amount_paid']);

    $product_ids  = $_POST['product_id'] ?? [];
    $category_ids = $_POST['category_id_item'] ?? [];
    $weights      = $_POST['weight'] ?? [];
    $mrps         = $_POST['mrp'] ?? [];
    $quantities   = $_POST['quantity'] ?? [];
    $rates        = $_POST['rate'] ?? [];
    $discounts    = $_POST['discount_pct'] ?? [];

    if (!$cust_id || !$invoice_no || empty($product_ids)) {
        $msg = '<div class="alert alert-error">❌ Please fill all required fields and add at least one product.</div>';
    } else {
        // Check duplicate invoice (exclude current sale)
        $chk = $conn->query("SELECT id FROM sales WHERE invoice_no='$invoice_no' AND id != $id");
        if ($chk && $chk->num_rows > 0) {
            $msg = '<div class="alert alert-error">❌ Invoice No already exists for another sale!</div>';
        } else {
            // ── Helper: restore stock using reverse-FIFO across all batches ──
            // Adds back $restore_kg to batches newest-first, respecting each batch's new_stock cap.
            $restoreStock = function($cid, $restore_kg) use ($conn) {
                if ($restore_kg <= 0) return;
                $batches = $conn->query("SELECT * FROM stock WHERE category_id=$cid ORDER BY batch_date DESC, id DESC");
                while ($batch = $batches->fetch_assoc()) {
                    if ($restore_kg <= 0) break;
                    $space = round($batch['new_stock'] - $batch['remaining_stock'], 3);
                    if ($space <= 0) continue;
                    $add     = min($space, $restore_kg);
                    $new_rem = round($batch['remaining_stock'] + $add, 3);
                    $conn->query("UPDATE stock SET remaining_stock=$new_rem WHERE id={$batch['id']}");
                    $restore_kg = round($restore_kg - $add, 3);
                }
            };

            // ── STEP 1: Restore stock for old items (reverse-FIFO across all batches) ──
            $old_items = $conn->query("SELECT * FROM sale_items WHERE sale_id=$id");
            while ($oi = $old_items->fetch_assoc()) {
                $cid        = intval($oi['category_id']);
                $restore_kg = round(floatval($oi['weight']) * floatval($oi['quantity']), 3);
                $restoreStock($cid, $restore_kg);
            }

            // ── STEP 2: Delete old sale items ──
            $conn->query("DELETE FROM sale_items WHERE sale_id=$id");

            // ── STEP 3: Recalculate totals ──
            $subtotal    = 0;
            $line_totals = [];
            for ($i = 0; $i < count($product_ids); $i++) {
                $qty  = floatval($quantities[$i]);
                $rate = floatval($rates[$i]);
                $disc = floatval($discounts[$i]);
                $lt   = $qty * $rate * (1 - $disc / 100);
                $line_totals[] = $lt;
                $subtotal += $lt;
            }
            $cgst       = $apply_gst ? round($subtotal * 0.025, 2) : 0;
            $sgst       = $apply_gst ? round($subtotal * 0.025, 2) : 0;
            $grand      = $subtotal + $cgst + $sgst + $transport_cost;
            $amount_due = ($payment_status === 'Paid') ? 0 : ($payment_status === 'Not Paid' ? $grand : $grand - $amount_paid);
            $amount_paid_final = ($payment_status === 'Paid') ? $grand : $amount_paid;

            // ── STEP 4: Update the sale record ──
            $conn->query("UPDATE sales SET 
                customer_id=$cust_id, bill_date='$bill_date', transport_mode='$transport_mode',
                invoice_no='$invoice_no', transport_cost=$transport_cost, subtotal=$subtotal,
                apply_gst=$apply_gst, cgst=$cgst, sgst=$sgst, grand_total=$grand,
                payment_status='$payment_status', amount_paid=$amount_paid_final, amount_due=$amount_due
                WHERE id=$id");

            // ── STEP 5: Insert new sale items & deduct stock FIFO ──
            for ($i = 0; $i < count($product_ids); $i++) {
                $pid  = intval($product_ids[$i]);
                $cid  = intval($category_ids[$i]);
                $wt   = floatval($weights[$i]);
                $mrp  = floatval($mrps[$i]);
                $qty  = floatval($quantities[$i]);
                $rate = floatval($rates[$i]);
                $disc = floatval($discounts[$i]);
                $lt   = $line_totals[$i];

                $conn->query("INSERT INTO sale_items (sale_id, category_id, product_id, weight, mrp, quantity, rate, discount_pct, line_total)
                              VALUES ($id, $cid, $pid, $wt, $mrp, $qty, $rate, $disc, $lt)");

                $total_kg = round($wt * $qty, 3);
                $deduct   = $total_kg;
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

            $msg = '<div class="alert alert-success">✅ Sale updated! Invoice: <strong>' . $invoice_no . '</strong> | Grand Total: ₹' . number_format($grand, 2) . ' 
                    <a href="sales_view.php?id=' . $id . '" class="btn btn-info btn-sm" style="margin-left:10px;">👁️ View</a>
                    <a href="sales_print.php?id=' . $id . '" target="_blank" class="btn btn-success btn-sm" style="margin-left:6px;">🖨️ Print</a></div>';
        }
    }
}

// Load sale for the form
$r = $conn->query("SELECT s.*, c.customer_name, c.shop_name, c.city, c.phone, c.gst_no
                   FROM sales s JOIN customers c ON s.customer_id = c.id WHERE s.id = $id");
if (!$r || $r->num_rows === 0) {
    header('Location: sales_dashboard.php');
    exit();
}
$sale = $r->fetch_assoc();

// Load sale items
$existing_items = [];
$ri = $conn->query("SELECT si.*, p.product_name, pc.category_name
                    FROM sale_items si 
                    JOIN products p ON si.product_id = p.id 
                    JOIN product_categories pc ON si.category_id = pc.id
                    WHERE si.sale_id = $id ORDER BY si.id ASC");
while ($row = $ri->fetch_assoc()) $existing_items[] = $row;

// Fetch customers for autocomplete
$all_customers = [];
$rc = $conn->query("SELECT id, customer_name, shop_name, city, phone, gst_no FROM customers ORDER BY customer_name");
while ($c = $rc->fetch_assoc()) $all_customers[] = $c;

// Fetch categories
$all_cats = [];
$rcat = $conn->query("SELECT * FROM product_categories ORDER BY category_name");
while ($c = $rcat->fetch_assoc()) $all_cats[] = $c;

include 'sidebar.php';
?>

<!-- Main Content -->
<div class="edit-sale-page">
    <?php echo $msg; ?>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-icon">✏️</div>
            <div class="summary-info">
                <span class="summary-label">Editing Sale</span>
                <span class="summary-value">#<?php echo $id; ?></span>
                <span class="summary-sub">Sale ID</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">🏷️</div>
            <div class="summary-info">
                <span class="summary-label">Invoice No</span>
                <span class="summary-value"><?php echo htmlspecialchars($sale['invoice_no']); ?></span>
                <span class="summary-sub">Current invoice</span>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">📅</div>
            <div class="summary-info">
                <span class="summary-label">Bill Date</span>
                <span class="summary-value"><?php echo date('d M Y', strtotime($sale['bill_date'])); ?></span>
                <span class="summary-sub">Original date</span>
            </div>
        </div>
    </div>

    <!-- Edit Sale Form Card -->
    <div class="card premium-card">
        <div class="card-header">
            <div class="card-header-icon">✏️</div>
            <div>
                <h3>Edit Sale</h3>
                <p class="card-subtitle">Modify sale details — stock will be re-adjusted automatically</p>
            </div>
            <div class="card-header-actions">
                <a href="sales_view.php?id=<?php echo $id; ?>" class="btn btn-info btn-sm">👁️ View Sale</a>
                <a href="sales_dashboard.php" class="btn btn-secondary btn-sm">✖ Cancel</a>
            </div>
        </div>

        <form method="POST" id="saleForm" class="premium-form">
            <input type="hidden" name="submit_edit_sale" value="1">
            <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $sale['customer_id']; ?>">

            <!-- Customer & Invoice Details -->
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label class="form-label">
                        <span class="label-icon">👤</span>
                        Select Customer *
                    </label>
                    <div class="customer-search-wrapper">
                        <input type="text" id="custSearch" autocomplete="off"
                               value="<?php echo htmlspecialchars($sale['customer_name']); ?>"
                               placeholder="Type customer name or shop name..."
                               oninput="searchCustomer(this.value)"
                               class="form-input" required>
                        <div id="custDropdown" class="customer-dropdown"></div>
                    </div>
                    <small class="form-hint">Search by name, shop name, or city</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🔢</span>
                        Invoice No *
                    </label>
                    <input type="text" name="invoice_no" class="form-input"
                           value="<?php echo htmlspecialchars($sale['invoice_no']); ?>"
                           placeholder="e.g. INV-0001" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">📅</span>
                        Bill Date *
                    </label>
                    <input type="date" name="bill_date" class="form-input"
                           value="<?php echo $sale['bill_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">🚚</span>
                        Transport Mode
                    </label>
                    <select name="transport_mode" class="form-select">
                        <?php foreach (['By Hand', 'Courier', 'Transport', 'Lorry', 'Train', 'Bus'] as $tm): ?>
                            <option <?php echo $sale['transport_mode'] === $tm ? 'selected' : ''; ?>><?php echo $tm; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <span class="label-icon">💰</span>
                        Transport Cost (₹)
                    </label>
                    <input type="number" step="0.01" name="transport_cost" class="form-input"
                           id="transport_cost" oninput="calcTotal()"
                           value="<?php echo $sale['transport_cost']; ?>" min="0">
                </div>

                <div class="form-group gst-checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="apply_gst" id="applyGst"
                               <?php echo $sale['apply_gst'] ? 'checked' : ''; ?>
                               onchange="calcTotal()">
                        <span class="checkbox-text">Apply GST (CGST 2.5% + SGST 2.5%)</span>
                    </label>
                </div>
            </div>

            <!-- Customer Info Display -->
            <div id="custInfo" class="customer-info-bar" style="<?php echo $sale['customer_id'] ? '' : 'display:none;'; ?>">
                <div class="customer-info-content">
                    <span class="info-icon">📍</span>
                    <span class="info-text">
                        <strong><?php echo htmlspecialchars($sale['customer_name']); ?></strong>
                        &nbsp;|&nbsp; Shop: <?php echo htmlspecialchars($sale['shop_name']); ?>
                        &nbsp;|&nbsp; City: <?php echo htmlspecialchars($sale['city']); ?>
                        &nbsp;|&nbsp; 📞 <?php echo htmlspecialchars($sale['phone']); ?>
                        &nbsp;|&nbsp; GST: <?php echo htmlspecialchars($sale['gst_no']) ?: 'N/A'; ?>
                    </span>
                </div>
            </div>

            <!-- Items Section -->
            <div class="items-section">
                <div class="items-header">
                    <div class="items-title">
                        <span class="items-icon">🛒</span>
                        <h4>Sale Items</h4>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        ➕ Add Item
                    </button>
                </div>
                <div id="itemsContainer" class="items-container"></div>
            </div>

            <!-- Totals Summary -->
            <div class="totals-section">
                <div class="totals-card">
                    <div class="totals-row">
                        <span>Subtotal</span>
                        <span id="disp_sub">₹0.00</span>
                    </div>
                    <div class="totals-row gst-row" id="gstRow" style="display:none;">
                        <span>CGST (2.5%)</span>
                        <span id="disp_cgst">₹0.00</span>
                    </div>
                    <div class="totals-row gst-row" id="sgstRow" style="display:none;">
                        <span>SGST (2.5%)</span>
                        <span id="disp_sgst">₹0.00</span>
                    </div>
                    <div class="totals-row">
                        <span>Transport Cost</span>
                        <span id="disp_transport">₹0.00</span>
                    </div>
                    <div class="totals-row grand-total">
                        <span>Grand Total</span>
                        <span id="disp_grand">₹0.00</span>
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
                                class="form-select" onchange="toggleSalePartial(this.value)">
                            <?php foreach (['Paid', 'Not Paid', 'Partial Paid'] as $ps): ?>
                                <option <?php echo $sale['payment_status'] === $ps ? 'selected' : ''; ?>>
                                    <?php echo $ps === 'Paid' ? '✅ Paid' : ($ps === 'Not Paid' ? '⏳ Not Paid' : '🔸 Partial Paid'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="salePartialDiv"
                         style="<?php echo $sale['payment_status'] === 'Partial Paid' ? '' : 'display:none;'; ?>">
                        <label class="form-label">Amount Paid (₹)</label>
                        <input type="number" step="0.01" name="amount_paid" id="saleAmtPaid"
                               class="form-input" oninput="calcTotal()"
                               value="<?php echo $sale['amount_paid']; ?>" min="0">
                    </div>

                    <div class="form-group" id="saleBalanceDiv"
                         style="<?php echo $sale['payment_status'] === 'Partial Paid' ? '' : 'display:none;'; ?>">
                        <label class="form-label">Balance Due (₹)</label>
                        <input type="text" id="saleBalance" class="form-input balance-field" readonly
                               value="₹<?php echo number_format($sale['amount_due'], 2); ?>">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <span class="btn-icon">💾</span>
                    Update Sale
                </button>
                <a href="sales_view.php?id=<?php echo $id; ?>" class="btn btn-info">
                    <span class="btn-icon">👁️</span>
                    View Sale
                </a>
                <button type="button" class="btn btn-outline" onclick="resetToOriginal()">
                    <span class="btn-icon">↩️</span>
                    Reset Changes
                </button>
                <a href="sales_dashboard.php" class="btn btn-secondary">
                    <span class="btn-icon">✖</span>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* ========== EDIT SALE PAGE ENHANCED STYLES ========== */

.edit-sale-page {
    max-width: 1400px;
    margin: 0 auto;
}

/* Summary Cards Grid */
.summary-grid {
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
    flex-wrap: wrap;
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

.card-header-actions {
    margin-left: auto;
    display: flex;
    gap: 10px;
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

/* Customer Search */
.customer-search-wrapper {
    position: relative;
}

.customer-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: none;
}

.dropdown-item {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: #f8fafc;
}

.dropdown-item strong {
    color: #1e4663;
}

/* Customer Info Bar */
.customer-info-bar {
    background: #e8f0fe;
    border-radius: 14px;
    padding: 12px 18px;
    margin-bottom: 24px;
    border-left: 4px solid #1e4663;
}

.customer-info-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.info-icon {
    font-size: 18px;
}

.info-text {
    font-size: 13px;
    color: #334155;
}

.info-text strong {
    color: #1e4663;
}

/* GST Checkbox */
.gst-checkbox-group {
    justify-content: flex-end;
    padding-top: 28px;
}

.checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: #334155;
}

.checkbox-label input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* Items Section */
.items-section {
    margin: 24px 0;
}

.items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #eef2f8;
    flex-wrap: wrap;
    gap: 12px;
}

.items-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.items-icon {
    font-size: 20px;
}

.items-header h4 {
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
    margin-bottom: 14px;
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
    padding: 5px 14px;
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

.field-group {
    display: flex;
    flex-direction: column;
}

.field-group label {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.field-group input,
.field-group select {
    padding: 8px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 13px;
    font-family: inherit;
    transition: all 0.2s;
}

.field-group input:focus,
.field-group select:focus {
    outline: none;
    border-color: #1e4663;
    box-shadow: 0 0 0 3px rgba(30, 70, 99, 0.1);
}

.line-total-field {
    background: #e8f0fe;
    font-weight: 700;
    color: #1e4663;
}

/* Totals Section */
.totals-section {
    display: flex;
    justify-content: flex-end;
    margin: 24px 0;
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

.totals-row.gst-row {
    font-size: 13px;
    color: #64748b;
}

.grand-total {
    border-top: 2px solid #e2e8f0;
    margin-top: 8px;
    padding-top: 12px;
    font-size: 18px;
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
    padding-bottom: 10px;
    border-bottom: 1px solid #eef2f8;
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

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border: none;
}

.btn-sm {
    padding: 6px 14px;
    font-size: 12px;
}

.btn-large {
    padding: 12px 28px;
    font-size: 14px;
}

.btn-icon {
    margin-right: 4px;
}

.btn-primary {
    background: #1e4663;
    color: white;
}

.btn-primary:hover {
    background: #0f2b3d;
    transform: translateY(-1px);
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

.btn-info {
    background: #e3f2fd;
    color: #0288d1;
    border: 1px solid #bbdefb;
}

.btn-info:hover {
    background: #0288d1;
    color: white;
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

/* Required Field Indicator */
.form-group:has([required]) .form-label:after {
    content: " *";
    color: #c62828;
    font-weight: 700;
}

/* ========== MOBILE RESPONSIVE ========== */
@media (max-width: 768px) {
    .summary-grid {
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
        gap: 18px;
    }
    
    .form-group-full {
        grid-column: span 1;
    }
    
    .gst-checkbox-group {
        padding-top: 0;
        justify-content: flex-start;
    }
    
    .premium-form {
        padding: 20px;
    }
    
    .card-header {
        flex-direction: column;
        text-align: center;
        padding: 18px 20px;
    }
    
    .card-header-actions {
        margin-left: 0;
        width: 100%;
        justify-content: center;
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
        justify-content: center;
    }
    
    .items-header {
        flex-direction: column;
    }
    
    .items-header .btn {
        width: 100%;
        justify-content: center;
    }
    
    .customer-info-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    
    .payment-section .form-grid {
        gap: 16px;
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
    
    .totals-card {
        padding: 16px;
    }
    
    .grand-total {
        font-size: 16px;
    }
}
</style>

<script>
// ── Data from PHP ──
var customers  = <?php echo json_encode($all_customers); ?>;
var categories = <?php echo json_encode($all_cats); ?>;
var existingItems = <?php echo json_encode($existing_items); ?>;
var itemCount  = 0;

// ── Customer Search ──
function searchCustomer(val) {
    var dd = document.getElementById('custDropdown');
    if (!val || val.length < 1) { dd.style.display = 'none'; return; }
    var lv = val.toLowerCase();
    var filtered = customers.filter(c =>
        c.customer_name.toLowerCase().includes(lv) ||
        (c.shop_name && c.shop_name.toLowerCase().includes(lv)) ||
        (c.city && c.city.toLowerCase().includes(lv))
    ).slice(0, 8);
    if (!filtered.length) { dd.style.display = 'none'; return; }
    dd.innerHTML = filtered.map(c => `
        <div class="dropdown-item" onclick="selectCustomer(${c.id},'${escapeHtml(c.customer_name)}','${escapeHtml(c.shop_name||'')}','${escapeHtml(c.city||'')}','${escapeHtml(c.phone||'')}','${escapeHtml(c.gst_no||'')}')">
            <strong>${escapeHtml(c.customer_name)}</strong>
            ${c.shop_name ? `<small style="color:#888;margin-left:6px;">${escapeHtml(c.shop_name)}</small>` : ''}
            ${c.city ? `<small style="color:#aaa;margin-left:6px;">📍 ${escapeHtml(c.city)}</small>` : ''}
        </div>`).join('');
    dd.style.display = 'block';
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
}

function selectCustomer(id, name, shop, city, phone, gst) {
    document.getElementById('customer_id').value = id;
    document.getElementById('custSearch').value = name;
    document.getElementById('custDropdown').style.display = 'none';
    var info = document.getElementById('custInfo');
    info.style.display = 'block';
    info.innerHTML = `
        <div class="customer-info-content">
            <span class="info-icon">📍</span>
            <span class="info-text">
                <strong>${escapeHtml(name)}</strong>
                &nbsp;|&nbsp; Shop: ${escapeHtml(shop)}
                &nbsp;|&nbsp; City: ${escapeHtml(city)}
                &nbsp;|&nbsp; 📞 ${escapeHtml(phone)}
                &nbsp;|&nbsp; GST: ${escapeHtml(gst) || 'N/A'}
            </span>
        </div>`;
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#custDropdown') && e.target.id !== 'custSearch') {
        document.getElementById('custDropdown').style.display = 'none';
    }
});

// ── Add Item ──
function addItem(prefill) {
    itemCount++;
    var div = document.createElement('div');
    div.id = 'item_' + itemCount;
    div.className = 'item-card';

    var catOptions = categories.map(c =>
        `<option value="${c.id}" ${prefill && prefill.category_id == c.id ? 'selected' : ''}>${escapeHtml(c.category_name)}</option>`
    ).join('');

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
                    ${catOptions}
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
                <input type="number" step="0.001" name="weight[]" placeholder="0.000" min="0"
                       value="${prefill ? prefill.weight : ''}">
            </div>
            <div class="field-group">
                <label>MRP (₹)</label>
                <input type="number" step="0.01" name="mrp[]" placeholder="0.00" min="0"
                       value="${prefill ? prefill.mrp : ''}">
            </div>
            <div class="field-group">
                <label>QUANTITY *</label>
                <input type="number" step="0.001" name="quantity[]" placeholder="0" min="0"
                       oninput="calcTotal()" required value="${prefill ? prefill.quantity : ''}">
            </div>
            <div class="field-group">
                <label>RATE (₹) *</label>
                <input type="number" step="0.01" name="rate[]" placeholder="0.00" min="0"
                       oninput="calcTotal()" required value="${prefill ? prefill.rate : ''}">
            </div>
            <div class="field-group">
                <label>DISCOUNT %</label>
                <input type="number" step="0.01" name="discount_pct[]" min="0" max="100"
                       oninput="calcTotal()" value="${prefill ? prefill.discount_pct : '0'}">
            </div>
            <div class="field-group">
                <label>LINE TOTAL</label>
                <input type="text" id="lt_${itemCount}" readonly class="line-total-field">
            </div>
        </div>
    `;
    document.getElementById('itemsContainer').appendChild(div);

    if (prefill && prefill.category_id) {
        var catSel = div.querySelector('select[name="category_id_item[]"]');
        var idx = itemCount;
        loadProducts(catSel, idx, prefill.product_id);
    }
}

function removeItem(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
    calcTotal();
}

function loadProducts(sel, idx, preselectId) {
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
                prods.map(p =>
                    `<option value="${p.id}" ${preselectId && p.id == preselectId ? 'selected' : ''}>${escapeHtml(p.product_name)} (₹${p.price})</option>`
                ).join('');
            calcTotal();
        });
}

function calcTotal() {
    var qtys  = document.getElementsByName('quantity[]');
    var rates = document.getElementsByName('rate[]');
    var discs = document.getElementsByName('discount_pct[]');
    var subtotal = 0;

    for (var i = 0; i < qtys.length; i++) {
        var q  = parseFloat(qtys[i].value)  || 0;
        var r  = parseFloat(rates[i].value) || 0;
        var d  = parseFloat(discs[i].value) || 0;
        var lt = q * r * (1 - d / 100);
        subtotal += lt;

        var itemDiv = qtys[i].closest('.item-card');
        if (itemDiv) {
            var ltInput = itemDiv.querySelector('input[readonly].line-total-field');
            if (ltInput) ltInput.value = '₹' + lt.toFixed(2);
        }
    }

    var gst  = document.getElementById('applyGst').checked;
    var cgst = gst ? subtotal * 0.025 : 0;
    var sgst = gst ? subtotal * 0.025 : 0;
    var tc   = parseFloat(document.getElementById('transport_cost').value) || 0;
    var grand = subtotal + cgst + sgst + tc;

    document.getElementById('disp_sub').textContent       = '₹' + subtotal.toFixed(2);
    document.getElementById('disp_cgst').textContent      = '₹' + cgst.toFixed(2);
    document.getElementById('disp_sgst').textContent      = '₹' + sgst.toFixed(2);
    document.getElementById('disp_transport').textContent = '₹' + tc.toFixed(2);
    document.getElementById('disp_grand').textContent     = '₹' + grand.toFixed(2);
    document.getElementById('gstRow').style.display       = gst ? 'flex' : 'none';
    document.getElementById('sgstRow').style.display      = gst ? 'flex' : 'none';

    var ps = document.getElementById('salePayStatus').value;
    if (ps === 'Partial Paid') {
        var paid = parseFloat(document.getElementById('saleAmtPaid').value) || 0;
        document.getElementById('saleBalance').value = '₹' + (grand - paid).toFixed(2);
    }
}

function toggleSalePartial(v) {
    var isPartial = v === 'Partial Paid';
    document.getElementById('salePartialDiv').style.display = isPartial ? 'block' : 'none';
    document.getElementById('saleBalanceDiv').style.display = isPartial ? 'block' : 'none';
    calcTotal();
}

function resetToOriginal() {
    if (confirm('Reset all changes back to original values?')) {
        location.reload();
    }
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => { 
            alert.style.opacity = '0'; 
            setTimeout(() => alert.remove(), 300); 
        }, 5000);
    });

    // Load existing items
    existingItems.forEach(function(item) {
        addItem(item);
    });

    if (existingItems.length === 0) addItem();
    calcTotal();
});
</script>

<?php include 'sidebar_close.php'; ?>