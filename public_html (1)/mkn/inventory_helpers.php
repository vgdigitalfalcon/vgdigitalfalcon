<?php
/**
 * inventory_helpers.php
 * Shared inventory functions for MKN Bro's
 * Include after db.php
 */

/**
 * Get live stock for a product (sum of all batch remainders)
 */
function getProductStock($conn, $product_id) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity_remaining),0) AS stock FROM stock_batches WHERE product_id=? AND quantity_remaining>0");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['stock'];
}

/**
 * Get stock with status (in_stock / low_stock / out_of_stock)
 */
function getProductStockStatus($conn, $product_id) {
    $sql = "SELECT COALESCE(SUM(sb.quantity_remaining),0) AS stock, p.minimum_stock
            FROM products p
            LEFT JOIN stock_batches sb ON sb.product_id=p.id AND sb.quantity_remaining>0
            WHERE p.id=?
            GROUP BY p.id, p.minimum_stock";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stock = (int)($row['stock'] ?? 0);
    $min   = (int)($row['minimum_stock'] ?? 50);
    $status = $stock === 0 ? 'out_of_stock' : ($stock <= $min ? 'low_stock' : 'in_stock');
    return ['stock' => $stock, 'minimum_stock' => $min, 'status' => $status];
}

/**
 * Add stock batch (called from add_purchase or manual stock-in)
 */
function addStockBatch($conn, $product_id, $quantity, $batch_date, $added_by, $purchase_id = null, $remarks = '') {
    $stmt = $conn->prepare("INSERT INTO stock_batches (product_id, batch_date, quantity_in, quantity_remaining, purchase_id, added_by, remarks) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("isiisss", $product_id, $batch_date, $quantity, $quantity, $purchase_id, $added_by, $remarks);
    $stmt->execute();
    $batch_id = $conn->insert_id;

    // Audit log
    $stmt2 = $conn->prepare("INSERT INTO stock_audit_log (product_id, batch_id, change_type, quantity, reference_id, reason, done_by) VALUES (?,?,'stock_in',?,?,'Stock Added',?)");
    $stmt2->bind_param("iiiss", $product_id, $batch_id, $quantity, $purchase_id, $added_by);
    $stmt2->execute();

    return $batch_id;
}

/**
 * FIFO deduction – called after a sale is saved
 * Returns true if successful, false if insufficient stock (but still deducts what's available)
 */
function deductStockFIFO($conn, $product_id, $qty_needed, $sale_id, $done_by) {
    $batches = $conn->prepare("SELECT id, quantity_remaining FROM stock_batches WHERE product_id=? AND quantity_remaining>0 ORDER BY batch_date ASC, id ASC");
    $batches->bind_param("i", $product_id);
    $batches->execute();
    $result = $batches->get_result();

    $remaining = $qty_needed;
    while ($row = $result->fetch_assoc()) {
        if ($remaining <= 0) break;
        $deduct = min($remaining, (int)$row['quantity_remaining']);

        $upd = $conn->prepare("UPDATE stock_batches SET quantity_remaining = quantity_remaining - ? WHERE id=?");
        $upd->bind_param("ii", $deduct, $row['id']);
        $upd->execute();

        $neg = -$deduct;
        $log = $conn->prepare("INSERT INTO stock_audit_log (product_id, batch_id, change_type, quantity, reference_id, reason, done_by) VALUES (?,'?','sale_deduction',?,?,'Sale Invoice',?)");
        $log = $conn->prepare("INSERT INTO stock_audit_log (product_id, batch_id, change_type, quantity, reference_id, reason, done_by) VALUES (?,?,'sale_deduction',?,?,'Sale Invoice',?)");
        $log->bind_param("iiiis", $product_id, $row['id'], $neg, $sale_id, $done_by);
        $log->execute();

        $remaining -= $deduct;
    }

    return $remaining <= 0; // true = fully deducted
}

/**
 * Count low-stock products (for badge)
 */
function countLowStock($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM v_product_stock WHERE stock_status IN ('low_stock','out_of_stock')");
    if (!$result) return 0;
    return (int)$result->fetch_assoc()['cnt'];
}

/**
 * Manual stock adjustment (+/-)
 */
function adjustStock($conn, $product_id, $adjustment, $reason, $adjusted_by) {
    // Save adjustment record
    $stmt = $conn->prepare("INSERT INTO stock_adjustments (product_id, adjustment, reason, adjusted_by) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $product_id, $adjustment, $reason, $adjusted_by);
    $stmt->execute();

    if ($adjustment > 0) {
        // Add as a new batch
        addStockBatch($conn, $product_id, $adjustment, date('Y-m-d'), $adjusted_by, null, 'Manual Adjustment: ' . $reason);
    } else {
        // Deduct via FIFO (use 0 as reference_id for manual)
        $qty = abs($adjustment);
        $neg = -$qty;
        $batches = $conn->prepare("SELECT id, quantity_remaining FROM stock_batches WHERE product_id=? AND quantity_remaining>0 ORDER BY batch_date ASC, id ASC");
        $batches->bind_param("i", $product_id);
        $batches->execute();
        $result = $batches->get_result();
        $rem = $qty;
        while ($row = $result->fetch_assoc()) {
            if ($rem <= 0) break;
            $deduct = min($rem, (int)$row['quantity_remaining']);
            $conn->query("UPDATE stock_batches SET quantity_remaining = quantity_remaining - $deduct WHERE id = {$row['id']}");
            $neg2 = -$deduct;
            $log = $conn->prepare("INSERT INTO stock_audit_log (product_id, batch_id, change_type, quantity, reason, done_by) VALUES (?,?,'manual_adjust',?,?,?)");
            $log->bind_param("iiiss", $product_id, $row['id'], $neg2, $reason, $adjusted_by);
            $log->execute();
            $rem -= $deduct;
        }
    }
}
