<?php if (!defined('SITE_NAME')) require_once __DIR__ . '/config.php'; ?>

<!-- WhatsApp Float (desktop) -->
<a href="https://wa.me/<?php echo WHATSAPP_NO; ?>?text=Hello%20Wavestek%2C%20I%20need%20a%20quote" class="float-wa d-none d-md-flex" target="_blank" aria-label="WhatsApp">
  <i class="fab fa-whatsapp"></i>
</a>

<!-- Back To Top -->
<div id="backToTop" aria-label="Back to top"><i class="fas fa-chevron-up"></i></div>

<!-- Mobile Sticky CTA Bar -->
<div class="mobile-cta-bar">
  <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="bar-call"><i class="fas fa-phone-alt"></i>Call</a>
  <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>?text=Hello%20Wavestek%2C%20I%20need%20a%20quote" class="bar-wa" target="_blank"><i class="fab fa-whatsapp"></i>WhatsApp</a>
  <a href="<?php echo SITE_URL; ?>/contact.php" class="bar-quote"><i class="fas fa-file-alt"></i>Get Quote</a>
</div>

<!-- Footer -->
<footer class="site-footer">
  <div class="footer-top">
    <div class="container">
      <div class="row g-4">
        <!-- Brand -->
        <div class="col-lg-4">
          <div class="footer-brand">
            <div class="logo-wrap mb-3">
              <div class="logo-icon">
                <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect width="44" height="44" rx="4" fill="#D32F2F"/>
                  <path d="M24 6L10 26h12l-2 12 18-22H26l-2-10z" fill="#F5A623" stroke="#fff" stroke-width="1"/>
                </svg>
              </div>
              <div class="logo-text">
                <span class="logo-brand" style="color:#fff">WAVESTEK</span>
                <span class="logo-sub" style="color:#F5A623">POWER SOLUTIONS</span>
              </div>
            </div>
            <p class="footer-desc">Manufacturer, Sales &amp; Service Provider for Online UPS, Servo Voltage Stabilizers, Inverters and Batteries — serving 300+ clients across India since 1990.</p>
            <div class="footer-social">
              <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
              <a href="mailto:<?php echo EMAIL_ADMIN; ?>"><i class="fas fa-envelope"></i></a>
              <a href="tel:<?php echo PHONE_PRIMARY; ?>"><i class="fas fa-phone"></i></a>
            </div>
          </div>
        </div>
        <!-- Quick Links -->
        <div class="col-lg-2 col-sm-6">
          <h5 class="footer-heading">Quick Links</h5>
          <ul class="footer-links">
            <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
            <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
            <li><a href="<?php echo SITE_URL; ?>/service.php">Service</a></li>
            <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
          </ul>
        </div>
        <!-- Products -->
        <div class="col-lg-3 col-sm-6">
          <h5 class="footer-heading">Products</h5>
          <ul class="footer-links">
            <li><a href="<?php echo SITE_URL; ?>/products/ups-1-3kva.php">Online UPS (1–3 KVA)</a></li>
            <li><a href="<?php echo SITE_URL; ?>/products/ups-15-400kva.php">Three Phase UPS</a></li>
            <li><a href="<?php echo SITE_URL; ?>/products/stabilizer-single-phase.php">Servo Stabilizer</a></li>
            <li><a href="<?php echo SITE_URL; ?>/products/inverter-microtek.php">Inverters</a></li>
            <li><a href="<?php echo SITE_URL; ?>/products/battery-smf.php">SMF Batteries</a></li>
            <li><a href="<?php echo SITE_URL; ?>/products/solar-inverter.php">Solar Products</a></li>
          </ul>
        </div>
        <!-- Contact -->
        <div class="col-lg-3">
          <h5 class="footer-heading">Contact Us</h5>
          <ul class="footer-contact">
            <li><i class="fas fa-map-marker-alt"></i><span><strong>Sales Office:</strong><br><?php echo ADDRESS_SALES; ?></span></li>
            <li><i class="fas fa-industry"></i><span><strong>Factory:</strong><br><?php echo ADDRESS_FACTORY; ?></span></li>
            <li><i class="fas fa-phone"></i><a href="tel:<?php echo PHONE_PRIMARY; ?>"><?php echo PHONE_PRIMARY; ?></a></li>
            <li><i class="fas fa-phone"></i><a href="tel:<?php echo PHONE_SECONDARY; ?>"><?php echo PHONE_SECONDARY; ?></a></li>
            <li><i class="fas fa-envelope"></i><a href="mailto:<?php echo EMAIL_ADMIN; ?>"><?php echo EMAIL_ADMIN; ?></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <p>&copy; <?php echo date('Y'); ?> Wavestek Power Solutions. All Rights Reserved. | Designed for excellence in power solutions.</p>
    </div>
  </div>
