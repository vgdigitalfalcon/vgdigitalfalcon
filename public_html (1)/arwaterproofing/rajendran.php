<?php
require_once __DIR__ . '/includes/config.php';
$db = getDB();

// Featured Services — prefer waterproofing-related services
$servicesStmt = $db->query("
  SELECT * FROM services 
  WHERE name LIKE '%Waterproof%' OR name LIKE '%waterproof%' OR name LIKE '%Grout%' OR name LIKE '%Repair%'
  ORDER BY name ASC 
  LIMIT 3
");
$services = $servicesStmt->fetchAll();

// Fallback if no matching services
if (empty($services)) {
  $servicesStmt = $db->query("SELECT * FROM services ORDER BY name ASC LIMIT 3");
  $services = $servicesStmt->fetchAll();
}

// Featured Projects (limit to 3)
$projectsStmt = $db->query("
  SELECT p.*, s.name as service_name,
         (SELECT file_path FROM project_media WHERE project_id = p.id AND media_type = 'image' ORDER BY sort_order ASC, id ASC LIMIT 1) as cover_image
  FROM projects p
  LEFT JOIN services s ON p.service_id = s.id
  ORDER BY p.created_at DESC
  LIMIT 3
");
$projects = $projectsStmt->fetchAll();

$pageTitle = 'C. Rajendran | Founder | A.R. Waterproofing';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta title= "C. Rajendran | Founder | A.R. Waterproofing">
  <meta name="theme-color" content="#5422c8">
  <meta name="description" content="C. Rajendran - Founder of A.R. Waterproofing. Premier waterproofing and construction services in Chennai for over 30 years. Rotary Club Member.">
  <meta property="og:title" content="C. Rajendran | Founder | A.R. Waterproofing">
  <meta property="og:description" content="Founder of A.R. Waterproofing. 30+ years of excellence in waterproofing solutions across Tamil Nadu.">
  <meta property="og:type" content="profile">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --purple: #5422c8;
      --purple-2: #7545e8;
      --purple-dark: #251052;
      --lavender: #f2edff;
      --ink: #19152c;
      --muted: #716c81;
      --line: #e8e2f4;
      --white: #fff;
      --green: #0a9f62;
      --shadow: 0 16px 45px rgba(47, 23, 104, 0.09);
      --radius: 22px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      background: #faf8ff;
      color: var(--ink);
      font-family: "DM Sans", sans-serif;
      font-size: 15px;
      line-height: 1.65;
      -webkit-font-smoothing: antialiased;
    }
    a { color: inherit; text-decoration: none; }
    img { max-width: 100%; display: block; }

    .site-header {
      height: 78px; padding: 0 28px; display: flex; align-items: center; justify-content: space-between;
      background: rgba(255,255,255,.95); backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(84,34,200,.08); position: sticky; top: 0; z-index: 100;
    }
    .brand { display: flex; align-items: center; gap: 12px; font-family: Manrope, sans-serif; font-weight: 700; }
    .brand-logo-img {
      width: 46px; height: 46px; object-fit: contain; border-radius: 10px;
    }
    .brand-text span { font-size: 14px; letter-spacing: -0.3px; display: block; }
    .brand-text small { display: block; font-size: 9px; color: var(--muted); font-weight: 600; letter-spacing: 1px; margin-top: 2px; }
    .header-actions { display: flex; gap: 10px; align-items: center; }
    .btn {
      display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px;
      border-radius: 12px; font-weight: 700; font-size: 13px; border: none; cursor: pointer;
      transition: transform .2s, box-shadow .2s;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(84,34,200,.2); }
    .btn-primary { background: linear-gradient(135deg, var(--purple), #713ee3); color: #fff; }
    .btn-light { background: #fff; border: 1px solid var(--line); color: var(--ink); }
    .btn-whatsapp { background: #08a766; color: #fff; }
    .btn-outline { background: transparent; border: 1.5px solid var(--purple); color: var(--purple); }
    .btn-sm { padding: 9px 16px; font-size: 12px; }

    .profile-hero {
      padding: 50px 24px 30px; max-width: 1100px; margin: 0 auto;
      display: grid; grid-template-columns: 300px 1fr; gap: 40px; align-items: start;
    }
    .profile-card {
      background: #fff; border-radius: 28px; padding: 28px 24px; text-align: center;
      box-shadow: var(--shadow); border: 1px solid var(--line); position: sticky; top: 100px;
    }
    .profile-photo {
      width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 18px;
      object-fit: cover; border: 6px solid var(--lavender); background: var(--lavender);
    }
    .profile-card h1 { font-family: Manrope, sans-serif; font-size: 26px; font-weight: 800; letter-spacing: -0.6px; margin-bottom: 4px; }
    .role { color: var(--purple); font-weight: 700; font-size: 14px; margin-bottom: 8px; }
    .rotary-badge {
      display: inline-flex; align-items: center; gap: 6px; background: #f8f4ff;
      color: var(--purple); padding: 4px 10px; border-radius: 16px; font-size: 11px; font-weight: 700; margin-bottom: 14px;
    }
    .company-tag {
      display: inline-block; background: var(--lavender); color: var(--purple);
      padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.4px;
    }
    .quick-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 22px; }
    .quick-actions .btn { width: 100%; justify-content: center; }

    .hero-content h2 {
      font-family: Manrope, sans-serif; font-size: clamp(30px, 4vw, 44px);
      font-weight: 800; letter-spacing: -1.1px; line-height: 1.15; margin-bottom: 14px;
    }
    .hero-content h2 em { font-style: normal; color: var(--purple); }
    .hero-content p { color: var(--muted); font-size: 16px; max-width: 620px; margin-bottom: 16px; }
    .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }

    .stats-section { max-width: 1100px; margin: 10px auto 50px; padding: 0 24px; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .stat-card {
      background: #fff; border-radius: 20px; padding: 24px 16px; text-align: center;
      border: 1px solid var(--line); box-shadow: 0 8px 30px rgba(47,23,104,.06);
    }
    .stat-number { font-family: Manrope, sans-serif; font-size: 38px; font-weight: 800; color: var(--purple); line-height: 1; margin-bottom: 6px; }
    .stat-label { font-size: 12px; color: var(--muted); font-weight: 600; }

    .content-section {
      max-width: 1100px; margin: 0 auto 50px; padding: 0 24px;
      display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 28px;
    }
    .about-card, .cofounder-card {
      background: #fff; border-radius: 24px; padding: 32px 28px;
      border: 1px solid var(--line); box-shadow: var(--shadow);
    }
    .section-label { font-size: 11px; letter-spacing: 1.6px; color: var(--purple); font-weight: 800; margin-bottom: 10px; }
    .about-card h3, .cofounder-card h3 {
      font-family: Manrope, sans-serif; font-size: 24px; font-weight: 800; margin-bottom: 14px; letter-spacing: -0.4px;
    }
    .about-card p { color: var(--muted); margin-bottom: 14px; }
    .cofounder-photo {
      width: 110px; height: 110px; border-radius: 18px; object-fit: cover; margin-bottom: 16px; background: var(--lavender);
    }
    .cofounder-card .name { font-family: Manrope, sans-serif; font-size: 20px; font-weight: 800; margin-bottom: 2px; }
    .cofounder-card .role-sm { color: var(--purple); font-weight: 700; margin-bottom: 12px; font-size: 14px; }
    .cofounder-card p { color: var(--muted); font-size: 14px; margin-bottom: 10px; }

    .showcase-section { max-width: 1100px; margin: 0 auto 50px; padding: 0 24px; }
    .showcase-header {
      display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; gap: 16px; flex-wrap: wrap;
    }
    .showcase-header h3 { font-family: Manrope, sans-serif; font-size: 26px; font-weight: 800; }
    .showcase-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
    .showcase-card {
      background: #fff; border-radius: 18px; overflow: hidden; border: 1px solid var(--line);
      box-shadow: 0 6px 20px rgba(47,23,104,.05); transition: transform .2s;
    }
    .showcase-card:hover { transform: translateY(-4px); }
    .showcase-card a { display: block; color: inherit; }
    .showcase-img {
      width: 100%; height: 180px; object-fit: cover; background: #f0ebff;
    }
    .showcase-img-placeholder {
      width: 100%; height: 180px; background: linear-gradient(135deg, var(--lavender), #e8e0ff);
      display: flex; align-items: center; justify-content: center; color: var(--purple); font-size: 36px; font-weight: 800;
    }
    .showcase-body { padding: 18px; }
    .showcase-body h4 { font-family: Manrope, sans-serif; font-size: 16px; font-weight: 800; margin-bottom: 6px; }
    .showcase-body p { font-size: 13px; color: var(--muted); margin: 0; line-height: 1.5; }
    .showcase-meta { font-size: 11px; color: var(--purple); font-weight: 700; margin-top: 8px; }

    .client-value { max-width: 1100px; margin: 0 auto 50px; padding: 0 24px; }
    .value-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 16px; }
    .value-item { background: #fff; border-radius: 18px; padding: 22px; border: 1px solid var(--line); }
    .value-item strong { display: block; font-family: Manrope, sans-serif; font-size: 15px; margin-bottom: 6px; }
    .value-item p { font-size: 13px; color: var(--muted); margin: 0; }

    .contact-section { background: linear-gradient(130deg, #2c125f, #5422c8); color: #fff; padding: 60px 24px; }
    .contact-inner { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start; }
    .contact-copy h2 { font-family: Manrope, sans-serif; font-size: clamp(30px, 4vw, 42px); font-weight: 800; letter-spacing: -0.8px; margin-bottom: 12px; }
    .contact-copy h2 em { font-style: normal; color: #d6c5ff; }
    .contact-copy p { color: #e0d7f4; margin-bottom: 24px; max-width: 420px; }
    .contact-card { background: #fff; color: var(--ink); border-radius: 24px; padding: 28px; box-shadow: 0 25px 55px rgba(23,8,58,.3); }
    .contact-row { display: flex; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--line); }
    .contact-row:last-child { border-bottom: none; }
    .contact-icon {
      width: 40px; height: 40px; border-radius: 12px; background: var(--lavender); color: var(--purple);
      display: grid; place-items: center; font-size: 18px; flex-shrink: 0;
    }
    .contact-row small { font-size: 9px; letter-spacing: 1.1px; color: var(--muted); font-weight: 800; display: block; margin-bottom: 2px; }
    .contact-row strong, .contact-row a { font-size: 14px; font-weight: 700; }
    .contact-row a:hover { color: var(--purple); }

    .modal-overlay {
      position: fixed; inset: 0; background: rgba(25,21,44,.55); display: none;
      align-items: center; justify-content: center; z-index: 200; padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal {
      background: #fff; border-radius: 24px; padding: 32px; width: 100%; max-width: 460px;
      box-shadow: 0 30px 60px rgba(0,0,0,.25); position: relative;
    }
    .modal h3 { font-family: Manrope, sans-serif; font-size: 22px; font-weight: 800; margin-bottom: 8px; }
    .modal p { color: var(--muted); font-size: 14px; margin-bottom: 20px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; }
    .form-group input, .form-group textarea {
      width: 100%; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px;
      font-family: inherit; font-size: 14px; outline: none;
    }
    .form-group input:focus, .form-group textarea:focus { border-color: var(--purple); }
    .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
    .close-modal { position: absolute; top: 16px; right: 18px; background: none; border: none; font-size: 22px; cursor: pointer; color: var(--muted); }

    .site-footer { background: #171126; color: #bdb4d0; padding: 36px 24px 28px; text-align: center; font-size: 13px; }
    .site-footer a { color: #fff; font-weight: 600; }
    .site-footer .brand-footer { color: #fff; font-family: Manrope, sans-serif; font-weight: 700; margin-bottom: 6px; }

    @media (max-width: 900px) {
      .profile-hero { grid-template-columns: 1fr; }
      .profile-card { position: static; max-width: 340px; margin: 0 auto 20px; }
      .hero-content { text-align: center; }
      .hero-content p { margin-left: auto; margin-right: auto; }
      .hero-actions { justify-content: center; }
      .stats-grid, .showcase-grid { grid-template-columns: 1fr 1fr; }
      .content-section, .contact-inner { grid-template-columns: 1fr; }
      .value-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
      .site-header { padding: 0 16px; height: 68px; }
      .header-actions .btn-light { display: none; }
      .stats-grid, .showcase-grid { grid-template-columns: 1fr; }
      .profile-hero { padding: 36px 16px 16px; }
      .showcase-img, .showcase-img-placeholder { height: 160px; }
    }
  </style>
</head>
<body>

  <header class="site-header">
    <a href="index.php" class="brand">
      <img class="brand-logo-img" src="assets/ar-logo.png" alt="A.R. Waterproofing Logo" onerror="this.style.display='none'">
      <div class="brand-text">
        <span>A.R. WATERPROOFING</span>
        <small>PROTECTING WHAT MATTERS</small>
      </div>
    </a>
    <div class="header-actions">
      <a href="index.php" class="btn btn-light">← Website</a>
      <a href="tel:+919841044994" class="btn btn-primary">Call Now</a>
    </div>
  </header>

  <section class="profile-hero">
    <div class="profile-card">
      <img class="profile-photo" src="rajendiran.png" alt="C. Rajendran" onerror="this.style.background='var(--lavender)'">
      <h1>C. Rajendran</h1>
      <div class="role">Founder</div>
      <div class="rotary-badge">Rotary Club Member</div>
      <div class="company-tag">A.R. Waterproofing</div>

      <div class="quick-actions">
        <button class="btn btn-primary" onclick="downloadVCard()">Save Contact</button>
        <button class="btn btn-outline" onclick="openExchangeModal()">Exchange Contact</button>
        <a href="https://g.page/r/YOUR_GOOGLE_REVIEW_LINK/review" class="btn btn-light" target="_blank" rel="noopener">Google Review</a>
        <a href="https://wa.me/919841044994?text=Hello%20Mr.%20Rajendran%2C%20I%20would%20like%20to%20discuss%20a%20waterproofing%20project." class="btn btn-whatsapp" target="_blank">WhatsApp</a>
      </div>
    </div>

    <div class="hero-content">
      <div class="section-label">FOUNDER PROFILE</div>
      <h2>Building Trust.<br><em>Protecting For Life.</em></h2>
      <p style="text-align:justify;">C. Rajendran leads AR Waterproofing with a focus on quality workmanship, reliable service and long-term building protection. Under his leadership, the company provides waterproofing solutions for residential, commercial and industrial properties, helping protect structures from water leakage, seepage and moisture-related damage.</p>
      <p style="text-align:justify;">With a commitment to quality materials, practical solutions and customer satisfaction, he aims to help property owners maintain durable, well-protected buildings. His approach reflects the importance of trust, professional service and lasting value in every project.</p>
      <p style="text-align:justify;">Beyond his professional responsibilities, his association with Rotary can also be highlighted as part of his commitment to community involvement, once his specific club membership and activities are confirmed.</p>
      <div class="hero-actions">
        <a href="#services" class="btn btn-primary">Our Services</a>
        <a href="#projects" class="btn btn-light">View Projects</a>
        <a href="#contact" class="btn btn-outline">Get in Touch</a>
      </div>
    </div>
  </section>

  <section class="stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><span class="counter" data-target="3000">0</span>+</div>
        <div class="stat-label">Projects Done</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><span class="counter" data-target="1500">0</span>+</div>
        <div class="stat-label">Happy Clients</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><span class="counter" data-target="30">0</span>+</div>
        <div class="stat-label">Years Experience</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><span class="counter" data-target="100">0</span>%</div>
        <div class="stat-label">Client Satisfaction</div>
      </div>
    </div>
  </section>

  <section class="content-section">
    <div class="about-card">
      <div class="section-label">ABOUT THE FOUNDER</div>
      <h3>C. Rajendran</h3>
      <p style="text-align:justify;">C. Rajendran founded A.R. Waterproofing in 1995 and has been leading the company for over three decades from its base in Nungambakkam, Chennai. Starting with a clear focus on quality waterproofing work, he built the firm into a trusted name for residential, commercial, and industrial projects across Tamil Nadu.</p>
      <p style="text-align:justify;">He is known for his practical, hands-on approach. He personally reviews site requirements, recommends suitable solutions, and ensures the work is executed with care. Alongside his professional work, he is an active member of the Rotary Club, where he contributes to community service initiatives.</p>
      <p style="text-align:justify-content;">Under his leadership, A.R. Waterproofing has completed more than 3000+ projects and served over 1500+ clients. His goal remains the same: to deliver reliable, long-lasting waterproofing solutions that protect buildings from water damage and give clients peace of mind.</p>
    </div>

    <div class="cofounder-card">
      <div class="section-label">LEADERSHIP</div>
      <img class="cofounder-photo" src="1.jpg" alt="Mr. R. Prakash" onerror="this.style.display='none'">
      <div class="name">Mr. R. Prakash B.E.</div>
      <div class="role-sm">Co-Founder</div>
      <p style="text-align:justify;">Mr. R. Prakash B.E. serves as Co-Founder of A.R. Waterproofing, bringing strong engineering expertise to the team. He supports the company’s technical direction and helps ensure every project is planned and executed with precision.</p>
      <p style="text-align:justify;">With a focus on quality materials, proper application methods, and site supervision, he plays a key role in delivering durable waterproofing and construction solutions that meet client expectations.</p>
      <p style="text-align:justify;">His contribution strengthens the company’s ability to handle both new work and repair projects across residential, commercial, and industrial properties with professionalism and care.</p>
    </div>
  </section>

  <!-- Dynamic Services Showcase -->
  <section class="showcase-section" id="services">
    <div class="showcase-header">
      <div>
        <div class="section-label">WHAT WE DO</div>
        <h3>Featured Services</h3>
      </div>
      <a href="services.php" class="btn btn-primary btn-sm">View All Services →</a>
    </div>
    <div class="showcase-grid">
      <?php if (empty($services)): ?>
        <p style="grid-column: 1 / -1; color: var(--muted);">Services will appear here once added from the admin panel.</p>
      <?php else: ?>
        <?php foreach ($services as $service): 
          $img = function_exists('getServiceImage') ? getServiceImage($service['name'], $service['image_path'] ?? '') : '';
          $about = strip_tags($service['about'] ?? '');
          $shortAbout = mb_strlen($about) > 100 ? mb_substr($about, 0, 100) . '...' : $about;
        ?>
          <article class="showcase-card">
            <a href="service.php?id=<?php echo (int)$service['id']; ?>">
              <?php if ($img): ?>
                <img class="showcase-img" src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" loading="lazy">
              <?php else: ?>
                <div class="showcase-img-placeholder">⌂</div>
              <?php endif; ?>
              <div class="showcase-body">
                <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                <p><?php echo htmlspecialchars($shortAbout); ?></p>
                <div class="showcase-meta">View Details →</div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Dynamic Projects Showcase -->
  <section class="showcase-section" id="projects">
    <div class="showcase-header">
      <div>
        <div class="section-label">OUR WORK</div>
        <h3>Featured Projects</h3>
      </div>
      <a href="projects.php" class="btn btn-primary btn-sm">View All Projects →</a>
    </div>
    <div class="showcase-grid">
      <?php if (empty($projects)): ?>
        <p style="grid-column: 1 / -1; color: var(--muted);">Projects will appear here once added from the admin panel.</p>
      <?php else: ?>
        <?php foreach ($projects as $project): 
          $cover = '';
          if (!empty($project['cover_image']) && function_exists('getPublicMediaUrl')) {
            $cover = getPublicMediaUrl($project['cover_image']);
          }
          if (!$cover) {
            $cover = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80';
          }
        ?>
          <article class="showcase-card">
            <a href="project.php?id=<?php echo (int)$project['id']; ?>">
              <img class="showcase-img" src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>" loading="lazy">
              <div class="showcase-body">
                <h4><?php echo htmlspecialchars($project['name']); ?></h4>
                <p><?php echo htmlspecialchars($project['service_name'] ?? 'Waterproofing Project'); ?></p>
                <div class="showcase-meta">View Project →</div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <section class="client-value">
    <div class="section-label" style="text-align:center;">WHY BIG CLIENTS CHOOSE US</div>
    <h3 style="font-family:Manrope,sans-serif;font-size:28px;font-weight:800;text-align:center;margin-bottom:8px;">Trusted For Serious Projects</h3>
    <div class="value-grid">
      <div class="value-item">
        <strong>Proven Track Record</strong>
        <p>3000+ projects completed across residential, commercial, and industrial sites over 30+ years.</p>
      </div>
      <div class="value-item">
        <strong>Quality Materials & Methods</strong>
        <p>We use proven techniques and quality materials suited to each site condition for long-lasting results.</p>
      </div>
      <div class="value-item">
        <strong>Professional Execution</strong>
        <p>Skilled team, clear communication, and disciplined site execution from start to finish.</p>
      </div>
      <div class="value-item">
        <strong>On-Time Delivery</strong>
        <p>We plan and execute projects efficiently to meet timelines without compromising quality.</p>
      </div>
      <div class="value-item">
        <strong>Long-Term Protection</strong>
        <p>Solutions designed to protect buildings from water damage and stand the test of time.</p>
      </div>
      <div class="value-item">
        <strong>Direct Access to Founder</strong>
        <p>Speak directly with the founder for important projects and clear accountability.</p>
      </div>
    </div>
  </section>

  <section class="contact-section" id="contact">
    <div class="contact-inner">
      <div class="contact-copy">
        <div class="section-label" style="color:#d6c5ff;">GET IN TOUCH</div>
        <h2>Let’s Talk About<br><em>Your Project.</em></h2>
        <p>Reach out directly to discuss waterproofing solutions for residential, commercial, or industrial properties.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <a href="tel:+919841044994" class="btn" style="background:#fff;color:var(--purple);">Call Now</a>
          <a href="https://wa.me/919841044994" class="btn btn-whatsapp" target="_blank">WhatsApp</a>
          <button class="btn btn-outline" style="border-color:#fff;color:#fff;" onclick="openExchangeModal()">Exchange Contact</button>
        </div>
      </div>
      <div class="contact-card">
        <div class="contact-row">
          <div class="contact-icon">☎</div>
          <div>
            <small>PHONE</small>
            <a href="tel:+919841044994"><strong>+91 98410 44994</strong></a><br>
            <a href="tel:+918680070461"><strong>+91 86800 70461</strong></a>
          </div>
        </div>
        <div class="contact-row">
          <div class="contact-icon">✉</div>
          <div>
            <small>EMAIL</small>
            <a href="mailto:rajendran@arwaterproofing.com"><strong>rajendran@arwaterproofing.com</strong></a><br>
            <a href="mailto:arwaterproofing@gmail.com"><strong>arwaterproofing@gmail.com</strong></a>
          </div>
        </div>
        <div class="contact-row">
          <div class="contact-icon">🌐</div>
          <div>
            <small>WEBSITE</small>
            <a href="https://www.arwaterproofing.com" target="_blank"><strong>www.arwaterproofing.com</strong></a><br>
            <a href="index.php"><strong>Main Website</strong></a>
          </div>
        </div>
        <div class="contact-row">
          <div class="contact-icon">⌖</div>
          <div>
            <small>ADDRESS</small>
            <strong style="font-weight:600;line-height:1.5;">
              #9, 3, Pushpa Nagar Main Rd,<br>
              Near Valluvar Kottam, Lake Area,<br>
              Nungambakkam, Chennai - 600 034
            </strong>
            <a href="https://maps.google.com/?q=Pushpa+Nagar+Main+Rd,+Nungambakkam,+Chennai+600034" target="_blank" style="display:inline-block;margin-top:6px;font-size:12px;color:var(--purple);font-weight:700;">Open in Google Maps ↗</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="brand-footer">A.R. Waterproofing</div>
    <p>Built on Trust. Protected for Life.</p>
    <p style="margin-top:12px;">
      <a href="index.php">Home</a> &nbsp;•&nbsp;
      <a href="services.php">Services</a> &nbsp;•&nbsp;
      <a href="projects.php">Projects</a> &nbsp;•&nbsp;
      <a href="index.php#reviews">Reviews</a> &nbsp;•&nbsp;
      <a href="#contact">Contact</a>
    </p>
    <p style="margin-top:18px;font-size:11px;opacity:.7;">© A.R. Waterproofing. All rights reserved.</p>
  </footer>

  <!-- Exchange Contact Modal -->
  <div class="modal-overlay" id="exchangeModal">
    <div class="modal">
      <button class="close-modal" onclick="closeExchangeModal()">&times;</button>
      <h3>Exchange Your Contact</h3>
      <p>Share your details so we can connect with you regarding your waterproofing requirements.</p>
      <form id="exchangeForm" onsubmit="handleExchange(event)">
        <div class="form-group">
          <label>Your Name</label>
          <input type="text" name="name" required placeholder="Full Name">
        </div>
        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" required placeholder="+91">
        </div>
        <div class="form-group">
          <label>Email (Optional)</label>
          <input type="email" name="email" placeholder="your@email.com">
        </div>
        <div class="form-group">
          <label>Company / Project Type</label>
          <input type="text" name="company" placeholder="Residential / Commercial / Industrial">
        </div>
        <div class="form-group">
          <label>Message</label>
          <textarea name="message" rows="3" placeholder="Brief about your requirement"></textarea>
        </div>
        <div class="modal-actions">
          <button type="submit" class="btn btn-primary">Submit</button>
          <button type="button" class="btn btn-light" onclick="closeExchangeModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const counters = document.querySelectorAll('.counter');
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;
            const target = parseInt(el.getAttribute('data-target'));
            const duration = 1600;
            const startTime = performance.now();
            function update(currentTime) {
              const elapsed = currentTime - startTime;
              const progress = Math.min(elapsed / duration, 1);
              const eased = 1 - Math.pow(1 - progress, 3);
              el.textContent = Math.floor(eased * target).toLocaleString();
              if (progress < 1) requestAnimationFrame(update);
              else el.textContent = target.toLocaleString();
            }
            requestAnimationFrame(update);
            observer.unobserve(el);
          }
        });
      }, { threshold: 0.5 });
      counters.forEach(c => observer.observe(c));
    });

    function downloadVCard() {
      const vcard = `BEGIN:VCARD
VERSION:3.0
N:Rajendran;C;;;
FN:C. Rajendran
ORG:A.R. Waterproofing
TITLE:Founder
TEL;TYPE=CELL:+919841044994
TEL;TYPE=WORK:+918680070461
EMAIL;TYPE=INTERNET:rajendran@arwaterproofing.com
EMAIL;TYPE=INTERNET:arwaterproofing@gmail.com
URL:https://www.arwaterproofing.com
ADR;TYPE=WORK:;;#9, 3, Pushpa Nagar Main Rd, Near Valluvar Kottam, Lake Area, Nungambakkam;Chennai;;600034;India
NOTE:Founder of A.R. Waterproofing. Rotary Club Member. 30+ years of waterproofing expertise in Chennai.
END:VCARD`;
      const blob = new Blob([vcard], { type: 'text/vcard' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'C_Rajendran_AR_Waterproofing.vcf';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    function openExchangeModal() {
      document.getElementById('exchangeModal').classList.add('active');
    }
    function closeExchangeModal() {
      document.getElementById('exchangeModal').classList.remove('active');
    }
    function handleExchange(e) {
      e.preventDefault();
      const form = e.target;
      const data = new FormData(form);
      const text = encodeURIComponent(
        `Contact Exchange from Profile:\nName: ${data.get('name')}\nPhone: ${data.get('phone')}\nEmail: ${data.get('email') || ''}\nCompany/Project: ${data.get('company') || ''}\nMessage: ${data.get('message') || ''}`
      );
      window.open(`https://wa.me/919841044994?text=${text}`, '_blank');
      alert('Thank you! Your details have been prepared. Opening WhatsApp to send them.');
      form.reset();
      closeExchangeModal();
    }

    document.getElementById('exchangeModal').addEventListener('click', function(e) {
      if (e.target === this) closeExchangeModal();
    });
  </script>
</body>
</html>