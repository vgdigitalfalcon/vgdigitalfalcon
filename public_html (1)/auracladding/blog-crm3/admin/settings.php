<?php
/**
 * General Settings — Site URL, company info, etc.
 * This is the main place to change Site URL for subdomain hosting
 */
require_once __DIR__ . '/../config.php';
require_login();

if (!is_admin()) {
    set_flash('danger', 'Only administrators can access Settings.');
    header('Location: index.php');
    exit;
}

$page_title = 'Settings';
$page_subtitle = 'Site URL, company details and system preferences';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $keys = [
        'site_name',
        'site_url',
        'blog_title',
        'blog_description',
        'default_meta_title',
        'default_meta_description',
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'twitter_handle',
        'facebook_url',
        'linkedin_url',
        'posts_per_page'
    ];

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
            // Remove trailing slash from site_url
            if ($key === 'site_url') {
                $value = rtrim($value, '/');
            }
            $stmt->execute([$key, $value]);
        }
    }

    // Also update robots.txt sitemap line if site_url changed
    if (!empty($_POST['site_url'])) {
        $new_url = rtrim(trim($_POST['site_url']), '/');
        $robots = get_setting('robots_txt');
        // Refresh robots with new sitemap URL
        regenerate_sitemap();
    }

    set_flash('success', 'Settings saved successfully. Site URL updated.');
    header('Location: settings.php');
    exit;
}

require __DIR__ . '/includes/header.php';
?>

<div class="alert alert-info" style="margin-bottom:24px;">
    <i class="fas fa-info-circle"></i>
    <div>
        <strong>Site URL is the most important setting.</strong><br>
        Because you are currently hosting on a subdomain, set the full URL of the site where the public blog is accessible<br>
        (example: <code>https://blog.yourdomain.com</code> or <code>https://subdomain.othersite.com</code>).
        Do <strong>not</strong> add a trailing slash.
    </div>
</div>

<form method="POST">
<input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">

    <!-- Left Column -->
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fas fa-globe" style="margin-right:8px;color:var(--orange);"></i> Site URL (Required)</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Site URL <span class="required">*</span></label>
                    <input type="url" name="site_url" class="form-control" required
                           value="<?= e(get_setting('site_url', SITE_URL)) ?>"
                           placeholder="https://blog.yourdomain.com">
                    <div class="form-hint">
                        Full URL of the website/subdomain where the public blog is hosted.<br>
                        Used for: Sitemap, Canonical URLs, Open Graph, Schema, “View Blog” button.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="site_name" class="form-control"
                           value="<?= e(get_setting('site_name', 'Aura Cladding')) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Blog Page Title</label>
                    <input type="text" name="blog_title" class="form-control"
                           value="<?= e(get_setting('blog_title')) ?>">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Blog Page Description</label>
                    <textarea name="blog_description" class="form-control" rows="3"><?= e(get_setting('blog_description')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Default SEO Meta</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Default Meta Title</label>
                    <input type="text" name="default_meta_title" class="form-control"
                           value="<?= e(get_setting('default_meta_title')) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Default Meta Description</label>
                    <textarea name="default_meta_description" class="form-control" rows="3"><?= e(get_setting('default_meta_description')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Company / Publisher Info</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control"
                           value="<?= e(get_setting('company_name', 'Aura Cladding')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control"
                           value="<?= e(get_setting('company_address')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="company_phone" class="form-control"
                           value="<?= e(get_setting('company_phone')) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Email</label>
                    <input type="email" name="company_email" class="form-control"
                           value="<?= e(get_setting('company_email')) ?>">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Social Profiles</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Twitter / X Handle</label>
                    <input type="text" name="twitter_handle" class="form-control"
                           value="<?= e(get_setting('twitter_handle')) ?>" placeholder="@auracladding">
                </div>
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-control"
                           value="<?= e(get_setting('facebook_url')) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" class="form-control"
                           value="<?= e(get_setting('linkedin_url')) ?>">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Blog Listing</h3></div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Posts Per Page</label>
                    <input type="number" name="posts_per_page" class="form-control" min="3" max="24"
                           value="<?= e(get_setting('posts_per_page', '9')) ?>">
                    <div class="form-hint">Default is 9 (matches the design grid).</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding:14px;">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </div>
</div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
