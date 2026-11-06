<?php
// ============================================================
// VG Digital Falcon — Bulk Page Generator
// Run via CLI: php generate-pages.php
// Or access via browser: /generate-pages.php?secret=YOUR_KEY
// ============================================================
declare(strict_types=1);
ini_set('max_execution_time', '300');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Simple auth check (change this key!)
$SECRET = 'vgdf_generate_2024';
$isCli  = php_sapi_name() === 'cli';

if (!$isCli && ($_GET['secret'] ?? '') !== $SECRET) {
    http_response_code(403);
    die('Forbidden');
}

// ============================================================
// ALL CITIES DATA
// ============================================================
$cities = [
    ['name' => 'Bangalore',    'state' => 'Karnataka',     'hub' => 'Whitefield, Koramangala, HSR Layout',    'pop' => '12 million',  'nickname' => 'Silicon Valley of India'],
    ['name' => 'Delhi',        'state' => 'Delhi NCR',      'hub' => 'Connaught Place, Cyber City, Noida',     'pop' => '32 million',  'nickname' => "India's capital"],
    ['name' => 'Hyderabad',    'state' => 'Telangana',      'hub' => 'HITEC City, Banjara Hills, Gachibowli',  'pop' => '10 million',  'nickname' => 'City of Pearls'],
    ['name' => 'Pune',         'state' => 'Maharashtra',    'hub' => 'Hinjewadi, Koregaon Park, Viman Nagar',  'pop' => '7 million',   'nickname' => 'Oxford of the East'],
    ['name' => 'Ahmedabad',    'state' => 'Gujarat',        'hub' => 'SG Highway, Prahladnagar, Navrangpura',  'pop' => '8 million',   'nickname' => "India's Manchester"],
    ['name' => 'Coimbatore',   'state' => 'Tamil Nadu',     'hub' => 'Avinashi Road, Peelamedu, RS Puram',    'pop' => '2 million',   'nickname' => 'Manchester of South India'],
    ['name' => 'Kolkata',      'state' => 'West Bengal',    'hub' => 'Park Street, Salt Lake, New Town',       'pop' => '15 million',  'nickname' => 'City of Joy'],
    ['name' => 'Jaipur',       'state' => 'Rajasthan',      'hub' => 'Vaishali Nagar, Malviya Nagar, C-Scheme','pop' => '4 million',   'nickname' => 'Pink City'],
    ['name' => 'Surat',        'state' => 'Gujarat',        'hub' => 'Ring Road, Vesu, Adajan',                'pop' => '7 million',   'nickname' => 'Diamond City of India'],
    ['name' => 'Lucknow',      'state' => 'Uttar Pradesh',  'hub' => 'Hazratganj, Gomti Nagar, Aliganj',       'pop' => '4 million',   'nickname' => 'City of Nawabs'],
    ['name' => 'Kochi',        'state' => 'Kerala',         'hub' => 'Infopark, Smart City, Kakkanad',         'pop' => '2.5 million', 'nickname' => 'Queen of the Arabian Sea'],
    ['name' => 'Chandigarh',   'state' => 'Punjab/Haryana', 'hub' => 'Sector 17, IT Park, Panchkula',          'pop' => '1.2 million', 'nickname' => 'City Beautiful'],
    ['name' => 'Indore',       'state' => 'Madhya Pradesh', 'hub' => 'Vijay Nagar, AB Road, Palasia',          'pop' => '3 million',   'nickname' => 'Mini Mumbai'],
    ['name' => 'Madurai',      'state' => 'Tamil Nadu',     'hub' => 'Anna Nagar, KK Nagar, Bypass Road',      'pop' => '1.5 million', 'nickname' => 'Temple City'],
    ['name' => 'Vizag',        'state' => 'Andhra Pradesh', 'hub' => 'Rushikonda, MVP Colony, Madhurawada',    'pop' => '2.5 million', 'nickname' => 'Jewel of the East Coast'],
];

