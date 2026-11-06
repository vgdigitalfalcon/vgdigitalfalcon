<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/fields.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM submissions WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    header('Location: dashboard.php');
    exit;
}
$payload = json_decode($row['payload'], true) ?: [];

$page_title = 'Submission #' . $id . ' | VG Digital Falcon';
$admin = true;
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>Submission #<?= $id ?></h1>
  <p class="lede"><?= h($row['created_at']) ?> · <?= h($row['contact_name']) ?> · <?= h($row['contact_email']) ?></p>
  <p>
    <a class="btn btn-blue" href="pdf.php?id=<?= $id ?>">Download PDF</a>
    <a class="btn btn-line" href="dashboard.php">Back</a>
    <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete this submission?')">
      <input type="hidden" name="id" value="<?= $id ?>">
      <button class="btn btn-danger" type="submit">Delete</button>
    </form>
  </p>
</section>
<section class="card">
  <div class="kv">
    <?php foreach (crf_labels() as $key => $label): ?>
      <div><b><?= h($label) ?></b></div>
      <div><?= nl2br(h(field($payload, $key))) ?></div>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
