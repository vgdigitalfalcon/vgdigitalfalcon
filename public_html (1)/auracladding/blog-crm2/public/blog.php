<?php
/**
 * Public Blog Listing Page
 * Exact design matching the Blog Page screenshot
 * Navbar + Footer aligned with main site (tuffen-glass.html style)
 */
require_once __DIR__ . '/../config.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = (int)get_setting('posts_per_page', 9);
$offset = ($page - 1) * $per_page;

$sort = $_GET['sort'] ?? 'latest'; // latest | oldest
$order = $sort === 'oldest' ? 'ASC' : 'DESC';

$total = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

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

$site_url = rtrim(get_setting('site_url', SITE_URL), '/');
$site_name = get_setting('site_name', SITE_NAME);
$blog_title = get_setting('blog_title', 'Our Blog. Insights. Ideas. Inspiration.');
$blog_desc = get_setting('blog_description', 'Stay updated with the latest trends, expert insights and guides on cladding, glazing and modern architecture.');
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

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($blog_title) ?>">
    <meta property="og:description" content="<?= e($blog_desc) ?>">
    <meta property="og:url" content="<?= e(blog_list_url()) ?>">
    <meta property="og:site_name" content="<?= e($site_name) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($blog_title) ?>">
    <meta name="twitter:description" content="<?= e($blog_desc) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= e(blog_asset_url('css/blog.css')) ?>">

    <style>
    
    /* ============================================================
   Aura Cladding Blog Page — Fixed Navbar + Footer Alignment
   Matches main site theme (navy + orange) + screenshot design
   ============================================================ */
:root {
    --navy: #051329;
    --navy-dark: #051329;
    --navy-light: #0a1f3f;
    --orange: #f37021;
    --orange-hover: #d95e1a;
    --white: #FFFFFF;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1E293B;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Poppins', sans-serif;
    color: var(--gray-800);
    line-height: 1.6;
    background: #fff;
}
a { text-decoration: none; color: inherit; transition: 0.2s; }
img { max-width: 100%; display: block; }
ul { list-style: none; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

/* ============================================================
   NAVBAR — fixed alignment (desktop + mobile)
   ============================================================ */
.navbar,
.site-header {
    background: var(--navy);
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 20px rgba(0,0,0,0.15);
}

.nav-container,
.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 72px;
    position: relative;
    width: 100%;
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #fff;
    flex-shrink: 0;
}
.logo-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    background: linear-gradient(135deg, var(--orange), #ff9a4a);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
}
.logo-text strong {
    display: block;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
}
.logo-text span {
    font-size: 9px;
    color: rgba(255,255,255,0.55);
    font-weight: 500;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}

/* Main horizontal nav — ALWAYS flex on desktop */
.nav-links,
.main-nav {
    display: flex;
    align-items: center;
    gap: 28px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-links > li,
.main-nav > li {
    position: relative;
    list-style: none;
}

.nav-links > li > a,
.main-nav > li > a {
    color: rgba(255,255,255,0.85);
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    padding: 6px 0;
    position: relative;
}

.nav-links > li > a:hover,
.main-nav > li > a:hover,
.nav-links > li > a.active,
.main-nav > li > a.active {
    color: var(--orange);
}

/* Active underline */
.nav-links > li > a.active::after,
.main-nav > li > a.active::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--orange);
    border-radius: 2px;
}

/* Dropdown — hidden by default, show on hover (desktop only) */
.dropdown-menu {
    display: none !important;          /* force hidden by default */
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    background: #fff;
    min-width: 240px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    border-radius: 8px;
    padding: 8px 0;
    z-index: 1001;
    list-style: none;
    margin: 0;
}

/* Show dropdown only on hover of parent li (desktop) */
.nav-links > li.has-dropdown:hover > .dropdown-menu,
.main-nav > li.has-dropdown:hover > .dropdown-menu {
    display: block !important;
}

.dropdown-menu li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 20px;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
}
.dropdown-menu li a i {
    color: var(--orange);
    width: 18px;
    text-align: center;
    font-size: 14px;
}
.dropdown-menu li a:hover {
    background: #fff5ef;
    color: var(--orange);
}

/* Quote button */
.btn-quote,
.nav-btn {
    background: linear-gradient(135deg, var(--orange), #ff9a4a);
    color: #fff !important;
    padding: 11px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    border: none;
    white-space: nowrap;
}
.btn-quote:hover,
.nav-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(243,112,33,0.4);
}

