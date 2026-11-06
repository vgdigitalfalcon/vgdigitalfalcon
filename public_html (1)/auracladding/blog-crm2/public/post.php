<?php
/**
 * Single Blog Post — Exact design matching the screenshot
 * Two-column layout with sidebar, navbar/footer from main site
 */
require_once __DIR__ . '/../config.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: ' . blog_list_url());
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug,
           u.full_name as author_name, u.bio as author_bio, u.expertise as author_expertise
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.author_id = u.id
    WHERE p.slug = ? AND p.status = 'published'
    LIMIT 1
");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    </head><body style="font-family:Poppins,sans-serif;text-align:center;padding:80px;">
    <h1>Article not found</h1><p><a href="' . htmlspecialchars(blog_list_url()) . '" style="color:#f37021;">← Back to Blog</a></p></body></html>';
    exit;
}

$pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = ?")->execute([$post['id']]);

$site_url    = rtrim(get_setting('site_url', SITE_URL), '/');
$site_name   = get_setting('site_name', SITE_NAME);
$title       = $post['meta_title'] ?: $post['title'];
$description = $post['meta_description'] ?: $post['excerpt'] ?: $post['ai_summary'] ?: '';
$cover       = !empty($post['featured_image']) ? upload_url($post['featured_image']) : '';
$og_image    = !empty($post['social_image']) ? upload_url($post['social_image']) : $cover;
$canonical   = !empty($post['canonical_url']) ? $post['canonical_url'] : post_url($post['slug']);
$date_pub    = $post['published_at'] ? date('M j, Y', strtotime($post['published_at'])) : date('M j, Y', strtotime($post['created_at']));
$date_iso    = date('c', strtotime($post['published_at'] ?: $post['created_at']));

$author = [
    'full_name' => $post['author_name'] ?? $site_name,
    'bio'       => $post['author_bio'] ?? '',
    'expertise' => $post['author_expertise'] ?? ''
];
$category = !empty($post['category_name']) ? ['name' => $post['category_name']] : null;

