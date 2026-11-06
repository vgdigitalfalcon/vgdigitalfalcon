<?php
// ============================================================
// VG Digital Falcon — Main Router
// Entry point for all programmatic SEO pages
// ============================================================
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ---- Routing -----------------------------------------------
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Strip the subfolder prefix /seo-service/
$subfolder = '/seo-service/';
if (strpos($path, $subfolder) === 0) {
    $path = substr($path, strlen($subfolder));
}

$cleanPath = trim($path, '/');
$cleanPath = preg_replace('/\.php$/', '', $cleanPath);

// Homepage
if ($cleanPath === '' || $cleanPath === 'index') {
    $firstPage = DB::queryOne("SELECT slug FROM seo_pages WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
    if ($firstPage) {
        header('Location: /seo-service/' . $firstPage['slug'], true, 302);
        exit;
    }
    http_response_code(404);
    include __DIR__ . '/templates/404.php';
    exit;
}

// Sitemap
if ($cleanPath === 'sitemap.xml') {
    include __DIR__ . '/sitemap.xml.php';
    exit;
}

// Validate slug (alphanumeric + hyphens only)
if (!preg_match('/^[a-z0-9\-]{2,120}$/', $cleanPath)) {
    http_response_code(404);
    include __DIR__ . '/templates/404.php';
    exit;
}

// Fetch page from database
$page = getPageBySlug($cleanPath);

if (!$page) {
    http_response_code(404);
    include __DIR__ . '/templates/404.php';
    exit;
}

// ---- Data Preparation --------------------------------------
$services      = getSeoServices();
$testimonials  = getFeaturedTestimonials(3);
$relatedPages  = getRelatedPages($page, 9);

// ---- Cache Headers (optional, adjust as needed) -------------
$etag = md5($page['updated_at'] . $page['slug']);
header('ETag: "' . $etag . '"');
header('Cache-Control: public, max-age=3600, stale-while-revalidate=86400');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304);
    exit;
}

// ---- Render Template ----------------------------------------
include __DIR__ . '/templates/seo-page.php';