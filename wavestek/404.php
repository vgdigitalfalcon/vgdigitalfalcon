<?php
require_once 'includes/config.php';
$page_title = 'Page Not Found';
require_once 'includes/header.php';
?>
<section style="min-height:60vh;display:flex;align-items:center;background:var(--light)">
  <div class="container text-center py-5">
    <div style="font-family:var(--font-head);font-size:120px;font-weight:800;color:var(--red);line-height:1;opacity:0.15">404</div>
    <div style="margin-top:-60px;position:relative;z-index:1">
      <i class="fas fa-bolt" style="font-size:56px;color:var(--red);margin-bottom:20px;display:block"></i>
      <h1 class="font-head fw-700 text-uppercase" style="font-size:36px">Page Not Found</h1>
      <p class="text-muted mt-3 mb-4" style="font-size:16px;max-width:480px;margin:auto">The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>
      <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
        <a href="index.php" class="btn btn-danger font-head fw-700 text-uppercase" style="padding:12px 32px;letter-spacing:1px"><i class="fas fa-home me-2"></i>Go Home</a>
        <a href="contact.php" class="btn btn-outline-secondary font-head fw-700 text-uppercase" style="padding:12px 32px;letter-spacing:1px"><i class="fas fa-envelope me-2"></i>Contact Us</a>
      </div>
    </div>
  </div>
</section>
<?php require_once 'includes/footer.php'; ?>