// ============================================================
// ALL INDUSTRIES DATA
// ============================================================
$industries = [
    [
        'name'    => 'Education',
        'slug'    => 'seo-services-education',
        'keyword' => 'SEO Services for Education',
        'hero'    => 'Attract More Students with Education SEO Services',
        'sub'     => 'SEO built for schools, colleges, EdTech platforms & coaching institutes — rank for student search queries and drive admissions.',
        'intro'   => 'The education sector is one of the most competitive digital markets in India. Students and parents extensively research institutions, courses, and coaching centers online before making admission decisions. VG Digital Falcon\'s Education SEO Services help schools, colleges, universities, and EdTech platforms rank for the exact search queries your prospective students are using — driving organic inquiries and admissions at zero cost per lead.',
        'why'     => 'Our education SEO team understands student search behavior at every stage — from course discovery to institution comparison to admission inquiries. We have helped 30+ educational institutions achieve first-page rankings for competitive terms, significantly reducing their dependence on paid advertising for student acquisition.',
        'cta1'    => 'Get More Student Inquiries with Education SEO',
        'cta2'    => 'Stop Paying Per Lead — Build an Organic Admissions Channel',
    ],
    [
        'name'    => 'Legal',
        'slug'    => 'seo-services-legal',
        'keyword' => 'SEO Services for Law Firms',
        'hero'    => 'Get More Clients with Law Firm SEO Services',
        'sub'     => 'SEO designed for advocates, law firms & legal consultancies — rank for high-value legal search queries and attract qualified clients.',
        'intro'   => 'Legal services is one of the highest-value industries for SEO investment — clients searching for lawyers are often in urgent situations with high decision-making intent. A single page-one ranking for a key practice area can generate crores in fee revenue. VG Digital Falcon\'s Law Firm SEO Services are built for advocates, solicitors, and legal consultancies — helping you rank for the exact legal queries your potential clients are searching.',
        'why'     => 'Legal SEO requires a unique blend of content authority, local targeting, and practice area specificity. Our legal SEO team creates authoritative, jurisdiction-specific content that ranks for practice area searches while building trust with potential clients who are evaluating legal representation.',
        'cta1'    => 'Attract More Legal Clients with SEO',
        'cta2'    => 'Every Search Is a Potential Client — Make Sure They Find You',
    ],
    [
        'name'    => 'IT Companies',
        'slug'    => 'seo-services-it-companies',
        'keyword' => 'SEO Services for IT Companies',
        'hero'    => 'Generate More B2B Leads with IT Company SEO',
        'sub'     => 'Specialized SEO for software companies, IT firms & tech startups — rank for service keywords and generate qualified B2B inquiries organically.',
        'intro'   => 'India\'s IT and technology sector is intensely competitive online — thousands of software companies, IT services firms, and tech startups compete for the same B2B buyers on Google. VG Digital Falcon\'s IT Company SEO Services help technology businesses rank for service-specific, technology-stack, and industry-vertical keywords that attract qualified procurement decision-makers and generate organic B2B leads.',
        'why'     => 'B2B technology buyers conduct extensive online research before vendor selection. Our IT SEO strategy creates comprehensive content ecosystems — service pages, solution guides, case studies, and comparison content — that capture B2B buyers at every stage of their research journey and establish your IT company as the authoritative choice.',
        'cta1'    => 'Generate More B2B Tech Leads with SEO',
        'cta2'    => 'Your Ideal B2B Buyers Are Searching for Your Services Right Now',
    ],
    [
        'name'    => 'Restaurants',
        'slug'    => 'seo-services-restaurants',
        'keyword' => 'SEO Services for Restaurants',
        'hero'    => 'Fill More Tables with Restaurant SEO Services',
        'sub'     => 'Local SEO for restaurants, cafes & food businesses — rank for "restaurants near me" searches and drive more walk-in and delivery orders.',
        'intro'   => 'Restaurant searches are among the most local, high-intent queries on Google — "restaurants near me," "best biryani in [city]," "rooftop cafe [area]." People searching these terms are ready to visit or order within minutes. VG Digital Falcon\'s Restaurant SEO Services ensure your restaurant appears at the top of these searches — in Google Maps, the local 3-pack, and organic results — driving more footfall and delivery orders every single day.',
        'why'     => 'Our restaurant SEO team specializes in local search optimization — Google Business Profile management, location-specific keyword targeting, review strategy, and food-category content creation. We have helped 40+ restaurants and food businesses dramatically increase their organic visibility and customer footfall.',
        'cta1'    => 'Drive More Customers to Your Restaurant with SEO',
        'cta2'    => 'People Are Searching for Your Food Right Now — Are They Finding You?',
    ],
    [
        'name'    => 'Finance',
        'slug'    => 'seo-services-finance',
        'keyword' => 'SEO Services for Finance Companies',
        'hero'    => 'Grow Your Finance Business with Expert SEO Services',
        'sub'     => 'YMYL-compliant SEO for banks, NBFCs, insurance, mutual funds & fintech — rank for high-intent financial search queries and attract qualified leads.',
        'intro'   => 'Financial services is a YMYL (Your Money or Your Life) industry — Google applies its highest scrutiny to financial content, requiring exceptional expertise, authority, and trust signals. VG Digital Falcon\'s Finance SEO Services are purpose-built for this compliance requirement — our financial content team creates E-E-A-T-compliant content that ranks for high-value financial queries while meeting Google\'s rigorous quality standards.',
        'why'     => 'We have deep experience in financial services SEO across banking, insurance, investment, lending, and fintech verticals. Our financial SEO strategies combine regulatory compliance awareness, authoritative content creation, and technical excellence to drive sustainable organic growth for financial brands.',
        'cta1'    => 'Attract More Financial Clients with SEO',
        'cta2'    => 'Your Competitors Are Winning High-Value Financial Clients Through SEO',
    ],
];

