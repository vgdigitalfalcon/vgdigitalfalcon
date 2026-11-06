<?php
if (!isset($page_title)) $page_title = 'Dashboard';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | Aura Cladding CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <?php if (!empty($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fas fa-building"></i></div>
            <div class="logo-text">
                Aura Cladding
                <span>Blog CRM</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="index.php" class="nav-item <?= ($page_title === 'Dashboard') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-th-large"></i></span> Dashboard
                </a>
                <a href="posts.php" class="nav-item <?= (strpos($page_title, 'Post') !== false || strpos($page_title, 'Blog') !== false) ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-newspaper"></i></span> All Blogs
                </a>
                <a href="post-edit.php" class="nav-item <?= ($page_title === 'New Blog Post') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-plus-circle"></i></span> Add New Blog
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Gallery / Works</div>
                <a href="gallery.php" class="nav-item <?= (strpos($page_title, 'Gallery') !== false || strpos($page_title, 'Project') !== false) ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-th"></i></span> All Projects
                </a>
                <a href="gallery-edit.php" class="nav-item <?= ($page_title === 'New Project') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-plus-circle"></i></span> Add Project
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Organization</div>
                <a href="categories.php" class="nav-item <?= (strpos($page_title, 'Categor') !== false) ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-folder"></i></span> Categories
                </a>
                <a href="tags.php" class="nav-item <?= (strpos($page_title, 'Tag') !== false) ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-tags"></i></span> Tags
                </a>
                <a href="media.php" class="nav-item <?= ($page_title === 'Media Library') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-images"></i></span> Media Library
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">SEO & Technical</div>
                <a href="seo-settings.php" class="nav-item <?= (strpos($page_title, 'SEO') !== false) ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-search"></i></span> SEO + GEO Settings
                </a>
                <a href="sitemap.php" class="nav-item <?= ($page_title === 'Sitemap') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-sitemap"></i></span> Sitemap & Robots
                </a>
            </div>
            
            <?php if (is_admin()): ?>
            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <a href="users.php" class="nav-item <?= ($page_title === 'Users') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-users"></i></span> Users
                </a>
                <a href="settings.php" class="nav-item <?= ($page_title === 'Settings') ? 'active' : '' ?>">
                    <span class="icon"><i class="fas fa-cog"></i></span> Settings
                </a>
            </div>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($user['full_name'] ?? 'A', 0, 1)) ?></div>
                <div class="user-details">
                    <div class="name"><?= e($user['full_name'] ?? 'Admin') ?></div>
                    <div class="role"><?= e($user['role'] ?? 'admin') ?></div>
                </div>
                <a href="logout.php" title="Logout" style="color: rgba(255,255,255,0.5); font-size: 14px;"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </aside>
    
    <!-- Main -->
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-title">
                <h2><?= e($page_title) ?></h2>
                <?php if (!empty($page_subtitle)): ?>
                    <p><?= e($page_subtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="topbar-actions">
                <a href="<?= e(works_list_url()) ?>" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i> View Gallery
                </a>
                <a href="<?= e(blog_list_url()) ?>" target="_blank" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i> View Blog
                </a>
                <a href="gallery-edit.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Project
                </a>
            </div>
        </header>
        
        <div class="content-area">
            <?php
            $flash = get_flash();
            if ($flash):
            ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <?= e($flash['message']) ?>
            </div>
            <?php endif; ?>
