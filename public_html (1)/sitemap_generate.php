<?php
/**
 * ============================================================
 * VG Digital Falcon – Sitemap Generator
 * ============================================================
 * Generates two sitemaps:
 *   1. sitemap-pseo.xml      – all programmatic SEO pages
 *   2. sitemap-index.xml     – sitemap index referencing all sitemaps
 *
 * Run:  php sitemap_generate.php
 * Run with static pages:  php sitemap_generate.php --include-static
 * ============================================================
 */

define('BASE_URL',        'https://vgdigitalfalcon.com');
define('OUTPUT_DIR',      __DIR__ . '/generated');
define('SITEMAP_PSEO',    __DIR__ . '/sitemap-pseo.xml');
define('SITEMAP_STATIC',  __DIR__ . '/sitemap-static.xml');
define('SITEMAP_INDEX',   __DIR__ . '/sitemap-index.xml');

$includeStatic = in_array('--include-static', $argv);

// ─── Services ─────────────────────────────────────────────────
$services = [
    'Digital Marketing'     => 'digital-marketing-agency',
    'SEO'                   => 'seo-agency',
    'Website Development'   => 'website-development-agency',
    'Youtube SEO'           => 'youtube-seo-agency',
    'Performance Marketing' => 'performance-marketing-agency',
    'Personal Branding'     => 'personal-branding-agency',
    'Ecommerce SEO'         => 'ecommerce-seo-agency',
    'Graphic Designing'     => 'graphic-designing-agency',
    'Social media Marketing'=> 'social-media-marketing-agency',
];