/* Hamburger — hidden on desktop */
.menu-toggle {
    display: none;
    font-size: 22px;
    cursor: pointer;
    color: #fff;
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ============================================================
   HERO
   ============================================================ */
.blog-hero {
    position: relative;
    min-height: 520px;
    display: flex;
    align-items: center;
    background: linear-gradient(90deg, rgba(5,19,41,0.94) 0%, rgba(5,19,41,0.78) 42%, rgba(5,19,41,0.32) 100%),
                url('image/about-banner.png') center center / cover no-repeat;
    color: var(--white);
    padding: 0;
    overflow: hidden;
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(5,19,41,0.92) 0%, rgba(5,19,41,0.65) 55%, rgba(5,19,41,0.35) 100%);
}
.hero-content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1200px;
    padding: 70px 24px;
    margin: 0 auto;
}

.hero-content > * {
    max-width: 650px;
}
 .breadcrumb {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 20px;
        }
        .breadcrumb a {
            color: rgba(255,255,255,0.7);
            transition: var(--transition);
        }
        .breadcrumb a:hover {
            color: var(--accent-orange);
        }
        .breadcrumb span {
            color: var(--accent-orange);
        }
.blog-hero h1 {
    font-size: clamp(38px, 4.2vw, 58px);
    line-height: 1.08;
    font-weight: 800;
    letter-spacing: -1.2px;
    margin: 0 0 22px;
}

.blog-hero h1 span { color: var(--orange); }
.blog-hero p {
    font-size: 16px;
    color: rgba(255,255,255,0.8);
    margin-bottom: 28px;
    line-height: 1.7;
}
.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1.5px solid rgba(255,255,255,0.45);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
}
.btn-explore:hover {
    background: var(--orange);
    border-color: var(--orange);
    color: #fff;
}

/* ============================================================
   ARTICLES SECTION
   ============================================================ */
.articles-section { padding: 70px 0 80px; background: #fff; }
.section-header { text-align: center; margin-bottom: 40px; }
.section-label {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: var(--orange);
    margin-bottom: 8px;
}
.section-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 12px;
}
.section-line {
    width: 50px;
    height: 3px;
    background: var(--orange);
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
}
.sort-select {
    display: flex;
    align-items: center;
    gap: 4px;
}
.sort-select a {
    color: var(--gray-500);
    font-size: 14px;
}
.sort-select a.active,
.sort-select a:hover {
    color: var(--orange);
    font-weight: 600;
}
.sort-select .sep { color: var(--gray-400); margin: 0 6px; }

/* Cards grid */
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
    box-shadow: 0 4px 20px rgba(11,28,46,0.06);
    transition: 0.25s;
}
.post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(11,28,46,0.12);
}
.post-card.featured { border-color: rgba(243,112,33,0.3); }
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
    background: var(--navy);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 6px;
}
.card-body { padding: 20px 22px 24px; }
.card-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: var(--gray-400);
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.card-meta i { margin-right: 4px; }
.card-body h3 {
    font-size: 17px;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 10px;
}
.card-body h3 a { color: var(--navy); }
.card-body h3 a:hover { color: var(--orange); }
.card-body p {
    font-size: 14px;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 16px;
}
.read-more {
    font-size: 14px;
    font-weight: 600;
    color: var(--orange);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.read-more:hover { gap: 10px; }

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 48px;
    flex-wrap: wrap;
}
.pagination a,
.pagination span {
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
    padding: 0 12px;
}
.pagination a:hover {
    background: var(--navy);
    color: white;
    border-color: var(--navy);
}
.pagination .current {
    background: var(--orange);
    color: #fff;
    border-color: var(--orange);
    font-weight: 700;
}
.pagination .dots { border: none; }

/* ============================================================
   CTA
   ============================================================ */
