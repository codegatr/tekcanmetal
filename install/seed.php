<?php
/**
 * Tekcan Metal — Varsayılan Veriler (Seed)
 * Kurulum sırasında doldurulur
 */

return [

// ===== AYARLAR =====
'settings' => [
    ['site_name',         'Tekcan Metal Sanayi ve Ticaret Ltd. Şti.', 'general'],
    ['site_short_name',   'Tekcan Metal',                              'general'],
    ['site_slogan',       'Ticaret ile Bitmeyen Dostluk',              'general'],
    ['site_description',  'Demir adına Herşey... Sac, boru, profil, hadde ve özel çelik ürünleri ile inşaat, sanayi ve OEM müşterilerine 7/24 hizmet.', 'general'],
    ['site_keywords',     'demir, çelik, sac, boru, profil, hadde, lazer kesim, oksijen kesim, dekoratif sac, konya demir, karatay demir', 'seo'],
    ['site_email',        'info@tekcanmetal.com',                      'contact'],
    ['site_email_satis',  'satis@tekcanmetal.com',                     'contact'],
    ['site_phone',        '0 332 342 24 52',                           'contact'],
    ['site_mobile',       '0 554 835 0 226',                           'contact'],
    ['site_whatsapp',     '905548350226',                              'contact'],
    ['site_address',      'Fevziçakmak Mah. Gülistan Cad. Atiker 3, 2.Blok No:33 AS',  'contact'],
    ['site_district',     'Karatay',                                   'contact'],
    ['site_city',         'Konya',                                     'contact'],
    ['site_country',      'Türkiye',                                   'contact'],
    ['site_postcode',     '42050',                                     'contact'],
    ['site_map_lat',      '37.9089',                                   'contact'],
    ['site_map_lng',      '32.5524',                                   'contact'],
    ['site_facebook',     'https://www.facebook.com/tekcanmetal',      'social'],
    ['site_instagram',    '',                                          'social'],
    ['site_linkedin',     '',                                          'social'],
    ['site_youtube',      '',                                          'social'],
    ['site_twitter',      '',                                          'social'],
    ['working_hours',     'Pazartesi–Cumartesi: 08:00 – 18:00',        'general'],
    ['founded_year',      '2010',                                      'general'],
    ['tax_office',        'Selçuk',                                    'general'],
    ['tax_no',            '',                                          'general'],
    ['mersis_no',         '',                                          'general'],
    ['logo',              'assets/img/logo.svg',                       'branding'],
    ['logo_white',        'assets/img/logo-white.svg',                 'branding'],
    ['favicon',           'assets/img/favicon.png',                    'branding'],
    ['theme_primary',     '#1a2b4a',                                   'branding'],
    ['theme_accent',      '#c9a961',                                   'branding'],
    ['smtp_enabled',      '0',                                         'mail'],
    ['smtp_host',         '',                                          'mail'],
    ['smtp_port',         '587',                                       'mail'],
    ['smtp_user',         '',                                          'mail'],
    ['smtp_pass',         '',                                          'mail'],
    ['smtp_from_email',   'noreply@tekcanmetal.com',                   'mail'],
    ['smtp_from_name',    'Tekcan Metal',                              'mail'],
    ['recipient_emails',  'info@tekcanmetal.com',                      'mail'],
    ['kvkk_accepted_required','1',                                     'legal'],
    ['github_repo',       'codegatr/tekcanmetal',                      'system'],
    ['github_token',      '',                                          'system'],
    ['analytics_code',    '',                                          'seo'],
    ['maintenance_mode',  '0',                                         'system'],
    ['homepage_about_title', 'Birlikte Daha Güçlüyüz',                 'homepage'],
    ['homepage_about_text',  'Tekcan Metal, 2010 yılından bu yana demir-çelik sektöründe geniş ürün yelpazesi, hızlı sevkiyat ve müşteri odaklı hizmet anlayışıyla Konya başta olmak üzere tüm Türkiye’ye hizmet vermektedir. "Ticaret ile Bitmeyen Dostluk" felsefemizle, müşterilerimizle uzun soluklu iş ortaklıkları kuruyoruz.',  'homepage'],
    ['stat_year',         '15+',                                       'homepage'],
    ['stat_year_label',   'Yıllık Tecrübe',                            'homepage'],
    ['stat_products',     '500+',                                      'homepage'],
    ['stat_products_label','Ürün Çeşidi',                              'homepage'],
    ['stat_customers',    '1.000+',                                    'homepage'],
    ['stat_customers_label','Mutlu Müşteri',                           'homepage'],
    ['stat_delivery',     '7/24',                                      'homepage'],
    ['stat_delivery_label','Sevkiyat Hizmeti',                         'homepage'],
],

// ===== SAYFALAR =====
'pages' => [
    [
        'slug' => 'hakkimizda',
        'title' => 'Hakkımızda',
        'subtitle' => 'Tekcan Metal — Demir adına Herşey...',
        'content' => '<p class="lead">Tekcan Metal, 2010 yılında Konya’nın Karatay ilçesinde, Fevziçakmak Sanayi Bölgesi’nde demir-çelik ticareti alanında faaliyet göstermek üzere kurulmuştur.</p>

<p>Kuruluşumuzdan bu yana <strong>"Ticaret ile Bitmeyen Dostluk"</strong> felsefesiyle hareket eden firmamız, müşterilerini bir aile olarak görmüş, kalıcı ve güvene dayalı iş ortaklıkları kurmaya öncelik vermiştir.</p>

<h3>Ürün Yelpazemiz</h3>
<p>Stoklarımızda <strong>siyah sac, DKP, HRP, ST-52, galvanizli sac</strong>; su, kazan ve konstrüksiyon <strong>boruları</strong>; kare, dikdörtgen ve oval <strong>profiller</strong>; lama, silme, köşebent, HEA/HEB, NPI, NPU ve kare demiri gibi <strong>hadde ürünleri</strong>; <strong>patent dirsek, norm flanş, petek kiriş, çatı/cephe paneli, nervürlü inşaat demiri ve çelik hasır</strong> başta olmak üzere geniş bir ürün yelpazesi bulunmaktadır.</p>

<h3>Hizmetlerimiz</h3>
<p>Ürün satışının yanı sıra <strong>lazer kesim, oksijen kesim ve dekoratif sac</strong> üretim hizmetlerimizle müşterilerimizin özel projelerine de çözüm üretmekteyiz.</p>

<h3>Hedefimiz</h3>
<p>Stok derinliğimiz, hızlı sevkiyat ağımız ve uzman kadromuzla; inşaat sektöründen sanayi üreticilerine, OEM firmalarından bireysel ustalara kadar geniş bir müşteri kitlesine kesintisiz hizmet sunmaktır.</p>',
        'meta_desc' => 'Tekcan Metal — 2010’dan bu yana Konya merkezli demir-çelik tedarikçisi. Sac, boru, profil, hadde ürünlerinde stok ve hızlı sevkiyat.',
        'sort_order' => 1,
    ],
    [
        'slug' => 'kvkk',
        'title' => 'Kişisel Verilerin Korunması',
        'subtitle' => 'KVKK Aydınlatma Metni',
        'content' => '<h3>1. Veri Sorumlusu</h3>
<p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, kişisel verileriniz <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong> ("Şirket") tarafından, aşağıda açıklanan kapsamda işlenebilecektir.</p>

<h3>2. Kişisel Verilerin Toplanma Yöntemi</h3>
<p>Kişisel verileriniz; web sitemizdeki iletişim formları, mail order başvuruları, müşteri sadakat üyelikleri, telefon görüşmeleri, ticari elektronik iletişim ve fiziksel ziyaretleriniz aracılığıyla toplanmaktadır.</p>

<h3>3. İşlenen Veriler</h3>
<ul>
<li>Kimlik bilgileri (ad, soyad)</li>
<li>İletişim bilgileri (telefon, e-posta, adres)</li>
<li>Müşteri işlem bilgileri (sipariş, sevkiyat, ödeme)</li>
<li>Web sitesi kullanım bilgileri (IP, çerez)</li>
</ul>

<h3>4. İşleme Amaçları</h3>
<p>Verileriniz; ticari faaliyetlerin yürütülmesi, sipariş ve sevkiyat süreçlerinin yönetilmesi, müşteri ilişkileri yönetimi, yasal yükümlülüklerin yerine getirilmesi ve hizmet kalitesinin geliştirilmesi amaçlarıyla işlenmektedir.</p>

<h3>5. Haklarınız</h3>
<p>KVKK’nın 11. maddesi uyarınca; kişisel verilerinizin işlenip işlenmediğini öğrenme, işlenmişse buna ilişkin bilgi talep etme, düzeltilmesini, silinmesini veya yok edilmesini isteme haklarına sahipsiniz. Talepleriniz için <a href="mailto:info@tekcanmetal.com">info@tekcanmetal.com</a> adresine başvurabilirsiniz.</p>',
        'sort_order' => 2,
    ],
    [
        'slug' => 'cerez-politikasi',
        'title' => 'Çerez Politikası',
        'subtitle' => 'Web sitemizde kullanılan çerezler hakkında',
        'content' => '<p>Web sitemiz; ziyaretçi deneyimini iyileştirmek, sayfa kullanım istatistiklerini analiz etmek ve oturum yönetimi yapmak amacıyla çerezler kullanmaktadır.</p>

<h3>Kullanılan Çerez Türleri</h3>
<ul>
<li><strong>Zorunlu çerezler:</strong> Oturum yönetimi, güvenlik ve form gönderimleri için.</li>
<li><strong>İstatistik çerezleri:</strong> Sayfa görüntüleme ve ziyaretçi sayılarını ölçmek için.</li>
</ul>

<p>Tarayıcı ayarlarınızdan çerezleri her zaman silebilir ya da engelleyebilirsiniz.</p>',
        'sort_order' => 3,
    ],
],

// ===== ÜRÜN KATEGORİLERİ =====
'categories' => [
    ['sac',           'Sac',           'Siyah sac, DKP, HRP, ST-52 ve galvanizli sac çeşitleri',    'layers',     1],
    ['boru',          'Boru',          'Su borusu, kazan borusu ve konstrüksiyon borusu',            'minus',      2],
    ['profil',        'Profil',        'Kare, dikdörtgen ve oval profil çeşitleri',                  'square',     3],
    ['hadde',         'Hadde',         'Lama, silme, köşebent, HEA/HEB, NPI, NPU, kare demiri',      'grid',       4],
    ['flans-dirsek',  'Patent Dirsek & Norm Flanş', 'Patent dirsek ve norm flanş ürünleri',          'circle',     5],
    ['petek-kiris',   'Petek Kirişler', 'Hafif çelik konstrüksiyon için petek kiriş',                'menu',       6],
    ['panel',         'Panel',         'Çatı paneli ve cephe paneli',                                'grid',       7],
    ['insaat-demiri', 'Nervürlü İnşaat Demiri & Çelik Hasır', 'Nervürlü demir ve çelik hasır ürünleri', 'bar-chart',  8],
    ['osb-levha',     'OSB Levha',     'OSB-2, OSB-3, OSB-4 yönlendirilmiş yonga levha',             'square',     9],
],

// ===== ÜRÜNLER (Her kategori için temel ürünler) =====
'products' => [
    ['sac',  'siyah-sac',         'Siyah Sac',          'Endüstriyel uygulamalarda en yaygın kullanılan ham sac. 1 mm – 100 mm kalınlık aralığı.'],
    ['sac',  'dkp-sac',           'DKP Sac (Soğuk Haddelenmiş)', 'Yüzeyi pürüzsüz, soğuk haddeleme yöntemiyle üretilen yüksek kalite sac.'],
    ['sac',  'hrp-sac',           'HRP Sac (Sıcak Haddelenmiş)', 'Sıcak haddeleme ile üretilen, geniş kalınlık aralığında ekonomik çözüm.'],
    ['sac',  'st52-sac',          'ST-52 Sac',          'Yüksek mukavemetli yapı çeliği. Konstrüksiyon ve makine imalatında tercih edilir.'],
    ['sac',  'galvanizli-sac',    'Galvanizli Sac',     'Sıcak daldırma yöntemiyle çinko kaplı, paslanmaya karşı dayanıklı sac.'],
    ['boru', 'su-borusu',         'Su Borusu',          'TS 301-2 standardında siyah ve galvanizli su borusu.'],
    ['boru', 'kazan-borusu',      'Kazan Borusu',       'Yüksek basınç ve sıcaklığa dayanıklı kazan borusu.'],
    ['boru', 'konstruksiyon-boru','Konstrüksiyon Borusu', 'Yapı ve makine imalatında kullanılan dikişli/dikişsiz borular.'],
    ['profil','kare-profil',      'Kare Profil',        'Konstrüksiyon ve dekorasyon uygulamaları için kare kesitli profil.'],
    ['profil','diktortgen-profil','Dikdörtgen Profil',  'Geniş kullanım alanına sahip dikdörtgen kesitli profil.'],
    ['profil','oval-profil',      'Oval Profil',        'Estetik uygulamalarda tercih edilen oval profil.'],
    ['hadde','lama',              'Lama',               'Yatay kesitli düz çelik. Çeşitli ölçülerde stoklu.'],
    ['hadde','kosebent',          'Köşebent',           '90° kesitli L profil. Çatı ve konstrüksiyon işlerinde temel ürün.'],
    ['hadde','hea-heb',           'HEA / HEB Profil',   'Avrupa standardı geniş başlıklı I kesitli profiller.'],
    ['hadde','npi-npu',           'NPI / NPU Profil',   'Standart I ve U kesitli yapı profilleri.'],
    ['hadde','kare-demiri',       'Kare Demiri',        'Dövme demir ve ferforje işleri için kare kesitli demir.'],
    ['flans-dirsek','patent-dirsek','Patent Dirsek',    '90° ve 45° patent dirsek çeşitleri.'],
    ['flans-dirsek','norm-flans', 'Norm Flanş',         'DIN/EN standartlarında alın kaynaklı flanşlar.'],
    ['petek-kiris','petek-kiris', 'Petek Kiriş',        'Hafif çelik konstrüksiyon için optimize edilmiş petek kesitli kirişler.'],
    ['panel','cati-paneli',       'Çatı Paneli',        'Sandviç çatı paneli — yalıtımlı ve yalıtımsız çeşitler.'],
    ['panel','cephe-paneli',      'Cephe Paneli',       'Estetik ve yalıtımlı cephe kaplama panelleri.'],
    ['insaat-demiri','nervurlu-demir','Nervürlü İnşaat Demiri', 'BÇIII-A standardında nervürlü inşaat demiri.'],
    ['insaat-demiri','celik-hasir','Çelik Hasır',       'Q ve R tipi çelik hasır ürünleri.'],
    ['osb-levha','osb-levha',     'OSB Levha',          'OSB-2, OSB-3, OSB-4 yönlendirilmiş yonga levha çeşitleri.'],
],

// ===== HİZMETLER =====
'services' => [
    [
        'slug' => 'lazer-kesim',
        'title' => 'Lazer Kesim',
        'short_desc' => 'Yüksek hassasiyetli CNC lazer kesim hizmeti — her türlü sac kalınlığında hızlı ve temiz kesim.',
        'description' => '<p>Modern CNC lazer kesim makinalarımızla, müşterilerimizin DXF, DWG ve PDF formatındaki çizimlerini yüksek hassasiyetle gerçek ürüne dönüştürüyoruz.</p>

<p>İnce dekoratif saclardan kalın yapı çeliklerine kadar geniş bir kalınlık aralığında lazer kesim yapabiliyor; aynı gün üretim ve teslim olanağı sunuyoruz.</p>',
        'icon' => 'zap',
        'features' => json_encode(['CNC tabanlı yüksek hassasiyet', 'DXF / DWG / PDF dosya kabulü', 'Aynı gün üretim seçeneği', 'Sac, paslanmaz, alüminyum kesim'], JSON_UNESCAPED_UNICODE),
    ],
    [
        'slug' => 'oksijen-kesim',
        'title' => 'Oksijen Kesim',
        'short_desc' => 'Kalın saclar için ekonomik ve güvenilir oksi-asetilen kesim hizmeti.',
        'description' => '<p>10 mm üzeri kalın saclarda en ekonomik kesim çözümü olan oksijen kesim hizmetimizle, ağır sanayi ve makine imalatı projelerinize destek oluyoruz.</p>

<p>CNC tabanlı oksijen kesim makinalarımız, müşteri çizimlerine sadık kalarak temiz kenar ve düşük kesim payı sağlar.</p>',
        'icon' => 'flame',
        'features' => json_encode(['10 mm üzeri kalın sac kesimi', 'CNC tabanlı temiz kenar', 'Ekonomik birim maliyet', 'Kalıp ve seri üretim desteği'], JSON_UNESCAPED_UNICODE),
    ],
    [
        'slug' => 'dekoratif-saclar',
        'title' => 'Dekoratif Saclar',
        'short_desc' => 'Mimari ve iç mekan projeleri için lazer ile şekillendirilmiş özel desenli saclar.',
        'description' => '<p>Cephe kaplamalarından iç mekan paravan ve dekoratif uygulamalara kadar geniş bir alanda kullanılan dekoratif sac ürünlerimizi, müşteri talebine özel desen ve ölçülerde üretiyoruz.</p>

<p>Standart desen kataloğumuzdan seçim yapabileceğiniz gibi, kendi desen dosyanızı da bize iletebilirsiniz.</p>',
        'icon' => 'star',
        'features' => json_encode(['Özel desen üretimi', 'Standart desen kataloğu', 'Mimari cephe uygulamaları', 'İç mekan paravan ve dekorasyon'], JSON_UNESCAPED_UNICODE),
    ],
],

// ===== EKİP =====
'team' => [
    ['Murat Can',     'Kurucu',                  'Tekcan Metal’in kurucusu. Demir-çelik sektöründe 25+ yıllık tecrübe.', null, '', ''],
    ['Yunus Aksoy',   'Satış Temsilcisi',        '2007 Selçuk Üniversitesi mezunu. Satış-pazarlama alanında uzman; firmanın dijital dönüşümünü yönetmektedir.', null, 'satis@tekcanmetal.com', '0 554 835 0 226'],
    ['İsmail Gökmen', 'Depo & Sevkiyat Sorumlusu', 'Stok yönetimi ve sevkiyat operasyonlarından sorumlu.', null, '', ''],
],

// ===== SLIDER =====
'sliders' => [
    [
        'title' => 'Demir Adına Herşey',
        'subtitle' => 'Ticaret ile Bitmeyen Dostluk',
        'description' => 'Sac, boru, profil, hadde ve özel çelik ürünlerinde geniş stok, hızlı sevkiyat.',
        'image' => 'assets/img/slider-1.jpg',
        'link_text' => 'Ürünlerimizi Keşfet',
        'link_url' => 'urunler.php',
    ],
    [
        'title' => 'Lazer & Oksijen Kesim',
        'subtitle' => 'Hassas. Hızlı. Ekonomik.',
        'description' => 'CNC lazer ve oksijen kesim hizmetimizle, çiziminizden ürününüze kadar tek elden çözüm.',
        'image' => 'assets/img/slider-2.jpg',
        'link_text' => 'Hizmetlerimiz',
        'link_url' => 'hizmetler.php',
    ],
    [
        'title' => 'Aksoy Holding Güvencesi',
        'subtitle' => '15+ Yıllık Tecrübe',
        'description' => 'Konya merkezli, Türkiye genelinde sevkiyat ağıyla 1.000+ kurumsal müşteriye hizmet.',
        'image' => 'assets/img/slider-3.jpg',
        'link_text' => 'Hakkımızda',
        'link_url' => 'hakkimizda.php',
    ],
],

// ===== SSS =====
'faq' => [
    ['siparis', 'Sipariş süreci nasıl işliyor?', 'Web sitemizdeki iletişim formundan, telefondan ya da WhatsApp üzerinden talebinizi iletebilirsiniz. Sipariş onayı sonrası ürün hazırlığı yapılır ve aynı gün/ertesi gün sevkiyat planlanır.'],
    ['sevkiyat', 'Konya dışına sevkiyat yapıyor musunuz?', 'Evet. Türkiye genelinde anlaşmalı nakliye firmalarımızla sevkiyat sağlamaktayız. Şehir bazlı sevkiyat süreleri için satış temsilcimizle görüşebilirsiniz.'],
    ['odeme', 'Hangi ödeme yöntemleri kabul ediliyor?', 'Banka havalesi/EFT, kurumsal cari hesap ve mail order kredi kartı ödemelerini kabul etmekteyiz. Çek/senet kabulü cari ilişkiye göre değişmektedir.'],
    ['kesim', 'Lazer kesim için minimum sipariş tutarı var mı?', 'Standart projeler için minimum tutar uygulanmamaktadır. Ancak kompleks ve kısa süreli projelerde özel fiyatlandırma yapılır.'],
    ['stok', 'Stokta olmayan ürünleri sipariş edebilir miyim?', 'Evet. Stoklarımızda olmayan ürünleri 24-72 saat içinde tedarik edebilmekteyiz. Lütfen detaylı talebinizi iletişim formundan iletiniz.'],
    ['fatura', 'Faturalarınızı e-fatura olarak kesiyor musunuz?', 'Evet. Tüm faturalarımız GİB entegrasyonlu e-fatura sistemi üzerinden düzenlenmektedir.'],
],

// ===== ÇÖZÜM ORTAKLARI =====
'partners' => [
    ['Borçelik',      'https://www.borcelik.com',      'Galvanizli sac üretiminin lider tedarikçisi'],
    ['Erdemir',       'https://www.erdemir.com.tr',    'Yassı çelik üretiminde Türkiye’nin köklü markası'],
    ['Habaş',         'https://www.habas.com.tr',      'Demir-çelik ve sanayi gazları üretimi'],
    ['Tosyalı Çelik', 'https://www.tosyaliholding.com.tr', 'Global çelik üretimi ve ihracatı'],
    ['Kardemir',      'https://www.kardemir.com',      'Türkiye’nin ilk entegre demir-çelik tesisi'],
    ['İçdaş',         'https://www.icdas.com.tr',      'Geniş yelpazede çelik ürünleri üretimi'],
],

// ===== BANKA / IBAN =====
'banks' => [
    ['Ziraat Bankası',    'Konya / Karatay Şubesi',  'TR00 0001 0000 0000 0000 0000 00', 'TRY'],
    ['İş Bankası',        'Konya Sanayi Şubesi',     'TR00 0006 4000 0010 0000 0000 00', 'TRY'],
    ['Halkbank',          'Konya Ticaret Şubesi',    'TR00 0001 2009 4670 0010 0000 00', 'TRY'],
    ['Garanti BBVA',      'Konya Şubesi',            'TR00 0006 2000 0000 0000 0000 00', 'TRY'],
],

// ===== BLOG KATEGORİLERİ =====
'blog_categories' => [
    ['sektor-haberleri', 'Sektör Haberleri', 'Demir-çelik sektörü ve piyasa gelişmeleri'],
    ['teknik-bilgiler',  'Teknik Bilgiler',  'Ürünlerimiz hakkında teknik makaleler'],
    ['firma-haberleri',  'Firma Haberleri',  'Tekcan Metal ile ilgili güncel haberler'],
],

// ===== GALERİ ALBÜM =====
'gallery_albums' => [
    ['depo-stok',       'Depo ve Stok Sahası',        'Karatay Fevziçakmak deposundan görüntüler'],
    ['lazer-kesim',     'Lazer Kesim Üretim',         'CNC lazer kesim makinalarımız ve örnek ürünler'],
    ['sevkiyat',        'Sevkiyat ve Lojistik',       'Türkiye genelinde sevkiyat operasyonlarımız'],
],

// ===== SİSTEM SÜRÜMÜ =====
'version' => '1.0.0',

];
