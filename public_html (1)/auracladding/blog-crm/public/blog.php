<?php
/**
 * Public Blog Listing — Aura theme
 * Clean URL: /blog
 */
require_once __DIR__ . '/../config.php';
auto_publish_scheduled_posts();
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = (int)get_setting('posts_per_page', 9);
$offset = ($page - 1) * $per_page;
$sort = $_GET['sort'] ?? 'latest';
$order = $sort === 'oldest' ? 'ASC' : 'DESC';
$total = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'published'
    ORDER BY p.is_featured DESC, p.published_at $order
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
$site_url   = rtrim(get_setting('site_url', SITE_URL), '/');
$site_name  = get_setting('site_name', SITE_NAME);
$blog_title = get_setting('blog_title', 'Our Blog. Insights. Ideas. Inspiration.');
$blog_desc  = get_setting('blog_description', 'Stay updated with the latest trends, expert insights and guides on cladding, glazing and modern architecture.');
$logo_url   = $site_url . '/image/aura-logo.png';
$banner_url = $site_url . '/image/glazing-banner.png';
$favicon_url = $site_url . '/image/favicon.png';
$quote_url = $site_url . '/send-quote.php';
$showing_from = $total > 0 ? $offset + 1 : 0;
$showing_to   = min($offset + $per_page, $total);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title><?= e(get_setting('default_meta_title', $blog_title . ' | ' . $site_name)) ?></title>
<link rel="icon" type="image/png" href="<?= e($favicon_url) ?>">
<link rel="shortcut icon" type="image/png" href="<?= e($favicon_url) ?>">
<link rel="apple-touch-icon" href="<?= e($favicon_url) ?>">
<meta name="description" content="<?= e(get_setting('default_meta_description', $blog_desc)) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<link rel="canonical" href="<?= e(blog_list_url()) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($blog_title) ?>">
<meta property="og:description" content="<?= e($blog_desc) ?>">
<meta property="og:url" content="<?= e(blog_list_url()) ?>">
<meta property="og:site_name" content="<?= e($site_name) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($blog_title) ?>">
<meta name="twitter:description" content="<?= e($blog_desc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary-dark: #051329;
    --primary-light: #0a1f3f;
    --accent-orange: #f37021;
    --accent-hover: #d95e1a;
    --text-main: #333333;
    --text-light: #666666;
    --bg-light: #f8f9fa;
    --white: #ffffff;
    --border-color: #e5e5e5;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1E293B;
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.05);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.08);
    --transition: all 0.3s ease;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