// ============================================================
// ALL BUSINESS TYPES DATA
// ============================================================
$businessTypes = [
    [
        'name'    => 'Startups',
        'slug'    => 'seo-services-startups',
        'keyword' => 'SEO Services for Startups',
        'hero'    => 'Scale Your Startup Faster with Strategic SEO Services',
        'sub'     => 'SEO built for early-stage and growth-stage startups — build organic traction, reduce CAC, and create a sustainable growth channel from day one.',
        'intro'   => 'For startups, SEO is one of the highest-ROI growth channels available — it compounds over time, builds brand authority, and reduces customer acquisition costs as organic traffic grows. VG Digital Falcon\'s Startup SEO Services are designed for the unique constraints and growth goals of early-stage and growth-stage companies — building organic foundations that scale with your business.',
        'why'     => 'We understand the startup growth context — limited budgets, urgent traction needs, and the importance of scalable, compounding channels. Our startup SEO approach prioritizes quick wins alongside long-term foundation building, so you see early results while building the organic authority that will sustain growth as you scale.',
        'cta1'    => 'Build Organic Growth for Your Startup',
        'cta2'    => 'Organic SEO: The Growth Channel That Compounds as You Scale',
    ],
    [
        'name'    => 'Local Business',
        'slug'    => 'seo-services-local-business',
        'keyword' => 'SEO Services for Local Business',
        'hero'    => 'Dominate Local Search and Grow Your Business',
        'sub'     => 'Local SEO for shops, service providers & neighborhood businesses — rank for "near me" searches and get found by customers in your area.',
        'intro'   => 'Local search is the lifeblood of neighborhood businesses — salons, plumbers, electricians, clinics, retail shops, and service providers all depend on customers finding them in local Google searches. VG Digital Falcon\'s Local Business SEO Services ensure your business appears prominently in Google Maps and local organic results for every relevant search in your area — driving more phone calls, walk-ins, and bookings from nearby customers.',
        'why'     => 'We have helped hundreds of local businesses achieve top-3 Google Maps positions and page-one organic rankings in their cities and neighborhoods. Our local SEO methodology covers every element of local search prominence — from Google Business Profile optimization to local citation building to review acquisition.',
        'cta1'    => 'Get Found by Local Customers Ready to Buy',
        'cta2'    => 'Every Day Without Local SEO Is Revenue Your Neighborhood Competitors Are Taking',
    ],
    [
        'name'    => 'Enterprise',
        'slug'    => 'seo-services-enterprise',
        'keyword' => 'Enterprise SEO Services India',
        'hero'    => 'Scalable Enterprise SEO Built for Market Leadership',
        'sub'     => 'Enterprise-grade SEO for large organizations — multi-site management, international SEO, custom reporting, and strategic SEO at scale.',
        'intro'   => 'Enterprise organizations face unique SEO challenges — large-scale websites with thousands of pages, multiple stakeholders, complex technical architectures, multi-market and multi-language requirements, and the need for robust reporting tied to business KPIs. VG Digital Falcon\'s Enterprise SEO Services are built for this complexity — delivering scalable, systematic SEO that moves the needle for large organizations competing at the highest level.',
        'why'     => 'Our enterprise SEO team brings the operational rigor, technical depth, and strategic sophistication required to manage complex, large-scale SEO programs. We have delivered enterprise SEO for organizations with 100,000+ page websites, international operations, and C-suite reporting requirements — consistently driving measurable organic revenue growth.',
        'cta1'    => 'Build an Organic Revenue Engine for Your Enterprise',
        'cta2'    => 'Enterprise SEO Strategy That Aligns with Your Business Objectives',
    ],
];