.cta-banner {
    background: linear-gradient(135deg, var(--navy-dark), var(--navy-light));
    padding: 50px 48px;
    color: white;
    border-radius: 16px;
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
.cta-text h2 span { color: var(--orange); }
.cta-text p {
    color: rgba(255,255,255,0.7);
    font-size: 15px;
    max-width: 420px;
    margin: 0;
}
.cta-actions {
    display: flex;
    gap: 14px;
    flex-shrink: 0;
}
.btn-primary {
    background: linear-gradient(135deg, var(--orange), #ff9a4a);
    color: #fff;
    padding: 14px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-outline-light {
    border: 1.5px solid rgba(255,255,255,0.4);
    color: white;
    padding: 14px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-outline-light:hover { background: rgba(255,255,255,0.1); }

.empty-blog {
    text-align: center;
    padding: 80px 20px;
    color: var(--gray-400);
}
.empty-blog i { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }

/* ============================================================
   FOOTER — fixed column alignment
   ============================================================ */
.footer,
.site-footer {
    background: var(--navy-dark);
    color: rgba(255,255,255,0.7);
    padding: 60px 0 0;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr 1.4fr 1fr;
    gap: 32px;
    padding-bottom: 40px;
    align-items: start;
}

.footer-col {
    min-width: 0; /* prevent overflow */
}

.footer-col h4 {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 20px;
    text-transform: none;
}

.footer-brand .logo,
.footer-logo.logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
.footer-brand p,
.footer-col p {
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 18px;
    color: rgba(255,255,255,0.65);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}
.footer-links li {
    margin-bottom: 12px;
}
.footer-links a {
    font-size: 14px;
    color: rgba(255,255,255,0.65);
    display: inline-block;
}
.footer-links a:hover {
    color: var(--orange);
    padding-left: 4px;
}

.contact-info {
    list-style: none;
    padding: 0;
    margin: 0;
}
.contact-info li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 14px;
    font-size: 14px;
    color: rgba(255,255,255,0.65);
    line-height: 1.5;
}
.contact-info i {
    color: var(--orange);
    margin-top: 3px;
    width: 16px;
    flex-shrink: 0;
}

.social-icons,
.socials {
    display: flex;
    gap: 10px;
    margin-top: 4px;
}
.social-icons a,
.socials a {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    transition: 0.25s;
}
.social-icons a:hover,
.socials a:hover {
    background: var(--orange);
    color: #fff;
    transform: translateY(-2px);
}

.map-placeholder {
    height: 110px;
    background: rgba(255,255,255,0.06);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: var(--orange);
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 18px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: rgba(255,255,255,0.45);
}
.footer-bottom p { margin: 0; }
.footer-bottom-links a {
    color: rgba(255,255,255,0.45);
    margin-left: 12px;
}
.footer-bottom-links a:hover { color: var(--orange); }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1100px) {
    .nav-links,
    .main-nav { gap: 18px; }
    .nav-links > li > a,
    .main-nav > li > a { font-size: 13px; }
    .footer-grid {
        grid-template-columns: 1.5fr 1fr 1fr 1.3fr;
    }
    .footer-grid .footer-col:last-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 900px) {
    /* Hide desktop nav + quote, show hamburger */
    .nav-links,
    .main-nav {
        display: none;                 /* hidden until .active */
        position: absolute;
        top: 72px;
        left: 0;
        right: 0;
        background: var(--navy);
        flex-direction: column;
        align-items: flex-start;
        gap: 0;
        padding: 16px 24px 24px;
        z-index: 999;
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
        border-top: 1px solid rgba(255,255,255,0.06);
    }

    /* When hamburger opens the menu */
    .nav-links.active,
    .main-nav.active {
        display: flex !important;
    }

    .nav-links > li,
    .main-nav > li {
        width: 100%;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .nav-links > li > a,
    .main-nav > li > a {
        padding: 14px 0;
        width: 100%;
        font-size: 15px;
    }
    .nav-links > li > a.active::after,
    .main-nav > li > a.active::after {
        display: none;
    }

    /* Mobile dropdown — show only when parent has .dropdown-open */
    .dropdown-menu {
        position: static !important;
        box-shadow: none !important;
        background: transparent !important;
        padding: 0 0 8px 12px !important;
        min-width: 0;
        display: none !important;
    }
    .has-dropdown.dropdown-open > .dropdown-menu {
        display: block !important;
    }
    .dropdown-menu li a {
        color: rgba(255,255,255,0.7) !important;
        padding: 10px 0;
    }
    .dropdown-menu li a:hover {
        background: transparent;
        color: var(--orange) !important;
    }

    .btn-quote,
    .nav-btn {
        display: none;
    }
    .menu-toggle {
        display: flex;
    }

    /* Content */
    .posts-grid { grid-template-columns: repeat(2, 1fr); }
    .cta-banner {
        flex-direction: column;
        text-align: center;
        padding: 40px 28px;
    }
    .cta-actions { justify-content: center; flex-wrap: wrap; }
    .blog-hero h1 { font-size: 32px; }
    .listing-meta {
        flex-direction: column;
        gap: 12px;
        align-items: flex-start;
    }

    /* Footer */
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }
}

@media (max-width: 600px) {
    .posts-grid { grid-template-columns: 1fr; }
    .blog-hero { min-height: 360px; }
    .blog-hero h1 { font-size: 26px; }
    .cta-banner .cta-text h2 { font-size: 22px; }
    .btn-primary,
    .btn-outline-light {
        padding: 12px 18px;
        font-size: 13px;
    }
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 28px;
    }
    .footer-bottom {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    .footer-bottom-links a { margin: 0 8px; }
}

        /* Hero background image (exact path requested) */
        .blog-hero {
            background: linear-gradient(90deg, rgba(5,19,41,0.94) 0%, rgba(5,19,41,0.78) 42%, rgba(5,19,41,0.32) 100%),
                        url('<?= e($site_url) ?>/image/glazing-banner.png') center center / cover no-repeat;
        }
        /* CTA full-bleed with subtle building image on the right */
        .cta-banner {
            position: relative;
            overflow: hidden;
            background: linear-gradient(90deg, #051329 0%, #0a1f3f 55%, rgba(5,19,41,0.85) 100%),
                        url('<?= e($site_url) ?>/image/glazing-banner.png') right center / cover no-repeat;
            border-radius: 16px;
            padding: 50px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
            color: #fff;
        }
        .cta-banner .cta-text h2 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 10px;
        }
        .cta-banner .cta-text h2 span { color: var(--orange); }
        .cta-banner .cta-text p {
            color: rgba(255,255,255,0.75);
            font-size: 15px;
            max-width: 420px;
            margin: 0;
        }
        .cta-actions {
            display: flex;
            gap: 14px;
            flex-shrink: 0;
        }
        /* Sort as text links (matches screenshot) */
        .sort-select {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .sort-select a {
            color: var(--gray-500);
            font-size: 14px;
            text-decoration: none;
        }
        .sort-select a.active,
        .sort-select a:hover {
            color: var(--orange);
            font-weight: 600;
        }
        .sort-select .sep { color: var(--gray-400); margin: 0 6px; }

        /* Ensure logo text looks good next to icon */
        .logo-text strong { font-size: 15px; letter-spacing: 0.2px; }
        .logo-text span { font-size: 9px; letter-spacing: 0.5px; text-transform: uppercase; }

        /* Active nav underline like screenshot */
        .nav-links > li > a.active {
            color: var(--orange);
            position: relative;
        }
        .nav-links > li > a.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--orange);
            border-radius: 2px;
        }

        /* Mobile refinements */
        @media (max-width: 900px) {
            .cta-banner {
                flex-direction: column;
                text-align: center;
                padding: 40px 28px;
            }
            .cta-actions { justify-content: center; flex-wrap: wrap; }
            .blog-hero h1 { font-size: 32px; }
            .listing-meta { flex-direction: column; gap: 12px; align-items: flex-start; }
        }
        @media (max-width: 600px) {
            .blog-hero { min-height: 360px; }
            .blog-hero h1 { font-size: 26px; }
            .cta-banner .cta-text h2 { font-size: 22px; }
            .btn-primary, .btn-outline-light { padding: 12px 18px; font-size: 13px; }
        }
        
         .logo {
            display: flex;
            align-items: center;
        }
        .logo img {
            max-height: 45px;
        }

        /* Blog hero: left aligned on desktop, clean responsive layout on mobile */
        @media (max-width: 900px) {
            .blog-hero {
                min-height: 460px;
                background-position: 62% center;
            }

            .hero-content {
                padding: 56px 24px 60px;
                text-align: left;
            }

            .hero-content > * {
                max-width: 100%;
            }

            .breadcrumb {
                margin-bottom: 16px;
            }

            .blog-hero h1 {
                font-size: clamp(32px, 9vw, 42px);
                line-height: 1.1;
                letter-spacing: -0.7px;
                margin-bottom: 18px;
            }

            .blog-hero p {
                font-size: 14px;
                line-height: 1.7;
                margin-bottom: 24px;
            }

            .btn-explore {
                padding: 11px 18px;
                font-size: 13px;
            }
        }

        @media (max-width: 600px) {
            .blog-hero {
                min-height: 430px;
                background-position: 68% center;
            }

            .hero-content {
                padding: 48px 20px 52px;
            }

            .breadcrumb {
                font-size: 13px;
                margin-bottom: 14px;
            }

            .blog-hero h1 {
                font-size: 30px;
                line-height: 1.08;
            }

            .blog-hero p {
                font-size: 13.5px;
                line-height: 1.65;
            }
        }
        
        .footer-logo {
            margin-bottom: 15px;
        }
        .footer-logo img {
            max-height: 45px;
        }
    </style>

    <!-- JSON-LD for Blog listing -->
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

<!-- ================= NAVBAR (exact same structure as tuffen-glass / main site) ================= -->
<nav class="navbar site-header">
    <div class="container nav-container header-inner">
         <a href="index.html" class="logo">
                <img src="image/aura-logo.png" alt="Aura Logo">
            </a>
        <ul class="nav-links main-nav">
            <li><a href="<?= e($site_url) ?>/">Home</a></li>
            <li><a href="<?= e($site_url) ?>/about.html">About Us</a></li>
            <li class="has-dropdown">
                <a href="<?= e($site_url) ?>/services.html" class="services-toggle" aria-haspopup="true" aria-expanded="false">
                    Services <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e($site_url) ?>/services.html"><i class="fas fa-th-large"></i> All Services</a></li>
                    <li><a href="<?= e($site_url) ?>/acp-cladding.html"><i class="fas fa-layer-group"></i> ACP Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/structural-glazing.html"><i class="fas fa-border-all"></i> Structural Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/exterior-glazing.html"><i class="fas fa-building"></i> Exterior Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/tuffen-glass.html"><i class="fas fa-square"></i> Tuffen Glass</a></li>
                    <li><a href="<?= e($site_url) ?>/ms-fabrication.html"><i class="fas fa-industry"></i> MS Fabrication</a></li>
                </ul>
            </li>
            <li><a href="<?= e($site_url) ?>/services.html">Our Works</a></li>
            <li><a href="<?= e(blog_list_url()) ?>" class="active">Blog</a></li>
            <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
        </ul>
        <a href="<?= e($site_url) ?>/contact.html" class="btn-quote nav-btn">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
        <div class="menu-toggle" role="button" tabindex="0" aria-label="Open navigation menu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>

<!-- ================= HERO (matches screenshot) ================= -->
<section class="blog-hero">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="breadcrumb">
            <a href="<?= e($site_url) ?>/">Home</a> &nbsp;/&nbsp; <span>Blog</span>
        </div>
        <h1>Our <span> Blogs </span></h1>
        <p>Stay updated with the latest trends, expert insights and guides on cladding, glazing and modern architecture.</p>
        <a href="#articles" class="btn-explore">Explore Our Insights <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

<!-- ================= ARTICLES ================= -->
<section class="articles-section" id="articles">
    <div class="container">
        <div class="section-header">
            <div class="section-label">ALL ARTICLES</div>
            <h2>Stories That Build Better Spaces</h2>
            <div class="section-line"></div>
        </div>

        <div class="listing-meta">
            <span>Showing <?= $total > 0 ? ($offset + 1) : 0 ?>-<?= min($offset + $per_page, $total) ?> of <?= (int)$total ?> articles</span>
            <div class="sort-select">
                <a href="?sort=latest" class="<?= $sort === 'latest' ? 'active' : '' ?>">Latest First</a>
                <span class="sep">|</span>
                <a href="?sort=oldest" class="<?= $sort === 'oldest' ? 'active' : '' ?>">Oldest First <i class="fas fa-arrow-right" style="font-size:11px;margin-left:2px;"></i></a>
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
                $img = !empty($post['featured_image'])
                    ? upload_url($post['featured_image'])
                    : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&h=400&fit=crop';
                $date = $post['published_at']
                    ? date('d M Y', strtotime($post['published_at']))
                    : date('d M Y', strtotime($post['created_at']));
                $cat = $post['category_name'] ?? 'Article';
            ?>
            <article class="post-card<?= !empty($post['is_featured']) ? ' featured' : '' ?>">
                <a href="<?= e(post_url($post['slug'])) ?>" class="card-image">
                    <img src="<?= e($img) ?>" alt="<?= e($post['featured_image_alt'] ?: $post['title']) ?>" loading="lazy">
                    <span class="cat-badge"><?= e($cat) ?></span>
                </a>
                <div class="card-body">
                    <div class="card-meta">
                        <span><i class="far fa-calendar-alt"></i> <?= $date ?></span>
                        <span>&bull;</span>
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

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&sort=<?= e($sort) ?>"><i class="fas fa-arrow-left"></i> Prev</a>
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
            <a href="?page=<?= $page + 1 ?>&sort=<?= e($sort) ?>">Next <i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================= CTA BANNER (matches screenshot) ================= -->
<div class="container" style="padding-bottom: 70px;">
    <section class="cta-banner">
        <div class="cta-text">
            <h2>Have a Project in Mind?<br><span>Let's Build It Together.</span></h2>
            <p>From concept to completion, we deliver cladding and glazing solutions that stand the test of time.</p>
        </div>
        <div class="cta-actions">
            <a href="<?= e($site_url) ?>/contact.html" class="btn-primary">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
            <a href="<?= e($site_url) ?>/contact.html" class="btn-outline-light">Contact Our Team <i class="fas fa-arrow-right"></i></a>
        </div>
    </section>
</div>

<!-- ================= FOOTER (exact same structure as tuffen-glass / main site) ================= -->
<footer class="footer site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-brand">
              <div class="footer-logo">
                        <a href="index.html"><img src="image/aura-logo.png" alt="Aura Logo"></a>
                    </div>
                <p>We craft high-performance glazing solutions that blend aesthetics, durability, and innovation.</p>
                <div class="social-icons socials">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?= e($site_url) ?>/">Home</a></li>
                    <li><a href="<?= e($site_url) ?>/about.html">About Us</a></li>
                    <li><a href="<?= e($site_url) ?>/services.html">Services</a></li>
                    <li><a href="<?= e($site_url) ?>/services.html">Our Works</a></li>
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
                    <li><i class="fas fa-phone-alt"></i> <?= e(get_setting('company_phone') ?: '+91 98765 43210') ?></li>
                    <li><i class="fas fa-envelope"></i> <?= e(get_setting('company_email') ?: 'info@auracladding.com') ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?= e(get_setting('company_address') ?: '123, Business Park, Bengaluru, Karnataka - 560001, India') ?></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Our Location</h4>
                <div class="map-placeholder">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Aura Cladding. All Rights Reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a> |
                <a href="#">Terms & Conditions</a>
            </div>
        </div>
    </div>
</footer>

<script>
// Responsive Navigation (same behavior as tuffen-glass.html)
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
const servicesItem = document.querySelector('.has-dropdown');
const servicesToggle = document.querySelector('.services-toggle');

if (menuToggle && navLinks) {
    menuToggle.setAttribute('aria-expanded', 'false');

    menuToggle.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            menuToggle.click();
        }
    });

    menuToggle.addEventListener('click', function(event) {
        event.stopPropagation();
        const isOpen = navLinks.classList.toggle('active');
        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        if (!isOpen && servicesItem) {
            servicesItem.classList.remove('dropdown-open');
            if (servicesToggle) servicesToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

if (servicesItem && servicesToggle) {
    servicesToggle.addEventListener('click', function(event) {
        if (window.innerWidth <= 900) {
            event.preventDefault();
            event.stopPropagation();
            const isOpen = servicesItem.classList.toggle('dropdown-open');
            servicesToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    });
}

document.querySelectorAll('.nav-links a:not(.services-toggle)').forEach(function(link) {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 900) {
            navLinks.classList.remove('active');
            if (servicesItem) servicesItem.classList.remove('dropdown-open');
            if (servicesToggle) servicesToggle.setAttribute('aria-expanded', 'false');
            if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
        }
    });
});

document.addEventListener('click', function(event) {
    const navbar = document.querySelector('.navbar');
    if (navbar && !navbar.contains(event.target) && navLinks) {
        navLinks.classList.remove('active');
        if (servicesItem) servicesItem.classList.remove('dropdown-open');
        if (servicesToggle) servicesToggle.setAttribute('aria-expanded', 'false');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
    }
});

window.addEventListener('resize', function() {
    if (window.innerWidth > 900 && navLinks) {
        navLinks.classList.remove('active');
        if (servicesItem) servicesItem.classList.remove('dropdown-open');
        if (servicesToggle) servicesToggle.setAttribute('aria-expanded', 'false');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
    }
});
</script>
</body>
</html>