<?php
require_once __DIR__ . '/../config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$post = null;

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) {
        set_flash('danger', 'Post not found.');
        header('Location: posts.php');
        exit;
    }
    $page_title = 'Edit Blog Post';
    $page_subtitle = e($post['title']);
} else {
    $page_title = 'New Blog Post';
    $page_subtitle = 'Create a SEO + GEO optimized article';
}

$categories = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$all_tags = $pdo->query("SELECT id, name FROM tags ORDER BY name")->fetchAll();
$post_tag_ids = [];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT tag_id FROM post_tags WHERE post_id = ?");
    $stmt->execute([$id]);
    $post_tag_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Decode existing FAQ
$faq_items = [];
if (!empty($post['faq_json'])) {
    $faq_items = is_string($post['faq_json']) ? json_decode($post['faq_json'], true) : $post['faq_json'];
    if (!is_array($faq_items)) $faq_items = [];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords = trim($_POST['meta_keywords'] ?? '');
        $focus_keyword = trim($_POST['focus_keyword'] ?? '');
        $canonical_url = trim($_POST['canonical_url'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0) ?: null;
        $status = $_POST['status'] ?? 'draft';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $featured_image_alt = trim($_POST['featured_image_alt'] ?? '');
        $social_image_alt = trim($_POST['social_image_alt'] ?? '');
        $tag_ids = $_POST['tags'] ?? [];
        
        // GEO fields
        $key_takeaways = trim($_POST['key_takeaways'] ?? '');
        $ai_summary = trim($_POST['ai_summary'] ?? '');
        $schema_type = $_POST['schema_type'] ?? 'BlogPosting';
        $sources = trim($_POST['sources'] ?? '');
        
        // FAQ builder
        $faq_questions = $_POST['faq_question'] ?? [];
        $faq_answers = $_POST['faq_answer'] ?? [];
        $faq_data = [];
        for ($i = 0; $i < count($faq_questions); $i++) {
            $q = trim($faq_questions[$i] ?? '');
            $a = trim($faq_answers[$i] ?? '');
            if ($q && $a) {
                $faq_data[] = ['question' => $q, 'answer' => $a];
            }
        }
        $faq_json = !empty($faq_data) ? json_encode($faq_data, JSON_UNESCAPED_UNICODE) : null;
        
        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($content)) $errors[] = 'Content is required.';
        if (!in_array($status, ['draft','published','scheduled','archived'])) $status = 'draft';
        if (!in_array($schema_type, ['BlogPosting','Article','HowTo','FAQPage'])) $schema_type = 'BlogPosting';
        
        if (empty($slug)) $slug = slugify($title);
        else $slug = slugify($slug);
        
        $slug_check = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
        $slug_check->execute([$slug, $id]);
        if ($slug_check->fetch()) $slug .= '-' . time();
        
        // Featured image
        $featured_image = $post['featured_image'] ?? null;
        if (!empty($_FILES['featured_image']['name'])) {
            $upload = handle_image_upload($_FILES['featured_image'], 'featured');
            if ($upload['success']) {
                if ($featured_image && file_exists(UPLOAD_PATH . $featured_image)) @unlink(UPLOAD_PATH . $featured_image);
                $featured_image = $upload['filename'];
            } else $errors[] = $upload['error'];
        }
        
        // FIXED: Changed isset() to !empty() to prevent accidental deletion
        if (!empty($_POST['remove_featured_image'])) {
            if ($featured_image && file_exists(UPLOAD_PATH . $featured_image)) @unlink(UPLOAD_PATH . $featured_image);
            $featured_image = null;
        }
        
        // Social image
        $social_image = $post['social_image'] ?? null;
        if (!empty($_FILES['social_image']['name'])) {
            $upload = handle_image_upload($_FILES['social_image'], 'social');
            if ($upload['success']) {
                if ($social_image && file_exists(UPLOAD_PATH . $social_image)) @unlink(UPLOAD_PATH . $social_image);
                $social_image = $upload['filename'];
            } else $errors[] = $upload['error'];
        }
        
        // FIXED: Changed isset() to !empty() to prevent accidental deletion
        if (!empty($_POST['remove_social_image'])) {
            if ($social_image && file_exists(UPLOAD_PATH . $social_image)) @unlink(UPLOAD_PATH . $social_image);
            $social_image = null;
        }
        
        if (empty($errors)) {
            $reading_time = calculate_reading_time($content);
            $author_id = $_SESSION['user_id'];
            $published_at = null;
            if ($status === 'published') {
                $published_at = ($is_edit && !empty($post['published_at'])) ? $post['published_at'] : date('Y-m-d H:i:s');
            }
            
            if ($is_edit) {
                $sql = "UPDATE posts SET 
                    title=?, slug=?, content=?, excerpt=?, 
                    meta_title=?, meta_description=?, meta_keywords=?, focus_keyword=?, canonical_url=?,
                    featured_image=?, featured_image_alt=?, social_image=?, social_image_alt=?,
                    category_id=?, status=?, is_featured=?, reading_time=?,
                    key_takeaways=?, ai_summary=?, faq_json=?, schema_type=?, sources=?,
                    published_at=?, updated_at=NOW()
                    WHERE id=?";
                $pdo->prepare($sql)->execute([
                    $title, $slug, $content, $excerpt,
                    $meta_title ?: null, $meta_description ?: null, $meta_keywords ?: null, $focus_keyword ?: null, $canonical_url ?: null,
                    $featured_image, $featured_image_alt ?: null, $social_image, $social_image_alt ?: null,
                    $category_id, $status, $is_featured, $reading_time,
                    $key_takeaways ?: null, $ai_summary ?: null, $faq_json, $schema_type, $sources ?: null,
                    $published_at, $id
                ]);
                $pdo->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$id]);
                set_flash('success', 'Blog post updated. Sitemap regenerated if published.');
            } else {
                $sql = "INSERT INTO posts (
                    title, slug, content, excerpt, 
                    meta_title, meta_description, meta_keywords, focus_keyword, canonical_url,
                    featured_image, featured_image_alt, social_image, social_image_alt,
                    category_id, author_id, status, is_featured, reading_time,
                    key_takeaways, ai_summary, faq_json, schema_type, sources, published_at
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $pdo->prepare($sql)->execute([
                    $title, $slug, $content, $excerpt,
                    $meta_title ?: null, $meta_description ?: null, $meta_keywords ?: null, $focus_keyword ?: null, $canonical_url ?: null,
                    $featured_image, $featured_image_alt ?: null, $social_image, $social_image_alt ?: null,
                    $category_id, $author_id, $status, $is_featured, $reading_time,
                    $key_takeaways ?: null, $ai_summary ?: null, $faq_json, $schema_type, $sources ?: null, $published_at
                ]);
                $id = $pdo->lastInsertId();
                set_flash('success', 'Blog post created successfully.');
            }
            
            if (!empty($tag_ids)) {
                $tag_stmt = $pdo->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                foreach ($tag_ids as $tid) $tag_stmt->execute([$id, (int)$tid]);
            }
            
            if ($status === 'published') regenerate_sitemap();
            
            $pdo->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address) VALUES (?,?,?,?,?)")
                ->execute([$_SESSION['user_id'], $is_edit ? 'update_post' : 'create_post', 'post', $id, $_SERVER['REMOTE_ADDR'] ?? '']);
            
            header('Location: post-edit.php?id=' . $id);
            exit;
        }
    }
    $post = array_merge($post ?? [], $_POST);
    $post['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    
    $faq_items = [];
    $fq = $_POST['faq_question'] ?? [];
    $fa = $_POST['faq_answer'] ?? [];
    for ($i = 0; $i < count($fq); $i++) {
        if (trim($fq[$i] ?? '') || trim($fa[$i] ?? '')) {
            $faq_items[] = ['question' => $fq[$i] ?? '', 'answer' => $fa[$i] ?? ''];
        }
    }
}

