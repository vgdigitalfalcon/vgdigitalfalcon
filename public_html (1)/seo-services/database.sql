-- VG Digital Falcon - Programmatic SEO Database
-- Run this file to set up the complete database structure

CREATE DATABASE IF NOT EXISTS vg_digital_falcon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vg_digital_falcon;

-- =============================================
-- KEYWORD CLUSTERS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS seo_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    page_type ENUM('city', 'industry', 'business') NOT NULL,
    
    -- Core identifiers
    target_keyword VARCHAR(255) NOT NULL,       -- e.g. "SEO Services in Chennai"
    location_name VARCHAR(150) DEFAULT NULL,    -- Chennai, Mumbai, etc.
    industry_name VARCHAR(150) DEFAULT NULL,    -- Healthcare, Real Estate, etc.
    business_type VARCHAR(150) DEFAULT NULL,    -- Small Business, Startup, etc.
    
    -- SEO Meta
    meta_title VARCHAR(70) NOT NULL,
    meta_description VARCHAR(160) NOT NULL,
    meta_keywords TEXT,
    canonical_url VARCHAR(500),
    schema_markup LONGTEXT,

    -- Hero Section
    hero_headline VARCHAR(255) NOT NULL,
    hero_subheadline VARCHAR(255),
    hero_cta_primary VARCHAR(80) DEFAULT 'Get Free SEO Audit',
    hero_cta_secondary VARCHAR(80) DEFAULT 'View Our Work',

    -- Stats Bar
    stat_experience VARCHAR(50) DEFAULT '10+',
    stat_projects VARCHAR(50) DEFAULT '500+',
    stat_keywords VARCHAR(50) DEFAULT '10,000+',

    -- Dynamic Content Sections
    intro_paragraph LONGTEXT,
    what_we_do_intro TEXT,
    why_trust_content LONGTEXT,
    local_context TEXT,       -- GEO-specific paragraph

    -- Sections 4 & 5 (flexible)
    section4_title VARCHAR(255),
    section4_content LONGTEXT,
    section4_type ENUM('faq','process','benefits','case_study') DEFAULT 'benefits',

    section5_title VARCHAR(255),
    section5_content LONGTEXT,
    section5_type ENUM('faq','process','testimonials','stats') DEFAULT 'faq',

    -- CTA Sections
    cta1_headline VARCHAR(255),
    cta1_subtext VARCHAR(255),
    cta2_headline VARCHAR(255),
    cta2_subtext VARCHAR(255),

    -- Status
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_type (page_type),
    INDEX idx_location (location_name),
    INDEX idx_industry (industry_name)
);

-- =============================================
-- LEAD CAPTURE TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(255),
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(30),
    website VARCHAR(300),
    business_type VARCHAR(150),
    message TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    source_keyword VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_page (page_slug)
);

-- =============================================
-- SEO SERVICES (shared across all pages)
-- =============================================
CREATE TABLE IF NOT EXISTS seo_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_key VARCHAR(80) NOT NULL UNIQUE,
    icon VARCHAR(10) NOT NULL,
    title VARCHAR(120) NOT NULL,
    short_description TEXT NOT NULL,
    bullets TEXT,  -- JSON array of bullet points
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

