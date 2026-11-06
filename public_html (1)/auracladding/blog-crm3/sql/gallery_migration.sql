-- ============================================================
-- Gallery / Our Works migration for existing Aura Blog CRM DBs
-- Run this once if you already installed the blog CRM
-- ============================================================

USE u670120666_aura;  -- change to your DB name if different

CREATE TABLE IF NOT EXISTS gallery_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(255) DEFAULT NULL,
    area VARCHAR(100) DEFAULT NULL,
    materials JSON DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gallery_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES gallery_projects(id) ON DELETE CASCADE,
    INDEX idx_project (project_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('works_title', 'Our Work. Your Inspiration.'),
('works_description', 'A showcase of our premium cladding and glazing projects that combine innovation, precision and exceptional craftsmanship.'),
('works_meta_title', 'Our Works | Aura Cladding – Premium Cladding & Glazing Projects'),
('works_meta_description', 'Explore real cladding and glazing transformations by Aura Cladding. Commercial offices, shopping malls, hotels, residences and more across India.');

-- Optional sample projects (skip if you want empty)
INSERT INTO gallery_projects (title, slug, location, area, materials, status, sort_order, is_featured)
SELECT * FROM (
    SELECT 'Commercial Office' AS title, 'commercial-office-bangalore' AS slug, 'Bangalore, Karnataka' AS location, '45,000 sq.ft.' AS area, '["Glass & Steel"]' AS materials, 'published' AS status, 1 AS sort_order, 1 AS is_featured
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM gallery_projects LIMIT 1);

INSERT INTO gallery_projects (title, slug, location, area, materials, status, sort_order, is_featured)
SELECT * FROM (
    SELECT 'Shopping Mall', 'shopping-mall-pune', 'Pune, Maharashtra', '1,20,000 sq.ft.', '["Glass & Concrete"]', 'published', 2, 1
) AS tmp
WHERE (SELECT COUNT(*) FROM gallery_projects) < 2;

INSERT INTO gallery_projects (title, slug, location, area, materials, status, sort_order, is_featured)
SELECT * FROM (
    SELECT 'Corporate Building', 'corporate-building-hyderabad', 'Hyderabad, Telangana', '60,000 sq.ft.', '["Steel & Glass"]', 'published', 3, 0
) AS tmp
WHERE (SELECT COUNT(*) FROM gallery_projects) < 3;

INSERT INTO gallery_projects (title, slug, location, area, materials, status, sort_order, is_featured)
SELECT * FROM (
    SELECT 'Hotel Entrance', 'hotel-entrance-goa', 'Goa', '35,000 sq.ft.', '["Stone & Wood"]', 'published', 4, 0
) AS tmp
WHERE (SELECT COUNT(*) FROM gallery_projects) < 4;

INSERT INTO gallery_projects (title, slug, location, area, materials, status, sort_order, is_featured)
SELECT * FROM (
    SELECT 'Residential Villa', 'residential-villa-kochi', 'Kochi, Kerala', '8,000 sq.ft.', '["Concrete & Wood"]', 'published', 5, 0
) AS tmp
WHERE (SELECT COUNT(*) FROM gallery_projects) < 5;

INSERT INTO gallery_projects (title, slug, location, area, materials, status, sort_order, is_featured)
SELECT * FROM (
    SELECT 'Office Lobby', 'office-lobby-ahmedabad', 'Ahmedabad, Gujarat', '25,000 sq.ft.', '["Glass & Marble"]', 'published', 6, 0
) AS tmp
WHERE (SELECT COUNT(*) FROM gallery_projects) < 6;
