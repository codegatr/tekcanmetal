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