function handle_image_upload($file, $prefix = 'img') {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'Upload error.'];
    if ($file['size'] > MAX_IMAGE_SIZE) return ['success' => false, 'error' => 'Image too large (max 5MB).'];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) return ['success' => false, 'error' => 'Invalid image type.'];
    
    $ext = match($mime) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', default => 'jpg' };
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
    
    if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) return ['success' => true, 'filename' => $filename];
    return ['success' => false, 'error' => 'Failed to save file.'];
}

$extra_js = <<<'JS'
<script>
document.addEventListener("DOMContentLoaded", function() {
    initEditor("#content");
    
    const titleInput = document.getElementById("title");
    const slugInput = document.getElementById("slug");
    let slugManual = false;
    
    titleInput.addEventListener("input", function() {
        if (!slugManual) slugInput.value = generateSlug(this.value);
        const metaTitle = document.getElementById("meta_title");
        if (metaTitle && !metaTitle.dataset.touched) {
            metaTitle.value = this.value.substring(0, 60);
            updateCharCount(metaTitle, "meta_title_count", 60);
            updateSeoPreview();
        }
    });
    
    slugInput.addEventListener("input", () => slugManual = true);
    
    ["meta_title", "meta_description"].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener("input", function() {
                this.dataset.touched = "1";
                updateCharCount(this, id + "_count", id === "meta_title" ? 60 : 160);
                updateSeoPreview();
            });
            updateCharCount(el, id + "_count", id === "meta_title" ? 60 : 160);
        }
    });
    
    updateSeoPreview();
    
    // FAQ add/remove
    window.addFaqRow = function() {
        const container = document.getElementById("faq-container");
        const div = document.createElement("div");
        div.className = "faq-row";
        div.style.cssText = "background:var(--gray-50);padding:14px;border-radius:8px;margin-bottom:10px;position:relative;";
        div.innerHTML = `
            <button type="button" onclick="this.parentElement.remove()" style="position:absolute;top:8px;right:8px;background:none;border:none;color:#EF4444;cursor:pointer;"><i class="fas fa-times"></i></button>
            <div class="form-group" style="margin-bottom:10px;">
                <label class="form-label">Question</label>
                <input type="text" name="faq_question[]" class="form-control" placeholder="What is ACP cladding?">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Answer</label>
                <textarea name="faq_answer[]" class="form-control" rows="2" placeholder="Clear, direct answer..."></textarea>
            </div>`;
        container.appendChild(div);
    };
});