-- =============================================
-- TESTIMONIALS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(120) NOT NULL,
    client_designation VARCHAR(150),
    client_company VARCHAR(150),
    city VARCHAR(80),
    industry VARCHAR(80),
    rating TINYINT DEFAULT 5,
    testimonial_text TEXT NOT NULL,
    result_highlight VARCHAR(200),
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- INSERT: SEO SERVICES DATA
-- =============================================
INSERT INTO seo_services (service_key, icon, title, short_description, bullets, sort_order) VALUES
('on-page-seo', '🎯', 'On-Page SEO', 'Optimize every element on your website — from title tags to content structure — to rank higher on Google and attract the right audience.', '["Keyword-rich title & meta tags","Header tag optimization (H1–H6)","Content quality & depth improvement","Internal linking strategy","Image alt text & compression","URL structure optimization","Schema markup implementation"]', 1),
('off-page-seo', '🔗', 'Off-Page SEO', 'Build your domain authority with high-quality backlinks, brand mentions, and digital PR strategies that signal trust to search engines.', '["White-hat link building","Guest posting on authority sites","Digital PR & brand mentions","Broken link reclamation","Competitor backlink analysis","Local citations & NAP consistency","Social signal amplification"]', 2),
('technical-seo', '⚙️', 'Technical SEO', 'Fix the behind-the-scenes issues that prevent search engines from crawling, indexing, and ranking your site effectively.', '["Core Web Vitals optimization","Site speed & performance audits","Mobile-first indexing fixes","XML sitemap & robots.txt optimization","Crawl budget management","HTTPS & security compliance","Structured data markup"]', 3),
('local-seo', '📍', 'Local SEO', 'Dominate local search results and Google Maps for your city and neighborhood. Get found by customers searching near you.', '["Google Business Profile optimization","Local keyword targeting","NAP citation building","Review generation strategy","Local schema markup","Geo-targeted content creation","Local competitor analysis"]', 4),
('content-seo', '✍️', 'Content SEO', 'Create search-intent-driven content that ranks, converts, and establishes your brand as the definitive authority in your niche.', '["Keyword cluster content planning","Topic authority development","Blog & article optimization","Pillar page architecture","Content gap analysis","E-E-A-T compliance","Semantic SEO implementation"]', 5),
('ecommerce-seo', '🛒', 'E-Commerce SEO', 'Boost product visibility, category page rankings, and organic revenue for your online store with specialized e-commerce SEO.', '["Product page optimization","Category page SEO","Product schema & rich snippets","Faceted navigation fixes","Duplicate content resolution","Shopping feed optimization","Conversion rate + SEO integration"]', 6),
('seo-audit', '🔍', 'SEO Audit', 'Get a comprehensive 200+ point technical and strategic audit that uncovers exactly what is holding your website back from page one.', '["200+ point technical audit","Competitor gap analysis","Backlink toxicity review","Content quality assessment","Keyword opportunity mapping","Quick-win identification","90-day roadmap delivery"]', 7),
('enterprise-seo', '🏢', 'Enterprise SEO', 'Scalable SEO solutions for large websites, multi-location businesses, and enterprise brands competing in high-volume, high-stakes markets.', '["Multi-site SEO management","Programmatic SEO at scale","International & hreflang SEO","Cross-team SEO collaboration","Custom reporting dashboards","API-driven SEO automation","C-suite executive reporting"]', 8);

-- =============================================
-- INSERT: SAMPLE PAGE DATA — CITY PAGES
-- =============================================
INSERT INTO seo_pages (slug, page_type, target_keyword, location_name, meta_title, meta_description, hero_headline, hero_subheadline, intro_paragraph, why_trust_content, local_context, section4_title, section4_content, section4_type, section5_title, section5_content, section5_type, cta1_headline, cta1_subtext, cta2_headline, cta2_subtext) VALUES

