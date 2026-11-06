<?php
require_once 'config.php';
requireLogin();
$cat_id = intval($_GET['cat_id']);
if (!$cat_id) { echo '<p>No category selected.</p>'; exit(); }

$res = $conn->query("SELECT s.*, pc.category_name, pc.price_per FROM stock s 
                     JOIN product_categories pc ON s.category_id=pc.id
                     WHERE s.category_id=$cat_id AND s.remaining_stock > 0 
                     ORDER BY s.batch_date ASC");

if (!$res || $res->num_rows === 0) {
    echo '<p style="color:#888;font-style:italic;">No old stock available for this category.</p>';
    exit();
}

echo '<table style="width:100%;border-collapse:collapse;font-size:13px;">
<thead><tr>
<th style="background:#f5f5f5;padding:8px 12px;text-align:left;">Batch Date</th>
<th style="background:#f5f5f5;padding:8px 12px;text-align:left;">Category</th>
<th style="background:#f5f5f5;padding:8px 12px;text-align:left;">New Stock Added</th>
<th style="background:#f5f5f5;padding:8px 12px;text-align:left;">Remaining Stock</th>
<th style="background:#f5f5f5;padding:8px 12px;text-align:left;">Cost Per Unit</th>
<th style="background:#f5f5f5;padding:8px 12px;text-align:left;">Total Cost</th>
</tr></thead><tbody>';

while ($row = $res->fetch_assoc()) {
    echo "<tr>
    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>" . date('d M Y', strtotime($row['batch_date'])) . "</td>
    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>" . htmlspecialchars($row['category_name']) . "</td>
    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>" . $row['new_stock'] . " " . $row['stock_per'] . "</td>
    <td style='padding:8px 12px;border-bottom:1px solid #eee;font-weight:700;color:#2e7d32;'>" . $row['remaining_stock'] . " " . $row['stock_per'] . "</td>
    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>₹" . number_format($row['cost_per_unit'], 2) . "</td>
    <td style='padding:8px 12px;border-bottom:1px solid #eee;'>₹" . number_format($row['total_cost'], 2) . "</td>
    </tr>";
}
echo '</tbody></table>';
?>