// Related posts (same category, exclude current)
$related = [];
try {
    $relStmt = $pdo->prepare("
        SELECT p.id, p.title, p.slug, p.featured_image, p.featured_image_alt, p.published_at, p.reading_time,
               c.name as category_name
        FROM posts p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'published' AND p.id != ?
        ORDER BY p.published_at DESC
        LIMIT 3
    ");
    $relStmt->execute([$post['id']]);
    $related = $relStmt->fetchAll();
} catch (Exception $e) {}

// Popular posts for sidebar
$popular = [];
try {
    $popStmt = $pdo->query("
        SELECT p.title, p.slug, p.featured_image, p.published_at
        FROM posts p
        WHERE p.status = 'published'
        ORDER BY p.view_count DESC, p.published_at DESC
        LIMIT 4
    ");
    $popular = $popStmt->fetchAll();
} catch (Exception $e) {}

// Categories with counts
$categories = [];
try {
    $catStmt = $pdo->query("
        SELECT c.name, c.slug, COUNT(p.id) as post_count
        FROM categories c
        LEFT JOIN posts p ON p.category_id = c.id AND p.status = 'published'
        GROUP BY c.id
        ORDER BY post_count DESC
        LIMIT 6
    ");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {}

// Prev / Next
$prev_post = null;
$next_post = null;
try {
    $prevStmt = $pdo->prepare("SELECT title, slug FROM posts WHERE status='published' AND published_at < ? ORDER BY published_at DESC LIMIT 1");
    $prevStmt->execute([$post['published_at'] ?: $post['created_at']]);
    $prev_post = $prevStmt->fetch();

    $nextStmt = $pdo->prepare("SELECT title, slug FROM posts WHERE status='published' AND published_at > ? ORDER BY published_at ASC LIMIT 1");
    $nextStmt->execute([$post['published_at'] ?: $post['created_at']]);
    $next_post = $nextStmt->fetch();
} catch (Exception $e) {}

$faq_items = [];
if (!empty($post['faq_json'])) {
    $decoded = is_string($post['faq_json']) ? json_decode($post['faq_json'], true) : $post['faq_json'];
    if (is_array($decoded)) $faq_items = $decoded;
}

$takeaways = [];
if (!empty($post['key_takeaways'])) {
    foreach (preg_split('/\r\n|\r|\n/', $post['key_takeaways']) as $line) {
        $line = trim($line, " \t•\-\*");
        if ($line !== '') $takeaways[] = $line;
    }
}

$logo_url = $site_url . '/image/aura-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title><?= e($title) ?> | <?= e($site_name) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($post['meta_keywords'])): ?>
<meta name="keywords" content="<?= e($post['meta_keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="author" content="<?= e($author['full_name']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<meta property="og:type" content="article">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:site_name" content="<?= e($site_name) ?>">
<?php if ($og_image): ?>
<meta property="og:image" content="<?= e($og_image) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta property="article:published_time" content="<?= e($date_iso) ?>">
<meta property="article:modified_time" content="<?= e(date('c', strtotime($post['updated_at']))) ?>">
<?php if (!empty($post['category_name'])): ?>
<meta property="article:section" content="<?= e($post['category_name']) ?>">
<?php endif; ?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<?php if ($og_image): ?>
<meta name="twitter:image" content="<?= e($og_image) ?>">
<?php endif; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= e(blog_asset_url('css/post.css')) ?>">

<?= generate_json_ld($post, $author, $category) ?>

<style>
    
    /* ============================================================
   Single Blog Post — Exact design matching the screenshot
   Aura navy (#051329) + orange (#f37021)
   ============================================================ */
:root {
    --navy: #051329;
    --navy-dark: #051329;
    --navy-light: #0a1f3f;
    --orange: #f37021;
    --orange-hover: #d95e1a;
    --white: #ffffff;
    --gray-50: #f8f9fa;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-400: #94a3b8;
    --gray-500: #64748b;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1e293b;
    --text-main: #333333;
    --text-light: #666666;
    --border: #e5e5e5;
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.05);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.08);
    --transition: all 0.3s ease;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Poppins', 'Inter', sans-serif;
    color: var(--text-main);
    background: #fff;
    line-height: 1.6;
    overflow-x: hidden;
}
a { text-decoration: none; color: inherit; transition: var(--transition); }
img { max-width: 100%; display: block; }
ul { list-style: none; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

/* ========== NAVBAR (same as main site) ========== */
.navbar {
    background: var(--navy);
    color: #fff;
    padding: 12px 0;
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
.logo img { max-height: 44px; width: auto; }
.nav-links {
    display: flex;
    gap: 28px;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
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
    padding: 6px 0;
}
.nav-links > li > a:hover,
.nav-links > li > a.active { color: var(--orange); }
.nav-links > li > a.active::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--orange);
    border-radius: 2px;
}
.dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    background: #fff;
    min-width: 240px;
    box-shadow: var(--shadow-md);
    border-radius: 8px;
    padding: 8px 0;
    z-index: 1001;
    list-style: none;
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
.dropdown-menu li a i { color: var(--orange); width: 18px; text-align: center; }
.dropdown-menu li a:hover { background: #fff5ef; color: var(--orange); }
.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    background: var(--orange);
    color: #fff !important;
    font-weight: 600;
    font-size: 14px;
    border-radius: 6px;
    border: none;
    white-space: nowrap;
}
.nav-btn:hover { background: var(--orange-hover); transform: translateY(-1px); }
.menu-toggle {
    display: none;
    font-size: 22px;
    cursor: pointer;
    color: #fff;
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
}

/* ========== BREADCRUMB ========== */
.post-breadcrumb {
    background: #fff;
    padding: 16px 0 0;
    font-size: 13px;
    color: var(--gray-500);
}
.post-breadcrumb a { color: var(--gray-500); }
.post-breadcrumb a:hover { color: var(--orange); }
.post-breadcrumb span { color: var(--navy); font-weight: 500; }
.post-breadcrumb i { font-size: 10px; margin: 0 6px; color: var(--gray-400); }

/* ========== LAYOUT ========== */
.post-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 40px;
    padding: 28px 0 60px;
    align-items: start;
}

/* ========== MAIN CONTENT ========== */
.post-content { min-width: 0; }

/* Featured image */
.post-featured-img {
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.post-featured-img img {
    width: 100%;
    height: auto;
    aspect-ratio: 16 / 9;
    object-fit: cover;
}

/* Category */
.post-cat-label {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--orange);
    margin-bottom: 10px;
}

/* Title */
.post-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1.3;
    margin-bottom: 16px;
    letter-spacing: -0.02em;
}

/* Meta */
.post-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    font-size: 13px;
    color: var(--gray-500);
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--gray-200);
}
.post-meta .meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}
.post-meta i { color: var(--orange); font-size: 13px; }
.post-meta .author-avatar-sm {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--orange), #ff9a4a);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Quote box */
.post-quote {
    background: #f8fafc;
    border-radius: 12px;
    padding: 24px 28px;
    margin-bottom: 28px;
    position: relative;
}
.post-quote .quote-icon {
    font-size: 28px;
    color: var(--orange);
    opacity: 0.7;
    margin-bottom: 8px;
}
.post-quote p {
    font-size: 17px;
    font-style: italic;
    color: var(--navy);
    line-height: 1.6;
    margin: 0 0 10px;
}
.post-quote cite {
    font-size: 13px;
    color: var(--gray-500);
    font-style: normal;
    font-weight: 500;
}

