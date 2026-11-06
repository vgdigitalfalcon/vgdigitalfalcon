<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

// Dynamic services
$servicesStmt = $db->query("SELECT * FROM services ORDER BY name ASC LIMIT 14");
$services = $servicesStmt->fetchAll();

// Dynamic projects (newest first by completion date, prefer After images)
$projectsStmt = $db->query("
  SELECT p.*, s.name as service_name,
         (SELECT file_path FROM project_media 
          WHERE project_id = p.id AND media_type = 'image' 
          ORDER BY CASE WHEN phase = 'after' THEN 0 ELSE 1 END, sort_order ASC, id ASC 
          LIMIT 1) as cover_image
  FROM projects p
  LEFT JOIN services s ON p.service_id = s.id
  ORDER BY COALESCE(p.duration, '0000-00') DESC, p.created_at DESC
  LIMIT 4
");
$projects = $projectsStmt->fetchAll();

$pageTitle = 'A.R. Waterproofing';
require_once __DIR__ . '/includes/header.php';
?>
    <section class="hero section-wrap" id="home">
      <div class="hero-copy">
        <span class="eyebrow"><span class="live-dot"></span> CHENNAI BASED • WATERPROOFING EXPERTS</span>
        <h1>Protecting Your Building For A <em>Stronger Tomorrow.</em></h1>
        <p>Complete waterproofing and construction solutions for residential, commercial and industrial buildings.</p>
        <div class="hero-actions">
          <a class="button button-primary" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing%2C%20I%20would%20like%20to%20enquire%20about%20your%20services." target="_blank" rel="noopener">WhatsApp Us <span>↗</span></a>
          <a class="button button-light" href="tel:+919841044994"><span class="phone-icon">☎</span> Call Now</a>
        </div>
        <div class="hero-proof">
          <div><b class="count-up" data-count="30" data-suffix="+">0</b><span>Years of experience</span></div>
          <div><b class="count-up" data-count="3000" data-suffix="+">0</b><span>Projects completed</span></div>
          <div><b class="count-up" data-count="1500" data-suffix="+">0</b><span>Satisfied clients</span></div>
        </div>
      </div>
      <div class="hero-visual">
        <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=85" alt="Modern residential building protected by professional building services" fetchpriority="high">
        <div class="visual-badge"><span class="badge-icon">✓</span><span><strong>Built on trust.</strong><small>Protected for life.</small></span></div>
        <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
      </div>
    </section>

    <section class="property-types section-wrap" aria-label="Property types we serve">
      <div class="property-intro"><span class="eyebrow">BUILT FOR EVERY PROPERTY</span><h2>One Trusted Team.<br><em>Multiple Property Needs.</em></h2><p>From family homes to large facilities, explore solutions suited to your building and site requirements.</p></div>
      <a class="property-card" href="services.php"><span class="property-visual property-home" aria-hidden="true">⌂</span><span class="property-card-copy"><strong>Residential</strong><small>Homes, apartments & villas</small><b>Explore services ↗</b></span></a>
      <a class="property-card" href="services.php"><span class="property-visual property-commercial" aria-hidden="true">▦</span><span class="property-card-copy"><strong>Commercial</strong><small>Offices, retail & complexes</small><b>Explore services ↗</b></span></a>
      <a class="property-card" href="services.php"><span class="property-visual property-industrial" aria-hidden="true">⚒</span><span class="property-card-copy"><strong>Industrial</strong><small>Facilities & infrastructure</small><b>Explore services ↗</b></span></a>
    </section>

    <section class="section section-wrap" id="services">
      <div class="section-heading">
        <div><span class="eyebrow">WHAT WE DO</span><h2>Our <em>services</em></h2><p>Practical solutions to protect, repair and improve your property.</p></div>
        <a class="text-link" href="services.php">View all services <span>→</span></a>
      </div>
      <div class="service-grid">
        <?php if (empty($services)): ?>
          <p style="grid-column: 1 / -1;">Services will appear here once added from the admin panel.</p>
        <?php else: ?>
          <?php foreach ($services as $service): 
            $img = getPublicMediaUrl(getServiceImage($service['name'], $service['image_path'] ?? null));
          ?>
            <article class="service-card">
              <a href="service.php?id=<?php echo (int)$service['id']; ?>">
                <img src="<?php echo sanitize($img); ?>" alt="<?php echo sanitize($service['name']); ?>" loading="lazy">
                <div class="service-content">
                  <span class="service-icon">⌂</span>
                  <h3><?php echo sanitize($service['name']); ?></h3>
                  <p><?php echo sanitize(mb_substr(strip_tags($service['about'] ?? ''), 0, 90)); ?><?php echo mb_strlen($service['about'] ?? '') > 90 ? '...' : ''; ?></p>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <section class="about-section" id="about">
      <div class="section-wrap about-grid">
        <div class="about-image"><img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1000&q=85" alt="Modern building exterior" loading="lazy"><div class="image-caption"><span>AR</span><strong>Property protection<br>with a professional approach</strong></div></div>
        <div class="about-copy"><span class="eyebrow">ABOUT A.R. WATERPROOFING</span><h2>Built on trust.<br><em>Protected for life.</em></h2><p>A.R. Waterproofing is a Chennai-based service provider offering waterproofing, grouting, construction and renovation solutions for residential, commercial and industrial properties.</p><p>From terraces and basements to structural repairs and painting, the aim is to understand the site requirement and recommend a suitable solution.</p>
          <ul class="check-list"><li>Solutions across multiple building types</li><li>Service options for repair and new work</li><li>Clear communication about the requirement</li><li>Contact the team to discuss your site</li></ul>
          <a class="button button-primary" href="rajendran.php" target="_blank" rel="noopener">Check Founder's Profile <span>↗</span></a>
        </div>
      </div>
    </section>

    <section class="section section-wrap" id="projects">
      <div class="section-heading">
        <div><span class="eyebrow">OUR WORK</span><h2>Project <em>showcase</em></h2><p>Recent projects completed by A.R. Waterproofing.</p></div>
        <a class="text-link" href="projects.php">View all projects <span>→</span></a>
      </div>
      <div class="project-grid">
        <?php if (empty($projects)): ?>
          <p style="grid-column: 1 / -1;">Projects will appear here once added from the admin panel.</p>
        <?php else: ?>
          <?php foreach ($projects as $index => $project): 
            $cover = $project['cover_image'] 
              ? getPublicMediaUrl($project['cover_image']) 
              : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=85';
            $isLarge = ($index === 0);
          ?>
            <article class="project-card <?php echo $isLarge ? 'project-large' : ''; ?>">
              <a href="project.php?id=<?php echo (int)$project['id']; ?>">
                <img src="<?php echo sanitize($cover); ?>" alt="<?php echo sanitize($project['name']); ?>" loading="lazy">
                <div class="project-overlay">
                  <span><?php echo sanitize($project['service_name'] ?? 'Project'); ?></span>
                  <h3><?php echo sanitize($project['name']); ?></h3>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <section class="why-section">
      <div class="section-wrap"><div class="section-heading section-heading-light"><div><span class="eyebrow">THE A.R. APPROACH</span><h2>Why choose <em>us?</em></h2><p>Focused on understanding the job and communicating clearly.</p></div></div>
        <div class="why-grid">
          <article><span>✦</span><h3>Skilled Approach</h3><p>Site needs are considered before deciding on the work.</p></article>
          <article><span>◈</span><h3>Suitable Materials</h3><p>Material selection depends on the application and surface.</p></article>
          <article><span>◷</span><h3>Clear Coordination</h3><p>Discuss scope, access and expected timelines upfront.</p></article>
          <article><span>⌂</span><h3>Multiple Services</h3><p>Waterproofing, repairs, construction and renovation in one profile.</p></article>
        </div>
      </div>
    </section>

    <section class="section section-wrap" id="reviews">
      <div class="section-heading"><div><span class="eyebrow">CLIENT EXPERIENCE</span><h2>Reviews & <em>feedback</em></h2><p>Read customer feedback or leave a review after your experience.</p></div><a class="text-link" href="https://www.google.com/search?q=A.R.+Waterproofing+Chennai+reviews" target="_blank" rel="noopener">Find reviews <span>↗</span></a></div>
      <div class="review-callout"><div class="review-stars" aria-label="Review prompt">★★★★★</div><div><h3>Worked with A.R. Waterproofing?</h3><p>Your feedback helps others learn about the service.</p></div><a class="button button-primary" href="https://www.google.com/search?q=A.R.+Waterproofing+Chennai+Google+reviews" target="_blank" rel="noopener">Add a Review <span>↗</span></a></div>
      <p class="review-note">Review links above are search links until the official Google Business Profile review URL is added. Replace them with the direct review link before launch.</p>
    </section>

    <section class="contact-section" id="contact">
      <div class="section-wrap contact-grid">
        <div class="contact-copy"><span class="eyebrow">LET'S TALK ABOUT YOUR SITE</span><h2>Need help with a <em>leak or repair?</em></h2><p>Call or message the team to explain the issue and discuss the next step. No online form required.</p>
          <div class="contact-actions"><a class="button button-white" href="tel:+919841044994">☎ Call the Team</a><a class="button button-whatsapp" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing%2C%20I%20need%20help%20with%20a%20site%20requirement." target="_blank" rel="noopener">◉ WhatsApp</a></div>
          <div class="digital-card-actions">
            <button class="digital-action" id="saveContactButton" type="button"><span>＋</span><strong>Save Contact</strong><small>Download contact card</small></button>
            <button class="digital-action" id="shareContactButton" type="button"><span>↓</span><strong>Download Profile</strong><small>Save Rajendran’s profile</small></button>
          </div>
        </div>
        <div class="contact-card">
          <div class="contact-row"><span class="contact-symbol">♙</span><div><small>PROPRIETOR</small><strong>Rajendran C</strong></div></div>
          <div class="contact-row"><span class="contact-symbol">☎</span><div><small>PHONE</small><a href="tel:+919841044994">+91 98410 44994</a><a href="tel:+918680070461">+91 86800 70461</a></div></div>
          <div class="contact-row"><span class="contact-symbol">✉</span><div><small>EMAIL</small><a href="mailto:rgn.1972@gmail.com">rgn.1972@gmail.com</a></div></div>
          <div class="contact-row"><span class="contact-symbol">◎</span><div><small>WEBSITE</small><a href="https://www.arwaterproofing.com" target="_blank" rel="noopener">www.arwaterproofing.com</a></div></div>
          <div class="contact-row"><span class="contact-symbol">⌖</span><div><small>ADDRESS</small><address>9/3, Pushpa Nagar Main Road,<br>Nungambakkam, Chennai 600 034</address><a class="map-link" href="https://www.google.com/maps/search/?api=1&query=9%2F3+Pushpa+Nagar+Main+Road+Nungambakkam+Chennai+600034" target="_blank" rel="noopener">Open in Google Maps ↗</a></div></div>
        </div>
      </div>
    </section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>