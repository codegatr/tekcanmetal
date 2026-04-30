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
    ['site_phone',        '0 332 342 24 52',                           'contact'],
    ['site_mobile',       '0 554 835 0 226',                           'contact'],
    ['site_whatsapp',     '905320652400',                              'contact'],
    ['site_whatsapp_label','Tekcan Metal - Danışman',                  'contact'],
    ['site_whatsapp_msg', 'Merhaba. Size nasıl yardımcı olabiliriz?',  'contact'],
    ['site_address',      'Fevziçakmak Mahallesi Gülistan Cad. Atiker 3, 2.Blok No:33 AS - Karatay - Konya',  'contact'],
    ['site_district',     'Karatay',                                   'contact'],
    ['site_city',         'Konya',                                     'contact'],
    ['site_country',      'Türkiye',                                   'contact'],
    ['site_postcode',     '42050',                                     'contact'],
    ['site_map_lat',      '37.929244',                                 'contact'],
    ['site_map_lng',      '32.558043',                                 'contact'],
    ['site_map_iframe',   '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d336.4753529653593!2d32.55804339610216!3d37.929244534581855!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14d091b020845759%3A0xbe5849fc4faf9419!2sTekcan%20Metal%20Sanayi%20ve%20Ticaret%20Ltd.%C5%9Eti.!5e1!3m2!1str!2str!4v1679979922426!5m2!1str!2str" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>', 'contact'],
    ['site_facebook',     '',                                          'social'],
    ['site_instagram',    'https://www.instagram.com/tekcanmetal',     'social'],
    ['site_linkedin',     '',                                          'social'],
    ['site_youtube',      '',                                          'social'],
    ['site_twitter',      '',                                          'social'],
    ['working_hours',     'Pazartesi–Cumartesi: 08:00 – 18:00',        'general'],
    ['founded_year',      '2005',                                      'general'],
    ['legal_year',        '2017',                                      'general'],
    ['tax_office',        'Selçuk',                                    'general'],
    ['tax_no',            '',                                          'general'],
    ['mersis_no',         '',                                          'general'],
    ['logo',              'assets/img/logo.png',                       'branding'],
    ['logo_white',        'assets/img/logo.png',                       'branding'],
    ['favicon',           'assets/img/favicon.jpg',                    'branding'],
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
    ['homepage_about_text',  'Tekcan Metal, demir-çelik sektöründe üretilen mamul ve yarı mamullerin pazarlama ve dağıtımını yapmak amacıyla 2005 yılında şahıs şirketi olarak kurulmuştur. Artan iş hacmi ve müşteri memnuniyetinin getirdiği güvenle 2017 yılında şirketleşerek faaliyetlerini kurumsal yapıya taşımıştır. Bugün, Karatay/Konya adresinde faaliyet gösteren Tekcan Metal; yüksek kaliteli hizmet anlayışı, güler yüzlü ticaret yaklaşımı ve müşteri odaklı çözümleri ile sektörde güvenilir bir konum elde etmiştir.',  'homepage'],
    ['footer_about_text',    'Tekcan Metal, demir-çelik sektöründe üretilen mamul ve yarı mamullerin pazarlama ve dağıtımını yapmak amacıyla 2005 yılında şahıs şirketi olarak kurulmuştur. Artan iş hacmi ve müşteri memnuniyetinin getirdiği güvenle 2017 yılında şirketleşerek faaliyetlerini kurumsal yapıya taşımıştır.', 'general'],
    ['footer_keywords_text', 'Yüksek kaliteli boru, profil, sac, HRP, DKP, ST52, galvaniz, trapez sac, çatı paneli, cephe paneli, lama, silme, kare demir, NPU, NPI, IPE, HEA, HEB ve inşaat demiri tedarikinde güvenilir çözüm ortağınız. TEKCAN METAL — Güçlü yapılar, sağlam çözümler. Geleceğe atılan çelik adımlar!', 'general'],
    ['maintenance_message',  'Şu anda geçici bir bakım yapılmaktadır. En kısa sürede sizlerleyiz. info@tekcanmetal.com — 0 554 835 0 226', 'system'],
    ['stat_year',         '20+',                                       'homepage'],
    ['stat_year_label',   'Yıllık Tecrübe',                            'homepage'],
    ['stat_products',     '1.000+',                                    'homepage'],
    ['stat_products_label','Ürün Çeşidi',                              'homepage'],
    ['stat_customers',    '1.000+',                                    'homepage'],
    ['stat_customers_label','Mutlu Müşteri',                           'homepage'],
    ['stat_orders',       '3.436',                                     'homepage'],
    ['stat_orders_label', 'Ürün Siparişi',                             'homepage'],
    ['stat_branches',     '1',                                         'homepage'],
    ['stat_branches_label','Firma Şubesi',                             'homepage'],
    ['stat_delivery',     '7/24',                                      'homepage'],
    ['stat_delivery_label','Sevkiyat Hizmeti',                         'homepage'],
],

