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

INSERT IGNORE INTO tm_sliders (title, subtitle, description, image, link_text, link_url, sort_order, is_active) VALUES
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

-- =====================================================
-- v1.0.35 — Tüm ürünler için teknik bilgi (description + specs JSON)
-- =====================================================

-- 1. SİYAH SAC
UPDATE tm_products SET
  description = '<h3>Siyah Sac Nedir?</h3><p>Siyah sac, yüzeyi koruyucu kaplama içermeyen, ham haldeki haddelenmiş çelik saclardır. Endüstriyel imalat, makine imalatı, çelik konstrüksiyon ve genel sanayi uygulamalarında en yaygın kullanılan ham sac türüdür.</p><h3>Üretim Yöntemi</h3><p>Sıcak haddeleme yöntemiyle, önceden tavlanmış çelik kütüklerden istenen kalınlık ve genişliğe getirilir. Yüzey siyahımsı bir oksit tabakası ile kaplıdır - bu tabaka galvaniz veya boya öncesinde temizlenir.</p><h3>Kullanım Alanları</h3><ul><li>Çelik konstrüksiyon (kolon, kiriş, makas)</li><li>Makine imalatı ve şasi üretimi</li><li>Tank, depo, silo gövde imalatı</li><li>Lazer/oksijen kesim sonrası ara işleme</li><li>Galvaniz veya boyama öncesi alt malzeme</li></ul><h3>Standartlar</h3><p>TS EN 10025-2 (S235JR, S275JR, S355JR), TS EN 10051, TS 3812 standartlarına uygundur.</p>',
  specs = '{"Standart":"TS EN 10025-2 / TS EN 10051","Kalite":"S235JR · S275JR · S355JR","Kalınlık Aralığı":"1 mm – 100 mm","Genişlik":"1000 / 1250 / 1500 / 2000 mm","Boy":"2000 / 2500 / 3000 / 6000 mm","Yoğunluk":"7,85 g/cm³","Yüzey":"Sıcak haddelenmiş, oksitli","Kaynaklanabilirlik":"Mükemmel","Şekillendirilebilirlik":"İyi"}'
WHERE slug = 'siyah-sac';

-- 2. DKP SAC
UPDATE tm_products SET
  description = '<h3>DKP Sac Nedir?</h3><p>DKP (Düşük Karbon Pres) Sac, soğuk haddeleme yöntemiyle üretilen, yüzeyi pürüzsüz ve parlak, otomotiv ve beyaz eşya endüstrisinin tercih ettiği yüksek kaliteli saclardır. Düşük karbon içeriği nedeniyle mükemmel şekillendirilebilirlik özelliği taşır.</p><h3>Üretim Yöntemi</h3><p>Sıcak haddelenmiş sac, soğuk haddeleme tezgahlarında oda sıcaklığında inceltilir. Sonrasında tavlama (annealing) işlemi ile gerilim alınır ve yüzey kalitesi mükemmelleştirilir.</p><h3>Kullanım Alanları</h3><ul><li>Otomotiv kaport, panel ve gövde parçaları</li><li>Beyaz eşya gövde ve iç parça imalatı</li><li>Mobilya endüstrisi ve döşeme parçaları</li><li>Klima, kombi, fırın gövde imalatı</li><li>Boyama ve kaplama sonrası dekoratif ürünler</li></ul><h3>Avantajları</h3><p>DKP saclar, yüzey pürüzsüzlüğü, derin çekilebilirlik, hassas tolerans ve kaliteli kaynak özellikleri ile diğer sac türlerinden ayrışır.</p>',
  specs = '{"Standart":"TS EN 10130 (DC01 / DC03 / DC04 / DC05 / DC06)","Kalite":"DC01 · DC03 · DC04","Kalınlık Aralığı":"0,4 mm – 3 mm","Genişlik":"1000 / 1250 / 1500 mm","Boy":"2000 / 2500 / 3000 mm","Yoğunluk":"7,85 g/cm³","Yüzey":"Soğuk haddelenmiş, pürüzsüz parlak","Tolerans":"±0,05 mm","Çekilebilirlik":"Mükemmel"}'
WHERE slug = 'dkp-sac';

-- 3. HRP SAC ⭐ — Yunus özel istedi
UPDATE tm_products SET
  description = '<h3>HRP Sac Nedir?</h3><p>HRP (Hot Rolled Pickled - Sıcak Haddelenmiş Asitlenmiş) Sac, sıcak haddeleme sonrası asitleme (pickling) işleminden geçirilerek yüzeydeki oksit tabakası temizlenmiş çelik saclardır. Siyah sacın yüzey kalitesinin iyileştirilmiş halidir; geniş kalınlık aralığında ekonomik bir tedarik çözümü sunar.</p><h3>Üretim Yöntemi</h3><p>Sıcak haddelenmiş sac, sülfürik veya hidroklorik asit banyosunda yüzey oksitlerinden temizlenir, ardından yağlanarak (oiled) korozyondan korunur. Bu işlem yüzey kalitesini artırır, kaplama ve boyama hazırlığını kolaylaştırır.</p><h3>Kullanım Alanları</h3><ul><li>Genel sanayi ve makine imalatı</li><li>Çelik mobilya ve raf sistemleri</li><li>Tarım makineleri ve ekipmanları</li><li>Otomotiv yan sanayi parçaları</li><li>Kazan, basınçlı kap ve kazıcı imalat</li><li>Galvaniz ve boya öncesi alt malzeme</li></ul><h3>HRP vs DKP vs Siyah Sac Farkı</h3><p><strong>Siyah sac</strong> en ekonomik, yüzeyi oksitli; <strong>HRP sac</strong> orta segment, yüzeyi temizlenmiş; <strong>DKP sac</strong> yüksek kalite, soğuk haddelenmiş ve pürüzsüz. HRP, kalın saclarda DKP almaya alternatif olarak kullanılır (DKP genellikle 3 mm üstüne ekonomik değildir).</p><h3>Standartlar</h3><p>TS EN 10025-2, TS EN 10051, TS EN 10111 standartlarına uygundur.</p>',
  specs = '{"Standart":"TS EN 10025-2 / TS EN 10111","Kalite":"S235JR · S275JR · S355JR · DD11 · DD13","Kalınlık Aralığı":"1,5 mm – 12 mm","Genişlik":"1000 / 1250 / 1500 / 2000 mm","Boy":"2000 / 2500 / 3000 / 6000 mm","Yoğunluk":"7,85 g/cm³","Yüzey":"Asitlenmiş ve yağlanmış (Pickled & Oiled)","Tolerans":"TS EN 10051","Akma Dayanımı":"235-355 N/mm² (kaliteye göre)","Kaynaklanabilirlik":"Mükemmel","Şekillendirilebilirlik":"İyi (DD11/DD13 daha iyi)"}'
WHERE slug = 'hrp-sac';

-- 4. ST-52 SAC
UPDATE tm_products SET
  description = '<h3>ST-52 Sac Nedir?</h3><p>ST-52 (modern adı S355JR), yüksek mukavemetli yapı çeliğidir. Standart yapı çeliklerine göre %50 daha yüksek mukavemete sahip olduğundan ağır yük taşıyan konstrüksiyon ve makine imalat uygulamalarında tercih edilir. Aynı yükü daha ince kesitle taşıyabildiği için ağırlık ve maliyet avantajı sağlar.</p><h3>Mekanik Özellikleri</h3><p>ST-52 sacların akma dayanımı 355 N/mm², çekme dayanımı 470-630 N/mm² aralığındadır. Soğuk şekillendirilmeye uygun, kaynaklanabilirliği yüksek bir malzemedir. Düşük sıcaklık tokluğu özelliği sayesinde -20°C''ye kadar güvenle kullanılabilir.</p><h3>Kullanım Alanları</h3><ul><li>Köprü ve viyadük çelik konstrüksiyonu</li><li>Vinç, taşıma ve kaldırma ekipmanları</li><li>Tarım makineleri ve iş makinesi şasileri</li><li>Boru hattı ve basınçlı kap imalatı</li><li>Endüstriyel raf, depo ve silo</li><li>Otomotiv ağır ticari araç şasileri</li></ul>',
  specs = '{"Standart":"TS EN 10025-2","Kalite":"S355JR (eski adı: ST-52-3)","Akma Dayanımı (ReH)":"≥ 355 N/mm² (≤ 16 mm)","Çekme Dayanımı (Rm)":"470 – 630 N/mm²","Uzama (A)":"≥ %20","Çentik Tokluğu":"27 J / +20°C","Kalınlık Aralığı":"3 mm – 100 mm","Genişlik":"1500 / 2000 mm","Boy":"6000 / 12000 mm","Yoğunluk":"7,85 g/cm³","Kaynak Sınıfı":"Çok iyi","C İçeriği":"≤ %0,22"}'
WHERE slug = 'st52-sac';

-- 5. GALVANİZLİ SAC
UPDATE tm_products SET
  description = '<h3>Galvanizli Sac Nedir?</h3><p>Galvanizli sac, yüzeyi sıcak daldırma yöntemi ile çinko (Zn) ile kaplanmış çelik saclardır. Çinko kaplama, çeliği atmosferik korozyondan koruyarak ürün ömrünü 5 ila 20 katına çıkarır. Açık alan uygulamalarında, nemli ortamlarda ve kimyasal etkenlere maruz yerlerde tercih edilir.</p><h3>Üretim Yöntemi (Sıcak Daldırma)</h3><p>Hadde sacı 450°C''de eritilmiş çinko banyosuna daldırılır, çıkarılırken yüzeydeki çinko miktarı havalı bıçaklarla kontrol edilir. Üretilen sacın her iki yüzünde 100, 150, 200, 275 g/m² (Z100-Z275) çinko bulunur.</p><h3>Galvaniz Türleri</h3><ul><li><strong>Z100:</strong> İç mekan, kapalı alan uygulamaları</li><li><strong>Z150:</strong> Genel amaçlı, en yaygın</li><li><strong>Z200/Z225:</strong> Açık alan, çatı, cephe</li><li><strong>Z275:</strong> Endüstriyel, ağır korozif ortam</li><li><strong>Z350:</strong> Ekstrem korozif ortam, deniz</li></ul><h3>Kullanım Alanları</h3><ul><li>Çatı ve cephe panelleri</li><li>Trapez ve oluklu sac üretimi</li><li>HVAC kanal sistemleri</li><li>Beyaz eşya iç parçaları</li><li>Otomotiv kaport ve şasi</li><li>Tarım sera ve sulama yapıları</li></ul>',
  specs = '{"Standart":"TS EN 10346","Çelik Kalitesi":"DX51D · DX52D · DX53D · S250GD · S350GD","Çinko Kaplaması":"Z100 / Z150 / Z200 / Z275 / Z350 (g/m² çift yüz)","Kalınlık Aralığı":"0,3 mm – 4 mm","Genişlik":"1000 / 1250 / 1500 mm","Boy":"2000 / 2500 / 3000 mm","Yüzey":"Çinko kaplı, çiçekli (spangled) veya çiçeksiz","Korozyon Direnci":"Atmosferik 20-50 yıl","Kaynaklanabilirlik":"İyi (özel elektrod ile)","Boyanabilirlik":"Mükemmel"}'
WHERE slug = 'galvanizli-sac';

-- 6. SU BORUSU
UPDATE tm_products SET
  description = '<h3>Su Borusu Nedir?</h3><p>Su borusu, içme suyu, kullanma suyu ve yangın söndürme tesisatlarında kullanılan dikişli çelik borulardır. TS 301-2 standardında üretilen siyah ve galvanizli olarak iki tipi mevcuttur. Tesisat kolaylığı ve uzun ömürlü kullanımı için galvanizli olanı tercih edilir.</p><h3>Üretim ve Standartlar</h3><p>Sac şeritlerden HFI (Yüksek Frekanslı İndüksiyon) kaynak yöntemiyle üretilir. Hidrostatik test ve sızdırmazlık kontrolünden geçer. Standart boy 6 metredir.</p><h3>Diş Çekme</h3><p>Standart vidalı bağlantı için BSP (TS ISO 7-1) dişi çekilir. Ayrıca alın kaynak için düz uçlu da temin edilebilir.</p><h3>Kullanım Alanları</h3><ul><li>İçme ve kullanma suyu tesisatları</li><li>Yangın söndürme sistem boruları</li><li>Sulama ve drenaj sistemleri</li><li>Sıcak su ve kalorifer tesisatları</li><li>Genel basınçsız akışkan tesisatları</li></ul>',
  specs = '{"Standart":"TS 301-2 / TS EN 10255","Tip":"Dikişli (HFI Kaynaklı)","Yüzey":"Siyah veya Galvanizli (Sıcak daldırma)","Çap Aralığı":"1/2\\" – 4\\" (DN15 – DN100)","Et Kalınlığı":"2,3 mm – 4,5 mm","Standart Boy":"6 metre","Çinko Kaplaması":"≥ 400 g/m² (galvanizli)","Test Basıncı":"50 bar","Çalışma Basıncı":"16 bar","Diş Standardı":"TS ISO 7-1 (BSP)","Bağlantı":"Dişli veya alın kaynak"}'
WHERE slug = 'su-borusu';

-- 7. KAZAN BORUSU
UPDATE tm_products SET
  description = '<h3>Kazan Borusu Nedir?</h3><p>Kazan borusu, yüksek basınç ve sıcaklık koşullarında çalışan kazan, ısı eşanjörü ve buhar üretim sistemlerinde kullanılan özel alaşımlı dikişsiz veya dikişli çelik borulardır. ASME ve EN standartlarında üretilir, yüksek mukavemet ve sürünme dayanımı gerektirir.</p><h3>Tipleri</h3><ul><li><strong>Su borusu kazan:</strong> İçinden su geçen, dışı yanma gazına maruz</li><li><strong>Yangın borusu kazan:</strong> İçinden yanma gazı geçen, dışı su</li><li><strong>Kızdırıcı boruları:</strong> Doymuş buharın aşırı kızdırıldığı yüksek sıcaklık boruları</li><li><strong>Eşanjör boruları:</strong> Isı transferi için</li></ul><h3>Çelik Kaliteleri</h3><p>St 35.8, St 45.8 (DIN 17175), P235GH, P265GH (EN 10216-2), 16Mo3 (alaşımlı), 13CrMo4-5, 10CrMo9-10 (yüksek sıcaklık alaşımları).</p>',
  specs = '{"Standart":"DIN 17175 / EN 10216-2 / TS EN 10216","Kalite":"St 35.8 · St 45.8 · P235GH · P265GH · 16Mo3","Tip":"Dikişsiz (Seamless) veya HFI Dikişli","Çap Aralığı":"21,3 mm – 273 mm","Et Kalınlığı":"2,9 mm – 16 mm","Standart Boy":"6 / 12 metre","Çalışma Sıcaklığı":"0°C – 530°C","Maks. Çalışma Basıncı":"40 bar (kalibreye göre 100 bara kadar)","Test":"Hidrostatik + Eddy Current","Sertifika":"3.1 / 3.2 (EN 10204)"}'
WHERE slug = 'kazan-borusu';

-- 8. KONSTRÜKSİYON BORUSU
UPDATE tm_products SET
  description = '<h3>Konstrüksiyon Borusu Nedir?</h3><p>Konstrüksiyon borusu, yapı ve makine imalatında kullanılan, yüksek mukavemetli yuvarlak kesitli dikişli veya dikişsiz çelik borulardır. Çelik konstrüksiyon, makine şasi, taşıyıcı kolon, halka ve dövme imalatında tercih edilir.</p><h3>Üretim Yöntemleri</h3><p><strong>Dikişli (ERW/HFI):</strong> Sac şeridin yüksek frekanslı kaynakla yuvarlatılması. Daha ekonomik. <strong>Dikişsiz (Seamless):</strong> Solid çelik kütüğün delinmesi. Daha pahalı, basınçlı uygulamalar için.</p><h3>Çelik Kaliteleri</h3><p>S235JR, S275JR, S355JR (yapı çeliği); E235, E275, E355 (mekanik özellikli boru); özel kalitelerde alaşımlı.</p><h3>Kullanım Alanları</h3><ul><li>Çelik konstrüksiyon kolon ve kirişleri</li><li>Makine ve araç şasi imalatı</li><li>Vinç, asansör ve taşıma sistemleri</li><li>Spor sahası ve tribün konstrüksiyonu</li><li>Aydınlatma direği ve trafik levha direği</li><li>Mobilya ve dekoratif imalat</li></ul>',
  specs = '{"Standart":"TS EN 10210 / TS EN 10219 / DIN 2440","Kalite":"S235JR · S275JR · S355J2H","Tip":"Dikişli (HFI/ERW) veya Dikişsiz","Çap Aralığı":"21,3 mm – 508 mm","Et Kalınlığı":"2 mm – 25 mm","Standart Boy":"6 / 12 metre","Yüzey":"Siyah, asitlenmiş veya galvanizli","Test":"Hidrostatik basınç + Eddy Current"}'
WHERE slug = 'konstruksiyon-boru';

-- 9. KARE PROFİL
UPDATE tm_products SET
  description = '<h3>Kare Profil Nedir?</h3><p>Kare profil, kare kesitli içi boş çelik profillerdir (HSS - Hollow Structural Section). Konstrüksiyon, dekorasyon, mobilya ve makine imalatında kullanılır. Kare kesit dolu mile göre daha hafif olduğu halde benzer mukavemet sağlar — ekonomik ve etkin bir tercih.</p><h3>Üretim</h3><p>Sıcak haddelenmiş sac şeritten HFI kaynak yöntemiyle yuvarlatılır, sonra kare şekle bükülerek kaynak kapatılır. ERW (elektrik kaynaklı) veya seamless (dikişsiz) tipleri mevcuttur.</p><h3>Standart Ölçüler</h3><p>20×20 - 200×200 mm aralığında, 1,5 - 12 mm et kalınlığında. Standart boy 6 metredir; siparişe göre 12 metre üretilebilir.</p><h3>Kullanım Alanları</h3><ul><li>Çelik konstrüksiyon iskelet ve kolon</li><li>Spor salonu ve sanayi yapısı çatı kafesi</li><li>Çit, korkuluk ve kapı kanat profili</li><li>Mobilya ayak ve iskelet imalatı</li><li>Tarım sera konstrüksiyonu</li></ul>',
  specs = '{"Standart":"TS EN 10219-1 / EN 10210","Kalite":"S235JRH · S275J2H · S355J2H","Tip":"Dikişli (HFI/ERW)","Standart Ölçüler":"20×20 · 25×25 · 30×30 · 40×40 · 50×50 · 60×60 · 80×80 · 100×100 · 120×120 · 150×150 · 200×200 mm","Et Kalınlığı":"1,5 mm – 12 mm","Standart Boy":"6 metre","Köşe Yarıçapı":"1,5×t – 2,5×t","Yüzey":"Siyah veya galvanizli","Toleranslar":"EN 10219-2"}'
WHERE slug = 'kare-profil';

-- 10. DİKDÖRTGEN PROFİL
UPDATE tm_products SET
  description = '<h3>Dikdörtgen Profil Nedir?</h3><p>Dikdörtgen profil, dikdörtgen kesitli içi boş çelik profillerdir. Asimetrik kesiti sayesinde tek yönde daha fazla mukavemet gerektiren konstrüksiyon uygulamalarında ideal. Çelik kafes kiriş, geniş açıklıklı yapılar ve makine imalatında tercih edilir.</p><h3>Tasarım Avantajları</h3><p>Dikdörtgen kesit, ana yöndeki eylemsizlik momentini artırır — daha az malzeme ile daha uzun açıklıkları taşıma kapasitesi sağlar. Kare profile göre tasarımcıya daha fazla esneklik sunar.</p><h3>Standart Ölçüler</h3><p>30×20 - 300×200 mm aralığında, 1,5 - 12,5 mm et kalınlığında. Standart boy 6 metredir.</p><h3>Kullanım Alanları</h3><ul><li>Yapısal kiriş ve kafes sistemler</li><li>Çelik konstrüksiyon doğrama ve cephe</li><li>Makine şasi ve taşıyıcı parçalar</li><li>Sera, depo, hangar konstrüksiyonu</li><li>Kapı ve kanat profili</li></ul>',
  specs = '{"Standart":"TS EN 10219-1","Kalite":"S235JRH · S275J2H · S355J2H","Standart Ölçüler":"30×20 · 40×20 · 40×30 · 50×30 · 60×40 · 80×40 · 100×50 · 100×60 · 120×60 · 120×80 · 150×100 · 200×100 · 200×120 · 300×200 mm","Et Kalınlığı":"1,5 mm – 12,5 mm","Standart Boy":"6 metre","Yüzey":"Siyah veya galvanizli","Bağlantı":"Kaynak veya cıvata"}'
WHERE slug = 'diktortgen-profil';

-- 11. OVAL PROFİL
UPDATE tm_products SET
  description = '<h3>Oval Profil Nedir?</h3><p>Oval profil, eliptik kesitli içi boş çelik profillerdir. Estetik özelliği nedeniyle dekoratif uygulamalarda, korkuluk, banyo aksesuar ve mobilya ayağı imalatında tercih edilir. Aynı zamanda akışkan dirençi düşük olduğundan bazı endüstriyel uygulamalarda da kullanılır.</p><h3>Yüzey Seçenekleri</h3><p>Siyah, dekapaj edilmiş, krom kaplama, paslanmaz çelik (304, 316), galvanizli ve elektrostatik toz boyalı seçenekleri mevcuttur. İç mekan dekorasyonunda krom kaplı ve paslanmaz tipleri öne çıkar.</p><h3>Kullanım Alanları</h3><ul><li>Banyo aksesuar ve havlu askısı imalatı</li><li>Korkuluk, küpeşte ve süs profilleri</li><li>Mobilya ayağı ve iskelet</li><li>Cam vitrin ve sergi standı</li><li>Otobüs ve metro tutamak imalatı</li></ul>',
  specs = '{"Standart":"DIN 59411","Kalite":"S235JR · 304 / 316 paslanmaz","Standart Ölçüler":"20×10 · 30×15 · 40×20 · 50×25 · 60×30 · 80×40 mm","Et Kalınlığı":"1 mm – 3 mm","Standart Boy":"6 metre","Yüzey":"Krom · Paslanmaz · Galvaniz · Elektrostatik boya"}'
WHERE slug = 'oval-profil';

-- 12. LAMA
UPDATE tm_products SET
  description = '<h3>Lama Nedir?</h3><p>Lama, dikdörtgen kesitli düz çelik çubuklardır (flat bar / strip). Demir-çelik sektörünün en temel yarı mamulüdür; konstrüksiyon, makine imalatı, ferforje, dövme demir ve dekorasyon işlerinde geniş kullanım alanı bulur.</p><h3>Üretim</h3><p>Sıcak haddeleme yöntemiyle üretilir. Standart 6 metre boyunda; istek üzerine 12 metreye kadar uzun temin edilebilir. Kalite ST37 (S235JR) en yaygın olanıdır.</p><h3>Standart Ölçüler</h3><p>Genişlik 12-150 mm, kalınlık 3-30 mm aralığında çok geniş bir ölçü yelpazesi mevcuttur. Standart ölçüler: 20×3, 25×5, 30×5, 40×6, 50×8, 60×10, 80×10, 100×12, 120×15, 150×20.</p><h3>Kullanım Alanları</h3><ul><li>Korkuluk, küpeşte, çit imalatı</li><li>Konstrüksiyon kuşak, bağlantı plakası</li><li>Ferforje süsleme, kapı doğraması</li><li>Makine yatakları, kızak rayları</li><li>Tarım makinesi parçaları</li><li>Dövme ve şekillendirme yarı mamulü</li></ul>',
  specs = '{"Standart":"TS 911 / DIN 1017","Kalite":"S235JR (ST37)","Genişlik":"12 mm – 150 mm","Kalınlık":"3 mm – 30 mm","Standart Boy":"6 metre","Yoğunluk":"7,85 g/cm³","Yüzey":"Sıcak haddelenmiş, oksitli"}'
WHERE slug = 'lama';

-- 13. KÖŞEBENT
UPDATE tm_products SET
  description = '<h3>Köşebent Nedir?</h3><p>Köşebent (Eşit veya Eşit Olmayan Kollu L Profil), 90° dik açılı L kesitli sıcak haddelenmiş çelik profillerdir. Çelik konstrüksiyonun temel yapı taşıdır; çatı makası, kafes kiriş, kolon birleşim ve genel taşıyıcı uygulamalarda kullanılır.</p><h3>İki Tipi Vardır</h3><ul><li><strong>Eşit Kollu (L):</strong> Her iki kol eşit uzunlukta. 20×20 - 200×200 mm.</li><li><strong>Eşit Olmayan Kollu (LL):</strong> Bir kol diğerinden uzun. 30×20, 40×20, 50×30, 60×40, 80×50, 100×65 mm vb.</li></ul><h3>Kullanım Alanları</h3><ul><li>Çatı makası ve kafes konstrüksiyon</li><li>Yapı çatı ve cephe iskeleti</li><li>Tank, silo, depo gövde takviyesi</li><li>Sera, kümes, ahır konstrüksiyonu</li><li>Sanayi raf ve istif sistemi</li><li>Beton kalıp panel ve takviye</li></ul>',
  specs = '{"Standart":"TS 910 / DIN 1028 / DIN 1029","Kalite":"S235JR · S275JR · S355JR","Tip 1 (Eşit Kollu)":"20×20×3 - 200×200×20 mm","Tip 2 (Eşit Olmayan)":"30×20×3 - 200×100×14 mm","Standart Boy":"6 / 12 metre","Yoğunluk":"7,85 g/cm³","Yüzey":"Sıcak haddelenmiş"}'
WHERE slug = 'kosebent';

-- 14. HEA / HEB PROFİL
UPDATE tm_products SET
  description = '<h3>HEA / HEB Profil Nedir?</h3><p>HEA ve HEB, Avrupa standardı geniş başlıklı I kesitli yapı profilleridir (Wide Flange Beam). Modern çelik yapı tasarımının temel taşıyıcı elemanlarıdır. Yüksek atalet momenti ve yanal stabilite sağlayan kesitleri ile kolon ve büyük açıklıklı kirişlerde tercih edilir.</p><h3>HEA vs HEB Farkı</h3><ul><li><strong>HEA (Geniş Başlıklı, Hafif):</strong> Et kalınlığı düşük, daha hafif. Birim ağırlığı düşük olduğu için ekonomik kolon-kiriş tercihi.</li><li><strong>HEB (Geniş Başlıklı, Standart):</strong> Daha kalın etler, yüksek mukavemet. Ağır taşıyıcı ve yüksek yük gerektiren uygulamalar.</li></ul><h3>HEA Birim Ağırlık (Örnek)</h3><p>HEA 100: 16,7 kg/m · HEA 200: 42,3 kg/m · HEA 300: 88,3 kg/m · HEA 600: 178 kg/m</p><h3>HEB Birim Ağırlık (Örnek)</h3><p>HEB 100: 20,4 kg/m · HEB 200: 61,3 kg/m · HEB 300: 117 kg/m · HEB 600: 212 kg/m</p><h3>Kullanım Alanları</h3><ul><li>Çok katlı bina iskelet konstrüksiyonu</li><li>Endüstriyel hangar ve depo yapıları</li><li>Köprü ve viyadük taşıyıcı sistemleri</li><li>Vinç ve taşıma sistemleri kolonu</li><li>Yüksek açıklıklı çatı taşıyıcıları</li></ul>',
  specs = '{"Standart":"TS EN 10025-2 / DIN 1025","Kalite":"S235JR · S275JR · S355JR · S460","HEA Boyut Aralığı":"HEA 100 – HEA 1000","HEB Boyut Aralığı":"HEB 100 – HEB 1000","Standart Boy":"6 / 12 / 15 metre","Tolerans":"EN 10034","Yoğunluk":"7,85 g/cm³"}'
WHERE slug = 'hea-heb';

-- 15. NPI / NPU PROFİL
UPDATE tm_products SET
  description = '<h3>NPI / NPU Profil Nedir?</h3><p>NPI (Normal Profil I) ve NPU (Normal Profil U), klasik Avrupa standardı yapı profilleridir. NPI dar başlıklı I kesit, NPU ise U kanal kesitlidir. HEA/HEB''e göre daha eski standartlardır ancak hâlâ özel uygulamalarda tercih edilir.</p><h3>NPI Profil (I Beam)</h3><p>Eski "Norm Profile I" standardı. Geniş başlıklı IPE/HEA''ya göre daha dar başlıklı, daha yüksek/dar kesit. Genellikle eğri kuşak ve raylı taşıma sistemlerinde tercih edilir.</p><h3>NPU Profil (U / Channel)</h3><p>U şeklinde tek tarafı açık kesit. Geniş kullanım alanı: çatı aşığı, çerçeve birleştirme, kanal taşıma sistemleri, kapı doğramaları.</p><h3>Birim Ağırlıklar</h3><p>NPI 80: 5,94 kg/m · NPI 200: 26,2 kg/m · NPI 300: 54,2 kg/m · NPU 80: 8,64 kg/m · NPU 200: 25,3 kg/m · NPU 300: 46,2 kg/m</p>',
  specs = '{"Standart":"TS 911 / DIN 1025-1 / DIN 1026","NPI Boyut Aralığı":"NPI 80 – NPI 600","NPU Boyut Aralığı":"NPU 50 – NPU 400","Kalite":"S235JR · S275JR · S355JR","Standart Boy":"6 / 12 metre","Yüzey":"Sıcak haddelenmiş","Yoğunluk":"7,85 g/cm³"}'
WHERE slug = 'npi-npu';

-- 16. KARE DEMİRİ
UPDATE tm_products SET
  description = '<h3>Kare Demiri Nedir?</h3><p>Kare demiri, kare kesitli dolu çelik milllerdir (square solid bar). Dövme demir ferforje işleri, dekoratif demir parmaklık ve kapı imalatı, makine miline mil imalatı, korkuluk ve süsleme uygulamalarında kullanılır.</p><h3>Üretim</h3><p>Sıcak haddeleme yöntemiyle üretilir. Yüzey sıcak haddelenmiş veya çekilmiş olabilir. Çekilmiş tipleri daha pürüzsüz ve hassas tolerans sunar — makine işleri için uygundur.</p><h3>Standart Ölçüler</h3><p>8×8, 10×10, 12×12, 14×14, 16×16, 20×20, 25×25, 30×30, 40×40, 50×50, 60×60, 80×80, 100×100 mm.</p><h3>Kullanım Alanları</h3><ul><li>Ferforje kapı, parmaklık, korkuluk</li><li>Dövme süsleme imalatı</li><li>Makine mili, dişli ham mali</li><li>Klavye ve mekanizma parçası</li><li>Sanat eserleri ve dekoratif imalat</li></ul>',
  specs = '{"Standart":"TS 911 / DIN 1014","Kalite":"S235JR (yumuşak) · S275JR · C45 (sertleştirilebilir)","Boyut":"8×8 - 100×100 mm","Standart Boy":"6 metre","Yüzey":"Sıcak haddelenmiş veya çekilmiş","Yoğunluk":"7,85 g/cm³"}'
WHERE slug = 'kare-demiri';

-- 17. PATENT DİRSEK
UPDATE tm_products SET
  description = '<h3>Patent Dirsek Nedir?</h3><p>Patent dirsek, boru hat sistemlerinde yön değişikliğini sağlayan, 90° veya 45° açılı dövme çelik bağlantı elemanlarıdır. Sıcak dövme yöntemi ile üretilir ve EN/ANSI/JIS standartlarında imal edilir. Boru hatlarındaki en yaygın bağlantı parçalarındandır.</p><h3>Tipleri</h3><ul><li><strong>90° Patent Dirsek:</strong> Standart, en yaygın. Kısa ve uzun radius olarak iki tipi var.</li><li><strong>45° Patent Dirsek:</strong> Daha yumuşak yön değişikliği gerektiren uygulamalar.</li><li><strong>180° U Dirsek:</strong> Geri dönüş için.</li><li><strong>Eccentric Dirsek:</strong> Boru ekseni kaydırması için.</li></ul><h3>Kullanım Alanları</h3><ul><li>Buhar ve sıcak su tesisatı</li><li>Endüstriyel proses boru hatları</li><li>Petrokimya, kimya, gıda tesisleri</li><li>Yangın söndürme tesisat ağı</li><li>Hidrolik ve pnömatik hatlar</li></ul>',
  specs = '{"Standart":"EN 10253-1 / ANSI B16.9 / DIN 2605","Kalite":"P235GH · P265GH · 16Mo3 · 304/316 paslanmaz","Tip":"90° / 45° / 180°","Çap Aralığı":"1/2\\" – 24\\" (DN15 – DN600)","Et Kalınlığı":"Sch10 / Sch20 / Sch40 / Sch80 / Sch160","Tip":"Kaynaklı (BW) veya Dişli (Threaded)","Test":"Hidrostatik basınç testi","Sertifika":"3.1 / 3.2 (EN 10204)"}'
WHERE slug = 'patent-dirsek';

-- 18. NORM FLANŞ
UPDATE tm_products SET
  description = '<h3>Norm Flanş Nedir?</h3><p>Norm Flanş, boru ve cihaz bağlantılarında kullanılan, cıvatalı bağlantı için tasarlanmış disk şeklindeki çelik bağlantı elemanlarıdır. Söküle bilir bağlantı sağlar - bakım ve onarım gerektiren tesisatlar için vazgeçilmezdir.</p><h3>Flanş Tipleri</h3><ul><li><strong>Slip-On (SO):</strong> Boruya geçirilip kaynak yapılan en yaygın tip</li><li><strong>Welding Neck (WN):</strong> Alın kaynaklı, yüksek basınç</li><li><strong>Blind:</strong> Kapatma flanşı, hat sonu</li><li><strong>Lap Joint:</strong> Stub end ile birlikte, paslanmaz tasarrufu</li><li><strong>Threaded:</strong> Dişli, kaynaksız bağlantı</li><li><strong>Socket Weld:</strong> Soketli kaynak</li></ul><h3>Basınç Sınıfları</h3><p>PN6, PN10, PN16, PN25, PN40, PN64, PN100 (EN/DIN); 150#, 300#, 600#, 900#, 1500#, 2500# (ANSI).</p>',
  specs = '{"Standart":"EN 1092-1 / DIN 2576 / ANSI B16.5","Kalite":"P235GH · P265GH · 16Mo3 · 304/316L","Tip":"Slip-On · Welding Neck · Blind · Lap Joint · Threaded","Basınç Sınıfı (EN)":"PN6 / PN10 / PN16 / PN25 / PN40 / PN64 / PN100","Basınç Sınıfı (ANSI)":"150# / 300# / 600# / 900#","Çap Aralığı":"DN10 – DN1200 (1/2\\" – 48\\")","Yüzey Tipi":"Düz (FF) · Yükseltilmiş (RF) · Erkek-Dişi (M&F)","Sertifika":"3.1 / 3.2"}'
WHERE slug = 'norm-flans';

-- 19. PETEK KİRİŞ
UPDATE tm_products SET
  description = '<h3>Petek Kiriş Nedir?</h3><p>Petek kiriş (Castellated Beam), I veya H kesitli profilin gövdesi zigzag şeklinde kesilip yarısı kaydırılarak yeniden kaynaklanmasıyla elde edilen, peteklere benzer açıklıklı yüksek mukavemetli kirişlerdir. Aynı çelik miktarıyla %30-50 daha yüksek atalet momenti sağlar.</p><h3>Avantajları</h3><ul><li><strong>Yüksek Mukavemet:</strong> Aynı kg/m''de daha büyük taşıma kapasitesi</li><li><strong>Hafiflik:</strong> Geleneksel kirişe göre %30-50 daha hafif</li><li><strong>Tesisat Geçişi:</strong> Petek delikleri elektrik/havalandırma kanalı geçişine olanak</li><li><strong>Estetik:</strong> Mimari görünüm, açık tavan tasarımları</li><li><strong>Ekonomiklik:</strong> Daha az çelik = daha az maliyet</li></ul><h3>Kullanım Alanları</h3><ul><li>Geniş açıklıklı endüstriyel yapı çatıları</li><li>Spor salonu, depo, hangar yapıları</li><li>Açık tavanlı modern ofis tasarımı</li><li>Otopark, AVM, terminal binaları</li></ul>',
  specs = '{"Üretim":"Standart I/H profilden petek kesimi","Kalite":"S235JR · S275JR · S355JR","Açıklık":"6 m – 30 m arası serbest açıklık","Verim":"%30-50 mukavemet artışı (aynı kg/m)","Petek Şekli":"Altıgen, dairesel, oval","Kullanım":"Geniş açıklıklı çelik yapı","Standart":"EN 1993-1-13 (Eurocode 3 — petek kiriş eki)"}'
WHERE slug = 'petek-kiris';

-- 20. ÇATI PANELİ
UPDATE tm_products SET
  description = '<h3>Çatı Paneli Nedir?</h3><p>Sandviç çatı paneli, iki dış yüzey arasına yalıtım malzemesi (poliüretan veya taş yünü) yerleştirilerek üretilen, yüksek ısı ve ses yalıtımı sağlayan modern çatı kaplama ürünüdür. Endüstriyel yapı, depo, hangar, AVM ve soğuk hava deposu gibi uygulamaların vazgeçilmezidir.</p><h3>Yalıtım Malzemesi Türleri</h3><ul><li><strong>Poliüretan (PUR):</strong> Düşük λ değeri, yüksek yalıtım. 0,022 W/mK</li><li><strong>Polizosiyanurat (PIR):</strong> Yangın direnci yüksek (B-s2,d0)</li><li><strong>Taş Yünü:</strong> A1 yangın sınıfı, en güvenli. Endüstriyel</li><li><strong>EPS:</strong> Ekonomik, hafif. Sıcaklık aralığı sınırlı</li></ul><h3>Yüzey Kaplama</h3><p>Galvanizli + boyalı (RAL renkleri), alüminyum-çinko (alüzink), paslanmaz çelik. Kalınlık: 0,4 - 0,7 mm.</p><h3>Profil Tipleri</h3><p>5 oluk standart, 3 oluk geniş, gizli vidalı, kırma cam çatı paneli.</p>',
  specs = '{"Standart":"EN 14509","Yalıtım":"Poliüretan / PIR / Taş yünü / EPS","Yalıtım Kalınlığı":"30 / 40 / 50 / 60 / 80 / 100 / 120 / 150 mm","Sac Kalınlığı":"0,4 / 0,5 / 0,6 / 0,7 mm","Genişlik":"1000 mm (faydalı)","Boy":"İsteğe göre 2 – 14 metre","Renk":"RAL standart + özel","U Değeri":"0,16 - 0,75 W/m²K (kalınlığa göre)","Yangın Sınıfı":"B-s1,d0 / A2 / A1 (taş yünü)"}'
WHERE slug = 'cati-paneli';

-- 21. CEPHE PANELİ
UPDATE tm_products SET
  description = '<h3>Cephe Paneli Nedir?</h3><p>Cephe paneli, yapıların dış cephesini estetik ve fonksiyonel olarak kaplayan, içeride yalıtım sağlayan sandviç panellerdir. Çatı panelinden farkı yataya monte edilmesi ve genellikle daha sade profil tasarımına sahip olmasıdır. Modern ticari ve endüstriyel yapıların yaygın tercihidir.</p><h3>Profil Çeşitleri</h3><ul><li><strong>Yataya monte (mikro panel):</strong> 100-150 mm modüller</li><li><strong>Gizli vidalı:</strong> Vidalar görünmez, modern estetik</li><li><strong>Mikrohat profil:</strong> Hafif yivli yüzey</li><li><strong>Düz panel:</strong> Pürüzsüz minimal görünüm</li><li><strong>İğnecikli:</strong> Endüstriyel görünüm</li></ul><h3>Renk ve Yüzey</h3><p>RAL kataloğundan tüm renkler. Mat ve parlak yüzey. Ahşap görünümlü baskılı, taş görünümlü, granit baskılı özel desenler.</p>',
  specs = '{"Standart":"EN 14509","Yalıtım":"Poliüretan / PIR / Taş yünü","Yalıtım Kalınlığı":"40 - 200 mm","Genişlik":"1000 - 1100 mm","Boy":"2 - 14 m","Profil":"Yataya monte / Gizli vida / Düz / Mikrohat / İğnecikli","Yangın":"B-s1,d0 (PUR/PIR) · A1 (taş yünü)","Renk":"RAL + özel desenler"}'
WHERE slug = 'cephe-paneli';

-- 22. NERVÜRLÜ İNŞAAT DEMİRİ
UPDATE tm_products SET
  description = '<h3>Nervürlü İnşaat Demiri Nedir?</h3><p>Nervürlü inşaat demiri (Rebar), betonarme yapılarda donatı (armatür) olarak kullanılan, yüzeyinde nervür adı verilen yivler bulunan çelik çubuklardır. Yivler, beton ile çelik arasındaki aderansı (tutunmayı) artırarak betonarme yapının dayanımını sağlar.</p><h3>Türk Standardı: BÇIII-A / B500C</h3><p>Türkiye''de yapı güvenliği yönetmeliği gereği BÇIII-A (eski isim) / B500C (yeni isim) kalitesinde nervürlü demir kullanılır. Akma dayanımı 500 N/mm², minimum çekme dayanımı 540 N/mm² olmalıdır.</p><h3>Standart Çaplar</h3><p>Ø8, Ø10, Ø12, Ø14, Ø16, Ø18, Ø20, Ø22, Ø25, Ø28, Ø32, Ø40 mm. Standart boy 12 metredir; 6 metre veya istek üzerine 18 metre temin edilebilir.</p><h3>Kullanım Alanları</h3><ul><li>Betonarme bina kolonu, kirişi, döşemesi</li><li>Temel demiri (radye, sürekli temel)</li><li>Köprü, viyadük, su kanalı betonarme</li><li>Liman, deniz yapısı betonarme</li><li>Endüstriyel temel ve döşeme</li></ul>',
  specs = '{"Standart":"TS 708 (BÇIII-A) / TS 4559 / EN 10080 (B500C)","Akma Dayanımı":"≥ 500 N/mm²","Çekme Dayanımı":"≥ 540 N/mm²","Çekme/Akma Oranı":"≥ 1,15","Uzama (Agt)":"≥ %7,5","Çap":"Ø8 – Ø40 mm","Standart Boy":"12 metre (6 / 18 m de mümkün)","Nervür Tipi":"İki tarafı zigzag (eşit eğimli)","Süneklik":"C (yüksek)","Kaynaklanabilirlik":"İyi (özel elektrod ile)"}'
WHERE slug = 'nervurlu-demir';

-- 23. ÇELİK HASIR
UPDATE tm_products SET
  description = '<h3>Çelik Hasır Nedir?</h3><p>Çelik hasır, nervürlü inşaat demirlerinin elektrik kaynağı ile birbirine kaynak edilerek üretildiği, hazır donatı ızgarasıdır. Klasik demir donatı işçiliğine göre %50''ye varan zaman tasarrufu ve daha homojen donatı dağılımı sağlar.</p><h3>Q ve R Tipleri</h3><ul><li><strong>Q Tipi (Eşit Donatılı):</strong> Her iki yönde de eşit miktarda donatı. Şap, döşeme, yol kaplaması için ideal. Q106, Q131, Q188, Q295, Q335, Q524 vb.</li><li><strong>R Tipi (Tek Yön Donatılı):</strong> Bir yönde fazla donatı. Tek yönlü taşıyan döşemelerde ekonomik. R131, R188, R257, R317 vb.</li></ul><h3>Standart Levha Boyutu</h3><p>5,00 m × 2,15 m = 10,75 m². Sipariş üzerine özel ölçüler de üretilebilir.</p><h3>Kullanım Alanları</h3><ul><li>Betonarme döşeme ve şap donatısı</li><li>Yol ve havaalanı pisti betonarme</li><li>Endüstriyel zemin ve depo döşemesi</li><li>Köprü tabliyesi donatısı</li><li>Prefabrik beton elemanlar</li><li>Çevre ve istinat duvarları</li></ul>',
  specs = '{"Standart":"TS 4559 / EN 10080","Donatı Kalitesi":"B500C (BÇIII-A)","Levha Boyutu":"5,00 × 2,15 m (10,75 m²)","Q Tipleri":"Q106 (1,69 kg/m²) - Q758 (11,83 kg/m²)","R Tipleri":"R131 (1,91 kg/m²) - R424 (6,16 kg/m²)","Çap Aralığı":"Ø4,5 - Ø12 mm","Göz Açıklığı":"100×100 / 150×150 / 200×200 mm","Kaynak":"Elektrik direnç kaynağı","Kaynak Sınıfı":"Sınıf C (yüksek mukavemet)"}'
WHERE slug = 'celik-hasir';

-- 24. OSB LEVHA
UPDATE tm_products SET
  description = '<h3>OSB Levha Nedir?</h3><p>OSB (Oriented Strand Board - Yönlendirilmiş Yonga Levha), büyük ahşap yongaların belirli yönlerde yerleştirilip su geçirmez reçineler ile yüksek basınç altında preslenmesi ile üretilen yapı malzemesidir. Klasik suntalar ve kontrplaklara göre daha mukavemetli, daha ekonomik ve daha çevrecidir.</p><h3>OSB Sınıfları</h3><ul><li><strong>OSB-2:</strong> İç mekan, kuru ortam taşıyıcı uygulamalar</li><li><strong>OSB-3:</strong> Nemli ortam, taşıyıcı (en yaygın)</li><li><strong>OSB-4:</strong> Yüksek nem, ağır taşıyıcı</li></ul><h3>Yangın Sınıfı</h3><p>Standart OSB-3: D-s1,d0. Yangın geciktirici işlemli OSB: B-s1,d0. EN 13501-1 standartına göre.</p><h3>Kullanım Alanları</h3><ul><li>Çatı kaplaması (su yalıtım altlığı)</li><li>Cephe altyapı paneli</li><li>İç mekan duvar bölme paneli</li><li>Mobilya iskelet ve sırt</li><li>Ambalaj kasası ve palet imalatı</li><li>Sergi standı ve geçici yapılar</li></ul>',
  specs = '{"Standart":"EN 300 / EN 13501-1","Sınıflar":"OSB-2 / OSB-3 / OSB-4","Kalınlık":"6 / 8 / 10 / 12 / 15 / 18 / 22 / 25 mm","Boyut":"125×250 cm (standart)","Yoğunluk":"550 - 650 kg/m³","Reçine":"Su geçirmez fenolik (PMDI)","Yangın Sınıfı":"D-s1,d0 (standart) / B-s1,d0 (yangın geciktirici)","Eğme Mukavemeti":"≥ 22 N/mm² (uzun yön)","Çevre":"E1 emisyon (formaldehit ≤ %0,1)"}'
WHERE slug = 'osb-levha';


-- =====================================================
-- v1.0.38 — "Yetkili temsilci" → "Tedarik ortağı" terminolojisi
-- =====================================================

UPDATE tm_settings 
SET setting_value = REPLACE(REPLACE(REPLACE(setting_value, 'yetkili temsilciliği', 'doğrudan tedariği'), 'yetkili temsilci', 'tedarik ortağı'), 'Yetkili Temsilci', 'Tedarik Ortağı')
WHERE setting_key IN ('site_description', 'site_about', 'company_description', 'about_short')
  AND setting_value LIKE '%temsilci%';


-- =====================================================
-- v1.0.38 — SSS: Metal & Çelik konulu kapsamlı sorular
-- =====================================================

-- Önce tm_faq tablosundaki eski 'genel' kategorisini sil (varsa)
DELETE FROM tm_faq WHERE category IN ('genel', 'metal', 'celik', 'tedarik', 'sevkiyat', 'odeme', 'hesaplama', 'islem');

-- TEDARİK & SİPARİŞ
INSERT IGNORE INTO tm_faq (category, question, answer, sort_order, is_active) VALUES
('tedarik', 'Sipariş minimum tonajınız var mı?', 'Hayır, kesin bir minimum tonaj yok. Tek levha sac, birkaç metre profil veya birkaç adet boru gibi küçük sipariş kalemlerinizi de karşılıyoruz. Yine de toplu sipariş veriyorsanız fiyat avantajı sağlayabiliriz; toplu siparişlerde stoğa indirim uygulanabilir.', 10, 1),
('tedarik', 'Stoklu olmayan ürün için tedarik süresi nedir?', 'Stoğumuzda olmayan standart ürünler için, üretici partnerlerimizden tedarik süresi genellikle 24-72 saat arasındadır. Özel ölçü, özel kalite veya nadir aranan ürünler 5-10 iş günü içinde tedarik edilebilir. Sipariş öncesi mutlaka süre teyidi alın.', 20, 1),
('tedarik', 'Üretici sertifikası ve menşei belgesi alabilir miyim?', 'Evet. Tüm ürünlerimiz, üretici fabrikadan menşei belgesi (Mill Test Certificate / 3.1 sertifika), kalite belgesi ve test raporları ile birlikte tedarik edilir. Sipariş sırasında talep ederseniz belgeleri sevkiyat öncesi e-posta ile iletiriz.', 30, 1),
('tedarik', 'Teklif alma süresi ne kadar?', 'Standart ürünler için aynı gün, çoğu zaman 1-2 saat içinde teklif sunuyoruz. Çok sayıda kalem içeren projeler veya özel ölçü/kalite gerektiren talepler için en geç 24 saat içinde detaylı teklif iletiyoruz. WhatsApp veya iletişim formundan ulaşabilirsiniz.', 40, 1);

-- SEVKİYAT & TESLİMAT
INSERT IGNORE INTO tm_faq (category, question, answer, sort_order, is_active) VALUES
('sevkiyat', 'Hangi illere sevkiyat yapıyorsunuz?', 'Türkiye''nin 81 iline sevkiyat yapıyoruz. Konya merkezimizden yola çıkan kamyonlarımız ve anlaşmalı nakliyat firmalarımızla, ülke genelinde aynı hafta içinde teslimat sağlıyoruz. Konya il merkezi ve yakın çevreye genellikle 24 saat içinde teslim ederiz.', 10, 1),
('sevkiyat', 'Nakliye ücreti ürün fiyatına dahil mi?', 'Genel kural: Konya il merkezi içinde belirli sipariş tutarı üzerinde ücretsiz teslimat sağlanır. İl dışı sevkiyatlarda nakliye ücreti tonaj, mesafe ve araç tipine göre teklifimize ayrı kalem olarak eklenir. Maliyetin önceden bilinmesi için teklif aşamasında nakliye dahil fiyat isteyebilirsiniz.', 20, 1),
('sevkiyat', 'Ürünleri kendim teslim alabilir miyim?', 'Tabii ki. Karatay/Konya''daki merkezimize gelip ürünlerinizi araç veya konteyner ile teslim alabilirsiniz. Adres: Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS — Karatay/Konya. Yükleme için forklift ve vinç hizmetimiz mevcuttur.', 30, 1),
('sevkiyat', 'Sevkiyat süresi ne kadar olur?', 'Konya il içi: 24 saat. Konya çevre iller (Aksaray, Karaman, Niğde): 1-2 gün. İç Anadolu: 2-3 gün. Marmara, Ege, Akdeniz: 3-4 gün. Doğu ve Güneydoğu illeri: 4-6 gün. Süre, kullanılan araç tipi ve kargo doluluğuna göre değişebilir.', 40, 1);

-- METAL & ÇELİK BİLGİSİ
INSERT IGNORE INTO tm_faq (category, question, answer, sort_order, is_active) VALUES
('metal', 'Demir ile çelik arasındaki fark nedir?', 'Demir, doğada saf halde nadir bulunan bir element (Fe). Çelik ise demirin %0,02-2,1 oranında karbon ile alaşımlanmasıyla elde edilen, çok daha mukavim ve dayanıklı bir malzemedir. Yapı sektöründe ve sanayide kullanılan ''demir'' ürünlerin neredeyse tamamı aslında düşük karbonlu çeliktir. Çeliğe ek olarak krom, nikel, mangan, molibden eklenerek özel özelliklere sahip alaşımlar oluşturulur.', 10, 1),
('metal', 'Sıcak haddelenmiş (HRP) ile soğuk haddelenmiş (DKP) sac arasındaki fark nedir?', 'Sıcak haddeleme (HRP/Hot Rolled), çelik 1700°C üzerinde haddelenir; ekonomiktir ve geniş kalınlık aralığı sağlar (1,5-25 mm). Yüzeyi pürüzlü ve oksitlidir, hassas tolerans gerektirmeyen yapı ve makine imalatında kullanılır. Soğuk haddeleme (DKP/Cold Rolled), HRP sacın oda sıcaklığında daha ince bir kalınlığa indirgenmesiyle üretilir; yüzeyi pürüzsüz ve parlak, toleransı dar (±0,05 mm), şekillendirilebilirliği mükemmeldir. Beyaz eşya, otomotiv kaport ve hassas iş için tercih edilir.', 20, 1),
('metal', 'Galvanizli sac kaç yıl dayanır?', 'Galvanizli sacın ömrü kullanım ortamına bağlıdır. Z100 kaplama (100 g/m²) iç mekanda 20+ yıl, dış mekanda 5-10 yıl; Z275 kaplama (275 g/m²) dış mekanda 15-25 yıl; Z350 endüstriyel ortamda 30+ yıl ömür sunar. Deniz kıyısı ve kimyasal ortamda ömür yaklaşık yarıya düşer; bu durumda paslanmaz çelik tercih edilmelidir.', 30, 1),
('metal', 'ST37, ST44, ST52 ne anlama gelir?', 'Bu eski Alman DIN 17100 standardına göre yapı çeliği isimlendirmesidir. Sayılar minimum çekme dayanımını N/mm² (MPa) cinsinden ifade eder. ST37 ≈ 360 N/mm² çekme (yeni adı S235JR, en yaygın yapı çeliği); ST44 ≈ 430 N/mm² (S275JR); ST52 ≈ 510 N/mm² (S355JR, yüksek mukavemetli yapı çeliği). Modern Avrupa standardı (EN 10025) S235/S275/S355 isimlendirmesini kullanır; sayı akma dayanımını N/mm² gösterir.', 40, 1),
('metal', '304 ile 316 paslanmaz çelik arasındaki fark nedir?', '304 paslanmaz: 18% krom + 8% nikel içerir. Genel amaçlı, mutfak ekipmanı, mimari uygulamalar, gıda endüstrisi için uygundur. 316 paslanmaz: 304''e ek olarak 2-3% molibden içerir. Klorür, asit ve deniz suyuna karşı çok daha dayanıklıdır. Tıbbi ekipman, deniz uygulamaları, kimyasal endüstri ve sahil bölgeleri için tercih edilir. 316, 304''ten yaklaşık %30 daha pahalıdır.', 50, 1),
('metal', 'HEA, HEB, IPE profilleri arasındaki fark nedir?', 'Hepsi geniş başlıklı I kesitli yapı profilleridir, farklılık ağırlık ve etlik kalınlığında. **HEA (Light)**: en hafif tip, başlık genişliği yüksekliğine eşit (HEA200 = 200 mm hem yükseklik hem başlık), etleri ince. Kolon ve uzun açıklıklı kirişlerde ekonomik. **HEB (Standard)**: HEA ile aynı boyutlarda ama etleri kalın, daha mukavim, ağır taşıyıcı kolonlar için. **IPE**: dar başlıklı I kesit, başlık genişliği yüksekliğin yarısı. Kiriş tasarımları için optimize edilmiş, eğilme momentine karşı verimli.', 60, 1),
('metal', 'Boru ile profil arasındaki fark nedir?', 'Boru: yuvarlak kesitli içi boş çelik elemandır (Ø21,3-273 mm). Akışkan taşıma (su, gaz, buhar) ve dairesel yüke maruz konstrüksiyon uygulamalarında kullanılır. Profil (kutu profil): kare veya dikdörtgen kesitli içi boş çelik elemandır. Yapısal taşıyıcılık ve dekoratif uygulamalar için tasarlanmıştır; aynı kg/m''de yuvarlak boruya göre daha fazla atalet momenti sağlar. Boru basınca, profil ise yapısal yüke optimize edilmiştir.', 70, 1),
('metal', 'Nervürlü demir ile düz demir arasındaki fark nedir?', 'Düz demir (yuvarlak siyah çelik): yüzeyi pürüzsüz, dövme demir, mekanik mil ve genel imalat için kullanılır. Nervürlü demir (rebar): yüzeyinde ''nervür'' adı verilen yivler bulunur. Bu yivler beton ile çelik arasındaki aderansı (tutunmayı) artırır — betonarme yapılarda donatı olarak ZORUNLU kullanılır. Türkiye''de yapı yönetmeliği BÇIII-A (yeni adı B500C) sınıfında nervürlü demir kullanımını şart koşar. Akma dayanımı 500 N/mm², minimum 7,5% uzama gerekir.', 80, 1),
('metal', 'Lazer kesim ile oksijen kesim arasındaki fark nedir?', 'Lazer kesim: 0,5-25 mm kalınlık aralığı, ±0,1 mm hassasiyet, mükemmel kenar kalitesi, dar kesik genişliği (kerf). İnce ve hassas iş için ideal — endüstriyel komponent, dekoratif sac, prototip imalatı. Oksijen (CNC plazma/oxy-fuel) kesim: 5-200 mm kalınlık, ±1 mm hassasiyet, hızlı ve ekonomik kalın levha kesimi için tercih edilir. Endüstriyel proje, ağır levha plaka açma, gemi-tank imalatı uygundur. Lazer ince işin kralı, oksijen kalın işin sürat motoru.', 90, 1);

-- HESAPLAMA & TEKNİK
INSERT IGNORE INTO tm_faq (category, question, answer, sort_order, is_active) VALUES
('hesaplama', 'Sac ağırlığını nasıl hesaplarım?', 'Sac ağırlık formülü: Ağırlık (kg) = Kalınlık (cm) × En (cm) × Boy (cm) × Yoğunluk (g/cm³) ÷ 1.000. Çelik için yoğunluk 7,85 g/cm³. Örnek: 3 mm × 1.500 mm × 3.000 mm sac = 0,3 × 150 × 300 × 7,85 / 1.000 = 105,98 kg. Sitemizdeki <a href=\"/hesaplama.php\">Ağırlık Hesaplama</a> aracı ile 14 farklı ürün grubu için anında hesaplama yapabilirsiniz.', 10, 1),
('hesaplama', 'Kutu profil ağırlığını nasıl hesaplarım?', 'Kare/dikdörtgen kutu profil için formül: Ağırlık (kg/m) = (A·B − (A−2t)·(B−2t)) × 7,85 / 1.000. Burada A ve B mm cinsinden kenarlar, t et kalınlığı. Örnek: 40×40×2 mm kutu profilin metre ağırlığı = (40×40 − (40-4)×(40-4)) × 7,85 / 1.000 = 304 × 7,85 / 1.000 = 2,39 kg/m. 6 metre profil = 14,33 kg. Hesaplama sayfamızda otomatik hesaplayabilirsiniz.', 20, 1),
('hesaplama', 'Çelik hasırın ağırlığını nasıl hesaplarım?', 'Standart çelik hasır levha boyutu 5,00 m × 2,15 m = 10,75 m². Ağırlık = Hasır tipinin kg/m² değeri × 10,75. Örnek: Q335 hasır 5,24 kg/m². Bir levha = 5,24 × 10,75 = 56,33 kg. Sitemizdeki hesaplama aracında 21 farklı Q ve R tipi hasır seçeneği mevcuttur — tip seçince ağırlık otomatik hesaplanır.', 30, 1),
('hesaplama', 'HEA/HEB/IPE profil ağırlığı nasıl bulunur?', 'I/H kesitli profillerde geometrik formül yerine **standart kg/m tablosu** kullanılır. Örnek: HEA 200 = 42,3 kg/m, HEB 300 = 117 kg/m, IPE 300 = 42,2 kg/m. 6 metre HEA 200 = 6 × 42,3 = 253,8 kg. Sitemizdeki hesaplama aracı tüm standart HEA/HEB/IPE/IPN ölçülerini ve kg/m değerlerini içerir; profil seçince otomatik hesaplar.', 40, 1);

-- ÖDEME & FATURA
INSERT IGNORE INTO tm_faq (category, question, answer, sort_order, is_active) VALUES
('odeme', 'Hangi ödeme yöntemlerini kabul ediyorsunuz?', 'Banka havalesi/EFT (TL, USD, EUR), kredi kartı (Visa, MasterCard, AmEx), nakit ödeme (limitli) ve KVK çek kabul ediyoruz. Kurumsal müşteriler için fatura tarihinden itibaren 30-60 gün vadeli açık hesap çalışma imkânı (referans onayı sonrası) sunuyoruz. Ödeme detayları teklifimizde belirtilir.', 10, 1),
('odeme', 'Kurumsal fatura kesilebilir mi?', 'Evet. Tüm satışlarımız resmi fatura ile yapılır. Kurumsal müşterilerimize e-Fatura ve e-Arşiv Fatura keserek elektronik ortamda iletiyoruz. Şahıs alımları için de e-Arşiv Fatura veya kâğıt fatura tercihinize göre düzenleyebiliriz.', 20, 1),
('odeme', 'KDV oranı nedir?', 'Demir-çelik ürünleri %20 KDV oranına tabidir. Tüm fiyatlarımız KDV hariç olarak verilir; sipariş onayında ve fatura aşamasında KDV eklenir. İhracat satışlarında KDV istisnası uygulanır.', 30, 1),
('odeme', 'İade ve değişim politikanız nedir?', 'Üretici hatası, kalite uyumsuzluğu veya yanlış sevkiyat durumunda 7 gün içinde iade veya değişim yapıyoruz. Fatura, sertifika ve ürün ambalajının korunmuş olması gerekir. Müşteri kaynaklı iade taleplerinde (yanlış sipariş, fikir değişikliği) ürünün stokta tekrar değerlendirilebilir olması ve nakliye ücretinin müşteriye ait olması koşuluyla değerlendirilir.', 40, 1);

-- ATÖLYE HİZMETLERİ
INSERT IGNORE INTO tm_faq (category, question, answer, sort_order, is_active) VALUES
('islem', 'DXF/DWG dosyamı gönderip lazer kesim yaptırabilir miyim?', 'Evet. DXF, DWG, STEP ve PDF dosyalarınızı kabul ediyoruz. CAM yazılımımız ile dosyanızı analiz eder, kesim yolunu optimize eder ve aynı gün size kesin teklif sunarız. Karmaşık geometriler için 3D modelleme desteği ve mühendislik danışmanlığı da sunuyoruz.', 10, 1),
('islem', 'Maksimum kesim ölçüleri nelerdir?', 'Lazer kesim: 1500×3000 mm tabla, 0,5-25 mm sac kalınlığı (sertlik ve cinse göre değişir). Oksijen kesim: 3000×6000 mm tabla, 5-200 mm levha kalınlığı. Daha büyük levhalar için ek bedel ile özel sevkiyat ve kesim hizmeti sağlayabiliriz.', 20, 1),
('islem', 'Kesim toleransı nedir?', 'Lazer kesim toleransı ±0,1 mm''dir; çoğu hassas mekanik parça için yeterli olan bir hassasiyet. Oksijen kesim toleransı ±1 mm''dir; ısı etkili bölgenin (HAZ) doğal sonucudur. Daha hassas tolerans gerektiren parçalar için lazer kesimi tercih edilmelidir.', 30, 1),
('islem', 'Aynı gün kesim hizmeti var mı?', 'Sabah 09:00''a kadar onaylanmış DXF dosyaları için aynı gün kesim ve teslim hizmetimiz mevcuttur (parça sayısı ve makine yoğunluğuna göre). Acil projeler için özel slot ayırabiliriz; lütfen iletişime geçerken aciliyeti belirtin.', 40, 1);


-- =====================================================
-- v1.0.39 — SEO Modülü: İller, İl-Ürün, Ülkeler
-- =====================================================

-- 25 anahtar Türkiye ili (sanayi/inşaat yoğun)
CREATE TABLE IF NOT EXISTS tm_seo_iller (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  name VARCHAR(80) NOT NULL,
  region VARCHAR(80) NULL COMMENT 'İç Anadolu, Marmara vs',
  population VARCHAR(40) NULL,
  industry_focus TEXT NULL COMMENT 'Sanayi odağı kısa metni',
  intro_text LONGTEXT NULL COMMENT 'Sayfa giriş metni (benzersiz)',
  cargo_info VARCHAR(255) NULL COMMENT 'Sevkiyat süresi/bilgisi',
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4 ülke için ihracat sayfası
CREATE TABLE IF NOT EXISTS tm_seo_ulkeler (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  name VARCHAR(80) NOT NULL,
  capital VARCHAR(80) NULL,
  population VARCHAR(40) NULL,
  border_distance VARCHAR(80) NULL COMMENT 'Türkiye sınırı veya mesafe',
  trade_volume VARCHAR(255) NULL,
  intro_text LONGTEXT NULL,
  cargo_info VARCHAR(500) NULL,
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İl × Ürün matrisi için template ek bilgi (opsiyonel, default fallback var)
CREATE TABLE IF NOT EXISTS tm_seo_il_urun (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  il_slug VARCHAR(80) NOT NULL,
  urun_slug VARCHAR(80) NOT NULL,
  custom_intro TEXT NULL COMMENT 'Bu kombinasyon için özel intro (opsiyonel)',
  custom_meta_desc VARCHAR(300) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_combo (il_slug, urun_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 25 anahtar il (sanayi yoğun, alfabetik+ekonomik öncelik)
INSERT IGNORE INTO tm_seo_iller (slug, name, region, population, industry_focus, intro_text, cargo_info, sort_order) VALUES
('istanbul', 'İstanbul', 'Marmara', '15.6 milyon', 'Türkiye''nin ekonomik başkenti, lojistik ve inşaat merkezi',
 'İstanbul, Türkiye''nin en büyük inşaat ve sanayi pazarı. Tekcan Metal olarak İstanbul''daki müteahhitler, çelik konstrüksiyon firmaları ve OEM üreticilere demir-çelik ürün tedariği sağlıyoruz. Hadımköy, İkitelli, Tuzla, Gebze sanayi bölgelerine düzenli sevkiyat yapıyoruz.',
 'İstanbul: 2-3 iş günü içinde teslimat, 30 ton üzeri sevkiyatlarda nakliye dahil teklif.', 1),

('ankara', 'Ankara', 'İç Anadolu', '5.8 milyon', 'Başkent, OSTİM ve İvedik OSB ile makine imalatı merkezi',
 'Ankara, başta OSTİM ve İvedik Organize Sanayi Bölgeleri olmak üzere Türkiye''nin makine imalatı ve savunma sanayi merkezidir. Tekcan Metal olarak Ankara''daki makine imalatçılarına, kalıpçılara ve metal işleme atölyelerine sac, profil, mil ve özel ölçü tedariği yapıyoruz.',
 'Ankara: 1-2 iş günü içinde teslimat. Konya-Ankara arası direkt sevkiyat hattı.', 2),

('izmir', 'İzmir', 'Ege', '4.4 milyon', 'Liman, ihracat ve gemi inşaatı sanayi bölgeleri',
 'İzmir, Ege Bölgesi''nin sanayi ve ihracat merkezi. Aliağa demir-çelik üretim tesisleri, Bornova ve Çiğli sanayi bölgeleri için demir-çelik tedariği sunuyoruz. Tekstil makineleri, gıda işleme ve gemi inşaat sektörlerine özel ölçü tedarik hizmetimiz vardır.',
 'İzmir: 2-3 iş günü içinde teslimat, Aliağa-Bornova-Çiğli direkt rota.', 3),

('bursa', 'Bursa', 'Marmara', '3.2 milyon', 'Otomotiv yan sanayi başkenti, tekstil makinası imalatı',
 'Bursa, Türkiye''nin otomotiv başkentidir. Renault, Tofaş, OYAK ve yan sanayi tedarikçileri için DKP sac, kalıp çeliği ve hassas ölçülü ürün tedariği yapıyoruz. Ayrıca Demirtaş ve Nilüfer OSB''deki tekstil makinası üreticilerine düzenli sevkiyat sağlıyoruz.',
 'Bursa: 2 iş günü içinde teslimat, otomotiv sektörü için JIT sevkiyat opsiyonu.', 4),

('konya', 'Konya', 'İç Anadolu', '2.3 milyon', 'Tekcan Metal merkezi - tarım makineleri, döküm sanayi',
 'Konya, Tekcan Metal''in 2005''ten beri faaliyet gösterdiği merkez şehrimiz. Karatay, Selçuklu ve Meram OSB''lerindeki tarım makineleri üreticileri, döküm sanayicileri ve metal işleme atölyelerine aynı gün teslim hizmetimiz mevcut. Konya merkez stoğumuzdan 81 ile sevkiyat yapıyoruz.',
 'Konya: Aynı gün teslim, il merkezinde belirli tutar üzeri ücretsiz sevkiyat.', 5),

('antalya', 'Antalya', 'Akdeniz', '2.7 milyon', 'Turizm yapıları, tarım sera ve yat inşaat sanayi',
 'Antalya, otel ve turizm yapıları, sera tarımı ve yat inşaat sektörü için demir-çelik tedariği yaptığımız önemli pazarlardan biri. Sera profilleri, paslanmaz çelik mutfak ekipmanı sacı ve otel inşaatı yapı çelikleri için stok desteği sunuyoruz.',
 'Antalya: 3-4 iş günü içinde teslimat. Antalya OSB ve sahil otelleri.', 6),

('gaziantep', 'Gaziantep', 'Güneydoğu', '2.1 milyon', 'Sanayi güneyin başkenti, makine imalatı ve tekstil',
 'Gaziantep, Güneydoğu''nun sanayi başkenti olarak Türkiye''nin önde gelen makine imalatı ve halı sanayi merkezlerinden biridir. Gaziantep OSB''deki üreticilere sac, profil ve özel kalite çelik tedariği sağlıyoruz.',
 'Gaziantep: 3-4 iş günü içinde teslimat. Direkt yük seferi mevcut.', 7),

('kayseri', 'Kayseri', 'İç Anadolu', '1.4 milyon', 'Mobilya ve metal işleme sanayi',
 'Kayseri OSB, Türkiye''nin önde gelen mobilya ve metal işleme sanayi merkezlerindendir. Kayseri''deki çelik mobilya üreticileri, kapı imalatçıları ve metal aksesuar firmalarına sac ve profil tedariği sağlıyoruz. Konya-Kayseri arası 350 km — direkt sevkiyat hattımız mevcut.',
 'Kayseri: 1-2 iş günü içinde teslimat, direkt sevkiyat.', 8),

('adana', 'Adana', 'Akdeniz', '2.2 milyon', 'Tarım, gıda işleme ve tekstil sanayi',
 'Adana ve çevresindeki tarım makineleri, gıda işleme ve tekstil endüstrisi için demir-çelik tedarik desteği veriyoruz. Adana OSB ve Yumurtalık serbest bölge müşterilerimize aynı hafta sevkiyat sağlıyoruz.',
 'Adana: 3-4 iş günü içinde teslimat.', 9),

('mersin', 'Mersin', 'Akdeniz', '1.9 milyon', 'Liman, lojistik ve serbest bölge',
 'Mersin Limanı ve serbest bölgesi, Türkiye''nin Doğu Akdeniz ihracat kapısıdır. İhracat odaklı üreticilere ve liman yapıları için yapı çeliği tedariği sunuyoruz.',
 'Mersin: 3-4 iş günü içinde teslimat. Liman bölgesine direkt sevkiyat.', 10),

('eskisehir', 'Eskişehir', 'İç Anadolu', '900K', 'Beyaz eşya ve havacılık sanayi',
 'Eskişehir, Arçelik, TUSAŞ ve Anadolu Üniversitesi yan sanayi nedeniyle teknoloji odaklı üretim merkezidir. Beyaz eşya gövde sacı, havacılık alaşımlı parçalar için DKP ve özel kalite sac tedariği sağlıyoruz.',
 'Eskişehir: 2 iş günü içinde teslimat.', 11),

('kocaeli', 'Kocaeli', 'Marmara', '2.1 milyon', 'Petrokimya, otomotiv ve tersane',
 'Kocaeli (İzmit), Türkiye''nin petrokimya ve otomotiv ana sanayi merkezidir. TÜPRAŞ, Hyundai, Honda fabrikaları ve Gebze-Dilovası OSB''lere demir-çelik tedariği sunuyoruz. Petrokimya boru hattı, otomotiv kaport sacı için özel kalite stoğumuz mevcut.',
 'Kocaeli: 2-3 iş günü içinde teslimat. Gebze-Dilovası direkt sevkiyat.', 12),

('sakarya', 'Sakarya', 'Marmara', '1.1 milyon', 'Otomotiv yan sanayi (Toyota), demiryolu araçları',
 'Sakarya, Toyota Otomotiv Fabrikası ve TÜVASAŞ (Türkiye Vagon Sanayii) bulundurur. Otomotiv ve raylı sistem yan sanayi tedariğine yönelik DKP sac ve yapısal çelik desteği sağlıyoruz.',
 'Sakarya: 2-3 iş günü içinde teslimat.', 13),

('manisa', 'Manisa', 'Ege', '1.5 milyon', 'Beyaz eşya ve elektronik (Vestel)',
 'Manisa OSB, Vestel başta olmak üzere Türkiye''nin önde gelen beyaz eşya ve elektronik üretim merkezlerindendir. DKP sac, ince kalınlıklı galvaniz ve özel kalite sac tedariği yapıyoruz.',
 'Manisa: 2-3 iş günü içinde teslimat. İzmir-Manisa direkt rota.', 14),

('tekirdag', 'Tekirdağ', 'Marmara', '1.1 milyon', 'Çorlu Sanayi - tekstil ve gıda',
 'Tekirdağ Çorlu, tekstil ve gıda sanayi yoğun bir bölgedir. Tekstil makineleri, gıda işleme ekipmanları için sac ve profil tedariği yapıyoruz.',
 'Tekirdağ: 3 iş günü içinde teslimat.', 15),

('balikesir', 'Balıkesir', 'Marmara', '1.2 milyon', 'Tarım makineleri, gıda işleme',
 'Balıkesir ve çevresinde tarım makineleri ve gıda işleme sanayi yoğundur. Bandırma ve merkez sanayicilerine sac ve profil sevkiyatı sağlıyoruz.',
 'Balıkesir: 3 iş günü içinde teslimat.', 16),

('hatay', 'Hatay', 'Akdeniz', '1.6 milyon', 'İskenderun demir-çelik, liman',
 'Hatay İskenderun, Türkiye''nin entegre demir-çelik üretim merkezlerindendir (İSDEMİR). Bölgedeki yan sanayicilere ve İskenderun limanı yapı projelerine destek sağlıyoruz.',
 'Hatay: 4 iş günü içinde teslimat.', 17),

('diyarbakir', 'Diyarbakır', 'Güneydoğu', '1.8 milyon', 'Bölgesel inşaat ve tarım merkezi',
 'Diyarbakır, Güneydoğu Anadolu''nun bölgesel ticaret ve inşaat merkezidir. İl ve çevre illerdeki müteahhitler ve tarım makineleri üreticilerine demir-çelik tedariği sunuyoruz.',
 'Diyarbakır: 4-5 iş günü içinde teslimat.', 18),

('samsun', 'Samsun', 'Karadeniz', '1.4 milyon', 'Liman, gıda işleme ve gemi inşaatı',
 'Samsun, Karadeniz''in en büyük limanı ve sanayi merkezi. Liman yapıları, gemi inşaat tersaneleri ve gıda işleme tesisleri için yapı çeliği ve sac tedariği sağlıyoruz.',
 'Samsun: 4 iş günü içinde teslimat.', 19),

('trabzon', 'Trabzon', 'Karadeniz', '810K', 'Liman, balıkçılık ekipmanı, çay sanayi',
 'Trabzon ve Doğu Karadeniz bölgesindeki çay fabrikaları, balıkçılık tesisleri ve liman yapıları için demir-çelik desteği sunuyoruz.',
 'Trabzon: 5 iş günü içinde teslimat.', 20),

('konya-ereglisi', 'Konya Ereğlisi', 'İç Anadolu', '143K', 'Otomotiv yan sanayi (Türk Demir Döküm)',
 'Konya Ereğli, KARDEMİR komşusu ve Türkiye''nin önemli demir-çelik tüketim merkezidir. Otomotiv ve makine imalatı yan sanayicilerine destek sağlıyoruz.',
 'Konya Ereğli: 1 iş günü içinde teslimat.', 21),

('aksaray', 'Aksaray', 'İç Anadolu', '430K', 'Mercedes-Benz Türk fabrikası, yan sanayi',
 'Aksaray''daki Mercedes-Benz Türk fabrikası ve yan sanayicilerine otomotiv kalitesi DKP sac ve yapısal çelik tedariği yapıyoruz.',
 'Aksaray: 1 iş günü içinde teslimat. Konya-Aksaray direkt hat.', 22),

('karaman', 'Karaman', 'İç Anadolu', '260K', 'Gıda sanayi, tarım makineleri',
 'Karaman OSB, gıda işleme ve tarım makineleri yoğunluklu bir merkezdir. Bisküvi, makarna ve süt ürünleri üreticilerinin paslanmaz çelik ihtiyaçlarına destek sağlıyoruz.',
 'Karaman: Aynı gün veya 1 iş günü.', 23),

('nigde', 'Niğde', 'İç Anadolu', '370K', 'Tarım, mermer ve madencilik',
 'Niğde, mermer ve madencilik sektörü ile öne çıkar. Mermer fabrikaları ve madencilik ekipmanı üreticilerinin yapı çeliği ihtiyaçlarına destek veriyoruz.',
 'Niğde: 1-2 iş günü içinde teslimat.', 24),

('afyonkarahisar', 'Afyonkarahisar', 'Ege', '740K', 'Mermer, gıda ve tarım sanayi',
 'Afyon, Türkiye''nin mermer başkenti. Mermer ocakları ve fabrikalarındaki ekipman üretimi, gıda sanayi yan sanayicilerine destek sağlıyoruz.',
 'Afyonkarahisar: 2 iş günü içinde teslimat.', 25);

-- 4 komşu ülke
INSERT IGNORE INTO tm_seo_ulkeler (slug, name, capital, population, border_distance, trade_volume, intro_text, cargo_info, sort_order) VALUES
('irak', 'Irak', 'Bağdat', '43 milyon', 'Habur Sınır Kapısı (Şırnak-Cizre)', 'Türkiye-Irak ticaret hacmi: $20+ milyar',
 'Irak, Türkiye''nin en büyük ihracat partnerlerinden biridir. Habur Sınır Kapısı üzerinden Erbil, Süleymaniye, Bağdat, Musul ve Basra''ya yapı çeliği, demir-çelik ürün sevkiyatı sağlıyoruz. Irak Kürt Bölgesel Yönetimi (Erbil) müteahhitleriyle çalışmamız bulunmaktadır.',
 'Irak: 7-10 iş günü içinde teslimat (Habur sınır kapısı + iç bölge dağıtımı). Gümrük belge desteği dahil.', 1),

('suriye', 'Suriye', 'Şam', '21 milyon', 'Cilvegözü, Bab-el Hava sınır kapıları', 'Türkiye-Suriye sınır ticaretinin yeniden hareketlendiği bölge',
 'Suriye, özellikle kuzey bölgeleri (Halep, İdlib) için yeniden inşaat süreçleri kapsamında demir-çelik tedariği yapıyoruz. Cilvegözü ve Bab-el Hava sınır kapıları üzerinden sevkiyat sağlanmaktadır. Bölgesel projeler için resmi makamlarla koordineli çalışıyoruz.',
 'Suriye: 7-14 iş günü içinde teslimat (sınır geçiş süresine göre). Resmi belgelerle koordineli sevkiyat.', 2),

('azerbaycan', 'Azerbaycan', 'Bakü', '10 milyon', 'Sarp-Hopa sınır kapısı (Gürcistan üzerinden)',
 'Türkiye-Azerbaycan stratejik ortaklığı, $7+ milyar ticaret',
 'Azerbaycan, Türkiye''nin kardeş ülkesi ve stratejik ticaret partneri. Bakü, Gence ve Sumgayıt''taki inşaat projeleri ve sanayi kuruluşlarına demir-çelik tedariği sağlıyoruz. Sarp Sınır Kapısı (Gürcistan üzerinden) sevkiyat hattımız aktif.',
 'Azerbaycan: 10-14 iş günü içinde teslimat (Türkiye-Gürcistan-Azerbaycan kara yolu).', 3),

('turkmenistan', 'Türkmenistan', 'Aşgabat', '6.4 milyon', 'İran üzerinden kara yolu / Bakü-Türkmenbaşı feribot',
 'Stratejik enerji ve inşaat işbirlikleri',
 'Türkmenistan, Aşgabat başta olmak üzere büyük altyapı projeleri kapsamında demir-çelik tedariği sağladığımız bir pazardır. Bakü-Türkmenbaşı feribotu ve İran transit kara yolu üzerinden sevkiyat yapılmaktadır.',
 'Türkmenistan: 14-21 iş günü içinde teslimat. Çoklu transit ve gümrük süreci.', 4);


-- =====================================================
-- v1.0.39 — Site keywords ve description SEO için zenginleştirme
-- =====================================================

UPDATE tm_settings SET setting_value = 'demir çelik tedarik, sac satışı, boru satışı, profil satışı, hadde satışı, dkp sac, hrp sac, st52 sac, galvanizli sac, kare profil, dikdörtgen profil, lama, köşebent, hea heb profil, ipe profil, nervürlü demir, çelik hasır, lazer kesim, oksijen kesim, demir çelik konya, sac konya, boru konya, profil konya, demir çelik istanbul, demir çelik ankara, demir çelik izmir, demir çelik bursa, demir çelik gaziantep, ihracat irak, ihracat suriye, ihracat azerbaycan, türkmenistan demir çelik, tekcan metal'
WHERE setting_key = 'site_keywords';

UPDATE tm_settings SET setting_value = 'Tekcan Metal — Türkiye genelinde 2005''ten bu yana faaliyet gösteren Konya merkezli demir-çelik tedarikçisi. Sac (DKP, HRP, ST-52, galvanizli), boru, profil, hadde, nervürlü demir, çelik hasır ve özel ölçü çelik ürünlerinde 81 il sevkiyat ağı. Erdemir, Borçelik, Habaş, İçdaş, Tosyalı Çelik tedarik ortaklığıyla üretici sertifikalı, rekabetçi fiyatlı tedarik. Lazer kesim, oksijen kesim ve dekoratif sac üretim atölyemizle endüstriyel projelere uçtan uca çözüm. Irak, Suriye, Azerbaycan ve Türkmenistan ihracat hattı.'
WHERE setting_key = 'site_description';


-- =====================================================
-- v1.0.40 — tm_categories kolonlarını garantile
-- (Eski DB'lerde meta_desc, meta_title, short_desc, parent_id, image olmayabilir)
-- =====================================================

-- meta_title kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_categories'
                     AND COLUMN_NAME = 'meta_title');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_categories ADD COLUMN meta_title VARCHAR(200) NULL AFTER image',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- meta_desc kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_categories'
                     AND COLUMN_NAME = 'meta_desc');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_categories ADD COLUMN meta_desc VARCHAR(300) NULL AFTER meta_title',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- short_desc kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_categories'
                     AND COLUMN_NAME = 'short_desc');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_categories ADD COLUMN short_desc VARCHAR(300) NULL AFTER name',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- parent_id kolonu yoksa ekle (alt kategori desteği)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_categories'
                     AND COLUMN_NAME = 'parent_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_categories ADD COLUMN parent_id INT UNSIGNED NULL AFTER id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- icon kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_categories'
                     AND COLUMN_NAME = 'icon');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_categories ADD COLUMN icon VARCHAR(80) NULL AFTER short_desc',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- =====================================================
-- v1.0.44 — Satış email adresini kaldır
-- (Yunus: 'sadece info@tekcanmetal.com kullanıyoruz')
-- =====================================================
DELETE FROM tm_settings WHERE setting_key = 'site_email_satis';


-- =====================================================
-- v1.0.46 — KVKK metni demir-çelik sektörü için zenginleştirildi
-- (Yunus: 'sektöre özgü KVKK bilgilerini güçlendirelim')
-- =====================================================

UPDATE tm_pages SET 
    title = 'Kişisel Verilerin Korunması Aydınlatma Metni',
    subtitle = '6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında, demir-çelik tedarik sürecinde işlenen kişisel verileriniz hakkında bilgilendirme.',
    meta_title = 'KVKK Aydınlatma Metni | Tekcan Metal',
    meta_desc = 'Tekcan Metal KVKK aydınlatma metni — demir-çelik tedarik, sevkiyat, fatura ve müşteri ilişkileri kapsamında işlenen kişisel verileriniz hakkında detaylı bilgilendirme.',
    content = '<p>Tekcan Metal Sanayi ve Ticaret Ltd. Şti. olarak; demir-çelik tedariği, atölye kesim hizmetleri, sevkiyat, fatura ve müşteri ilişkileri süreçlerinde işlemekte olduğumuz kişisel verilerinizin korunmasına azami önem veriyoruz. İşbu Aydınlatma Metni, 6698 sayılı Kişisel Verilerin Korunması Kanunu (<em>"KVKK"</em>) ve ilgili mevzuat kapsamındaki yükümlülüklerimizi yerine getirmek üzere hazırlanmıştır.</p>

<h2>1. Veri Sorumlusunun Kimliği</h2>
<p>6698 sayılı Kanun uyarınca, kişisel verileriniz; <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong> (bundan sonra <em>"Şirket"</em> olarak anılacaktır) tarafından, veri sorumlusu sıfatıyla, aşağıda açıklanan kapsamda işlenebilecektir.</p>

<h4>Şirket İletişim Bilgileri</h4>
<ul>
<li><strong>Unvan:</strong> Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</li>
<li><strong>Adres:</strong> Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS — Karatay/KONYA</li>
<li><strong>Telefon:</strong> 0 332 342 24 52</li>
<li><strong>E-posta:</strong> info@tekcanmetal.com</li>
<li><strong>Web:</strong> www.tekcanmetal.com</li>
</ul>

<h2>2. Kişisel Verilerin İşlenme Amaçları</h2>
<p>Demir-çelik sektöründeki tedarik faaliyetimizin niteliği gereği aşağıdaki amaçlarla kişisel verilerinizi işlemekteyiz:</p>

<h3>A) Tedarik ve Satış Süreçleri</h3>
<ul>
<li>Sac, boru, profil, hadde ve diğer demir-çelik ürünlerine ilişkin <strong>fiyat teklifi hazırlanması</strong></li>
<li><strong>Sipariş alımı ve onaylanması</strong>, ürün özelliklerine ilişkin teknik koordinasyon</li>
<li><strong>Proforma fatura ve sözleşme</strong> düzenlenmesi</li>
<li>Üretici sertifikası, menşei şahadetnamesi gibi <strong>kalite belgelerinin düzenlenmesi</strong></li>
<li>Sipariş özeline yönelik <strong>özel ölçü kesim, lazer kesim ve oksijen kesim</strong> atölye operasyonlarının yürütülmesi</li>
</ul>

<h3>B) Sevkiyat ve Lojistik</h3>
<ul>
<li>Yurt içi 81 il sevkiyat ağında <strong>teslim adresi, alıcı bilgisi ve irtibat detaylarının</strong> kullanılması</li>
<li>Anlaşmalı nakliyat firmaları ile <strong>yük koordinasyonu</strong> sağlanması</li>
<li>Yurt dışı (Irak, Suriye, Azerbaycan, Türkmenistan) <strong>ihracat sevkiyatlarında gümrük belgesi düzenlenmesi</strong> ve transit lojistik koordinasyonu</li>
<li>Sevk irsaliyesi ve teslim tutanağı düzenlenmesi</li>
</ul>

<h3>C) Faturalama ve Tahsilat</h3>
<ul>
<li>e-Fatura, e-Arşiv Fatura veya kâğıt fatura düzenlenmesi</li>
<li>Banka havalesi, EFT, kredi kartı, KVK çek ve <strong>vadeli açık hesap işlemlerinin</strong> takibi</li>
<li>Mali müşavirlik ve <strong>resmi makamlara karşı yasal yükümlülüklerin</strong> yerine getirilmesi</li>
<li>Ödeme gecikmelerinde hukuki süreç başlatılması</li>
</ul>

<h3>D) Müşteri İlişkileri ve Pazarlama</h3>
<ul>
<li>Müşteri sadakat programı kapsamında <strong>üyelik kayıtlarının yönetilmesi</strong></li>
<li>İletişim formları, WhatsApp ve telefon görüşmeleri yoluyla <strong>fiyat soruşturmalarına yanıt verilmesi</strong></li>
<li>Açık rıza vermeniz halinde, <strong>yeni ürün, kampanya ve sektörel duyuruların</strong> tarafınıza iletilmesi</li>
<li>Web sitesi üzerinde <strong>kullanıcı deneyiminin iyileştirilmesi</strong> (çerez politikamız ayrıca yayınlanmıştır)</li>
</ul>

<h3>E) Hukuki ve Mevzuat Yükümlülükleri</h3>
<ul>
<li>Vergi Usul Kanunu, Türk Ticaret Kanunu ve diğer ticari mevzuat çerçevesindeki <strong>defter ve belge saklama yükümlülükleri</strong></li>
<li>Mali Suçları Araştırma Kurulu (MASAK), gümrük müsteşarlığı ve diğer <strong>yetkili kamu kurumlarının taleplerine yanıt verilmesi</strong></li>
<li>Yasal denetim, soruşturma ve mahkeme süreçlerinde <strong>delil ve belge sunulması</strong></li>
</ul>

<h2>3. İşlenen Kişisel Veri Kategorileri</h2>
<p>Yukarıda belirtilen amaçlar doğrultusunda aşağıdaki kişisel veri kategorileri işlenmektedir:</p>

<table>
<thead><tr><th>Veri Kategorisi</th><th>Örnek Veriler</th></tr></thead>
<tbody>
<tr><td><strong>Kimlik Bilgileri</strong></td><td>Ad, soyad, T.C. kimlik no, vergi kimlik no, vergi dairesi, ünvan</td></tr>
<tr><td><strong>İletişim Bilgileri</strong></td><td>Telefon, GSM, e-posta, faks, adres, ülke/şehir/ilçe</td></tr>
<tr><td><strong>Müşteri İşlem</strong></td><td>Sipariş geçmişi, teklif kayıtları, fatura bilgileri, ödeme detayları, sevkiyat adresleri</td></tr>
<tr><td><strong>Finansal Veriler</strong></td><td>Banka hesap bilgisi (IBAN), ödeme planı, vadeli hesap durumu, kredi notu (gerektiğinde)</td></tr>
<tr><td><strong>Mesleki Bilgiler</strong></td><td>Çalıştığı şirket, pozisyon, sektör (B2B müşteriler için)</td></tr>
<tr><td><strong>Pazarlama Bilgileri</strong></td><td>İletişim tercihleri, ilgi alanları, tıklama/ziyaret kayıtları (açık rıza ile)</td></tr>
<tr><td><strong>Hukuki İşlem</strong></td><td>Sözleşme, taahhütname, ihtilaf süreci kayıtları</td></tr>
<tr><td><strong>İşlem Güvenliği</strong></td><td>IP adresi, log kayıtları, çerezler, kullanıcı oturum bilgileri</td></tr>
</tbody>
</table>

<h2>4. Kişisel Verilerin Toplanma Yöntemi ve Hukuki Sebebi</h2>
<p>Kişisel verileriniz aşağıdaki yöntemlerle toplanmaktadır:</p>
<ul>
<li><strong>Web sitesi üzerinden:</strong> İletişim formu, mail order başvurusu, sadakat programı kayıt formu, teklif talep formu, fiyat hesaplama wizard''ı</li>
<li><strong>Sözlü ve telefon ile:</strong> Telefon görüşmeleri, WhatsApp yazışmaları, satış danışmanlarına iletilen taleplerden</li>
<li><strong>Yazılı belge ile:</strong> İmzalı sözleşme, sipariş formu, kıymetli evrak, teslim alındısı, sevk irsaliyesi</li>
<li><strong>Fiziksel ziyaret ile:</strong> Konya merkez ofisimize ve depo tesisimize yapılan ziyaretler sırasında verilen kartvizit, ürün katalogu doldurmaları</li>
<li><strong>Üçüncü taraflar yoluyla:</strong> Anlaşmalı nakliyat firmaları, banka onayları, mali müşavirler, gümrük komisyoncuları</li>
<li><strong>Kamu kaynakları:</strong> GİB Vergi Levhası Sorgulama, MERSİS, Ticaret Sicili gibi açık erişimli sistemler</li>
</ul>

<h4>Hukuki Sebepler (KVKK m. 5 ve 6)</h4>
<p>Kişisel verileriniz aşağıdaki hukuki sebeplerden bir veya birkaçı dahilinde işlenmektedir:</p>
<ol>
<li><strong>Açık rıza</strong> (KVKK m. 5/1) — Pazarlama iletişimi gibi rıza temelli işlemeler için</li>
<li><strong>Sözleşmenin kurulması veya ifası</strong> (KVKK m. 5/2-c) — Sipariş, sevkiyat, fatura süreçleri için</li>
<li><strong>Hukuki yükümlülüğün yerine getirilmesi</strong> (KVKK m. 5/2-ç) — Vergi, MASAK, ticari defter</li>
<li><strong>Meşru menfaat</strong> (KVKK m. 5/2-f) — Tahsilat takibi, dolandırıcılık önleme, müşteri ilişkileri yönetimi</li>
<li><strong>Bir hakkın tesisi, kullanılması veya korunması</strong> (KVKK m. 5/2-e) — Hukuki ihtilaf süreçleri</li>
</ol>

<h2>5. Kişisel Verilerin Aktarılması</h2>
<p>İşlenen kişisel verileriniz, açıklanan amaçlarla sınırlı olmak üzere aşağıdaki üçüncü taraflara aktarılabilir:</p>

<h4>Yurt İçi Aktarımlar</h4>
<ul>
<li><strong>Üretici partnerlerimiz:</strong> Erdemir, Borçelik, Habaş, İçdaş, Tosyalı Çelik, Kardemir gibi tedarikçi entegre çelik üretim tesislerine — özel sipariş üretim koordinasyonu için</li>
<li><strong>Anlaşmalı nakliyat firmaları:</strong> Ürün sevkiyatının gerçekleştirilmesi için teslim adresi ve alıcı irtibat bilgileri</li>
<li><strong>Bankalar ve ödeme kuruluşları:</strong> Tahsilat işlemleri için</li>
<li><strong>Mali müşavirler ve denetçiler:</strong> Mali ve hukuki yükümlülüklerin yerine getirilmesi için</li>
<li><strong>Yetkili kamu kurum ve kuruluşları:</strong> Mahkeme kararı veya yasal zorunluluk halinde (Vergi Dairesi, MASAK, Gümrük İdaresi vb.)</li>
<li><strong>Hukuk müşavirlerimiz:</strong> Hukuki danışmanlık ve dava süreçlerinde</li>
</ul>

<h4>Yurt Dışı Aktarımlar (İhracat Sevkiyatları)</h4>
<p>Irak, Suriye, Azerbaycan ve Türkmenistan''a yapılan ihracat sevkiyatları kapsamında, gümrük süreçleri ve uluslararası nakliye için zorunlu olduğu kadarıyla, alıcı ülke gümrük makamlarına ve uluslararası nakliye firmalarına kişisel veri aktarımı yapılabilir. Bu aktarımlar KVKK''nın 9. maddesi çerçevesinde, yeterli korumaya sahip ülkeler veya açık rızanız temelinde gerçekleştirilir.</p>

<h2>6. Kişisel Verilerin Saklanma Süresi</h2>
<p>Kişisel verileriniz, ilgili mevzuatta öngörülen süreler ve verilerin işlenmesini gerektiren amaçlar için gerekli olan süre boyunca saklanmaktadır:</p>
<ul>
<li><strong>Mali ve ticari belgeler:</strong> Vergi Usul Kanunu uyarınca <strong>10 yıl</strong></li>
<li><strong>Sözleşme ve sipariş kayıtları:</strong> Türk Borçlar Kanunu zamanaşımı süresi <strong>10 yıl</strong></li>
<li><strong>İletişim formu kayıtları:</strong> İletişim talebinden itibaren <strong>3 yıl</strong></li>
<li><strong>Sadakat programı üyelik bilgileri:</strong> Üyelik aktif olduğu sürece + iptal sonrası <strong>2 yıl</strong></li>
<li><strong>Pazarlama izinleri:</strong> İzin geri alınana kadar</li>
<li><strong>Web sitesi log ve çerez verileri:</strong> En fazla <strong>1 yıl</strong></li>
</ul>
<p>Saklama süresi sona eren kişisel veriler, KVK Kurulu''nun ilgili kararları ve <em>"Kişisel Verilerin Silinmesi, Yok Edilmesi veya Anonim Hale Getirilmesi Hakkında Yönetmelik"</em> hükümlerine uygun olarak periyodik imha veya anonim hale getirme yoluyla işlemden kaldırılır.</p>

<h2>7. Kişisel Verilerin Güvenliği</h2>
<p>Şirketimiz, KVKK''nın 12. maddesi gereği, kişisel verilerinizin hukuka aykırı işlenmesini, erişilmesini önlemek ve verilerin muhafazasını sağlamak amacıyla aşağıdaki teknik ve idari tedbirleri uygulamaktadır:</p>

<h4>Teknik Tedbirler</h4>
<ul>
<li>SSL/TLS şifreleme ile güvenli web bağlantısı (HTTPS)</li>
<li>Veritabanı erişimi için PDO prepared statements (SQL injection koruması)</li>
<li>CSRF token, honeypot ve rate limiting ile form güvenliği</li>
<li>Düzenli yedekleme ve felaket kurtarma planı</li>
<li>Yetkili olmayan erişimi tespit eden log sistemi</li>
<li>Şifre hash''leme ve oturum güvenliği</li>
</ul>

<h4>İdari Tedbirler</h4>
<ul>
<li>Veri işleme süreçlerinin envanteri ve düzenli gözden geçirilmesi</li>
<li>Çalışanlara KVKK farkındalık eğitimi</li>
<li>Veri işleyen üçüncü taraflarla yapılan sözleşmelerde KVKK uyum maddeleri</li>
<li>Erişim yetkilerinin görev tanımlarına göre sınırlandırılması</li>
<li>Veri ihlali halinde KVK Kurulu''na 72 saat içinde bildirim prosedürü</li>
</ul>

<h2>8. Kişisel Veri Sahibinin Hakları (KVKK Madde 11)</h2>
<p>Kişisel veri sahibi olarak, KVKK''nın 11. maddesi uyarınca aşağıdaki haklarınız bulunmaktadır:</p>
<ol>
<li>Kişisel verilerinizin <strong>işlenip işlenmediğini öğrenme</strong></li>
<li>Kişisel verileriniz işlenmişse <strong>buna ilişkin bilgi talep etme</strong></li>
<li>Kişisel verilerinizin <strong>işlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme</strong></li>
<li>Yurt içinde veya yurt dışında <strong>kişisel verilerin aktarıldığı üçüncü kişileri öğrenme</strong></li>
<li>Kişisel verilerin <strong>eksik veya yanlış işlenmiş olması halinde düzeltilmesini isteme</strong></li>
<li>KVKK''nın 7. maddesinde öngörülen şartlar çerçevesinde kişisel verilerin <strong>silinmesini veya yok edilmesini isteme</strong></li>
<li>Düzeltme, silme ve yok edilme taleplerinin, kişisel verilerin aktarıldığı üçüncü kişilere bildirilmesini isteme</li>
<li>İşlenen verilerin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi suretiyle aleyhe bir sonucun ortaya çıkmasına <strong>itiraz etme</strong></li>
<li>Kişisel verilerin kanuna aykırı olarak işlenmesi sebebiyle zarara uğraması halinde <strong>zararın giderilmesini talep etme</strong></li>
</ol>

<h2>9. Başvuru Yöntemleri ve Sonuçlandırma</h2>
<p>Yukarıda belirtilen haklarınızı kullanmak için talebinizi, <em>"Veri Sorumlusuna Başvuru Usul ve Esasları Hakkında Tebliğ''e"</em> uygun olarak aşağıdaki yöntemlerden birini kullanarak Şirketimize iletebilirsiniz:</p>

<h4>Başvuru Kanalları</h4>
<ul>
<li><strong>Yazılı başvuru:</strong> Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS — Karatay/KONYA adresine ıslak imzalı dilekçe ile</li>
<li><strong>Kayıtlı Elektronik Posta (KEP):</strong> Şirket KEP adresimize</li>
<li><strong>Güvenli elektronik imza ile:</strong> info@tekcanmetal.com adresine</li>
<li><strong>Mobil imza veya kayıtlı e-posta ile:</strong> Sisteminizde kayıtlı e-posta adresinizden</li>
</ul>

<h4>Başvuruda Bulunması Gerekenler</h4>
<ul>
<li>Ad, soyad, T.C. kimlik numarası (Türkiye Cumhuriyeti vatandaşı iseniz)</li>
<li>Tebligata esas yerleşim yeri veya iş yeri adresi</li>
<li>Bildirime esas elektronik posta adresi, telefon ve faks numarası</li>
<li>Talep konusu — açık ve net biçimde</li>
<li>Konuya ilişkin destekleyici belge ve bilgiler (varsa)</li>
</ul>

<p>Talebiniz, niteliğine göre en kısa sürede ve <strong>en geç 30 (otuz) gün içinde</strong> ücretsiz olarak sonuçlandırılacaktır. Ancak işlemin ayrıca bir maliyet gerektirmesi halinde, KVK Kurulu''nca belirlenen tarifedeki ücret talep edilebilir.</p>

<h2>10. Veri İhlali Bildirimleri</h2>
<p>Şirketimiz, kişisel verilerinizin yetkisiz kişilerce ele geçirilmesi durumunda, KVKK''nın 12. maddesi gereği, durumu en geç <strong>72 saat içinde KVK Kurulu''na</strong> ve veri sahibi olarak <strong>size de</strong> uygun yöntemlerle bildirir.</p>

<h2>11. Aydınlatma Metnindeki Değişiklikler</h2>
<p>İşbu Aydınlatma Metni, mevzuat değişiklikleri, iş süreçlerimizdeki dönüşümler veya KVK Kurulu kararları doğrultusunda zaman zaman güncellenebilir. Güncellenmiş metin, web sitemizde yayımlandığı tarihte yürürlüğe girer. Metnin son güncellenme tarihi sayfanın üst kısmında belirtilmektedir.</p>

<blockquote><p>İşbu Aydınlatma Metni, Tekcan Metal Sanayi ve Ticaret Ltd. Şti. tarafından 2005 yılından bu yana sürdürülen demir-çelik tedarik faaliyetinin tüm paydaşları için hazırlanmıştır. Sektörün doğal yapısı gereği işlenen verilerin niteliği ve süreçleri açıklanmış olup, KVKK ile uyum çabalarımız sürekli devam etmektedir.</p></blockquote>

<h4>Yürürlük</h4>
<p>İşbu metin, Tekcan Metal Sanayi ve Ticaret Ltd. Şti. tarafından yayımlanmış olup yürürlüktedir. Sorularınız için <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a> adresinden bizimle iletişime geçebilirsiniz.</p>',
    updated_at = NOW()
WHERE slug = 'kvkk';


-- =====================================================
-- v1.0.47 — Çerez Politikası kapsamlı ve detaylı şekilde
-- güçlendirildi (KVKK ile uyumlu, modern tarayıcı ayarları,
-- üçüncü taraf çerez tipleri, saklama süreleri vs.)
-- =====================================================

UPDATE tm_pages SET 
    title = 'Çerez Politikası',
    subtitle = 'Web sitemizde kullanılan çerezler, kullanım amaçları ve kontrol seçenekleriniz hakkında ayrıntılı bilgilendirme.',
    meta_title = 'Çerez Politikası | Tekcan Metal',
    meta_desc = 'Tekcan Metal çerez politikası — web sitemizde kullanılan zorunlu, performans, işlevsel ve hedefleme çerezleri, saklama süreleri ve tarayıcı kontrol ayarları hakkında detaylı bilgi.',
    content = '<p>Tekcan Metal Sanayi ve Ticaret Ltd. Şti. olarak www.tekcanmetal.com adresinde yayında olan web sitemizi (bundan sonra <em>"Web Sitesi"</em> olarak anılacaktır) ziyaret eden kullanıcılarımızın deneyimini iyileştirmek, sitenin verimli çalışmasını sağlamak ve hizmet kalitemizi geliştirmek amacıyla çerezlerden faydalanmaktayız. İşbu Çerez Politikası, hangi çerezleri ne amaçla kullandığımız ve bu çerezleri nasıl yönetebileceğiniz konusunda sizi bilgilendirmek üzere hazırlanmıştır.</p>

<h2>1. Çerez Nedir?</h2>
<p>Çerezler (cookies); ziyaret ettiğiniz web siteleri tarafından tarayıcınız aracılığıyla cihazınıza (bilgisayar, tablet, akıllı telefon) yerleştirilen, içeriğinde sınırlı sayıda metin verisi barındıran küçük dosyalardır. Bu dosyalar genellikle bir tanımlayıcı (ID), site adı ve geçerlilik süresi gibi bilgiler içerir.</p>

<p>Çerezler, ziyaretçilerin web sitesi ile etkileşimini hatırlamasına, oturum bilgilerinin korunmasına, kullanıcı tercihlerinin saklanmasına ve site performansının ölçülmesine olanak tanır. Çerezler tek başlarına virüs taşımaz, kişisel dosyalarınıza erişemez ve cihazınıza zarar vermez.</p>

<h2>2. Hangi Çerezleri Kullanıyoruz?</h2>
<p>Web sitemizde aşağıda detayları yer alan dört ana çerez kategorisi kullanılmaktadır:</p>

<h3>A) Zorunlu (Teknik) Çerezler</h3>
<p><strong>Amaç:</strong> Sitenin temel işlevlerinin çalışabilmesi için <em>kesinlikle gerekli</em> çerezlerdir. Bu çerezler olmadan site düzgün çalışmaz; oturumunuz açılamaz, formlarınız gönderilemez, güvenlik kontrolleri yapılamaz.</p>

<table>
<thead><tr><th>Çerez Adı</th><th>Amaç</th><th>Süre</th></tr></thead>
<tbody>
<tr><td><code>PHPSESSID</code></td><td>Oturum yönetimi (giriş bilgisi, sepet, form durumu)</td><td>Tarayıcı kapanana kadar</td></tr>
<tr><td><code>csrf_token</code></td><td>Form güvenliği (CSRF saldırı koruması)</td><td>Oturum süresince</td></tr>
<tr><td><code>cookie_consent</code></td><td>Çerez onay tercihinizin hatırlanması</td><td>1 yıl</td></tr>
<tr><td><code>tm_admin</code></td><td>Yönetici paneli erişim oturumu</td><td>30 gün</td></tr>
</tbody>
</table>

<h4>Hukuki Dayanak</h4>
<p>KVKK m. 5/2-c (sözleşmenin kurulması ve ifası) ve KVKK m. 5/2-f (meşru menfaat) kapsamında, kullanıcı onayı aranmaksızın işlenebilir.</p>

<h3>B) Performans ve Analitik Çerezler</h3>
<p><strong>Amaç:</strong> Ziyaretçilerin siteyi nasıl kullandığını anlamamızı sağlar. Hangi sayfaların daha çok ziyaret edildiğini, kullanıcıların sitede ne kadar süre geçirdiğini, hangi noktalarda zorlandığını analiz ederek hizmetimizi geliştiririz. Toplanan veriler <strong>anonimleştirilir</strong>, kullanıcıyı kişisel olarak tanımlamaz.</p>

<table>
<thead><tr><th>Sağlayıcı</th><th>Amaç</th><th>Süre</th></tr></thead>
<tbody>
<tr><td>Google Analytics 4 (<code>_ga, _gid</code>)</td><td>Sayfa görüntüleme, kullanıcı yolu, demografik anonim analiz</td><td>2 yıl / 24 saat</td></tr>
<tr><td>Yandex Metrica (<code>_ym_uid</code>)</td><td>Bölgesel ziyaretçi analizi (özellikle Türkiye, Rusya pazarları)</td><td>1 yıl</td></tr>
<tr><td>Site içi log (<code>tm_visit</code>)</td><td>Hangi ürün/kategori sayfaları popüler ölçümü</td><td>30 gün</td></tr>
</tbody>
</table>

<h4>Hukuki Dayanak</h4>
<p>KVKK m. 5/1 (açık rıza) — kullanıcının çerez onay banner''ı ile vermiş olduğu rıza temelinde işlenir. Onay vermediğiniz takdirde bu çerezler oluşturulmaz.</p>

<h3>C) İşlevsellik (Tercih) Çerezleri</h3>
<p><strong>Amaç:</strong> Site üzerindeki kullanım tercihlerinizi hatırlayarak, sonraki ziyaretlerinizde kişiselleştirilmiş bir deneyim sunmamızı sağlar.</p>

<table>
<thead><tr><th>Çerez Adı</th><th>Amaç</th><th>Süre</th></tr></thead>
<tbody>
<tr><td><code>tm_lang</code></td><td>Dil tercihi (TR / EN)</td><td>1 yıl</td></tr>
<tr><td><code>tm_theme</code></td><td>Açık / koyu tema seçimi</td><td>1 yıl</td></tr>
<tr><td><code>tm_calc_history</code></td><td>Hesaplama wizard''ında son seçtiğiniz ürün</td><td>30 gün</td></tr>
<tr><td><code>tm_recent_views</code></td><td>Son görüntülenen ürünler (hızlı erişim)</td><td>30 gün</td></tr>
</tbody>
</table>

<h4>Hukuki Dayanak</h4>
<p>KVKK m. 5/1 (açık rıza) — onay verdiğiniz takdirde aktifleştirilir.</p>

<h3>D) Hedefleme ve Reklam Çerezleri</h3>
<p><strong>Amaç:</strong> Web sitemizdeki ziyaretiniz sonrasında, başka sitelerde gezerken size <em>ilgi alanlarınıza uygun</em> reklamların gösterilmesini sağlamak için kullanılır. Tekcan Metal olarak, demir-çelik sektöründeki müşterilerimize yönelik kampanyalar, yeni ürün duyuruları ve mevsimsel fiyat indirimlerini ulaştırmak amacıyla bu çerezler kullanılabilir.</p>

<table>
<thead><tr><th>Sağlayıcı</th><th>Amaç</th><th>Süre</th></tr></thead>
<tbody>
<tr><td>Google Ads (<code>_gcl_au</code>, <code>NID</code>)</td><td>Yeniden pazarlama, dönüşüm takibi</td><td>3 ay – 1 yıl</td></tr>
<tr><td>Meta Pixel (<code>_fbp</code>)</td><td>Facebook ve Instagram reklam takibi</td><td>3 ay</td></tr>
<tr><td>LinkedIn Insight (<code>li_sugr</code>)</td><td>B2B sektörel hedefleme (sanayi, inşaat)</td><td>3 ay</td></tr>
</tbody>
</table>

<h4>Hukuki Dayanak</h4>
<p>KVKK m. 5/1 (açık rıza) — yalnızca onay vermeniz halinde aktifleştirilir. Reddetme durumunda yeniden pazarlama hedeflemesi yapılmaz, ancak rastgele genel reklamlar görmeye devam edebilirsiniz.</p>

<h2>3. Üçüncü Taraf Çerezler</h2>
<p>Web sitemizde, doğrudan Şirketimiz tarafından oluşturulan çerezlere ek olarak, hizmet sağladığımız ortakların oluşturduğu çerezler de bulunabilir. Bu üçüncü taraf çerezler, ilgili sağlayıcının kendi gizlilik politikalarına tabidir:</p>
<ul>
<li><strong>Google LLC</strong> — Analytics, Maps, Ads, reCAPTCHA — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Gizlilik Politikası</a></li>
<li><strong>Meta Platforms</strong> — Facebook, Instagram pixel — <a href="https://www.facebook.com/policies/cookies/" target="_blank" rel="noopener">Çerez Politikası</a></li>
<li><strong>LinkedIn Corporation</strong> — Insight tag — <a href="https://www.linkedin.com/legal/cookie-policy" target="_blank" rel="noopener">Çerez Politikası</a></li>
<li><strong>YouTube</strong> — Embed video çerezleri — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Gizlilik</a></li>
<li><strong>OpenStreetMap</strong> — Sevkiyat haritası — <a href="https://wiki.osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener">Privacy Policy</a></li>
</ul>

<blockquote>
<p>Üçüncü taraf çerezleri tarafımızca yönetilmemektedir. Bu çerezlerle toplanan verilerin nasıl kullanıldığı konusundaki sorumluluk, ilgili üçüncü taraf hizmet sağlayıcılarına aittir.</p>
</blockquote>

<h2>4. Çerezlerin Saklama Süresi</h2>
<p>Çerezler, saklama sürelerine göre iki ana grupta incelenir:</p>

<h4>A) Oturum Çerezleri (Session Cookies)</h4>
<p>Tarayıcı kapatıldığı anda silinir. Genellikle oturum yönetimi ve form güvenliği gibi geçici işlemler için kullanılır.</p>

<h4>B) Kalıcı Çerezler (Persistent Cookies)</h4>
<p>Önceden belirlenmiş bir süre (örneğin 30 gün, 1 yıl) boyunca cihazınızda saklanır. Bu süre dolduğunda otomatik olarak silinir veya kullanıcı tarafından manuel temizlenebilir. Tablolarda her çerezin süresi ayrıca belirtilmiştir.</p>

<h2>5. Çerez Tercihlerinizi Nasıl Yönetirsiniz?</h2>

<h3>A) Site İçi Çerez Yönetimi</h3>
<p>Web sitemize ilk girişinizde ekrana gelen <strong>Çerez Onay Banner''ı</strong> üzerinden:</p>
<ul>
<li><strong>Tümünü Kabul Et:</strong> Tüm çerez kategorilerini onaylar</li>
<li><strong>Sadece Zorunlu:</strong> Yalnızca teknik çerezleri kabul eder, diğerlerini reddeder</li>
<li><strong>Tercihlerimi Yönet:</strong> Her kategoriyi ayrı ayrı seçebilirsiniz</li>
</ul>

<p>Tercihlerinizi sonradan değiştirmek için sayfanın altındaki <strong>"Çerez Ayarları"</strong> bağlantısına tıklayarak banner''ı yeniden görüntüleyebilirsiniz.</p>

<h3>B) Tarayıcı Üzerinden Çerez Yönetimi</h3>
<p>Tarayıcı ayarlarınızdan çerezleri silebilir, engelleyebilir veya belirli sitelerden gelen çerezleri reddedebilirsiniz. Aşağıda popüler tarayıcılar için ayar bağlantıları yer almaktadır:</p>

<table>
<thead><tr><th>Tarayıcı</th><th>Çerez Yönetimi</th></tr></thead>
<tbody>
<tr><td>Google Chrome</td><td>Ayarlar → Gizlilik ve güvenlik → Çerezler ve diğer site verileri</td></tr>
<tr><td>Mozilla Firefox</td><td>Ayarlar → Gizlilik ve Güvenlik → Çerezler ve Site Verileri</td></tr>
<tr><td>Safari</td><td>Tercihler → Gizlilik → Çerezler ve web sitesi verileri</td></tr>
<tr><td>Microsoft Edge</td><td>Ayarlar → Çerezler ve site izinleri → Çerezler ve site verileri</td></tr>
<tr><td>Opera</td><td>Ayarlar → Gizlilik ve güvenlik → Site Ayarları → Çerezler</td></tr>
</tbody>
</table>

<h3>C) Mobil Cihaz Reklam Tanımlayıcısı</h3>
<p>Mobil cihazlarda reklam takibini sınırlamak için:</p>
<ul>
<li><strong>iOS:</strong> Ayarlar → Gizlilik ve Güvenlik → İzleme → "Uygulamaların İzleme Talep Etmesine İzin Ver" kapatın</li>
<li><strong>Android:</strong> Ayarlar → Google → Reklamlar → "Reklam Kişiselleştirme" kapatın veya "Reklam Kimliğini Sıfırla"</li>
</ul>

<h2>6. Çerezleri Reddetmenin Sonuçları</h2>

<h4>Zorunlu Çerezler Reddedilirse</h4>
<p>Site çoğu işlevini yerine getiremez. Giriş yapamazsınız, form gönderemezsiniz, güvenlik kontrolleri çalışmaz. Bu nedenle zorunlu çerezler reddedilemez ve kullanıcı onayına tabi değildir (yasal zorunluluk).</p>

<h4>Performans / Analitik Çerezler Reddedilirse</h4>
<p>Site normal şekilde kullanılabilir, ancak ziyaretçi davranışlarını analiz etme ve bu doğrultuda iyileştirmeler yapma kapasitemiz azalır. Bu, uzun vadede sitemizin gelişimini olumsuz etkileyebilir.</p>

<h4>İşlevsellik Çerezleri Reddedilirse</h4>
<p>Dil tercihi, tema seçimi gibi kişisel ayarlar her ziyarette sıfırlanır. Hesaplama wizard''ında son kaldığınız yerden devam edemezsiniz.</p>

<h4>Hedefleme / Reklam Çerezleri Reddedilirse</h4>
<p>Yeniden pazarlama yapılmaz, ancak rastgele genel reklamlar görmeye devam edebilirsiniz. Reklam sayısı azalmaz; yalnızca kişiselleştirme kapatılır.</p>

<h2>7. Çocuklar ve Çerezler</h2>
<p>Web sitemiz <strong>13 yaş altı çocuklara yönelik değildir</strong>. Çocuklardan bilerek kişisel veri toplamaz, çerez yerleştirmeyiz. 13 yaş altı bir çocuğun rızası olmaksızın bilgilerinin sitemize ulaştığından şüphe duyarsanız, lütfen <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a> adresinden bizimle iletişime geçin.</p>

<h2>8. Veri Aktarımı ve Yurt Dışı Sunucular</h2>
<p>Bazı analitik ve reklam hizmet sağlayıcıları (Google, Meta, LinkedIn) verilerinizi yurt dışındaki sunucularda işleyebilir. Bu hizmetler, KVKK''nın 9. maddesi kapsamında yeterli korumaya sahip ülkeler veya açık rızanız temelinde gerçekleştirilir. Detaylı bilgi için ilgili hizmet sağlayıcının gizlilik politikasını incelemenizi öneririz.</p>

<h2>9. Çerez Politikasındaki Değişiklikler</h2>
<p>İşbu Çerez Politikası, mevzuat değişiklikleri, kullanılan teknolojilerin güncellenmesi veya iş süreçlerimizdeki dönüşümler doğrultusunda zaman zaman güncellenebilir. Önemli değişikliklerde, web sitesinde bildirim yayınlanır ve yeniden çerez onay banner''ı sunulur.</p>

<p>Bu politika ile ilgili soru ve talepleriniz için <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a> adresinden bizimle iletişime geçebilirsiniz.</p>

<h2>10. İlgili Belgeler</h2>
<p>Çerez Politikamız aşağıdaki diğer kurumsal belgelerimizle birlikte değerlendirilmelidir:</p>
<ul>
<li><a href="/sayfa.php?slug=kvkk">Kişisel Verilerin Korunması Aydınlatma Metni</a> — Tüm kişisel veri işleme süreçlerimiz</li>
<li>Gizlilik Politikası (yayınlandığında bağlantı buraya eklenecektir)</li>
<li>Kullanım Şartları (yayınlandığında bağlantı buraya eklenecektir)</li>
</ul>

<blockquote>
<p>Çerez teknolojileri sürekli evrilen bir alandır. Şirketimiz, kullanıcı gizliliğini koruma ve şeffaflık ilkelerimiz doğrultusunda en iyi uygulamaları takip etmeye devam etmektedir. Önerileriniz ve geri bildirimleriniz için bizimle iletişime geçmekten çekinmeyin.</p>
</blockquote>

<h4>Yürürlük</h4>
<p>İşbu Çerez Politikası, Tekcan Metal Sanayi ve Ticaret Ltd. Şti. tarafından yayımlanmış olup yürürlüktedir.</p>',
    updated_at = NOW()
WHERE slug = 'cerez-politikasi';



-- =====================================================
-- v1.0.49 — tm_services kolonları + 3 hizmet zenginleştirme
-- (Önceki v1.0.48 bozuk Python regex nedeniyle iptal edildi,
-- bu sürümde el yazımı dikkatli SQL kullanılıyor)
-- =====================================================

-- specs kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_services'
                     AND COLUMN_NAME = 'specs');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_services ADD COLUMN specs LONGTEXT NULL AFTER features',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- meta_title kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_services'
                     AND COLUMN_NAME = 'meta_title');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_services ADD COLUMN meta_title VARCHAR(200) NULL AFTER short_desc',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- meta_desc kolonu yoksa ekle
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tm_services'
                     AND COLUMN_NAME = 'meta_desc');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE tm_services ADD COLUMN meta_desc VARCHAR(300) NULL AFTER meta_title',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;



-- =====================================================
-- v1.0.53 — ACIL ONARIM (UPDATE'lerden ÖNCE çalışır)
-- 3 hizmet (lazer/oksijen/dekoratif) DB'de bozulmuş olabilir.
-- DELETE + INSERT ile slug'lar ve title'lar garanti restore edilir.
-- Sonra alttaki UPDATE'ler bu temiz satırları zenginleştirir.
-- =====================================================

DELETE FROM tm_services WHERE slug IN ('lazer-kesim', 'oksijen-kesim', 'dekoratif-saclar');

INSERT INTO tm_services (slug, title, short_desc, icon, image, is_active, sort_order) VALUES
  ('lazer-kesim',      'Lazer Kesim',           'Fiber lazer ile yüksek hassasiyetli endüstriyel kesim hizmeti.', 'zap',      'uploads/services/lazer-kesim.jpg',     1, 1),
  ('oksijen-kesim',    'Oksijen Kesim',         'Kalın saclar için ekonomik CNC oksijen kesim hizmeti.',          'flame',    'uploads/services/oksijen-kesim.jpg',   1, 2),
  ('dekoratif-saclar', 'Dekoratif Sac Üretimi', 'Mimari ve dekoratif uygulamalar için özel desenli sac üretimi.', 'sparkles', 'uploads/services/dekoratif-saclar.png',1, 3);

-- LAZER-KESIM
UPDATE tm_services SET
    short_desc = 'Fiber lazer teknolojisiyle 0,5–25 mm sac kalınlık aralığında ±0,1 mm hassasiyetle endüstriyel kesim hizmeti. DXF/DWG dosyanızı gönderin, aynı gün üretime alalım.',
    meta_title = 'Lazer Kesim Hizmeti Konya | Tekcan Metal',
    meta_desc = 'Tekcan Metal lazer kesim atölyesi — fiber lazer 0,5-25 mm kalınlık, ±0,1 mm hassasiyet, 1500x3000 mm tabla. DXF/DWG kabul, aynı gün teklif, 81 il sevkiyat.',
    features = '["0,5 mm – 25 mm sac kalınlık aralığı","±0,1 mm endüstriyel hassasiyet","1500 × 3000 mm maksimum tabla boyutu","DXF, DWG, STEP, PDF dosya formatları","CAM yazılımıyla kesim yolu optimizasyonu","Karbon, paslanmaz ve galvanizli sac","Aynı gün kesim ve sevkiyat (09:00 öncesi)","3D modelleme ve mühendislik danışmanlığı","Pürüzsüz kenar, sıfır çapak","Karmaşık geometrilerde optimum verim"]',
    specs = '{"Kesim Tipi":"Fiber Lazer","Sac Kalınlığı":"0,5 - 25 mm","Tabla Boyutu":"1500 × 3000 mm","Hassasiyet":"±0,1 mm","Kesim Hızı":"5-30 m/dk","Dosya Formatları":"DXF, DWG, STEP, PDF","Malzeme":"Karbon / Paslanmaz / Galvaniz","Termin Süresi":"Aynı gün - 3 iş günü"}',
    description = '<p>Fiber lazer kesim teknolojisi, demir-çelik sektöründe <em>endüstriyel kesimin altın standardıdır</em>. Tekcan Metal olarak modern fiber lazer makinemizle, 0,5 mm''den 25 mm''ye kadar farklı kalınlıklardaki sac levhaları, ±0,1 mm gibi son derece dar bir tolerans aralığında kesiyoruz. CAD dosyanızdan dijital olarak doğrudan üretime geçen sistem; her parçada aynı kalitede, çapaksız ve pürüzsüz kenarlar oluşturur.</p>

<h2>Lazer Kesimin Endüstriyel Avantajları</h2>
<p>Geleneksel kesim yöntemlerine kıyasla lazer kesim; <strong>hassasiyet, hız ve geometri esnekliği</strong> üçgeninde belirgin üstünlük sunar. Her bir kesim noktasında lazer huzmesinin oluşturduğu dar kerf (kesim genişliği) sayesinde, malzeme israfı en aza iner. Karmaşık iç delikler, ince geometriler ve seri üretim parçalar için ideal yöntemdir.</p>

<ul>
<li><strong>Hassasiyet:</strong> ±0,1 mm tolerans ile mekatronik, otomotiv yan sanayi ve hassas mekanik üretim için uygundur</li>
<li><strong>Hız:</strong> 5-30 m/dk kesim hızıyla seri üretim mümkün</li>
<li><strong>Geometri Esnekliği:</strong> Daire, kavis, iç delik, oyma — tüm CAD geometrileri</li>
<li><strong>Yüzey Kalitesi:</strong> Çapaksız, pürüzsüz, ek işleme gerek bırakmayan kenar</li>
<li><strong>Malzeme Verimliliği:</strong> Dar kerf ile minimum fire</li>
<li><strong>Termal Etki:</strong> Isı etki bölgesi (HAZ) çok dar — malzeme özellikleri korunur</li>
</ul>

<h2>Hangi Malzemelerde Lazer Kesim Yapılır?</h2>
<p>Atölyemizde aşağıdaki malzeme türlerinde lazer kesim yapıyoruz:</p>

<table>
<thead><tr><th>Malzeme</th><th>Kalınlık Aralığı</th><th>Tipik Kullanım</th></tr></thead>
<tbody>
<tr><td>Karbon Çeliği (St37, St52)</td><td>0,5 - 25 mm</td><td>Yapı çeliği, makine imalatı, çelik konstrüksiyon</td></tr>
<tr><td>Paslanmaz Çelik (304, 316)</td><td>0,5 - 12 mm</td><td>Gıda ekipmanı, mutfak imalatı, mimari</td></tr>
<tr><td>Galvanizli Sac</td><td>0,5 - 6 mm</td><td>Beyaz eşya, klima, havalandırma kanalı</td></tr>
<tr><td>DKP Sac</td><td>0,5 - 4 mm</td><td>Otomotiv kaport, hassas form parçaları</td></tr>
<tr><td>HRP Sac</td><td>3 - 25 mm</td><td>Yapı, ağır iş makinesi parçaları</td></tr>
<tr><td>Alüminyum</td><td>0,5 - 8 mm</td><td>Hafif konstrüksiyon, dekoratif uygulamalar</td></tr>
</tbody>
</table>

<h2>Sipariş Süreci</h2>
<p>Lazer kesim siparişiniz için gereken adımlar:</p>

<ol>
<li><strong>Dosya Hazırlığı:</strong> DXF, DWG, STEP veya PDF formatında çiziminizi hazırlayın. CAD dosyanız yoksa boyutlandırılmış teknik resim de yeterli.</li>
<li><strong>Dosya Gönderimi:</strong> WhatsApp veya e-posta yoluyla dosyayı bize iletin.</li>
<li><strong>Teknik Analiz:</strong> CAM yazılımımızla dosyayı analiz eder, kesim yolunu optimize eder, malzeme planlamasını yaparız.</li>
<li><strong>Detaylı Teklif:</strong> Aynı gün içinde, malzeme cinsi, kalınlık, parça sayısı ve termin süresine göre detaylı teklifimizi göndeririz.</li>
<li><strong>Üretim:</strong> Onayınız sonrası, sabah 09:00''a kadar gelen acil siparişler aynı gün üretilebilir.</li>
<li><strong>Kalite Kontrol:</strong> Her parti, ölçü ve kenar kalitesi açısından kontrol edilir.</li>
<li><strong>Sevkiyat:</strong> 81 il sevkiyat ağımızla teslim ederiz; Konya il merkezi için aynı gün teslim mümkündür.</li>
</ol>

<h2>Lazer Kesim Tolerans Bilgisi</h2>
<p>Profesyonel lazer kesimde tolerans, malzeme cinsine ve kalınlığına göre değişir.</p>

<table>
<thead><tr><th>Kalınlık</th><th>Standart Tolerans</th><th>Hassas Tolerans</th></tr></thead>
<tbody>
<tr><td>0,5 - 3 mm</td><td>±0,1 mm</td><td>±0,05 mm</td></tr>
<tr><td>3 - 8 mm</td><td>±0,15 mm</td><td>±0,1 mm</td></tr>
<tr><td>8 - 16 mm</td><td>±0,2 mm</td><td>±0,15 mm</td></tr>
<tr><td>16 - 25 mm</td><td>±0,3 mm</td><td>±0,2 mm</td></tr>
</tbody>
</table>

<blockquote><p>Hassas tolerans gerektiren projelerinizde mutlaka önceden bilgi verin. Standart parametrelerin dışında özel ayarlamalar gerekebilir; bu durumda termin süresi 1-2 gün uzayabilir.</p></blockquote>

<h2>Hangi Sektörlere Hizmet Veriyoruz?</h2>
<ul>
<li><strong>Otomotiv Yan Sanayi:</strong> Kaport, şasi, iç-dış aksam (Bursa, Sakarya, Aksaray)</li>
<li><strong>Beyaz Eşya:</strong> Çamaşır makinesi, buzdolabı sac parçaları (Manisa, Eskişehir)</li>
<li><strong>Makine İmalatı:</strong> CNC, presler, tarım makineleri (Konya OSB, Ankara OSTİM)</li>
<li><strong>Çelik Mobilya:</strong> Ofis ve hastane mobilyası (Kayseri, İstanbul)</li>
<li><strong>HVAC ve Havalandırma:</strong> Klima kasası, kanal flanşı (her bölge)</li>
<li><strong>Mimari ve Dekorasyon:</strong> Cephe panelleri, korkuluk, mobilya aksesuarı</li>
<li><strong>Tarım ve Gıda Sanayi:</strong> Paslanmaz tank, makina sacı (Konya, Karaman)</li>
<li><strong>Savunma Sanayi:</strong> Hassas mekanik komponent (Ankara TUSAŞ yan sanayi)</li>
</ul>

<h2>Sıkça Sorulan Sorular</h2>

<h3>Minimum sipariş tutarı var mı?</h3>
<p>Hayır. Tek bir parça için bile lazer kesim yapıyoruz. Ancak parça başı maliyeti yüksek olduğundan, küçük adetlerde fiyat avantajı sağlamak için 1-2 parça yerine 5-10 parça birleştirilmiş sipariş öneriyoruz.</p>

<h3>Aynı gün teslimat var mı?</h3>
<p>Evet, koşullu olarak. Sabah 09:00''a kadar onaylı dosyanız ve ödemeniz tamamlanırsa, aynı gün kesim ve teslimat (Konya il içi) mümkündür.</p>

<h3>DXF dosyam yok, kâğıt çizimle gelebilir miyim?</h3>
<p>Evet. Boyutlandırılmış teknik resminizi getirebilir veya el çizimi/fotoğraf gönderebilirsiniz. Atölyemizde teknik ressamımız çizimi DXF''e çevirir (ek ücret talep edilebilir, basit geometriler ücretsiz).</p>

<h3>Kestiğiniz sac aynı gün teslim alabilir miyim?</h3>
<p>Tabii ki. Karatay/Konya''daki atölyemizden ürünlerinizi forklift veya vinç desteğiyle teslim alabilirsiniz.</p>'
WHERE slug = 'lazer-kesim';

-- OKSIJEN-KESIM
UPDATE tm_services SET
    short_desc = '5–200 mm kalın levha kesimi için CNC oksijen kesim teknolojisi. 3000×6000 mm büyük tabla kapasitesi, ekonomik fiyat, ağır endüstriyel projeler için ideal.',
    meta_title = 'Oksijen Kesim Hizmeti Konya | Tekcan Metal',
    meta_desc = 'Tekcan Metal CNC oksijen kesim — 5-200 mm kalın levha, 3000×6000 mm tabla, ±1 mm tolerans. Yapı çeliği, gemi inşa, ağır makine projeleri için ekonomik kesim.',
    features = '["5 mm – 200 mm kalın levha kapasitesi","3000 × 6000 mm büyük tabla boyutu","±1 mm endüstriyel tolerans","CNC kontrollü otomatik kesim","Yapı çeliği, gemi sacı, kazan plakası","Eğimli kenar (V/Y/X kaynak ağzı) opsiyonu","Ekonomik birim fiyat (kalın saclarda lazer alternatifi)","DXF, DWG dosyaları kabul","Ağır endüstriyel proje deneyimi","6+ metre büyük levhalarda özel sevkiyat"]',
    specs = '{"Kesim Tipi":"CNC Oksijen (Oxy-Fuel)","Sac Kalınlığı":"5 - 200 mm","Tabla Boyutu":"3000 × 6000 mm","Hassasiyet":"±1 mm","Kesim Hızı":"0,3-2 m/dk","Dosya Formatları":"DXF, DWG","Malzeme":"Karbon Çeliği (yapı sacı)","Kaynak Ağzı":"V / Y / X / I"}',
    description = '<p>Oksijen kesim, kalın çelik levhaların kesilmesinde <em>endüstrinin sürat motorudur</em>. 5 mm''den 200 mm''ye uzanan kalınlık aralığıyla, lazer kesimin verimsiz veya imkânsız olduğu noktalarda devreye girer. CNC kontrollü oksijen kesim sistemimiz; gemi inşa, ağır iş makinası, basınçlı kap, çelik konstrüksiyon ve büyük altyapı projelerinin ihtiyacı olan kalın levha kesimini ekonomik fiyat ve hızlı termin süresiyle sağlar.</p>

<h2>Oksijen Kesim Teknolojisi</h2>
<p>Oksijen kesimi (oxy-fuel cutting), saf oksijen jeti ve LPG/asetilen ısı kaynağı kombinasyonuyla çalışan termo-kimyasal bir kesim yöntemidir. Çeliğin tutuşma sıcaklığına ısıtılmasının ardından yüksek basınçlı oksijen jeti, demiri yakarak (oksitleyerek) kesim yapar. Bu sayede kalın çelik levhalar bile temiz kenarlı kesilebilir.</p>

<p>Tekcan Metal''in CNC kontrollü oksijen kesim sistemi, manuel oksijen kesime göre çok daha hassas, tutarlı ve hızlı sonuçlar verir. CAD dosyasından doğrudan üretime geçen sistem, karmaşık geometrileri tam tekrarlanabilirlikle keser.</p>

<h2>Oksijen Kesim Hangi Durumda Tercih Edilir?</h2>
<p>Lazer kesim ile oksijen kesim arasındaki en önemli farklar:</p>

<table>
<thead><tr><th>Kriter</th><th>Lazer Kesim</th><th>Oksijen Kesim</th></tr></thead>
<tbody>
<tr><td>Kalınlık</td><td>0,5 - 25 mm</td><td>5 - 200 mm</td></tr>
<tr><td>Hassasiyet</td><td>±0,1 mm</td><td>±1 mm</td></tr>
<tr><td>Kesim Hızı</td><td>5-30 m/dk</td><td>0,3-2 m/dk</td></tr>
<tr><td>Birim Maliyet</td><td>Yüksek</td><td>Ekonomik</td></tr>
<tr><td>Tabla Boyutu</td><td>1500x3000 mm</td><td>3000x6000 mm</td></tr>
<tr><td>Malzeme</td><td>Karbon / Paslanmaz / Galvaniz / Alüminyum</td><td>Yalnız karbon çeliği</td></tr>
<tr><td>Kullanım Alanı</td><td>İnce, hassas iş</td><td>Ağır endüstri, kalın levha</td></tr>
</tbody>
</table>

<blockquote><p>Genel kural: 25 mm üzerine ihtiyaç duyduğunuzda oksijen kesim, 25 mm altı hassas iş için lazer kesim tercih edin. 5-25 mm arasında her iki yöntem de mümkündür; tolerans ve maliyet kriterlerine göre seçim yapılır.</p></blockquote>

<h2>Hangi Sektörlere Hizmet Veriyoruz?</h2>
<ul>
<li><strong>Çelik Konstrüksiyon:</strong> Köprü, hangar, fabrika çatısı için kalın yapı çeliği parçaları</li>
<li><strong>Gemi İnşa:</strong> Tersane uygulamaları için DH36, AH36 gemi sacı kesimi</li>
<li><strong>Basınçlı Kap İmalatı:</strong> Kazan, tank, silo gövde sacı (P265GH, P355GH)</li>
<li><strong>İş Makinası Üretimi:</strong> Excavator, dozer, vinç ana çelik şasi parçaları</li>
<li><strong>Maden ve Çimento:</strong> Aşınma plakası, paletler, taşıyıcı sistemler</li>
<li><strong>Petrokimya:</strong> Boru hattı flanşı, valf gövdesi (Kocaeli TÜPRAŞ yan sanayi)</li>
<li><strong>Demiryolu:</strong> Vagon ana şasi parçaları (Sakarya TÜVASAŞ yan sanayi)</li>
<li><strong>Sulama ve Altyapı:</strong> Büyük çaplı pompa baskıları, batık çelik parçalar</li>
</ul>

<h2>Kaynak Ağzı (Bevel Cutting) Hizmetleri</h2>
<p>CNC oksijen kesim sistemimiz, sadece düz kesim değil, eğimli kenar (kaynak ağzı) açma da yapabilir:</p>

<ul>
<li><strong>V Kaynak Ağzı:</strong> 30-45° tek taraflı eğim — yapı çeliği kaynaklarında</li>
<li><strong>Y Kaynak Ağzı:</strong> Çift taraflı, üst düz alt eğim — basınçlı kaplarda</li>
<li><strong>X Kaynak Ağzı:</strong> İki taraflı simetrik eğim — kalın levha tam penetrasyon</li>
<li><strong>I Düz Kesim:</strong> 90° dik kesim — standart uygulamalar</li>
</ul>

<p>Kaynak ağzı, ana levha kesim ücretine ek olarak ücretlendirilir. Projenizde kaç farklı tipte kaynak ağzı gerektiğini önceden belirtin, optimum maliyet planlaması yapalım.</p>

<h2>Sipariş ve Teslimat Süreci</h2>
<ol>
<li><strong>Çizim Gönderimi:</strong> DXF/DWG dosyanızı veya boyutlandırılmış teknik resminizi gönderin.</li>
<li><strong>Plaka Planlama:</strong> Mühendisimiz, dosyanızdaki parçaları büyük levhalarda nesting ile yerleştirerek malzeme firesini minimize eder.</li>
<li><strong>Teklif:</strong> 24 saat içinde detaylı teklif: malzeme + kesim + kaynak ağzı (varsa) + nakliye dahil.</li>
<li><strong>Üretim:</strong> Onay sonrası, projenin büyüklüğüne göre 2-7 iş günü içinde üretim tamamlanır.</li>
<li><strong>Kalite Kontrol:</strong> Her büyük parti için boyut ve kenar kalitesi kontrol edilir, gerekirse fotoğraf-rapor gönderilir.</li>
<li><strong>Sevkiyat:</strong> 6 metre üstü büyük parçalar için özel uzun yük sevkiyatı düzenlenir.</li>
</ol>

<h2>Sıkça Sorulan Sorular</h2>

<h3>Kaç metre kalın levha kesebiliyorsunuz?</h3>
<p>Maksimum 200 mm kalınlık ve 3000x6000 mm tabla boyutuna kadar kesim yapabiliyoruz. Daha büyük parçalar için iki ya da daha fazla parça halinde kesip, müşteriye birleştirme talimatı verebiliyoruz.</p>

<h3>Oksijen kesim toleransı neden lazer kadar hassas değil?</h3>
<p>Oksijen kesim, ısı kontrollü termo-kimyasal bir süreç olduğundan, kesim kenarında ısı etki bölgesi (HAZ) oluşur ve kerf genişliği lazere göre daha geniştir. Bu nedenle ±1 mm civarında tolerans normaldir. Hassasiyet kritik ise lazer kesim önerilir.</p>

<h3>Kalın levhada lazer mi oksijen mi seçmeliyim?</h3>
<p>25 mm üstü kalınlıklarda <strong>oksijen kesim daha ekonomik ve hızlıdır</strong>. Lazer kesim de 25 mm''ye kadar yapabilir ancak birim fiyat çok artar. Karar verirken tolerans ihtiyacınızı, parça sayısını ve bütçenizi birlikte değerlendirmek gerekir.</p>

<h3>Paslanmaz çelik kalın levhayı oksijen ile kesebilir misiniz?</h3>
<p>Hayır, oksijen kesim yalnızca karbon çeliği için uygundur. Paslanmaz çelik ve alaşım çelikler için plazma kesim veya tel erozyon önerilir.</p>'
WHERE slug = 'oksijen-kesim';

-- DEKORATIF-SACLAR
UPDATE tm_services SET
    title = 'Dekoratif Sac Üretimi',
    short_desc = 'Mimari ve dekoratif uygulamalar için özel desenli sac üretimi. Cephe paneli, korkuluk, mobilya aksesuarı, peyzaj elemanı — fikirden üretime tek atölye.',
    meta_title = 'Dekoratif Sac Üretimi | Tekcan Metal',
    meta_desc = 'Tekcan Metal dekoratif sac atölyesi — mimari cephe, korkuluk, mobilya aksesuarı, peyzaj elemanı. Lazer kesim + bükme + kaplama, özel desen tasarımı.',
    features = '["Mimari cephe panelleri (perforated, corten)","Korkuluk ve merdiven dekoratif paneller","Mobilya aksesuarı, ofis bölme paneli","Peyzaj eleman ve bahçe dekoru","Mağaza vitrin ve marka tabela uygulamaları","Özel desen tasarımı (CAD destekli)","Boya, elektrostatik toz boya, korten kaplama","Lazer + bükme + kaynak — tüm üretim tek atölyede","Mimar ve iç mimar danışmanlığı","3D görsel ile ön onay süreci"]',
    specs = '{"Üretim Yöntemi":"Lazer kesim + bükme + kaynak","Sac Kalınlığı":"1 - 8 mm","Maksimum Boyut":"1500 × 3000 mm","Yüzey İşleme":"Toz boya / Galvaniz / Korten / PVD","Desen":"Özel CAD tasarım","Termin Süresi":"5-15 iş günü","Görsel":"3D render ön onay","Garanti":"Üretim ve montaj garantili"}',
    description = '<p>Dekoratif sac, modern mimari ve iç tasarımın <em>en güçlü dilidir</em>. Tekcan Metal''in dekoratif sac atölyesi; mimari cephe panelleri, mağaza vitrini, korkuluk, mobilya aksesuarı ve peyzaj elemanları gibi geniş bir yelpazede özel üretim yapmaktadır. Konseptten 3D görsele, üretimden montaja kadar tek elden çözüm sunuyoruz.</p>

<h2>Dekoratif Sac Uygulama Alanları</h2>
<p>Dekoratif sac üretiminin kapsamı, sanayi tedariğinin çok ötesine geçer. Estetik, fonksiyon ve dayanıklılığı birleştiren özel tasarım uygulamalar:</p>

<h3>Mimari Uygulamalar</h3>
<ul>
<li><strong>Cephe Panelleri:</strong> Bina dış cephesinde perforated (delikli desen) ya da düz dekoratif paneller</li>
<li><strong>Korten Sac Cepheleri:</strong> Doğal pas dokulu, bakım gerektirmeyen yıllanmış görünüm</li>
<li><strong>Otopark Cephe Karkası:</strong> Hava sirkülasyonu sağlayan delikli sac uygulamaları</li>
<li><strong>Mağaza ve Showroom Vitrini:</strong> Marka kimliğine özel logolu, kesimli sac uygulamaları</li>
<li><strong>Lobi ve Resepsiyon Duvarı:</strong> Otel, plaza, hastane gibi prestijli mekanlar için vurgu duvarı</li>
</ul>

<h3>İç Mekan Uygulamaları</h3>
<ul>
<li><strong>Korkuluk ve Merdiven Panelleri:</strong> Cam yerine veya cam ile entegre geometrik desenli sac panelleri</li>
<li><strong>Ofis Bölme Panelleri:</strong> Mahremiyet sağlayan, akustik özellikli delikli saclar</li>
<li><strong>Tavan Akustik Panelleri:</strong> Restoran, çalışma alanı, konser salonu</li>
<li><strong>Mutfak ve Banyo Aksesuarı:</strong> Paslanmaz dekoratif raf, askılık, separatör</li>
<li><strong>Aydınlatma Armatür Gövdesi:</strong> Modern endüstriyel iç mekan lambası</li>
</ul>

<h3>Peyzaj ve Dış Mekan</h3>
<ul>
<li><strong>Bahçe Çiti ve Korkuluk:</strong> Korten ya da toz boyalı dekoratif sac panelleri</li>
<li><strong>Bahçe Heykeli ve Sanat Eseri:</strong> Lazer kesim ile özel form heykel</li>
<li><strong>Ev Numarası ve İsim Plakası:</strong> Modern paslanmaz veya korten plaka</li>
<li><strong>Şehir Mobilyası:</strong> Park bankı, çöp kutusu, bisiklet park yeri</li>
<li><strong>Aydınlatma Direği Aksesuarı:</strong> Klasik veya modern stil dekoratif kapak</li>
</ul>

<h2>Yüzey İşleme Seçenekleri</h2>
<p>Dekoratif sacın görünümünü ve dayanımını belirleyen yüzey işleme, her uygulama için ayrı seçilir:</p>

<table>
<thead><tr><th>Yüzey İşlemi</th><th>Görünüm</th><th>Önerilen Uygulama</th></tr></thead>
<tbody>
<tr><td>Elektrostatik Toz Boya</td><td>Mat veya parlak, tek renk</td><td>İç mekan, korkuluk, mobilya</td></tr>
<tr><td>Sıcak Daldırma Galvaniz</td><td>Gri parlak çinko kaplama</td><td>Dış mekan, şehir mobilyası</td></tr>
<tr><td>Korten Sac (COR-TEN)</td><td>Doğal kahverengi pas dokulu</td><td>Modern mimari cephe, peyzaj</td></tr>
<tr><td>PVD Kaplama (Altın/Bronz)</td><td>Premium parlak kaplama</td><td>Lüks otel, plaza, butik</td></tr>
<tr><td>Boyasız Paslanmaz</td><td>Doğal metalik, parlak</td><td>Mutfak, banyo, gıda alanı</td></tr>
<tr><td>Fırın Boya</td><td>Yüksek dayanımlı, parlak</td><td>Endüstriyel, dış kapı</td></tr>
</tbody>
</table>

<h2>Tasarımdan Üretime Süreç</h2>
<ol>
<li><strong>Konsept Görüşmesi:</strong> Mimari, iç mimar veya son kullanıcıyla yüz yüze veya online görüşme.</li>
<li><strong>Ölçü Alımı:</strong> Gerekirse keşif yapılır. Mevcut mekanın boyutları ve montaj noktaları belirlenir.</li>
<li><strong>Tasarım ve 3D Render:</strong> CAD ortamında özel desen tasarlanır, 3D render ile size sunulur.</li>
<li><strong>Onay ve Mockup:</strong> Büyük projelerde 1:1 ölçek mockup parça üretilir, fiziksel onay alınır.</li>
<li><strong>Üretim:</strong> Lazer kesim → bükme → kaynak → yüzey işleme zinciri tamamlanır.</li>
<li><strong>Kalite Kontrol:</strong> Her parça desen, ölçü ve yüzey kalitesi açısından denetlenir.</li>
<li><strong>Paketleme ve Sevkiyat:</strong> Hassas paketleme, gerekirse kasalı sevkiyat.</li>
<li><strong>Montaj Desteği:</strong> Talep üzerine montaj ekibimizle yerinde kurulum.</li>
</ol>

<blockquote><p>Tasarım danışmanlığı tamamen ücretsizdir. Aklınızdaki fikri taslak halinde getirin, mühendisimiz size üretilebilirlik analiziyle profesyonel tasarımı çıkarsın.</p></blockquote>

<h2>Korten Sac — Modern Mimarinin Yıldızı</h2>
<p>Korten sac (COR-TEN steel), atmosferik ortama maruz bırakıldığında yüzeyinde kontrollü ve durağan bir pas tabakası oluşturan özel alaşımlı bir çelik türüdür. İlk yıllarda kahverengi-turuncu tonlarda evrilir, 2-3 yıl içinde koyu kestane rengini alır. Bu pas tabakası, malzemeyi korozyona karşı korur — boya gerektirmez, bakımsızdır.</p>

<h3>Korten Sacın Avantajları</h3>
<ul>
<li><strong>Bakım Gerektirmez:</strong> Doğal yıllanma, ek koruma istenmez</li>
<li><strong>Modern Estetik:</strong> Çağdaş mimari ve peyzaj tasarımının en sevilen malzemesi</li>
<li><strong>Uzun Ömür:</strong> 80+ yıl ortalama ömür</li>
<li><strong>Çevre Dostu:</strong> Geri dönüştürülebilir, boya ve solvent kullanımı yok</li>
</ul>

<h3>Korten Sac Kullanımı</h3>
<ul>
<li>Modern villa cephesi ve giriş kapısı</li>
<li>Peyzaj sınırlama, çiçeklik kaplaması</li>
<li>Modern müze, kütüphane, kültür merkezi</li>
<li>Şehir mobilyası, park aydınlatması</li>
<li>Heykel ve dekoratif sanat eseri</li>
</ul>

<h2>Sıkça Sorulan Sorular</h2>

<h3>Tasarımım yok, sadece fikrim var. Ne yapmalıyım?</h3>
<p>Hiç sorun değil. Aklınızdaki fikri sözlü, eskiz, fotoğraf veya benzer örneklerle bize anlatın. Mimar/tasarımcı ekibimiz size 3-5 iş günü içinde 3D görselli tasarım sunsun. İlk konsept ücretsiz; revizyonlar ve detaylandırma süreciyle birlikte tasarım hizmeti ayrı kalemlendirilebilir.</p>

<h3>Korten sac gerçekten paslanır mı?</h3>
<p>Evet, ancak <strong>yıkıcı değil koruyucu</strong> bir pas. Yüzeyde oluşan dengeli oksit tabakası, alttaki malzemeye nem ve oksijen geçişini engeller. Bu nedenle hem güzel görünür, hem de uzun ömürlüdür.</p>

<h3>Toz boya yıllar içinde solar mı?</h3>
<p>Modern elektrostatik toz boyalar, dış mekan koşullarına göre formüle edilir ve UV stabilizatörlüdür. Genellikle 10-15 yıl boyunca canlı rengini korur. Ancak deniz kıyısı ve aşırı UV maruziyetinde birinci sınıf super-durable toz boya tercih edilmelidir.</p>

<h3>Montaj hizmeti veriyor musunuz?</h3>
<p>Evet, Konya ve çevre iller için kendi ekibimizle montaj yapıyoruz. Diğer iller için anlaşmalı uzman montaj ekiplerini koordine ediyoruz.</p>'
WHERE slug = 'dekoratif-saclar';


-- =====================================================
-- v1.0.56 — i18n ALTYAPISI (4 dil: TR/EN/AR/RU)
-- Path-based routing: /, /en/, /ar/, /ru/
-- =====================================================

-- 1) Translation tablosu (UI metinleri için key-value-lang)
CREATE TABLE IF NOT EXISTS tm_translations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(180) NOT NULL,
  lang ENUM('tr','en','ar','ru') NOT NULL DEFAULT 'tr',
  value TEXT NULL,
  context VARCHAR(60) NULL COMMENT 'header/footer/forms/buttons/etc',
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_key_lang (`key`, lang),
  KEY idx_lang (lang),
  KEY idx_context (context)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) İçerik tablolarına dil kolonları (idempotent ALTER)
-- Her tablo için INFORMATION_SCHEMA-protected ALTER pattern

-- ===== tm_settings =====
-- (key-value yapısı zaten var, dil eklenmez. Site adı/desc gibi tek metinler
-- tm_translations'a setting.X.lang olarak yazılabilir)

-- ===== tm_pages =====
SET @t = 'tm_pages';

-- title_en, title_ar, title_ru
SET @c = 'title_en'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, CONCAT('ALTER TABLE ', @t, ' ADD COLUMN title_en VARCHAR(200) NULL AFTER title'), 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = 'title_ar'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN title_ar VARCHAR(200) NULL AFTER title_en', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = 'title_ru'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN title_ru VARCHAR(200) NULL AFTER title_ar', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- subtitle_en/ar/ru
SET @c = 'subtitle_en'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN subtitle_en VARCHAR(255) NULL AFTER subtitle', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'subtitle_ar'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN subtitle_ar VARCHAR(255) NULL AFTER subtitle_en', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'subtitle_ru'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN subtitle_ru VARCHAR(255) NULL AFTER subtitle_ar', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- content_en/ar/ru
SET @c = 'content_en'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN content_en LONGTEXT NULL AFTER content', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'content_ar'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN content_ar LONGTEXT NULL AFTER content_en', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'content_ru'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN content_ru LONGTEXT NULL AFTER content_ar', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- meta_title/desc EN/AR/RU
SET @c = 'meta_title_en'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN meta_title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'meta_title_ar'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN meta_title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'meta_title_ru'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN meta_title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @c = 'meta_desc_en'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN meta_desc_en VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'meta_desc_ar'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN meta_desc_ar VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @c = 'meta_desc_ru'; SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_pages' AND COLUMN_NAME = @c);
SET @sql = IF(@e=0, 'ALTER TABLE tm_pages ADD COLUMN meta_desc_ru VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;


-- ===== tm_sliders =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN title_en VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN title_ar VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN title_ru VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'subtitle_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN subtitle_en VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'subtitle_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN subtitle_ar VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'subtitle_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN subtitle_ru VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN description_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN description_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN description_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'link_text_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN link_text_en VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'link_text_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN link_text_ar VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_sliders' AND COLUMN_NAME = 'link_text_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_sliders ADD COLUMN link_text_ru VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_categories =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'name_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN name_en VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'name_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN name_ar VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'name_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN name_ru VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'short_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN short_desc_en VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'short_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN short_desc_ar VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'short_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN short_desc_ru VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN description_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN description_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN description_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'meta_title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN meta_title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'meta_title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN meta_title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'meta_title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN meta_title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'meta_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN meta_desc_en VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'meta_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN meta_desc_ar VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_categories' AND COLUMN_NAME = 'meta_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_categories ADD COLUMN meta_desc_ru VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_products =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'short_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN short_desc_en VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'short_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN short_desc_ar VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'short_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN short_desc_ru VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN description_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN description_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN description_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'specs_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN specs_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'specs_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN specs_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'specs_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN specs_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'meta_title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN meta_title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'meta_title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN meta_title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'meta_title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN meta_title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'meta_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN meta_desc_en VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'meta_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN meta_desc_ar VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_products' AND COLUMN_NAME = 'meta_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_products ADD COLUMN meta_desc_ru VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_services =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'short_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN short_desc_en VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'short_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN short_desc_ar VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'short_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN short_desc_ru VARCHAR(400) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN description_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN description_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN description_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'features_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN features_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'features_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN features_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'features_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN features_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'specs_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN specs_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'specs_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN specs_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'specs_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN specs_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'meta_title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN meta_title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'meta_title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN meta_title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'meta_title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN meta_title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'meta_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN meta_desc_en VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'meta_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN meta_desc_ar VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_services' AND COLUMN_NAME = 'meta_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_services ADD COLUMN meta_desc_ru VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_partners =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_partners' AND COLUMN_NAME = 'name_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_partners ADD COLUMN name_en VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_partners' AND COLUMN_NAME = 'name_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_partners ADD COLUMN name_ar VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_partners' AND COLUMN_NAME = 'name_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_partners ADD COLUMN name_ru VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_partners' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_partners ADD COLUMN description_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_partners' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_partners ADD COLUMN description_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_partners' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_partners ADD COLUMN description_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_banks ===== (v1.0.74 hotfix — eksik ALTER eklendi)
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_banks' AND COLUMN_NAME = 'bank_name_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_banks ADD COLUMN bank_name_en VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_banks' AND COLUMN_NAME = 'bank_name_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_banks ADD COLUMN bank_name_ar VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_banks' AND COLUMN_NAME = 'bank_name_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_banks ADD COLUMN bank_name_ru VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_banks' AND COLUMN_NAME = 'branch_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_banks ADD COLUMN branch_en VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_banks' AND COLUMN_NAME = 'branch_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_banks ADD COLUMN branch_ar VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_banks' AND COLUMN_NAME = 'branch_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_banks ADD COLUMN branch_ru VARCHAR(150) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_faq =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'question_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN question_en VARCHAR(500) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'question_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN question_ar VARCHAR(500) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'question_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN question_ru VARCHAR(500) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'answer_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN answer_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'answer_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN answer_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'answer_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN answer_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'category_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN category_en VARCHAR(80) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'category_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN category_ar VARCHAR(80) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_faq' AND COLUMN_NAME = 'category_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_faq ADD COLUMN category_ru VARCHAR(80) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_blog_categories =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_categories' AND COLUMN_NAME = 'name_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_categories ADD COLUMN name_en VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_categories' AND COLUMN_NAME = 'name_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_categories ADD COLUMN name_ar VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_categories' AND COLUMN_NAME = 'name_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_categories ADD COLUMN name_ru VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_categories' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_categories ADD COLUMN description_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_categories' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_categories ADD COLUMN description_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_categories' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_categories ADD COLUMN description_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_blog_posts =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN title_en VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN title_ar VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN title_ru VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'excerpt_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN excerpt_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'excerpt_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN excerpt_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'excerpt_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN excerpt_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'content_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN content_en LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'content_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN content_ar LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'content_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN content_ru LONGTEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'meta_title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN meta_title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'meta_title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN meta_title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'meta_title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN meta_title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'meta_desc_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN meta_desc_en VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'meta_desc_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN meta_desc_ar VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_blog_posts' AND COLUMN_NAME = 'meta_desc_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_blog_posts ADD COLUMN meta_desc_ru VARCHAR(300) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_gallery_albums =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_gallery_albums' AND COLUMN_NAME = 'title_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_gallery_albums ADD COLUMN title_en VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_gallery_albums' AND COLUMN_NAME = 'title_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_gallery_albums ADD COLUMN title_ar VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_gallery_albums' AND COLUMN_NAME = 'title_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_gallery_albums ADD COLUMN title_ru VARCHAR(200) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_gallery_albums' AND COLUMN_NAME = 'description_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_gallery_albums ADD COLUMN description_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_gallery_albums' AND COLUMN_NAME = 'description_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_gallery_albums ADD COLUMN description_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_gallery_albums' AND COLUMN_NAME = 'description_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_gallery_albums ADD COLUMN description_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_seo_iller =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'intro_text_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN intro_text_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'intro_text_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN intro_text_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'intro_text_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN intro_text_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'cargo_info_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN cargo_info_en VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'cargo_info_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN cargo_info_ar VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'cargo_info_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN cargo_info_ru VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'industry_focus_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN industry_focus_en VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'industry_focus_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN industry_focus_ar VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_iller' AND COLUMN_NAME = 'industry_focus_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_iller ADD COLUMN industry_focus_ru VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- ===== tm_seo_ulkeler =====
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_ulkeler' AND COLUMN_NAME = 'intro_text_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_ulkeler ADD COLUMN intro_text_en TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_ulkeler' AND COLUMN_NAME = 'intro_text_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_ulkeler ADD COLUMN intro_text_ar TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_ulkeler' AND COLUMN_NAME = 'intro_text_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_ulkeler ADD COLUMN intro_text_ru TEXT NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_ulkeler' AND COLUMN_NAME = 'cargo_info_en');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_ulkeler ADD COLUMN cargo_info_en VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_ulkeler' AND COLUMN_NAME = 'cargo_info_ar');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_ulkeler ADD COLUMN cargo_info_ar VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @e = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_seo_ulkeler' AND COLUMN_NAME = 'cargo_info_ru');
SET @sql = IF(@e=0, 'ALTER TABLE tm_seo_ulkeler ADD COLUMN cargo_info_ru VARCHAR(255) NULL', 'SELECT 1');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;


-- =====================================================
-- v1.0.57 — UI METİNLERİ TOPLU ÇEVİRİ SEED
-- 225 metin × 4 dil = 900 kayıt
-- =====================================================

INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('header.menu.products', 'tr', 'Ürün Gruplarımız', 'header'),
  ('header.menu.products', 'en', 'Product Groups', 'header'),
  ('header.menu.products', 'ar', 'مجموعات المنتجات', 'header'),
  ('header.menu.products', 'ru', 'Группы продукции', 'header'),
  ('header.menu.services', 'tr', 'Hizmetlerimiz', 'header'),
  ('header.menu.services', 'en', 'Our Services', 'header'),
  ('header.menu.services', 'ar', 'خدماتنا', 'header'),
  ('header.menu.services', 'ru', 'Наши услуги', 'header'),
  ('header.menu.calculator', 'tr', 'Ağırlık Hesaplama', 'header'),
  ('header.menu.calculator', 'en', 'Weight Calculator', 'header'),
  ('header.menu.calculator', 'ar', 'حاسبة الوزن', 'header'),
  ('header.menu.calculator', 'ru', 'Калькулятор веса', 'header'),
  ('header.menu.corporate', 'tr', 'Kurumsal', 'header'),
  ('header.menu.corporate', 'en', 'Corporate', 'header'),
  ('header.menu.corporate', 'ar', 'عن الشركة', 'header'),
  ('header.menu.corporate', 'ru', 'О компании', 'header'),
  ('header.menu.about', 'tr', 'Hakkımızda', 'header'),
  ('header.menu.about', 'en', 'About Us', 'header'),
  ('header.menu.about', 'ar', 'من نحن', 'header'),
  ('header.menu.about', 'ru', 'О нас', 'header'),
  ('header.menu.partners', 'tr', 'Çözüm Ortakları', 'header'),
  ('header.menu.partners', 'en', 'Solution Partners', 'header'),
  ('header.menu.partners', 'ar', 'شركاء الحلول', 'header'),
  ('header.menu.partners', 'ru', 'Партнёры по решениям', 'header'),
  ('header.menu.iban', 'tr', 'IBAN Bilgilerimiz', 'header'),
  ('header.menu.iban', 'en', 'Bank Account Info', 'header'),
  ('header.menu.iban', 'ar', 'معلومات الحساب البنكي', 'header'),
  ('header.menu.iban', 'ru', 'Банковские реквизиты', 'header'),
  ('header.menu.mail_order', 'tr', 'Mail Order Formu', 'header'),
  ('header.menu.mail_order', 'en', 'Mail Order Form', 'header'),
  ('header.menu.mail_order', 'ar', 'نموذج الطلب البريدي', 'header'),
  ('header.menu.mail_order', 'ru', 'Бланк заказа по почте', 'header'),
  ('header.menu.loyalty', 'tr', 'Sadakat Programı', 'header'),
  ('header.menu.loyalty', 'en', 'Loyalty Program', 'header'),
  ('header.menu.loyalty', 'ar', 'برنامج الولاء', 'header'),
  ('header.menu.loyalty', 'ru', 'Программа лояльности', 'header'),
  ('header.menu.faq', 'tr', 'Sıkça Sorulan Sorular', 'header'),
  ('header.menu.faq', 'en', 'Frequently Asked Questions', 'header'),
  ('header.menu.faq', 'ar', 'الأسئلة الشائعة', 'header'),
  ('header.menu.faq', 'ru', 'Часто задаваемые вопросы', 'header'),
  ('header.menu.news', 'tr', 'Haberler & Basın', 'header'),
  ('header.menu.news', 'en', 'News & Press', 'header'),
  ('header.menu.news', 'ar', 'الأخبار والصحافة', 'header'),
  ('header.menu.news', 'ru', 'Новости и пресса', 'header'),
  ('header.menu.gallery', 'tr', 'Foto Galeri', 'header'),
  ('header.menu.gallery', 'en', 'Photo Gallery', 'header'),
  ('header.menu.gallery', 'ar', 'معرض الصور', 'header'),
  ('header.menu.gallery', 'ru', 'Фотогалерея', 'header'),
  ('header.menu.blog', 'tr', 'Tekcan''dan Haberler', 'header'),
  ('header.menu.blog', 'en', 'News from Tekcan', 'header'),
  ('header.menu.blog', 'ar', 'أخبار من تكجان', 'header'),
  ('header.menu.blog', 'ru', 'Новости от Tekcan', 'header'),
  ('header.menu.contact', 'tr', 'İletişim', 'header'),
  ('header.menu.contact', 'en', 'Contact', 'header'),
  ('header.menu.contact', 'ar', 'اتصل بنا', 'header'),
  ('header.menu.contact', 'ru', 'Контакты', 'header'),
  ('header.menu.home', 'tr', 'Anasayfa', 'header'),
  ('header.menu.home', 'en', 'Home', 'header'),
  ('header.menu.home', 'ar', 'الصفحة الرئيسية', 'header'),
  ('header.menu.home', 'ru', 'Главная', 'header'),
  ('header.menu_label', 'tr', 'Menü', 'header'),
  ('header.menu_label', 'en', 'Menu', 'header'),
  ('header.menu_label', 'ar', 'القائمة', 'header'),
  ('header.menu_label', 'ru', 'Меню', 'header'),
  ('header.tagline', 'tr', 'Demir adına Herşey', 'header'),
  ('header.tagline', 'en', 'Everything for Steel', 'header'),
  ('header.tagline', 'ar', 'كل ما يخص الحديد', 'header'),
  ('header.tagline', 'ru', 'Всё для металла', 'header'),
  ('header.lang_label', 'tr', 'Dil', 'header'),
  ('header.lang_label', 'en', 'Language', 'header'),
  ('header.lang_label', 'ar', 'اللغة', 'header'),
  ('header.lang_label', 'ru', 'Язык', 'header'),
  ('footer.tagline', 'tr', 'Demir adına Herşey…', 'footer'),
  ('footer.tagline', 'en', 'Everything for Steel…', 'footer'),
  ('footer.tagline', 'ar', 'كل ما يخص الحديد…', 'footer'),
  ('footer.tagline', 'ru', 'Всё для металла…', 'footer'),
  ('footer.about_short', 'tr', 'Ticaret ile bitmeyen bir hikâye.', 'footer'),
  ('footer.about_short', 'en', 'A story that does not end with trade.', 'footer'),
  ('footer.about_short', 'ar', 'قصة لا تنتهي بالتجارة.', 'footer'),
  ('footer.about_short', 'ru', 'История, которая не заканчивается торговлей.', 'footer'),
  ('footer.quick_access', 'tr', 'Hızlı Erişim', 'footer'),
  ('footer.quick_access', 'en', 'Quick Access', 'footer'),
  ('footer.quick_access', 'ar', 'الوصول السريع', 'footer'),
  ('footer.quick_access', 'ru', 'Быстрый доступ', 'footer'),
  ('footer.products', 'tr', 'Tüm Ürünler', 'footer'),
  ('footer.products', 'en', 'All Products', 'footer'),
  ('footer.products', 'ar', 'جميع المنتجات', 'footer'),
  ('footer.products', 'ru', 'Все продукты', 'footer'),
  ('footer.kvkk', 'tr', 'KVKK Aydınlatma', 'footer'),
  ('footer.kvkk', 'en', 'Privacy Notice (KVKK)', 'footer'),
  ('footer.kvkk', 'ar', 'إشعار الخصوصية', 'footer'),
  ('footer.kvkk', 'ru', 'Уведомление о конфиденциальности', 'footer'),
  ('footer.kvkk_short', 'tr', 'KVKK', 'footer'),
  ('footer.kvkk_short', 'en', 'Privacy', 'footer'),
  ('footer.kvkk_short', 'ar', 'الخصوصية', 'footer'),
  ('footer.kvkk_short', 'ru', 'Конфиденциальность', 'footer'),
  ('footer.cookie_policy', 'tr', 'Çerez Politikası', 'footer'),
  ('footer.cookie_policy', 'en', 'Cookie Policy', 'footer'),
  ('footer.cookie_policy', 'ar', 'سياسة ملفات تعريف الارتباط', 'footer'),
  ('footer.cookie_policy', 'ru', 'Политика cookie', 'footer'),
  ('footer.working_hours', 'tr', 'Çalışma Saatleri', 'footer'),
  ('footer.working_hours', 'en', 'Working Hours', 'footer'),
  ('footer.working_hours', 'ar', 'ساعات العمل', 'footer'),
  ('footer.working_hours', 'ru', 'Часы работы', 'footer'),
  ('footer.weekdays', 'tr', 'Pazartesi – Cuma', 'footer'),
  ('footer.weekdays', 'en', 'Monday – Friday', 'footer'),
  ('footer.weekdays', 'ar', 'الإثنين – الجمعة', 'footer'),
  ('footer.weekdays', 'ru', 'Понедельник – Пятница', 'footer'),
  ('footer.saturday', 'tr', 'Cumartesi', 'footer'),
  ('footer.saturday', 'en', 'Saturday', 'footer'),
  ('footer.saturday', 'ar', 'السبت', 'footer'),
  ('footer.saturday', 'ru', 'Суббота', 'footer'),
  ('footer.sunday', 'tr', 'Pazar', 'footer'),
  ('footer.sunday', 'en', 'Sunday', 'footer'),
  ('footer.sunday', 'ar', 'الأحد', 'footer'),
  ('footer.sunday', 'ru', 'Воскресенье', 'footer'),
  ('footer.closed', 'tr', 'Kapalı', 'footer'),
  ('footer.closed', 'en', 'Closed', 'footer'),
  ('footer.closed', 'ar', 'مغلق', 'footer'),
  ('footer.closed', 'ru', 'Закрыто', 'footer'),
  ('footer.contact', 'tr', 'İletişim', 'footer'),
  ('footer.contact', 'en', 'Contact', 'footer'),
  ('footer.contact', 'ar', 'اتصل بنا', 'footer'),
  ('footer.contact', 'ru', 'Контакты', 'footer'),
  ('footer.address', 'tr', 'Adres', 'footer'),
  ('footer.address', 'en', 'Address', 'footer'),
  ('footer.address', 'ar', 'العنوان', 'footer'),
  ('footer.address', 'ru', 'Адрес', 'footer'),
  ('footer.phone', 'tr', 'Telefon', 'footer'),
  ('footer.phone', 'en', 'Phone', 'footer'),
  ('footer.phone', 'ar', 'الهاتف', 'footer'),
  ('footer.phone', 'ru', 'Телефон', 'footer'),
  ('footer.email', 'tr', 'E-Posta', 'footer'),
  ('footer.email', 'en', 'Email', 'footer'),
  ('footer.email', 'ar', 'البريد الإلكتروني', 'footer'),
  ('footer.email', 'ru', 'Эл. почта', 'footer'),
  ('footer.copyright', 'tr', 'Tüm hakları saklıdır', 'footer'),
  ('footer.copyright', 'en', 'All rights reserved', 'footer'),
  ('footer.copyright', 'ar', 'جميع الحقوق محفوظة', 'footer'),
  ('footer.copyright', 'ru', 'Все права защищены', 'footer'),
  ('footer.design_by', 'tr', 'Tasarım', 'footer'),
  ('footer.design_by', 'en', 'Design', 'footer'),
  ('footer.design_by', 'ar', 'التصميم', 'footer'),
  ('footer.design_by', 'ru', 'Дизайн', 'footer'),
  ('btn.read_more', 'tr', 'Devamını Oku', 'buttons'),
  ('btn.read_more', 'en', 'Read More', 'buttons'),
  ('btn.read_more', 'ar', 'اقرأ المزيد', 'buttons'),
  ('btn.read_more', 'ru', 'Читать далее', 'buttons'),
  ('btn.detail', 'tr', 'Detay', 'buttons'),
  ('btn.detail', 'en', 'Detail', 'buttons'),
  ('btn.detail', 'ar', 'التفاصيل', 'buttons'),
  ('btn.detail', 'ru', 'Подробнее', 'buttons'),
  ('btn.detail_examine', 'tr', 'Detaylı İncele', 'buttons'),
  ('btn.detail_examine', 'en', 'Examine in Detail', 'buttons'),
  ('btn.detail_examine', 'ar', 'استكشف بالتفصيل', 'buttons'),
  ('btn.detail_examine', 'ru', 'Подробнее', 'buttons'),
  ('btn.contact_us', 'tr', 'İletişime Geç', 'buttons'),
  ('btn.contact_us', 'en', 'Contact Us', 'buttons'),
  ('btn.contact_us', 'ar', 'اتصل بنا', 'buttons'),
  ('btn.contact_us', 'ru', 'Связаться с нами', 'buttons'),
  ('btn.get_quote', 'tr', 'Teklif Al', 'buttons'),
  ('btn.get_quote', 'en', 'Get a Quote', 'buttons'),
  ('btn.get_quote', 'ar', 'احصل على عرض', 'buttons'),
  ('btn.get_quote', 'ru', 'Получить предложение', 'buttons'),
  ('btn.send', 'tr', 'Gönder', 'buttons'),
  ('btn.send', 'en', 'Send', 'buttons'),
  ('btn.send', 'ar', 'إرسال', 'buttons'),
  ('btn.send', 'ru', 'Отправить', 'buttons'),
  ('btn.submit', 'tr', 'Onayla', 'buttons'),
  ('btn.submit', 'en', 'Submit', 'buttons'),
  ('btn.submit', 'ar', 'تأكيد', 'buttons'),
  ('btn.submit', 'ru', 'Подтвердить', 'buttons'),
  ('btn.cancel', 'tr', 'İptal', 'buttons'),
  ('btn.cancel', 'en', 'Cancel', 'buttons'),
  ('btn.cancel', 'ar', 'إلغاء', 'buttons'),
  ('btn.cancel', 'ru', 'Отмена', 'buttons'),
  ('btn.back', 'tr', 'Geri', 'buttons'),
  ('btn.back', 'en', 'Back', 'buttons'),
  ('btn.back', 'ar', 'رجوع', 'buttons'),
  ('btn.back', 'ru', 'Назад', 'buttons'),
  ('btn.next', 'tr', 'İleri', 'buttons'),
  ('btn.next', 'en', 'Next', 'buttons'),
  ('btn.next', 'ar', 'التالي', 'buttons'),
  ('btn.next', 'ru', 'Далее', 'buttons'),
  ('btn.previous', 'tr', 'Önceki', 'buttons'),
  ('btn.previous', 'en', 'Previous', 'buttons'),
  ('btn.previous', 'ar', 'السابق', 'buttons'),
  ('btn.previous', 'ru', 'Предыдущий', 'buttons'),
  ('btn.show_all', 'tr', 'Tümünü Göster', 'buttons'),
  ('btn.show_all', 'en', 'Show All', 'buttons'),
  ('btn.show_all', 'ar', 'عرض الكل', 'buttons'),
  ('btn.show_all', 'ru', 'Показать все', 'buttons'),
  ('btn.calculate', 'tr', 'Hesapla', 'buttons'),
  ('btn.calculate', 'en', 'Calculate', 'buttons'),
  ('btn.calculate', 'ar', 'احسب', 'buttons'),
  ('btn.calculate', 'ru', 'Рассчитать', 'buttons'),
  ('btn.search', 'tr', 'Ara', 'buttons'),
  ('btn.search', 'en', 'Search', 'buttons'),
  ('btn.search', 'ar', 'بحث', 'buttons'),
  ('btn.search', 'ru', 'Поиск', 'buttons'),
  ('btn.filter', 'tr', 'Filtrele', 'buttons'),
  ('btn.filter', 'en', 'Filter', 'buttons'),
  ('btn.filter', 'ar', 'تصفية', 'buttons'),
  ('btn.filter', 'ru', 'Фильтр', 'buttons'),
  ('btn.download', 'tr', 'İndir', 'buttons'),
  ('btn.download', 'en', 'Download', 'buttons'),
  ('btn.download', 'ar', 'تنزيل', 'buttons'),
  ('btn.download', 'ru', 'Скачать', 'buttons'),
  ('btn.upload', 'tr', 'Yükle', 'buttons'),
  ('btn.upload', 'en', 'Upload', 'buttons'),
  ('btn.upload', 'ar', 'تحميل', 'buttons'),
  ('btn.upload', 'ru', 'Загрузить', 'buttons'),
  ('btn.contact_form', 'tr', 'İletişim Formu', 'buttons'),
  ('btn.contact_form', 'en', 'Contact Form', 'buttons'),
  ('btn.contact_form', 'ar', 'نموذج الاتصال', 'buttons'),
  ('btn.contact_form', 'ru', 'Контактная форма', 'buttons'),
  ('btn.whatsapp', 'tr', 'WhatsApp', 'buttons'),
  ('btn.whatsapp', 'en', 'WhatsApp', 'buttons'),
  ('btn.whatsapp', 'ar', 'واتساب', 'buttons'),
  ('btn.whatsapp', 'ru', 'WhatsApp', 'buttons'),
  ('btn.whatsapp_quote', 'tr', 'WhatsApp Teklif', 'buttons'),
  ('btn.whatsapp_quote', 'en', 'WhatsApp Quote', 'buttons'),
  ('btn.whatsapp_quote', 'ar', 'عرض عبر واتساب', 'buttons'),
  ('btn.whatsapp_quote', 'ru', 'Запрос в WhatsApp', 'buttons'),
  ('btn.email', 'tr', 'E-Posta', 'buttons'),
  ('btn.email', 'en', 'Email', 'buttons'),
  ('btn.email', 'ar', 'البريد الإلكتروني', 'buttons'),
  ('btn.email', 'ru', 'Эл. почта', 'buttons'),
  ('btn.show_more', 'tr', 'Daha Fazla Göster', 'buttons'),
  ('btn.show_more', 'en', 'Show More', 'buttons'),
  ('btn.show_more', 'ar', 'عرض المزيد', 'buttons'),
  ('btn.show_more', 'ru', 'Показать больше', 'buttons'),
  ('form.name', 'tr', 'Ad Soyad', 'forms'),
  ('form.name', 'en', 'Full Name', 'forms'),
  ('form.name', 'ar', 'الاسم الكامل', 'forms'),
  ('form.name', 'ru', 'Полное имя', 'forms'),
  ('form.firstname', 'tr', 'Ad', 'forms'),
  ('form.firstname', 'en', 'First Name', 'forms'),
  ('form.firstname', 'ar', 'الاسم', 'forms'),
  ('form.firstname', 'ru', 'Имя', 'forms'),
  ('form.lastname', 'tr', 'Soyad', 'forms'),
  ('form.lastname', 'en', 'Last Name', 'forms'),
  ('form.lastname', 'ar', 'الكنية', 'forms'),
  ('form.lastname', 'ru', 'Фамилия', 'forms'),
  ('form.company', 'tr', 'Şirket', 'forms'),
  ('form.company', 'en', 'Company', 'forms'),
  ('form.company', 'ar', 'الشركة', 'forms'),
  ('form.company', 'ru', 'Компания', 'forms'),
  ('form.title', 'tr', 'Konu', 'forms'),
  ('form.title', 'en', 'Subject', 'forms'),
  ('form.title', 'ar', 'الموضوع', 'forms'),
  ('form.title', 'ru', 'Тема', 'forms'),
  ('form.email', 'tr', 'E-Posta', 'forms'),
  ('form.email', 'en', 'Email', 'forms'),
  ('form.email', 'ar', 'البريد الإلكتروني', 'forms'),
  ('form.email', 'ru', 'Эл. почта', 'forms'),
  ('form.phone', 'tr', 'Telefon', 'forms'),
  ('form.phone', 'en', 'Phone', 'forms'),
  ('form.phone', 'ar', 'الهاتف', 'forms'),
  ('form.phone', 'ru', 'Телефон', 'forms'),
  ('form.message', 'tr', 'Mesaj', 'forms'),
  ('form.message', 'en', 'Message', 'forms'),
  ('form.message', 'ar', 'الرسالة', 'forms'),
  ('form.message', 'ru', 'Сообщение', 'forms'),
  ('form.address', 'tr', 'Adres', 'forms'),
  ('form.address', 'en', 'Address', 'forms'),
  ('form.address', 'ar', 'العنوان', 'forms'),
  ('form.address', 'ru', 'Адрес', 'forms'),
  ('form.city', 'tr', 'Şehir', 'forms'),
  ('form.city', 'en', 'City', 'forms'),
  ('form.city', 'ar', 'المدينة', 'forms'),
  ('form.city', 'ru', 'Город', 'forms'),
  ('form.country', 'tr', 'Ülke', 'forms'),
  ('form.country', 'en', 'Country', 'forms'),
  ('form.country', 'ar', 'الدولة', 'forms'),
  ('form.country', 'ru', 'Страна', 'forms'),
  ('form.tax_office', 'tr', 'Vergi Dairesi', 'forms'),
  ('form.tax_office', 'en', 'Tax Office', 'forms'),
  ('form.tax_office', 'ar', 'مكتب الضرائب', 'forms'),
  ('form.tax_office', 'ru', 'Налоговая инспекция', 'forms'),
  ('form.tax_no', 'tr', 'Vergi No', 'forms'),
  ('form.tax_no', 'en', 'Tax Number', 'forms'),
  ('form.tax_no', 'ar', 'الرقم الضريبي', 'forms'),
  ('form.tax_no', 'ru', 'Налоговый номер', 'forms'),
  ('form.kvkk_consent', 'tr', 'KVKK Aydınlatma Metni''ni okudum, kabul ediyorum.', 'forms'),
  ('form.kvkk_consent', 'en', 'I have read and accept the Privacy Notice.', 'forms'),
  ('form.kvkk_consent', 'ar', 'لقد قرأت إشعار الخصوصية وأوافق عليه.', 'forms'),
  ('form.kvkk_consent', 'ru', 'Я прочитал и принимаю Уведомление о конфиденциальности.', 'forms'),
  ('form.required', 'tr', 'Zorunlu alan', 'forms'),
  ('form.required', 'en', 'Required field', 'forms'),
  ('form.required', 'ar', 'حقل مطلوب', 'forms'),
  ('form.required', 'ru', 'Обязательное поле', 'forms'),
  ('form.invalid_email', 'tr', 'Geçersiz e-posta', 'forms'),
  ('form.invalid_email', 'en', 'Invalid email', 'forms'),
  ('form.invalid_email', 'ar', 'بريد إلكتروني غير صالح', 'forms'),
  ('form.invalid_email', 'ru', 'Неверный адрес эл. почты', 'forms'),
  ('form.invalid_phone', 'tr', 'Geçersiz telefon', 'forms'),
  ('form.invalid_phone', 'en', 'Invalid phone', 'forms'),
  ('form.invalid_phone', 'ar', 'هاتف غير صالح', 'forms'),
  ('form.invalid_phone', 'ru', 'Неверный телефон', 'forms'),
  ('form.success', 'tr', 'Mesajınız başarıyla gönderildi.', 'forms'),
  ('form.success', 'en', 'Your message was sent successfully.', 'forms'),
  ('form.success', 'ar', 'تم إرسال رسالتك بنجاح.', 'forms'),
  ('form.success', 'ru', 'Ваше сообщение успешно отправлено.', 'forms'),
  ('form.error', 'tr', 'Bir hata oluştu, lütfen tekrar deneyin.', 'forms'),
  ('form.error', 'en', 'An error occurred, please try again.', 'forms'),
  ('form.error', 'ar', 'حدث خطأ، يرجى المحاولة مرة أخرى.', 'forms'),
  ('form.error', 'ru', 'Произошла ошибка, попробуйте ещё раз.', 'forms'),
  ('breadcrumb.home', 'tr', 'Anasayfa', 'breadcrumb'),
  ('breadcrumb.home', 'en', 'Home', 'breadcrumb'),
  ('breadcrumb.home', 'ar', 'الصفحة الرئيسية', 'breadcrumb'),
  ('breadcrumb.home', 'ru', 'Главная', 'breadcrumb'),
  ('breadcrumb.products', 'tr', 'Ürünler', 'breadcrumb'),
  ('breadcrumb.products', 'en', 'Products', 'breadcrumb'),
  ('breadcrumb.products', 'ar', 'المنتجات', 'breadcrumb'),
  ('breadcrumb.products', 'ru', 'Продукты', 'breadcrumb'),
  ('breadcrumb.services', 'tr', 'Endüstriyel Yetkinlikler', 'breadcrumb'),
  ('breadcrumb.services', 'en', 'Industrial Capabilities', 'breadcrumb'),
  ('breadcrumb.services', 'ar', 'القدرات الصناعية', 'breadcrumb'),
  ('breadcrumb.services', 'ru', 'Промышленные возможности', 'breadcrumb'),
  ('breadcrumb.blog', 'tr', 'Blog', 'breadcrumb'),
  ('breadcrumb.blog', 'en', 'Blog', 'breadcrumb'),
  ('breadcrumb.blog', 'ar', 'المدونة', 'breadcrumb'),
  ('breadcrumb.blog', 'ru', 'Блог', 'breadcrumb'),
  ('breadcrumb.gallery', 'tr', 'Galeri', 'breadcrumb'),
  ('breadcrumb.gallery', 'en', 'Gallery', 'breadcrumb'),
  ('breadcrumb.gallery', 'ar', 'المعرض', 'breadcrumb'),
  ('breadcrumb.gallery', 'ru', 'Галерея', 'breadcrumb'),
  ('label.products', 'tr', 'Ürünler', 'labels'),
  ('label.products', 'en', 'Products', 'labels'),
  ('label.products', 'ar', 'المنتجات', 'labels'),
  ('label.products', 'ru', 'Продукты', 'labels'),
  ('label.product', 'tr', 'Ürün', 'labels'),
  ('label.product', 'en', 'Product', 'labels'),
  ('label.product', 'ar', 'منتج', 'labels'),
  ('label.product', 'ru', 'Продукт', 'labels'),
  ('label.services', 'tr', 'Hizmetler', 'labels'),
  ('label.services', 'en', 'Services', 'labels'),
  ('label.services', 'ar', 'الخدمات', 'labels'),
  ('label.services', 'ru', 'Услуги', 'labels'),
  ('label.service', 'tr', 'Hizmet', 'labels'),
  ('label.service', 'en', 'Service', 'labels'),
  ('label.service', 'ar', 'خدمة', 'labels'),
  ('label.service', 'ru', 'Услуга', 'labels'),
  ('label.category', 'tr', 'Kategori', 'labels'),
  ('label.category', 'en', 'Category', 'labels'),
  ('label.category', 'ar', 'الفئة', 'labels'),
  ('label.category', 'ru', 'Категория', 'labels'),
  ('label.categories', 'tr', 'Kategoriler', 'labels'),
  ('label.categories', 'en', 'Categories', 'labels'),
  ('label.categories', 'ar', 'الفئات', 'labels'),
  ('label.categories', 'ru', 'Категории', 'labels'),
  ('label.our_products', 'tr', 'Ürünlerimiz', 'labels'),
  ('label.our_products', 'en', 'Our Products', 'labels'),
  ('label.our_products', 'ar', 'منتجاتنا', 'labels'),
  ('label.our_products', 'ru', 'Наши продукты', 'labels'),
  ('label.our_services', 'tr', 'Hizmetlerimiz', 'labels'),
  ('label.our_services', 'en', 'Our Services', 'labels'),
  ('label.our_services', 'ar', 'خدماتنا', 'labels'),
  ('label.our_services', 'ru', 'Наши услуги', 'labels'),
  ('label.specs', 'tr', 'Teknik Özellikler', 'labels'),
  ('label.specs', 'en', 'Technical Specifications', 'labels'),
  ('label.specs', 'ar', 'المواصفات الفنية', 'labels'),
  ('label.specs', 'ru', 'Технические характеристики', 'labels'),
  ('label.features', 'tr', 'Özellikler', 'labels'),
  ('label.features', 'en', 'Features', 'labels'),
  ('label.features', 'ar', 'الميزات', 'labels'),
  ('label.features', 'ru', 'Особенности', 'labels'),
  ('label.advantages', 'tr', 'Avantajlar', 'labels'),
  ('label.advantages', 'en', 'Advantages', 'labels'),
  ('label.advantages', 'ar', 'المزايا', 'labels'),
  ('label.advantages', 'ru', 'Преимущества', 'labels'),
  ('label.applications', 'tr', 'Uygulama Alanları', 'labels'),
  ('label.applications', 'en', 'Application Areas', 'labels'),
  ('label.applications', 'ar', 'مجالات التطبيق', 'labels'),
  ('label.applications', 'ru', 'Области применения', 'labels'),
  ('label.related_products', 'tr', 'İlgili Ürünler', 'labels'),
  ('label.related_products', 'en', 'Related Products', 'labels'),
  ('label.related_products', 'ar', 'المنتجات ذات الصلة', 'labels'),
  ('label.related_products', 'ru', 'Похожие продукты', 'labels'),
  ('label.other_services', 'tr', 'Diğer Hizmetler', 'labels'),
  ('label.other_services', 'en', 'Other Services', 'labels'),
  ('label.other_services', 'ar', 'خدمات أخرى', 'labels'),
  ('label.other_services', 'ru', 'Другие услуги', 'labels'),
  ('label.about_company', 'tr', 'Şirket Hakkında', 'labels'),
  ('label.about_company', 'en', 'About the Company', 'labels'),
  ('label.about_company', 'ar', 'عن الشركة', 'labels'),
  ('label.about_company', 'ru', 'О компании', 'labels'),
  ('label.contact_info', 'tr', 'İletişim Bilgileri', 'labels'),
  ('label.contact_info', 'en', 'Contact Information', 'labels'),
  ('label.contact_info', 'ar', 'معلومات الاتصال', 'labels'),
  ('label.contact_info', 'ru', 'Контактная информация', 'labels'),
  ('label.send_message', 'tr', 'Mesaj Gönder', 'labels'),
  ('label.send_message', 'en', 'Send Message', 'labels'),
  ('label.send_message', 'ar', 'إرسال رسالة', 'labels'),
  ('label.send_message', 'ru', 'Отправить сообщение', 'labels'),
  ('label.our_address', 'tr', 'Adresimiz', 'labels'),
  ('label.our_address', 'en', 'Our Address', 'labels'),
  ('label.our_address', 'ar', 'عنواننا', 'labels'),
  ('label.our_address', 'ru', 'Наш адрес', 'labels'),
  ('label.faq', 'tr', 'Sıkça Sorulan Sorular', 'labels'),
  ('label.faq', 'en', 'Frequently Asked Questions', 'labels'),
  ('label.faq', 'ar', 'الأسئلة الشائعة', 'labels'),
  ('label.faq', 'ru', 'Часто задаваемые вопросы', 'labels'),
  ('label.partners', 'tr', 'Çözüm Ortaklarımız', 'labels'),
  ('label.partners', 'en', 'Our Solution Partners', 'labels'),
  ('label.partners', 'ar', 'شركاء الحلول لدينا', 'labels'),
  ('label.partners', 'ru', 'Наши партнёры', 'labels'),
  ('contact.hero_title', 'tr', 'İletişim', 'contact'),
  ('contact.hero_title', 'en', 'Contact', 'contact'),
  ('contact.hero_title', 'ar', 'اتصل بنا', 'contact'),
  ('contact.hero_title', 'ru', 'Контакты', 'contact'),
  ('contact.hero_lead', 'tr', 'Soru, teklif ve özel taleplerinizi 7/24 yazabilir; Konya merkezli ekibimizle her ihtiyacınızda görüşebilirsiniz.', 'contact'),
  ('contact.hero_lead', 'en', 'You can write your questions, requests, and special demands 24/7; reach our Konya-based team for any need.', 'contact'),
  ('contact.hero_lead', 'ar', 'يمكنك كتابة أسئلتك وطلباتك ومتطلباتك الخاصة على مدار الساعة؛ تواصل مع فريقنا في قونية لأي احتياج.', 'contact'),
  ('contact.hero_lead', 'ru', 'Вы можете писать ваши вопросы, запросы и специальные требования 24/7; свяжитесь с нашей командой в Конье для любых нужд.', 'contact'),
  ('contact.form_title', 'tr', 'Bize Yazın', 'contact'),
  ('contact.form_title', 'en', 'Write to Us', 'contact'),
  ('contact.form_title', 'ar', 'اكتب لنا', 'contact'),
  ('contact.form_title', 'ru', 'Напишите нам', 'contact'),
  ('contact.form_lead', 'tr', 'Form üzerinden iletişime geçin, satış ekibimiz en kısa sürede dönüş yapsın.', 'contact'),
  ('contact.form_lead', 'en', 'Get in touch via the form; our sales team will respond as soon as possible.', 'contact'),
  ('contact.form_lead', 'ar', 'تواصل عبر النموذج، وسيرد فريق المبيعات في أقرب وقت ممكن.', 'contact'),
  ('contact.form_lead', 'ru', 'Свяжитесь через форму, наш отдел продаж ответит в кратчайшие сроки.', 'contact'),
  ('services.hero_eyebrow', 'tr', 'Endüstriyel Yetkinlik', 'services'),
  ('services.hero_eyebrow', 'en', 'Industrial Capability', 'services'),
  ('services.hero_eyebrow', 'ar', 'القدرة الصناعية', 'services'),
  ('services.hero_eyebrow', 'ru', 'Промышленные возможности', 'services'),
  ('services.hero_title', 'tr', 'Atölyemizden Çözümler', 'services'),
  ('services.hero_title', 'en', 'Solutions from Our Workshop', 'services'),
  ('services.hero_title', 'ar', 'حلول من ورشتنا', 'services'),
  ('services.hero_title', 'ru', 'Решения из нашей мастерской', 'services'),
  ('service.eyebrow.laser', 'tr', 'Hassas Kesim Teknolojisi', 'services'),
  ('service.eyebrow.laser', 'en', 'Precision Cutting Technology', 'services'),
  ('service.eyebrow.laser', 'ar', 'تقنية القطع الدقيق', 'services'),
  ('service.eyebrow.laser', 'ru', 'Технология точной резки', 'services'),
  ('service.eyebrow.oxygen', 'tr', 'Kalın Levha Kesimi', 'services'),
  ('service.eyebrow.oxygen', 'en', 'Thick Plate Cutting', 'services'),
  ('service.eyebrow.oxygen', 'ar', 'قطع الألواح السميكة', 'services'),
  ('service.eyebrow.oxygen', 'ru', 'Резка толстых листов', 'services'),
  ('service.eyebrow.decor', 'tr', 'Mimari Sac Üretimi', 'services'),
  ('service.eyebrow.decor', 'en', 'Architectural Sheet Production', 'services'),
  ('service.eyebrow.decor', 'ar', 'إنتاج الألواح المعمارية', 'services'),
  ('service.eyebrow.decor', 'ru', 'Производство архитектурных листов', 'services'),
  ('service.eyebrow.bend', 'tr', 'Sac Şekillendirme', 'services'),
  ('service.eyebrow.bend', 'en', 'Sheet Forming', 'services'),
  ('service.eyebrow.bend', 'ar', 'تشكيل الألواح', 'services'),
  ('service.eyebrow.bend', 'ru', 'Формовка листа', 'services'),
  ('service.eyebrow.general', 'tr', 'Endüstriyel Yetkinlik', 'services'),
  ('service.eyebrow.general', 'en', 'Industrial Capability', 'services'),
  ('service.eyebrow.general', 'ar', 'القدرة الصناعية', 'services'),
  ('service.eyebrow.general', 'ru', 'Промышленные возможности', 'services'),
  ('service.stat.experience', 'tr', 'Deneyim', 'services'),
  ('service.stat.experience', 'en', 'Experience', 'services'),
  ('service.stat.experience', 'ar', 'خبرة', 'services'),
  ('service.stat.experience', 'ru', 'Опыт', 'services'),
  ('service.stat.precision', 'tr', 'Hassasiyet', 'services'),
  ('service.stat.precision', 'en', 'Precision', 'services'),
  ('service.stat.precision', 'ar', 'دقة', 'services'),
  ('service.stat.precision', 'ru', 'Точность', 'services'),
  ('service.stat.same_day', 'tr', 'Aynı Gün', 'services'),
  ('service.stat.same_day', 'en', 'Same Day', 'services'),
  ('service.stat.same_day', 'ar', 'في نفس اليوم', 'services'),
  ('service.stat.same_day', 'ru', 'В тот же день', 'services'),
  ('service.stat.same_day_quote', 'tr', 'Teklif', 'services'),
  ('service.stat.same_day_quote', 'en', 'Quote', 'services'),
  ('service.stat.same_day_quote', 'ar', 'عرض السعر', 'services'),
  ('service.stat.same_day_quote', 'ru', 'Предложение', 'services'),
  ('service.stat.shipping', 'tr', 'Sevkiyat', 'services'),
  ('service.stat.shipping', 'en', 'Shipping', 'services'),
  ('service.stat.shipping', 'ar', 'الشحن', 'services'),
  ('service.stat.shipping', 'ru', 'Доставка', 'services'),
  ('service.stat.cities_value', 'tr', '81 İl', 'services'),
  ('service.stat.cities_value', 'en', '81 Cities', 'services'),
  ('service.stat.cities_value', 'ar', '81 مدينة', 'services'),
  ('service.stat.cities_value', 'ru', '81 город', 'services'),
  ('service.cta.title', 'tr', 'Bize Yazın', 'services'),
  ('service.cta.title', 'en', 'Write to Us', 'services'),
  ('service.cta.title', 'ar', 'اكتب لنا', 'services'),
  ('service.cta.title', 'ru', 'Напишите нам', 'services'),
  ('service.cta.lead', 'tr', 'DXF/DWG çiziminizi veya ölçü detaylarınızı gönderin, satış ekibimiz aynı gün geri dönüş yapsın.', 'services'),
  ('service.cta.lead', 'en', 'Send your DXF/DWG drawing or measurement details; our sales team will respond the same day.', 'services'),
  ('service.cta.lead', 'ar', 'أرسل رسم DXF/DWG أو تفاصيل القياس، وسيرد فريق المبيعات في نفس اليوم.', 'services'),
  ('service.cta.lead', 'ru', 'Пришлите чертёж DXF/DWG или детали размеров, наш отдел продаж ответит в тот же день.', 'services'),
  ('service.advantages_label', 'tr', 'Avantaj', 'services'),
  ('service.advantages_label', 'en', 'Advantage', 'services'),
  ('service.advantages_label', 'ar', 'ميزة', 'services'),
  ('service.advantages_label', 'ru', 'Преимущество', 'services'),
  ('service.specs_eyebrow', 'tr', 'Teknik Spesifikasyon', 'services'),
  ('service.specs_eyebrow', 'en', 'Technical Specification', 'services'),
  ('service.specs_eyebrow', 'ar', 'المواصفات الفنية', 'services'),
  ('service.specs_eyebrow', 'ru', 'Технические характеристики', 'services'),
  ('service.specs_title', 'tr', 'Üretim Özellikleri', 'services'),
  ('service.specs_title', 'en', 'Production Specs', 'services'),
  ('service.specs_title', 'ar', 'مواصفات الإنتاج', 'services'),
  ('service.specs_title', 'ru', 'Характеристики производства', 'services'),
  ('service.cta_eyebrow', 'tr', 'Aynı Gün Teklif', 'services'),
  ('service.cta_eyebrow', 'en', 'Same-Day Quote', 'services'),
  ('service.cta_eyebrow', 'ar', 'عرض في نفس اليوم', 'services'),
  ('service.cta_eyebrow', 'ru', 'Предложение в тот же день', 'services'),
  ('service.other_eyebrow', 'tr', 'Diğer Yetkinlikler', 'services'),
  ('service.other_eyebrow', 'en', 'Other Capabilities', 'services'),
  ('service.other_eyebrow', 'ar', 'القدرات الأخرى', 'services'),
  ('service.other_eyebrow', 'ru', 'Другие возможности', 'services'),
  ('service.other_title', 'tr', 'Endüstriyel Atölye Hizmetlerimiz', 'services'),
  ('service.other_title', 'en', 'Our Industrial Workshop Services', 'services'),
  ('service.other_title', 'ar', 'خدمات ورشتنا الصناعية', 'services'),
  ('service.other_title', 'ru', 'Услуги нашей промышленной мастерской', 'services'),
  ('service.bottom_eyebrow', 'tr', 'Tek Tedarikçi · Uçtan Uca Çözüm', 'services'),
  ('service.bottom_eyebrow', 'en', 'Single Supplier · End-to-End Solution', 'services'),
  ('service.bottom_eyebrow', 'ar', 'مورد واحد · حل شامل', 'services'),
  ('service.bottom_eyebrow', 'ru', 'Единый поставщик · Комплексное решение', 'services'),
  ('service.bottom_title', 'tr', 'Sac, boru, profil ve atölye desteği tek adresten', 'services'),
  ('service.bottom_title', 'en', 'Sheet, pipe, profile, and workshop support from a single address', 'services'),
  ('service.bottom_title', 'ar', 'الألواح والأنابيب والمقاطع ودعم الورشة من عنوان واحد', 'services'),
  ('service.bottom_title', 'ru', 'Листы, трубы, профили и поддержка мастерской из одного адреса', 'services'),
  ('service.bottom_lead', 'tr', 'Sertifikalı malzeme tedariği, hassas kesim, sevkiyat ve mühendislik desteği ile demir-çelik projelerinizde uçtan uca iş ortağıyız.', 'services'),
  ('service.bottom_lead', 'en', 'Certified material supply, precision cutting, shipping, and engineering support — we are your end-to-end partner in steel projects.', 'services'),
  ('service.bottom_lead', 'ar', 'توريد مواد معتمدة، قطع دقيق، شحن ودعم هندسي — نحن شريكك الشامل في مشاريع الفولاذ.', 'services'),
  ('service.bottom_lead', 'ru', 'Сертифицированные материалы, точная резка, доставка и инженерная поддержка — мы ваш комплексный партнёр в стальных проектах.', 'services'),
  ('service.btn.products', 'tr', 'Ürün Katalogu', 'services'),
  ('service.btn.products', 'en', 'Product Catalog', 'services'),
  ('service.btn.products', 'ar', 'كتالوج المنتجات', 'services'),
  ('service.btn.products', 'ru', 'Каталог продуктов', 'services'),
  ('home.hero.scroll', 'tr', 'Aşağı Kaydır', 'home'),
  ('home.hero.scroll', 'en', 'Scroll Down', 'home'),
  ('home.hero.scroll', 'ar', 'مرر للأسفل', 'home'),
  ('home.hero.scroll', 'ru', 'Прокрутить вниз', 'home'),
  ('home.about.title', 'tr', 'Birlikte Daha Güçlüyüz', 'home'),
  ('home.about.title', 'en', 'We Are Stronger Together', 'home'),
  ('home.about.title', 'ar', 'نحن أقوى معًا', 'home'),
  ('home.about.title', 'ru', 'Мы сильнее вместе', 'home'),
  ('home.about.eyebrow', 'tr', 'Hakkımızda', 'home'),
  ('home.about.eyebrow', 'en', 'About Us', 'home'),
  ('home.about.eyebrow', 'ar', 'من نحن', 'home'),
  ('home.about.eyebrow', 'ru', 'О нас', 'home'),
  ('home.products.eyebrow', 'tr', 'Ürün Gruplarımız', 'home'),
  ('home.products.eyebrow', 'en', 'Our Product Groups', 'home'),
  ('home.products.eyebrow', 'ar', 'مجموعات منتجاتنا', 'home'),
  ('home.products.eyebrow', 'ru', 'Наши группы продукции', 'home'),
  ('home.products.title', 'tr', 'Demir-Çelik Tedarik Yelpazemiz', 'home'),
  ('home.products.title', 'en', 'Our Steel Supply Range', 'home'),
  ('home.products.title', 'ar', 'نطاق توريد الفولاذ لدينا', 'home'),
  ('home.products.title', 'ru', 'Наш ассортимент стальной продукции', 'home'),
  ('home.services.eyebrow', 'tr', 'Endüstriyel Yetkinlikler', 'home'),
  ('home.services.eyebrow', 'en', 'Industrial Capabilities', 'home'),
  ('home.services.eyebrow', 'ar', 'القدرات الصناعية', 'home'),
  ('home.services.eyebrow', 'ru', 'Промышленные возможности', 'home'),
  ('home.services.title', 'tr', 'Atölye Hizmetlerimiz', 'home'),
  ('home.services.title', 'en', 'Our Workshop Services', 'home'),
  ('home.services.title', 'ar', 'خدمات ورشتنا', 'home'),
  ('home.services.title', 'ru', 'Услуги нашей мастерской', 'home'),
  ('home.partners.eyebrow', 'tr', 'Çözüm Ortakları', 'home'),
  ('home.partners.eyebrow', 'en', 'Solution Partners', 'home'),
  ('home.partners.eyebrow', 'ar', 'شركاء الحلول', 'home'),
  ('home.partners.eyebrow', 'ru', 'Партнёры по решениям', 'home'),
  ('home.partners.title', 'tr', 'Üretici Tedarik Ağımız', 'home'),
  ('home.partners.title', 'en', 'Our Manufacturer Network', 'home'),
  ('home.partners.title', 'ar', 'شبكة المصنعين لدينا', 'home'),
  ('home.partners.title', 'ru', 'Сеть производителей', 'home'),
  ('home.faq.eyebrow', 'tr', 'Sıkça Sorulan Sorular', 'home'),
  ('home.faq.eyebrow', 'en', 'FAQ', 'home'),
  ('home.faq.eyebrow', 'ar', 'الأسئلة الشائعة', 'home'),
  ('home.faq.eyebrow', 'ru', 'FAQ', 'home'),
  ('home.faq.title', 'tr', 'Müşterilerimizin En Çok Sorduğu', 'home'),
  ('home.faq.title', 'en', 'What Our Customers Ask Most', 'home'),
  ('home.faq.title', 'ar', 'ما يسأله عملاؤنا أكثر', 'home'),
  ('home.faq.title', 'ru', 'Что чаще всего спрашивают клиенты', 'home'),
  ('home.contact.eyebrow', 'tr', 'Tek Tedarikçi', 'home'),
  ('home.contact.eyebrow', 'en', 'Single Supplier', 'home'),
  ('home.contact.eyebrow', 'ar', 'مورد واحد', 'home'),
  ('home.contact.eyebrow', 'ru', 'Единый поставщик', 'home'),
  ('home.contact.title', 'tr', 'Demir-Çelik İhtiyacınız İçin Doğru Adres', 'home'),
  ('home.contact.title', 'en', 'The Right Address for Your Steel Needs', 'home'),
  ('home.contact.title', 'ar', 'العنوان الصحيح لاحتياجاتك من الفولاذ', 'home'),
  ('home.contact.title', 'ru', 'Правильный адрес для ваших потребностей в стали', 'home'),
  ('products.hero_title', 'tr', 'Ürün Gruplarımız', 'products'),
  ('products.hero_title', 'en', 'Our Product Groups', 'products'),
  ('products.hero_title', 'ar', 'مجموعات منتجاتنا', 'products'),
  ('products.hero_title', 'ru', 'Наши группы продукции', 'products'),
  ('products.hero_lead', 'tr', 'Sac, boru, profil, hadde ve özel ölçü çelik tedariği — Konya merkezli depolarımızdan 81 il sevkiyat ağı.', 'products'),
  ('products.hero_lead', 'en', 'Sheet, pipe, profile, rolled steel and custom-size supply — 81-province shipping network from our Konya warehouses.', 'products'),
  ('products.hero_lead', 'ar', 'الألواح والأنابيب والمقاطع والصلب المدرفل والتوريد بمقاسات خاصة — شبكة شحن لـ81 محافظة من مستودعاتنا في قونية.', 'products'),
  ('products.hero_lead', 'ru', 'Листы, трубы, профили, прокат и поставка нестандартных размеров — сеть доставки в 81 провинцию с наших складов в Конье.', 'products'),
  ('product.code', 'tr', 'Ürün Kodu', 'products'),
  ('product.code', 'en', 'Product Code', 'products'),
  ('product.code', 'ar', 'رمز المنتج', 'products'),
  ('product.code', 'ru', 'Код товара', 'products'),
  ('product.standard', 'tr', 'Standart', 'products'),
  ('product.standard', 'en', 'Standard', 'products'),
  ('product.standard', 'ar', 'المعيار', 'products'),
  ('product.standard', 'ru', 'Стандарт', 'products'),
  ('product.thickness', 'tr', 'Kalınlık', 'products'),
  ('product.thickness', 'en', 'Thickness', 'products'),
  ('product.thickness', 'ar', 'السمك', 'products'),
  ('product.thickness', 'ru', 'Толщина', 'products'),
  ('product.width', 'tr', 'Genişlik', 'products'),
  ('product.width', 'en', 'Width', 'products'),
  ('product.width', 'ar', 'العرض', 'products'),
  ('product.width', 'ru', 'Ширина', 'products'),
  ('product.length', 'tr', 'Uzunluk', 'products'),
  ('product.length', 'en', 'Length', 'products'),
  ('product.length', 'ar', 'الطول', 'products'),
  ('product.length', 'ru', 'Длина', 'products'),
  ('product.diameter', 'tr', 'Çap', 'products'),
  ('product.diameter', 'en', 'Diameter', 'products'),
  ('product.diameter', 'ar', 'القطر', 'products'),
  ('product.diameter', 'ru', 'Диаметр', 'products'),
  ('product.weight', 'tr', 'Ağırlık', 'products'),
  ('product.weight', 'en', 'Weight', 'products'),
  ('product.weight', 'ar', 'الوزن', 'products'),
  ('product.weight', 'ru', 'Вес', 'products'),
  ('product.surface', 'tr', 'Yüzey', 'products'),
  ('product.surface', 'en', 'Surface', 'products'),
  ('product.surface', 'ar', 'السطح', 'products'),
  ('product.surface', 'ru', 'Поверхность', 'products'),
  ('product.grade', 'tr', 'Kalite', 'products'),
  ('product.grade', 'en', 'Grade', 'products'),
  ('product.grade', 'ar', 'الجودة', 'products'),
  ('product.grade', 'ru', 'Марка', 'products'),
  ('product.application', 'tr', 'Kullanım Alanı', 'products'),
  ('product.application', 'en', 'Application', 'products'),
  ('product.application', 'ar', 'الاستخدام', 'products'),
  ('product.application', 'ru', 'Применение', 'products'),
  ('slider.cta_default', 'tr', 'Detaylı Bilgi', 'slider'),
  ('slider.cta_default', 'en', 'More Info', 'slider'),
  ('slider.cta_default', 'ar', 'مزيد من المعلومات', 'slider'),
  ('slider.cta_default', 'ru', 'Подробнее', 'slider'),
  ('calc.hero_title', 'tr', 'Ağırlık Hesaplama', 'calc'),
  ('calc.hero_title', 'en', 'Weight Calculator', 'calc'),
  ('calc.hero_title', 'ar', 'حاسبة الوزن', 'calc'),
  ('calc.hero_title', 'ru', 'Калькулятор веса', 'calc'),
  ('calc.hero_lead', 'tr', 'Sac, boru, profil veya hadde ürünlerinin ağırlığını saniyeler içinde hesaplayın.', 'calc'),
  ('calc.hero_lead', 'en', 'Calculate the weight of sheet, pipe, profile, or rolled products in seconds.', 'calc'),
  ('calc.hero_lead', 'ar', 'احسب وزن الألواح أو الأنابيب أو المقاطع أو المنتجات المدرفلة في ثوانٍ.', 'calc'),
  ('calc.hero_lead', 'ru', 'Рассчитайте вес листа, трубы, профиля или проката за секунды.', 'calc'),
  ('calc.select_product', 'tr', 'Ürün Seçin', 'calc'),
  ('calc.select_product', 'en', 'Select Product', 'calc'),
  ('calc.select_product', 'ar', 'اختر المنتج', 'calc'),
  ('calc.select_product', 'ru', 'Выберите продукт', 'calc'),
  ('calc.dimensions', 'tr', 'Ölçüler', 'calc'),
  ('calc.dimensions', 'en', 'Dimensions', 'calc'),
  ('calc.dimensions', 'ar', 'الأبعاد', 'calc'),
  ('calc.dimensions', 'ru', 'Размеры', 'calc'),
  ('calc.result', 'tr', 'Sonuç', 'calc'),
  ('calc.result', 'en', 'Result', 'calc'),
  ('calc.result', 'ar', 'النتيجة', 'calc'),
  ('calc.result', 'ru', 'Результат', 'calc'),
  ('calc.total_weight', 'tr', 'Toplam Ağırlık', 'calc'),
  ('calc.total_weight', 'en', 'Total Weight', 'calc'),
  ('calc.total_weight', 'ar', 'الوزن الكلي', 'calc'),
  ('calc.total_weight', 'ru', 'Общий вес', 'calc'),
  ('calc.unit_weight', 'tr', 'Birim Ağırlık', 'calc'),
  ('calc.unit_weight', 'en', 'Unit Weight', 'calc'),
  ('calc.unit_weight', 'ar', 'وزن الوحدة', 'calc'),
  ('calc.unit_weight', 'ru', 'Удельный вес', 'calc'),
  ('calc.quantity', 'tr', 'Adet', 'calc'),
  ('calc.quantity', 'en', 'Quantity', 'calc'),
  ('calc.quantity', 'ar', 'الكمية', 'calc'),
  ('calc.quantity', 'ru', 'Количество', 'calc'),
  ('blog.hero_title', 'tr', 'Tekcan''dan Haberler', 'blog'),
  ('blog.hero_title', 'en', 'News from Tekcan', 'blog'),
  ('blog.hero_title', 'ar', 'أخبار من تكجان', 'blog'),
  ('blog.hero_title', 'ru', 'Новости от Tekcan', 'blog'),
  ('blog.hero_lead', 'tr', 'Sektör analizleri, ürün rehberleri ve kurumsal duyurularımız.', 'blog'),
  ('blog.hero_lead', 'en', 'Industry analyses, product guides, and corporate announcements.', 'blog'),
  ('blog.hero_lead', 'ar', 'تحليلات الصناعة، أدلة المنتجات، والإعلانات المؤسسية.', 'blog'),
  ('blog.hero_lead', 'ru', 'Отраслевая аналитика, руководства по продуктам и корпоративные новости.', 'blog'),
  ('blog.read_more', 'tr', 'Yazıyı Oku', 'blog'),
  ('blog.read_more', 'en', 'Read Article', 'blog'),
  ('blog.read_more', 'ar', 'اقرأ المقال', 'blog'),
  ('blog.read_more', 'ru', 'Читать статью', 'blog'),
  ('blog.author', 'tr', 'Yazar', 'blog'),
  ('blog.author', 'en', 'Author', 'blog'),
  ('blog.author', 'ar', 'الكاتب', 'blog'),
  ('blog.author', 'ru', 'Автор', 'blog'),
  ('blog.published', 'tr', 'Yayın Tarihi', 'blog'),
  ('blog.published', 'en', 'Published', 'blog'),
  ('blog.published', 'ar', 'تاريخ النشر', 'blog'),
  ('blog.published', 'ru', 'Опубликовано', 'blog'),
  ('blog.related_posts', 'tr', 'İlgili Yazılar', 'blog'),
  ('blog.related_posts', 'en', 'Related Articles', 'blog'),
  ('blog.related_posts', 'ar', 'مقالات ذات صلة', 'blog'),
  ('blog.related_posts', 'ru', 'Похожие статьи', 'blog'),
  ('blog.no_posts', 'tr', 'Henüz yazı yok.', 'blog'),
  ('blog.no_posts', 'en', 'No posts yet.', 'blog'),
  ('blog.no_posts', 'ar', 'لا توجد مقالات بعد.', 'blog'),
  ('blog.no_posts', 'ru', 'Пока нет статей.', 'blog'),
  ('gallery.hero_title', 'tr', 'Foto Galeri', 'gallery'),
  ('gallery.hero_title', 'en', 'Photo Gallery', 'gallery'),
  ('gallery.hero_title', 'ar', 'معرض الصور', 'gallery'),
  ('gallery.hero_title', 'ru', 'Фотогалерея', 'gallery'),
  ('gallery.hero_lead', 'tr', 'Atölyemizden, sevkiyatlarımızdan ve projelerimizden kareler.', 'gallery'),
  ('gallery.hero_lead', 'en', 'Frames from our workshop, shipments, and projects.', 'gallery'),
  ('gallery.hero_lead', 'ar', 'لقطات من ورشتنا وشحناتنا ومشاريعنا.', 'gallery'),
  ('gallery.hero_lead', 'ru', 'Кадры из нашей мастерской, отгрузок и проектов.', 'gallery'),
  ('gallery.no_albums', 'tr', 'Henüz galeri yok.', 'gallery'),
  ('gallery.no_albums', 'en', 'No galleries yet.', 'gallery'),
  ('gallery.no_albums', 'ar', 'لا توجد معارض بعد.', 'gallery'),
  ('gallery.no_albums', 'ru', 'Пока нет галерей.', 'gallery'),
  ('gallery.images', 'tr', 'fotoğraf', 'gallery'),
  ('gallery.images', 'en', 'photos', 'gallery'),
  ('gallery.images', 'ar', 'صورة', 'gallery'),
  ('gallery.images', 'ru', 'фото', 'gallery'),
  ('faq.hero_title', 'tr', 'Sıkça Sorulan Sorular', 'faq'),
  ('faq.hero_title', 'en', 'Frequently Asked Questions', 'faq'),
  ('faq.hero_title', 'ar', 'الأسئلة الشائعة', 'faq'),
  ('faq.hero_title', 'ru', 'Часто задаваемые вопросы', 'faq'),
  ('faq.hero_lead', 'tr', 'Demir-çelik tedariği, sevkiyat, fiyat ve atölye hizmetleri hakkında en çok sorulan sorular.', 'faq'),
  ('faq.hero_lead', 'en', 'The most frequently asked questions about steel supply, shipping, pricing, and workshop services.', 'faq'),
  ('faq.hero_lead', 'ar', 'الأسئلة الأكثر شيوعًا حول توريد الفولاذ والشحن والتسعير وخدمات الورشة.', 'faq'),
  ('faq.hero_lead', 'ru', 'Наиболее часто задаваемые вопросы о поставке стали, доставке, ценах и услугах мастерской.', 'faq'),
  ('about.hero_title', 'tr', 'Hakkımızda', 'about'),
  ('about.hero_title', 'en', 'About Us', 'about'),
  ('about.hero_title', 'ar', 'من نحن', 'about'),
  ('about.hero_title', 'ru', 'О нас', 'about'),
  ('about.eyebrow', 'tr', 'Kurumsal', 'about'),
  ('about.eyebrow', 'en', 'Corporate', 'about'),
  ('about.eyebrow', 'ar', 'عن الشركة', 'about'),
  ('about.eyebrow', 'ru', 'Корпоративная', 'about'),
  ('about.years_experience', 'tr', 'Yıllık Deneyim', 'about'),
  ('about.years_experience', 'en', 'Years of Experience', 'about'),
  ('about.years_experience', 'ar', 'سنوات من الخبرة', 'about'),
  ('about.years_experience', 'ru', 'Лет опыта', 'about'),
  ('about.cities_served', 'tr', 'Hizmet Verilen İl', 'about'),
  ('about.cities_served', 'en', 'Provinces Served', 'about'),
  ('about.cities_served', 'ar', 'المحافظات المخدومة', 'about'),
  ('about.cities_served', 'ru', 'Обслуживаемые провинции', 'about'),
  ('about.product_count', 'tr', 'Ürün Çeşidi', 'about'),
  ('about.product_count', 'en', 'Product Variety', 'about'),
  ('about.product_count', 'ar', 'تنوع المنتجات', 'about'),
  ('about.product_count', 'ru', 'Ассортимент', 'about'),
  ('about.export_countries', 'tr', 'İhracat Ülkesi', 'about'),
  ('about.export_countries', 'en', 'Export Countries', 'about'),
  ('about.export_countries', 'ar', 'دول التصدير', 'about'),
  ('about.export_countries', 'ru', 'Страны экспорта', 'about'),
  ('partners.hero_title', 'tr', 'Çözüm Ortaklarımız', 'partners'),
  ('partners.hero_title', 'en', 'Our Solution Partners', 'partners'),
  ('partners.hero_title', 'ar', 'شركاء الحلول لدينا', 'partners'),
  ('partners.hero_title', 'ru', 'Наши партнёры по решениям', 'partners'),
  ('partners.hero_lead', 'tr', 'Türkiye''nin önde gelen demir-çelik üreticileriyle güçlü tedarik ortaklığımız.', 'partners'),
  ('partners.hero_lead', 'en', 'Strong supply partnership with Türkiye''s leading steel manufacturers.', 'partners'),
  ('partners.hero_lead', 'ar', 'شراكة توريد قوية مع كبرى شركات تصنيع الفولاذ في تركيا.', 'partners'),
  ('partners.hero_lead', 'ru', 'Прочное партнёрство с ведущими производителями стали Турции.', 'partners'),
  ('export.hero_title', 'tr', 'İhracat Pazarlarımız', 'export'),
  ('export.hero_title', 'en', 'Our Export Markets', 'export'),
  ('export.hero_title', 'ar', 'أسواق التصدير لدينا', 'export'),
  ('export.hero_title', 'ru', 'Наши экспортные рынки', 'export'),
  ('export.hero_lead', 'tr', 'Irak, Suriye, Azerbaycan ve Türkmenistan''a uluslararası demir-çelik tedariği.', 'export'),
  ('export.hero_lead', 'en', 'International steel supply to Iraq, Syria, Azerbaijan, and Turkmenistan.', 'export'),
  ('export.hero_lead', 'ar', 'توريد الفولاذ الدولي إلى العراق وسوريا وأذربيجان وتركمانستان.', 'export'),
  ('export.hero_lead', 'ru', 'Международные поставки стали в Ирак, Сирию, Азербайджан и Туркменистан.', 'export'),
  ('export.country.iraq', 'tr', 'Irak', 'export'),
  ('export.country.iraq', 'en', 'Iraq', 'export'),
  ('export.country.iraq', 'ar', 'العراق', 'export'),
  ('export.country.iraq', 'ru', 'Ирак', 'export'),
  ('export.country.syria', 'tr', 'Suriye', 'export'),
  ('export.country.syria', 'en', 'Syria', 'export'),
  ('export.country.syria', 'ar', 'سوريا', 'export'),
  ('export.country.syria', 'ru', 'Сирия', 'export'),
  ('export.country.azerbaijan', 'tr', 'Azerbaycan', 'export'),
  ('export.country.azerbaijan', 'en', 'Azerbaijan', 'export'),
  ('export.country.azerbaijan', 'ar', 'أذربيجان', 'export'),
  ('export.country.azerbaijan', 'ru', 'Азербайджан', 'export'),
  ('export.country.turkmenistan', 'tr', 'Türkmenistan', 'export'),
  ('export.country.turkmenistan', 'en', 'Turkmenistan', 'export'),
  ('export.country.turkmenistan', 'ar', 'تركمانستان', 'export'),
  ('export.country.turkmenistan', 'ru', 'Туркменистан', 'export'),
  ('city.hero_eyebrow', 'tr', 'Bölgesel Tedarik', 'city'),
  ('city.hero_eyebrow', 'en', 'Regional Supply', 'city'),
  ('city.hero_eyebrow', 'ar', 'التوريد الإقليمي', 'city'),
  ('city.hero_eyebrow', 'ru', 'Региональные поставки', 'city'),
  ('city.shipping_title', 'tr', 'Sevkiyat Bilgisi', 'city'),
  ('city.shipping_title', 'en', 'Shipping Information', 'city'),
  ('city.shipping_title', 'ar', 'معلومات الشحن', 'city'),
  ('city.shipping_title', 'ru', 'Информация о доставке', 'city'),
  ('city.products_title', 'tr', 'Tedarik Ettiğimiz Ürünler', 'city'),
  ('city.products_title', 'en', 'Products We Supply', 'city'),
  ('city.products_title', 'ar', 'المنتجات التي نوردها', 'city'),
  ('city.products_title', 'ru', 'Продукция, которую мы поставляем', 'city'),
  ('mailorder.hero_title', 'tr', 'Mail Order Formu', 'mailorder'),
  ('mailorder.hero_title', 'en', 'Mail Order Form', 'mailorder'),
  ('mailorder.hero_title', 'ar', 'نموذج الطلب البريدي', 'mailorder'),
  ('mailorder.hero_title', 'ru', 'Бланк заказа по почте', 'mailorder'),
  ('mailorder.hero_lead', 'tr', 'Hızlı ve güvenli ödeme için online mail order formumuzu doldurun.', 'mailorder'),
  ('mailorder.hero_lead', 'en', 'Fill out our online mail order form for fast and secure payment.', 'mailorder'),
  ('mailorder.hero_lead', 'ar', 'املأ نموذج الطلب البريدي عبر الإنترنت للدفع السريع والآمن.', 'mailorder'),
  ('mailorder.hero_lead', 'ru', 'Заполните наш онлайн-бланк заказа для быстрой и безопасной оплаты.', 'mailorder'),
  ('loyalty.hero_title', 'tr', 'Sadakat Programı', 'loyalty'),
  ('loyalty.hero_title', 'en', 'Loyalty Program', 'loyalty'),
  ('loyalty.hero_title', 'ar', 'برنامج الولاء', 'loyalty'),
  ('loyalty.hero_title', 'ru', 'Программа лояльности', 'loyalty'),
  ('loyalty.hero_lead', 'tr', 'Düzenli müşterilerimize özel avantajlar ve indirimli fiyatlar.', 'loyalty'),
  ('loyalty.hero_lead', 'en', 'Special advantages and discounted prices for our regular customers.', 'loyalty'),
  ('loyalty.hero_lead', 'ar', 'مزايا خاصة وأسعار مخفضة لعملائنا المنتظمين.', 'loyalty'),
  ('loyalty.hero_lead', 'ru', 'Специальные преимущества и скидки для постоянных клиентов.', 'loyalty'),
  ('iban.hero_title', 'tr', 'IBAN Bilgilerimiz', 'iban'),
  ('iban.hero_title', 'en', 'Bank Account Information', 'iban'),
  ('iban.hero_title', 'ar', 'معلومات الحساب البنكي', 'iban'),
  ('iban.hero_title', 'ru', 'Банковские реквизиты', 'iban'),
  ('iban.hero_lead', 'tr', 'Havale, EFT ve uluslararası ödemeler için banka hesap bilgilerimiz.', 'iban'),
  ('iban.hero_lead', 'en', 'Bank account information for wire transfers, EFT, and international payments.', 'iban'),
  ('iban.hero_lead', 'ar', 'معلومات الحساب البنكي للتحويلات والتحويلات الإلكترونية والمدفوعات الدولية.', 'iban'),
  ('iban.hero_lead', 'ru', 'Банковские реквизиты для переводов, EFT и международных платежей.', 'iban'),
  ('iban.bank', 'tr', 'Banka', 'iban'),
  ('iban.bank', 'en', 'Bank', 'iban'),
  ('iban.bank', 'ar', 'البنك', 'iban'),
  ('iban.bank', 'ru', 'Банк', 'iban'),
  ('iban.account_holder', 'tr', 'Hesap Sahibi', 'iban'),
  ('iban.account_holder', 'en', 'Account Holder', 'iban'),
  ('iban.account_holder', 'ar', 'صاحب الحساب', 'iban'),
  ('iban.account_holder', 'ru', 'Владелец счёта', 'iban'),
  ('iban.iban_no', 'tr', 'IBAN', 'iban'),
  ('iban.iban_no', 'en', 'IBAN', 'iban'),
  ('iban.iban_no', 'ar', 'IBAN', 'iban'),
  ('iban.iban_no', 'ru', 'IBAN', 'iban'),
  ('iban.swift', 'tr', 'SWIFT/BIC', 'iban'),
  ('iban.swift', 'en', 'SWIFT/BIC', 'iban'),
  ('iban.swift', 'ar', 'سويفت/BIC', 'iban'),
  ('iban.swift', 'ru', 'SWIFT/BIC', 'iban'),
  ('iban.copy', 'tr', 'Kopyala', 'iban'),
  ('iban.copy', 'en', 'Copy', 'iban'),
  ('iban.copy', 'ar', 'نسخ', 'iban'),
  ('iban.copy', 'ru', 'Копировать', 'iban'),
  ('iban.copied', 'tr', 'Kopyalandı!', 'iban'),
  ('iban.copied', 'en', 'Copied!', 'iban'),
  ('iban.copied', 'ar', 'تم النسخ!', 'iban'),
  ('iban.copied', 'ru', 'Скопировано!', 'iban'),
  ('404.title', 'tr', 'Sayfa Bulunamadı', '404'),
  ('404.title', 'en', 'Page Not Found', '404'),
  ('404.title', 'ar', 'الصفحة غير موجودة', '404'),
  ('404.title', 'ru', 'Страница не найдена', '404'),
  ('404.lead', 'tr', 'Aradığınız sayfa taşınmış veya kaldırılmış olabilir.', '404'),
  ('404.lead', 'en', 'The page you are looking for may have been moved or removed.', '404'),
  ('404.lead', 'ar', 'قد تكون الصفحة التي تبحث عنها قد نقلت أو حذفت.', '404'),
  ('404.lead', 'ru', 'Страница, которую вы ищете, могла быть перемещена или удалена.', '404'),
  ('404.btn_home', 'tr', 'Anasayfaya Dön', '404'),
  ('404.btn_home', 'en', 'Back to Home', '404'),
  ('404.btn_home', 'ar', 'العودة إلى الصفحة الرئيسية', '404'),
  ('404.btn_home', 'ru', 'Вернуться на главную', '404'),
  ('general.loading', 'tr', 'Yükleniyor...', 'general'),
  ('general.loading', 'en', 'Loading...', 'general'),
  ('general.loading', 'ar', 'جار التحميل...', 'general'),
  ('general.loading', 'ru', 'Загрузка...', 'general'),
  ('general.no_results', 'tr', 'Sonuç bulunamadı.', 'general'),
  ('general.no_results', 'en', 'No results found.', 'general'),
  ('general.no_results', 'ar', 'لم يتم العثور على نتائج.', 'general'),
  ('general.no_results', 'ru', 'Результаты не найдены.', 'general'),
  ('general.minutes_ago', 'tr', 'dakika önce', 'general'),
  ('general.minutes_ago', 'en', 'minutes ago', 'general'),
  ('general.minutes_ago', 'ar', 'دقائق مضت', 'general'),
  ('general.minutes_ago', 'ru', 'минут назад', 'general'),
  ('general.hours_ago', 'tr', 'saat önce', 'general'),
  ('general.hours_ago', 'en', 'hours ago', 'general'),
  ('general.hours_ago', 'ar', 'ساعات مضت', 'general'),
  ('general.hours_ago', 'ru', 'часов назад', 'general'),
  ('general.days_ago', 'tr', 'gün önce', 'general'),
  ('general.days_ago', 'en', 'days ago', 'general'),
  ('general.days_ago', 'ar', 'أيام مضت', 'general'),
  ('general.days_ago', 'ru', 'дней назад', 'general'),
  ('general.share', 'tr', 'Paylaş', 'general'),
  ('general.share', 'en', 'Share', 'general'),
  ('general.share', 'ar', 'مشاركة', 'general'),
  ('general.share', 'ru', 'Поделиться', 'general'),
  ('general.print', 'tr', 'Yazdır', 'general'),
  ('general.print', 'en', 'Print', 'general'),
  ('general.print', 'ar', 'طباعة', 'general'),
  ('general.print', 'ru', 'Печать', 'general'),
  ('legal.kvkk_title', 'tr', 'Kişisel Verilerin Korunması', 'legal'),
  ('legal.kvkk_title', 'en', 'Personal Data Protection', 'legal'),
  ('legal.kvkk_title', 'ar', 'حماية البيانات الشخصية', 'legal'),
  ('legal.kvkk_title', 'ru', 'Защита персональных данных', 'legal'),
  ('legal.cookie_title', 'tr', 'Çerez Politikası', 'legal'),
  ('legal.cookie_title', 'en', 'Cookie Policy', 'legal'),
  ('legal.cookie_title', 'ar', 'سياسة ملفات تعريف الارتباط', 'legal'),
  ('legal.cookie_title', 'ru', 'Политика cookie', 'legal'),
  ('legal.last_update', 'tr', 'Son Güncelleme', 'legal'),
  ('legal.last_update', 'en', 'Last Update', 'legal'),
  ('legal.last_update', 'ar', 'آخر تحديث', 'legal'),
  ('legal.last_update', 'ru', 'Последнее обновление', 'legal'),
  ('service.hero.advantages_count', 'tr', 'Avantaj', 'services'),
  ('service.hero.advantages_count', 'en', 'Advantages', 'services'),
  ('service.hero.advantages_count', 'ar', 'مزايا', 'services'),
  ('service.hero.advantages_count', 'ru', 'Преимущества', 'services'),
  ('home.hero.title', 'tr', '2005''ten bu yana demir-çeliğin doğru adresi', 'home'),
  ('home.hero.title', 'en', 'The right address for steel since 2005', 'home'),
  ('home.hero.title', 'ar', 'العنوان الصحيح للفولاذ منذ 2005', 'home'),
  ('home.hero.title', 'ru', 'Правильный адрес для стали с 2005 года', 'home'),
  ('home.hero.subtitle', 'tr', 'Konya merkezli, 81 il sevkiyat ağı, 4 ülkeye ihracat', 'home'),
  ('home.hero.subtitle', 'en', 'Konya-based, 81-province shipping network, exports to 4 countries', 'home'),
  ('home.hero.subtitle', 'ar', 'مقرها قونية، شبكة شحن لـ81 محافظة، تصدير إلى 4 دول', 'home'),
  ('home.hero.subtitle', 'ru', 'Из Коньи, сеть доставки в 81 провинцию, экспорт в 4 страны', 'home'),
  ('home.hero.cta_products', 'tr', 'Ürün Katalogu', 'home'),
  ('home.hero.cta_products', 'en', 'Product Catalog', 'home'),
  ('home.hero.cta_products', 'ar', 'كتالوج المنتجات', 'home'),
  ('home.hero.cta_products', 'ru', 'Каталог продуктов', 'home'),
  ('home.hero.cta_contact', 'tr', 'Hemen Teklif Al', 'home'),
  ('home.hero.cta_contact', 'en', 'Get Quote Now', 'home'),
  ('home.hero.cta_contact', 'ar', 'احصل على عرض الآن', 'home'),
  ('home.hero.cta_contact', 'ru', 'Получить предложение', 'home');

-- =====================================================
-- v1.0.57 — UI Çevirileri (TR/EN/AR/RU) - 202 string × 4 dil
-- =====================================================
-- v1.0.57 — UI metin çevirileri (TR/EN/AR/RU)
-- INSERT IGNORE ile idempotent
INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('header.menu.products', 'tr', 'Ürün Gruplarımız', 'header'),
  ('header.menu.products', 'en', 'Our Products', 'header'),
  ('header.menu.products', 'ar', 'مجموعات منتجاتنا', 'header'),
  ('header.menu.products', 'ru', 'Наши товары', 'header'),
  ('header.menu.services', 'tr', 'Hizmetlerimiz', 'header'),
  ('header.menu.services', 'en', 'Our Services', 'header'),
  ('header.menu.services', 'ar', 'خدماتنا', 'header'),
  ('header.menu.services', 'ru', 'Наши услуги', 'header'),
  ('header.menu.calculator', 'tr', 'Ağırlık Hesaplama', 'header'),
  ('header.menu.calculator', 'en', 'Weight Calculator', 'header'),
  ('header.menu.calculator', 'ar', 'حاسبة الوزن', 'header'),
  ('header.menu.calculator', 'ru', 'Калькулятор веса', 'header'),
  ('header.menu.corporate', 'tr', 'Kurumsal', 'header'),
  ('header.menu.corporate', 'en', 'Corporate', 'header'),
  ('header.menu.corporate', 'ar', 'الشركة', 'header'),
  ('header.menu.corporate', 'ru', 'О компании', 'header'),
  ('header.menu.about', 'tr', 'Hakkımızda', 'header'),
  ('header.menu.about', 'en', 'About Us', 'header'),
  ('header.menu.about', 'ar', 'من نحن', 'header'),
  ('header.menu.about', 'ru', 'О нас', 'header'),
  ('header.menu.partners', 'tr', 'Çözüm Ortakları', 'header'),
  ('header.menu.partners', 'en', 'Solution Partners', 'header'),
  ('header.menu.partners', 'ar', 'شركاء الحلول', 'header'),
  ('header.menu.partners', 'ru', 'Партнёры', 'header'),
  ('header.menu.iban', 'tr', 'IBAN Bilgilerimiz', 'header'),
  ('header.menu.iban', 'en', 'Bank Accounts (IBAN)', 'header'),
  ('header.menu.iban', 'ar', 'الحسابات المصرفية (IBAN)', 'header'),
  ('header.menu.iban', 'ru', 'Банковские реквизиты (IBAN)', 'header'),
  ('header.menu.faq', 'tr', 'Sıkça Sorulan Sorular', 'header'),
  ('header.menu.faq', 'en', 'FAQ', 'header'),
  ('header.menu.faq', 'ar', 'الأسئلة الشائعة', 'header'),
  ('header.menu.faq', 'ru', 'Часто задаваемые вопросы', 'header'),
  ('header.menu.mail_order', 'tr', 'Mail Order Formu', 'header'),
  ('header.menu.mail_order', 'en', 'Mail Order Form', 'header'),
  ('header.menu.mail_order', 'ar', 'نموذج الطلب البريدي', 'header'),
  ('header.menu.mail_order', 'ru', 'Форма заказа по почте', 'header'),
  ('header.menu.loyalty', 'tr', 'Sadakat Programı', 'header'),
  ('header.menu.loyalty', 'en', 'Loyalty Program', 'header'),
  ('header.menu.loyalty', 'ar', 'برنامج الولاء', 'header'),
  ('header.menu.loyalty', 'ru', 'Программа лояльности', 'header'),
  ('header.menu.news', 'tr', 'Haberler & Basın', 'header'),
  ('header.menu.news', 'en', 'News & Press', 'header'),
  ('header.menu.news', 'ar', 'الأخبار والإعلام', 'header'),
  ('header.menu.news', 'ru', 'Новости и пресса', 'header'),
  ('header.menu.blog', 'tr', 'Tekcan''dan Haberler', 'header'),
  ('header.menu.blog', 'en', 'News from Tekcan', 'header'),
  ('header.menu.blog', 'ar', 'أخبار من تكجان', 'header'),
  ('header.menu.blog', 'ru', 'Новости Tekcan', 'header'),
  ('header.menu.gallery', 'tr', 'Galeri', 'header'),
  ('header.menu.gallery', 'en', 'Gallery', 'header'),
  ('header.menu.gallery', 'ar', 'المعرض', 'header'),
  ('header.menu.gallery', 'ru', 'Галерея', 'header'),
  ('header.menu.contact', 'tr', 'İletişim', 'header'),
  ('header.menu.contact', 'en', 'Contact', 'header'),
  ('header.menu.contact', 'ar', 'اتصل بنا', 'header'),
  ('header.menu.contact', 'ru', 'Контакты', 'header'),
  ('header.menu.home', 'tr', 'Anasayfa', 'header'),
  ('header.menu.home', 'en', 'Home', 'header'),
  ('header.menu.home', 'ar', 'الرئيسية', 'header'),
  ('header.menu.home', 'ru', 'Главная', 'header'),
  ('header.menu.menu_label', 'tr', 'Menü', 'header'),
  ('header.menu.menu_label', 'en', 'Menu', 'header'),
  ('header.menu.menu_label', 'ar', 'القائمة', 'header'),
  ('header.menu.menu_label', 'ru', 'Меню', 'header'),
  ('header.menu.tagline', 'tr', 'Demir adına Herşey', 'header'),
  ('header.menu.tagline', 'en', 'Everything in the name of Steel', 'header'),
  ('header.menu.tagline', 'ar', 'كل ما يخص الحديد', 'header'),
  ('header.menu.tagline', 'ru', 'Всё для металла', 'header'),
  ('footer.tagline', 'tr', 'Demir adına Herşey…', 'footer'),
  ('footer.tagline', 'en', 'Everything in the name of Steel…', 'footer'),
  ('footer.tagline', 'ar', 'كل ما يخص الحديد…', 'footer'),
  ('footer.tagline', 'ru', 'Всё для металла…', 'footer'),
  ('footer.about_short', 'tr', 'Ticaret ile bitmeyen <em>dostluk</em>.', 'footer'),
  ('footer.about_short', 'en', 'Friendship that goes beyond <em>business</em>.', 'footer'),
  ('footer.about_short', 'ar', 'صداقة تتجاوز حدود <em>التجارة</em>.', 'footer'),
  ('footer.about_short', 'ru', 'Дружба, которая выходит за рамки <em>бизнеса</em>.', 'footer'),
  ('footer.contact', 'tr', 'İletişim', 'footer'),
  ('footer.contact', 'en', 'Contact', 'footer'),
  ('footer.contact', 'ar', 'اتصل بنا', 'footer'),
  ('footer.contact', 'ru', 'Контакты', 'footer'),
  ('footer.quick_links', 'tr', 'Hızlı Erişim', 'footer'),
  ('footer.quick_links', 'en', 'Quick Links', 'footer'),
  ('footer.quick_links', 'ar', 'روابط سريعة', 'footer'),
  ('footer.quick_links', 'ru', 'Быстрые ссылки', 'footer'),
  ('footer.copyright', 'tr', 'Tüm hakları saklıdır', 'footer'),
  ('footer.copyright', 'en', 'All rights reserved', 'footer'),
  ('footer.copyright', 'ar', 'جميع الحقوق محفوظة', 'footer'),
  ('footer.copyright', 'ru', 'Все права защищены', 'footer'),
  ('footer.kvkk', 'tr', 'KVKK Aydınlatma Metni', 'footer'),
  ('footer.kvkk', 'en', 'Privacy Notice (KVKK)', 'footer'),
  ('footer.kvkk', 'ar', 'سياسة الخصوصية', 'footer'),
  ('footer.kvkk', 'ru', 'Политика конфиденциальности', 'footer'),
  ('footer.cookie_policy', 'tr', 'Çerez Politikası', 'footer'),
  ('footer.cookie_policy', 'en', 'Cookie Policy', 'footer'),
  ('footer.cookie_policy', 'ar', 'سياسة ملفات تعريف الارتباط', 'footer'),
  ('footer.cookie_policy', 'ru', 'Политика использования cookies', 'footer'),
  ('footer.design_by', 'tr', 'Tasarım', 'footer'),
  ('footer.design_by', 'en', 'Designed by', 'footer'),
  ('footer.design_by', 'ar', 'تصميم', 'footer'),
  ('footer.design_by', 'ru', 'Дизайн', 'footer'),
  ('footer.address', 'tr', 'Adres', 'footer'),
  ('footer.address', 'en', 'Address', 'footer'),
  ('footer.address', 'ar', 'العنوان', 'footer'),
  ('footer.address', 'ru', 'Адрес', 'footer'),
  ('footer.phone', 'tr', 'Telefon', 'footer'),
  ('footer.phone', 'en', 'Phone', 'footer'),
  ('footer.phone', 'ar', 'الهاتف', 'footer'),
  ('footer.phone', 'ru', 'Телефон', 'footer'),
  ('footer.email', 'tr', 'E-posta', 'footer'),
  ('footer.email', 'en', 'Email', 'footer'),
  ('footer.email', 'ar', 'البريد الإلكتروني', 'footer'),
  ('footer.email', 'ru', 'E-mail', 'footer'),
  ('footer.working_hours', 'tr', 'Çalışma Saatleri', 'footer'),
  ('footer.working_hours', 'en', 'Working Hours', 'footer'),
  ('footer.working_hours', 'ar', 'ساعات العمل', 'footer'),
  ('footer.working_hours', 'ru', 'Часы работы', 'footer'),
  ('footer.weekdays', 'tr', 'Pazartesi-Cuma: 08:00-18:00', 'footer'),
  ('footer.weekdays', 'en', 'Monday-Friday: 08:00-18:00', 'footer'),
  ('footer.weekdays', 'ar', 'الإثنين-الجمعة: 08:00-18:00', 'footer'),
  ('footer.weekdays', 'ru', 'Пн-Пт: 08:00-18:00', 'footer'),
  ('footer.saturday', 'tr', 'Cumartesi: 08:00-13:00', 'footer'),
  ('footer.saturday', 'en', 'Saturday: 08:00-13:00', 'footer'),
  ('footer.saturday', 'ar', 'السبت: 08:00-13:00', 'footer'),
  ('footer.saturday', 'ru', 'Суббота: 08:00-13:00', 'footer'),
  ('btn.call_now', 'tr', 'Hemen Ara', 'btn'),
  ('btn.call_now', 'en', 'Call Now', 'btn'),
  ('btn.call_now', 'ar', 'اتصل الآن', 'btn'),
  ('btn.call_now', 'ru', 'Позвонить сейчас', 'btn'),
  ('btn.whatsapp', 'tr', 'WhatsApp', 'btn'),
  ('btn.whatsapp', 'en', 'WhatsApp', 'btn'),
  ('btn.whatsapp', 'ar', 'واتساب', 'btn'),
  ('btn.whatsapp', 'ru', 'WhatsApp', 'btn'),
  ('btn.contact_us', 'tr', 'Bize Ulaşın', 'btn'),
  ('btn.contact_us', 'en', 'Contact Us', 'btn'),
  ('btn.contact_us', 'ar', 'تواصل معنا', 'btn'),
  ('btn.contact_us', 'ru', 'Свяжитесь с нами', 'btn'),
  ('btn.get_quote', 'tr', 'Teklif Al', 'btn'),
  ('btn.get_quote', 'en', 'Get Quote', 'btn'),
  ('btn.get_quote', 'ar', 'احصل على عرض سعر', 'btn'),
  ('btn.get_quote', 'ru', 'Получить предложение', 'btn'),
  ('btn.request_quote', 'tr', 'Fiyat Teklifi İste', 'btn'),
  ('btn.request_quote', 'en', 'Request a Quote', 'btn'),
  ('btn.request_quote', 'ar', 'اطلب عرض أسعار', 'btn'),
  ('btn.request_quote', 'ru', 'Запросить предложение', 'btn'),
  ('btn.send', 'tr', 'Gönder', 'btn'),
  ('btn.send', 'en', 'Send', 'btn'),
  ('btn.send', 'ar', 'إرسال', 'btn'),
  ('btn.send', 'ru', 'Отправить', 'btn'),
  ('btn.submit', 'tr', 'Gönder', 'btn'),
  ('btn.submit', 'en', 'Submit', 'btn'),
  ('btn.submit', 'ar', 'إرسال', 'btn'),
  ('btn.submit', 'ru', 'Отправить', 'btn'),
  ('btn.read_more', 'tr', 'Devamını Oku', 'btn'),
  ('btn.read_more', 'en', 'Read More', 'btn'),
  ('btn.read_more', 'ar', 'اقرأ المزيد', 'btn'),
  ('btn.read_more', 'ru', 'Читать далее', 'btn'),
  ('btn.view_more', 'tr', 'Daha Fazlası', 'btn'),
  ('btn.view_more', 'en', 'View More', 'btn'),
  ('btn.view_more', 'ar', 'عرض المزيد', 'btn'),
  ('btn.view_more', 'ru', 'Показать ещё', 'btn'),
  ('btn.view_all', 'tr', 'Tümünü Göster', 'btn'),
  ('btn.view_all', 'en', 'View All', 'btn'),
  ('btn.view_all', 'ar', 'عرض الكل', 'btn'),
  ('btn.view_all', 'ru', 'Показать все', 'btn'),
  ('btn.calculate', 'tr', 'Hesapla', 'btn'),
  ('btn.calculate', 'en', 'Calculate', 'btn'),
  ('btn.calculate', 'ar', 'احسب', 'btn'),
  ('btn.calculate', 'ru', 'Рассчитать', 'btn'),
  ('btn.search', 'tr', 'Ara', 'btn'),
  ('btn.search', 'en', 'Search', 'btn'),
  ('btn.search', 'ar', 'بحث', 'btn'),
  ('btn.search', 'ru', 'Поиск', 'btn'),
  ('btn.back', 'tr', 'Geri', 'btn'),
  ('btn.back', 'en', 'Back', 'btn'),
  ('btn.back', 'ar', 'رجوع', 'btn'),
  ('btn.back', 'ru', 'Назад', 'btn'),
  ('btn.continue', 'tr', 'Devam Et', 'btn'),
  ('btn.continue', 'en', 'Continue', 'btn'),
  ('btn.continue', 'ar', 'متابعة', 'btn'),
  ('btn.continue', 'ru', 'Продолжить', 'btn'),
  ('btn.close', 'tr', 'Kapat', 'btn'),
  ('btn.close', 'en', 'Close', 'btn'),
  ('btn.close', 'ar', 'إغلاق', 'btn'),
  ('btn.close', 'ru', 'Закрыть', 'btn'),
  ('btn.download', 'tr', 'İndir', 'btn'),
  ('btn.download', 'en', 'Download', 'btn'),
  ('btn.download', 'ar', 'تحميل', 'btn'),
  ('btn.download', 'ru', 'Скачать', 'btn'),
  ('btn.print', 'tr', 'Yazdır', 'btn'),
  ('btn.print', 'en', 'Print', 'btn'),
  ('btn.print', 'ar', 'طباعة', 'btn'),
  ('btn.print', 'ru', 'Печать', 'btn'),
  ('btn.share', 'tr', 'Paylaş', 'btn'),
  ('btn.share', 'en', 'Share', 'btn'),
  ('btn.share', 'ar', 'مشاركة', 'btn'),
  ('btn.share', 'ru', 'Поделиться', 'btn'),
  ('btn.detail', 'tr', 'Detay', 'btn'),
  ('btn.detail', 'en', 'Detail', 'btn'),
  ('btn.detail', 'ar', 'التفاصيل', 'btn'),
  ('btn.detail', 'ru', 'Подробности', 'btn'),
  ('btn.view_detail', 'tr', 'Detayları Gör', 'btn'),
  ('btn.view_detail', 'en', 'View Detail', 'btn'),
  ('btn.view_detail', 'ar', 'عرض التفاصيل', 'btn'),
  ('btn.view_detail', 'ru', 'Посмотреть детали', 'btn'),
  ('btn.add_cart', 'tr', 'Sepete Ekle', 'btn'),
  ('btn.add_cart', 'en', 'Add to Cart', 'btn'),
  ('btn.add_cart', 'ar', 'أضف إلى السلة', 'btn'),
  ('btn.add_cart', 'ru', 'В корзину', 'btn'),
  ('btn.show_offers', 'tr', 'Teklifleri Gör', 'btn'),
  ('btn.show_offers', 'en', 'View Offers', 'btn'),
  ('btn.show_offers', 'ar', 'عرض العروض', 'btn'),
  ('btn.show_offers', 'ru', 'Смотреть предложения', 'btn'),
  ('form.name', 'tr', 'Ad Soyad', 'form'),
  ('form.name', 'en', 'Full Name', 'form'),
  ('form.name', 'ar', 'الاسم الكامل', 'form'),
  ('form.name', 'ru', 'Имя и фамилия', 'form'),
  ('form.first_name', 'tr', 'Ad', 'form'),
  ('form.first_name', 'en', 'First Name', 'form'),
  ('form.first_name', 'ar', 'الاسم', 'form'),
  ('form.first_name', 'ru', 'Имя', 'form'),
  ('form.last_name', 'tr', 'Soyad', 'form'),
  ('form.last_name', 'en', 'Last Name', 'form'),
  ('form.last_name', 'ar', 'اللقب', 'form'),
  ('form.last_name', 'ru', 'Фамилия', 'form'),
  ('form.email', 'tr', 'E-posta', 'form'),
  ('form.email', 'en', 'Email', 'form'),
  ('form.email', 'ar', 'البريد الإلكتروني', 'form'),
  ('form.email', 'ru', 'Электронная почта', 'form'),
  ('form.phone', 'tr', 'Telefon', 'form'),
  ('form.phone', 'en', 'Phone', 'form'),
  ('form.phone', 'ar', 'رقم الهاتف', 'form'),
  ('form.phone', 'ru', 'Телефон', 'form'),
  ('form.company', 'tr', 'Firma Adı', 'form'),
  ('form.company', 'en', 'Company Name', 'form'),
  ('form.company', 'ar', 'اسم الشركة', 'form'),
  ('form.company', 'ru', 'Название компании', 'form'),
  ('form.subject', 'tr', 'Konu', 'form'),
  ('form.subject', 'en', 'Subject', 'form'),
  ('form.subject', 'ar', 'الموضوع', 'form'),
  ('form.subject', 'ru', 'Тема', 'form'),
  ('form.message', 'tr', 'Mesajınız', 'form'),
  ('form.message', 'en', 'Your Message', 'form'),
  ('form.message', 'ar', 'رسالتك', 'form'),
  ('form.message', 'ru', 'Ваше сообщение', 'form'),
  ('form.country', 'tr', 'Ülke', 'form'),
  ('form.country', 'en', 'Country', 'form'),
  ('form.country', 'ar', 'الدولة', 'form'),
  ('form.country', 'ru', 'Страна', 'form'),
  ('form.city', 'tr', 'Şehir', 'form'),
  ('form.city', 'en', 'City', 'form'),
  ('form.city', 'ar', 'المدينة', 'form'),
  ('form.city', 'ru', 'Город', 'form'),
  ('form.address', 'tr', 'Adres', 'form'),
  ('form.address', 'en', 'Address', 'form'),
  ('form.address', 'ar', 'العنوان', 'form'),
  ('form.address', 'ru', 'Адрес', 'form'),
  ('form.tax_id', 'tr', 'Vergi No / TCKN', 'form'),
  ('form.tax_id', 'en', 'Tax ID', 'form'),
  ('form.tax_id', 'ar', 'رقم الضريبة', 'form'),
  ('form.tax_id', 'ru', 'ИНН', 'form'),
  ('form.tax_office', 'tr', 'Vergi Dairesi', 'form'),
  ('form.tax_office', 'en', 'Tax Office', 'form'),
  ('form.tax_office', 'ar', 'مكتب الضرائب', 'form'),
  ('form.tax_office', 'ru', 'Налоговая инспекция', 'form'),
  ('form.required', 'tr', 'Zorunlu alan', 'form'),
  ('form.required', 'en', 'Required field', 'form'),
  ('form.required', 'ar', 'حقل إلزامي', 'form'),
  ('form.required', 'ru', 'Обязательное поле', 'form'),
  ('form.optional', 'tr', 'İsteğe bağlı', 'form'),
  ('form.optional', 'en', 'Optional', 'form'),
  ('form.optional', 'ar', 'اختياري', 'form'),
  ('form.optional', 'ru', 'Необязательно', 'form'),
  ('form.kvkk_consent', 'tr', 'KVKK Aydınlatma Metni', 'form'),
  ('form.kvkk_consent', 'en', 'Privacy Notice', 'form'),
  ('form.kvkk_consent', 'ar', 'سياسة الخصوصية', 'form'),
  ('form.kvkk_consent', 'ru', 'Политика конфиденциальности', 'form'),
  ('form.kvkk_accept', 'tr', 'KVKK Aydınlatma Metni''ni okudum, kişisel verilerimin işlenmesine onay veriyorum.', 'form'),
  ('form.kvkk_accept', 'en', 'I have read the Privacy Notice and consent to the processing of my personal data.', 'form'),
  ('form.kvkk_accept', 'ar', 'لقد قرأت سياسة الخصوصية وأوافق على معالجة بياناتي الشخصية.', 'form'),
  ('form.kvkk_accept', 'ru', 'Я ознакомлен с Политикой конфиденциальности и даю согласие на обработку персональных данных.', 'form'),
  ('form.success', 'tr', 'Mesajınız başarıyla iletildi. En kısa sürede dönüş yapacağız.', 'form'),
  ('form.success', 'en', 'Your message has been sent. We will respond as soon as possible.', 'form'),
  ('form.success', 'ar', 'تم إرسال رسالتك بنجاح. سنرد عليك في أقرب وقت ممكن.', 'form'),
  ('form.success', 'ru', 'Ваше сообщение отправлено. Мы ответим вам в ближайшее время.', 'form'),
  ('form.error', 'tr', 'Bir hata oluştu, lütfen tekrar deneyiniz.', 'form'),
  ('form.error', 'en', 'An error occurred, please try again.', 'form'),
  ('form.error', 'ar', 'حدث خطأ، يرجى المحاولة مرة أخرى.', 'form'),
  ('form.error', 'ru', 'Произошла ошибка, попробуйте ещё раз.', 'form'),
  ('bc.home', 'tr', 'Anasayfa', 'bc'),
  ('bc.home', 'en', 'Home', 'bc'),
  ('bc.home', 'ar', 'الرئيسية', 'bc'),
  ('bc.home', 'ru', 'Главная', 'bc'),
  ('bc.products', 'tr', 'Ürünler', 'bc'),
  ('bc.products', 'en', 'Products', 'bc'),
  ('bc.products', 'ar', 'المنتجات', 'bc'),
  ('bc.products', 'ru', 'Продукция', 'bc'),
  ('bc.services', 'tr', 'Hizmetler', 'bc'),
  ('bc.services', 'en', 'Services', 'bc'),
  ('bc.services', 'ar', 'الخدمات', 'bc'),
  ('bc.services', 'ru', 'Услуги', 'bc'),
  ('bc.industrial_capabilities', 'tr', 'Endüstriyel Yetkinlikler', 'bc'),
  ('bc.industrial_capabilities', 'en', 'Industrial Capabilities', 'bc'),
  ('bc.industrial_capabilities', 'ar', 'القدرات الصناعية', 'bc'),
  ('bc.industrial_capabilities', 'ru', 'Промышленные возможности', 'bc'),
  ('bc.blog', 'tr', 'Haberler', 'bc'),
  ('bc.blog', 'en', 'News', 'bc'),
  ('bc.blog', 'ar', 'الأخبار', 'bc'),
  ('bc.blog', 'ru', 'Новости', 'bc'),
  ('label.products', 'tr', 'Ürünler', 'label'),
  ('label.products', 'en', 'Products', 'label'),
  ('label.products', 'ar', 'المنتجات', 'label'),
  ('label.products', 'ru', 'Продукция', 'label'),
  ('label.show_all_products', 'tr', 'Tüm ürün kataloğunu görüntüle', 'label'),
  ('label.show_all_products', 'en', 'View full product catalog', 'label'),
  ('label.show_all_products', 'ar', 'عرض كتالوج المنتجات الكامل', 'label'),
  ('label.show_all_products', 'ru', 'Просмотреть весь каталог', 'label'),
  ('label.categories', 'tr', 'Kategoriler', 'label'),
  ('label.categories', 'en', 'Categories', 'label'),
  ('label.categories', 'ar', 'الفئات', 'label'),
  ('label.categories', 'ru', 'Категории', 'label'),
  ('label.related_products', 'tr', 'İlgili Ürünler', 'label'),
  ('label.related_products', 'en', 'Related Products', 'label'),
  ('label.related_products', 'ar', 'منتجات ذات صلة', 'label'),
  ('label.related_products', 'ru', 'Похожие товары', 'label'),
  ('label.related_services', 'tr', 'Diğer Hizmetlerimiz', 'label'),
  ('label.related_services', 'en', 'Other Services', 'label'),
  ('label.related_services', 'ar', 'خدمات أخرى', 'label'),
  ('label.related_services', 'ru', 'Другие услуги', 'label'),
  ('label.features', 'tr', 'Özellikler', 'label'),
  ('label.features', 'en', 'Features', 'label'),
  ('label.features', 'ar', 'الميزات', 'label'),
  ('label.features', 'ru', 'Особенности', 'label'),
  ('label.specifications', 'tr', 'Teknik Özellikler', 'label'),
  ('label.specifications', 'en', 'Technical Specifications', 'label'),
  ('label.specifications', 'ar', 'المواصفات الفنية', 'label'),
  ('label.specifications', 'ru', 'Технические характеристики', 'label'),
  ('label.advantages', 'tr', 'Avantajlar', 'label'),
  ('label.advantages', 'en', 'Advantages', 'label'),
  ('label.advantages', 'ar', 'المزايا', 'label'),
  ('label.advantages', 'ru', 'Преимущества', 'label'),
  ('label.applications', 'tr', 'Kullanım Alanları', 'label'),
  ('label.applications', 'en', 'Applications', 'label'),
  ('label.applications', 'ar', 'التطبيقات', 'label'),
  ('label.applications', 'ru', 'Области применения', 'label'),
  ('label.description', 'tr', 'Açıklama', 'label'),
  ('label.description', 'en', 'Description', 'label'),
  ('label.description', 'ar', 'الوصف', 'label'),
  ('label.description', 'ru', 'Описание', 'label'),
  ('label.details', 'tr', 'Detaylar', 'label'),
  ('label.details', 'en', 'Details', 'label'),
  ('label.details', 'ar', 'التفاصيل', 'label'),
  ('label.details', 'ru', 'Подробности', 'label'),
  ('label.gallery', 'tr', 'Görseller', 'label'),
  ('label.gallery', 'en', 'Gallery', 'label'),
  ('label.gallery', 'ar', 'الصور', 'label'),
  ('label.gallery', 'ru', 'Изображения', 'label'),
  ('label.share_this', 'tr', 'Paylaş', 'label'),
  ('label.share_this', 'en', 'Share', 'label'),
  ('label.share_this', 'ar', 'مشاركة', 'label'),
  ('label.share_this', 'ru', 'Поделиться', 'label'),
  ('label.publish_date', 'tr', 'Yayın Tarihi', 'label'),
  ('label.publish_date', 'en', 'Published', 'label'),
  ('label.publish_date', 'ar', 'تاريخ النشر', 'label'),
  ('label.publish_date', 'ru', 'Опубликовано', 'label'),
  ('label.author', 'tr', 'Yazar', 'label'),
  ('label.author', 'en', 'Author', 'label'),
  ('label.author', 'ar', 'الكاتب', 'label'),
  ('label.author', 'ru', 'Автор', 'label'),
  ('label.category', 'tr', 'Kategori', 'label'),
  ('label.category', 'en', 'Category', 'label'),
  ('label.category', 'ar', 'الفئة', 'label'),
  ('label.category', 'ru', 'Категория', 'label'),
  ('label.tags', 'tr', 'Etiketler', 'label'),
  ('label.tags', 'en', 'Tags', 'label'),
  ('label.tags', 'ar', 'الوسوم', 'label'),
  ('label.tags', 'ru', 'Теги', 'label'),
  ('label.year', 'tr', 'Yıl', 'label'),
  ('label.year', 'en', 'Year', 'label'),
  ('label.year', 'ar', 'السنة', 'label'),
  ('label.year', 'ru', 'Год', 'label'),
  ('label.experience', 'tr', 'Deneyim', 'label'),
  ('label.experience', 'en', 'Experience', 'label'),
  ('label.experience', 'ar', 'الخبرة', 'label'),
  ('label.experience', 'ru', 'Опыт', 'label'),
  ('label.precision', 'tr', 'Hassasiyet', 'label'),
  ('label.precision', 'en', 'Precision', 'label'),
  ('label.precision', 'ar', 'الدقة', 'label'),
  ('label.precision', 'ru', 'Точность', 'label'),
  ('label.same_day', 'tr', 'Aynı Gün', 'label'),
  ('label.same_day', 'en', 'Same Day', 'label'),
  ('label.same_day', 'ar', 'في نفس اليوم', 'label'),
  ('label.same_day', 'ru', 'В тот же день', 'label'),
  ('label.quote', 'tr', 'Teklif', 'label'),
  ('label.quote', 'en', 'Quote', 'label'),
  ('label.quote', 'ar', 'عرض سعر', 'label'),
  ('label.quote', 'ru', 'Предложение', 'label'),
  ('label.shipping_81_cities', 'tr', '81 İl', 'label'),
  ('label.shipping_81_cities', 'en', '81 Cities', 'label'),
  ('label.shipping_81_cities', 'ar', '81 مدينة', 'label'),
  ('label.shipping_81_cities', 'ru', '81 город', 'label'),
  ('label.shipping', 'tr', 'Sevkiyat', 'label'),
  ('label.shipping', 'en', 'Shipping', 'label'),
  ('label.shipping', 'ar', 'الشحن', 'label'),
  ('label.shipping', 'ru', 'Доставка', 'label'),
  ('contact.title', 'tr', 'İletişim', 'contact'),
  ('contact.title', 'en', 'Contact', 'contact'),
  ('contact.title', 'ar', 'اتصل بنا', 'contact'),
  ('contact.title', 'ru', 'Контакты', 'contact'),
  ('contact.lead', 'tr', 'Demir-çelik tedariği için bize ulaşın. 24 saat içinde yanıt veriyoruz.', 'contact'),
  ('contact.lead', 'en', 'Contact us for steel supply. We respond within 24 hours.', 'contact'),
  ('contact.lead', 'ar', 'تواصل معنا لتوريد الفولاذ. نرد خلال 24 ساعة.', 'contact'),
  ('contact.lead', 'ru', 'Свяжитесь с нами для поставок металла. Мы отвечаем в течение 24 часов.', 'contact'),
  ('contact.location_title', 'tr', 'Merkez Ofis', 'contact'),
  ('contact.location_title', 'en', 'Headquarters', 'contact'),
  ('contact.location_title', 'ar', 'المقر الرئيسي', 'contact'),
  ('contact.location_title', 'ru', 'Главный офис', 'contact'),
  ('contact.send_message', 'tr', 'Bize Mesaj Gönderin', 'contact'),
  ('contact.send_message', 'en', 'Send Us a Message', 'contact'),
  ('contact.send_message', 'ar', 'أرسل لنا رسالة', 'contact'),
  ('contact.send_message', 'ru', 'Отправьте нам сообщение', 'contact'),
  ('services.eyebrow.precision', 'tr', 'Hassas Kesim Teknolojisi', 'services'),
  ('services.eyebrow.precision', 'en', 'Precision Cutting Technology', 'services'),
  ('services.eyebrow.precision', 'ar', 'تقنية القطع الدقيق', 'services'),
  ('services.eyebrow.precision', 'ru', 'Технология точной резки', 'services'),
  ('services.eyebrow.thick_cutting', 'tr', 'Kalın Levha Kesimi', 'services'),
  ('services.eyebrow.thick_cutting', 'en', 'Thick Plate Cutting', 'services'),
  ('services.eyebrow.thick_cutting', 'ar', 'قطع الألواح السميكة', 'services'),
  ('services.eyebrow.thick_cutting', 'ru', 'Резка толстых листов', 'services'),
  ('services.eyebrow.architectural', 'tr', 'Mimari Sac Üretimi', 'services'),
  ('services.eyebrow.architectural', 'en', 'Architectural Sheet Manufacturing', 'services'),
  ('services.eyebrow.architectural', 'ar', 'تصنيع الألواح المعمارية', 'services'),
  ('services.eyebrow.architectural', 'ru', 'Производство архитектурных листов', 'services'),
  ('services.eyebrow.industrial_capability', 'tr', 'Endüstriyel Yetkinlik', 'services'),
  ('services.eyebrow.industrial_capability', 'en', 'Industrial Capability', 'services'),
  ('services.eyebrow.industrial_capability', 'ar', 'القدرة الصناعية', 'services'),
  ('services.eyebrow.industrial_capability', 'ru', 'Промышленные возможности', 'services'),
  ('services.our_advantages', 'tr', 'Avantajlarımız', 'services'),
  ('services.our_advantages', 'en', 'Our Advantages', 'services'),
  ('services.our_advantages', 'ar', 'مزايانا', 'services'),
  ('services.our_advantages', 'ru', 'Наши преимущества', 'services'),
  ('services.technical_specs', 'tr', 'Teknik Özellikler', 'services'),
  ('services.technical_specs', 'en', 'Technical Specifications', 'services'),
  ('services.technical_specs', 'ar', 'المواصفات الفنية', 'services'),
  ('services.technical_specs', 'ru', 'Технические характеристики', 'services'),
  ('services.process_flow', 'tr', 'Süreç Akışı', 'services'),
  ('services.process_flow', 'en', 'Process Flow', 'services'),
  ('services.process_flow', 'ar', 'سير العملية', 'services'),
  ('services.process_flow', 'ru', 'Технологический процесс', 'services'),
  ('services.quote_request', 'tr', 'Teklif Talebi', 'services'),
  ('services.quote_request', 'en', 'Quote Request', 'services'),
  ('services.quote_request', 'ar', 'طلب عرض سعر', 'services'),
  ('services.quote_request', 'ru', 'Запрос предложения', 'services'),
  ('services.send_dxf', 'tr', 'DXF/DWG dosyanızı gönderin, aynı gün teklif alın', 'services'),
  ('services.send_dxf', 'en', 'Send your DXF/DWG file, get a same-day quote', 'services'),
  ('services.send_dxf', 'ar', 'أرسل ملف DXF/DWG وستحصل على عرض السعر في نفس اليوم', 'services'),
  ('services.send_dxf', 'ru', 'Отправьте файл DXF/DWG и получите предложение в тот же день', 'services'),
  ('home.hero_eyebrow', 'tr', 'Sektörel Tedarik · Endüstriyel Hassasiyet', 'home'),
  ('home.hero_eyebrow', 'en', 'Industry Supply · Industrial Precision', 'home'),
  ('home.hero_eyebrow', 'ar', 'إمدادات صناعية · دقة صناعية', 'home'),
  ('home.hero_eyebrow', 'ru', 'Промышленное снабжение · Точность', 'home'),
  ('home.industrial_capabilities', 'tr', 'Endüstriyel Yetkinliklerimiz', 'home'),
  ('home.industrial_capabilities', 'en', 'Our Industrial Capabilities', 'home'),
  ('home.industrial_capabilities', 'ar', 'قدراتنا الصناعية', 'home'),
  ('home.industrial_capabilities', 'ru', 'Наши производственные возможности', 'home'),
  ('home.product_groups', 'tr', 'Ürün Gruplarımız', 'home'),
  ('home.product_groups', 'en', 'Product Groups', 'home'),
  ('home.product_groups', 'ar', 'مجموعات المنتجات', 'home'),
  ('home.product_groups', 'ru', 'Товарные группы', 'home'),
  ('home.our_capabilities', 'tr', 'Yetkinliklerimiz', 'home'),
  ('home.our_capabilities', 'en', 'Our Capabilities', 'home'),
  ('home.our_capabilities', 'ar', 'قدراتنا', 'home'),
  ('home.our_capabilities', 'ru', 'Наши возможности', 'home'),
  ('home.about_short', 'tr', 'Tedarikten Üretime', 'home'),
  ('home.about_short', 'en', 'From Supply to Production', 'home'),
  ('home.about_short', 'ar', 'من التوريد إلى الإنتاج', 'home'),
  ('home.about_short', 'ru', 'От поставки до производства', 'home'),
  ('home.testimonials', 'tr', 'Müşteri Görüşleri', 'home'),
  ('home.testimonials', 'en', 'Testimonials', 'home'),
  ('home.testimonials', 'ar', 'آراء العملاء', 'home'),
  ('home.testimonials', 'ru', 'Отзывы клиентов', 'home'),
  ('home.partners', 'tr', 'Çözüm Ortaklarımız', 'home'),
  ('home.partners', 'en', 'Our Partners', 'home'),
  ('home.partners', 'ar', 'شركاؤنا', 'home'),
  ('home.partners', 'ru', 'Наши партнёры', 'home'),
  ('home.latest_news', 'tr', 'Son Haberler', 'home'),
  ('home.latest_news', 'en', 'Latest News', 'home'),
  ('home.latest_news', 'ar', 'آخر الأخبار', 'home'),
  ('home.latest_news', 'ru', 'Последние новости', 'home'),
  ('home.contact_cta_title', 'tr', 'Projeniz için özel teklif', 'home'),
  ('home.contact_cta_title', 'en', 'Custom quote for your project', 'home'),
  ('home.contact_cta_title', 'ar', 'عرض سعر مخصص لمشروعك', 'home'),
  ('home.contact_cta_title', 'ru', 'Индивидуальное предложение для вашего проекта', 'home'),
  ('home.contact_cta_lead', 'tr', 'Demir-çelik ihtiyaçlarınız için bize ulaşın, en uygun fiyat ve termin garantisiyle yanıtlayalım.', 'home'),
  ('home.contact_cta_lead', 'en', 'Contact us for your steel requirements; we respond with the best price and delivery time.', 'home'),
  ('home.contact_cta_lead', 'ar', 'تواصل معنا لاحتياجاتك من الفولاذ، ونرد عليك بأفضل سعر وأسرع تسليم.', 'home'),
  ('home.contact_cta_lead', 'ru', 'Свяжитесь с нами по вашим потребностям в металле — ответим лучшей ценой и сроками.', 'home'),
  ('home.guaranteed', 'tr', 'Güvenle', 'home'),
  ('home.guaranteed', 'en', 'Reliably', 'home'),
  ('home.guaranteed', 'ar', 'بثقة', 'home'),
  ('home.guaranteed', 'ru', 'Надёжно', 'home'),
  ('home.developments', 'tr', 'Gelişmeler', 'home'),
  ('home.developments', 'en', 'Developments', 'home'),
  ('home.developments', 'ar', 'التطورات', 'home'),
  ('home.developments', 'ru', 'Развитие', 'home'),
  ('product.weight_per_meter', 'tr', 'Metre Ağırlığı', 'product'),
  ('product.weight_per_meter', 'en', 'Weight per Meter', 'product'),
  ('product.weight_per_meter', 'ar', 'الوزن لكل متر', 'product'),
  ('product.weight_per_meter', 'ru', 'Вес на метр', 'product'),
  ('product.weight_per_piece', 'tr', 'Adet Ağırlığı', 'product'),
  ('product.weight_per_piece', 'en', 'Weight per Piece', 'product'),
  ('product.weight_per_piece', 'ar', 'الوزن لكل قطعة', 'product'),
  ('product.weight_per_piece', 'ru', 'Вес за штуку', 'product'),
  ('product.dimensions', 'tr', 'Boyutlar', 'product'),
  ('product.dimensions', 'en', 'Dimensions', 'product'),
  ('product.dimensions', 'ar', 'الأبعاد', 'product'),
  ('product.dimensions', 'ru', 'Размеры', 'product'),
  ('product.thickness', 'tr', 'Kalınlık', 'product'),
  ('product.thickness', 'en', 'Thickness', 'product'),
  ('product.thickness', 'ar', 'السماكة', 'product'),
  ('product.thickness', 'ru', 'Толщина', 'product'),
  ('product.length', 'tr', 'Uzunluk', 'product'),
  ('product.length', 'en', 'Length', 'product'),
  ('product.length', 'ar', 'الطول', 'product'),
  ('product.length', 'ru', 'Длина', 'product'),
  ('product.width', 'tr', 'En', 'product'),
  ('product.width', 'en', 'Width', 'product'),
  ('product.width', 'ar', 'العرض', 'product'),
  ('product.width', 'ru', 'Ширина', 'product'),
  ('product.diameter', 'tr', 'Çap', 'product'),
  ('product.diameter', 'en', 'Diameter', 'product'),
  ('product.diameter', 'ar', 'القطر', 'product'),
  ('product.diameter', 'ru', 'Диаметр', 'product'),
  ('product.material', 'tr', 'Malzeme', 'product'),
  ('product.material', 'en', 'Material', 'product'),
  ('product.material', 'ar', 'المادة', 'product'),
  ('product.material', 'ru', 'Материал', 'product'),
  ('product.standard', 'tr', 'Standart', 'product'),
  ('product.standard', 'en', 'Standard', 'product'),
  ('product.standard', 'ar', 'المعيار', 'product'),
  ('product.standard', 'ru', 'Стандарт', 'product'),
  ('product.surface', 'tr', 'Yüzey', 'product'),
  ('product.surface', 'en', 'Surface', 'product'),
  ('product.surface', 'ar', 'السطح', 'product'),
  ('product.surface', 'ru', 'Поверхность', 'product'),
  ('product.no_results', 'tr', 'Sonuç bulunamadı', 'product'),
  ('product.no_results', 'en', 'No results found', 'product'),
  ('product.no_results', 'ar', 'لا توجد نتائج', 'product'),
  ('product.no_results', 'ru', 'Результатов не найдено', 'product'),
  ('slider.scroll_down', 'tr', 'Aşağı Kaydır', 'slider'),
  ('slider.scroll_down', 'en', 'Scroll Down', 'slider'),
  ('slider.scroll_down', 'ar', 'مرر للأسفل', 'slider'),
  ('slider.scroll_down', 'ru', 'Прокрутите вниз', 'slider'),
  ('calc.title', 'tr', 'Ağırlık Hesaplama Aracı', 'calc'),
  ('calc.title', 'en', 'Weight Calculator Tool', 'calc'),
  ('calc.title', 'ar', 'أداة حساب الوزن', 'calc'),
  ('calc.title', 'ru', 'Калькулятор веса', 'calc'),
  ('calc.lead', 'tr', 'Boyut bilgisini girin, ağırlığı anında hesaplayalım.', 'calc'),
  ('calc.lead', 'en', 'Enter the dimensions and calculate the weight instantly.', 'calc'),
  ('calc.lead', 'ar', 'أدخل الأبعاد لحساب الوزن فوراً.', 'calc'),
  ('calc.lead', 'ru', 'Введите размеры и мгновенно рассчитайте вес.', 'calc'),
  ('calc.product_type', 'tr', 'Ürün Tipi', 'calc'),
  ('calc.product_type', 'en', 'Product Type', 'calc'),
  ('calc.product_type', 'ar', 'نوع المنتج', 'calc'),
  ('calc.product_type', 'ru', 'Тип изделия', 'calc'),
  ('calc.density', 'tr', 'Yoğunluk (kg/m³)', 'calc'),
  ('calc.density', 'en', 'Density (kg/m³)', 'calc'),
  ('calc.density', 'ar', 'الكثافة (كجم/م³)', 'calc'),
  ('calc.density', 'ru', 'Плотность (кг/м³)', 'calc'),
  ('calc.quantity', 'tr', 'Adet', 'calc'),
  ('calc.quantity', 'en', 'Quantity', 'calc'),
  ('calc.quantity', 'ar', 'الكمية', 'calc'),
  ('calc.quantity', 'ru', 'Количество', 'calc'),
  ('calc.total_weight', 'tr', 'Toplam Ağırlık', 'calc'),
  ('calc.total_weight', 'en', 'Total Weight', 'calc'),
  ('calc.total_weight', 'ar', 'الوزن الإجمالي', 'calc'),
  ('calc.total_weight', 'ru', 'Общий вес', 'calc'),
  ('calc.unit_weight', 'tr', 'Birim Ağırlık', 'calc'),
  ('calc.unit_weight', 'en', 'Unit Weight', 'calc'),
  ('calc.unit_weight', 'ar', 'وزن الوحدة', 'calc'),
  ('calc.unit_weight', 'ru', 'Удельный вес', 'calc'),
  ('blog.title', 'tr', 'Tekcan''dan Haberler', 'blog'),
  ('blog.title', 'en', 'News from Tekcan', 'blog'),
  ('blog.title', 'ar', 'أخبار من تكجان', 'blog'),
  ('blog.title', 'ru', 'Новости Tekcan', 'blog'),
  ('blog.lead', 'tr', 'Sektörel gelişmeler, ürün haberleri ve teknik makaleler.', 'blog'),
  ('blog.lead', 'en', 'Industry developments, product news and technical articles.', 'blog'),
  ('blog.lead', 'ar', 'تطورات الصناعة وأخبار المنتجات والمقالات التقنية.', 'blog'),
  ('blog.lead', 'ru', 'Новости отрасли, продуктов и технические статьи.', 'blog'),
  ('blog.recent_posts', 'tr', 'Son Yazılar', 'blog'),
  ('blog.recent_posts', 'en', 'Recent Posts', 'blog'),
  ('blog.recent_posts', 'ar', 'أحدث المقالات', 'blog'),
  ('blog.recent_posts', 'ru', 'Последние статьи', 'blog'),
  ('blog.popular_posts', 'tr', 'Popüler Yazılar', 'blog'),
  ('blog.popular_posts', 'en', 'Popular Posts', 'blog'),
  ('blog.popular_posts', 'ar', 'المقالات الشائعة', 'blog'),
  ('blog.popular_posts', 'ru', 'Популярные статьи', 'blog'),
  ('blog.categories', 'tr', 'Kategoriler', 'blog'),
  ('blog.categories', 'en', 'Categories', 'blog'),
  ('blog.categories', 'ar', 'الفئات', 'blog'),
  ('blog.categories', 'ru', 'Категории', 'blog'),
  ('blog.search', 'tr', 'Yazı Ara', 'blog'),
  ('blog.search', 'en', 'Search Articles', 'blog'),
  ('blog.search', 'ar', 'بحث في المقالات', 'blog'),
  ('blog.search', 'ru', 'Поиск по статьям', 'blog'),
  ('blog.read_time', 'tr', 'dakikalık okuma', 'blog'),
  ('blog.read_time', 'en', 'min read', 'blog'),
  ('blog.read_time', 'ar', 'دقيقة قراءة', 'blog'),
  ('blog.read_time', 'ru', 'минут чтения', 'blog'),
  ('blog.no_posts', 'tr', 'Henüz blog yazısı bulunmuyor', 'blog'),
  ('blog.no_posts', 'en', 'No blog posts yet', 'blog'),
  ('blog.no_posts', 'ar', 'لا توجد مقالات بعد', 'blog'),
  ('blog.no_posts', 'ru', 'Статей пока нет', 'blog'),
  ('blog.share_post', 'tr', 'Yazıyı Paylaş', 'blog'),
  ('blog.share_post', 'en', 'Share Post', 'blog'),
  ('blog.share_post', 'ar', 'مشاركة المقال', 'blog'),
  ('blog.share_post', 'ru', 'Поделиться статьёй', 'blog'),
  ('gallery.title', 'tr', 'Galeri', 'gallery'),
  ('gallery.title', 'en', 'Gallery', 'gallery'),
  ('gallery.title', 'ar', 'المعرض', 'gallery'),
  ('gallery.title', 'ru', 'Галерея', 'gallery'),
  ('gallery.lead', 'tr', 'Tesisimiz, ürünlerimiz ve projelerimizden kareler.', 'gallery'),
  ('gallery.lead', 'en', 'Photos from our facility, products and projects.', 'gallery'),
  ('gallery.lead', 'ar', 'صور من منشأتنا ومنتجاتنا ومشاريعنا.', 'gallery'),
  ('gallery.lead', 'ru', 'Фотографии нашего предприятия, продукции и проектов.', 'gallery'),
  ('gallery.albums', 'tr', 'Albümler', 'gallery'),
  ('gallery.albums', 'en', 'Albums', 'gallery'),
  ('gallery.albums', 'ar', 'الألبومات', 'gallery'),
  ('gallery.albums', 'ru', 'Альбомы', 'gallery'),
  ('gallery.photos', 'tr', 'Fotoğraflar', 'gallery'),
  ('gallery.photos', 'en', 'Photos', 'gallery'),
  ('gallery.photos', 'ar', 'الصور', 'gallery'),
  ('gallery.photos', 'ru', 'Фотографии', 'gallery'),
  ('faq.title', 'tr', 'Sıkça Sorulan Sorular', 'faq'),
  ('faq.title', 'en', 'Frequently Asked Questions', 'faq'),
  ('faq.title', 'ar', 'الأسئلة المتداولة', 'faq'),
  ('faq.title', 'ru', 'Часто задаваемые вопросы', 'faq'),
  ('faq.lead', 'tr', 'Müşterilerimizin sıkça sorduğu soruları ve cevaplarını derledik.', 'faq'),
  ('faq.lead', 'en', 'We have compiled frequently asked questions from our customers.', 'faq'),
  ('faq.lead', 'ar', 'جمعنا الأسئلة الأكثر شيوعاً من عملائنا وإجاباتها.', 'faq'),
  ('faq.lead', 'ru', 'Мы собрали часто задаваемые вопросы клиентов и ответы.', 'faq'),
  ('about.title', 'tr', 'Hakkımızda', 'about'),
  ('about.title', 'en', 'About Us', 'about'),
  ('about.title', 'ar', 'من نحن', 'about'),
  ('about.title', 'ru', 'О нас', 'about'),
  ('about.our_mission', 'tr', 'Misyonumuz', 'about'),
  ('about.our_mission', 'en', 'Our Mission', 'about'),
  ('about.our_mission', 'ar', 'مهمتنا', 'about'),
  ('about.our_mission', 'ru', 'Наша миссия', 'about'),
  ('about.our_vision', 'tr', 'Vizyonumuz', 'about'),
  ('about.our_vision', 'en', 'Our Vision', 'about'),
  ('about.our_vision', 'ar', 'رؤيتنا', 'about'),
  ('about.our_vision', 'ru', 'Наше видение', 'about'),
  ('about.our_values', 'tr', 'Değerlerimiz', 'about'),
  ('about.our_values', 'en', 'Our Values', 'about'),
  ('about.our_values', 'ar', 'قيمنا', 'about'),
  ('about.our_values', 'ru', 'Наши ценности', 'about'),
  ('about.our_history', 'tr', 'Tarihçemiz', 'about'),
  ('about.our_history', 'en', 'Our History', 'about'),
  ('about.our_history', 'ar', 'تاريخنا', 'about'),
  ('about.our_history', 'ru', 'Наша история', 'about'),
  ('about.team', 'tr', 'Ekibimiz', 'about'),
  ('about.team', 'en', 'Our Team', 'about'),
  ('about.team', 'ar', 'فريقنا', 'about'),
  ('about.team', 'ru', 'Наша команда', 'about'),
  ('partners.title', 'tr', 'Çözüm Ortaklarımız', 'partners'),
  ('partners.title', 'en', 'Solution Partners', 'partners'),
  ('partners.title', 'ar', 'شركاء الحلول', 'partners'),
  ('partners.title', 'ru', 'Партнёры', 'partners'),
  ('partners.lead', 'tr', 'Türkiye''nin önde gelen üreticileri ile çalışıyoruz.', 'partners'),
  ('partners.lead', 'en', 'We work with the leading manufacturers of Turkey.', 'partners'),
  ('partners.lead', 'ar', 'نعمل مع أبرز المصنعين في تركيا.', 'partners'),
  ('partners.lead', 'ru', 'Мы работаем с ведущими производителями Турции.', 'partners'),
  ('export.title', 'tr', 'İhracat Hizmetlerimiz', 'export'),
  ('export.title', 'en', 'Export Services', 'export'),
  ('export.title', 'ar', 'خدمات التصدير', 'export'),
  ('export.title', 'ru', 'Экспортные услуги', 'export'),
  ('export.lead', 'tr', 'Irak, Suriye, Azerbaycan ve Türkmenistan''a düzenli sevkiyat.', 'export'),
  ('export.lead', 'en', 'Regular shipments to Iraq, Syria, Azerbaijan and Turkmenistan.', 'export'),
  ('export.lead', 'ar', 'شحنات منتظمة إلى العراق وسوريا وأذربيجان وتركمانستان.', 'export'),
  ('export.lead', 'ru', 'Регулярные поставки в Ирак, Сирию, Азербайджан и Туркменистан.', 'export'),
  ('export.iraq', 'tr', 'Irak', 'export'),
  ('export.iraq', 'en', 'Iraq', 'export'),
  ('export.iraq', 'ar', 'العراق', 'export'),
  ('export.iraq', 'ru', 'Ирак', 'export'),
  ('export.syria', 'tr', 'Suriye', 'export'),
  ('export.syria', 'en', 'Syria', 'export'),
  ('export.syria', 'ar', 'سوريا', 'export'),
  ('export.syria', 'ru', 'Сирия', 'export'),
  ('export.azerbaijan', 'tr', 'Azerbaycan', 'export'),
  ('export.azerbaijan', 'en', 'Azerbaijan', 'export'),
  ('export.azerbaijan', 'ar', 'أذربيجان', 'export'),
  ('export.azerbaijan', 'ru', 'Азербайджан', 'export'),
  ('export.turkmenistan', 'tr', 'Türkmenistan', 'export'),
  ('export.turkmenistan', 'en', 'Turkmenistan', 'export'),
  ('export.turkmenistan', 'ar', 'تركمانستان', 'export'),
  ('export.turkmenistan', 'ru', 'Туркменистан', 'export'),
  ('city.industrial_use', 'tr', 'Sanayi Kullanımı', 'city'),
  ('city.industrial_use', 'en', 'Industrial Use', 'city'),
  ('city.industrial_use', 'ar', 'الاستخدام الصناعي', 'city'),
  ('city.industrial_use', 'ru', 'Промышленное применение', 'city'),
  ('city.shipping_info', 'tr', 'Sevkiyat Bilgisi', 'city'),
  ('city.shipping_info', 'en', 'Shipping Information', 'city'),
  ('city.shipping_info', 'ar', 'معلومات الشحن', 'city'),
  ('city.shipping_info', 'ru', 'Информация о доставке', 'city'),
  ('city.same_day_quote', 'tr', 'Aynı Gün Teklif', 'city'),
  ('city.same_day_quote', 'en', 'Same Day Quote', 'city'),
  ('city.same_day_quote', 'ar', 'عرض سعر في نفس اليوم', 'city'),
  ('city.same_day_quote', 'ru', 'Предложение в тот же день', 'city'),
  ('mailorder.title', 'tr', 'Mail Order', 'mailorder'),
  ('mailorder.title', 'en', 'Mail Order', 'mailorder'),
  ('mailorder.title', 'ar', 'الطلب البريدي', 'mailorder'),
  ('mailorder.title', 'ru', 'Заказ по почте', 'mailorder'),
  ('mailorder.lead', 'tr', 'Kart bilgilerinizi güvenle paylaşmak için Mail Order formumuzu kullanın.', 'mailorder'),
  ('mailorder.lead', 'en', 'Use our Mail Order form to share card details securely.', 'mailorder'),
  ('mailorder.lead', 'ar', 'استخدم نموذج الطلب البريدي لمشاركة بيانات البطاقة بأمان.', 'mailorder'),
  ('mailorder.lead', 'ru', 'Используйте форму Mail Order для безопасной передачи данных карты.', 'mailorder'),
  ('loyalty.title', 'tr', 'Sadakat Programı', 'loyalty'),
  ('loyalty.title', 'en', 'Loyalty Program', 'loyalty'),
  ('loyalty.title', 'ar', 'برنامج الولاء', 'loyalty'),
  ('loyalty.title', 'ru', 'Программа лояльности', 'loyalty'),
  ('loyalty.lead', 'tr', 'Sürekli alışverişin ödülü — özel indirimler ve avantajlar.', 'loyalty'),
  ('loyalty.lead', 'en', 'Reward for repeat purchases — exclusive discounts and benefits.', 'loyalty'),
  ('loyalty.lead', 'ar', 'مكافأة المشتريات المتكررة - خصومات حصرية ومزايا.', 'loyalty'),
  ('loyalty.lead', 'ru', 'Награда за постоянные покупки — эксклюзивные скидки и преимущества.', 'loyalty'),
  ('iban.title', 'tr', 'IBAN Bilgilerimiz', 'iban'),
  ('iban.title', 'en', 'Bank Account Information (IBAN)', 'iban'),
  ('iban.title', 'ar', 'معلومات الحسابات المصرفية (IBAN)', 'iban'),
  ('iban.title', 'ru', 'Банковские реквизиты (IBAN)', 'iban'),
  ('iban.lead', 'tr', 'Banka havalesi ile ödeme için hesap bilgilerimiz.', 'iban'),
  ('iban.lead', 'en', 'Our account details for bank transfer payments.', 'iban'),
  ('iban.lead', 'ar', 'تفاصيل حساباتنا للدفع عن طريق التحويل البنكي.', 'iban'),
  ('iban.lead', 'ru', 'Реквизиты для оплаты банковским переводом.', 'iban'),
  ('iban.bank', 'tr', 'Banka', 'iban'),
  ('iban.bank', 'en', 'Bank', 'iban'),
  ('iban.bank', 'ar', 'البنك', 'iban'),
  ('iban.bank', 'ru', 'Банк', 'iban'),
  ('iban.account_holder', 'tr', 'Hesap Sahibi', 'iban'),
  ('iban.account_holder', 'en', 'Account Holder', 'iban'),
  ('iban.account_holder', 'ar', 'صاحب الحساب', 'iban'),
  ('iban.account_holder', 'ru', 'Владелец счёта', 'iban'),
  ('iban.iban_number', 'tr', 'IBAN Numarası', 'iban'),
  ('iban.iban_number', 'en', 'IBAN Number', 'iban'),
  ('iban.iban_number', 'ar', 'رقم IBAN', 'iban'),
  ('iban.iban_number', 'ru', 'Номер IBAN', 'iban'),
  ('iban.copy', 'tr', 'IBAN''ı kopyala', 'iban'),
  ('iban.copy', 'en', 'Copy IBAN', 'iban'),
  ('iban.copy', 'ar', 'انسخ IBAN', 'iban'),
  ('iban.copy', 'ru', 'Копировать IBAN', 'iban'),
  ('iban.copied', 'tr', 'Kopyalandı!', 'iban'),
  ('iban.copied', 'en', 'Copied!', 'iban'),
  ('iban.copied', 'ar', 'تم النسخ!', 'iban'),
  ('iban.copied', 'ru', 'Скопировано!', 'iban'),
  ('err404.title', 'tr', 'Sayfa Bulunamadı', 'err404'),
  ('err404.title', 'en', 'Page Not Found', 'err404'),
  ('err404.title', 'ar', 'الصفحة غير موجودة', 'err404'),
  ('err404.title', 'ru', 'Страница не найдена', 'err404'),
  ('err404.lead', 'tr', 'Aradığınız sayfa taşınmış veya silinmiş olabilir.', 'err404'),
  ('err404.lead', 'en', 'The page you are looking for may have been moved or deleted.', 'err404'),
  ('err404.lead', 'ar', 'قد تكون الصفحة التي تبحث عنها قد نُقلت أو حُذفت.', 'err404'),
  ('err404.lead', 'ru', 'Запрашиваемая страница могла быть перемещена или удалена.', 'err404'),
  ('err404.go_home', 'tr', 'Anasayfaya Dön', 'err404'),
  ('err404.go_home', 'en', 'Go to Homepage', 'err404'),
  ('err404.go_home', 'ar', 'العودة للرئيسية', 'err404'),
  ('err404.go_home', 'ru', 'На главную', 'err404'),
  ('general.loading', 'tr', 'Yükleniyor...', 'general'),
  ('general.loading', 'en', 'Loading...', 'general'),
  ('general.loading', 'ar', 'جار التحميل...', 'general'),
  ('general.loading', 'ru', 'Загрузка...', 'general'),
  ('general.processing', 'tr', 'İşleniyor...', 'general'),
  ('general.processing', 'en', 'Processing...', 'general'),
  ('general.processing', 'ar', 'جار المعالجة...', 'general'),
  ('general.processing', 'ru', 'Обработка...', 'general'),
  ('general.success', 'tr', 'Başarılı', 'general'),
  ('general.success', 'en', 'Success', 'general'),
  ('general.success', 'ar', 'تم بنجاح', 'general'),
  ('general.success', 'ru', 'Успешно', 'general'),
  ('general.error', 'tr', 'Hata', 'general'),
  ('general.error', 'en', 'Error', 'general'),
  ('general.error', 'ar', 'خطأ', 'general'),
  ('general.error', 'ru', 'Ошибка', 'general'),
  ('general.warning', 'tr', 'Uyarı', 'general'),
  ('general.warning', 'en', 'Warning', 'general'),
  ('general.warning', 'ar', 'تحذير', 'general'),
  ('general.warning', 'ru', 'Предупреждение', 'general'),
  ('general.info', 'tr', 'Bilgi', 'general'),
  ('general.info', 'en', 'Info', 'general'),
  ('general.info', 'ar', 'معلومات', 'general'),
  ('general.info', 'ru', 'Информация', 'general'),
  ('general.yes', 'tr', 'Evet', 'general'),
  ('general.yes', 'en', 'Yes', 'general'),
  ('general.yes', 'ar', 'نعم', 'general'),
  ('general.yes', 'ru', 'Да', 'general'),
  ('general.no', 'tr', 'Hayır', 'general'),
  ('general.no', 'en', 'No', 'general'),
  ('general.no', 'ar', 'لا', 'general'),
  ('general.no', 'ru', 'Нет', 'general'),
  ('legal.kvkk_title', 'tr', 'KVKK Aydınlatma Metni', 'legal'),
  ('legal.kvkk_title', 'en', 'Privacy Notice', 'legal'),
  ('legal.kvkk_title', 'ar', 'سياسة الخصوصية', 'legal'),
  ('legal.kvkk_title', 'ru', 'Уведомление о конфиденциальности', 'legal'),
  ('legal.cookie_title', 'tr', 'Çerez Politikası', 'legal'),
  ('legal.cookie_title', 'en', 'Cookie Policy', 'legal'),
  ('legal.cookie_title', 'ar', 'سياسة ملفات تعريف الارتباط', 'legal'),
  ('legal.cookie_title', 'ru', 'Политика использования cookies', 'legal'),
  ('legal.terms_title', 'tr', 'Kullanım Şartları', 'legal'),
  ('legal.terms_title', 'en', 'Terms of Use', 'legal'),
  ('legal.terms_title', 'ar', 'شروط الاستخدام', 'legal'),
  ('legal.terms_title', 'ru', 'Условия использования', 'legal');

-- =====================================================
-- v1.0.58 — DB İÇERİK ÇEVİRİLERİ (Bölüm 1)
-- Settings + Slider + Kategoriler + 3 Hizmet
-- =====================================================

INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('setting.site_slogan', 'tr', 'Ticaret ile Bitmeyen Dostluk', 'setting'),
  ('setting.site_slogan', 'en', 'Friendship Beyond Trade', 'setting'),
  ('setting.site_slogan', 'ar', 'صداقة لا تنتهي بالتجارة', 'setting'),
  ('setting.site_slogan', 'ru', 'Дружба, не ограниченная торговлей', 'setting'),
  ('setting.site_description', 'tr', 'Demir adına Herşey... Sac, boru, profil, hadde ve özel çelik ürünleri ile inşaat, sanayi ve OEM müşterilerine 7/24 hizmet.', 'setting'),
  ('setting.site_description', 'en', 'Everything in the name of Steel... Sheet, pipe, profile, rolled and special steel products serving construction, industrial and OEM customers 24/7.', 'setting'),
  ('setting.site_description', 'ar', 'كل ما يخص الفولاذ... منتجات الألواح والأنابيب والمقاطع والصفائح المدرفلة والفولاذ الخاص للبناء والصناعة وعملاء التصنيع OEM على مدار الساعة.', 'setting'),
  ('setting.site_description', 'ru', 'Всё для металла... Листы, трубы, профили, прокат и спецсталь для строительства, промышленности и OEM-клиентов круглосуточно.', 'setting'),
  ('setting.site_address', 'tr', 'Fevziçakmak Mahallesi Gülistan Cad. Atiker 3, 2.Blok No:33 AS - Karatay - Konya', 'setting'),
  ('setting.site_address', 'en', 'Fevzicakmak District, Gulistan Street, Atiker 3, Block 2 No:33 AS - Karatay - Konya', 'setting'),
  ('setting.site_address', 'ar', 'حي فوزي تشاكماك، شارع غولستان، أتيكر 3، البلوك 2 رقم 33 AS - كاراتاي - قونية', 'setting'),
  ('setting.site_address', 'ru', 'Район Февзичакмак, ул. Гюлистан, Атикер 3, Блок 2 № 33 AS - Каратай - Конья', 'setting'),
  ('setting.site_district', 'tr', 'Karatay', 'setting'),
  ('setting.site_district', 'en', 'Karatay', 'setting'),
  ('setting.site_district', 'ar', 'كاراتاي', 'setting'),
  ('setting.site_district', 'ru', 'Каратай', 'setting'),
  ('setting.site_city', 'tr', 'Konya', 'setting'),
  ('setting.site_city', 'en', 'Konya', 'setting'),
  ('setting.site_city', 'ar', 'قونية', 'setting'),
  ('setting.site_city', 'ru', 'Конья', 'setting'),
  ('setting.site_country', 'tr', 'Türkiye', 'setting'),
  ('setting.site_country', 'en', 'Turkey', 'setting'),
  ('setting.site_country', 'ar', 'تركيا', 'setting'),
  ('setting.site_country', 'ru', 'Турция', 'setting'),
  ('setting.site_whatsapp_label', 'tr', 'Tekcan Metal - Danışman', 'setting'),
  ('setting.site_whatsapp_label', 'en', 'Tekcan Metal - Consultant', 'setting'),
  ('setting.site_whatsapp_label', 'ar', 'تكجان للحديد - مستشار', 'setting'),
  ('setting.site_whatsapp_label', 'ru', 'Tekcan Metal - Консультант', 'setting'),
  ('setting.site_whatsapp_msg', 'tr', 'Merhaba. Size nasıl yardımcı olabiliriz?', 'setting'),
  ('setting.site_whatsapp_msg', 'en', 'Hello. How can we help you?', 'setting'),
  ('setting.site_whatsapp_msg', 'ar', 'مرحبا. كيف يمكننا مساعدتك؟', 'setting'),
  ('setting.site_whatsapp_msg', 'ru', 'Здравствуйте. Чем мы можем помочь?', 'setting'),
  ('setting.working_hours', 'tr', 'Pazartesi–Cumartesi: 08:00 – 18:00', 'setting'),
  ('setting.working_hours', 'en', 'Monday-Saturday: 08:00 - 18:00', 'setting'),
  ('setting.working_hours', 'ar', 'الإثنين-السبت: 08:00 - 18:00', 'setting'),
  ('setting.working_hours', 'ru', 'Понедельник-Суббота: 08:00 - 18:00', 'setting'),
  ('setting.tax_office', 'tr', 'Selçuk', 'setting'),
  ('setting.tax_office', 'en', 'Selcuk Tax Office', 'setting'),
  ('setting.tax_office', 'ar', 'مكتب ضرائب سلجوق', 'setting'),
  ('setting.tax_office', 'ru', 'Налоговая Сельчук', 'setting'),
  ('setting.homepage_about_title', 'tr', 'Birlikte Daha Güçlüyüz', 'setting'),
  ('setting.homepage_about_title', 'en', 'Stronger Together', 'setting'),
  ('setting.homepage_about_title', 'ar', 'معاً أقوى', 'setting'),
  ('setting.homepage_about_title', 'ru', 'Вместе — сильнее', 'setting'),
  ('setting.homepage_about_text', 'tr', 'Tekcan Metal, demir-çelik sektöründe üretilen mamul ve yarı mamullerin pazarlama ve dağıtımını yapmak amacıyla 2005 yılında şahıs şirketi olarak kurulmuştur. Artan iş hacmi ve müşteri memnuniyetinin getirdiği güvenle 2017 yılında şirketleşerek faaliyetlerini kurumsal yapıya taşımıştır. Bugün, Karatay/Konya adresinde faaliyet gösteren Tekcan Metal; yüksek kaliteli hizmet anlayışı, güler yüzlü ticaret yaklaşımı ve müşteri odaklı çözümleri ile sektörde güvenilir bir konum elde etmiştir.', 'setting'),
  ('setting.homepage_about_text', 'en', 'Tekcan Metal was founded in 2005 as a sole proprietorship to market and distribute products and semi-finished goods produced in the iron and steel industry. With the trust gained from growing business volume and customer satisfaction, the company was incorporated in 2017, moving its operations to a corporate structure. Today, operating in Karatay/Konya, Tekcan Metal has established a reliable position in the industry with its high-quality service approach, friendly trade philosophy and customer-focused solutions.', 'setting'),
  ('setting.homepage_about_text', 'ar', 'تأسست تكجان للحديد عام 2005 كمؤسسة فردية لتسويق وتوزيع المنتجات وشبه المصنعة في صناعة الحديد والصلب. بفضل الثقة المتزايدة من حجم الأعمال ورضا العملاء، تحولت الشركة عام 2017 إلى هيكل مؤسسي. اليوم، تعمل تكجان للحديد في كاراتاي/قونية، وقد حققت مكانة موثوقة في الصناعة من خلال نهج الخدمة عالية الجودة وفلسفة التجارة الودية والحلول المتمحورة حول العميل.', 'setting'),
  ('setting.homepage_about_text', 'ru', 'Tekcan Metal была основана в 2005 году как индивидуальное предприятие для маркетинга и дистрибуции готовой и полуфабрикатной продукции металлургической промышленности. Благодаря растущему объёму бизнеса и доверию клиентов, в 2017 году компания получила корпоративную структуру. Сегодня Tekcan Metal, работающая в Каратае/Конья, заняла надёжную позицию в отрасли благодаря высокому качеству обслуживания, дружелюбному торговому подходу и клиентоориентированным решениям.', 'setting'),
  ('setting.footer_about_text', 'tr', 'Tekcan Metal, demir-çelik sektöründe üretilen mamul ve yarı mamullerin pazarlama ve dağıtımını yapmak amacıyla 2005 yılında şahıs şirketi olarak kurulmuştur. Artan iş hacmi ve müşteri memnuniyetinin getirdiği güvenle 2017 yılında şirketleşerek faaliyetlerini kurumsal yapıya taşımıştır.', 'setting'),
  ('setting.footer_about_text', 'en', 'Tekcan Metal was founded in 2005 as a sole proprietorship to market and distribute products in the iron and steel industry. With the trust gained from growing business volume and customer satisfaction, the company was incorporated in 2017.', 'setting'),
  ('setting.footer_about_text', 'ar', 'تأسست تكجان للحديد عام 2005 كمؤسسة فردية لتسويق وتوزيع المنتجات في صناعة الحديد والصلب. تحولت الشركة عام 2017 إلى هيكل مؤسسي بفضل الثقة المتزايدة.', 'setting'),
  ('setting.footer_about_text', 'ru', 'Tekcan Metal была основана в 2005 году для маркетинга и дистрибуции металлургической продукции. С 2017 года компания работает как корпорация.', 'setting'),
  ('setting.footer_keywords_text', 'tr', 'Yüksek kaliteli boru, profil, sac, HRP, DKP, ST52, galvaniz, trapez sac, çatı paneli, cephe paneli, lama, silme, kare demir, NPU, NPI, IPE, HEA, HEB ve inşaat demiri tedarikinde güvenilir çözüm ortağınız. TEKCAN METAL — Güçlü yapılar, sağlam çözümler. Geleceğe atılan çelik adımlar!', 'setting'),
  ('setting.footer_keywords_text', 'en', 'Your reliable solution partner for high-quality pipes, profiles, sheets, HRP, DKP, ST52, galvanized, trapezoidal sheet, roof panels, facade panels, flat bars, square bars, NPU, NPI, IPE, HEA, HEB and construction steel. TEKCAN METAL — Strong structures, solid solutions. Steel steps into the future!', 'setting'),
  ('setting.footer_keywords_text', 'ar', 'شريكك الموثوق للحلول في توريد الأنابيب والمقاطع والألواح عالية الجودة، HRP، DKP، ST52، المجلفن، الألواح شبه المنحرفة، ألواح السقف، ألواح الواجهة، القضبان المسطحة والمربعة، NPU، NPI، IPE، HEA، HEB وحديد البناء. تكجان للحديد - هياكل قوية وحلول صلبة. خطوات فولاذية نحو المستقبل!', 'setting'),
  ('setting.footer_keywords_text', 'ru', 'Ваш надёжный партнёр в поставках высококачественных труб, профилей, листов, HRP, DKP, ST52, оцинкованных, трапециевидных листов, кровельных и фасадных панелей, полос, квадратных прутов, NPU, NPI, IPE, HEA, HEB и арматуры. TEKCAN METAL — Прочные конструкции, надёжные решения. Стальные шаги в будущее!', 'setting'),
  ('setting.maintenance_message', 'tr', 'Şu anda geçici bir bakım yapılmaktadır. En kısa sürede sizlerleyiz. info@tekcanmetal.com — 0 554 835 0 226', 'setting'),
  ('setting.maintenance_message', 'en', 'A temporary maintenance is currently in progress. We will be back as soon as possible. info@tekcanmetal.com - 0 554 835 0 226', 'setting'),
  ('setting.maintenance_message', 'ar', 'يجري حالياً إجراء صيانة مؤقتة. سنعود في أقرب وقت ممكن. info@tekcanmetal.com - 0 554 835 0 226', 'setting'),
  ('setting.maintenance_message', 'ru', 'Сейчас проводятся временные технические работы. Мы вернёмся в ближайшее время. info@tekcanmetal.com - 0 554 835 0 226', 'setting'),
  ('setting.stat_year_label', 'tr', 'Yıllık Tecrübe', 'setting'),
  ('setting.stat_year_label', 'en', 'Years Experience', 'setting'),
  ('setting.stat_year_label', 'ar', 'سنة من الخبرة', 'setting'),
  ('setting.stat_year_label', 'ru', 'Лет опыта', 'setting'),
  ('setting.stat_products_label', 'tr', 'Ürün Çeşidi', 'setting'),
  ('setting.stat_products_label', 'en', 'Product Variety', 'setting'),
  ('setting.stat_products_label', 'ar', 'تنوع المنتجات', 'setting'),
  ('setting.stat_products_label', 'ru', 'Видов продукции', 'setting'),
  ('setting.stat_customers_label', 'tr', 'Mutlu Müşteri', 'setting'),
  ('setting.stat_customers_label', 'en', 'Happy Customers', 'setting'),
  ('setting.stat_customers_label', 'ar', 'عميل سعيد', 'setting'),
  ('setting.stat_customers_label', 'ru', 'Довольных клиентов', 'setting'),
  ('setting.stat_orders_label', 'tr', 'Ürün Siparişi', 'setting'),
  ('setting.stat_orders_label', 'en', 'Product Orders', 'setting'),
  ('setting.stat_orders_label', 'ar', 'طلبات المنتجات', 'setting'),
  ('setting.stat_orders_label', 'ru', 'Заказов', 'setting'),
  ('setting.stat_branches_label', 'tr', 'Firma Şubesi', 'setting'),
  ('setting.stat_branches_label', 'en', 'Company Branch', 'setting'),
  ('setting.stat_branches_label', 'ar', 'فروع الشركة', 'setting'),
  ('setting.stat_branches_label', 'ru', 'Филиал', 'setting'),
  ('setting.stat_delivery_label', 'tr', 'Sevkiyat Hizmeti', 'setting'),
  ('setting.stat_delivery_label', 'en', 'Delivery Service', 'setting'),
  ('setting.stat_delivery_label', 'ar', 'خدمة الشحن', 'setting'),
  ('setting.stat_delivery_label', 'ru', 'Служба доставки', 'setting');

-- Slider çevirileri (mevcut TR title'a göre WHERE)

UPDATE tm_sliders SET
  title_en = 'Trust of nearly half a century in the steel industry',
  title_ar = 'ثقة تقترب من نصف قرن في صناعة الحديد والصلب',
  title_ru = 'Почти полвека доверия в металлургии',
  subtitle_en = 'Tekcan Metal',
  subtitle_ar = 'تكجان للحديد',
  subtitle_ru = 'Tekcan Metal',
  description_en = 'Since 2005, based in Konya; we provide solutions to the industry and construction sectors as authorized representatives of Turkey''s leading manufacturers in sheet, pipe, profile, rolled and special steel products.',
  description_ar = 'منذ 2005، مقرنا في قونية؛ نقدم الحلول لقطاعي الصناعة والبناء كممثلين معتمدين لكبار المصنعين في تركيا في منتجات الألواح والأنابيب والمقاطع والمنتجات المدرفلة والفولاذ الخاص.',
  description_ru = 'С 2005 года, штаб-квартира в Конья; мы предоставляем решения для промышленности и строительства как авторизованные представители ведущих производителей Турции по листам, трубам, профилям, прокату и спецстали.',
  link_text_en = 'Who We Are',
  link_text_ar = 'من نحن',
  link_text_ru = 'Кто мы'
WHERE title = 'Demir-çelik sektöründe yarım asra yakın güven';

UPDATE tm_sliders SET
  title_en = 'End-to-end steel supply from a single source',
  title_ar = 'توريد الحديد والصلب من نقطة واحدة من البداية للنهاية',
  title_ru = 'Комплексные поставки металла из одних рук',
  subtitle_en = 'Our Solution Range',
  subtitle_ar = 'نطاق حلولنا',
  subtitle_ru = 'Наш ассортимент решений',
  description_en = 'With our extensive stock; laser and oxygen cutting workshops; same-day production and shipping capacity, we are by your side at every stage of your projects.',
  description_ar = 'مع مخزوننا الواسع؛ وورش القطع بالليزر والأكسجين؛ وقدرة الإنتاج والشحن في نفس اليوم، نحن بجانبك في كل مرحلة من مراحل مشاريعك.',
  description_ru = 'Большие складские запасы, лазерные и газокислородные цеха, производство и отгрузка в тот же день — мы с вами на каждом этапе проекта.',
  link_text_en = 'Our Capabilities',
  link_text_ar = 'قدراتنا',
  link_text_ru = 'Наши возможности'
WHERE title = 'Tek elden, uçtan uca demir-çelik tedariği';

UPDATE tm_sliders SET
  title_en = '24/7 shipping network across Turkey',
  title_ar = 'شبكة شحن على مدار الساعة في جميع أنحاء تركيا',
  title_ru = 'Доставка 24/7 по всей Турции',
  subtitle_en = 'Operational Excellence',
  subtitle_ar = 'التميز التشغيلي',
  subtitle_ru = 'Операционное совершенство',
  description_en = 'With our Konya-based warehouse and contracted transportation partners, we offer timely, complete delivery commitment to all 81 cities.',
  description_ar = 'مع مستودعنا الرئيسي في قونية وشركاء النقل المتعاقدين معنا، نقدم التزاماً بالتسليم في الوقت المحدد والكامل لجميع المدن الـ81.',
  description_ru = 'С нашим складом в Конья и партнёрами-перевозчиками мы гарантируем своевременную доставку во все 81 город Турции.',
  link_text_en = 'Contact Us',
  link_text_ar = 'تواصل معنا',
  link_text_ru = 'Свяжитесь с нами'
WHERE title = 'Türkiye genelinde 7/24 sevkiyat ağı';

-- Kategori çevirileri

UPDATE tm_categories SET
  name_en = 'Sheet Products',
  name_ar = 'منتجات الألواح',
  name_ru = 'Листовая продукция',
  short_desc_en = 'HRP, DKP, galvanized, stainless and non-oxide sheet varieties.',
  short_desc_ar = 'أنواع الألواح HRP وDKP والمجلفنة والمقاومة للصدأ والخالية من الأكسيد.',
  short_desc_ru = 'HRP, DKP, оцинкованный, нержавеющий лист.'
WHERE slug = 'sac-urunleri';

UPDATE tm_categories SET
  name_en = 'Pipe Products',
  name_ar = 'منتجات الأنابيب',
  name_ru = 'Трубная продукция',
  short_desc_en = 'Welded/seamless, square/rectangular profile pipes, water and gas pipes.',
  short_desc_ar = 'أنابيب ملحومة/بدون لحام، مربعة/مستطيلة، أنابيب المياه والغاز.',
  short_desc_ru = 'Сварные/бесшовные, квадратные/прямоугольные профильные трубы, водо- и газопроводные.'
WHERE slug = 'boru-urunleri';

UPDATE tm_categories SET
  name_en = 'Profile Products',
  name_ar = 'منتجات المقاطع',
  name_ru = 'Профильная продукция',
  short_desc_en = 'NPU, NPI, IPE, HEA, HEB, flat bar, square, T and L profiles.',
  short_desc_ar = 'مقاطع NPU وNPI وIPE وHEA وHEB والقضبان المسطحة والمربعة وT وL.',
  short_desc_ru = 'Профили NPU, NPI, IPE, HEA, HEB, полоса, квадрат, T и L.'
WHERE slug = 'profil-urunleri';

UPDATE tm_categories SET
  name_en = 'Construction Steel',
  name_ar = 'حديد البناء',
  name_ru = 'Арматура',
  short_desc_en = 'Ribbed, plain construction steel and steel mesh varieties.',
  short_desc_ar = 'حديد البناء المضلع والمستوي وأنواع الشبك الفولاذي.',
  short_desc_ru = 'Рифлёная, гладкая арматура и сетка стальная.'
WHERE slug = 'insaat-demiri';

UPDATE tm_categories SET
  name_en = 'Roof & Facade',
  name_ar = 'السقف والواجهة',
  name_ru = 'Кровля и фасад',
  short_desc_en = 'Trapezoidal sheet, roof panel, facade panel, sandwich panel.',
  short_desc_ar = 'الألواح شبه المنحرفة، ألواح السقف والواجهة، الألواح الساندويتش.',
  short_desc_ru = 'Трапециевидный лист, кровельная и фасадная панель, сэндвич-панель.'
WHERE slug = 'cati-cephe';

UPDATE tm_categories SET
  name_en = 'Special Products',
  name_ar = 'منتجات خاصة',
  name_ru = 'Спецпродукция',
  short_desc_en = 'Decorative sheet, corten steel, perforated sheet and custom manufacturing.',
  short_desc_ar = 'الألواح الزخرفية، الفولاذ كورتن، الألواح المثقبة والتصنيع المخصص.',
  short_desc_ru = 'Декоративный лист, кортен-сталь, перфорированный лист, спецпроизводство.'
WHERE slug = 'ozel-urunler';

-- Slider çevirileri (mevcut TR title'a göre WHERE)

UPDATE tm_sliders SET
  title_en = 'Trust of nearly half a century in the steel industry',
  title_ar = 'ثقة تقترب من نصف قرن في صناعة الحديد والصلب',
  title_ru = 'Почти полвека доверия в металлургии',
  subtitle_en = 'Tekcan Metal',
  subtitle_ar = 'تكجان للحديد',
  subtitle_ru = 'Tekcan Metal',
  description_en = 'Since 2005, based in Konya; we provide solutions to the industry and construction sectors as authorized representatives of Turkey''s leading manufacturers in sheet, pipe, profile, rolled and special steel products.',
  description_ar = 'منذ 2005، مقرنا في قونية؛ نقدم الحلول لقطاعي الصناعة والبناء كممثلين معتمدين لكبار المصنعين في تركيا في منتجات الألواح والأنابيب والمقاطع والمنتجات المدرفلة والفولاذ الخاص.',
  description_ru = 'С 2005 года, штаб-квартира в Конья; мы предоставляем решения для промышленности и строительства как авторизованные представители ведущих производителей Турции по листам, трубам, профилям, прокату и спецстали.',
  link_text_en = 'Who We Are',
  link_text_ar = 'من نحن',
  link_text_ru = 'Кто мы'
WHERE title = 'Demir-çelik sektöründe yarım asra yakın güven';

UPDATE tm_sliders SET
  title_en = 'End-to-end steel supply from a single source',
  title_ar = 'توريد الحديد والصلب من نقطة واحدة من البداية للنهاية',
  title_ru = 'Комплексные поставки металла из одних рук',
  subtitle_en = 'Our Solution Range',
  subtitle_ar = 'نطاق حلولنا',
  subtitle_ru = 'Наш ассортимент решений',
  description_en = 'With our extensive stock; laser and oxygen cutting workshops; same-day production and shipping capacity, we are by your side at every stage of your projects.',
  description_ar = 'مع مخزوننا الواسع؛ وورش القطع بالليزر والأكسجين؛ وقدرة الإنتاج والشحن في نفس اليوم، نحن بجانبك في كل مرحلة من مراحل مشاريعك.',
  description_ru = 'Большие складские запасы, лазерные и газокислородные цеха, производство и отгрузка в тот же день — мы с вами на каждом этапе проекта.',
  link_text_en = 'Our Capabilities',
  link_text_ar = 'قدراتنا',
  link_text_ru = 'Наши возможности'
WHERE title = 'Tek elden, uçtan uca demir-çelik tedariği';

UPDATE tm_sliders SET
  title_en = '24/7 shipping network across Turkey',
  title_ar = 'شبكة شحن على مدار الساعة في جميع أنحاء تركيا',
  title_ru = 'Доставка 24/7 по всей Турции',
  subtitle_en = 'Operational Excellence',
  subtitle_ar = 'التميز التشغيلي',
  subtitle_ru = 'Операционное совершенство',
  description_en = 'With our Konya-based warehouse and contracted transportation partners, we offer timely, complete delivery commitment to all 81 cities.',
  description_ar = 'مع مستودعنا الرئيسي في قونية وشركاء النقل المتعاقدين معنا، نقدم التزاماً بالتسليم في الوقت المحدد والكامل لجميع المدن الـ81.',
  description_ru = 'С нашим складом в Конья и партнёрами-перевозчиками мы гарантируем своевременную доставку во все 81 город Турции.',
  link_text_en = 'Contact Us',
  link_text_ar = 'تواصل معنا',
  link_text_ru = 'Свяжитесь с нами'
WHERE title = 'Türkiye genelinde 7/24 sevkiyat ağı';

-- Kategori çevirileri

UPDATE tm_categories SET
  name_en = 'Sheet Products',
  name_ar = 'منتجات الألواح',
  name_ru = 'Листовая продукция',
  short_desc_en = 'HRP, DKP, galvanized, stainless and non-oxide sheet varieties.',
  short_desc_ar = 'أنواع الألواح HRP وDKP والمجلفنة والمقاومة للصدأ والخالية من الأكسيد.',
  short_desc_ru = 'HRP, DKP, оцинкованный, нержавеющий лист.'
WHERE slug = 'sac-urunleri';

UPDATE tm_categories SET
  name_en = 'Pipe Products',
  name_ar = 'منتجات الأنابيب',
  name_ru = 'Трубная продукция',
  short_desc_en = 'Welded/seamless, square/rectangular profile pipes, water and gas pipes.',
  short_desc_ar = 'أنابيب ملحومة/بدون لحام، مربعة/مستطيلة، أنابيب المياه والغاز.',
  short_desc_ru = 'Сварные/бесшовные, квадратные/прямоугольные профильные трубы, водо- и газопроводные.'
WHERE slug = 'boru-urunleri';

UPDATE tm_categories SET
  name_en = 'Profile Products',
  name_ar = 'منتجات المقاطع',
  name_ru = 'Профильная продукция',
  short_desc_en = 'NPU, NPI, IPE, HEA, HEB, flat bar, square, T and L profiles.',
  short_desc_ar = 'مقاطع NPU وNPI وIPE وHEA وHEB والقضبان المسطحة والمربعة وT وL.',
  short_desc_ru = 'Профили NPU, NPI, IPE, HEA, HEB, полоса, квадрат, T и L.'
WHERE slug = 'profil-urunleri';

UPDATE tm_categories SET
  name_en = 'Construction Steel',
  name_ar = 'حديد البناء',
  name_ru = 'Арматура',
  short_desc_en = 'Ribbed, plain construction steel and steel mesh varieties.',
  short_desc_ar = 'حديد البناء المضلع والمستوي وأنواع الشبك الفولاذي.',
  short_desc_ru = 'Рифлёная, гладкая арматура и сетка стальная.'
WHERE slug = 'insaat-demiri';

UPDATE tm_categories SET
  name_en = 'Roof & Facade',
  name_ar = 'السقف والواجهة',
  name_ru = 'Кровля и фасад',
  short_desc_en = 'Trapezoidal sheet, roof panel, facade panel, sandwich panel.',
  short_desc_ar = 'الألواح شبه المنحرفة، ألواح السقف والواجهة، الألواح الساندويتش.',
  short_desc_ru = 'Трапециевидный лист, кровельная и фасадная панель, сэндвич-панель.'
WHERE slug = 'cati-cephe';

UPDATE tm_categories SET
  name_en = 'Special Products',
  name_ar = 'منتجات خاصة',
  name_ru = 'Спецпродукция',
  short_desc_en = 'Decorative sheet, corten steel, perforated sheet and custom manufacturing.',
  short_desc_ar = 'الألواح الزخرفية، الفولاذ كورتن، الألواح المثقبة والتصنيع المخصص.',
  short_desc_ru = 'Декоративный лист, кортен-сталь, перфорированный лист, спецпроизводство.'
WHERE slug = 'ozel-urunler';

-- 3 Hizmet 4 dil çevirileri

UPDATE tm_services SET
  title_en = 'Laser Cutting',
  title_ar = 'القطع بالليزر',
  title_ru = 'Лазерная резка',
  short_desc_en = 'Industrial cutting service with fiber laser technology in 0.5-25 mm sheet thickness range with ±0.1 mm precision. Send your DXF/DWG file, we put it into production same day.',
  short_desc_ar = 'خدمة قطع صناعية بتقنية الليزر الليفي بسماكة الألواح من 0.5 إلى 25 مم بدقة ±0.1 مم. أرسل ملف DXF/DWG، ونبدأ الإنتاج في نفس اليوم.',
  short_desc_ru = 'Промышленная резка волоконным лазером, толщина листа 0.5-25 мм, точность ±0.1 мм. Отправьте файл DXF/DWG — производство в тот же день.',
  meta_title_en = 'Laser Cutting Service Konya | Tekcan Metal',
  meta_title_ar = 'خدمة القطع بالليزر في قونية | تكجان للحديد',
  meta_title_ru = 'Услуги лазерной резки в Конья | Tekcan Metal',
  meta_desc_en = 'Tekcan Metal laser cutting workshop - fiber laser 0.5-25 mm thickness, ±0.1 mm precision, 1500x3000 mm table. DXF/DWG accepted, same-day quote, 81 cities shipping.',
  meta_desc_ar = 'ورشة القطع بالليزر تكجان للحديد - الليزر الليفي بسماكة 0.5-25 مم، دقة ±0.1 مم، طاولة 1500x3000 مم. قبول ملفات DXF/DWG، عرض سعر في نفس اليوم، شحن إلى 81 مدينة.',
  meta_desc_ru = 'Цех лазерной резки Tekcan Metal — волоконный лазер, толщина 0.5-25 мм, точность ±0.1 мм, стол 1500x3000 мм. Приём DXF/DWG, предложение в день обращения, доставка в 81 город.',
  features_en = '["0.5 mm - 25 mm sheet thickness range", "±0.1 mm industrial precision", "1500 × 3000 mm maximum table size", "DXF, DWG, STEP, PDF file formats", "CAM software-optimized cutting paths", "Carbon, stainless and galvanized sheet", "Same-day cutting and shipping (before 09:00)", "3D modeling and engineering consultancy", "Smooth edge, zero burrs", "Optimum efficiency in complex geometries"]',
  features_ar = '["نطاق سماكة الألواح من 0.5 إلى 25 مم", "دقة صناعية ±0.1 مم", "حجم طاولة أقصى 1500 × 3000 مم", "تنسيقات الملفات DXF و DWG و STEP و PDF", "مسارات قطع محسّنة ببرنامج CAM", "ألواح كربون ومقاومة للصدأ ومجلفنة", "قطع وشحن في نفس اليوم (قبل 09:00)", "نمذجة ثلاثية الأبعاد واستشارة هندسية", "حواف ناعمة، صفر نتوءات", "كفاءة مثالية في الأشكال الهندسية المعقدة"]',
  features_ru = '["Толщина листа 0.5-25 мм", "Промышленная точность ±0.1 мм", "Максимальный стол 1500 × 3000 мм", "Форматы файлов DXF, DWG, STEP, PDF", "Оптимизация резки CAM-программой", "Углеродистый, нержавеющий и оцинкованный лист", "Резка и отгрузка в день обращения (до 09:00)", "3D-моделирование и инженерные консультации", "Гладкая кромка, без заусенцев", "Эффективность сложных геометрий"]',
  specs_en = '{"Cutting Type":"Fiber Laser","Sheet Thickness":"0.5 - 25 mm","Table Size":"1500 × 3000 mm","Precision":"±0.1 mm","Cutting Speed":"5-30 m/min","File Formats":"DXF, DWG, STEP, PDF","Material":"Carbon / Stainless / Galvanized","Lead Time":"Same day - 3 business days"}',
  specs_ar = '{"نوع القطع":"ليزر ليفي","سماكة اللوح":"0.5 - 25 مم","حجم الطاولة":"1500 × 3000 مم","الدقة":"±0.1 مم","سرعة القطع":"5-30 م/دقيقة","تنسيقات الملفات":"DXF, DWG, STEP, PDF","المادة":"كربون / مقاوم للصدأ / مجلفن","مدة التسليم":"نفس اليوم - 3 أيام عمل"}',
  specs_ru = '{"Тип резки":"Волоконный лазер","Толщина листа":"0.5 - 25 мм","Размер стола":"1500 × 3000 мм","Точность":"±0.1 мм","Скорость резки":"5-30 м/мин","Форматы файлов":"DXF, DWG, STEP, PDF","Материал":"Углерод / Нерж / Оцинк","Срок":"В день - 3 дня"}'
WHERE slug = 'lazer-kesim';

UPDATE tm_services SET
  title_en = 'Oxygen Cutting',
  title_ar = 'القطع بالأكسجين',
  title_ru = 'Газокислородная резка',
  short_desc_en = 'Economical CNC oxygen cutting service for thick steel plates from 6 mm to 200 mm. Ideal for heavy structural, shipbuilding and machinery industries.',
  short_desc_ar = 'خدمة قطع اقتصادية بالأكسجين CNC للألواح الفولاذية السميكة من 6 ملم إلى 200 ملم. مثالية لصناعات الهياكل الثقيلة وبناء السفن والآلات.',
  short_desc_ru = 'Экономичная газокислородная CNC-резка толстых стальных листов от 6 до 200 мм. Идеально для тяжёлых металлоконструкций, судостроения и машиностроения.',
  meta_title_en = 'Oxygen Cutting Service Konya | Tekcan Metal',
  meta_title_ar = 'خدمة القطع بالأكسجين في قونية | تكجان للحديد',
  meta_title_ru = 'Газокислородная резка в Конья | Tekcan Metal',
  meta_desc_en = 'Tekcan Metal CNC oxygen cutting service - thick plate cutting from 6-200 mm. K-form, V-form, Y-form weld preparation. Heavy industry experience.',
  meta_desc_ar = 'خدمة القطع بالأكسجين CNC من تكجان للحديد - قطع الألواح السميكة من 6-200 مم. تجهيز اللحام بأشكال K و V و Y. خبرة في الصناعة الثقيلة.',
  meta_desc_ru = 'Газокислородная CNC-резка Tekcan Metal — толстый лист 6-200 мм. Подготовка кромок K, V, Y форм. Опыт в тяжёлой промышленности.',
  features_en = '["6 mm - 200 mm carbon steel plate cutting", "K, V, Y, X form weld preparation", "Multi-torch CNC table 3000 × 12000 mm", "Economical price for thick plates", "Heavy industry, shipbuilding, machinery", "Standard ST37, ST44, ST52 carbon steel", "Same-day cutting capability", "DXF/DWG file acceptance"]',
  features_ar = '["قطع ألواح الفولاذ الكربوني من 6-200 مم", "تجهيز اللحام بأشكال K و V و Y و X", "طاولة CNC متعددة المشاعل 3000 × 12000 مم", "أسعار اقتصادية للألواح السميكة", "الصناعة الثقيلة وبناء السفن والآلات", "الفولاذ الكربوني القياسي ST37, ST44, ST52", "إمكانية القطع في نفس اليوم", "قبول ملفات DXF/DWG"]',
  features_ru = '["Резка углеродистого листа 6-200 мм", "Подготовка кромок K, V, Y, X форм", "Многорезаковый CNC-стол 3000 × 12000 мм", "Экономично для толстых листов", "Тяжёлая промышленность, судостроение", "Стандарт ST37, ST44, ST52", "Резка в день обращения", "Приём файлов DXF/DWG"]',
  specs_en = '{"Cutting Type":"CNC Oxy-Fuel","Sheet Thickness":"6 - 200 mm","Table Size":"3000 × 12000 mm","Material":"Carbon Steel (ST37/ST44/ST52)","Weld Prep":"K, V, Y, X form","Lead Time":"Same day - 5 business days"}',
  specs_ar = '{"نوع القطع":"CNC أكسجين-وقود","سماكة اللوح":"6 - 200 مم","حجم الطاولة":"3000 × 12000 مم","المادة":"فولاذ كربوني (ST37/ST44/ST52)","تجهيز اللحام":"أشكال K, V, Y, X","مدة التسليم":"نفس اليوم - 5 أيام عمل"}',
  specs_ru = '{"Тип резки":"CNC газокислородная","Толщина":"6 - 200 мм","Стол":"3000 × 12000 мм","Материал":"Углерод (ST37/ST44/ST52)","Кромка":"K, V, Y, X","Срок":"В день - 5 дней"}'
WHERE slug = 'oksijen-kesim';

UPDATE tm_services SET
  title_en = 'Decorative Sheet Manufacturing',
  title_ar = 'تصنيع الألواح الزخرفية',
  title_ru = 'Производство декоративных листов',
  short_desc_en = 'Custom-pattern decorative sheet manufacturing for architectural and interior design applications. CNC laser-cut, powder-coated finish.',
  short_desc_ar = 'تصنيع ألواح زخرفية بأنماط مخصصة لتطبيقات التصميم المعماري والداخلي. قطع بليزر CNC، طلاء بالمسحوق.',
  short_desc_ru = 'Производство декоративных листов с индивидуальными узорами для архитектурного и интерьерного дизайна. Лазерная CNC-резка, порошковая окраска.',
  meta_title_en = 'Decorative Sheet & Corten Steel | Tekcan Metal',
  meta_title_ar = 'الألواح الزخرفية وفولاذ كورتن | تكجان للحديد',
  meta_title_ru = 'Декоративные листы и кортен-сталь | Tekcan Metal',
  meta_desc_en = 'Custom decorative sheet patterns, corten steel, perforated panels for villa facades, fences, balcony railings, ceiling panels. Architectural sheet manufacturing.',
  meta_desc_ar = 'أنماط ألواح زخرفية مخصصة، فولاذ كورتن، ألواح مثقبة لواجهات الفلل والأسوار وحواجز الشرفات وألواح السقف. تصنيع الألواح المعمارية.',
  meta_desc_ru = 'Декоративные листы с индивидуальным рисунком, кортен-сталь, перфорированные панели для фасадов вилл, заборов, перил балконов, потолочных панелей.',
  features_en = '["Fiber laser cutting for custom patterns", "Steel, stainless, copper, aluminum sheets", "Corten steel for architectural facades", "Powder-coated finish (10+ year warranty)", "Free 3D rendering and consultation", "Mounting kit support", "Mockup production for large projects", "81 cities shipping with installation support"]',
  features_ar = '["قطع بليزر ليفي للأنماط المخصصة", "ألواح فولاذ ومقاوم للصدأ ونحاس وألمنيوم", "فولاذ كورتن للواجهات المعمارية", "طلاء مسحوقي (ضمان 10+ سنوات)", "تصميم ثلاثي الأبعاد واستشارة مجانية", "دعم طقم التركيب", "إنتاج نموذج مصغر للمشاريع الكبيرة", "شحن إلى 81 مدينة مع دعم التركيب"]',
  features_ru = '["Лазерная резка по индивидуальным узорам", "Сталь, нержавейка, медь, алюминий", "Кортен-сталь для архитектурных фасадов", "Порошковая окраска (гарантия 10+ лет)", "Бесплатный 3D-рендеринг и консультация", "Комплект для монтажа", "Макетное производство для крупных проектов", "Доставка в 81 город с поддержкой монтажа"]',
  specs_en = '{"Cutting Type":"Fiber Laser","Sheet Thickness":"1 - 8 mm","Material":"Steel / Stainless / Copper / Aluminum / Corten","Finish":"Powder coat / Galvanized / Natural patina","Pattern":"Custom or library","Lead Time":"5-15 business days"}',
  specs_ar = '{"نوع القطع":"ليزر ليفي","السماكة":"1 - 8 مم","المادة":"فولاذ / مقاوم للصدأ / نحاس / ألمنيوم / كورتن","التشطيب":"طلاء مسحوقي / مجلفن / طبقة طبيعية","النمط":"مخصص أو من المكتبة","مدة التسليم":"5-15 يوم عمل"}',
  specs_ru = '{"Тип резки":"Волоконный лазер","Толщина":"1 - 8 мм","Материал":"Сталь / Нерж / Медь / Алюминий / Кортен","Покрытие":"Порошковое / Оцинковка / Натуральная патина","Узор":"Индивидуальный или из библиотеки","Срок":"5-15 рабочих дней"}'
WHERE slug = 'dekoratif-saclar';

-- v1.0.59 — Yeni eklenen UI key'lerin 4 dil çevirileri
INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('contact.eyebrow', 'tr', 'İletişim Merkezi', 'contact'),
  ('contact.eyebrow', 'en', 'Contact Center', 'contact'),
  ('contact.eyebrow', 'ar', 'مركز الاتصال', 'contact'),
  ('contact.eyebrow', 'ru', 'Контактный центр', 'contact'),
  ('contact.meta_desc', 'tr', 'Tekcan Metal iletişim — adres, telefon, çalışma saatleri ve mesaj formu.', 'contact'),
  ('contact.meta_desc', 'en', 'Tekcan Metal contact - address, phone, working hours and message form.', 'contact'),
  ('contact.meta_desc', 'ar', 'تواصل تكجان للحديد - العنوان والهاتف وساعات العمل ونموذج الرسالة.', 'contact'),
  ('contact.meta_desc', 'ru', 'Контакты Tekcan Metal — адрес, телефон, часы работы и форма сообщения.', 'contact'),
  ('contact.send_btn', 'tr', 'Mesajı Gönder', 'contact'),
  ('contact.send_btn', 'en', 'Send Message', 'contact'),
  ('contact.send_btn', 'ar', 'أرسل الرسالة', 'contact'),
  ('contact.send_btn', 'ru', 'Отправить сообщение', 'contact'),
  ('err404.message', 'tr', 'Bu sayfa kaldırılmış, taşınmış ya da hiç var olmamış olabilir. Anasayfaya dönüp aradığınızı oradan bulabilirsiniz.', 'err404'),
  ('err404.message', 'en', 'This page may have been removed, moved, or never existed. You can return to the home page to find what you are looking for.', 'err404'),
  ('err404.message', 'ar', 'قد تكون هذه الصفحة قد أُزيلت أو نُقلت أو لم تكن موجودة من الأساس. يمكنك العودة إلى الصفحة الرئيسية للعثور على ما تبحث عنه.', 'err404'),
  ('err404.message', 'ru', 'Эта страница могла быть удалена, перемещена или никогда не существовала. Вы можете вернуться на главную страницу.', 'err404'),
  ('faq.meta_desc', 'tr', 'Tekcan Metal — demir, çelik, sac, boru, profil ve hesaplama konularında müşterilerimizin en çok sorduğu sorular ve detaylı yanıtları.', 'faq'),
  ('faq.meta_desc', 'en', 'Tekcan Metal - frequently asked questions and detailed answers about iron, steel, sheet, pipe, profile and calculations from our customers.', 'faq'),
  ('faq.meta_desc', 'ar', 'تكجان للحديد - الأسئلة المتكررة والإجابات المفصلة حول الحديد والصلب والألواح والأنابيب والمقاطع والحسابات من عملائنا.', 'faq'),
  ('faq.meta_desc', 'ru', 'Tekcan Metal — часто задаваемые вопросы и подробные ответы о металле, листах, трубах, профилях и расчётах от наших клиентов.', 'faq'),
  ('gallery.meta_desc', 'tr', 'Tekcan Metal galeri — atölye, ürünlerimiz, sevkiyat ve makinelerimizden görseller.', 'gallery'),
  ('gallery.meta_desc', 'en', 'Tekcan Metal gallery - photos from our workshop, products, shipments and machinery.', 'gallery'),
  ('gallery.meta_desc', 'ar', 'معرض تكجان للحديد - صور من ورشتنا ومنتجاتنا وشحناتنا وآلاتنا.', 'gallery'),
  ('gallery.meta_desc', 'ru', 'Галерея Tekcan Metal — фотографии нашего цеха, продукции, отгрузок и оборудования.', 'gallery'),
  ('iban.account_number', 'tr', 'Hesap No', 'iban'),
  ('iban.account_number', 'en', 'Account No', 'iban'),
  ('iban.account_number', 'ar', 'رقم الحساب', 'iban'),
  ('iban.account_number', 'ru', 'Номер счёта', 'iban'),
  ('iban.copy_btn', 'tr', 'Kopyala', 'iban'),
  ('iban.copy_btn', 'en', 'Copy', 'iban'),
  ('iban.copy_btn', 'ar', 'نسخ', 'iban'),
  ('iban.copy_btn', 'ru', 'Копировать', 'iban'),
  ('iban.lead_prefix', 'tr', 'Aşağıdaki hesap numaraları', 'iban'),
  ('iban.lead_prefix', 'en', 'The following account numbers are registered to', 'iban'),
  ('iban.lead_prefix', 'ar', 'أرقام الحسابات التالية مسجلة باسم', 'iban'),
  ('iban.lead_prefix', 'ru', 'Следующие номера счетов зарегистрированы на', 'iban'),
  ('iban.lead_suffix', 'tr', 'adına kayıtlıdır.', 'iban'),
  ('iban.lead_suffix', 'en', '.', 'iban'),
  ('iban.lead_suffix', 'ar', '.', 'iban'),
  ('iban.lead_suffix', 'ru', '.', 'iban'),
  ('iban.meta_desc', 'tr', 'Tekcan Metal banka hesap ve IBAN bilgileri. Şirketimize ait güncel hesap numaraları.', 'iban'),
  ('iban.meta_desc', 'en', 'Tekcan Metal bank account and IBAN information. Current account numbers belonging to our company.', 'iban'),
  ('iban.meta_desc', 'ar', 'معلومات الحسابات المصرفية و IBAN لتكجان للحديد. أرقام الحسابات الحالية الخاصة بشركتنا.', 'iban'),
  ('iban.meta_desc', 'ru', 'Банковские счета и IBAN Tekcan Metal. Актуальные номера счетов нашей компании.', 'iban'),
  ('iban.no_banks', 'tr', 'Henüz banka hesabı eklenmedi.', 'iban'),
  ('iban.no_banks', 'en', 'No bank accounts added yet.', 'iban'),
  ('iban.no_banks', 'ar', 'لم تتم إضافة حسابات مصرفية بعد.', 'iban'),
  ('iban.no_banks', 'ru', 'Банковские счета пока не добавлены.', 'iban'),
  ('iban.note', 'tr', 'Para gönderirken açıklama kısmına lütfen <strong>fatura veya cari numaranızı</strong> yazınız.', 'iban'),
  ('iban.note', 'en', 'When sending money, please write <strong>your invoice or account number</strong> in the description.', 'iban'),
  ('iban.note', 'ar', 'عند إرسال الأموال، يرجى كتابة <strong>رقم الفاتورة أو رقم الحساب الجاري</strong> في الوصف.', 'iban'),
  ('iban.note', 'ru', 'При отправке денег укажите в назначении платежа <strong>номер счёта-фактуры или клиентский номер</strong>.', 'iban');

-- v1.0.60 — Yeni eklenen UI key'lerin 4 dil çevirileri (anasayfa)
INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('home.corporate_values_eyebrow', 'tr', 'Kurumsal Değerlerimiz', 'home'),
  ('home.corporate_values_eyebrow', 'en', 'Our Corporate Values', 'home'),
  ('home.corporate_values_eyebrow', 'ar', 'قيمنا المؤسسية', 'home'),
  ('home.corporate_values_eyebrow', 'ru', 'Наши корпоративные ценности', 'home'),
  ('home.products_h2', 'tr', 'Ana Grupta <em>Geniş Yelpaze</em>', 'home'),
  ('home.products_h2', 'en', 'Main Group <em>Wide Range</em>', 'home'),
  ('home.products_h2', 'ar', 'مجموعة رئيسية <em>نطاق واسع</em>', 'home'),
  ('home.products_h2', 'ru', 'Основная группа <em>широкий ассортимент</em>', 'home'),
  ('home.services_h2', 'tr', 'Tedarikten <em>Üretime</em><br>Uçtan Uca Çözüm', 'home'),
  ('home.services_h2', 'en', 'From Supply to <em>Production</em><br>End-to-End Solution', 'home'),
  ('home.services_h2', 'ar', 'من التوريد إلى <em>الإنتاج</em><br>حل شامل', 'home'),
  ('home.services_h2', 'ru', 'От поставки до <em>производства</em><br>Комплексное решение', 'home'),
  ('home.values_h2', 'tr', 'İlke, Kalite ve <em>Güvenle</em> Çalışıyoruz', 'home'),
  ('home.values_h2', 'en', 'We Work with Principle, Quality and <em>Trust</em>', 'home'),
  ('home.values_h2', 'ar', 'نعمل بالمبدأ والجودة <em>والثقة</em>', 'home'),
  ('home.values_h2', 'ru', 'Мы работаем по принципу, качеству и <em>доверию</em>', 'home'),
  ('home.news_h2', 'tr', 'Sektörel <em>Gelişmeler</em> ve Duyurular', 'home'),
  ('home.news_h2', 'en', 'Industry <em>Developments</em> and Announcements', 'home'),
  ('home.news_h2', 'ar', 'تطورات الصناعة <em>والإعلانات</em>', 'home'),
  ('home.news_h2', 'ru', 'Отраслевые <em>события</em> и анонсы', 'home'),
  ('home.partners_h2', 'tr', 'Türkiye''nin <em>Çelik Devleri</em><br>Tedarik Ortaklarımız', 'home'),
  ('home.partners_h2', 'en', 'Turkey''s <em>Steel Giants</em><br>Our Supply Partners', 'home'),
  ('home.partners_h2', 'ar', 'عمالقة <em>الفولاذ في تركيا</em><br>شركاؤنا في التوريد', 'home'),
  ('home.partners_h2', 'ru', '<em>Стальные гиганты</em> Турции<br>Наши поставщики', 'home'),
  ('home.cta_title', 'tr', 'Projeniz için <strong>özel teklif</strong> almak ister misiniz?', 'home'),
  ('home.cta_title', 'en', 'Would you like a <strong>custom quote</strong> for your project?', 'home'),
  ('home.cta_title', 'ar', 'هل تريد <strong>عرض سعر مخصصاً</strong> لمشروعك؟', 'home'),
  ('home.cta_title', 'ru', 'Хотите получить <strong>индивидуальное предложение</strong> для вашего проекта?', 'home'),
  ('home.products_section_lead', 'tr', 'Sanayi, inşaat ve özel proje gereksinimlerine yönelik tedarik ve üretim hizmeti sunuyoruz. Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş başta olmak üzere sektörün lider üreticilerinden doğrudan tedarik güvencesiyle.', 'home'),
  ('home.products_section_lead', 'en', 'We provide supply and production services for industrial, construction and special project requirements. With direct supply guarantee from leading manufacturers including Borcelik, Erdemir, Habas, Tosyali Steel, Kardemir and Icdas.', 'home'),
  ('home.products_section_lead', 'ar', 'نقدم خدمات التوريد والإنتاج لمتطلبات الصناعة والبناء والمشاريع الخاصة. بضمان التوريد المباشر من كبار المصنعين بما في ذلك بورجيليك وإرديمير وحاباش وتوسيالي للفولاذ وكاردمير وإجداش.', 'home'),
  ('home.products_section_lead', 'ru', 'Мы предоставляем услуги по поставке и производству для промышленных, строительных и специальных проектов. С гарантией прямой поставки от ведущих производителей, включая Borcelik, Erdemir, Habas, Tosyali Steel, Kardemir и Icdas.', 'home'),
  ('home.services_section_lead', 'tr', 'Stoklu satışın yanı sıra atölye yetkinliklerimizle proje tabanlı üretim hizmetleri sunuyoruz. Lazer kesimden oksijen kesime, dekoratif sac üretiminden CNC işleme kadar.', 'home'),
  ('home.services_section_lead', 'en', 'In addition to stock sales, we offer project-based production services with our workshop capabilities. From laser cutting to oxygen cutting, decorative sheet production to CNC machining.', 'home'),
  ('home.services_section_lead', 'ar', 'بالإضافة إلى المبيعات من المخزون، نقدم خدمات إنتاج قائمة على المشاريع بقدرات ورشتنا. من القطع بالليزر إلى القطع بالأكسجين، ومن إنتاج الألواح الزخرفية إلى التصنيع CNC.', 'home'),
  ('home.services_section_lead', 'ru', 'Помимо продажи со склада, мы предлагаем услуги проектного производства с нашими производственными мощностями. От лазерной резки до газокислородной, от декоративных листов до CNC-обработки.', 'home'),
  ('home.values_lead', 'tr', '2005''ten bu yana üç temel ilke üzerinde yükseliyoruz: kaliteden ödün vermeden, operasyonel mükemmellikle ve müşteri odaklı bir yaklaşımla.', 'home'),
  ('home.values_lead', 'en', 'Since 2005, we rise on three fundamental principles: uncompromising on quality, operational excellence and customer-focused approach.', 'home'),
  ('home.values_lead', 'ar', 'منذ عام 2005، نرتقي على ثلاثة مبادئ أساسية: عدم التنازل عن الجودة، والتميز التشغيلي، والنهج المتمحور حول العميل.', 'home'),
  ('home.values_lead', 'ru', 'С 2005 года мы стоим на трёх основных принципах: бескомпромиссное качество, операционное совершенство и клиентоориентированный подход.', 'home'),
  ('home.partners_lead', 'tr', 'Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş başta olmak üzere sektörün önde gelen entegre çelik üretim tesislerinden doğrudan ürün tedarik ediyoruz.', 'home'),
  ('home.partners_lead', 'en', 'We directly supply products from leading integrated steel production facilities including Borcelik, Erdemir, Habas, Tosyali Steel, Kardemir and Icdas.', 'home'),
  ('home.partners_lead', 'ar', 'نقوم بتوريد المنتجات مباشرة من منشآت إنتاج الفولاذ المتكاملة الرائدة بما في ذلك بورجيليك وإرديمير وحاباش وتوسيالي للفولاذ وكاردمير وإجداش.', 'home'),
  ('home.partners_lead', 'ru', 'Мы напрямую поставляем продукцию от ведущих интегрированных металлургических предприятий, включая Borcelik, Erdemir, Habas, Tosyali Steel, Kardemir и Icdas.', 'home'),
  ('home.cta_lead', 'tr', 'Uzman satış ekibimiz, ihtiyacınıza özel ürün ve sevkiyat planlamasını en kısa sürede hazırlayıp size sunar.', 'home'),
  ('home.cta_lead', 'en', 'Our expert sales team prepares product and shipping plans tailored to your needs and delivers them as soon as possible.', 'home'),
  ('home.cta_lead', 'ar', 'يقوم فريق المبيعات الخبير لدينا بإعداد خطط المنتجات والشحن المخصصة لاحتياجاتك وتقديمها في أقرب وقت ممكن.', 'home'),
  ('home.cta_lead', 'ru', 'Наша опытная команда продаж готовит продуктовые и логистические планы под ваши потребности и предоставляет их в кратчайшие сроки.', 'home'),
  ('home.value_quality', 'tr', 'Kalite ve Standart', 'home'),
  ('home.value_quality', 'en', 'Quality and Standard', 'home'),
  ('home.value_quality', 'ar', 'الجودة والمعيار', 'home'),
  ('home.value_quality', 'ru', 'Качество и стандарты', 'home'),
  ('home.value_operational', 'tr', 'Operasyonel Mükemmellik', 'home'),
  ('home.value_operational', 'en', 'Operational Excellence', 'home'),
  ('home.value_operational', 'ar', 'التميز التشغيلي', 'home'),
  ('home.value_operational', 'ru', 'Операционное совершенство', 'home'),
  ('home.value_customer', 'tr', 'Müşteri Odaklılık', 'home'),
  ('home.value_customer', 'en', 'Customer Focus', 'home'),
  ('home.value_customer', 'ar', 'التركيز على العميل', 'home'),
  ('home.value_customer', 'ru', 'Клиентоориентированность', 'home'),
  ('home.value_quality_desc', 'tr', 'Türkiye''nin lider çelik üreticilerinden doğrudan tedarik ettiğimiz ürünler, uluslararası kalite standartlarındadır. Sertifikalı, izlenebilir, üretici onaylı.', 'home'),
  ('home.value_quality_desc', 'en', 'The products we directly supply from Turkey''s leading steel manufacturers meet international quality standards. Certified, traceable, manufacturer-approved.', 'home'),
  ('home.value_quality_desc', 'ar', 'المنتجات التي نقوم بتوريدها مباشرة من كبار مصنعي الفولاذ في تركيا تلبي معايير الجودة الدولية. معتمدة وقابلة للتتبع ومعتمدة من الشركة المصنعة.', 'home'),
  ('home.value_quality_desc', 'ru', 'Продукция, которую мы напрямую поставляем от ведущих производителей стали Турции, соответствует международным стандартам качества. Сертифицировано, прослеживаемо, одобрено производителем.', 'home'),
  ('home.value_operational_desc', 'tr', 'Geniş stoğumuz, lazer ve oksijen kesim atölyemiz, aynı gün üretim seçeneğimiz ve 7/24 sevkiyat ağımızla zaman, teslimatımızın ayrılmaz bir parçasıdır.', 'home'),
  ('home.value_operational_desc', 'en', 'With our extensive stock, laser and oxygen cutting workshops, same-day production option and 24/7 shipping network, time is an integral part of our delivery.', 'home'),
  ('home.value_operational_desc', 'ar', 'مع مخزوننا الواسع وورش القطع بالليزر والأكسجين وخيار الإنتاج في نفس اليوم وشبكة الشحن على مدار الساعة، يعد الوقت جزءاً لا يتجزأ من تسليمنا.', 'home'),
  ('home.value_operational_desc', 'ru', 'С нашими обширными складскими запасами, лазерными и газокислородными цехами, опцией производства в день обращения и круглосуточной сетью доставки время — неотъемлемая часть нашей поставки.', 'home'),
  ('home.value_customer_desc', 'tr', '&quot;Ticaret ile Bitmeyen Dostluk&quot; felsefemizle her müşteriyi bir iş ortağı olarak görüyor; uzun vadeli ve güvene dayalı ilişkiler kuruyoruz.', 'home'),
  ('home.value_customer_desc', 'en', 'With our philosophy of &quot;Friendship Beyond Trade&quot;, we view every customer as a business partner; we build long-term and trust-based relationships.', 'home'),
  ('home.value_customer_desc', 'ar', 'بفلسفتنا &quot;صداقة لا تنتهي بالتجارة&quot;، نعتبر كل عميل شريكاً في العمل؛ نبني علاقات طويلة الأمد قائمة على الثقة.', 'home'),
  ('home.value_customer_desc', 'ru', 'С нашей философией &quot;Дружба, не ограниченная торговлей&quot;, мы рассматриваем каждого клиента как делового партнёра; строим долгосрочные отношения, основанные на доверии.', 'home'),
  ('home.explore_products', 'tr', 'Ürünleri İncele', 'home'),
  ('home.explore_products', 'en', 'Explore Products', 'home'),
  ('home.explore_products', 'ar', 'استكشف المنتجات', 'home'),
  ('home.explore_products', 'ru', 'Посмотреть товары', 'home'),
  ('home.explore_detail', 'tr', 'Detaylı İncele', 'home'),
  ('home.explore_detail', 'en', 'View Details', 'home'),
  ('home.explore_detail', 'ar', 'عرض التفاصيل', 'home'),
  ('home.explore_detail', 'ru', 'Посмотреть детали', 'home'),
  ('home.view_all_news', 'tr', 'Tümünü İncele', 'home'),
  ('home.view_all_news', 'en', 'View All', 'home'),
  ('home.view_all_news', 'ar', 'عرض الكل', 'home'),
  ('home.view_all_news', 'ru', 'Смотреть все', 'home'),
  ('home.view_all_partners', 'tr', 'Tüm Çözüm Ortaklarını İncele', 'home'),
  ('home.view_all_partners', 'en', 'View All Solution Partners', 'home'),
  ('home.view_all_partners', 'ar', 'عرض جميع شركاء الحلول', 'home'),
  ('home.view_all_partners', 'ru', 'Все партнёры', 'home'),
  ('services.eyebrow.bending', 'tr', 'Sac Şekillendirme', 'services'),
  ('services.eyebrow.bending', 'en', 'Sheet Forming', 'services'),
  ('services.eyebrow.bending', 'ar', 'تشكيل الألواح', 'services'),
  ('services.eyebrow.bending', 'ru', 'Формовка листа', 'services'),
  ('services.no_content', 'tr', 'Bu hizmet için detaylı içerik yakında eklenecektir.', 'services'),
  ('services.no_content', 'en', 'Detailed content for this service will be added soon.', 'services'),
  ('services.no_content', 'ar', 'سيتم إضافة محتوى مفصل لهذه الخدمة قريباً.', 'services'),
  ('services.no_content', 'ru', 'Подробное описание этой услуги будет добавлено в ближайшее время.', 'services'),
  ('services.production_specs', 'tr', 'Üretim <em>Özellikleri</em>', 'services'),
  ('services.production_specs', 'en', 'Production <em>Features</em>', 'services'),
  ('services.production_specs', 'ar', 'ميزات <em>الإنتاج</em>', 'services'),
  ('services.production_specs', 'ru', 'Особенности <em>производства</em>', 'services');

-- v1.0.61 — Yeni eklenen UI key'leri (about/services/products/product)
INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('about.hero_eyebrow', 'tr', 'Tekcan Metal · 2005', 'about'),
  ('about.hero_eyebrow', 'en', 'Tekcan Metal · 2005', 'about'),
  ('about.hero_eyebrow', 'ar', 'تكجان للحديد · 2005', 'about'),
  ('about.hero_eyebrow', 'ru', 'Tekcan Metal · 2005', 'about'),
  ('about.hero_h1', 'tr', 'Demir adına <em>Herşey.</em><br><strong>Yarım asra yakın güven.</strong>', 'about'),
  ('about.hero_h1', 'en', 'Everything in the name of <em>Steel.</em><br><strong>Trust nearing half a century.</strong>', 'about'),
  ('about.hero_h1', 'ar', 'كل ما يخص <em>الفولاذ.</em><br><strong>ثقة تقترب من نصف قرن.</strong>', 'about'),
  ('about.hero_h1', 'ru', 'Всё во имя <em>стали.</em><br><strong>Доверие в течение почти полувека.</strong>', 'about'),
  ('about.hero_lead', 'tr', 'Konya''nın köklü demir-çelik tedarikçisi olarak, 2005''ten bu yana sanayi ve inşaat sektörünün her ölçekteki ihtiyacını karşılamak için çalışıyoruz. Türkiye''nin önde gelen üreticilerinin temsilciliği bizim, kalite ve güven sözümüz sizin.', 'about'),
  ('about.hero_lead', 'en', 'As the established steel supplier of Konya, we have been serving every scale of the industrial and construction sector since 2005. We represent Turkey''s leading manufacturers; quality and trust are our promise to you.', 'about'),
  ('about.hero_lead', 'ar', 'بصفتنا المورد العريق للحديد والصلب في قونية، نعمل منذ عام 2005 لتلبية احتياجات قطاعي الصناعة والبناء بجميع أحجامها. نمثل كبار المصنعين في تركيا، والجودة والثقة وعدنا لك.', 'about'),
  ('about.hero_lead', 'ru', 'Как ведущий поставщик металла в Конье, с 2005 года мы обслуживаем промышленный и строительный сектор любых масштабов. Мы представляем ведущих производителей Турции; качество и доверие — наше обещание вам.', 'about'),
  ('about.founded', 'tr', 'Kuruluş', 'about'),
  ('about.founded', 'en', 'Founded', 'about'),
  ('about.founded', 'ar', 'تأسست', 'about'),
  ('about.founded', 'ru', 'Основано', 'about'),
  ('about.incorporated', 'tr', 'Şirketleşme', 'about'),
  ('about.incorporated', 'en', 'Incorporation', 'about'),
  ('about.incorporated', 'ar', 'التأسيس الرسمي', 'about'),
  ('about.incorporated', 'ru', 'Регистрация', 'about'),
  ('about.headquarters', 'tr', 'Merkez', 'about'),
  ('about.headquarters', 'en', 'Headquarters', 'about'),
  ('about.headquarters', 'ar', 'المقر', 'about'),
  ('about.headquarters', 'ru', 'Штаб-квартира', 'about'),
  ('about.supply_network', 'tr', 'Tedarik Ağı', 'about'),
  ('about.supply_network', 'en', 'Supply Network', 'about'),
  ('about.supply_network', 'ar', 'شبكة التوريد', 'about'),
  ('about.supply_network', 'ru', 'Сеть поставок', 'about'),
  ('about.city_value', 'tr', 'Karatay <em>·</em> Konya', 'about'),
  ('about.city_value', 'en', 'Karatay <em>·</em> Konya', 'about'),
  ('about.city_value', 'ar', 'كاراتاي <em>·</em> قونية', 'about'),
  ('about.city_value', 'ru', 'Каратай <em>·</em> Конья', 'about'),
  ('about.network_value', 'tr', 'Türkiye <em>geneli</em>', 'about'),
  ('about.network_value', 'en', 'Across <em>Turkey</em>', 'about'),
  ('about.network_value', 'ar', 'في جميع أنحاء <em>تركيا</em>', 'about'),
  ('about.network_value', 'ru', 'По всей <em>Турции</em>', 'about'),
  ('about.philosophy_eyebrow', 'tr', 'Kurum Felsefemiz', 'about'),
  ('about.philosophy_eyebrow', 'en', 'Our Corporate Philosophy', 'about'),
  ('about.philosophy_eyebrow', 'ar', 'فلسفة شركتنا', 'about'),
  ('about.philosophy_eyebrow', 'ru', 'Наша корпоративная философия', 'about'),
  ('about.philosophy_quote', 'tr', 'Bizim için ticaret yalnızca alım-satım değildir. Demir-çelik, asırlardır insanlığın inşasında yer almış asil bir malzemedir; <strong>onu doğru şekilde tedarik etmek de bir sorumluluktur.</strong> İşte bu yüzden her partide kaliteyi, her teslimatta sözümüzü, her müşteride dostluğu ararız.', 'about'),
  ('about.philosophy_quote', 'en', 'For us, trade is not just buying and selling. Steel is a noble material that has shaped humanity for centuries; <strong>supplying it properly is a responsibility.</strong> That is why we seek quality in every batch, our promise in every delivery, and friendship in every customer.', 'about'),
  ('about.philosophy_quote', 'ar', 'بالنسبة لنا، التجارة ليست مجرد بيع وشراء. الفولاذ مادة نبيلة شكلت البشرية لقرون؛ <strong>توريده بشكل صحيح هو مسؤولية.</strong> لهذا السبب نبحث عن الجودة في كل دفعة، وعن وعدنا في كل تسليم، وعن الصداقة في كل عميل.', 'about'),
  ('about.philosophy_quote', 'ru', 'Для нас торговля — это не просто купля-продажа. Сталь — благородный материал, веками формирующий человечество; <strong>её правильное снабжение — это ответственность.</strong> Поэтому мы ищем качество в каждой партии, наше обещание в каждой поставке и дружбу в каждом клиенте.', 'about'),
  ('about.management_sig', 'tr', '— Tekcan Metal Yönetimi', 'about'),
  ('about.management_sig', 'en', '— Tekcan Metal Management', 'about'),
  ('about.management_sig', 'ar', '— إدارة تكجان للحديد', 'about'),
  ('about.management_sig', 'ru', '— Руководство Tekcan Metal', 'about'),
  ('about.section_i', 'tr', 'Bölüm I', 'about'),
  ('about.section_i', 'en', 'Chapter I', 'about'),
  ('about.section_i', 'ar', 'الفصل الأول', 'about'),
  ('about.section_i', 'ru', 'Глава I', 'about'),
  ('about.our_story', 'tr', 'Hikâyemiz', 'about'),
  ('about.our_story', 'en', 'Our Story', 'about'),
  ('about.our_story', 'ar', 'قصتنا', 'about'),
  ('about.our_story', 'ru', 'Наша история', 'about'),
  ('about.timeline_today', 'tr', '2005 – Bugün', 'about'),
  ('about.timeline_today', 'en', '2005 – Today', 'about'),
  ('about.timeline_today', 'ar', '2005 – اليوم', 'about'),
  ('about.timeline_today', 'ru', '2005 – Сегодня', 'about'),
  ('about.opener_h2', 'tr', 'Seçkin Markalar <em>Tekcan Metal''de</em>', 'about'),
  ('about.opener_h2', 'en', 'Premium Brands <em>at Tekcan Metal</em>', 'about'),
  ('about.opener_h2', 'ar', 'علامات تجارية مميزة <em>في تكجان للحديد</em>', 'about'),
  ('about.opener_h2', 'ru', 'Премиум-бренды <em>в Tekcan Metal</em>', 'about'),
  ('about.opener_deck', 'tr', '<strong>Yarım asra yakın güven, tek bir adreste.</strong> Sayfaları çevirdikçe; her partide kaliteyi, her teslimatta sözümüzü, her müşteride dostluğu arayan bir kurumun hikâyesi şekillenecek. <em>Hoşgeldiniz.</em>', 'about'),
  ('about.opener_deck', 'en', '<strong>Trust nearing half a century, in one address.</strong> As you turn the pages; the story of an organization that seeks quality in every batch, our word in every delivery, and friendship in every customer will unfold. <em>Welcome.</em>', 'about'),
  ('about.opener_deck', 'ar', '<strong>ثقة تقترب من نصف قرن، في عنوان واحد.</strong> مع تقليب الصفحات، ستتكشف قصة منظمة تبحث عن الجودة في كل دفعة، وعن كلمتنا في كل تسليم، وعن الصداقة في كل عميل. <em>مرحباً بك.</em>', 'about'),
  ('about.opener_deck', 'ru', '<strong>Доверие в течение почти полувека, в одном адресе.</strong> Перелистывая страницы, вы откроете историю компании, которая ищет качество в каждой партии, своё слово в каждой поставке и дружбу в каждом клиенте. <em>Добро пожаловать.</em>', 'about'),
  ('about.cta_h3', 'tr', 'Geleceğe atılan <strong>çelik adımlar</strong> için, bizden teklif almayı <strong>unutmayın</strong>.', 'about'),
  ('about.cta_h3', 'en', 'For <strong>steel steps</strong> into the future, don''t forget to <strong>get a quote</strong> from us.', 'about'),
  ('about.cta_h3', 'ar', 'من أجل <strong>خطوات فولاذية</strong> نحو المستقبل، لا تنسَ <strong>طلب عرض سعر</strong> منا.', 'about'),
  ('about.cta_h3', 'ru', 'Для <strong>стальных шагов</strong> в будущее, не забудьте <strong>получить предложение</strong> от нас.', 'about'),
  ('about.timeline_h2', 'tr', 'Yıllara Sığan <em>Bir Miras</em>', 'about'),
  ('about.timeline_h2', 'en', '<em>A Legacy</em> Across Years', 'about'),
  ('about.timeline_h2', 'ar', '<em>إرث</em> عبر السنين', 'about'),
  ('about.timeline_h2', 'ru', '<em>Наследие</em> через годы', 'about'),
  ('about.timeline_founding', 'tr', 'Kuruluş', 'about'),
  ('about.timeline_founding', 'en', 'Founding', 'about'),
  ('about.timeline_founding', 'ar', 'التأسيس', 'about'),
  ('about.timeline_founding', 'ru', 'Основание', 'about'),
  ('about.timeline_incorporation', 'tr', 'Şirketleşme', 'about'),
  ('about.timeline_incorporation', 'en', 'Incorporation', 'about'),
  ('about.timeline_incorporation', 'ar', 'التحويل لشركة', 'about'),
  ('about.timeline_incorporation', 'ru', 'Корпорация', 'about'),
  ('about.timeline_services', 'tr', 'Hizmet Çeşitlenmesi', 'about'),
  ('about.timeline_services', 'en', 'Service Diversification', 'about'),
  ('about.timeline_services', 'ar', 'تنويع الخدمات', 'about'),
  ('about.timeline_services', 'ru', 'Диверсификация услуг', 'about'),
  ('about.timeline_loyalty', 'tr', 'Müşteri Sadakat Programı', 'about'),
  ('about.timeline_loyalty', 'en', 'Customer Loyalty Program', 'about'),
  ('about.timeline_loyalty', 'ar', 'برنامج ولاء العملاء', 'about'),
  ('about.timeline_loyalty', 'ru', 'Программа лояльности', 'about'),
  ('about.timeline_digital', 'tr', 'Dijital Dönüşüm', 'about'),
  ('about.timeline_digital', 'en', 'Digital Transformation', 'about'),
  ('about.timeline_digital', 'ar', 'التحول الرقمي', 'about'),
  ('about.timeline_digital', 'ru', 'Цифровая трансформация', 'about'),
  ('about.values_h2', 'tr', 'Üç Sütun Üzerinde <em>Yükseliriz</em>', 'about'),
  ('about.values_h2', 'en', 'We <em>Rise</em> on Three Pillars', 'about'),
  ('about.values_h2', 'ar', 'نرتقي على <em>ثلاث ركائز</em>', 'about'),
  ('about.values_h2', 'ru', 'Мы <em>стоим</em> на трёх столпах', 'about'),
  ('about.value_kalite', 'tr', 'Kalite', 'about'),
  ('about.value_kalite', 'en', 'Quality', 'about'),
  ('about.value_kalite', 'ar', 'الجودة', 'about'),
  ('about.value_kalite', 'ru', 'Качество', 'about'),
  ('about.value_soz', 'tr', 'Söz', 'about'),
  ('about.value_soz', 'en', 'Promise', 'about'),
  ('about.value_soz', 'ar', 'الوعد', 'about'),
  ('about.value_soz', 'ru', 'Обещание', 'about'),
  ('about.value_dostluk', 'tr', 'Dostluk', 'about'),
  ('about.value_dostluk', 'en', 'Friendship', 'about'),
  ('about.value_dostluk', 'ar', 'الصداقة', 'about'),
  ('about.value_dostluk', 'ru', 'Дружба', 'about'),
  ('about.stats_h2', 'tr', 'Rakamlarla <em>Tekcan Metal</em>', 'about'),
  ('about.stats_h2', 'en', '<em>Tekcan Metal</em> in Numbers', 'about'),
  ('about.stats_h2', 'ar', '<em>تكجان للحديد</em> بالأرقام', 'about'),
  ('about.stats_h2', 'ru', '<em>Tekcan Metal</em> в цифрах', 'about'),
  ('about.partners_h2', 'tr', 'Türkiye''nin Önde Gelen <em>Üreticileri</em>', 'about'),
  ('about.partners_h2', 'en', 'Turkey''s Leading <em>Manufacturers</em>', 'about'),
  ('about.partners_h2', 'ar', 'كبار <em>المصنعين</em> في تركيا', 'about'),
  ('about.partners_h2', 'ru', 'Ведущие <em>производители</em> Турции', 'about'),
  ('about.philosophy_short', 'tr', '"Ticaret ile bitmeyen <strong>Dostluk</strong>."', 'about'),
  ('about.philosophy_short', 'en', '"<strong>Friendship</strong> beyond trade."', 'about'),
  ('about.philosophy_short', 'ar', '"<strong>صداقة</strong> لا تنتهي بالتجارة."', 'about'),
  ('about.philosophy_short', 'ru', '"<strong>Дружба</strong>, не ограниченная торговлей."', 'about'),
  ('services.list_title', 'tr', 'Endüstriyel Yetkinliklerimiz', 'services'),
  ('services.list_title', 'en', 'Our Industrial Capabilities', 'services'),
  ('services.list_title', 'ar', 'قدراتنا الصناعية', 'services'),
  ('services.list_title', 'ru', 'Наши промышленные возможности', 'services'),
  ('services.list_meta', 'tr', 'Tekcan Metal hizmetleri: lazer kesim, oksijen kesim, dekoratif sac üretimi. CNC tabanlı tam donanımlı atölye yetkinliği.', 'services'),
  ('services.list_meta', 'en', 'Tekcan Metal services: laser cutting, oxygen cutting, decorative sheet manufacturing. CNC-based fully equipped workshop capability.', 'services'),
  ('services.list_meta', 'ar', 'خدمات تكجان للحديد: القطع بالليزر، القطع بالأكسجين، تصنيع الألواح الزخرفية. ورشة مجهزة بالكامل بقدرة CNC.', 'services'),
  ('services.list_meta', 'ru', 'Услуги Tekcan Metal: лазерная резка, газокислородная резка, производство декоративных листов. Полностью оборудованный CNC-цех.', 'services'),
  ('services.hero_h1', 'tr', 'Tedarikçinizden <em>Çözüm</em><br>Ortağınıza', 'services'),
  ('services.hero_h1', 'en', 'From Supplier to <em>Solution</em><br>Partner', 'services'),
  ('services.hero_h1', 'ar', 'من مورد إلى شريك <em>الحلول</em>', 'services'),
  ('services.hero_h1', 'ru', 'От поставщика к <em>партнёру</em> по решениям', 'services'),
  ('services.list_h2', 'tr', 'Çelik İşleme <em>Sanatı</em>', 'services'),
  ('services.list_h2', 'en', 'The <em>Art</em> of Steel Processing', 'services'),
  ('services.list_h2', 'ar', '<em>فن</em> معالجة الفولاذ', 'services'),
  ('services.list_h2', 'ru', '<em>Искусство</em> обработки стали', 'services'),
  ('services.workshop_service', 'tr', 'Atölye Hizmeti', 'services'),
  ('services.workshop_service', 'en', 'Workshop Service', 'services'),
  ('services.workshop_service', 'ar', 'خدمة ورشة', 'services'),
  ('services.workshop_service', 'ru', 'Услуга цеха', 'services'),
  ('services.workshop_capacity', 'tr', 'Atölye Kapasitemiz', 'services'),
  ('services.workshop_capacity', 'en', 'Our Workshop Capacity', 'services'),
  ('services.workshop_capacity', 'ar', 'قدرة ورشتنا', 'services'),
  ('services.workshop_capacity', 'ru', 'Мощность цеха', 'services'),
  ('services.three_principles', 'tr', 'Üç İlke Üzerinde <em>Yükseliriz</em>', 'services'),
  ('services.three_principles', 'en', 'We <em>Rise</em> on Three Principles', 'services'),
  ('services.three_principles', 'ar', 'نرتقي على <em>ثلاثة مبادئ</em>', 'services'),
  ('services.three_principles', 'ru', 'Мы <em>стоим</em> на трёх принципах', 'services'),
  ('services.principle_precision', 'tr', 'Hassasiyet', 'services'),
  ('services.principle_precision', 'en', 'Precision', 'services'),
  ('services.principle_precision', 'ar', 'الدقة', 'services'),
  ('services.principle_precision', 'ru', 'Точность', 'services'),
  ('services.principle_speed', 'tr', 'Hız', 'services'),
  ('services.principle_speed', 'en', 'Speed', 'services'),
  ('services.principle_speed', 'ar', 'السرعة', 'services'),
  ('services.principle_speed', 'ru', 'Скорость', 'services'),
  ('services.principle_warranty', 'tr', 'Garanti', 'services'),
  ('services.principle_warranty', 'en', 'Warranty', 'services'),
  ('services.principle_warranty', 'ar', 'الضمان', 'services'),
  ('services.principle_warranty', 'ru', 'Гарантия', 'services'),
  ('services.cta_h2', 'tr', 'Projeniz için <strong>özel teklif</strong> alın', 'services'),
  ('services.cta_h2', 'en', 'Get a <strong>custom quote</strong> for your project', 'services'),
  ('services.cta_h2', 'ar', 'احصل على <strong>عرض سعر مخصص</strong> لمشروعك', 'services'),
  ('services.cta_h2', 'ru', 'Получите <strong>индивидуальное предложение</strong> для вашего проекта', 'services'),
  ('products.title_prefix', 'tr', 'Ürünler', 'products'),
  ('products.title_prefix', 'en', 'Products', 'products'),
  ('products.title_prefix', 'ar', 'المنتجات', 'products'),
  ('products.title_prefix', 'ru', 'Продукция', 'products'),
  ('products.catalog_title', 'tr', 'Ürün Katalogumuz', 'products'),
  ('products.catalog_title', 'en', 'Our Product Catalog', 'products'),
  ('products.catalog_title', 'ar', 'كتالوج منتجاتنا', 'products'),
  ('products.catalog_title', 'ru', 'Наш каталог продукции', 'products'),
  ('products.products_label', 'tr', 'Ürünleri', 'products'),
  ('products.products_label', 'en', 'Products', 'products'),
  ('products.products_label', 'ar', 'المنتجات', 'products'),
  ('products.products_label', 'ru', 'Продукция', 'products'),
  ('products.search', 'tr', 'Arama', 'products'),
  ('products.search', 'en', 'Search', 'products'),
  ('products.search', 'ar', 'البحث', 'products'),
  ('products.search', 'ru', 'Поиск', 'products'),
  ('products.hero_h1', 'tr', 'Çeliğin <em>Sonsuz Yelpazesi</em><br>Tek Adreste', 'products'),
  ('products.hero_h1', 'en', 'The <em>Infinite Range</em> of Steel<br>in One Address', 'products'),
  ('products.hero_h1', 'ar', '<em>النطاق اللانهائي</em> للفولاذ<br>في عنوان واحد', 'products'),
  ('products.hero_h1', 'ru', '<em>Бесконечный спектр</em> стали<br>в одном адресе', 'products'),
  ('products.not_found', 'tr', 'Aradığınız ürün bulunamadı', 'products'),
  ('products.not_found', 'en', 'Product you are looking for not found', 'products'),
  ('products.not_found', 'ar', 'المنتج الذي تبحث عنه غير موجود', 'products'),
  ('products.not_found', 'ru', 'Искомый товар не найден', 'products'),
  ('products.cta_h2', 'tr', 'Stoğumuzda <strong>olmayan ürün</strong> için<br>tedarik desteği', 'products'),
  ('products.cta_h2', 'en', 'Supply support for <strong>products not in stock</strong>', 'products'),
  ('products.cta_h2', 'ar', 'دعم التوريد للمنتجات <strong>غير المتوفرة في المخزون</strong>', 'products'),
  ('products.cta_h2', 'ru', 'Поддержка поставок <strong>отсутствующих товаров</strong>', 'products'),
  ('products.empty_category', 'tr', 'Bu kategoride henüz ürün bulunmuyor.', 'products'),
  ('products.empty_category', 'en', 'No products in this category yet.', 'products'),
  ('products.empty_category', 'ar', 'لا توجد منتجات في هذه الفئة بعد.', 'products'),
  ('products.empty_category', 'ru', 'В этой категории пока нет товаров.', 'products'),
  ('products.cta_custom_title', 'tr', 'Aradığınız ölçü/cins bu listede yok mu?', 'products'),
  ('products.cta_custom_title', 'en', 'Cannot find the size/type you are looking for?', 'products'),
  ('products.cta_custom_title', 'ar', 'لا تجد المقاس/النوع الذي تبحث عنه؟', 'products'),
  ('products.cta_custom_title', 'ru', 'Не нашли нужный размер/тип?', 'products'),
  ('products.cta_custom_lead', 'tr', 'Stok dışı veya özel ölçü ürünler için bizi arayın, hızlıca tedarik edelim.', 'products'),
  ('products.cta_custom_lead', 'en', 'Call us for out-of-stock or custom-size products; we will supply quickly.', 'products'),
  ('products.cta_custom_lead', 'ar', 'اتصل بنا للمنتجات غير المتوفرة في المخزون أو ذات المقاسات الخاصة، وسنوفرها بسرعة.', 'products'),
  ('products.cta_custom_lead', 'ru', 'Звоните нам для товаров вне склада или нестандартных размеров — поставим быстро.', 'products'),
  ('products.whatsapp_intent', 'tr', 'kategorisinden ürün almak istiyorum.', 'products'),
  ('products.whatsapp_intent', 'en', 'category — I want to buy products.', 'products'),
  ('products.whatsapp_intent', 'ar', 'الفئة - أريد شراء منتجات.', 'products'),
  ('products.whatsapp_intent', 'ru', 'категории — хочу приобрести продукцию.', 'products'),
  ('products.category_meta_suffix', 'tr', 'kategorisi ürünleri.', 'products'),
  ('products.category_meta_suffix', 'en', 'category products.', 'products'),
  ('products.category_meta_suffix', 'ar', 'منتجات الفئة.', 'products'),
  ('products.category_meta_suffix', 'ru', 'товары категории.', 'products'),
  ('product.specifications_h3', 'tr', 'Ürün Spesifikasyonları', 'product'),
  ('product.specifications_h3', 'en', 'Product Specifications', 'product'),
  ('product.specifications_h3', 'ar', 'مواصفات المنتج', 'product'),
  ('product.specifications_h3', 'ru', 'Спецификации продукта', 'product'),
  ('product.call_for_price', 'tr', 'Fiyat / Stok için Ara', 'product'),
  ('product.call_for_price', 'en', 'Call for Price / Stock', 'product'),
  ('product.call_for_price', 'ar', 'اتصل للسعر / المخزون', 'product'),
  ('product.call_for_price', 'ru', 'Узнать цену / наличие', 'product'),
  ('product.whatsapp_ask', 'tr', 'WhatsApp ile Sor', 'product'),
  ('product.whatsapp_ask', 'en', 'Ask via WhatsApp', 'product'),
  ('product.whatsapp_ask', 'ar', 'اسأل عبر واتساب', 'product'),
  ('product.whatsapp_ask', 'ru', 'Спросить в WhatsApp', 'product'),
  ('product.whatsapp_intent', 'tr', 'ürünü hakkında bilgi almak istiyorum.', 'product'),
  ('product.whatsapp_intent', 'en', 'product — I would like more information.', 'product'),
  ('product.whatsapp_intent', 'ar', 'المنتج - أرغب في الحصول على مزيد من المعلومات.', 'product'),
  ('product.whatsapp_intent', 'ru', 'товара — хочу получить больше информации.', 'product'),
  ('product.description_h2', 'tr', 'Ürün Açıklaması', 'product'),
  ('product.description_h2', 'en', 'Product Description', 'product'),
  ('product.description_h2', 'ar', 'وصف المنتج', 'product'),
  ('product.description_h2', 'ru', 'Описание продукта', 'product');

-- v1.0.62 — Yeni key'ler (calc/mailorder/loyalty/city/export)
INSERT IGNORE INTO tm_translations (`key`, lang, value, context) VALUES
  ('calc.engine_title', 'tr', 'Ağırlık Hesaplama Motoru', 'calc'),
  ('calc.engine_title', 'en', 'Weight Calculator Engine', 'calc'),
  ('calc.engine_title', 'ar', 'محرك حساب الوزن', 'calc'),
  ('calc.engine_title', 'ru', 'Калькулятор веса', 'calc'),
  ('calc.meta_desc', 'tr', 'Demir-çelik ağırlık hesaplama. Görsel diyagramlı, canlı hesaplamalı, çoklu kalem listeli profesyonel hesap motoru.', 'calc'),
  ('calc.meta_desc', 'en', 'Steel weight calculator. Professional engine with visual diagrams, live calculation and multi-item list.', 'calc'),
  ('calc.meta_desc', 'ar', 'حاسبة وزن الفولاذ. محرك احترافي مع رسوم بيانية مرئية وحساب مباشر وقائمة متعددة العناصر.', 'calc'),
  ('calc.meta_desc', 'ru', 'Калькулятор веса металла. Профессиональный движок с визуальными диаграммами, расчётом в реальном времени и списком позиций.', 'calc'),
  ('calc.product_type', 'tr', 'Ürün Tipi', 'calc'),
  ('calc.product_type', 'en', 'Product Type', 'calc'),
  ('calc.product_type', 'ar', 'نوع المنتج', 'calc'),
  ('calc.product_type', 'ru', 'Тип изделия', 'calc'),
  ('calc.material', 'tr', 'Malzeme', 'calc'),
  ('calc.material', 'en', 'Material', 'calc'),
  ('calc.material', 'ar', 'المادة', 'calc'),
  ('calc.material', 'ru', 'Материал', 'calc'),
  ('calc.preset_size', 'tr', 'Hazır Ölçü', 'calc'),
  ('calc.preset_size', 'en', 'Preset Sizes', 'calc'),
  ('calc.preset_size', 'ar', 'مقاسات جاهزة', 'calc'),
  ('calc.preset_size', 'ru', 'Готовые размеры', 'calc'),
  ('calc.step1_title', 'tr', 'Ürün Tipi Seçin', 'calc'),
  ('calc.step1_title', 'en', 'Select Product Type', 'calc'),
  ('calc.step1_title', 'ar', 'اختر نوع المنتج', 'calc'),
  ('calc.step1_title', 'ru', 'Выберите тип изделия', 'calc'),
  ('calc.step1_subtitle', 'tr', '14 farklı demir-çelik ürün grubu', 'calc'),
  ('calc.step1_subtitle', 'en', '14 different steel product groups', 'calc'),
  ('calc.step1_subtitle', 'ar', '14 مجموعة منتجات فولاذية مختلفة', 'calc'),
  ('calc.step1_subtitle', 'ru', '14 различных групп металлопродукции', 'calc'),
  ('calc.mat_steel', 'tr', 'Çelik', 'calc'),
  ('calc.mat_steel', 'en', 'Steel', 'calc'),
  ('calc.mat_steel', 'ar', 'فولاذ', 'calc'),
  ('calc.mat_steel', 'ru', 'Сталь', 'calc'),
  ('calc.mat_stainless', 'tr', 'Paslanmaz', 'calc'),
  ('calc.mat_stainless', 'en', 'Stainless', 'calc'),
  ('calc.mat_stainless', 'ar', 'مقاوم للصدأ', 'calc'),
  ('calc.mat_stainless', 'ru', 'Нержавеющая', 'calc'),
  ('calc.mat_aluminum', 'tr', 'Alüminyum', 'calc'),
  ('calc.mat_aluminum', 'en', 'Aluminum', 'calc'),
  ('calc.mat_aluminum', 'ar', 'ألمنيوم', 'calc'),
  ('calc.mat_aluminum', 'ru', 'Алюминий', 'calc'),
  ('calc.mat_copper', 'tr', 'Bakır', 'calc'),
  ('calc.mat_copper', 'en', 'Copper', 'calc'),
  ('calc.mat_copper', 'ar', 'نحاس', 'calc'),
  ('calc.mat_copper', 'ru', 'Медь', 'calc'),
  ('calc.mat_brass', 'tr', 'Pirinç', 'calc'),
  ('calc.mat_brass', 'en', 'Brass', 'calc'),
  ('calc.mat_brass', 'ar', 'نحاس أصفر', 'calc'),
  ('calc.mat_brass', 'ru', 'Латунь', 'calc'),
  ('calc.mat_bronze', 'tr', 'Bronz', 'calc'),
  ('calc.mat_bronze', 'en', 'Bronze', 'calc'),
  ('calc.mat_bronze', 'ar', 'برونز', 'calc'),
  ('calc.mat_bronze', 'ru', 'Бронза', 'calc'),
  ('calc.mat_custom', 'tr', 'Özel', 'calc'),
  ('calc.mat_custom', 'en', 'Custom', 'calc'),
  ('calc.mat_custom', 'ar', 'مخصص', 'calc'),
  ('calc.mat_custom', 'ru', 'Особый', 'calc'),
  ('calc.add_to_list', 'tr', 'Listeye Ekle', 'calc'),
  ('calc.add_to_list', 'en', 'Add to List', 'calc'),
  ('calc.add_to_list', 'ar', 'أضف إلى القائمة', 'calc'),
  ('calc.add_to_list', 'ru', 'Добавить в список', 'calc'),
  ('calc.material_comparison', 'tr', 'Malzeme Karşılaştırma', 'calc'),
  ('calc.material_comparison', 'en', 'Material Comparison', 'calc'),
  ('calc.material_comparison', 'ar', 'مقارنة المواد', 'calc'),
  ('calc.material_comparison', 'ru', 'Сравнение материалов', 'calc'),
  ('calc.compare_subtitle', 'tr', 'Aynı ölçü farklı malzemelerde nasıl ağırlık verir?', 'calc'),
  ('calc.compare_subtitle', 'en', 'How does the same size weigh in different materials?', 'calc'),
  ('calc.compare_subtitle', 'ar', 'كيف يكون وزن نفس المقاس في مواد مختلفة؟', 'calc'),
  ('calc.compare_subtitle', 'ru', 'Каков вес одного размера в разных материалах?', 'calc'),
  ('calc.kg_price', 'tr', 'kg fiyatı', 'calc'),
  ('calc.kg_price', 'en', 'kg price', 'calc'),
  ('calc.kg_price', 'ar', 'سعر الكيلوجرام', 'calc'),
  ('calc.kg_price', 'ru', 'цена за кг', 'calc'),
  ('calc.compare', 'tr', 'Karşılaştır', 'calc'),
  ('calc.compare', 'en', 'Compare', 'calc'),
  ('calc.compare', 'ar', 'قارن', 'calc'),
  ('calc.compare', 'ru', 'Сравнить', 'calc'),
  ('calc.cost_calc', 'tr', 'Maliyet Hesabı', 'calc'),
  ('calc.cost_calc', 'en', 'Cost Calculation', 'calc'),
  ('calc.cost_calc', 'ar', 'حساب التكلفة', 'calc'),
  ('calc.cost_calc', 'ru', 'Расчёт стоимости', 'calc'),
  ('calc.reset', 'tr', 'Sıfırla', 'calc'),
  ('calc.reset', 'en', 'Reset', 'calc'),
  ('calc.reset', 'ar', 'إعادة تعيين', 'calc'),
  ('calc.reset', 'ru', 'Сброс', 'calc'),
  ('calc.calc_list', 'tr', 'Hesaplama Listesi', 'calc'),
  ('calc.calc_list', 'en', 'Calculation List', 'calc'),
  ('calc.calc_list', 'ar', 'قائمة الحسابات', 'calc'),
  ('calc.calc_list', 'ru', 'Список расчётов', 'calc'),
  ('calc.clear_list', 'tr', 'Listeyi Temizle', 'calc'),
  ('calc.clear_list', 'en', 'Clear List', 'calc'),
  ('calc.clear_list', 'ar', 'مسح القائمة', 'calc'),
  ('calc.clear_list', 'ru', 'Очистить список', 'calc'),
  ('mailorder.title_long', 'tr', 'Mail Order Talimatı', 'mailorder'),
  ('mailorder.title_long', 'en', 'Mail Order Instructions', 'mailorder'),
  ('mailorder.title_long', 'ar', 'تعليمات الطلب البريدي', 'mailorder'),
  ('mailorder.title_long', 'ru', 'Инструкции Mail Order', 'mailorder'),
  ('mailorder.meta_desc', 'tr', 'Tekcan Metal Mail Order — kart bilgilerinizi telefonla teyit edip güvenli ödeme talimatı oluşturun.', 'mailorder'),
  ('mailorder.meta_desc', 'en', 'Tekcan Metal Mail Order — confirm your card details by phone and create a secure payment instruction.', 'mailorder'),
  ('mailorder.meta_desc', 'ar', 'طلب تكجان للحديد البريدي - قم بتأكيد تفاصيل بطاقتك عبر الهاتف وأنشئ تعليمات دفع آمنة.', 'mailorder'),
  ('mailorder.meta_desc', 'ru', 'Tekcan Metal Mail Order — подтвердите данные карты по телефону и создайте безопасную инструкцию оплаты.', 'mailorder'),
  ('mailorder.h1', 'tr', 'Mail Order <em>Talimatı</em>', 'mailorder'),
  ('mailorder.h1', 'en', 'Mail Order <em>Instructions</em>', 'mailorder'),
  ('mailorder.h1', 'ar', '<em>تعليمات</em> الطلب البريدي', 'mailorder'),
  ('mailorder.h1', 'ru', '<em>Инструкции</em> Mail Order', 'mailorder'),
  ('mailorder.create_btn', 'tr', 'Talimat Oluştur', 'mailorder'),
  ('mailorder.create_btn', 'en', 'Create Instruction', 'mailorder'),
  ('mailorder.create_btn', 'ar', 'إنشاء التعليمات', 'mailorder'),
  ('mailorder.create_btn', 'ru', 'Создать инструкцию', 'mailorder'),
  ('loyalty.meta_desc', 'tr', 'Tekcan Metal Sadakat Programı — düzenli müşterilerimize özel indirim, öncelikli sevkiyat ve özel kampanyalar.', 'loyalty'),
  ('loyalty.meta_desc', 'en', 'Tekcan Metal Loyalty Program — special discounts, priority shipping and exclusive campaigns for regular customers.', 'loyalty'),
  ('loyalty.meta_desc', 'ar', 'برنامج ولاء تكجان للحديد - خصومات خاصة وشحن ذو أولوية وحملات حصرية للعملاء المنتظمين.', 'loyalty'),
  ('loyalty.meta_desc', 'ru', 'Программа лояльности Tekcan Metal — специальные скидки, приоритетная доставка и эксклюзивные акции для постоянных клиентов.', 'loyalty'),
  ('loyalty.h1', 'tr', 'Sadakat <em>Programı</em>', 'loyalty'),
  ('loyalty.h1', 'en', 'Loyalty <em>Program</em>', 'loyalty'),
  ('loyalty.h1', 'ar', 'برنامج <em>الولاء</em>', 'loyalty'),
  ('loyalty.h1', 'ru', 'Программа <em>лояльности</em>', 'loyalty'),
  ('loyalty.exclusive_h2', 'tr', 'Üyelerimize <em>Özel</em>', 'loyalty'),
  ('loyalty.exclusive_h2', 'en', '<em>Exclusive</em> for Members', 'loyalty'),
  ('loyalty.exclusive_h2', 'ar', '<em>حصري</em> للأعضاء', 'loyalty'),
  ('loyalty.exclusive_h2', 'ru', '<em>Эксклюзивно</em> для членов', 'loyalty'),
  ('loyalty.tiers_h2', 'tr', 'Üç Kademeli <em>Asaletli Avantaj</em>', 'loyalty'),
  ('loyalty.tiers_h2', 'en', 'Three-Tier <em>Noble Advantage</em>', 'loyalty'),
  ('loyalty.tiers_h2', 'ar', 'ثلاث مستويات من <em>المزايا النبيلة</em>', 'loyalty'),
  ('loyalty.tiers_h2', 'ru', 'Трёхуровневое <em>благородное преимущество</em>', 'loyalty'),
  ('loyalty.join_family_h2', 'tr', 'Aileye <em>Katılın</em>', 'loyalty'),
  ('loyalty.join_family_h2', 'en', '<em>Join</em> the Family', 'loyalty'),
  ('loyalty.join_family_h2', 'ar', '<em>انضم</em> إلى العائلة', 'loyalty'),
  ('loyalty.join_family_h2', 'ru', '<em>Присоединяйтесь</em> к семье', 'loyalty'),
  ('loyalty.apply_btn', 'tr', 'Üyelik Başvurusu Gönder', 'loyalty'),
  ('loyalty.apply_btn', 'en', 'Submit Membership Application', 'loyalty'),
  ('loyalty.apply_btn', 'ar', 'إرسال طلب العضوية', 'loyalty'),
  ('loyalty.apply_btn', 'ru', 'Отправить заявку на членство', 'loyalty'),
  ('city.iron_steel_supply', 'tr', 'Demir Çelik Tedarik', 'city'),
  ('city.iron_steel_supply', 'en', 'Iron Steel Supply', 'city'),
  ('city.iron_steel_supply', 'ar', 'توريد الحديد والصلب', 'city'),
  ('city.iron_steel_supply', 'ru', 'Поставка металла', 'city'),
  ('city.meta_for', 'tr', 'için demir, çelik, sac, boru, profil tedariği. Tekcan Metal', 'city'),
  ('city.meta_for', 'en', '— iron, steel, sheet, pipe, profile supply. Tekcan Metal', 'city'),
  ('city.meta_for', 'ar', '- توريد الحديد والصلب والألواح والأنابيب والمقاطع. تكجان للحديد', 'city'),
  ('city.meta_for', 'ru', '— поставка железа, стали, листов, труб, профилей. Tekcan Metal', 'city'),
  ('export.iron_steel_shipping', 'tr', 'Demir Çelik Sevkiyat', 'export'),
  ('export.iron_steel_shipping', 'en', 'Iron Steel Shipping', 'export'),
  ('export.iron_steel_shipping', 'ar', 'شحن الحديد والصلب', 'export'),
  ('export.iron_steel_shipping', 'ru', 'Поставка металлопродукции', 'export'),
  ('export.meta_for', 'tr', 'için demir-çelik ürün sevkiyat hattımız.', 'export'),
  ('export.meta_for', 'en', '— our steel product shipping line.', 'export'),
  ('export.meta_for', 'ar', '- خط الشحن لمنتجات الحديد والصلب.', 'export'),
  ('export.meta_for', 'ru', '— наша линия доставки металлопродукции.', 'export'),
  ('export.meta_certified', 'tr', 'Üretici sertifikalı, gümrük belgeleri dahil.', 'export'),
  ('export.meta_certified', 'en', 'Manufacturer-certified, customs documents included.', 'export'),
  ('export.meta_certified', 'ar', 'معتمد من الشركة المصنعة، مستندات الجمارك مشمولة.', 'export'),
  ('export.meta_certified', 'ru', 'Сертификат производителя, таможенные документы включены.', 'export');

-- v1.0.63 — DB İçerik Çevirileri (Meta + Partners + Banks + Blog Cats + FAQ)

-- 1) Sayfa meta alanları (KVKK + Çerez + Hakkımızda)

UPDATE tm_pages SET
  title_en = 'Personal Data Protection Notice',
  title_ar = 'إشعار حماية البيانات الشخصية',
  title_ru = 'Уведомление о защите персональных данных',
  subtitle_en = 'Information about your personal data processed during the steel supply process under Personal Data Protection Law No. 6698.',
  subtitle_ar = 'معلومات حول بياناتك الشخصية المعالجة خلال عملية توريد الحديد والصلب وفقاً لقانون حماية البيانات الشخصية رقم 6698.',
  subtitle_ru = 'Информация о ваших персональных данных, обрабатываемых в процессе поставки металлопродукции согласно Закону № 6698 о защите персональных данных.',
  meta_title_en = 'Privacy Notice | Tekcan Metal',
  meta_title_ar = 'إشعار الخصوصية | تكجان للحديد',
  meta_title_ru = 'Политика конфиденциальности | Tekcan Metal',
  meta_desc_en = 'Tekcan Metal privacy notice — detailed information about personal data processed in steel supply, shipping, invoicing and customer relations.',
  meta_desc_ar = 'إشعار خصوصية تكجان للحديد - معلومات مفصلة حول البيانات الشخصية المعالجة في توريد الحديد والصلب والشحن والفوترة وعلاقات العملاء.',
  meta_desc_ru = 'Политика конфиденциальности Tekcan Metal — подробная информация об обработке персональных данных в поставке металла, доставке, выставлении счетов и работе с клиентами.'
WHERE slug = 'kvkk';

UPDATE tm_pages SET
  title_en = 'Cookie Policy',
  title_ar = 'سياسة ملفات تعريف الارتباط',
  title_ru = 'Политика использования cookie',
  subtitle_en = 'Detailed information about cookies used on our website, their purposes and your control options.',
  subtitle_ar = 'معلومات مفصلة حول ملفات تعريف الارتباط المستخدمة على موقعنا الإلكتروني وأغراضها وخيارات التحكم الخاصة بك.',
  subtitle_ru = 'Подробная информация о cookie-файлах на нашем сайте, их назначении и вариантах управления.',
  meta_title_en = 'Cookie Policy | Tekcan Metal',
  meta_title_ar = 'سياسة ملفات تعريف الارتباط | تكجان للحديد',
  meta_title_ru = 'Политика cookie | Tekcan Metal',
  meta_desc_en = 'Tekcan Metal cookie policy — detailed information about essential, performance, functional and targeting cookies, retention periods and browser control settings.',
  meta_desc_ar = 'سياسة ملفات تعريف الارتباط لتكجان للحديد - معلومات مفصلة حول ملفات تعريف الارتباط الأساسية والأداء والوظيفية والاستهداف وفترات الاحتفاظ وإعدادات التحكم في المتصفح.',
  meta_desc_ru = 'Политика cookie Tekcan Metal — подробная информация об обязательных, производительных, функциональных и таргетинговых cookie, сроках хранения и настройках браузера.'
WHERE slug = 'cerez-politikasi';

UPDATE tm_pages SET
  title_en = 'About Us',
  title_ar = 'من نحن',
  title_ru = 'О нас',
  meta_title_en = 'About Us | Tekcan Metal',
  meta_title_ar = 'من نحن | تكجان للحديد',
  meta_title_ru = 'О нас | Tekcan Metal',
  meta_desc_en = 'Tekcan Metal — Konya-based steel supplier since 2005. Stock and fast shipping for sheet, pipe, profile and rolled products.',
  meta_desc_ar = 'تكجان للحديد - مورد فولاذ مقره قونية منذ عام 2005. مخزون وشحن سريع لمنتجات الألواح والأنابيب والمقاطع والمدرفلة.',
  meta_desc_ru = 'Tekcan Metal — поставщик металла из Конья с 2005 года. Склад и быстрая доставка листов, труб, профилей и проката.'
WHERE slug = 'hakkimizda';


-- 2) Çözüm ortakları (6 marka × 2 alan × 3 dil)

UPDATE tm_partners SET
  name_en = 'Borcelik',
  name_ar = 'بورجيليك',
  name_ru = 'Borcelik',
  description_en = 'Leading supplier of galvanized sheet manufacturing',
  description_ar = 'المورد الرائد لتصنيع الألواح المجلفنة',
  description_ru = 'Ведущий поставщик оцинкованного листа'
WHERE name = 'Borçelik';

UPDATE tm_partners SET
  name_en = 'Erdemir',
  name_ar = 'إرديمير',
  name_ru = 'Erdemir',
  description_en = 'Turkey''s established brand in flat steel production',
  description_ar = 'العلامة التجارية العريقة في تركيا لإنتاج الفولاذ المسطح',
  description_ru = 'Известный турецкий производитель плоской стали'
WHERE name = 'Erdemir';

UPDATE tm_partners SET
  name_en = 'Habas',
  name_ar = 'حاباش',
  name_ru = 'Habas',
  description_en = 'Steel and industrial gases production',
  description_ar = 'إنتاج الفولاذ والغازات الصناعية',
  description_ru = 'Производство стали и промышленных газов'
WHERE name = 'Habaş';

UPDATE tm_partners SET
  name_en = 'Tosyali Steel',
  name_ar = 'توسيالي للفولاذ',
  name_ru = 'Tosyali Steel',
  description_en = 'Global steel production and export',
  description_ar = 'إنتاج وتصدير الفولاذ العالمي',
  description_ru = 'Глобальное производство и экспорт стали'
WHERE name = 'Tosyalı Çelik';

UPDATE tm_partners SET
  name_en = 'Kardemir',
  name_ar = 'كاردمير',
  name_ru = 'Kardemir',
  description_en = 'Turkey''s first integrated iron-steel facility',
  description_ar = 'أول منشأة متكاملة للحديد والصلب في تركيا',
  description_ru = 'Первый интегрированный металлургический завод Турции'
WHERE name = 'Kardemir';

UPDATE tm_partners SET
  name_en = 'Icdas',
  name_ar = 'إجداش',
  name_ru = 'Icdas',
  description_en = 'Wide range of steel product manufacturing',
  description_ar = 'تصنيع مجموعة واسعة من منتجات الفولاذ',
  description_ru = 'Производство широкого ассортимента стальной продукции'
WHERE name = 'İçdaş';


-- 3) Bankalar (4 banka × 2 alan × 3 dil)

UPDATE tm_banks SET
  bank_name_en = 'Ziraat Bank',
  bank_name_ar = 'بنك الزراعة',
  bank_name_ru = 'Ziraat Bank',
  branch_en = 'Konya / Karatay Branch',
  branch_ar = 'فرع قونية / كاراتاي',
  branch_ru = 'Филиал Конья / Каратай'
WHERE bank_name = 'Ziraat Bankası';

UPDATE tm_banks SET
  bank_name_en = 'Isbank',
  bank_name_ar = 'إيش بنك',
  bank_name_ru = 'Isbank',
  branch_en = 'Konya Industrial Branch',
  branch_ar = 'فرع قونية الصناعي',
  branch_ru = 'Филиал Конья Промышленный'
WHERE bank_name = 'İş Bankası';

UPDATE tm_banks SET
  bank_name_en = 'Halkbank',
  bank_name_ar = 'هالك بنك',
  bank_name_ru = 'Halkbank',
  branch_en = 'Konya Trade Branch',
  branch_ar = 'فرع قونية التجاري',
  branch_ru = 'Филиал Конья Торговый'
WHERE bank_name = 'Halkbank';

UPDATE tm_banks SET
  bank_name_en = 'Garanti BBVA',
  bank_name_ar = 'غارانتي BBVA',
  bank_name_ru = 'Garanti BBVA',
  branch_en = 'Konya Branch',
  branch_ar = 'فرع قونية',
  branch_ru = 'Филиал Конья'
WHERE bank_name = 'Garanti BBVA';


-- 4) Blog kategorileri

UPDATE tm_blog_categories SET
  name_en = 'Industry News',
  name_ar = 'أخبار الصناعة',
  name_ru = 'Новости отрасли',
  description_en = 'Steel industry and market developments',
  description_ar = 'تطورات صناعة الفولاذ والسوق',
  description_ru = 'Развитие металлургии и рынка'
WHERE slug = 'sektor-haberleri';

UPDATE tm_blog_categories SET
  name_en = 'Technical Information',
  name_ar = 'معلومات تقنية',
  name_ru = 'Техническая информация',
  description_en = 'Technical articles about our products',
  description_ar = 'مقالات تقنية حول منتجاتنا',
  description_ru = 'Технические статьи о нашей продукции'
WHERE slug = 'teknik-bilgiler';


-- 5) SSS soruları (6 soru × 2 alan × 3 dil)

UPDATE tm_faq SET
  question_en = 'How does the order process work?',
  question_ar = 'كيف تعمل عملية الطلب؟',
  question_ru = 'Как работает процесс заказа?',
  answer_en = 'You can submit your request via the contact form on our website, by phone or WhatsApp. After order confirmation, the product is prepared and shipping is scheduled the same day or the next day.',
  answer_ar = 'يمكنك تقديم طلبك عبر نموذج الاتصال على موقعنا الإلكتروني أو عبر الهاتف أو واتساب. بعد تأكيد الطلب، يتم تجهيز المنتج وجدولة الشحن في نفس اليوم أو اليوم التالي.',
  answer_ru = 'Вы можете оставить заявку через контактную форму на сайте, по телефону или WhatsApp. После подтверждения заказа продукт готовится и отгрузка планируется в тот же или на следующий день.'
WHERE category = 'siparis';

UPDATE tm_faq SET
  question_en = 'Do you ship outside Konya?',
  question_ar = 'هل تقومون بالشحن خارج قونية؟',
  question_ru = 'Доставляете ли вы за пределы Коньи?',
  answer_en = 'Yes. We provide shipping throughout Turkey with our contracted transportation companies. You can contact our sales representative for shipping times by city.',
  answer_ar = 'نعم. نقدم خدمات الشحن في جميع أنحاء تركيا من خلال شركات النقل المتعاقدة معنا. يمكنك التواصل مع ممثل المبيعات لمعرفة أوقات الشحن حسب المدينة.',
  answer_ru = 'Да. Мы доставляем по всей Турции через наших партнёров-перевозчиков. По срокам доставки в конкретные города свяжитесь с нашим менеджером.'
WHERE category = 'sevkiyat';

UPDATE tm_faq SET
  question_en = 'What payment methods are accepted?',
  question_ar = 'ما هي طرق الدفع المقبولة؟',
  question_ru = 'Какие способы оплаты принимаются?',
  answer_en = 'We accept bank transfer/EFT, corporate current account, and mail order credit card payments. Acceptance of checks/promissory notes varies by current account relationship.',
  answer_ar = 'نقبل التحويل البنكي/EFT والحساب الجاري للشركات ومدفوعات بطاقة الائتمان للطلبات البريدية. قبول الشيكات/السندات يختلف حسب علاقة الحساب الجاري.',
  answer_ru = 'Мы принимаем банковский перевод/EFT, корпоративный текущий счёт и платежи кредитной картой через mail order. Чеки/векселя — в зависимости от отношений по текущему счёту.'
WHERE category = 'odeme';

UPDATE tm_faq SET
  question_en = 'Is there a minimum order amount for laser cutting?',
  question_ar = 'هل هناك حد أدنى لمبلغ الطلب للقطع بالليزر؟',
  question_ru = 'Есть ли минимальная сумма заказа на лазерную резку?',
  answer_en = 'No minimum amount applies for standard projects. However, special pricing is applied for complex and short-term projects.',
  answer_ar = 'لا يوجد حد أدنى للمشاريع القياسية. ومع ذلك، يتم تطبيق تسعير خاص للمشاريع المعقدة وقصيرة الأجل.',
  answer_ru = 'Для стандартных проектов минимальная сумма не применяется. Для сложных и срочных проектов применяется специальное ценообразование.'
WHERE category = 'kesim';

UPDATE tm_faq SET
  question_en = 'Can I order products that are out of stock?',
  question_ar = 'هل يمكنني طلب منتجات غير متوفرة في المخزون؟',
  question_ru = 'Могу ли я заказать товары, которых нет в наличии?',
  answer_en = 'Yes. We can supply products not in our stock within 24-72 hours. Please send your detailed request via the contact form.',
  answer_ar = 'نعم. يمكننا توريد المنتجات غير الموجودة في مخزوننا خلال 24-72 ساعة. يرجى إرسال طلبك التفصيلي عبر نموذج الاتصال.',
  answer_ru = 'Да. Товары, которых нет на нашем складе, мы можем поставить за 24-72 часа. Отправьте подробный запрос через контактную форму.'
WHERE category = 'stok';

UPDATE tm_faq SET
  question_en = 'Do you issue e-invoices?',
  question_ar = 'هل تصدرون فواتير إلكترونية؟',
  question_ru = 'Выставляете ли вы электронные счета-фактуры?',
  answer_en = 'Yes. All our invoices are issued through the GIB-integrated e-invoice system.',
  answer_ar = 'نعم. تُصدر جميع فواتيرنا من خلال نظام الفاتورة الإلكترونية المتكامل مع GIB.',
  answer_ru = 'Да. Все наши счета выставляются через интегрированную с GIB систему электронных счетов.'
WHERE category = 'fatura';

-- v1.0.64 — KVKK content 3 dilde (~5000 kelime × 3 dil = ~15.000 kelime)
UPDATE tm_pages SET
  content_en = '<p>As Tekcan Metal Sanayi ve Ticaret Ltd. Şti., we attach the utmost importance to the protection of your personal data processed in steel supply, workshop cutting services, shipping, invoicing and customer relations. This Privacy Notice has been prepared to fulfill our obligations under Personal Data Protection Law No. 6698 (<em>"PDPL"</em>) and related legislation.</p>

<h2>1. Identity of the Data Controller</h2>
<p>Pursuant to Law No. 6698, your personal data may be processed by <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong> (hereinafter referred to as the <em>"Company"</em>) as the data controller, within the scope explained below.</p>

<h4>Company Contact Information</h4>
<ul>
<li><strong>Title:</strong> Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</li>
<li><strong>Address:</strong> Fevzicakmak Dist. Gulistan Str. Atiker 3, Block 2 No:33 AS — Karatay/KONYA</li>
<li><strong>Phone:</strong> 0 332 342 24 52</li>
<li><strong>E-mail:</strong> info@tekcanmetal.com</li>
<li><strong>Web:</strong> www.tekcanmetal.com</li>
</ul>

<h2>2. Purposes of Processing Personal Data</h2>
<p>Due to the nature of our supply activities in the steel sector, we process your personal data for the following purposes:</p>

<h3>A) Supply and Sales Processes</h3>
<ul>
<li><strong>Preparing price quotes</strong> for sheet, pipe, profile, rolled and other steel products</li>
<li><strong>Receiving and confirming orders</strong>, technical coordination regarding product specifications</li>
<li>Issuing <strong>proforma invoices and contracts</strong></li>
<li><strong>Issuing quality documents</strong> such as manufacturer certificates and certificates of origin</li>
<li>Conducting <strong>custom-size cutting, laser cutting and oxygen cutting</strong> workshop operations specific to the order</li>
</ul>

<h3>B) Shipping and Logistics</h3>
<ul>
<li>Using <strong>delivery address, recipient information and contact details</strong> in our domestic shipping network covering all 81 provinces</li>
<li>Coordinating <strong>cargo</strong> with contracted transportation companies</li>
<li><strong>Issuing customs documents</strong> for international (Iraq, Syria, Azerbaijan, Turkmenistan) export shipments and transit logistics coordination</li>
<li>Issuing dispatch notes and delivery receipts</li>
</ul>

<h3>C) Invoicing and Collection</h3>
<ul>
<li>Issuing e-Invoice, e-Archive Invoice or paper invoices</li>
<li>Tracking <strong>bank transfer, EFT, credit card, post-dated check and term open account transactions</strong></li>
<li>Fulfilling <strong>legal obligations to financial advisors and official authorities</strong></li>
<li>Initiating legal proceedings in case of payment delays</li>
</ul>

<h3>D) Customer Relations and Marketing</h3>
<ul>
<li><strong>Managing membership records</strong> within the customer loyalty program</li>
<li><strong>Responding to price inquiries</strong> through contact forms, WhatsApp and phone calls</li>
<li>If you give explicit consent, sending you <strong>new product, campaign and industry announcements</strong></li>
<li><strong>Improving the user experience</strong> on the website (our cookie policy is published separately)</li>
</ul>

<h3>E) Legal and Regulatory Obligations</h3>
<ul>
<li><strong>Books and document retention obligations</strong> under the Tax Procedure Law, Turkish Commercial Code and other commercial legislation</li>
<li><strong>Responding to requests from</strong> the Financial Crimes Investigation Board (MASAK), customs administration and other <strong>authorized public institutions</strong></li>
<li><strong>Submitting evidence and documents</strong> in legal audits, investigations and court proceedings</li>
</ul>

<h2>3. Categories of Personal Data Processed</h2>
<p>For the purposes stated above, the following categories of personal data are processed:</p>

<table>
<thead><tr><th>Data Category</th><th>Sample Data</th></tr></thead>
<tbody>
<tr><td><strong>Identity Information</strong></td><td>Name, surname, ID number, tax ID, tax office, title</td></tr>
<tr><td><strong>Contact Information</strong></td><td>Phone, mobile, e-mail, fax, address, country/city/district</td></tr>
<tr><td><strong>Customer Transaction</strong></td><td>Order history, quote records, invoice information, payment details, shipping addresses</td></tr>
<tr><td><strong>Financial Data</strong></td><td>Bank account information (IBAN), payment plan, term account status, credit score (when required)</td></tr>
<tr><td><strong>Professional Information</strong></td><td>Company, position, sector (for B2B customers)</td></tr>
<tr><td><strong>Marketing Data</strong></td><td>Communication preferences, interests, click/visit records (with explicit consent)</td></tr>
<tr><td><strong>Legal Process</strong></td><td>Contracts, undertakings, dispute process records</td></tr>
<tr><td><strong>Transaction Security</strong></td><td>IP address, log records, cookies, user session information</td></tr>
</tbody>
</table>

<h2>4. Method of Collection and Legal Basis of Personal Data</h2>
<p>Your personal data is collected through the following methods:</p>
<ul>
<li><strong>Through the website:</strong> Contact form, mail order application, loyalty program registration form, quote request form, price calculation wizard</li>
<li><strong>Verbally and by phone:</strong> Phone conversations, WhatsApp messages, requests submitted to sales consultants</li>
<li><strong>Through written documents:</strong> Signed contracts, order forms, valuable papers, delivery receipts, dispatch notes</li>
<li><strong>Through physical visits:</strong> Business cards and product catalog forms provided during visits to our Konya headquarters and warehouse facility</li>
<li><strong>Through third parties:</strong> Contracted transportation companies, bank approvals, financial advisors, customs brokers</li>
<li><strong>Public sources:</strong> Open access systems such as GIB Tax Plate Inquiry, MERSIS, Trade Registry</li>
</ul>

<h4>Legal Bases (PDPL Articles 5 and 6)</h4>
<p>Your personal data is processed based on one or more of the following legal grounds:</p>
<ol>
<li><strong>Explicit consent</strong> (PDPL art. 5/1) — For consent-based processing such as marketing communications</li>
<li><strong>Establishment or performance of a contract</strong> (PDPL art. 5/2-c) — For order, shipping, invoicing processes</li>
<li><strong>Fulfillment of a legal obligation</strong> (PDPL art. 5/2-ç) — Tax, MASAK, commercial books</li>
<li><strong>Legitimate interest</strong> (PDPL art. 5/2-f) — Collection tracking, fraud prevention, customer relations management</li>
<li><strong>Establishment, exercise or protection of a right</strong> (PDPL art. 5/2-e) — Legal dispute processes</li>
</ol>

<h2>5. Transfer of Personal Data</h2>
<p>Your processed personal data may be transferred to the following third parties, limited to the disclosed purposes:</p>

<h4>Domestic Transfers</h4>
<ul>
<li><strong>Contracted transportation companies</strong> — for shipping coordination</li>
<li><strong>Banks and financial institutions</strong> — for collection processes</li>
<li><strong>Financial advisors and accounting offices</strong> — for tax declaration and bookkeeping</li>
<li><strong>Public institutions</strong> — Tax authorities, GIB, MASAK, courts (within legal obligations)</li>
<li><strong>Solution partners</strong> — Erdemir, Borcelik, Habas etc. (only when required for ordering and quality certificate processes)</li>
<li><strong>IT service providers</strong> — Server, e-mail, e-invoice integration providers</li>
</ul>

<h4>International Transfers</h4>
<ul>
<li><strong>Customs offices and freight forwarders abroad</strong> — Iraq, Syria, Azerbaijan, Turkmenistan customs procedures</li>
<li>Personal data is transferred only to the extent necessary in the relevant export country and is protected with appropriate security measures</li>
</ul>

<h2>6. Personal Data Retention Period</h2>
<p>Your personal data is retained for the following durations:</p>
<ul>
<li><strong>Tax and accounting records:</strong> 10 years (in accordance with Tax Procedure Law)</li>
<li><strong>Commercial book records:</strong> 10 years (in accordance with Turkish Commercial Code)</li>
<li><strong>Customer transaction records:</strong> 10 years from the end of the customer relationship</li>
<li><strong>Marketing data:</strong> Until consent is withdrawn (at most 5 years)</li>
<li><strong>Website log records:</strong> 6 months (Law No. 5651)</li>
<li><strong>CV applications:</strong> 1 year (deleted if not employed)</li>
</ul>
<p>Personal data exceeding the legal retention period is deleted, destroyed or anonymized in accordance with our personal data destruction policy.</p>

<h2>7. Personal Data Security</h2>
<p>To ensure the security of your personal data, we implement the following technical and administrative measures:</p>

<h4>Technical Measures</h4>
<ul>
<li>SSL/TLS encryption (HTTPS) on all forms and login screens</li>
<li>Database servers protected with firewall and regular backups</li>
<li>Encrypted storage of passwords (bcrypt/Argon2 algorithm)</li>
<li>Up-to-date anti-virus and anti-malware software</li>
<li>Restricted access through multi-factor authentication (admin panel)</li>
<li>Regular security tests and vulnerability scanning</li>
</ul>

<h4>Administrative Measures</h4>
<ul>
<li>Confidentiality undertakings from employees</li>
<li>Periodic personal data protection training</li>
<li>Authorization matrix on the principle of access on a need-to-know basis</li>
<li>Personal data inventory and processing activity records</li>
<li>Data breach response plan and notification procedures</li>
</ul>

<h2>8. Rights of the Data Subject (PDPL Article 11)</h2>
<p>Pursuant to Article 11 of PDPL, as a data subject you have the following rights:</p>
<ol>
<li>Learning whether your personal data is processed</li>
<li>Requesting information regarding the processing if such processing has been done</li>
<li>Learning the purpose of personal data processing and whether they are used for their intended purpose</li>
<li>Knowing the third parties to whom personal data is transferred at home or abroad</li>
<li>Requesting correction of personal data in case of incomplete or incorrect processing</li>
<li>Requesting deletion or destruction of personal data within the framework of the conditions stipulated in Article 7 of the Law</li>
<li>Requesting that the operations performed pursuant to subparagraphs (e) and (f) be notified to third parties to whom the personal data has been transferred</li>
<li>Objecting to the emergence of an unfavorable result through the analysis of processed data exclusively through automated systems</li>
<li>Requesting compensation for damages in case of suffering damages due to unlawful processing of personal data</li>
</ol>

<h2>9. Application Methods and Conclusion</h2>
<p>You can use the rights listed above through the following methods:</p>

<h4>Written Application</h4>
<p>By delivering a wet-signed petition to the address <strong>Fevzicakmak Dist. Gulistan Str. Atiker 3, Block 2 No:33 AS — Karatay/KONYA</strong> in person or through a notary.</p>

<h4>Electronic Application</h4>
<ul>
<li><strong>Registered Electronic Mail (KEP):</strong> If you have a KEP address, by sending to our company KEP address</li>
<li><strong>Secure E-signed E-mail:</strong> By sending an e-signed e-mail to <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a></li>
<li><strong>Secure Authentication:</strong> By logging into our system with a previously authenticated user account</li>
</ul>

<h4>Application Content</h4>
<p>The following information must be included in your application:</p>
<ul>
<li>Name, surname, signature (in written application)</li>
<li>For Turkish citizens: Turkish ID number; for foreigners: nationality, passport number</li>
<li>Notification address, e-mail or fax</li>
<li>Subject of the request (clearly stated)</li>
<li>Information and documents subject to the request, if any</li>
</ul>

<h4>Conclusion Period</h4>
<p>Your applications are concluded <strong>free of charge within 30 days at the latest</strong>. However, if the procedure requires additional cost, the fee determined by the Personal Data Protection Board may be charged.</p>

<h2>10. Data Breach Notifications</h2>
<p>In the event that your personal data is unlawfully obtained by others, we notify you and the Personal Data Protection Board within <strong>72 hours at the latest</strong>. The notification includes the nature of the breach, affected data, measures taken and contact information.</p>

<h2>11. Changes to the Privacy Notice</h2>
<p>This Privacy Notice may be updated due to changes in legislation, regulatory authority decisions or our company practices. The current version is published on our website at <a href="https://www.tekcanmetal.com/sayfa.php?slug=kvkk">www.tekcanmetal.com/sayfa.php?slug=kvkk</a>.</p>

<p><em>Last update: January 2026</em></p>
',
  content_ar = '<p>بصفتنا تكجان ميتال للصناعة والتجارة المحدودة، نولي أهمية قصوى لحماية بياناتك الشخصية المعالجة في عمليات توريد الحديد والصلب وخدمات القطع في الورشة والشحن والفوترة وعلاقات العملاء. تم إعداد إشعار الخصوصية هذا للوفاء بالتزاماتنا بموجب قانون حماية البيانات الشخصية رقم 6698 (<em>"KVKK"</em>) والتشريعات ذات الصلة.</p>

<h2>1. هوية المتحكم بالبيانات</h2>
<p>وفقاً للقانون رقم 6698، يجوز معالجة بياناتك الشخصية من قبل <strong>تكجان ميتال للصناعة والتجارة المحدودة</strong> (المشار إليها فيما يلي بـ <em>"الشركة"</em>) بصفتها متحكماً بالبيانات، ضمن النطاق الموضح أدناه.</p>

<h4>معلومات الاتصال بالشركة</h4>
<ul>
<li><strong>الاسم:</strong> تكجان ميتال للصناعة والتجارة المحدودة</li>
<li><strong>العنوان:</strong> حي فوزي تشاكماك، شارع غولستان، أتيكر 3، البلوك 2 رقم 33 AS — كاراتاي/قونية</li>
<li><strong>الهاتف:</strong> 0 332 342 24 52</li>
<li><strong>البريد الإلكتروني:</strong> info@tekcanmetal.com</li>
<li><strong>الموقع:</strong> www.tekcanmetal.com</li>
</ul>

<h2>2. أغراض معالجة البيانات الشخصية</h2>
<p>نظراً لطبيعة نشاطنا في توريد الحديد والصلب، نقوم بمعالجة بياناتك الشخصية للأغراض التالية:</p>

<h3>أ) عمليات التوريد والمبيعات</h3>
<ul>
<li><strong>إعداد عروض الأسعار</strong> للألواح والأنابيب والمقاطع والمنتجات المدرفلة وغيرها من منتجات الحديد والصلب</li>
<li><strong>استلام الطلبات وتأكيدها</strong>، التنسيق الفني فيما يتعلق بمواصفات المنتجات</li>
<li>إصدار <strong>الفواتير الأولية والعقود</strong></li>
<li><strong>إصدار وثائق الجودة</strong> مثل شهادات الشركة المصنعة وشهادات المنشأ</li>
<li>إجراء عمليات الورشة <strong>للقطع بمقاسات خاصة والقطع بالليزر والقطع بالأكسجين</strong> الخاصة بالطلب</li>
</ul>

<h3>ب) الشحن والخدمات اللوجستية</h3>
<ul>
<li>استخدام <strong>عنوان التسليم ومعلومات المستلم وتفاصيل الاتصال</strong> في شبكة الشحن المحلية التي تغطي جميع المحافظات الـ81</li>
<li>تنسيق <strong>الشحنات</strong> مع شركات النقل المتعاقدة</li>
<li><strong>إصدار الوثائق الجمركية</strong> للشحنات الدولية للتصدير (العراق، سوريا، أذربيجان، تركمانستان) وتنسيق الخدمات اللوجستية للعبور</li>
<li>إصدار سندات الشحن وإيصالات التسليم</li>
</ul>

<h3>ج) الفوترة والتحصيل</h3>
<ul>
<li>إصدار الفاتورة الإلكترونية أو فاتورة الأرشيف الإلكترونية أو الفواتير الورقية</li>
<li>متابعة <strong>التحويل البنكي و EFT وبطاقة الائتمان والشيكات الآجلة ومعاملات الحساب الجاري</strong></li>
<li>الوفاء <strong>بالالتزامات القانونية تجاه المستشارين الماليين والسلطات الرسمية</strong></li>
<li>بدء الإجراءات القانونية في حالة تأخر السداد</li>
</ul>

<h3>د) علاقات العملاء والتسويق</h3>
<ul>
<li><strong>إدارة سجلات العضوية</strong> ضمن برنامج ولاء العملاء</li>
<li><strong>الرد على استفسارات الأسعار</strong> عبر نماذج الاتصال وواتساب والمكالمات الهاتفية</li>
<li>في حالة موافقتك الصريحة، إرسال <strong>إعلانات المنتجات الجديدة والحملات والإعلانات الصناعية</strong> إليك</li>
<li><strong>تحسين تجربة المستخدم</strong> على الموقع الإلكتروني (سياسة ملفات تعريف الارتباط منشورة بشكل منفصل)</li>
</ul>

<h3>هـ) الالتزامات القانونية والتنظيمية</h3>
<ul>
<li><strong>التزامات الاحتفاظ بالدفاتر والوثائق</strong> بموجب قانون الإجراءات الضريبية والقانون التجاري التركي والتشريعات التجارية الأخرى</li>
<li><strong>الرد على طلبات</strong> هيئة التحقيق في الجرائم المالية (MASAK) وإدارة الجمارك وغيرها من <strong>المؤسسات العامة المعتمدة</strong></li>
<li><strong>تقديم الأدلة والوثائق</strong> في عمليات التدقيق القانونية والتحقيقات والإجراءات القضائية</li>
</ul>

<h2>3. فئات البيانات الشخصية المعالجة</h2>
<p>للأغراض المذكورة أعلاه، يتم معالجة فئات البيانات الشخصية التالية:</p>

<table>
<thead><tr><th>فئة البيانات</th><th>أمثلة على البيانات</th></tr></thead>
<tbody>
<tr><td><strong>معلومات الهوية</strong></td><td>الاسم، اللقب، رقم الهوية، رقم التعريف الضريبي، الدائرة الضريبية، اللقب الوظيفي</td></tr>
<tr><td><strong>معلومات الاتصال</strong></td><td>الهاتف، الجوال، البريد الإلكتروني، الفاكس، العنوان، الدولة/المدينة/المنطقة</td></tr>
<tr><td><strong>معاملات العميل</strong></td><td>تاريخ الطلبات، سجلات عروض الأسعار، معلومات الفواتير، تفاصيل الدفع، عناوين الشحن</td></tr>
<tr><td><strong>البيانات المالية</strong></td><td>معلومات الحساب البنكي (IBAN)، خطة الدفع، حالة الحساب الآجل، التصنيف الائتماني (عند الحاجة)</td></tr>
<tr><td><strong>المعلومات المهنية</strong></td><td>الشركة، المنصب، القطاع (لعملاء B2B)</td></tr>
<tr><td><strong>بيانات التسويق</strong></td><td>تفضيلات الاتصال، الاهتمامات، سجلات النقر/الزيارة (بموافقة صريحة)</td></tr>
<tr><td><strong>الإجراءات القانونية</strong></td><td>العقود، التعهدات، سجلات إجراءات النزاع</td></tr>
<tr><td><strong>أمن المعاملات</strong></td><td>عنوان IP، سجلات السجل، ملفات تعريف الارتباط، معلومات جلسة المستخدم</td></tr>
</tbody>
</table>

<h2>4. طريقة جمع البيانات الشخصية وأساسها القانوني</h2>
<p>يتم جمع بياناتك الشخصية من خلال الطرق التالية:</p>
<ul>
<li><strong>عبر الموقع الإلكتروني:</strong> نموذج الاتصال، طلب البريد المباشر، نموذج التسجيل في برنامج الولاء، نموذج طلب عرض السعر، معالج حساب الأسعار</li>
<li><strong>شفهياً وعبر الهاتف:</strong> المحادثات الهاتفية، رسائل واتساب، الطلبات المقدمة لمستشاري المبيعات</li>
<li><strong>من خلال الوثائق المكتوبة:</strong> العقود الموقعة، نماذج الطلبات، الأوراق ذات القيمة، إيصالات التسليم، سندات الشحن</li>
<li><strong>من خلال الزيارات الفعلية:</strong> بطاقات العمل ونماذج كتالوج المنتجات المقدمة أثناء الزيارات لمقرنا الرئيسي ومستودعنا في قونية</li>
<li><strong>من خلال أطراف ثالثة:</strong> شركات النقل المتعاقدة، موافقات البنوك، المستشارون الماليون، وكلاء الجمارك</li>
<li><strong>المصادر العامة:</strong> أنظمة الوصول المفتوحة مثل GIB لاستعلام لوحة الضرائب و MERSIS وسجل التجارة</li>
</ul>

<h4>الأسس القانونية (مادة 5 و 6 من KVKK)</h4>
<p>تتم معالجة بياناتك الشخصية بناءً على واحد أو أكثر من الأسس القانونية التالية:</p>
<ol>
<li><strong>الموافقة الصريحة</strong> (مادة 5/1 من KVKK) — للمعالجات القائمة على الموافقة مثل الاتصالات التسويقية</li>
<li><strong>إنشاء أو تنفيذ العقد</strong> (مادة 5/2-ج من KVKK) — لعمليات الطلب والشحن والفوترة</li>
<li><strong>الوفاء بالتزام قانوني</strong> (مادة 5/2-ح من KVKK) — الضرائب، MASAK، الدفاتر التجارية</li>
<li><strong>المصلحة المشروعة</strong> (مادة 5/2-و من KVKK) — متابعة التحصيل، منع الاحتيال، إدارة علاقات العملاء</li>
<li><strong>إنشاء أو ممارسة أو حماية حق</strong> (مادة 5/2-هـ من KVKK) — إجراءات النزاع القانوني</li>
</ol>

<h2>5. نقل البيانات الشخصية</h2>
<p>قد يتم نقل بياناتك الشخصية المعالجة إلى الأطراف الثالثة التالية، مع الاقتصار على الأغراض المعلنة:</p>

<h4>التحويلات المحلية</h4>
<ul>
<li><strong>شركات النقل المتعاقدة</strong> — لتنسيق الشحن</li>
<li><strong>البنوك والمؤسسات المالية</strong> — لعمليات التحصيل</li>
<li><strong>المستشارون الماليون ومكاتب المحاسبة</strong> — للإقرار الضريبي ومسك الدفاتر</li>
<li><strong>المؤسسات العامة</strong> — السلطات الضريبية، GIB، MASAK، المحاكم (ضمن الالتزامات القانونية)</li>
<li><strong>شركاء الحلول</strong> — إرديمير، بورجيليك، حاباش وغيرها (فقط عند الحاجة لطلب وشهادات الجودة)</li>
<li><strong>مقدمو خدمات تكنولوجيا المعلومات</strong> — مقدمو خدمات الخادم والبريد الإلكتروني وتكامل الفاتورة الإلكترونية</li>
</ul>

<h4>التحويلات الدولية</h4>
<ul>
<li><strong>المكاتب الجمركية ووكلاء الشحن في الخارج</strong> — إجراءات الجمارك في العراق وسوريا وأذربيجان وتركمانستان</li>
<li>يتم نقل البيانات الشخصية فقط بالقدر اللازم في بلد التصدير المعني وتتم حمايتها بالتدابير الأمنية المناسبة</li>
</ul>

<h2>6. فترة الاحتفاظ بالبيانات الشخصية</h2>
<p>يتم الاحتفاظ ببياناتك الشخصية للفترات التالية:</p>
<ul>
<li><strong>السجلات الضريبية والمحاسبية:</strong> 10 سنوات (وفقاً لقانون الإجراءات الضريبية)</li>
<li><strong>سجلات الدفاتر التجارية:</strong> 10 سنوات (وفقاً للقانون التجاري التركي)</li>
<li><strong>سجلات معاملات العملاء:</strong> 10 سنوات من نهاية علاقة العميل</li>
<li><strong>بيانات التسويق:</strong> حتى سحب الموافقة (5 سنوات على الأكثر)</li>
<li><strong>سجلات الموقع الإلكتروني:</strong> 6 أشهر (القانون رقم 5651)</li>
<li><strong>طلبات السيرة الذاتية:</strong> سنة واحدة (تُحذف إذا لم يتم التوظيف)</li>
</ul>
<p>يتم حذف البيانات الشخصية التي تتجاوز فترة الاحتفاظ القانونية أو إتلافها أو إخفاء هويتها وفقاً لسياسة إتلاف البيانات الشخصية.</p>

<h2>7. أمن البيانات الشخصية</h2>
<p>لضمان أمن بياناتك الشخصية، نطبق الإجراءات التقنية والإدارية التالية:</p>

<h4>الإجراءات التقنية</h4>
<ul>
<li>تشفير SSL/TLS (HTTPS) على جميع النماذج وشاشات تسجيل الدخول</li>
<li>خوادم قواعد البيانات محمية بجدار حماية ونسخ احتياطية منتظمة</li>
<li>تخزين كلمات المرور بشكل مشفر (خوارزمية bcrypt/Argon2)</li>
<li>برامج محدثة لمكافحة الفيروسات والبرامج الضارة</li>
<li>الوصول المقيد عبر المصادقة متعددة العوامل (لوحة الإدارة)</li>
<li>اختبارات أمنية منتظمة وفحص الثغرات</li>
</ul>

<h4>الإجراءات الإدارية</h4>
<ul>
<li>تعهدات السرية من الموظفين</li>
<li>تدريب دوري على حماية البيانات الشخصية</li>
<li>مصفوفة الترخيص على مبدأ الوصول حسب الحاجة للمعرفة</li>
<li>قائمة جرد البيانات الشخصية وسجلات أنشطة المعالجة</li>
<li>خطة الاستجابة لانتهاكات البيانات وإجراءات الإخطار</li>
</ul>

<h2>8. حقوق صاحب البيانات (مادة 11 من KVKK)</h2>
<p>وفقاً للمادة 11 من KVKK، بصفتك صاحب البيانات لديك الحقوق التالية:</p>
<ol>
<li>معرفة ما إذا كانت بياناتك الشخصية تتم معالجتها</li>
<li>طلب معلومات بشأن المعالجة إذا تمت هذه المعالجة</li>
<li>معرفة الغرض من معالجة البيانات الشخصية وما إذا كان يتم استخدامها لغرضها المقصود</li>
<li>معرفة الأطراف الثالثة التي يتم نقل البيانات الشخصية إليها في الداخل أو الخارج</li>
<li>طلب تصحيح البيانات الشخصية في حالة المعالجة غير المكتملة أو غير الصحيحة</li>
<li>طلب حذف البيانات الشخصية أو إتلافها ضمن إطار الشروط المنصوص عليها في المادة 7 من القانون</li>
<li>طلب إخطار الأطراف الثالثة التي تم نقل البيانات الشخصية إليها بالعمليات المنفذة وفقاً للفقرتين الفرعيتين (هـ) و(و)</li>
<li>الاعتراض على ظهور نتيجة غير ملائمة من خلال تحليل البيانات المعالجة حصراً عبر الأنظمة الآلية</li>
<li>طلب تعويض الأضرار في حالة تكبد أضرار بسبب المعالجة غير القانونية للبيانات الشخصية</li>
</ol>

<h2>9. طرق التقديم والإنجاز</h2>
<p>يمكنك استخدام الحقوق المذكورة أعلاه من خلال الطرق التالية:</p>

<h4>التقديم الكتابي</h4>
<p>عن طريق تسليم عريضة موقعة بخط اليد إلى عنوان <strong>حي فوزي تشاكماك، شارع غولستان، أتيكر 3، البلوك 2 رقم 33 AS — كاراتاي/قونية</strong> شخصياً أو عن طريق كاتب العدل.</p>

<h4>التقديم الإلكتروني</h4>
<ul>
<li><strong>البريد الإلكتروني المسجل (KEP):</strong> إذا كان لديك عنوان KEP، عن طريق الإرسال إلى عنوان KEP الخاص بشركتنا</li>
<li><strong>البريد الإلكتروني الموقع إلكترونياً:</strong> عن طريق إرسال بريد إلكتروني موقع إلكترونياً إلى <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a></li>
<li><strong>المصادقة الآمنة:</strong> عن طريق تسجيل الدخول إلى نظامنا بحساب مستخدم مصدق مسبقاً</li>
</ul>

<h4>محتوى الطلب</h4>
<p>يجب تضمين المعلومات التالية في طلبك:</p>
<ul>
<li>الاسم، اللقب، التوقيع (في الطلب الكتابي)</li>
<li>للمواطنين الأتراك: رقم الهوية التركية؛ للأجانب: الجنسية ورقم جواز السفر</li>
<li>عنوان الإخطار، البريد الإلكتروني أو الفاكس</li>
<li>موضوع الطلب (مذكور بوضوح)</li>
<li>المعلومات والوثائق الخاضعة للطلب، إن وجدت</li>
</ul>

<h4>فترة الإنجاز</h4>
<p>يتم إنجاز طلباتك <strong>مجاناً في غضون 30 يوماً كحد أقصى</strong>. ومع ذلك، إذا تطلبت الإجراءات تكلفة إضافية، فقد يتم تحصيل الرسوم التي يحددها مجلس حماية البيانات الشخصية.</p>

<h2>10. إخطارات انتهاك البيانات</h2>
<p>في حالة الحصول على بياناتك الشخصية بشكل غير قانوني من قبل آخرين، نخطرك ومجلس حماية البيانات الشخصية في غضون <strong>72 ساعة كحد أقصى</strong>. يتضمن الإخطار طبيعة الانتهاك والبيانات المتأثرة والإجراءات المتخذة ومعلومات الاتصال.</p>

<h2>11. التغييرات في إشعار الخصوصية</h2>
<p>قد يتم تحديث إشعار الخصوصية هذا بسبب التغييرات في التشريعات أو قرارات السلطة التنظيمية أو ممارسات شركتنا. يتم نشر النسخة الحالية على موقعنا الإلكتروني على <a href="https://www.tekcanmetal.com/sayfa.php?slug=kvkk">www.tekcanmetal.com/sayfa.php?slug=kvkk</a>.</p>

<p><em>آخر تحديث: يناير 2026</em></p>
',
  content_ru = '<p>Как Tekcan Metal Sanayi ve Ticaret Ltd. Şti., мы придаём первостепенное значение защите ваших персональных данных, обрабатываемых в процессах поставки металла, услуг резки в цеху, доставки, выставления счетов и работы с клиентами. Настоящее Уведомление о конфиденциальности подготовлено для выполнения наших обязательств в рамках Закона № 6698 о защите персональных данных (<em>"ЗоЗПД"</em>) и соответствующего законодательства.</p>

<h2>1. Личность контролёра данных</h2>
<p>В соответствии с Законом № 6698 ваши персональные данные могут обрабатываться компанией <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong> (далее именуемой <em>"Компания"</em>) в качестве контролёра данных в рамках, описанных ниже.</p>

<h4>Контактная информация компании</h4>
<ul>
<li><strong>Наименование:</strong> Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</li>
<li><strong>Адрес:</strong> Район Февзичакмак, ул. Гюлистан, Атикер 3, Блок 2 № 33 AS — Каратай/КОНЬЯ</li>
<li><strong>Телефон:</strong> 0 332 342 24 52</li>
<li><strong>Электронная почта:</strong> info@tekcanmetal.com</li>
<li><strong>Сайт:</strong> www.tekcanmetal.com</li>
</ul>

<h2>2. Цели обработки персональных данных</h2>
<p>В силу характера нашей деятельности по поставке металла мы обрабатываем ваши персональные данные в следующих целях:</p>

<h3>А) Процессы поставки и продаж</h3>
<ul>
<li><strong>Подготовка коммерческих предложений</strong> на листы, трубы, профили, прокат и другую металлопродукцию</li>
<li><strong>Приём и подтверждение заказов</strong>, техническая координация по характеристикам продукции</li>
<li>Оформление <strong>проформ-инвойсов и договоров</strong></li>
<li><strong>Оформление документов качества</strong>, таких как сертификаты производителя и сертификаты происхождения</li>
<li>Проведение цеховых операций <strong>резки нестандартных размеров, лазерной резки и газокислородной резки</strong> по индивидуальным заказам</li>
</ul>

<h3>Б) Доставка и логистика</h3>
<ul>
<li>Использование <strong>адреса доставки, информации о получателе и контактных данных</strong> в нашей внутренней сети доставки, охватывающей все 81 провинцию</li>
<li>Координация <strong>грузоперевозок</strong> с партнёрами-перевозчиками</li>
<li><strong>Оформление таможенных документов</strong> для международных экспортных отгрузок (Ирак, Сирия, Азербайджан, Туркменистан) и координация транзитной логистики</li>
<li>Оформление транспортных накладных и актов приёма-передачи</li>
</ul>

<h3>В) Выставление счетов и взыскание</h3>
<ul>
<li>Выставление электронных счетов, электронных архивных счетов или бумажных счетов</li>
<li>Отслеживание <strong>банковских переводов, EFT, кредитных карт, отсроченных чеков и операций по открытому срочному счёту</strong></li>
<li>Выполнение <strong>юридических обязательств перед финансовыми консультантами и официальными органами</strong></li>
<li>Возбуждение судебных разбирательств в случае задержки платежей</li>
</ul>

<h3>Г) Работа с клиентами и маркетинг</h3>
<ul>
<li><strong>Управление членскими записями</strong> в рамках программы лояльности клиентов</li>
<li><strong>Ответы на ценовые запросы</strong> через контактные формы, WhatsApp и телефонные звонки</li>
<li>В случае вашего явного согласия отправка вам <strong>анонсов о новых продуктах, акциях и отраслевых событиях</strong></li>
<li><strong>Улучшение пользовательского опыта</strong> на сайте (наша политика cookie публикуется отдельно)</li>
</ul>

<h3>Д) Юридические и нормативные обязательства</h3>
<ul>
<li><strong>Обязательства по хранению книг и документов</strong> в соответствии с Законом о налоговой процедуре, Турецким коммерческим кодексом и другим коммерческим законодательством</li>
<li><strong>Ответы на запросы</strong> Совета по расследованию финансовых преступлений (MASAK), таможенной администрации и других <strong>уполномоченных государственных учреждений</strong></li>
<li><strong>Предоставление доказательств и документов</strong> в ходе юридических аудитов, расследований и судебных разбирательств</li>
</ul>

<h2>3. Категории обрабатываемых персональных данных</h2>
<p>Для указанных выше целей обрабатываются следующие категории персональных данных:</p>

<table>
<thead><tr><th>Категория данных</th><th>Примеры данных</th></tr></thead>
<tbody>
<tr><td><strong>Идентификационные данные</strong></td><td>Имя, фамилия, номер ID, ИНН, налоговая инспекция, должность</td></tr>
<tr><td><strong>Контактные данные</strong></td><td>Телефон, мобильный, электронная почта, факс, адрес, страна/город/район</td></tr>
<tr><td><strong>Транзакции клиента</strong></td><td>История заказов, записи предложений, информация по счетам, данные платежей, адреса доставки</td></tr>
<tr><td><strong>Финансовые данные</strong></td><td>Банковские реквизиты (IBAN), план платежей, статус срочного счёта, кредитный рейтинг (при необходимости)</td></tr>
<tr><td><strong>Профессиональная информация</strong></td><td>Компания, должность, отрасль (для B2B-клиентов)</td></tr>
<tr><td><strong>Маркетинговые данные</strong></td><td>Коммуникационные предпочтения, интересы, записи кликов/посещений (с явным согласием)</td></tr>
<tr><td><strong>Юридические процессы</strong></td><td>Договоры, обязательства, записи о спорах</td></tr>
<tr><td><strong>Безопасность транзакций</strong></td><td>IP-адрес, журналы, cookie, информация о сеансе пользователя</td></tr>
</tbody>
</table>

<h2>4. Способ сбора персональных данных и правовое основание</h2>
<p>Ваши персональные данные собираются следующими способами:</p>
<ul>
<li><strong>Через сайт:</strong> Контактная форма, заявка на mail order, регистрационная форма программы лояльности, форма запроса предложения, мастер расчёта цен</li>
<li><strong>Устно и по телефону:</strong> Телефонные разговоры, переписка в WhatsApp, запросы, направляемые консультантам по продажам</li>
<li><strong>Через письменные документы:</strong> Подписанные договоры, бланки заказов, ценные бумаги, акты приёма, транспортные накладные</li>
<li><strong>Через личные визиты:</strong> Визитные карточки и формы каталога продукции, заполненные во время посещения нашего головного офиса и склада в Конье</li>
<li><strong>Через третьи стороны:</strong> Партнёры-перевозчики, банковские подтверждения, финансовые консультанты, таможенные брокеры</li>
<li><strong>Открытые источники:</strong> Системы открытого доступа, такие как GIB-проверка налогового свидетельства, MERSIS, Торговый реестр</li>
</ul>

<h4>Правовые основания (ст. 5 и 6 ЗоЗПД)</h4>
<p>Ваши персональные данные обрабатываются на основании одного или нескольких из следующих правовых оснований:</p>
<ol>
<li><strong>Явное согласие</strong> (ст. 5/1 ЗоЗПД) — Для обработки на основе согласия, такой как маркетинговые коммуникации</li>
<li><strong>Заключение или исполнение договора</strong> (ст. 5/2-в ЗоЗПД) — Для процессов заказа, доставки, выставления счетов</li>
<li><strong>Выполнение юридического обязательства</strong> (ст. 5/2-г ЗоЗПД) — Налоги, MASAK, коммерческие книги</li>
<li><strong>Законный интерес</strong> (ст. 5/2-е ЗоЗПД) — Отслеживание платежей, предотвращение мошенничества, управление клиентскими отношениями</li>
<li><strong>Установление, осуществление или защита права</strong> (ст. 5/2-д ЗоЗПД) — Юридические споры</li>
</ol>

<h2>5. Передача персональных данных</h2>
<p>Обработанные вами персональные данные могут быть переданы следующим третьим сторонам, ограниченным заявленными целями:</p>

<h4>Внутренние передачи</h4>
<ul>
<li><strong>Партнёры-перевозчики</strong> — для координации доставки</li>
<li><strong>Банки и финансовые учреждения</strong> — для процессов взыскания</li>
<li><strong>Финансовые консультанты и бухгалтерские офисы</strong> — для налоговых деклараций и ведения книг</li>
<li><strong>Государственные учреждения</strong> — Налоговые органы, GIB, MASAK, суды (в рамках юридических обязательств)</li>
<li><strong>Партнёры по решениям</strong> — Erdemir, Borcelik, Habas и др. (только при необходимости для процессов заказа и сертификатов качества)</li>
<li><strong>Поставщики ИТ-услуг</strong> — Поставщики серверов, электронной почты, интеграции электронных счетов</li>
</ul>

<h4>Международные передачи</h4>
<ul>
<li><strong>Таможенные службы и экспедиторы за рубежом</strong> — Таможенные процедуры в Ираке, Сирии, Азербайджане, Туркменистане</li>
<li>Персональные данные передаются только в объёме, необходимом в соответствующей стране экспорта, и защищены соответствующими мерами безопасности</li>
</ul>

<h2>6. Срок хранения персональных данных</h2>
<p>Ваши персональные данные хранятся в течение следующих сроков:</p>
<ul>
<li><strong>Налоговые и бухгалтерские записи:</strong> 10 лет (в соответствии с Законом о налоговой процедуре)</li>
<li><strong>Записи коммерческих книг:</strong> 10 лет (в соответствии с Турецким коммерческим кодексом)</li>
<li><strong>Записи транзакций клиентов:</strong> 10 лет с окончания клиентских отношений</li>
<li><strong>Маркетинговые данные:</strong> До отзыва согласия (максимум 5 лет)</li>
<li><strong>Журналы сайта:</strong> 6 месяцев (Закон № 5651)</li>
<li><strong>Заявки на работу:</strong> 1 год (удаляются, если не приняты на работу)</li>
</ul>
<p>Персональные данные, превышающие установленный законом срок хранения, удаляются, уничтожаются или анонимизируются в соответствии с нашей политикой уничтожения персональных данных.</p>

<h2>7. Безопасность персональных данных</h2>
<p>Для обеспечения безопасности ваших персональных данных мы применяем следующие технические и административные меры:</p>

<h4>Технические меры</h4>
<ul>
<li>SSL/TLS-шифрование (HTTPS) на всех формах и экранах входа</li>
<li>Серверы баз данных защищены файрволом и регулярным резервным копированием</li>
<li>Зашифрованное хранение паролей (алгоритм bcrypt/Argon2)</li>
<li>Актуальное антивирусное и антишпионское ПО</li>
<li>Ограниченный доступ через многофакторную аутентификацию (админ-панель)</li>
<li>Регулярные тесты безопасности и сканирование уязвимостей</li>
</ul>

<h4>Административные меры</h4>
<ul>
<li>Соглашения о конфиденциальности от сотрудников</li>
<li>Периодическое обучение защите персональных данных</li>
<li>Матрица авторизации по принципу доступа на основе необходимости знать</li>
<li>Реестр персональных данных и записи о деятельности по обработке</li>
<li>План реагирования на нарушения данных и процедуры уведомления</li>
</ul>

<h2>8. Права субъекта данных (ст. 11 ЗоЗПД)</h2>
<p>В соответствии со статьёй 11 ЗоЗПД, как субъект данных вы имеете следующие права:</p>
<ol>
<li>Узнать, обрабатываются ли ваши персональные данные</li>
<li>Запросить информацию об обработке, если такая обработка проводилась</li>
<li>Узнать цель обработки персональных данных и используются ли они по назначению</li>
<li>Знать третьи стороны, которым передаются персональные данные внутри страны или за рубежом</li>
<li>Запросить исправление персональных данных в случае неполной или неверной обработки</li>
<li>Запросить удаление или уничтожение персональных данных в рамках условий, установленных статьёй 7 Закона</li>
<li>Запросить уведомление третьих сторон, которым были переданы персональные данные, об операциях, выполненных согласно подпунктам (д) и (е)</li>
<li>Возразить против появления неблагоприятного результата через анализ обработанных данных исключительно автоматизированными системами</li>
<li>Запросить компенсацию ущерба в случае ущерба из-за незаконной обработки персональных данных</li>
</ol>

<h2>9. Способы подачи заявления и его рассмотрение</h2>
<p>Вы можете воспользоваться перечисленными выше правами следующими способами:</p>

<h4>Письменное заявление</h4>
<p>Доставив петицию с собственноручной подписью на адрес <strong>Район Февзичакмак, ул. Гюлистан, Атикер 3, Блок 2 № 33 AS — Каратай/КОНЬЯ</strong> лично или через нотариуса.</p>

<h4>Электронное заявление</h4>
<ul>
<li><strong>Зарегистрированная электронная почта (KEP):</strong> Если у вас есть адрес KEP, путём отправки на корпоративный адрес KEP нашей компании</li>
<li><strong>Электронная почта с защищённой ЭЦП:</strong> Путём отправки электронного письма с электронной подписью на <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a></li>
<li><strong>Безопасная аутентификация:</strong> Путём входа в нашу систему с ранее аутентифицированной учётной записью пользователя</li>
</ul>

<h4>Содержание заявления</h4>
<p>В заявлении должна быть указана следующая информация:</p>
<ul>
<li>Имя, фамилия, подпись (в письменном заявлении)</li>
<li>Для граждан Турции: номер ID Турции; для иностранцев: гражданство, номер паспорта</li>
<li>Адрес для уведомлений, электронная почта или факс</li>
<li>Предмет запроса (чётко указанный)</li>
<li>Информация и документы, относящиеся к запросу, при наличии</li>
</ul>

<h4>Срок рассмотрения</h4>
<p>Ваши заявления рассматриваются <strong>бесплатно в срок не более 30 дней</strong>. Однако, если процедура требует дополнительных затрат, может взиматься плата, определённая Советом по защите персональных данных.</p>

<h2>10. Уведомления о нарушении данных</h2>
<p>В случае незаконного получения ваших персональных данных другими лицами мы уведомляем вас и Совет по защите персональных данных в течение <strong>не более 72 часов</strong>. Уведомление включает характер нарушения, затронутые данные, принятые меры и контактную информацию.</p>

<h2>11. Изменения в Уведомлении о конфиденциальности</h2>
<p>Настоящее Уведомление о конфиденциальности может обновляться в связи с изменениями в законодательстве, решениями регулирующих органов или практикой нашей компании. Текущая версия публикуется на нашем сайте по адресу <a href="https://www.tekcanmetal.com/sayfa.php?slug=kvkk">www.tekcanmetal.com/sayfa.php?slug=kvkk</a>.</p>

<p><em>Последнее обновление: январь 2026</em></p>
'
WHERE slug = 'kvkk';

-- v1.0.65 — Çerez Politikası content 3 dilde (~5000 kelime × 3 dil)
UPDATE tm_pages SET
  content_en = '<p>As Tekcan Metal Sanayi ve Ticaret Ltd. Şti., we use cookies on our website at www.tekcanmetal.com (hereinafter referred to as the <em>"Website"</em>) to improve user experience, ensure efficient operation of the site and enhance our service quality. This Cookie Policy has been prepared to inform you about which cookies we use and for what purposes, and how you can manage them.</p>

<h2>1. What Is a Cookie?</h2>
<p>Cookies are small files containing limited text data that are placed on your device (computer, tablet, smartphone) by the websites you visit through your browser. These files generally contain information such as an identifier (ID), site name and validity period.</p>

<p>Cookies allow visitors to remember their interaction with the website, preserve session information, store user preferences and measure site performance. Cookies do not carry viruses on their own, cannot access your personal files, and do not damage your device.</p>

<h2>2. Which Cookies Do We Use?</h2>
<p>Four main cookie categories detailed below are used on our website:</p>

<h3>A) Essential (Technical) Cookies</h3>
<p><strong>Purpose:</strong> Cookies that are <em>strictly necessary</em> for the basic functions of the site to work. Without these cookies, the site does not work properly; you cannot log in, your forms cannot be submitted, security checks cannot be performed.</p>

<table>
<thead><tr><th>Cookie Name</th><th>Purpose</th><th>Duration</th></tr></thead>
<tbody>
<tr><td><code>PHPSESSID</code></td><td>Session management (login info, cart, form state)</td><td>Until browser closes</td></tr>
<tr><td><code>csrf_token</code></td><td>Form security (CSRF attack protection)</td><td>Session duration</td></tr>
<tr><td><code>cookie_consent</code></td><td>Remembering your cookie consent preference</td><td>1 year</td></tr>
<tr><td><code>tm_admin</code></td><td>Admin panel access session</td><td>30 days</td></tr>
</tbody>
</table>

<h4>Legal Basis</h4>
<p>Can be processed without user consent under PDPL art. 5/2-c (establishment and performance of contract) and PDPL art. 5/2-f (legitimate interest).</p>

<h3>B) Performance and Analytics Cookies</h3>
<p><strong>Purpose:</strong> Allow us to understand how visitors use the site. We analyze which pages are visited more, how long users spend on the site, and where they struggle, to improve our service. The collected data is <strong>anonymized</strong> and does not personally identify the user.</p>

<table>
<thead><tr><th>Provider</th><th>Purpose</th><th>Duration</th></tr></thead>
<tbody>
<tr><td>Google Analytics 4 (<code>_ga, _gid</code>)</td><td>Page views, user journey, demographic anonymous analysis</td><td>2 years / 24 hours</td></tr>
<tr><td>Yandex Metrica (<code>_ym_uid</code>)</td><td>Regional visitor analysis (especially Turkey, Russia markets)</td><td>1 year</td></tr>
<tr><td>Site log (<code>tm_visit</code>)</td><td>Measuring popular product/category pages</td><td>30 days</td></tr>
</tbody>
</table>

<h4>Legal Basis</h4>
<p>PDPL art. 5/1 (explicit consent) — processed based on the consent given by the user via the cookie consent banner. These cookies are not created if you do not give consent.</p>

<h3>C) Functionality (Preference) Cookies</h3>
<p><strong>Purpose:</strong> Allow us to provide a personalized experience on subsequent visits by remembering your usage preferences on the site.</p>

<table>
<thead><tr><th>Cookie Name</th><th>Purpose</th><th>Duration</th></tr></thead>
<tbody>
<tr><td><code>tm_lang</code></td><td>Language preference (TR / EN / AR / RU)</td><td>1 year</td></tr>
<tr><td><code>tm_theme</code></td><td>Light / dark theme selection</td><td>1 year</td></tr>
<tr><td><code>tm_calc_history</code></td><td>Last selected product in calculation wizard</td><td>30 days</td></tr>
<tr><td><code>tm_recent_views</code></td><td>Recently viewed products (quick access)</td><td>30 days</td></tr>
</tbody>
</table>

<h4>Legal Basis</h4>
<p>PDPL art. 5/1 (explicit consent) — activated if you give consent.</p>

<h3>D) Targeting and Advertising Cookies</h3>
<p><strong>Purpose:</strong> Used to show you ads <em>relevant to your interests</em> while browsing other sites after your visit to our website. As Tekcan Metal, these cookies may be used to deliver campaigns, new product announcements and seasonal price discounts to our customers in the steel sector.</p>

<table>
<thead><tr><th>Provider</th><th>Purpose</th><th>Duration</th></tr></thead>
<tbody>
<tr><td>Google Ads (<code>_gcl_au</code>, <code>NID</code>)</td><td>Remarketing, conversion tracking</td><td>3 months – 1 year</td></tr>
<tr><td>Meta Pixel (<code>_fbp</code>)</td><td>Facebook and Instagram ad tracking</td><td>3 months</td></tr>
<tr><td>LinkedIn Insight (<code>li_sugr</code>)</td><td>B2B sectoral targeting (industry, construction)</td><td>3 months</td></tr>
</tbody>
</table>

<h4>Legal Basis</h4>
<p>PDPL art. 5/1 (explicit consent) — activated only if you give consent. If declined, no remarketing targeting is performed, but you may continue to see random general ads.</p>

<h2>3. Third-Party Cookies</h2>
<p>In addition to cookies created directly by our Company on our website, there may also be cookies created by partners with whom we provide services. These third-party cookies are subject to the privacy policies of the respective providers:</p>
<ul>
<li><strong>Google LLC</strong> — Analytics, Maps, Ads, reCAPTCHA — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a></li>
<li><strong>Meta Platforms</strong> — Facebook, Instagram pixel — <a href="https://www.facebook.com/policies/cookies/" target="_blank" rel="noopener">Cookie Policy</a></li>
<li><strong>LinkedIn Corporation</strong> — Insight tag — <a href="https://www.linkedin.com/legal/cookie-policy" target="_blank" rel="noopener">Cookie Policy</a></li>
<li><strong>YouTube</strong> — Embed video cookies — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google Privacy</a></li>
<li><strong>OpenStreetMap</strong> — Shipping map — <a href="https://wiki.osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener">Privacy Policy</a></li>
</ul>

<blockquote>
<p>Third-party cookies are not managed by us. The responsibility for how the data collected by these cookies is used belongs to the relevant third-party service providers.</p>
</blockquote>

<h2>4. Cookie Retention Period</h2>
<p>Cookies are examined in two main groups according to their retention period:</p>

<h4>A) Session Cookies</h4>
<p>Deleted as soon as the browser is closed. Generally used for temporary operations such as session management and form security.</p>

<h4>B) Persistent Cookies</h4>
<p>Stored on your device for a predetermined period (e.g., 30 days, 1 year). When this period expires, they are automatically deleted or can be manually cleared by the user. The duration of each cookie is also specified in the tables.</p>

<h2>5. How Do You Manage Your Cookie Preferences?</h2>

<h3>A) On-Site Cookie Management</h3>
<p>Through the <strong>Cookie Consent Banner</strong> displayed on your first visit to our website:</p>
<ul>
<li>You can give consent to all cookies by clicking <strong>"Accept All"</strong>;</li>
<li>You can use only the technical cookies necessary for site operation by clicking <strong>"Essential Only"</strong>;</li>
<li>You can give cookie permissions by category by clicking <strong>"Customize"</strong> (e.g., Analytics yes, Advertising no).</li>
</ul>

<p>You can change your preference at any time using the <em>Cookie Settings</em> link at the bottom of the site.</p>

<h3>B) Browser Settings</h3>
<p>You can manage cookies via your browser settings:</p>
<ul>
<li><strong>Google Chrome:</strong> Settings → Privacy and security → Cookies and other site data</li>
<li><strong>Mozilla Firefox:</strong> Options → Privacy & Security → Cookies and Site Data</li>
<li><strong>Safari:</strong> Preferences → Privacy → Manage Website Data</li>
<li><strong>Microsoft Edge:</strong> Settings → Cookies and site permissions</li>
</ul>

<h3>C) Mobile Device Settings</h3>
<ul>
<li><strong>iOS:</strong> Settings → Safari → Block All Cookies</li>
<li><strong>Android:</strong> Chrome → Settings → Site settings → Cookies</li>
</ul>

<h2>6. Consequences of Refusing Cookies</h2>
<p>You have the right to refuse cookies. However, please note the following:</p>
<ul>
<li><strong>If essential cookies are blocked:</strong> Site functionality (login, form submission, cart) will be disabled. The site cannot fully function.</li>
<li><strong>If analytics cookies are blocked:</strong> Site browsing experience is not affected, but our ability to improve services is limited.</li>
<li><strong>If functionality cookies are blocked:</strong> Your settings such as language and theme are not remembered, you must re-enter on each visit.</li>
<li><strong>If advertising cookies are blocked:</strong> You will not see Tekcan Metal-specific personalized campaigns; however, random general ads on other sites may continue to appear.</li>
</ul>

<h2>7. Children and Cookies</h2>
<p>Our website is intended for B2B (business-to-business) industrial customers and is not designed for children under the age of 13. We do not knowingly collect personal data from children. If we determine that a child''s data has been mistakenly collected, we will promptly delete it.</p>

<h2>8. Data Transfer and Foreign Servers</h2>
<p>Some third-party cookie data (especially Google, Meta, LinkedIn) may be processed on servers located outside Turkey. These transfers are subject to the privacy frameworks of the respective providers (such as EU-US Data Privacy Framework). For more detailed information, please review our <a href="https://www.tekcanmetal.com/sayfa.php?slug=kvkk">Privacy Notice</a>.</p>

<h2>9. Changes to the Cookie Policy</h2>
<p>This Cookie Policy may be updated as our service offering, third-party providers and legal regulations change. The current version is published on our website at <a href="https://www.tekcanmetal.com/sayfa.php?slug=cerez-politikasi">www.tekcanmetal.com/sayfa.php?slug=cerez-politikasi</a>. The "Last update" date will be revised in case of significant changes.</p>

<p><em>Last update: January 2026</em></p>

<h2>10. Related Documents</h2>
<ul>
<li><a href="/sayfa.php?slug=kvkk">Personal Data Protection Privacy Notice</a> — All our personal data processing operations</li>
<li><a href="/iletisim.php">Contact</a> — For any questions and applications regarding cookies</li>
<li><a href="/">Tekcan Metal Homepage</a> — Our products and services</li>
</ul>

<h4>Effective Date</h4>
<p>This Cookie Policy has been published by Tekcan Metal Sanayi ve Ticaret Ltd. Şti. and is in effect.</p>
',
  content_ar = '<p>بصفتنا تكجان ميتال للصناعة والتجارة المحدودة، نستخدم ملفات تعريف الارتباط على موقعنا الإلكتروني www.tekcanmetal.com (المشار إليه فيما يلي بـ <em>"الموقع الإلكتروني"</em>) لتحسين تجربة المستخدم وضمان التشغيل الفعال للموقع وتعزيز جودة خدماتنا. تم إعداد سياسة ملفات تعريف الارتباط هذه لإعلامك بملفات تعريف الارتباط التي نستخدمها وأغراضها وكيفية إدارتها.</p>

<h2>1. ما هي ملفات تعريف الارتباط؟</h2>
<p>ملفات تعريف الارتباط (cookies) هي ملفات صغيرة تحتوي على بيانات نصية محدودة يتم وضعها على جهازك (الكمبيوتر، الجهاز اللوحي، الهاتف الذكي) من قبل المواقع الإلكترونية التي تزورها عبر متصفحك. تحتوي هذه الملفات عادةً على معلومات مثل المعرف (ID) واسم الموقع وفترة الصلاحية.</p>

<p>تتيح ملفات تعريف الارتباط للزوار تذكر تفاعلهم مع الموقع الإلكتروني والحفاظ على معلومات الجلسة وتخزين تفضيلات المستخدم وقياس أداء الموقع. لا تحمل ملفات تعريف الارتباط فيروسات بمفردها ولا يمكنها الوصول إلى ملفاتك الشخصية ولا تلحق الضرر بجهازك.</p>

<h2>2. ما هي ملفات تعريف الارتباط التي نستخدمها؟</h2>
<p>يستخدم موقعنا الإلكتروني أربع فئات رئيسية من ملفات تعريف الارتباط الموضحة أدناه بالتفصيل:</p>

<h3>أ) ملفات تعريف الارتباط الأساسية (التقنية)</h3>
<p><strong>الغرض:</strong> ملفات تعريف الارتباط <em>الضرورية تماماً</em> لعمل الوظائف الأساسية للموقع. بدون ملفات تعريف الارتباط هذه لا يعمل الموقع بشكل صحيح؛ لا يمكنك تسجيل الدخول، ولا يمكن إرسال نماذجك، ولا يمكن إجراء فحوصات الأمان.</p>

<table>
<thead><tr><th>اسم ملف تعريف الارتباط</th><th>الغرض</th><th>المدة</th></tr></thead>
<tbody>
<tr><td><code>PHPSESSID</code></td><td>إدارة الجلسة (معلومات تسجيل الدخول، السلة، حالة النموذج)</td><td>حتى إغلاق المتصفح</td></tr>
<tr><td><code>csrf_token</code></td><td>أمان النموذج (الحماية من هجمات CSRF)</td><td>طوال مدة الجلسة</td></tr>
<tr><td><code>cookie_consent</code></td><td>تذكر تفضيل موافقتك على ملفات تعريف الارتباط</td><td>سنة واحدة</td></tr>
<tr><td><code>tm_admin</code></td><td>جلسة الوصول إلى لوحة الإدارة</td><td>30 يوماً</td></tr>
</tbody>
</table>

<h4>الأساس القانوني</h4>
<p>يمكن معالجتها دون موافقة المستخدم بموجب مادة 5/2-ج من KVKK (إنشاء وتنفيذ العقد) ومادة 5/2-و من KVKK (المصلحة المشروعة).</p>

<h3>ب) ملفات تعريف الارتباط الخاصة بالأداء والتحليلات</h3>
<p><strong>الغرض:</strong> تتيح لنا فهم كيفية استخدام الزوار للموقع. نحلل الصفحات التي تتم زيارتها أكثر، والوقت الذي يقضيه المستخدمون على الموقع، والنقاط التي يواجهون فيها صعوبات، لتطوير خدماتنا. يتم <strong>إخفاء هوية</strong> البيانات التي تم جمعها ولا تحدد المستخدم شخصياً.</p>

<table>
<thead><tr><th>المزود</th><th>الغرض</th><th>المدة</th></tr></thead>
<tbody>
<tr><td>Google Analytics 4 (<code>_ga, _gid</code>)</td><td>مشاهدات الصفحة، رحلة المستخدم، التحليل الديموغرافي المجهول الهوية</td><td>سنتان / 24 ساعة</td></tr>
<tr><td>Yandex Metrica (<code>_ym_uid</code>)</td><td>تحليل الزوار الإقليمي (خاصة أسواق تركيا وروسيا)</td><td>سنة واحدة</td></tr>
<tr><td>سجل الموقع (<code>tm_visit</code>)</td><td>قياس صفحات المنتجات/الفئات الشائعة</td><td>30 يوماً</td></tr>
</tbody>
</table>

<h4>الأساس القانوني</h4>
<p>مادة 5/1 من KVKK (الموافقة الصريحة) — تتم المعالجة بناءً على الموافقة الممنوحة من قبل المستخدم عبر شعار موافقة ملفات تعريف الارتباط. لا يتم إنشاء ملفات تعريف الارتباط هذه إذا لم تعطِ موافقتك.</p>

<h3>ج) ملفات تعريف الارتباط الوظيفية (التفضيل)</h3>
<p><strong>الغرض:</strong> تتيح لنا تقديم تجربة شخصية في زياراتك اللاحقة من خلال تذكر تفضيلات الاستخدام الخاصة بك على الموقع.</p>

<table>
<thead><tr><th>اسم ملف تعريف الارتباط</th><th>الغرض</th><th>المدة</th></tr></thead>
<tbody>
<tr><td><code>tm_lang</code></td><td>تفضيل اللغة (TR / EN / AR / RU)</td><td>سنة واحدة</td></tr>
<tr><td><code>tm_theme</code></td><td>اختيار السمة الفاتحة / الداكنة</td><td>سنة واحدة</td></tr>
<tr><td><code>tm_calc_history</code></td><td>المنتج الأخير المحدد في معالج الحساب</td><td>30 يوماً</td></tr>
<tr><td><code>tm_recent_views</code></td><td>المنتجات المعروضة مؤخراً (الوصول السريع)</td><td>30 يوماً</td></tr>
</tbody>
</table>

<h4>الأساس القانوني</h4>
<p>مادة 5/1 من KVKK (الموافقة الصريحة) — يتم تفعيلها إذا أعطيت موافقتك.</p>

<h3>د) ملفات تعريف الارتباط الخاصة بالاستهداف والإعلانات</h3>
<p><strong>الغرض:</strong> تستخدم لعرض إعلانات <em>ذات صلة باهتماماتك</em> أثناء تصفحك لمواقع أخرى بعد زيارتك لموقعنا. كتكجان ميتال، يمكن استخدام ملفات تعريف الارتباط هذه لإيصال الحملات وإعلانات المنتجات الجديدة وتخفيضات الأسعار الموسمية لعملائنا في قطاع الحديد والصلب.</p>

<table>
<thead><tr><th>المزود</th><th>الغرض</th><th>المدة</th></tr></thead>
<tbody>
<tr><td>Google Ads (<code>_gcl_au</code>, <code>NID</code>)</td><td>إعادة التسويق، تتبع التحويلات</td><td>3 أشهر – سنة واحدة</td></tr>
<tr><td>Meta Pixel (<code>_fbp</code>)</td><td>تتبع إعلانات Facebook و Instagram</td><td>3 أشهر</td></tr>
<tr><td>LinkedIn Insight (<code>li_sugr</code>)</td><td>الاستهداف القطاعي B2B (الصناعة، البناء)</td><td>3 أشهر</td></tr>
</tbody>
</table>

<h4>الأساس القانوني</h4>
<p>مادة 5/1 من KVKK (الموافقة الصريحة) — يتم تفعيلها فقط إذا أعطيت موافقتك. في حالة الرفض، لا يتم استهداف إعادة التسويق، ولكن قد تستمر في رؤية إعلانات عشوائية عامة.</p>

<h2>3. ملفات تعريف الارتباط الخاصة بالأطراف الثالثة</h2>
<p>بالإضافة إلى ملفات تعريف الارتباط التي تنشئها شركتنا مباشرة على موقعنا الإلكتروني، قد توجد أيضاً ملفات تعريف الارتباط التي ينشئها الشركاء الذين نقدم لهم الخدمات. تخضع ملفات تعريف الارتباط هذه الخاصة بالأطراف الثالثة لسياسات الخصوصية الخاصة بالمزودين المعنيين:</p>
<ul>
<li><strong>Google LLC</strong> — Analytics, Maps, Ads, reCAPTCHA — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">سياسة الخصوصية</a></li>
<li><strong>Meta Platforms</strong> — Facebook, Instagram pixel — <a href="https://www.facebook.com/policies/cookies/" target="_blank" rel="noopener">سياسة ملفات تعريف الارتباط</a></li>
<li><strong>LinkedIn Corporation</strong> — Insight tag — <a href="https://www.linkedin.com/legal/cookie-policy" target="_blank" rel="noopener">سياسة ملفات تعريف الارتباط</a></li>
<li><strong>YouTube</strong> — ملفات تعريف الارتباط الخاصة بالفيديو المضمن — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">خصوصية Google</a></li>
<li><strong>OpenStreetMap</strong> — خريطة الشحن — <a href="https://wiki.osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener">Privacy Policy</a></li>
</ul>

<blockquote>
<p>لا نقوم بإدارة ملفات تعريف الارتباط الخاصة بالأطراف الثالثة. تقع المسؤولية عن كيفية استخدام البيانات التي تم جمعها بواسطة ملفات تعريف الارتباط هذه على عاتق مزودي الخدمة من الأطراف الثالثة المعنيين.</p>
</blockquote>

<h2>4. فترة الاحتفاظ بملفات تعريف الارتباط</h2>
<p>تنقسم ملفات تعريف الارتباط إلى مجموعتين رئيسيتين وفقاً لفترات الاحتفاظ:</p>

<h4>أ) ملفات تعريف ارتباط الجلسة (Session Cookies)</h4>
<p>تُحذف بمجرد إغلاق المتصفح. تُستخدم عموماً للعمليات المؤقتة مثل إدارة الجلسة وأمان النموذج.</p>

<h4>ب) ملفات تعريف الارتباط الدائمة (Persistent Cookies)</h4>
<p>يتم تخزينها على جهازك لفترة محددة مسبقاً (مثل 30 يوماً، سنة واحدة). عند انتهاء هذه الفترة، يتم حذفها تلقائياً أو يمكن للمستخدم مسحها يدوياً. يتم تحديد مدة كل ملف تعريف ارتباط في الجداول.</p>

<h2>5. كيف تدير تفضيلات ملفات تعريف الارتباط الخاصة بك؟</h2>

<h3>أ) إدارة ملفات تعريف الارتباط على الموقع</h3>
<p>عبر <strong>شعار موافقة ملفات تعريف الارتباط</strong> الذي يظهر على شاشتك عند زيارتك الأولى لموقعنا الإلكتروني:</p>
<ul>
<li>يمكنك إعطاء موافقتك على جميع ملفات تعريف الارتباط بالنقر على <strong>"قبول الكل"</strong>؛</li>
<li>يمكنك استخدام ملفات تعريف الارتباط التقنية الضرورية لتشغيل الموقع فقط بالنقر على <strong>"الضرورية فقط"</strong>؛</li>
<li>يمكنك إعطاء أذونات ملفات تعريف الارتباط حسب الفئة بالنقر على <strong>"تخصيص"</strong> (مثلاً، التحليلات نعم، الإعلانات لا).</li>
</ul>

<p>يمكنك تغيير تفضيلك في أي وقت باستخدام رابط <em>إعدادات ملفات تعريف الارتباط</em> الموجود في أسفل الموقع.</p>

<h3>ب) إعدادات المتصفح</h3>
<p>يمكنك إدارة ملفات تعريف الارتباط عبر إعدادات المتصفح:</p>
<ul>
<li><strong>Google Chrome:</strong> الإعدادات → الخصوصية والأمان → ملفات تعريف الارتباط وبيانات الموقع الأخرى</li>
<li><strong>Mozilla Firefox:</strong> الخيارات → الخصوصية والأمان → ملفات تعريف الارتباط وبيانات الموقع</li>
<li><strong>Safari:</strong> التفضيلات → الخصوصية → إدارة بيانات الموقع</li>
<li><strong>Microsoft Edge:</strong> الإعدادات → ملفات تعريف الارتباط وأذونات الموقع</li>
</ul>

<h3>ج) إعدادات الجهاز المحمول</h3>
<ul>
<li><strong>iOS:</strong> الإعدادات → Safari → حظر جميع ملفات تعريف الارتباط</li>
<li><strong>Android:</strong> Chrome → الإعدادات → إعدادات الموقع → ملفات تعريف الارتباط</li>
</ul>

<h2>6. عواقب رفض ملفات تعريف الارتباط</h2>
<p>لديك الحق في رفض ملفات تعريف الارتباط. ومع ذلك، يرجى ملاحظة ما يلي:</p>
<ul>
<li><strong>إذا تم حظر ملفات تعريف الارتباط الأساسية:</strong> ستتعطل وظيفة الموقع (تسجيل الدخول، إرسال النماذج، السلة). لا يمكن للموقع العمل بشكل كامل.</li>
<li><strong>إذا تم حظر ملفات تعريف الارتباط التحليلية:</strong> لا تتأثر تجربة تصفح الموقع، ولكن قدرتنا على تحسين الخدمات تكون محدودة.</li>
<li><strong>إذا تم حظر ملفات تعريف الارتباط الوظيفية:</strong> لن يتم تذكر إعداداتك مثل اللغة والسمة، ويجب عليك إعادة الإدخال في كل زيارة.</li>
<li><strong>إذا تم حظر ملفات تعريف الارتباط الإعلانية:</strong> لن ترى الحملات الشخصية الخاصة بتكجان ميتال؛ ومع ذلك، قد تستمر الإعلانات العامة العشوائية في الظهور على المواقع الأخرى.</li>
</ul>

<h2>7. الأطفال وملفات تعريف الارتباط</h2>
<p>موقعنا الإلكتروني مخصص لعملاء B2B (من شركة لشركة) الصناعيين وغير مصمم للأطفال دون سن 13 عاماً. لا نجمع عن قصد البيانات الشخصية من الأطفال. إذا تبين أنه تم جمع بيانات طفل عن طريق الخطأ، فسنقوم بحذفها على الفور.</p>

<h2>8. نقل البيانات والخوادم الأجنبية</h2>
<p>قد تتم معالجة بعض بيانات ملفات تعريف الارتباط الخاصة بالأطراف الثالثة (خاصة Google و Meta و LinkedIn) على خوادم موجودة خارج تركيا. تخضع عمليات النقل هذه لأطر الخصوصية الخاصة بالمزودين المعنيين (مثل EU-US Data Privacy Framework). للحصول على معلومات أكثر تفصيلاً، يرجى مراجعة <a href="https://www.tekcanmetal.com/sayfa.php?slug=kvkk">إشعار الخصوصية</a>.</p>

<h2>9. التغييرات في سياسة ملفات تعريف الارتباط</h2>
<p>قد يتم تحديث سياسة ملفات تعريف الارتباط هذه مع تغير عرض خدماتنا والمزودين الخارجيين واللوائح القانونية. يتم نشر النسخة الحالية على موقعنا الإلكتروني على <a href="https://www.tekcanmetal.com/sayfa.php?slug=cerez-politikasi">www.tekcanmetal.com/sayfa.php?slug=cerez-politikasi</a>. سيتم تعديل تاريخ "آخر تحديث" في حالة وجود تغييرات مهمة.</p>

<p><em>آخر تحديث: يناير 2026</em></p>

<h2>10. الوثائق ذات الصلة</h2>
<ul>
<li><a href="/sayfa.php?slug=kvkk">إشعار حماية البيانات الشخصية</a> — جميع عمليات معالجة البيانات الشخصية لدينا</li>
<li><a href="/iletisim.php">اتصل بنا</a> — لأي أسئلة وطلبات تتعلق بملفات تعريف الارتباط</li>
<li><a href="/">الصفحة الرئيسية لتكجان ميتال</a> — منتجاتنا وخدماتنا</li>
</ul>

<h4>تاريخ السريان</h4>
<p>تم نشر سياسة ملفات تعريف الارتباط هذه من قبل تكجان ميتال للصناعة والتجارة المحدودة وهي سارية المفعول.</p>
',
  content_ru = '<p>Как Tekcan Metal Sanayi ve Ticaret Ltd. Şti., мы используем файлы cookie на нашем сайте www.tekcanmetal.com (далее именуемом <em>"Сайт"</em>) для улучшения пользовательского опыта, обеспечения эффективной работы сайта и повышения качества наших услуг. Настоящая Политика использования cookie подготовлена, чтобы информировать вас о том, какие cookie мы используем, для каких целей и как вы можете ими управлять.</p>

<h2>1. Что такое cookie?</h2>
<p>Cookie — это небольшие файлы, содержащие ограниченные текстовые данные, которые размещаются на вашем устройстве (компьютер, планшет, смартфон) посещаемыми вами веб-сайтами через ваш браузер. Эти файлы обычно содержат такую информацию, как идентификатор (ID), имя сайта и срок действия.</p>

<p>Cookie позволяют посетителям запоминать своё взаимодействие с веб-сайтом, сохранять информацию о сеансе, хранить пользовательские предпочтения и измерять производительность сайта. Cookie сами по себе не содержат вирусов, не имеют доступа к вашим личным файлам и не наносят вреда вашему устройству.</p>

<h2>2. Какие cookie мы используем?</h2>
<p>На нашем сайте используются четыре основные категории cookie, подробно описанные ниже:</p>

<h3>А) Обязательные (технические) cookie</h3>
<p><strong>Цель:</strong> Cookie, <em>строго необходимые</em> для работы основных функций сайта. Без этих cookie сайт не работает должным образом; вы не можете войти в систему, не отправляются ваши формы, не выполняются проверки безопасности.</p>

<table>
<thead><tr><th>Имя cookie</th><th>Цель</th><th>Срок</th></tr></thead>
<tbody>
<tr><td><code>PHPSESSID</code></td><td>Управление сеансом (информация о входе, корзина, состояние формы)</td><td>До закрытия браузера</td></tr>
<tr><td><code>csrf_token</code></td><td>Безопасность формы (защита от CSRF-атак)</td><td>На время сеанса</td></tr>
<tr><td><code>cookie_consent</code></td><td>Запоминание ваших предпочтений согласия на cookie</td><td>1 год</td></tr>
<tr><td><code>tm_admin</code></td><td>Сеанс доступа к панели администратора</td><td>30 дней</td></tr>
</tbody>
</table>

<h4>Правовое основание</h4>
<p>Может обрабатываться без согласия пользователя в соответствии со ст. 5/2-в ЗоЗПД (заключение и исполнение договора) и ст. 5/2-е ЗоЗПД (законный интерес).</p>

<h3>Б) Cookie производительности и аналитики</h3>
<p><strong>Цель:</strong> Позволяют нам понять, как посетители используют сайт. Мы анализируем, какие страницы посещаются чаще, сколько времени пользователи проводят на сайте и в каких местах испытывают трудности, чтобы улучшить наши услуги. Собранные данные <strong>анонимизируются</strong> и не идентифицируют пользователя лично.</p>

<table>
<thead><tr><th>Поставщик</th><th>Цель</th><th>Срок</th></tr></thead>
<tbody>
<tr><td>Google Analytics 4 (<code>_ga, _gid</code>)</td><td>Просмотры страниц, путь пользователя, демографический анонимный анализ</td><td>2 года / 24 часа</td></tr>
<tr><td>Yandex Metrica (<code>_ym_uid</code>)</td><td>Региональный анализ посетителей (особенно рынки Турции и России)</td><td>1 год</td></tr>
<tr><td>Журнал сайта (<code>tm_visit</code>)</td><td>Измерение популярных страниц продуктов/категорий</td><td>30 дней</td></tr>
</tbody>
</table>

<h4>Правовое основание</h4>
<p>Ст. 5/1 ЗоЗПД (явное согласие) — обрабатывается на основе согласия, данного пользователем через баннер согласия на cookie. Эти cookie не создаются, если вы не даёте согласия.</p>

<h3>В) Функциональные cookie (предпочтений)</h3>
<p><strong>Цель:</strong> Позволяют нам обеспечить персонализированный опыт при последующих визитах, запоминая ваши предпочтения использования на сайте.</p>

<table>
<thead><tr><th>Имя cookie</th><th>Цель</th><th>Срок</th></tr></thead>
<tbody>
<tr><td><code>tm_lang</code></td><td>Языковые предпочтения (TR / EN / AR / RU)</td><td>1 год</td></tr>
<tr><td><code>tm_theme</code></td><td>Выбор светлой / тёмной темы</td><td>1 год</td></tr>
<tr><td><code>tm_calc_history</code></td><td>Последний выбранный продукт в мастере расчёта</td><td>30 дней</td></tr>
<tr><td><code>tm_recent_views</code></td><td>Недавно просмотренные продукты (быстрый доступ)</td><td>30 дней</td></tr>
</tbody>
</table>

<h4>Правовое основание</h4>
<p>Ст. 5/1 ЗоЗПД (явное согласие) — активируется, если вы даёте согласие.</p>

<h3>Г) Cookie таргетинга и рекламы</h3>
<p><strong>Цель:</strong> Используются для показа вам рекламы <em>соответствующей вашим интересам</em> при просмотре других сайтов после вашего визита на наш сайт. Как Tekcan Metal, эти cookie могут использоваться для доставки кампаний, анонсов новых продуктов и сезонных ценовых скидок нашим клиентам в металлургическом секторе.</p>

<table>
<thead><tr><th>Поставщик</th><th>Цель</th><th>Срок</th></tr></thead>
<tbody>
<tr><td>Google Ads (<code>_gcl_au</code>, <code>NID</code>)</td><td>Ремаркетинг, отслеживание конверсий</td><td>3 месяца – 1 год</td></tr>
<tr><td>Meta Pixel (<code>_fbp</code>)</td><td>Отслеживание рекламы Facebook и Instagram</td><td>3 месяца</td></tr>
<tr><td>LinkedIn Insight (<code>li_sugr</code>)</td><td>B2B отраслевой таргетинг (промышленность, строительство)</td><td>3 месяца</td></tr>
</tbody>
</table>

<h4>Правовое основание</h4>
<p>Ст. 5/1 ЗоЗПД (явное согласие) — активируется только при вашем согласии. В случае отказа таргетинг ремаркетинга не выполняется, но вы можете продолжать видеть случайную общую рекламу.</p>

<h2>3. Сторонние cookie</h2>
<p>В дополнение к cookie, создаваемым непосредственно нашей Компанией на нашем сайте, могут также присутствовать cookie, создаваемые партнёрами, которым мы предоставляем услуги. Эти сторонние cookie подпадают под политику конфиденциальности соответствующих поставщиков:</p>
<ul>
<li><strong>Google LLC</strong> — Analytics, Maps, Ads, reCAPTCHA — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Политика конфиденциальности</a></li>
<li><strong>Meta Platforms</strong> — Facebook, Instagram pixel — <a href="https://www.facebook.com/policies/cookies/" target="_blank" rel="noopener">Политика cookie</a></li>
<li><strong>LinkedIn Corporation</strong> — Insight tag — <a href="https://www.linkedin.com/legal/cookie-policy" target="_blank" rel="noopener">Политика cookie</a></li>
<li><strong>YouTube</strong> — cookie встроенного видео — <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Конфиденциальность Google</a></li>
<li><strong>OpenStreetMap</strong> — карта доставки — <a href="https://wiki.osmfoundation.org/wiki/Privacy_Policy" target="_blank" rel="noopener">Privacy Policy</a></li>
</ul>

<blockquote>
<p>Сторонние cookie не управляются нами. Ответственность за то, как используются данные, собираемые этими cookie, лежит на соответствующих сторонних поставщиках услуг.</p>
</blockquote>

<h2>4. Срок хранения cookie</h2>
<p>Cookie рассматриваются в двух основных группах в зависимости от срока хранения:</p>

<h4>А) Сеансовые cookie (Session Cookies)</h4>
<p>Удаляются сразу после закрытия браузера. Обычно используются для временных операций, таких как управление сеансом и безопасность форм.</p>

<h4>Б) Постоянные cookie (Persistent Cookies)</h4>
<p>Хранятся на вашем устройстве в течение заранее определённого периода (например, 30 дней, 1 год). По истечении этого срока они автоматически удаляются или могут быть очищены пользователем вручную. Срок действия каждого cookie также указан в таблицах.</p>

<h2>5. Как управлять предпочтениями cookie?</h2>

<h3>А) Управление cookie на сайте</h3>
<p>Через <strong>Баннер согласия на cookie</strong>, отображаемый при вашем первом посещении нашего сайта:</p>
<ul>
<li>Вы можете дать согласие на все cookie, нажав <strong>"Принять все"</strong>;</li>
<li>Вы можете использовать только технические cookie, необходимые для работы сайта, нажав <strong>"Только обязательные"</strong>;</li>
<li>Вы можете дать разрешения на cookie по категориям, нажав <strong>"Настроить"</strong> (например, аналитика — да, реклама — нет).</li>
</ul>

<p>Вы можете изменить свои предпочтения в любое время, используя ссылку <em>Настройки cookie</em> в нижней части сайта.</p>

<h3>Б) Настройки браузера</h3>
<p>Вы можете управлять cookie через настройки браузера:</p>
<ul>
<li><strong>Google Chrome:</strong> Настройки → Конфиденциальность и безопасность → Файлы cookie и другие данные сайта</li>
<li><strong>Mozilla Firefox:</strong> Параметры → Приватность и защита → Куки и данные сайтов</li>
<li><strong>Safari:</strong> Настройки → Конфиденциальность → Управление данными веб-сайтов</li>
<li><strong>Microsoft Edge:</strong> Настройки → Файлы cookie и разрешения сайтов</li>
</ul>

<h3>В) Настройки мобильного устройства</h3>
<ul>
<li><strong>iOS:</strong> Настройки → Safari → Блокировать все cookie</li>
<li><strong>Android:</strong> Chrome → Настройки → Настройки сайтов → Cookie</li>
</ul>

<h2>6. Последствия отказа от cookie</h2>
<p>У вас есть право отказаться от cookie. Однако, пожалуйста, обратите внимание на следующее:</p>
<ul>
<li><strong>Если обязательные cookie заблокированы:</strong> Функциональность сайта (вход, отправка форм, корзина) будет отключена. Сайт не сможет полноценно работать.</li>
<li><strong>Если аналитические cookie заблокированы:</strong> Опыт просмотра сайта не пострадает, но наша возможность улучшать услуги ограничена.</li>
<li><strong>Если функциональные cookie заблокированы:</strong> Ваши настройки, такие как язык и тема, не запоминаются, вам нужно вводить их заново при каждом посещении.</li>
<li><strong>Если рекламные cookie заблокированы:</strong> Вы не увидите персонализированных кампаний Tekcan Metal; однако случайные общие объявления на других сайтах могут продолжать появляться.</li>
</ul>

<h2>7. Дети и cookie</h2>
<p>Наш сайт предназначен для B2B-клиентов (бизнес-бизнес) промышленных секторов и не предназначен для детей младше 13 лет. Мы сознательно не собираем персональные данные у детей. Если будет установлено, что данные ребёнка были собраны по ошибке, мы оперативно их удалим.</p>

<h2>8. Передача данных и зарубежные серверы</h2>
<p>Некоторые данные сторонних cookie (особенно Google, Meta, LinkedIn) могут обрабатываться на серверах, расположенных за пределами Турции. Эти передачи подпадают под рамки конфиденциальности соответствующих поставщиков (например, EU-US Data Privacy Framework). Для получения более подробной информации, пожалуйста, ознакомьтесь с нашим <a href="https://www.tekcanmetal.com/sayfa.php?slug=kvkk">Уведомлением о конфиденциальности</a>.</p>

<h2>9. Изменения в Политике использования cookie</h2>
<p>Настоящая Политика использования cookie может обновляться по мере изменения нашего предложения услуг, сторонних поставщиков и юридических нормативов. Текущая версия публикуется на нашем сайте по адресу <a href="https://www.tekcanmetal.com/sayfa.php?slug=cerez-politikasi">www.tekcanmetal.com/sayfa.php?slug=cerez-politikasi</a>. Дата "Последнее обновление" будет пересмотрена в случае значительных изменений.</p>

<p><em>Последнее обновление: январь 2026</em></p>

<h2>10. Связанные документы</h2>
<ul>
<li><a href="/sayfa.php?slug=kvkk">Уведомление о защите персональных данных</a> — Все наши операции по обработке персональных данных</li>
<li><a href="/iletisim.php">Контакты</a> — По любым вопросам и заявлениям относительно cookie</li>
<li><a href="/">Главная страница Tekcan Metal</a> — Наши продукты и услуги</li>
</ul>

<h4>Дата вступления в силу</h4>
<p>Настоящая Политика использования cookie опубликована Tekcan Metal Sanayi ve Ticaret Ltd. Şti. и действует.</p>
'
WHERE slug = 'cerez-politikasi';

-- =====================================================
-- v1.0.66 — SEO SÜPER İÇERİK: Genişletilmiş Sac + Baklava Sac
-- Search Console verisi: 3.632 + 2.410 gösterim
-- Hedef: 1. sayfa Google, %5+ CTR
-- =====================================================

-- 1) Blog kategorisi (yoksa) - 'urun-rehberi'
INSERT IGNORE INTO tm_blog_categories (slug, name, description, sort_order)
VALUES ('urun-rehberi', 'Ürün Rehberi', 'Demir-çelik ürün detayları, teknik özellikler ve kullanım rehberleri', 0);

-- 2) Genişletilmiş Sac süper sayfa
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'genisletilmis-sac-rehberi',
    'Genişletilmiş Sac: Ölçüler, Ağırlık Hesaplama, Çeşitler ve Kullanım Alanları',
    'Genişletilmiş sac ölçüleri, ağırlık tablosu, DKP/HRP/galvaniz/paslanmaz/alüminyum çeşitleri ve kullanım alanları. Tekcan Metal — 20+ yıl tecrübe, Konya merkezli, 81 il sevkiyat.',
    '<p class="lead"><strong>Genişletilmiş sac</strong> (genleştirilmiş, gerdirilmiş veya delikli sac olarak da bilinir), düz metal levhalarına özel preslerle çekme uygulanarak elde edilen, açık <strong>elmas, baklava veya altıgen desenli</strong> tek parça delikli yapıdır. Tekcan Metal olarak 2005''ten bu yana Konya merkezimizden Türkiye geneline genişletilmiş sac tedariği gerçekleştiriyoruz.</p>

<h2>Genişletilmiş Sac Nedir? Üretim Süreci ve Yapısı</h2>

<p>Genişletilmiş sac, düz bir sac levhanın özel kalıplı preslerde aynı anda kesilip esnetilmesiyle elde edilen, ağ benzeri açık desenlere sahip yapısal bir metal üründür. Üretim sürecinde tek bir saç levha kullanılır; <strong>kaynak ya da montaj yapılmaz</strong>. Bu yöntem, ürünün hem hafif hem de oldukça dayanıklı olmasını sağlar.</p>

<h3>Üretim Aşamaları</h3>
<ol>
<li><strong>Hammadde seçimi:</strong> 0,5 mm – 6 mm kalınlığında DKP, HRP, paslanmaz, alüminyum veya galvaniz sac levhası</li>
<li><strong>Pres ile kesme ve genleştirme:</strong> Kalıbın yukarı-aşağı hareketiyle saç eş zamanlı kesilir ve gerilir</li>
<li><strong>Düzleştirme (opsiyonel):</strong> Yükseltilmiş yapıdan düzleştirilmiş yapıya geçirilir</li>
<li><strong>Yüzey işlemi:</strong> Galvaniz, elektrostatik boya veya elektro-galvaniz uygulaması (ihtiyaca göre)</li>
<li><strong>Kalite kontrol:</strong> Boyut, ağırlık, açıklık oranı, gerilim direnci ölçümü</li>
</ol>

<h2>Genişletilmiş Sac Ölçüleri ve Standart Boyutları</h2>

<p>Tekcan Metal stoklarında bulunan <strong>standart genişletilmiş sac ölçüleri</strong> aşağıdaki tabloda listelenmiştir. Özel ölçü siparişleri için lazer kesim atölyemizden faydalanabilirsiniz.</p>

<table>
<thead>
<tr><th>Levha Boyutu (mm)</th><th>Hücre Açıklığı</th><th>Hücre Yüksekliği</th><th>Sac Kalınlığı</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>15 × 30</td><td>2,5 mm</td><td>1,5 mm – 3 mm</td></tr>
<tr><td>1000 × 2000</td><td>20 × 50</td><td>4 mm</td><td>2 mm – 4 mm</td></tr>
<tr><td>1250 × 2500</td><td>30 × 60</td><td>5 mm</td><td>3 mm – 5 mm</td></tr>
<tr><td>1500 × 3000</td><td>40 × 80</td><td>6 mm</td><td>3 mm – 6 mm</td></tr>
<tr><td>Özel ölçü</td><td>Talebe göre</td><td>Talebe göre</td><td>0,8 mm – 8 mm</td></tr>
</tbody>
</table>

<h3>Hücre Tipleri (Açıklık Geometrisi)</h3>
<ul>
<li><strong>Elmas (Diamond):</strong> En yaygın tip — yürüme yolları, koruyucu gridler</li>
<li><strong>Altıgen (Hexagonal):</strong> Estetik mimari uygulamalar, dekoratif cepheler</li>
<li><strong>Kare (Square):</strong> Endüstriyel filtreleme, hava sirkülasyonu</li>
<li><strong>Yuvarlak (Round):</strong> Akustik paneller, hassas filtreleme</li>
</ul>

<h2>Genişletilmiş Sac Ağırlık Hesaplama</h2>

<p>Genişletilmiş sacın <strong>m² ağırlığı</strong>, kullanılan baz sacın yoğunluğu, kalınlığı ve hücre açıklık oranına göre değişir. Tekcan Metal''in <a href="/hesaplama.php">online ağırlık hesaplama motoru</a> ile saniyeler içinde doğru hesabı yapabilirsiniz.</p>

<h3>Pratik Ağırlık Tablosu (kg/m²)</h3>
<table>
<thead>
<tr><th>Sac Kalınlığı</th><th>Hücre 15×30</th><th>Hücre 20×50</th><th>Hücre 30×60</th><th>Hücre 40×80</th></tr>
</thead>
<tbody>
<tr><td>1,5 mm</td><td>5,8 kg</td><td>4,2 kg</td><td>3,5 kg</td><td>2,9 kg</td></tr>
<tr><td>2 mm</td><td>7,7 kg</td><td>5,6 kg</td><td>4,7 kg</td><td>3,8 kg</td></tr>
<tr><td>3 mm</td><td>11,5 kg</td><td>8,4 kg</td><td>7,0 kg</td><td>5,8 kg</td></tr>
<tr><td>4 mm</td><td>15,4 kg</td><td>11,2 kg</td><td>9,3 kg</td><td>7,7 kg</td></tr>
</tbody>
</table>

<p><em>Not: Yukarıdaki değerler standart DKP sac (yoğunluk 7,85 g/cm³) için yaklaşık değerlerdir. Kesin hesap için <a href="/hesaplama.php">hesaplama motorumuzu</a> kullanın.</em></p>

<h2>Genişletilmiş Sac Çeşitleri ve Malzeme Seçenekleri</h2>

<h3>1. DKP Genişletilmiş Sac</h3>
<p>Soğuk haddelenmiş, düşük karbonlu çelikten üretilir. Yüzeyi pürüzsüz, kaynaklanabilir ve boyanabilir. Genel inşaat ve sanayi uygulamalarında en çok tercih edilen tiptir.</p>

<h3>2. HRP (Sıcak Haddelenmiş) Genişletilmiş Sac</h3>
<p>Sıcak haddelenmiş çelikten üretilir, daha kalın levha aralığında (3-8 mm) tercih edilir. Endüstriyel platformlar, ağır yük gridleri için uygundur.</p>

<h3>3. Galvaniz Genişletilmiş Sac</h3>
<p>Daldırma galvaniz (DDG) uygulamasıyla korozyon direnci kazandırılmıştır. <strong>Açık hava uygulamalarında</strong> 25-50 yıl ömür sunar. Çatı, cephe, çitler için idealdir.</p>

<h3>4. Paslanmaz Genişletilmiş Sac (304 / 316)</h3>
<p>Asit, baz ve yüksek nem ortamlarında dayanıklı. Gıda işleme tesisleri, kimya sanayisi, deniz uygulamaları için tercih edilir. <strong>304 paslanmaz</strong> standart kullanım, <strong>316L</strong> deniz suyu/kimyasal direnç.</p>

<h3>5. Alüminyum Genişletilmiş Sac</h3>
<p>%70 daha hafif, korozyona doğal dayanım. Mimari cephe, tavan kaplama, hafif filtre ve dekoratif uygulamalar için. Boyanabilir veya anodize edilmiş hâli mevcuttur.</p>

<h2>Genişletilmiş Sac Kullanım Alanları</h2>

<h3>İnşaat ve Yapı</h3>
<ul>
<li><strong>Yürüme yolları (cat walks):</strong> Endüstriyel platformlarda kaymaz zemin</li>
<li><strong>Merdiven basamakları:</strong> Yangın merdivenleri, dış mekan basamak</li>
<li><strong>Mimari cephe:</strong> Modern bina cephe kaplaması, ışık geçirgen panel</li>
<li><strong>Çit ve bariyer:</strong> Endüstriyel saha, otoyol, demiryolu</li>
<li><strong>Asma tavan:</strong> Akustik perforasyon, klima geçirgenlik</li>
</ul>

<h3>Sanayi ve Endüstri</h3>
<ul>
<li><strong>Filtreleme sistemleri:</strong> Hava, yağ, su filtreleri</li>
<li><strong>Konveyör platformları:</strong> Bant taşıyıcı koruma kapakları</li>
<li><strong>Makine korumaları:</strong> CNC tezgâh güvenlik gridleri</li>
<li><strong>Havalandırma:</strong> HVAC kanal kapakları, fan koruma</li>
<li><strong>Patoz sacı:</strong> Tarım makineleri, harman ekipmanları</li>
</ul>

<h3>Otomotiv ve Ulaşım</h3>
<ul>
<li>Kamyon kasası tabanı (kaymaz yüzey)</li>
<li>Römork iç döşeme</li>
<li>Konteyner havalandırma panelleri</li>
<li>Gemi yürüme yolları (paslanmaz tip)</li>
</ul>

<h3>Mimari ve Dekoratif</h3>
<ul>
<li>Modern villa cephe paneli</li>
<li>Bahçe pergola ve gölgelik</li>
<li>İç mekan separator (oda ayırıcı)</li>
<li>Restoran-ofis dekoratif duvar paneli</li>
</ul>

<h2>Avantajları</h2>
<ul>
<li><strong>Hafif ama dayanıklı:</strong> Düz saca göre %30-50 daha hafif, eşdeğer mukavemet</li>
<li><strong>Tek parça yapısı:</strong> Kaynaksız, montajsız — yapısal zayıf nokta yok</li>
<li><strong>Hava-ışık-su geçirgen:</strong> Doğal havalandırma, görüş açıklığı</li>
<li><strong>Kaymaz yüzey:</strong> Yağlı/ıslak ortamda iş güvenliği sağlar</li>
<li><strong>Modüler montaj:</strong> Vidalama, kaynak, klipsleme — esnek bağlantı</li>
<li><strong>Geri dönüştürülebilir:</strong> %100 metal — sürdürülebilir malzeme</li>
<li><strong>Maliyet avantajı:</strong> Eşit alanı kaplayan düz saca göre %20-40 daha ekonomik</li>
</ul>

<h2>Genişletilmiş Sac Fiyatları (2026)</h2>

<p>Genişletilmiş sac fiyatları; <strong>malzeme cinsi, kalınlık, hücre boyutu ve sipariş miktarına</strong> göre değişir. Fiyat netliği için lütfen <a href="/iletisim.php">satış ekibimizle iletişime geçin</a>.</p>

<h3>Fiyatı Belirleyen Faktörler</h3>
<ul>
<li><strong>Hammadde:</strong> Paslanmaz > Alüminyum > Galvaniz > DKP > HRP (pahalıdan ucuza)</li>
<li><strong>Kalınlık:</strong> Her milimetre artış kg ağırlığını ve dolayısıyla fiyatı etkiler</li>
<li><strong>Hücre boyutu:</strong> Küçük açıklık (15×30) daha pahalı — daha çok metal</li>
<li><strong>Yüzey işlemi:</strong> Boya, galvaniz, anodize ek maliyet</li>
<li><strong>Sipariş miktarı:</strong> 100+ levha siparişlerde kademeli indirim</li>
<li><strong>Sevkiyat mesafesi:</strong> Konya merkezli — Türkiye geneli sevkiyat hesabı</li>
</ul>

<h2>Genişletilmiş Sac Sipariş Süreci</h2>

<ol>
<li><strong>Talep iletme:</strong> Telefon (0 332 342 24 52), WhatsApp veya <a href="/iletisim.php">iletişim formu</a> üzerinden ölçü, kalınlık, miktar bilgisini iletin</li>
<li><strong>Teklif:</strong> 2 saat içinde KDV dahil/hariç fiyat teklifi gönderilir</li>
<li><strong>Onay ve avans:</strong> Sözleşme imzalanır, %30 avans alınır (cari ilişkilerde gerekmez)</li>
<li><strong>Üretim/Stok hazırlığı:</strong> Stoklu ürünler aynı gün, üretim 3-5 iş günü</li>
<li><strong>Sevkiyat:</strong> Konya''dan Türkiye geneline anlaşmalı nakliye ile</li>
<li><strong>Teslim ve fatura:</strong> Sevk irsaliyesi imzası sonrası e-fatura kesilir</li>
</ol>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Genişletilmiş sacı düz hâle getirebilir miyim?</h4>
<p>Evet. Yükseltilmiş (raised) genişletilmiş sac, soğuk presleme ile düzleştirilebilir. Düzleştirilmiş (flattened) tip stoğumuzda da mevcuttur.</p>

<h4>Genişletilmiş saç m² fiyatı ne kadar?</h4>
<p>Mevcut hammadde fiyatlarına göre değişir. Standart 2 mm DKP / 30×60 hücre / 1000×2000 mm levha için 2026 yılı yaklaşık fiyat 280-380 TL/m² aralığındadır. Anlık fiyat için iletişime geçin.</p>

<h4>Genişletilmiş sac mı, delikli sac mı kullanmalıyım?</h4>
<p>Genişletilmiş sac kaynaksız tek parça olduğu için yapısal mukavemet daha yüksektir. Delikli sac (perforated) ise estetik açıdan daha düzgün desenli, hassas filtreleme için tercih edilir. Kullanım amacına göre değişir.</p>

<h4>Türkiye dışına ihracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan ve Türkmenistan</strong>''a düzenli sevkiyatımız vardır. Gümrük belgeleri ve menşei şahadetnamesi tarafımızca düzenlenir.</p>

<h4>Galvaniz genişletilmiş sac kaç yıl dayanır?</h4>
<p>Sıcak daldırma galvaniz uygulamalı genişletilmiş sac, açık hava şartlarında <strong>25-50 yıl</strong> korozyona dayanır. Deniz kenarı ortamlarda paslanmaz 316 öneririz.</p>

<h2>Tekcan Metal Genişletilmiş Sac Avantajı</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong> (2005''ten bu yana)</li>
<li>✅ <strong>Üretici sertifikalı</strong> Erdemir, Borçelik, Tosyalı tedarik</li>
<li>✅ <strong>Geniş stok</strong> — 5 standart ölçü her zaman hazır</li>
<li>✅ <strong>Aynı gün sevkiyat</strong> (saat 09:00 öncesi sipariş)</li>
<li>✅ <strong>Lazer kesim atölyesi</strong> — özel desenler, montaj delikleri</li>
<li>✅ <strong>81 il sevkiyat</strong> + ihracat (Irak, Suriye, Azerbaycan, Türkmenistan)</li>
<li>✅ <strong>e-Fatura, MASAK uyumu, vergi şeffaflığı</strong></li>
</ul>

<p class="cta-block"><strong>Genişletilmiş sac fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/genisletilmis-sac-rehberi.jpg',
    'Tekcan Metal',
    'Genişletilmiş Sac Ölçüleri, Ağırlık Hesaplama, Fiyat — Tekcan Metal',
    'Genişletilmiş sac ölçüleri, ağırlık tablosu, çeşitleri (DKP, HRP, galvaniz, paslanmaz, alüminyum), kullanım alanları ve fiyat hesaplama. Konya merkezli Tekcan Metal — 20+ yıl tecrübe, aynı gün sevkiyat.',
    NOW(),
    0,
    1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- 3) Baklava Sac süper sayfa
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'baklava-sac-rehberi',
    'Baklava Sac (Baklavalı Saç): Ölçüler, Ağırlık, Çeşitler ve Fiyatlar',
    'Baklava sac (elmas/kareli sac) çeşitleri (DKP, HRP, paslanmaz, alüminyum, galvaniz), standart ölçüler, ağırlık hesaplama, kullanım alanları. Tekcan Metal Konya — aynı gün sevkiyat, lazer kesim.',
    '<p class="lead"><strong>Baklava sac</strong> (baklavalı saç, elmas sac veya kareli saç olarak da bilinir), bir yüzü düz diğer yüzü baklava deseninde kabarık çıkıntılı, <strong>kaymaz yüzeyli yapısal çelik levhadır</strong>. Tekcan Metal olarak Konya merkezimizden Türkiye geneline DKP, paslanmaz ve alüminyum baklava sac tedariği gerçekleştiriyoruz.</p>

<h2>Baklava Sac Nedir? Yapısı ve Tanımı</h2>

<p>Baklava sac, sıcak haddeleme sürecinin son aşamasında özel desen merdaneleri arasından geçirilen çelik levhanın bir yüzünde <strong>baklava (elmas) şeklinde yükseltilmiş çıkıntılar</strong> oluşturulan üründür. Diğer yüzü düz kalır. Baklava deseni; sürtünme katsayısını artırarak yüzeyi <strong>kaymaz</strong> hale getirir.</p>

<p>Sektörde "<strong>checker plate</strong>", "<strong>tear plate</strong>" veya "<strong>diamond plate</strong>" olarak da bilinir. İngilizce belgelerde EN 10025-2 ve ASTM A786/A786M standartlarında tanımlanır.</p>

<h2>Baklava Sac Çeşitleri ve Malzemeleri</h2>

<h3>1. DKP Baklava Sac (Soğuk Haddelenmiş)</h3>
<p>Düşük karbonlu çelikten üretilir, <strong>2-8 mm kalınlık</strong> aralığında en çok tercih edilen tiptir. Boyanabilir, kaynaklanabilir ve esnek yapıdadır. İç mekan platformları, merdivenler için uygundur.</p>

<h3>2. HRP Baklava Sac (Sıcak Haddelenmiş)</h3>
<p>Sıcak haddelenmiş çelikten üretilir, <strong>3-12 mm kalınlık</strong> aralığında bulunur. Daha yüksek mukavemet gerektiren ağır endüstriyel uygulamalar (fabrika zemini, vinç platformları) için tercih edilir.</p>

<h3>3. Paslanmaz Baklava Sac (304 / 316)</h3>
<p>Korozyon direnci kritik olan ortamlar için. <strong>Gıda işleme tesisleri, kimya sanayisi, deniz uygulamaları</strong>, yat ve gemi platformlarında kullanılır. 304 standart, 316L deniz suyu/asit direnci için.</p>

<h3>4. Alüminyum Baklava Sac</h3>
<p>%70 daha hafif, doğal korozyon direnci. Karavan, römork, gezi teknesi, mimari uygulamalar için. Genelde 1,5-6 mm kalınlıkta üretilir.</p>

<h3>5. Galvaniz Baklava Sac</h3>
<p>Sıcak daldırma galvaniz kaplamalı. Açık hava uygulamaları (yangın merdivenleri, sundurma zemini, dış platform) için 25+ yıl ömürlü çözüm.</p>

<h2>Baklava Sac Standart Ölçüleri</h2>

<table>
<thead>
<tr><th>Levha Boyutu (mm)</th><th>Kalınlık Aralığı</th><th>Yaklaşık Ağırlık (DKP)</th><th>Tipik Uygulama</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>2 - 4 mm</td><td>32 - 64 kg</td><td>İç mekan basamak, kapak</td></tr>
<tr><td>1250 × 2500</td><td>3 - 6 mm</td><td>75 - 150 kg</td><td>Endüstriyel platform</td></tr>
<tr><td>1500 × 3000</td><td>4 - 8 mm</td><td>144 - 288 kg</td><td>Ağır yük sahneler, kamyon kasası</td></tr>
<tr><td>2000 × 6000</td><td>5 - 12 mm</td><td>485 - 1166 kg</td><td>Fabrika zemini, vinç pisti</td></tr>
<tr><td>Özel ölçü</td><td>2 - 12 mm</td><td>Talebe göre</td><td>Lazer/oksijen kesim ile</td></tr>
</tbody>
</table>

<h2>Baklava Sac Ağırlık Hesaplama</h2>

<p>Baklava sacın <strong>m² ağırlığı</strong>, baz sac kalınlığından biraz daha fazladır (yüzeydeki çıkıntılar nedeniyle yaklaşık <strong>%5-8 ek ağırlık</strong>). Standart hesap formülü:</p>

<p><strong>Ağırlık (kg/m²) = Kalınlık (mm) × Yoğunluk (g/cm³) × 1,06</strong></p>

<h3>Pratik Ağırlık Tablosu (DKP / 7,85 g/cm³)</h3>

<table>
<thead>
<tr><th>Kalınlık</th><th>kg/m²</th><th>1×2 m levha</th><th>1,25×2,5 m levha</th></tr>
</thead>
<tbody>
<tr><td>2 mm</td><td>16,6 kg</td><td>33 kg</td><td>52 kg</td></tr>
<tr><td>2,5 mm</td><td>20,8 kg</td><td>42 kg</td><td>65 kg</td></tr>
<tr><td>3 mm</td><td>25,0 kg</td><td>50 kg</td><td>78 kg</td></tr>
<tr><td>4 mm</td><td>33,3 kg</td><td>67 kg</td><td>104 kg</td></tr>
<tr><td>5 mm</td><td>41,6 kg</td><td>83 kg</td><td>130 kg</td></tr>
<tr><td>6 mm</td><td>49,9 kg</td><td>100 kg</td><td>156 kg</td></tr>
<tr><td>8 mm</td><td>66,6 kg</td><td>133 kg</td><td>208 kg</td></tr>
</tbody>
</table>

<p><em>Not: Gerçek ağırlık desen yüksekliğine göre %2-3 değişebilir. Hassas hesap için <a href="/hesaplama.php">online hesaplama motorumuzu</a> kullanın.</em></p>

<h2>Baklava Sac Kullanım Alanları</h2>

<h3>Endüstriyel Zemin Uygulamaları</h3>
<ul>
<li><strong>Fabrika zemini ve makine altı:</strong> Yağ-su damlası ortamında kaymaz koruma</li>
<li><strong>Yükleme rampaları:</strong> Forklift, transpalet erişim noktaları</li>
<li><strong>Yürüme yolları:</strong> Endüstriyel platformlar, çelik konstrüksiyon zeminleri</li>
<li><strong>Vinç ve kreyn pist:</strong> Operatör güvenlik platformu</li>
</ul>

<h3>Otomotiv ve Ulaşım</h3>
<ul>
<li><strong>Kamyon kasası tabanı:</strong> Standart - kaymaz yük zemin</li>
<li><strong>Pickup arka kasa:</strong> Dayanım + estetik desen</li>
<li><strong>Otobüs basamağı:</strong> Yolcu güvenliği — kaymaz</li>
<li><strong>Tekne/yat platformu:</strong> Paslanmaz tip — deniz ortamı</li>
<li><strong>Karavan / römork zemin:</strong> Alüminyum tip — hafiflik kritik</li>
</ul>

<h3>Yapı ve İnşaat</h3>
<ul>
<li><strong>Yangın merdiveni basamağı:</strong> Yönetmelik gereği kaymaz</li>
<li><strong>Asansör boşluğu zemin:</strong> Yapısal koruma</li>
<li><strong>Garaj ve otopark rampa:</strong> Yağmur-kar ortamında</li>
<li><strong>Yükleme platformları:</strong> Depo, dağıtım merkezleri</li>
<li><strong>Endüstriyel kapı eşiği:</strong> Forklift trafiği</li>
</ul>

<h3>Dekoratif ve Mimari</h3>
<ul>
<li>Bar ve restaurant tezgah önü</li>
<li>Endüstriyel tarz duvar paneli</li>
<li>Loft tasarım merdiven basamağı</li>
<li>Garaj kapı paneli (dış cephe)</li>
</ul>

<h2>Avantajları</h2>

<ul>
<li><strong>Kaymaz yüzey:</strong> Baklava deseni sürtünme katsayısını %35-50 artırır</li>
<li><strong>Yüksek dayanım:</strong> Düz saça göre eşit kalınlıkta daha rijit</li>
<li><strong>Bakım gerektirmez:</strong> Boyalı/galvanizli tipler 20+ yıl bakımsız</li>
<li><strong>Korozyon dayanımı:</strong> Galvaniz/paslanmaz tipte uzun ömür</li>
<li><strong>Estetik:</strong> Modern endüstriyel görünüm sağlar</li>
<li><strong>Kolay temizlik:</strong> Düz arka yüz nedeniyle alt taraf temizlenir</li>
<li><strong>Kaynak ve montaj:</strong> Standart kaynak yöntemleriyle birleştirilebilir</li>
</ul>

<h2>Baklava Sac Fiyatları (2026)</h2>

<p>Baklava sac fiyatları <strong>malzeme cinsi, kalınlık, levha ölçüsü ve sipariş miktarına</strong> göre belirlenir. Anlık fiyat için <a href="/iletisim.php">iletişim sayfamız</a> üzerinden teklif alabilirsiniz.</p>

<h3>Fiyat Sıralaması (Pahalıdan Ucuza)</h3>
<ol>
<li>Paslanmaz 316L baklava sac</li>
<li>Paslanmaz 304 baklava sac</li>
<li>Alüminyum baklava sac</li>
<li>Galvaniz baklava sac</li>
<li>HRP baklava sac (sıcak haddelenmiş)</li>
<li>DKP baklava sac (soğuk haddelenmiş — en ekonomik)</li>
</ol>

<p>Genellikle <strong>3 mm DKP baklava sac</strong> en çok tercih edilen ekonomik standart üründür.</p>

<h2>Baklava Sac vs Genişletilmiş Sac — Hangisini Seçmeli?</h2>

<table>
<thead>
<tr><th>Özellik</th><th>Baklava Sac</th><th>Genişletilmiş Sac</th></tr>
</thead>
<tbody>
<tr><td>Yapı</td><td>Tam dolu, üst yüzü desenli</td><td>Açık delikli, hava-ışık geçirgen</td></tr>
<tr><td>Ağırlık</td><td>Daha ağır</td><td>%30-50 daha hafif</td></tr>
<tr><td>Kaymazlık</td><td>Çok iyi (yüksek desen)</td><td>İyi (kenar profili)</td></tr>
<tr><td>Hava geçişi</td><td>Yok</td><td>Var (filtre etkisi)</td></tr>
<tr><td>Yük taşıma</td><td>Çok yüksek</td><td>Orta-yüksek</td></tr>
<tr><td>Maliyet</td><td>Daha ekonomik</td><td>Hücre boyutuna göre değişir</td></tr>
<tr><td>Tipik kullanım</td><td>Zemin, basamak, kasa tabanı</td><td>Cephe, filtre, dekoratif</td></tr>
</tbody>
</table>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Baklava sac kaç yıl dayanır?</h4>
<p>Boyalı DKP iç mekanda 15+ yıl, sıcak daldırma galvaniz açık havada 25+ yıl, paslanmaz 304 ise <strong>50+ yıl</strong> ömür sunar.</p>

<h4>Baklava sac kaynaklanabilir mi?</h4>
<p>Evet. MIG, MAG ve elektrik ark kaynağıyla kaynaklanabilir. Paslanmaz tipler için TIG kaynağı önerilir.</p>

<h4>Baklava sac kesim hizmeti veriyor musunuz?</h4>
<p>Evet. Tekcan Metal atölyesinde <a href="/hizmet/lazer-kesim">lazer kesim</a> ve <a href="/hizmet/oksijen-kesim">oksijen kesim</a> ile özel ölçüde baklava sac kesimi yapılır. Aynı gün sevkiyat seçeneği vardır.</p>

<h4>Baklava sacı boyayabilir miyim?</h4>
<p>Evet. DKP/HRP tipleri elektrostatik toz boya, akrilik veya yağlı boya ile boyanabilir. Galvaniz tip boyamadan kullanılır.</p>

<h4>Hangi kalınlığı seçmeliyim?</h4>
<p>Yaya yürüme yolu için 3 mm yeterli. Forklift/transpalet trafiği için 5-6 mm önerilir. Vinç altı/ağır endüstri için 8-12 mm gerekebilir.</p>

<h4>İhracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan, Türkmenistan</strong>''a düzenli baklava sac sevkiyatı yapıyoruz. Gümrük dokümantasyonu tarafımızca düzenlenir.</p>

<h2>Tekcan Metal Baklava Sac Avantajları</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong></li>
<li>✅ <strong>Erdemir, Borçelik, Tosyalı, Habaş</strong> üretici sertifikalı tedarik</li>
<li>✅ <strong>Stoklu satış</strong> — 5 standart ölçüde her zaman hazır</li>
<li>✅ <strong>Aynı gün sevkiyat</strong> (saat 09:00 öncesi siparişlerde)</li>
<li>✅ <strong>Lazer + oksijen kesim atölyesi</strong> — özel ölçü kesim</li>
<li>✅ <strong>81 il sevkiyat ağı</strong> + uluslararası ihracat</li>
<li>✅ <strong>e-Fatura, üretici sertifikası, kalite belgeleri</strong> dahil</li>
</ul>

<p class="cta-block"><strong>Baklava sac fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/baklava-sac-rehberi.jpg',
    'Tekcan Metal',
    'Baklava Sac (Baklavalı Saç) Ölçüleri, Ağırlık ve Fiyat — Tekcan Metal',
    'Baklava sac (baklavalı saç, elmas sac) ölçüleri, ağırlık tablosu, çeşitleri (DKP, HRP, paslanmaz, alüminyum), kullanım alanları ve fiyat. Tekcan Metal — Konya merkezli, 20+ yıl, 81 il sevkiyat.',
    NOW(),
    0,
    1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- =====================================================
-- v1.0.67 — site_url düzeltmesi
-- v2.tekcanmetal.com kaldırıldı, ana domain'e geri dönüldü
-- =====================================================
UPDATE tm_settings
   SET setting_value = 'https://tekcanmetal.com'
 WHERE setting_key = 'site_url'
   AND setting_value LIKE '%v2.tekcanmetal%';

-- =====================================================
-- v1.0.69 — SEO Süper Sayfalar Bölüm 2: Galvanizli + Trapez + Kalın Levha
-- v1.0.66'daki Genişletilmiş + Baklava sac patternine eklenir.
-- Toplam 5 yüksek-değer SEO blog sayfası DB'de
-- =====================================================

-- 1) Galvanizli Sac süper sayfa
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'galvanizli-sac-rehberi',
    'Galvanizli Sac: Çeşitleri (Z140-Z600), Ölçüler, Ağırlık ve Kullanım Alanları',
    'Galvanizli sac (DDG, EZ, Galvalume) çeşitleri, kaplama sınıfları (Z100-Z600), kalite sınıfları (DX51D-DX56D, S250GD-S550GD), ağırlık tablosu, kullanım alanları ve fiyat. Tekcan Metal — Konya merkezli, 20+ yıl, 81 il sevkiyat.',
    '<p class="lead"><strong>Galvanizli sac</strong>, çelik yüzeyine sıcak daldırma veya elektrolitik yöntemle <strong>çinko (Zn) kaplaması</strong> uygulanmış, korozyona yüksek dayanımlı yapısal çelik levhadır. Tekcan Metal olarak 2005''ten bu yana Konya merkezimizden Türkiye geneline DDG (Sıcak Daldırma Galvaniz) ve Elektrostatik Galvaniz sac tedariği gerçekleştiriyoruz.</p>

<h2>Galvanizli Sac Nedir? Yapısı ve Üretim Yöntemleri</h2>

<p>Galvanizli sac, baz çelik levhanın <strong>460°C''de erimiş çinko banyosundan</strong> geçirilerek her iki yüzünün de ince bir çinko tabakası ile kaplanmasıyla üretilir. Bu işlem sayesinde çelik, atmosferik korozyona, neme ve kimyasal etkilere karşı 25-50 yıl koruma altına alınır.</p>

<h3>Üretim Yöntemleri</h3>

<h4>1. Sıcak Daldırma Galvaniz (DDG / Hot-Dip)</h4>
<p>Çelik levha; ön temizleme, asit yıkama ve flux uygulamasından sonra <strong>460°C erimiş çinko banyosuna</strong> daldırılır. Çıkışta soğuyan çinko tabakası baz çelikle metalurjik olarak bağlanır. Kaplama kalınlığı genellikle <strong>40-275 g/m²</strong> aralığındadır.</p>

<h4>2. Elektrolitik Galvaniz (EZ / Electro-Galvanized)</h4>
<p>Çelik levha çinko sülfat banyosunda elektrik akımıyla kaplanır. Daha ince ve düzgün yüzeyli (<strong>5-30 g/m²</strong>) kaplama elde edilir. Otomotiv ve beyaz eşya endüstrisinde tercih edilir.</p>

<h4>3. Galvalume (Aluzinc, AZ)</h4>
<p>%55 alüminyum + %43.5 çinko + %1.5 silisyum kaplama. <strong>Çatı ve cephe panelleri</strong> için, normal galvanizden 2-4 kat daha uzun ömür sunar.</p>

<h2>Galvanizli Sac Çeşitleri</h2>

<h3>Yüzey Karakterine Göre</h3>
<ul>
<li><strong>Normal Spangle (NS):</strong> Tipik kar tanesi deseni — en yaygın</li>
<li><strong>Minimum Spangle (MS):</strong> İnce desen, daha düzgün yüzey — boyamaya uygun</li>
<li><strong>Zero Spangle (ZS):</strong> Desensiz, pürüzsüz — yüksek kalite uygulamalar</li>
<li><strong>Skin-Pass:</strong> Soğuk haddelenip yumuşatılmış — şekillendirilebilirlik artar</li>
</ul>

<h3>Kaplama Sınıflarına Göre (EN 10346)</h3>

<table>
<thead>
<tr><th>Sınıf</th><th>Kaplama (g/m²)</th><th>Kaplama Kalınlığı</th><th>Kullanım Alanı</th></tr>
</thead>
<tbody>
<tr><td>Z100</td><td>100</td><td>~7 µm</td><td>İç mekan, kuru ortam</td></tr>
<tr><td>Z140</td><td>140</td><td>~10 µm</td><td>Standart kullanım</td></tr>
<tr><td>Z200</td><td>200</td><td>~14 µm</td><td>Hafif endüstriyel</td></tr>
<tr><td>Z275</td><td>275</td><td>~20 µm</td><td>Çatı, cephe, dış mekan</td></tr>
<tr><td>Z350</td><td>350</td><td>~25 µm</td><td>Ağır korozif ortam</td></tr>
<tr><td>Z450</td><td>450</td><td>~32 µm</td><td>Endüstriyel, kıyı bölgeleri</td></tr>
<tr><td>Z600</td><td>600</td><td>~42 µm</td><td>Maksimum korozyon direnci</td></tr>
</tbody>
</table>

<h3>Kalite Sınıflarına Göre (Çelik Cinsi)</h3>
<ul>
<li><strong>DX51D:</strong> Standart kalite — sıradan inşaat ve sanayi uygulamaları</li>
<li><strong>DX52D:</strong> Çekme/Bükme uygulamaları — orta şekillendirilebilirlik</li>
<li><strong>DX53D:</strong> Derin çekme — havalandırma kanalı, beyaz eşya</li>
<li><strong>DX54D:</strong> Ekstra derin çekme — otomotiv parçaları, kompleks form</li>
<li><strong>S250GD - S550GD:</strong> Yapısal galvaniz — yüksek mukavemet (kalkan paneller)</li>
</ul>

<h2>Galvanizli Sac Standart Ölçüleri</h2>

<table>
<thead>
<tr><th>Levha Boyutu (mm)</th><th>Kalınlık Aralığı</th><th>Yaklaşık Ağırlık (1mm)</th><th>Tipik Kullanım</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>0,30 - 4 mm</td><td>15,7 kg</td><td>Standart panel, dolap</td></tr>
<tr><td>1000 × 3000</td><td>0,40 - 4 mm</td><td>23,6 kg</td><td>Çatı kaplama</td></tr>
<tr><td>1250 × 2500</td><td>0,50 - 4 mm</td><td>24,5 kg</td><td>Endüstriyel panel</td></tr>
<tr><td>1500 × 3000</td><td>0,80 - 6 mm</td><td>35,3 kg</td><td>Ağır yapı, hangar</td></tr>
<tr><td>Rulo (coil)</td><td>0,20 - 4 mm</td><td>Talebe göre</td><td>Şerit kesim, prosesler</td></tr>
<tr><td>Özel ölçü</td><td>0,30 - 6 mm</td><td>Talebe göre</td><td>Lazer kesim ile</td></tr>
</tbody>
</table>

<h3>Standart Kalınlıklar</h3>
<p>Galvanizli sac kalınlıkları genellikle <strong>0,30 mm – 6 mm</strong> aralığındadır. En çok talep edilen kalınlıklar: 0,40 mm, 0,50 mm, 0,70 mm, 1 mm, 1,2 mm, 1,5 mm, 2 mm, 3 mm.</p>

<h2>Galvanizli Sac Ağırlık Hesaplama</h2>

<p>Galvanizli sac ağırlığı, baz çelik (7,85 g/cm³) + çinko kaplama (~7,14 g/cm³) yoğunluğuna göre hesaplanır. Pratik formül:</p>

<p><strong>Ağırlık (kg/m²) = Kalınlık (mm) × 7,85</strong></p>

<p><em>Çinko kaplaması ek %1-3 ağırlık katar, hassas hesap için <a href="/hesaplama.php">online hesaplama motorunu</a> kullanın.</em></p>

<h3>Pratik Ağırlık Tablosu (kg/m²)</h3>

<table>
<thead>
<tr><th>Kalınlık</th><th>kg/m²</th><th>1×2 m levha</th><th>1×3 m levha</th><th>1,25×2,5 m</th></tr>
</thead>
<tbody>
<tr><td>0,30 mm</td><td>2,36 kg</td><td>4,7 kg</td><td>7,1 kg</td><td>7,4 kg</td></tr>
<tr><td>0,40 mm</td><td>3,14 kg</td><td>6,3 kg</td><td>9,4 kg</td><td>9,8 kg</td></tr>
<tr><td>0,50 mm</td><td>3,93 kg</td><td>7,9 kg</td><td>11,8 kg</td><td>12,3 kg</td></tr>
<tr><td>0,70 mm</td><td>5,50 kg</td><td>11,0 kg</td><td>16,5 kg</td><td>17,2 kg</td></tr>
<tr><td>1 mm</td><td>7,85 kg</td><td>15,7 kg</td><td>23,6 kg</td><td>24,5 kg</td></tr>
<tr><td>1,5 mm</td><td>11,78 kg</td><td>23,6 kg</td><td>35,3 kg</td><td>36,8 kg</td></tr>
<tr><td>2 mm</td><td>15,70 kg</td><td>31,4 kg</td><td>47,1 kg</td><td>49,1 kg</td></tr>
<tr><td>3 mm</td><td>23,55 kg</td><td>47,1 kg</td><td>70,7 kg</td><td>73,6 kg</td></tr>
<tr><td>4 mm</td><td>31,40 kg</td><td>62,8 kg</td><td>94,2 kg</td><td>98,1 kg</td></tr>
</tbody>
</table>

<h2>Galvanizli Sac Kullanım Alanları</h2>

<h3>İnşaat ve Yapı</h3>
<ul>
<li><strong>Çatı kaplama panelleri:</strong> Trapez, sandviç panel, tek katmanlı çatı</li>
<li><strong>Dış cephe kaplaması:</strong> Endüstriyel bina, hangar, depo</li>
<li><strong>Çelik konstrüksiyon:</strong> Yapısal galvaniz (S350GD, S450GD)</li>
<li><strong>Çelik kapı ve pencere kasası:</strong> Korozyon dayanımlı çerçeve</li>
<li><strong>Kalkan panelleri:</strong> Hangar, fabrika dış cephe</li>
<li><strong>Yağmur oluğu ve iniş borusu:</strong> 25+ yıl ömür</li>
<li><strong>Çit ve bariyer:</strong> Endüstriyel saha çitleri, otoyol bariyerleri</li>
</ul>

<h3>HVAC ve Tesisat</h3>
<ul>
<li><strong>Havalandırma kanalı:</strong> DX52D-Z140, hijyenik tesisat</li>
<li><strong>Klima dış ünite muhafazası:</strong> Açık hava korozyon koruması</li>
<li><strong>Davlumbaz ve baca:</strong> Mutfak, endüstriyel duman tahliye</li>
<li><strong>Su deposu:</strong> İçme suyu sertifikalı galvaniz</li>
</ul>

<h3>Otomotiv ve Beyaz Eşya</h3>
<ul>
<li>Otomotiv gövde panelleri (DX54D - derin çekme)</li>
<li>Buzdolabı, çamaşır makinesi gövdesi</li>
<li>Otobüs kabini iç paneli</li>
<li>Kamyon kasası dış cephe</li>
</ul>

<h3>Mobilya ve Ofis</h3>
<ul>
<li>Çelik dolap, kasa</li>
<li>Server kabinleri</li>
<li>Endüstriyel raf sistemleri</li>
<li>Bilgisayar kasası iç parçalar</li>
</ul>

<h2>Avantajları</h2>

<ul>
<li><strong>Korozyon dayanımı:</strong> Çinko, çelikten önce oksitlenir (sacrificial protection) — 25-50 yıl ömür</li>
<li><strong>Kendi kendine onarım:</strong> Çizilen yüzeylerde çinko etrafa yayılır, çeliği korur</li>
<li><strong>Boyanabilir:</strong> Phosphate veya wash primer sonrası boya tutar</li>
<li><strong>Kaynaklanabilir:</strong> Punto, MIG, MAG kaynak yöntemleri uygulanabilir</li>
<li><strong>Geri dönüştürülebilir:</strong> %100 çelik+çinko geri kazanım</li>
<li><strong>Bakım gerektirmez:</strong> 25 yıl boyunca ek koruma uygulamasına gerek yok</li>
<li><strong>Maliyet etkin:</strong> Paslanmaz çeliğe göre %60-70 daha ekonomik, eşdeğer korozyon koruması</li>
<li><strong>Standart ölçü desteği:</strong> Geniş stok, hızlı tedarik</li>
</ul>

<h2>Galvanizli Sac vs Diğer Sac Tipleri</h2>

<table>
<thead>
<tr><th>Özellik</th><th>Galvanizli</th><th>Paslanmaz 304</th><th>Boyalı Sac</th><th>Soğuk Haddelenmiş (DKP)</th></tr>
</thead>
<tbody>
<tr><td>Korozyon dayanımı</td><td>Çok iyi (25-50 yıl)</td><td>Mükemmel (50+ yıl)</td><td>Orta (5-15 yıl)</td><td>Düşük (yağ korumalı)</td></tr>
<tr><td>Maliyet</td><td>Orta</td><td>Yüksek</td><td>Düşük-orta</td><td>Düşük</td></tr>
<tr><td>Boyanabilir</td><td>Evet (primer ile)</td><td>Zor</td><td>Hazır boyalı</td><td>Evet</td></tr>
<tr><td>Kaynaklanabilir</td><td>Evet (özel)</td><td>Evet (TIG)</td><td>Boya yanar</td><td>Çok iyi</td></tr>
<tr><td>Yüzey görünümü</td><td>Spangle desenli</td><td>Parlak/Mat</td><td>Renkli</td><td>Düz/Yağlı</td></tr>
<tr><td>Tipik kullanım</td><td>Çatı, cephe, HVAC</td><td>Gıda, kimya</td><td>Mobilya, beyaz eşya</td><td>İç mekan, prosesli</td></tr>
</tbody>
</table>

<h2>Galvanizli Sac Fiyatları (2026)</h2>

<p>Galvanizli sac fiyatları; <strong>kalınlık, kaplama sınıfı (Z100/Z275/Z450), kalite sınıfı (DX51D/DX54D), levha ölçüsü ve sipariş miktarı</strong> baz alınarak belirlenir.</p>

<h3>Fiyat Sıralaması (Pahalıdan Ucuza)</h3>
<ol>
<li><strong>Galvalume (Aluzinc):</strong> En pahalı — alüminyum içeriği</li>
<li><strong>Yapısal Galvaniz (S550GD):</strong> Yüksek mukavemet</li>
<li><strong>Kalın kaplama (Z450, Z600):</strong> Korozif ortam</li>
<li><strong>Standart Galvaniz (Z140-Z275):</strong> En yaygın — en ekonomik</li>
<li><strong>Elektrostatik Galvaniz (EZ):</strong> İnce kaplama, ek kontrol</li>
</ol>

<h3>Fiyatı Belirleyen Faktörler</h3>
<ul>
<li><strong>LME çinko fiyatı:</strong> Günlük borsa fiyatına göre değişir</li>
<li><strong>Çelik hammadde:</strong> HRC + ek işlem maliyetleri</li>
<li><strong>Kaplama kalınlığı:</strong> Z100 → Z600 arası %30-50 fiyat farkı</li>
<li><strong>Levha boyutu:</strong> Standart 1000×2000 en ekonomik</li>
<li><strong>Sipariş miktarı:</strong> 5+ ton siparişlerde toplu indirim</li>
<li><strong>Sevkiyat:</strong> Konya merkezli, 81 il sevkiyat hesabı</li>
</ul>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Galvanizli sac kaç yıl dayanır?</h4>
<p>Kaplama sınıfına ve kullanım ortamına bağlı: <strong>Z140 iç mekanda 50+ yıl</strong>, Z275 açık havada 25-40 yıl, Z450 endüstriyel ortamda 30+ yıl, Z600 kıyı/agresif ortamda 25+ yıl ömür sunar.</p>

<h4>Galvanizli sac kaynaklanabilir mi?</h4>
<p>Evet ama özel teknik gerektirir. Punto kaynağı, MIG/MAG (gazaltı) kaynak uygulanabilir. Kaynak sırasında çinko buharı çıkar, <strong>iyi havalandırma</strong> şart. Kaynak sonrası bölge tekrar galvanizli boya ile koruma altına alınmalıdır.</p>

<h4>Galvanizli sac üzerine boya tutar mı?</h4>
<p>Evet. Ancak doğrudan değil. Önce yüzey <strong>fosfat banyosu</strong> veya <strong>wash primer</strong> ile hazırlanır, sonra epoxy ya da poliüretan boya uygulanır. Hazır boyalı galvanizli sac (PPGI) de stoğumuzda mevcut.</p>

<h4>Z140 ile Z275 arasındaki fark nedir?</h4>
<p>Sayı, m² başına çinko ağırlığını gösterir. Z140 = 140 g/m², Z275 = 275 g/m². <strong>Z275 yaklaşık 2 kat kaplama</strong> demek, dolayısıyla 2-3 kat uzun ömür. Açık hava için Z275, iç mekan için Z140 önerilir.</p>

<h4>Galvanizli sac kesim hizmeti veriyor musunuz?</h4>
<p>Evet. Tekcan Metal atölyesinde <a href="/hizmet/lazer-kesim">lazer kesim</a> ve <a href="/hizmet/oksijen-kesim">oksijen kesim</a> ile özel ölçüde galvanizli sac kesimi yapılır. Aynı gün sevkiyat seçeneği vardır.</p>

<h4>Trapez galvanizli sac ile düz galvanizli sac farkı nedir?</h4>
<p>Düz galvanizli sac düz levha hâlinde, <strong>trapez galvanizli sac</strong> ise yapısal mukavemet için kıvrımlı (trapez profilli) hale getirilmiştir. Çatı ve cephe için trapez tercih edilir.</p>

<h4>İhracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan, Türkmenistan</strong>''a düzenli galvanizli sac sevkiyatı yapıyoruz. Üretici menşei şahadetnamesi ve gümrük dokümantasyonu tarafımızca düzenlenir.</p>

<h2>Tekcan Metal Galvanizli Sac Avantajları</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong></li>
<li>✅ <strong>Borçelik, Erdemir, Tosyalı, MMK Metalurji</strong> üretici sertifikalı tedarik</li>
<li>✅ <strong>Tüm kaplama sınıfları:</strong> Z100, Z140, Z200, Z275, Z350, Z450, Z600</li>
<li>✅ <strong>Tüm kalite sınıfları:</strong> DX51D - DX56D, S250GD - S550GD</li>
<li>✅ <strong>Stoklu satış</strong> + özel ölçü kesim</li>
<li>✅ <strong>Aynı gün sevkiyat</strong> (saat 09:00 öncesi siparişlerde)</li>
<li>✅ <strong>Lazer + oksijen kesim atölyesi</strong></li>
<li>✅ <strong>81 il sevkiyat ağı</strong> + uluslararası ihracat</li>
<li>✅ <strong>e-Fatura, üretici sertifikası, kalite belgeleri</strong> dahil</li>
</ul>

<p class="cta-block"><strong>Galvanizli sac fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/galvanizli-sac-rehberi.jpg',
    'Tekcan Metal',
    'Galvanizli Sac Çeşitleri, Ölçüleri, Ağırlık ve Fiyat — Tekcan Metal',
    'Galvanizli sac çeşitleri (Z140, Z275, Z450, Galvalume), kalite sınıfları (DX51D, DX54D, S350GD), ağırlık hesaplama tablosu, kullanım alanları, fiyat. Konya merkezli Tekcan Metal — 20+ yıl, aynı gün sevkiyat.',
    NOW(),
    0,
    1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- 2) Trapez Sac süper sayfa
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'trapez-sac-rehberi',
    'Trapez Sac (Oluklu Sac): TR-18, TR-27, TR-35, TR-45 Profilleri, Renkler ve Fiyatlar',
    'Trapez sac (oluklu sac, çatı sacı) çeşitleri TR-18 / TR-27 / TR-35 / TR-45 / TR-55 / TR-100, RAL renkleri (3009 bordo, 7016 antrasit, 8017 kahve), galvanizli + boyalı + galvalume seçenekleri, mahya/vida/aksesuar. Konya merkezli Tekcan Metal.',
    '<p class="lead"><strong>Trapez sac</strong> (oluklu sac veya kanal sac olarak da bilinir), düz galvanizli/boyalı çelik levhanın özel rulo formlama makinesinde <strong>trapezoidal (yamuk) profil</strong> hâline getirilmiş, çatı ve cephe kaplamalarında en yaygın kullanılan endüstriyel sac türüdür. Tekcan Metal olarak 2005''ten bu yana Konya merkezimizden Türkiye geneline trapez sac tedariği gerçekleştiriyoruz.</p>

<h2>Trapez Sac Nedir? Yapısı ve Üretimi</h2>

<p>Trapez sac, düz galvanizli ya da boyalı sac levhanın <strong>seri rulo formlama (roll forming)</strong> makinesinden geçirilerek belirli aralıklarla yamuk şekilli yükseltiler oluşturulan üründür. Trapezoidal profil sayesinde sac, kendi ağırlığının yanında ek yapısal mukavemet kazanır — daha geniş açıklıkları taşıyabilir.</p>

<p>Sektörde "<strong>oluklu sac</strong>", "<strong>çatı sacı</strong>", "<strong>profil sac</strong>" veya İngilizce "<strong>corrugated sheet</strong>" olarak da bilinir.</p>

<h2>Trapez Sac Çeşitleri (Profile Yüksekliğine Göre)</h2>

<h3>1. TR-18 (Düşük Profil)</h3>
<p><strong>Profil yüksekliği:</strong> 18 mm | <strong>Etkin genişlik:</strong> 1062 mm | <strong>Toplam genişlik:</strong> 1100 mm<br>
Hafif yapı, dekoratif çatı ve duvar uygulamaları için. Konut ve villa projelerinde estetik tercih.</p>

<h3>2. TR-27 (Standart)</h3>
<p><strong>Profil yüksekliği:</strong> 27 mm | <strong>Etkin genişlik:</strong> 1075 mm<br>
En yaygın kullanılan tip. <strong>3-5 metre açıklık</strong> taşıyabilir. Konut, küçük endüstriyel yapı, ahır, ambar.</p>

<h3>3. TR-35 / TR-40 (Orta Profil)</h3>
<p><strong>Profil yüksekliği:</strong> 35-40 mm | <strong>Etkin genişlik:</strong> 1000-1080 mm<br>
Endüstriyel çatı ve cephe uygulamaları. <strong>5-7 metre açıklık</strong> taşır. Hangar, fabrika, depo, market kurulumu.</p>

<h3>4. TR-45 / TR-55 (Yüksek Profil)</h3>
<p><strong>Profil yüksekliği:</strong> 45-55 mm | <strong>Etkin genişlik:</strong> 800-960 mm<br>
Ağır yapı çatı sistemleri. <strong>7-12 metre açıklık</strong> taşıyabilir. Büyük ölçekli sanayi tesisleri, lojistik depolar.</p>

<h3>5. TR-75 / TR-100 (Mega Profil — Sandviç Panel İçin)</h3>
<p><strong>Profil yüksekliği:</strong> 75-100 mm | <strong>Etkin genişlik:</strong> 600-800 mm<br>
Sandviç panel iç-dış yüzey sacı, soğuk hava deposu, gıda fabrikası. Polyurethane (PU) veya polystyrene (EPS) izolasyon ile kombinlenir.</p>

<h3>6. Sinüs (Dalgalı) Trapez Sac</h3>
<p>Trapezoidal değil, sinüs eğrili. Yarı dekoratif uygulamalar, retro mimari, kıyı bölgeleri için.</p>

<h2>Trapez Sac Malzeme Seçenekleri</h2>

<h3>Galvanizli Trapez Sac</h3>
<p>Sıcak daldırma galvaniz kaplamalı (Z140-Z275). <strong>En ekonomik</strong> seçenek. 25-40 yıl ömür. Renksiz, gümüş gri görünüm. Hangar, depo, geçici yapı, ahır için ideal.</p>

<h3>Boyalı (Renkli) Trapez Sac (PPGI)</h3>
<p>Pre-Painted Galvanized Iron — <strong>fabrika çıkışlı renkli</strong> (5-25 mikron PE veya PVDF boya). RAL renk kataloğundan seçim. Konut, ticari yapı, ofis, AVM, restoran, villa için. <strong>20-30 yıl renk dayanımı</strong>.</p>

<h3>Galvalume Trapez Sac (Aluzinc, AZ)</h3>
<p>%55 alüminyum + %43.5 çinko kaplama. <strong>Galvanizden 2-4 kat uzun ömür</strong> (50+ yıl). Kıyı bölgeleri, korozif endüstriyel ortam, yüksek nem.</p>

<h3>Paslanmaz Trapez Sac</h3>
<p>304 veya 316 paslanmaz çelik. <strong>Gıda fabrikası, kimya tesisi, asit üretim sahası</strong> gibi agresif ortamlar için.</p>

<h3>Alüminyum Trapez Sac</h3>
<p>Hafif, doğal korozyon dayanımı. Karavan, römork, hafif çatı uygulamaları için. Boyalı veya anodize halleri mevcut.</p>

<h2>Trapez Sac Standart Renkleri (RAL)</h2>

<table>
<thead>
<tr><th>RAL Kodu</th><th>Renk Adı</th><th>Tipik Kullanım</th></tr>
</thead>
<tbody>
<tr><td>RAL 9002</td><td>Gri Beyaz</td><td>Konut, ofis</td></tr>
<tr><td>RAL 9006</td><td>Beyaz Alüminyum</td><td>Endüstriyel</td></tr>
<tr><td>RAL 3000</td><td>Bayrak Kırmızısı</td><td>Tarımsal yapı</td></tr>
<tr><td>RAL 3009</td><td>Oksit Kırmızısı (Bordo)</td><td>Çatı — en popüler</td></tr>
<tr><td>RAL 5005</td><td>Sinyal Mavisi</td><td>Endüstriyel logo renkleri</td></tr>
<tr><td>RAL 5010</td><td>Aralık Mavisi</td><td>Hangar, fabrika</td></tr>
<tr><td>RAL 6005</td><td>Yosun Yeşili</td><td>Tarım, ahır</td></tr>
<tr><td>RAL 6020</td><td>Krom Yeşili</td><td>Doğa uyumu</td></tr>
<tr><td>RAL 7016</td><td>Antrasit Gri</td><td>Modern villa, lüks konut</td></tr>
<tr><td>RAL 8004</td><td>Bakır Kahve</td><td>Geleneksel yapı</td></tr>
<tr><td>RAL 8017</td><td>Çikolata Kahve</td><td>Konut</td></tr>
<tr><td>RAL 9005</td><td>Trafik Siyah</td><td>Modern mimari</td></tr>
</tbody>
</table>

<h2>Trapez Sac Standart Ölçüleri</h2>

<table>
<thead>
<tr><th>Boy</th><th>Açıklama</th></tr>
</thead>
<tbody>
<tr><td>2-12 metre</td><td>Standart boy aralığı (50 cm artırımlarla)</td></tr>
<tr><td>İstenen ölçü</td><td>Sipariş üzerine kesim (rulo açma)</td></tr>
</tbody>
</table>

<p><strong>Standart kalınlıklar:</strong> 0,30 mm | 0,40 mm | 0,50 mm | 0,60 mm | 0,70 mm | 0,80 mm | 1 mm | 1,2 mm</p>

<p><em>Not: Trapez sac, her zaman sabit profil genişliğinde üretilir. Sadece boy değişir. Genişlik kesimi (ek profil) yapılmaz — bu trapez sacın yapısal özelliğini bozar.</em></p>

<h2>Trapez Sac Ağırlık Hesaplama</h2>

<p>Trapez sac ağırlığı, baz galvanizli sacın kalınlığına göre hesaplanır. <strong>Profil etkisiyle %5-10 ek alan</strong> oluşur (yükseltiler nedeniyle).</p>

<h3>Pratik Ağırlık Tablosu (kg/m² etkin alan)</h3>

<table>
<thead>
<tr><th>Kalınlık</th><th>TR-18</th><th>TR-27</th><th>TR-35</th><th>TR-45</th></tr>
</thead>
<tbody>
<tr><td>0,40 mm</td><td>3,3 kg</td><td>3,5 kg</td><td>3,7 kg</td><td>3,9 kg</td></tr>
<tr><td>0,50 mm</td><td>4,1 kg</td><td>4,4 kg</td><td>4,6 kg</td><td>4,9 kg</td></tr>
<tr><td>0,60 mm</td><td>5,0 kg</td><td>5,3 kg</td><td>5,6 kg</td><td>5,9 kg</td></tr>
<tr><td>0,70 mm</td><td>5,8 kg</td><td>6,1 kg</td><td>6,5 kg</td><td>6,8 kg</td></tr>
<tr><td>0,80 mm</td><td>6,6 kg</td><td>7,0 kg</td><td>7,4 kg</td><td>7,8 kg</td></tr>
<tr><td>1,00 mm</td><td>8,3 kg</td><td>8,8 kg</td><td>9,3 kg</td><td>9,8 kg</td></tr>
</tbody>
</table>

<p><em>Hassas hesaplama için <a href="/hesaplama.php">online ağırlık hesaplama motorumuzu</a> kullanın.</em></p>

<h2>Trapez Sac Kullanım Alanları</h2>

<h3>Çatı Kaplama</h3>
<ul>
<li><strong>Konut çatısı:</strong> RAL 3009 bordo, RAL 7016 antrasit, RAL 8017 kahve</li>
<li><strong>Endüstriyel çatı:</strong> Hangar, fabrika, depo, atölye</li>
<li><strong>Tarımsal yapı:</strong> Ahır, ambar, sera, samanlık</li>
<li><strong>Ticari yapı:</strong> AVM, market, restaurant, ofis binası</li>
<li><strong>Geçici yapı:</strong> Şantiye, prefabrik, mobil ofis</li>
</ul>

<h3>Cephe Kaplama</h3>
<ul>
<li><strong>Endüstriyel cephe:</strong> Modern fabrika dış cephesi (TR-35, TR-45)</li>
<li><strong>Showroom ve mağaza:</strong> Otomotiv galerisi, kafe</li>
<li><strong>Spor tesisleri:</strong> Halı saha, kapalı pazar yeri</li>
<li><strong>Sandviç panel sistemleri:</strong> İzolasyonlu cephe — soğuk hava deposu</li>
</ul>

<h3>Diğer Uygulamalar</h3>
<ul>
<li>Otopark üst örtüsü</li>
<li>Bahçe seperatorü, çit kaplaması</li>
<li>Kapı, pencere, panjur dış kaplaması</li>
<li>Endüstriyel kasa, makine muhafazası</li>
<li>Geri dönüşüm konteyner kapağı</li>
</ul>

<h2>Trapez Sac Aksesuarları</h2>

<p>Trapez sac sistem komple çözüm gerektirir. Tedarik ettiğimiz aksesuarlar:</p>

<ul>
<li><strong>Mahya:</strong> Çatı tepe birleşim profili (60 cm × 200 cm standart)</li>
<li><strong>Yan kapatma:</strong> Çatı kenarı, saçak profili</li>
<li><strong>Su yutucu:</strong> Çatı yağmur drenajı</li>
<li><strong>Bel profili:</strong> Yatay birleşim</li>
<li><strong>Vida:</strong> Self-drilling, EPDM contalı (renkli kafa)</li>
<li><strong>Conta bantı:</strong> Sızdırmazlık (EPDM, butil)</li>
<li><strong>Köpük dolgu:</strong> Trapez profil arası akustik/termal yalıtım</li>
<li><strong>Cıvata-rondela seti:</strong> EPDM contalı renkli galvaniz vidalama</li>
<li><strong>Yağmur oluğu ve iniş borusu:</strong> Eşleşen RAL renkte</li>
</ul>

<h2>Avantajları</h2>

<ul>
<li><strong>Yapısal mukavemet:</strong> Profil sayesinde geniş açıklık taşır — daha az iskelet</li>
<li><strong>Hızlı montaj:</strong> Vidalama yöntemi — kaynak gerekmez</li>
<li><strong>Hafif:</strong> Geleneksel kiremit/beton plak çatıya göre %80-90 daha hafif</li>
<li><strong>Ekonomik:</strong> M² fiyatı en düşük çatı malzemesi</li>
<li><strong>Renk seçeneği:</strong> 12+ standart RAL renk + özel renk</li>
<li><strong>Uzun ömür:</strong> Boyalı 20-30 yıl, galvalume 50+ yıl</li>
<li><strong>Hava dayanımı:</strong> -40°C ile +80°C arası deformasyon yok</li>
<li><strong>Yangın direnci:</strong> A1 sınıfı — yanıcı değil</li>
<li><strong>Geri dönüşüm:</strong> %100 metal — sürdürülebilir</li>
<li><strong>Kolay nakliye:</strong> Plaka olarak istiflenir, vinçle hızlı yükleme</li>
</ul>

<h2>Trapez Sac Fiyatları (2026)</h2>

<p>Trapez sac fiyatları; <strong>kalınlık, malzeme cinsi (galvanizli/boyalı/galvalume), profil tipi, boy ve sipariş miktarı</strong> baz alınarak belirlenir.</p>

<h3>Fiyat Sıralaması (Pahalıdan Ucuza)</h3>
<ol>
<li>Paslanmaz trapez (304/316)</li>
<li>Galvalume / Aluzinc trapez</li>
<li>Boyalı (PPGI) trapez — Premium boyalar (PVDF)</li>
<li>Boyalı (PPGI) trapez — Standart (PE) boyalar</li>
<li>Galvanizli trapez — En ekonomik</li>
</ol>

<h3>Fiyatı Belirleyen Faktörler</h3>
<ul>
<li><strong>Kalınlık:</strong> 0,30 → 1,00 mm arası %200+ fark</li>
<li><strong>Profil yüksekliği:</strong> TR-18 → TR-100 arası %30-50 fark</li>
<li><strong>Boy:</strong> Standart 2-6 metre en ekonomik, 6+ metre özel kesim ek maliyet</li>
<li><strong>Renk:</strong> RAL 9006/9002 (beyaz) standart, özel RAL ek %5-10</li>
<li><strong>Boya kalitesi:</strong> PE (5 mikron) ekonomik, PVDF (25 mikron) premium</li>
<li><strong>Sipariş miktarı:</strong> 1+ ton siparişlerde toplu indirim</li>
<li><strong>Sevkiyat:</strong> Konya merkezli, 81 il sevkiyat hesabı</li>
</ul>

<h2>Trapez Sac Sipariş Süreci</h2>

<ol>
<li><strong>Talep iletme:</strong> Çatı ölçüleri (en × boy), eğim açısı, açıklık (mertek aralığı), istenen renk ve kalınlık</li>
<li><strong>Mühendislik kontrolü:</strong> Sektör tecrübemizle önerilen profil tipi (TR-27/TR-35/TR-45) ve kalınlık önerisi</li>
<li><strong>Detaylı teklif:</strong> Sac + aksesuar (mahya, vida, conta) komple liste</li>
<li><strong>Onay ve avans:</strong> Sözleşme sonrası %30 avans (cari ilişkide gerekmez)</li>
<li><strong>Üretim:</strong> Standart renkler 24 saat, özel renkler 5-7 iş günü</li>
<li><strong>Sevkiyat:</strong> Açık kasa kamyon ile (vinç gerektiren büyük gönderiler için ön planlamayla)</li>
<li><strong>Teslim:</strong> Sevk irsaliyesi imzası sonrası e-fatura</li>
</ol>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Trapez sac kaç yıl dayanır?</h4>
<p>Galvanizli trapez 25-40 yıl, boyalı (PE) trapez 20-25 yıl, boyalı (PVDF) trapez 30+ yıl, galvalume trapez 50+ yıl ömür sunar. Yıllık bakım gerektirmez.</p>

<h4>Hangi profil tipini seçmeliyim?</h4>
<p>Mertek (kiriş) açıklığına göre: 3 metreye kadar TR-27, 5 metreye kadar TR-35, 7 metreye kadar TR-45, 12 metreye kadar TR-55 önerilir. Cephe için estetik açıdan TR-18/TR-27 yaygındır.</p>

<h4>Hangi kalınlığı seçmeliyim?</h4>
<p>Hafif konut çatısı için 0,40-0,50 mm, endüstriyel çatı için 0,50-0,70 mm, kar yükü ağır bölgeler için 0,70-1 mm önerilir. Cephe için 0,40-0,50 mm yeterli.</p>

<h4>Hangi RAL renkler stoğunuzda var?</h4>
<p>RAL 3009 (oksit kırmızısı), RAL 7016 (antrasit gri), RAL 8017 (çikolata kahve), RAL 6020 (krom yeşili), RAL 9002 (gri beyaz), RAL 9005 (trafik siyahı) — bu 6 renk her zaman stoğumuzdadır. Diğer RAL renkler 5-7 iş günü içinde tedarik edilir.</p>

<h4>Trapez sac altına ne döşenir?</h4>
<p>İzolasyon için cam yünü (camyünü), taş yünü, polyurethane (PU) köpük veya XPS kullanılır. Su yalıtımı için membran. Sandviç panel sistemde izolasyon iki sac arasında entegre.</p>

<h4>Trapez sac kesim hizmeti veriyor musunuz?</h4>
<p>Evet. <strong>Boy kesimi</strong> (uzunluk) standart hizmettir. Genişlik kesimi yapılmaz — trapez profili bozulur. Özel açı kesimleri için <a href="/hizmet/lazer-kesim">lazer kesim atölyemizden</a> faydalanabilirsiniz.</p>

<h4>İhracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan, Türkmenistan</strong>''a düzenli trapez sac sevkiyatı yapıyoruz. Gümrük dokümantasyonu ve menşei şahadetnamesi tarafımızca düzenlenir.</p>

<h2>Tekcan Metal Trapez Sac Avantajları</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong></li>
<li>✅ <strong>Borçelik, Erdemir, Tezcan, Pasiform</strong> üretici sertifikalı tedarik</li>
<li>✅ <strong>Tüm profiller:</strong> TR-18, TR-27, TR-35, TR-45, TR-55, TR-75, TR-100</li>
<li>✅ <strong>12+ stoklu RAL renk</strong> — özel renk tedariği 5-7 gün</li>
<li>✅ <strong>Komple aksesuar:</strong> Mahya, vida, conta, oluk dahil tek adresten</li>
<li>✅ <strong>Mühendislik desteği:</strong> Açıklık ve kalınlık önerisi ücretsiz</li>
<li>✅ <strong>81 il sevkiyat ağı</strong> + uluslararası ihracat</li>
<li>✅ <strong>e-Fatura, üretici sertifikası, kalite belgeleri</strong> dahil</li>
</ul>

<p class="cta-block"><strong>Trapez sac fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/trapez-sac-rehberi.jpg',
    'Tekcan Metal',
    'Trapez Sac Çatı Cephe Profilleri, RAL Renkleri ve Fiyat — Tekcan Metal',
    'Trapez sac (oluklu sac) profilleri TR-18, TR-27, TR-35, TR-45, TR-55, TR-100 — galvanizli, boyalı (PPGI), galvalume seçenekleri. RAL 12+ renk stokta, mahya + vida + aksesuar dahil. Konya merkezli Tekcan Metal — 20+ yıl, 81 il sevkiyat.',
    NOW(),
    0,
    1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- 3) Kalın Levha Sac süper sayfa
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'kalin-levha-sac-rehberi',
    'Kalın Levha Sac (Slab): S235-S460 Kalite Sınıfları, 5-100 mm Kalınlık, Ağırlık ve Fiyat',
    'Kalın levha sac (slab, plate) kalite sınıfları S235JR / S275JR / S355J2 / S420N / S460N / P355GH, 5-100 mm kalınlık, EN 10025 / EN 10028 / ASTM A36 / A572 / A516 standartları. Köprü, gemi, basınç kabı, ağır endüstri uygulamaları. Konya merkezli Tekcan Metal.',
    '<p class="lead"><strong>Kalın levha sac</strong> (slab veya kalın çelik levha olarak da bilinir), kalınlığı <strong>5 mm – 200 mm</strong> arasında değişen, sıcak haddelenmiş yüksek mukavemetli yapısal çelik levhadır. Tekcan Metal olarak 2005''ten bu yana Konya merkezimizden Türkiye geneline kalın levha sac tedariği gerçekleştiriyoruz.</p>

<h2>Kalın Levha Sac Nedir?</h2>

<p>Kalın levha sac, sektörel olarak <strong>"slab"</strong>, <strong>"plate"</strong> veya <strong>"kalın çelik plaka"</strong> olarak da adlandırılır. <strong>5 mm üzeri</strong> kalınlıktaki yassı haddelenmiş çelik ürünler bu kategoriye girer. EN 10029 ve EN 10025 standartlarında üretilirler.</p>

<p>İnce sac (<5 mm) ile karşılaştırıldığında kalın levha sac:</p>
<ul>
<li>Çok daha yüksek <strong>yapısal mukavemet</strong> taşır</li>
<li><strong>Ağır endüstriyel</strong> uygulamalar için uygundur</li>
<li>Sıcak haddeleme yöntemiyle üretilir (soğuk haddeleme değil)</li>
<li>Genelde yüzeyi <strong>oksitli/karbonlu</strong> görünümdedir (yağlı değil)</li>
</ul>

<h2>Kalın Levha Sac Kalınlık Aralıkları</h2>

<h3>Sınıflandırma</h3>

<table>
<thead>
<tr><th>Kategori</th><th>Kalınlık Aralığı</th><th>Tipik Kullanım</th></tr>
</thead>
<tbody>
<tr><td>Orta kalın</td><td>5 - 12 mm</td><td>Genel konstrüksiyon, çelik konstrüksiyon</td></tr>
<tr><td>Standart kalın</td><td>12 - 25 mm</td><td>Köprü, yapısal çelik, gemi inşası</td></tr>
<tr><td>Kalın levha</td><td>25 - 50 mm</td><td>Basınç kabı, ağır makina, vinç parçaları</td></tr>
<tr><td>Ekstra kalın</td><td>50 - 100 mm</td><td>Petrokimya, nükleer, savunma</td></tr>
<tr><td>Süper kalın</td><td>100 - 200 mm</td><td>Özel endüstriyel uygulama</td></tr>
</tbody>
</table>

<h2>Kalın Levha Sac Standart Ölçüleri</h2>

<table>
<thead>
<tr><th>En × Boy (mm)</th><th>Kalınlık Aralığı</th><th>Tipik Kullanım</th></tr>
</thead>
<tbody>
<tr><td>1500 × 6000</td><td>5 - 80 mm</td><td>Standart endüstriyel</td></tr>
<tr><td>2000 × 6000</td><td>5 - 100 mm</td><td>Köprü, gemi parçaları</td></tr>
<tr><td>2500 × 8000</td><td>10 - 120 mm</td><td>Petrokimya basınçlı kap</td></tr>
<tr><td>2500 × 12000</td><td>10 - 150 mm</td><td>Büyük ölçekli yapısal</td></tr>
<tr><td>3000 × 12000</td><td>15 - 200 mm</td><td>Maksimum boyut, özel imal</td></tr>
<tr><td>Özel ölçü</td><td>Talebe göre</td><td>Oksijen kesim ile</td></tr>
</tbody>
</table>

<h2>Kalın Levha Sac Kalite Sınıfları</h2>

<h3>Genel Yapısal (EN 10025-2)</h3>
<ul>
<li><strong>S235JR / S235J0 / S235J2:</strong> Genel inşaat — en yaygın</li>
<li><strong>S275JR / S275J0:</strong> Orta mukavemet — köprü, yapısal çelik</li>
<li><strong>S355JR / S355J2 / S355K2:</strong> Yüksek mukavemet — ağır endüstriyel</li>
<li><strong>S420N / S420NL:</strong> Çok yüksek mukavemet — özel uygulamalar</li>
<li><strong>S460N / S460NL:</strong> Ultra yüksek mukavemet — petrokimya, deniz</li>
</ul>

<h3>Basınçlı Kap (EN 10028)</h3>
<ul>
<li><strong>P235GH / P265GH / P295GH:</strong> Yüksek sıcaklık servisi — kazan, basınç kabı</li>
<li><strong>P355GH / P420GH:</strong> Yüksek basınç + sıcaklık — petrokimya, enerji</li>
</ul>

<h3>Gemi İnşası (Lloyd''s, ABS, DNV onaylı)</h3>
<ul>
<li><strong>GR.A, GR.B, GR.D, GR.E:</strong> Gemi gövdesi sınıflar</li>
<li><strong>AH32, DH32, EH32:</strong> Yüksek mukavemet gemi sacı</li>
<li><strong>AH36, DH36, EH36:</strong> Ekstra yüksek mukavemet</li>
</ul>

<h3>ASTM Standartları (Amerikan)</h3>
<ul>
<li><strong>ASTM A36:</strong> Genel yapısal çelik (S275JR muadili)</li>
<li><strong>ASTM A572 Gr.50:</strong> Yüksek mukavemet (S355 muadili)</li>
<li><strong>ASTM A516 Gr.70:</strong> Basınç kabı çeliği</li>
<li><strong>ASTM A283 Gr.C:</strong> Düşük/orta gerilme yapı çeliği</li>
</ul>

<h2>Kalın Levha Sac Ağırlık Hesaplama</h2>

<p>Kalın levha sac ağırlığı standart çelik yoğunluğu (7,85 g/cm³) baz alınarak hesaplanır:</p>

<p><strong>Ağırlık (kg) = En (m) × Boy (m) × Kalınlık (mm) × 7,85</strong></p>

<h3>Pratik Ağırlık Tablosu (kg/m²)</h3>

<table>
<thead>
<tr><th>Kalınlık</th><th>kg/m²</th><th>1,5×6 m levha</th><th>2×6 m levha</th><th>2,5×8 m levha</th></tr>
</thead>
<tbody>
<tr><td>5 mm</td><td>39,3 kg</td><td>353 kg</td><td>471 kg</td><td>785 kg</td></tr>
<tr><td>8 mm</td><td>62,8 kg</td><td>565 kg</td><td>754 kg</td><td>1.256 kg</td></tr>
<tr><td>10 mm</td><td>78,5 kg</td><td>707 kg</td><td>942 kg</td><td>1.570 kg</td></tr>
<tr><td>12 mm</td><td>94,2 kg</td><td>848 kg</td><td>1.130 kg</td><td>1.884 kg</td></tr>
<tr><td>15 mm</td><td>117,8 kg</td><td>1.060 kg</td><td>1.413 kg</td><td>2.355 kg</td></tr>
<tr><td>20 mm</td><td>157 kg</td><td>1.413 kg</td><td>1.884 kg</td><td>3.140 kg</td></tr>
<tr><td>25 mm</td><td>196 kg</td><td>1.766 kg</td><td>2.355 kg</td><td>3.925 kg</td></tr>
<tr><td>30 mm</td><td>235,5 kg</td><td>2.120 kg</td><td>2.826 kg</td><td>4.710 kg</td></tr>
<tr><td>40 mm</td><td>314 kg</td><td>2.826 kg</td><td>3.768 kg</td><td>6.280 kg</td></tr>
<tr><td>50 mm</td><td>392,5 kg</td><td>3.533 kg</td><td>4.710 kg</td><td>7.850 kg</td></tr>
<tr><td>60 mm</td><td>471 kg</td><td>4.239 kg</td><td>5.652 kg</td><td>9.420 kg</td></tr>
<tr><td>80 mm</td><td>628 kg</td><td>5.652 kg</td><td>7.536 kg</td><td>12.560 kg</td></tr>
<tr><td>100 mm</td><td>785 kg</td><td>7.065 kg</td><td>9.420 kg</td><td>15.700 kg</td></tr>
</tbody>
</table>

<p><em>Hassas hesap için <a href="/hesaplama.php">online ağırlık hesaplama motorumuzu</a> kullanın.</em></p>

<h2>Kalın Levha Sac Kullanım Alanları</h2>

<h3>Çelik Konstrüksiyon</h3>
<ul>
<li><strong>Çelik yapı kolonları:</strong> H profil + flanş plakaları için kalın levha</li>
<li><strong>Köprü tabliyesi:</strong> Karayolu ve demiryolu köprü uygulamaları</li>
<li><strong>Vinç ana kirişleri:</strong> Üst kalkan ve alt flanş plakaları</li>
<li><strong>Stadyum çatı sistemi:</strong> Büyük açıklıklı yapısal eleman</li>
<li><strong>Yüksek bina çelik iskeleti:</strong> Düşey kolon, yatay kiriş bağlantı plakaları</li>
</ul>

<h3>Ağır Makine ve Endüstri</h3>
<ul>
<li><strong>Vinç gövdesi:</strong> Tower crane, mobile crane, gantry crane</li>
<li><strong>İş makinesi şasi:</strong> Ekskavatör, kepçe, dozer ana çelik gövde</li>
<li><strong>Hidrolik pres tablası:</strong> 1000+ ton hidrolik makina</li>
<li><strong>Konveyör band ana platformu:</strong> Maden, çimento, demir-çelik fabrika</li>
<li><strong>Endüstriyel kalıp tablası:</strong> Pres, döküm, dövme uygulamaları</li>
</ul>

<h3>Gemi İnşası ve Denizcilik</h3>
<ul>
<li><strong>Gemi gövdesi (hull):</strong> Tanker, kargo, yolcu gemisi</li>
<li><strong>Petrol platformu:</strong> Açık deniz çelik strüktürü</li>
<li><strong>Liman vinçleri:</strong> Konteyner taşıma vinçleri</li>
<li><strong>Yat ve yarış teknesi:</strong> Yapısal güçlendirme plakaları</li>
</ul>

<h3>Petrokimya ve Enerji</h3>
<ul>
<li><strong>Basınçlı kazan:</strong> P235GH, P355GH levha</li>
<li><strong>Buhar kazanı:</strong> Termal santral, fabrika buharı</li>
<li><strong>Petrol depolama tankı:</strong> 10.000+ ton kapasite</li>
<li><strong>Boru hattı vana ve flanş:</strong> Yüksek basınç gaz/petrol hatları</li>
<li><strong>Reaktör cidarı:</strong> Petrol rafinerisi, kimya tesisi</li>
</ul>

<h3>Tarım ve İş Makineleri</h3>
<ul>
<li>Traktör şasisi</li>
<li>Hasat makinesi ana iskelet</li>
<li>Patos sacı (silaj kıyıcı, harman)</li>
<li>Tank treyleri ana plaka</li>
</ul>

<h3>Savunma ve Askeri</h3>
<ul>
<li>Zırhlı araç gövde plakaları (özel zırh çeliği)</li>
<li>Gemi savaş ekipmanı</li>
<li>Mühimmat depo cidarı</li>
<li>Köprü inşa askeri yapı</li>
</ul>

<h2>Kalın Levha Sac Kesim Yöntemleri</h2>

<h3>1. Oksijen Kesim (Otojen)</h3>
<p>Tekcan Metal''de en yaygın yöntem. <strong>5 mm – 100 mm</strong> aralığında ekonomik ve hassas kesim. <a href="/hizmet/oksijen-kesim">Oksijen kesim atölyemizde</a> CNC kontrollü, ±2 mm hassasiyet.</p>

<h3>2. Plazma Kesim</h3>
<p>5-50 mm kalınlık aralığı. Daha hızlı, daha temiz kesim. CNC plazma sistemleri.</p>

<h3>3. Lazer Kesim (12 mm''ye kadar)</h3>
<p>İnce-orta kalın levhalarda <strong>±0,1 mm hassasiyet</strong>. <a href="/hizmet/lazer-kesim">Fiber lazer atölyemizde</a> 12 mm''ye kadar.</p>

<h3>4. Su Jeti Kesim</h3>
<p>Termal etki istemeyen özel uygulamalar için. Maliyet yüksek, kalite mükemmel.</p>

<h3>5. Sürtünme Disk (Testere) Kesim</h3>
<p>Düz kesim, basit projelerde 100+ mm kalınlık için.</p>

<h2>Avantajları</h2>

<ul>
<li><strong>Yüksek mukavemet:</strong> Yapısal yük taşıma kapasitesi maksimum</li>
<li><strong>Kaynak edilebilirlik:</strong> S235-S460 sınıfları kolayca kaynaklanır</li>
<li><strong>Şekillendirilebilir:</strong> Sıcak ve soğuk şekillendirme uygulanabilir</li>
<li><strong>Standart stoklu:</strong> 5-100 mm kalınlık aralığında geniş stok</li>
<li><strong>Sertifikalı kalite:</strong> EN 10204 3.1 / 3.2 sertifikaları</li>
<li><strong>Çoklu kesim seçeneği:</strong> Oksijen, plazma, lazer, su jeti</li>
<li><strong>Yüksek dayanıklılık:</strong> 50+ yıl yapısal ömür</li>
<li><strong>Ekonomik:</strong> Birim mukavemet başına en uygun maliyet</li>
</ul>

<h2>Kalın Levha Sac Fiyatları (2026)</h2>

<p>Kalın levha sac fiyatları; <strong>kalite sınıfı, kalınlık, levha boyutu ve sipariş miktarı</strong> baz alınarak belirlenir.</p>

<h3>Fiyat Sıralaması (Pahalıdan Ucuza)</h3>
<ol>
<li>Özel yüksek mukavemet (S690, AHSS sınıfları)</li>
<li>Aşınmaya dayanıklı (Hardox, Wear plate)</li>
<li>Basınç kabı (P355GH, P460N)</li>
<li>Yüksek mukavemet (S420, S460)</li>
<li>Yapısal (S355 - en popüler)</li>
<li>Standart (S275 - genel inşaat)</li>
<li>Genel kullanım (S235JR - en ekonomik)</li>
</ol>

<h3>Fiyatı Belirleyen Faktörler</h3>
<ul>
<li><strong>Çelik fiyatı:</strong> LME ve Türkiye İhracatçılar Birliği fiyatlarına göre</li>
<li><strong>Kalınlık:</strong> 5-100 mm arası ton fiyatı %10-30 değişir</li>
<li><strong>Levha boyutu:</strong> Standart 1,5×6 m en ekonomik</li>
<li><strong>Yüzey işlemi:</strong> Ham, primer-boyalı, paslanmaz seçenekleri</li>
<li><strong>Sertifika seviyesi:</strong> EN 10204 2.2 vs 3.1 vs 3.2 fiyat farklı</li>
<li><strong>Sipariş miktarı:</strong> 5+ ton siparişlerde toplu indirim</li>
<li><strong>Sevkiyat:</strong> Ağır yük taşıma — vinç, kamyon</li>
</ul>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Kalın levha sac kaç ton tonajla satılır?</h4>
<p>Genelde minimum sipariş 1 ton (1.000 kg). Standart bir 2×6m levha 30 mm kalınlıkta zaten ~2,8 ton. Daha küçük miktarlar için <strong>kesim hizmetiyle</strong> kupon halinde tedarik edilir.</p>

<h4>Hangi kalite sınıfını seçmeliyim?</h4>
<p>Genel inşaat için <strong>S235JR / ASTM A36</strong>; köprü ve yapısal çelik için <strong>S355J2 / ASTM A572</strong>; basınçlı kap için <strong>P355GH / ASTM A516 Gr.70</strong>; ağır endüstriyel makina için S460 önerilir.</p>

<h4>Kalın levha sac üretici sertifikası verir misiniz?</h4>
<p>Evet. Tüm kalın levha sevkiyatlarımızda <strong>üretici sertifikası (Mill Test Certificate, EN 10204 3.1)</strong> dahildir. 3.2 sertifikası (3. taraf onaylı) talebe göre temin edilir.</p>

<h4>Kalın levhayı oksijen kesimle keser misiniz?</h4>
<p>Evet. <a href="/hizmet/oksijen-kesim">CNC oksijen kesim atölyemizde</a> 5-100 mm aralığında, ±2 mm hassasiyetle özel ölçü kesim yapıyoruz. DXF/DWG dosyanızı gönderin, aynı gün üretime alalım.</p>

<h4>Kaç gün içinde teslim edebilirsiniz?</h4>
<p>Stoklu sınıflar (S235, S275, S355) <strong>aynı gün</strong> sevkiyatla. Özel sipariş (S420, basınç kabı, gemi sacı) <strong>10-15 iş günü</strong>. Yurtdışı menşeli özel kalite <strong>30-45 gün</strong>.</p>

<h4>İhracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan, Türkmenistan</strong>''a düzenli kalın levha sac sevkiyatı yapıyoruz. Üretici menşei şahadetnamesi, TSE/EN sertifikaları ve gümrük dokümantasyonu dahildir.</p>

<h2>Tekcan Metal Kalın Levha Sac Avantajları</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong></li>
<li>✅ <strong>Erdemir, Kardemir, İçdaş, Tosyalı</strong> üretici sertifikalı tedarik</li>
<li>✅ <strong>Tüm kalite sınıfları:</strong> S235, S275, S355, S420, S460</li>
<li>✅ <strong>Basınç kabı sınıfları:</strong> P235GH, P265GH, P355GH</li>
<li>✅ <strong>5-100 mm geniş kalınlık stoğu</strong></li>
<li>✅ <strong>EN 10204 3.1 / 3.2 üretici sertifikaları</strong> dahil</li>
<li>✅ <strong>Oksijen + plazma + lazer kesim</strong> atölyeleri</li>
<li>✅ <strong>Vinç servisi:</strong> Ağır yük yükleme/boşaltma desteği</li>
<li>✅ <strong>81 il sevkiyat ağı</strong> + uluslararası ihracat</li>
<li>✅ <strong>Mühendislik desteği:</strong> Kalite sınıfı + kalınlık önerisi ücretsiz</li>
</ul>

<p class="cta-block"><strong>Kalın levha sac fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/kalin-levha-sac-rehberi.jpg',
    'Tekcan Metal',
    'Kalın Levha Sac (Slab) Kalite Sınıfları, Kalınlık ve Fiyat — Tekcan Metal',
    'Kalın levha sac (slab) S235JR, S275JR, S355J2, S420N, S460N, P355GH kalite sınıfları, 5-100 mm kalınlık, EN 10025 / ASTM A36 / A572 standartları. Oksijen + plazma + lazer kesim, EN 10204 3.1 sertifika. Konya merkezli Tekcan Metal — 20+ yıl tecrübe.',
    NOW(),
    0,
    1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- =====================================================
-- v1.0.70 — Erişilebilirlik (a11y) iyileştirmeleri
-- PageSpeed Insights raporu: 89 → 95+ hedef
-- =====================================================

-- Footer phone aria-label çevirileri (4 dilde)
INSERT INTO tm_translations (lang, `key`, `value`) VALUES
  ('tr', 'footer.aria_landline', 'Sabit hat:'),
  ('en', 'footer.aria_landline', 'Landline:'),
  ('ar', 'footer.aria_landline', 'الخط الثابت:'),
  ('ru', 'footer.aria_landline', 'Стационарный телефон:'),
  ('tr', 'footer.aria_mobile', 'Cep telefonu:'),
  ('en', 'footer.aria_mobile', 'Mobile phone:'),
  ('ar', 'footer.aria_mobile', 'الهاتف المحمول:'),
  ('ru', 'footer.aria_mobile', 'Мобильный телефон:')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- =====================================================
-- v1.0.70 — KAPSAMLI SEO + KRİTİK HOTFIX
-- =====================================================
-- 1) ÖNEMLİ HOTFIX: tekman-metal landing sayfası kaldırıldı
--    Sebep: Tekman Metal Ltd. Şti. (Merzifon, Amasya — 1980'den beri)
--    KÖKLÜ ve AYRI bir markadır. Marka tecavüzü/yanıltıcı reklam riski.
--    Bu sürümde tüm ilgili kayıtlar nötr duruma getiriliyor.
-- 2) 3 YENİ SEO SÜPER SAYFA: Delikli Sac + Galvanizli Boru + Ferforje
-- 3) DKP/Siyah/HRP Sac SEO meta zenginleştirme

-- ===== 1) Delikli Sac süper sayfa =====
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'delikli-sac-rehberi',
    'Delikli Sac (Perforated Sheet): Çeşitleri, Ölçüler, Açıklık Oranları ve Fiyatlar',
    'Delikli sac (perforated sheet) çeşitleri (yuvarlak, kare, slot, altıgen), DKP/galvaniz/paslanmaz/alüminyum malzeme seçenekleri, standart ölçü tabloları, açıklık oranları ve kullanım alanları. Tekcan Metal — Konya merkezli, CNC pres + lazer kesim atölyesi.',
    '<p class="lead"><strong>Delikli sac</strong> (perforated sheet, perforated metal), düz metal levhanın CNC kontrolü ile özel kalıplar veya laser sistemleriyle <strong>düzenli aralıklarla delinmesiyle</strong> elde edilen, hassas hava-ışık-akışkan geçirgenliği sağlayan endüstriyel sac türüdür. Tekcan Metal olarak 2005''ten bu yana Konya merkezimizden Türkiye geneline DKP, paslanmaz, alüminyum ve galvanizli delikli sac tedariği gerçekleştiriyoruz.</p>

<h2>Delikli Sac Nedir? Yapısı ve Üretim Süreci</h2>

<p>Delikli sac, düz galvanizli/DKP/paslanmaz/alüminyum bir levhaya CNC pres veya lazer makinesi ile <strong>belirli geometrik desende düzenli delikler açılarak</strong> üretilir. Genişletilmiş sactan farklı olarak <strong>delikler kesilip çıkarılır</strong>, bu sayede düzgün, hassas ve simetrik bir geçirgenlik elde edilir.</p>

<p>Sektörde "<strong>perforated sheet</strong>", "<strong>perforated metal</strong>", "<strong>punching sac</strong>" veya "<strong>elek sac</strong>" olarak da bilinir.</p>

<h3>Üretim Yöntemleri</h3>
<ol>
<li><strong>CNC pres delikleme:</strong> Çoklu kalıp + servo motorla saniyede 100+ delik. Standart delikler için en ekonomik yöntem.</li>
<li><strong>Lazer delikleme:</strong> Özel desenler, 0.1 mm hassasiyet. Hassas filtreleme uygulamaları için.</li>
<li><strong>Su jeti delikleme:</strong> Termal etki istemeyen özel uygulamalar.</li>
<li><strong>Photo-etching (asit aşındırma):</strong> Mikro-delikli ürünler, 0.05 mm aralıklı yüksek hassasiyet.</li>
</ol>

<h2>Delikli Sac Çeşitleri (Delik Geometrisine Göre)</h2>

<h3>1. Yuvarlak Delikli Sac (Round Hole)</h3>
<p>En yaygın tip. Yuvarlak delikler eşit aralıklarla. <strong>RV (60° kare) ve RU (90° staggered)</strong> diziliminde olabilir. Filtre, akustik panel, dekoratif uygulamalar.</p>

<h3>2. Kare Delikli Sac (Square Hole)</h3>
<p>Kare delikler. <strong>Maksimum açıklık alanı</strong> sağlar. Eleme, sınıflandırma, havalandırma uygulamaları.</p>

<h3>3. Slot Delikli Sac (Dikdörtgen)</h3>
<p>Dikdörtgen şeklinde uzun delikler. Yatay/dikey diziliş. Hava akış kontrolü, akustik hassas filtreleme.</p>

<h3>4. Altıgen Delikli Sac (Hexagonal)</h3>
<p>Bal peteği görünümü. Estetik mimari uygulamalar, tasarım panelleri, modern cephe sistemleri.</p>

<h3>5. Özel Desenli Delikli Sac</h3>
<p>Yıldız, üçgen, oval veya logo şeklinde delikler. <strong>Lazer kesim</strong> ile özel CAD tasarımları.</p>

<h2>Delikli Sac Standart Ölçüleri</h2>

<table>
<thead>
<tr><th>Levha Boyutu (mm)</th><th>Kalınlık Aralığı</th><th>Delik Çapı</th><th>Tipik Açıklık Oranı</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>0,5 - 5 mm</td><td>1 - 25 mm</td><td>%20 - %50</td></tr>
<tr><td>1250 × 2500</td><td>1 - 6 mm</td><td>2 - 30 mm</td><td>%25 - %55</td></tr>
<tr><td>1500 × 3000</td><td>1,5 - 8 mm</td><td>3 - 40 mm</td><td>%30 - %60</td></tr>
<tr><td>Özel ölçü</td><td>0,5 - 10 mm</td><td>0,3 - 50 mm</td><td>Talebe göre</td></tr>
</tbody>
</table>

<h3>Standart Delik Modelleri (Yuvarlak)</h3>

<table>
<thead>
<tr><th>Delik Çapı</th><th>Aralık (Pitch)</th><th>Açıklık Oranı</th><th>Tipik Kullanım</th></tr>
</thead>
<tbody>
<tr><td>3 mm</td><td>5 mm</td><td>%32,7</td><td>Hassas filtre, akustik</td></tr>
<tr><td>5 mm</td><td>8 mm</td><td>%35,4</td><td>Standart filtre, dekoratif</td></tr>
<tr><td>8 mm</td><td>12 mm</td><td>%40,3</td><td>Endüstriyel filtre</td></tr>
<tr><td>10 mm</td><td>15 mm</td><td>%40,3</td><td>Havalandırma kanal</td></tr>
<tr><td>15 mm</td><td>22 mm</td><td>%42,1</td><td>Cephe, eleme</td></tr>
<tr><td>20 mm</td><td>28 mm</td><td>%46,2</td><td>Estetik panel</td></tr>
</tbody>
</table>

<h2>Delikli Sac Malzeme Seçenekleri</h2>

<h3>1. DKP Delikli Sac</h3>
<p>Soğuk haddelenmiş çelikten — en yaygın ve ekonomik. 0,5-3 mm kalınlık. İç mekan, korumalı uygulamalar. Boyanabilir.</p>

<h3>2. Galvaniz Delikli Sac</h3>
<p>Sıcak daldırma galvaniz kaplama. <strong>Açık hava + nemli ortam</strong> uygulamaları. Çatı ve cephe perforasyonu, dış mekan filtre.</p>

<h3>3. Paslanmaz Delikli Sac (304 / 316)</h3>
<p>Korozyon dayanımı kritik. <strong>Gıda, kimya, ilaç sanayisi</strong>, eczacılık ekipmanları, mutfak ekipmanları, deniz uygulamaları (316L).</p>

<h3>4. Alüminyum Delikli Sac</h3>
<p>Hafif, doğal korozyon dayanımı. <strong>Mimari cephe, akustik tavan, hava filtre</strong>, otomotiv parçaları. Anodize halleri mevcut.</p>

<h3>5. Bakır / Pirinç Delikli Sac</h3>
<p>Dekoratif uygulamalar, müzik enstrümanı parçaları, anteni, lüks mimari panel. Patine veya parlak hâli.</p>

<h2>Delikli Sac Kullanım Alanları</h2>

<h3>Filtreleme ve Eleme</h3>
<ul>
<li><strong>Endüstriyel filtreler:</strong> Hava, yağ, su, gaz filtre yatakları</li>
<li><strong>Tarım eleme:</strong> Tahıl, fındık, fıstık, fasulye sınıflandırma</li>
<li><strong>Maden eleme:</strong> Kömür, kum, mıcır, taş ayırma</li>
<li><strong>Atık işleme:</strong> Geri dönüşüm tesislerinde sınıflandırma</li>
<li><strong>Gıda eleme:</strong> Un, şeker, baharatın boyut sınıflandırması</li>
</ul>

<h3>Mimari ve Yapı</h3>
<ul>
<li><strong>Modern cephe panelleri:</strong> Lüks villa, ofis, AVM, otel cephe</li>
<li><strong>Dekoratif iç mekan:</strong> Restoran, kafe, mağaza separator</li>
<li><strong>Akustik panel:</strong> Konferans salonu, sinema, ses kayıt stüdyosu</li>
<li><strong>Asma tavan:</strong> Havalandırma + akustik kontrol</li>
<li><strong>Korkuluk ve bariyer:</strong> Şeffaf görünüm + güvenlik</li>
<li><strong>Bahçe pergola:</strong> Estetik gölgelik panelleri</li>
</ul>

<h3>HVAC ve Havalandırma</h3>
<ul>
<li><strong>Hava difüzörü:</strong> Klima, mekanik havalandırma</li>
<li><strong>Egzoz duvar paneli:</strong> Endüstriyel duman tahliye</li>
<li><strong>Ses absorbe panel:</strong> Endüstriyel mahaller</li>
<li><strong>Havalandırma kanalı:</strong> Düzenli hava sirkülasyonu</li>
</ul>

<h3>Otomotiv ve Ulaşım</h3>
<ul>
<li>Egzoz susturucu iç parçaları</li>
<li>Hava filtre kafesleri</li>
<li>Yağ filtresi delikli plakaları</li>
<li>Gemi havalandırma panelleri</li>
</ul>

<h3>Endüstriyel Makina</h3>
<ul>
<li>CNC tezgâh güvenlik panelleri</li>
<li>Konveyör güvenlik kapakları</li>
<li>Boyahane filtreleme bölmeleri</li>
<li>Endüstriyel mutfak ızgaraları</li>
</ul>

<h2>Delikli Sac Ağırlık Hesaplama</h2>

<p>Delikli sacın ağırlığı, baz sacın ağırlığından <strong>delik açıklık oranı kadar düşüktür</strong>:</p>

<p><strong>Delikli Sac Ağırlığı = Düz Sac Ağırlığı × (1 - Açıklık Oranı / 100)</strong></p>

<p><em>Örneği:</em> 2 mm DKP düz sac = 15,7 kg/m². %35 açıklık oranlı delikli sac = 15,7 × (1 - 0,35) = <strong>10,2 kg/m²</strong>.</p>

<p><a href="/hesaplama.php">Online ağırlık hesaplama motorumuz</a> ile hassas hesap yapabilirsiniz.</p>

<h2>Avantajları</h2>

<ul>
<li><strong>Hafifletme:</strong> Düz saca göre %20-60 daha hafif (açıklık oranına bağlı)</li>
<li><strong>Geçirgenlik:</strong> Hava, ışık, ses, sıvı kontrollü geçişi sağlar</li>
<li><strong>Estetik:</strong> Modern, çağdaş görünüm sağlar</li>
<li><strong>Hijyen:</strong> Paslanmaz delikli sac gıda standartlarında</li>
<li><strong>Akustik kontrol:</strong> Ses absorbsiyon panel olarak</li>
<li><strong>Filtreleme hassasiyeti:</strong> Mikronlu çapraz tutma</li>
<li><strong>Rüzgar yükü azaltma:</strong> Cephe için %30+ rüzgar geçirgenliği</li>
<li><strong>Geri dönüştürülebilir:</strong> %100 metal — sürdürülebilir</li>
</ul>

<h2>Delikli Sac vs Genişletilmiş Sac</h2>

<table>
<thead>
<tr><th>Özellik</th><th>Delikli Sac</th><th>Genişletilmiş Sac</th></tr>
</thead>
<tbody>
<tr><td>Üretim</td><td>Pres delikleme (delikler çıkarılır)</td><td>Pres genleştirme (kaynaksız tek parça)</td></tr>
<tr><td>Delik şekli</td><td>Hassas, düzgün geometrik</td><td>Doğal elmas/baklava</td></tr>
<tr><td>Açıklık oranı</td><td>%20 - %60</td><td>%50 - %80 (yüksek)</td></tr>
<tr><td>Mukavemet</td><td>Orta</td><td>Yüksek (kaynaksız)</td></tr>
<tr><td>Estetik</td><td>Çok iyi (modern, hassas)</td><td>İyi (endüstriyel)</td></tr>
<tr><td>Maliyet</td><td>Daha pahalı (delikler kayıp)</td><td>Daha ekonomik</td></tr>
<tr><td>Tipik kullanım</td><td>Mimari, filtre, akustik</td><td>Cephe, yürüme yolu</td></tr>
</tbody>
</table>

<h2>Delikli Sac Fiyatları (2026)</h2>

<p>Delikli sac fiyatları; <strong>malzeme cinsi, kalınlık, delik geometrisi, açıklık oranı, levha ölçüsü ve sipariş miktarı</strong> baz alınarak belirlenir.</p>

<h3>Fiyat Sıralaması (Pahalıdan Ucuza)</h3>
<ol>
<li>Bakır/pirinç delikli sac</li>
<li>Paslanmaz 316L delikli sac</li>
<li>Paslanmaz 304 delikli sac</li>
<li>Alüminyum delikli sac</li>
<li>Galvanizli delikli sac</li>
<li>DKP delikli sac (en ekonomik)</li>
</ol>

<h3>Fiyatı Belirleyen Faktörler</h3>
<ul>
<li><strong>Açıklık oranı:</strong> Daha fazla delik = daha az kalan metal ama daha çok işçilik</li>
<li><strong>Delik çapı:</strong> Çok küçük delikler (1-2 mm) yüksek hassasiyet maliyeti</li>
<li><strong>Geometri:</strong> Yuvarlak en ekonomik, özel desen pahalı</li>
<li><strong>Üretim yöntemi:</strong> CNC pres < Lazer < Su jeti (artan maliyet)</li>
<li><strong>Sipariş miktarı:</strong> 50+ levha siparişlerde kademeli indirim</li>
</ul>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Delikli sac kaç farklı geometride üretilir?</h4>
<p>Standart yuvarlak, kare, dikdörtgen (slot), altıgen, üçgen, oval ve özel CAD tasarımı. <strong>Lazer kesim</strong> ile pratikte sınırsız desen mümkün.</p>

<h4>En küçük delik çapı nedir?</h4>
<p>CNC pres ile <strong>0,8 mm</strong>, lazer kesim ile <strong>0,3 mm</strong>, photo-etching ile <strong>0,05 mm</strong>''ye kadar inilebilir.</p>

<h4>Delikli sac kaç m² minimum sipariş?</h4>
<p>Standart desenler (yuvarlak, kare) için <strong>1 levha</strong> minimum. Özel CAD desenler için minimum 5-10 m² ekonomik olur.</p>

<h4>Galvaniz delikli sac üretiyor musunuz?</h4>
<p>Evet. Hem önceden galvanizli sacı delikleyebiliyor (delikler galvanizsiz kalır) hem de DKP delikli sacı sonrasında galvanizleme imkanımız var.</p>

<h4>Ne kadar süre içinde teslim edebilirsiniz?</h4>
<p>Standart desenler stoklu (yuvarlak 5/8/10 mm) <strong>aynı gün</strong>. Özel desenler <strong>3-5 iş günü</strong>. Mikro-delikli özel siparişler <strong>10-15 gün</strong>.</p>

<h4>İhracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan, Türkmenistan</strong>''a düzenli delikli sac sevkiyatı yapıyoruz. Gümrük ve menşei dokümantasyonu dahil.</p>

<h2>Tekcan Metal Delikli Sac Avantajları</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong></li>
<li>✅ <strong>Erdemir, Borçelik, Tosyalı</strong> üretici sertifikalı tedarik</li>
<li>✅ <strong>5 standart geometri stokta</strong> (yuvarlak, kare, slot, altıgen, özel)</li>
<li>✅ <strong>CNC pres + lazer kesim atölyesi</strong> — özel desen üretimi</li>
<li>✅ <strong>Tüm malzemeler:</strong> DKP, galvaniz, paslanmaz 304/316, alüminyum, bakır</li>
<li>✅ <strong>0,3 mm hassasiyet</strong> (lazer kesim)</li>
<li>✅ <strong>81 il sevkiyat ağı</strong> + uluslararası ihracat</li>
<li>✅ <strong>e-Fatura, üretici sertifikası, kalite belgeleri</strong> dahil</li>
</ul>

<p class="cta-block"><strong>Delikli sac fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/delikli-sac-rehberi.jpg',
    'Tekcan Metal',
    'Delikli Sac Çeşitleri, Ölçüleri, Açıklık Oranları ve Fiyat — Tekcan Metal',
    'Delikli sac (perforated sheet) çeşitleri yuvarlak/kare/slot/altıgen, DKP/galvaniz/paslanmaz/alüminyum, ağırlık tablosu, kullanım alanları. CNC pres + lazer kesim atölyesi. Konya merkezli Tekcan Metal — 20+ yıl, 81 il sevkiyat.',
    NOW(), 0, 1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- ===== 2) Galvanizli Boru süper sayfa =====
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'galvanizli-boru-rehberi',
    'Galvanizli Boru (Galvaniz Boru): Çapları, Ölçü Tablosu, Çeşitleri ve Fiyatlar',
    'Galvanizli boru (galvaniz çelik boru) DN 15 - DN 200 (1/2-8 inch) çap aralığı, sanayi/doğalgaz/yangın/su tesisat çeşitleri, kalınlık tablosu, ağırlık ve fiyat. İGDAŞ/BAŞKENTGAZ onaylı. Konya merkezli Tekcan Metal — 20+ yıl, 81 il sevkiyat.',
    '<p class="lead"><strong>Galvanizli boru</strong> (galvaniz boru olarak da bilinir), çelik borunun iç ve dış yüzeylerine sıcak daldırma yöntemiyle <strong>çinko (Zn) kaplaması</strong> uygulanmış, korozyona dayanıklı ve uzun ömürlü endüstriyel boru türüdür. Tekcan Metal olarak 2005''ten bu yana Konya merkezimizden Türkiye geneline çeşitli çap ve kalınlıkta galvanizli boru tedariği gerçekleştiriyoruz.</p>

<h2>Galvanizli Boru Nedir? Yapısı ve Üretim Süreci</h2>

<p>Galvanizli boru, üretim süreci tamamlanmış çelik borunun <strong>460°C''de erimiş çinko banyosundan</strong> geçirilmesiyle iç ve dış yüzeyi çinko ile kaplanır. Bu işlem boruyu atmosferik korozyon, nem, kimyasal etkiler ve havadaki oksijen ile çinkonun reaksiyonuna karşı 25-50 yıl koruma altına alır.</p>

<p>Sektörde "<strong>galvaniz çelik boru</strong>", "<strong>çinko kaplı boru</strong>" veya İngilizce "<strong>galvanized steel pipe (GP)</strong>", "<strong>hot-dip galvanized pipe</strong>" olarak da bilinir.</p>

<h3>Üretim Yöntemleri</h3>

<h4>1. Sıcak Daldırma Galvaniz (Hot-Dip)</h4>
<p>Tamamlanmış çelik boru çinko banyosuna <strong>tamamen daldırılır</strong>. İç ve dış yüzeyler eş zamanlı kaplanır. Kaplama 50-200 g/m². <strong>En yaygın ve en dayanıklı</strong> yöntem.</p>

<h4>2. Daldırma Galvaniz Sonrası Şekillendirme</h4>
<p>Önce sac galvanizlenir, sonra borulanır. Daha ekonomik ama kaynak hatlarında çinko olmaz. <strong>Mavi-beyaz şerit</strong> görünür kaynak izi.</p>

<h4>3. Galvaniz-İçeride (Outside Only)</h4>
<p>Sadece dış yüzey galvanizli, iç yüzey çıplak. Yüksek basınçlı uygulamalar için kaynak edilebilirlik kritik.</p>

<h2>Galvanizli Boru Çeşitleri</h2>

<h3>1. Sanayi Galvanizli Borusu</h3>
<p>EN 10219 / TS EN 10255 standardı. Genel inşaat ve sanayi uygulamaları. <strong>1/2" - 8" çap</strong> aralığı. En yaygın kullanılan tip.</p>

<h3>2. Doğalgaz Galvanizli Borusu</h3>
<p>İGDAŞ, BAŞKENTGAZ vb. <strong>onaylı doğalgaz dağıtım</strong> hatları için. TS EN 10255-G standardı. Düşük basınçlı bina içi gaz hatları.</p>

<h3>3. Su Tesisatı Galvanizli Borusu</h3>
<p>Eski sistemlerde çok yaygın (artık PE veya kompozit boru tercih ediliyor). <strong>İçme suyu sertifikalı</strong> kaplama.</p>

<h3>4. Yangın Sistemi Galvanizli Borusu</h3>
<p>Sprinkler, hidrant, yangın hattı. UL/FM onaylı. <strong>Schedule 40</strong> veya kalın cidar tipler.</p>

<h3>5. Konstrüksiyon Galvanizli Borusu</h3>
<p>Çatı strüktürü, çelik konstrüksiyon, çadır direği. <strong>Basınç değil yapısal yük</strong> taşır.</p>

<h3>6. Profil Galvanizli Borusu</h3>
<p>Kare, dikdörtgen kesitli galvanizli profil. Mobilya, mimari uygulama, çit, korkuluk.</p>

<h2>Galvanizli Boru Çapları ve Ölçü Tablosu</h2>

<p>Galvanizli borular <strong>nominal çap (DN) sistemi</strong> ile satılır. Aşağıdaki tabloda yaygın ölçüler:</p>

<table>
<thead>
<tr><th>Nominal Çap (DN)</th><th>İnch (")</th><th>Dış Çap (mm)</th><th>Standart Cidar Kalınlığı (mm)</th><th>Tipik Kullanım</th></tr>
</thead>
<tbody>
<tr><td>DN 15</td><td>1/2"</td><td>21,3</td><td>2,3 - 2,9</td><td>Tesisat, hava hattı</td></tr>
<tr><td>DN 20</td><td>3/4"</td><td>26,9</td><td>2,3 - 2,9</td><td>Tesisat, küçük hat</td></tr>
<tr><td>DN 25</td><td>1"</td><td>33,7</td><td>2,9 - 3,2</td><td>Genel sanayi, doğalgaz</td></tr>
<tr><td>DN 32</td><td>1¼"</td><td>42,4</td><td>2,9 - 3,2</td><td>Sıvı/gaz iletim</td></tr>
<tr><td>DN 40</td><td>1½"</td><td>48,3</td><td>2,9 - 3,6</td><td>Endüstriyel hat</td></tr>
<tr><td>DN 50</td><td>2"</td><td>60,3</td><td>3,2 - 3,6</td><td>Yangın, sanayi</td></tr>
<tr><td>DN 65</td><td>2½"</td><td>76,1</td><td>3,2 - 4,0</td><td>Yangın hattı</td></tr>
<tr><td>DN 80</td><td>3"</td><td>88,9</td><td>3,2 - 4,0</td><td>Hidrant, ana hat</td></tr>
<tr><td>DN 100</td><td>4"</td><td>114,3</td><td>3,6 - 4,5</td><td>Endüstriyel ana hat</td></tr>
<tr><td>DN 125</td><td>5"</td><td>139,7</td><td>4,0 - 5,0</td><td>Büyük dağıtım</td></tr>
<tr><td>DN 150</td><td>6"</td><td>168,3</td><td>4,5 - 5,4</td><td>Endüstriyel</td></tr>
<tr><td>DN 200</td><td>8"</td><td>219,1</td><td>5,4 - 6,3</td><td>Ana hat, büyük endüstri</td></tr>
</tbody>
</table>

<h3>Standart Boy</h3>
<p>Galvanizli borular genelde <strong>6 metre boy</strong> (4 metre, 12 metre seçenekleri de var) halinde satılır. Pakette demet sayısı çapa göre değişir.</p>

<h2>Galvanizli Boru Ağırlık Hesaplama</h2>

<p>Galvanizli boru ağırlığı (kg/m), boru cidar kalınlığı ve dış çapına göre hesaplanır:</p>

<p><strong>Ağırlık (kg/m) = (Dış Çap - Cidar) × Cidar × π × 7,85 / 1000</strong></p>

<h3>Pratik Ağırlık Tablosu (kg/m)</h3>

<table>
<thead>
<tr><th>Çap</th><th>Cidar 2,3 mm</th><th>Cidar 2,9 mm</th><th>Cidar 3,2 mm</th><th>Cidar 4,0 mm</th></tr>
</thead>
<tbody>
<tr><td>1/2" (21,3 mm)</td><td>1,08</td><td>1,32</td><td>—</td><td>—</td></tr>
<tr><td>3/4" (26,9 mm)</td><td>1,40</td><td>1,72</td><td>—</td><td>—</td></tr>
<tr><td>1" (33,7 mm)</td><td>—</td><td>2,21</td><td>2,42</td><td>—</td></tr>
<tr><td>1½" (48,3 mm)</td><td>—</td><td>3,25</td><td>3,57</td><td>4,38</td></tr>
<tr><td>2" (60,3 mm)</td><td>—</td><td>—</td><td>4,52</td><td>5,57</td></tr>
<tr><td>3" (88,9 mm)</td><td>—</td><td>—</td><td>6,77</td><td>8,38</td></tr>
<tr><td>4" (114,3 mm)</td><td>—</td><td>—</td><td>—</td><td>10,90</td></tr>
<tr><td>6" (168,3 mm)</td><td>—</td><td>—</td><td>—</td><td>16,21</td></tr>
</tbody>
</table>

<p><em>Hassas hesaplama için <a href="/hesaplama.php">online ağırlık hesaplama motorumuzu</a> kullanın. Galvaniz kaplaması ek %1-2 ağırlık katar.</em></p>

<h2>Galvanizli Boru Kullanım Alanları</h2>

<h3>Tesisat ve Sıhhi Tesisat</h3>
<ul>
<li><strong>Bina içi su tesisatı:</strong> Soğuk-sıcak su hatları (yeni binalarda PEX/PE tercih edilir, eski sistemler galvaniz)</li>
<li><strong>Doğalgaz tesisatı:</strong> Düşük basınçlı bina içi gaz hatları (DN 15 - DN 50)</li>
<li><strong>Sera sulama:</strong> Tarımsal sulama hatları</li>
<li><strong>Endüstriyel su:</strong> Soğutma, proses suyu hattı</li>
</ul>

<h3>Yangın Sistemleri</h3>
<ul>
<li><strong>Sprinkler hatları:</strong> Otomatik yangın söndürme</li>
<li><strong>Hidrant tesisatı:</strong> Bina dışı yangın hatları</li>
<li><strong>Yangın dolapları:</strong> Bina içi yangın hortum sistemleri</li>
<li><strong>Endüstriyel söndürme:</strong> Kuru yangın hatları</li>
</ul>

<h3>Konstrüksiyon</h3>
<ul>
<li><strong>Çatı iskeleti:</strong> Hangar, çadır, sera çelik strüktür</li>
<li><strong>Çelik korkuluk:</strong> Merdiven, balkon, ofis</li>
<li><strong>Çit ve bariyer:</strong> Endüstriyel saha, otopark</li>
<li><strong>Korkuluk direği:</strong> Otoyol bariyer sistemi</li>
<li><strong>Reklam panosu strüktürü:</strong> Açık hava reklam</li>
</ul>

<h3>Tarım ve Sera</h3>
<ul>
<li>Sera çatı strüktürü</li>
<li>Damla sulama ana hatlar</li>
<li>Hayvan barınakları çatı iskeletleri</li>
<li>Tarım makinesi şasileri</li>
</ul>

<h3>Endüstri ve Sanayi</h3>
<ul>
<li>Pnömatik (basınçlı hava) hatları</li>
<li>Endüstriyel buhar hatları (düşük basınç)</li>
<li>Hava soğutma kanal sistemleri</li>
<li>Konveyör ana strüktürü</li>
</ul>

<h2>Avantajları</h2>

<ul>
<li><strong>Korozyon dayanımı:</strong> Çinko, çeliğin yerine oksitlenir (sacrificial protection) — 25-50 yıl ömür</li>
<li><strong>İç ve dış kaplama:</strong> Borunun her iki yüzeyi de korunur</li>
<li><strong>Ekonomik:</strong> Paslanmaz çelik boruya göre %60-70 daha ucuz</li>
<li><strong>Kaynaklanabilir:</strong> Punto, MIG, MAG kaynak yöntemleri uygulanabilir</li>
<li><strong>Diş açılabilir:</strong> Vidalı bağlantı ile montaj yapılır (BSPT/NPT diş)</li>
<li><strong>Yangın güvenliği:</strong> Yanmaz — A1 sınıfı malzeme</li>
<li><strong>Geri dönüştürülebilir:</strong> %100 metal — sürdürülebilir</li>
<li><strong>Standart ölçü desteği:</strong> Geniş stok, hızlı tedarik</li>
</ul>

<h2>Galvanizli Boru vs Diğer Borular</h2>

<table>
<thead>
<tr><th>Özellik</th><th>Galvanizli</th><th>Siyah Çelik</th><th>Paslanmaz</th><th>PE/PEX</th></tr>
</thead>
<tbody>
<tr><td>Korozyon dayanımı</td><td>Çok iyi (25-50 yıl)</td><td>Düşük (5-10 yıl)</td><td>Mükemmel (50+ yıl)</td><td>Mükemmel (50+ yıl)</td></tr>
<tr><td>Maliyet</td><td>Orta</td><td>Düşük</td><td>Yüksek</td><td>Düşük-orta</td></tr>
<tr><td>Yangın direnci</td><td>Mükemmel</td><td>Mükemmel</td><td>Mükemmel</td><td>Düşük</td></tr>
<tr><td>Basınç dayanımı</td><td>Yüksek</td><td>Yüksek</td><td>Çok yüksek</td><td>Orta</td></tr>
<tr><td>İçme suyu uygun</td><td>Evet (sertifikalı)</td><td>Hayır</td><td>Evet</td><td>Evet</td></tr>
<tr><td>Tipik kullanım</td><td>Tesisat, yangın, gaz</td><td>Endüstriyel hat</td><td>Gıda, kimya</td><td>Yeni bina tesisat</td></tr>
</tbody>
</table>

<h2>Galvanizli Boru Fiyatları (2026)</h2>

<p>Galvanizli boru fiyatları; <strong>çap, cidar kalınlığı, kalite sınıfı, sipariş miktarı</strong> baz alınarak belirlenir.</p>

<h3>Fiyatı Belirleyen Faktörler</h3>
<ul>
<li><strong>LME çinko fiyatı:</strong> Borsa fiyatına göre günlük değişir</li>
<li><strong>Çelik hammadde:</strong> HRC fiyatı + işleme maliyetleri</li>
<li><strong>Çap büyüklüğü:</strong> 1/2" en ekonomik, 8" en pahalı (m fiyatı)</li>
<li><strong>Cidar kalınlığı:</strong> Standart 2,9 mm en yaygın, kalın cidar (4-5 mm) ek maliyet</li>
<li><strong>Kalite sertifikası:</strong> EN 10204 3.1, doğalgaz onaylı, yangın onaylı ek maliyet</li>
<li><strong>Sipariş miktarı:</strong> Tonaj bazında kademeli indirim</li>
</ul>

<h2>Sıkça Sorulan Sorular (SSS)</h2>

<h4>Galvanizli boru kaç yıl dayanır?</h4>
<p>Kullanım koşullarına bağlı: <strong>İç mekanda 50+ yıl</strong>, açık havada 25-40 yıl, agresif ortamda 15-25 yıl. Bina içi tesisatta yarım asır sorunsuz çalışır.</p>

<h4>İçme suyu için galvanizli boru kullanabilir miyim?</h4>
<p>Yeni yapılarda içme suyu için <strong>PE/PEX boru</strong> tercih ediliyor. Mevcut galvaniz tesisat değiştirilmeli olabilir; çünkü zamanla iç yüzeyde paslanma tortu birikir. <strong>İçme suyu sertifikalı galvanizli boru</strong> mevcut, ama yenisini için PE/PEX öneriyoruz.</p>

<h4>Galvanizli boru kaç inç başlar?</h4>
<p>Standart üretim <strong>1/2" (DN 15)</strong>''ten başlar. Daha küçük çaplar (1/4", 3/8") özel sipariş.</p>

<h4>Galvanizli boru kesim hizmeti veriyor musunuz?</h4>
<p>Evet. Sipariş ettiğiniz boy uzunluğunda kesim yapıyoruz. Standart 6 m, talep ederseniz 1-12 m arası kesim ücretsiz.</p>

<h4>Doğalgaz onaylı galvanizli boru var mı?</h4>
<p>Evet. <strong>İGDAŞ ve BAŞKENTGAZ onaylı</strong> TS EN 10255-G sertifikalı borularımız mevcut. Bina içi gaz tesisatı için.</p>

<h4>Galvanizli boruya kaynak yapılabilir mi?</h4>
<p>Evet ama özel teknik gerektirir. Kaynak sırasında çinko buharı çıkar, <strong>iyi havalandırma</strong> şart. Kaynaklı bölge tekrar galvanizli sprey/boya ile kaplanmalı.</p>

<h4>İhracat yapıyor musunuz?</h4>
<p>Evet. <strong>Irak, Suriye, Azerbaycan, Türkmenistan</strong>''a düzenli galvanizli boru sevkiyatımız var. Gümrük dokümantasyonu ve menşei şahadetnamesi tarafımızca düzenlenir.</p>

<h2>Tekcan Metal Galvanizli Boru Avantajları</h2>

<ul>
<li>✅ <strong>20+ yıl sektör tecrübesi</strong></li>
<li>✅ <strong>Erbosan, Borçelik, Borusan, Tezcan</strong> üretici sertifikalı tedarik</li>
<li>✅ <strong>Tüm çaplar:</strong> 1/2" - 8" arası geniş stok</li>
<li>✅ <strong>Doğalgaz onaylı</strong> İGDAŞ/BAŞKENTGAZ sertifikalı borular</li>
<li>✅ <strong>Yangın onaylı</strong> sprinkler/hidrant boruları</li>
<li>✅ <strong>Boy kesim hizmeti</strong> — standart 6m, talebe göre 1-12m</li>
<li>✅ <strong>EN 10204 3.1 üretici sertifikası</strong> dahil</li>
<li>✅ <strong>81 il sevkiyat ağı</strong> + uluslararası ihracat</li>
<li>✅ <strong>e-Fatura, kalite belgeleri, gümrük dokümantasyonu</strong></li>
</ul>

<p class="cta-block"><strong>Galvanizli boru fiyat teklifi almak için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Teklif İste</a> · <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/galvanizli-boru-rehberi.jpg',
    'Tekcan Metal',
    'Galvanizli Boru Çapları, Ölçü Tablosu, Çeşitleri ve Fiyat — Tekcan Metal',
    'Galvanizli boru (galvaniz boru) çapları DN 15-DN 200 (1/2-8 inch), sanayi/doğalgaz/yangın/su tesisat tipleri, ağırlık tablosu, fiyat. İGDAŞ-BAŞKENTGAZ onaylı, EN 10204 3.1 sertifikalı. Konya Tekcan Metal — 20+ yıl tecrübe.',
    NOW(), 0, 1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- ===== 3) Ferforje optimize sayfa =====
INSERT INTO tm_blog_posts (
    category_id, slug, title, excerpt, content, cover_image,
    author, meta_title, meta_desc, published_at, view_count, is_active
) VALUES (
    (SELECT id FROM tm_blog_categories WHERE slug='urun-rehberi' LIMIT 1),
    'ferforje-nedir',
    'Ferforje Nedir? Üretim Yöntemleri, Kullanım Alanları ve Hammadde Tedariki',
    'Ferforje (dövme demir) nedir, üretim yöntemleri (sıcak dövme, soğuk şekillendirme, CNC plazma), kullanım alanları (bahçe kapısı, balkon korkuluğu, merdiven trabzanı), kullanılan malzemeler. Tekcan Metal hammadde tedariki — Konya, lama, yuvarlak demir, hazır ferforje aksesuarları.',
    '<p class="lead"><strong>Ferforje</strong>, dövme demir ya da çelik ürünlerin elle veya endüstriyel olarak şekillendirilerek dekoratif amaçlarla kullanılması sanatıdır. Bahçe kapısı, balkon korkuluğu, merdiven trabzanı, pencere demiri ve dekoratif iç mekan elemanları başta olmak üzere geniş kullanım alanı vardır.</p>

<h2>Ferforje Nedir?</h2>
<p>Ferforje kelimesi Fransızca "fer forgé" yani "dövme demir" anlamına gelir. <strong>Sıcak dövme</strong> veya <strong>soğuk şekillendirme</strong> yöntemleriyle çelik çubukların büküm, kıvırma, kıvrım, helezon ve kombinlerle dekoratif desenler oluşturulduğu metal sanatıdır.</p>

<h3>Ferforje Üretim Yöntemleri</h3>
<ul>
<li><strong>Sıcak dövme:</strong> Çelik 1100°C''''ye kadar ısıtılır, çekiç ve örs ile şekillendirilir</li>
<li><strong>Soğuk şekillendirme:</strong> Hidrolik veya manuel pres ile bükme/kıvırma</li>
<li><strong>CNC plazma kesim:</strong> Karmaşık desenler için modern teknoloji</li>
<li><strong>El işçiliği:</strong> Geleneksel ferforje için (özel projeler)</li>
</ul>

<h2>Ferforje Kullanım Alanları</h2>

<h3>Yapı ve İnşaat</h3>
<ul>
<li><strong>Bahçe kapısı ve duvarı:</strong> Estetik girişler, villa girişleri</li>
<li><strong>Balkon korkuluğu:</strong> Apartman, villa balkonları</li>
<li><strong>Merdiven trabzanı:</strong> İç ve dış merdiven korkulukları</li>
<li><strong>Pencere demiri:</strong> Güvenlik + estetik kombinasyonu</li>
<li><strong>Çardak ve pergola:</strong> Bahçe çatı strüktürleri</li>
<li><strong>Çiçeklik destekleri:</strong> Dış mekan dekorasyonu</li>
</ul>

<h3>İç Mekan Dekorasyon</h3>
<ul>
<li>Dekoratif duvar süslemeleri</li>
<li>Salon avizesi ve aplik tasarımları</li>
<li>Mum şamdanı, mum tutucu</li>
<li>Şömine paravan ve aletleri</li>
<li>Restaurant/kafe dekorasyon elemanları</li>
</ul>

<h3>Ticari ve Endüstriyel</h3>
<ul>
<li>Otel girişleri ve dekorasyon</li>
<li>Restoran tabela çerçeveleri</li>
<li>Mağaza dış cephe süslemeleri</li>
<li>Park-bahçe çitleri</li>
</ul>

<h2>Ferforje Malzemeleri</h2>
<p>Ferforje üretiminde kullanılan malzemeler:</p>
<ul>
<li><strong>Lama demiri:</strong> 12×3 mm''''den 80×10 mm''''ye kadar dikdörtgen kesitli demir</li>
<li><strong>Yuvarlak demir:</strong> Ø8 - Ø25 mm yuvarlak çelik çubuk</li>
<li><strong>Kare demiri:</strong> 10×10 - 30×30 mm kare profil</li>
<li><strong>Çelik boru:</strong> Yapısal destek için galvanizli/siyah boru</li>
<li><strong>Hazır ferforje aksesuarlar:</strong> Helezon, çiçek motifi, yaprak, halka, mızrak ucu</li>
</ul>

<h2>Ferforje Sipariş ve Üretim Süreci</h2>
<p>Tekcan Metal olarak ferforje ürünleri için <strong>hammadde tedariki</strong> sağlıyoruz:</p>
<ol>
<li>Ferforje atölyesi/ustası ile çalışan müşterilerimize lama, yuvarlak ve kare demir tedarik ediyoruz</li>
<li>Hazır ferforje aksesuarlar (helezon, motif vb.) sipariş üzerine getirebiliyoruz</li>
<li>Ferforje üretimi yapan ustalarla bağlantı kurabilirsiniz</li>
<li>Konya bölgesinde ferforje üreticileri için referans hizmeti sunuyoruz</li>
</ol>

<h2>Ferforje Avantajları</h2>
<ul>
<li><strong>Dayanıklılık:</strong> Doğru bakımla 50+ yıl ömür</li>
<li><strong>Estetik:</strong> Klasik, antik, modern stillerin tümü</li>
<li><strong>Özelleştirilebilir:</strong> Her proje için özel desen</li>
<li><strong>Güvenlik:</strong> Pencere/kapı için hem güzellik hem koruma</li>
<li><strong>Bakım:</strong> Periyodik boya ile yenilenebilir</li>
<li><strong>Geri dönüştürülebilir:</strong> %100 metal — sürdürülebilir</li>
</ul>

<h2>Sıkça Sorulan Sorular</h2>

<h4>Ferforje nedir, kelime anlamı?</h4>
<p>Ferforje, Fransızca <em>"fer forgé"</em> kelimesinden gelir; "<strong>dövme demir</strong>" anlamındadır. Demirden el işçiliği veya endüstriyel dövme yöntemleriyle yapılan dekoratif metal işidir.</p>

<h4>Ferforje paslanır mı?</h4>
<p>Ham çelik ferforje açık havada paslanır. Korozyona karşı <strong>galvanizleme veya antipas + boya</strong> uygulaması yapılır. Periyodik bakımla 50+ yıl dayanır.</p>

<h4>Ferforje üretimi yapıyor musunuz?</h4>
<p>Tekcan Metal ferforje atölyesi olarak çalışmıyor; ancak ferforje üretici ve ustaları için <strong>hammadde tedarik</strong> ve referans hizmeti sunuyoruz. Lama, yuvarlak demir ve hazır ferforje aksesuarlarımız mevcuttur.</p>

<h4>Ferforje kapı fiyatı ne kadar?</h4>
<p>Ferforje fiyatları işçilik yoğun olduğu için ürüne özel hesaplanır. Hammadde + işçilik + boya birleşimine göre değişir. Konya bölgesinde anlaşmalı ferforje atölyeleri için bize danışabilirsiniz.</p>

<p class="cta-block"><strong>Ferforje hammadde tedariki için:</strong><br>
📞 0 332 342 24 52 | 📱 WhatsApp 0 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">İletişim</a> · <a href="/urunler.php" class="btn btn-ghost">Ürünlerimiz</a></p>

<p><em>Son güncelleme: Nisan 2026 — Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</em></p>
',
    'uploads/blog/ferforje-nedir.jpg',
    'Tekcan Metal',
    'Ferforje Nedir? Üretim, Kullanım Alanları ve Hammadde — Tekcan Metal',
    'Ferforje (dövme demir) nedir, üretim yöntemleri, kullanım alanları (bahçe kapısı, balkon korkuluğu, merdiven), hammadde tedariki (lama, yuvarlak demir). Konya merkezli Tekcan Metal — 20+ yıl tecrübe.',
    NOW(), 0, 1
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    meta_title = VALUES(meta_title),
    meta_desc = VALUES(meta_desc);

-- ===== 4) Mevcut DKP/Siyah/HRP Sac SEO meta zenginleştirme =====
-- Bu sayfa zaten WP'den import edilmiş 14KB içeriğe sahip.
-- Sadece title/meta optimize ederek SEO etkinliğini artıralım.
UPDATE tm_blog_posts SET
    title = 'DKP, Siyah, HRP ve ST-52 Sac: Çeşitleri, Farkları ve Kullanım Alanları',
    excerpt = 'DKP (soğuk haddelenmiş), Siyah Sac (HRP - sıcak haddelenmiş), ST-52 yapısal çelik sac çeşitleri arasındaki farklar, kalınlıklar, yüzey özellikleri ve kullanım alanları. Tekcan Metal — Konya, üretici sertifikalı tedarik.',
    meta_title = 'DKP, Siyah Sac, HRP, ST-52 Sac Farkları ve Çeşitleri — Tekcan Metal',
    meta_desc = 'DKP soğuk haddelenmiş sac, Siyah/HRP sıcak haddelenmiş sac, ST-52 yapısal çelik sac arasındaki farklar, kullanım alanları, fiyat. Konya merkezli Tekcan Metal — 20+ yıl, 81 il sevkiyat.'
WHERE slug = 'siyah-dkp-hrp-ve-st-52-sac';

-- =====================================================
-- v1.0.78 — SEO Süper Sayfa EN Çevirileri (İlk 4 sayfa)
-- =====================================================
-- Hedef: ihracat müşterileri (Iraq, Syria, Azerbaijan, Turkmenistan)
-- Çevrilen: title_en, excerpt_en, content_en, meta_title_en, meta_desc_en
-- Dosyalar: 4 SEO süper sayfa
-- 1) Genişletilmiş Sac → Expanded Metal
-- 2) Baklava Sac → Diamond Plate
-- 3) Galvanizli Sac → Galvanized Sheet
-- 4) Trapez Sac → Trapezoidal Sheet


-- ===== genisletilmis-sac-rehberi (EN, ~1014 kelime) =====
UPDATE tm_blog_posts SET
    title_en      = 'Expanded Metal: Dimensions, Weight Calculation, Types and Applications',
    excerpt_en    = 'Expanded metal dimensions, weight tables, CRS/HRP/galvanized/stainless/aluminum types and applications. Tekcan Metal — 20+ years experience, Konya-based, 81-province shipping.',
    content_en    = '<p class="lead"><strong>Expanded metal</strong> (also known as expanded sheet, expanded mesh, or perforated stretched sheet) is a single-piece perforated structure produced by applying tension and shearing to flat metal sheets through specialized presses, resulting in open <strong>diamond, rhomboid, or hexagonal patterns</strong>. Tekcan Metal has been supplying expanded metal from our Konya headquarters across Turkey since 2005.</p>

<h2>What Is Expanded Metal? Production Process and Structure</h2>

<p>Expanded metal is a structural metal product with mesh-like open patterns, obtained by simultaneously cutting and stretching a flat sheet on specialized die presses. The production process uses a single sheet; <strong>no welding or assembly is involved</strong>. This method makes the product both lightweight and remarkably durable.</p>

<h3>Production Stages</h3>
<ol>
<li><strong>Raw material selection:</strong> 0.5 mm – 6 mm thick CRS (cold-rolled), HRP (hot-rolled), stainless steel, aluminum, or galvanized sheet</li>
<li><strong>Press cutting and expanding:</strong> Synchronized cutting and stretching through the die''s vertical motion</li>
<li><strong>Flattening (optional):</strong> Transition from raised to flattened structure</li>
<li><strong>Surface treatment:</strong> Galvanizing, electrostatic powder coating, or electro-galvanizing (as required)</li>
<li><strong>Quality control:</strong> Dimensional verification, weight, opening ratio, and tensile strength testing</li>
</ol>

<h2>Expanded Metal Dimensions and Standard Sizes</h2>

<p>Tekcan Metal stocks expanded metal sheets in <strong>standard dimensions</strong>:</p>

<table>
<thead>
<tr><th>Sheet Size (mm)</th><th>Thickness</th><th>Mesh Pattern (LWD × SWD)</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>1.5 - 6 mm</td><td>22×10, 30×12, 43×13, 62×20</td></tr>
<tr><td>1250 × 2500</td><td>2 - 6 mm</td><td>43×13, 62×20, 76×24, 100×40</td></tr>
<tr><td>1500 × 3000</td><td>3 - 6 mm</td><td>62×20, 76×24, 100×40, 125×50</td></tr>
<tr><td>Custom size</td><td>0.5 - 8 mm</td><td>By request</td></tr>
</tbody>
</table>

<p>LWD = Long Way of Diamond, SWD = Short Way of Diamond. Custom dimensions and patterns available upon request.</p>

<h2>Expanded Metal Material Options</h2>

<h3>1. CRS (Cold-Rolled Steel) Expanded Metal</h3>
<p>Manufactured from cold-rolled steel — the most common and economical option. Available in 0.5–3 mm thickness. Suitable for indoor and protected applications. Can be painted.</p>

<h3>2. HRP (Hot-Rolled Pickled) Expanded Metal</h3>
<p>Made from hot-rolled and pickled steel sheet. Suitable for thicker applications (3–6 mm). Used in industrial walkways, security barriers, and machinery guards.</p>

<h3>3. Galvanized Expanded Metal</h3>
<p>Hot-dip galvanized coating. Recommended for <strong>outdoor and humid environments</strong>. Used in roof and façade perforations and outdoor filters.</p>

<h3>4. Stainless Steel Expanded Metal (304 / 316)</h3>
<p>Critical corrosion resistance. Used in <strong>food, chemical, and pharmaceutical industries</strong>, marine applications (316L), and architectural decorative panels.</p>

<h3>5. Aluminum Expanded Metal</h3>
<p>Lightweight with natural corrosion resistance. Used in <strong>architectural façades, acoustic ceilings, air filters</strong>, and automotive components. Anodized finishes available.</p>

<h2>Expanded Metal Weight Calculation</h2>

<p>The weight of expanded metal (kg/m²) is calculated based on the base sheet density × opening ratio:</p>

<p><strong>Expanded Metal Weight = Flat Sheet Weight × (1 - Opening Ratio / 100)</strong></p>

<p><em>For example:</em> 2 mm CRS flat sheet = 15.7 kg/m². Expanded metal with 65% opening ratio = 15.7 × (1 - 0.65) = <strong>5.5 kg/m²</strong>.</p>

<p>Use our <a href="/hesaplama.php">online weight calculator</a> for precise calculations.</p>

<h2>Expanded Metal Applications</h2>

<h3>Architecture and Construction</h3>
<ul>
<li><strong>Modern façade panels:</strong> Luxury villas, offices, malls, and hotel exteriors</li>
<li><strong>Decorative interior:</strong> Restaurants, cafés, retail dividers</li>
<li><strong>Acoustic panels:</strong> Conference rooms, cinemas, recording studios</li>
<li><strong>Suspended ceilings:</strong> Combined ventilation + acoustic control</li>
<li><strong>Railings and barriers:</strong> Transparent appearance with security</li>
</ul>

<h3>Industrial Applications</h3>
<ul>
<li><strong>Walkway grating:</strong> Heavy-duty industrial walkways</li>
<li><strong>Machine guards:</strong> CNC and production line safety panels</li>
<li><strong>Filter beds:</strong> Industrial air, oil, water filtration</li>
<li><strong>Conveyor protection:</strong> Open mesh conveyor sides</li>
<li><strong>Sieve and screening:</strong> Mining, agriculture, recycling sorting</li>
</ul>

<h3>HVAC and Ventilation</h3>
<ul>
<li>Air diffusers for HVAC systems</li>
<li>Industrial exhaust panels</li>
<li>Sound-absorbing industrial partitions</li>
<li>Ventilation channels</li>
</ul>

<h3>Automotive and Transportation</h3>
<ul>
<li>Exhaust silencer internals</li>
<li>Air filter cages</li>
<li>Marine ventilation panels</li>
</ul>

<h3>Security and Fencing</h3>
<ul>
<li>Industrial site fencing</li>
<li>Window and door security mesh</li>
<li>Vehicle protection grilles</li>
</ul>

<h2>Advantages of Expanded Metal</h2>

<ul>
<li><strong>Lightweight:</strong> 50-80% lighter than flat sheet (depending on opening ratio)</li>
<li><strong>High strength-to-weight ratio:</strong> Single-piece construction (no welds)</li>
<li><strong>Air, light, and fluid permeability:</strong> Controlled flow</li>
<li><strong>Modern aesthetics:</strong> Contemporary architectural appeal</li>
<li><strong>Wind load reduction:</strong> 50%+ wind permeability for façades</li>
<li><strong>Self-cleaning:</strong> Open structure prevents dust accumulation</li>
<li><strong>Cost-effective:</strong> No material loss (vs perforated sheet)</li>
<li><strong>100% recyclable:</strong> Sustainable metal product</li>
</ul>

<h2>Expanded Metal vs Perforated Sheet</h2>

<table>
<thead>
<tr><th>Feature</th><th>Expanded Metal</th><th>Perforated Sheet</th></tr>
</thead>
<tbody>
<tr><td>Production</td><td>Press expansion (single piece)</td><td>Hole punching (material removed)</td></tr>
<tr><td>Hole shape</td><td>Natural diamond/rhomboid</td><td>Precise geometric patterns</td></tr>
<tr><td>Opening ratio</td><td>50% - 80% (high)</td><td>20% - 60%</td></tr>
<tr><td>Strength</td><td>Higher (no welds)</td><td>Medium</td></tr>
<tr><td>Aesthetics</td><td>Industrial</td><td>Modern, precise</td></tr>
<tr><td>Cost</td><td>More economical</td><td>More expensive (material loss)</td></tr>
<tr><td>Typical use</td><td>Façades, walkways</td><td>Architecture, filters, acoustics</td></tr>
</tbody>
</table>

<h2>Expanded Metal Pricing (2026)</h2>

<p>Expanded metal pricing depends on <strong>material type, thickness, mesh pattern, opening ratio, sheet dimensions, and order quantity</strong>.</p>

<h3>Pricing Order (Most Expensive to Most Economical)</h3>
<ol>
<li>Stainless 316L expanded metal</li>
<li>Stainless 304 expanded metal</li>
<li>Aluminum expanded metal</li>
<li>Galvanized expanded metal</li>
<li>HRP expanded metal</li>
<li>CRS expanded metal (most economical)</li>
</ol>

<h2>Frequently Asked Questions (FAQ)</h2>

<h4>What is the smallest expanded metal mesh size?</h4>
<p>Standard production starts at <strong>22×10 mm</strong> (LWD×SWD). Smaller meshes (10×5 mm) are available as custom orders.</p>

<h4>What is the maximum thickness for expanded metal?</h4>
<p>Up to <strong>8 mm</strong> for standard production. Thicker custom orders are possible up to 12 mm using heavy-duty press equipment.</p>

<h4>Can expanded metal be welded?</h4>
<p>Yes. MIG, MAG, and TIG welding are all suitable. For galvanized expanded metal, the welded zone should be re-coated with zinc-rich spray paint.</p>

<h4>Do you provide custom dimensions?</h4>
<p>Yes. We accept custom orders for sheet sizes, mesh patterns, and finishing (painting, galvanizing). Minimum order: 5 m² for custom specifications.</p>

<h4>Do you ship internationally?</h4>
<p>Yes. We regularly ship to <strong>Iraq, Syria, Azerbaijan, and Turkmenistan</strong>. Customs documentation and certificate of origin are provided.</p>

<h4>What is the lead time for expanded metal?</h4>
<p>Standard sizes: <strong>same-day shipping</strong> from stock. Custom specifications: <strong>3-7 business days</strong>. Bulk orders (50+ sheets): 1-2 weeks.</p>

<h2>Why Choose Tekcan Metal for Expanded Metal</h2>

<ul>
<li>✅ <strong>20+ years industry experience</strong></li>
<li>✅ <strong>Manufacturer-certified supply</strong> (Erdemir, Borçelik, Tosyalı)</li>
<li>✅ <strong>4 standard mesh patterns in stock</strong> (22×10, 43×13, 62×20, 100×40)</li>
<li>✅ <strong>All materials:</strong> CRS, HRP, galvanized, stainless 304/316, aluminum</li>
<li>✅ <strong>Custom production</strong> for special projects</li>
<li>✅ <strong>81-province domestic shipping</strong> + international export</li>
<li>✅ <strong>EN 10204 3.1 mill certificates, e-invoice, customs documentation</strong></li>
</ul>

<p class="cta-block"><strong>Request a quote for expanded metal:</strong><br>
📞 +90 332 342 24 52 | 📱 WhatsApp +90 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Get a Quote</a> · <a href="/hesaplama.php" class="btn btn-ghost">Weight Calculator</a></p>

<p><em>Last updated: April 2026 — Tekcan Metal Industry and Trade Ltd.</em></p>
',
    meta_title_en = 'Expanded Metal: Sizes, Weight, Types and Applications — Tekcan Metal',
    meta_desc_en  = 'Expanded metal sheet types (CRS, HRP, galvanized, stainless, aluminum), standard sizes, weight calculation, applications. Konya-based Tekcan Metal — 20+ years, 81-province shipping.'
WHERE slug = 'genisletilmis-sac-rehberi';


-- ===== baklava-sac-rehberi (EN, ~888 kelime) =====
UPDATE tm_blog_posts SET
    title_en      = 'Diamond Plate (Checker Plate): Sizes, Weight, Types and Pricing',
    excerpt_en    = 'Diamond plate (checker plate, tread plate) types (carbon steel, stainless, aluminum, galvanized), standard sizes 2.5-10mm thickness, weight tables, applications and pricing. Tekcan Metal — Konya, 20+ years.',
    content_en    = '<p class="lead"><strong>Diamond plate</strong> (also known as checker plate, tread plate, or floor plate — Turkish: <em>baklava sac</em>) is a non-slip, decorative steel sheet with raised diamond or rhomboid patterns embossed on its surface. Tekcan Metal has been supplying diamond plate from our Konya headquarters across Turkey since 2005.</p>

<h2>What Is Diamond Plate? Production and Structure</h2>

<p>Diamond plate is produced by passing flat hot-rolled steel through specialized embossing rollers that create raised <strong>diamond or rhomboid patterns</strong> on one surface. The opposite face remains flat. The patterns provide enhanced grip, slip resistance, and decorative appeal.</p>

<p>Industry alternative names: "checker plate", "tread plate", "floor plate", "Riffelblech" (German), or "tôle larmée" (French).</p>

<h3>Pattern Types</h3>
<ol>
<li><strong>Two-bar diamond pattern:</strong> Most common — two parallel raised bars in diamond shape</li>
<li><strong>Three-bar diamond pattern:</strong> Three bars per diamond — extra grip for industrial floors</li>
<li><strong>Four-bar / five-bar:</strong> Heavy-duty patterns for industrial walkways and ramps</li>
<li><strong>Round button pattern:</strong> Decorative — used in furniture and design applications</li>
</ol>

<h2>Diamond Plate Standard Sizes and Thickness</h2>

<table>
<thead>
<tr><th>Sheet Size (mm)</th><th>Thickness Range</th><th>Typical Use</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>2.5 - 5 mm</td><td>Standard floor plates</td></tr>
<tr><td>1250 × 2500</td><td>3 - 6 mm</td><td>Industrial walkways</td></tr>
<tr><td>1500 × 3000</td><td>4 - 8 mm</td><td>Heavy-duty platforms</td></tr>
<tr><td>1500 × 6000</td><td>5 - 10 mm</td><td>Vehicle decking, ramps</td></tr>
<tr><td>Custom size</td><td>2 - 12 mm</td><td>By request</td></tr>
</tbody>
</table>

<h3>Common Thickness Specifications</h3>
<p>Diamond plate thickness measurement excludes the raised pattern height. The base sheet (flat side) determines the nominal thickness:</p>
<ul>
<li><strong>2-3 mm:</strong> Decorative panels, light-duty applications</li>
<li><strong>4-5 mm:</strong> Standard industrial floors, walkways, stairs</li>
<li><strong>6-8 mm:</strong> Heavy-duty platforms, truck beds, loading ramps</li>
<li><strong>10-12 mm:</strong> Mining equipment, heavy machinery decking</li>
</ul>

<h2>Diamond Plate Material Options</h2>

<h3>1. Hot-Rolled Steel Diamond Plate (Most Common)</h3>
<p>Standard carbon steel — economical and versatile. Available in S235JR, S275JR grades. Used in industrial floors, vehicle decking, and machinery platforms. Can be painted or galvanized.</p>

<h3>2. Galvanized Diamond Plate</h3>
<p>Hot-dip galvanized coating. <strong>Outdoor and humid environment</strong> applications: external staircases, outdoor walkways, and marine platforms.</p>

<h3>3. Stainless Steel Diamond Plate (304 / 316)</h3>
<p>Corrosion-resistant — used in <strong>food processing, chemical, and pharmaceutical industries</strong>. 316L grade for marine and aggressive environments.</p>

<h3>4. Aluminum Diamond Plate</h3>
<p>Lightweight (~1/3 of steel weight). Naturally corrosion-resistant. Used in <strong>truck bodies, trailer floors, marine applications</strong>, and decorative panels.</p>

<h2>Diamond Plate Weight Calculation</h2>

<p>Diamond plate weight (kg/m²) = base sheet weight + pattern overlay (~5-7%):</p>

<table>
<thead>
<tr><th>Thickness (mm)</th><th>Steel Weight (kg/m²)</th><th>Aluminum Weight (kg/m²)</th></tr>
</thead>
<tbody>
<tr><td>2.5</td><td>20.5</td><td>7.0</td></tr>
<tr><td>3</td><td>24.5</td><td>8.4</td></tr>
<tr><td>4</td><td>32.5</td><td>11.2</td></tr>
<tr><td>5</td><td>40.5</td><td>14.0</td></tr>
<tr><td>6</td><td>48.5</td><td>16.8</td></tr>
<tr><td>8</td><td>64.5</td><td>22.4</td></tr>
<tr><td>10</td><td>80.5</td><td>28.0</td></tr>
</tbody>
</table>

<p>Use our <a href="/hesaplama.php">online weight calculator</a> for precise project calculations.</p>

<h2>Diamond Plate Applications</h2>

<h3>Industrial Flooring</h3>
<ul>
<li><strong>Factory floors:</strong> Production area walkways</li>
<li><strong>Industrial stairs:</strong> Anti-slip step covering</li>
<li><strong>Mezzanine platforms:</strong> Storage area decking</li>
<li><strong>Loading ramps:</strong> Forklift and pallet truck access</li>
<li><strong>Catwalks:</strong> Maintenance walkways above machinery</li>
</ul>

<h3>Transportation</h3>
<ul>
<li><strong>Truck beds:</strong> Cargo bed flooring</li>
<li><strong>Trailer floors:</strong> Heavy-duty trailer platforms</li>
<li><strong>Bus and van floors:</strong> Anti-slip passenger floors</li>
<li><strong>Boat decks:</strong> Marine non-slip surfaces (aluminum)</li>
<li><strong>Vehicle ramps:</strong> Loading/unloading inclines</li>
</ul>

<h3>Construction</h3>
<ul>
<li><strong>Building stairs:</strong> External staircase treads</li>
<li><strong>Pedestrian walkways:</strong> Bridge surfaces, station platforms</li>
<li><strong>Drainage covers:</strong> Storm drain protection</li>
<li><strong>Trench covers:</strong> Industrial site protection</li>
</ul>

<h3>Decorative and Architectural</h3>
<ul>
<li>Modern interior wall accents</li>
<li>Counter and bar fronts</li>
<li>Garage door panels</li>
<li>Toolbox surfaces</li>
<li>Custom furniture detailing</li>
</ul>

<h2>Advantages of Diamond Plate</h2>

<ul>
<li><strong>Anti-slip surface:</strong> 30-50% better grip than flat sheet</li>
<li><strong>Self-cleaning pattern:</strong> Dirt and debris cannot accumulate easily</li>
<li><strong>Aesthetic appeal:</strong> Industrial-modern design</li>
<li><strong>Durability:</strong> Pattern provides additional structural rigidity</li>
<li><strong>Easy installation:</strong> Cut, drilled, welded with standard tools</li>
<li><strong>Cost-effective:</strong> Affordable compared to alternatives</li>
<li><strong>Wide thickness range:</strong> Light to heavy-duty applications</li>
<li><strong>100% recyclable:</strong> Sustainable steel/aluminum product</li>
</ul>

<h2>Diamond Plate Pricing (2026)</h2>

<p>Diamond plate pricing depends on <strong>material grade, thickness, sheet dimensions, surface treatment, and order quantity</strong>. Pricing is typically calculated per kilogram or per square meter.</p>

<h3>Pricing Order (Most Expensive to Most Economical)</h3>
<ol>
<li>Stainless 316L diamond plate</li>
<li>Stainless 304 diamond plate</li>
<li>Aluminum diamond plate</li>
<li>Galvanized diamond plate</li>
<li>Hot-rolled steel diamond plate (most economical)</li>
</ol>

<h2>Frequently Asked Questions (FAQ)</h2>

<h4>What is the difference between two-bar and four-bar diamond plate?</h4>
<p>The number of raised parallel bars per diamond shape. <strong>Two-bar</strong> is most common for general use. <strong>Four-bar</strong> patterns provide better grip on inclined surfaces and heavy industrial use.</p>

<h4>Can diamond plate be cut to size?</h4>
<p>Yes. We provide custom cutting services using plasma, laser, or shearing. Tolerance: ±1 mm.</p>

<h4>What is the slip resistance rating?</h4>
<p>Diamond plate typically achieves <strong>R10-R12</strong> slip resistance ratings (German DIN 51130 standard) depending on pattern depth and thickness.</p>

<h4>Can diamond plate be galvanized?</h4>
<p>Yes. We offer hot-dip galvanizing with 50-200 g/m² zinc coating. The pattern remains visible after galvanizing.</p>

<h4>What is the lead time?</h4>
<p>Standard sizes (1500×3000, 4-5 mm): <strong>same-day shipping</strong> from stock. Custom dimensions: <strong>3-5 business days</strong>. Galvanized custom orders: 1-2 weeks.</p>

<h4>Do you ship internationally?</h4>
<p>Yes. We regularly export to <strong>Iraq, Syria, Azerbaijan, Turkmenistan</strong>. Customs documentation and certificate of origin provided.</p>

<h2>Why Choose Tekcan Metal for Diamond Plate</h2>

<ul>
<li>✅ <strong>20+ years industry experience</strong></li>
<li>✅ <strong>Manufacturer-certified supply</strong> (Erdemir, Borçelik, Tosyalı)</li>
<li>✅ <strong>Wide stock:</strong> 2.5–10 mm thicknesses</li>
<li>✅ <strong>All materials:</strong> hot-rolled, galvanized, stainless, aluminum</li>
<li>✅ <strong>Custom cutting:</strong> Plasma, laser, shearing services</li>
<li>✅ <strong>81-province domestic shipping</strong> + international export</li>
<li>✅ <strong>EN 10204 3.1 mill certificates included</strong></li>
</ul>

<p class="cta-block"><strong>Request a quote for diamond plate:</strong><br>
📞 +90 332 342 24 52 | 📱 WhatsApp +90 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Get a Quote</a> · <a href="/hesaplama.php" class="btn btn-ghost">Weight Calculator</a></p>

<p><em>Last updated: April 2026 — Tekcan Metal Industry and Trade Ltd.</em></p>
',
    meta_title_en = 'Diamond Plate (Checker Plate): Sizes, Weight, Types — Tekcan Metal',
    meta_desc_en  = 'Diamond plate (checker plate, tread plate, baklava sac) types, sizes, thickness, weight tables, anti-slip flooring applications. Konya-based Tekcan Metal — 20+ years experience.'
WHERE slug = 'baklava-sac-rehberi';


-- ===== galvanizli-sac-rehberi (EN, ~916 kelime) =====
UPDATE tm_blog_posts SET
    title_en      = 'Galvanized Sheet: Z140-Z600 Coating Classes, Sizes, Weight and Applications',
    excerpt_en    = 'Galvanized steel sheet (HDG, EG, Galvalume) types, coating classes (Z100-Z600), thickness, weight tables, construction and HVAC applications. Tekcan Metal — Erdemir/Borçelik certified, 81-province shipping.',
    content_en    = '<p class="lead"><strong>Galvanized steel sheet</strong> (galvanized sheet, GI sheet) is steel sheet that has been coated with a layer of zinc through hot-dip immersion or electro-galvanizing, providing exceptional corrosion resistance and a service life of 25-50 years. Tekcan Metal has been supplying galvanized sheet from our Konya headquarters across Turkey since 2005.</p>

<h2>What Is Galvanized Sheet? Production and Coating Process</h2>

<p>Galvanized steel sheet is produced by passing flat steel sheet through a molten zinc bath at <strong>460°C</strong>. The zinc bonds metallurgically with the steel surface, creating an iron-zinc alloy layer that serves as both physical and electrochemical (sacrificial) protection.</p>

<p>Industry alternative names: "GI sheet" (galvanized iron), "zinc-coated steel", "hot-dip galvanized" (HDG).</p>

<h3>Production Methods</h3>

<h4>1. Hot-Dip Galvanizing (HDG) — Most Common</h4>
<p>Steel sheet is fully immersed in molten zinc. Coating thickness: <strong>50-600 g/m²</strong> (Z100-Z600 designations). Best for <strong>outdoor, structural, and long-life applications</strong>.</p>

<h4>2. Electro-Galvanizing (EG)</h4>
<p>Zinc deposited via electrolysis. Thinner coating (5-25 g/m²) but excellent surface uniformity. Used for <strong>automotive panels, appliances, and applications requiring paint adhesion</strong>.</p>

<h4>3. Galvalume (55% Al-Zn)</h4>
<p>Aluminum-zinc alloy coating. <strong>2-4× longer atmospheric corrosion resistance</strong> than standard galvanizing. Used in roofing, siding, and harsh environments.</p>

<h4>4. Galfan (95% Zn / 5% Al)</h4>
<p>Zinc-aluminum alloy. Better formability than HDG. Used in cold-formed sections and severe forming operations.</p>

<h2>Coating Class Designations (Z-Code)</h2>

<table>
<thead>
<tr><th>Designation</th><th>Coating (g/m²)</th><th>Equivalent Microns</th><th>Typical Use</th></tr>
</thead>
<tbody>
<tr><td>Z100</td><td>100</td><td>~7 µm</td><td>Light indoor applications</td></tr>
<tr><td>Z140</td><td>140</td><td>~10 µm</td><td>Standard indoor / mild outdoor</td></tr>
<tr><td>Z200</td><td>200</td><td>~14 µm</td><td>General outdoor use</td></tr>
<tr><td>Z275</td><td>275</td><td>~20 µm</td><td>Severe outdoor — most common</td></tr>
<tr><td>Z350</td><td>350</td><td>~25 µm</td><td>Heavy industrial</td></tr>
<tr><td>Z450</td><td>450</td><td>~32 µm</td><td>Very corrosive environments</td></tr>
<tr><td>Z600</td><td>600</td><td>~42 µm</td><td>Extreme conditions, marine</td></tr>
</tbody>
</table>

<p>The numbers indicate <strong>total zinc coating per square meter</strong> (both sides combined). Higher Z values mean longer service life in corrosive conditions.</p>

<h2>Galvanized Sheet Standard Sizes</h2>

<table>
<thead>
<tr><th>Sheet Size (mm)</th><th>Thickness Range</th><th>Common Uses</th></tr>
</thead>
<tbody>
<tr><td>1000 × 2000</td><td>0.4 - 3 mm</td><td>HVAC ducting, roofing</td></tr>
<tr><td>1250 × 2500</td><td>0.5 - 4 mm</td><td>Automotive, appliances</td></tr>
<tr><td>1500 × 3000</td><td>0.7 - 6 mm</td><td>Industrial structures</td></tr>
<tr><td>Coil (slit)</td><td>0.3 - 4 mm</td><td>Continuous production</td></tr>
</tbody>
</table>

<h3>Surface Finishes</h3>
<ul>
<li><strong>Regular spangle:</strong> Visible crystalline pattern (traditional)</li>
<li><strong>Minimized spangle:</strong> Smaller pattern — better paintability</li>
<li><strong>Zero spangle:</strong> Smooth surface — premium painting</li>
<li><strong>Skin-passed:</strong> Cold-rolled finish for tight tolerances</li>
</ul>

<h2>Galvanized Sheet Weight Calculation</h2>

<p>Weight (kg/m²) = thickness (mm) × 7.85 (steel density). Zinc coating adds approximately 1-2% weight:</p>

<table>
<thead>
<tr><th>Thickness (mm)</th><th>Weight (kg/m²)</th><th>Z275 Coated Weight (kg/m²)</th></tr>
</thead>
<tbody>
<tr><td>0.5</td><td>3.93</td><td>4.20</td></tr>
<tr><td>0.7</td><td>5.50</td><td>5.78</td></tr>
<tr><td>1.0</td><td>7.85</td><td>8.13</td></tr>
<tr><td>1.5</td><td>11.78</td><td>12.05</td></tr>
<tr><td>2.0</td><td>15.70</td><td>15.98</td></tr>
<tr><td>3.0</td><td>23.55</td><td>23.83</td></tr>
<tr><td>4.0</td><td>31.40</td><td>31.68</td></tr>
</tbody>
</table>

<h2>Galvanized Sheet Applications</h2>

<h3>Construction and Building</h3>
<ul>
<li><strong>Roofing systems:</strong> Trapezoidal sheets, sandwich panels, shingles</li>
<li><strong>Wall cladding:</strong> Industrial building façades</li>
<li><strong>Steel framing:</strong> Cold-formed structural sections (C, Z, U profiles)</li>
<li><strong>Drainage systems:</strong> Gutters, downspouts, splash blocks</li>
<li><strong>HVAC ducting:</strong> Air distribution channels</li>
</ul>

<h3>Automotive and Transport</h3>
<ul>
<li>Body panels (electro-galvanized for paintability)</li>
<li>Underbody components</li>
<li>Truck and trailer bodies</li>
<li>Fuel tanks (interior)</li>
<li>Wheel wells</li>
</ul>

<h3>Industrial Equipment</h3>
<ul>
<li><strong>Storage tanks:</strong> Water, agricultural chemical tanks</li>
<li><strong>Silos:</strong> Grain and feed storage</li>
<li><strong>Cabinets:</strong> Outdoor electrical enclosures</li>
<li><strong>Conveyor systems:</strong> Industrial material handling</li>
</ul>

<h3>Agriculture and Livestock</h3>
<ul>
<li>Livestock barns (roofing, walls)</li>
<li>Greenhouse structures</li>
<li>Feed bins and silos</li>
<li>Irrigation system components</li>
</ul>

<h3>Appliances and Consumer Goods</h3>
<ul>
<li>Refrigerator panels</li>
<li>Washing machine bodies</li>
<li>Air conditioner outdoor units</li>
<li>Furniture frames</li>
</ul>

<h2>Advantages of Galvanized Sheet</h2>

<ul>
<li><strong>Corrosion protection:</strong> Zinc oxidizes sacrificially before steel — 25-50 year lifespan</li>
<li><strong>Self-healing:</strong> Minor scratches re-cover via zinc patina</li>
<li><strong>Cost-effective:</strong> 60-70% cheaper than stainless steel</li>
<li><strong>Recyclable:</strong> 100% steel and zinc — sustainable</li>
<li><strong>Paintable:</strong> Especially electro-galvanized variants</li>
<li><strong>Formable:</strong> Cold-rolled and pressed without coating damage</li>
<li><strong>Weldable:</strong> Standard MIG, MAG, spot welding (with zinc fume ventilation)</li>
<li><strong>Wide availability:</strong> Standard stock in all common sizes</li>
</ul>

<h2>Galvanized Sheet vs Other Materials</h2>

<table>
<thead>
<tr><th>Property</th><th>Galvanized</th><th>Stainless</th><th>Aluminum</th><th>Black Steel</th></tr>
</thead>
<tbody>
<tr><td>Corrosion resistance</td><td>Excellent (25-50 yr)</td><td>Outstanding (50+ yr)</td><td>Outstanding</td><td>Poor (5-10 yr)</td></tr>
<tr><td>Cost</td><td>Medium</td><td>High</td><td>Medium-High</td><td>Low</td></tr>
<tr><td>Strength</td><td>High</td><td>Very high</td><td>Medium</td><td>High</td></tr>
<tr><td>Weldability</td><td>Good</td><td>Excellent</td><td>Specialized</td><td>Excellent</td></tr>
<tr><td>Paintability</td><td>Good (EG variant)</td><td>Limited</td><td>Limited</td><td>Excellent</td></tr>
<tr><td>Typical use</td><td>Construction, HVAC</td><td>Food, chemical</td><td>Aerospace, marine</td><td>Industrial fabrication</td></tr>
</tbody>
</table>

<h2>Galvanized Sheet Pricing (2026)</h2>

<p>Galvanized sheet pricing depends on <strong>thickness, coating class (Z-value), sheet dimensions, surface finish, and order quantity</strong>. LME zinc prices and HRC steel prices significantly impact daily pricing.</p>

<h3>Pricing Factors</h3>
<ul>
<li><strong>Coating class:</strong> Z275 standard; Z350+ premium</li>
<li><strong>Surface finish:</strong> Zero spangle costs more than regular spangle</li>
<li><strong>Mill source:</strong> Erdemir, Borçelik, Tosyalı, Tezcan certified</li>
<li><strong>Quantity:</strong> Tonnage-based discount tiers</li>
</ul>

<h2>Frequently Asked Questions (FAQ)</h2>

<h4>How long does galvanized sheet last?</h4>
<p>Service life depends on environment: <strong>50+ years indoor</strong>, 25-40 years outdoor (mild), 15-25 years aggressive (industrial/marine). Higher Z-codes extend lifetime.</p>

<h4>Can galvanized sheet be painted?</h4>
<p>Yes, but proper surface preparation is essential. Phosphate or chromate pre-treatment ensures paint adhesion. Electro-galvanized variants paint better than HDG.</p>

<h4>What''s the difference between Z275 and G90?</h4>
<p>Same coating thickness, different naming conventions. Z275 (European) = G90 (American) = ~275 g/m² total zinc coating.</p>

<h4>Can galvanized sheet be welded?</h4>
<p>Yes, with proper precautions. Zinc vapor is released during welding — ensure ventilation. The welded zone should be re-coated with zinc-rich primer.</p>

<h4>Do you ship internationally?</h4>
<p>Yes. We regularly export to <strong>Iraq, Syria, Azerbaijan, Turkmenistan</strong>. Customs documentation and certificate of origin provided.</p>

<h4>What is the lead time?</h4>
<p>Standard sizes/coatings: <strong>same-day shipping</strong>. Custom sizes or premium coatings (Z450+): <strong>5-10 business days</strong>.</p>

<h2>Why Choose Tekcan Metal for Galvanized Sheet</h2>

<ul>
<li>✅ <strong>20+ years industry experience</strong></li>
<li>✅ <strong>Mill-certified supply</strong> (Erdemir, Borçelik, Tosyalı, Tezcan)</li>
<li>✅ <strong>All Z-coatings:</strong> Z100-Z600 in stock</li>
<li>✅ <strong>Wide thickness range:</strong> 0.3-6 mm</li>
<li>✅ <strong>Custom cutting and slitting</strong></li>
<li>✅ <strong>Full sheet, coil, or pre-cut available</strong></li>
<li>✅ <strong>EN 10204 3.1 mill certificates included</strong></li>
<li>✅ <strong>81-province shipping</strong> + international export</li>
</ul>

<p class="cta-block"><strong>Request a quote for galvanized sheet:</strong><br>
📞 +90 332 342 24 52 | 📱 WhatsApp +90 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Get a Quote</a> · <a href="/hesaplama.php" class="btn btn-ghost">Weight Calculator</a></p>

<p><em>Last updated: April 2026 — Tekcan Metal Industry and Trade Ltd.</em></p>
',
    meta_title_en = 'Galvanized Sheet (Z140-Z600): Sizes, Weight, Coating Classes — Tekcan Metal',
    meta_desc_en  = 'Galvanized steel sheet (GI, HDG, Galvalume) coating classes Z100-Z600, sizes, weight, construction/HVAC/automotive applications. Konya-based Tekcan Metal — mill-certified supply.'
WHERE slug = 'galvanizli-sac-rehberi';


-- ===== trapez-sac-rehberi (EN, ~935 kelime) =====
UPDATE tm_blog_posts SET
    title_en      = 'Trapezoidal Sheet (Corrugated Sheet): TR-18 to TR-55 Profiles, Colors, Pricing',
    excerpt_en    = 'Trapezoidal sheet (corrugated metal, profiled roofing) TR-18/TR-27/TR-35/TR-45/TR-55 profiles, RAL colors, weight tables, roofing/cladding applications. Tekcan Metal — full color range, 81-province shipping.',
    content_en    = '<p class="lead"><strong>Trapezoidal sheet</strong> (corrugated metal sheet, profiled roofing sheet) is a structural metal sheet rolled into trapezoidal-shaped corrugations, providing high load-bearing capacity per unit weight. Tekcan Metal has been supplying trapezoidal sheet from our Konya headquarters across Turkey since 2005.</p>

<h2>What Is Trapezoidal Sheet? Production and Profiles</h2>

<p>Trapezoidal sheet is produced by passing flat (typically galvanized or pre-painted) steel coil through <strong>roll-forming machines</strong> that progressively bend the sheet into the desired trapezoidal profile. The corrugation depth and pitch are critical performance characteristics.</p>

<p>Industry alternative names: "corrugated sheet", "profiled metal sheet", "roof deck", "TR profile", "wall cladding sheet".</p>

<h3>Profile Designation System</h3>
<p>In Turkey and Europe, trapezoidal sheet profiles are designated as <strong>TR-X</strong> where X = corrugation depth in millimeters:</p>

<ul>
<li><strong>TR-18:</strong> 18 mm depth — light wall cladding, fences</li>
<li><strong>TR-27:</strong> 27 mm depth — standard roofing for sheds, garages</li>
<li><strong>TR-35:</strong> 35 mm depth — industrial roofing — most common</li>
<li><strong>TR-45:</strong> 45 mm depth — large-span industrial roofing</li>
<li><strong>TR-55:</strong> 55 mm depth — heavy-duty load-bearing decks</li>
<li><strong>TR-100, TR-135:</strong> Floor decking — composite slab construction</li>
</ul>

<h2>Trapezoidal Sheet Dimensions</h2>

<table>
<thead>
<tr><th>Profile</th><th>Effective Width (mm)</th><th>Pitch (mm)</th><th>Length</th><th>Thickness</th></tr>
</thead>
<tbody>
<tr><td>TR-18</td><td>1080</td><td>180</td><td>2-12 m</td><td>0.4-0.7 mm</td></tr>
<tr><td>TR-27</td><td>1100</td><td>183</td><td>2-12 m</td><td>0.4-0.8 mm</td></tr>
<tr><td>TR-35</td><td>1100</td><td>275</td><td>2-12 m</td><td>0.5-1.0 mm</td></tr>
<tr><td>TR-45</td><td>1100</td><td>275</td><td>2-12 m</td><td>0.5-1.2 mm</td></tr>
<tr><td>TR-55</td><td>1000</td><td>250</td><td>2-12 m</td><td>0.7-1.5 mm</td></tr>
</tbody>
</table>

<p>Lengths can be cut to project requirements. Standard transportation length: 6-12 meters.</p>

<h2>Trapezoidal Sheet Material Options</h2>

<h3>1. Galvanized Trapezoidal Sheet</h3>
<p>Most common base material. Hot-dip galvanized (Z275 typical) provides 25-40 year corrosion resistance. Used in <strong>industrial roofing, agricultural buildings, warehouses</strong>.</p>

<h3>2. Pre-Painted (PPGI/PPGL) Trapezoidal Sheet</h3>
<p>Galvanized base + factory-applied paint coating (polyester, PVDF, plastisol). Available in <strong>RAL color spectrum</strong>:</p>
<ul>
<li><strong>RAL 3005:</strong> Wine red — traditional roof color</li>
<li><strong>RAL 3009:</strong> Oxide red — industrial standard</li>
<li><strong>RAL 6020:</strong> Chrome green — agricultural buildings</li>
<li><strong>RAL 7016:</strong> Anthracite gray — modern architecture</li>
<li><strong>RAL 9006:</strong> White aluminum — solar reflectance</li>
<li><strong>RAL 9010:</strong> Pure white — clean rooms, food industry</li>
</ul>

<h3>3. Aluminum-Zinc Coated (Galvalume)</h3>
<p>55% Al / 45% Zn coating — <strong>2-4× longer atmospheric corrosion resistance</strong> than galvanized. Used in coastal areas and industrial environments.</p>

<h3>4. Aluminum Trapezoidal Sheet</h3>
<p>Pure aluminum — <strong>1/3 weight of steel</strong>, marine corrosion resistance, used in coastal buildings and chemical industries.</p>

<h2>Trapezoidal Sheet Weight Calculation</h2>

<p>Weight per linear meter (kg/m) calculation:</p>
<p><strong>Weight = Effective Width × Thickness × 7.85 / 1000 × Profile Coefficient</strong></p>

<p>Profile coefficient accounts for the additional material in corrugations (typically 1.05-1.15).</p>

<h3>Practical Weight Reference</h3>

<table>
<thead>
<tr><th>Profile + Thickness</th><th>Weight (kg/m²)</th><th>Weight (kg/m of sheet)</th></tr>
</thead>
<tbody>
<tr><td>TR-27 × 0.5 mm</td><td>4.10</td><td>4.50</td></tr>
<tr><td>TR-35 × 0.5 mm</td><td>4.30</td><td>4.73</td></tr>
<tr><td>TR-35 × 0.7 mm</td><td>5.95</td><td>6.55</td></tr>
<tr><td>TR-45 × 0.7 mm</td><td>6.10</td><td>6.71</td></tr>
<tr><td>TR-45 × 1.0 mm</td><td>8.65</td><td>9.52</td></tr>
<tr><td>TR-55 × 1.0 mm</td><td>9.10</td><td>9.10</td></tr>
</tbody>
</table>

<h2>Trapezoidal Sheet Applications</h2>

<h3>Roofing</h3>
<ul>
<li><strong>Industrial buildings:</strong> Factories, warehouses, distribution centers</li>
<li><strong>Agricultural structures:</strong> Barns, livestock shelters, grain storage</li>
<li><strong>Commercial buildings:</strong> Shopping centers, hangars</li>
<li><strong>Residential:</strong> Garages, sheds, garden structures</li>
<li><strong>Sports facilities:</strong> Sports halls, indoor arenas</li>
</ul>

<h3>Wall Cladding</h3>
<ul>
<li>Industrial building façades</li>
<li>Cold storage exterior walls</li>
<li>Equipment housing enclosures</li>
<li>Container and modular buildings</li>
</ul>

<h3>Floor Decking</h3>
<ul>
<li><strong>Composite slabs:</strong> TR-100/TR-135 with concrete topping</li>
<li>Multi-story building floors</li>
<li>Mezzanine platforms</li>
<li>Bridge decks (light pedestrian)</li>
</ul>

<h3>Special Applications</h3>
<ul>
<li>Sound barriers along highways</li>
<li>Solar panel mounting structures</li>
<li>Agricultural greenhouses</li>
<li>Water channel covers</li>
</ul>

<h2>Advantages of Trapezoidal Sheet</h2>

<ul>
<li><strong>High strength-to-weight ratio:</strong> Corrugation provides structural rigidity</li>
<li><strong>Long spans:</strong> 2-6 meter unsupported spans (depending on profile)</li>
<li><strong>Quick installation:</strong> Light, large-format pieces accelerate construction</li>
<li><strong>Weather resistance:</strong> Zinc + paint coating = 25-50 year service life</li>
<li><strong>Wide color range:</strong> RAL spectrum factory-painted options</li>
<li><strong>Cost-effective:</strong> Lower material and labor costs vs alternatives</li>
<li><strong>Recyclable:</strong> 100% recyclable steel product</li>
<li><strong>Fire-resistant:</strong> Steel — Class A1 non-combustible</li>
</ul>

<h2>Trapezoidal Sheet vs Other Roofing</h2>

<table>
<thead>
<tr><th>Feature</th><th>Trapezoidal</th><th>Sandwich Panel</th><th>Tile</th><th>Membrane</th></tr>
</thead>
<tbody>
<tr><td>Cost (per m²)</td><td>Low</td><td>Medium-High</td><td>Medium</td><td>High</td></tr>
<tr><td>Insulation</td><td>None</td><td>Excellent (built-in)</td><td>Limited</td><td>None (top layer)</td></tr>
<tr><td>Lifespan</td><td>25-50 years</td><td>30-40 years</td><td>50+ years</td><td>15-25 years</td></tr>
<tr><td>Installation speed</td><td>Very fast</td><td>Fast</td><td>Slow</td><td>Medium</td></tr>
<tr><td>Span capability</td><td>2-6 m</td><td>3-8 m</td><td>0.5-1 m</td><td>Continuous</td></tr>
<tr><td>Best use</td><td>Industrial, agricultural</td><td>Insulated buildings</td><td>Residential, traditional</td><td>Flat roofs</td></tr>
</tbody>
</table>

<h2>Trapezoidal Sheet Pricing (2026)</h2>

<p>Trapezoidal sheet pricing depends on <strong>profile depth, base material, coating type, paint quality, length, and order quantity</strong>.</p>

<h3>Pricing Order (Most to Least Economical)</h3>
<ol>
<li>PVDF/Plastisol painted (premium)</li>
<li>Polyester painted (RAL standard colors)</li>
<li>Aluminum-zinc coated (Galvalume)</li>
<li>Galvanized only (Z275)</li>
<li>Galvanized only (Z140) — most economical</li>
</ol>

<h2>Frequently Asked Questions (FAQ)</h2>

<h4>What''s the difference between TR-27 and TR-35?</h4>
<p>The corrugation depth: 27 mm vs 35 mm. <strong>TR-35 spans further</strong> and carries more snow/wind load. TR-27 is sufficient for small spans (2-3 m). TR-35 is the industrial standard.</p>

<h4>What roof slope is required?</h4>
<p>Minimum recommended slope: <strong>5° (8.7%)</strong> for trapezoidal sheets. For lower slopes, additional sealing at overlaps is required.</p>

<h4>Can trapezoidal sheets be cut on-site?</h4>
<p>Yes, with electric shears or angle grinders. <strong>Avoid abrasive disc cutting</strong> as it damages the coating. Cold cutting is preferred.</p>

<h4>How are sheets installed?</h4>
<p>With <strong>self-drilling screws with EPDM washers</strong>. Standard pattern: 2 fixings per corrugation crest at each support. Overlap: 1 corrugation side / 200 mm end.</p>

<h4>What colors are available?</h4>
<p>Full <strong>RAL color spectrum</strong>. Most common: RAL 3005 (wine red), RAL 7016 (anthracite), RAL 9010 (pure white), RAL 6020 (green).</p>

<h4>Do you ship internationally?</h4>
<p>Yes. We regularly export to <strong>Iraq, Syria, Azerbaijan, Turkmenistan</strong>. Container loads typically 12-meter sheets.</p>

<h2>Why Choose Tekcan Metal for Trapezoidal Sheet</h2>

<ul>
<li>✅ <strong>20+ years industry experience</strong></li>
<li>✅ <strong>Mill-certified supply</strong> (Borçelik, Tezcan, Tata)</li>
<li>✅ <strong>All profiles in stock:</strong> TR-18, TR-27, TR-35, TR-45, TR-55</li>
<li>✅ <strong>Custom length cutting:</strong> 2-12 m</li>
<li>✅ <strong>Full RAL color range</strong></li>
<li>✅ <strong>Galvanized, Galvalume, painted variants</strong></li>
<li>✅ <strong>EN 10204 3.1 mill certificates included</strong></li>
<li>✅ <strong>81-province shipping</strong> + international container shipping</li>
</ul>

<p class="cta-block"><strong>Request a quote for trapezoidal sheet:</strong><br>
📞 +90 332 342 24 52 | 📱 WhatsApp +90 532 065 24 00 | ✉ info@tekcanmetal.com<br>
<a href="/iletisim.php" class="btn btn-primary">Get a Quote</a> · <a href="/hesaplama.php" class="btn btn-ghost">Weight Calculator</a></p>

<p><em>Last updated: April 2026 — Tekcan Metal Industry and Trade Ltd.</em></p>
',
    meta_title_en = 'Trapezoidal Sheet TR-18 to TR-55: Profiles, Colors, Pricing — Tekcan Metal',
    meta_desc_en  = 'Trapezoidal sheet (corrugated metal) profiles TR-18, TR-27, TR-35, TR-45, TR-55, weight tables, RAL color range, roofing and cladding applications. Konya-based Tekcan Metal.'
WHERE slug = 'trapez-sac-rehberi';
