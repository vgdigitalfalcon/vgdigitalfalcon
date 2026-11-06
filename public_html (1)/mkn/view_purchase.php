<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];

$query = "
    SELECT p.id,
           COALESCE(pr.product_name, p.purchase_name) AS item_name,
           p.amount, p.unit_type, p.quantity, p.date
    FROM purchases p
    LEFT JOIN products pr ON p.product_id = pr.id
    WHERE p.id = $id
";
$result = $conn->query($query);
if ($result->num_rows === 0) {
    die("Purchase not found");
}
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Purchase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow-lg">
    <div class="card-header bg-primary text-white">
      <h4>Purchase Details (ID: <?= $row['id'] ?>)</h4>
    </div>
    <div class="card-body">
      <p><strong>Item Name:</strong> <?= htmlspecialchars($row['item_name']) ?></p>
      <p><strong>Amount:</strong> ₹<?= number_format($row['amount'], 2) ?></p>
      <p><strong>Unit Type:</strong> <?= htmlspecialchars($row['unit_type']) ?></p>
      <p><strong>Quantity:</strong> <?= $row['quantity'] ?></p>
      <p><strong>Date:</strong> <?= $row['date'] ?></p>
    </div>
    <div class="card-footer text-end">
      <a href="purchases_list.php" class="btn btn-secondary">Back</a>
      <a href="edit_purchase.php?id=<?= $row['id'] ?>" class="btn btn-info text-white">Edit</a>
    </div>
  </div>
</div>
</body>
</html>