// ===== SAYFALAR =====
'pages' => [
    [
        'slug' => 'hakkimizda',
        'title' => 'Hakkımızda',
        'subtitle' => 'Tekcan Metal — Demir adına Herşey...',
        'content' => '<p class="lead">Tekcan Metal, demir-çelik sektöründe üretilen mamul ve yarı mamullerin pazarlama ve dağıtımını yapmak amacıyla <strong>2005 yılında şahıs şirketi</strong> olarak kurulmuştur. Artan iş hacmi ve müşteri memnuniyetinin getirdiği güvenle <strong>2017 yılında şirketleşerek</strong> faaliyetlerini kurumsal yapıya taşımıştır.</p>

<p>Bugün, <strong>Fevziçakmak Mahallesi Gülistan Caddesi Atiker 3 Sanayi Sitesi, 2. Blok No:33 AS – Karatay / Konya</strong> adresinde faaliyet gösteren Tekcan Metal; yüksek kaliteli hizmet anlayışı, güler yüzlü ticaret yaklaşımı ve müşteri odaklı çözümleri ile sektörde güvenilir bir konum elde etmiştir.</p>

<p>Türkiye’nin en önemli sanayi merkezlerinden biri olan Konya’da; kalite ve fiyatın en önemli faktörler olduğunun bilincindeyiz. Bu nedenle mamul ve yarı mamul ürünlerde <strong>Türkiye’nin önde gelen üreticilerinin temsilciliklerini</strong> alarak, kaliteli ürünleri uygun fiyatlarla müşterilerimize sunmaktan mutluluk duyuyoruz.</p>

<h3>"Ticaret ile Bitmeyen Dostluk"</h3>
<p>Felsefemiz; müşterilerimizi bir aile olarak görmek, kalıcı ve güvene dayalı iş ortaklıkları kurmaktır. Bu anlayışla 20 yılı aşkın süredir sektörde varlığımızı sürdürüyor, her geçen gün müşteri ağımızı genişletiyoruz.</p>

<h3>Ürün Yelpazemiz</h3>
<p>Stoklarımızda <strong>siyah sac, DKP, HRP, ST-52, galvanizli sac</strong>; su, kazan ve konstrüksiyon <strong>boruları</strong>; kare, dikdörtgen ve oval <strong>profiller</strong>; lama, silme, köşebent, HEA/HEB, NPI, NPU ve kare demiri gibi <strong>hadde ürünleri</strong>; <strong>patent dirsek, norm flanş, petek kiriş, çatı/cephe paneli, nervürlü inşaat demiri ve çelik hasır</strong> başta olmak üzere geniş bir ürün yelpazesi bulunmaktadır.</p>

<h3>Hizmetlerimiz</h3>
<p>Ürün satışının yanı sıra <strong>lazer kesim, oksijen kesim ve dekoratif sac</strong> üretim hizmetlerimizle müşterilerimizin özel projelerine de çözüm üretmekteyiz.</p>

<h3>Çözüm Ortaklarımız</h3>
<p>Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş gibi sektörün lider üreticileriyle çalışıyor; size her zaman en kaliteli ürünleri sunuyoruz.</p>

<h3>Hedefimiz</h3>
<p>Stok derinliğimiz, hızlı sevkiyat ağımız ve uzman kadromuzla; inşaat sektöründen sanayi üreticilerine, OEM firmalarından bireysel ustalara kadar geniş bir müşteri kitlesine kesintisiz hizmet sunmaktır.</p>

<p class="closing"><strong>Geleceğe atılan çelik adımlar için, bizden teklif almayı unutmayın!</strong></p>',
        'meta_desc' => 'Tekcan Metal — 2005’ten bu yana Konya merkezli demir-çelik tedarikçisi. Sac, boru, profil, hadde ürünlerinde stok ve hızlı sevkiyat. Borçelik, Erdemir, Habaş, Tosyalı, Kardemir, İçdaş çözüm ortaklığıyla.',
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
    // [slug, name, short_desc, icon, sort_order, image]
    ['sac',           'Sac',           'Siyah sac, DKP, HRP, ST-52 ve galvanizli sac çeşitleri',    'layers',     1, 'uploads/categories/sac.jpg'],
    ['boru',          'Boru',          'Su borusu, kazan borusu ve konstrüksiyon borusu',            'minus',      2, 'uploads/categories/boru.jpg'],
    ['profil',        'Profil',        'Kare, dikdörtgen ve oval profil çeşitleri',                  'square',     3, 'uploads/categories/profil.jpg'],
    ['hadde',         'Hadde',         'Lama, silme, köşebent, HEA/HEB, NPI, NPU, kare demiri',      'grid',       4, 'uploads/categories/hadde.png'],
    ['flans-dirsek',  'Patent Dirsek & Norm Flanş', 'Patent dirsek ve norm flanş ürünleri',          'circle',     5, 'uploads/categories/flans-dirsek.jpg'],
    ['petek-kiris',   'Petek Kirişler', 'Hafif çelik konstrüksiyon için petek kiriş',                'menu',       6, 'uploads/categories/petek-kiris.jpg'],
    ['panel',         'Panel',         'Çatı paneli ve cephe paneli',                                'grid',       7, 'uploads/categories/panel.png'],
    ['insaat-demiri', 'Nervürlü İnşaat Demiri & Çelik Hasır', 'Nervürlü demir ve çelik hasır ürünleri', 'bar-chart',  8, 'uploads/categories/insaat-demiri.jpg'],
    ['osb-levha',     'OSB Levha',     'OSB-2, OSB-3, OSB-4 yönlendirilmiş yonga levha',             'square',     9, 'uploads/categories/osb-levha.webp'],
],

// ===== ÜRÜNLER (Her kategori için temel ürünler) =====
// [cat, slug, name, short_desc, description, image]
'products' => [
    ['sac',  'siyah-sac',         'Siyah Sac',          'Endüstriyel uygulamalarda en yaygın kullanılan ham sac. 1 mm – 100 mm kalınlık aralığı.', null, 'uploads/products/siyah-sac.jpg'],
    ['sac',  'dkp-sac',           'DKP Sac (Soğuk Haddelenmiş)', 'Yüzeyi pürüzsüz, soğuk haddeleme yöntemiyle üretilen yüksek kalite sac.', null, 'uploads/products/dkp-sac.jpg'],
    ['sac',  'hrp-sac',           'HRP Sac (Sıcak Haddelenmiş)', 'Sıcak haddeleme ile üretilen, geniş kalınlık aralığında ekonomik çözüm.', null, 'uploads/products/hrp-sac.jpg'],
    ['sac',  'st52-sac',          'ST-52 Sac',          'Yüksek mukavemetli yapı çeliği. Konstrüksiyon ve makine imalatında tercih edilir.', null, 'uploads/products/st52-sac.jpg'],
    ['sac',  'galvanizli-sac',    'Galvanizli Sac',     'Sıcak daldırma yöntemiyle çinko kaplı, paslanmaya karşı dayanıklı sac.', null, 'uploads/products/galvanizli-sac.jpg'],
    ['boru', 'su-borusu',         'Su Borusu',          'TS 301-2 standardında siyah ve galvanizli su borusu.', null, 'uploads/products/su-borusu.jpg'],
    ['boru', 'kazan-borusu',      'Kazan Borusu',       'Yüksek basınç ve sıcaklığa dayanıklı kazan borusu.', null, 'uploads/products/kazan-borusu.jpg'],
    ['boru', 'konstruksiyon-boru','Konstrüksiyon Borusu', 'Yapı ve makine imalatında kullanılan dikişli/dikişsiz borular.', null, 'uploads/products/konstruksiyon-boru.jpg'],
    ['profil','kare-profil',      'Kare Profil',        'Konstrüksiyon ve dekorasyon uygulamaları için kare kesitli profil.', null, 'uploads/products/kare-profil.jpg'],
    ['profil','diktortgen-profil','Dikdörtgen Profil',  'Geniş kullanım alanına sahip dikdörtgen kesitli profil.', null, 'uploads/products/diktortgen-profil.jpg'],
    ['profil','oval-profil',      'Oval Profil',        'Estetik uygulamalarda tercih edilen oval profil.', null, 'uploads/products/oval-profil.jpg'],
    ['hadde','lama',              'Lama',               'Yatay kesitli düz çelik. Çeşitli ölçülerde stoklu.', null, 'uploads/products/lama.jpg'],
    ['hadde','kosebent',          'Köşebent',           '90° kesitli L profil. Çatı ve konstrüksiyon işlerinde temel ürün.', null, 'uploads/products/kosebent.jpg'],
    ['hadde','hea-heb',           'HEA / HEB Profil',   'Avrupa standardı geniş başlıklı I kesitli profiller.', null, 'uploads/products/hea-heb.jpg'],
    ['hadde','npi-npu',           'NPI / NPU Profil',   'Standart I ve U kesitli yapı profilleri.', null, 'uploads/products/npi-npu.jpg'],
    ['hadde','kare-demiri',       'Kare Demiri',        'Dövme demir ve ferforje işleri için kare kesitli demir.', null, 'uploads/products/kare-demiri.png'],
    ['flans-dirsek','patent-dirsek','Patent Dirsek',    '90° ve 45° patent dirsek çeşitleri.', null, 'uploads/products/patent-dirsek.jpg'],
    ['flans-dirsek','norm-flans', 'Norm Flanş',         'DIN/EN standartlarında alın kaynaklı flanşlar.', null, 'uploads/products/norm-flans.jpg'],
    ['petek-kiris','petek-kiris', 'Petek Kiriş',        'Hafif çelik konstrüksiyon için optimize edilmiş petek kesitli kirişler.', null, 'uploads/products/petek-kiris.jpg'],
    ['panel','cati-paneli',       'Çatı Paneli',        'Sandviç çatı paneli — yalıtımlı ve yalıtımsız çeşitler.', null, 'uploads/products/cati-paneli.png'],
    ['panel','cephe-paneli',      'Cephe Paneli',       'Estetik ve yalıtımlı cephe kaplama panelleri.', null, 'uploads/products/cephe-paneli.png'],
    ['insaat-demiri','nervurlu-demir','Nervürlü İnşaat Demiri', 'BÇIII-A standardında nervürlü inşaat demiri.', null, 'uploads/products/nervurlu-demir.jpg'],
    ['insaat-demiri','celik-hasir','Çelik Hasır',       'Q ve R tipi çelik hasır ürünleri.', null, 'uploads/products/celik-hasir.jpg'],
    ['osb-levha','osb-levha',     'OSB Levha',          'OSB-2, OSB-3, OSB-4 yönlendirilmiş yonga levha çeşitleri.', null, 'uploads/products/osb-levha.webp'],
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
        'image' => 'uploads/services/lazer-kesim.jpg',
        'features' => json_encode(['CNC tabanlı yüksek hassasiyet', 'DXF / DWG / PDF dosya kabulü', 'Aynı gün üretim seçeneği', 'Sac, paslanmaz, alüminyum kesim'], JSON_UNESCAPED_UNICODE),
    ],
    [
        'slug' => 'oksijen-kesim',
        'title' => 'Oksijen Kesim',
        'short_desc' => 'Kalın saclar için ekonomik ve güvenilir oksi-asetilen kesim hizmeti.',
        'description' => '<p>10 mm üzeri kalın saclarda en ekonomik kesim çözümü olan oksijen kesim hizmetimizle, ağır sanayi ve makine imalatı projelerinize destek oluyoruz.</p>

<p>CNC tabanlı oksijen kesim makinalarımız, müşteri çizimlerine sadık kalarak temiz kenar ve düşük kesim payı sağlar.</p>',
        'icon' => 'flame',
        'image' => 'uploads/services/oksijen-kesim.jpg',
        'features' => json_encode(['10 mm üzeri kalın sac kesimi', 'CNC tabanlı temiz kenar', 'Ekonomik birim maliyet', 'Kalıp ve seri üretim desteği'], JSON_UNESCAPED_UNICODE),
    ],
    [
        'slug' => 'dekoratif-saclar',
        'title' => 'Dekoratif Saclar',
        'short_desc' => 'Mimari ve iç mekan projeleri için lazer ile şekillendirilmiş özel desenli saclar.',
        'description' => '<p>Cephe kaplamalarından iç mekan paravan ve dekoratif uygulamalara kadar geniş bir alanda kullanılan dekoratif sac ürünlerimizi, müşteri talebine özel desen ve ölçülerde üretiyoruz.</p>

<p>Standart desen kataloğumuzdan seçim yapabileceğiniz gibi, kendi desen dosyanızı da bize iletebilirsiniz.</p>',
        'icon' => 'star',
        'image' => 'uploads/services/dekoratif-saclar.png',
        'features' => json_encode(['Özel desen üretimi', 'Standart desen kataloğu', 'Mimari cephe uygulamaları', 'İç mekan paravan ve dekorasyon'], JSON_UNESCAPED_UNICODE),
    ],
],

