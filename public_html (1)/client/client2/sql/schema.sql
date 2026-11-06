CREATE DATABASE IF NOT EXISTS u670120666_clients CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE u670120666_clients;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','employee','client') NOT NULL,
  name VARCHAR(160) NOT NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  website VARCHAR(255) DEFAULT '',
  package_cost DECIMAL(12,2) DEFAULT 0,
  retention_day TINYINT NOT NULL DEFAULT 1,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS client_services (
  client_id INT NOT NULL,
  service VARCHAR(40) NOT NULL,
  PRIMARY KEY (client_id, service),
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS employee_profiles (
  user_id INT PRIMARY KEY,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS employee_work (
  user_id INT NOT NULL,
  work_type VARCHAR(20) NOT NULL,
  PRIMARY KEY (user_id, work_type),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS employee_clients (
  user_id INT NOT NULL,
  client_id INT NOT NULL,
  PRIMARY KEY (user_id, client_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS keywords (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  keyword VARCHAR(255) NOT NULL,
  search_volume INT DEFAULT 0,
  competition VARCHAR(40) DEFAULT '',
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS keyword_ranks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  keyword_id INT NOT NULL,
  log_date DATE NOT NULL,
  position INT NOT NULL,
  show_client TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (keyword_id) REFERENCES keywords(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS site_metrics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  log_date DATE NOT NULL,
  da DECIMAL(8,2) DEFAULT NULL,
  pa DECIMAL(8,2) DEFAULT NULL,
  spam_score DECIMAL(8,2) DEFAULT NULL,
  backlinks INT DEFAULT NULL,
  referring_domains INT DEFAULT NULL,
  dofollow_pct DECIMAL(8,2) DEFAULT NULL,
  nofollow_pct DECIMAL(8,2) DEFAULT NULL,
  show_json TEXT,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS range_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  kind ENUM('gsc','analytics') NOT NULL,
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  metrics_json TEXT NOT NULL,
  show_json TEXT,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS gbp_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  month CHAR(7) NOT NULL,
  overview DECIMAL(12,2) DEFAULT NULL,
  calls INT DEFAULT NULL,
  direction INT DEFAULT NULL,
  website_click INT DEFAULT NULL,
  chat_click INT DEFAULT NULL,
  show_json TEXT,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS offpage_rows (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  month CHAR(7) NOT NULL,
  link_type VARCHAR(120) NOT NULL,
  counts INT NOT NULL DEFAULT 0,
  show_client TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS onpage_issues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  issue VARCHAR(255) NOT NULL,
  fixation TEXT,
  status ENUM('done','pending','working') NOT NULL DEFAULT 'pending',
  show_client TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS client_channels (
  client_id INT NOT NULL,
  channel VARCHAR(30) NOT NULL,
  PRIMARY KEY (client_id, channel),
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS social_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  channel VARCHAR(30) NOT NULL,
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  metrics_json TEXT NOT NULL,
  show_json TEXT,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  name VARCHAR(180) NOT NULL,
  total_spend DECIMAL(12,2) DEFAULT 0,
  amount_spent DECIMAL(12,2) DEFAULT 0,
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  objective VARCHAR(255) DEFAULT '',
  leads INT DEFAULT 0,
  cost_per_lead DECIMAL(12,2) DEFAULT 0,
  cost_per_message DECIMAL(12,2) DEFAULT 0,
  cost_per_click DECIMAL(12,2) DEFAULT 0,
  ages_json TEXT,
  ig_reach INT DEFAULT 0,
  fb_reach INT DEFAULT 0,
  impressions INT DEFAULT 0,
  views INT DEFAULT 0,
  likes INT DEFAULT 0,
  shares INT DEFAULT 0,
  comments INT DEFAULT 0,
  ad_sets INT DEFAULT 0,
  ads INT DEFAULT 0,
  show_json TEXT,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