/* Body */
.post-body {
    font-size: 16px;
    line-height: 1.8;
    color: var(--gray-700);
}
.post-body > *:first-child { margin-top: 0; }
.post-body h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--navy);
    margin: 36px 0 14px;
    line-height: 1.3;
}
.post-body h3 {
    font-size: 18px;
    font-weight: 650;
    color: var(--navy);
    margin: 28px 0 12px;
}
.post-body p { margin: 0 0 18px; }
.post-body ul, .post-body ol {
    margin: 0 0 20px;
    padding-left: 0;
}
.post-body ul li {
    position: relative;
    padding-left: 28px;
    margin-bottom: 12px;
    list-style: none;
}
.post-body ul li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 6px;
    width: 16px;
    height: 16px;
    background: var(--orange);
    border-radius: 4px;
    opacity: 0.85;
}
.post-body a {
    color: var(--orange);
    text-decoration: underline;
    text-underline-offset: 3px;
}
.post-body img {
    border-radius: 12px;
    margin: 24px 0;
    box-shadow: var(--shadow-sm);
}
.post-body blockquote {
    margin: 24px 0;
    padding: 18px 24px;
    border-left: 4px solid var(--orange);
    background: var(--gray-50);
    border-radius: 0 10px 10px 0;
    font-style: italic;
    color: var(--navy);
}

/* Feature list with icons (for content) */
.feature-list {
    display: grid;
    gap: 12px;
    margin: 20px 0 28px;
}
.feature-list-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 0;
}
.feature-list-item .fl-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 8px;
    background: rgba(243,112,33,0.1);
    color: var(--orange);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}
.feature-list-item strong {
    display: block;
    font-size: 15px;
    color: var(--navy);
    margin-bottom: 2px;
}
.feature-list-item span {
    font-size: 13px;
    color: var(--gray-500);
}

/* Image gallery in content */
.content-gallery {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin: 28px 0;
}
.content-gallery img {
    border-radius: 10px;
    height: 140px;
    object-fit: cover;
    width: 100%;
    margin: 0;
}

/* Mid CTA */
.post-cta {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 32px 36px;
    margin: 40px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.post-cta h3 {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #fff;
}
.post-cta p {
    font-size: 14px;
    color: rgba(255,255,255,0.75);
    margin: 0 0 16px;
    max-width: 380px;
}
.post-cta .cta-btns {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.post-cta .btn-cta-primary {
    background: var(--orange);
    color: #fff;
    padding: 12px 22px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.post-cta .btn-cta-outline {
    border: 1.5px solid rgba(255,255,255,0.4);
    color: #fff;
    padding: 12px 22px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.post-cta .btn-cta-primary:hover { background: var(--orange-hover); }
.post-cta .btn-cta-outline:hover { background: rgba(255,255,255,0.1); }

/* Tags */
.post-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin: 32px 0;
}
.post-tags > span {
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-500);
    margin-right: 4px;
}
.post-tags a {
    display: inline-block;
    padding: 6px 14px;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 20px;
    font-size: 12px;
    color: var(--gray-600);
    font-weight: 500;
}
.post-tags a:hover {
    background: var(--orange);
    border-color: var(--orange);
    color: #fff;
}

/* Prev / Next */
.post-nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin: 32px 0;
}
.post-nav-item {
    padding: 18px 20px;
    border: 1px solid var(--gray-200);
    border-radius: 10px;
    transition: var(--transition);
}
.post-nav-item:hover {
    border-color: var(--orange);
    box-shadow: var(--shadow-sm);
}
.post-nav-item .nav-label {
    font-size: 12px;
    color: var(--gray-400);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.post-nav-item .nav-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
    line-height: 1.4;
}
.post-nav-item.next { text-align: right; }
.post-nav-item.next .nav-label { justify-content: flex-end; }

/* Related posts */
.related-section { margin: 40px 0 20px; }
.related-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 20px;
}
.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}
.related-card {
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    overflow: hidden;
    transition: var(--transition);
    background: #fff;
}
.related-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}
.related-card img {
    width: 100%;
    height: 130px;
    object-fit: cover;
}
.related-card-body { padding: 14px 16px 18px; }
.related-card .rel-cat {
    font-size: 11px;
    font-weight: 600;
    color: var(--orange);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}
