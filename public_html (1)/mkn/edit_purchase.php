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
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $amount = (float) $_POST['amount'];
    $unit_type = $conn->real_escape_string($_POST['unit_type']);
    $quantity = (int) $_POST['quantity'];
    $date = $_POST['date'];

    $update = "UPDATE purchases 
               SET purchase_name = '$item_name',
                   amount = '$amount',
                   unit_type = '$unit_type',
                   quantity = '$quantity',
                   date = '$date'
               WHERE id = $id";
    if ($conn->query($update)) {
        header("Location: view_purchase.php?id=$id");
        exit();
    } else {
        $message = "Error updating record: " . $conn->error;
    }
}

// Fetch current data
$query = "SELECT p.id, COALESCE(pr.product_name, p.purchase_name) AS item_name, 
                 p.amount, p.unit_type, p.quantity, p.date
          FROM purchases p
          LEFT JOIN products pr ON p.product_id = pr.id
          WHERE p.id = $id";
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
  <title>Edit Purchase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow-lg">
    <div class="card-header bg-info text-white">
      <h4>Edit Purchase (ID: <?= $row['id'] ?>)</h4>
    </div>
    <div class="card-body">
      <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Item Name</label>
          <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars($row['item_name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" name="amount" class="form-control" value="<?= $row['amount'] ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Unit Type</label>
          <input type="text" name="unit_type" class="form-control" value="<?= htmlspecialchars($row['unit_type']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control" value="<?= $row['quantity'] ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="<?= $row['date'] ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="purchases_list.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>
