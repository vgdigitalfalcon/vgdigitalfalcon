<?php
/**
 * Single Blog Post — self-contained design (CSS inlined) + SEO/GEO
 * Works at: /aura/blog-crm/public/post.php?slug=...
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
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Not Found</title></head><body style="font-family:Inter,sans-serif;text-align:center;padding:80px;">
    <h1>Article not found</h1><p><a href="' . htmlspecialchars(blog_list_url()) . '">← Back to Blog</a></p></body></html>';
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
$date_pub    = $post['published_at'] ? date('d M Y', strtotime($post['published_at'])) : date('d M Y', strtotime($post['created_at']));
$date_iso    = date('c', strtotime($post['published_at'] ?: $post['created_at']));

$author = [
    'full_name' => $post['author_name'] ?? '',
    'bio'       => $post['author_bio'] ?? '',
    'expertise' => $post['author_expertise'] ?? ''
];
$category = !empty($post['category_name']) ? ['name' => $post['category_name']] : null;

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

$css_base = public_blog_base();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> | <?= e($site_name) ?></title>
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
<meta property="og:image:secure_url" content="<?= e($og_image) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($post['social_image_alt'] ?: $post['featured_image_alt'] ?: $post['title']) ?>">
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ===== Shared header/footer (from blog theme) ===== */
:root{--navy:#0B1C2E;--navy-dark:#071421;--orange:#F5A623;--orange-dark:#D4A017;--gray-50:#F8FAFC;--gray-100:#F1F5F9;--gray-200:#E2E8F0;--gray-400:#94A3B8;--gray-500:#64748B;--gray-600:#475569;--gray-700:#334155;--gray-800:#1E293B}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,-apple-system,sans-serif;color:var(--gray-800);line-height:1.6;background:#fff}
a{text-decoration:none;color:inherit;transition:.2s}
img{max-width:100%;display:block}
.container{max-width:1200px;margin:0 auto;padding:0 24px}

.site-header{background:var(--navy);position:sticky;top:0;z-index:100;box-shadow:0 2px 20px rgba(0,0,0,.15)}
.header-inner{display:flex;align-items:center;justify-content:space-between;height:72px}
.logo{display:flex;align-items:center;gap:10px;color:#fff}
.logo-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--orange),var(--orange-dark));border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--navy-dark)}
.logo-text strong{display:block;font-size:16px;line-height:1.2}
.logo-text span{font-size:10px;color:var(--gray-400);font-weight:400}
.main-nav{display:flex;gap:28px}
.main-nav a{color:rgba(255,255,255,.8);font-size:14px;font-weight:500}
.main-nav a:hover,.main-nav a.active{color:var(--orange)}
.btn-quote{background:linear-gradient(135deg,var(--orange),var(--orange-dark));color:var(--navy-dark);padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;gap:8px}
.btn-quote:hover{transform:translateY(-1px);box-shadow:0 4px 15px rgba(245,166,35,.4)}

