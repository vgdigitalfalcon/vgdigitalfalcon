<?php
// ============================================================
// VG Digital Falcon — Helper Functions
// ============================================================
require_once __DIR__ . '/config.php';

// ---- Page Data Fetchers ------------------------------------

function getPageBySlug(string $slug): ?array {
    return DB::queryOne(
        "SELECT * FROM seo_pages WHERE slug = ? AND is_active = 1 LIMIT 1",
        [$slug]
    );
}

function getSeoServices(): array {
    return DB::query(
        "SELECT * FROM seo_services WHERE is_active = 1 ORDER BY sort_order ASC"
    );
}

function getFeaturedTestimonials(int $limit = 3): array {
    return DB::query(
        "SELECT * FROM testimonials WHERE is_featured = 1 AND is_active = 1 ORDER BY RAND() LIMIT ?",
        [$limit]
    );
}

function getRelatedPages(array $page, int $limit = 6): array {
    if ($page['page_type'] === 'city') {
        return DB::query(
            "SELECT slug, target_keyword, page_type, location_name, industry_name, business_type 
             FROM seo_pages 
             WHERE page_type = 'city' AND slug != ? AND is_active = 1 
             ORDER BY RAND() LIMIT ?",
            [$page['slug'], $limit]
        );
    }
    if ($page['page_type'] === 'industry') {
        return DB::query(
            "SELECT slug, target_keyword, page_type, location_name, industry_name, business_type 
             FROM seo_pages 
             WHERE page_type = 'industry' AND slug != ? AND is_active = 1 
             ORDER BY RAND() LIMIT ?",
            [$page['slug'], $limit]
        );
    }
    return DB::query(
        "SELECT slug, target_keyword, page_type, location_name, industry_name, business_type 
         FROM seo_pages 
         WHERE slug != ? AND is_active = 1 
         ORDER BY RAND() LIMIT ?",
        [$page['slug'], $limit]
    );
}

// ---- Lead Form Handler -------------------------------------

function handleLeadSubmit(): array {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return ['success' => false];

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $website = trim($_POST['website'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $slug    = trim($_POST['page_slug'] ?? '');

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please provide a valid name and email.'];
    }

    // Honeypot spam check
    if (!empty($_POST['company_fax'])) {
        return ['success' => true]; // silently discard spam
    }

    DB::execute(
        "INSERT INTO leads (page_slug, name, email, phone, website, message, ip_address, user_agent, source_keyword) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $slug, $name, $email, $phone, $website, $message,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_REFERER'] ?? ''
        ]
    );

    return ['success' => true];
}

// ---- Schema / SEO Helpers -----------------------------------

function buildSchemaMarkup(array $page): string {
    $baseSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'ProfessionalService',
        'name'     => SITE_NAME,
        'url'      => SITE_URL,
        'telephone'=> SITE_PHONE,
        'email'    => SITE_EMAIL,
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $page['location_name'] ?? 'Chennai',
            'addressCountry'  => 'IN'
        ],
        'description' => $page['meta_description'],
        'serviceType' => 'SEO Services',
        'areaServed'  => $page['location_name'] ?? 'India',
        'priceRange'  => '₹₹',
        'aggregateRating' => [
            '@type'       => 'AggregateRating',
            'ratingValue' => '4.9',
            'reviewCount' => '127',
            'bestRating'  => '5'
        ]
    ];

    $faqSchema = null;
    if ($page['section5_type'] === 'faq' && !empty($page['section5_content'])) {
        $faqs = json_decode($page['section5_content'], true);
        if ($faqs) {
            $faqSchema = [
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => array_map(fn($faq) => [
                    '@type'          => 'Question',
                    'name'           => $faq['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']]
                ], $faqs)
            ];
        }
    }

    $output = '<script type="application/ld+json">' . json_encode($baseSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    if ($faqSchema) {
        $output .= "\n" . '<script type="application/ld+json">' . json_encode($faqSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }
    return $output;
}

function buildBreadcrumbSchema(array $page): string {
    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => SITE_URL],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'SEO Services', 'item' => SITE_URL . '/seo-services'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $page['target_keyword'], 'item' => SITE_URL . '/' . $page['slug']],
        ]
    ];
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
}

// ---- Utility ------------------------------------------------

function sanitize(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function getPageLabel(array $page): string {
    return match($page['page_type']) {
        'city'     => $page['location_name'] ?? '',
        'industry' => $page['industry_name'] ?? '',
        'business' => $page['business_type'] ?? '',
        default    => ''
    };
}

function getSectionIcon(string $type): string {
    return match($type) {
        'city'     => '📍',
        'industry' => '🏭',
        'business' => '💼',
        default    => '🔗'
    };
}
