<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta -->
    <title><?= sanitize($page['meta_title']) ?></title>
    <meta name="description" content="<?= sanitize($page['meta_description']) ?>">
    <?php if (!empty($page['meta_keywords'])): ?>
    <meta name="keywords" content="<?= sanitize($page['meta_keywords']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= SITE_URL ?>/<?= sanitize($page['slug']) ?>">

    <!-- Open Graph -->
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?= SITE_URL ?>/<?= sanitize($page['slug']) ?>">
    <meta property="og:title"       content="<?= sanitize($page['meta_title']) ?>">
    <meta property="og:description" content="<?= sanitize($page['meta_description']) ?>">
    <meta property="og:site_name"   content="<?= SITE_NAME ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?= sanitize($page['meta_title']) ?>">
    <meta name="twitter:description" content="<?= sanitize($page['meta_description']) ?>">

    <!-- Schema Markup -->
    <?= buildSchemaMarkup($page) ?>
    <?= buildBreadcrumbSchema($page) ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
    /* =====================================================
       VG DIGITAL FALCON — PROGRAMMATIC SEO TEMPLATE
       Brand: Bold Midnight Blue × Electric Amber
    ===================================================== */

    :root {
        --navy:        #0A0F1E;
        --navy-mid:    #0F1730;
        --navy-light:  #1A2545;
        --amber:       #F59E0B;
        --amber-light: #FCD34D;
        --amber-dark:  #D97706;
        --white:       #FFFFFF;
        --off-white:   #F8F9FF;
        --text-muted:  #94A3B8;
        --text-body:   #CBD5E1;
        --border:      rgba(255,255,255,0.08);
        --card-bg:     rgba(255,255,255,0.04);
        --card-hover:  rgba(245,158,11,0.08);
        --shadow-card: 0 4px 24px rgba(0,0,0,0.3);
        --shadow-btn:  0 4px 20px rgba(245,158,11,0.35);
        --radius-sm:   8px;
        --radius-md:   16px;
        --radius-lg:   24px;
        --font-head:   'Syne', sans-serif;
        --font-body:   'DM Sans', sans-serif;
        --transition:  all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; font-size: 16px; }

    body {
        background: var(--navy);
        color: var(--text-body);
        font-family: var(--font-body);
        line-height: 1.7;
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
    }

    /* ---- Typography ---- */
    h1, h2, h3, h4, h5 {
        font-family: var(--font-head);
        color: var(--white);
        line-height: 1.2;
        letter-spacing: -0.01em;
    }

    h1 { font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 800; }
    h2 { font-size: clamp(1.6rem, 3.5vw, 2.5rem); font-weight: 700; }
    h3 { font-size: clamp(1.1rem, 2vw, 1.4rem); font-weight: 700; }

    p { margin-bottom: 1rem; }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .section { padding: 90px 0; }
    .section-sm { padding: 60px 0; }

    .label-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(245,158,11,0.12);
        color: var(--amber);
        border: 1px solid rgba(245,158,11,0.25);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .section-header {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 60px;
    }

    .section-header p {
        font-size: 1.1rem;
        color: var(--text-muted);
        margin: 0;
    }

    .amber { color: var(--amber); }

    /* =====================================================
       NAVIGATION
    ===================================================== */
    .navbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        z-index: 1000;
        padding: 0 24px;
        transition: var(--transition);
    }

    .navbar.scrolled {
        background: rgba(10, 15, 30, 0.95);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border);
        box-shadow: 0 4px 30px rgba(0,0,0,0.4);
    }

    .nav-inner {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 72px;
    }

    .nav-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .logo-icon {
        width: 38px;
        height: 38px;
        background: linear-gradient(135deg, var(--amber), var(--amber-dark));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .logo-text {
        font-family: var(--font-head);
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--white);
        line-height: 1.1;
    }

    .logo-text span {
        color: var(--amber);
        display: block;
        font-size: 0.65rem;
        font-weight: 400;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--text-muted);
    }

    .nav-links {
        display: flex;
        gap: 32px;
        list-style: none;
    }

    .nav-links a {
        text-decoration: none;
        color: var(--text-muted);
        font-size: 0.9rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .nav-links a:hover { color: var(--white); }

    .nav-cta {
        background: var(--amber);
        color: var(--navy) !important;
        padding: 10px 22px;
        border-radius: var(--radius-sm);
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        transition: var(--transition) !important;
    }

    .nav-cta:hover {
        background: var(--amber-light) !important;
        transform: translateY(-1px);
        box-shadow: var(--shadow-btn);
    }

    .hamburger {
        display: none;
        flex-direction: column;
        gap: 5px;
        cursor: pointer;
        padding: 4px;
        background: none;
        border: none;
    }

    .hamburger span {
        display: block;
        width: 24px;
        height: 2px;
        background: var(--white);
        border-radius: 2px;
        transition: var(--transition);
    }

    .mobile-menu {
        display: none;
        position: fixed;
        top: 72px; left: 0; right: 0;
        background: var(--navy-mid);
        border-top: 1px solid var(--border);
        padding: 24px;
        z-index: 999;
    }

    .mobile-menu.open { display: block; }
    .mobile-menu ul { list-style: none; }
    .mobile-menu li { border-bottom: 1px solid var(--border); }
    .mobile-menu a {
        display: block;
        padding: 14px 0;
        color: var(--text-body);
        text-decoration: none;
        font-weight: 500;
        font-size: 1rem;
    }

    /* =====================================================
       HERO SECTION
    ===================================================== */
    .hero {
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        padding: 120px 0 80px;
        background: var(--navy);
    }

    /* Grid background */
    .hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(245,158,11,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(245,158,11,0.04) 1px, transparent 1px);
        background-size: 60px 60px;
        pointer-events: none;
    }

    /* Radial glow */
    .hero::after {
        content: '';
        position: absolute;
        top: -20%;
        right: -10%;
        width: 700px;
        height: 700px;
        background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, transparent 65%);
        pointer-events: none;
    }

    .hero-grid {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(245,158,11,0.1);
        border: 1px solid rgba(245,158,11,0.2);
        color: var(--amber);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 24px;
        animation: fadeInUp 0.6s ease both;
    }

    .hero-badge::before {
        content: '';
        width: 8px;
        height: 8px;
        background: var(--amber);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.5; transform: scale(1.5); }
    }

    .hero h1 {
        animation: fadeInUp 0.7s ease 0.1s both;
        margin-bottom: 20px;
    }

    .hero-sub {
        font-size: 1.1rem;
        color: var(--text-muted);
        animation: fadeInUp 0.7s ease 0.2s both;
        margin-bottom: 36px;
        line-height: 1.7;
    }

    .hero-buttons {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        animation: fadeInUp 0.7s ease 0.3s both;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--amber);
        color: var(--navy);
        padding: 14px 28px;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        font-family: var(--font-body);
    }

    .btn-primary:hover {
        background: var(--amber-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-btn);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        color: var(--white);
        padding: 14px 28px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: var(--transition);
        font-family: var(--font-body);
    }

    .btn-secondary:hover {
        border-color: rgba(255,255,255,0.3);
        background: var(--card-bg);
        transform: translateY(-2px);
    }

    /* HERO FORM */
    .hero-form-wrap {
        animation: fadeInUp 0.8s ease 0.2s both;
    }

    .hero-form {
        background: var(--navy-mid);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 36px 32px;
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: hidden;
    }

    .hero-form::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--amber), var(--amber-light));
    }

    .form-title {
        font-family: var(--font-head);
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--white);
        margin-bottom: 6px;
    }

    .form-subtitle {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 16px;
        position: relative;
    }

    .form-group label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 8px;
        letter-spacing: 0.02em;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--white);
        font-size: 0.9rem;
        font-family: var(--font-body);
        transition: var(--transition);
        outline: none;
    }

    .form-control:focus {
        border-color: var(--amber);
        background: rgba(245,158,11,0.06);
        box-shadow: 0 0 0 3px rgba(245,158,11,0.1);
    }

    .form-control::placeholder { color: rgba(148,163,184,0.5); }

    textarea.form-control {
        resize: vertical;
        min-height: 90px;
    }

    .honeypot { display: none !important; }

    .form-submit {
        width: 100%;
        padding: 14px;
        background: var(--amber);
        color: var(--navy);
        border: none;
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        font-weight: 700;
        font-family: var(--font-body);
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .form-submit:hover {
        background: var(--amber-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-btn);
    }

    .form-trust {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-top: 14px;
    }

    .form-trust i { color: var(--amber); font-size: 0.7rem; }

    /* Form Success State */
    .form-success {
        display: none;
        text-align: center;
        padding: 40px 20px;
    }

    .form-success .success-icon {
        font-size: 3rem;
        margin-bottom: 16px;
    }

    .form-success h3 {
        font-size: 1.3rem;
        margin-bottom: 8px;
    }

    .form-success p {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    /* =====================================================
       STATS BAR
    ===================================================== */
    .stats-bar {
        background: var(--navy-mid);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 50px 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
    }

    .stat-item {
        text-align: center;
        padding: 20px;
        position: relative;
    }

    .stat-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 60px;
        width: 1px;
        background: var(--border);
    }

    .stat-number {
        font-family: var(--font-head);
        font-size: clamp(2.2rem, 4vw, 3.2rem);
        font-weight: 800;
        color: var(--amber);
        display: block;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* =====================================================
       WHAT WE DO — SERVICES GRID
    ===================================================== */
    .services-section {
        background: var(--navy);
        position: relative;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .service-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 28px;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        cursor: default;
    }

    .service-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(245,158,11,0.05), transparent);
        opacity: 0;
        transition: var(--transition);
    }

    .service-card:hover {
        border-color: rgba(245,158,11,0.3);
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.3);
    }

    .service-card:hover::before { opacity: 1; }

    .service-icon {
        font-size: 2rem;
        margin-bottom: 16px;
        display: block;
    }

    .service-card h3 {
        font-size: 1.05rem;
        margin-bottom: 10px;
        color: var(--white);
    }

    .service-card p {
        font-size: 0.88rem;
        color: var(--text-muted);
        line-height: 1.65;
        margin-bottom: 16px;
    }

    .service-bullets {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .service-bullets li {
        font-size: 0.82rem;
        color: var(--text-muted);
        display: flex;
        align-items: flex-start;
        gap: 8px;
        line-height: 1.4;
    }

    .service-bullets li::before {
        content: '→';
        color: var(--amber);
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 0.05em;
    }

    /* =====================================================
       CTA SECTION
    ===================================================== */
    .cta-section {
        background: linear-gradient(135deg, var(--navy-mid), var(--navy-light));
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 80px 0;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: 50%;
        transform: translateX(-50%);
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 65%);
        pointer-events: none;
    }

    .cta-section h2 { margin-bottom: 12px; }
    .cta-section p { color: var(--text-muted); margin-bottom: 36px; font-size: 1.05rem; }

    .cta-buttons {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* =====================================================
       DYNAMIC SECTION 4 & 5
    ===================================================== */

    /* Benefits Grid */
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .benefit-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 28px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
        transition: var(--transition);
    }

    .benefit-card:hover {
        border-color: rgba(245,158,11,0.25);
        transform: translateY(-2px);
    }

    .benefit-num {
        width: 40px;
        height: 40px;
        background: rgba(245,158,11,0.12);
        border: 1px solid rgba(245,158,11,0.2);
        color: var(--amber);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-family: var(--font-head);
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .benefit-body h4 {
        font-size: 1rem;
        margin-bottom: 6px;
        color: var(--white);
    }

    .benefit-body p {
        font-size: 0.87rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.6;
    }

    /* Process Steps */
    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0;
        position: relative;
    }

    .process-step {
        text-align: center;
        padding: 30px 20px;
        position: relative;
    }

    .process-step:not(:last-child)::after {
        content: '→';
        position: absolute;
        right: -12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--amber);
        font-size: 1.2rem;
        z-index: 2;
    }

    .step-num {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, var(--amber), var(--amber-dark));
        color: var(--navy);
        font-family: var(--font-head);
        font-weight: 800;
        font-size: 1.1rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
    }

    .process-step h4 {
        font-size: 1rem;
        margin-bottom: 8px;
        color: var(--white);
    }

    .process-step p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.6;
    }

    /* FAQ Accordion */
    .faq-list {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .faq-item {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        transition: var(--transition);
    }

    .faq-item.open { border-color: rgba(245,158,11,0.3); }

    .faq-question {
        width: 100%;
        background: none;
        border: none;
        padding: 22px 24px;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: var(--white);
        font-size: 1rem;
        font-weight: 600;
        font-family: var(--font-body);
        transition: var(--transition);
    }

    .faq-question:hover { color: var(--amber); }
    .faq-item.open .faq-question { color: var(--amber); }

    .faq-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--card-bg);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--amber);
        font-size: 0.8rem;
        transition: var(--transition);
    }

    .faq-item.open .faq-icon { transform: rotate(45deg); background: rgba(245,158,11,0.1); }

    .faq-answer {
        display: none;
        padding: 0 24px 22px;
        color: var(--text-muted);
        font-size: 0.92rem;
        line-height: 1.75;
        border-top: 1px solid var(--border);
        padding-top: 18px;
    }

    .faq-item.open .faq-answer { display: block; }

    /* =====================================================
       WHY TRUST SECTION
    ===================================================== */
    .trust-section {
        background: var(--navy-mid);
        border-top: 1px solid var(--border);
    }

    .trust-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .trust-content p {
        color: var(--text-muted);
        font-size: 1rem;
        line-height: 1.8;
        margin-bottom: 24px;
    }

    .trust-points {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .trust-points li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 0.92rem;
        color: var(--text-body);
    }

    .trust-check {
        width: 22px;
        height: 22px;
        background: rgba(245,158,11,0.12);
        border: 1px solid rgba(245,158,11,0.25);
        color: var(--amber);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* Testimonials in Trust */
    .testimonial-cards {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .testimonial-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 24px;
        transition: var(--transition);
    }

    .testimonial-card:hover { border-color: rgba(245,158,11,0.2); }

    .testimonial-stars {
        color: var(--amber);
        font-size: 0.85rem;
        margin-bottom: 10px;
        letter-spacing: 2px;
    }

    .testimonial-text {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.7;
        font-style: italic;
        margin-bottom: 16px;
    }

    .testimonial-result {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(245,158,11,0.1);
        color: var(--amber);
        padding: 4px 10px;
        border-radius: 50px;
        font-size: 0.78rem;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .author-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--amber), var(--amber-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-head);
        font-weight: 800;
        color: var(--navy);
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .author-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--white);
    }

    .author-role {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    /* =====================================================
       RELATED PAGES
    ===================================================== */
    .related-section { background: var(--navy); }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }

    .related-card {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px 18px;
        text-decoration: none;
        transition: var(--transition);
    }

    .related-card:hover {
        border-color: rgba(245,158,11,0.3);
        background: var(--card-hover);
        transform: translateY(-2px);
    }

    .related-card-icon { font-size: 1rem; flex-shrink: 0; }

    .related-card-text {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-body);
        line-height: 1.3;
    }

    .related-card:hover .related-card-text { color: var(--white); }

    /* =====================================================
       FOOTER
    ===================================================== */
    footer {
        background: var(--navy-mid);
        border-top: 1px solid var(--border);
        padding: 70px 0 30px;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: 1.8fr 1fr 1fr 1fr;
        gap: 48px;
        margin-bottom: 60px;
    }

    .footer-brand p {
        font-size: 0.88rem;
        color: var(--text-muted);
        margin: 16px 0 24px;
        line-height: 1.7;
    }

    .footer-social {
        display: flex;
        gap: 10px;
    }

    .social-btn {
        width: 38px;
        height: 38px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: var(--text-muted);
        font-size: 0.85rem;
        transition: var(--transition);
    }

    .social-btn:hover {
        background: rgba(245,158,11,0.1);
        border-color: rgba(245,158,11,0.3);
        color: var(--amber);
    }

    .footer-col h4 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--white);
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .footer-links {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .footer-links a {
        text-decoration: none;
        color: var(--text-muted);
        font-size: 0.88rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .footer-links a:hover { color: var(--amber); padding-left: 4px; }
    .footer-links a::before { content: '›'; color: var(--amber); font-size: 1rem; }

    .footer-bottom {
        border-top: 1px solid var(--border);
        padding-top: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .footer-bottom p {
        font-size: 0.82rem;
        color: var(--text-muted);
        margin: 0;
    }

    .footer-bottom-links {
        display: flex;
        gap: 20px;
        list-style: none;
    }

    .footer-bottom-links a {
        font-size: 0.82rem;
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
    }

    .footer-bottom-links a:hover { color: var(--amber); }

    /* =====================================================
       BREADCRUMB
    ===================================================== */
    .breadcrumb-bar {
        background: var(--navy-mid);
        border-bottom: 1px solid var(--border);
        padding: 12px 0;
        position: relative;
        z-index: 1;
        margin-top: 72px;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        list-style: none;
        flex-wrap: wrap;
    }

    .breadcrumb li {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: var(--text-muted);
    }

    .breadcrumb a {
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
    }

    .breadcrumb a:hover { color: var(--amber); }
    .breadcrumb li:last-child { color: var(--amber); }
    .breadcrumb li:not(:last-child)::after { content: '/'; opacity: 0.4; }

    /* =====================================================
       ANIMATIONS
    ===================================================== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* =====================================================
       FLOATING CTA BAR (mobile)
    ===================================================== */
    .sticky-cta {
        display: none;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: var(--navy-mid);
        border-top: 1px solid var(--border);
        padding: 14px 20px;
        z-index: 900;
        gap: 10px;
        backdrop-filter: blur(10px);
    }

    .sticky-cta .btn-primary, .sticky-cta .btn-secondary {
        flex: 1;
        justify-content: center;
        padding: 12px;
        font-size: 0.88rem;
    }

    /* =====================================================
       RESPONSIVE
    ===================================================== */
    @media (max-width: 1024px) {
        .footer-grid { grid-template-columns: 1.5fr 1fr 1fr; }
        .footer-col:last-child { grid-column: 2; }
    }

    @media (max-width: 768px) {
        .nav-links { display: none; }
        .hamburger { display: flex; }
        .section { padding: 60px 0; }

        .hero { padding: 100px 0 60px; min-height: auto; }
        .hero-grid { grid-template-columns: 1fr; gap: 40px; }
        .hero-form-wrap { order: -1; }
        .hero-buttons { flex-direction: column; }
        .btn-primary, .btn-secondary { justify-content: center; }

        .stats-grid { grid-template-columns: 1fr; gap: 20px; }
        .stat-item:not(:last-child)::after { display: none; }

        .trust-grid { grid-template-columns: 1fr; gap: 40px; }

        .process-steps { grid-template-columns: 1fr 1fr; }
        .process-step:not(:last-child)::after { display: none; }

        .footer-grid { grid-template-columns: 1fr 1fr; }

        .footer-bottom { flex-direction: column; text-align: center; }

        .sticky-cta { display: flex; }
        body { padding-bottom: 70px; }
    }

    @media (max-width: 480px) {
        .footer-grid { grid-template-columns: 1fr; gap: 32px; }
        .related-grid { grid-template-columns: 1fr; }
        .process-steps { grid-template-columns: 1fr; }
        h1 { font-size: 1.8rem; }
        .hero-form { padding: 24px 20px; }
    }
    </style>
</head>
<body>

<!-- =====================================================
     NAVIGATION
===================================================== -->
<nav class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="<?= SITE_URL ?>" class="nav-logo">
            <div class="logo-icon">🦅</div>
            <div class="logo-text">
                VG Digital Falcon
                <span>Digital Growth Agency</span>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="<?= SITE_URL ?>/seo-services">SEO Services</a></li>
            <li><a href="<?= SITE_URL ?>/about">About</a></li>
            <li><a href="<?= SITE_URL ?>/case-studies">Case Studies</a></li>
            <li><a href="<?= SITE_URL ?>/blog">Blog</a></li>
            <li><a href="#contact" class="nav-cta">Get Free Audit</a></li>
        </ul>

        <button class="hamburger" onclick="toggleMenu()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <ul>
        <li><a href="<?= SITE_URL ?>/seo-services">SEO Services</a></li>
        <li><a href="<?= SITE_URL ?>/about">About</a></li>
        <li><a href="<?= SITE_URL ?>/case-studies">Case Studies</a></li>
        <li><a href="<?= SITE_URL ?>/blog">Blog</a></li>
        <li><a href="#contact" class="nav-cta" style="color:var(--amber);font-weight:700;">Get Free Audit →</a></li>
    </ul>
</div>

<!-- =====================================================
     BREADCRUMB
===================================================== -->
<div class="breadcrumb-bar">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="<?= SITE_URL ?>">Home</a></li>
            <li><a href="<?= SITE_URL ?>/seo-services">SEO Services</a></li>
            <li><?= sanitize($page['target_keyword']) ?></li>
        </ol>
    </div>
</div>

<!-- =====================================================
     HERO SECTION
===================================================== -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-grid">

            <!-- Left: Headline + Buttons -->
            <div class="hero-left">
                <div class="hero-badge">
                    <i class="fas fa-star" style="font-size:0.65rem;"></i>
                    Rated 4.9/5 by 127+ clients
                </div>

                <h1><?= sanitize($page['hero_headline']) ?></h1>

                <p class="hero-sub"><?= sanitize($page['hero_subheadline']) ?></p>

                <div class="hero-buttons">
                    <a href="#contact" class="btn-primary">
                        <i class="fas fa-chart-line"></i>
                        <?= sanitize($page['hero_cta_primary']) ?>
                    </a>
                    <a href="#services" class="btn-secondary">
                        <i class="fas fa-play-circle"></i>
                        <?= sanitize($page['hero_cta_secondary']) ?>
                    </a>
                </div>
            </div>

            <!-- Right: Lead Capture Form -->
            <div class="hero-form-wrap" id="contact">
                <div class="hero-form">
                    <div class="form-body" id="formBody">
                        <div class="form-title">Get Your Free SEO Audit</div>
                        <div class="form-subtitle">Response guaranteed within 24 hours</div>

                        <?php
                        $formResult = handleLeadSubmit();
                        if ($formResult['success'] ?? false): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('formBody').style.display = 'none';
                                document.getElementById('formSuccess').style.display = 'block';
                            });
                        </script>
                        <?php endif; ?>

                        <form method="POST" action="#contact" id="leadForm" novalidate>
                            <input type="hidden" name="page_slug" value="<?= sanitize($page['slug']) ?>">
                            <!-- Honeypot -->
                            <div class="honeypot">
                                <input type="text" name="company_fax" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="name">Your Name *</label>
                                <input type="text" id="name" name="name" class="form-control"
                                       placeholder="John Smith" required
                                       value="<?= sanitize($_POST['name'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Work Email *</label>
                                <input type="email" id="email" name="email" class="form-control"
                                       placeholder="john@company.com" required
                                       value="<?= sanitize($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       placeholder="+91 98765 43210"
                                       value="<?= sanitize($_POST['phone'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="website">Website URL</label>
                                <input type="url" id="website" name="website" class="form-control"
                                       placeholder="https://yourwebsite.com"
                                       value="<?= sanitize($_POST['website'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label for="message">What's your biggest SEO challenge?</label>
                                <textarea id="message" name="message" class="form-control"
                                          placeholder="Tell us about your goals..."><?= sanitize($_POST['message'] ?? '') ?></textarea>
                            </div>

                            <?php if (!empty($formResult['error'])): ?>
                            <p style="color:#F87171;font-size:0.85rem;margin-bottom:12px;">
                                <?= sanitize($formResult['error']) ?>
                            </p>
                            <?php endif; ?>

                            <button type="submit" class="form-submit">
                                <i class="fas fa-rocket"></i>
                                Get My Free SEO Audit
                            </button>

                            <div class="form-trust">
                                <i class="fas fa-lock"></i>
                                100% Free · No spam · No commitment
                            </div>
                        </form>
                    </div>

                    <div class="form-success" id="formSuccess">
                        <div class="success-icon">🎉</div>
                        <h3>Audit Request Received!</h3>
                        <p>Our SEO experts will review your website and get back to you within 24 hours with a detailed audit.</p>
                        <br>
                        <p style="color:var(--amber);font-weight:600;">Check your email for confirmation.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- =====================================================
     STATS BAR — Experience / Projects / Keywords
===================================================== -->
<section class="stats-bar">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item reveal">
                <span class="stat-number counter" data-target="<?= (int)filter_var($page['stat_experience'], FILTER_SANITIZE_NUMBER_INT) ?>"><?= sanitize($page['stat_experience']) ?></span>
                <div class="stat-label">Years of SEO Experience</div>
            </div>
            <div class="stat-item reveal">
                <span class="stat-number counter" data-target="<?= (int)filter_var($page['stat_projects'], FILTER_SANITIZE_NUMBER_INT) ?>"><?= sanitize($page['stat_projects']) ?></span>
                <div class="stat-label">Projects Delivered</div>
            </div>
            <div class="stat-item reveal">
                <span class="stat-number"><?= sanitize($page['stat_keywords']) ?></span>
                <div class="stat-label">Keywords Ranked on Page 1</div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     WHAT WE DO — SEO SERVICES
===================================================== -->
<section class="services-section section" id="services">
    <div class="container">
        <div class="section-header reveal">
            <div class="label-chip">⚡ What We Do</div>
            <h2>Complete SEO Services for <span class="amber"><?= sanitize(getPageLabel($page) ?: 'Your Business') ?></span></h2>
            <p>A full-spectrum SEO approach — every element working together to drive rankings, traffic, and revenue for your business.</p>
        </div>

        <div class="services-grid">
            <?php foreach ($services as $i => $svc):
                $bullets = json_decode($svc['bullets'] ?? '[]', true);
            ?>
            <div class="service-card reveal" style="transition-delay: <?= ($i % 4) * 80 ?>ms">
                <span class="service-icon"><?= $svc['icon'] ?></span>
                <h3><?= sanitize($svc['title']) ?></h3>
                <p><?= sanitize($svc['short_description']) ?></p>
                <?php if ($bullets): ?>
                <ul class="service-bullets">
                    <?php foreach (array_slice($bullets, 0, 4) as $b): ?>
                    <li><?= sanitize($b) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =====================================================
     CTA SECTION 1
===================================================== -->
<section class="cta-section">
    <div class="container" style="position:relative;z-index:2;">
        <div class="label-chip" style="justify-content:center;">🚀 Start Today</div>
        <h2><?= sanitize($page['cta1_headline'] ?? 'Ready to Rank Higher on Google?') ?></h2>
        <p><?= sanitize($page['cta1_subtext'] ?? 'Get a free, no-obligation SEO audit from our expert team.') ?></p>
        <div class="cta-buttons">
            <a href="#contact" class="btn-primary">
                <i class="fas fa-chart-line"></i>
                Get Free SEO Audit
            </a>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', SITE_PHONE) ?>" class="btn-secondary">
                <i class="fas fa-phone"></i>
                <?= SITE_PHONE ?>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     SECTION 4 — Dynamic (Process / Benefits / FAQ)
===================================================== -->
<?php
$s4Data = json_decode($page['section4_content'] ?? '[]', true);
if ($s4Data):
?>
<section class="section" style="background: var(--navy-mid); border-top: 1px solid var(--border);">
    <div class="container">
        <div class="section-header reveal">
            <div class="label-chip">📋 Our Approach</div>
            <h2><?= sanitize($page['section4_title'] ?? 'How We Deliver Results') ?></h2>
        </div>

        <?php if ($page['section4_type'] === 'process'): ?>
        <div class="process-steps">
            <?php foreach ($s4Data as $step): ?>
            <div class="process-step reveal">
                <div class="step-num"><?= sanitize($step['step']) ?></div>
                <h4><?= sanitize($step['title']) ?></h4>
                <p><?= sanitize($step['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($page['section4_type'] === 'benefits'): ?>
        <div class="benefits-grid">
            <?php foreach ($s4Data as $i => $b): ?>
            <div class="benefit-card reveal">
                <div class="benefit-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></div>
                <div class="benefit-body">
                    <h4><?= sanitize($b['benefit']) ?></h4>
                    <p><?= sanitize($b['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- =====================================================
     SECTION 5 — Dynamic (FAQ / Process / Testimonials)
===================================================== -->
<?php
$s5Data = json_decode($page['section5_content'] ?? '[]', true);
if ($s5Data):
?>
<section class="section" style="background: var(--navy);">
    <div class="container">
        <div class="section-header reveal">
            <?php if ($page['section5_type'] === 'faq'): ?>
            <div class="label-chip">❓ FAQ</div>
            <?php else: ?>
            <div class="label-chip">⭐ Results</div>
            <?php endif; ?>
            <h2><?= sanitize($page['section5_title'] ?? 'Frequently Asked Questions') ?></h2>
        </div>

        <?php if ($page['section5_type'] === 'faq'): ?>
        <div class="faq-list">
            <?php foreach ($s5Data as $i => $faq): ?>
            <div class="faq-item reveal" id="faq-<?= $i ?>">
                <button class="faq-question" onclick="toggleFaq(<?= $i ?>)" aria-expanded="false">
                    <span><?= sanitize($faq['q']) ?></span>
                    <span class="faq-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer">
                    <?= sanitize($faq['a']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- =====================================================
     CTA SECTION 2
===================================================== -->
<section class="cta-section" style="background: linear-gradient(135deg, #0F1730, #1A2545);">
    <div class="container" style="position:relative;z-index:2;">
        <div class="label-chip" style="justify-content:center;">💡 Free Consultation</div>
        <h2><?= sanitize($page['cta2_headline'] ?? 'Stop Losing Customers to Your Competitors') ?></h2>
        <p><?= sanitize($page['cta2_subtext'] ?? 'Every day without SEO is revenue you are leaving on the table.') ?></p>
        <div class="cta-buttons">
            <a href="#contact" class="btn-primary">
                <i class="fas fa-rocket"></i>
                Start My SEO Journey
            </a>
            <a href="mailto:<?= SITE_EMAIL ?>" class="btn-secondary">
                <i class="fas fa-envelope"></i>
                Email Us
            </a>
        </div>
    </div>
</section>

<!-- =====================================================
     WHY TRUST VG DIGITAL FALCON (Section 6)
===================================================== -->
<section class="trust-section section">
    <div class="container">
        <div class="trust-grid">
            <!-- Left: Trust Content -->
            <div class="trust-content reveal">
                <div class="label-chip">🦅 Why VG Digital Falcon</div>
                <h2>Why Businesses in <span class="amber"><?= sanitize(getPageLabel($page) ?: 'India') ?></span> Trust Us for SEO</h2>

                <p><?= nl2br(sanitize($page['why_trust_content'] ?? '')) ?></p>

                <?php if (!empty($page['local_context'])): ?>
                <p><?= nl2br(sanitize($page['local_context'])) ?></p>
                <?php endif; ?>

                <ul class="trust-points">
                    <li>
                        <span class="trust-check"><i class="fas fa-check"></i></span>
                        10+ years of proven SEO expertise in competitive Indian markets
                    </li>
                    <li>
                        <span class="trust-check"><i class="fas fa-check"></i></span>
                        500+ businesses ranked on page one of Google
                    </li>
                    <li>
                        <span class="trust-check"><i class="fas fa-check"></i></span>
                        Transparent monthly reporting — no vanity metrics, only real ROI
                    </li>
                    <li>
                        <span class="trust-check"><i class="fas fa-check"></i></span>
                        White-hat, Google-compliant strategies — no shortcuts, no penalties
                    </li>
                    <li>
                        <span class="trust-check"><i class="fas fa-check"></i></span>
                        Dedicated account manager for every client, every month
                    </li>
                    <li>
                        <span class="trust-check"><i class="fas fa-check"></i></span>
                        Flexible month-to-month engagements — no lock-in contracts
                    </li>
                </ul>
            </div>

            <!-- Right: Testimonials -->
            <div class="testimonial-cards reveal">
                <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"<?= sanitize($t['testimonial_text']) ?>"</p>
                    <?php if ($t['result_highlight']): ?>
                    <div class="testimonial-result">
                        <i class="fas fa-chart-line" style="font-size:0.7rem;"></i>
                        <?= sanitize($t['result_highlight']) ?>
                    </div>
                    <?php endif; ?>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <?= mb_substr($t['client_name'], 0, 1) ?>
                        </div>
                        <div>
                            <div class="author-name"><?= sanitize($t['client_name']) ?></div>
                            <div class="author-role"><?= sanitize($t['client_designation']) ?>, <?= sanitize($t['client_company']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     RELATED PAGES
===================================================== -->
<?php if ($relatedPages): ?>
<section class="related-section section-sm">
    <div class="container">
        <div class="section-header reveal">
            <div class="label-chip">🔗 Explore More</div>
            <h2>More SEO Services by VG Digital Falcon</h2>
            <p>Specialized SEO solutions for every city, industry, and business type across India.</p>
        </div>

        <div class="related-grid">
            <?php foreach ($relatedPages as $rp): ?>
            <a href="<?= SITE_URL ?>/<?= sanitize($rp['slug']) ?>" class="related-card reveal">
                <span class="related-card-icon"><?= getSectionIcon($rp['page_type']) ?></span>
                <span class="related-card-text"><?= sanitize($rp['target_keyword']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =====================================================
     FOOTER
===================================================== -->
<footer>
    <div class="container">
        <div class="footer-grid">
            <!-- Brand -->
            <div class="footer-brand">
                <a href="<?= SITE_URL ?>" class="nav-logo" style="margin-bottom:0;">
                    <div class="logo-icon">🦅</div>
                    <div class="logo-text" style="font-size:1rem;">
                        VG Digital Falcon
                        <span>Digital Growth Agency</span>
                    </div>
                </a>
                <p>India's trusted SEO agency helping businesses rank higher on Google, drive organic traffic, and grow revenue through data-driven search strategies.</p>
                <div class="footer-social">
                    <a href="#" class="social-btn" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-btn" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="social-btn" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- SEO Services -->
            <div class="footer-col">
                <h4>SEO Services</h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/on-page-seo">On-Page SEO</a></li>
                    <li><a href="<?= SITE_URL ?>/off-page-seo">Off-Page SEO</a></li>
                    <li><a href="<?= SITE_URL ?>/technical-seo">Technical SEO</a></li>
                    <li><a href="<?= SITE_URL ?>/local-seo">Local SEO</a></li>
                    <li><a href="<?= SITE_URL ?>/content-seo">Content SEO</a></li>
                    <li><a href="<?= SITE_URL ?>/ecommerce-seo">E-Commerce SEO</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-audit">SEO Audit</a></li>
                </ul>
            </div>

            <!-- Cities -->
            <div class="footer-col">
                <h4>Cities We Serve</h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/seo-services-chennai">SEO in Chennai</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-services-mumbai">SEO in Mumbai</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-services-bangalore">SEO in Bangalore</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-services-delhi">SEO in Delhi</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-services-hyderabad">SEO in Hyderabad</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-services-pune">SEO in Pune</a></li>
                    <li><a href="<?= SITE_URL ?>/seo-services-coimbatore">SEO in Coimbatore</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="footer-col">
                <h4>Company</h4>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/about">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/case-studies">Case Studies</a></li>
                    <li><a href="<?= SITE_URL ?>/blog">SEO Blog</a></li>
                    <li><a href="<?= SITE_URL ?>/careers">Careers</a></li>
                    <li><a href="<?= SITE_URL ?>/contact">Contact</a></li>
                </ul>
                <div style="margin-top:24px; display:flex; flex-direction:column; gap:10px;">
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', SITE_PHONE) ?>"
                       style="color:var(--text-muted);text-decoration:none;font-size:0.88rem;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-phone" style="color:var(--amber);font-size:0.8rem;"></i>
                        <?= SITE_PHONE ?>
                    </a>
                    <a href="mailto:<?= SITE_EMAIL ?>"
                       style="color:var(--text-muted);text-decoration:none;font-size:0.88rem;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-envelope" style="color:var(--amber);font-size:0.8rem;"></i>
                        <?= SITE_EMAIL ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> VG Digital Falcon. All rights reserved. Built with ❤️ for results.</p>
            <ul class="footer-bottom-links">
                <li><a href="<?= SITE_URL ?>/privacy-policy">Privacy Policy</a></li>
                <li><a href="<?= SITE_URL ?>/terms">Terms of Service</a></li>
                <li><a href="<?= SITE_URL ?>/sitemap.xml">Sitemap</a></li>
            </ul>
        </div>
    </div>
</footer>

<!-- Mobile Sticky CTA -->
<div class="sticky-cta">
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', SITE_PHONE) ?>" class="btn-secondary">
        <i class="fas fa-phone"></i> Call
    </a>
    <a href="#contact" class="btn-primary">
        <i class="fas fa-rocket"></i> Free Audit
    </a>
</div>

<!-- =====================================================
     JavaScript
===================================================== -->
<script>
// Navbar scroll behavior
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 20);
});

// Mobile menu
function toggleMenu() {
    document.getElementById('mobileMenu').classList.toggle('open');
}

// Close menu on link click
document.querySelectorAll('.mobile-menu a').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('mobileMenu').classList.remove('open');
    });
});

// FAQ accordion
function toggleFaq(id) {
    const item = document.getElementById('faq-' + id);
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}

// Scroll reveal
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// Client-side form validation
document.getElementById('leadForm')?.addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!name || !email || !emailRe.test(email)) {
        e.preventDefault();
        alert('Please enter a valid name and email address.');
        return;
    }

    const btn = this.querySelector('.form-submit');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

</body>
</html>
