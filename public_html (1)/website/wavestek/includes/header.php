<?php
if (!defined('SITE_NAME')) require_once __DIR__ . '/config.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME; ?></title>
  <meta name="description" content="<?php echo isset($page_desc) ? $page_desc : 'Wavestek Power Solutions – Manufacturer & supplier of Online UPS, Servo Voltage Stabilizers, Inverters, Batteries and Solar Products in Chennai.'; ?>">
  <meta name="theme-color" content="#D32F2F">
  <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/images/favicon.svg">
  <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.svg">
  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title).' | '.SITE_NAME : SITE_NAME; ?>">
  <meta property="og:description" content="<?php echo isset($page_desc) ? htmlspecialchars($page_desc) : 'Wavestek Power Solutions — UPS, Servo Stabilizers, Inverters &amp; Batteries. Chennai.'; ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Wavestek Power Solutions">
  <link rel="canonical" href="https://www.wavestekpowersolutions.in<?php echo strtok($_SERVER['REQUEST_URI'],'?'); ?>">
  <!-- Local Business Schema -->
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"ElectricalContractor","name":"Wavestek Power Solutions","telephone":["+919884152004","+919342486992"],"email":"admin@wavestekpowersolutions.in","address":{"@type":"PostalAddress","streetAddress":"3/23, Old Bangaru Colony 1st Street, West K.K.Nagar","addressLocality":"Chennai","addressRegion":"Tamil Nadu","postalCode":"600078","addressCountry":"IN"},"openingHours":"Mo-Sa 09:00-19:00","foundingDate":"2021","areaServed":"India"}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>

<!-- Top Bar -->
<div class="topbar">
  <div class="container">
    <div class="topbar-inner">
      <div class="topbar-left">
        <a href="mailto:<?php echo EMAIL_ADMIN; ?>"><i class="fas fa-envelope"></i> <?php echo EMAIL_ADMIN; ?></a>
        <a href="tel:<?php echo PHONE_PRIMARY; ?>"><i class="fas fa-phone"></i> <?php echo PHONE_PRIMARY; ?></a>
      </div>
      <div class="topbar-right">
        <span><i class="fas fa-map-marker-alt"></i> Chennai, India</span>
        <a href="https://wa.me/<?php echo WHATSAPP_NO; ?>" target="_blank" class="topbar-wa"><i class="fab fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </div>
</div>

<!-- Main Header -->
<header class="main-header" id="mainHeader">
  <div class="container">
    <nav class="navbar navbar-expand-lg">
      <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
        <div class="logo-wrap">
          <div class="logo-icon">
            <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="44" height="44" rx="4" fill="#D32F2F"/>
              <path d="M24 6L10 26h12l-2 12 18-22H26l-2-10z" fill="#F5A623" stroke="#fff" stroke-width="1"/>
            </svg>
          </div>
          <div class="logo-text">
            <span class="logo-brand">WAVESTEK</span>
            <span class="logo-sub">POWER SOLUTIONS</span>
          </div>
        </div>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="toggler-icon"><span></span><span></span><span></span></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <?php foreach ($nav_menu as $item):
            $has_drop = !empty($item['dropdown']);
            $is_active = ($current_page === basename($item['href']));
          ?>
          <li class="nav-item <?php echo $has_drop ? 'dropdown' : ''; ?> <?php echo $is_active ? 'active' : ''; ?>">
            <?php if ($has_drop): ?>
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><?php echo $item['label']; ?></a>
              <?php render_dropdown($item['dropdown']); ?>
            <?php else: ?>
              <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo SITE_URL . '/' . ltrim($item['href'], '/'); ?>"><?php echo $item['label']; ?></a>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
          <li class="nav-item ms-lg-3">
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-danger btn-header">Get Quote</a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</header>
