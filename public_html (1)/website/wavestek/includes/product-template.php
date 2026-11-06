<?php
/**
 * WAVESTEK — REUSABLE PRODUCT PAGE TEMPLATE
 * Usage: Set $product array, then include this file.
 *
 * $product = [
 *   'title'       => 'Online UPS 1–3 KVA',
 *   'breadcrumb'  => [['Home','../index.php'],['Products','#'],['Online UPS','#'],['Single Phase','#']],
 *   'summary'     => 'Short 1-2 line description',
 *   'desc'        => 'Full HTML description',
 *   'icon'        => 'fas fa-plug',          // fallback icon
 *   'icon_color'  => '#3A5F8A',
 *   'specs'       => [['Capacity','1–3 KVA'], ...],
 *   'related'     => [['title','file','icon','color'], ...],
 *   'whatsapp_msg'=> 'I need a quote for ...',
 * ];
 */
if (!defined('SITE_NAME')) require_once dirname(__DIR__) . '/includes/config.php';
$page_title = $product['title'];
require_once dirname(__DIR__) . '/includes/header.php';
$wa_msg = urlencode('Hello Wavestek, I need a quote for: ' . ($product['whatsapp_msg'] ?? $product['title']));
?>

<!-- Product Banner -->
<section class="product-banner">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-3">
        <?php foreach ($product['breadcrumb'] as $i => $crumb):
          $last = ($i === count($product['breadcrumb'])-1);
        ?>
        <li class="breadcrumb-item <?php echo $last ? 'active' : ''; ?>">
          <?php echo $last ? htmlspecialchars($crumb[0]) : '<a href="' . htmlspecialchars($crumb[1]) . '">' . htmlspecialchars($crumb[0]) . '</a>'; ?>
        </li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <h1><?php echo htmlspecialchars($product['title']); ?></h1>
  </div>
</section>

<!-- Product Details -->
<section class="section-pad">
  <div class="container">
    <div class="row g-5">

      <!-- LEFT: Image Gallery -->
      <div class="col-lg-5">
        <div class="product-main-image">
          <i class="<?php echo $product['icon'] ?? 'fas fa-plug'; ?>" style="font-size:120px;color:<?php echo $product['icon_color'] ?? 'var(--blue)'; ?>;opacity:0.6" id="mainProductImg"></i>
        </div>
        <div class="product-thumbs mt-3">
          <?php for ($i=1;$i<=3;$i++): ?>
          <div class="product-thumb <?php echo $i===1 ? 'active' : ''; ?>">
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--light)">
              <i class="<?php echo $product['icon'] ?? 'fas fa-plug'; ?>" style="font-size:28px;color:<?php echo $product['icon_color'] ?? 'var(--blue)'; ?>;opacity:0.5"></i>
            </div>
          </div>
          <?php endfor; ?>
        </div>
        <!-- Share -->
        <div class="mt-3 d-flex gap-2">
          <a href="https://wa.me/?text=<?php echo urlencode('Check out ' . $product['title'] . ' from Wavestek Power Solutions: ' . (isset($_SERVER['HTTP_HOST']) ? 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : '')); ?>" class="btn btn-sm btn-outline-success font-head" target="_blank"><i class="fab fa-whatsapp me-1"></i> Share</a>
          <button class="btn btn-sm btn-outline-secondary font-head" onclick="navigator.clipboard.writeText(window.location.href).then(()=>alert('Link copied!'))"><i class="fas fa-copy me-1"></i> Copy Link</button>
        </div>
      </div>

      <!-- RIGHT: Info -->
      <div class="col-lg-7">
        <h1 class="product-info h1"><?php echo htmlspecialchars($product['title']); ?></h1>
        <p class="product-summary"><?php echo htmlspecialchars($product['summary'] ?? ''); ?></p>
        <div class="product-desc mb-4"><?php echo $product['desc'] ?? ''; ?></div>
        <div class="d-flex flex-wrap gap-3">
          <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>?text=<?php echo $wa_msg; ?>" class="btn btn-product-wa" target="_blank">
            <i class="fab fa-whatsapp me-2"></i>WhatsApp Now
          </a>
          <a href="../contact.php" class="btn btn-product-dl">
            <i class="fas fa-file-download me-2"></i>Download Catalogue
          </a>
        </div>
        <div class="mt-4 p-3" style="background:var(--light);border-radius:6px;display:flex;gap:24px;flex-wrap:wrap">
          <div class="d-flex align-items-center gap-2"><i class="fas fa-phone text-red"></i><a href="tel:<?php echo PHONE_PRIMARY; ?>" class="fw-600" style="font-size:14px"><?php echo PHONE_PRIMARY; ?></a></div>
          <div class="d-flex align-items-center gap-2"><i class="fas fa-envelope text-blue"></i><a href="mailto:<?php echo EMAIL_ADMIN; ?>" style="font-size:14px"><?php echo EMAIL_ADMIN; ?></a></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Specifications -->