// ============================================================
// GENERATE CITY PAGES
// ============================================================
function generateCityPage(array $city, PDO $pdo): bool {
    $cityName = $city['name'];
    $slug     = 'seo-services-' . slugify($cityName);

    // Check if already exists
    $exists = DB::queryOne("SELECT id FROM seo_pages WHERE slug = ?", [$slug]);
    if ($exists) {
        echo "SKIP (exists): $slug\n";
        return false;
    }

    $keyword   = "SEO Services in $cityName";
    $state     = $city['state'];
    $hub       = $city['hub'];
    $pop       = $city['pop'];
    $nickname  = $city['nickname'];

    $introPara = "$cityName — $nickname with a population of $pop — is one of India's most dynamic digital markets. Businesses across every sector in $state are investing heavily in online visibility, making professional SEO a competitive necessity rather than a luxury. VG Digital Falcon's SEO Services in $cityName combine local market expertise with data-driven strategy to deliver first-page rankings that translate directly into leads, customers, and revenue for your $cityName business.";

    $whyTrust = "VG Digital Falcon has been delivering measurable SEO results for businesses in $cityName and across $state for over 10 years. Our team understands the $cityName market — from the search behavior of consumers in $hub to the competitive landscape of your specific industry. We have helped 500+ Indian businesses achieve first-page Google rankings, and our $cityName clients benefit from this deep national experience combined with hyper-local market knowledge.";

    $localCtx = "$cityName's key business districts — including $hub — each represent unique digital opportunities. Our $cityName SEO campaigns are tailored to these micro-geographies, targeting neighborhood-level keywords, optimizing Google Business Profiles for local prominence, and building $cityName-relevant content authority that ranks for the searches your ideal customers are actually making.";

    $s4Content = json_encode([
        ['step' => '1', 'title' => "$cityName SEO Audit", 'desc' => "Comprehensive analysis of your website, keyword landscape, and top $cityName competitors — completely free."],
        ['step' => '2', 'title' => 'Custom Strategy', 'desc' => "A tailored SEO roadmap built for your $cityName business goals, target audience, and competitive environment."],
        ['step' => '3', 'title' => 'Technical Foundation', 'desc' => "Full technical SEO implementation — speed, mobile, crawlability — optimized to Google's current standards."],
        ['step' => '4', 'title' => "Content & Authority", 'desc' => "$cityName-relevant content creation and strategic link building that grows your domain authority every month."],
        ['step' => '5', 'title' => 'Results & Reporting', 'desc' => "Monthly reports showing keyword rankings, organic traffic growth, and lead attribution — no vanity metrics."],
    ]);

    $s5Content = json_encode([
        ['q' => "How long does SEO take to show results in $cityName?", 'a' => "Most $cityName businesses see measurable ranking improvements within 3–6 months. Competitive industries may take 6–12 months for top-3 positions. We provide monthly progress reports throughout."],
        ['q' => "How much does SEO cost for a $cityName business?", 'a' => "Our $cityName SEO packages start at ₹15,000/month for small businesses, scaling based on competition and scope. We start every engagement with a free audit to quote accurately."],
        ['q' => "Do you guarantee first-page rankings?", 'a' => "No ethical agency can guarantee rankings — Google's algorithm is not owned by anyone. We guarantee a proven process, full transparency, and a track record of 500+ clients achieving first-page results."],
        ['q' => "Do you serve all areas of $cityName?", 'a' => "Yes. We serve businesses across all major areas and suburbs of $cityName and neighboring districts in $state."],
        ['q' => "What industries do you serve in $cityName?", 'a' => "We serve healthcare, real estate, IT, e-commerce, education, legal, retail, hospitality, and virtually every other industry in the $cityName market."],
    ]);

    DB::execute(
        "INSERT INTO seo_pages (slug, page_type, target_keyword, location_name, 
         meta_title, meta_description, hero_headline, hero_subheadline,
         intro_paragraph, why_trust_content, local_context,
         section4_title, section4_content, section4_type,
         section5_title, section5_content, section5_type,
         cta1_headline, cta1_subtext, cta2_headline, cta2_subtext)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $slug, 'city', $keyword, $cityName,
            "SEO Services in $cityName | #1 SEO Agency - VG Digital Falcon",
            "Expert SEO services in $cityName. VG Digital Falcon helps $cityName businesses rank on page 1 of Google. Free SEO audit available today.",
            "Dominate Google Search in $cityName with Expert SEO Services",
            "Trusted by growing $cityName businesses. Rank where your customers are searching and grow your revenue with organic SEO.",
            $introPara, $whyTrust, $localCtx,
            "Our $cityName SEO Process — From Audit to Page 1",
            $s4Content, 'process',
            "Frequently Asked Questions — SEO Services in $cityName",
            $s5Content, 'faq',
            "Ready to Rank #1 in $cityName?",
            "Join 500+ Indian businesses growing organically with VG Digital Falcon",
            "Stop Losing $cityName Customers to Your Competitors",
            "Get your free $cityName SEO audit — delivered within 48 hours"
        ]
    );

    echo "CREATED: $slug\n";
    return true;
}

