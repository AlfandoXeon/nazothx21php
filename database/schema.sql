-- Nazo Linktree - Database Schema
-- Database: nazo_linktree

CREATE DATABASE IF NOT EXISTS nazo_linktree CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nazo_linktree;

-- Profile
CREATE TABLE IF NOT EXISTS profile (
  id INT PRIMARY KEY DEFAULT 1,
  name VARCHAR(100) NOT NULL DEFAULT 'NAZO ナゾ',
  role VARCHAR(150) DEFAULT 'MLBB CONTENT CREATOR',
  bio TEXT,
  avatar_url VARCHAR(255) DEFAULT '/Foto/fotoProfileNazo.png',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cards (Social Media Links)
CREATE TABLE IF NOT EXISTS cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  subtitle TEXT,
  url TEXT NOT NULL,
  image_url TEXT,
  is_special TINYINT(1) DEFAULT 0,
  special_title VARCHAR(150),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Social Header Icons
CREATE TABLE IF NOT EXISTS social_header (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  url TEXT NOT NULL,
  icon_slug VARCHAR(100) COMMENT 'Simple Icons slug (e.g. tiktok, instagram)',
  sort_order INT DEFAULT 0
);

-- Gallery (Photos & Videos)
CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  file_path VARCHAR(255) NOT NULL,
  file_type ENUM('image','video') DEFAULT 'image',
  caption TEXT,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Public Comments
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  is_approved TINYINT(1) DEFAULT 1,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings (Key-Value)
CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT
);

-- Admin Activity Logs
CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(255),
  detail TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Visitor Logs (Analytics)
CREATE TABLE IF NOT EXISTS visitor_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45),
  user_agent VARCHAR(500),
  page VARCHAR(100) DEFAULT '/',
  visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_date (visited_at),
  INDEX idx_ip_date (ip_address, visited_at)
);

-- Card Click Tracker
CREATE TABLE IF NOT EXISTS card_clicks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  card_id INT NOT NULL,
  ip_address VARCHAR(45),
  clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_card (card_id),
  INDEX idx_date (clicked_at),
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE
);

-- Initial Essential Keys & Default Profile (No seed cards/comments)
INSERT IGNORE INTO profile (id, name, role, bio, avatar_url) VALUES
  (1, 'NAZO ナゾ', 'MLBB CONTENT CREATOR', '', '/Foto/fotoProfileNazo.png');

INSERT IGNORE INTO settings (`key`, `value`) VALUES
  ('footer_text', ''),
  ('gallery_title', 'Gallery'),
  ('gallery_subtitle', ''),
  ('ad_enabled', '0'),
  ('ad_title', ''),
  ('ad_description', ''),
  ('ad_image_url', ''),
  ('ad_target_url', ''),
  ('ad_button_text', 'Lihat Sekarang'),
  ('maintenance_mode', '0'),
  ('maintenance_countdown', ''),
  ('seo_title', 'NAZO ナゾ — Links'),
  ('seo_description', 'NAZO ナゾ — MLBB Content Creator. Temukan semua media sosial dan konten Nazo di sini.'),
  ('seo_og_image', '');
