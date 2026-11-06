<?php
/**
 * Public Blog Listing — Aura theme (navbar/footer match Tuffen Glass)
 * Clean URL: /blog
 */
require_once __DIR__ . '/../config.php';

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
$showing_from = $total > 0 ? $offset + 1 : 0;
$showing_to   = min($offset + $per_page, $total);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title><?= e(get_setting('default_meta_title', $blog_title . ' | ' . $site_name)) ?></title>
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
    --navy: #0B1C2E;
    --navy-dark: #071421;
    --orange: #F5A623;
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
body {
    font-family: 'Inter', 'Poppins', system-ui, -apple-system, sans-serif;
    color: var(--text-main);
    background: var(--white);
    line-height: 1.6;
}
a { text-decoration: none; color: inherit; transition: var(--transition); }
img { max-width: 100%; display: block; }
ul { list-style: none; }
.container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* ========== NAVBAR ========== */
.navbar {
    background-color: var(--primary-dark);
    color: var(--white);
    padding: 15px 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.logo { display: flex; align-items: center; }
.logo img { max-height: 45px; width: auto; }
.nav-links {
    display: flex;
    gap: 28px;
    align-items: center;
}
.nav-links > li { position: relative; }
.nav-links > li > a {
    font-size: 14px;
    font-weight: 500;
    color: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    gap: 5px;
    position: relative;
}
.nav-links > li > a:hover { color: var(--accent-orange); }
.nav-links > li > a.active { color: var(--accent-orange); }
.nav-links > li > a.active::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--accent-orange);
    border-radius: 2px;
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% - 2px);
    left: 0;
    background: var(--white);
    min-width: 240px;
    box-shadow: var(--shadow-md);
    border-radius: 6px;
    padding: 8px 0;
    z-index: 1001;
    margin-top: 12px;
}
.nav-links li:hover > .dropdown-menu { display: block; }
.dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 20px;
    color: var(--text-main);
    font-size: 14px;
    font-weight: 500;
}
.dropdown-menu li a i {
    color: var(--accent-orange);
    width: 18px;
    text-align: center;
    font-size: 15px;
}
.dropdown-menu li a:hover {
    background: #fff5ef;
    color: var(--accent-orange);
}
.btn-quote {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: var(--accent-orange);
    color: var(--white);
    font-weight: 600;
    border-radius: 4px;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(243,112,33,0.25);
}
.btn-quote:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
}
.menu-toggle {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--white);
    background: none;
    border: none;
    padding: 4px 8px;
}

/* ========== HERO ========== */
.blog-hero {
    position: relative;
    min-height: 460px;
    display: flex;
    align-items: center;
    background: linear-gradient(90deg, rgba(5,19,41,0.92) 0%, rgba(5,19,41,0.75) 45%, rgba(5,19,41,0.4) 100%),
                url('<?= e($banner_url) ?>') center center / cover no-repeat;
    color: var(--white);
    padding: 80px 0 70px;
}
.blog-hero-content { max-width: 560px; position: relative; z-index: 1; }
.breadcrumb {
    font-size: 14px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 20px;
}
.breadcrumb a { color: rgba(255,255,255,0.7); }
.breadcrumb a:hover { color: var(--accent-orange); }
.breadcrumb span { color: var(--accent-orange); }
.blog-hero h1 {
    font-size: 42px;
    font-weight: 700;
    line-height: 1.25;
    margin-bottom: 20px;
}
.blog-hero h1 span { color: var(--accent-orange); }
.blog-hero p {
    font-size: 16px;
    color: rgba(255,255,255,0.85);
    margin-bottom: 30px;
    line-height: 1.7;
    max-width: 480px;
}
.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1.5px solid rgba(255,255,255,0.45);
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
}
.btn-explore:hover {
    background: var(--accent-orange);
    border-color: var(--accent-orange);
    color: #fff;
}

/* ========== ARTICLES ========== */
.articles-section { padding: 70px 0 80px; background: #fff; }
.section-header { text-align: center; margin-bottom: 40px; }
.section-label {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: var(--accent-orange);
    margin-bottom: 8px;
    text-transform: uppercase;
}
.section-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-dark);
    margin-bottom: 12px;
}
.section-line {
    width: 50px;
    height: 3px;
    background: var(--accent-orange);
    margin: 0 auto;
    border-radius: 2px;
}
.listing-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    font-size: 14px;
    color: var(--gray-500);
    flex-wrap: wrap;
    gap: 12px;
}
.sort-select a { color: var(--gray-500); margin: 0 6px; }
.sort-select a.active,
.sort-select a:hover { color: var(--accent-orange); font-weight: 600; }

.posts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
}
.post-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--gray-100);
    box-shadow: 0 4px 20px rgba(5,19,41,0.06);
    transition: 0.25s;
}
.post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(5,19,41,0.12);
}
.post-card.featured { border-color: rgba(243,112,33,0.35); }
.card-image {
    position: relative;
    display: block;
    height: 200px;
    overflow: hidden;
}
.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.4s;
}
.post-card:hover .card-image img { transform: scale(1.05); }
.cat-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: var(--primary-dark);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
    letter-spacing: 0.3px;
}
.card-body { padding: 20px 22px 24px; }
.card-meta {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: var(--gray-400);
    margin-bottom: 10px;
}
.card-meta i { margin-right: 4px; color: var(--accent-orange); }
.card-body h3 {
    font-size: 17px;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 10px;
}
.card-body h3 a { color: var(--primary-dark); }
.card-body h3 a:hover { color: var(--accent-orange); }
.card-body p {
    font-size: 14px;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 16px;
}
.read-more {
    font-size: 14px;
    font-weight: 600;
    color: var(--accent-orange);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.read-more:hover { gap: 10px; }

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 48px;
    flex-wrap: wrap;
}
.pagination a, .pagination span {
    min-width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid var(--gray-200);
    color: var(--gray-600);
    padding: 0 10px;
}
.pagination a:hover { background: var(--primary-dark); color: white; border-color: var(--primary-dark); }
.pagination .current {
    background: var(--accent-orange);
    color: #fff;
    border-color: var(--accent-orange);
    font-weight: 700;
}
.pagination .dots { border: none; }

