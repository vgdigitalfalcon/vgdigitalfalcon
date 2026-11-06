<?php
$page_title = 'Form received | VG Digital Falcon';
require __DIR__ . '/includes/header.php';
$name = htmlspecialchars($_GET['n'] ?? 'there');
?>
<section class="card center">
  <img src="assets/img/dflogo-black.png" alt="VG Digital Falcon" class="logo-sm">
  <h1>Form received</h1>
  <p class="lede">Thank you <?= $name ?>. Your requirement form is saved. Our team will use it to prepare structure and quote.</p>
  <p><a class="btn btn-orange" href="index.php">Submit another</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