('seo-services-chennai', 'city', 'SEO Services in Chennai', 'Chennai',
'SEO Services in Chennai | #1 SEO Agency - VG Digital Falcon',
'Top-rated SEO services in Chennai. VG Digital Falcon helps Chennai businesses rank on page 1 of Google. Get a free SEO audit today.',
'Dominate Google Search in Chennai with Expert SEO Services',
'Trusted by 500+ Chennai businesses. From Anna Nagar to OMR — we rank your brand where your customers are searching.',
'Chennai is one of India\'s fastest-growing digital markets, with over 12 million residents and a booming startup and enterprise ecosystem. Yet most businesses in Chennai are invisible on Google — losing customers to competitors who have invested in strategic SEO. At VG Digital Falcon, we specialize in Chennai SEO Services that are data-driven, locally relevant, and built for long-term rankings. Whether you are in IT, manufacturing, healthcare, or retail, our team understands the Chennai market deeply and crafts SEO strategies that deliver measurable organic growth.',
'VG Digital Falcon was founded in Chennai and has spent over a decade mastering the local digital landscape. We know how Chennai consumers search — from Tamil-language queries to hyper-local neighborhood terms like "SEO agency in Nungambakkam" or "digital marketing Anna Nagar." Our team of 30+ SEO specialists, content strategists, and technical experts has helped over 500 Chennai businesses achieve first-page rankings, driving real leads and real revenue. We don\'t just rank keywords — we grow businesses.',
'Chennai\'s competitive business districts including Guindy, OMR, Anna Nagar, T. Nagar, and Nungambakkam each have distinct search behavior patterns. Our Chennai SEO campaigns are hyper-localized — we target neighborhood-level keywords, optimize Google Business Profiles for multiple locations, and build local citations across Chennai-specific directories and news portals.',
'Our Chennai SEO Process — From Audit to Page 1',
'[{"step":"1","title":"Free SEO Audit","desc":"We analyze your website, competitors, and keyword landscape across Chennai markets — completely free, no strings attached."},{"step":"2","title":"Custom Strategy","desc":"We build a tailored SEO roadmap aligned with your Chennai business goals, target audience, and budget."},{"step":"3","title":"Technical Foundation","desc":"We fix all technical SEO issues — speed, mobile, crawlability — before building content and links."},{"step":"4","title":"Content & Link Building","desc":"We create Chennai-relevant content and earn high-authority backlinks that build your domain authority month over month."},{"step":"5","title":"Rank & Report","desc":"You get transparent monthly reports showing keyword rankings, traffic growth, and lead conversions — no vanity metrics."}]',
'process',
'Frequently Asked Questions — SEO Services in Chennai',
'[{"q":"How long does SEO take to show results in Chennai?","a":"Most Chennai businesses see measurable ranking improvements within 3–6 months. Competitive industries may take 6–12 months for top-3 positions. We provide monthly progress reports so you always know where you stand."},{"q":"How much do SEO services cost in Chennai?","a":"Our Chennai SEO packages start at ₹15,000/month for small businesses and scale up based on competition, keyword volume, and scope. Every engagement begins with a free audit so we can quote accurately."},{"q":"Do you guarantee first page rankings?","a":"No ethical SEO agency can guarantee rankings — Google\'s algorithm is not controlled by anyone. What we guarantee is a proven process, transparent reporting, and a track record of 500+ Chennai clients achieving first-page results."},{"q":"Can you do SEO in Tamil language?","a":"Yes. We create bilingual SEO strategies targeting both English and Tamil search queries, which is essential for businesses serving Tamil-speaking customers in Chennai and across Tamil Nadu."},{"q":"What industries do you serve in Chennai?","a":"We serve IT companies, hospitals, real estate developers, educational institutions, e-commerce stores, restaurants, law firms, and virtually every other vertical in the Chennai market."}]',
'faq',
'Ready to Rank #1 in Chennai?',
'Join 500+ Chennai businesses growing with VG Digital Falcon SEO',
'Stop Losing Customers to Your Chennai Competitors',
'Get your free Chennai SEO audit — no commitment, no credit card required'),