.empty-blog {
    text-align: center;
    padding: 80px 20px;
    color: var(--gray-400);
}
.empty-blog i { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }

/* ========== CTA ========== */
.cta-banner {
    background: linear-gradient(135deg, var(--primary-dark), var(--primary-light));
    padding: 60px 0;
    color: #fff;
}
.cta-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
}
.cta-text h2 {
    font-size: 28px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 10px;
}
.cta-text p { color: rgba(255,255,255,0.7); font-size: 15px; }
.cta-actions { display: flex; gap: 14px; flex-shrink: 0; flex-wrap: wrap; }
.btn-primary {
    background: var(--accent-orange);
    color: #fff;
    padding: 14px 24px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-primary:hover { background: var(--accent-hover); }
.btn-outline-light {
    border: 1.5px solid rgba(255,255,255,0.4);
    color: #fff;
    padding: 14px 24px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-outline-light:hover { background: rgba(255,255,255,0.1); }

/* ========== FOOTER ========== */
.footer {
    background: var(--primary-dark);
    color: rgba(255,255,255,0.7);
    padding-top: 60px;
}
.footer-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1.2fr 1fr;
    gap: 32px;
    padding-bottom: 50px;
}
.footer-logo { margin-bottom: 16px; }
.footer-logo img { max-height: 42px; }
.footer-col p {
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 18px;
    text-align: left;
}
.social-icons { display: flex; gap: 10px; }
.social-icons a {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 13px;
}
.social-icons a:hover {
    background: var(--accent-orange);
    color: #fff;
}
.footer-col h4 {
    color: white;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.8px;
    margin-bottom: 18px;
    text-transform: uppercase;
}
.footer-links li { margin-bottom: 10px; }
.footer-links a,
.contact-info li {
    font-size: 13px;
    color: rgba(255,255,255,0.65);
}
.footer-links a:hover { color: var(--accent-orange); }
.contact-info li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
}
.contact-info i {
    color: var(--accent-orange);
    margin-top: 3px;
    width: 16px;
}
.map-placeholder {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    background: rgba(255,255,255,0.06);
}
.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 18px 0;
    font-size: 12px;
}
.footer-bottom .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}
.footer-bottom-links a {
    color: rgba(255,255,255,0.5);
    margin-left: 8px;
}
.footer-bottom-links a:hover { color: var(--accent-orange); }

/* ========== RESPONSIVE ========== */
@media (max-width: 992px) {
    .menu-toggle { display: block; }
    .nav-links {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--primary-dark);
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 20px 24px;
        gap: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    .nav-links.active { display: flex; }
    .nav-links > li {
        width: 100%;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .nav-links > li > a {
        padding: 14px 0;
        width: 100%;
    }
    .nav-links > li > a.active::after { display: none; }
    .dropdown-menu {
        position: static;
        display: none;
        box-shadow: none;
        background: rgba(0,0,0,0.2);
        margin: 0 0 8px;
        border-radius: 6px;
        min-width: 100%;
    }
    .nav-links li.dropdown-open > .dropdown-menu { display: block; }
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
    .logo img { max-height: 38px; }
    .listing-meta { flex-direction: column; align-items: flex-start; }
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

<!-- NAVBAR -->
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
                </ul>
            </li>
            <li><a href="<?= e(works_list_url()) ?>">Our Works</a></li>
            <li><a href="<?= e(blog_list_url()) ?>" class="active">Blog</a></li>
            <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
        </ul>
        <a href="<?= e($site_url) ?>/contact.html" class="btn-quote">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
    </div>
</header>

<!-- HERO -->
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

<!-- ARTICLES -->
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

<!-- CTA -->
<section class="cta-banner">
    <div class="container cta-inner">
        <div class="cta-text">
            <h2>Have a Project in Mind?<br>Let's Build It Together.</h2>
            <p>From concept to completion, we deliver cladding and glazing solutions that stand the test of time.</p>
        </div>
        <div class="cta-actions">
            <a href="<?= e($site_url) ?>/contact.html" class="btn-primary">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
            <a href="<?= e($site_url) ?>/contact.html" class="btn-outline-light">Contact Our Team <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-logo">
                    <a href="<?= e($site_url) ?>">
                        <img src="<?= e($logo_url) ?>" alt="<?= e($site_name) ?>" onerror="this.style.display='none'">
                    </a>
                </div>
                <p>We craft high-performance glazing solutions that blend aesthetics, durability, and innovation.</p>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
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
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact Us</h4>
                <ul class="contact-info">
                    <li><i class="fas fa-phone-alt"></i> <?= e(get_setting('company_phone', '+91 98765 43210')) ?></li>
                    <li><i class="fas fa-envelope"></i> <?= e(get_setting('company_email', 'info@auracladding.com')) ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?= e(get_setting('company_address', '123, Business Park, Bengaluru, Karnataka - 560001, India')) ?></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Our Location</h4>
                <img src="https://placehold.co/300x150/0a1f3f/666?text=Map" alt="Map" class="map-placeholder">
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container" style="max-width:1200px;padding:0;">
                <p>&copy; <?= date('Y') ?> Aura Cladding. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a> |
                    <a href="#">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </div>
</footer>

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
        servicesToggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                e.stopPropagation();
                servicesItem.classList.toggle('dropdown-open');
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
})();
</script>
</body>
</html>