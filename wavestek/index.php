<?php
require_once 'includes/config.php';
$page_title = 'Home';
$page_desc  = 'Wavestek Power Solutions – Leading manufacturer & supplier of Online UPS, Servo Voltage Stabilizers, Inverters, Batteries and Solar Products in Chennai.';
require_once 'includes/header.php';
?>

<!-- ========================
     HERO SLIDER
======================== -->
<section class="hero-slider">
  <div class="swiper hero-swiper">
    <div class="swiper-wrapper">

      <!-- Slide 1 -->
      <div class="swiper-slide hero-slide slide-1">
        <div class="slide-bg"></div>
        <div class="slide-bg-overlay"></div>
        <div class="container slide-content">
          <div class="row">
            <div class="col-lg-7">
              <span class="slide-badge"><i class="fas fa-bolt me-1"></i> Chennai's Power Experts</span>
              <h1 class="slide-title">Powering India's<br><span>Infrastructure</span></h1>
              <p class="slide-desc">Manufacturer, sales &amp; service provider for Online UPS, Servo Voltage Stabilizers, Inverters and Batteries.</p>
              <div class="d-flex flex-wrap gap-3">
                <a href="products/ups-1-3kva.php" class="btn btn-danger btn-lg font-head fw-700">Explore UPS Range</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg font-head fw-700">Get Free Quote</a>
              </div>
              <div class="slide-icon-grid">
                <div class="slide-icon-item"><i class="fas fa-shield-alt"></i>Reliable</div>
                <div class="slide-icon-item"><i class="fas fa-headset"></i>24/7 Support</div>
                <div class="slide-icon-item"><i class="fas fa-award"></i>35+ Years</div>
                <div class="slide-icon-item"><i class="fas fa-users"></i>300+ Clients</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="swiper-slide hero-slide slide-2">
        <div class="slide-bg"></div>
        <div class="slide-bg-overlay"></div>
        <div class="container slide-content">
          <div class="row">
            <div class="col-lg-7">
              <span class="slide-badge"><i class="fas fa-tools me-1"></i> Always On-Call</span>
              <h1 class="slide-title">24/7 Critical<br><span>Power Support</span></h1>
              <p class="slide-desc">Emergency UPS service &amp; AMC contracts. We respond fast — because downtime costs everything.</p>
              <div class="d-flex flex-wrap gap-3">
                <a href="service.php" class="btn btn-danger btn-lg font-head fw-700">Our Services</a>
                <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-outline-light btn-lg font-head fw-700"><i class="fas fa-phone me-2"></i>Call Now</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="swiper-slide hero-slide slide-3">
        <div class="slide-bg"></div>
        <div class="slide-bg-overlay"></div>
        <div class="container slide-content">
          <div class="row">
            <div class="col-lg-7">
              <span class="slide-badge"><i class="fas fa-star me-1"></i> Trusted Across India</span>
              <h1 class="slide-title">Trusted by<br><span>300+ Leaders</span></h1>
              <p class="slide-desc">From hospitals and universities to data centres and factories — Wavestek powers the critical systems that matter most.</p>
              <div class="d-flex flex-wrap gap-3">
                <a href="about.php" class="btn btn-danger btn-lg font-head fw-700">Our Story</a>
                <a href="contact.php" class="btn btn-outline-light btn-lg font-head fw-700">Contact Us</a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>

<!-- ========================
     COUNTER SECTION
======================== -->
<section class="counter-section">
  <div class="container">
    <div class="row text-center gy-4 align-items-center">
      <div class="col-6 col-md-3">
        <div class="counter-item">
          <div class="counter-num" data-target="35" data-suffix="+">35+</div>
          <div class="counter-label">Years of Experience</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="counter-item">
          <div class="counter-num" data-target="300" data-suffix="+">300+</div>
          <div class="counter-label">Major Clients</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="counter-item">
          <div class="counter-num" data-target="2021" data-suffix="">2021</div>
          <div class="counter-label">Company Founded</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="counter-item">
          <div class="counter-num" data-target="100" data-suffix="%">100%</div>
          <div class="counter-label">Quality Commitment</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================
     CORE OFFERINGS