.related-card h4 {
    font-size: 14px;
    font-weight: 650;
    color: var(--navy);
    line-height: 1.4;
    margin-bottom: 8px;
}
.related-card .rel-meta {
    font-size: 12px;
    color: var(--gray-400);
}

/* Comment CTA */
.comment-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px;
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    margin-top: 32px;
}
.comment-cta .cc-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.comment-cta .cc-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(243,112,33,0.1);
    color: var(--orange);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.comment-cta h4 {
    font-size: 15px;
    font-weight: 650;
    color: var(--navy);
    margin-bottom: 2px;
}
.comment-cta p {
    font-size: 13px;
    color: var(--gray-500);
    margin: 0;
}
.comment-cta .btn-comment {
    background: var(--orange);
    color: #fff;
    padding: 11px 22px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}
.comment-cta .btn-comment:hover { background: var(--orange-hover); }

/* ========== SIDEBAR ========== */
.post-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
    position: sticky;
    top: 90px;
}

.sidebar-widget {
    background: #fff;
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    padding: 22px;
}

/* Search */
.sidebar-search {
    display: flex;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    overflow: hidden;
}
.sidebar-search input {
    flex: 1;
    border: none;
    padding: 12px 14px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    background: var(--gray-50);
}
.sidebar-search button {
    border: none;
    background: var(--navy);
    color: #fff;
    padding: 0 16px;
    cursor: pointer;
    font-size: 14px;
}
.sidebar-search button:hover { background: var(--orange); }

/* Newsletter */
.sidebar-newsletter {
    background: var(--navy);
    border: none;
    color: #fff;
    border-radius: 12px;
    padding: 24px;
}
.sidebar-newsletter .nl-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(243,112,33,0.2);
    color: var(--orange);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 14px;
}
.sidebar-newsletter h3 {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
}
.sidebar-newsletter p {
    font-size: 13px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 16px;
    line-height: 1.5;
}
.sidebar-newsletter input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    background: rgba(255,255,255,0.08);
    color: #fff;
    font-size: 14px;
    font-family: inherit;
    margin-bottom: 10px;
    outline: none;
}
.sidebar-newsletter input::placeholder { color: rgba(255,255,255,0.45); }
.sidebar-newsletter button {
    width: 100%;
    padding: 12px;
    background: var(--orange);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    font-family: inherit;
}
.sidebar-newsletter button:hover { background: var(--orange-hover); }
.sidebar-newsletter .nl-note {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    margin-top: 10px;
    text-align: center;
}

/* About */
.sidebar-widget h3 {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 12px;
}
.sidebar-about p {
    font-size: 13px;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 12px;
}
.sidebar-about a {
    font-size: 13px;
    font-weight: 600;
    color: var(--orange);
}
.sidebar-about a:hover { gap: 6px; }

/* Categories */
.sidebar-cats li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--gray-100);
    font-size: 14px;
}
.sidebar-cats li:last-child { border-bottom: none; }
.sidebar-cats a { color: var(--gray-700); font-weight: 500; }
.sidebar-cats a:hover { color: var(--orange); }
.sidebar-cats .cat-count {
    background: var(--gray-50);
    color: var(--gray-500);
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 12px;
}

/* Popular posts */
.popular-item {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-100);
}
.popular-item:last-child { border-bottom: none; padding-bottom: 0; }
.popular-item:first-child { padding-top: 0; }
.popular-item img {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}
.popular-item h4 {
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    line-height: 1.4;
    margin-bottom: 4px;
}
.popular-item h4:hover { color: var(--orange); }
.popular-item .pop-date {
    font-size: 11px;
    color: var(--gray-400);
}