html, body { max-width: 100%; overflow-x: hidden; }
body { font-family: 'Inter', 'Poppins', system-ui, -apple-system, sans-serif; color: var(--text-main); background: var(--white); line-height: 1.6; }
a { text-decoration: none; color: inherit; transition: var(--transition); }
img { max-width: 100%; display: block; }
ul { list-style: none; }
.container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.navbar { background-color: var(--primary-dark); color: var(--white); padding: 10px 0; position: sticky; top: 0; z-index: 1000; }
.nav-container { display: flex; justify-content: space-between; align-items: center; }
.logo { display: flex; align-items: center; flex-shrink: 0; }
.logo img { height: 68px; max-height: 68px; width: auto; object-fit: contain; }
.nav-links { display: flex; gap: 28px; align-items: center; }
.nav-links > li { position: relative; }
.nav-links > li > a { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.9); display: flex; align-items: center; gap: 5px; position: relative; }
.nav-links > li > a:hover, .nav-links > li > a.active { color: var(--accent-orange); }
.nav-links > li > a.active::after { content: ''; position: absolute; bottom: -8px; left: 0; width: 100%; height: 2px; background: var(--accent-orange); border-radius: 2px; }
.dropdown-menu { visibility: hidden; opacity: 0; pointer-events: none; position: absolute; top: 100%; left: 0; background: transparent; min-width: 240px; box-shadow: none; border-radius: 6px; padding: 14px 0 0; z-index: 1001; margin-top: 0; transition: opacity 0.18s ease, visibility 0.18s ease; }
.dropdown-menu::before { content: ''; position: absolute; top: 14px; left: 0; right: 0; bottom: 0; background: var(--white); border-radius: 6px; box-shadow: var(--shadow-md); z-index: -1; }
.nav-links li.has-dropdown:hover > .dropdown-menu,
.nav-links li.has-dropdown:focus-within > .dropdown-menu,
.nav-links li.has-dropdown.dropdown-open > .dropdown-menu { visibility: visible; opacity: 1; pointer-events: auto; }
.dropdown-menu li a { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: var(--text-main); font-size: 14px; font-weight: 500; }
.dropdown-menu li a i { color: var(--accent-orange); width: 18px; text-align: center; font-size: 15px; }
.dropdown-menu li a:hover { background: #fff5ef; color: var(--accent-orange); }
.btn-quote { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: var(--accent-orange); color: var(--white); font-weight: 600; border-radius: 4px; font-size: 14px; box-shadow: 0 4px 15px rgba(243,112,33,0.25); cursor: pointer; }
.btn-quote:hover { background: var(--accent-hover); color: #fff; transform: translateY(-1px); }
.menu-toggle { display: none; font-size: 24px; cursor: pointer; color: var(--white); background: none; border: none; padding: 4px 8px; }
.blog-hero { position: relative; min-height: 460px; display: flex; align-items: center; background: linear-gradient(90deg, rgba(5,19,41,0.92) 0%, rgba(5,19,41,0.75) 45%, rgba(5,19,41,0.4) 100%), url('<?= e($banner_url) ?>') center center / cover no-repeat; color: var(--white); padding: 80px 0 70px; }
.blog-hero-content { max-width: 560px; position: relative; z-index: 1; }
.breadcrumb { font-size: 14px; color: rgba(255,255,255,0.7); margin-bottom: 20px; }
.breadcrumb a { color: rgba(255,255,255,0.7); }
.breadcrumb a:hover { color: var(--accent-orange); }
.breadcrumb span { color: var(--accent-orange); }
.blog-hero h1 { font-size: 42px; font-weight: 700; line-height: 1.25; margin-bottom: 20px; }
.blog-hero h1 span { color: var(--accent-orange); }
.blog-hero p { font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 30px; line-height: 1.7; max-width: 480px; }
.btn-explore { display: inline-flex; align-items: center; gap: 10px; border: 1.5px solid rgba(255,255,255,0.45); color: white; padding: 12px 24px; border-radius: 4px; font-weight: 600; font-size: 14px; }
.btn-explore:hover { background: var(--accent-orange); border-color: var(--accent-orange); color: #fff; }
.articles-section { padding: 70px 0 80px; background: #fff; }
.section-header { text-align: center; margin-bottom: 40px; }
.section-label { font-size: 12px; font-weight: 600; letter-spacing: 1.5px; color: var(--accent-orange); margin-bottom: 8px; text-transform: uppercase; }
.section-header h2 { font-size: 32px; font-weight: 700; color: var(--primary-dark); margin-bottom: 12px; }
.section-line { width: 50px; height: 3px; background: var(--accent-orange); margin: 0 auto; border-radius: 2px; }
.listing-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; font-size: 14px; color: var(--gray-500); flex-wrap: wrap; gap: 12px; }
.sort-select a { color: var(--gray-500); margin: 0 6px; }
.sort-select a.active, .sort-select a:hover { color: var(--accent-orange); font-weight: 600; }
.posts-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
.post-card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid var(--gray-100); box-shadow: 0 4px 20px rgba(5,19,41,0.06); transition: 0.25s; }
.post-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(5,19,41,0.12); }
.post-card.featured { border-color: rgba(243,112,33,0.35); }
.card-image { position: relative; display: block; height: 200px; overflow: hidden; }
.card-image img { width: 100%; height: 100%; object-fit: cover; transition: 0.4s; }
.post-card:hover .card-image img { transform: scale(1.05); }
.cat-badge { position: absolute; top: 14px; left: 14px; background: var(--primary-dark); color: white; font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 6px; }
.card-body { padding: 20px 22px 24px; }
.card-meta { display: flex; gap: 16px; font-size: 12px; color: var(--gray-400); margin-bottom: 10px; }
.card-meta i { margin-right: 4px; color: var(--accent-orange); }
.card-body h3 { font-size: 17px; font-weight: 700; line-height: 1.4; margin-bottom: 10px; }
.card-body h3 a { color: var(--primary-dark); }
.card-body h3 a:hover { color: var(--accent-orange); }
.card-body p { font-size: 14px; color: var(--gray-500); line-height: 1.6; margin-bottom: 16px; }
.read-more { font-size: 14px; font-weight: 600; color: var(--accent-orange); display: inline-flex; align-items: center; gap: 6px; }
.read-more:hover { gap: 10px; }
.pagination { display: flex; justify-content: center; gap: 8px; margin-top: 48px; flex-wrap: wrap; }
.pagination a, .pagination span { min-width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px; font-weight: 500; border: 1px solid var(--gray-200); color: var(--gray-600); padding: 0 10px; }
.pagination a:hover { background: var(--primary-dark); color: white; border-color: var(--primary-dark); }
.pagination .current { background: var(--accent-orange); color: #fff; border-color: var(--accent-orange); font-weight: 700; }
.pagination .dots { border: none; }
.empty-blog { text-align: center; padding: 80px 20px; color: var(--gray-400); }
.empty-blog i { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
.cta-banner { background: linear-gradient(135deg, var(--primary-dark), var(--primary-light)); padding: 60px 0; color: #fff; }
.cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 40px; }
.cta-text h2 { font-size: 28px; font-weight: 700; line-height: 1.3; margin-bottom: 10px; }
.cta-text p { color: rgba(255,255,255,0.7); font-size: 15px; }
.cta-actions { display: flex; gap: 14px; flex-shrink: 0; flex-wrap: wrap; }
.btn-primary { background: var(--accent-orange); color: #fff; padding: 14px 24px; border-radius: 4px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.btn-primary:hover { background: var(--accent-hover); color: #fff; }
.btn-outline-light { border: 1.5px solid rgba(255,255,255,0.4); color: #fff; padding: 14px 24px; border-radius: 4px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
.btn-outline-light:hover { background: rgba(255,255,255,0.1); }
.footer { background: var(--primary-dark); color: rgba(255,255,255,0.7); padding-top: 60px; }
.footer-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.2fr 1fr; gap: 32px; padding-bottom: 50px; }
.footer-logo { margin-bottom: 16px; }
.footer-logo img { height: 64px; max-height: 64px; width: auto; object-fit: contain; }
.footer-col p { font-size: 13px; line-height: 1.7; margin-bottom: 18px; text-align: left; }
.social-icons { display: flex; gap: 10px; }
.social-icons a { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: white; font-size: 13px; }
.social-icons a:hover { background: var(--accent-orange); color: #fff; }
.footer-col h4 { color: white; font-size: 13px; font-weight: 700; letter-spacing: 0.8px; margin-bottom: 18px; text-transform: uppercase; }
.footer-links li { margin-bottom: 10px; }
.footer-links a, .contact-info li { font-size: 13px; color: rgba(255,255,255,0.65); }
.footer-links a:hover { color: var(--accent-orange); }
.contact-info li { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
.contact-info i { color: var(--accent-orange); margin-top: 3px; width: 16px; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding: 18px 0; font-size: 12px; }
.footer-bottom .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.footer-bottom-links a { color: rgba(255,255,255,0.5); margin-left: 8px; }
.footer-bottom-links a:hover { color: var(--accent-orange); }
.whatsapp-float { position: fixed; right: 22px; bottom: 22px; width: 58px; height: 58px; border-radius: 50%; background: #25D366; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 8px 22px rgba(37,211,102,0.45); z-index: 1400; transition: var(--transition); }
.whatsapp-float:hover { transform: translateY(-3px) scale(1.06); background: #1ebe57; color: #fff; }
.quote-modal-overlay { position: fixed; inset: 0; background: rgba(5,19,41,0.72); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 18px; }
.quote-modal-overlay.active { display: flex; }
.quote-modal { background: #fff; width: 100%; max-width: 540px; border-radius: 12px; padding: 32px 28px 26px; position: relative; box-shadow: var(--shadow-md); max-height: 92vh; overflow-y: auto; }
.quote-modal h3 { color: var(--primary-dark); font-size: 24px; margin-bottom: 6px; }
.quote-modal .modal-sub { color: var(--text-light); font-size: 14px; margin-bottom: 22px; }
.quote-close { position: absolute; top: 12px; right: 14px; width: 34px; height: 34px; border: none; background: var(--bg-light); border-radius: 50%; cursor: pointer; font-size: 18px; color: var(--text-main); }
.quote-close:hover { background: #eee; color: var(--accent-orange); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--primary-dark); margin-bottom: 6px; }
.form-group input, .form-group select { width: 100%; height: 46px; border: 1px solid var(--border-color); border-radius: 6px; padding: 0 14px; font-family: 'Poppins', sans-serif; font-size: 14px; color: var(--text-main); background: #fff; outline: none; }
.form-group input:focus, .form-group select:focus { border-color: var(--accent-orange); box-shadow: 0 0 0 3px rgba(243,112,33,0.12); }
.quote-modal .btn { width: 100%; text-align: center; margin-top: 6px; justify-content: center; display: inline-flex; align-items: center; background: var(--accent-orange); color: #fff; padding: 12px 20px; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; }
.form-status { display: none; margin-top: 12px; font-size: 13px; text-align: center; padding: 10px 12px; border-radius: 6px; }
.form-status.success { display: block; background: #e8f8ef; color: #147a3f; }
.form-status.error { display: block; background: #fdecea; color: #b42318; }
body.modal-open { overflow: hidden; }
@media (max-width: 992px) {
    .menu-toggle { display: block; }
    .nav-links { display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--primary-dark); flex-direction: column; align-items: flex-start; padding: 16px 20px 24px; gap: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.08); }
    .nav-links.active { display: flex; }
    .nav-links > li { width: 100%; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .nav-links > li > a { padding: 14px 0; width: 100%; }
    .nav-links > li > a.active::after { display: none; }
    .dropdown-menu { position: static; visibility: hidden; opacity: 0; pointer-events: none; height: 0; overflow: hidden; box-shadow: none; background: rgba(0,0,0,0.2); margin: 0 0 8px; border-radius: 6px; min-width: 100%; padding: 0; }
    .dropdown-menu::before { display: none; }
    .nav-links li.has-dropdown.dropdown-open > .dropdown-menu { visibility: visible; opacity: 1; pointer-events: auto; height: auto; overflow: visible; padding: 5px 0; display: block; }
    .dropdown-menu li a { color: rgba(255,255,255,0.85); padding: 10px 16px; }
    .dropdown-menu li a:hover { background: rgba(243,112,33,0.15); color: var(--accent-orange); }
    .btn-quote { display: none; }
    .posts-grid { grid-template-columns: repeat(2, 1fr); }
    .footer-grid { grid-template-columns: 1fr 1fr; }
    .cta-inner { flex-direction: column; text-align: center; }
    .cta-actions { justify-content: center; }
    .blog-hero h1 { font-size: 34px; }
}
@media (max-width: 600px) {
    .posts-grid { grid-template-columns: 1fr; }
    .blog-hero { min-height: 380px; padding: 60px 0 50px; }
    .blog-hero h1 { font-size: 28px; }
    .section-header h2 { font-size: 26px; }
    .footer-grid { grid-template-columns: 1fr; }
    .footer-bottom .container { flex-direction: column; text-align: center; }
.logo img { height: 84px; max-height: 96px; width: auto; }
.listing-meta { flex-direction: column; align-items: flex-start; }
    .whatsapp-float { width: 52px; height: 52px; font-size: 26px; right: 16px; bottom: 16px; }
    .quote-modal { padding: 26px 16px 20px; }
    .form-row { grid-template-columns: 1fr 1fr; gap: 10px; }
}
</style>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Blog",
    "name": "<?= e($blog_title) ?>",
    "description": "<?= e($blog_desc) ?>",
    "url": "<?= e(blog_list_url()) ?>",
    "publisher": {
        "@type": "Organization",
        "name": "<?= e($site_name) ?>",
        "url": "<?= e($site_url) ?>"
    }
}
</script>
</head>
<body>
<header class="navbar">
    <div class="container nav-container">
        <a href="<?= e($site_url) ?>" class="logo">
            <img src="<?= e($logo_url) ?>" alt="<?= e($site_name) ?>" onerror="this.style.display='none';this.insertAdjacentHTML('afterend','<strong style=color:#fff;font-size:18px>Aura</strong>');">
        </a>
        <button type="button" class="menu-toggle" aria-label="Menu" aria-expanded="false"><i class="fas fa-bars"></i></button>
        <ul class="nav-links">
            <li><a href="<?= e($site_url) ?>">Home</a></li>
            <li><a href="<?= e($site_url) ?>/about.html">About Us</a></li>
            <li class="has-dropdown">
                <a href="<?= e($site_url) ?>/services.html" class="services-toggle">Services <i class="fas fa-chevron-down" style="font-size:10px;"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e($site_url) ?>/acp-cladding.html"><i class="fas fa-layer-group"></i> ACP Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/structural-glazing.html"><i class="fas fa-building"></i> Structural Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/exterior-glazing.html"><i class="fas fa-home"></i> Exterior Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/tuffen-glass.html"><i class="fas fa-shield-alt"></i> Tuffen Glass</a></li>
                    <li><a href="<?= e($site_url) ?>/ms-fabrication.html"><i class="fas fa-industry"></i> MS Fabrication</a></li>
                </ul>
            </li>
            <li><a href="<?= e(works_list_url()) ?>">Our Works</a></li>
            <li><a href="<?= e(blog_list_url()) ?>" class="active">Blog</a></li>
            <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
        </ul>
        <a href="#quote" class="btn-quote open-quote-modal">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
    </div>
</header>
<section class="blog-hero">
    <div class="container">
        <div class="blog-hero-content">
            <div class="breadcrumb"><a href="<?= e($site_url) ?>">Home</a> &nbsp;/&nbsp; <span>Blog</span></div>
            <h1>Our Blog.<br><span>Insights. Ideas. Inspiration.</span></h1>
            <p><?= e($blog_desc) ?></p>
            <a href="#articles" class="btn-explore">Explore Our Insights <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<section class="articles-section" id="articles">
    <div class="container">
        <div class="section-header">
            <div class="section-label">ALL ARTICLES</div>
            <h2>Stories That Build Better Spaces</h2>
            <div class="section-line"></div>
        </div>
        <div class="listing-meta">
            <span>Showing <?= (int)$showing_from ?>–<?= (int)$showing_to ?> of <?= (int)$total ?> articles</span>
            <div class="sort-select">
                <a href="?sort=latest<?= $page > 1 ? '&page=' . $page : '' ?>" class="<?= $sort === 'latest' ? 'active' : '' ?>">Latest First</a>
                <span>|</span>
                <a href="?sort=oldest<?= $page > 1 ? '&page=' . $page : '' ?>" class="<?= $sort === 'oldest' ? 'active' : '' ?>">Oldest First</a>
            </div>
        </div>
        <?php if (empty($posts)): ?>
        <div class="empty-blog">
            <i class="fas fa-newspaper"></i>
            <p>No articles published yet. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post):
                $img = $post['featured_image'] ? upload_url($post['featured_image']) : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&h=400&fit=crop';
                $date = $post['published_at'] ? date('d M Y', strtotime($post['published_at'])) : date('d M Y', strtotime($post['created_at']));
                $cat = $post['category_name'] ?? 'Article';
            ?>
            <article class="post-card<?= !empty($post['is_featured']) ? ' featured' : '' ?>">
                <a href="<?= e(post_url($post['slug'])) ?>" class="card-image">
                    <img src="<?= e($img) ?>" alt="<?= e($post['featured_image_alt'] ?: $post['title']) ?>" loading="lazy">
                    <span class="cat-badge"><?= e($cat) ?></span>
                </a>
                <div class="card-body">
                    <div class="card-meta">
                        <span><i class="far fa-calendar"></i> <?= e($date) ?></span>
                        <span><i class="far fa-clock"></i> <?= (int)$post['reading_time'] ?> min read</span>
                    </div>
                    <h3><a href="<?= e(post_url($post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
                    <p><?= e(mb_strimwidth(strip_tags($post['excerpt'] ?: $post['ai_summary'] ?: $post['content']), 0, 110, '...')) ?></p>
                    <a href="<?= e(post_url($post['slug'])) ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&sort=<?= e($sort) ?>">← Prev</a>
            <?php endif; ?>
            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            if ($start > 1) echo '<a href="?page=1&sort=' . e($sort) . '">1</a>';
            if ($start > 2) echo '<span class="dots">...</span>';
            for ($i = $start; $i <= $end; $i++):
                if ($i == $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&sort=<?= e($sort) ?>"><?= $i ?></a>
                <?php endif;
            endfor;
            if ($end < $total_pages - 1) echo '<span class="dots">...</span>';
            if ($end < $total_pages) echo '<a href="?page=' . $total_pages . '&sort=' . e($sort) . '">' . $total_pages . '</a>';
            ?>
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&sort=<?= e($sort) ?>">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<section class="cta-banner">
    <div class="container cta-inner">
        <div class="cta-text">
            <h2>Have a Project in Mind?<br>Let's Build It Together.</h2>
            <p>From concept to completion, we deliver cladding and glazing solutions that stand the test of time.</p>
        </div>
        <div class="cta-actions">
            <a href="#quote" class="btn-primary open-quote-modal">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
            <a href="<?= e($site_url) ?>/contact.html" class="btn-outline-light">Contact Our Team <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-logo">
                    <a href="<?= e($site_url) ?>">
                        <img src="<?= e($logo_url) ?>" alt="<?= e($site_name) ?>" onerror="this.style.display='none'">
                    </a>
                </div>
                <p>We craft high-performance cladding solutions that blend aesthetics, durability, and innovation.</p>
                <div class="social-icons">
                    <a href="https://www.facebook.com/auracladding" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/auracladding/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= e($site_url) ?>">Home</a></li>
                    <li><a href="<?= e($site_url) ?>/about.html">About Us</a></li>
                    <li><a href="<?= e($site_url) ?>/services.html">Services</a></li>
                    <li><a href="<?= e(works_list_url()) ?>">Our Works</a></li>
                    <li><a href="<?= e(blog_list_url()) ?>">Blog</a></li>
                    <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
                </ul>
            </div>
             <div class="footer-col">
                <h4>Services</h4>
                <ul class="footer-links">
                    <li><a href="<?= e($site_url) ?>/acp-cladding.html">ACP Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/structural-glazing.html">Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/exterior-glazing.html">Exterior Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/tuffen-glass.html">Tuffen Glass</a></li>
                    <li><a href="<?= e($site_url) ?>/ms-fabrication.html">MS Fabrication</a></li>            
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact Us</h4>
                <ul class="contact-info">
                    <li><i class="fas fa-phone-alt"></i><a href="tel:+918682878886">+91 86828 78886</a></li>
                    <li><i class="fas fa-phone-alt"></i><a href="tel:+918862878887">+91 88628 78887</a></li>
                    <li><i class="fas fa-phone-alt"></i><a href="tel:+918682878885">+91 86828 78885</a></li>
                    <li><i class="fas fa-envelope"></i><a href="mailto:info@auracladding.com">info@auracladding.com</a></li>
                    <li><i class="fas fa-globe"></i><a href="https://www.auracladding.com" target="_blank">www.auracladding.com</a></li>
                    <li><i class="fas fa-map-marker-alt"></i>101, Pachaiamman Koil St,<br>Virugambakkam, Tamil Nadu, 600092.</li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Our Location</h4>
                <iframe src="https://maps.google.com/maps?q=101%20Kamarajar%20High%20Road%2C%20Thanthai%20Periyar%20Nagar%2C%20Virugambakkam%2C%20Chennai%20600092&t=&z=15&ie=UTF8&iwloc=&output=embed" width="100%" height="140" style="border:0; border-radius:6px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container" style="max-width:1200px;padding:0;">
                <p>&copy; <?= date('Y') ?> Aura Cladding. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a> |
                    <a href="#">Terms &amp; Conditions</a> |
                    Designed and Developed By
                    <a href="https://vgdigitalfalcon.com/" target="_blank" style="color:#f37021; font-weight:600;">@ VG DIGITAL FALCON</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<a href="https://wa.me/918682878886" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><i class="fab fa-whatsapp"></i></a>

<div class="quote-modal-overlay" id="quoteModal" role="dialog" aria-modal="true" aria-labelledby="quoteTitle">
    <div class="quote-modal">
        <button type="button" class="quote-close" id="quoteClose" aria-label="Close quote form">&times;</button>
        <h3 id="quoteTitle">Get a Free Quote</h3>
        <p class="modal-sub">Share your details and our team will get back to you shortly.</p>
        <form id="quoteForm" action="<?= e($quote_url) ?>" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="quoteName">Name</label>
                    <input type="text" id="quoteName" name="name" placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label for="quotePhone">Phone Number</label>
                    <input type="tel" id="quotePhone" name="phone" placeholder="10-digit mobile number" required>
                </div>
            </div>
            <div class="form-group">
                <label for="quoteLocation">Location</label>
                <input type="text" id="quoteLocation" name="location" placeholder="Your city / location" required>
            </div>
            <div class="form-group">
                <label for="quoteService">Service Looking for</label>
                <select id="quoteService" name="service" required>
                    <option value="" disabled selected>Select a service</option>
                    <option value="ACP Cladding">ACP Cladding</option>
                    <option value="Structural Glazing">Structural Glazing</option>
                    <option value="Exterior Glazing">Exterior Glazing</option>
                    <option value="Tuffen Glass">Tuffen Glass</option>
                    <option value="MS Fabrication">MS Fabrication</option>
                </select>
            </div>
            <button type="submit" class="btn" id="quoteSubmit">Submit Enquiry</button>
            <div class="form-status" id="quoteStatus"></div>
        </form>
    </div>
</div>

<script>
(function() {
    var menuToggle = document.querySelector('.menu-toggle');
    var navLinks = document.querySelector('.nav-links');
    var servicesItem = document.querySelector('.has-dropdown');
    var servicesToggle = document.querySelector('.services-toggle');
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = navLinks.classList.toggle('active');
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            menuToggle.innerHTML = isOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
            if (!isOpen && servicesItem) servicesItem.classList.remove('dropdown-open');
        });
    }
    if (servicesItem && servicesToggle) {
        var dropdownTimer;
        servicesToggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                e.stopPropagation();
                servicesItem.classList.toggle('dropdown-open');
            }
        });
        servicesItem.addEventListener('mouseenter', function() {
            if (window.innerWidth > 992) {
                clearTimeout(dropdownTimer);
                servicesItem.classList.add('dropdown-open');
            }
        });
        servicesItem.addEventListener('mouseleave', function() {
            if (window.innerWidth > 992) {
                dropdownTimer = setTimeout(function() {
                    servicesItem.classList.remove('dropdown-open');
                }, 180);
            }
        });
    }
    document.querySelectorAll('.nav-links a:not(.services-toggle)').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992 && navLinks) {
                navLinks.classList.remove('active');
                if (menuToggle) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
                if (servicesItem) servicesItem.classList.remove('dropdown-open');
            }
        });
    });
    document.addEventListener('click', function(e) {
        var navbar = document.querySelector('.navbar');
        if (navbar && !navbar.contains(e.target) && navLinks) {
            navLinks.classList.remove('active');
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
            if (servicesItem) servicesItem.classList.remove('dropdown-open');
        }
    });
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992 && navLinks) {
            navLinks.classList.remove('active');
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
            if (servicesItem) servicesItem.classList.remove('dropdown-open');
        }
    });

    var quoteModal = document.getElementById('quoteModal');
    var quoteForm = document.getElementById('quoteForm');
    var quoteStatus = document.getElementById('quoteStatus');
    var quoteSubmit = document.getElementById('quoteSubmit');
    var quoteClose = document.getElementById('quoteClose');
    function openQuoteModal(e) {
        if (e) e.preventDefault();
        if (!quoteModal) return;
        quoteModal.classList.add('active');
        document.body.classList.add('modal-open');
    }
    function closeQuoteModal() {
        if (!quoteModal) return;
        quoteModal.classList.remove('active');
        document.body.classList.remove('modal-open');
    }
    document.querySelectorAll('.open-quote-modal').forEach(function(btn) {
        btn.addEventListener('click', openQuoteModal);
    });
    if (quoteClose) quoteClose.addEventListener('click', closeQuoteModal);
    if (quoteModal) quoteModal.addEventListener('click', function(e) { if (e.target === quoteModal) closeQuoteModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeQuoteModal(); });
    if (quoteForm) {
        quoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var name = document.getElementById('quoteName').value.trim();
            var phone = document.getElementById('quotePhone').value.trim();
            var location = document.getElementById('quoteLocation').value.trim();
            var service = document.getElementById('quoteService').value;
            if (!name || !phone || !location || !service) {
                quoteStatus.className = 'form-status error';
                quoteStatus.textContent = 'Please fill in all the fields.';
                return;
            }
            quoteSubmit.disabled = true;
            quoteSubmit.textContent = 'Sending...';
            quoteStatus.className = 'form-status';
            quoteStatus.textContent = '';
            var formData = new FormData(quoteForm);
            formData.append('ajax', '1');
            fetch(quoteForm.getAttribute('action'), { method: 'POST', body: formData })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.success) {
                    quoteStatus.className = 'form-status success';
                    quoteStatus.textContent = 'Thank you! Your enquiry has been sent. We will contact you soon.';
                    quoteForm.reset();
                    setTimeout(closeQuoteModal, 1800);
                } else { throw new Error((data && data.message) || 'Failed'); }
            })
            .catch(function() {
                quoteStatus.className = 'form-status error';
                quoteStatus.textContent = 'Unable to send right now. Please try again or call us.';
            })
            .finally(function() {
                quoteSubmit.disabled = false;
                quoteSubmit.textContent = 'Submit Enquiry';
            });
        });
    }
})();
</script>
</body>
</html>