('seo-services-mumbai', 'city', 'SEO Services in Mumbai', 'Mumbai',
'SEO Services in Mumbai | Top SEO Agency - VG Digital Falcon',
'Result-driven SEO services in Mumbai. VG Digital Falcon helps Mumbai businesses rank on page 1. Free audit available.',
'Rule Google Search in Mumbai with Data-Driven SEO',
'Trusted by growing Mumbai businesses. From BKC to Andheri — we put your brand in front of customers who are ready to buy.',
'Mumbai — India\'s financial and commercial capital — is also one of the most fiercely competitive digital markets in the country. With millions of monthly searches for products and services across every category, the businesses that win in Mumbai are the ones that own page one of Google. VG Digital Falcon delivers enterprise-grade SEO Services in Mumbai that combine technical excellence, locally-relevant content, and authority link building to drive sustainable organic growth for your business.',
'Our deep expertise in Mumbai\'s diverse business landscape — from the financial hubs of BKC and Nariman Point to the startup corridors of Powai and Andheri — gives us an unmatched advantage in crafting SEO strategies that resonate with Mumbai\'s audiences. With 10+ years of experience and 500+ clients served, VG Digital Falcon is the Mumbai SEO agency that delivers rankings, traffic, and revenue.',
'Mumbai\'s distinct neighborhoods each represent unique search opportunities. BKC attracts B2B finance and consulting searches. Andheri and Powai drive tech and startup queries. South Mumbai commands premium services and legal searches. Our Mumbai SEO strategies are mapped to these micro-geographies, ensuring your business ranks where your customers actually are.',
'Our Mumbai SEO Success Process',
'[{"step":"1","title":"Mumbai Market Audit","desc":"Deep-dive analysis of your website, keyword opportunities, and top Mumbai competitors in your niche."},{"step":"2","title":"Neighborhood-Level Strategy","desc":"We build hyper-local keyword maps targeting Mumbai\'s key districts — BKC, Andheri, Powai, Worli, South Mumbai."},{"step":"3","title":"Technical Excellence","desc":"Full technical SEO implementation ensuring your site meets Google\'s Page Experience standards."},{"step":"4","title":"Authority Building","desc":"Strategic content creation and link building from high-authority Mumbai and national publications."},{"step":"5","title":"Growth Reporting","desc":"Monthly ROI-focused reports with keyword rankings, organic traffic, and lead attribution."}]',
'process',
'FAQs — SEO Services in Mumbai',
'[{"q":"How competitive is SEO in Mumbai?","a":"Mumbai is one of India\'s most competitive SEO markets. This makes professional SEO even more critical — businesses with strong SEO foundations see compounding returns as competitors struggle."},{"q":"Do you serve businesses across all Mumbai suburbs?","a":"Yes — we serve clients across Greater Mumbai including Andheri, Bandra, Kurla, BKC, Powai, Thane, Navi Mumbai, and South Mumbai."},{"q":"What is your pricing for Mumbai SEO?","a":"SEO packages for Mumbai businesses start at ₹20,000/month depending on industry competitiveness and scope. We offer a free audit before recommending any package."},{"q":"How soon will we see results?","a":"Initial ranking movements are typically visible within 60–90 days. Significant organic traffic increases generally happen between months 4–8 depending on your industry and competition level."},{"q":"Can you help with both Hindi and English SEO?","a":"Absolutely. We create bilingual content strategies targeting Hindi and English searchers, essential for Mumbai\'s diverse consumer base."}]',
'faq',
'Claim Your Mumbai SEO Advantage Today',
'Free audit reveals exactly where you are losing customers to competitors',
'Your Mumbai Competitors Are Investing in SEO — Are You?',
'Get your free SEO audit and roadmap in 48 hours');

-- =============================================
-- INSERT: INDUSTRY-SPECIFIC PAGES
-- =============================================
INSERT INTO seo_pages (slug, page_type, target_keyword, industry_name, meta_title, meta_description, hero_headline, hero_subheadline, intro_paragraph, why_trust_content, local_context, section4_title, section4_content, section4_type, section5_title, section5_content, section5_type, cta1_headline, cta1_subtext, cta2_headline, cta2_subtext) VALUES