</footer>

<!-- ========================
     QUICK QUOTE MODAL
======================== -->
<div class="modal fade" id="quoteModal" tabindex="-1" aria-labelledby="quoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border:none;border-radius:10px;overflow:hidden">
      <div style="background:linear-gradient(135deg,#D32F2F,#b71c1c);padding:28px 32px;display:flex;align-items:center;justify-content:space-between">
        <div>
          <h4 style="color:#fff;font-family:var(--font-head);font-weight:800;text-transform:uppercase;margin:0;letter-spacing:1px"><i class="fas fa-bolt me-2" style="color:var(--yellow)"></i>Get a Free Quote</h4>
          <p style="color:rgba(255,255,255,0.75);margin:4px 0 0;font-size:13px">We'll respond within 2 hours during business hours</p>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div id="modalFormResponse" style="display:none" class="mb-3"></div>
        <form id="modalEnquiryForm" novalidate>
          <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" placeholder="Your name" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control" placeholder="+91 XXXXX XXXXX" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="your@email.com">
            </div>
            <div class="col-sm-6">
              <label class="form-label">Product Interested In</label>
              <select name="subject" class="form-select">
                <option>Online UPS (Single Phase)</option>
                <option>Online UPS (Three Phase)</option>
                <option>Modular UPS</option>
                <option>Servo Voltage Stabilizer</option>
                <option>Inverter (Microtek / Luminous)</option>
                <option>SMF / Tubular Batteries</option>
                <option>Solar Products</option>
                <option>AMC / Service Contract</option>
                <option>Other</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Your Requirement</label>
              <textarea name="message" class="form-control" rows="3" placeholder="e.g. 10 KVA three phase UPS for server room, Chennai..."></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-submit w-100" id="modalSubmitBtn">
                <i class="fas fa-paper-plane me-2"></i>Send Quote Request
              </button>
              <p class="text-center text-muted mt-3 mb-0" style="font-size:13px">
                Or call us directly: <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="fw-700 text-red"><?php echo PHONE_PRIMARY; ?></a>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Quick Quote Modal AJAX
(function() {
  var form = document.getElementById('modalEnquiryForm');
  var btn  = document.getElementById('modalSubmitBtn');
  var resp = document.getElementById('modalFormResponse');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    resp.style.display = 'none';

    var data = new FormData(form);

    fetch('<?php echo SITE_URL; ?>/enquiry.php', {
      method: 'POST', body: data
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
      resp.style.display = 'block';
      if (json.success) {
        resp.innerHTML = '<div class="alert-success-msg"><i class="fas fa-check-circle me-2"></i>' + json.message + '</div>';
        form.reset();
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Quote Sent!';
      } else {
        resp.innerHTML = '<div class="alert alert-danger">' + json.message + '</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Quote Request';
      }
    })
    .catch(function() {
      resp.style.display = 'block';
      resp.innerHTML = '<div class="alert alert-warning">Connection issue. Please call <?php echo PHONE_PRIMARY; ?></div>';
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Quote Request';
    });
  });
})();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
