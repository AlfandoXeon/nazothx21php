-- Nazo Linktree - Migration V2
-- 5 New Features: Maintenance Countdown, Share WA, SEO Meta, Visitor Counter, Link Click Tracker

USE nazo_linktree;

-- Visitor logs for daily analytics and total counter
CREATE TABLE IF NOT EXISTS visitor_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45),
  user_agent VARCHAR(500),
  page VARCHAR(100) DEFAULT '/',
  visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_date (visited_at),
  INDEX idx_ip_date (ip_address, visited_at)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Click tracker per card
CREATE TABLE IF NOT EXISTS card_clicks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  card_id INT NOT NULL,
  ip_address VARCHAR(45),
  clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_card (card_id),
  INDEX idx_date (clicked_at),
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- New Settings Keys
INSERT IGNORE INTO settings (`key`, `value`) VALUES
  ('maintenance_countdown', ''),
  ('seo_title', 'NAZO ナゾ — Links'),
  ('seo_description', 'NAZO ナゾ — MLBB Content Creator. Temukan semua media sosial dan konten Nazo di sini.'),
  ('seo_og_image', '');
