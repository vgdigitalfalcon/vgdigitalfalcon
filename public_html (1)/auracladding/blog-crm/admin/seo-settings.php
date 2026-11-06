<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'SEO & GEO Settings';
$page_subtitle = 'Global SEO defaults + Generative Engine Optimization';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $keys = [
        'site_name', 'site_url', 'blog_title', 'blog_description',
        'default_meta_title', 'default_meta_description',
        'company_address', 'company_phone', 'company_email', 'company_name',
        'twitter_handle', 'facebook_url', 'linkedin_url'
    ];
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($keys as $key) {
        if (isset($_POST[$key])) $stmt->execute([$key, trim($_POST[$key])]);
    }
    set_flash('success', 'Settings saved.');
    header('Location: seo-settings.php');
    exit;
}

require __DIR__ . '/includes/header.php';
?>

<form method="POST">
<input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    <div class="card">
        <div class="card-header"><h3>Site & Blog Defaults</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Site Name</label>
                <input type="text" name="site_name" class="form-control" value="<?= e(get_setting('site_name')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Site URL (no trailing slash)</label>
                <input type="url" name="site_url" class="form-control" value="<?= e(get_setting('site_url')) ?>" placeholder="https://www.auracladding.com">
                <div class="form-hint">Used for sitemap, canonical URLs and schema.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Blog Page Title</label>
                <input type="text" name="blog_title" class="form-control" value="<?= e(get_setting('blog_title')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Blog Page Description</label>
                <textarea name="blog_description" class="form-control" rows="3"><?= e(get_setting('blog_description')) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Default Meta Title</label>
                <input type="text" name="default_meta_title" class="form-control" value="<?= e(get_setting('default_meta_title')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Default Meta Description</label>
                <textarea name="default_meta_description" class="form-control" rows="3"><?= e(get_setting('default_meta_description')) ?></textarea>
            </div>
        </div>
    </div>
    
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Company / Publisher (for Schema)</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?= e(get_setting('company_name')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="company_address" class="form-control" value="<?= e(get_setting('company_address')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="company_phone" class="form-control" value="<?= e(get_setting('company_phone')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="company_email" class="form-control" value="<?= e(get_setting('company_email')) ?>">
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Social Profiles</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Twitter / X Handle</label>
                    <input type="text" name="twitter_handle" class="form-control" value="<?= e(get_setting('twitter_handle')) ?>" placeholder="@auracladding">
                </div>
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-control" value="<?= e(get_setting('facebook_url')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" class="form-control" value="<?= e(get_setting('linkedin_url')) ?>">
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Save All Settings</button>
            </div>
        </div>
    </div>
</div>
</form>

<div class="card" style="margin-top:24px;">
    <div class="card-header"><h3>Generative Engine Optimization Tips</h3></div>
    <div class="card-body" style="font-size:14px;color:#475569;line-height:1.7;">
        <p><strong>GEO</strong> = Generative Engine Optimization — making your content easy for AI systems (Google AI Overviews, ChatGPT, Perplexity, Claude, etc.) to understand, cite and surface.</p>
        <ul style="margin:12px 0 0 20px;">
            <li>Always fill <strong>AI Summary</strong> with a clear, factual 1–3 sentence direct answer.</li>
            <li>Add <strong>Key Takeaways</strong> as short bullet points.</li>
            <li>Use the <strong>FAQ builder</strong> — FAQ schema is one of the strongest signals for AI Overviews.</li>
            <li>Write with clear H2/H3 headings and short paragraphs.</li>
            <li>Cite authoritative sources — AI engines prefer well-referenced content.</li>
            <li>Keep featured images at <strong>1200 × 630 px</strong> for best social + AI preview rendering.</li>
        </ul>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
