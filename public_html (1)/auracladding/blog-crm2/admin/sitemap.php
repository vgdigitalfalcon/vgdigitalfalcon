<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'Sitemap & Robots';
$page_subtitle = 'Automatic XML Sitemap + AI-crawler friendly robots.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    if (isset($_POST['regenerate'])) {
        regenerate_sitemap();
        set_flash('success', 'Sitemap and robots.txt regenerated successfully!');
    }
    if (isset($_POST['update_robots'])) {
        $robots = $_POST['robots_txt'] ?? '';
        $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('robots_txt', ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$robots]);
        file_put_contents(__DIR__ . '/../public/robots.txt', $robots);
        set_flash('success', 'robots.txt updated.');
    }
    header('Location: sitemap.php');
    exit;
}

regenerate_sitemap();

$sitemap_path = __DIR__ . '/../public/sitemap.xml';
$robots_path = __DIR__ . '/../public/robots.txt';
$sitemap_content = file_exists($sitemap_path) ? file_get_contents($sitemap_path) : '';
$robots_content = file_exists($robots_path) ? file_get_contents($robots_path) : get_setting('robots_txt');
$post_count = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();

require __DIR__ . '/includes/header.php';
?>

<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check"></i></div>
        <div class="stat-info"><h4><?= $post_count ?></h4><p>Published URLs in Sitemap</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-sync"></i></div>
        <div class="stat-info"><h4>Auto</h4><p>Updates on Publish / Edit / Delete</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-robot"></i></div>
        <div class="stat-info"><h4>AI Crawlers</h4><p>GPTBot, Claude, Perplexity… Allowed</p></div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-sitemap" style="margin-right:8px;color:var(--orange);"></i> XML Sitemap</h3>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
                <button type="submit" name="regenerate" value="1" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Regenerate Now</button>
            </form>
        </div>
        <div class="card-body">
            <p style="font-size:14px;color:#64748B;margin-bottom:16px;">
                Automatically updated when you publish, update or delete a blog. Submit this to Google Search Console and Bing:
            </p>
            <div style="background:var(--gray-50);padding:12px 16px;border-radius:8px;font-family:monospace;font-size:13px;word-break:break-all;margin-bottom:16px;">
                <?= e(rtrim(get_setting('site_url', SITE_URL), '/')) ?>/sitemap.xml
            </div>
            <a href="../public/sitemap.xml" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-external-link-alt"></i> View Sitemap</a>
            <details style="margin-top:20px;">
                <summary style="cursor:pointer;font-weight:600;font-size:14px;color:var(--navy);">Preview Sitemap XML</summary>
                <pre style="background:#0B1C2E;color:#E2E8F0;padding:16px;border-radius:8px;font-size:11px;overflow:auto;max-height:300px;margin-top:12px;"><?= e($sitemap_content) ?></pre>
            </details>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-robot" style="margin-right:8px;color:var(--orange);"></i> robots.txt (GEO Optimized)</h3></div>
        <div class="card-body">
            <p style="font-size:13px;color:#64748B;margin-bottom:12px;">
                Explicitly allows major AI / generative search crawlers so your content can be indexed and cited by ChatGPT, Google AI Overviews, Perplexity, Claude, etc.
            </p>
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
                <div class="form-group">
                    <textarea name="robots_txt" class="form-control" rows="14" style="font-family:monospace;font-size:12px;"><?= e($robots_content) ?></textarea>
                </div>
                <button type="submit" name="update_robots" value="1" class="btn btn-primary"><i class="fas fa-save"></i> Save robots.txt</button>
                <a href="../public/robots.txt" target="_blank" class="btn btn-outline" style="margin-left:8px;">View Live</a>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-top:24px;">
    <div class="card-header"><h3>What Makes This CRM Google + AI Indexing Friendly (GEO)</h3></div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:20px;">
            <div>
                <h4 style="font-size:14px;color:var(--navy);margin-bottom:8px;"><i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i> JSON-LD Schema</h4>
                <p style="font-size:13px;color:#64748B;">BlogPosting / Article / HowTo / FAQPage structured data on every post — critical for AI citations.</p>
            </div>
            <div>
                <h4 style="font-size:14px;color:var(--navy);margin-bottom:8px;"><i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i> FAQ Schema</h4>
                <p style="font-size:13px;color:#64748B;">FAQ blocks generate FAQPage schema that powers Google AI Overviews & featured snippets.</p>
            </div>
            <div>
                <h4 style="font-size:14px;color:var(--navy);margin-bottom:8px;"><i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i> AI Summary + Takeaways</h4>
                <p style="font-size:13px;color:#64748B;">Direct-answer summaries and bullet takeaways that generative engines love to quote.</p>
            </div>
            <div>
                <h4 style="font-size:14px;color:var(--navy);margin-bottom:8px;"><i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i> AI Crawler Access</h4>
                <p style="font-size:13px;color:#64748B;">robots.txt allows GPTBot, ClaudeBot, PerplexityBot, Google-Extended, Bytespider, etc.</p>
            </div>
            <div>
                <h4 style="font-size:14px;color:var(--navy);margin-bottom:8px;"><i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i> Clean Meta + OG</h4>
                <p style="font-size:13px;color:#64748B;">Title, description, canonical, Open Graph, Twitter cards on every page.</p>
            </div>
            <div>
                <h4 style="font-size:14px;color:var(--navy);margin-bottom:8px;"><i class="fas fa-check-circle" style="color:var(--success);margin-right:6px;"></i> Auto Sitemap</h4>
                <p style="font-size:13px;color:#64748B;">XML sitemap with lastmod & priority regenerates on every publish/update/delete.</p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