// ===== SLIDER =====
'sliders' => [
    [
        'title' => 'Demir-çelik sektöründe yarım asra yakın güven',
        'subtitle' => 'Tekcan Metal',
        'description' => '2005’ten bu yana Konya merkezli; sac, boru, profil, hadde ve özel çelik ürünlerinde Türkiye’nin lider üreticilerinin temsilciliği ile sanayi ve inşaat sektörüne çözüm üretiyoruz.',
        'image' => 'uploads/sliders/slider-1-tekcan.jpg',
        'link_text' => 'Biz Kimiz',
        'link_url' => 'hakkimizda.php',
    ],
    [
        'title' => 'Tek elden, uçtan uca demir-çelik tedariği',
        'subtitle' => 'Çözüm Yelpazemiz',
        'description' => 'Geniş stoğumuz; lazer ve oksijen kesim atölyelerimiz; aynı gün üretim ve sevkiyat kapasitemiz ile projelerinizin her aşamasında yanınızdayız.',
        'image' => 'uploads/sliders/slider-2-laser.jpg',
        'link_text' => 'Faaliyet Alanlarımız',
        'link_url' => 'urunler.php',
    ],
    [
        'title' => 'Türkiye genelinde 7/24 sevkiyat ağı',
        'subtitle' => 'Operasyonel Mükemmellik',
        'description' => 'Konya merkezli stok depomuz ve anlaşmalı nakliye partnerlerimizle 81 ile zamanında, eksiksiz teslimat taahhüdü sunuyoruz.',
        'image' => 'uploads/sliders/slider-3-delivery.png',
        'link_text' => 'Bize Ulaşın',
        'link_url' => 'iletisim.php',
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
// [name, website, description, logo]
'partners' => [
    ['Borçelik',      'https://www.borcelik.com',      'Galvanizli sac üretiminin lider tedarikçisi', null],
    ['Erdemir',       'https://www.erdemir.com.tr',    'Yassı çelik üretiminde Türkiye’nin köklü markası', null],
    ['Habaş',         'https://www.habas.com.tr',      'Demir-çelik ve sanayi gazları üretimi', null],
    ['Tosyalı Çelik', 'https://www.tosyaliholding.com.tr', 'Global çelik üretimi ve ihracatı', null],
    ['Kardemir',      'https://www.kardemir.com',      'Türkiye’nin ilk entegre demir-çelik tesisi', null],
    ['İçdaş',         'https://www.icdas.com.tr',      'Geniş yelpazede çelik ürünleri üretimi', null],
],

// ===== BANKA / IBAN =====
// [bank_name, branch, iban, currency, logo]
'banks' => [
    ['Ziraat Bankası',    'Konya / Karatay Şubesi',  'TR00 0001 0000 0000 0000 0000 00', 'TRY', 'uploads/banks/ziraat.jpg'],
    ['İş Bankası',        'Konya Sanayi Şubesi',     'TR00 0006 4000 0010 0000 0000 00', 'TRY', null],
    ['Halkbank',          'Konya Ticaret Şubesi',    'TR00 0001 2009 4670 0010 0000 00', 'TRY', 'uploads/banks/halkbank.png'],
    ['Garanti BBVA',      'Konya Şubesi',            'TR00 0006 2000 0000 0000 0000 00', 'TRY', null],
],

// ===== BLOG KATEGORİLERİ =====
'blog_categories' => [
    ['sektor-haberleri', 'Sektör Haberleri', 'Demir-çelik sektörü ve piyasa gelişmeleri'],
    ['teknik-bilgiler',  'Teknik Bilgiler',  'Ürünlerimiz hakkında teknik makaleler'],
    ['firma-haberleri',  'Firma Haberleri',  'Tekcan Metal ile ilgili güncel haberler'],
],

// ===== GALERİ ALBÜM =====
// [slug, title, description, cover_image]
'gallery_albums' => [
    ['depo-stok',       'Depo ve Stok Sahası',        'Karatay Fevziçakmak deposundan görüntüler', 'uploads/pages/tekcan-metal-bina.jpg'],
    ['lazer-kesim',     'Lazer Kesim Üretim',         'CNC lazer kesim makinalarımız ve örnek ürünler', 'uploads/services/lazer-kesim.jpg'],
    ['sevkiyat',        'Sevkiyat ve Lojistik',       'Türkiye genelinde sevkiyat operasyonlarımız', 'uploads/sliders/slider-3-delivery.png'],
],

// ===== SİSTEM SÜRÜMÜ =====
'version' => '1.0.54',

];