function updateSeoPreview() {
    const title = document.getElementById("meta_title")?.value || document.getElementById("title")?.value || "Page Title";
    const desc = document.getElementById("meta_description")?.value || "Meta description will appear here...";
    const slug = document.getElementById("slug")?.value || "post-slug";
    
    document.getElementById("seo_prev_title").textContent = title.substring(0, 60);
    document.getElementById("seo_prev_url").textContent = (window.SITE_URL || "") + "/blog/" + slug;
    document.getElementById("seo_prev_desc").textContent = desc.substring(0, 160);
}
</script>
JS;

$extra_js = '<script>window.SITE_URL = "' . e(rtrim(get_setting('site_url', SITE_URL), '/')) . '";</script>' . $extra_js;

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <ul style="margin:0;padding-left:18px;"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="postForm">
<input type="hidden" name="csrf" value="<?= e(generate_csrf()) ?>">
<div style="display:grid; grid-template-columns: 1fr 340px; gap:24px;">
    
    <!-- MAIN COLUMN -->
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Blog Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           value="<?= e($post['title'] ?? '') ?>" placeholder="Enter a compelling, specific title..."
                           style="font-size:18px;font-weight:600;padding:14px 16px;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Slug / URL</label>
                    <div style="display:flex;align-items:center;">
                        <span style="background:var(--gray-100);padding:12px 14px;border:1.5px solid var(--gray-200);border-right:0;border-radius:8px 0 0 8px;font-size:13px;color:#64748B;">/blog/</span>
                        <input type="text" id="slug" name="slug" class="form-control" 
                               value="<?= e($post['slug'] ?? '') ?>" placeholder="auto-generated-from-title"
                               style="border-radius:0 8px 8px 0;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Content <span class="required">*</span></label>
                    <textarea id="content" name="content"><?= e($post['content'] ?? '') ?></textarea>
                    <div class="form-hint">Use clear H2/H3 headings, lists and short paragraphs — AI engines prefer structured content.</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Excerpt (shown on blog listing cards)</label>
                    <textarea name="excerpt" class="form-control" rows="2" placeholder="1-2 sentence summary for the card..."><?= e($post['excerpt'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- CLASSIC SEO -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fas fa-search" style="margin-right:8px;color:var(--orange);"></i> SEO Meta Tags</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Focus Keyword</label>
                    <input type="text" name="focus_keyword" class="form-control" value="<?= e($post['focus_keyword'] ?? '') ?>" placeholder="e.g. ACP cladding benefits">
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Title <span style="font-weight:400;color:#94A3B8;">(≈ 60 chars)</span></label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control" maxlength="70" value="<?= e($post['meta_title'] ?? '') ?>">
                    <div id="meta_title_count" class="char-count">0 / 60</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Description <span style="font-weight:400;color:#94A3B8;">(≈ 160 chars)</span></label>
                    <textarea id="meta_description" name="meta_description" class="form-control" rows="3" maxlength="170"><?= e($post['meta_description'] ?? '') ?></textarea>
                    <div id="meta_description_count" class="char-count">0 / 160</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" value="<?= e($post['meta_keywords'] ?? '') ?>" placeholder="cladding, acp, glazing (comma separated)">
                </div>
                <div class="form-group">
                    <label class="form-label">Canonical URL (optional)</label>
                    <input type="url" name="canonical_url" class="form-control" value="<?= e($post['canonical_url'] ?? '') ?>" placeholder="Leave blank to use default">
                </div>
                
                <div class="seo-preview">
                    <div style="font-size:12px;color:#94A3B8;margin-bottom:8px;font-weight:600;">GOOGLE SEARCH PREVIEW</div>
                    <div class="seo-title" id="seo_prev_title">Page Title</div>
                    <div class="seo-url" id="seo_prev_url">https://www.auracladding.com/blog/slug</div>
                    <div class="seo-desc" id="seo_prev_desc">Meta description will appear here...</div>
                </div>
            </div>
        </div>
        
        <!-- GENERATIVE ENGINE OPTIMIZATION (GEO) -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-robot" style="margin-right:8px;color:var(--orange);"></i> Generative Engine Optimization (GEO)</h3>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:#64748B;margin-bottom:18px;">
                    These fields help AI systems (Google AI Overviews, ChatGPT, Perplexity, Claude, etc.) understand, cite and surface your content.
                </p>
                
                <div class="form-group">
                    <label class="form-label">AI Summary <span style="font-weight:400;color:#94A3B8;">(direct answer, 1-3 sentences)</span></label>
                    <textarea name="ai_summary" class="form-control" rows="3" placeholder="Write a clear, factual summary that directly answers the main question of the article. AI engines love concise direct answers."><?= e($post['ai_summary'] ?? '') ?></textarea>
                    <div class="form-hint">This is the text most likely to be quoted by generative AI.</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Key Takeaways <span style="font-weight:400;color:#94A3B8;">(one per line)</span></label>
                    <textarea name="key_takeaways" class="form-control" rows="4" placeholder="- ACP cladding is lightweight and weather-resistant&#10;- Ideal for high-rise commercial buildings&#10;- Reduces maintenance costs by up to 40%"><?= e($post['key_takeaways'] ?? '') ?></textarea>
                    <div class="form-hint">Bullet points AI systems frequently extract and cite.</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Schema Type</label>
                    <select name="schema_type" class="form-select">
                        <option value="BlogPosting" <?= ($post['schema_type'] ?? 'BlogPosting') === 'BlogPosting' ? 'selected' : '' ?>>BlogPosting (default)</option>
                        <option value="Article" <?= ($post['schema_type'] ?? '') === 'Article' ? 'selected' : '' ?>>Article</option>
                        <option value="HowTo" <?= ($post['schema_type'] ?? '') === 'HowTo' ? 'selected' : '' ?>>HowTo</option>
                        <option value="FAQPage" <?= ($post['schema_type'] ?? '') === 'FAQPage' ? 'selected' : '' ?>>FAQPage</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">FAQ Section <span style="font-weight:400;color:#94A3B8;">(generates FAQPage schema — highly valuable for AI)</span></label>
                    <div id="faq-container">
                        <?php if (empty($faq_items)): $faq_items = [['question'=>'','answer'=>'']]; endif; ?>
                        <?php foreach ($faq_items as $idx => $faq): ?>
                        <div class="faq-row" style="background:var(--gray-50);padding:14px;border-radius:8px;margin-bottom:10px;position:relative;">
                            <?php if ($idx > 0): ?>
                            <button type="button" onclick="this.parentElement.remove()" style="position:absolute;top:8px;right:8px;background:none;border:none;color:#EF4444;cursor:pointer;"><i class="fas fa-times"></i></button>
                            <?php endif; ?>
                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label">Question</label>
                                <input type="text" name="faq_question[]" class="form-control" value="<?= e($faq['question'] ?? '') ?>" placeholder="What is ACP cladding?">
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label">Answer</label>
                                <textarea name="faq_answer[]" class="form-control" rows="2" placeholder="Clear, direct answer that AI can quote..."><?= e($faq['answer'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addFaqRow()"><i class="fas fa-plus"></i> Add FAQ</button>
                </div>
                
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Sources / Citations <span style="font-weight:400;color:#94A3B8;">(one per line)</span></label>
                    <textarea name="sources" class="form-control" rows="3" placeholder="https://example.com/research-paper&#10;Indian Standard IS 7312:2019"><?= e($post['sources'] ?? '') ?></textarea>
                    <div class="form-hint">Authoritative sources increase trust signals for both Google and generative AI.</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SIDEBAR -->
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Publish</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="scheduled" <?= ($post['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="archived" <?= ($post['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;">
                    <label class="form-label" style="margin:0;">Featured Story</label>
                    <label class="switch">
                        <input type="checkbox" name="is_featured" value="1" <?= !empty($post['is_featured']) ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="form-hint" style="margin-top:-8px;margin-bottom:16px;">Show as Featured on the blog listing</div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> <?= $is_edit ? 'Update Blog' : 'Create Blog' ?>
                </button>
                <?php if ($is_edit): ?>
                <a href="posts.php" class="btn btn-outline btn-block" style="margin-top:10px;">← Back to List</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Category</h3></div>
            <div class="card-body">
                <select name="category_id" class="form-select">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($post['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Tags</h3></div>
            <div class="card-body">
                <?php if (empty($all_tags)): ?>
                <p style="font-size:13px;color:#94A3B8;">No tags yet. <a href="tags.php">Create tags</a></p>
                <?php else: ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;max-height:140px;overflow-y:auto;">
                    <?php foreach ($all_tags as $tag): ?>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;background:var(--gray-50);padding:5px 10px;border-radius:6px;">
                        <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $post_tag_ids) ? 'checked' : '' ?>>
                        <?= e($tag['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Featured Image -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3>Featured / Cover Image</h3></div>
            <div class="card-body">
                <div class="image-upload-box <?= !empty($post['featured_image']) ? 'has-image' : '' ?>" id="featured_preview" onclick="document.getElementById('featured_image').click()">
                    <?php if (!empty($post['featured_image'])): ?>
                    <div class="image-preview">
                        <img src="<?= e(UPLOAD_URL . $post['featured_image']) ?>" alt="">
                        <button type="button" class="remove-btn" onclick="event.stopPropagation(); document.getElementById('remove_featured').value=1; clearImage('featured_image','featured_preview');"><i class="fas fa-times"></i></button>
                    </div>
                    <?php else: ?>
                    <i class="fas fa-cloud-upload-alt" style="font-size:32px;color:#94A3B8;margin-bottom:12px;"></i>
                    <p style="color:#64748B;font-size:14px;">Click to upload</p>
                    <p style="color:#94A3B8;font-size:12px;">Preferred: <?= FEATURED_IMAGE_NOTE ?></p>
                    <?php endif; ?>
                </div>
                <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display:none" onchange="previewImage(this, 'featured_preview')">
                <input type="hidden" name="remove_featured_image" id="remove_featured" value="">
                
                <div class="form-group" style="margin-top:12px;margin-bottom:0;">
                    <label class="form-label">ALT Text (important for SEO & accessibility)</label>
                    <input type="text" name="featured_image_alt" class="form-control" value="<?= e($post['featured_image_alt'] ?? '') ?>" placeholder="Describe the image clearly">
                </div>
                <div class="form-hint" style="margin-top:8px;">
                    <strong>Preferred size:</strong> <?= FEATURED_IMAGE_NOTE ?><br>
                    This size works perfectly for blog cards, social sharing and AI previews.
                </div>
            </div>
        </div>
        
        <!-- Social Image -->
        <div class="card">
            <div class="card-header"><h3>Social / OG Image</h3></div>
            <div class="card-body">
                <div class="image-upload-box <?= !empty($post['social_image']) ? 'has-image' : '' ?>" id="social_preview" onclick="document.getElementById('social_image').click()">
                    <?php if (!empty($post['social_image'])): ?>
                    <div class="image-preview">
                        <img src="<?= e(UPLOAD_URL . $post['social_image']) ?>" alt="">
                        <button type="button" class="remove-btn" onclick="event.stopPropagation(); document.getElementById('remove_social').value=1; clearImage('social_image','social_preview');"><i class="fas fa-times"></i></button>
                    </div>
                    <?php else: ?>
                    <i class="fas fa-share-alt" style="font-size:28px;color:#94A3B8;margin-bottom:12px;"></i>
                    <p style="color:#64748B;font-size:14px;">Open Graph / Twitter / AI</p>
                    <p style="color:#94A3B8;font-size:12px;">1200 × 630 px recommended</p>
                    <?php endif; ?>
                </div>
                <input type="file" id="social_image" name="social_image" accept="image/*" style="display:none" onchange="previewImage(this, 'social_preview')">
                <input type="hidden" name="remove_social_image" id="remove_social" value="">
                
                <div class="form-group" style="margin-top:12px;margin-bottom:0;">
                    <label class="form-label">Social Image ALT</label>
                    <input type="text" name="social_image_alt" class="form-control" value="<?= e($post['social_image_alt'] ?? '') ?>">
                </div>
            </div>
        </div>
        
    </div>
</div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>