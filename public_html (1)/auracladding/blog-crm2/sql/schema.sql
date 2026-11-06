-- ============================================================
-- Aura Cladding Blog CRM Database Schema
-- PHP + MySQL | SEO + Generative Engine Optimization (GEO)
-- Optimized for Google + AI Search Engines (ChatGPT, Perplexity,
-- Google AI Overviews, Claude, etc.)
-- ============================================================

CREATE DATABASE IF NOT EXISTS aura_blog_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aura_blog_crm;

-- Users table (Admin / Authors) — E-E-A-T signals
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor', 'author') DEFAULT 'author',
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,                    -- Author bio for E-E-A-T
    expertise VARCHAR(255) DEFAULT NULL,      -- e.g. "ACP Cladding Specialist"
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    meta_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(160) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    parent_id INT UNSIGNED DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Posts / Blogs  (SEO + Generative Engine Optimization)
CREATE TABLE posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    
    -- Classic SEO
    meta_title VARCHAR(70) DEFAULT NULL,
    meta_description VARCHAR(160) DEFAULT NULL,
    meta_keywords VARCHAR(255) DEFAULT NULL,
    focus_keyword VARCHAR(100) DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    
    -- Images
    featured_image VARCHAR(255) DEFAULT NULL,
    featured_image_alt VARCHAR(255) DEFAULT NULL,
    social_image VARCHAR(255) DEFAULT NULL,          -- OG / Twitter / AI preview
    social_image_alt VARCHAR(255) DEFAULT NULL,
    
    -- Classification
    category_id INT UNSIGNED DEFAULT NULL,
    author_id INT UNSIGNED NOT NULL,
    
    -- Status & Features
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    is_featured TINYINT(1) DEFAULT 0,               -- Featured Blog / Story
    is_sticky TINYINT(1) DEFAULT 0,
    
    -- Reading & Stats
    reading_time INT UNSIGNED DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    
    -- ========== GENERATIVE ENGINE OPTIMIZATION (GEO) ==========
    -- These fields help AI systems (Google AI Overviews, ChatGPT,
    -- Perplexity, Claude, etc.) understand, cite and surface content
    key_takeaways TEXT DEFAULT NULL,                -- Bullet summary AI loves to quote
    ai_summary TEXT DEFAULT NULL,                   -- Short direct-answer summary
    faq_json JSON DEFAULT NULL,                     -- FAQ pairs → FAQPage schema
    schema_type VARCHAR(50) DEFAULT 'BlogPosting', -- BlogPosting | Article | HowTo | FAQPage
    sources TEXT DEFAULT NULL,                      -- Citations / references (one per line)
    -- ==========================================================
    
    -- Publishing
    published_at DATETIME DEFAULT NULL,
    scheduled_at DATETIME DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_published (published_at),
    INDEX idx_slug (slug),
    FULLTEXT INDEX ft_search (title, content, excerpt, meta_keywords, key_takeaways, ai_summary)
) ENGINE=InnoDB;

-- Post Images (gallery / additional images with ALT)
CREATE TABLE post_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    caption VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tags
CREATE TABLE tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(60) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Post Tags pivot
CREATE TABLE post_tags (
    post_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Media Library
CREATE TABLE media (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    width INT UNSIGNED DEFAULT NULL,
    height INT UNSIGNED DEFAULT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    uploaded_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Settings
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Activity log
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    details TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default admin (password: Admin@123)
INSERT INTO users (username, email, password, full_name, role, bio, expertise) VALUES
('admin', 'admin@auracladding.com', '$2y$12$OlqNrb60CijEiibftW2tZeqo8Lqt2vMs/ci8wKb.G0j9XtOmK0LZi', 'Site Administrator', 'admin',
 'Expert in high-performance cladding and glazing solutions with over a decade of industry experience.', 'ACP Cladding & Architectural Glazing Specialist');

-- Categories matching the live Blog page
INSERT INTO categories (name, slug, description) VALUES
('Cladding', 'cladding', 'Everything about cladding solutions, ACP, exterior cladding'),
('Glazing', 'glazing', 'Glass and glazing solutions for modern buildings'),
('Exterior Cladding', 'exterior-cladding', 'Exterior cladding materials and installation'),
('Toughened Glass', 'toughened-glass', 'Safety and strength of toughened glass'),
('Architecture', 'architecture', 'Architectural insights and modern design'),
('Installation', 'installation', 'Installation processes and best practices'),
('Sustainability', 'sustainability', 'Eco-friendly and sustainable building materials'),
('Design', 'design', 'Creative cladding and design ideas'),
('Energy Efficiency', 'energy-efficiency', 'How glazing and cladding improve energy efficiency');

-- Default settings (including AI-crawler friendly robots)
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Aura Cladding'),
('site_url', 'https://www.auracladding.com'),
('blog_title', 'Our Blog. Insights. Ideas. Inspiration.'),
('blog_description', 'Stay updated with the latest trends, expert insights and guides on cladding, glazing and modern architecture.'),
('default_meta_title', 'Aura Cladding Blog | Insights on Cladding, Glazing & Architecture'),
('default_meta_description', 'Expert articles on ACP cladding, glazing solutions, toughened glass, exterior cladding and sustainable architecture from Aura Cladding, Bengaluru.'),
('robots_txt', 'User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /blog-crm/admin/\n\n# Allow major AI / Generative Search crawlers (GEO)\nUser-agent: GPTBot\nAllow: /\n\nUser-agent: ChatGPT-User\nAllow: /\n\nUser-agent: Google-Extended\nAllow: /\n\nUser-agent: ClaudeBot\nAllow: /\n\nUser-agent: anthropic-ai\nAllow: /\n\nUser-agent: PerplexityBot\nAllow: /\n\nUser-agent: Bytespider\nAllow: /\n\nUser-agent: CCBot\nAllow: /\n\nSitemap: https://www.auracladding.com/sitemap.xml'),
('sitemap_enabled', '1'),
('posts_per_page', '9'),
('company_address', '322, Business Park, Bengaluru, Karnataka - 560001, India'),
('company_phone', '+91 98765 43210'),
('company_email', 'info@auracladding.com'),
('company_name', 'Aura Cladding'),
('twitter_handle', '@auracladding'),
('facebook_url', 'https://www.facebook.com/auracladding'),
('linkedin_url', 'https://www.linkedin.com/company/auracladding');
