-- A.R. Waterproofing CRM Database Schema
-- Run this in MySQL to set up the database

CREATE DATABASE IF NOT EXISTS u670120666_arwater CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u670120666_arwater;

-- Admin users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services
CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  about TEXT,
  key_benefits TEXT,
  process TEXT,
  image_path VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects
CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  client_name VARCHAR(200),
  location VARCHAR(200),
  duration VARCHAR(100),
  overview TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Project media (images & videos)
CREATE TABLE project_media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  media_type ENUM('image', 'video') NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Default admin user (password: admin123) - CHANGE THIS AFTER SETUP
-- Generate new hash with: password_hash('yourpassword', PASSWORD_DEFAULT)
INSERT INTO users (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- The above hash is for password "password". For production, change immediately.

-- Sample service (optional, remove if not needed)
INSERT INTO services (name, about, key_benefits, process) VALUES 
('Terrace Waterproofing', 
 'Professional terrace waterproofing solutions to protect your building from seepage and rainwater penetration.',
 'Long-lasting protection against water damage\nPrevents structural deterioration\nSuitable for all terrace types\nExpert application methods',
 '1. Site inspection and assessment\n2. Surface preparation\n3. Application of waterproofing membrane\n4. Quality check and finishing\n5. Warranty and maintenance guidance');
