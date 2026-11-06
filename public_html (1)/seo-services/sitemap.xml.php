<?php
// ============================================================
// VG Digital Falcon — Dynamic XML Sitemap Generator
// Access at: yourdomain.com/sitemap.xml
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

$pages = DB::query(
    "SELECT slug, updated_at, page_type FROM seo_pages WHERE is_active = 1 ORDER BY page_type, slug"
);

$priority = [
    'city'     => '0.9',
    'industry' => '0.85',
    'business' => '0.80',
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

    <!-- Homepage -->
    <url>
        <loc><?= SITE_URL ?>/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
        <lastmod><?= date('Y-m-d') ?></lastmod>
    </url>

    <!-- Programmatic SEO Pages -->
    <?php foreach ($pages as $p): ?>
    <url>
        <loc><?= SITE_URL ?>/<?= htmlspecialchars($p['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority><?= $priority[$p['page_type']] ?? '0.7' ?></priority>
    </url>
    <?php endforeach; ?>

</urlset>
