<?php
require_once 'includes/config.php';
$page_title = 'Contact Us';
$page_desc  = 'Contact Wavestek Power Solutions — Get a free quote for UPS, Stabilizers, Inverters and Batteries. Two offices in Chennai, 24/7 support.';
require_once 'includes/header.php';
?>

<section class="product-banner">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">Contact</li>
    </ol></nav>
    <h1>Contact <span style="color:var(--yellow)">Us</span></h1>
    <p class="lead" style="color:rgba(255,255,255,0.75);max-width:560px">Get in touch for quotes, support or any enquiry. We respond within 24 hours.</p>
  </div>
</section>

<section class="section-pad">
  <div class="container">
    <div class="row g-5">

      <!-- Info -->
      <div class="col-lg-4">
        <div class="contact-info-card">
          <h4 class="font-head fw-700 text-uppercase mb-4" style="color:var(--yellow);letter-spacing:1px">Get In Touch</h4>

          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div>
              <h6>Sales Office</h6>
              <p><?php echo ADDRESS_SALES; ?></p>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-industry"></i></div>
            <div>
              <h6>Manufacturing Unit</h6>
              <p><?php echo ADDRESS_FACTORY; ?></p>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-phone"></i></div>
            <div>
              <h6>Phone</h6>
              <a href="tel:<?php echo PHONE_PRIMARY; ?>"><?php echo PHONE_PRIMARY; ?></a><br>
              <a href="tel:<?php echo PHONE_SECONDARY; ?>"><?php echo PHONE_SECONDARY; ?></a><br>
              <a href="tel:<?php echo PHONE_OFFICE; ?>"><?php echo PHONE_OFFICE; ?></a>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
            <div>
              <h6>Email</h6>
              <a href="mailto:<?php echo EMAIL_ADMIN; ?>"><?php echo EMAIL_ADMIN; ?></a><br>
              <a href="mailto:<?php echo EMAIL_GENERAL; ?>"><?php echo EMAIL_GENERAL; ?></a>
            </div>
          </div>
          <div class="contact-info-item">
            <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
            <div>
              <h6>Working Hours</h6>
              <p>Mon – Sat: 9:00 AM – 7:00 PM<br><span style="color:var(--yellow)">Emergency Support: 24/7</span></p>
            </div>
          </div>

          <div class="mt-4">
            <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>?text=Hello+Wavestek%2C+I+need+a+quote" class="btn w-100 mb-2" style="background:#25D366;color:#fff;font-family:var(--font-head);font-weight:700;text-transform:uppercase;letter-spacing:1px" target="_blank"><i class="fab fa-whatsapp me-2"></i>WhatsApp Us</a>
            <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn w-100" style="background:var(--red);color:#fff;font-family:var(--font-head);font-weight:700;text-transform:uppercase;letter-spacing:1px"><i class="fas fa-phone me-2"></i>Call Now</a>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="col-lg-8">
        <div class="contact-form-wrap">
          <h3 class="font-head fw-700 text-uppercase mb-1" style="font-size:28px">Send Us a Message</h3>
          <p class="text-muted mb-4" style="font-size:14px">Fill the form below and we'll get back to you within 24 hours.</p>

          <!-- AJAX response area -->
          <div id="formResponse" style="display:none" class="mb-3"></div>

          <form id="enquiryForm" novalidate>
            <!-- Honeypot (hidden from humans, catches bots) -->
            <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="fname" class="form-control" placeholder="Your name" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+91 XXXXX XXXXX">
              </div>
              <div class="col-sm-6">
                <label class="form-label">Subject</label>
                <select name="subject" class="form-select">
                  <option>General Enquiry</option>
                  <option>Online UPS Quote</option>
                  <option>Servo Stabilizer Quote</option>
                  <option>Inverter / Battery Quote</option>
                  <option>Solar Products</option>
                  <option>AMC / Service</option>
                  <option>Emergency Support</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Message <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="5" placeholder="Describe your requirement, load capacity, location, etc..." required></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-submit" id="submitBtn">
                  <i class="fas fa-paper-plane me-2"></i>Send Message
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Google Map -->
<section class="pb-5">
  <div class="container">
    <div style="border-radius:8px;overflow:hidden;box-shadow:var(--shadow)">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3886.7!2d80.1!3d13.05!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sK.K.Nagar%2C+Chennai!5e0!3m2!1sen!2sin!4v1"
        width="100%" height="380" style="border:0;display:block" allowfullscreen loading="lazy">
      </iframe>
    </div>
    <div class="row g-3 mt-3">
      <div class="col-md-6">
        <div class="d-flex gap-3 align-items-start p-3" style="background:var(--light);border-radius:6px">
          <i class="fas fa-map-marker-alt text-red mt-1" style="font-size:20px"></i>
          <div>
            <strong class="font-head d-block text-uppercase" style="font-size:14px">Sales &amp; Service Office</strong>
            <span class="text-muted" style="font-size:13px"><?php echo ADDRESS_SALES; ?></span>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="d-flex gap-3 align-items-start p-3" style="background:var(--light);border-radius:6px">
          <i class="fas fa-industry text-blue mt-1" style="font-size:20px"></i>
          <div>
            <strong class="font-head d-block text-uppercase" style="font-size:14px">Manufacturing Division</strong>
            <span class="text-muted" style="font-size:13px"><?php echo ADDRESS_FACTORY; ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
(function() {
  var form = document.getElementById('enquiryForm');
  var btn  = document.getElementById('submitBtn');
  var resp = document.getElementById('formResponse');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    // Basic HTML5 validation
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    resp.style.display = 'none';

    var data = new FormData(form);

    fetch('<?php echo SITE_URL; ?>/enquiry.php', {
      method: 'POST',
      body: data
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
      resp.style.display = 'block';
      if (json.success) {
        resp.innerHTML = '<div class="alert-success-msg"><i class="fas fa-check-circle me-2"></i>' + json.message + '</div>';
        form.reset();
      } else {
        resp.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + json.message + '</div>';
      }
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Message';
      resp.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    })
    .catch(function() {
      resp.style.display = 'block';
      resp.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Connection issue. Please call <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="fw-700"><?php echo PHONE_PRIMARY; ?></a> directly.</div>';
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Send Message';
    });
  });
})();
</script>
<?php require_once 'includes/footer.php'; ?>
