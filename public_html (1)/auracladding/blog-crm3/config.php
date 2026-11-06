<?php
/**
 * Aura Cladding Blog CRM - Configuration
 * Theme: Deep Navy + Orange/Gold
 * Optimized for Google + Generative Engine Optimization (GEO)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'u670120666_aura');
define('DB_USER', 'u670120666_aura');
define('DB_PASS', 'AuraClading2026');
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_NAME', 'Aura Cladding');
define('SITE_URL', 'https://auracladding.vgdigitalfalcon.com');
define('ADMIN_URL', SITE_URL . '/blog-crm/admin');
define('UPLOAD_PATH', __DIR__ . '/admin/uploads/');
define('UPLOAD_URL', SITE_URL . '/blog-crm/admin/uploads/');

// Recommended Featured / Cover Image size
define('FEATURED_IMAGE_WIDTH', 1200);
define('FEATURED_IMAGE_HEIGHT', 630);
define('FEATURED_IMAGE_NOTE', '1200 × 630 px (JPG or WebP, under 300 KB recommended)');

// Security
define('SESSION_NAME', 'aura_crm_session');
define('CSRF_TOKEN_NAME', 'aura_csrf');
define('PASSWORD_COST', 12);

// Pagination
define('POSTS_PER_PAGE', 9);

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB

date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// PDO Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "<br>Please check DB credentials in config.php");
}

// ---------- Helpers ----------

function generate_csrf() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verify_csrf($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function current_user() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}

function is_admin() {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'n-a';
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function get_setting($key, $default = '') {
    global $pdo;
    static $settings = null;
    if ($settings === null) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Public URL helpers — clean URLs via .htaccess at domain root
 * Listing:  {site_url}/blog
 * Single:   {site_url}/blog/{slug}
 * Gallery:  {site_url}/our-works
 * Images:   {site_url}/blog-crm/admin/uploads/{file}
 *
 * Physical fallback (if rewrite is off): {site_url}/blog-crm/public/...
 */
function public_blog_base() {
    $site = rtrim(get_setting('site_url', SITE_URL), '/');
    return $site . '/blog-crm/public';
}

/** Full URL to a single post (clean URL) */
function post_url($slug) {
    $site = rtrim(get_setting('site_url', SITE_URL), '/');
    return $site . '/blog/' . rawurlencode($slug);
}

/** Full URL to the blog listing (clean URL) */
function blog_list_url() {
    $site = rtrim(get_setting('site_url', SITE_URL), '/');
    return $site . '/blog';
}

/** Full URL to the Works / Gallery listing (clean URL) */
function works_list_url() {
    $site = rtrim(get_setting('site_url', SITE_URL), '/');
    return $site . '/our-works';
}

/** Correct absolute URL for uploaded images */
function upload_url($filename = '') {
    $site = rtrim(get_setting('site_url', SITE_URL), '/');
    $base = $site . '/blog-crm/admin/uploads/';
    return $filename ? $base . ltrim($filename, '/') : $base;
}

function calculate_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    return max(1, ceil($word_count / 200));
}

/**
 * Regenerate XML Sitemap + robots.txt
 */
function regenerate_sitemap() {
    global $pdo;
    $site_url = rtrim(get_setting('site_url', SITE_URL), '/');

    $stmt = $pdo->query("SELECT slug, updated_at, published_at, is_featured FROM posts WHERE status = 'published' ORDER BY published_at DESC");
    $posts = $stmt->fetchAll();

    $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Blog listing
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . blog_list_url() . "</loc>\n";
    $xml .= "    <changefreq>daily</changefreq>\n";
    $xml .= "    <priority>0.9</priority>\n";
    $xml .= "  </url>\n";

    // Works / Gallery listing
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . works_list_url() . "</loc>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.9</priority>\n";
    $xml .= "  </url>\n";

    foreach ($posts as $post) {
        $lastmod  = date('Y-m-d', strtotime($post['updated_at'] ?: $post['published_at']));
        $priority = $post['is_featured'] ? '0.9' : '0.8';
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . post_url($post['slug']) . "</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";
    }
    $xml .= '</urlset>';

    $public = __DIR__ . '/public/';
    if (!is_dir($public)) {
        mkdir($public, 0755, true);
    }
    file_put_contents($public . 'sitemap.xml', $xml);

    // robots.txt
    $robots = get_setting('robots_txt');
    if (empty($robots)) {
        $robots = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /blog-crm/admin/\n\nSitemap: {$site_url}/sitemap.xml";
    }
    if (stripos($robots, 'Sitemap:') === false) {
        $robots .= "\n\nSitemap: {$site_url}/sitemap.xml";
    }
    file_put_contents($public . 'robots.txt', $robots);

    return true;
}

/**
 * Generate JSON-LD structured data for a post
 * Critical for Generative Engine Optimization (AI citations)
 */
function generate_json_ld(array $post, array $author = null, array $category = null) {
    $site_url  = rtrim(get_setting('site_url', SITE_URL), '/');
    $site_name = get_setting('site_name', SITE_NAME);

    $schema_type = $post['schema_type'] ?? 'BlogPosting';
    $url = post_url($post['slug']);
    $image = !empty($post['social_image'])
        ? upload_url($post['social_image'])
        : (!empty($post['featured_image']) ? upload_url($post['featured_image']) : '');

    $data = [
        '@context' => 'https://schema.org',
        '@type'    => $schema_type,
        'headline' => $post['title'],
        'description' => $post['meta_description'] ?: $post['excerpt'] ?: '',
        'url' => $url,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => $url
        ],
        'datePublished' => date('c', strtotime($post['published_at'] ?: $post['created_at'])),
        'dateModified'  => date('c', strtotime($post['updated_at'] ?: $post['created_at'])),
        'publisher' => [
            '@type' => 'Organization',
            'name'  => $site_name,
            'url'   => $site_url,
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => $site_url . '/assets/logo.png'
            ]
        ]
    ];

    if ($image) {
        $data['image'] = [
            '@type'  => 'ImageObject',
            'url'    => $image,
            'width'  => FEATURED_IMAGE_WIDTH,
            'height' => FEATURED_IMAGE_HEIGHT
        ];
    }

    if ($author) {
        $data['author'] = [
            '@type' => 'Person',
            'name'  => $author['full_name'],
            'url'   => $site_url
        ];
        if (!empty($author['expertise'])) {
            $data['author']['jobTitle'] = $author['expertise'];
        }
        if (!empty($author['bio'])) {
            $data['author']['description'] = $author['bio'];
        }
    }

    if ($category) {
        $data['articleSection'] = $category['name'];
    }

    if (!empty($post['focus_keyword'])) {
        $data['keywords'] = $post['focus_keyword'];
    }

    if (!empty($post['key_takeaways'])) {
        $data['abstract'] = $post['key_takeaways'];
    }

    // FAQ schema
    $faq_script = '';
    if (!empty($post['faq_json'])) {
        $items = is_string($post['faq_json']) ? json_decode($post['faq_json'], true) : $post['faq_json'];
        if (is_array($items) && count($items) > 0) {
            $faq_entities = [];
            foreach ($items as $item) {
                if (!empty($item['question']) && !empty($item['answer'])) {
                    $faq_entities[] = [
                        '@type' => 'Question',
                        'name'  => $item['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text'  => $item['answer']
                        ]
                    ];
                }
            }
            if ($faq_entities) {
                $faq = [
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'mainEntity' => $faq_entities
                ];
                $faq_script = "\n" . '<script type="application/ld+json">' . json_encode($faq, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
            }
        }
    }

    $output = '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
    return $output . $faq_script;
}