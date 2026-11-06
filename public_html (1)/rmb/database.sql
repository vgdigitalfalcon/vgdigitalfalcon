-- ============================================
-- RMB Chennai Central CRM - Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS rmb_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rmb_crm;

-- ---------------------------------------------
-- Table: admins  (CRM login users)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stored as password_hash()
    full_name VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- NOTE: Do NOT insert the admin row manually here.
-- After importing this SQL file, open setup_admin.php in your browser ONCE
-- to create the first admin login with a properly hashed password.
-- Then delete setup_admin.php from the server for security.

-- ---------------------------------------------
-- Table: members
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_picture VARCHAR(255) DEFAULT NULL,
    name VARCHAR(150) NOT NULL,              -- stored WITHOUT "Rtn." prefix
    position VARCHAR(150) DEFAULT NULL,
    company_name VARCHAR(150) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    club_name VARCHAR(150) DEFAULT NULL,
    linkedin VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    member_page ENUM('RMB Chennai Central Members', 'Rotarians Non Members') NOT NULL DEFAULT 'RMB Chennai Central Members',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