('seo-services-healthcare', 'industry', 'SEO Services for Healthcare', 'Healthcare',
'SEO Services for Healthcare | Medical SEO Agency - VG Digital Falcon',
'Specialized healthcare SEO services for hospitals, clinics, and medical practices. Rank higher on Google and attract more patients. Free audit.',
'Get More Patients with Expert Healthcare SEO Services',
'Specialized medical SEO for hospitals, clinics & healthcare brands — HIPAA-aware, E-E-A-T compliant, and built to convert.',
'Healthcare is one of the most search-intent-driven industries on the internet. Patients search for symptoms, doctors, treatments, and hospitals before making any healthcare decision. If your hospital, clinic, or healthcare brand is not on page one of Google, you are losing potential patients to competitors every single day. VG Digital Falcon\'s Healthcare SEO Services are built specifically for the medical industry — YMYL and E-E-A-T compliant, structured for trust signals, and optimized to convert searchers into patients.',
'Healthcare SEO is governed by Google\'s highest quality standards — Your Money or Your Life (YMYL) pages require exceptional expertise, authoritativeness, and trustworthiness (E-E-A-T). Our team includes healthcare content specialists with medical writing credentials, ensuring every piece of content we create meets Google\'s strict quality bar. We have helped 50+ healthcare organizations — from multi-specialty hospitals to solo practitioners — build dominant organic search presences that drive patient volume and brand trust.',
'From "cardiologist near me" to "best orthopedic hospital in [city]" to "symptoms of diabetes," healthcare searches span thousands of intent patterns. We map your medical specialty to the exact search terms your potential patients are using, creating content and landing pages that answer their questions, build trust, and guide them to book an appointment with your practice.',
'Healthcare SEO Benefits That Drive Patient Volume',
'[{"benefit":"Rank for high-intent patient searches","desc":"Target treatment, symptom, and doctor-search queries that indicate patients ready to book."},{"benefit":"Build doctor & hospital authority","desc":"E-E-A-T optimized doctor profiles and department pages that Google trusts and ranks."},{"benefit":"Dominate local healthcare searches","desc":"Own \"best hospital near me\" and specialty searches in your city and surrounding areas."},{"benefit":"HIPAA-aware content strategy","desc":"All content created with patient privacy, medical accuracy, and regulatory compliance in mind."},{"benefit":"Appointment-driving landing pages","desc":"SEO-optimized specialty pages designed to convert organic visitors into booked consultations."},{"benefit":"Reputation & review management","desc":"Grow positive Google reviews and manage your online reputation to attract more patients."}]',
'benefits',
'Healthcare SEO FAQs',
'[{"q":"Is SEO important for hospitals and clinics?","a":"Critically important. Over 77% of patients use search engines before booking a healthcare appointment. If you are not visible on Google, you are invisible to the majority of potential patients."},{"q":"What makes healthcare SEO different?","a":"Healthcare falls under Google\'s YMYL (Your Money or Your Life) category, requiring the highest E-E-A-T standards. Content must be medically accurate, authored or reviewed by qualified professionals, and built to establish deep trust."},{"q":"Can you help with doctor profile SEO?","a":"Yes. We optimize individual doctor profiles with specialty-specific keywords, credentials, patient reviews, and schema markup that helps each doctor rank for their specialty searches."},{"q":"Do you work with multi-location hospital chains?","a":"Yes. We specialize in multi-location healthcare SEO, creating location-specific pages for each hospital or clinic branch with hyper-local keyword targeting."},{"q":"How do you handle sensitive medical content?","a":"All medical content is written by healthcare content specialists, reviewed for accuracy, and follows Google\'s E-E-A-T guidelines to ensure quality and trustworthiness."}]',
'faq',
'Get More Patients with Healthcare SEO',
'Free medical SEO audit — see exactly where you can rank for patient searches',
'Your Competitors Are Ranking for Patients You Could Be Treating',
'Request your free healthcare SEO strategy session today'),

