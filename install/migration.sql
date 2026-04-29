-- =====================================================
-- Tekcan Metal CMS — Migration v1.0.5
-- Logo renkleri (kırmızı + lacivert) + slider yenileme
-- =====================================================

-- Slider görsellerini yeni adlarla güncelle
UPDATE tm_sliders
   SET image = 'uploads/sliders/slider-1-tekcan.jpg',
       title = 'Demir Adına Herşey',
       subtitle = 'Ticaret ile Bitmeyen Dostluk',
       description = 'Sac, boru, profil, hadde ve özel çelik ürünlerinde geniş stok, Konya merkezli hızlı sevkiyat ağıyla 1.000+ kurumsal müşteriye 7/24 hizmet.',
       link_text = 'Ürünlerimizi Keşfet',
       link_url = 'urunler.php'
 WHERE sort_order = 1;

UPDATE tm_sliders
   SET image = 'uploads/sliders/slider-2-laser.jpg',
       title = 'Lazer & Oksijen Kesim',
       subtitle = 'Hassas. Hızlı. Ekonomik.',
       description = 'CNC lazer ve oksijen kesim hizmetimizle, çiziminizden ürününüze kadar tek elden çözüm. Aynı gün üretim seçeneği.',
       link_text = 'Hizmetlerimiz',
       link_url = 'hizmetler.php'
 WHERE sort_order = 2;

UPDATE tm_sliders
   SET image = 'uploads/sliders/slider-3-delivery.png',
       title = '7/24 Sevkiyat Ağı',
       subtitle = 'Konya Merkezli, Türkiye Geneline',
       description = '20+ yıllık tecrübe ve 1.000+ kurumsal müşteri ile zamanında sevkiyat garantisi. Anlaşmalı nakliye firmalarımızla 81 ile teslimat.',
       link_text = 'Bize Ulaşın',
       link_url = 'iletisim.php'
 WHERE sort_order = 3;

-- Tema renk ayarlarını logo paletine geçir (lacivert + kırmızı)
UPDATE tm_settings SET setting_value = '#1e4a9e'  WHERE setting_key = 'theme_primary';
UPDATE tm_settings SET setting_value = '#c8102e'  WHERE setting_key = 'theme_accent';

-- Logo ve favicon path'lerini gerçek dosyalara zorla yönlendir
UPDATE tm_settings SET setting_value = 'assets/img/logo.png'    WHERE setting_key = 'logo';
UPDATE tm_settings SET setting_value = 'assets/img/favicon.jpg' WHERE setting_key = 'favicon';