.cta-banner{background:linear-gradient(135deg,var(--navy-dark),#132A42);padding:60px 0;color:#fff}
.cta-inner{display:flex;align-items:center;justify-content:space-between;gap:40px}
.cta-text h2{font-size:28px;font-weight:700;line-height:1.3;margin-bottom:10px}
.cta-text p{color:rgba(255,255,255,.7);font-size:15px}
.cta-actions{display:flex;gap:14px;flex-shrink:0}
.btn-primary{background:linear-gradient(135deg,var(--orange),var(--orange-dark));color:var(--navy-dark);padding:14px 24px;border-radius:8px;font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:8px}
.btn-outline-light{border:1.5px solid rgba(255,255,255,.4);color:#fff;padding:14px 24px;border-radius:8px;font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:8px}

.site-footer{background:var(--navy-dark);color:rgba(255,255,255,.7);padding-top:50px}
.footer-grid{display:grid;grid-template-columns:1.5fr 1fr 1.2fr;gap:32px;padding-bottom:40px}
.footer-brand .logo{margin-bottom:14px}
.footer-brand p{font-size:13px;line-height:1.7}
.footer-col h4{color:#fff;font-size:13px;font-weight:700;letter-spacing:.8px;margin-bottom:16px}
.footer-col a,.footer-col p{display:block;font-size:13px;margin-bottom:10px;color:rgba(255,255,255,.65)}
.footer-col a:hover{color:var(--orange)}
.footer-col p i{width:18px;color:var(--orange);margin-right:6px}
.footer-bottom{border-top:1px solid rgba(255,255,255,.08);padding:16px 0;font-size:12px}
.footer-bottom .container{display:flex;justify-content:space-between}

@media(max-width:992px){.main-nav{display:none}.cta-inner{flex-direction:column;text-align:center}.footer-grid{grid-template-columns:1fr}}

/* ===== Single post layout ===== */
.post-main{background:#fff;padding:36px 0 72px}
.post-wrap{max-width:760px;margin:0 auto;padding:0 24px}

.back-link{display:inline-flex;align-items:center;gap:8px;color:var(--orange);font-weight:600;font-size:14px;margin-bottom:24px}
.back-link:hover{gap:12px}

.post-cat{display:inline-block;background:var(--navy);color:#fff;font-size:12px;font-weight:600;padding:5px 14px;border-radius:6px;margin-right:8px;margin-bottom:14px}
.post-featured{display:inline-block;background:rgba(245,166,35,.15);color:#B45309;font-size:12px;font-weight:600;padding:5px 12px;border-radius:6px;margin-bottom:14px}

.post-title{font-size:34px;font-weight:800;color:var(--navy);line-height:1.25;margin:0 0 20px;letter-spacing:-.02em}

.post-meta-bar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;padding-bottom:22px;border-bottom:1px solid var(--gray-200);margin-bottom:28px}
.meta-author{display:flex;align-items:center;gap:12px}
.author-avatar{width:42px;height:42px;min-width:42px;border-radius:50%;background:linear-gradient(135deg,var(--orange),var(--orange-dark));color:var(--navy-dark);font-weight:700;font-size:16px;display:flex;align-items:center;justify-content:center}
.meta-author strong{display:block;font-size:14px;color:var(--navy)}
.meta-author span{font-size:12px;color:var(--gray-400)}
.meta-right{display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:var(--gray-500)}
.meta-right i{color:var(--orange);margin-right:5px}

/* Cover */
.post-cover{margin:0 0 32px;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(11,28,46,.1);background:var(--gray-100)}
.post-cover img{width:100%;height:auto;aspect-ratio:1200/630;object-fit:cover}
.post-cover figcaption{font-size:13px;color:var(--gray-400);padding:10px 4px 0;text-align:center;font-style:italic}

/* AI Summary */
.ai-summary-box{background:linear-gradient(135deg,rgba(245,166,35,.1),rgba(11,28,46,.04));border:1px solid rgba(245,166,35,.3);border-radius:12px;padding:22px 26px;margin-bottom:32px}
.ai-summary-label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#B45309;margin-bottom:10px}
.ai-summary-label i{margin-right:6px;color:var(--orange)}
.ai-summary-box p{font-size:16.5px;line-height:1.7;color:var(--gray-800);margin:0;font-weight:500}

/* Body typography */
.post-body{font-size:17px;line-height:1.85;color:var(--gray-700)}
.post-body>*:first-child{margin-top:0}
.post-body h2{font-size:24px;font-weight:700;color:var(--navy);margin:40px 0 14px;line-height:1.3}
.post-body h3{font-size:19px;font-weight:650;color:var(--navy);margin:28px 0 10px;line-height:1.35}
.post-body h4{font-size:16px;font-weight:650;color:var(--gray-800);margin:22px 0 8px}
.post-body p{margin:0 0 18px}
.post-body ul,.post-body ol{margin:0 0 20px;padding-left:26px}
.post-body li{margin-bottom:8px}
.post-body li::marker{color:var(--orange)}
.post-body a{color:#D97706;text-decoration:underline;text-underline-offset:3px}
.post-body a:hover{color:#B45309}
.post-body img{max-width:100%;height:auto;border-radius:10px;margin:24px 0;box-shadow:0 4px 20px rgba(11,28,46,.08)}
.post-body blockquote{margin:24px 0;padding:16px 22px;border-left:4px solid var(--orange);background:var(--gray-50);border-radius:0 10px 10px 0;font-size:17px;font-style:italic;color:var(--gray-800)}
.post-body blockquote p:last-child{margin-bottom:0}
.post-body table{width:100%;border-collapse:collapse;margin:22px 0;font-size:15px}
.post-body th,.post-body td{border:1px solid var(--gray-200);padding:10px 12px;text-align:left}
.post-body th{background:var(--gray-100);font-weight:600;color:var(--navy)}
.post-body hr{border:none;border-top:1px solid var(--gray-200);margin:32px 0}

/* Takeaways */
.takeaways-box{margin:44px 0 0;background:var(--navy);color:#fff;border-radius:14px;padding:26px 30px}
.takeaways-box h2{font-size:17px;font-weight:700;margin:0 0 16px;color:#fff;display:flex;align-items:center;gap:10px}
.takeaways-box h2 i{color:var(--orange)}
.takeaways-box ul{list-style:none;margin:0;padding:0}
.takeaways-box li{position:relative;padding:10px 0 10px 32px;font-size:15px;line-height:1.55;border-bottom:1px solid rgba(255,255,255,.08)}
.takeaways-box li:last-child{border-bottom:none;padding-bottom:0}
.takeaways-box li::before{content:"✓";position:absolute;left:0;top:10px;width:22px;height:22px;background:var(--orange);color:var(--navy-dark);border-radius:50%;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center}

/* FAQ */
.faq-box{margin:44px 0 0}
.faq-box>h2{font-size:22px;font-weight:700;color:var(--navy);margin:0 0 18px;display:flex;align-items:center;gap:10px}
.faq-box>h2 i{color:var(--orange)}
.faq-list{display:flex;flex-direction:column;gap:12px}
.faq-item{border:1px solid var(--gray-200);border-radius:12px;overflow:hidden;background:#fff}
.faq-item[open]{border-color:rgba(245,166,35,.4);box-shadow:0 4px 16px rgba(245,166,35,.08)}
.faq-item summary{padding:16px 50px 16px 18px;font-weight:600;font-size:15.5px;color:var(--navy);cursor:pointer;list-style:none;position:relative;line-height:1.4;background:var(--gray-50)}
.faq-item summary::-webkit-details-marker{display:none}
.faq-item summary::after{content:"+";position:absolute;right:16px;top:50%;transform:translateY(-50%);width:26px;height:26px;background:var(--navy);color:#fff;border-radius:50%;font-size:16px;display:flex;align-items:center;justify-content:center}
.faq-item[open] summary::after{content:"−";background:var(--orange);color:var(--navy-dark)}
.faq-item[open] summary{background:#fff;border-bottom:1px solid var(--gray-100)}
.faq-answer{padding:16px 18px 20px;font-size:15px;line-height:1.75;color:var(--gray-600)}

/* Sources */
.sources-box{margin:36px 0 0;padding:22px 26px;background:var(--gray-50);border-radius:12px;border:1px solid var(--gray-200)}
.sources-box h2{font-size:15px;font-weight:700;color:var(--navy);margin:0 0 12px;display:flex;align-items:center;gap:8px}
.sources-box h2 i{color:var(--gray-400)}
.sources-box ol{margin:0;padding-left:20px}
.sources-box li{font-size:13.5px;color:var(--gray-500);margin-bottom:8px;line-height:1.5}
.sources-box a{color:#D97706;word-break:break-all}

/* Author */
.author-box{margin:44px 0 0;display:flex;gap:18px;padding:26px;background:linear-gradient(135deg,var(--gray-50),#fff);border:1px solid var(--gray-200);border-radius:14px}
.author-avatar-lg{width:68px;height:68px;min-width:68px;border-radius:50%;background:linear-gradient(135deg,var(--orange),var(--orange-dark));color:var(--navy-dark);font-weight:800;font-size:26px;display:flex;align-items:center;justify-content:center}
.author-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;color:var(--gray-400);margin-bottom:4px}
.author-info h3{font-size:17px;font-weight:700;color:var(--navy);margin:0 0 4px}
.author-role{font-size:13px;color:var(--orange);font-weight:600;margin-bottom:8px}
.author-info p{font-size:14px;color:var(--gray-500);line-height:1.6;margin:0}

/* Share */
.share-bar{margin-top:36px;padding-top:24px;border-top:1px solid var(--gray-200);display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.share-bar>span{font-size:14px;font-weight:600;color:var(--gray-500);margin-right:4px}
.share-bar a{width:38px;height:38px;border-radius:50%;background:var(--gray-100);color:var(--navy);display:flex;align-items:center;justify-content:center;font-size:14px}
.share-bar a:hover{background:var(--navy);color:var(--orange)}

@media(max-width:640px){
  .post-title{font-size:26px}
  .post-body{font-size:16px}
  .post-body h2{font-size:21px}
  .post-meta-bar{flex-direction:column;align-items:flex-start}
  .author-box{flex-direction:column;align-items:flex-start}
  .takeaways-box,.ai-summary-box{padding:18px}
}
</style>

<?= generate_json_ld($post, $author, $category) ?>
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="<?= e($site_url) ?>" class="logo">
      <div class="logo-icon"><i class="fas fa-home"></i></div>
      <div class="logo-text"><strong>Aura</strong><span>Cladding & Glazing</span></div>
    </a>
    <nav class="main-nav">
      <a href="<?= e($site_url) ?>">Home</a>
      <a href="<?= e($site_url) ?>/about">About Us</a>
      <a href="<?= e($site_url) ?>/services">Services</a>
      <a href="<?= e(works_list_url()) ?>">Our Works</a>
      <a href="<?= e(blog_list_url()) ?>" class="active">Blog</a>
      <a href="<?= e($site_url) ?>/contact">Contact Us</a>
    </nav>
    <a href="<?= e($site_url) ?>/quote" class="btn-quote">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
  </div>
</header>

<main class="post-main">
  <div class="post-wrap">

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
      <img src="<?= e($cover) ?>"
           alt="<?= e($post['featured_image_alt'] ?: $post['title']) ?>"
           width="1200" height="630"
           loading="eager" fetchpriority="high"
           onerror="this.parentElement.style.display='none'">
      <?php if (!empty($post['featured_image_alt'])): ?>
      <figcaption><?= e($post['featured_image_alt']) ?></figcaption>
      <?php endif; ?>
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
    <section class="takeaways-box" aria-label="Key Takeaways">
      <h2><i class="fas fa-check-double"></i> Key Takeaways</h2>
      <ul>
        <?php foreach ($takeaways as $item): ?>
        <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if (!empty($faq_items)): ?>
    <section class="faq-box" aria-label="Frequently Asked Questions">
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
    <section class="sources-box" aria-label="Sources and References">
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
      <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($canonical) ?>&title=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' ' . $canonical) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
    </div>

  </div>
</main>

<section class="cta-banner">
  <div class="container cta-inner">
    <div class="cta-text">
      <h2>Have a Project in Mind?<br>Let's Build It Together.</h2>
      <p>From concept to completion, we deliver cladding and glazing solutions that stand the test of time.</p>
    </div>
    <div class="cta-actions">
      <a href="<?= e($site_url) ?>/quote" class="btn-primary">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
      <a href="<?= e($site_url) ?>/contact" class="btn-outline-light">Contact Our Team</a>
    </div>
  </div>
</section>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <div class="logo">
        <div class="logo-icon"><i class="fas fa-home"></i></div>
        <div class="logo-text"><strong>Aura</strong><span>Cladding & Glazing</span></div>
      </div>
      <p>We craft high-performance glazing solutions that blend aesthetics, durability, and innovation.</p>
    </div>
    <div class="footer-col">
      <h4>QUICK LINKS</h4>
      <a href="<?= e($site_url) ?>">Home</a>
      <a href="<?= e(blog_list_url()) ?>">Blog</a>
      <a href="<?= e($site_url) ?>/contact">Contact</a>
    </div>
    <div class="footer-col">
      <h4>CONTACT</h4>
      <p><i class="fas fa-phone"></i> <?= e(get_setting('company_phone')) ?></p>
      <p><i class="fas fa-envelope"></i> <?= e(get_setting('company_email')) ?></p>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container"><span>© <?= date('Y') ?> Aura Cladding. All Rights Reserved.</span></div>
  </div>
</footer>

</body>
</html>