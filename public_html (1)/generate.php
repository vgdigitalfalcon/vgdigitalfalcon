<?php
/**
 * ============================================================
 * VG Digital Falcon – Programmatic SEO Page Generator
 * ============================================================
 * Generates one HTML page per city × service combination.
 *
 * CLI Usage:
 *   php generate.php              – generate all pages
 *   php generate.php --dry-run    – count pages without writing
 *   php generate.php --service="Website Development" --city="Madurai"
 *                                  – generate a single page
 *   php generate.php --force      – overwrite existing files
 *
 * Output folder: ./generated/
 * URL structure: /{service-slug}-in-{city-slug}
 *   e.g.  /website-development-agency-in-madurai
 *
 * ────────────────────────────────────────────────────────────
 * CONTENT VARIATION ENGINE (NEW)
 * ────────────────────────────────────────────────────────────
 * Every text block that previously had ONE fixed string per service now
 * has 3–7 variants, each picked deterministically from its OWN seed so
 * that fields rotate independently of one another. That means the
 * *combination* of meta description + title + intros + hero + CTA +
 * review on any given page is one of thousands of possibilities, not
 * one of 4. This directly reduces duplicate/thin-content risk across
 * the 287 cities × 9 services in this generator, which matters because
 * Google's near-duplicate detection weighs <title>, <meta description>,
 * and opening body paragraphs most heavily.
 * ============================================================
 */

// ─── Detect environment (CLI vs Web) ─────────────────────────
$isCLI = (php_sapi_name() === 'cli' || defined('STDIN'));

// ─── Error handling & limits ──────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);

// ─── Parse arguments based on environment ─────────────────────
$dryRun         = false;
$forceOverwrite = false;
$filterService  = null;
$filterCity     = null;

if ($isCLI) {
    global $argv;
    $args = $argv ?? [];
    $dryRun         = in_array('--dry-run', $args);
    $forceOverwrite = in_array('--force', $args);
    foreach ($args as $arg) {
        if (strpos($arg, '--service=') === 0) $filterService = trim(substr($arg, 10), '"\'');
        if (strpos($arg, '--city=') === 0)    $filterCity    = trim(substr($arg, 7),  '"\'');
    }
} else {
    $dryRun         = isset($_GET['dry-run']) || isset($_GET['dry_run']);
    $forceOverwrite = isset($_GET['force']);
    $filterService  = $_GET['service'] ?? null;
    $filterCity     = $_GET['city']    ?? null;
    header('Content-Type: text/plain; charset=utf-8');
    $allowedIPs = ['127.0.0.1', '::1'];
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIPs)) {
        // Uncomment for production: die("Access denied.\n");
    }
}

// ─── Config ───────────────────────────────────────────────────
define('BASE_URL',     'https://vgdigitalfalcon.com');
define('TEMPLATE',     __DIR__ . '/template.html');
define('OUTPUT_DIR',   __DIR__ . '/service');
define('SITEMAP_FILE', __DIR__ . '/sitemap-pseo.xml');

// ─── Tracking Scripts (injected into <head>) ──────────────────
// These are stored as a constant and replaced via {{TRACKING_SCRIPTS}} in template.html
// Add {{TRACKING_SCRIPTS}} inside your <head> tag in template.html
define('TRACKING_SCRIPTS', <<<'SCRIPTS'
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-SHM6SNGHL8"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-SHM6SNGHL8');
</script>
<!-- Microsoft Clarity -->
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "xdwd8oxpkn");
</script>
<!-- Tawk.to Live Chat -->
<script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/6a3cdccd2a464c1d44a16197/default';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
    })();
</script>
SCRIPTS);

// ─── Services ─────────────────────────────────────────────────
$services = [
    'Digital Marketing'      => 'digital-marketing-agency',
    'SEO'                    => 'seo-agency',
    'Website Development'    => 'website-development-agency',
    'Youtube SEO'            => 'youtube-seo-agency',
    'Performance Marketing'  => 'performance-marketing-agency',
    'Personal Branding'      => 'personal-branding-agency',
    'Ecommerce SEO'          => 'ecommerce-seo-agency',
    'Graphic Designing'      => 'graphic-designing-agency',
    'Social media Marketing' => 'social-media-marketing-agency',
];

