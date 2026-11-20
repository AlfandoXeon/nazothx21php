-- ============================================================================
-- Nazo Linktree - Seed & Migration SQL untuk Fitur Artikel (Artikel By Nazo)
-- File: database/seedsqlarticle.sql
--
-- Petunjuk Import (Hosting / InfinityFree / cPanel phpMyAdmin / Lokal):
-- 1. Pilih database aktif Anda terlebih dahulu di phpMyAdmin.
-- 2. Import file ini langsung (tanpa perlu CREATE DATABASE / USE).
-- 3. Aman dijalankan ulang (idempotent menggunakan IF NOT EXISTS & INSERT IGNORE).
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabel Artikel Utama
CREATE TABLE IF NOT EXISTS `articles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `excerpt` VARCHAR(350) DEFAULT '',
  `content` LONGTEXT NOT NULL,
  `thumbnail_url` VARCHAR(500) DEFAULT '',
  `category` VARCHAR(80) DEFAULT 'Umum',
  `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
  `views_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_articles_slug` (`slug`),
  KEY `idx_articles_status_pub` (`status`, `published_at`),
  KEY `idx_articles_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Log Pembaca Artikel (deduplikasi per IP 1 jam)
CREATE TABLE IF NOT EXISTS `article_views` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `article_id` INT(11) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `viewed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_av_article_ip_time` (`article_id`, `ip_address`, `viewed_at`),
  KEY `idx_av_viewed_at` (`viewed_at`),
  CONSTRAINT `fk_article_views_article`
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Komentar Artikel
CREATE TABLE IF NOT EXISTS `article_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `article_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `is_approved` TINYINT(1) NOT NULL DEFAULT 1,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ac_article_approved` (`article_id`, `is_approved`, `created_at`),
  KEY `idx_ac_ip_created` (`ip_address`, `created_at`),
  CONSTRAINT `fk_article_comments_article`
    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Default Pengaturan Halaman Artikel
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
  ('article_title', 'Artikel By Nazo'),
  ('article_subtitle', 'Tulisan, panduan, dan catatan terbaru dari Nazo.');

SET FOREIGN_KEY_CHECKS = 1;
