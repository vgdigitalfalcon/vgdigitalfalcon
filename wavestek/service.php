<?php
require_once 'includes/config.php';
$page_title = 'Service';
$page_desc  = 'Wavestek Power Solutions offers UPS maintenance, AMC services, emergency support and installation across India.';
require_once 'includes/header.php';
?>

<section class="product-banner">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active">Service</li>
    </ol></nav>
    <h1>Our <span style="color:var(--yellow)">Services</span></h1>
    <p class="lead" style="color:rgba(255,255,255,0.75);max-width:580px">Comprehensive power solutions support — from installation to 24/7 emergency assistance.</p>
  </div>
</section>

<!-- Services Grid -->
<section class="section-pad">
  <div class="container">
    <div class="text-center mb-5">
      <p class="text-red font-head fw-700 text-uppercase mb-1" style="letter-spacing:2px">What We Do</p>
      <h2 class="section-title">Complete Power <span>Support Services</span></h2>
      <div class="section-line section-line-center"></div>
    </div>
    <div class="row g-4">
      <?php
      $services = [
        ['fas fa-tools','UPS Maintenance','Regular preventive maintenance for all UPS systems — online UPS, offline UPS and line interactive. Battery testing, connection checks, firmware updates and performance reports included.'],
        ['fas fa-file-contract','AMC Services','Annual Maintenance Contracts (AMC) for worry-free power management. Scheduled visits, priority emergency response, spare parts coverage, and detailed service history documentation.'],
        ['fas fa-bolt','Emergency Support 24/7','Critical power failure? Our emergency response team is available 24 hours, 7 days a week, 365 days a year. Fast diagnosis and rapid resolution to minimise downtime.'],
        ['fas fa-wrench','Installation & Commissioning','Professional installation of UPS systems, stabilizers, inverters and batteries. End-to-end commissioning with load testing, parameter configuration and handover documentation.'],
        ['fas fa-battery-full','Battery Replacement','Genuine OEM battery replacement for all major UPS brands. Battery health testing, proper disposal of old batteries, and extended backup solutions.'],
        ['fas fa-chart-line','Power Audits','Comprehensive site power audits to identify issues, calculate load requirements, and recommend optimal power protection solutions for your facility.'],
      ];
      foreach ($services as $s):
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="service-card reveal">
          <i class="<?php echo $s[0]; ?>"></i>
          <h5><?php echo $s[1]; ?></h5>
          <p><?php echo $s[2]; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Service Process -->
<section class="section-pad bg-light-gray">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">How We <span>Work</span></h2>
      <div class="section-line section-line-center"></div>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
      $steps = [
        ['1','fas fa-phone-alt','Contact Us','Call, WhatsApp or fill the contact form. Describe your power requirement or issue.'],
        ['2','fas fa-search','Site Assessment','Our engineer visits your site to assess the load, existing setup and requirements.'],
        ['3','fas fa-clipboard-list','Proposal','You receive a detailed proposal with product recommendations and transparent pricing.'],
        ['4','fas fa-check-circle','Installation','Professional installation and commissioning. Full handover with documentation and training.'],
        ['5','fas fa-headset','Ongoing Support','24/7 support, AMC visits, and priority emergency response whenever you need us.'],
      ];
      foreach ($steps as $step):
      ?>
      <div class="col-sm-6 col-md-4 col-lg-2">
        <div class="text-center reveal">
          <div style="width:64px;height:64px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;position:relative">
            <i class="<?php echo $step[1]; ?>" style="font-size:24px;color:#fff"></i>
            <span style="position:absolute;top:-6px;right:-6px;width:22px;height:22px;background:var(--yellow);border-radius:50%;font-family:var(--font-head);font-size:11px;font-weight:700;color:#111;display:flex;align-items:center;justify-content:center"><?php echo $step[0]; ?></span>
          </div>
          <h6 class="font-head fw-700 text-uppercase" style="font-size:14px;letter-spacing:0.5px"><?php echo $step[2]; ?></h6>
          <p style="font-size:13px;color:var(--gray)"><?php echo $step[3]; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container position-relative text-center" style="z-index:2">
    <h2 class="section-title" style="color:#fff">Need Urgent <span>Power Support?</span></h2>
    <p class="section-subtitle mt-2 mb-4">Our team is on standby. Reach us now by call or WhatsApp.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-cta-primary"><i class="fas fa-phone me-2"></i>Call Now — <?php echo PHONE_PRIMARY; ?></a>
      <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>?text=I+need+emergency+power+support" class="btn btn-cta-outline" target="_blank"><i class="fab fa-whatsapp me-2"></i>WhatsApp Us</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