======================== -->
<section class="section-pad offerings-section">
  <div class="container">
    <div class="text-center mb-5">
      <p class="text-red font-head fw-700 text-uppercase letter-sp mb-1">What We Offer</p>
      <h2 class="section-title">Core <span>Power Solutions</span></h2>
      <div class="section-line section-line-center"></div>
    </div>
    <div class="row g-4">
      <div class="col-sm-6 col-lg-3">
        <div class="offering-card reveal">
          <div class="offering-icon"><i class="fas fa-plug"></i></div>
          <h4>Online UPS &amp; Inverters</h4>
          <p>Single phase, three phase &amp; modular UPS systems from 1 KVA to 400 KVA for critical power protection.</p>
          <a href="products/ups-1-3kva.php" class="d-inline-flex align-items-center gap-2 text-red fw-700 font-head mt-3" style="font-size:13px;text-transform:uppercase">View Range <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="offering-card reveal">
          <div class="offering-icon"><i class="fas fa-sliders-h"></i></div>
          <h4>Servo Stabilizers</h4>
          <p>Single &amp; three phase servo voltage stabilizers from 1 KVA to 500 KVA. Protect your sensitive equipment.</p>
          <a href="products/stabilizer-single-phase.php" class="d-inline-flex align-items-center gap-2 text-red fw-700 font-head mt-3" style="font-size:13px;text-transform:uppercase">View Range <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="offering-card reveal">
          <div class="offering-icon"><i class="fas fa-battery-full"></i></div>
          <h4>Batteries</h4>
          <p>SMF, Tubular, Automotive &amp; Lithium-Ion batteries from leading brands. Reliable power backup solutions.</p>
          <a href="products/battery-smf.php" class="d-inline-flex align-items-center gap-2 text-red fw-700 font-head mt-3" style="font-size:13px;text-transform:uppercase">View Range <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="offering-card reveal">
          <div class="offering-icon"><i class="fas fa-solar-panel"></i></div>
          <h4>Solar Solutions</h4>
          <p>Solar inverters &amp; solar water heaters for sustainable energy. Reduce costs with clean power.</p>
          <a href="products/solar-inverter.php" class="d-inline-flex align-items-center gap-2 text-red fw-700 font-head mt-3" style="font-size:13px;text-transform:uppercase">View Range <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================
     CTA SECTION
======================== -->
<section class="cta-section">
  <div class="container position-relative" style="z-index:2">
    <div class="row align-items-center gy-4">
      <div class="col-lg-8">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div style="width:52px;height:52px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-bolt" style="font-size:22px;color:#fff"></i>
          </div>
          <h2 class="section-title mb-0" style="color:#fff">Critical Power Failure?<br><span>We're Ready 24/7</span></h2>
        </div>
        <p class="section-subtitle">Don't let power issues shut down your business. Our expert technicians are on call round the clock for emergency support, installation &amp; AMC services.</p>
      </div>
      <div class="col-lg-4 d-flex flex-column flex-sm-row flex-lg-column gap-3">
        <a href="contact.php" class="btn btn-cta-primary text-center">Get Free Quote</a>
        <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-cta-outline text-center"><i class="fas fa-phone me-2"></i>Contact Now</a>
      </div>
    </div>
  </div>
</section>

<!-- ========================
     WHY CHOOSE US
======================== -->
<section class="section-pad">
  <div class="container">
    <div class="row align-items-center gy-5">
      <div class="col-lg-4">
        <p class="text-red font-head fw-700 text-uppercase mb-1" style="letter-spacing:2px">Our Advantage</p>
        <h2 class="section-title">Why Choose <span>Wavestek?</span></h2>
        <div class="section-line mb-4"></div>
        <p class="text-muted mb-4">Founded on 35+ years of hands-on experience in the power solutions industry, Wavestek combines technical expertise with rapid response to deliver unmatched value to our clients across India.</p>
        <a href="about.php" class="btn btn-outline-danger font-head fw-700 text-uppercase" style="letter-spacing:1px">Learn More About Us</a>
      </div>
      <div class="col-lg-8">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="why-card">
              <div class="why-icon"><i class="fas fa-truck-fast"></i></div>
              <div>
                <h5>Quick Delivery</h5>
                <p>Fast turnaround on orders. We installed 6 UPS units across 6 cinema screens in just 5 hours.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="why-card">
              <div class="why-icon"><i class="fas fa-tag"></i></div>
              <div>
                <h5>Best Pricing</h5>
                <p>Competitive and transparent pricing with no hidden charges. Direct manufacturer pricing advantage.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="why-card">
              <div class="why-icon"><i class="fas fa-user-tie"></i></div>
              <div>
                <h5>Expert Experience</h5>
                <p>35+ years in power solutions. We've served Regional Met Centre, Anna University &amp; major hospitals.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="why-card">
              <div class="why-icon"><i class="fas fa-headset"></i></div>
              <div>
                <h5>24/7 Support</h5>
                <p>Round-the-clock emergency support. Our technicians respond fast when critical systems need help.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="why-card">
              <div class="why-icon"><i class="fas fa-certificate"></i></div>
              <div>
                <h5>Quality Products</h5>
                <p>100% quality commitment — every product is tested and meets industry standards before delivery.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="why-card">
              <div class="why-icon"><i class="fas fa-industry"></i></div>
              <div>
                <h5>Own Manufacturing</h5>
                <p>Chennai-based manufacturing unit producing our own UPS &amp; Servo Stabilizer range.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ========================
     SUCCESS STORIES
