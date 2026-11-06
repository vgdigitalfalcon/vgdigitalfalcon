<?php
require_once __DIR__ . '/../config.php';
require_login();

$page_title = 'Dashboard';
$page_subtitle = 'Overview of your blog content';

// Stats
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$published = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$drafts = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
$featured = $pdo->query("SELECT COUNT(*) FROM posts WHERE is_featured = 1")->fetchColumn();
$total_views = $pdo->query("SELECT COALESCE(SUM(view_count),0) FROM posts")->fetchColumn();
$categories = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();

// Recent posts
$recent = $pdo->query("
    SELECT p.*, c.name as category_name, u.full_name as author_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN users u ON p.author_id = u.id 
    ORDER BY p.updated_at DESC LIMIT 8
")->fetchAll();

// Featured posts
$featured_posts = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM posts p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 AND p.status = 'published'
    ORDER BY p.published_at DESC LIMIT 5
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <h4><?= number_format($total_posts) ?></h4>
            <p>Total Blogs</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h4><?= number_format($published) ?></h4>
            <p>Published</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-file-alt"></i></div>
        <div class="stat-info">
            <h4><?= number_format($drafts) ?></h4>
            <p>Drafts</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-star"></i></div>
        <div class="stat-info">
            <h4><?= number_format($featured) ?></h4>
            <p>Featured Stories</p>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
    <!-- Recent Posts -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock" style="margin-right:8px;color:var(--orange);"></i> Recent Blogs</h3>
            <a href="posts.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:#94A3B8;">No blogs yet. <a href="post-edit.php">Create your first blog</a></td></tr>
                        <?php else: foreach ($recent as $post): ?>
                        <tr>
                            <td>
                                <strong style="color:var(--navy);"><?= e(mb_strimwidth($post['title'], 0, 50, '...')) ?></strong>
                                <?php if ($post['is_featured']): ?>
                                    <span class="badge badge-featured" style="margin-left:6px;">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($post['category_name'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= e($post['status']) ?>"><?= ucfirst($post['status']) ?></span></td>
                            <td style="color:#64748B;font-size:13px;"><?= date('d M Y', strtotime($post['updated_at'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="post-edit.php?id=<?= $post['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quick Info -->
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fas fa-star" style="margin-right:8px;color:var(--orange);"></i> Featured Stories</h3>
            </div>
            <div class="card-body">
                <?php if (empty($featured_posts)): ?>
                    <p style="color:#94A3B8;font-size:14px;text-align:center;">No featured blogs yet.</p>
                <?php else: foreach ($featured_posts as $fp): ?>
                    <div style="padding:10px 0;border-bottom:1px solid var(--gray-100);">
                        <a href="post-edit.php?id=<?= $fp['id'] ?>" style="font-weight:600;color:var(--navy);font-size:14px;">
                            <?= e(mb_strimwidth($fp['title'], 0, 45, '...')) ?>
                        </a>
                        <div style="font-size:12px;color:#94A3B8;margin-top:2px;"><?= e($fp['category_name'] ?? '') ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right:8px;color:var(--orange);"></i> Quick Stats</h3>
            </div>
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gray-100);">
                    <span style="color:#64748B;">Total Views</span>
                    <strong><?= number_format($total_views) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--gray-100);">
                    <span style="color:#64748B;">Categories</span>
                    <strong><?= $categories ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span style="color:#64748B;">Sitemap</span>
                    <a href="../public/sitemap.xml" target="_blank" style="color:var(--orange);font-weight:600;">View XML</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