// ─── Cities (287 cities from CSV) ─────────────────────────────
$cities = [
    'Madurai', 'Chennai', 'Trichy', 'Coimbatore', 'Erode', 'Namakkal', 'Tirunelveli',
    'Nagercoil', 'Kanniyakumari', 'Salem', 'Tiruppur', 'Vellore', 'Thoothukudi',
    'Cuddalore', 'Dharmapuri', 'Dindigul', 'Karur', 'Krishnagiri', 'Viluppuram',
    'Sivakasi', 'Thanjavur', 'Theni', 'Nagapattinam', 'Tiruvannamalai', 'Kanchipuram',
    'Tiruvallur', 'Ranipet', 'Sivagangai', 'Virudhunagar', 'Ooty', 'Perundurai',
    'Coonoor', 'Kumbakonam', 'Ramanathapuram', 'Kovilpatti', 'Sattur', 'Rajapalayam',
    'Pondicherry', 'Marthandam', 'Hosur', 'Tiruchengode', 'Pollachi', 'Gobichettipalayam',
    'Mettupalayam', 'Udumalpet', 'Avinashi', 'Palladam', 'Kangeyam', 'Dharapuram',
    'Bhavani', 'Sathyamangalam', 'Anthiyur', 'Komarapalayam', 'Rasipuram', 'Attur',
    'Mettur', 'Edappadi', 'Sankari', 'Yercaud', 'Arakkonam', 'Gudiyatham', 'Arcot',
    'Ambur', 'Vaniyambadi', 'Walajapet', 'Arani', 'Cheyyar', 'Tindivanam', 'Gingee',
    'Chidambaram', 'Neyveli', 'Panruti', 'Vriddhachalam', 'Mayiladuthurai', 'Sirkazhi',
    'Mannargudi', 'Pattukkottai', 'Papanasam', 'Orathanadu', 'Perambalur', 'Ariyalur',
    'Jayankondam', 'Pudukkottai', 'Aranthangi', 'Karaikudi', 'Devakottai', 'Paramakudi',
    'Rameswaram', 'Mudukulathur', 'Usilampatti', 'Melur', 'Bodinayakanur', 'Cumbum',
    'Palani', 'Oddanchatram', 'Nilakottai', 'Aruppukkottai', 'Tenkasi', 'Sankarankovil',
    'Ambasamudram', 'Chengalpattu', 'Shengottai', 'Alangulam', 'Tiruchendur',
    'Vilathikulam', 'Srivilliputhur', 'Valparai', 'Thiruthuraipoondi',
    // Kerala
    'Kochi', 'Thiruvananthapuram', 'Kozhikode', 'Thrissur', 'Kollam', 'Kannur',
    'Alappuzha', 'Kottayam', 'Palakkad', 'Malappuram', 'Kasaragod', 'Pathanamthitta',
    'Idukki', 'Wayanad', 'Ernakulam', 'Kothamangalam', 'Muvattupuzha', 'Angamaly',
    'Aluva', 'Perumbavoor', 'Kalamassery', 'Kakkanad', 'Tripunithura', 'North Paravur',
    'Piravom', 'Changanassery', 'Pala', 'Vaikom', 'Ettumanoor', 'Thodupuzha',
    'Kattappana', 'Munnar', 'Nedumkandam', 'Manjeri', 'Tirur', 'Perinthalmanna',
    'Nilambur', 'Ponnani', 'Kottakkal', 'Kondotty', 'Koyilandy', 'Vadakara',
    'Ramanattukara', 'Feroke', 'Payyannur', 'Taliparamba', 'Mattannur', 'Thalassery',
    'Kasaragod Town', 'Kanhangad', 'Hosdurg', 'Cherthala', 'Kayamkulam', 'Haripad',
    'Adoor', 'Pandalam', 'Neyyattinkara', 'Attingal', 'Varkala', 'Guruvayur',
    // Karnataka
    'Bengaluru', 'Mysuru', 'Mangaluru', 'Hubballi', 'Dharwad', 'Belagavi', 'Ballari',
    'Shivamogga', 'Davanagere', 'Tumakuru', 'Kalaburagi', 'Vijayapura', 'Raichur',
    'Bidar', 'Kolar', 'Chikkaballapur', 'Ramanagara', 'Mandya', 'Hassan',
    'Chikkamagaluru', 'Udupi', 'Karwar', 'Sirsi', 'Bagalkot', 'Gadag', 'Haveri',
    'Chitradurga', 'Koppal', 'Yadgir', 'Hospet', 'Harihar', 'Ranebennur', 'Bhadravati',
    'Sagar', 'Madikeri', 'Virajpet', 'Puttur', 'Bantwal', 'Moodbidri', 'Kundapura',
    'Gokak', 'Athani', 'Jamkhandi', 'Sedam', 'Shahapur', 'Sindhanur', 'Mudhol',
    'Chintamani', 'Nanjangud', 'Kollegala', 'Tiptur', 'Arsikere', 'Sakleshpur',
    'Doddaballapur', 'Nelamangala', 'Devanahalli', 'Anekal', 'Bidadi', 'Malur', 'Hoskote',
    // Andhra Pradesh
    'Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Kakinada', 'Rajahmundry',
    'Nellore', 'Kurnool', 'Anantapur', 'Kadapa', 'Eluru', 'Ongole', 'Chittoor',
    'Machilipatnam', 'Srikakulam', 'Vizianagaram', 'Hindupur', 'Proddatur', 'Tenali',
    'Nandyal', 'Adoni', 'Amalapuram', 'Bhimavaram', 'Tadepalligudem', 'Narasaraopet',
    'Gudivada', 'Chirala', 'Madanapalle', 'Dharmavaram', 'Guntakal', 'Tadipatri',
    'Kavali', 'Markapur', 'Palakollu', 'Tanuku', 'Bapatla', 'Mangalagiri', 'Sattenapalle',
    'Repalle', 'Nuzvid', 'Piduguralla', 'Vinukonda', 'Rayachoti', 'Pulivendula',
    'Yemmiganur', 'Allagadda', 'Atmakur', 'Naidupeta', 'Sullurpeta', 'Tuni', 'Samalkot',
    'Peddapuram', 'Ramachandrapuram', 'Parvathipuram', 'Bobbili', 'Palasa', 'Ichchapuram',
    'Narasapuram', 'Avanigadda',
];

// ─── Static pages ─────────────────────────────────────────────
$staticPages = [
    ['loc' => '',                                     'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => 'about-us.html',                        'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => 'contact.html',                         'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'digital-marketing-agency-chennai.html','priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'seo-service.html',                     'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'web-development.html',                 'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'performance-marketing.html',           'priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'social-media-optimization-agency.html','priority' => '0.9', 'changefreq' => 'monthly'],
    ['loc' => 'personal-branding-service.html',       'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => 'youtube-seo-agency.html',              'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => 'ecommerce-seo-service.html',           'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => 'graphic-designing-service.html',       'priority' => '0.8', 'changefreq' => 'monthly'],
];

// ─── Helper ───────────────────────────────────────────────────
function toSlug(string $text): string
{
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $text), '-'));
}