<?php if (!empty($product['specs'])): ?>
<section class="specs-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-8">
        <p class="text-red font-head fw-700 text-uppercase mb-1" style="letter-spacing:2px">Technical Data</p>
        <h2 class="section-title mb-3">Key <span>Specifications</span></h2>
        <div class="section-line mb-4"></div>
        <div class="specs-wrap" style="border:1px solid var(--border);border-radius:6px;overflow:hidden">
          <?php foreach ($product['specs'] as $spec): ?>
          <div class="spec-row">
            <div class="spec-label"><?php echo htmlspecialchars($spec[0]); ?></div>
            <div class="spec-value"><?php echo htmlspecialchars($spec[1]); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="ms-lg-4 mt-4 mt-lg-0 p-4" style="background:var(--dark);border-radius:8px;color:#fff;position:sticky;top:100px">
          <h5 class="font-head fw-700 text-uppercase mb-3" style="letter-spacing:1px;color:var(--yellow)">Need This Product?</h5>
          <p style="font-size:14px;color:rgba(255,255,255,0.7);margin-bottom:20px">Contact us for custom configurations, bulk pricing and fast delivery across India.</p>
          <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>?text=<?php echo $wa_msg; ?>" class="btn btn-product-wa w-100 mb-2" target="_blank"><i class="fab fa-whatsapp me-2"></i>WhatsApp Now</a>
          <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn w-100" style="background:var(--red);color:#fff;font-family:var(--font-head);font-weight:700;text-transform:uppercase;letter-spacing:1px"><i class="fas fa-phone me-2"></i>Call Now</a>
          <hr style="border-color:rgba(255,255,255,0.15);margin:20px 0">
          <div style="font-size:13px;color:rgba(255,255,255,0.55)">
            <div class="d-flex align-items-center gap-2 mb-2"><i class="fas fa-check-circle" style="color:var(--yellow)"></i> Pan-India Delivery</div>
            <div class="d-flex align-items-center gap-2 mb-2"><i class="fas fa-check-circle" style="color:var(--yellow)"></i> 24/7 After-Sales Support</div>
            <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle" style="color:var(--yellow)"></i> Installation Included</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Related Products -->
<?php if (!empty($product['related'])): ?>
<section class="section-pad">
  <div class="container">
    <p class="text-red font-head fw-700 text-uppercase mb-1" style="letter-spacing:2px">Also Available</p>
    <h2 class="section-title mb-4">Related <span>Products</span></h2>
    <div class="row g-4">
      <?php foreach ($product['related'] as $r): ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <a href="<?php echo htmlspecialchars($r[1]); ?>" class="related-card">
          <div class="related-card-img">
            <i class="<?php echo $r[2] ?? 'fas fa-plug'; ?>" style="font-size:48px;color:<?php echo $r[3] ?? 'var(--blue)'; ?>;opacity:0.7"></i>
          </div>
          <div class="related-card-body">
            <h6><?php echo htmlspecialchars($r[0]); ?></h6>
            <small>View Product <i class="fas fa-arrow-right ms-1"></i></small>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