$servicePageSlugs = [
    'Digital Marketing'      => 'digital-marketing-agency-chennai.html',
    'SEO'                    => 'seo-service.html',
    'Website Development'    => 'web-development.html',
    'Youtube SEO'            => 'youtube-seo-agency.html',
    'Performance Marketing'  => 'performance-marketing.html',
    'Personal Branding'      => 'personal-branding-service.html',
    'Ecommerce SEO'          => 'ecommerce-seo-service.html',
    'Graphic Designing'      => 'graphic-designing-service.html',
    'Social media Marketing' => 'social-media-optimization-agency.html',
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

// ─── Deterministic helpers ─────────────────────────────────────
function getDeterministicIndex(string $seed, int $max): int
{
    return abs(crc32($seed)) % $max;
}

/**
 * pick() — deterministically select one option from a pool using its own
 * independent seed. Always pass a seed suffix unique to the FIELD being
 * varied (e.g. 'meta', 'title', 'hero', 'cta', 'review', 'why', 'svcin',
 * 'procin', 'offcanvas', 'ctasub') so that fields rotate on independent
 * cycles. That's what produces combinatorial variation instead of every
 * field just mirroring the same index.
 */
function pick(string $seed, array $options)
{
    if (empty($options)) return '';
    $idx = getDeterministicIndex($seed, count($options));
    return $options[$idx];
}

// ─── Content data per service ─────────────────────────────────
function getServiceContent(string $service, string $city): array
{
    $seed = $service . '|' . $city;

    // ── Shared reviewer pool ──────────────────────────────────
    $reviewers = [
        'Priya Sharma', 'Ramesh Kumar', 'Anitha Devi', 'Karthik Raj', 'Sundar Pandi',
        'Meenakshi Sundaram', 'Vijay Anand', 'Lakshmi Narayanan', 'Deepa Rajan',
        'Murugan Selvaraj', 'Bharathi Krishnan', 'Selvam Arumugam', 'Kavya Venkatesh',
        'Arun Prakash', 'Nithya Mohan', 'Arjun Balasubramaniam', 'Sowmiya Ravi',
        'Dinesh Chandrasekaran', 'Preethi Nagarajan', 'Sathish Babu',
    ];
    $reviewer = pick($seed . 'reviewer', $reviewers);

    // ════════════════════════════════════════════════════════════════
    // VARIATION POOLS — each field now has 6–7 (hero/cta/review) or
    // 3 (meta/title/intros) variants per service, up from 1–4.
    // ════════════════════════════════════════════════════════════════

    // ── Hero sub-text pool (7 per service, was 4) ──────────────
    $heroSubtexts = [
        'Digital Marketing' => [
            "Grow your {$city} business with data-driven digital marketing. From SEO and social media to paid ads and content strategy, we build full-funnel campaigns that deliver measurable ROI. Trusted by 200+ businesses across South India.",
            "Your {$city} brand deserves to be seen. We craft performance-led digital marketing campaigns — search, social, email, and beyond — that turn browsers into buyers and followers into loyal customers.",
            "In a competitive {$city} market, standing out online requires more than just presence. Our integrated digital marketing solutions combine strategy, creativity, and analytics to accelerate growth for businesses of every size.",
            "From startups to established enterprises in {$city}, VG Digital Falcon delivers digital marketing that moves the needle. Transparent reporting, dedicated teams, and strategies built around your specific growth goals.",
            "Looking to scale your {$city} business this year? Our digital marketing team builds campaigns around your actual sales goals — not vanity metrics — across search, social, and paid media.",
            "{$city} customers are searching, scrolling, and comparing online right now. We make sure your brand is the one they find, trust, and buy from.",
            "Whether you're a {$city} startup or an established brand, our digital marketing programmes are built to scale with you — starting lean, scaling smart.",
        ],
        'SEO' => [
            "Dominate Google search results in {$city} with our proven SEO strategies. We drive consistent organic traffic, quality leads, and long-term business growth through white-hat SEO techniques.",
            "Stop losing customers to competitors ranking above you. Our {$city}-focused SEO programmes combine technical mastery, authoritative content, and strategic link building to push your business to page one — and keep it there.",
            "Organic search is the highest-ROI digital channel for {$city} businesses. We build SEO foundations that compound over time, turning your website into a 24/7 lead-generation machine without paid ad spend.",
            "From local map pack dominance to national keyword rankings, VG Digital Falcon's SEO team has the expertise to help {$city} businesses capture the search traffic their competitors are missing.",
            "{$city} businesses that rank on page 1 capture the majority of clicks — and the customers who never see page 2. We build the SEO foundation to get and keep you there.",
            "Think of SEO as compounding interest for your {$city} business: every optimised page keeps working for you long after the work is done.",
            "We've helped {$city} businesses move from invisible to unavoidable on Google — through technical fixes, content strategy, and consistent authority building.",
        ],
        'Website Development' => [
            "Transform your online presence with {$city}'s premier website development experts. We build custom websites, e-commerce platforms, and web applications that drive business growth for {$city} businesses.",
            "A great website is your most powerful sales asset. We design and develop fast, secure, mobile-first websites for {$city} businesses that convert visitors into customers from the very first visit.",
            "Your website should work as hard as you do. Our {$city} development team builds performance-optimised, SEO-ready websites across WordPress, Shopify, React, and custom stacks tailored to your business needs.",
            "Beyond aesthetics — we engineer websites with purpose. Every {$city} project we deliver is strategically designed to maximise user experience, search visibility, and conversion rate.",
            "Your {$city} website is open 24/7 — make sure it's actually selling while you sleep. We build sites engineered around conversions, not just visuals.",
            "{$city} businesses lose customers to slow, confusing websites every day. We fix that with fast, clear, mobile-first builds that guide visitors to action.",
            "A website redesign shouldn't be a gamble. Our {$city} clients get a structured process — research, design, build, test — so the final result performs from day one.",
        ],
        'Youtube SEO' => [
            "Grow your YouTube channel and business revenue with {$city}'s top YouTube SEO experts. We optimise your channel to rank higher, attract more subscribers, and convert viewers into customers.",
            "YouTube is the world's second-largest search engine — and most {$city} businesses are barely scratching the surface. We unlock its full potential with data-backed channel strategies and video SEO that gets real results.",
            "From zero to thousands of subscribers, VG Digital Falcon's YouTube SEO programme helps {$city} creators and brands build audiences that translate into tangible business growth.",
            "Stop uploading videos and hoping. We give your {$city} YouTube channel a scientific growth strategy — keyword research, optimised metadata, CTR-driven thumbnails, and distribution tactics that compound over time.",
            "{$city} creators and brands are sitting on an underused growth channel. We turn YouTube into a predictable source of views, subscribers, and revenue.",
            "Posting videos isn't a strategy — ranking for what {$city} audiences actually search is. We build that system video by video.",
            "From thumbnail psychology to retention-optimised scripts, we apply real YouTube SEO science to grow {$city} channels methodically, not by luck.",
        ],
    ];

    // ── CTA heading pool (7 per service, was 4) ────────────────
    $ctaHeadings = [
        'Digital Marketing' => [
            "Ready to Dominate Digital Marketing in {$city}?",
            "Let's Grow Your {$city} Business Online Together",
            "Transform Your {$city} Brand's Digital Presence Today",
            "Your {$city} Competitors Are Already Online — Are You?",
            "Let's Build a Digital Marketing Engine for Your {$city} Business",
            "Stop Guessing — Start Growing Your {$city} Business Online",
            "{$city} Businesses Are Moving Online Fast. Let's Get You Ahead.",
        ],
        'SEO' => [
            "Ready to Rank #1 on Google in {$city}?",
            "Let's Put Your {$city} Business on Page One",
            "Start Winning Organic Traffic in {$city} Today",
            "Your Ideal {$city} Customers Are Searching — Will They Find You?",
            "Let's Get Your {$city} Business Found First on Google",
            "Outrank Your {$city} Competitors — Starting This Month",
            "Turn Search Traffic Into {$city} Customers, Starting Now",
        ],
        'Website Development' => [
            "Ready to Build Your Dream Website in {$city}?",
            "Let's Launch Your {$city} Business Online with Impact",
            "Your {$city} Business Deserves a Website That Converts",
            "Bring Your {$city} Brand to Life with a World-Class Website",
            "Let's Build a {$city} Website That Actually Converts",
            "Your {$city} Business Website Should Be Working Harder",
            "Time for a {$city} Website That Matches Your Ambitions",
        ],
        'Youtube SEO' => [
            "Ready to Grow Your YouTube Channel in {$city}?",
            "Let's Turn Your {$city} Channel into a Subscriber Magnet",
            "Start Getting More Views for Your {$city} Business on YouTube",
            "Your {$city} YouTube Growth Journey Starts Here",
            "Let's Build a Real Growth Engine for Your {$city} Channel",
            "Stop Posting Blind — Grow Your {$city} Channel Strategically",
            "Your {$city} YouTube Channel's Next Chapter Starts Here",
        ],
    ];

    // ── CTA subtext pool (3 per service — NEW) ─────────────────
    $ctaSubtexts = [
        'Digital Marketing' => [
            "Get a free 30-minute strategy session with our {$city} digital marketing experts. Walk away with a clear, actionable roadmap — no obligations.",
            "Book a no-pressure consultation with our {$city} team and get a custom digital marketing plan built around your actual business goals.",
            "Tell us about your {$city} business and we'll show you exactly where the quickest digital marketing wins are — free of charge.",
        ],
        'SEO' => [
            "Get a free SEO audit and keyword report for your {$city} business. See exactly where you stand and how to outrank your competitors — zero obligations.",
            "Book a free ranking review with our {$city} SEO team — we'll show you precisely what's holding your site back from page one.",
            "Find out where your {$city} business stands on Google today, and what it would take to move up — completely free, no strings attached.",
        ],
        'Website Development' => [
            "Schedule a free website strategy session with our {$city}-based development experts. Get a customised website plan — no obligations, just actionable insights.",
            "Talk to our {$city} development team about your project and get a clear scope, timeline, and quote — with zero pressure to commit.",
            "Get a free teardown of your current {$city} website highlighting exactly what's costing you conversions, and how to fix it.",
        ],
        'Youtube SEO' => [
            "Get a free YouTube channel audit from our {$city} experts. We'll show you exactly what's holding back your growth and how to fix it — no obligations.",
            "Book a free channel review with our {$city} YouTube SEO team and walk away with three concrete improvements you can make this week.",
            "Talk to our {$city} team about your channel goals and get a tailored growth plan — completely free, no commitment required.",
        ],
    ];

    // ── Review text pool (7 per service, was 4) ────────────────
    $reviewTexts = [
        'Digital Marketing' => [
            "VG Digital Falcon transformed our online presence in {$city}. Within 4 months our organic traffic doubled and our Google Ads ROAS hit 4.8×. Highly professional team.",
            "Best digital marketing agency we have worked with. Our {$city} store's monthly leads increased from 30 to 190 in just 5 months. Transparent reporting and genuine care for results.",
            "The VG Digital Falcon team understands {$city}'s market deeply. Our campaign ROI exceeded projections in every single month. We renewed for a second year without hesitation.",
            "Switching to VG Digital Falcon was the best business decision we made this year. Our social media engagement in {$city} grew 6× and conversions improved dramatically.",
            "We came to VG Digital Falcon after two failed agency attempts. Within 3 months our {$city} business had its first real, trackable pipeline of leads from digital marketing.",
            "What stood out working with VG Digital Falcon was the clarity — every rupee spent on our {$city} campaigns was explained, tracked, and justified by results.",
            "Our {$city} brand finally has a consistent digital presence thanks to VG Digital Falcon. Engagement, leads, and repeat customers have all grown steadily.",
        ],
        'SEO' => [
            "Our {$city} business moved from page 3 to position 2 for our main keyword in just 5 months. Organic leads have doubled since.",
            "VG Digital Falcon's SEO work is meticulous and results-driven. We now rank #1 in {$city} for five high-value keywords and inbound enquiries have tripled.",
            "Before VG Digital Falcon, our {$city} website was invisible on Google. Today we rank on page 1 for 12 target keywords. The SEO team is exceptional.",
            "Honest, transparent, and results-focused. Our {$city} business's organic traffic grew 340% in 8 months thanks to VG Digital Falcon's SEO expertise.",
            "We didn't believe SEO could work for a niche {$city} business like ours — VG Digital Falcon proved us wrong within 6 months of consistent ranking growth.",
            "The technical SEO fixes alone made a visible difference to our {$city} site's speed and rankings. Content and links built on top of that sealed the deal.",
            "VG Digital Falcon set realistic expectations from day one and then delivered ahead of schedule for our {$city} business. Refreshing to work with an honest SEO team.",
        ],
        'Website Development' => [
            "VG Digital Falcon built an incredible e-commerce website for our {$city} store. Our online sales grew by 300% within 3 months of launch. Exceptional work!",
            "The team delivered our {$city} website ahead of schedule and it performs beautifully — fast, mobile-friendly, and already ranking on Google. Brilliant execution.",
            "We gave VG Digital Falcon a complex brief for our {$city} business portal. They delivered every feature, the UI is stunning, and load time is under 2 seconds. Five stars.",
            "Our old {$city} website was embarrassing. The new one from VG Digital Falcon is professional, fast, and our conversion rate jumped from 1.2% to 4.7% within the first month.",
            "Our old site embarrassed us in front of {$city} clients. The new one from VG Digital Falcon is the first thing we now proudly share with prospects.",
            "From wireframes to launch, the VG Digital Falcon team kept our {$city} project on schedule and explained every decision clearly. Zero surprises.",
            "Page speed, mobile experience, and lead capture all improved dramatically after VG Digital Falcon rebuilt our {$city} business website.",
        ],
        'Youtube SEO' => [
            "My {$city} cooking channel went from 500 to 18,000 subscribers in 6 months with VG Digital Falcon's YouTube SEO strategy. Absolutely game-changing!",
            "VG Digital Falcon helped our {$city} business YouTube channel reach 50,000 views per month in under a year. The thumbnail and title strategy alone doubled our CTR.",
            "We were stuck at 1,200 subscribers for over a year. VG Digital Falcon overhauled our {$city} channel strategy and we hit 25,000 subs in 4 months. Incredible team.",
            "The YouTube SEO team at VG Digital Falcon is genuinely expert. Our videos now rank on both YouTube and Google for competitive {$city} industry keywords. Revenue has tripled.",
            "Our {$city} channel had good content but zero strategy. VG Digital Falcon gave us the missing keyword and thumbnail system — subscribers followed.",
            "We finally understand why some of our {$city} videos work and others don't, thanks to VG Digital Falcon's analytics breakdown and content planning.",
            "Watch time on our {$city} channel nearly doubled after VG Digital Falcon restructured our video intros and chapter strategy.",
        ],
    ];

    // ── META_DESCRIPTION pool (3 per service — NEW) ────────────
    $metaDescriptions = [
        'Digital Marketing' => [
            "Looking for the top Digital Marketing Agency in {$city}? VG Digital Falcon delivers data-driven digital marketing solutions that grow your brand visibility, generate quality leads, and boost revenue for {$city} businesses.",
            "VG Digital Falcon is {$city}'s trusted digital marketing partner — SEO, paid ads, social media, and content strategy combined into full-funnel campaigns built for measurable growth.",
            "Searching for a digital marketing agency in {$city} that delivers real ROI? We plan, execute, and report on every campaign with full transparency, helping {$city} brands grow online sustainably.",
        ],
        'SEO' => [
            "Rank #1 on Google with VG Digital Falcon — the best SEO Agency in {$city}. We offer technical SEO, local SEO, content marketing, and link building that drives real organic growth.",
            "Looking for an SEO agency in {$city} that delivers measurable rankings? VG Digital Falcon combines technical SEO, content, and authority building for lasting page-one results.",
            "VG Digital Falcon helps {$city} businesses capture more organic traffic through ethical, white-hat SEO — local search, technical fixes, and strategic content, all in one programme.",
        ],
        'Website Development' => [
            "VG Digital Falcon is the top Website Development Agency in {$city} offering custom website design, e-commerce solutions, web applications, and mobile-responsive development services.",
            "Need a website built for conversions in {$city}? VG Digital Falcon designs and develops fast, secure, mobile-first websites for businesses of every size.",
            "VG Digital Falcon delivers custom website development in {$city} — from corporate sites to e-commerce platforms — engineered for speed, SEO, and conversions.",
        ],
        'Youtube SEO' => [
            "Grow your YouTube channel with VG Digital Falcon, the leading YouTube SEO Agency in {$city}. We optimise titles, descriptions, thumbnails, and tags to maximise views and subscribers.",
            "Looking for a YouTube SEO agency in {$city}? VG Digital Falcon builds data-backed channel strategies that turn views into subscribers and subscribers into customers.",
            "VG Digital Falcon helps {$city} creators and brands grow on YouTube with keyword research, thumbnail strategy, and retention-focused content planning.",
        ],
    ];

    // ── PAGE_TITLE pool (3 per service — NEW) ──────────────────
    $pageTitles = [
        'Digital Marketing' => [
            "Top Digital Marketing Agency in {$city} - VG Digital Falcon",
            "Digital Marketing Company in {$city} | VG Digital Falcon",
            "{$city} Digital Marketing Agency — Grow Online with VG Digital Falcon",
        ],
        'SEO' => [
            "Best SEO Agency in {$city} | VG Digital Falcon",
            "SEO Services in {$city} - Rank Higher with VG Digital Falcon",
            "{$city} SEO Agency — Page-One Rankings with VG Digital Falcon",
        ],
        'Website Development' => [
            "Top Website Development Agency in {$city} - VG Digital Falcon",
            "Website Design & Development Company in {$city} | VG Digital Falcon",
            "{$city} Website Development Agency — Built to Convert | VG Digital Falcon",
        ],
        'Youtube SEO' => [
            "Best YouTube SEO Agency in {$city} | VG Digital Falcon",
            "YouTube SEO Services in {$city} - Grow Your Channel | VG Digital Falcon",
            "{$city} YouTube SEO Agency — Subscribers & Views | VG Digital Falcon",
        ],
    ];

    // ── WHY_CHOOSE_INTRO pool (3 per service — NEW) ────────────
    $whyChooseIntros = [
        'Digital Marketing' => [
            "We combine local market expertise in {$city} with proven digital strategies to deliver campaigns that convert. Here's why leading {$city} brands trust us:",
            "{$city} businesses don't need more marketing noise — they need a partner who turns strategy into revenue. Here's what that looks like in practice:",
            "Every {$city} market has its own rhythm, customers, and competitors. Here's how we tailor digital marketing around exactly that:",
        ],
        'SEO' => [
            "Our data-driven SEO approach has helped hundreds of South India businesses achieve first-page rankings. Here's why {$city} businesses choose us:",
            "{$city} businesses don't just need traffic — they need traffic that converts. Here's how our SEO programme delivers both:",
            "Ranking on Google is a long game, and {$city} businesses need a partner who plays it right. Here's what sets our approach apart:",
        ],
        'Website Development' => [
            "We combine technical expertise with strategic design thinking to create websites that not only look great but also deliver measurable business results. Here's what sets us apart in {$city}:",
            "{$city} businesses need websites that do more than look good — they need to convert. Here's how we make sure every project does both:",
            "Every {$city} project starts with a business goal, not a design trend. Here's how that shows up in the way we build websites:",
        ],
        'Youtube SEO' => [
            "YouTube is the world's second-largest search engine. We help {$city} businesses and creators unlock its full potential:",
            "{$city} creators don't need more uploads — they need a strategy that turns views into subscribers and subscribers into revenue. Here's ours:",
            "Growing on YouTube isn't luck — it's a system. Here's how we build that system for {$city} channels:",
        ],
    ];

    // ── SERVICES_INTRO pool (3 per service — NEW) ──────────────
    $servicesIntros = [
        'Digital Marketing' => [
            "As a full-service digital marketing agency in {$city}, we offer comprehensive solutions across all online channels.",
            "From first click to final sale, our {$city} digital marketing services cover the entire customer journey.",
            "Here's the full range of digital marketing services we run for {$city} businesses, all built to work together rather than in silos.",
        ],
        'SEO' => [
            "We offer a full suite of SEO services tailored to the {$city} market.",
            "Our {$city} SEO programme covers everything from technical fixes to content and links — built as one connected system, not isolated tactics.",
            "Here's how we structure SEO for {$city} businesses, covering every layer that influences your rankings.",
        ],
        'Website Development' => [
            "As a full-service website development agency in {$city}, we offer comprehensive solutions tailored to your business goals.",
            "From first build to long-term support, here's the full scope of website development services we offer {$city} businesses.",
            "Every {$city} website project draws from the same core set of services below, scoped to exactly what your business needs.",
        ],
        'Youtube SEO' => [
            "Complete YouTube growth services for creators and businesses in {$city}.",
            "Here's the full range of YouTube SEO and channel growth services we run for {$city} creators and brands.",
            "From channel setup to monetisation, here's how we support {$city} YouTube channels at every stage of growth.",
        ],
    ];

    // ── PROCESS_INTRO pool (3 per service — NEW) ───────────────
    $processIntros = [
        'Digital Marketing' => [
            "Our structured 6-phase digital marketing process ensures your {$city} brand achieves consistent, scalable growth online.",
            "Here's exactly how we take a {$city} brand from a digital marketing plan on paper to measurable monthly growth.",
            "No guesswork — our 6-step process for {$city} clients turns strategy into a repeatable system for growth.",
        ],
        'SEO' => [
            "Our structured SEO process delivers transparent, predictable results for your {$city} business.",
            "Here's the exact 6-step process we follow to take a {$city} website from invisible to ranking.",
            "SEO works best as a system, not a one-off task. Here's how we structure that system for {$city} businesses.",
        ],
        'Website Development' => [
            "We follow a structured, transparent 6-step development process that ensures your {$city} website is delivered on time, within budget, and exceeds expectations.",
            "Here's exactly how a {$city} website project moves from first conversation to live launch with us.",
            "Every {$city} website we build follows the same proven 6-step process, regardless of project size.",
        ],
        'Youtube SEO' => [
            "Our proven 6-step YouTube SEO process consistently grows subscribers and views for {$city} channels.",
            "Here's exactly how we take a {$city} YouTube channel from inconsistent uploads to a predictable growth system.",
            "Channel growth isn't random — it's a process. Here's the 6-step system we run for {$city} creators and brands.",
        ],
    ];

    // ── OFFCANVAS_DESC pool (3 per service — NEW) ──────────────
    $offcanvasDescs = [
        'Digital Marketing' => [
            "At VG Digital Falcon, we craft result-oriented digital marketing campaigns for {$city} businesses. Strategy, execution, and growth — all under one roof.",
            "VG Digital Falcon partners with {$city} businesses to plan, run, and optimise digital marketing campaigns that are accountable to real business outcomes.",
            "We help {$city} brands grow online through digital marketing that's built on data first, creativity second — never the other way around.",
        ],
        'SEO' => [
            "VG Digital Falcon helps {$city} businesses rank higher on Google with ethical, result-driven SEO strategies that stand the test of time.",
            "We build lasting organic growth for {$city} businesses through white-hat SEO — no shortcuts, no penalties, just sustainable rankings.",
            "VG Digital Falcon's SEO team turns {$city} websites into long-term organic traffic assets, one optimised page at a time.",
        ],
        'Website Development' => [
            "At VG Digital Falcon, we craft digital experiences that drive business growth. Our website development services combine cutting-edge technology with strategic design to create websites that convert visitors into customers.",
            "VG Digital Falcon builds websites for {$city} businesses that are fast, functional, and designed around a single goal: converting visitors.",
            "We treat every {$city} website as a business tool first and a design project second — built to perform, not just to look good.",
        ],
        'Youtube SEO' => [
            "VG Digital Falcon helps {$city} creators and businesses grow on YouTube through data-driven SEO, compelling thumbnails, and strategic content planning.",
            "We turn YouTube into a real growth channel for {$city} creators and brands — backed by keyword research, not guesswork.",
            "VG Digital Falcon's YouTube SEO programme gives {$city} channels a repeatable system for views, subscribers, and revenue.",
        ],
    ];

    switch ($service) {

        // ══════════════════════════════════════════════════════
        case 'Digital Marketing':
        // ══════════════════════════════════════════════════════
        $heroIdx   = getDeterministicIndex($seed . 'hero',   count($heroSubtexts['Digital Marketing']));
        $ctaIdx    = getDeterministicIndex($seed . 'cta',    count($ctaHeadings['Digital Marketing']));
        $reviewIdx = getDeterministicIndex($seed . 'review', count($reviewTexts['Digital Marketing']));
        return [
            'META_DESCRIPTION'  => pick($seed . 'meta',  $metaDescriptions['Digital Marketing']),
            'PAGE_TITLE'        => pick($seed . 'title', $pageTitles['Digital Marketing']),
            'SCHEMA_DESCRIPTION'=> "VG Digital Falcon is a leading digital marketing agency serving {$city} with SEO, social media, PPC, content marketing, and complete online branding solutions.",
            'HERO_H1'           => "#1 Digital Marketing Agency in {$city}",
            'HERO_SUBTEXT'      => $heroSubtexts['Digital Marketing'][$heroIdx],
            'HERO_CTA_TEXT'     => "Get Free Digital Marketing Audit",
            'FORM_TITLE'        => "Get Your Digital Marketing Plan!",
            'OFFCANVAS_DESC'    => pick($seed . 'offcanvas', $offcanvasDescs['Digital Marketing']),
            'WHY_CHOOSE_INTRO'  => pick($seed . 'why', $whyChooseIntros['Digital Marketing']),
            'BENEFIT1_TITLE'    => "Local Market Expertise",
            'BENEFIT1_DESC'     => "Deep understanding of {$city}'s consumer behaviour, seasonal trends, and competitive landscape ensures your campaigns resonate with the right audience.",
            'BENEFIT2_TITLE'    => "Full-Funnel Campaigns",
            'BENEFIT2_DESC'     => "We manage every touchpoint — awareness, consideration, and conversion — so your {$city} brand stays top-of-mind across all digital channels.",
            'BENEFIT3_TITLE'    => "Transparent Reporting",
            'BENEFIT3_DESC'     => "Weekly dashboards and monthly strategy calls keep you informed. No vanity metrics — only the KPIs that matter to your {$city} business.",
            'BENEFIT4_TITLE'    => "ROI-Focused Execution",
            'BENEFIT4_DESC'     => "Every rupee of your ad spend is tracked and optimised. Our average client in {$city} sees a 3–5× return on their digital marketing investment.",
            'BENEFIT5_TITLE'    => "Omni-Channel Presence",
            'BENEFIT5_DESC'     => "We synchronise your {$city} brand's message across Google, Meta, LinkedIn, YouTube, and email — creating a cohesive experience that builds trust at every touchpoint.",
            'BENEFIT6_TITLE'    => "Dedicated Account Team",
            'BENEFIT6_DESC'     => "A senior strategist, content specialist, and performance analyst are assigned exclusively to your {$city} account, ensuring continuity and deep brand understanding.",
            'BENEFIT7_TITLE'    => "Agile Campaign Management",
            'BENEFIT7_DESC'     => "We pivot strategies quickly based on real-time data, seasonal trends in {$city}, and platform algorithm updates — keeping your brand ahead of the curve.",
            'BENEFIT8_TITLE'    => "Conversion Rate Optimisation",
            'BENEFIT8_DESC'     => "We don't just drive traffic — we systematically improve the percentage of {$city} visitors who take action, maximising the value of every campaign rupee.",
            'PROCESS_INTRO'     => pick($seed . 'procin', $processIntros['Digital Marketing']),
            'STEP1_TITLE' => 'Discovery & Audit',        'STEP1_SUBTITLE' => 'Understanding Your Brand',
            'STEP1_INTRO' => 'We analyse your current digital footprint in '.$city,
            'STEP1_P1_LABEL' => 'Website Audit',         'STEP1_P1_DESC' => 'Technical health, speed & UX review',
            'STEP1_P2_LABEL' => 'Competitor Analysis',   'STEP1_P2_DESC' => 'Benchmark against top '.$city.' competitors',
            'STEP1_P3_LABEL' => 'Audience Research',     'STEP1_P3_DESC' => 'Buyer personas & intent mapping',
            'STEP1_P4_LABEL' => 'Goal Setting',          'STEP1_P4_DESC' => 'KPIs aligned with business targets',
            'STEP2_TITLE' => 'Strategy Development',     'STEP2_SUBTITLE' => 'Blueprint for Growth',
            'STEP2_INTRO' => 'Building your custom digital roadmap for '.$city,
            'STEP2_P1_LABEL' => 'Channel Mix',           'STEP2_P1_DESC' => 'SEO, PPC, social, email strategy',
            'STEP2_P2_LABEL' => 'Content Calendar',      'STEP2_P2_DESC' => 'Topic clusters & publishing schedule',
            'STEP2_P3_LABEL' => 'Budget Allocation',     'STEP2_P3_DESC' => 'Spend distribution for maximum ROI',
            'STEP2_P4_LABEL' => 'Funnel Design',         'STEP2_P4_DESC' => 'TOFU → MOFU → BOFU mapping',
            'STEP3_TITLE' => 'Content Creation',         'STEP3_SUBTITLE' => 'Compelling Brand Stories',
            'STEP3_INTRO' => 'Creating content that converts '.$city.' audiences',
            'STEP3_P1_LABEL' => 'SEO Articles',          'STEP3_P1_DESC' => 'Long-form keyword-optimised blogs',
            'STEP3_P2_LABEL' => 'Social Assets',         'STEP3_P2_DESC' => 'Graphics, reels & infographics',
            'STEP3_P3_LABEL' => 'Ad Creatives',          'STEP3_P3_DESC' => 'High-CTR copy & visuals for PPC',
            'STEP3_P4_LABEL' => 'Video Scripts',         'STEP3_P4_DESC' => 'YouTube & short-form video content',
            'STEP4_TITLE' => 'Campaign Launch',          'STEP4_SUBTITLE' => 'Going Live',
            'STEP4_INTRO' => 'Executing across all channels in '.$city,
            'STEP4_P1_LABEL' => 'SEO On-Page',           'STEP4_P1_DESC' => 'Meta, schema, internal linking',
            'STEP4_P2_LABEL' => 'Paid Ads Setup',        'STEP4_P2_DESC' => 'Google, Meta & YouTube campaigns',
            'STEP4_P3_LABEL' => 'Social Scheduling',     'STEP4_P3_DESC' => 'Consistent posting & engagement',
            'STEP4_P4_LABEL' => 'Email Flows',           'STEP4_P4_DESC' => 'Automated nurture sequences',
            'STEP5_TITLE' => 'Monitoring & Optimisation','STEP5_SUBTITLE' => 'Data-Driven Refinements',
            'STEP5_INTRO' => 'Continuous improvement for better results in '.$city,
            'STEP5_P1_LABEL' => 'Analytics Review',      'STEP5_P1_DESC' => 'Weekly GA4 & Search Console check',
            'STEP5_P2_LABEL' => 'A/B Testing',           'STEP5_P2_DESC' => 'Creative & landing page experiments',
            'STEP5_P3_LABEL' => 'Bid Management',        'STEP5_P3_DESC' => 'Real-time ad spend optimisation',
            'STEP5_P4_LABEL' => 'Rank Tracking',         'STEP5_P4_DESC' => 'Keyword position monitoring',
            'STEP6_TITLE' => 'Reporting & Scale',        'STEP6_SUBTITLE' => 'Growth Partnerships',
            'STEP6_INTRO' => 'Transparent updates & scaling wins for '.$city.' businesses',
            'STEP6_P1_LABEL' => 'Monthly Reports',       'STEP6_P1_DESC' => 'Custom dashboards with insights',
            'STEP6_P2_LABEL' => 'Strategy Reviews',      'STEP6_P2_DESC' => 'Quarterly planning sessions',
            'STEP6_P3_LABEL' => 'Scaling Budgets',       'STEP6_P3_DESC' => 'Reinvesting in winning channels',
            'STEP6_P4_LABEL' => 'New Opportunity Hunt',  'STEP6_P4_DESC' => 'Emerging platforms & trends',
            'CTA_HEADING'    => $ctaHeadings['Digital Marketing'][$ctaIdx],
            'CTA_SUBTEXT'    => pick($seed . 'ctasub', $ctaSubtexts['Digital Marketing']),
            'WHATSAPP_LINK'  => "https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20need%20Digital%20Marketing%20services%20for%20my%20business%20in%20{$city}.",
            'SERVICES_INTRO' => pick($seed . 'svcin', $servicesIntros['Digital Marketing']),
            'SVC1_TITLE' => '🔍 Search Engine Optimisation',
            'SVC1_DESC'  => "Rank #1 on Google for your most valuable {$city} keywords. Our technical SEO, content strategy, and link building programmes drive compounding organic traffic.",
            'SVC1_P1' => 'Local SEO for '.$city.' searches',  'SVC1_P2' => 'Technical SEO audits & fixes',
            'SVC1_P3' => 'Content gap analysis',               'SVC1_P4' => 'Authority link building',
            'SVC2_TITLE' => '📣 Social Media Marketing',
            'SVC2_DESC'  => "Build a loyal community on Facebook, Instagram, LinkedIn, and YouTube. We create scroll-stopping content and manage your brand voice across every platform in {$city}.",
            'SVC2_P1' => 'Platform-specific content strategy',  'SVC2_P2' => 'Influencer collaborations in '.$city,
            'SVC2_P3' => 'Paid social campaigns',               'SVC2_P4' => 'Community management & DM responses',
            'SVC3_TITLE' => '💰 Pay-Per-Click Advertising',
            'SVC3_DESC'  => "High-intent Google Ads, Meta Ads, and YouTube campaigns that bring in qualified leads for your {$city} business. Every rupee tracked, every conversion counted.",
            'SVC3_P1' => 'Google Search & Display Ads',         'SVC3_P2' => 'Meta (Facebook & Instagram) Ads',
            'SVC3_P3' => 'Remarketing & retargeting',           'SVC3_P4' => 'Shopping & YouTube Ads',
            'SVC4_TITLE' => '✍️ Content Marketing',
            'SVC4_DESC'  => "Build authority in {$city}'s market with blogs, videos, case studies, and infographics that educate, engage, and convert your ideal customers.",
            'SVC4_P1' => 'SEO-optimised blog writing',          'SVC4_P2' => 'Video production & scripting',
            'SVC4_P3' => 'Infographic design',                  'SVC4_P4' => 'Case studies & white papers',
            'SVC5_TITLE' => '📧 Email Marketing Automation',
            'SVC5_DESC'  => "Nurture leads and retain customers with personalised email sequences. We set up automated workflows that deliver the right message at the right time to {$city} audiences.",
            'SVC5_P1' => 'Welcome & onboarding flows',          'SVC5_P2' => 'Promotional campaigns & offers',
            'SVC5_P3' => 'Abandoned cart recovery',             'SVC5_P4' => 'Loyalty & re-engagement campaigns',
            'SVC6_TITLE' => '📊 Analytics & Reporting',
            'SVC6_DESC'  => "Custom dashboards that translate raw data into clear business insights. Know exactly what's working in your {$city} market and where to invest next.",
            'SVC6_P1' => 'GA4 & Search Console setup',          'SVC6_P2' => 'Custom Looker Studio dashboards',
            'SVC6_P3' => 'Conversion funnel analysis',          'SVC6_P4' => 'Monthly performance reports',
            'SVC7_TITLE' => '🎯 Conversion Rate Optimisation',
            'SVC7_DESC'  => "We test and refine every element of your {$city} website and landing pages to turn more visitors into leads and sales — maximising returns from existing traffic.",
            'SVC7_P1' => 'Landing page A/B testing',            'SVC7_P2' => 'Heatmap & session recording analysis',
            'SVC7_P3' => 'Form & checkout optimisation',        'SVC7_P4' => 'Copy & CTA experiments',
            'SVC8_TITLE' => '🤝 Influencer Marketing',
            'SVC8_DESC'  => "Connect with credible voices in {$city} and your niche. We identify, vet, and manage influencer collaborations that build authentic brand awareness.",
            'SVC8_P1' => 'Influencer identification & vetting', 'SVC8_P2' => 'Campaign brief & content approval',
            'SVC8_P3' => 'Performance tracking & ROI reporting', 'SVC8_P4' => 'Long-term ambassador programmes',
            'SVC9_TITLE' => '🌐 Online Reputation Management',
            'SVC9_DESC'  => "Protect and enhance your {$city} brand's online reputation. We monitor reviews, respond strategically, and build positive digital PR that earns customer trust.",
            'SVC9_P1' => 'Review monitoring & response',        'SVC9_P2' => 'Google Business Profile management',
            'SVC9_P3' => 'Negative content suppression',        'SVC9_P4' => 'Brand mention tracking',
            'FAQ1_Q' => "Why does my {$city} business need a digital marketing agency?",
            'FAQ1_A' => "The {$city} market is competitive and increasingly digital. An agency brings specialised skills — SEO, PPC, content, social — that are expensive to hire in-house. We deliver faster results with a proven system already tested across South India businesses.",
            'FAQ2_Q' => "How long before I see results from digital marketing in {$city}?",
            'FAQ2_A' => "Paid ads can deliver leads within 48 hours. SEO typically shows meaningful ranking improvements in 3–6 months. Social media builds momentum over 4–8 weeks. We set realistic timelines during onboarding so you know exactly what to expect.",
            'FAQ3_Q' => "What is the minimum budget for digital marketing in {$city}?",
            'FAQ3_A' => "We work with businesses at various stages. A starter digital marketing retainer begins at ₹15,000/month. Ad spend budgets are separate and we recommend a minimum of ₹10,000/month for paid campaigns to see meaningful volume in {$city}.",
            'FAQ4_Q' => "Do you specialise in any specific industries in {$city}?",
            'FAQ4_A' => "We have delivered results across retail, real estate, healthcare, education, hospitality, and manufacturing sectors across South India. Our team researches your specific industry before crafting a custom strategy.",
            'FAQ5_Q' => "How do you measure the success of digital marketing campaigns?",
            'FAQ5_A' => "We track leads generated, cost per lead, organic traffic growth, engagement rate, and ultimately revenue impact. Every campaign has clearly defined KPIs set at the beginning, and we report against them monthly.",
            'FAQ6_Q' => "Do you offer a free audit for {$city} businesses?",
            'FAQ6_A' => "Yes. Every new enquiry from {$city} receives a complimentary digital marketing audit covering your website health, social media presence, Google Ads account (if applicable), and top competitor rankings. Book your free audit today.",
            'FAQ7_Q' => "Can you manage both organic and paid marketing for my {$city} business?",
            'FAQ7_A' => "Absolutely. Our integrated approach means your SEO, content, social media, and paid ads teams work together from a single strategy, eliminating silos and maximising the synergy between organic and paid channels.",
            'FAQ8_Q' => "What makes VG Digital Falcon different from other digital marketing agencies in {$city}?",
            'FAQ8_A' => "We combine deep local market knowledge of {$city} with enterprise-level digital marketing capabilities. Unlike generalist agencies, every client gets a dedicated specialist team, transparent reporting, and strategies built around real business outcomes — not vanity metrics.",
            'REVIEW_AUTHOR' => $reviewer,
            'REVIEW_TEXT'   => $reviewTexts['Digital Marketing'][$reviewIdx],
        ];

        // ══════════════════════════════════════════════════════
        case 'Website Development':
        // ══════════════════════════════════════════════════════
        $heroIdx   = getDeterministicIndex($seed . 'hero',   count($heroSubtexts['Website Development']));
        $ctaIdx    = getDeterministicIndex($seed . 'cta',    count($ctaHeadings['Website Development']));
        $reviewIdx = getDeterministicIndex($seed . 'review', count($reviewTexts['Website Development']));
        return [
            'META_DESCRIPTION'  => pick($seed . 'meta',  $metaDescriptions['Website Development']),
            'PAGE_TITLE'        => pick($seed . 'title', $pageTitles['Website Development']),
            'SCHEMA_DESCRIPTION'=> "VG Digital Falcon is a leading website development agency in {$city} offering custom website design, e-commerce solutions, web applications, and responsive development services.",
            'HERO_H1'           => "#1 Website Development Agency in {$city}",
            'HERO_SUBTEXT'      => $heroSubtexts['Website Development'][$heroIdx],
            'HERO_CTA_TEXT'     => "Get Free Website Consultation",
            'FORM_TITLE'        => "Get Your Website Quote Now!",
            'OFFCANVAS_DESC'    => pick($seed . 'offcanvas', $offcanvasDescs['Website Development']),
            'WHY_CHOOSE_INTRO'  => pick($seed . 'why', $whyChooseIntros['Website Development']),
            'BENEFIT1_TITLE'    => "Custom Development",
            'BENEFIT1_DESC'     => "Every website is custom-built from scratch to perfectly match your brand identity, business needs, and target audience in {$city}'s competitive market.",
            'BENEFIT2_TITLE'    => "Multi-Platform Expertise",
            'BENEFIT2_DESC'     => "From WordPress and Shopify to React and Laravel, we have expertise across all major platforms to deliver the perfect solution for your {$city} business.",
            'BENEFIT3_TITLE'    => "E-commerce Specialists",
            'BENEFIT3_DESC'     => "We build powerful e-commerce websites with secure payment gateways, inventory management, and seamless user experiences that convert {$city} visitors into customers.",
            'BENEFIT4_TITLE'    => "Performance Optimisation",
            'BENEFIT4_DESC'     => "Our websites are optimised for speed, SEO, and mobile responsiveness. We ensure fast loading times and excellent user experience across all devices for your {$city} audience.",
            'BENEFIT5_TITLE'    => "SEO-Ready Architecture",
            'BENEFIT5_DESC'     => "Every website we build in {$city} is structured with clean code, semantic HTML, fast load speeds, and proper schema markup — giving your site the best possible foundation to rank on Google.",
            'BENEFIT6_TITLE'    => "Post-Launch Support",
            'BENEFIT6_DESC'     => "Our relationship doesn't end at launch. We provide dedicated post-launch support, security monitoring, and performance optimisation to keep your {$city} website running flawlessly.",
            'BENEFIT7_TITLE'    => "Scalable Technology Stack",
            'BENEFIT7_DESC'     => "We build with your future in mind. Whether you grow from 100 to 100,000 visitors in {$city}, our scalable architecture handles the load without expensive rebuilds.",
            'BENEFIT8_TITLE'    => "Conversion-Focused Design",
            'BENEFIT8_DESC'     => "Beautiful design alone doesn't pay bills. We engineer every {$city} website with strategic layouts, trust signals, and CTAs that guide visitors toward taking action.",
            'PROCESS_INTRO'     => pick($seed . 'procin', $processIntros['Website Development']),
            'STEP1_TITLE' => 'Discovery & Planning',   'STEP1_SUBTITLE' => 'Understanding Your Vision',
            'STEP1_INTRO' => 'We start by understanding your '.$city.' business goals & requirements',
            'STEP1_P1_LABEL' => 'Requirements Gathering','STEP1_P1_DESC' => 'Detailed project scope and objectives',
            'STEP1_P2_LABEL' => 'Audience Analysis',    'STEP1_P2_DESC' => 'Understanding your '.$city.' customers',
            'STEP1_P3_LABEL' => 'Competitor Research',  'STEP1_P3_DESC' => 'Analysing industry standards in '.$city,
            'STEP1_P4_LABEL' => 'Technology Selection', 'STEP1_P4_DESC' => 'Choosing the right platform & stack',
            'STEP2_TITLE' => 'UI/UX Design',           'STEP2_SUBTITLE' => 'Creating Visual Excellence',
            'STEP2_INTRO' => 'Designing intuitive user experiences for '.$city.' visitors',
            'STEP2_P1_LABEL' => 'Wireframing',          'STEP2_P1_DESC' => 'Structural layout planning',
            'STEP2_P2_LABEL' => 'Visual Design',        'STEP2_P2_DESC' => 'Creating brand-aligned interfaces',
            'STEP2_P3_LABEL' => 'User Flow Design',     'STEP2_P3_DESC' => 'Optimising navigation paths',
            'STEP2_P4_LABEL' => 'Mobile-First Design',  'STEP2_P4_DESC' => 'Responsive design approach',
            'STEP3_TITLE' => 'Development',            'STEP3_SUBTITLE' => 'Building Your Digital Solution',
            'STEP3_INTRO' => 'Turning designs into functional, high-performance code',
            'STEP3_P1_LABEL' => 'Frontend Development', 'STEP3_P1_DESC' => 'HTML, CSS, JavaScript implementation',
            'STEP3_P2_LABEL' => 'Backend Development',  'STEP3_P2_DESC' => 'Server-side programming & APIs',
            'STEP3_P3_LABEL' => 'Database Integration', 'STEP3_P3_DESC' => 'Data management & storage systems',
            'STEP3_P4_LABEL' => 'Third-Party APIs',     'STEP3_P4_DESC' => 'Payment, CRM & tool integrations',
            'STEP4_TITLE' => 'Testing & QA',           'STEP4_SUBTITLE' => 'Ensuring Perfection',
            'STEP4_INTRO' => 'Rigorous testing for flawless performance before launch',
            'STEP4_P1_LABEL' => 'Functional Testing',   'STEP4_P1_DESC' => 'Ensuring all features work correctly',
            'STEP4_P2_LABEL' => 'Cross-Browser Testing','STEP4_P2_DESC' => 'Compatibility across all browsers',
            'STEP4_P3_LABEL' => 'Mobile Responsiveness','STEP4_P3_DESC' => 'Testing on all device sizes',
            'STEP4_P4_LABEL' => 'Performance Testing',  'STEP4_P4_DESC' => 'Speed, load & stress testing',
            'STEP5_TITLE' => 'Deployment & Launch',    'STEP5_SUBTITLE' => 'Going Live Successfully',
            'STEP5_INTRO' => 'Launching your '.$city.' website to the world',
            'STEP5_P1_LABEL' => 'Server Configuration', 'STEP5_P1_DESC' => 'Setting up hosting environment',
            'STEP5_P2_LABEL' => 'Domain & SSL Setup',   'STEP5_P2_DESC' => 'Security certificate configuration',
            'STEP5_P3_LABEL' => 'Content Migration',    'STEP5_P3_DESC' => 'Transferring existing content safely',
            'STEP5_P4_LABEL' => 'Pre-Launch Checklist', 'STEP5_P4_DESC' => 'Final verification & sign-off',
            'STEP6_TITLE' => 'Maintenance & Support',  'STEP6_SUBTITLE' => 'Long-Term Partnership',
            'STEP6_INTRO' => 'Ongoing support for continued success in '.$city,
            'STEP6_P1_LABEL' => 'Regular Updates',       'STEP6_P1_DESC' => 'Software, plugins & security updates',
            'STEP6_P2_LABEL' => 'Performance Monitoring','STEP6_P2_DESC' => 'Uptime & speed continuous monitoring',
            'STEP6_P3_LABEL' => 'Technical Support',     'STEP6_P3_DESC' => 'Priority assistance when needed',
            'STEP6_P4_LABEL' => 'Content Management',    'STEP6_P4_DESC' => 'Regular content & image updates',
            'CTA_HEADING'    => $ctaHeadings['Website Development'][$ctaIdx],
            'CTA_SUBTEXT'    => pick($seed . 'ctasub', $ctaSubtexts['Website Development']),
            'WHATSAPP_LINK'  => "https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I'm%20interested%20in%20website%20development%20services%20for%20my%20business%20in%20{$city}.",
            'SERVICES_INTRO' => pick($seed . 'svcin', $servicesIntros['Website Development']),
            'SVC1_TITLE' => '🛒 E-commerce Development',
            'SVC1_DESC'  => "Complete e-commerce solutions with shopping cart functionality, secure payment gateways, and inventory management that converts {$city} visitors into paying customers.",
            'SVC1_P1' => 'Shopify & WooCommerce development', 'SVC1_P2' => 'Custom e-commerce platforms',
            'SVC1_P3' => 'Razorpay & Stripe integration',     'SVC1_P4' => 'Inventory & order management',
            'SVC2_TITLE' => '🏢 Corporate Website Development',
            'SVC2_DESC'  => "Professional corporate websites that establish credibility for your {$city} brand. Responsive, SEO-friendly, and conversion-optimised from day one.",
            'SVC2_P1' => 'Responsive design for all devices', 'SVC2_P2' => 'CMS integration for easy updates',
            'SVC2_P3' => 'SEO-optimised page structure',      'SVC2_P4' => 'Lead capture & contact forms',
            'SVC3_TITLE' => '📱 Web Application Development',
            'SVC3_DESC'  => "Custom web applications for {$city} businesses — from SaaS platforms to booking systems that solve unique operational challenges efficiently.",
            'SVC3_P1' => 'Custom SaaS applications',         'SVC3_P2' => 'CRM and ERP systems',
            'SVC3_P3' => 'Booking & reservation systems',    'SVC3_P4' => 'Dashboard & analytics portals',
            'SVC4_TITLE' => '🎨 CMS Website Development',
            'SVC4_DESC'  => "WordPress, Shopify, and custom CMS development that empowers your {$city} team to manage content without requiring technical expertise.",
            'SVC4_P1' => 'WordPress development',            'SVC4_P2' => 'Custom theme & plugin development',
            'SVC4_P3' => 'Multilingual website support',     'SVC4_P4' => 'Third-party CMS integrations',
            'SVC5_TITLE' => '🔧 Website Maintenance & Support',
            'SVC5_DESC'  => "Ongoing website maintenance, security monitoring, and technical support to keep your {$city} business website running at peak performance 24/7.",
            'SVC5_P1' => 'Regular security patch updates',   'SVC5_P2' => 'Performance optimisation',
            'SVC5_P3' => 'Automated daily backups',          'SVC5_P4' => 'Emergency technical support',
            'SVC6_TITLE' => '🚀 Website Redesign & Revamp',
            'SVC6_DESC'  => "Modernising existing websites with improved design, better functionality, and enhanced UX specifically tuned for the {$city} market.",
            'SVC6_P1' => 'Modern UI redesign',               'SVC6_P2' => 'Core Web Vitals improvement',
            'SVC6_P3' => 'Mobile & speed optimisation',      'SVC6_P4' => 'SEO migration & enhancement',
            'SVC7_TITLE' => '🔐 Website Security & SSL',
            'SVC7_DESC'  => "Protect your {$city} business website from hackers, malware, and data breaches with enterprise-grade security hardening and monitoring.",
            'SVC7_P1' => 'SSL installation & renewal',       'SVC7_P2' => 'Malware scanning & removal',
            'SVC7_P3' => 'Firewall & DDoS protection',       'SVC7_P4' => 'Security audit & vulnerability fixes',
            'SVC8_TITLE' => '⚡ Speed & Performance Optimisation',
            'SVC8_DESC'  => "Slow websites lose customers. We optimise your {$city} website's load time to under 2 seconds, improving both user experience and Google search rankings.",
            'SVC8_P1' => 'Image compression & lazy loading',  'SVC8_P2' => 'Caching & CDN implementation',
            'SVC8_P3' => 'Database query optimisation',       'SVC8_P4' => 'Core Web Vitals improvement',
            'SVC9_TITLE' => '🌐 Landing Page Development',
            'SVC9_DESC'  => "High-converting landing pages for your {$city} ad campaigns. We design pages engineered to maximise conversions from every rupee of traffic sent to them.",
            'SVC9_P1' => 'Campaign-specific landing pages',   'SVC9_P2' => 'A/B tested layouts & CTAs',
            'SVC9_P3' => 'Form & lead capture optimisation',  'SVC9_P4' => 'Analytics & heatmap integration',
            'FAQ1_Q' => "What types of websites do you develop for {$city} businesses?",
            'FAQ1_A' => "We develop all types including business websites, e-commerce stores, web applications, e-learning portals, healthcare & medical sites, real estate listings, restaurant sites, blogs, membership portals, and custom web solutions tailored to {$city} businesses.",
            'FAQ2_Q' => "Which platforms and technologies do you work with?",
            'FAQ2_A' => "CMS: WordPress, Shopify, Magento, WooCommerce. Frameworks: React.js, Angular, Vue.js, Laravel, Django, Node.js. Payment: Razorpay, Stripe, PayPal, CCAvenue. Cloud: AWS, Google Cloud, Azure.",
            'FAQ3_Q' => "How long does it take to develop a website for a {$city} business?",
            'FAQ3_A' => "Basic Business Website: 2–4 weeks. E-commerce Store: 4–8 weeks. Custom Web Application: 8–16 weeks. Enterprise Solutions: 3–6 months. We provide a detailed timeline after the discovery call.",
            'FAQ4_Q' => "Do you provide website maintenance after launch?",
            'FAQ4_A' => "Yes — all websites come with 30 days of free post-launch support. We then offer Basic Maintenance, Premium Support (24/7 monitoring), and Complete Management packages to suit every {$city} business budget.",
            'FAQ5_Q' => "What makes VG Digital Falcon different from other web agencies in {$city}?",
            'FAQ5_A' => "Strategic focus on conversions, performance-optimised builds, enterprise-grade security, scalable architecture, local {$city} market expertise, post-launch optimisation, and transparent communication throughout the project.",
            'FAQ6_Q' => "Do you build mobile-friendly websites for {$city} businesses?",
            'FAQ6_A' => "Absolutely. All websites we develop follow a mobile-first design philosophy. With over 70% of web traffic in {$city} coming from mobile devices, responsive design is non-negotiable in every project we deliver.",
            'FAQ7_Q' => "Can you integrate my existing tools and software into the new website?",
            'FAQ7_A' => "Yes. We integrate CRMs (HubSpot, Zoho, Salesforce), payment gateways (Razorpay, Stripe), email platforms (Mailchimp, Klaviyo), accounting software, ERP systems, and any third-party API your {$city} business relies on.",
            'FAQ8_Q' => "Do you offer a website care plan after development in {$city}?",
            'FAQ8_A' => "Yes — our Website Care Plans include monthly plugin & core updates, uptime monitoring, daily backups, security scans, and priority support. Think of it as a maintenance contract that keeps your {$city} website secure and fast.",
            'REVIEW_AUTHOR' => $reviewer,
            'REVIEW_TEXT'   => $reviewTexts['Website Development'][$reviewIdx],
        ];

        // ══════════════════════════════════════════════════════
        case 'SEO':
        // ══════════════════════════════════════════════════════
        $heroIdx   = getDeterministicIndex($seed . 'hero',   count($heroSubtexts['SEO']));
        $ctaIdx    = getDeterministicIndex($seed . 'cta',    count($ctaHeadings['SEO']));
        $reviewIdx = getDeterministicIndex($seed . 'review', count($reviewTexts['SEO']));
        return [
            'META_DESCRIPTION'  => pick($seed . 'meta',  $metaDescriptions['SEO']),
            'PAGE_TITLE'        => pick($seed . 'title', $pageTitles['SEO']),
            'SCHEMA_DESCRIPTION'=> "VG Digital Falcon is a top-rated SEO agency in {$city} delivering technical SEO, local SEO, content strategy, and authority link building for sustainable organic growth.",
            'HERO_H1'           => "#1 SEO Agency in {$city} – Rank Higher, Grow Faster",
            'HERO_SUBTEXT'      => $heroSubtexts['SEO'][$heroIdx],
            'HERO_CTA_TEXT'     => "Get Free SEO Audit",
            'FORM_TITLE'        => "Get Your Free SEO Audit!",
            'OFFCANVAS_DESC'    => pick($seed . 'offcanvas', $offcanvasDescs['SEO']),
            'WHY_CHOOSE_INTRO'  => pick($seed . 'why', $whyChooseIntros['SEO']),
            'BENEFIT1_TITLE'    => "Proven Track Record",
            'BENEFIT1_DESC'     => "We have ranked 200+ keywords on page 1 for {$city} businesses across industries including retail, healthcare, real estate, and B2B services.",
            'BENEFIT2_TITLE'    => "Technical SEO Mastery",
            'BENEFIT2_DESC'     => "From Core Web Vitals to structured data and crawl budget optimisation, our technical SEO ensures Google can index and rank your {$city} website effectively.",
            'BENEFIT3_TITLE'    => "Local SEO Specialists",
            'BENEFIT3_DESC'     => "We optimise your Google Business Profile and build local citations that put your {$city} business in front of customers who are ready to buy.",
            'BENEFIT4_TITLE'    => "No Black-Hat Tactics",
            'BENEFIT4_DESC'     => "Every strategy is 100% white-hat and Google-compliant. Your {$city} website gains rankings that last — no algorithm penalties, ever.",
            'BENEFIT5_TITLE'    => "Topical Authority Building",
            'BENEFIT5_DESC'     => "We establish your {$city} business as the definitive authority in your industry by building comprehensive content clusters that signal deep expertise to Google.",
            'BENEFIT6_TITLE'    => "Competitor Intelligence",
            'BENEFIT6_DESC'     => "We reverse-engineer what's working for your top-ranking {$city} competitors and build strategies that outpace them on every SEO signal.",
            'BENEFIT7_TITLE'    => "E-E-A-T Optimisation",
            'BENEFIT7_DESC'     => "Google's Experience, Expertise, Authoritativeness, and Trustworthiness guidelines are built into everything we do for your {$city} website — ensuring long-term ranking stability.",
            'BENEFIT8_TITLE'    => "Integrated SEO & CRO",
            'BENEFIT8_DESC'     => "More traffic is worthless without conversions. We combine SEO with conversion rate optimisation so your {$city} business gains both visitors and customers from every campaign.",
            'PROCESS_INTRO'     => pick($seed . 'procin', $processIntros['SEO']),
            'STEP1_TITLE' => 'SEO Audit',             'STEP1_SUBTITLE' => 'Complete Site Analysis',
            'STEP1_INTRO' => 'Deep dive into your website health and ranking opportunities in '.$city,
            'STEP1_P1_LABEL' => 'Technical Audit',    'STEP1_P1_DESC' => 'Crawl errors, speed, Core Web Vitals',
            'STEP1_P2_LABEL' => 'Keyword Research',   'STEP1_P2_DESC' => 'High-value '.$city.' search terms',
            'STEP1_P3_LABEL' => 'Competitor Gap',     'STEP1_P3_DESC' => 'Find ranking opportunities',
            'STEP1_P4_LABEL' => 'Backlink Audit',     'STEP1_P4_DESC' => 'Toxic link identification & disavow',
            'STEP2_TITLE' => 'On-Page SEO',           'STEP2_SUBTITLE' => 'Optimising Every Page',
            'STEP2_INTRO' => 'Making every page of your '.$city.' website rank-worthy',
            'STEP2_P1_LABEL' => 'Meta Optimisation',  'STEP2_P1_DESC' => 'Title tags & meta descriptions',
            'STEP2_P2_LABEL' => 'Content Optimisation','STEP2_P2_DESC' => 'Keyword density & semantic relevance',
            'STEP2_P3_LABEL' => 'Schema Markup',      'STEP2_P3_DESC' => 'Rich snippets & structured data',
            'STEP2_P4_LABEL' => 'Internal Linking',   'STEP2_P4_DESC' => 'Authority distribution across pages',
            'STEP3_TITLE' => 'Technical SEO',         'STEP3_SUBTITLE' => 'Foundation Fixes',
            'STEP3_INTRO' => 'Eliminating crawl & indexation issues blocking your '.$city.' rankings',
            'STEP3_P1_LABEL' => 'Site Speed',         'STEP3_P1_DESC' => 'Core Web Vitals improvement',
            'STEP3_P2_LABEL' => 'Mobile Optimisation','STEP3_P2_DESC' => 'Mobile-first indexing compliance',
            'STEP3_P3_LABEL' => 'XML Sitemaps',       'STEP3_P3_DESC' => 'Structured sitemap submission',
            'STEP3_P4_LABEL' => 'Canonical Tags',     'STEP3_P4_DESC' => 'Duplicate content prevention',
            'STEP4_TITLE' => 'Content Strategy',      'STEP4_SUBTITLE' => 'Building Topical Authority',
            'STEP4_INTRO' => 'Creating content that ranks and converts '.$city.' audiences',
            'STEP4_P1_LABEL' => 'Topic Clusters',     'STEP4_P1_DESC' => 'Pillar pages & supporting content',
            'STEP4_P2_LABEL' => 'Blog Writing',       'STEP4_P2_DESC' => 'Long-form SEO articles for '.$city,
            'STEP4_P3_LABEL' => 'Landing Pages',      'STEP4_P3_DESC' => 'Service & location-specific pages',
            'STEP4_P4_LABEL' => 'Content Refresh',    'STEP4_P4_DESC' => 'Updating underperforming pages',
            'STEP5_TITLE' => 'Link Building',         'STEP5_SUBTITLE' => 'Earning Authority',
            'STEP5_INTRO' => 'Building domain authority through quality backlinks for '.$city,
            'STEP5_P1_LABEL' => 'Guest Posts',        'STEP5_P1_DESC' => 'High-DA editorial placements',
            'STEP5_P2_LABEL' => 'Local Citations',    'STEP5_P2_DESC' => $city.' business directories',
            'STEP5_P3_LABEL' => 'HARO Outreach',      'STEP5_P3_DESC' => 'Media and journalist backlinks',
            'STEP5_P4_LABEL' => 'Digital PR',         'STEP5_P4_DESC' => 'Brand mention & link acquisition',
            'STEP6_TITLE' => 'Reporting & Growth',    'STEP6_SUBTITLE' => 'Tracking & Scaling',
            'STEP6_INTRO' => 'Transparent monthly reporting for your '.$city.' SEO campaign',
            'STEP6_P1_LABEL' => 'Rank Tracking',      'STEP6_P1_DESC' => 'Weekly keyword position updates',
            'STEP6_P2_LABEL' => 'Traffic Reports',    'STEP6_P2_DESC' => 'Organic sessions & conversions',
            'STEP6_P3_LABEL' => 'Competitor Monitor', 'STEP6_P3_DESC' => 'Watching for ranking shifts',
            'STEP6_P4_LABEL' => 'Strategy Refresh',   'STEP6_P4_DESC' => 'Quarterly goal & tactic reviews',
            'CTA_HEADING'    => $ctaHeadings['SEO'][$ctaIdx],
            'CTA_SUBTEXT'    => pick($seed . 'ctasub', $ctaSubtexts['SEO']),
            'WHATSAPP_LINK'  => "https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20need%20SEO%20services%20for%20my%20{$city}%20business.",
            'SERVICES_INTRO' => pick($seed . 'svcin', $servicesIntros['SEO']),
            'SVC1_TITLE' => '📍 Local SEO',
            'SVC1_DESC'  => "Dominate Google Maps and local search results in {$city}. We optimise your GBP, build local citations, and earn reviews that drive foot traffic and calls.",
            'SVC1_P1' => 'Google Business Profile optimisation', 'SVC1_P2' => $city.' local citation building',
            'SVC1_P3' => 'Review generation strategy',           'SVC1_P4' => 'Local keyword optimisation',
            'SVC2_TITLE' => '🔧 Technical SEO',
            'SVC2_DESC'  => "Fix the foundation of your website's search performance. We resolve crawl issues, improve Core Web Vitals, and ensure Google can fully index your {$city} site.",
            'SVC2_P1' => 'Core Web Vitals improvement',          'SVC2_P2' => 'Crawl error resolution',
            'SVC2_P3' => 'Structured data & schema markup',      'SVC2_P4' => 'XML sitemap & robots.txt',
            'SVC3_TITLE' => '✍️ Content SEO',
            'SVC3_DESC'  => "Rank for high-value keywords with expertly crafted content that answers {$city} users' search intent and builds topical authority in your industry.",
            'SVC3_P1' => 'Keyword-targeted blog articles',       'SVC3_P2' => 'Service & location landing pages',
            'SVC3_P3' => 'Content gap analysis',                 'SVC3_P4' => 'Existing content refresh & update',
            'SVC4_TITLE' => '🔗 Link Building',
            'SVC4_DESC'  => "Earn high-quality backlinks from authoritative websites that boost your domain authority and improve rankings for your {$city} business sustainably.",
            'SVC4_P1' => 'Guest posting on DA 40+ sites',        'SVC4_P2' => 'HARO & editorial link outreach',
            'SVC4_P3' => 'Niche-relevant link acquisition',      'SVC4_P4' => 'Competitor backlink replication',
            'SVC5_TITLE' => '🛒 E-commerce SEO',
            'SVC5_DESC'  => "Drive organic buyers to your online store in {$city}. We optimise product pages, category structures, and implement schema to dominate shopping searches.",
            'SVC5_P1' => 'Product page optimisation',            'SVC5_P2' => 'Category & faceted navigation SEO',
            'SVC5_P3' => 'Product schema markup',                'SVC5_P4' => 'Shopping feed & rich result optimisation',
            'SVC6_TITLE' => '📊 SEO Audit & Strategy',
            'SVC6_DESC'  => "A comprehensive SEO audit that uncovers every opportunity and issue affecting your {$city} website's ability to rank and convert.",
            'SVC6_P1' => 'Full technical site audit',            'SVC6_P2' => 'Keyword opportunity mapping',
            'SVC6_P3' => 'Competitor analysis report',           'SVC6_P4' => '12-month SEO roadmap',
            'SVC7_TITLE' => '🗺️ Google Business Profile Management',
            'SVC7_DESC'  => "A fully optimised and actively managed Google Business Profile ensures your {$city} business appears in the coveted local 3-pack and attracts ready-to-buy customers.",
            'SVC7_P1' => 'Profile creation & verification',      'SVC7_P2' => 'Weekly post & update publishing',
            'SVC7_P3' => 'Q&A & review management',              'SVC7_P4' => 'Photo & video optimisation',
            'SVC8_TITLE' => '📱 Mobile SEO',
            'SVC8_DESC'  => "With the majority of {$city} searches happening on mobile, we ensure your website is perfectly optimised for Google's mobile-first indexing.",
            'SVC8_P1' => 'Mobile page speed optimisation',       'SVC8_P2' => 'Touch-friendly UX improvements',
            'SVC8_P3' => 'AMP implementation (where applicable)','SVC8_P4' => 'Mobile SERP feature optimisation',
            'SVC9_TITLE' => '📰 Digital PR & Brand Mentions',
            'SVC9_DESC'  => "Earn media coverage and brand mentions from top publications that simultaneously build your {$city} business's authority and send powerful ranking signals to Google.",
            'SVC9_P1' => 'Media outreach & journalist relations', 'SVC9_P2' => 'Press release writing & distribution',
            'SVC9_P3' => 'Brand mention monitoring',              'SVC9_P4' => 'Unlinked mention conversion',
            'FAQ1_Q' => "How long does SEO take to show results for a {$city} business?",
            'FAQ1_A' => "On-page fixes can improve performance within 2–4 weeks. Meaningful ranking improvements typically take 3–6 months for competitive {$city} keywords. Local SEO results (Google Maps pack) can appear in 4–8 weeks.",
            'FAQ2_Q' => "What is the cost of SEO services in {$city}?",
            'FAQ2_A' => "Our SEO packages start at ₹12,000/month for local SEO and ₹25,000/month for comprehensive national SEO. Pricing depends on competition level, number of target keywords, and the depth of work required.",
            'FAQ3_Q' => "Do you use white-hat SEO techniques?",
            'FAQ3_A' => "Yes, 100%. We never use black-hat tactics like PBNs, keyword stuffing, or cloaking. All strategies comply with Google's Webmaster Guidelines, ensuring your rankings are sustainable long-term.",
            'FAQ4_Q' => "Can you guarantee #1 Google rankings in {$city}?",
            'FAQ4_A' => "No ethical SEO agency can guarantee specific rankings — Google's algorithm is beyond anyone's full control. However, we consistently deliver first-page results and set transparent expectations based on keyword competition.",
            'FAQ5_Q' => "Do you provide monthly SEO reports?",
            'FAQ5_A' => "Yes. Every month you receive a detailed report covering keyword rankings, organic traffic, backlinks acquired, technical health, and next month's priorities. We also hold a monthly strategy call to review results.",
            'FAQ6_Q' => "What is the difference between local SEO and national SEO for {$city} businesses?",
            'FAQ6_A' => "Local SEO targets customers searching for businesses in {$city} specifically — Google Maps pack, 'near me' searches, and local keywords. National SEO targets broader keywords across India. Most {$city} businesses benefit from both simultaneously.",
            'FAQ7_Q' => "How do you build backlinks for my {$city} website?",
            'FAQ7_A' => "Through guest posting on high-authority industry sites, HARO (Help a Reporter Out) responses, digital PR, local {$city} business directory submissions, and competitor backlink replication. We never purchase links or use link farms.",
            'FAQ8_Q' => "Will my {$city} website rankings drop if I stop SEO?",
            'FAQ8_A' => "Likely yes over time — competitors continue optimising, and Google's algorithm evolves. However, the authority and content assets built during your SEO campaign provide a durable advantage. We recommend at minimum a maintenance package to protect your {$city} rankings.",
            'REVIEW_AUTHOR' => $reviewer,
            'REVIEW_TEXT'   => $reviewTexts['SEO'][$reviewIdx],
        ];

        // ══════════════════════════════════════════════════════
        case 'Youtube SEO':
        // ══════════════════════════════════════════════════════
        $heroIdx   = getDeterministicIndex($seed . 'hero',   count($heroSubtexts['Youtube SEO']));
        $ctaIdx    = getDeterministicIndex($seed . 'cta',    count($ctaHeadings['Youtube SEO']));
        $reviewIdx = getDeterministicIndex($seed . 'review', count($reviewTexts['Youtube SEO']));
        return [
            'META_DESCRIPTION'  => pick($seed . 'meta',  $metaDescriptions['Youtube SEO']),
            'PAGE_TITLE'        => pick($seed . 'title', $pageTitles['Youtube SEO']),
            'SCHEMA_DESCRIPTION'=> "VG Digital Falcon provides expert YouTube SEO services in {$city} — channel optimisation, keyword research, thumbnail design, and watch-time strategies.",
            'HERO_H1'           => "#1 YouTube SEO Agency in {$city}",
            'HERO_SUBTEXT'      => $heroSubtexts['Youtube SEO'][$heroIdx],
            'HERO_CTA_TEXT'     => "Get Free YouTube Channel Audit",
            'FORM_TITLE'        => "Get Your YouTube Growth Plan!",
            'OFFCANVAS_DESC'    => pick($seed . 'offcanvas', $offcanvasDescs['Youtube SEO']),
            'WHY_CHOOSE_INTRO'  => pick($seed . 'why', $whyChooseIntros['Youtube SEO']),
            'BENEFIT1_TITLE'    => "Keyword Dominance",
            'BENEFIT1_DESC'     => "We research the exact keywords {$city} audiences search for and optimise every element of your video to rank at the top of YouTube search results.",
            'BENEFIT2_TITLE'    => "Thumbnail & Title Expertise",
            'BENEFIT2_DESC'     => "CTR-optimised thumbnails and titles increase your click-through rates, signalling YouTube's algorithm that your content deserves more reach.",
            'BENEFIT3_TITLE'    => "Watch Time Strategy",
            'BENEFIT3_DESC'     => "We structure scripts and intros to maximise audience retention — the most powerful ranking signal YouTube uses to promote your {$city} content.",
            'BENEFIT4_TITLE'    => "Channel Monetisation",
            'BENEFIT4_DESC'     => "From hitting monetisation thresholds to building brand deals, we help {$city} creators turn their YouTube channel into a revenue-generating asset.",
            'BENEFIT5_TITLE'    => "Algorithm Intelligence",
            'BENEFIT5_DESC'     => "YouTube's algorithm changes constantly. Our team monitors updates and adapts your {$city} channel strategy in real time to stay ahead of the curve.",
            'BENEFIT6_TITLE'    => "Cross-Platform Amplification",
            'BENEFIT6_DESC'     => "We amplify your YouTube content across Instagram Reels, Facebook, LinkedIn, and your website — turning each video into a multi-channel traffic driver for your {$city} brand.",
            'BENEFIT7_TITLE'    => "Subscriber Community Building",
            'BENEFIT7_DESC'     => "Views are vanity — subscribers are value. We implement community engagement strategies that convert casual {$city} viewers into loyal, returning subscribers.",
            'BENEFIT8_TITLE'    => "Data-Driven Content Planning",
            'BENEFIT8_DESC'     => "Every video idea we recommend for your {$city} channel is backed by YouTube search data, trending topic analysis, and competitor content gap research.",
            'PROCESS_INTRO'     => pick($seed . 'procin', $processIntros['Youtube SEO']),
            'STEP1_TITLE' => 'Channel Audit',           'STEP1_SUBTITLE' => 'Diagnosing Growth Blockers',
            'STEP1_INTRO' => 'Full analysis of your current channel performance in '.$city,
            'STEP1_P1_LABEL' => 'Channel Health Check',  'STEP1_P1_DESC' => 'Branding, about, links & keywords',
            'STEP1_P2_LABEL' => 'Video Performance',     'STEP1_P2_DESC' => 'CTR, watch time & retention data',
            'STEP1_P3_LABEL' => 'Competitor Analysis',   'STEP1_P3_DESC' => 'Top YouTube channels in your niche',
            'STEP1_P4_LABEL' => 'Keyword Gap Analysis',  'STEP1_P4_DESC' => 'Untapped search opportunities',
            'STEP2_TITLE' => 'Keyword Research',         'STEP2_SUBTITLE' => 'Finding What '.$city.' Searches',
            'STEP2_INTRO' => 'Identifying high-traffic, low-competition YouTube topics',
            'STEP2_P1_LABEL' => 'YouTube Search Data',   'STEP2_P1_DESC' => 'Volume, competition & trends',
            'STEP2_P2_LABEL' => 'Google Cross-Rank',     'STEP2_P2_DESC' => 'Videos that also rank on Google',
            'STEP2_P3_LABEL' => 'Trending Topics',       'STEP2_P3_DESC' => 'Capitalising on viral opportunities',
            'STEP2_P4_LABEL' => 'Long-Tail Keywords',    'STEP2_P4_DESC' => 'Lower competition, high-intent terms',
            'STEP3_TITLE' => 'Video Optimisation',       'STEP3_SUBTITLE' => 'Maximising Each Upload',
            'STEP3_INTRO' => 'Every element of your video optimised to rank and convert',
            'STEP3_P1_LABEL' => 'Title Optimisation',    'STEP3_P1_DESC' => 'Click-worthy, keyword-rich titles',
            'STEP3_P2_LABEL' => 'Description Writing',   'STEP3_P2_DESC' => 'SEO-friendly detailed descriptions',
            'STEP3_P3_LABEL' => 'Tag Strategy',          'STEP3_P3_DESC' => 'Relevant tags for discoverability',
            'STEP3_P4_LABEL' => 'Chapter Timestamps',    'STEP3_P4_DESC' => 'Improving watch time & UX',
            'STEP4_TITLE' => 'Thumbnail Design',         'STEP4_SUBTITLE' => 'Capturing Clicks',
            'STEP4_INTRO' => 'High-CTR thumbnails that stand out in '.$city.' YouTube searches',
            'STEP4_P1_LABEL' => 'Custom Thumbnails',     'STEP4_P1_DESC' => 'Brand-consistent visual design',
            'STEP4_P2_LABEL' => 'A/B Thumbnail Testing', 'STEP4_P2_DESC' => 'Identifying highest-CTR variants',
            'STEP4_P3_LABEL' => 'Text Overlay',          'STEP4_P3_DESC' => 'Readable text on mobile screens',
            'STEP4_P4_LABEL' => 'Emotional Triggers',    'STEP4_P4_DESC' => 'Curiosity & benefit-driven imagery',
            'STEP5_TITLE' => 'Promotion & Distribution', 'STEP5_SUBTITLE' => 'Amplifying Reach',
            'STEP5_INTRO' => 'Getting more eyes on your '.$city.' channel content',
            'STEP5_P1_LABEL' => 'Cross-Promotion',       'STEP5_P1_DESC' => 'Social media & website embedding',
            'STEP5_P2_LABEL' => 'Community Tab Posts',   'STEP5_P2_DESC' => 'Engagement posts to boost algorithm',
            'STEP5_P3_LABEL' => 'Playlist Strategy',     'STEP5_P3_DESC' => 'Session time & watch time improvement',
            'STEP5_P4_LABEL' => 'End Screen & Cards',    'STEP5_P4_DESC' => 'Subscriber CTA optimisation',
            'STEP6_TITLE' => 'Analytics & Growth',       'STEP6_SUBTITLE' => 'Scaling Your Channel',
            'STEP6_INTRO' => 'Data-driven decisions for continuous '.$city.' channel growth',
            'STEP6_P1_LABEL' => 'YouTube Studio Reports','STEP6_P1_DESC' => 'Views, subscribers & revenue tracking',
            'STEP6_P2_LABEL' => 'Audience Insights',     'STEP6_P2_DESC' => 'Demographics & behaviour analysis',
            'STEP6_P3_LABEL' => 'Content Calendar',      'STEP6_P3_DESC' => 'Consistent upload scheduling',
            'STEP6_P4_LABEL' => 'Monetisation Planning', 'STEP6_P4_DESC' => 'Ad revenue, sponsorships & merch',
            'CTA_HEADING'    => $ctaHeadings['Youtube SEO'][$ctaIdx],
            'CTA_SUBTEXT'    => pick($seed . 'ctasub', $ctaSubtexts['Youtube SEO']),
            'WHATSAPP_LINK'  => "https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20need%20YouTube%20SEO%20services%20for%20my%20channel%20in%20{$city}.",
            'SERVICES_INTRO' => pick($seed . 'svcin', $servicesIntros['Youtube SEO']),
            'SVC1_TITLE' => '🎬 YouTube Channel Optimisation',
            'SVC1_DESC'  => "Complete channel overhaul — branding, about section, keyword-rich channel description, playlists, and community tab setup for maximum {$city} discoverability.",
            'SVC1_P1' => 'Channel art & banner design',          'SVC1_P2' => 'Keyword-optimised about section',
            'SVC1_P3' => 'Strategic playlist architecture',      'SVC1_P4' => 'Channel trailer creation & optimisation',
            'SVC2_TITLE' => '🔍 Video SEO & Ranking',
            'SVC2_DESC'  => "Rank your videos at the top of YouTube and Google search for high-value keywords relevant to your {$city} audience and business niche.",
            'SVC2_P1' => 'Title & description optimisation',     'SVC2_P2' => 'Tag & hashtag strategy',
            'SVC2_P3' => 'Closed captions & transcripts',        'SVC2_P4' => 'Chapter & timestamp optimisation',
            'SVC3_TITLE' => '🖼️ Thumbnail Design',
            'SVC3_DESC'  => "Professionally designed, CTR-optimised thumbnails that stop the scroll and compel {$city} users to click on your videos every time.",
            'SVC3_P1' => 'Custom branded thumbnail templates',   'SVC3_P2' => 'A/B CTR testing framework',
            'SVC3_P3' => 'Mobile-first legibility check',        'SVC3_P4' => 'Emotional design psychology',
            'SVC4_TITLE' => '📝 Content Strategy & Scripting',
            'SVC4_DESC'  => "A data-backed content calendar with video ideas, scripts, and hooks engineered to maximise watch time and subscriber growth for your {$city} channel.",
            'SVC4_P1' => 'Monthly content calendar',             'SVC4_P2' => 'Hook & intro script writing',
            'SVC4_P3' => 'Competitor content gap analysis',      'SVC4_P4' => 'Trending topic integration',
            'SVC5_TITLE' => '📈 Subscriber Growth Campaigns',
            'SVC5_DESC'  => "Targeted campaigns combining organic SEO, cross-platform promotion, and community engagement to accelerate subscriber growth for your {$city} channel.",
            'SVC5_P1' => 'End screen & card strategy',           'SVC5_P2' => 'Community post engagement',
            'SVC5_P3' => 'Cross-platform sharing & promotion',   'SVC5_P4' => 'Collaboration channel outreach',
            'SVC6_TITLE' => '💰 YouTube Monetisation',
            'SVC6_DESC'  => "Reach YouTube Partner Program eligibility faster and diversify revenue streams beyond AdSense for your {$city}-based channel.",
            'SVC6_P1' => 'YPP eligibility planning',             'SVC6_P2' => 'Sponsorship pitch deck creation',
            'SVC6_P3' => 'Merchandise shelf strategy',           'SVC6_P4' => 'Channel membership setup',
            'SVC7_TITLE' => '📊 YouTube Analytics Consulting',
            'SVC7_DESC'  => "Deep dives into your YouTube Studio data to uncover exactly what's driving — or dragging — growth for your {$city} channel, with a clear action plan.",
            'SVC7_P1' => 'Watch time & retention analysis',      'SVC7_P2' => 'Traffic source deep dive',
            'SVC7_P3' => 'Audience demographic analysis',        'SVC7_P4' => 'Revenue & RPM optimisation',
            'SVC8_TITLE' => '🎙️ Podcast-to-YouTube Strategy',
            'SVC8_DESC'  => "Repurpose your podcast episodes into SEO-optimised YouTube videos — maximising content ROI for your {$city} brand without creating from scratch.",
            'SVC8_P1' => 'Audio-to-video conversion',            'SVC8_P2' => 'Dynamic visual overlays',
            'SVC8_P3' => 'Episode SEO optimisation',             'SVC8_P4' => 'Clip extraction for Shorts',
            'SVC9_TITLE' => '📱 YouTube Shorts Strategy',
            'SVC9_DESC'  => "Short-form video is exploding in {$city} and across India. We develop a YouTube Shorts strategy that rapidly grows your channel's reach and subscriber count.",
            'SVC9_P1' => 'Short-form content ideation',          'SVC9_P2' => 'Hook-driven script writing',
            'SVC9_P3' => 'Shorts SEO & hashtag strategy',        'SVC9_P4' => 'Cross-promotion to long-form content',
            'FAQ1_Q' => "How long does YouTube SEO take to show results in {$city}?",
            'FAQ1_A' => "Optimised videos can see improved rankings within 2–4 weeks. Significant subscriber and view growth typically occurs over 3–6 months of consistent, optimised content publication.",
            'FAQ2_Q' => "Can you grow any type of YouTube channel in {$city}?",
            'FAQ2_A' => "Yes — we work with business channels, educational creators, lifestyle influencers, news & current affairs, cooking, fitness, real estate, and more. Our strategies are adapted to your specific niche and {$city} audience.",
            'FAQ3_Q' => "Do you help with video production for {$city} creators?",
            'FAQ3_A' => "We specialise in YouTube SEO, strategy, scripting, and thumbnail design. For full video production (filming, editing), we have trusted {$city} production partners we can connect you with.",
            'FAQ4_Q' => "How do you increase watch time on my channel?",
            'FAQ4_A' => "We use hook engineering (first 30 seconds), pattern interrupts, chapter timestamps, and end-screen CTAs proven to improve audience retention. We also advise on pacing, topic structure, and editing style.",
            'FAQ5_Q' => "What metrics do you track for YouTube SEO success?",
            'FAQ5_A' => "Views, watch hours, subscriber growth, average view duration, click-through rate (CTR), impressions, revenue (if monetised), and keyword ranking positions. We report these monthly.",
            'FAQ6_Q' => "How do YouTube Shorts help grow my {$city} channel?",
            'FAQ6_A' => "YouTube Shorts have their own dedicated feed and can expose your {$city} channel to entirely new audiences. When done strategically, Shorts drive subscribers to your long-form content, creating a powerful growth flywheel.",
            'FAQ7_Q' => "Can you help me reach YouTube monetisation in {$city}?",
            'FAQ7_A' => "Yes. Our growth strategy is designed to hit the 1,000 subscriber and 4,000 watch-hour thresholds as efficiently as possible. We also help {$city} creators diversify revenue through sponsorships, affiliate marketing, and merchandise.",
            'FAQ8_Q' => "Do I need to post videos regularly for YouTube SEO to work?",
            'FAQ8_A' => "Consistency signals to YouTube's algorithm that your {$city} channel is active and reliable. We recommend a posting schedule your team can sustain — quality matters more than volume, but regularity is key for algorithmic growth.",
            'REVIEW_AUTHOR' => $reviewer,
            'REVIEW_TEXT'   => $reviewTexts['Youtube SEO'][$reviewIdx],
        ];

        // ══════════════════════════════════════════════════════
        default:
        // ══════════════════════════════════════════════════════
        return generateGenericContent($service, $city, $reviewer);
    }
}

// ─── Generic content generator for remaining services ─────────
// (Performance Marketing, Personal Branding, Ecommerce SEO,
//  Graphic Designing, Social media Marketing)
function generateGenericContent(string $service, string $city, string $reviewer): array
{
    $seed = $service . '|' . $city;
    $slug = strtolower(str_replace(' ', '-', $service));

    // ── Hero pool: 7 variants, was 4 ───────────────────────────
    $heroPool = [
        "Partner with {$city}'s most trusted {$service} experts. We deliver customised strategies, transparent reporting, and results that move the needle for your business.",
        "Your {$city} business deserves a {$service} partner who understands your goals, your market, and your customers. That's exactly what VG Digital Falcon delivers.",
        "Outpace your {$city} competitors with a {$service} strategy built on data, creativity, and relentless optimisation. We measure success in business outcomes — not activity.",
        "From strategy to execution, VG Digital Falcon handles every aspect of your {$service} journey in {$city} — so you can focus on running your business.",
        "{$city} businesses that invest in {$service} early build an advantage that compounds. We help you build that advantage methodically, month over month.",
        "We treat {$service} as a growth lever, not a checkbox. For {$city} businesses, that means strategy tied directly to revenue, not just activity reports.",
        "Whether you're just getting started with {$service} or looking to scale what's already working, our {$city} team builds a plan around exactly where you are today.",
    ];

    // ── CTA heading pool: 7 variants, was 4 ────────────────────
    $ctaHeadingPool = [
        "Ready to Grow with {$service} in {$city}?",
        "Let's Elevate Your {$city} Business with {$service}",
        "Transform Your {$city} Brand with Expert {$service}",
        "Your {$city} {$service} Growth Starts Here",
        "Let's Build a {$service} Plan for Your {$city} Business",
        "Stop Leaving {$service} Results on the Table in {$city}",
        "{$city} Businesses Grow Faster with the Right {$service} Partner",
    ];

    // ── CTA subtext pool: 3 variants — NEW ─────────────────────
    $ctaSubtextPool = [
        "Schedule a free strategy consultation with our {$city} {$service} experts. Get a customised plan for your business — no obligations.",
        "Talk to our {$city} team about your {$service} goals and get a tailored plan back within 48 hours — no pressure, no obligation.",
        "Get a free {$service} review for your {$city} business and see exactly where the quickest wins are hiding.",
    ];

    // ── Review pool: 7 variants, was 4 ─────────────────────────
    $reviewPool = [
        "Outstanding {$service} results for our {$city} business. VG Digital Falcon exceeded our expectations in every metric. Highly recommend!",
        "Professional, data-driven, and genuinely invested in our {$city} business growth. The {$service} team at VG Digital Falcon is exceptional.",
        "VG Digital Falcon's {$service} work transformed our {$city} business. Within 4 months we saw a 2.5× improvement in key metrics. Best agency we've used.",
        "From strategy to execution, VG Digital Falcon's {$service} team delivered everything they promised for our {$city} business and more. Thrilled with the results.",
        "We were sceptical about {$service} agencies after a bad experience elsewhere — VG Digital Falcon completely changed that for our {$city} business.",
        "Clear communication, realistic timelines, and consistent results. VG Digital Falcon's {$service} programme has been a genuine asset to our {$city} business.",
        "What impressed us most was how quickly VG Digital Falcon understood our {$city} market for {$service}. Results followed within weeks, not months.",
    ];

    // ── META_DESCRIPTION pool: 3 variants — NEW ────────────────
    $metaDescPool = [
        "VG Digital Falcon offers top-rated {$service} services in {$city}. Data-driven strategies, proven results, and dedicated support for {$city} businesses.",
        "Looking for {$service} experts in {$city}? VG Digital Falcon builds custom strategies backed by data and accountable to real business outcomes.",
        "VG Digital Falcon helps {$city} businesses grow through expert {$service} — strategy, execution, and transparent reporting, all in one partnership.",
    ];

    // ── PAGE_TITLE pool: 3 variants — NEW ──────────────────────
    $pageTitlePool = [
        "Best {$service} Agency in {$city} | VG Digital Falcon",
        "{$service} Services in {$city} - VG Digital Falcon",
        "{$city} {$service} Agency — Results-Driven | VG Digital Falcon",
    ];

    // ── WHY_CHOOSE_INTRO pool: 3 variants — NEW ────────────────
    $whyChooseIntroPool = [
        "Here's why {$city} businesses trust VG Digital Falcon for {$service}:",
        "{$city} businesses don't need more {$service} activity — they need results. Here's how we deliver them:",
        "Choosing a {$service} partner in {$city} matters more than most businesses realise. Here's what we bring to the table:",
    ];

    // ── SERVICES_INTRO pool: 3 variants — NEW ──────────────────
    $servicesIntroPool = [
        "Full-service {$service} solutions for {$city} businesses — strategy, execution, and growth.",
        "Here's the complete range of {$service} services we offer {$city} businesses, built to work as one connected system.",
        "From first audit to ongoing execution, here's how we structure {$service} for {$city} clients.",
    ];

    // ── PROCESS_INTRO pool: 3 variants — NEW ───────────────────
    $processIntroPool = [
        "Our structured 6-step {$service} process ensures consistent, scalable results for {$city} businesses.",
        "Here's exactly how we take a {$city} business from a {$service} plan to measurable monthly results.",
        "No guesswork — our 6-step {$service} process for {$city} clients turns strategy into a repeatable system.",
    ];

    // ── OFFCANVAS_DESC pool: 3 variants — NEW ──────────────────
    $offcanvasDescPool = [
        "VG Digital Falcon brings world-class {$service} expertise to {$city} businesses. Your growth is our mission.",
        "We help {$city} businesses turn {$service} into a measurable growth channel — not just another marketing line item.",
        "VG Digital Falcon partners with {$city} businesses on {$service} that's built around real outcomes, not vanity activity.",
    ];

    $heroIdx     = getDeterministicIndex($seed . 'hero',   count($heroPool));
    $ctaIdx      = getDeterministicIndex($seed . 'cta',    count($ctaHeadingPool));
    $reviewIdx   = getDeterministicIndex($seed . 'review', count($reviewPool));

    return [
        'META_DESCRIPTION'  => pick($seed . 'meta',  $metaDescPool),
        'PAGE_TITLE'        => pick($seed . 'title', $pageTitlePool),
        'SCHEMA_DESCRIPTION'=> "VG Digital Falcon delivers expert {$service} solutions in {$city}, helping businesses achieve measurable growth through strategic execution and local market knowledge.",
        'HERO_H1'           => "#1 {$service} Agency in {$city}",
        'HERO_SUBTEXT'      => $heroPool[$heroIdx],
        'HERO_CTA_TEXT'     => "Get Free {$service} Consultation",
        'FORM_TITLE'        => "Get Your Free {$service} Plan!",
        'OFFCANVAS_DESC'    => pick($seed . 'offcanvas', $offcanvasDescPool),
        'WHY_CHOOSE_INTRO'  => pick($seed . 'why', $whyChooseIntroPool),
        'BENEFIT1_TITLE'    => "Proven Results",
        'BENEFIT1_DESC'     => "We have delivered measurable {$service} results for 200+ businesses across South India, including several in {$city}.",
        'BENEFIT2_TITLE'    => "Local Market Knowledge",
        'BENEFIT2_DESC'     => "Our deep understanding of {$city}'s unique market dynamics ensures your {$service} strategy resonates with your target audience.",
        'BENEFIT3_TITLE'    => "Dedicated Account Manager",
        'BENEFIT3_DESC'     => "A single point of contact manages your entire {$service} engagement, ensuring consistent strategy and clear communication.",
        'BENEFIT4_TITLE'    => "Transparent Reporting",
        'BENEFIT4_DESC'     => "Monthly detailed reports and strategy calls keep you fully informed on every aspect of your {$service} campaign's performance.",
        'BENEFIT5_TITLE'    => "Flexible Engagement Models",
        'BENEFIT5_DESC'     => "Whether you need a full managed service or expert consulting to support your in-house team, we offer {$service} engagement models that fit your {$city} business perfectly.",
        'BENEFIT6_TITLE'    => "Industry-Specific Expertise",
        'BENEFIT6_DESC'     => "We bring proven {$service} frameworks from across multiple industries and adapt them to the specific nuances of your {$city} market and competitive landscape.",
        'BENEFIT7_TITLE'    => "Agile Execution",
        'BENEFIT7_DESC'     => "We work in sprints, review results fortnightly, and adapt your {$service} strategy in real time — ensuring your {$city} business stays ahead of the competition.",
        'BENEFIT8_TITLE'    => "End-to-End Service",
        'BENEFIT8_DESC'     => "From initial audit and strategy through to daily execution and reporting, VG Digital Falcon manages your entire {$service} workflow for your {$city} business.",
        'PROCESS_INTRO'     => pick($seed . 'procin', $processIntroPool),
        'STEP1_TITLE' => 'Discovery',          'STEP1_SUBTITLE' => 'Understanding Your Goals',
        'STEP1_INTRO' => 'We start by learning your '.$city.' business inside out',
        'STEP1_P1_LABEL' => 'Business Analysis',   'STEP1_P1_DESC' => 'Understanding your market position in '.$city,
        'STEP1_P2_LABEL' => 'Competitor Research', 'STEP1_P2_DESC' => 'Benchmarking against '.$city.' leaders',
        'STEP1_P3_LABEL' => 'Audience Insights',   'STEP1_P3_DESC' => 'Defining your ideal customer',
        'STEP1_P4_LABEL' => 'Goal Alignment',      'STEP1_P4_DESC' => 'KPIs tied to business outcomes',
        'STEP2_TITLE' => 'Strategy',           'STEP2_SUBTITLE' => 'Custom Roadmap',
        'STEP2_INTRO' => 'Building a tailored '.$service.' strategy for '.$city,
        'STEP2_P1_LABEL' => 'Strategy Development','STEP2_P1_DESC' => 'Custom plan for your '.$city.' goals',
        'STEP2_P2_LABEL' => 'Channel Selection',   'STEP2_P2_DESC' => 'Identifying the highest-ROI channels',
        'STEP2_P3_LABEL' => 'Budget Planning',     'STEP2_P3_DESC' => 'Optimal spend allocation',
        'STEP2_P4_LABEL' => 'Timeline Creation',   'STEP2_P4_DESC' => 'Milestones & deliverables',
        'STEP3_TITLE' => 'Execution',          'STEP3_SUBTITLE' => 'Expert Implementation',
        'STEP3_INTRO' => 'Bringing your '.$service.' strategy to life in '.$city,
        'STEP3_P1_LABEL' => 'Campaign Setup',      'STEP3_P1_DESC' => 'Full '.$service.' implementation',
        'STEP3_P2_LABEL' => 'Creative Development','STEP3_P2_DESC' => 'Compelling content & visual assets',
        'STEP3_P3_LABEL' => 'Quality Assurance',   'STEP3_P3_DESC' => 'Review before every publication',
        'STEP3_P4_LABEL' => 'Go-Live',             'STEP3_P4_DESC' => 'Launching campaigns in '.$city,
        'STEP4_TITLE' => 'Monitoring',         'STEP4_SUBTITLE' => 'Real-Time Tracking',
        'STEP4_INTRO' => 'Watching every metric that matters for your '.$city.' campaign',
        'STEP4_P1_LABEL' => 'Performance Tracking','STEP4_P1_DESC' => 'Daily & weekly KPI monitoring',
        'STEP4_P2_LABEL' => 'Alerts & Triggers',   'STEP4_P2_DESC' => 'Proactive issue detection',
        'STEP4_P3_LABEL' => 'Competitor Watching', 'STEP4_P3_DESC' => 'Staying ahead in '.$city,
        'STEP4_P4_LABEL' => 'Trend Analysis',      'STEP4_P4_DESC' => 'Capitalising on market shifts',
        'STEP5_TITLE' => 'Optimisation',       'STEP5_SUBTITLE' => 'Continuous Improvement',
        'STEP5_INTRO' => 'Refining for better performance each month in '.$city,
        'STEP5_P1_LABEL' => 'A/B Testing',         'STEP5_P1_DESC' => 'Data-driven creative experiments',
        'STEP5_P2_LABEL' => 'Performance Tuning',  'STEP5_P2_DESC' => 'Cost efficiency improvements',
        'STEP5_P3_LABEL' => 'Content Refinement',  'STEP5_P3_DESC' => 'Updating underperforming assets',
        'STEP5_P4_LABEL' => 'Process Improvements','STEP5_P4_DESC' => 'Increasing team efficiency',
        'STEP6_TITLE' => 'Reporting',          'STEP6_SUBTITLE' => 'Full Transparency',
        'STEP6_INTRO' => 'Monthly reports that tell the whole story for your '.$city.' business',
        'STEP6_P1_LABEL' => 'Performance Reports',  'STEP6_P1_DESC' => 'Custom dashboards for your KPIs',
        'STEP6_P2_LABEL' => 'Strategy Calls',       'STEP6_P2_DESC' => 'Monthly review & planning sessions',
        'STEP6_P3_LABEL' => 'ROI Analysis',         'STEP6_P3_DESC' => 'Revenue attribution reporting',
        'STEP6_P4_LABEL' => 'Next Month Planning',  'STEP6_P4_DESC' => 'Prioritised action items',
        'CTA_HEADING'    => $ctaHeadingPool[$ctaIdx],
        'CTA_SUBTEXT'    => pick($seed . 'ctasub', $ctaSubtextPool),
        'WHATSAPP_LINK'  => "https://wa.me/918695776922?text=Hi%20VG%20Digital%20Falcon%2C%20I%20need%20{$slug}%20services%20for%20my%20business%20in%20{$city}.",
        'SERVICES_INTRO' => pick($seed . 'svcin', $servicesIntroPool),
        'SVC1_TITLE' => "🎯 {$service} Strategy",
        'SVC1_DESC'  => "Custom {$service} strategies built around your {$city} business goals. We analyse your market, audience, and competition to create a plan that delivers results.",
        'SVC1_P1' => 'Market analysis for '.$city,          'SVC1_P2' => 'Competitor benchmarking',
        'SVC1_P3' => 'Custom strategy development',         'SVC1_P4' => 'KPI setting & goal alignment',
        'SVC2_TITLE' => "📈 {$service} Execution",
        'SVC2_DESC'  => "Expert implementation of your {$service} strategy by a dedicated {$city} team. From setup to daily management, we handle everything.",
        'SVC2_P1' => 'Full campaign setup',                 'SVC2_P2' => 'Creative asset development',
        'SVC2_P3' => 'Daily monitoring & management',       'SVC2_P4' => 'Continuous optimisation',
        'SVC3_TITLE' => "🔍 {$service} Audit",
        'SVC3_DESC'  => "Comprehensive audit of your current {$service} efforts to identify gaps, opportunities, and quick wins for your {$city} business.",
        'SVC3_P1' => 'Current performance analysis',        'SVC3_P2' => 'Gap & opportunity identification',
        'SVC3_P3' => 'Competitor benchmarking',             'SVC3_P4' => 'Actionable recommendations report',
        'SVC4_TITLE' => "📱 {$service} Content Creation",
        'SVC4_DESC'  => "Compelling content tailored for your {$service} channels that resonates with {$city} audiences and drives measurable engagement.",
        'SVC4_P1' => 'Platform-optimised content',          'SVC4_P2' => 'Visual design & copywriting',
        'SVC4_P3' => 'Content calendar management',         'SVC4_P4' => 'Performance-based content refresh',
        'SVC5_TITLE' => "💡 {$service} Consulting",
        'SVC5_DESC'  => "Expert consulting to guide your in-house team or complement our managed services, ensuring your {$city} business maximises every {$service} opportunity.",
        'SVC5_P1' => 'Expert strategy guidance',            'SVC5_P2' => 'Team training workshops',
        'SVC5_P3' => 'Tool & tech stack recommendations',   'SVC5_P4' => 'Quarterly strategic reviews',
        'SVC6_TITLE' => "📊 {$service} Reporting & Analytics",
        'SVC6_DESC'  => "Clear, actionable reporting that shows exactly how your {$service} investment is performing and where to focus next for maximum {$city} growth.",
        'SVC6_P1' => 'Custom KPI dashboards',               'SVC6_P2' => 'Monthly performance reports',
        'SVC6_P3' => 'ROI attribution analysis',            'SVC6_P4' => 'Data-driven recommendations',
        'SVC7_TITLE' => "🤝 {$service} Training & Workshops",
        'SVC7_DESC'  => "Empower your {$city} team with the skills to understand, contribute to, and amplify your {$service} efforts through hands-on training sessions.",
        'SVC7_P1' => 'Customised training curriculum',      'SVC7_P2' => 'Hands-on workshop sessions',
        'SVC7_P3' => 'Tools & platform walkthroughs',       'SVC7_P4' => 'Post-training support resources',
        'SVC8_TITLE' => "🔄 {$service} Campaign Refresh",
        'SVC8_DESC'  => "Is your current {$service} approach stale? We audit, refresh, and reinvigorate underperforming campaigns for {$city} businesses to restore growth momentum.",
        'SVC8_P1' => 'Campaign performance deep dive',      'SVC8_P2' => 'Creative & messaging refresh',
        'SVC8_P3' => 'Audience targeting review',           'SVC8_P4' => 'Quick-win implementation',
        'SVC9_TITLE' => "🚀 {$service} Growth Sprints",
        'SVC9_DESC'  => "90-day intensive {$service} growth sprints for {$city} businesses that need fast results. High-intensity execution with daily progress tracking and rapid optimisation.",
        'SVC9_P1' => 'Sprint goal & KPI definition',        'SVC9_P2' => 'Daily execution & monitoring',
        'SVC9_P3' => 'Weekly sprint reviews',               'SVC9_P4' => 'Post-sprint handoff & scale plan',
        'FAQ1_Q' => "Why should a {$city} business invest in {$service}?",
        'FAQ1_A' => "{$city}'s market is rapidly moving online. Businesses investing in {$service} today capture the customers who will define tomorrow's market leaders. VG Digital Falcon ensures your {$city} business leads, not follows.",
        'FAQ2_Q' => "How soon can we start seeing results from {$service}?",
        'FAQ2_A' => "Timeline varies by service type. Initial setup and quick wins within 2–4 weeks. Meaningful, sustainable results typically build over 3–6 months. We set clear expectations during our onboarding call.",
        'FAQ3_Q' => "What is the investment required for {$service} in {$city}?",
        'FAQ3_A' => "Investment depends on scope, competition, and goals. We offer flexible packages starting at ₹10,000/month. Book a free consultation and we'll provide a custom quote after understanding your {$city} business needs.",
        'FAQ4_Q' => "How do you stay updated with {$service} changes and trends?",
        'FAQ4_A' => "Our team attends industry conferences, holds weekly learning sessions, and follows leading industry publications. We proactively update your strategy when significant platform changes occur.",
        'FAQ5_Q' => "Do you provide a dedicated manager for {$city} clients?",
        'FAQ5_A' => "Yes. Every client gets a dedicated account manager who understands your business, your {$city} market, and serves as your single point of contact for all {$service} activities.",
        'FAQ6_Q' => "Can you work alongside our existing in-house team in {$city}?",
        'FAQ6_A' => "Absolutely. Many {$city} clients use VG Digital Falcon as an extension of their in-house team. We adapt our engagement model to complement your existing resources and fill the gaps where specialist expertise is needed.",
        'FAQ7_Q' => "Do you offer performance-based pricing for {$service} in {$city}?",
        'FAQ7_A' => "We offer flexible pricing models including monthly retainers, project-based fees, and hybrid performance models for certain services. Contact us to discuss what best fits your {$city} business goals and budget.",
        'FAQ8_Q' => "How do you ensure {$service} strategy stays aligned with my {$city} business goals?",
        'FAQ8_A' => "We hold monthly strategy reviews, send bi-weekly progress updates, and adjust tactics whenever your {$city} business goals evolve. Our account managers proactively flag opportunities and risks before they impact your campaigns.",
        'REVIEW_AUTHOR' => $reviewer,
        'REVIEW_TEXT'   => $reviewPool[$reviewIdx],
    ];
}

// ─── Helper functions ─────────────────────────────────────────
function toSlug(string $text): string
{
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $text), '-'));
}