('seo-services-real-estate', 'industry', 'SEO Services for Real Estate', 'Real Estate',
'SEO Services for Real Estate | Property SEO Agency - VG Digital Falcon',
'Specialized real estate SEO for developers, agents, and property portals. Rank on page 1 and generate more property leads. Free audit.',
'Generate More Property Leads with Real Estate SEO',
'SEO built for real estate developers, agents & property portals — rank for buyer searches and get more qualified inquiries.',
'Real estate is one of the highest-stakes digital industries — a single page-one ranking for a competitive property search can generate crores in revenue. Buyers and renters research properties extensively online before contacting an agent or developer. VG Digital Falcon\'s Real Estate SEO Services are purpose-built for property businesses — from individual agents to national developers — to rank for the exact searches your buyers are making and convert that traffic into qualified leads.',
'Our real estate SEO team understands the nuances of property search behavior — from project-specific searches to neighborhood comparison queries to investment intent searches. We have helped real estate clients achieve first-page rankings for highly competitive terms, driving consistent flows of organic leads that reduce dependence on expensive property portals and paid advertising.',
'Real estate searches are intensely local — buyers search for properties by city, neighborhood, and project name. Our real estate SEO strategy creates a comprehensive local presence for your brand, targeting every relevant geography with dedicated landing pages, neighborhood guides, and project-specific content that captures buyers at every stage of their property search journey.',
'Real Estate SEO Services That Generate Property Inquiries',
'[{"benefit":"Project & property page SEO","desc":"Rank individual project pages for location + project type + configuration searches that buyers use."},{"benefit":"Neighborhood & locality guides","desc":"Authoritative content about localities that ranks for area-research queries and builds buyer trust."},{"benefit":"Agent & broker profile optimization","desc":"Individual agent pages optimized to rank for name searches and local agent queries."},{"benefit":"Property type landing pages","desc":"Dedicated pages for 2BHK, 3BHK, villa, plot, commercial — each targeting specific configuration searches."},{"benefit":"Investment & market content","desc":"Market reports and investment guides that capture high-intent investment research searches."},{"benefit":"Portal-free lead generation","desc":"Reduce reliance on 99acres, MagicBricks by building your own organic lead channel through SEO."}]',
'benefits',
'Real Estate SEO FAQs',
'[{"q":"Can SEO compete with property portals like 99acres?","a":"Yes — and it is essential. While portals charge per lead, SEO generates leads at zero cost per acquisition once rankings are established. Many of our real estate clients now generate 60–70% of leads organically."},{"q":"How do you target buyers in specific cities?","a":"We create city and neighborhood-specific landing pages, optimize Google Business Profiles, and build locally-relevant content that ranks for geo-specific property searches."},{"q":"Can you help rank for specific project names?","a":"Absolutely. We optimize project-specific pages with the project name + city + configuration keywords, ensuring your project appears when buyers search for it directly."},{"q":"Do you work with individual real estate agents?","a":"Yes. We offer agent-specific SEO packages that include personal brand optimization, local area authority building, and Google Business Profile management."}]',
'faq',
'Generate Property Leads on Autopilot with SEO',
'Stop paying per lead — build an organic pipeline that works 24/7',
'See How Much Organic Traffic Your Real Estate Business Is Missing',
'Get your free real estate SEO audit and keyword opportunity report');

-- =============================================
-- INSERT: BUSINESS-TYPE SPECIFIC PAGES
-- =============================================
INSERT INTO seo_pages (slug, page_type, target_keyword, business_type, meta_title, meta_description, hero_headline, hero_subheadline, intro_paragraph, why_trust_content, local_context, section4_title, section4_content, section4_type, section5_title, section5_content, section5_type, cta1_headline, cta1_subtext, cta2_headline, cta2_subtext) VALUES