======================== -->
<section class="section-pad bg-light-gray">
  <div class="container">
    <div class="text-center mb-5">
      <p class="text-red font-head fw-700 text-uppercase mb-1" style="letter-spacing:2px">Proven Track Record</p>
      <h2 class="section-title">Success <span>Stories</span></h2>
      <div class="section-line section-line-center"></div>
    </div>
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="story-card reveal">
          <div class="story-icon"><i class="fas fa-film"></i></div>
          <h5>Rohini Movie Complex</h5>
          <p>Installed six complete UPS and battery sets for six cinema screens — entire project completed in just 5 hours with zero downtime to operations.</p>
          <div class="d-flex align-items-center gap-2 mt-3">
            <i class="fas fa-map-marker-alt text-red"></i>
            <small class="text-muted">Chennai, Tamil Nadu</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="story-card reveal">
          <div class="story-icon"><i class="fas fa-microchip"></i></div>
          <h5>SRM Technologies</h5>
          <p>Successfully installed a 60 KVA Eaton UPS system, providing robust, uninterrupted power protection for mission-critical IT infrastructure.</p>
          <div class="d-flex align-items-center gap-2 mt-3">
            <i class="fas fa-map-marker-alt text-red"></i>
            <small class="text-muted">Chennai, Tamil Nadu</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="story-card reveal">
          <div class="story-icon"><i class="fas fa-hospital"></i></div>
          <h5>Iswarya Fertility Centre</h5>
          <p>Delivered 4 complete inverter &amp; battery sets to three cities — Chennai, Madurai &amp; Hyderabad — all on the same day. Logistics excellence at its best.</p>
          <div class="d-flex align-items-center gap-2 mt-3">
            <i class="fas fa-map-marker-alt text-red"></i>
            <small class="text-muted">Pan-India Delivery</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Testimonials -->
    <div class="row">
      <div class="col-12">
        <h3 class="font-head fw-700 text-center text-uppercase mb-4" style="font-size:22px;letter-spacing:1px">What Our Clients Say</h3>
        <div class="swiper testimonial-swiper">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="quote"><i class="fas fa-quote-left"></i></div>
                <p>"Wavestek installed our UPS systems with remarkable speed and professionalism. Highly recommended for any critical power requirements."</p>
                <div class="name">Regional Meteorological Centre</div>
                <small class="text-muted">Chennai</small>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="quote"><i class="fas fa-quote-left"></i></div>
                <p>"Their 24/7 support has been a lifesaver. Whenever we face power issues, Wavestek responds immediately with experienced technicians."</p>
                <div class="name">Anna University</div>
                <small class="text-muted">Chennai</small>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="quote"><i class="fas fa-quote-left"></i></div>
                <p>"Delivered our inverters and batteries to three cities on the same day. That kind of commitment to service is rare. Excellent team!"</p>
                <div class="name">Iswarya Fertility Centre</div>
                <small class="text-muted">Chennai, Madurai, Hyderabad</small>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="testimonial-card">
                <div class="quote"><i class="fas fa-quote-left"></i></div>
                <p>"Best pricing in the market backed by genuine quality products and prompt after-sales service. Wavestek is our go-to power solutions partner."</p>
                <div class="name">IOG Government Hospital</div>
                <small class="text-muted">Egmore, Chennai</small>
              </div>
            </div>
          </div>
          <div class="testimonial-pagination swiper-pagination position-relative mt-4" style="bottom:auto"></div>
        </div>
      </div>
    </div>

    <!-- Prestigious Clients -->
    <div class="mt-5 pt-3">
      <h3 class="font-head fw-700 text-center text-uppercase mb-4" style="font-size:22px;letter-spacing:1px">Our Prestigious Clients</h3>
      <div class="row align-items-center justify-content-center text-center gy-3 gx-4">
        <?php
        $clients = ['Regional Met Centre', 'Anna University', 'IOG Hospital', 'Rohini Cinemas', 'SRM Technologies', 'Iswarya Fertility', 'Naidu Hall', 'Kumaran Hospital', 'Domex India', 'Yamaha Music'];
        foreach ($clients as $client):
        ?>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
          <div class="client-logo-box" style="background:#f5f6f8;border-radius:6px;padding:16px 12px;border:1px solid #eee;transition:all 0.3s;cursor:default" onmouseover="this.style.borderColor='#D32F2F';this.style.background='#fff'" onmouseout="this.style.borderColor='#eee';this.style.background='#f5f6f8'">
            <i class="fas fa-building" style="font-size:24px;color:#aaa;display:block;margin-bottom:8px"></i>
            <p style="font-size:11px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:0.5px;margin:0;line-height:1.3"><?php echo $client; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