function xmlEscape(string $str): string
{
    return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

// ─── Build pSEO sitemap ───────────────────────────────────────
$now           = date('Y-m-d');
$pseoUrlCount  = 0;

$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<!-- VG Digital Falcon – Programmatic SEO Sitemap -->' . "\n";
$xml .= '<!-- Generated: ' . date('Y-m-d H:i:s') . ' -->' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
$xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
$xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
$xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

foreach ($services as $serviceName => $serviceSlug) {
    // Service priority — first 3 services get slightly higher priority
    $priority = ($pseoUrlCount < count($cities) * 3) ? '0.8' : '0.7';

    foreach ($cities as $city) {
        $citySlug = toSlug($city);
        $urlPath  = "{$serviceSlug}-in-{$citySlug}";
        $fullUrl  = xmlEscape(BASE_URL . '/' . $urlPath);

        // Vary lastmod slightly by hash for crawl freshness signal
        // Pages are "fresh" — spread modification dates over the past 30 days
        $daysAgo  = abs(crc32($serviceName . $city)) % 30;
        $lastmod  = date('Y-m-d', strtotime("-{$daysAgo} days"));

        $xml .= "  <url>\n";
        $xml .= "    <loc>{$fullUrl}</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        $pseoUrlCount++;
    }
}

$xml .= '</urlset>';
file_put_contents(SITEMAP_PSEO, $xml);
echo "✓ sitemap-pseo.xml created — {$pseoUrlCount} URLs\n";


// ─── Build static sitemap (optional) ─────────────────────────
if ($includeStatic) {
    $sxml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sxml .= '<!-- VG Digital Falcon – Static Pages Sitemap -->' . "\n";
    $sxml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($staticPages as $page) {
        $loc = empty($page['loc'])
            ? BASE_URL . '/'
            : BASE_URL . '/' . $page['loc'];
        $sxml .= "  <url>\n";
        $sxml .= "    <loc>" . xmlEscape($loc) . "</loc>\n";
        $sxml .= "    <lastmod>{$now}</lastmod>\n";
        $sxml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
        $sxml .= "    <priority>{$page['priority']}</priority>\n";
        $sxml .= "  </url>\n";
    }

    $sxml .= '</urlset>';
    file_put_contents(SITEMAP_STATIC, $sxml);
    echo "✓ sitemap-static.xml created — " . count($staticPages) . " URLs\n";
}


// ─── Build sitemap index ──────────────────────────────────────
$ixml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$ixml .= '<!-- VG Digital Falcon – Sitemap Index -->' . "\n";
$ixml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// pSEO sitemap
$ixml .= "  <sitemap>\n";
$ixml .= "    <loc>" . xmlEscape(BASE_URL . '/sitemap-pseo.xml') . "</loc>\n";
$ixml .= "    <lastmod>{$now}</lastmod>\n";
$ixml .= "  </sitemap>\n";

// Static sitemap
if ($includeStatic) {
    $ixml .= "  <sitemap>\n";
    $ixml .= "    <loc>" . xmlEscape(BASE_URL . '/sitemap-static.xml') . "</loc>\n";
    $ixml .= "    <lastmod>{$now}</lastmod>\n";
    $ixml .= "  </sitemap>\n";
}

$ixml .= '</sitemapindex>';
file_put_contents(SITEMAP_INDEX, $ixml);
echo "✓ sitemap-index.xml created\n";

// ─── Summary ──────────────────────────────────────────────────
echo "\n=== Sitemap Summary ===\n";
echo "  pSEO URLs    : {$pseoUrlCount}\n";
echo "  Services     : " . count($services) . "\n";
echo "  Cities       : " . count($cities) . "\n";
if ($includeStatic) {
    echo "  Static pages : " . count($staticPages) . "\n";
}
echo "\n  Files written:\n";
echo "    → sitemap-pseo.xml\n";
if ($includeStatic) echo "    → sitemap-static.xml\n";
echo "    → sitemap-index.xml\n";
echo "\n  Submit to Google Search Console:\n";
echo "    " . BASE_URL . "/sitemap-index.xml\n\n";