function buildUrl(string $serviceSlug, string $citySlug): string
{
    return "{$serviceSlug}-in-{$citySlug}";
}

function loadTemplate(): string
{
    if (!file_exists(TEMPLATE)) {
        throw new RuntimeException("ERROR: template.html not found at " . TEMPLATE);
    }
    $content = file_get_contents(TEMPLATE);
    if ($content === false) {
        throw new RuntimeException("ERROR: Cannot read template.html at " . TEMPLATE);
    }
    return $content;
}

function renderPage(string $template, string $service, string $city, array $servicesMeta): string
{
    $citySlug     = toSlug($city);
    $serviceSlug  = $servicesMeta['slug'];
    $servicePage  = $servicesMeta['page'];
    $urlPath      = buildUrl($serviceSlug, $citySlug);
    $canonicalUrl = 'service/' . $urlPath;

    $vars = getServiceContent($service, $city);

    // Inject universal variables
    $vars['CITY']                = $city;
    $vars['SERVICE_FULL_NAME']   = $service;
    $vars['SERVICE_SLUG']        = $servicePage;
    $vars['SERVICE_SLUG_PREFIX'] = $serviceSlug;
    $vars['CANONICAL_URL']       = $canonicalUrl;
    $vars['TRACKING_SCRIPTS']    = TRACKING_SCRIPTS;  // ← Injects GA4 + Clarity + Tawk

    // Replace all {{PLACEHOLDERS}} in template
    $html = $template;
    foreach ($vars as $key => $value) {
        $html = str_replace("{{{$key}}}", $value, $html);
    }

    // Remove any remaining unreplaced placeholders (e.g. {{SOME_UNUSED_KEY}})
    // NOTE: we deliberately do NOT strip stray single { or } characters here,
    // because the JSON-LD <script> blocks in the template rely on real
    // { } object braces. The previous version of this function stripped
    // every { and } from the whole page, which silently destroyed the
    // JSON-LD object structure on every generated page (this is what
    // caused Google's "Invalid top level element: string" error).
    $html = preg_replace('/\{\{[A-Za-z0-9_]+\}\}/', '', $html);

    return $html;
}

