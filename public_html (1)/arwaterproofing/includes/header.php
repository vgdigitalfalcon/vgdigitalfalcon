<?php
// Shared header matching the exact homepage design from index.php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}
$pageTitle = $pageTitle ?? 'A.R. Waterproofing';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#55b7b9">
  <meta name="description" content="A.R. Waterproofing provides waterproofing, grouting, construction, renovation and structural repair services in Chennai.">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title><?php echo isset($pageTitle) && $pageTitle !== 'A.R. Waterproofing' ? sanitize($pageTitle) . ' | ' : ''; ?>A.R. Waterproofing | Built on Trust. Protected for Life.</title>
  <link rel="manifest" href="manifest.webmanifest">
  <link rel="icon" href="assets/favicon-64.png" sizes="64x64" type="image/png">
  <link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="app-splash" id="appSplash" role="status" aria-label="Loading A.R. Waterproofing">
    <img src="assets/ar-logo.png" alt="A.R. Waterproofing logo">
    <span class="splash-loader" aria-hidden="true"></span>
    <p>Waterproofing & Construction Solutions</p>
  </div>
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header">
    <a class="brand" href="index.php" aria-label="A.R. Waterproofing home">
      <img class="brand-logo" src="assets/ar-logo.png" alt="A.R. Waterproofing logo">
      <span class="brand-name">A.R. <strong>WATERPROOFING</strong><small>PROTECTING WHAT MATTERS</small></span>
    </a>
    <nav class="desktop-nav" aria-label="Main navigation">
      <a href="index.php">Home</a>
      <a href="index.php#about">About</a>
      <a href="services.php">Services</a>
      <a href="projects.php">Projects</a>
      <a href="rajendran.php">Founder</a>
      <a href="index.php#reviews">Reviews</a>
      <a href="index.php#contact">Contact</a>
    </nav>
    <a class="header-cta" href="tel:+919841044994">Call Now <span aria-hidden="true">↗</span></a>
    <button class="menu-toggle" id="menuToggle" aria-label="Open navigation" aria-expanded="false" aria-controls="mobileMenu"><span></span><span></span><span></span></button>
  </header>
  <nav class="mobile-menu" id="mobileMenu" aria-label="Mobile navigation" hidden>
    <a href="index.php">Home</a>
    <a href="index.php#about">About</a>
    <a href="services.php">Services</a>
    <a href="projects.php">Projects</a>
          <a href="rajendran.php">Founder</a>

    <a href="index.php#reviews">Reviews</a>
    <a href="index.php#contact">Contact</a>
  </nav>
  <main id="main">