('seo-services-small-business', 'business', 'SEO Services for Small Business', 'Small Business',
'SEO Services for Small Business | Affordable SEO - VG Digital Falcon',
'Affordable SEO services designed for small businesses. Rank higher on Google, get more customers. Transparent pricing, no contracts.',
'Affordable SEO That Grows Your Small Business',
'Built for SMEs and local businesses — proven SEO strategies that compete with big brands without the big price tag.',
'Small businesses face an uneven playing field on Google — competing against enterprise brands with massive marketing budgets. But here is the truth: with the right SEO strategy, a small business can out-rank large corporations for the searches that matter most to their local customers. VG Digital Falcon\'s Small Business SEO Services level the playing field — giving you enterprise-grade SEO execution at a price point built for SME budgets.',
'We understand the challenges small business owners face — limited budgets, limited time, and the need for ROI you can actually measure. That is why our small business SEO packages are results-first: we identify the highest-opportunity keywords for your specific business, implement the changes that move the needle fastest, and report on metrics that directly connect to your bottom line — not vanity numbers.',
'Small businesses that rank locally on Google have a decisive advantage — local search has high purchase intent, meaning people searching for your service in your area are ready to buy. We build your local SEO foundation with Google Business Profile optimization, local citations, and neighborhood-targeted content that puts you in front of nearby customers at their moment of decision.',
'Why Small Businesses Love Our SEO Approach',
'[{"benefit":"No long-term contracts","desc":"Month-to-month flexibility — we earn your business every month through results, not lock-in clauses."},{"benefit":"Transparent, flat-rate pricing","desc":"Clear pricing with no hidden fees or surprise invoices. You know exactly what you are paying for."},{"benefit":"Quick-win focus","desc":"We identify and execute the highest-impact SEO improvements first to generate results within your budget window."},{"benefit":"Dedicated account manager","desc":"A single point of contact who knows your business, your goals, and your market — not a rotating support team."},{"benefit":"Real-time ranking dashboard","desc":"Log in anytime to see your keyword rankings, organic traffic, and lead conversions — complete transparency."},{"benefit":"Local search domination","desc":"We focus your SEO where it counts most for small businesses — local and neighborhood-level searches with buying intent."}]',
'benefits',
'Small Business SEO FAQs',
'[{"q":"Can a small business really compete on Google?","a":"Absolutely. Google\'s local search algorithm actually favors relevance and proximity over brand size. A well-optimized small business consistently out-ranks national chains for local searches."},{"q":"What is the minimum budget for small business SEO?","a":"Our small business SEO packages start at ₹10,000/month. We also offer one-time SEO setup packages for businesses just starting out."},{"q":"How long before a small business sees results?","a":"Most small businesses in moderately competitive niches see first-page rankings within 3–4 months for long-tail keywords, and broader rankings within 6–8 months."},{"q":"Do you require long-term contracts?","a":"No. All our small business SEO packages are month-to-month. We believe in earning your business through results, not trapping you in a contract."}]',
'faq',
'Grow Your Small Business with SEO That Works',
'Affordable packages starting at ₹10,000/month — no contracts, no fluff',
'See How Much Business You Are Missing Without SEO',
'Get your free small business SEO audit — takes 2 minutes to request'),

('seo-services-ecommerce', 'business', 'SEO Services for E-Commerce', 'E-Commerce',
'SEO Services for E-Commerce | Online Store SEO - VG Digital Falcon',
'Specialized e-commerce SEO to rank your products on page 1 and drive more organic sales. Free store audit available.',
'Rank Your Products on Page 1 — Drive More Organic Sales',
'Specialized e-commerce SEO for online stores — from product page optimization to category rankings that convert browsers into buyers.',
'E-commerce SEO is one of the highest-ROI investments an online store can make. Unlike paid ads that stop the moment you stop spending, organic rankings compound over time — each new page that ranks adds permanent revenue-generating assets to your store. VG Digital Falcon\'s E-Commerce SEO Services are purpose-built for online retailers, combining deep technical SEO, product content optimization, and conversion-focused architecture to grow your organic revenue month over month.',
'We have scaled organic channels for e-commerce stores across fashion, electronics, home goods, health supplements, and dozens of other verticals. Our e-commerce SEO team understands the unique challenges of large-scale stores — duplicate content from faceted navigation, thin product descriptions, crawl budget management, and the technical complexity of Shopify, WooCommerce, and custom platforms. We fix these systematically while building the content and authority your store needs to dominate category searches.',
'E-commerce search intent ranges from broad discovery ("best running shoes") to specific product queries ("Nike Air Max 2024 men size 10 blue") to comparison searches ("Nike vs Adidas running shoes review"). Our e-commerce SEO captures buyers at every stage of this funnel — creating category pages for broad searches, optimized product pages for specific queries, and buying guide content for comparison researchers.',
'E-Commerce SEO Services That Drive Revenue',
'[{"benefit":"Product page SEO at scale","desc":"Systematic optimization of hundreds or thousands of product pages with unique, conversion-focused content."},{"benefit":"Category page dominance","desc":"High-authority category pages that rank for broad, high-volume product category searches."},{"benefit":"Technical SEO for large stores","desc":"Faceted navigation fixes, crawl budget optimization, and duplicate content resolution for complex storefronts."},{"benefit":"Rich snippet implementation","desc":"Product schema, review stars, price, and availability in search results — higher CTR, more qualified traffic."},{"benefit":"Buying guide content strategy","desc":"Informational content targeting research-phase searchers who become high-converting buyers."},{"benefit":"Platform-specific expertise","desc":"Deep expertise in Shopify, WooCommerce, Magento, and custom platform SEO optimization."}]',
'benefits',
'E-Commerce SEO FAQs',
'[{"q":"How is e-commerce SEO different from regular SEO?","a":"E-commerce SEO deals with unique technical challenges — thousands of pages, faceted navigation, product variants, duplicate content — plus a focus on purchase-intent keywords and conversion optimization alongside rankings."},{"q":"Can you handle stores with thousands of products?","a":"Yes. We specialize in large-scale e-commerce SEO with systematic workflows for auditing, optimizing, and monitoring thousands of product and category pages efficiently."},{"q":"Which platforms do you support?","a":"We have deep expertise in Shopify, WooCommerce, Magento, BigCommerce, and custom-built platforms. We adapt our approach to the specific technical architecture of your store."},{"q":"How do you measure e-commerce SEO success?","a":"We track keyword rankings, organic traffic, organic revenue attribution, conversion rates from organic traffic, and return on SEO investment — all tied directly to your store\'s revenue."}]',
'faq',
'Grow Your Online Store with E-Commerce SEO',
'Free store audit reveals your biggest organic revenue opportunities',
'Your Competitors Are Winning Organic Sales You Should Be Earning',
'Get your free e-commerce SEO audit and product keyword report');

