<?php
require_once 'config.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo "Invalid sale."; exit(); }

$r = $conn->query("SELECT s.*, c.customer_name, c.shop_name, c.address, c.city, c.state, c.phone, c.email, c.gst_no
                   FROM sales s JOIN customers c ON s.customer_id=c.id WHERE s.id=$id");
if (!$r || $r->num_rows === 0) { echo "Sale not found."; exit(); }
$sale = $r->fetch_assoc();

$ri = $conn->query("SELECT si.*, p.product_name, p.product_code, p.hsn_code, pc.category_name
                    FROM sale_items si
                    JOIN products p ON si.product_id=p.id
                    JOIN product_categories pc ON si.category_id=pc.id
                    WHERE si.sale_id=$id");
$items = [];
while ($row = $ri->fetch_assoc()) $items[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice <?php echo htmlspecialchars($sale['invoice_no']); ?> — MKN Kadalaimittai & Snacks</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --brand:       #7B2D00;
    --brand-light: #A33A00;
    --brand-dark:  #4A1A00;
    --gold:        #C8860A;
    --gold-light:  #F5C842;
    --cream:       #FDF8F0;
    --warm-gray:   #F5F0E8;
    --border:      #E8DDD0;
    --text:        #1C1410;
    --text-muted:  #7A6A5A;
    --green:       #1B5E20;
    --green-bg:    #E8F5E9;
    --red:         #B71C1C;
    --red-bg:      #FFEBEE;
    --orange:      #E65100;
    --orange-bg:   #FFF3E0;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: #EDE7DC;
    color: var(--text);
    padding: 28px 20px;
    min-height: 100vh;
  }

  /* ── ACTIONS BAR ── */
  .action-bar {
    max-width: 860px;
    margin: 0 auto 20px;
    display: flex;
    gap: 10px;
  }
  .btn-print {
    background: var(--brand);
    color: #fff;
    border: none;
    padding: 10px 22px;
    border-radius: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: background 0.2s;
  }
  .btn-print:hover { background: var(--brand-light); }
  .btn-back {
    background: #fff;
    color: var(--text);
    border: 1px solid var(--border);
    padding: 10px 22px;
    border-radius: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
  }

  /* ── INVOICE WRAPPER ── */
  .invoice {
    max-width: 860px;
    margin: 0 auto;
    background: #fff;
    border-radius: 2px;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
  }

  /* ── TOP BAND ── */
  .top-band {
    height: 6px;
    background: linear-gradient(90deg, var(--brand-dark), var(--brand), var(--gold), var(--gold-light), var(--gold), var(--brand), var(--brand-dark));
  }

  /* ── HEADER ── */
  .inv-header {
    background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand) 55%, var(--brand-light) 100%);
    padding: 28px 32px 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
    overflow: hidden;
  }
  .inv-header::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
  }
  .inv-header::after {
    content: '';
    position: absolute;
    bottom: -60px; left: 180px;
    width: 250px; height: 250px;
    border-radius: 50%;
    background: rgba(255,255,255,0.03);
  }

  .company-brand { position: relative; z-index: 1; }
  .company-logo-line {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
  }
  .logo-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(0,0,0,0.3);
  }
  .company-name {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.5px;
    line-height: 1.15;
  }
  .company-tagline {
    font-size: 10px;
    color: rgba(255,255,255,0.65);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-top: 2px;
  }
  .company-contact {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 3px;
  }
  .company-contact span {
    font-size: 11px;
    color: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .company-contact .ci { font-size: 12px; }

  .inv-meta { text-align: right; position: relative; z-index: 1; }
  .inv-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.55);
    margin-bottom: 4px;
  }
  .inv-number {
    font-family: 'DM Mono', monospace;
    font-size: 22px;
    font-weight: 500;
    color: var(--gold-light);
    letter-spacing: 1px;
  }
  .inv-detail-list {
    margin-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-end;
  }
  .inv-detail-list span {
    font-size: 11.5px;
    color: rgba(255,255,255,0.75);
  }
  .inv-detail-list strong { color: #fff; }

  /* STATUS BADGE */
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 8px;
  }
  .status-badge.paid    { background: var(--green-bg);  color: var(--green); }
  .status-badge.unpaid  { background: var(--red-bg);    color: var(--red); }
  .status-badge.partial { background: var(--orange-bg); color: var(--orange); }
  .status-badge::before { content: '●'; font-size: 8px; }

  /* ── ADDRESSES ── */
  .addresses {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    border-bottom: 1px solid var(--border);
  }
  .addr-block {
    padding: 20px 24px;
    border-right: 1px solid var(--border);
  }
  .addr-block:last-child { border-right: none; }
  .addr-block.shaded { background: var(--warm-gray); }
  .addr-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--brand);
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 2px solid var(--brand);
    display: inline-block;
  }
  .addr-block .cname {
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: var(--brand-dark);
    margin-bottom: 4px;
  }
  .addr-block .shop { font-size: 12px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
  .addr-block p { font-size: 11.5px; color: var(--text-muted); line-height: 1.7; }
  .addr-block p .em { margin-right: 5px; }

  .inv-detail-box p {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 12px;
    font-size: 11.5px;
    color: var(--text-muted);
    padding: 3px 0;
    border-bottom: 1px dashed var(--border);
  }
  .inv-detail-box p:last-child { border-bottom: none; }
  .inv-detail-box p strong {
    color: var(--text);
    font-weight: 600;
    text-align: right;
    flex-shrink: 0;
  }

  /* ── ITEMS TABLE ── */
  .table-wrap { overflow-x: auto; }
  table.items { width: 100%; border-collapse: collapse; }
  table.items thead tr {
    background: linear-gradient(90deg, var(--brand-dark), var(--brand));
  }
  table.items th {
    padding: 11px 12px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.85);
    white-space: nowrap;
  }
  table.items th:not(:first-child):not(:nth-child(2)):not(:last-child) { text-align: center; }
  table.items th:last-child { text-align: right; }
  table.items tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
  table.items tbody tr:nth-child(even) { background: var(--cream); }
  table.items tbody tr:hover { background: #FFF3E8; }
  table.items td { padding: 10px 12px; font-size: 12px; vertical-align: middle; }
  table.items td:not(:first-child):not(:nth-child(2)):not(:last-child) { text-align: center; }
  table.items td:last-child { text-align: right; }

  .sno {
    width: 32px; height: 32px;
    background: var(--brand);
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    font-weight: 700;
    margin: 0 auto;
  }
  .prod-name { font-weight: 600; color: var(--brand-dark); font-size: 12.5px; }
  .prod-cat { font-size: 10px; color: var(--text-muted); margin-top: 2px; font-style: italic; }
  .hsn-code { font-family: 'DM Mono', monospace; font-size: 11px; color: var(--text-muted); }
  .line-total { font-weight: 700; font-size: 13px; color: var(--brand-dark); }

  /* ── TOTALS + BANK SECTION ── */
  .bottom-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-top: 1px solid var(--border);
  }

  /* Bank + QR */
  .bank-section {
    padding: 20px 24px;
    border-right: 1px solid var(--border);
    background: var(--warm-gray);
  }
  .section-title {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--brand);
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 2px solid var(--brand);
    display: inline-block;
  }
  .bank-details { margin-bottom: 14px; }
  .bank-details p { font-size: 11.5px; color: var(--text-muted); padding: 3px 0; border-bottom: 1px dashed var(--border); display: flex; justify-content: space-between; }
  .bank-details p:last-child { border-bottom: none; }
  .bank-details p strong { color: var(--text); font-weight: 600; }
  .upi-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
  }
  .qr-img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 4px;
    border: 1px solid var(--border);
    flex-shrink: 0;
  }
  .qr-fallback {
    width: 80px; height: 80px;
    background: var(--warm-gray);
    border: 2px dashed var(--border);
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; color: var(--text-muted); text-align: center;
    line-height: 1.4;
    flex-shrink: 0;
  }
  .upi-info p { font-size: 11.5px; color: var(--text-muted); line-height: 1.8; }
  .upi-info strong { color: var(--text); font-weight: 600; }
  .upi-apps { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
  .upi-app-tag {
    padding: 3px 10px;
    background: var(--brand);
    color: #fff;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.3px;
  }

  /* Totals */
  .totals-section {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .totals-table { width: 100%; }
  .totals-table tr td { padding: 5px 0; font-size: 12px; }
  .totals-table tr td:last-child { text-align: right; font-weight: 600; }
  .totals-table tr td:first-child { color: var(--text-muted); }
  .totals-table .divider td { padding: 4px 0; }
  .totals-table .divider td hr { border: none; border-top: 1px solid var(--border); }
  .totals-table .grand-row td {
    padding: 10px 0 6px;
    font-size: 16px !important;
    font-weight: 800 !important;
    color: var(--brand-dark) !important;
    border-top: 2px solid var(--brand);
  }
  .totals-table .grand-row td:first-child {
    font-family: 'Playfair Display', serif;
    letter-spacing: 0.5px;
  }

  .payment-summary {
    margin-top: 16px;
    background: var(--warm-gray);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 14px;
  }
  .payment-summary .ps-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: 12px;
    border-bottom: 1px dashed var(--border);
  }
  .payment-summary .ps-row:last-child { border-bottom: none; }
  .ps-row .label { color: var(--text-muted); }
  .ps-row .val-green { color: var(--green); font-weight: 700; }
  .ps-row .val-red   { color: var(--red);   font-weight: 700; }

  /* ── SIGNATURE & AUTH ── */
  .sign-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-top: 1px solid var(--border);
    padding: 20px 28px;
    gap: 20px;
    background: var(--cream);
  }
  .sign-block { display: flex; flex-direction: column; }
  .sign-block .sign-label {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--text-muted);
    margin-bottom: 40px;
  }
  .sign-block .sign-line {
    border-bottom: 1.5px solid var(--brand);
    padding-bottom: 6px;
    margin-bottom: 6px;
  }
  .sign-block .sign-for {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--brand-dark);
    font-style: italic;
  }
  .sign-block .sign-for-sub {
    font-size: 10px;
    color: var(--text-muted);
    margin-top: 2px;
  }
  .auth-box {
    text-align: right;
  }
  .auth-seal {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px double var(--brand);
    padding: 12px;
    position: relative;
    margin-bottom: 8px;
  }
  .auth-seal::before {
    content: '';
    position: absolute;
    inset: 5px;
    border-radius: 50%;
    border: 1px solid var(--gold);
  }
  .auth-seal-text {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--brand);
    text-align: center;
    line-height: 1.3;
    position: relative;
    z-index: 1;
  }
  .auth-seal-icon { font-size: 20px; margin-bottom: 4px; position: relative; z-index: 1; }
  .sign-company {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--brand-dark);
    font-style: italic;
    margin-top: 4px;
  }
  .sign-title {
    font-size: 10px;
    color: var(--text-muted);
  }

  /* ── FOOTER ── */
  .inv-footer {
    background: linear-gradient(135deg, var(--brand-dark), var(--brand));
    padding: 14px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
  }
  .inv-footer .thank { font-size: 13px; font-weight: 600; color: var(--gold-light); font-style: italic; }
  .inv-footer .gen-info { font-size: 10px; color: rgba(255,255,255,0.6); text-align: right; }
  .auto-gen-banner {
    background: var(--warm-gray);
    border-top: 1px solid var(--border);
    padding: 8px 28px;
    text-align: center;
    font-size: 10.5px;
    color: var(--text-muted);
    font-style: italic;
    letter-spacing: 0.3px;
  }
  .auto-gen-banner span { margin: 0 6px; color: var(--border); }

  /* ── PRINT ── */
  @media print {
    body { padding: 0; background: #fff; }
    .no-print { display: none !important; }
    .invoice { box-shadow: none; border-radius: 0; max-width: 100%; }
    .top-band { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .inv-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    table.items thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .inv-footer { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>

<div class="action-bar no-print">
  <button class="btn-print" onclick="window.print()">🖨️ Print Invoice</button>
  <a class="btn-back" href="sales_dashboard.php">← Back to Dashboard</a>
</div>

<div class="invoice">

  <!-- TOP COLOUR BAND -->
  <div class="top-band"></div>

  <!-- HEADER -->
  <div class="inv-header">
    <div class="company-brand">
      <div class="company-logo-line">
        <div>
          <div class="company-name">MKN KADALAIMITTAI &amp; SNACKS</div>
        </div>
      </div>
      <div class="company-contact">
        <span><span class="ci">📍</span> 175/4, Kilaku Pandavarmangalam, Kovilpatti, Tamil Nadu – 628502</span>
        <span><span class="ci">📞</span> +91 93449 00855 &nbsp;|&nbsp; +91 88706 00855 &nbsp;|&nbsp; +91 78269 56973</span>
        <span><span class="ci">✉️</span> mknkadalaimittai@gmail.com</span>
      </div>
    </div>
    <div class="inv-meta">
      <div class="inv-label">Tax Invoice</div>
      <div class="inv-number"><?php echo htmlspecialchars($sale['invoice_no']); ?></div>
      <div class="inv-detail-list">
        <span>Date: <strong><?php echo date('d M Y', strtotime($sale['bill_date'])); ?></strong></span>
      </div>
      <?php
        $bs = $sale['payment_status'];
        $bc = $bs === 'Paid' ? 'paid' : ($bs === 'Not Paid' ? 'unpaid' : 'partial');
      ?>
      <div class="status-badge <?php echo $bc; ?>"><?php echo $bs; ?></div>
    </div>
  </div>

  <!-- ADDRESSES -->
  <div class="addresses">
    <!-- Bill To -->
    <div class="addr-block">
      <div class="addr-label">Bill To</div>
      <div class="cname"><?php echo htmlspecialchars($sale['customer_name']); ?></div>
      <?php if ($sale['shop_name']): ?>
        <div class="shop"><?php echo htmlspecialchars($sale['shop_name']); ?></div>
      <?php endif; ?>
      <p><?php echo nl2br(htmlspecialchars($sale['address'])); ?></p>
      <p><?php echo htmlspecialchars($sale['city']); ?><?php echo $sale['state'] ? ', ' . htmlspecialchars($sale['state']) : ''; ?></p>
      <?php if ($sale['phone']): ?><p><span class="em">📞</span><?php echo htmlspecialchars($sale['phone']); ?></p><?php endif; ?>
      <?php if ($sale['email']): ?><p><span class="em">✉️</span><?php echo htmlspecialchars($sale['email']); ?></p><?php endif; ?>
      <?php if ($sale['gst_no']): ?><p><span class="em">🏷</span>GST: <strong><?php echo htmlspecialchars($sale['gst_no']); ?></strong></p><?php endif; ?>
    </div>

    <!-- Ship To (reuse billing if no separate) -->
    <div class="addr-block shaded">
      <div class="addr-label">Ship To</div>
      <div class="cname"><?php echo htmlspecialchars($sale['customer_name']); ?></div>
      <?php if ($sale['shop_name']): ?>
        <div class="shop"><?php echo htmlspecialchars($sale['shop_name']); ?></div>
      <?php endif; ?>
      <p><?php echo nl2br(htmlspecialchars($sale['address'])); ?></p>
      <p><?php echo htmlspecialchars($sale['city']); ?><?php echo $sale['state'] ? ', ' . htmlspecialchars($sale['state']) : ''; ?></p>
    </div>

    <!-- Invoice Details -->
    <div class="addr-block">
      <div class="addr-label">Invoice Details</div>
      <div class="inv-detail-box">
        <p>Invoice No <strong><?php echo htmlspecialchars($sale['invoice_no']); ?></strong></p>
        <p>Date <strong><?php echo date('d M Y', strtotime($sale['bill_date'])); ?></strong></p>
        <p>Transport <strong><?php echo htmlspecialchars($sale['transport_mode']); ?></strong></p>
        <?php if ($sale['transport_cost'] > 0): ?>
        <p>Transport Cost <strong>₹<?php echo number_format($sale['transport_cost'], 2); ?></strong></p>
        <?php endif; ?>
        <p>Payment Status <strong><?php echo $bs; ?></strong></p>
      </div>
    </div>
  </div>

  <!-- ITEMS TABLE -->
  <div class="table-wrap">
    <table class="items">
      <thead>
        <tr>
          <th style="text-align:center;width:42px;">#</th>
          <th>Product Description</th>
          <th>HSN</th>
          <th>Weight</th>
          <th>MRP</th>
          <th>Qty</th>
          <th>Rate (₹)</th>
          <th>Disc %</th>
          <th>Amount (₹)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $item): ?>
        <tr>
          <td><div class="sno"><?php echo $i + 1; ?></div></td>
          <td>
            <div class="prod-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
            <div class="prod-cat"><?php echo htmlspecialchars($item['category_name']); ?></div>
          </td>
          <td><span class="hsn-code"><?php echo htmlspecialchars($item['hsn_code']); ?></span></td>
          <td><?php echo $item['weight'] > 0 ? $item['weight'] . ' kg' : '—'; ?></td>
          <td>₹<?php echo number_format($item['mrp'], 2); ?></td>
          <td><strong><?php echo $item['quantity']; ?></strong></td>
          <td>₹<?php echo number_format($item['rate'], 2); ?></td>
          <td><?php echo $item['discount_pct']; ?>%</td>
          <td><span class="line-total">₹<?php echo number_format($item['line_total'], 2); ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- BOTTOM: BANK + TOTALS -->
  <div class="bottom-section">

    <!-- Bank & UPI -->
    <div class="bank-section">
      <div class="section-title">Payment Details</div>

      <div class="bank-details">
        <p>Account Name <strong>Madan Nirmal M</strong></p>
        <p>UPI Number <strong>+91 88706 00855</strong></p>
        <p>UPI Name <strong>Madan Nirmal M</strong></p>
      </div>

      <div class="upi-row">
        <?php
          $qr_path = 'images/scanner.png';
          if (file_exists($qr_path)):
        ?>
          <img src="<?php echo $qr_path; ?>" alt="Payment QR Code" class="qr-img">
        <?php else: ?>
          <div class="qr-fallback">📱<br>Scan to<br>Pay</div>
        <?php endif; ?>
        <div class="upi-info">
          <p><strong>Scan &amp; Pay Instantly</strong></p>
          <p>UPI ID: <strong>+91 88706 00855</strong></p>
          <p>Name: <strong>Madan Nirmal M</strong></p>
          <div class="upi-apps">
            <span class="upi-app-tag">GPay</span>
            <span class="upi-app-tag">Paytm</span>
            <span class="upi-app-tag">PhonePe</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Totals -->
    <div class="totals-section">
      <div class="section-title">Summary</div>
      <table class="totals-table">
        <tr>
          <td>Subtotal</td>
          <td>₹<?php echo number_format($sale['subtotal'], 2); ?></td>
        </tr>
        <?php if ($sale['apply_gst']): ?>
        <tr>
          <td>CGST (2.5%)</td>
          <td>₹<?php echo number_format($sale['cgst'], 2); ?></td>
        </tr>
        <tr>
          <td>SGST (2.5%)</td>
          <td>₹<?php echo number_format($sale['sgst'], 2); ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($sale['transport_cost'] > 0): ?>
        <tr>
          <td>Transportation Cost</td>
          <td>₹<?php echo number_format($sale['transport_cost'], 2); ?></td>
        </tr>
        <?php endif; ?>
        <tr class="divider"><td colspan="2"><hr></td></tr>
        <tr class="grand-row">
          <td>Grand Total</td>
          <td>₹<?php echo number_format($sale['grand_total'], 2); ?></td>
        </tr>
      </table>

      <div class="payment-summary">
        <div class="ps-row">
          <span class="label">Payment Status</span>
          <span class="status-badge <?php echo $bc; ?>" style="margin-top:0;"><?php echo $bs; ?></span>
        </div>
        <div class="ps-row">
          <span class="label">Amount Paid</span>
          <span class="val-green">₹<?php echo number_format($sale['amount_paid'], 2); ?></span>
        </div>
        <div class="ps-row">
          <span class="label">Amount Due</span>
          <span class="val-red">₹<?php echo number_format($sale['amount_due'], 2); ?></span>
        </div>
      </div>
    </div>

  </div>

  <!-- SIGNATURE ROW -->
  <div class="sign-row">
    <div class="sign-block">
      <div class="sign-label">Customer Signature &amp; Acknowledgement</div>
      <div class="sign-line"></div>
      <div class="sign-for">Receiver's Signature</div>
      <div class="sign-for-sub">Name &amp; Date</div>
    </div>
    <div class="sign-block auth-box">
      <div class="sign-label" style="text-align:right;">Authorised Signatory</div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;">
        <div class="auth-seal">
          <div class="auth-seal-icon">✦</div>
          <div class="auth-seal-text">Authorised<br>Signatory</div>
        </div>
        <div class="sign-company">MKN KADALAIMITTAI &amp; SNACKS</div>
        <div class="sign-title">Authorised Signature</div>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="inv-footer">
    <div class="thank">🙏 Thank you for your business!</div>
    <div class="gen-info">
      MKN KADALAIMITTAI &amp; SNACKS &nbsp;|&nbsp; Kovilpatti, Tamil Nadu<br>
      Generated: <?php echo date('d M Y, h:i A'); ?>
    </div>
  </div>

  <!-- AUTO GENERATED NOTICE -->
  <div class="auto-gen-banner">
    <span>⚡</span> This is an auto generated invoice. <span>|</span> Please retain this for your records. <span>⚡</span>
  </div>

</div>
</body>
</html>