// ============================================================
// GENERATE INDUSTRY PAGES
// ============================================================
function generateIndustryPage(array $ind): bool {
    $slug = $ind['slug'];

    $exists = DB::queryOne("SELECT id FROM seo_pages WHERE slug = ?", [$slug]);
    if ($exists) {
        echo "SKIP (exists): $slug\n";
        return false;
    }

    $name = $ind['name'];

    $s5Content = json_encode([
        ['q' => "Why does the $name industry need specialized SEO?", 'a' => "The $name industry has unique search behaviors, compliance requirements, and buyer journeys that require industry-specific SEO strategies. Generic SEO approaches miss the nuances that drive results in this sector."],
        ['q' => "How soon will we see results?", 'a' => "Most clients in the $name sector see measurable ranking improvements within 3–6 months, with significant traffic growth typically occurring between months 4–8."],
        ['q' => "Do you understand our industry's regulations?", 'a' => "Yes. Our industry-specific SEO teams are briefed on relevant compliance considerations for each sector we serve, ensuring content and strategy align with regulatory requirements."],
        ['q' => "Can you help with both national and local $name SEO?", 'a' => "Absolutely. We create $name SEO strategies that capture both national/brand awareness searches and hyper-local searches from customers near your location."],
    ]);

    DB::execute(
        "INSERT INTO seo_pages (slug, page_type, target_keyword, industry_name,
         meta_title, meta_description, hero_headline, hero_subheadline,
         intro_paragraph, why_trust_content, local_context,
         section5_title, section5_content, section5_type,
         cta1_headline, cta1_subtext, cta2_headline, cta2_subtext)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $slug, 'industry', $ind['keyword'], $name,
            "$name SEO Services | Industry-Specific SEO - VG Digital Falcon",
            "Specialized SEO services for the $name industry. VG Digital Falcon helps $name businesses rank on page 1 and grow organic revenue. Free audit.",
            $ind['hero'], $ind['sub'],
            $ind['intro'], $ind['why'],
            "Our $name SEO strategies are built on deep industry knowledge, covering the specific keyword patterns, content formats, and authority signals that drive rankings and conversions in this sector.",
            "Frequently Asked Questions — $name SEO Services",
            $s5Content, 'faq',
            $ind['cta1'], "Free $name SEO audit — no commitment required",
            $ind['cta2'], "Request your free $name SEO strategy session today"
        ]
    );

    echo "CREATED: $slug\n";
    return true;
}