-- =============================================
-- INSERT: SAMPLE TESTIMONIALS
-- =============================================
INSERT INTO testimonials (client_name, client_designation, client_company, city, industry, rating, testimonial_text, result_highlight, is_featured) VALUES
('Rajesh Subramaniam', 'Marketing Director', 'MedTech Solutions', 'Chennai', 'Healthcare', 5, 'VG Digital Falcon completely transformed our online visibility. Within 6 months, we went from page 5 to position 1 for our most important keywords. Our patient inquiries from organic search increased by 340%. The team is incredibly knowledgeable about healthcare SEO specifically — they understand E-E-A-T requirements and create content that both patients and Google trust.', '340% increase in organic patient inquiries', 1),
('Priya Menon', 'CEO', 'Stellar Properties', 'Mumbai', 'Real Estate', 5, 'We were spending lakhs every month on property portals for leads. VG Digital Falcon built our organic SEO channel in 8 months, and now 65% of our qualified leads come from Google — completely free. The ROI has been extraordinary. Their understanding of real estate search behavior is genuinely impressive.', '65% of leads now from organic search', 1),
('Arjun Nair', 'Founder', 'SwiftCart Online', 'Bangalore', 'E-Commerce', 5, 'Our Shopify store had hundreds of products but almost no organic traffic. VG Digital Falcon did a complete technical and content overhaul — fixing our faceted navigation issues, optimizing product pages, and building category authority. Organic revenue grew 280% in one year. Best SEO investment we have ever made.', '280% growth in organic e-commerce revenue', 1),
('Sunita Sharma', 'Owner', 'Shine Dental Clinic', 'Delhi', 'Healthcare', 5, 'I was skeptical about SEO for my single-clinic dental practice. VG Digital Falcon showed me the opportunity, and within 4 months I was ranking for "best dentist in [area]" and getting 30–40 new patient inquiries per month from Google alone. The team genuinely cares about delivering results.', '30-40 new patient inquiries/month from SEO', 0),
('Mohammed Khalid', 'Digital Head', 'Urban Style Apparel', 'Chennai', 'E-Commerce', 5, 'VG Digital Falcon doubled our organic traffic in 5 months and helped us rank 500+ product keywords on page one. Their e-commerce SEO process is thorough, transparent, and genuinely effective. Would not trust anyone else with our SEO.', 'Ranked 500+ product keywords on page 1', 0);

SELECT 'Database setup complete!' as status;
SELECT COUNT(*) as total_pages FROM seo_pages;
SELECT COUNT(*) as total_services FROM seo_services;
SELECT COUNT(*) as total_testimonials FROM testimonials;
