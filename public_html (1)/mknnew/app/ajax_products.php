<?php
require_once 'config.php';
requireLogin();
$cat_id = intval($_GET['cat_id']);
$res = $conn->query("SELECT id, product_name, price FROM products WHERE category_id=$cat_id ORDER BY product_name");
$prods = [];
while ($r = $res->fetch_assoc()) $prods[] = $r;
header('Content-Type: application/json');
echo json_encode($prods);
?>
