const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

console.log('Generating updated ecommerce-seo-service.html...');

const htmlContent = `<!DOCTYPE html>
<html lang="en">
    <!--<< Header Area >>-->
    <head>
        <!-- ========== Meta Tags ========== -->
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="VG Digital Falcon">
        <meta name="description" content="Dominate Rankings & 10X Your Sales - Ecommerce SEO Services by VG Digital Falcon">
        <!-- ======== Page title ============ -->
        <title>Ecommerce SEO - VG Digital Falcon</title>
        <!--<< Favicon >>-->
        <link rel="shortcut icon" href="assets/img/dficon2.png">
        <!--<< Bootstrap min.css >>-->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <!--<< All Min Css >>-->
        <link rel="stylesheet" href="assets/css/all.min.css">
        <!--<< Animate.css >>-->
        <link rel="stylesheet" href="assets/css/animate.css">
        <!--<< Magnific Popup.css >>-->
        <link rel="stylesheet" href="assets/css/magnific-popup.css">
        <!--<< MeanMenu.css >>-->
        <link rel="stylesheet" href="assets/css/meanmenu.css">
        <!--<< Swiper Bundle.css >>-->
        <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
        <!--<< Nice Select.css >>-->
        <link rel="stylesheet" href="assets/css/nice-select.css">
        <!--<< Color.css >>-->
        <link rel="stylesheet" href="assets/css/color.css">
        <!--<< Main.css >>-->
        <link rel="stylesheet" href="assets/css/main.css">
    </head>
    <body class="vg-seo-page">

        <!-- Preloader Start -->
        <div id="preloader" class="preloader">
            <div class="animation-preloader">
                <div class="spinner">                
                </div>
                <div class="txt-loading">
                    <span data-text-preloader="V" class="letters-loading">V</span>
                    <span data-text-preloader="G" class="letters-loading">G</span>
                    <span data-text-preloader="D" class="letters-loading">D</span>
                    <span data-text-preloader="I" class="letters-loading">I</span>
                    <span data-text-preloader="G" class="letters-loading">G</span>
                    <span data-text-preloader="I" class="letters-loading">I</span>
                    <span data-text-preloader="T" class="letters-loading">T</span>
                    <span data-text-preloader="A" class="letters-loading">A</span>
                    <span data-text-preloader="L" class="letters-loading">L</span>
                </div>
                <p class="text-center">Loading</p>
            </div>
            <div class="loader">
                <div class="row">
                    <div class="col-3 loader-section section-left">
                        <div class="bg"></div>
                    </div>
                    <div class="col-3 loader-section section-left">
                        <div class="bg"></div>
                    </div>
                    <div class="col-3 loader-section section-right">
                        <div class="bg"></div>
                    </div>
                    <div class="col-3 loader-section section-right">
                        <div class="bg"></div>
                    </div>
                </div>
            </div>
        </div>

        <!--<< Mouse Cursor Start >>-->  
        <div class="mouse-cursor cursor-outer"></div>
        <div class="mouse-cursor cursor-inner"></div>

        <!-- Offcanvas Area Start -->
        <div class="fix-area">
            <div class="offcanvas__info">
                <div class="offcanvas__wrapper">
                    <div class="offcanvas__content">
                        <div class="offcanvas__top mb-4 d-flex justify-content-between align-items-center">
                            <div class="offcanvas__logo">
                                <a href="index.html" class="vg-brand-wrap">
                                    <img src="assets/img/logo/dflogo-white.png" alt="VG Digital Falcon Logo" class="vg-header-brand-logo">
                                </a>
                            </div>
                            <div class="offcanvas__close">
                                <button aria-label="Close navigation menu"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <p class="text mb-4" style="color: #cbd5e1; font-size: 14px;">
                            At Digital Falcon, our mission is to empower businesses with the tools and strategies they need to thrive in today’s competitive market. We believe in building strong, authentic brands that resonate with audiences and drive lasting success.
                        </p>
                        <div class="vg-mobile-drawer-menu mb-4">
                            <ul class="vg-mobile-nav-list">
                                <li><a href="index.html">Home</a></li>
                                <li><a href="about-us.html">About Us</a></li>
                                <li class="has-dropdown">
                                    <a href="#" class="vg-mobile-parent-link" role="button" aria-expanded="false">
                                        <span>Services</span> <i class="fas fa-chevron-down"></i>
                                    </a>
                                    <ul class="vg-mobile-sub">
                                        <li><a href="index.html#services" style="color: #c084fc; font-weight: 600;"><i class="fa-solid fa-arrow-right me-1"></i> All Services Overview</a></li>
                                        <li><a href="seo-service.html">SEO Optimization</a></li>
                                        <li><a href="ecommerce-seo-service.html" class="active">Ecommerce SEO</a></li>
                                        <li><a href="web-development.html">Website Development</a></li>
                                        <li><a href="performance-marketing.html">Performance Marketing</a></li>
                                        <li><a href="social-media-optimization-agency.html">Social Media Optimization</a></li>
                                        <li><a href="personal-branding-service.html">Personal Branding</a></li>
                                    </ul>
                                </li>
                                <li><a href="index.html#case-studies">Case Studies</a></li>
                                <li><a href="index.html#insights">Insights</a></li>
                                <li><a href="contact.html">Contact</a></li>
                            </ul>
                        </div>
                        <div class="offcanvas__contact">
                            <h4 style="color: #ffffff; font-size: 18px; margin-bottom: 15px;">Contact Info</h4>
                            <ul>
                                <li class="d-flex align-items-center mb-2">
                                    <i class="fal fa-map-marker-alt me-2" style="color: #c084fc;"></i>
                                    <span style="color: #cbd5e1;">Alandur, Tamil Nadu</span>
                                </li>
                                <li class="d-flex align-items-center mb-2">
                                    <i class="fal fa-envelope me-2" style="color: #c084fc;"></i>
                                    <a href="mailto:contact@vgdigitalfalcon.com" style="color: #cbd5e1;">contact@vgdigitalfalcon.com</a>
                                </li>
                                <li class="d-flex align-items-center mb-2">
                                    <i class="fal fa-clock me-2" style="color: #c084fc;"></i>
                                    <span style="color: #cbd5e1;">Mon - Fri, 09am - 05pm</span>
                                </li>
                                <li class="d-flex align-items-center mb-3">
                                    <i class="far fa-phone me-2" style="color: #c084fc;"></i>
                                    <a href="tel:+918695776922" style="color: #cbd5e1;">+91 8695776922</a>
                                </li>
                            </ul>
                            <div class="mt-4">
                                <a href="contact.html" class="vg-btn-primary w-100 text-center">
                                    Get A Quote <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas__overlay"></div>

        <!-- ==========================================================================
             STICKY HEADER AREA (DARK CYBERNETIC GLASSMORPHISM)
             ========================================================================== -->
        <header class="vg-header" id="header-sticky">
            <div class="container d-flex align-items-center justify-content-between">
                <!-- Brand Logo -->
                <a href="index.html" class="vg-brand-wrap" aria-label="VG Digital Falcon Home">
                    <img src="assets/img/logo/dflogo-white.png" alt="VG Digital Falcon Logo" class="vg-header-brand-logo">
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="d-none d-xl-block" aria-label="Main Navigation">
                    <ul class="vg-nav-links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about-us.html">About Us</a></li>
                        <li>
                            <a href="index.html#services" class="active">Services <i class="fas fa-chevron-down" style="font-size: 10px;"></i></a>
                            <ul class="vg-dropdown-menu">
                                <li><a href="seo-service.html">SEO Optimization</a></li>
                                <li><a href="ecommerce-seo-service.html" class="active">Ecommerce SEO</a></li>
                                <li><a href="performance-marketing.html">Google &amp; Meta Ads</a></li>
                                <li><a href="web-development.html">Website Development</a></li>
                                <li><a href="personal-branding-service.html">Personal Branding</a></li>
                                <li><a href="social-media-optimization-agency.html">Social Media Optimization</a></li>
                            </ul>
                        </li>
                        <li><a href="index.html#case-studies">Case Studies</a></li>
                        <li><a href="index.html#insights">Insights</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </nav>

                <!-- Header Action Button & Mobile Toggle -->
                <div class="d-flex align-items-center gap-3">
                    <a href="contact.html" class="vg-btn-primary d-none d-sm-inline-flex">
                        Get Free Strategy <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                    <div aria-label="Open mobile navigation menu" tabindex="0" role="button" class="sidebar__toggle d-xl-none" style="color: #ffffff; font-size: 22px; cursor: pointer;">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- ==========================================================================
             HERO SECTION: DOMINATE RANKINGS & 10X YOUR SALES (DARK CYBER THEME)
             ========================================================================== -->
        <section class="vg-seo-hero">
            <!-- Interactive 3D Search & Backlink Node Network Canvas -->
            <canvas id="vg-seo-hero-canvas" class="vg-seo-hero-canvas"></canvas>

            <div class="container position-relative" style="z-index: 2;">
                <div class="row align-items-center g-5">
                    <!-- Left Hero Content -->
                    <div class="col-lg-7">
                        <!-- Breadcrumb Navigation -->
                        <div class="vg-breadcrumb-wrap wow fadeInUp" data-wow-delay=".1s">
                            <a href="index.html">Home</a>
                            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                            <a href="index.html#services">Services</a>
                            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                            <span class="active">Ecommerce SEO</span>
                        </div>

                        <!-- Pill Badge -->
                        <div class="vg-section-badge mb-3 wow fadeInUp" data-wow-delay=".15s">
                            <span class="vg-pill-badge"><i class="fa-solid fa-cart-shopping me-2"></i> Best Ecommerce SEO Agency</span>
                        </div>

                        <!-- Main Headings (100% Original Content Preserved) -->
                        <h1 class="vg-seo-hero-title wow fadeInUp" data-wow-delay=".2s">
                            Dominate Rankings &amp; <span class="vg-gradient-text">10X Your Sales</span>
                        </h1>

                        <!-- Paragraph (100% Original Content Preserved) -->
                        <p class="vg-seo-hero-sub wow fadeInUp" data-wow-delay=".3s">
                            Rank #1 for high-value product keywords, drive qualified traffic, and boost conversions with our proven Ecommerce SEO strategies. Trusted by Shopify, WooCommerce, and Amazon sellers worldwide.
                        </p>

                        <!-- Hero Actions -->
                        <div class="d-flex align-items-center gap-3 flex-wrap wow fadeInUp" data-wow-delay=".4s">
                            <a href="https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20would%20like%20to%20get%20a%20free%20E-commerce%20Audit%20for%20my%20online%20store.%20Please%20share%20the%20details." target="_blank" rel="noopener" class="vg-btn-primary">
                                Claim Your Free Audit <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: High-Tech Glassmorphic Audit Form Card -->
                    <div class="col-lg-5 wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-seo-audit-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="vg-pill-badge" style="background: rgba(0, 240, 255, 0.12); border-color: rgba(0, 240, 255, 0.35); color: #00F0FF; font-size: 11px;">
                                    <i class="fa-solid fa-bolt me-1"></i> Worth $99 • Free Instant Audit
                                </span>
                            </div>
                            <h3 class="text-white mb-3" style="font-size: 22px; font-weight: 800; line-height: 1.3;">
                                Get a Free Ecommerce SEO Audit Worth $99!
                            </h3>

                            <form id="youtubeSeoForm" action="process_form.php" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                                <div class="form-group">
                                    <label for="name" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #cbd5e1;">Your Name:</label>
                                    <input type="text" id="name" name="name" required placeholder="Enter your full name" style="width: 100%; padding: 11px 14px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 8px; font-size: 14px; color: #ffffff; outline: none; transition: all 0.25s ease;">
                                </div>
                                <div class="form-group">
                                    <label for="channel" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #cbd5e1;">Website URL:</label>
                                    <input type="text" id="channel" name="channel" required placeholder="https://yourstore.com" style="width: 100%; padding: 11px 14px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 8px; font-size: 14px; color: #ffffff; outline: none; transition: all 0.25s ease;">
                                </div>
                                <div class="form-group">
                                    <label for="whatsapp" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #cbd5e1;">WhatsApp Number:</label>
                                    <input type="tel" id="whatsapp" name="whatsapp" required placeholder="+91 / Country Code & Number" style="width: 100%; padding: 11px 14px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 8px; font-size: 14px; color: #ffffff; outline: none; transition: all 0.25s ease;">
                                </div>
                                <button type="submit" class="vg-btn-primary w-100 justify-content-center" style="padding: 12px 20px; font-size: 15px; font-weight: 700; border: none; cursor: pointer; margin-top: 6px;">
                                    Get My Custom SEO Plan <span style="display: inline-block; margin-left: 6px;">→</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================================================
             FEATURE COUNTERS STRIP (GLASSMORPHIC TELEMETRY CARDS)
             ========================================================================== -->
        <section class="vg-ecom-stats-section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                        <div class="vg-ecom-counter-card">
                            <div class="vg-ecom-counter-icon">
                                <img loading="lazy" decoding="async" src="assets/img/feature-icon/icon-6.png" alt="Ecommerce Brands">
                            </div>
                            <h3 class="vg-ecom-counter-num counter" data-target="250">0</h3>
                            <p class="vg-ecom-counter-label">Ecommerce Brands Served</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-ecom-counter-card">
                            <div class="vg-ecom-counter-icon">
                                <img loading="lazy" decoding="async" src="assets/img/feature-icon/icon-8.png" alt="Ranking Keywords">
                            </div>
                            <h3 class="vg-ecom-counter-num counter" data-target="3200">0</h3>
                            <p class="vg-ecom-counter-label">#1 Ranking Keywords</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                        <div class="vg-ecom-counter-card">
                            <div class="vg-ecom-counter-icon">
                                <img loading="lazy" decoding="async" src="assets/img/feature-icon/icon-3.png" alt="Organic Clicks">
                            </div>
                            <h3 class="vg-ecom-counter-num counter" data-target="1200" data-suffix="K+">0</h3>
                            <p class="vg-ecom-counter-label">Monthly Organic Clicks</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".8s">
                        <div class="vg-ecom-counter-card">
                            <div class="vg-ecom-counter-icon">
                                <img loading="lazy" decoding="async" src="assets/img/feature-icon/icon-4.png" alt="Sales Increase">
                            </div>
                            <h3 class="vg-ecom-counter-num counter" data-target="3" data-suffix="X">0</h3>
                            <p class="vg-ecom-counter-label">Increase in Sales</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================================================
             FEATURE HOSTING / ABOUT SECTION (100% CONTENT PRESERVED)
             ========================================================================== -->
        <section class="vg-seo-about-section">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-xl-6 col-lg-6 wow fadeInUp" data-wow-delay=".3s">
                        <div class="vg-cyber-image-frame">
                            <img loading="lazy" decoding="async" src="assets/img/ecom2.png" alt="Ecommerce SEO Agency" style="border-radius: 14px;">
                        </div>
                    </div>
                    
                    <div class="col-xl-6 col-lg-6">
                        <div class="vg-section-badge mb-3 wow fadeInUp" data-wow-delay=".1s">
                            <span class="vg-pill-badge"><i class="fa-solid fa-chart-line me-2"></i> Our Ecommerce SEO Service</span>
                        </div>
                        <h2 class="text-white wow fadeInUp mb-4" data-wow-delay=".2s" style="font-size: clamp(28px, 2.5vw, 40px); font-weight: 800; line-height: 1.25;">
                            Grow Your Online Store with <br>
                            <span class="vg-gradient-text">Best Ecommerce SEO Agency</span>
                        </h2>
                        <p class="wow fadeInUp mb-3" data-wow-delay=".3s" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; text-align: justify;">
                            VG Digital Falcon is the best ecommerce SEO agency, delivering data-driven strategies that improve your rankings, optimize your product pages, and drive revenue from search engines. Whether you’re on Shopify, WooCommerce, Magento, or a custom CMS—we help your ecommerce business stand out. Looking for affordable ecommerce SEO services for small businesses or a partner to scale your enterprise store globally?
                        </p>
                        <p class="wow fadeInUp mb-2" data-wow-delay=".4s" style="color: #cbd5e1; font-size: 15px; font-weight: 600;">
                            Our top ecommerce SEO consultants near you specialize in:
                        </p>
                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-center gap-2 mb-2 wow fadeInUp" data-wow-delay=".45s" style="color: #cbd5e1; font-size: 14.5px;">
                                <i class="fa-solid fa-circle-check" style="color: #00F0FF;"></i>
                                <span>Ranking your products at the top of Google</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-2 wow fadeInUp" data-wow-delay=".5s" style="color: #cbd5e1; font-size: 14.5px;">
                                <i class="fa-solid fa-circle-check" style="color: #00F0FF;"></i>
                                <span>Increasing your visibility in competitive niches</span>
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-2 wow fadeInUp" data-wow-delay=".55s" style="color: #cbd5e1; font-size: 14.5px;">
                                <i class="fa-solid fa-circle-check" style="color: #00F0FF;"></i>
                                <span>Driving higher conversions through SEO for ecommerce</span>
                            </li>
                        </ul>
                        <p class="wow fadeInUp mb-4" data-wow-delay=".6s" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; text-align: justify;">
                            Let us transform your online store with expert ecommerce SEO audit services, product page optimization, and scalable content strategies.
                        </p>
                        <div class="wow fadeInUp" data-wow-delay=".7s">
                            <a href="https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20would%20like%20to%20get%20a%20free%20E-commerce%20Audit%20for%20my%20online%20store.%20Please%20share%20the%20details." target="_blank" rel="noopener" class="vg-btn-primary">
                                Talk To Our Ecommerce Specialist <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================================================
             WHY SEO SECTION (100% ORIGINAL CONTENT IN SLEEK GLASSMORPHIC PILLARS)
             ========================================================================== -->
        <section class="vg-seo-why-section">
            <div class="container">
                <div class="text-center max-w-750 mx-auto mb-5">
                    <span class="vg-pill-badge wow fadeInUp" data-wow-delay=".1s"><i class="fa-solid fa-circle-question me-2"></i> Why SEO?</span>
                    <h2 class="text-white mt-2 wow fadeInUp" data-wow-delay=".2s" style="font-size: clamp(26px, 2.3vw, 36px); font-weight: 800; line-height: 1.25;">
                        Why Does Your Online Store Need a Trusted <br>
                        <span class="vg-gradient-text">Ecommerce SEO Partner?</span>
                    </h2>
                    <p class="wow fadeInUp mt-3" data-wow-delay=".3s" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; text-align: center;">
                        Search Engine Optimization is not optional, it’s essential. Especially in ecommerce, where competition is fierce and visibility means everything. <br class="d-none d-lg-block">As the best ecommerce SEO agency in the USA, we craft strategies that bring qualified buyers to your website through:
                    </p>
                </div>

                <!-- 3 Pillars Grid -->
                <div class="row g-4 justify-content-center mb-4">
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                        <div class="vg-ecom-pillar-card">
                            <div class="vg-pillar-icon" style="color: #00F0FF;">
                                <i class="fa-solid fa-cart-shopping"></i>
                            </div>
                            <h4 class="text-white mt-2 mb-2" style="font-size: 18px; font-weight: 700;">Product Page SEO</h4>
                            <p style="color: #cbd5e1; font-size: 14px; margin: 0; line-height: 1.6;">
                                <i class="fa-solid fa-circle-check text-cyan me-1"></i> Product page SEO optimization services.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".3s">
                        <div class="vg-ecom-pillar-card">
                            <div class="vg-pillar-icon" style="color: #A855F7;">
                                <i class="fa-solid fa-gears"></i>
                            </div>
                            <h4 class="text-white mt-2 mb-2" style="font-size: 18px; font-weight: 700;">Technical Platform SEO</h4>
                            <p style="color: #cbd5e1; font-size: 14px; margin: 0; line-height: 1.6;">
                                <i class="fa-solid fa-circle-check text-violet me-1"></i> Technical SEO for Shopify and WooCommerce
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-ecom-pillar-card">
                            <div class="vg-pillar-icon" style="color: #10B981;">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <h4 class="text-white mt-2 mb-2" style="font-size: 18px; font-weight: 700;">Ethical Authority</h4>
                            <p style="color: #cbd5e1; font-size: 14px; margin: 0; line-height: 1.6;">
                                <i class="fa-solid fa-circle-check text-emerald me-1"></i> Ethical, white-hat link building.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="wow fadeInUp mt-4" data-wow-delay=".5s" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; text-align: center; max-width: 980px; margin-left: auto; margin-right: auto;">
                    From local SEO for ecommerce businesses to international ecommerce SEO services, we ensure your store ranks, converts, and <br class="d-none d-lg-block">scales—sustainably. void the trap of low-quality SEO. Choose experts who focus on revenue, not just rankings.
                </p>
            </div>
        </section>

        <!-- ==========================================================================
             CTA CONTACT STRIP (HIGH-IMPACT CYBERNETIC BANNER)
             ========================================================================== -->
        <section class="vg-seo-cta-strip">
            <div class="container">
                <div class="vg-seo-cta-banner wow fadeInUp" data-wow-delay=".2s">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <div class="vg-section-badge mb-2">
                                <span class="vg-pill-badge" style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.2); color: #ffffff;">
                                    <i class="fa-solid fa-headset me-2"></i> Contact Us
                                </span>
                            </div>
                            <h2 class="text-white mb-4" style="font-size: clamp(24px, 2.3vw, 34px); font-weight: 800; line-height: 1.3;">
                                Try Our Expert Ecommerce SEO Services
                            </h2>
                            <a href="https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20would%20like%20to%20get%20a%20free%20E-commerce%20Audit%20for%20my%20online%20store.%20Please%20share%20the%20details." target="_blank" rel="noopener" class="vg-btn-primary">
                                Get Free Audit <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="col-lg-4 text-center text-lg-end">
                            <div class="vg-cyber-image-frame">
                                <img loading="lazy" decoding="async" src="assets/img/cta-3.png" alt="Ecommerce SEO Services">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================================================
             SERVICES OVERVIEW (SWIPER CAROUSEL IN DARK GLASSMORPHIC CARDS)
             ========================================================================== -->
        <section class="vg-seo-services-section">
            <div class="container">
                <div class="section-title text-center max-w-750 mx-auto mb-5">
                    <span class="vg-pill-badge wow fadeInUp" data-wow-delay=".1s"><i class="fa-solid fa-layer-group me-2"></i> What We Do?</span>
                    <h2 class="text-white mt-2 wow fadeInUp" data-wow-delay=".2s" style="font-size: clamp(26px, 2.4vw, 36px); font-weight: 800;">
                        Ecommerce SEO Services Overview
                    </h2>
                    <p class="wow fadeInUp mt-2" data-wow-delay=".3s" style="color: #cbd5e1; font-size: 15px; line-height: 1.6;">
                        At VG Digital Falcon, a specialist Ecommerce SEO agency, we use proven strategies to help your Product rank higher, reach the right audience, and deliver measurable results. <br class="d-none d-lg-block">Discover how our expert Ecommerce marketing services can boost your brand visibility and business growth.
                    </p>
                </div>

                <div class="swiper testimonial-slider">
                    <div class="swiper-wrapper">
                        <!-- Slide 1 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">Local SEO Strategies</h3>
                                    <i class="fa-solid fa-location-dot" style="color: #00F0FF; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Capture more customers in your area with a SEO plans. We enhance your Google Business profile, improve local visibility, and drive both store and website visits from nearby searches.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">Content Plans for Growth</h3>
                                    <i class="fa-solid fa-pen-nib" style="color: #A855F7; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Target valuable buyer keywords with engaging blogs, guides, and product content that strengthens your authority and attracts visitors who are ready to purchase.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">High-Quality Link Building</h3>
                                    <i class="fa-solid fa-link" style="color: #10B981; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Build strong, relevant backlinks from trusted industry sites—boosting your store’s authority and rankings without resorting to risky or spam-based methods.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 4 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">Product Page SEO</h3>
                                    <i class="fa-solid fa-cart-shopping" style="color: #38BDF8; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Improve rankings with expert product page SEO. We refine headlines, meta descriptions, schema, alt tags, and layouts to raise visibility and click-through rates.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 5 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">Technical SEO Checks</h3>
                                    <i class="fa-solid fa-microchip" style="color: #C084FC; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Get a detailed ecommerce site audit to fix speed, crawling, Core Web Vitals, and mobile performance issues before they impact rankings or user experience.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 6 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">Keyword for Ecommerce Stores</h3>
                                    <i class="fa-solid fa-bullseye" style="color: #F59E0B; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Identify profitable, high-conversion keywords for Shopify, WooCommerce, and more. Learn what your audience searches for and rank higher for those terms.
                                </p>
                            </div>
                        </div>

                        <!-- Slide 7 -->
                        <div class="swiper-slide">
                            <div class="vg-ecom-service-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="vg-ecom-service-title mb-0">Competitor SEO Insights</h3>
                                    <i class="fa-solid fa-chess-knight" style="color: #FB7185; font-size: 20px;"></i>
                                </div>
                                <p class="vg-ecom-service-desc">
                                    Understand exactly how competitors outrank you. We analyze their SEO tactics in detail and create a stronger, data-driven plan to take the lead.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="swiper-dot mt-4 text-center">
                        <div class="dot"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================================================
             FAQ SECTION (DARK CYBERNETIC ACCORDION)
             ========================================================================== -->
        <section class="vg-seo-faq-section" id="faq">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-5 wow fadeInUp" data-wow-delay=".3s">
                        <div class="vg-cyber-image-frame">
                            <img loading="lazy" decoding="async" src="assets/img/FAQ3.png" alt="Ecommerce SEO FAQ">
                        </div>
                    </div>
                    
                    <div class="col-lg-7">
                        <div class="vg-section-badge mb-3 wow fadeInUp" data-wow-delay=".1s">
                            <span class="vg-pill-badge"><i class="fa-solid fa-circle-question me-2"></i> FAQs</span>
                        </div>
                        <h2 class="text-white mb-4 wow fadeInUp" data-wow-delay=".2s" style="font-size: clamp(26px, 2.3vw, 36px); font-weight: 800;">
                            Ecommerce SEO FAQ
                        </h2>

                        <div class="accordion" id="accordionEcom">
                            <div class="accordion-item vg-ecom-faq-item wow fadeInUp" data-wow-delay=".3s">
                                <h5 class="accordion-header">
                                    <button class="accordion-button vg-ecom-faq-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqE1" aria-expanded="true" aria-controls="faqE1">
                                        What is Ecommerce SEO, and why is it important for my store?
                                    </button>
                                </h5>
                                <div id="faqE1" class="accordion-collapse collapse show" data-bs-parent="#accordionEcom">
                                    <div class="accordion-body vg-ecom-faq-body">
                                        Ecommerce SEO is the process of optimizing your online store so it ranks higher in search engines, making it easier for potential customers to find your products. Without it, you’re relying solely on ads or word of mouth, which limits growth. SEO builds long-term, free traffic that converts.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item vg-ecom-faq-item wow fadeInUp" data-wow-delay=".4s">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed vg-ecom-faq-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqE2" aria-expanded="false" aria-controls="faqE2">
                                        How long does it take to see results from Ecommerce SEO?
                                    </button>
                                </h5>
                                <div id="faqE2" class="accordion-collapse collapse" data-bs-parent="#accordionEcom">
                                    <div class="accordion-body vg-ecom-faq-body">
                                        Most ecommerce businesses start seeing ranking improvements within 6–12 weeks, with significant traffic growth in 4–6 months. SEO is a compounding process—the longer you invest, the greater the returns.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item vg-ecom-faq-item wow fadeInUp" data-wow-delay=".5s">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed vg-ecom-faq-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqE3" aria-expanded="false" aria-controls="faqE3">
                                        Will Ecommerce SEO work for a new online store?
                                    </button>
                                </h5>
                                <div id="faqE3" class="accordion-collapse collapse" data-bs-parent="#accordionEcom">
                                    <div class="accordion-body vg-ecom-faq-body">
                                        Absolutely. While established stores may have more data to work with, we’ve helped brand-new shops rank by targeting low-competition keywords, optimizing product pages, and building early trust with Google.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item vg-ecom-faq-item wow fadeInUp" data-wow-delay=".6s">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed vg-ecom-faq-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqE4" aria-expanded="false" aria-controls="faqE4">
                                        Can Ecommerce SEO actually increase sales?
                                    </button>
                                </h5>
                                <div id="faqE4" class="accordion-collapse collapse" data-bs-parent="#accordionEcom">
                                    <div class="accordion-body vg-ecom-faq-body">
                                        Yes. By ranking for purchase-intent keywords like “buy [product] online” or “[product] free shipping,” we attract visitors who are ready to buy. More qualified traffic means higher sales without increasing ad spend.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item vg-ecom-faq-item wow fadeInUp" data-wow-delay=".7s">
                                <h5 class="accordion-header">
                                    <button class="accordion-button collapsed vg-ecom-faq-btn" type="button" data-bs-toggle="collapse" data-bs-target="#faqE5" aria-expanded="false" aria-controls="faqE5">
                                        Is Ecommerce SEO a one-time job or ongoing process?
                                    </button>
                                </h5>
                                <div id="faqE5" class="accordion-collapse collapse" data-bs-parent="#accordionEcom">
                                    <div class="accordion-body vg-ecom-faq-body">
                                        SEO is ongoing. Search algorithms evolve, competitors update their strategies, and new opportunities appear. Continuous optimization ensures your store maintains and improves rankings over time.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================================================
             FOOTER SECTION (DARK CYBERNETIC THEME WITH 2026 COPYRIGHT)
             ========================================================================== -->
        <footer class="footer-section fix bg-cover" style="background-color: #07070F !important; background: radial-gradient(circle at 50% 10%, rgba(139, 92, 246, 0.12) 0%, rgba(0, 240, 255, 0.05) 30%, #07070F 70%) !important;">
            <div class="footer-widgets-wrapper" style="padding: 70px 0 40px !important;">
                <div class="container">
                    <div class="row g-4">
                        <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                            <div class="single-footer-widget">
                                <div class="widget-head">
                                    <a href="index.html">
                                        <img src="assets/img/logo/dflogo-white.png" alt="VG Digital Falcon Logo">
                                    </a>
                                </div>
                                <div class="footer-content">
                                    <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6;">
                                        At Digital Falcon, our mission is to empower businesses with tools and strategies they need to thrive in today’s market. We believe in building strong, authentic brands that resonate with audiences and drive lasting success. 
                                    </p>
                                    <ul class="contact-info list-unstyled mt-3">
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="fa-solid fa-phone me-2" style="color: #00F0FF;"></i>
                                            <a href="tel:+918695776922" style="color: #cbd5e1;">+91 8695776922</a>
                                        </li>
                                        <li class="d-flex align-items-center">
                                            <i class="fa-solid fa-envelope me-2" style="color: #00F0FF;"></i>
                                            <a href="mailto:contact@vgdigitalfalcon.com" style="color: #cbd5e1;">contact@vgdigitalfalcon.com</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 ps-lg-5 wow fadeInUp" data-wow-delay=".4s">
                            <div class="single-footer-widget ml-50">
                                <div class="widget-head">
                                    <h3 class="text-white mb-3" style="font-size: 18px; font-weight: 700;">Company</h3>
                                </div>
                                <ul class="list-area list-unstyled">
                                    <li class="mb-2">
                                        <a href="about-us.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            About VG Digital Falcon
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="contact.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            Contact Us
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            Blogs
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="#" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            Portfolio
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#faq" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            FAQ’S
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 ps-lg-5 wow fadeInUp" data-wow-delay=".6s">
                            <div class="single-footer-widget style-margin">
                                <div class="widget-head">
                                    <h3 class="text-white mb-3" style="font-size: 18px; font-weight: 700;">Our Services</h3>
                                </div>
                                <ul class="list-area list-unstyled">
                                    <li class="mb-2">
                                        <a href="web-development.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            Web Development
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="social-media-optimization-agency.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            SMO Service
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="seo-service.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            SEO Service
                                        </a>
                                    </li>
                                    <li class="mb-2">
                                        <a href="performance-marketing.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            Performance Marketing
                                        </a>
                                    </li>
                                    <li>
                                        <a href="personal-branding-service.html" style="color: #cbd5e1;">
                                            <i class="fa-solid fa-angles-right me-2" style="color: #00F0FF; font-size: 11px;"></i>
                                            Personal Branding
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 ps-lg-4 wow fadeInUp" data-wow-delay=".9s">
                            <div class="single-footer-widget">
                                <div class="widget-head">
                                    <h3 class="text-white mb-3" style="font-size: 18px; font-weight: 700;">Newsletter</h3>
                                </div>
                                <div class="footer-content">
                                    <p style="color: #cbd5e1; font-size: 14px;">
                                        Sign up to seargin weekly newsletter to get the latest updates.
                                    </p>
                                    <div class="footer-input" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(168, 85, 247, 0.35); border-radius: 10px; display: flex; align-items: center; padding: 4px; margin: 15px 0;">
                                        <input aria-label="Enter Email Address" type="email" id="email2" placeholder="Enter Email Address" style="background: transparent; border: none; padding: 8px 12px; color: #ffffff; outline: none; width: 100%;">
                                        <button aria-label="Subscribe to newsletter" class="newsletter-btn" type="submit" style="background: linear-gradient(135deg, #00F0FF, #A855F7); border: none; width: 38px; height: 38px; border-radius: 8px; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                            <i class="fa-regular fa-paper-plane"></i>
                                        </button>
                                    </div>
                                    <div class="social-icon d-flex align-items-center gap-2">
                                        <a href="#" style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; display: flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f"></i></a>
                                        <a href="#" style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; display: flex; align-items: center; justify-content: center;"><i class="fab fa-twitter"></i></a>
                                        <a href="#" style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-linkedin-in"></i></a>
                                        <a href="#" style="width: 34px; height: 34px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #cbd5e1; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-youtube"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom" style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding: 20px 0;">
                <div class="container">
                    <div class="footer-wrapper d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <p class="wow fadeInLeft mb-0" data-wow-delay=".3s" style="color: #94a3b8; font-size: 13.5px;">
                            © All Copyright 2026 by <a href="index.html" style="color: #00F0FF; font-weight: 600;">VG Digital Falcon</a>
                        </p>
                        <div class="d-flex align-items-center gap-3" style="font-size: 13px;">
                            <a href="#" style="color: #94a3b8;">Privacy Policy</a>
                            <span style="color: #475569;">•</span>
                            <a href="#" style="color: #94a3b8;">Terms of Service</a>
                        </div>
                    </div>
                </div>
                <a href="#" id="scrollUp" class="scroll-icon" aria-label="Scroll to top">
                    <i class="far fa-arrow-up"></i>
                </a>
            </div>
        </footer>

        <!-- Floating WhatsApp Radar Button -->
        <a href="https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20would%20like%20to%20get%20a%20free%20E-commerce%20Audit%20for%20my%20online%20store.%20Please%20share%20the%20details." target="_blank" rel="noopener" class="vg-floating-whatsapp" title="Chat on WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>

        <!-- Global Persistent Flying Falcon Mascot -->
        <div id="vg-global-mascot" class="vg-global-mascot-wrap" title="VG Falcon AI Mascot">
            <div class="vg-mascot-bubble" id="vg-mascot-tip">🚀 Let's Scale Your Online Store!</div>
            <div class="vg-mascot-falcon">
                <div class="vg-mascot-aura"></div>
                <img loading="lazy" decoding="async" src="assets/img/dficon2.png" alt="VG Digital Falcon Logo Bird" class="vg-mascot-real-img">
            </div>
        </div>

        <!--<< All JS Plugins >>-->
        <script src="assets/js/jquery-3.7.1.min.js"></script>
        <script src="assets/js/viewport.jquery.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/jquery.nice-select.min.js"></script>
        <script src="assets/js/jquery.waypoints.js"></script>
        <script src="assets/js/jquery.counterup.min.js"></script>
        <script src="assets/js/swiper-bundle.min.js"></script>
        <script src="assets/js/jquery.meanmenu.min.js"></script>
        <script src="assets/js/jquery.magnific-popup.min.js"></script>
        <script src="assets/js/wow.min.js"></script>
        <script src="assets/js/main.js"></script>
        <script src="assets/js/dtapex-particles.js"></script>
        <script src="assets/js/seo-hero-canvas.js"></script>

        <!-- Preserved Form Submission Handler -->
        <script>
        document.getElementById('youtubeSeoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Form validation
            const name = document.getElementById('name').value;
            const channel = document.getElementById('channel').value;
            const whatsapp = document.getElementById('whatsapp').value;
            
            if (!name || !channel || !whatsapp) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Validate WhatsApp number (basic validation)
            if (!/^[\\d\\s\\+\\-\\(\\)]{10,}$/.test(whatsapp)) {
                alert('Please enter a valid WhatsApp number');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Add loading state
            submitBtn.innerHTML = 'Processing...';
            submitBtn.disabled = true;
            
            fetch('process_form.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you! Your request has been submitted. We will contact you soon.');
                    this.reset();
                } else {
                    alert('There was an error submitting your form. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error submitting your form. Please try again.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
        </script>

        <!-- Preserved Counter Script -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            const speed = 200;
            
            function runCounter() {
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const suffix = counter.getAttribute('data-suffix') || '';
                    const count = +counter.innerText;
                    const increment = target / speed;
                    
                    if (count < target) {
                        counter.innerText = Math.ceil(count + increment) + suffix;
                        setTimeout(runCounter, 1);
                    } else {
                        counter.innerText = target.toLocaleString() + suffix;
                    }
                });
            }
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        runCounter();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });
            
            counters.forEach(counter => {
                if (counter.parentElement) {
                    observer.observe(counter.parentElement);
                }
            });

            // Dedicated Mobile Drawer Menu Accordion
            var parentLinks = document.querySelectorAll('.vg-mobile-parent-link');
            parentLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var sub = this.nextElementSibling;
                    var icon = this.querySelector('i');
                    if (sub) {
                        sub.classList.toggle('is-open');
                        var isOpen = sub.classList.contains('is-open');
                        this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                        if (icon) {
                            if (isOpen) {
                                icon.classList.remove('fa-chevron-down');
                                icon.classList.add('fa-chevron-up');
                            } else {
                                icon.classList.remove('fa-chevron-up');
                                icon.classList.add('fa-chevron-down');
                            }
                        }
                    }
                });
            });

            var drawerCloseLinks = document.querySelectorAll('.offcanvas__wrapper a[href^="#"]:not(.vg-mobile-parent-link), .vg-mobile-nav-list a:not(.vg-mobile-parent-link)');
            drawerCloseLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    var info = document.querySelector('.offcanvas__info');
                    var overlay = document.querySelector('.offcanvas__overlay');
                    if (info) info.classList.remove('info-open');
                    if (overlay) overlay.classList.remove('overlay-open');
                });
            });

            // FAQ active state toggle
            var faqButtons = document.querySelectorAll('.vg-ecom-faq-btn');
            faqButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var parentItem = this.closest('.vg-ecom-faq-item');
                    var allItems = document.querySelectorAll('#accordionEcom .vg-ecom-faq-item');
                    setTimeout(function() {
                        allItems.forEach(function(item) {
                            var collapse = item.querySelector('.accordion-collapse');
                            if (collapse && collapse.classList.contains('show')) {
                                item.classList.add('is-open');
                            } else {
                                item.classList.remove('is-open');
                            }
                        });
                    }, 350);
                });
            });
        });
        </script>
    </body>
</html>`;

const targets = [
  path.join(rootDir, 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'website', 'ecommerce-seo-service.html')
];

targets.forEach((targetPath) => {
  if (fs.existsSync(path.dirname(targetPath))) {
    fs.writeFileSync(targetPath, htmlContent, 'utf8');
    console.log('Successfully wrote:', targetPath);
  }
});

console.log('--- Ecommerce SEO Page Generation Completed ---');

