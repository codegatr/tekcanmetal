-- =====================================================
-- Tekcan Metal CMS — Migration v1.0.6
-- Holding kurumsal tarzı: yeni slider metinleri + logo paleti
-- =====================================================

-- Slider görselleri + Holding tarzı yüksek lehçe başlıklar
UPDATE tm_sliders
   SET image = 'uploads/sliders/slider-1-tekcan.jpg',
       title = 'Demir-çelik sektöründe yarım asra yakın güven',
       subtitle = 'Tekcan Metal',
       description = '2005''ten bu yana Konya merkezli; sac, boru, profil, hadde ve özel çelik ürünlerinde Türkiye''nin lider üreticilerinin temsilciliği ile sanayi ve inşaat sektörüne çözüm üretiyoruz.',
       link_text = 'Biz Kimiz',
       link_url = 'hakkimizda.php'
 WHERE sort_order = 1;

UPDATE tm_sliders
   SET image = 'uploads/sliders/slider-2-laser.jpg',
       title = 'Tek elden, uçtan uca demir-çelik tedariği',
       subtitle = 'Çözüm Yelpazemiz',
       description = 'Geniş stoğumuz; lazer ve oksijen kesim atölyelerimiz; aynı gün üretim ve sevkiyat kapasitemiz ile projelerinizin her aşamasında yanınızdayız.',
       link_text = 'Faaliyet Alanlarımız',
       link_url = 'urunler.php'
 WHERE sort_order = 2;

UPDATE tm_sliders
   SET image = 'uploads/sliders/slider-3-delivery.png',
       title = 'Türkiye genelinde 7/24 sevkiyat ağı',
       subtitle = 'Operasyonel Mükemmellik',
       description = 'Konya merkezli stok depomuz ve anlaşmalı nakliye partnerlerimizle 81 ile zamanında, eksiksiz teslimat taahhüdü sunuyoruz.',
       link_text = 'Bize Ulaşın',
       link_url = 'iletisim.php'
 WHERE sort_order = 3;

-- Tema renk ayarları logo paletine
UPDATE tm_settings SET setting_value = '#1e4a9e'  WHERE setting_key = 'theme_primary';
UPDATE tm_settings SET setting_value = '#c8102e'  WHERE setting_key = 'theme_accent';

-- Logo ve favicon path'lerini gerçek dosyalara zorla yönlendir
UPDATE tm_settings SET setting_value = 'assets/img/logo.png'    WHERE setting_key = 'logo';
UPDATE tm_settings SET setting_value = 'assets/img/favicon.jpg' WHERE setting_key = 'favicon';


-- =====================================================
-- v1.0.27 — Ekip özelliği tamamen kaldırıldı
-- =====================================================
DROP TABLE IF EXISTS tm_team;

-- =====================================================
-- v1.0.31 — Petek Kiriş ürününe kategori görseli ata
-- =====================================================
UPDATE tm_products
   SET image = 'uploads/categories/petek-kiris.jpg'
 WHERE slug = 'petek-kiris'
   AND (image IS NULL OR image = '');

-- =====================================================
-- v1.0.33 — Slider'a eski WP'den 4 yeni görsel ekle
-- (Tekcan Metal endüstriyel görselleri)
-- =====================================================

-- Önce mevcut slider'ları temizle, en güzelleri yeniden yerleştir
DELETE FROM tm_sliders;

INSERT INTO tm_sliders (title, subtitle, description, image, link_text, link_url, sort_order, is_active) VALUES
(
  'Demir adına Herşey…',
  'Tekcan Metal — 2005''ten Bu Yana',
  'Konya merkezli, Türkiye''nin önde gelen üreticilerinin yetkili temsilciliği ile sac, boru, profil ve özel çelik ürünlerinde lider tedarikçi.',
  'uploads/wp-imported/2023/11/tekcan-metal.jpg',
  'Hakkımızda',
  'hakkimizda.php',
  1, 1
),
(
  'Geniş Stok, Hızlı Sevkiyat',
  'Operasyonel Mükemmellik',
  'Karatay merkezli stoğumuz ve anlaşmalı nakliye partnerlerimizle 81 ile zamanında, eksiksiz teslimat.',
  'uploads/wp-imported/2023/11/tekcan-metal-1.jpg',
  'Ürün Kataloğu',
  'urunler.php',
  2, 1
),
(
  'Boru ve Profil Tedariği',
  'Çözüm Yelpazemiz',
  'Su borusu, kazan borusu, konstrüksiyon borusu, kare ve dikdörtgen profil — her ölçü, her kalınlık.',
  'uploads/wp-imported/2023/09/boru_profil_1.jpg',
  'Boru ve Profil',
  'kategori.php?slug=boru',
  3, 1
),
(
  'Demirin Şekillendiği Yer',
  'Lazer ve Oksijen Kesim',
  'Atölye tesislerimizde projenize özel kesim, plaka açma ve hazırlık hizmetleri. Aynı gün üretim taahhüdü.',
  'uploads/wp-imported/2023/09/demirinsekillendigiyer.png',
  'Hizmetlerimiz',
  'hizmetler.php',
  4, 1
),
(
  'Lazer Kesimde Hassasiyet',
  'Mikron Toleransla Çalışıyoruz',
  'CNC kontrollü lazer kesim makinelerimizle 0.5 mm''den 25 mm''ye kadar her tür sac levhada hassas kesim.',
  'uploads/sliders/slider-2-laser.jpg',
  'Lazer Kesim Detayları',
  'hizmet.php?slug=lazer-kesim',
  5, 1
),
(
  'Türkiye Geneline Sevkiyat',
  '81 İl, 7/24 Hizmet',
  'Konya merkezli stok depomuzdan Türkiye''nin her noktasına anlaşmalı nakliye partnerlerimiz ile zamanında teslimat.',
  'uploads/sliders/slider-3-delivery.png',
  'İletişime Geç',
  'iletisim.php',
  6, 1
);

-- Çalışma saatleri ayarı düzeltildi
UPDATE tm_settings SET setting_value = 'Pazartesi-Cuma: 08:00–18:00 · Cumartesi: 08:00–13:00'
 WHERE setting_key = 'working_hours';