// ─── Main generation loop ─────────────────────────────────────
try {
    if (!$dryRun && !is_dir(OUTPUT_DIR)) {
        if (!mkdir(OUTPUT_DIR, 0755, true) && !is_dir(OUTPUT_DIR)) {
            throw new RuntimeException("Cannot create output directory: " . OUTPUT_DIR);
        }
    }

    $template   = loadTemplate();
    $generated  = [];
    $totalPages = 0;
    $skipped    = 0;
    $errors     = 0;

    echo "\n=== VG Digital Falcon – Programmatic SEO Generator ===\n";
    echo "PHP Version: "  . PHP_VERSION . "\n";
    echo "Memory Limit: " . ini_get('memory_limit') . "\n";
    echo "Mode: "         . ($isCLI ? 'CLI' : 'Web') . "\n";
    echo "Cities loaded: " . count($GLOBALS['cities'] ?? []) . "\n\n";

    foreach ($services as $serviceName => $serviceSlugPrefix) {

        if ($filterService && strtolower($filterService) !== strtolower($serviceName)) continue;

        foreach ($GLOBALS['cities'] as $city) {

            if ($filterCity && strtolower($filterCity) !== strtolower($city)) continue;

            $citySlug   = toSlug($city);
            $urlPath    = buildUrl($serviceSlugPrefix, $citySlug);
            $filename   = $urlPath . '.html';
            $filepath   = OUTPUT_DIR . '/' . $filename;
            $totalPages++;

            if ($dryRun) {
                echo "  [DRY] Would create: $filename\n";
                continue;
            }

            if (file_exists($filepath) && !$forceOverwrite) {
                $skipped++;
                continue;
            }

            try {
                $html = renderPage($template, $serviceName, $city, [
                    'slug' => $serviceSlugPrefix,
                    'page' => $servicePageSlugs[$serviceName] ?? '#',
                ]);

                $result = file_put_contents($filepath, $html);
                if ($result === false) throw new RuntimeException("Failed to write: $filepath");

                $generated[] = [
                    'service' => $serviceName,
                    'city'    => $city,
                    'url'     => BASE_URL . '/' . $urlPath,
                    'file'    => $filename,
                ];
                echo "  ✓ Generated: $filename\n";
            } catch (Exception $e) {
                $errors++;
                echo "  ✗ ERROR: $filename — " . $e->getMessage() . "\n";
            }
        }
    }

    // ─── Generate Sitemap ─────────────────────────────────────
    if (!$dryRun && count($generated) > 0) {
        $now = date('Y-m-d');
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($services as $serviceName => $serviceSlugPrefix) {
            foreach ($GLOBALS['cities'] as $city) {
                $citySlug = toSlug($city);
                $urlPath  = buildUrl($serviceSlugPrefix, $citySlug);
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . BASE_URL . '/' . $urlPath . "</loc>\n";
                $xml .= "    <lastmod>{$now}</lastmod>\n";
                $xml .= "    <changefreq>monthly</changefreq>\n";
                $xml .= "    <priority>0.8</priority>\n";
                $xml .= "  </url>\n";
            }
        }
        $xml .= '</urlset>';
        file_put_contents(SITEMAP_FILE, $xml);
        echo "\n✓ Sitemap written to: sitemap-pseo.xml\n";
    }

    // ─── Summary ──────────────────────────────────────────────
    $newCount = count($generated);
    echo "\n=== Summary ===\n";
    if ($dryRun) {
        echo "  Total pages that WOULD be generated: $totalPages\n";
    } else {
        echo "  New pages generated : $newCount\n";
        echo "  Skipped (exists)    : $skipped\n";
        echo "  Errors              : $errors\n";
        echo "  Total combinations  : $totalPages\n";
        echo "  Output directory    : " . OUTPUT_DIR . "\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n\n";
    exit(1);
}