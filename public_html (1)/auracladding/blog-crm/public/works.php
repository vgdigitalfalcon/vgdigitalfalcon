<?php
/**
 * Public Our Works / Gallery Page
 * Click project → popup with carousel + details
 * Clean URL: /our-works
 */
require_once __DIR__ . '/../config.php';
$projects = [];
$images_by_project = [];
try {
    $projects = $pdo->query("
        SELECT p.*
        FROM gallery_projects p
        WHERE p.status = 'published'
        ORDER BY p.sort_order ASC, p.updated_at DESC
    ")->fetchAll();
    $project_ids = array_column($projects, 'id');
    if ($project_ids) {
        $placeholders = implode(',', array_fill(0, count($project_ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE project_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
        $stmt->execute($project_ids);
        foreach ($stmt->fetchAll() as $img) {
            $images_by_project[$img['project_id']][] = $img;
        }
    }
} catch (Exception $e) {
    $projects = [];
}
$site_url    = rtrim(get_setting('site_url', SITE_URL), '/');
$site_name   = get_setting('site_name', SITE_NAME);
$works_title = get_setting('works_title', 'Our Work. Your Inspiration.');
$works_desc  = get_setting('works_description', 'A showcase of our premium cladding and glazing projects that combine innovation, precision and exceptional craftsmanship.');
$meta_title  = get_setting('works_meta_title', 'Our Works | Aura Cladding – Premium Cladding & Glazing Projects');
$meta_desc   = get_setting('works_meta_description', $works_desc);
$logo_url    = $site_url . '/image/aura-logo.png';
$banner_url  = $site_url . '/image/glazing-banner.png';
$favicon_url = $site_url . '/image/favicon.png';
$quote_url   = $site_url . '/send-quote.php';
$company_phone   = '+91 86828 78886';
$company_email   = 'info@auracladding.com';
$company_website = 'www.auracladding.com';
$company_address = '101, Kamarajar High Road, Pachaiamman Koil St, Thanthai Periyar Nagar, Virugambakkam, Tamil Nadu 600092';
$map_embed = 'https://www.google.com/maps?q=101,+Kamarajar+High+Road,+Pachaiamman+Koil+St,+Thanthai+Periyar+Nagar,+Virugambakkam,+Tamil+Nadu+600092&output=embed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title><?= e($meta_title) ?></title>
<link rel="icon" type="image/png" href="<?= e($favicon_url) ?>">
<link rel="shortcut icon" type="image/png" href="<?= e($favicon_url) ?>">
<link rel="apple-touch-icon" href="<?= e($favicon_url) ?>">
<meta name="description" content="<?= e($meta_desc) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<link rel="canonical" href="<?= e(works_list_url()) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($works_title) ?>">
<meta property="og:description" content="<?= e($works_desc) ?>">
<meta property="og:url" content="<?= e(works_list_url()) ?>">
<meta property="og:site_name" content="<?= e($site_name) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($works_title) ?>">
<meta name="twitter:description" content="<?= e($works_desc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary-dark: #051329;
    --primary-light: #0a1f3f;
    --accent-orange: #f37021;
    --accent-hover: #d95e1a;
    --text-main: #333333;
    --text-light: #666666;
    --bg-light: #f8f9fa;
    --white: #ffffff;
    --border-color: #e5e5e5;
    --gray-50: #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.05);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.08);
    --transition: all 0.3s ease;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
html, body { max-width: 100%; overflow-x: hidden; }
body { font-family: 'Inter', 'Poppins', system-ui, -apple-system, sans-serif; color: var(--text-main); background: var(--white); line-height: 1.6; }
body.modal-open { overflow: hidden; }
a { text-decoration: none; color: inherit; transition: var(--transition); }
img { max-width: 100%; display: block; }
ul { list-style: none; }
.container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.navbar { background-color: var(--primary-dark); color: var(--white); padding: 10px 0; position: sticky; top: 0; z-index: 1000; }
.nav-container { display: flex; justify-content: space-between; align-items: center; }
.logo { display: flex; align-items: center; flex-shrink: 0; }
.logo img { height: 68px; max-height: 68px; width: auto; object-fit: contain; }
.nav-links { display: flex; gap: 28px; align-items: center; }
.nav-links > li { position: relative; }
.nav-links > li > a { font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.9); display: flex; align-items: center; gap: 5px; position: relative; }
.nav-links > li > a:hover, .nav-links > li > a.active { color: var(--accent-orange); }
.nav-links > li > a.active::after { content: ''; position: absolute; bottom: -8px; left: 0; width: 100%; height: 2px; background: var(--accent-orange); border-radius: 2px; }
.dropdown-menu { visibility: hidden; opacity: 0; pointer-events: none; position: absolute; top: 100%; left: 0; background: transparent; min-width: 240px; box-shadow: none; border-radius: 6px; padding: 14px 0 0; z-index: 1001; margin-top: 0; transition: opacity 0.18s ease, visibility 0.18s ease; }
.dropdown-menu::before { content: ''; position: absolute; top: 14px; left: 0; right: 0; bottom: 0; background: var(--white); border-radius: 6px; box-shadow: var(--shadow-md); z-index: -1; }
.nav-links li.has-dropdown:hover > .dropdown-menu,
.nav-links li.has-dropdown:focus-within > .dropdown-menu,
.nav-links li.has-dropdown.dropdown-open > .dropdown-menu { visibility: visible; opacity: 1; pointer-events: auto; }
.dropdown-menu li a { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: var(--text-main); font-size: 14px; font-weight: 500; }
.dropdown-menu li a i { color: var(--accent-orange); width: 18px; text-align: center; font-size: 15px; }
.dropdown-menu li a:hover { background: #fff5ef; color: var(--accent-orange); }
.btn-quote { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: var(--accent-orange); color: var(--white); font-weight: 600; border-radius: 4px; font-size: 14px; box-shadow: 0 4px 15px rgba(243,112,33,0.25); cursor: pointer; }
.btn-quote:hover { background: var(--accent-hover); color: #fff; transform: translateY(-1px); }
.menu-toggle { display: none; font-size: 24px; cursor: pointer; color: var(--white); background: none; border: none; padding: 4px 8px; }
.works-hero { position: relative; min-height: 460px; display: flex; align-items: center; background: linear-gradient(90deg, rgba(5,19,41,0.92) 0%, rgba(5,19,41,0.75) 45%, rgba(5,19,41,0.4) 100%), url('<?= e($banner_url) ?>') center center / cover no-repeat; color: var(--white); padding: 80px 0 90px; }
.works-hero-content { max-width: 580px; position: relative; z-index: 1; }
.breadcrumb { font-size: 14px; color: rgba(255,255,255,0.7); margin-bottom: 20px; }
.breadcrumb a { color: rgba(255,255,255,0.7); }
.breadcrumb a:hover { color: var(--accent-orange); }
.breadcrumb span { color: var(--accent-orange); }
.works-hero h1 { font-size: 46px; font-weight: 700; line-height: 1.15; margin-bottom: 16px; }
.works-hero h1 span { color: var(--accent-orange); }
.works-hero p { font-size: 16px; color: rgba(255,255,255,0.85); line-height: 1.7; max-width: 480px; }
.works-stats { margin-top: -48px; position: relative; z-index: 5; padding-bottom: 10px; }
.stats-row { background: white; border-radius: 14px; box-shadow: 0 12px 40px rgba(5,19,41,0.12); display: grid; grid-template-columns: repeat(4, 1fr); padding: 28px 20px; gap: 12px; }
.stat-item { display: flex; align-items: center; gap: 14px; padding: 0 16px; border-right: 1px solid var(--gray-100); }
.stat-item:last-child { border-right: none; }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(243,112,33,0.12); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.stat-item strong { display: block; font-size: 24px; font-weight: 800; color: var(--primary-dark); line-height: 1.2; }
.stat-item span { font-size: 13px; color: var(--gray-500); }
.works-section { padding: 70px 0 80px; background: #fff; }
.section-header { text-align: center; margin-bottom: 20px; }
.section-label { font-size: 12px; font-weight: 600; letter-spacing: 1.5px; color: var(--accent-orange); margin-bottom: 8px; text-transform: uppercase; }
.section-header h2 { font-size: 32px; font-weight: 700; color: var(--primary-dark); margin-bottom: 12px; }
.section-sub { max-width: 620px; margin: 0 auto 16px; font-size: 15px; color: var(--gray-500); line-height: 1.7; text-align: center; }
.section-line { width: 50px; height: 3px; background: var(--accent-orange); margin: 0 auto; border-radius: 2px; }
.works-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-top: 36px; }
.work-card { background: white; border-radius: 14px; overflow: hidden; border: 1px solid var(--gray-100); box-shadow: 0 4px 20px rgba(5,19,41,0.06); transition: 0.25s; cursor: pointer; }
.work-card:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(5,19,41,0.12); }
.work-card:focus-visible { outline: 2px solid var(--accent-orange); outline-offset: 2px; }
.work-carousel { position: relative; height: 220px; overflow: hidden; background: var(--gray-100); }
.carousel-track { height: 100%; position: relative; }
.carousel-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.35s ease; }
.carousel-slide.active { opacity: 1; z-index: 1; }
.carousel-slide img { width: 100%; height: 100%; object-fit: cover; }
.carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 3; width: 34px; height: 34px; border-radius: 50%; border: none; background: rgba(5,19,41,0.65); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; opacity: 0; transition: 0.2s; }
.work-card:hover .carousel-btn { opacity: 1; }
.carousel-btn:hover { background: var(--accent-orange); color: #fff; }
.carousel-btn.prev { left: 10px; }
.carousel-btn.next { right: 10px; }
.carousel-dots { position: absolute; bottom: 12px; left: 0; right: 0; z-index: 3; display: flex; justify-content: center; gap: 6px; }
.carousel-dots .dot { width: 8px; height: 8px; border-radius: 50%; border: none; background: rgba(255,255,255,0.5); cursor: pointer; padding: 0; }
.carousel-dots .dot.active { background: var(--accent-orange); width: 18px; border-radius: 4px; }
.work-body { padding: 18px 20px 22px; }
.work-meta-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px; }
.work-meta-row:last-child { margin-bottom: 0; }
.work-meta .meta-label { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; letter-spacing: 0.4px; color: var(--accent-orange); margin-bottom: 4px; text-transform: uppercase; }
.work-meta strong { display: block; font-size: 14px; font-weight: 600; color: var(--primary-dark); line-height: 1.35; }
.work-view-hint { margin-top: 12px; font-size: 12px; font-weight: 600; color: var(--accent-orange); display: flex; align-items: center; gap: 6px; }
.empty-works { text-align: center; padding: 80px 20px; color: var(--gray-400); }
.empty-works i { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
.project-modal { position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; transition: opacity 0.25s ease, visibility 0.25s ease; }
.project-modal.open { opacity: 1; visibility: visible; }
.project-modal-backdrop { position: absolute; inset: 0; background: rgba(5, 19, 41, 0.75); backdrop-filter: blur(4px); }
.project-modal-dialog { position: relative; z-index: 1; background: #fff; border-radius: 16px; width: 100%; max-width: 860px; max-height: calc(100vh - 40px); overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 25px 80px rgba(0,0,0,0.35); transform: translateY(20px) scale(0.98); transition: transform 0.25s ease; }
.project-modal.open .project-modal-dialog { transform: translateY(0) scale(1); }
.project-modal-close { position: absolute; top: 14px; right: 14px; z-index: 10; width: 40px; height: 40px; border-radius: 50%; border: none; background: rgba(5,19,41,0.7); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.project-modal-close:hover { background: var(--accent-orange); }
.modal-carousel { position: relative; height: 380px; background: #0a1f3f; flex-shrink: 0; }
.modal-carousel-track { height: 100%; position: relative; }
.modal-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.35s ease; }
.modal-slide.active { opacity: 1; z-index: 1; }
.modal-slide img { width: 100%; height: 100%; object-fit: cover; }
.modal-carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 5; width: 44px; height: 44px; border-radius: 50%; border: none; background: rgba(255,255,255,0.9); color: var(--primary-dark); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.2); }
.modal-carousel-btn:hover { background: var(--accent-orange); color: #fff; }
.modal-carousel-btn.prev { left: 16px; }
.modal-carousel-btn.next { right: 16px; }
.modal-dots { position: absolute; bottom: 16px; left: 0; right: 0; z-index: 5; display: flex; justify-content: center; gap: 8px; }
.modal-dots .dot { width: 9px; height: 9px; border-radius: 50%; border: none; background: rgba(255,255,255,0.45); cursor: pointer; padding: 0; }
.modal-dots .dot.active { background: var(--accent-orange); width: 22px; border-radius: 5px; }
.modal-body { padding: 28px 32px 32px; overflow-y: auto; flex: 1; }
.modal-title { font-size: 24px; font-weight: 700; color: var(--primary-dark); margin-bottom: 20px; line-height: 1.3; padding-right: 40px; }
.modal-details { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 28px; }
.modal-detail-item { display: flex; gap: 12px; align-items: flex-start; }
.modal-detail-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(243,112,33,0.1); color: var(--accent-orange); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.modal-detail-item .label { font-size: 11px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; color: var(--accent-orange); margin-bottom: 4px; }
.modal-detail-item .value { font-size: 15px; font-weight: 600; color: var(--primary-dark); line-height: 1.4; }
.modal-materials { grid-column: 1 / -1; }
.modal-materials .value { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.material-chip { display: inline-block; padding: 6px 12px; background: #fff5ef; color: var(--accent-orange); border-radius: 20px; font-size: 13px; font-weight: 600; }
.cta-banner { background: linear-gradient(135deg, var(--primary-dark), var(--primary-light)); padding: 60px 0; color: #fff; }
.cta-inner { display: flex; align-items: center; justify-content: space-between; gap: 40px; }
.cta-text h2 { font-size: 28px; font-weight: 700; line-height: 1.3; margin-bottom: 10px; }
.cta-text p { color: rgba(255,255,255,0.7); font-size: 15px; }
.cta-actions { display: flex; gap: 14px; flex-shrink: 0; flex-wrap: wrap; }
.btn-primary { background: var(--accent-orange); color: #fff; padding: 14px 24px; border-radius: 4px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.btn-primary:hover { background: var(--accent-hover); color: #fff; }
.btn-outline-light { border: 1.5px solid rgba(255,255,255,0.4); color: #fff; padding: 14px 24px; border-radius: 4px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
.btn-outline-light:hover { background: rgba(255,255,255,0.1); }
.footer { background: var(--primary-dark); color: rgba(255,255,255,0.7); padding-top: 60px; }
.footer-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.3fr 1.2fr; gap: 32px; padding-bottom: 50px; }
.footer-logo { margin-bottom: 16px; }
.footer-logo img { height: 64px; max-height: 64px; width: auto; object-fit: contain; }
.footer-col p { font-size: 13px; line-height: 1.7; margin-bottom: 18px; text-align: left; }
.social-icons { display: flex; gap: 10px; }
.social-icons a { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: white; font-size: 13px; }
.social-icons a:hover { background: var(--accent-orange); color: #fff; }
.footer-col h4 { color: white; font-size: 13px; font-weight: 700; letter-spacing: 0.8px; margin-bottom: 18px; text-transform: uppercase; }
.footer-links li { margin-bottom: 10px; }
.footer-links a, .contact-info li { font-size: 13px; color: rgba(255,255,255,0.65); }
.footer-links a:hover { color: var(--accent-orange); }
.contact-info li { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
.contact-info i { color: var(--accent-orange); margin-top: 3px; width: 16px; flex-shrink: 0; }
.contact-info a:hover { color: var(--accent-orange); }
.map-embed { width: 100%; height: 140px; border: 0; border-radius: 8px; background: rgba(255,255,255,0.06); }
.footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding: 18px 0; font-size: 12px; }
.footer-bottom .container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.footer-bottom-links a { color: rgba(255,255,255,0.5); margin-left: 8px; }
.footer-bottom-links a:hover { color: var(--accent-orange); }
.whatsapp-float { position: fixed; right: 22px; bottom: 22px; width: 58px; height: 58px; border-radius: 50%; background: #25D366; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 8px 22px rgba(37,211,102,0.45); z-index: 1400; transition: var(--transition); }
.whatsapp-float:hover { transform: translateY(-3px) scale(1.06); background: #1ebe57; color: #fff; }
.quote-modal-overlay { position: fixed; inset: 0; background: rgba(5,19,41,0.72); display: none; align-items: center; justify-content: center; z-index: 2100; padding: 18px; }
.quote-modal-overlay.active { display: flex; }
.quote-modal { background: #fff; width: 100%; max-width: 540px; border-radius: 12px; padding: 32px 28px 26px; position: relative; box-shadow: var(--shadow-md); max-height: 92vh; overflow-y: auto; }
.quote-modal h3 { color: var(--primary-dark); font-size: 24px; margin-bottom: 6px; }
.quote-modal .modal-sub { color: var(--text-light); font-size: 14px; margin-bottom: 22px; }
.quote-close { position: absolute; top: 12px; right: 14px; width: 34px; height: 34px; border: none; background: var(--bg-light); border-radius: 50%; cursor: pointer; font-size: 18px; color: var(--text-main); }
.quote-close:hover { background: #eee; color: var(--accent-orange); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--primary-dark); margin-bottom: 6px; }
.form-group input, .form-group select { width: 100%; height: 46px; border: 1px solid var(--border-color); border-radius: 6px; padding: 0 14px; font-family: 'Poppins', sans-serif; font-size: 14px; color: var(--text-main); background: #fff; outline: none; }
.form-group input:focus, .form-group select:focus { border-color: var(--accent-orange); box-shadow: 0 0 0 3px rgba(243,112,33,0.12); }
.quote-modal .btn { width: 100%; text-align: center; margin-top: 6px; justify-content: center; display: inline-flex; align-items: center; background: var(--accent-orange); color: #fff; padding: 12px 20px; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; }
.form-status { display: none; margin-top: 12px; font-size: 13px; text-align: center; padding: 10px 12px; border-radius: 6px; }
.form-status.success { display: block; background: #e8f8ef; color: #147a3f; }
.form-status.error { display: block; background: #fdecea; color: #b42318; }
@media (max-width: 992px) {
    .menu-toggle { display: block; }
    .nav-links { display: none; position: absolute; top: 100%; left: 0; right: 0; background: var(--primary-dark); flex-direction: column; align-items: flex-start; padding: 16px 20px 24px; gap: 0; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.08); }
    .nav-links.active { display: flex; }
    .nav-links > li { width: 100%; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .nav-links > li > a { padding: 14px 0; width: 100%; }
    .nav-links > li > a.active::after { display: none; }
    .dropdown-menu { position: static; visibility: hidden; opacity: 0; pointer-events: none; height: 0; overflow: hidden; box-shadow: none; background: rgba(0,0,0,0.2); margin: 0 0 8px; border-radius: 6px; min-width: 100%; padding: 0; }
    .dropdown-menu::before { display: none; }
    .nav-links li.has-dropdown.dropdown-open > .dropdown-menu { visibility: visible; opacity: 1; pointer-events: auto; height: auto; overflow: visible; padding: 5px 0; display: block; }
    .dropdown-menu li a { color: rgba(255,255,255,0.85); padding: 10px 16px; }
    .dropdown-menu li a:hover { background: rgba(243,112,33,0.15); color: var(--accent-orange); }
    .btn-quote { display: none; }
    .works-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .stat-item { border-right: none; border-bottom: 1px solid var(--gray-100); padding: 12px 16px; }
    .stat-item:nth-child(3), .stat-item:nth-child(4) { border-bottom: none; }
    .footer-grid { grid-template-columns: 1fr 1fr; }
    .cta-inner { flex-direction: column; text-align: center; }
    .cta-actions { justify-content: center; }
    .works-hero h1 { font-size: 34px; }
    .modal-carousel { height: 280px; }
    .modal-details { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .works-grid { grid-template-columns: 1fr; }
    .stats-row { grid-template-columns: 1fr; }
    .stat-item { border-bottom: 1px solid var(--gray-100); }
    .stat-item:last-child { border-bottom: none; }
    .works-hero { min-height: 380px; padding: 60px 0 80px; }
    .works-hero h1 { font-size: 28px; }
    .work-meta-row { grid-template-columns: 1fr; gap: 10px; }
    .footer-grid { grid-template-columns: 1fr; }
    .footer-bottom .container { flex-direction: column; text-align: center; }
.logo img { height: 84px; max-height: 96px; width: auto; }
    .section-header h2 { font-size: 26px; }
    .project-modal { padding: 12px; }
    .project-modal-dialog { max-height: calc(100vh - 24px); border-radius: 12px; }
    .modal-carousel { height: 220px; }
    .modal-body { padding: 20px 18px 24px; }
    .modal-title { font-size: 20px; margin-bottom: 16px; }
    .whatsapp-float { width: 52px; height: 52px; font-size: 26px; right: 16px; bottom: 16px; }
    .quote-modal { padding: 26px 16px 20px; }
    .form-row { grid-template-columns: 1fr 1fr; gap: 10px; }
}
</style>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "<?= e($works_title) ?>",
    "description": "<?= e($works_desc) ?>",
    "url": "<?= e(works_list_url()) ?>",
    "publisher": { "@type": "Organization", "name": "<?= e($site_name) ?>", "url": "<?= e($site_url) ?>" }
}
</script>
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
            <li><a href="<?= e(works_list_url()) ?>" class="active">Our Works</a></li>
            <li><a href="<?= e(blog_list_url()) ?>">Blog</a></li>
            <li><a href="<?= e($site_url) ?>/contact.html">Contact Us</a></li>
        </ul>
        <a href="#quote" class="btn-quote open-quote-modal">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
    </div>
</header>
<section class="works-hero">
    <div class="container">
        <div class="works-hero-content">
            <div class="breadcrumb"><a href="<?= e($site_url) ?>">Home</a> &nbsp;/&nbsp; <span>Our Works</span></div>
            <h1>Our Work.<br><span>Your Inspiration.</span></h1>
            <p><?= e($works_desc) ?></p>
        </div>
    </div>
</section>
<section class="works-stats">
    <div class="container">
        <div class="stats-row">
            <div class="stat-item"><div class="stat-icon"><i class="fas fa-building"></i></div><div><strong>500+</strong><span>Projects Completed</span></div></div>
            <div class="stat-item"><div class="stat-icon"><i class="fas fa-users"></i></div><div><strong>250+</strong><span>Happy Clients</span></div></div>
            <div class="stat-item"><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div><div><strong>10+</strong><span>Years Experience</span></div></div>
            <div class="stat-item"><div class="stat-icon"><i class="fas fa-award"></i></div><div><strong>98%</strong><span>Client Satisfaction</span></div></div>
        </div>
    </div>
</section>
<section class="works-section" id="projects">
    <div class="container">
        <div class="section-header">
            <div class="section-label">OUR TRANSFORMATION</div>
            <h2>See the Difference We Create</h2>
            <p class="section-sub">Explore our real projects transformations through before-and-after experiences that showcase the impact of our premium cladding and glazing solutions.</p>
            <div class="section-line"></div>
        </div>
        <?php if (empty($projects)): ?>
        <div class="empty-works"><i class="fas fa-images"></i><p>Projects coming soon. Check back later!</p></div>
        <?php else: ?>
        <div class="works-grid">
            <?php foreach ($projects as $p):
                $imgs = $images_by_project[$p['id']] ?? [];
                $mats = [];
                if (!empty($p['materials'])) {
                    $mats = is_string($p['materials']) ? json_decode($p['materials'], true) : $p['materials'];
                    if (!is_array($mats)) $mats = [];
                }
                $material_str = $mats ? implode(' · ', $mats) : '—';
                $fallback = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=500&fit=crop';
                $carousel_id = 'carousel-' . (int)$p['id'];
                $img_urls = [];
                $img_alts = [];
                if (!empty($imgs)) {
                    foreach ($imgs as $img) {
                        $img_urls[] = upload_url($img['image_path']);
                        $img_alts[] = $img['alt_text'] ?: $p['title'];
                    }
                } else {
                    $img_urls[] = $fallback;
                    $img_alts[] = $p['title'];
                }
            ?>
            <article class="work-card" role="button" tabindex="0"
                     data-project-id="<?= (int)$p['id'] ?>"
                     data-title="<?= e($p['title']) ?>"
                     data-location="<?= e($p['location'] ?: '—') ?>"
                     data-area="<?= e($p['area'] ?: '—') ?>"
                     data-materials="<?= e(json_encode(array_values($mats))) ?>"
                     data-images="<?= e(json_encode($img_urls)) ?>"
                     data-alts="<?= e(json_encode($img_alts)) ?>"
                     onclick="openProjectModal(this)"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openProjectModal(this);}">
                <div class="work-carousel" id="<?= e($carousel_id) ?>" data-index="0">
                    <div class="carousel-track">
                        <?php if (!empty($imgs)): ?>
                            <?php foreach ($imgs as $idx => $img): ?>
                            <div class="carousel-slide <?= $idx === 0 ? 'active' : '' ?>">
                                <img src="<?= e(upload_url($img['image_path'])) ?>" alt="<?= e($img['alt_text'] ?: $p['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e($fallback) ?>';">
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-slide active"><img src="<?= e($fallback) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($imgs) > 1): ?>
                    <button type="button" class="carousel-btn prev" aria-label="Previous" onclick="event.stopPropagation();carouselPrev('<?= e($carousel_id) ?>')"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="carousel-btn next" aria-label="Next" onclick="event.stopPropagation();carouselNext('<?= e($carousel_id) ?>')"><i class="fas fa-chevron-right"></i></button>
                    <div class="carousel-dots">
                        <?php foreach ($imgs as $idx => $img): ?>
                        <button type="button" class="dot <?= $idx === 0 ? 'active' : '' ?>" onclick="event.stopPropagation();carouselGo('<?= e($carousel_id) ?>', <?= (int)$idx ?>)" aria-label="Slide <?= (int)$idx + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="work-body">
                    <div class="work-meta-row">
                        <div class="work-meta"><span class="meta-label"><i class="fas fa-folder-open"></i> PROJECT</span><strong><?= e($p['title']) ?></strong></div>
                        <div class="work-meta"><span class="meta-label"><i class="fas fa-ruler-combined"></i> AREA</span><strong><?= e($p['area'] ?: '—') ?></strong></div>
                    </div>
                    <div class="work-meta-row">
                        <div class="work-meta"><span class="meta-label"><i class="fas fa-map-marker-alt"></i> LOCATION</span><strong><?= e($p['location'] ?: '—') ?></strong></div>
                        <div class="work-meta"><span class="meta-label"><i class="fas fa-layer-group"></i> MATERIAL</span><strong><?= e($material_str) ?></strong></div>
                    </div>
                    <div class="work-view-hint"><i class="fas fa-expand"></i> View details</div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<section class="cta-banner">
    <div class="container cta-inner">
        <div class="cta-text">
            <h2>Have a Project in Mind?<br>Let's Build It Together.</h2>
            <p>From concept to completion, we deliver cladding and glazing solutions that elevate every space.</p>
        </div>
        <div class="cta-actions">
            <a href="#quote" class="btn-primary open-quote-modal">Get a Free Quote <i class="fas fa-arrow-right"></i></a>
            <a href="<?= e($site_url) ?>/contact.html" class="btn-outline-light">Contact Our Team <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-logo"><a href="<?= e($site_url) ?>"><img src="<?= e($logo_url) ?>" alt="<?= e($site_name) ?>" onerror="this.style.display='none'"></a></div>
                <p>We craft high-performance cladding solutions that blend aesthetics, durability and innovation.</p>
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
                <iframe class="map-embed" src="<?= e($map_embed) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Aura Cladding Location – Virugambakkam, Chennai"></iframe>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container" style="max-width:1200px;padding:0;">
                <p>&copy; <?= date('Y') ?> Aura Cladding. All Rights Reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a> |
                    <a href="#">Terms &amp; Conditions</a>
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
                <div class="form-group"><label for="quoteName">Name</label><input type="text" id="quoteName" name="name" placeholder="Your full name" required></div>
                <div class="form-group"><label for="quotePhone">Phone Number</label><input type="tel" id="quotePhone" name="phone" placeholder="10-digit mobile number" required></div>
            </div>
            <div class="form-group"><label for="quoteLocation">Location</label><input type="text" id="quoteLocation" name="location" placeholder="Your city / location" required></div>
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

<div class="project-modal" id="projectModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" hidden>
    <div class="project-modal-backdrop" onclick="closeProjectModal()"></div>
    <div class="project-modal-dialog">
        <button type="button" class="project-modal-close" onclick="closeProjectModal()" aria-label="Close"><i class="fas fa-times"></i></button>
        <div class="modal-carousel" id="modalCarousel" data-index="0">
            <div class="modal-carousel-track" id="modalCarouselTrack"></div>
            <button type="button" class="modal-carousel-btn prev" onclick="modalCarouselPrev()" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="modal-carousel-btn next" onclick="modalCarouselNext()" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
            <div class="modal-dots" id="modalDots"></div>
        </div>
        <div class="modal-body">
            <h2 class="modal-title" id="modalTitle"></h2>
            <div class="modal-details">
                <div class="modal-detail-item"><div class="modal-detail-icon"><i class="fas fa-folder-open"></i></div><div><div class="label">Project</div><div class="value" id="modalProject"></div></div></div>
                <div class="modal-detail-item"><div class="modal-detail-icon"><i class="fas fa-ruler-combined"></i></div><div><div class="label">Area</div><div class="value" id="modalArea"></div></div></div>
                <div class="modal-detail-item"><div class="modal-detail-icon"><i class="fas fa-map-marker-alt"></i></div><div><div class="label">Location</div><div class="value" id="modalLocation"></div></div></div>
                <div class="modal-detail-item modal-materials"><div class="modal-detail-icon"><i class="fas fa-layer-group"></i></div><div style="flex:1;"><div class="label">Materials</div><div class="value" id="modalMaterials"></div></div></div>
            </div>
        </div>
    </div>
</div>
<script>
function carouselGo(id, index) {
    var el = document.getElementById(id);
    if (!el) return;
    var slides = el.querySelectorAll('.carousel-slide');
    var dots = el.querySelectorAll('.dot');
    if (!slides.length) return;
    index = ((index % slides.length) + slides.length) % slides.length;
    el.dataset.index = index;
    slides.forEach(function(s, i) { s.classList.toggle('active', i === index); });
    dots.forEach(function(d, i) { d.classList.toggle('active', i === index); });
}
function carouselNext(id) { var el = document.getElementById(id); if (!el) return; carouselGo(id, parseInt(el.dataset.index || 0, 10) + 1); }
function carouselPrev(id) { var el = document.getElementById(id); if (!el) return; carouselGo(id, parseInt(el.dataset.index || 0, 10) - 1); }
var modalImages = [];
var modalAlts = [];
function modalCarouselGo(index) {
    var el = document.getElementById('modalCarousel');
    if (!el || !modalImages.length) return;
    index = ((index % modalImages.length) + modalImages.length) % modalImages.length;
    el.dataset.index = index;
    el.querySelectorAll('.modal-slide').forEach(function(s, i) { s.classList.toggle('active', i === index); });
    el.querySelectorAll('.modal-dots .dot').forEach(function(d, i) { d.classList.toggle('active', i === index); });
}
function modalCarouselNext() { var el = document.getElementById('modalCarousel'); if (!el) return; modalCarouselGo(parseInt(el.dataset.index || 0, 10) + 1); }
function modalCarouselPrev() { var el = document.getElementById('modalCarousel'); if (!el) return; modalCarouselGo(parseInt(el.dataset.index || 0, 10) - 1); }
function openProjectModal(card) {
    var title = card.getAttribute('data-title') || '';
    var location = card.getAttribute('data-location') || '—';
    var area = card.getAttribute('data-area') || '—';
    var materials = []; var images = []; var alts = [];
    try { materials = JSON.parse(card.getAttribute('data-materials') || '[]'); } catch(e) {}
    try { images = JSON.parse(card.getAttribute('data-images') || '[]'); } catch(e) {}
    try { alts = JSON.parse(card.getAttribute('data-alts') || '[]'); } catch(e) {}
    if (!Array.isArray(materials)) materials = [];
    if (!Array.isArray(images) || !images.length) { images = ['https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&h=800&fit=crop']; alts = [title]; }
    modalImages = images; modalAlts = alts;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalProject').textContent = title;
    document.getElementById('modalArea').textContent = area;
    document.getElementById('modalLocation').textContent = location;
    var matsEl = document.getElementById('modalMaterials');
    matsEl.innerHTML = materials.length ? materials.map(function(m) { return '<span class="material-chip">' + escapeHtml(m) + '</span>'; }).join('') : '<span class="value" style="font-weight:600;color:#051329;">—</span>';
    var track = document.getElementById('modalCarouselTrack');
    var dots = document.getElementById('modalDots');
    track.innerHTML = ''; dots.innerHTML = '';
    images.forEach(function(src, i) {
        var slide = document.createElement('div');
        slide.className = 'modal-slide' + (i === 0 ? ' active' : '');
        var img = document.createElement('img');
        img.src = src; img.alt = alts[i] || title; img.loading = 'lazy';
        img.onerror = function() { this.onerror = null; this.src = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&h=800&fit=crop'; };
        slide.appendChild(img); track.appendChild(slide);
        var dot = document.createElement('button');
        dot.type = 'button'; dot.className = 'dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('aria-label', 'Slide ' + (i + 1));
        dot.onclick = function() { modalCarouselGo(i); };
        dots.appendChild(dot);
    });
    var showNav = images.length > 1;
    document.querySelectorAll('.modal-carousel-btn').forEach(function(btn) { btn.style.display = showNav ? 'flex' : 'none'; });
    dots.style.display = showNav ? 'flex' : 'none';
    document.getElementById('modalCarousel').dataset.index = '0';
    var modal = document.getElementById('projectModal');
    modal.hidden = false; modal.classList.add('open'); document.body.classList.add('modal-open');
}
function closeProjectModal() {
    var modal = document.getElementById('projectModal');
    modal.classList.remove('open');
    document.body.classList.remove('modal-open');
    setTimeout(function() { modal.hidden = true; }, 250);
}
function escapeHtml(str) { var d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
document.addEventListener('keydown', function(e) {
    var quoteModal = document.getElementById('quoteModal');
    if (e.key === 'Escape') {
        if (quoteModal && quoteModal.classList.contains('active')) { quoteModal.classList.remove('active'); document.body.classList.remove('modal-open'); return; }
        closeProjectModal();
    }
    var modal = document.getElementById('projectModal');
    if (modal && modal.classList.contains('open')) {
        if (e.key === 'ArrowRight') modalCarouselNext();
        if (e.key === 'ArrowLeft') modalCarouselPrev();
    }
});
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
            if (window.innerWidth <= 992) { e.preventDefault(); e.stopPropagation(); servicesItem.classList.toggle('dropdown-open'); }
        });
        servicesItem.addEventListener('mouseenter', function() {
            if (window.innerWidth > 992) { clearTimeout(dropdownTimer); servicesItem.classList.add('dropdown-open'); }
        });
        servicesItem.addEventListener('mouseleave', function() {
            if (window.innerWidth > 992) { dropdownTimer = setTimeout(function() { servicesItem.classList.remove('dropdown-open'); }, 180); }
        });
    }
    document.querySelectorAll('.nav-links a:not(.services-toggle)').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992 && navLinks) {
                navLinks.classList.remove('active');
                if (menuToggle) { menuToggle.setAttribute('aria-expanded', 'false'); menuToggle.innerHTML = '<i class="fas fa-bars"></i>'; }
                if (servicesItem) servicesItem.classList.remove('dropdown-open');
            }
        });
    });
    document.addEventListener('click', function(e) {
        var navbar = document.querySelector('.navbar');
        if (navbar && !navbar.contains(e.target) && navLinks) {
            navLinks.classList.remove('active');
            if (menuToggle) { menuToggle.setAttribute('aria-expanded', 'false'); menuToggle.innerHTML = '<i class="fas fa-bars"></i>'; }
            if (servicesItem) servicesItem.classList.remove('dropdown-open');
        }
    });
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992 && navLinks) {
            navLinks.classList.remove('active');
            if (menuToggle) { menuToggle.setAttribute('aria-expanded', 'false'); menuToggle.innerHTML = '<i class="fas fa-bars"></i>'; }
            if (servicesItem) servicesItem.classList.remove('dropdown-open');
        }
    });
    var quoteModal = document.getElementById('quoteModal');
    var quoteForm = document.getElementById('quoteForm');
    var quoteStatus = document.getElementById('quoteStatus');
    var quoteSubmit = document.getElementById('quoteSubmit');
    var quoteClose = document.getElementById('quoteClose');
    function openQuoteModal(e) { if (e) e.preventDefault(); if (!quoteModal) return; quoteModal.classList.add('active'); document.body.classList.add('modal-open'); }
    function closeQuoteModal() { if (!quoteModal) return; quoteModal.classList.remove('active'); document.body.classList.remove('modal-open'); }
    document.querySelectorAll('.open-quote-modal').forEach(function(btn) { btn.addEventListener('click', openQuoteModal); });
    if (quoteClose) quoteClose.addEventListener('click', closeQuoteModal);
    if (quoteModal) quoteModal.addEventListener('click', function(e) { if (e.target === quoteModal) closeQuoteModal(); });
    if (quoteForm) {
        quoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var name = document.getElementById('quoteName').value.trim();
            var phone = document.getElementById('quotePhone').value.trim();
            var location = document.getElementById('quoteLocation').value.trim();
            var service = document.getElementById('quoteService').value;
            if (!name || !phone || !location || !service) { quoteStatus.className = 'form-status error'; quoteStatus.textContent = 'Please fill in all the fields.'; return; }
            quoteSubmit.disabled = true; quoteSubmit.textContent = 'Sending...'; quoteStatus.className = 'form-status'; quoteStatus.textContent = '';
            var formData = new FormData(quoteForm); formData.append('ajax', '1');
            fetch(quoteForm.getAttribute('action'), { method: 'POST', body: formData })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.success) { quoteStatus.className = 'form-status success'; quoteStatus.textContent = 'Thank you! Your enquiry has been sent. We will contact you soon.'; quoteForm.reset(); setTimeout(closeQuoteModal, 1800); }
                else { throw new Error((data && data.message) || 'Failed'); }
            })
            .catch(function() { quoteStatus.className = 'form-status error'; quoteStatus.textContent = 'Unable to send right now. Please try again or call us.'; })
            .finally(function() { quoteSubmit.disabled = false; quoteSubmit.textContent = 'Submit Enquiry'; });
        });
    }
})();
</script>
</body>
</html>