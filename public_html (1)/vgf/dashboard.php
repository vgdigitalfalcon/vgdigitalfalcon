<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_admin();

$rows = db()->query('SELECT * FROM submissions ORDER BY id DESC')->fetchAll();

function arch_badge(string $t): array {
    if (str_contains($t, 'Option 1')) return ['Option 1 · one site', 'b-a'];
    if (str_contains($t, 'Option 2')) return ['Option 2 · hub + sites', 'b-b'];
    if (str_contains($t, 'Option 3')) return ['Option 3 · separate', 'b-c'];
    return [$t !== '' ? $t : 'Not selected', 'b-b'];
}

$page_title = 'Submissions | VG Digital Falcon';
$admin = true;
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>Submissions</h1>
  <p class="lede"><?= count($rows) ?> saved form<?= count($rows) === 1 ? '' : 's' ?>. Open a row to view answers or download PDF.</p>
</section>
<section class="card">
  <?php if (!$rows): ?>
    <p>No submissions yet. Share <b>index.php</b> with the client.</p>
  <?php else: ?>
  <div style="overflow:auto">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>When</th><th>Contact</th><th>Architecture</th><th>Package</th><th>Budget</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): [$short, $cls] = arch_badge((string)$r['architecture']); ?>
        <tr>
          <td>#<?= (int)$r['id'] ?></td>
          <td><?= h($r['created_at']) ?></td>
          <td>
            <strong><?= h($r['contact_name']) ?></strong><br>
            <?= h($r['contact_email']) ?><br>
            <?= h($r['contact_phone']) ?>
          </td>
          <td><span class="badge <?= $cls ?>"><?= h($short) ?></span></td>
          <td><?= h($r['package']) ?></td>
          <td><?= h($r['budget']) ?></td>
          <td>
            <a class="btn btn-line" href="view.php?id=<?= (int)$r['id'] ?>">View</a>
            <a class="btn btn-blue" href="pdf.php?id=<?= (int)$r['id'] ?>">PDF</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