// ============================================================
// GENERATE BUSINESS TYPE PAGES
// ============================================================
function generateBusinessPage(array $biz): bool {
    $slug = $biz['slug'];

    $exists = DB::queryOne("SELECT id FROM seo_pages WHERE slug = ?", [$slug]);
    if ($exists) {
        echo "SKIP (exists): $slug\n";
        return false;
    }

    $name = $biz['name'];

    $s5Content = json_encode([
        ['q' => "What makes SEO different for $name?", 'a' => "SEO for $name has unique considerations around budget, timeline, keyword targeting, and content strategy compared to other business types. Our approach is tailored specifically to the $name context."],
        ['q' => "How quickly can a $name business see SEO results?", 'a' => "Most $name clients see measurable progress within 3–4 months for initial keywords, with significant ranking improvements in months 4–8 depending on competition and industry."],
        ['q' => "Is SEO worth it for a $name?", 'a' => "SEO typically delivers 10–20x ROI for businesses of all sizes. For $name specifically, organic traffic creates a sustainable, compounding growth channel that reduces long-term customer acquisition costs."],
        ['q' => "What do your $name SEO packages include?", 'a' => "Our $name SEO packages include technical SEO, keyword strategy, content creation, link building, local SEO (where relevant), and monthly reporting — everything needed for complete search visibility."],
    ]);

    DB::execute(
        "INSERT INTO seo_pages (slug, page_type, target_keyword, business_type,
         meta_title, meta_description, hero_headline, hero_subheadline,
         intro_paragraph, why_trust_content, local_context,
         section5_title, section5_content, section5_type,
         cta1_headline, cta1_subtext, cta2_headline, cta2_subtext)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            $slug, 'business', $biz['keyword'], $name,
            "{$biz['keyword']} | VG Digital Falcon",
            "Specialized SEO services for $name. VG Digital Falcon helps $name rank on page 1 of Google and grow organically. Free SEO audit available.",
            $biz['hero'], $biz['sub'],
            $biz['intro'], $biz['why'],
            "Our $name SEO approach is built around your specific growth goals — identifying the highest-opportunity keywords for your business type and creating a systematic path to organic visibility and lead generation.",
            "Frequently Asked Questions — SEO for $name",
            $s5Content, 'faq',
            $biz['cta1'], "Free SEO audit designed for $name",
            $biz['cta2'], "Start your organic growth journey — no commitment required"
        ]
    );

    echo "CREATED: $slug\n";
    return true;
}

// ============================================================
// RUN GENERATOR
// ============================================================
$created = 0;
$skipped = 0;

echo "\n=== VG Digital Falcon — Page Generator ===\n\n";

echo "--- Generating City Pages ---\n";
foreach ($cities as $city) {
    $r = generateCityPage($city, DB::connect());
    $r ? $created++ : $skipped++;
}

echo "\n--- Generating Industry Pages ---\n";
foreach ($industries as $ind) {
    $r = generateIndustryPage($ind);
    $r ? $created++ : $skipped++;
}

echo "\n--- Generating Business Type Pages ---\n";
foreach ($businessTypes as $biz) {
    $r = generateBusinessPage($biz);
    $r ? $created++ : $skipped++;
}

echo "\n=== Done! Created: $created | Skipped: $skipped ===\n\n";
