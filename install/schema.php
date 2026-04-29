<?php
/**
 * Tekcan Metal — Veritabanı Şeması
 * Bu dosya install.php tarafından çalıştırılır
 */

return "
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. SİSTEM AYARLARI
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL,
  setting_value LONGTEXT,
  setting_group VARCHAR(50) DEFAULT 'general',
  PRIMARY KEY (id),
  UNIQUE KEY uniq_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. KULLANICILAR (Admin)
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(150) NOT NULL,
  username VARCHAR(80) NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  role ENUM('superadmin','admin','editor') DEFAULT 'admin',
  is_active TINYINT(1) DEFAULT 1,
  last_login DATETIME NULL,
  last_ip VARCHAR(45) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_email (email),
  UNIQUE KEY uniq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. SAYFALAR (Statik içerik: Hakkımızda, KVKK vs.)
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  title VARCHAR(200) NOT NULL,
  subtitle VARCHAR(255) NULL,
  content LONGTEXT NULL,
  meta_title VARCHAR(200) NULL,
  meta_desc VARCHAR(300) NULL,
  hero_image VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. ANASAYFA SLIDER
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_sliders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  subtitle VARCHAR(300) NULL,
  description TEXT NULL,
  image VARCHAR(255) NOT NULL,
  link_text VARCHAR(80) NULL,
  link_url VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ÜRÜN KATEGORİLERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id INT UNSIGNED NULL,
  slug VARCHAR(120) NOT NULL,
  name VARCHAR(150) NOT NULL,
  short_desc VARCHAR(300) NULL,
  description LONGTEXT NULL,
  icon VARCHAR(80) NULL,
  image VARCHAR(255) NULL,
  meta_title VARCHAR(200) NULL,
  meta_desc VARCHAR(300) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug),
  KEY idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. ÜRÜNLER
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  slug VARCHAR(160) NOT NULL,
  name VARCHAR(200) NOT NULL,
  short_desc VARCHAR(400) NULL,
  description LONGTEXT NULL,
  specs LONGTEXT NULL COMMENT 'JSON spec listesi',
  image VARCHAR(255) NULL,
  is_featured TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  view_count INT UNSIGNED DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug),
  KEY idx_category (category_id),
  KEY idx_featured (is_featured, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ÜRÜN GÖRSELLERİ (Galeri)
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_product_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  alt_text VARCHAR(200) NULL,
  sort_order INT DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. HİZMETLER (Lazer Kesim, Oksijen Kesim vs.)
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  title VARCHAR(200) NOT NULL,
  short_desc VARCHAR(400) NULL,
  description LONGTEXT NULL,
  icon VARCHAR(80) NULL,
  image VARCHAR(255) NULL,
  features LONGTEXT NULL COMMENT 'JSON array',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. EKİP ÜYELERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_team (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(150) NOT NULL,
  position VARCHAR(150) NOT NULL,
  bio TEXT NULL,
  photo VARCHAR(255) NULL,
  email VARCHAR(150) NULL,
  phone VARCHAR(40) NULL,
  linkedin VARCHAR(200) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. ÇÖZÜM ORTAKLARI / PARTNERLER
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_partners (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(200) NOT NULL,
  logo VARCHAR(255) NULL,
  website VARCHAR(255) NULL,
  description VARCHAR(400) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. BANKA / IBAN BİLGİLERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_banks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  bank_name VARCHAR(150) NOT NULL,
  branch VARCHAR(150) NULL,
  account_holder VARCHAR(200) NOT NULL DEFAULT 'TEKCAN METAL SAN. VE TİC. LTD. ŞTİ.',
  iban VARCHAR(50) NOT NULL,
  account_no VARCHAR(50) NULL,
  currency VARCHAR(10) DEFAULT 'TRY',
  logo VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. SIKÇA SORULAN SORULAR
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_faq (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category VARCHAR(80) DEFAULT 'genel',
  question VARCHAR(400) NOT NULL,
  answer LONGTEXT NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. BLOG KATEGORİLERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_blog_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  name VARCHAR(150) NOT NULL,
  description VARCHAR(400) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. BLOG YAZILARI
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_blog_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NULL,
  slug VARCHAR(180) NOT NULL,
  title VARCHAR(255) NOT NULL,
  excerpt VARCHAR(500) NULL,
  content LONGTEXT NULL,
  cover_image VARCHAR(255) NULL,
  author VARCHAR(150) DEFAULT 'Tekcan Metal',
  meta_title VARCHAR(200) NULL,
  meta_desc VARCHAR(300) NULL,
  published_at DATETIME NULL,
  view_count INT UNSIGNED DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug),
  KEY idx_published (published_at, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. FOTO GALERİ ALBÜMLERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_gallery_albums (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  title VARCHAR(200) NOT NULL,
  description VARCHAR(500) NULL,
  cover_image VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. FOTO GALERİ GÖRSELLERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_gallery_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  album_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  caption VARCHAR(300) NULL,
  sort_order INT DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_album (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. İLETİŞİM MESAJLARI
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NULL,
  company VARCHAR(200) NULL,
  subject VARCHAR(255) NULL,
  message TEXT NOT NULL,
  ip_address VARCHAR(45) NULL,
  is_read TINYINT(1) DEFAULT 0,
  is_replied TINYINT(1) DEFAULT 0,
  admin_note TEXT NULL,
  source VARCHAR(50) DEFAULT 'iletisim',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_read (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. MAIL ORDER FORMU
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_mail_orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(150) NOT NULL,
  company VARCHAR(200) NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  card_holder VARCHAR(150) NOT NULL,
  card_last4 VARCHAR(4) NULL,
  amount DECIMAL(12,2) NOT NULL,
  description TEXT NULL,
  ip_address VARCHAR(45) NULL,
  status ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
  admin_note TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 19. MÜŞTERİ SADAKAT ÜYELERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_loyalty_members (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(150) NOT NULL,
  company VARCHAR(200) NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  city VARCHAR(80) NULL,
  preferred_products TEXT NULL,
  ip_address VARCHAR(45) NULL,
  is_approved TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 20. SİSTEM SÜRÜMLERİ
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_system_versions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  version VARCHAR(20) NOT NULL,
  source ENUM('install','manual','github') DEFAULT 'manual',
  release_date DATETIME NULL,
  notes TEXT NULL,
  applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  applied_by VARCHAR(150) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 21. AKTİVİTE LOGU
-- =====================================================
CREATE TABLE IF NOT EXISTS tm_activity_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  target_type VARCHAR(50) NULL,
  target_id INT UNSIGNED NULL,
  description VARCHAR(500) NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user (user_id),
  KEY idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
";