/* Share widget */
.sidebar-share .share-btns {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}
.sidebar-share .share-btns a {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    color: #fff;
}
.sidebar-share .share-btns a.fb { background: #1877f2; }
.sidebar-share .share-btns a.tw { background: #1da1f2; }
.sidebar-share .share-btns a.li { background: #0a66c2; }
.sidebar-share .share-btns a.wa { background: #25d366; }
.sidebar-share .share-btns a:hover { opacity: 0.85; transform: translateY(-2px); }
.sidebar-share .total-shares {
    text-align: center;
    padding: 14px;
    background: var(--gray-50);
    border-radius: 8px;
}
.sidebar-share .total-shares .num {
    font-size: 28px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1.2;
}
.sidebar-share .total-shares .lbl {
    font-size: 12px;
    color: var(--gray-500);
    font-weight: 500;
}

/* ========== FOOTER ========== */
.footer {
    background: var(--navy);
    color: rgba(255,255,255,0.7);
    padding: 60px 0 0;
}
.footer-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr 1.3fr 1.2fr;
    gap: 30px;
    padding-bottom: 40px;
}
.footer-col h4 {
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 18px;
}
.footer-logo img { max-height: 42px; margin-bottom: 14px; }
.footer-col p {
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 16px;
    color: rgba(255,255,255,0.6);
}
.footer-links li { margin-bottom: 10px; }
.footer-links a {
    font-size: 14px;
    color: rgba(255,255,255,0.65);
}
.footer-links a:hover { color: var(--orange); }
.contact-info li {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 13px;
    color: rgba(255,255,255,0.65);
}
.contact-info i { color: var(--orange); margin-top: 3px; width: 16px; flex-shrink: 0; }
.social-icons {
    display: flex;
    gap: 10px;
}
.social-icons a {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
}
.social-icons a:hover { background: var(--orange); }
.map-placeholder {
    height: 100px;
    background: rgba(255,255,255,0.06);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--orange);
    font-size: 24px;
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
.footer-bottom-links a {
    color: rgba(255,255,255,0.45);
    margin-left: 12px;
}
.footer-bottom-links a:hover { color: var(--orange); }

/* ========== RESPONSIVE ========== */
@media (max-width: 1024px) {
    .post-layout {
        grid-template-columns: 1fr 300px;
        gap: 28px;
    }
    .footer-grid {
        grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
    }
    .footer-grid .footer-col:last-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 900px) {
    .nav-links { display: none; }
    .nav-btn { display: none; }
    .menu-toggle { display: flex; }
    .nav-links.active {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 68px;
        left: 0;
        right: 0;
        background: var(--navy);
        padding: 16px 24px 24px;
        gap: 0;
        z-index: 999;
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
        align-items: flex-start;
    }
    .nav-links.active > li {
        width: 100%;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .nav-links.active > li > a {
        padding: 14px 0;
        width: 100%;
        font-size: 15px;
    }
    .nav-links.active > li > a.active::after { display: none; }
    .nav-links.active .dropdown-menu {
        position: static;
        box-shadow: none;
        background: transparent;
        display: none;
        padding: 0 0 8px 12px;
        min-width: 0;
    }
    .nav-links.active .has-dropdown.dropdown-open > .dropdown-menu {
        display: block;
    }
    .nav-links.active .dropdown-menu li a {
        color: rgba(255,255,255,0.7);
        padding: 10px 0;
    }

    .post-layout {
        grid-template-columns: 1fr;
    }
    .post-sidebar {
        position: static;
        order: 2;
    }
    .post-content { order: 1; }
    .related-grid { grid-template-columns: 1fr 1fr; }
    .content-gallery { grid-template-columns: 1fr 1fr; }
    .post-cta { flex-direction: column; text-align: center; }
    .post-cta .cta-btns { justify-content: center; }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; }
}

@media (max-width: 600px) {
    .post-title { font-size: 24px; }
    .post-meta { gap: 10px; }
    .related-grid { grid-template-columns: 1fr; }
    .content-gallery { grid-template-columns: 1fr; }
    .post-nav { grid-template-columns: 1fr; }
    .comment-cta {
        flex-direction: column;
        text-align: center;
    }
    .comment-cta .cc-left { flex-direction: column; }
    .footer-grid { grid-template-columns: 1fr; }
    .footer-bottom {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    .footer-bottom-links a { margin: 0 8px; }
}

</style>

</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar">
    <div class="container nav-container">
        <a href="<?= e($site_url) ?>/" class="logo">
            <img src="<?= e($logo_url) ?>" alt="Aura Cladding Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="logo-fallback" style="display:none;align-items:center;gap:10px;color:#fff;">
                <div style="width:40px;height:40px;background:linear-gradient(135deg,#f37021,#ff9a4a);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;"><i class="fas fa-home"></i></div>
                <div><strong style="display:block;font-size:15px;">Aura</strong><span style="font-size:9px;opacity:.6;letter-spacing:.5px;">CLADDING & GLAZING</span></div>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="<?= e($site_url) ?>/">Home</a></li>
            <li><a href="<?= e($site_url) ?>/about.html">About Us</a></li>
            <li class="has-dropdown">
                <a href="<?= e($site_url) ?>/services.html" class="services-toggle">Services <i class="fas fa-chevron-down" style="font-size:10px;"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="<?= e($site_url) ?>/services.html"><i class="fas fa-th-large"></i> All Services</a></li>
                    <li><a href="<?= e($site_url) ?>/acp-cladding.html"><i class="fas fa-layer-group"></i> ACP Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/structural-glazing.html"><i class="fas fa-border-all"></i> Structural Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/exterior-glazing.html"><i class="fas fa-building"></i> Exterior Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/tuffen-glass.html"><i class="fas fa-square"></i> Tuffen Glass</a></li>
                    <li><a href="<?= e($site_url) ?>/ms-fabrication.html"><i class="fas fa-industry"></i> MS Fabrication</a></li>
                </ul>
            </li>
            <li><a href="<?= e($site_url) ?>/services.html">Our Projects</a></li>
            <li><a href="<?= e(blog_list_url()) ?>" class="active">Blog</a></li>
            <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
        </ul>
        <a href="<?= e($site_url) ?>/contact.html" class="nav-btn">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
        <div class="menu-toggle" role="button" tabindex="0" aria-label="Menu"><i class="fas fa-bars"></i></div>
    </div>
</nav>

<!-- ================= BREADCRUMB ================= -->
<div class="post-breadcrumb">
    <div class="container">
        <a href="<?= e($site_url) ?>/">Home</a>
        <i class="fas fa-chevron-right"></i>
        <a href="<?= e(blog_list_url()) ?>">Blog</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= e(mb_strimwidth($post['title'], 0, 50, '…')) ?></span>
    </div>
</div>

<!-- ================= MAIN LAYOUT ================= -->
<div class="container">
    <div class="post-layout">

        <!-- ===== LEFT: CONTENT ===== -->
        <article class="post-content">

            <?php if ($cover): ?>
            <div class="post-featured-img">
                <img src="<?= e($cover) ?>"
                     alt="<?= e($post['featured_image_alt'] ?: $post['title']) ?>"
                     width="1200" height="675"
                     loading="eager" fetchpriority="high"
                     onerror="this.parentElement.style.display='none'">
            </div>
            <?php endif; ?>

            <?php if (!empty($post['category_name'])): ?>
            <div class="post-cat-label"><?= e(strtoupper($post['category_name'])) ?></div>
            <?php endif; ?>

            <h1 class="post-title"><?= e($post['title']) ?></h1>

            <div class="post-meta">
                <div class="meta-item">
                    <div class="author-avatar-sm"><?= e(strtoupper(substr($author['full_name'], 0, 1))) ?></div>
                    <span>By <?= e($author['full_name']) ?></span>
                </div>
                <div class="meta-item"><i class="far fa-calendar-alt"></i> <?= e($date_pub) ?></div>
                <div class="meta-item"><i class="far fa-clock"></i> <?= (int)$post['reading_time'] ?> min read</div>
                <div class="meta-item"><i class="far fa-eye"></i> <?= number_format((int)$post['view_count']) ?> Views</div>
            </div>

            <?php if (!empty($post['ai_summary'])): ?>
            <div class="post-quote">
                <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                <p><?= e($post['ai_summary']) ?></p>
                <cite>— The <?= e($site_name) ?> Team</cite>
            </div>
            <?php endif; ?>

            <div class="post-body">
                <?= $post['content'] ?>
            </div>

            <?php if (!empty($takeaways)): ?>
            <div class="feature-list">
                <?php
                $icons = ['fa-leaf', 'fa-drafting-compass', 'fa-tools', 'fa-clock', 'fa-check-circle', 'fa-star'];
                foreach ($takeaways as $i => $item):
                    $icon = $icons[$i % count($icons)];
                ?>
                <div class="feature-list-item">
                    <div class="fl-icon"><i class="fas <?= $icon ?>"></i></div>
                    <div><strong><?= e($item) ?></strong></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Mid CTA -->
            <div class="post-cta">
                <div>
                    <h3>Have a Project in Mind?<br>Let's Build It Together.</h3>
                    <p>Partner with Aura Cladding and turn your vision into reality. From concept to completion, we deliver cladding and glazing solutions that stand the test of time.</p>
                    <div class="cta-btns">
                        <a href="<?= e($site_url) ?>/contact.html" class="btn-cta-primary">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
                        <a href="<?= e($site_url) ?>/services.html" class="btn-cta-outline">Explore Our Work <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <?php if (!empty($post['focus_keyword']) || !empty($post['meta_keywords'])): ?>
            <div class="post-tags">
                <span>Tags:</span>
                <?php
                $tags = array_filter(array_map('trim', explode(',', $post['meta_keywords'] ?: $post['focus_keyword'])));
                foreach (array_slice($tags, 0, 6) as $tag):
                ?>
                <a href="<?= e(blog_list_url()) ?>?q=<?= urlencode($tag) ?>"><?= e($tag) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Prev / Next -->
            <?php if ($prev_post || $next_post): ?>
            <div class="post-nav">
                <?php if ($prev_post): ?>
                <a href="<?= e(post_url($prev_post['slug'])) ?>" class="post-nav-item">
                    <div class="nav-label"><i class="fas fa-arrow-left"></i> Previous Post</div>
                    <div class="nav-title"><?= e($prev_post['title']) ?></div>
                </a>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
                <?php if ($next_post): ?>
                <a href="<?= e(post_url($next_post['slug'])) ?>" class="post-nav-item next">
                    <div class="nav-label">Next Post <i class="fas fa-arrow-right"></i></div>
                    <div class="nav-title"><?= e($next_post['title']) ?></div>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Related Posts -->
            <?php if (!empty($related)): ?>
            <div class="related-section">
                <h3>Related Posts</h3>
                <div class="related-grid">
                    <?php foreach ($related as $rel):
                        $rel_img = !empty($rel['featured_image'])
                            ? upload_url($rel['featured_image'])
                            : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=260&fit=crop';
                        $rel_date = $rel['published_at'] ? date('M j, Y', strtotime($rel['published_at'])) : '';
                    ?>
                    <a href="<?= e(post_url($rel['slug'])) ?>" class="related-card">
                        <img src="<?= e($rel_img) ?>" alt="<?= e($rel['featured_image_alt'] ?: $rel['title']) ?>" loading="lazy">
                        <div class="related-card-body">
                            <?php if (!empty($rel['category_name'])): ?>
                            <div class="rel-cat"><?= e(strtoupper($rel['category_name'])) ?></div>
                            <?php endif; ?>
                            <h4><?= e($rel['title']) ?></h4>
                            <div class="rel-meta"><?= e($rel_date) ?> · <?= (int)$rel['reading_time'] ?> min read</div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comment CTA -->
            <div class="comment-cta">
                <div class="cc-left">
                    <div class="cc-icon"><i class="far fa-comment-dots"></i></div>
                    <div>
                        <h4>Join the Conversation</h4>
                        <p>Have thoughts to share? We'd love to hear from you.</p>
                    </div>
                </div>
                <a href="<?= e($site_url) ?>/contact.html" class="btn-comment">Leave a Comment</a>
            </div>

        </article>

        <!-- ===== RIGHT: SIDEBAR ===== -->
        <aside class="post-sidebar">

            <!-- Search -->
            <div class="sidebar-widget" style="padding:14px;">
                <form class="sidebar-search" action="<?= e(blog_list_url()) ?>" method="get">
                    <input type="text" name="q" placeholder="Search blogs..." aria-label="Search">
                    <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <!-- Newsletter -->
            <div class="sidebar-widget sidebar-newsletter">
                <div class="nl-icon"><i class="fas fa-envelope"></i></div>
                <h3>Stay Updated</h3>
                <p>Get the latest insights on architecture, cladding & construction delivered to your inbox.</p>
                <form action="#" method="post" onsubmit="return false;">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
                <div class="nl-note">We respect your privacy. Unsubscribe anytime.</div>
            </div>

            <!-- About -->
            <div class="sidebar-widget sidebar-about">
                <h3>About Aura Cladding</h3>
                <p>We are passionate about transforming spaces with high-quality cladding solutions that combine beauty, performance, and durability.</p>
                <a href="<?= e($site_url) ?>/about.html">Learn more about us <i class="fas fa-arrow-right"></i></a>
            </div>

            <!-- Categories -->
            <?php if (!empty($categories)): ?>
            <div class="sidebar-widget">
                <h3>Categories</h3>
                <ul class="sidebar-cats">
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= e(blog_list_url()) ?>?cat=<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></a>
                        <span class="cat-count"><?= (int)$cat['post_count'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Popular Posts -->
            <?php if (!empty($popular)): ?>
            <div class="sidebar-widget">
                <h3>Popular Posts</h3>
                <?php foreach ($popular as $pop):
                    $pop_img = !empty($pop['featured_image'])
                        ? upload_url($pop['featured_image'])
                        : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=120&h=120&fit=crop';
                    $pop_date = $pop['published_at'] ? date('M j, Y', strtotime($pop['published_at'])) : '';
                ?>
                <a href="<?= e(post_url($pop['slug'])) ?>" class="popular-item">
                    <img src="<?= e($pop_img) ?>" alt="" loading="lazy">
                    <div>
                        <h4><?= e($pop['title']) ?></h4>
                        <div class="pop-date"><?= e($pop_date) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Share -->
            <div class="sidebar-widget sidebar-share">
                <h3>Share this Article</h3>
                <p style="font-size:13px;color:var(--gray-500);margin-bottom:14px;">Spread the knowledge!</p>
                <div class="share-btns">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonical) ?>" target="_blank" rel="noopener" class="fb" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($canonical) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="tw" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($canonical) ?>&title=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="li" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' ' . $canonical) ?>" target="_blank" rel="noopener" class="wa" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
                <div class="total-shares">
                    <div class="num"><?= number_format(max(0, (int)$post['view_count'])) ?></div>
                    <div class="lbl">Total Shares</div>
                </div>
            </div>

        </aside>
    </div>
</div>

<!-- ================= FOOTER ================= -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-logo">
                    <a href="<?= e($site_url) ?>/">
                        <img src="<?= e($logo_url) ?>" alt="Aura Cladding" onerror="this.parentElement.innerHTML='<strong style=color:#fff;font-size:18px>Aura Cladding</strong>'">
                    </a>
                </div>
                <p>Transforming spaces with premium cladding solutions that combine innovation, elegance, and durability.</p>
                <div class="social-icons">
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
                    <li><a href="<?= e($site_url) ?>/services.html">Our Projects</a></li>
                    <li><a href="<?= e(blog_list_url()) ?>">Blog</a></li>
                    <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Our Services</h4>
                <ul class="footer-links">
                    <li><a href="<?= e($site_url) ?>/acp-cladding.html">ACP Cladding</a></li>
                    <li><a href="<?= e($site_url) ?>/structural-glazing.html">Structural Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/exterior-glazing.html">Exterior Glazing</a></li>
                    <li><a href="<?= e($site_url) ?>/tuffen-glass.html">Tuffen Glass</a></li>
                    <li><a href="<?= e($site_url) ?>/ms-fabrication.html">MS Fabrication</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact Info</h4>
                <ul class="contact-info">
                    <li><i class="fas fa-phone-alt"></i> <?= e(get_setting('company_phone') ?: '+91 98765 43210') ?></li>
                    <li><i class="fas fa-envelope"></i> <?= e(get_setting('company_email') ?: 'info@auracladding.com') ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?= e(get_setting('company_address') ?: '123, Business Park, Bengaluru, Karnataka - 560001, India') ?></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Find Us</h4>
                <div class="map-placeholder"><i class="fas fa-map-marker-alt"></i></div>
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
// Mobile nav
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
const servicesItem = document.querySelector('.has-dropdown');
const servicesToggle = document.querySelector('.services-toggle');

if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navLinks.classList.toggle('active');
    });
}
if (servicesItem && servicesToggle) {
    servicesToggle.addEventListener('click', function(e) {
        if (window.innerWidth <= 900) {
            e.preventDefault();
            e.stopPropagation();
            servicesItem.classList.toggle('dropdown-open');
        }
    });
}
document.addEventListener('click', function(e) {
    const navbar = document.querySelector('.navbar');
    if (navbar && !navbar.contains(e.target) && navLinks) {
        navLinks.classList.remove('active');
        if (servicesItem) servicesItem.classList.remove('dropdown-open');
    }
});
window.addEventListener('resize', function() {
    if (window.innerWidth > 900 && navLinks) {
        navLinks.classList.remove('active');
        if (servicesItem) servicesItem.classList.remove('dropdown-open');
    }
});
</script>
</body>
</html>
