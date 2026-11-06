<?php
/**
 * Single Blog Post — Aura Cladding (WordPress-style layout)
 * Clean URL: /blog/{slug}
 * Features: Sidebar + Related Posts + Recent Posts + Prev/Next
 */
require_once __DIR__ . '/../config.php';
auto_publish_scheduled_posts();
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
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Not Found</title></head><body style="font-family:Inter,sans-serif;text-align:center;padding:80px 20px;">
    <h1>Article not found</h1><p><a href="' . htmlspecialchars(blog_list_url()) . '" style="color:#f37021;">← Back to Blog</a></p></body></html>';
    exit;
}
$pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = ?")->execute([$post['id']]);
$related = [];
if (!empty($post['category_id'])) {
    $rel = $pdo->prepare("
        SELECT id, title, slug, featured_image, published_at, reading_time
        FROM posts
        WHERE status = 'published' AND category_id = ? AND id != ?
        ORDER BY published_at DESC
        LIMIT 3
    ");
    $rel->execute([$post['category_id'], $post['id']]);
    $related = $rel->fetchAll();
}
$recent = $pdo->prepare("
    SELECT id, title, slug, featured_image, published_at
    FROM posts
    WHERE status = 'published' AND id != ?
    ORDER BY published_at DESC
    LIMIT 5
");
$recent->execute([$post['id']]);
$recent_posts = $recent->fetchAll();
$categories = $pdo->query("
    SELECT c.id, c.name, c.slug, COUNT(p.id) as post_count
    FROM categories c
    LEFT JOIN posts p ON p.category_id = c.id AND p.status = 'published'
    WHERE c.is_active = 1
    GROUP BY c.id
    HAVING post_count > 0
    ORDER BY c.name
")->fetchAll();
$prev = $pdo->prepare("
    SELECT title, slug FROM posts
    WHERE status = 'published' AND published_at < ?
    ORDER BY published_at DESC LIMIT 1
");
$prev->execute([$post['published_at'] ?? $post['created_at']]);
$prev_post = $prev->fetch();
$next = $pdo->prepare("
    SELECT title, slug FROM posts
    WHERE status = 'published' AND published_at > ?
    ORDER BY published_at ASC LIMIT 1
");
$next->execute([$post['published_at'] ?? $post['created_at']]);
$next_post = $next->fetch();
$site_url   = rtrim(get_setting('site_url', SITE_URL), '/');
$site_name  = get_setting('site_name', SITE_NAME);
$title      = $post['meta_title'] ?: $post['title'];
$description= $post['meta_description'] ?: $post['excerpt'] ?: $post['ai_summary'] ?: '';
$cover      = !empty($post['featured_image']) ? upload_url($post['featured_image']) : '';
$og_image   = !empty($post['social_image']) ? upload_url($post['social_image']) : $cover;
$canonical  = !empty($post['canonical_url']) ? $post['canonical_url'] : post_url($post['slug']);
$date_pub   = $post['published_at'] ? date('d M Y', strtotime($post['published_at'])) : date('d M Y', strtotime($post['created_at']));
$date_iso   = date('c', strtotime($post['published_at'] ?: $post['created_at']));
$author     = [
    'full_name' => $post['author_name'] ?? '',
    'bio'       => $post['author_bio'] ?? '',
    'expertise' => $post['author_expertise'] ?? ''
];
$category   = !empty($post['category_name']) ? ['name' => $post['category_name']] : null;
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
$sources = [];
if (!empty($post['sources'])) {
    foreach (preg_split('/\r\n|\r|\n/', $post['sources']) as $line) {
        $line = trim($line);
        if ($line !== '') $sources[] = $line;
    }
}
$logo_url = $site_url . '/image/aura-logo.png';
$favicon_url = $site_url . '/image/favicon.png';
$quote_url = $site_url . '/send-quote.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> | <?= e($site_name) ?></title>
<link rel="icon" type="image/png" href="<?= e($favicon_url) ?>">
<link rel="shortcut icon" type="image/png" href="<?= e($favicon_url) ?>">
<link rel="apple-touch-icon" href="<?= e($favicon_url) ?>">
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($post['meta_keywords'])): ?>
<meta name="keywords" content="<?= e($post['meta_keywords']) ?>">
<?php endif; ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="author" content="<?= e($post['author_name'] ?: $site_name) ?>">
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
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<?php if ($og_image): ?>
<meta name="twitter:image" content="<?= e($og_image) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary-dark: #051329;
    --primary-light: #0a1f3f;
    --accent-orange: #f37021;
    --accent-hover: #d95e1a;
    --text-main: #333;
    --text-light: #666;
    --bg-light: #f8f9fa;
    --white: #fff;
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
* { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }
html, body { max-width:100%; overflow-x:hidden; }
body {
    font-family: 'Inter', 'Poppins', system-ui, sans-serif;
    color: var(--text-main);
    background: var(--white);
    line-height: 1.6;
}
a { text-decoration:none; color:inherit; transition:var(--transition); }
img { max-width:100%; display:block; }
ul { list-style:none; }
.container { width:100%; max-width:1200px; margin:0 auto; padding:0 20px; }
.navbar { background:var(--primary-dark); color:#fff; padding:10px 0; position:sticky; top:0; z-index:1000; }
.nav-container { display:flex; justify-content:space-between; align-items:center; }
.logo { display:flex; align-items:center; flex-shrink:0; }
.logo img { height:68px; max-height:68px; width:auto; object-fit:contain; }
.nav-links { display:flex; gap:28px; align-items:center; }
.nav-links > li { position:relative; }
.nav-links > li > a { font-size:14px; font-weight:500; color:rgba(255,255,255,0.9); display:flex; align-items:center; gap:5px; position:relative; }
.nav-links > li > a:hover, .nav-links > li > a.active { color:var(--accent-orange); }
.nav-links > li > a.active::after { content:''; position:absolute; bottom:-8px; left:0; width:100%; height:2px; background:var(--accent-orange); border-radius:2px; }
.dropdown-menu {
    visibility:hidden; opacity:0; pointer-events:none;
    position:absolute; top:100%; left:0; background:transparent;
    min-width:240px; box-shadow:none; border-radius:6px;
    padding:14px 0 0; z-index:1001; margin-top:0;
    transition:opacity 0.18s ease, visibility 0.18s ease;
}
.dropdown-menu::before {
    content:''; position:absolute; top:14px; left:0; right:0; bottom:0;
    background:#fff; border-radius:6px; box-shadow:var(--shadow-md); z-index:-1;
}
.nav-links li.has-dropdown:hover > .dropdown-menu,
.nav-links li.has-dropdown:focus-within > .dropdown-menu,
.nav-links li.has-dropdown.dropdown-open > .dropdown-menu { visibility:visible; opacity:1; pointer-events:auto; }
.dropdown-menu li a { display:flex; align-items:center; gap:12px; padding:11px 20px; color:var(--text-main); font-size:14px; font-weight:500; }
.dropdown-menu li a i { color:var(--accent-orange); width:18px; text-align:center; }
.dropdown-menu li a:hover { background:#fff5ef; color:var(--accent-orange); }
.btn-quote { display:inline-flex; align-items:center; gap:8px; padding:11px 22px; background:var(--accent-orange); color:#fff; font-weight:600; border-radius:4px; font-size:14px; cursor:pointer; border:none; }
.btn-quote:hover { background:var(--accent-hover); color:#fff; }
.menu-toggle { display:none; font-size:24px; cursor:pointer; color:#fff; background:none; border:none; padding:4px 8px; }
.post-main { background:#fff; padding:36px 0 60px; }
.post-layout { display:grid; grid-template-columns:1fr 320px; gap:48px; align-items:start; }
.post-content { min-width:0; }
.back-link { display:inline-flex; align-items:center; gap:8px; color:var(--accent-orange); font-weight:600; font-size:14px; margin-bottom:22px; }
.back-link:hover { gap:12px; }
.post-cat { display:inline-block; background:var(--primary-dark); color:#fff; font-size:12px; font-weight:600; padding:5px 14px; border-radius:6px; margin-right:8px; margin-bottom:14px; }
.post-featured { display:inline-block; background:rgba(243,112,33,0.15); color:#B45309; font-size:12px; font-weight:600; padding:5px 12px; border-radius:6px; margin-bottom:14px; }
.post-title { font-size:32px; font-weight:800; color:var(--primary-dark); line-height:1.25; margin:0 0 18px; letter-spacing:-0.02em; }
.post-meta-bar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding-bottom:20px; border-bottom:1px solid var(--gray-200); margin-bottom:26px; }
.meta-author { display:flex; align-items:center; gap:12px; }
.author-avatar { width:42px; height:42px; min-width:42px; border-radius:50%; background:linear-gradient(135deg,var(--accent-orange),#ff9a5c); color:#fff; font-weight:700; font-size:16px; display:flex; align-items:center; justify-content:center; }
.meta-author strong { display:block; font-size:14px; color:var(--primary-dark); }
.meta-author span { font-size:12px; color:var(--gray-400); }
.meta-right { display:flex; flex-wrap:wrap; gap:16px; font-size:13px; color:var(--gray-500); }
.meta-right i { color:var(--accent-orange); margin-right:5px; }
.post-cover { margin:0 0 28px; border-radius:14px; overflow:hidden; box-shadow:0 8px 30px rgba(5,19,41,0.1); background:var(--gray-100); }
.post-cover img { width:100%; height:auto; aspect-ratio:1200/630; object-fit:cover; }
.ai-summary-box { background:linear-gradient(135deg,rgba(243,112,33,0.1),rgba(5,19,41,0.04)); border:1px solid rgba(243,112,33,0.3); border-radius:12px; padding:20px 24px; margin-bottom:28px; }
.ai-summary-label { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#B45309; margin-bottom:10px; }
.ai-summary-label i { margin-right:6px; color:var(--accent-orange); }
.ai-summary-box p { font-size:16px; line-height:1.7; color:var(--gray-800); margin:0; font-weight:500; }
.post-body { font-size:16.5px; line-height:1.85; color:var(--gray-700); }
.post-body h2 { font-size:23px; font-weight:700; color:var(--primary-dark); margin:36px 0 12px; }
.post-body h3 { font-size:18px; font-weight:650; color:var(--primary-dark); margin:26px 0 10px; }
.post-body p { margin:0 0 16px; }
.post-body ul, .post-body ol { margin:0 0 18px; padding-left:24px; }
.post-body li { margin-bottom:7px; }
.post-body li::marker { color:var(--accent-orange); }
.post-body a { color:#D97706; text-decoration:underline; text-underline-offset:3px; }
.post-body img { max-width:100%; border-radius:10px; margin:20px 0; }
.post-body blockquote { margin:22px 0; padding:14px 20px; border-left:4px solid var(--accent-orange); background:var(--gray-50); border-radius:0 10px 10px 0; font-style:italic; }
.takeaways-box { margin:40px 0 0; background:var(--primary-dark); color:#fff; border-radius:14px; padding:24px 28px; }
.takeaways-box h2 { font-size:16px; font-weight:700; margin:0 0 14px; color:#fff; display:flex; align-items:center; gap:10px; }
.takeaways-box h2 i { color:var(--accent-orange); }
.takeaways-box ul { list-style:none; margin:0; padding:0; }
.takeaways-box li { position:relative; padding:9px 0 9px 30px; font-size:14.5px; border-bottom:1px solid rgba(255,255,255,0.08); }
.takeaways-box li:last-child { border-bottom:none; }
.takeaways-box li::before { content:"✓"; position:absolute; left:0; top:9px; width:20px; height:20px; background:var(--accent-orange); color:#fff; border-radius:50%; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; }
.faq-box { margin:40px 0 0; }
.faq-box > h2 { font-size:20px; font-weight:700; color:var(--primary-dark); margin:0 0 16px; display:flex; align-items:center; gap:10px; }
.faq-box > h2 i { color:var(--accent-orange); }
.faq-list { display:flex; flex-direction:column; gap:10px; }
.faq-item { border:1px solid var(--gray-200); border-radius:10px; overflow:hidden; background:#fff; }
.faq-item[open] { border-color:rgba(243,112,33,0.4); }
.faq-item summary { padding:14px 46px 14px 16px; font-weight:600; font-size:15px; color:var(--primary-dark); cursor:pointer; list-style:none; position:relative; background:var(--gray-50); }
.faq-item summary::-webkit-details-marker { display:none; }
.faq-item summary::after { content:"+"; position:absolute; right:14px; top:50%; transform:translateY(-50%); width:24px; height:24px; background:var(--primary-dark); color:#fff; border-radius:50%; font-size:15px; display:flex; align-items:center; justify-content:center; }
.faq-item[open] summary::after { content:"−"; background:var(--accent-orange); }
.faq-answer { padding:14px 16px 18px; font-size:14.5px; line-height:1.7; color:var(--gray-600); }
.sources-box { margin:32px 0 0; padding:20px 24px; background:var(--gray-50); border-radius:12px; border:1px solid var(--gray-200); }
.sources-box h2 { font-size:14px; font-weight:700; color:var(--primary-dark); margin:0 0 10px; display:flex; align-items:center; gap:8px; }
.sources-box ol { margin:0; padding-left:18px; }
.sources-box li { font-size:13px; color:var(--gray-500); margin-bottom:6px; }
.sources-box a { color:#D97706; word-break:break-all; }
.author-box { margin:40px 0 0; display:flex; gap:16px; padding:22px; background:linear-gradient(135deg,var(--gray-50),#fff); border:1px solid var(--gray-200); border-radius:14px; }
.author-avatar-lg { width:64px; height:64px; min-width:64px; border-radius:50%; background:linear-gradient(135deg,var(--accent-orange),#ff9a5c); color:#fff; font-weight:800; font-size:24px; display:flex; align-items:center; justify-content:center; }
.author-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.8px; color:var(--gray-400); margin-bottom:4px; }
.author-info h3 { font-size:16px; font-weight:700; color:var(--primary-dark); margin:0 0 4px; }
.author-role { font-size:13px; color:var(--accent-orange); font-weight:600; margin-bottom:6px; }
.author-info p { font-size:13.5px; color:var(--gray-500); line-height:1.55; margin:0; }
.share-bar { margin-top:32px; padding-top:20px; border-top:1px solid var(--gray-200); display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.share-bar > span { font-size:13px; font-weight:600; color:var(--gray-500); margin-right:4px; }
.share-bar a { width:36px; height:36px; border-radius:50%; background:var(--gray-100); color:var(--primary-dark); display:flex; align-items:center; justify-content:center; font-size:13px; }
.share-bar a:hover { background:var(--primary-dark); color:var(--accent-orange); }
.post-nav { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:40px; }
.post-nav a { display:block; padding:16px 18px; border:1px solid var(--gray-200); border-radius:10px; background:var(--gray-50); transition:var(--transition); }
.post-nav a:hover { border-color:var(--accent-orange); background:#fff; }
.post-nav .label { font-size:12px; color:var(--gray-400); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; }
.post-nav .title { font-size:14px; font-weight:600; color:var(--primary-dark); line-height:1.35; }
.post-nav .next { text-align:right; }
.sidebar { position:sticky; top:90px; }
.sidebar-widget { background:#fff; border:1px solid var(--gray-200); border-radius:12px; padding:20px; margin-bottom:22px; }
.sidebar-widget h3 { font-size:14px; font-weight:700; color:var(--primary-dark); text-transform:uppercase; letter-spacing:0.6px; margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid var(--accent-orange); display:inline-block; }
.recent-item { display:flex; gap:12px; margin-bottom:14px; }
.recent-item:last-child { margin-bottom:0; }
.recent-thumb { width:64px; height:48px; min-width:64px; border-radius:6px; overflow:hidden; background:var(--gray-100); }
.recent-thumb img { width:100%; height:100%; object-fit:cover; }
.recent-info h4 { font-size:13px; font-weight:600; color:var(--primary-dark); line-height:1.35; margin-bottom:3px; }
.recent-info h4:hover { color:var(--accent-orange); }
.recent-info span { font-size:11px; color:var(--gray-400); }
.cat-list li { margin-bottom:8px; }
.cat-list a { display:flex; justify-content:space-between; font-size:13.5px; color:var(--gray-600); padding:6px 0; border-bottom:1px dashed var(--gray-200); }
.cat-list a:hover { color:var(--accent-orange); }
.cat-list .count { background:var(--gray-100); color:var(--gray-500); font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
.sidebar-cta { background:linear-gradient(135deg,var(--primary-dark),var(--primary-light)); color:#fff; border-radius:12px; padding:24px 20px; text-align:center; }
.sidebar-cta h3 { color:#fff; border:none; margin-bottom:10px; font-size:16px; text-transform:none; letter-spacing:0; }
.sidebar-cta p { font-size:13px; color:rgba(255,255,255,0.7); margin-bottom:16px; line-height:1.5; }
.sidebar-cta .btn { display:inline-block; background:var(--accent-orange); color:#fff; padding:10px 20px; border-radius:4px; font-weight:600; font-size:13px; cursor:pointer; }
.sidebar-cta .btn:hover { background:var(--accent-hover); color:#fff; }
.related-section { background:var(--bg-light); padding:50px 0 60px; }
.related-section h2 { font-size:22px; font-weight:700; color:var(--primary-dark); margin-bottom:28px; text-align:center; }
.related-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
.related-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:var(--shadow-sm); transition:var(--transition); }
.related-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); }
.related-card img { width:100%; height:160px; object-fit:cover; background:var(--gray-100); }
.related-card-body { padding:16px 18px 20px; }
.related-card-body h3 { font-size:15px; font-weight:650; color:var(--primary-dark); line-height:1.4; margin-bottom:8px; }
.related-card-body h3:hover { color:var(--accent-orange); }
.related-card-body span { font-size:12px; color:var(--gray-400); }
.cta-banner { background:linear-gradient(135deg,var(--primary-dark),var(--primary-light)); padding:55px 0; color:#fff; }
.cta-inner { display:flex; align-items:center; justify-content:space-between; gap:40px; }
.cta-text h2 { font-size:26px; font-weight:700; line-height:1.3; margin-bottom:8px; }
.cta-text p { color:rgba(255,255,255,0.7); font-size:14.5px; }
.cta-actions { display:flex; gap:12px; flex-shrink:0; flex-wrap:wrap; }
.btn-primary { background:var(--accent-orange); color:#fff; padding:13px 22px; border-radius:4px; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:8px; cursor:pointer; }
.btn-primary:hover { background:var(--accent-hover); color:#fff; }
.btn-outline-light { border:1.5px solid rgba(255,255,255,0.4); color:#fff; padding:13px 22px; border-radius:4px; font-weight:600; font-size:14px; display:inline-flex; align-items:center; gap:8px; }
.btn-outline-light:hover { background:rgba(255,255,255,0.1); }
.footer { background:var(--primary-dark); color:rgba(255,255,255,0.7); padding-top:55px; }
.footer-grid { display:grid; grid-template-columns:1.4fr 1fr 1fr 1.2fr 1fr; gap:30px; padding-bottom:45px; }
.footer-logo { margin-bottom:14px; }
.footer-logo img { height:64px; max-height:64px; width:auto; object-fit:contain; }
.footer-col p { font-size:13px; line-height:1.7; margin-bottom:16px; text-align:left; }
.social-icons { display:flex; gap:10px; }
.social-icons a { width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; }
.social-icons a:hover { background:var(--accent-orange); }
.footer-col h4 { color:#fff; font-size:13px; font-weight:700; letter-spacing:0.8px; margin-bottom:16px; text-transform:uppercase; }
.footer-links li { margin-bottom:9px; }
.footer-links a, .contact-info li { font-size:13px; color:rgba(255,255,255,0.65); }
.footer-links a:hover { color:var(--accent-orange); }
.contact-info li { display:flex; align-items:flex-start; gap:10px; margin-bottom:11px; }
.contact-info i { color:var(--accent-orange); margin-top:3px; width:16px; }
.footer-bottom { border-top:1px solid rgba(255,255,255,0.08); padding:16px 0; font-size:12px; }
.footer-bottom .container { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
.footer-bottom-links a { color:rgba(255,255,255,0.5); margin-left:6px; }
.footer-bottom-links a:hover { color:var(--accent-orange); }
.whatsapp-float { position:fixed; right:22px; bottom:22px; width:58px; height:58px; border-radius:50%; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; font-size:30px; box-shadow:0 8px 22px rgba(37,211,102,0.45); z-index:1400; transition:var(--transition); }
.whatsapp-float:hover { transform:translateY(-3px) scale(1.06); background:#1ebe57; color:#fff; }
.quote-modal-overlay { position:fixed; inset:0; background:rgba(5,19,41,0.72); display:none; align-items:center; justify-content:center; z-index:2000; padding:18px; }
.quote-modal-overlay.active { display:flex; }
.quote-modal { background:#fff; width:100%; max-width:540px; border-radius:12px; padding:32px 28px 26px; position:relative; box-shadow:var(--shadow-md); max-height:92vh; overflow-y:auto; }
.quote-modal h3 { color:var(--primary-dark); font-size:24px; margin-bottom:6px; }
.quote-modal .modal-sub { color:var(--text-light); font-size:14px; margin-bottom:22px; }
.quote-close { position:absolute; top:12px; right:14px; width:34px; height:34px; border:none; background:var(--bg-light); border-radius:50%; cursor:pointer; font-size:18px; color:var(--text-main); }
.quote-close:hover { background:#eee; color:var(--accent-orange); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:var(--primary-dark); margin-bottom:6px; }
.form-group input, .form-group select { width:100%; height:46px; border:1px solid var(--border-color); border-radius:6px; padding:0 14px; font-family:'Poppins',sans-serif; font-size:14px; color:var(--text-main); background:#fff; outline:none; }
.form-group input:focus, .form-group select:focus { border-color:var(--accent-orange); box-shadow:0 0 0 3px rgba(243,112,33,0.12); }
.quote-modal .btn { width:100%; text-align:center; margin-top:6px; justify-content:center; display:inline-flex; align-items:center; background:var(--accent-orange); color:#fff; padding:12px 20px; border:none; border-radius:4px; font-weight:600; cursor:pointer; }
.form-status { display:none; margin-top:12px; font-size:13px; text-align:center; padding:10px 12px; border-radius:6px; }
.form-status.success { display:block; background:#e8f8ef; color:#147a3f; }
.form-status.error { display:block; background:#fdecea; color:#b42318; }
body.modal-open { overflow:hidden; }
@media (max-width: 992px) {
    .menu-toggle { display:block; }
    .nav-links { display:none; position:absolute; top:100%; left:0; right:0; background:var(--primary-dark); flex-direction:column; align-items:flex-start; padding:16px 20px 24px; gap:0; box-shadow:0 10px 30px rgba(0,0,0,0.3); border-top:1px solid rgba(255,255,255,0.08); }
    .nav-links.active { display:flex; }
    .nav-links > li { width:100%; border-bottom:1px solid rgba(255,255,255,0.06); }
    .nav-links > li > a { padding:14px 0; width:100%; }
    .nav-links > li > a.active::after { display:none; }
    .dropdown-menu { position:static; visibility:hidden; opacity:0; pointer-events:none; height:0; overflow:hidden; box-shadow:none; background:rgba(0,0,0,0.2); margin:0 0 8px; border-radius:6px; min-width:100%; padding:0; }
    .dropdown-menu::before { display:none; }
    .nav-links li.has-dropdown.dropdown-open > .dropdown-menu { visibility:visible; opacity:1; pointer-events:auto; height:auto; overflow:visible; padding:5px 0; display:block; }
    .dropdown-menu li a { color:rgba(255,255,255,0.85); padding:10px 16px; }
    .btn-quote { display:none; }
    .post-layout { grid-template-columns:1fr; }
    .sidebar { position:static; }
    .related-grid { grid-template-columns:1fr 1fr; }
    .footer-grid { grid-template-columns:1fr 1fr; }
    .cta-inner { flex-direction:column; text-align:center; }
    .cta-actions { justify-content:center; }
}
@media (max-width: 640px) {
    .post-title { font-size:24px; }
    .post-body { font-size:15.5px; }
    .related-grid { grid-template-columns:1fr; }
    .post-nav { grid-template-columns:1fr; }
    .footer-grid { grid-template-columns:1fr; }
    .footer-bottom .container { flex-direction:column; text-align:center; }
.logo img { height: 84px; max-height: 96px; width: auto; }
    .whatsapp-float { width:52px; height:52px; font-size:26px; right:16px; bottom:16px; }
    .quote-modal { padding:26px 16px 20px; }
    .form-row { grid-template-columns:1fr 1fr; gap:10px; }
}
</style>
<?= generate_json_ld($post, $author, $category) ?>
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
<main class="post-main">
    <div class="container">
        <div class="post-layout">
            <div class="post-content">
                <a href="<?= e(blog_list_url()) ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to all articles</a>
                <header class="post-header">
                    <?php if (!empty($post['category_name'])): ?>
                    <span class="post-cat"><?= e($post['category_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($post['is_featured'])): ?>
                    <span class="post-featured"><i class="fas fa-star"></i> Featured</span>
                    <?php endif; ?>
                    <h1 class="post-title"><?= e($post['title']) ?></h1>
                    <div class="post-meta-bar">
                        <div class="meta-left">
                            <?php if (!empty($post['author_name'])): ?>
                            <div class="meta-author">
                                <div class="author-avatar"><?= e(strtoupper(substr($post['author_name'], 0, 1))) ?></div>
                                <div>
                                    <strong><?= e($post['author_name']) ?></strong>
                                    <?php if (!empty($post['author_expertise'])): ?>
                                    <span><?= e($post['author_expertise']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="meta-right">
                            <span><i class="far fa-calendar-alt"></i> <?= e($date_pub) ?></span>
                            <span><i class="far fa-clock"></i> <?= (int)$post['reading_time'] ?> min read</span>
                            <span><i class="far fa-eye"></i> <?= number_format((int)$post['view_count']) ?> views</span>
                        </div>
                    </div>
                </header>
                <?php if ($cover): ?>
                <figure class="post-cover">
                    <img src="<?= e($cover) ?>" alt="<?= e($post['featured_image_alt'] ?: $post['title']) ?>" width="1200" height="630" loading="eager" fetchpriority="high" onerror="this.parentElement.style.display='none'">
                </figure>
                <?php endif; ?>
                <?php if (!empty($post['ai_summary'])): ?>
                <div class="ai-summary-box">
                    <div class="ai-summary-label"><i class="fas fa-bolt"></i> Key Insight</div>
                    <p><?= nl2br(e($post['ai_summary'])) ?></p>
                </div>
                <?php endif; ?>
                <div class="post-body">
                    <?= $post['content'] ?>
                </div>
                <?php if (!empty($takeaways)): ?>
                <section class="takeaways-box">
                    <h2><i class="fas fa-check-double"></i> Key Takeaways</h2>
                    <ul>
                        <?php foreach ($takeaways as $item): ?>
                        <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <?php endif; ?>
                <?php if (!empty($faq_items)): ?>
                <section class="faq-box">
                    <h2><i class="fas fa-circle-question"></i> Frequently Asked Questions</h2>
                    <div class="faq-list">
                        <?php foreach ($faq_items as $i => $faq):
                            if (empty($faq['question']) || empty($faq['answer'])) continue;
                        ?>
                        <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
                            <summary><?= e($faq['question']) ?></summary>
                            <div class="faq-answer"><?= nl2br(e($faq['answer'])) ?></div>
                        </details>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                <?php if (!empty($sources)): ?>
                <section class="sources-box">
                    <h2><i class="fas fa-book-open"></i> Sources &amp; References</h2>
                    <ol>
                        <?php foreach ($sources as $src): ?>
                        <li>
                            <?php if (filter_var($src, FILTER_VALIDATE_URL)): ?>
                                <a href="<?= e($src) ?>" target="_blank" rel="noopener noreferrer"><?= e($src) ?></a>
                            <?php else: ?>
                                <?= e($src) ?>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </section>
                <?php endif; ?>
                <?php if (!empty($post['author_name'])): ?>
                <section class="author-box">
                    <div class="author-avatar-lg"><?= e(strtoupper(substr($post['author_name'], 0, 1))) ?></div>
                    <div class="author-info">
                        <div class="author-label">Written by</div>
                        <h3><?= e($post['author_name']) ?></h3>
                        <?php if (!empty($post['author_expertise'])): ?>
                        <div class="author-role"><?= e($post['author_expertise']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($post['author_bio'])): ?>
                        <p><?= e($post['author_bio']) ?></p>
                        <?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>
                <div class="share-bar">
                    <span>Share this article</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonical) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($canonical) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' ' . $canonical) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                </div>
                <?php if ($prev_post || $next_post): ?>
                <nav class="post-nav">
                    <?php if ($prev_post): ?>
                    <a href="<?= e(post_url($prev_post['slug'])) ?>">
                        <div class="label">← Previous</div>
                        <div class="title"><?= e(mb_strimwidth($prev_post['title'], 0, 55, '...')) ?></div>
                    </a>
                    <?php else: ?><div></div><?php endif; ?>
                    <?php if ($next_post): ?>
                    <a href="<?= e(post_url($next_post['slug'])) ?>" class="next">
                        <div class="label">Next →</div>
                        <div class="title"><?= e(mb_strimwidth($next_post['title'], 0, 55, '...')) ?></div>
                    </a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </div>
            <aside class="sidebar">
                <?php if (!empty($recent_posts)): ?>
                <div class="sidebar-widget">
                    <h3>Recent Posts</h3>
                    <?php foreach ($recent_posts as $rp): ?>
                    <a href="<?= e(post_url($rp['slug'])) ?>" class="recent-item">
                        <div class="recent-thumb">
                            <?php if (!empty($rp['featured_image'])): ?>
                            <img src="<?= e(upload_url($rp['featured_image'])) ?>" alt="" loading="lazy" onerror="this.style.display='none'">
                            <?php endif; ?>
                        </div>
                        <div class="recent-info">
                            <h4><?= e(mb_strimwidth($rp['title'], 0, 55, '...')) ?></h4>
                            <span><?= $rp['published_at'] ? date('d M Y', strtotime($rp['published_at'])) : '' ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($categories)): ?>
                <div class="sidebar-widget">
                    <h3>Categories</h3>
                    <ul class="cat-list">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= e(blog_list_url()) ?>?category=<?= e($cat['slug']) ?>">
                                <?= e($cat['name']) ?>
                                <span class="count"><?= (int)$cat['post_count'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <div class="sidebar-cta">
                    <h3>Need a Quote?</h3>
                    <p>Get a free consultation for your next cladding or glazing project.</p>
                    <a href="#quote" class="btn open-quote-modal">Get Free Quote</a>
                </div>
            </aside>
        </div>
    </div>
</main>
<?php if (!empty($related)): ?>
<section class="related-section">
    <div class="container">
        <h2>Related Articles</h2>
        <div class="related-grid">
            <?php foreach ($related as $rel): ?>
            <a href="<?= e(post_url($rel['slug'])) ?>" class="related-card">
                <?php if (!empty($rel['featured_image'])): ?>
                <img src="<?= e(upload_url($rel['featured_image'])) ?>" alt="<?= e($rel['title']) ?>" loading="lazy" onerror="this.src='https://placehold.co/400x220/0a1f3f/666?text=Aura'">
                <?php else: ?>
                <img src="https://placehold.co/400x220/0a1f3f/666?text=Aura" alt="">
                <?php endif; ?>
                <div class="related-card-body">
                    <h3><?= e(mb_strimwidth($rel['title'], 0, 70, '...')) ?></h3>
                    <span>
                        <?= $rel['published_at'] ? date('d M Y', strtotime($rel['published_at'])) : '' ?>
                        <?php if (!empty($rel['reading_time'])): ?> · <?= (int)$rel['reading_time'] ?> min read<?php endif; ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<section class="cta-banner">
    <div class="container cta-inner">
        <div class="cta-text">
            <h2>Have a Project in Mind?<br>Let's Build It Together.</h2>
            <p>From concept to completion, we deliver cladding and glazing solutions that stand the test of time.</p>
        </div>
        <div class="cta-actions">
            <a href="#quote" class="btn-primary open-quote-modal">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
            <a href="<?= e($site_url) ?>/contact.html" class="btn-outline-light">Contact Our Team</a>
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