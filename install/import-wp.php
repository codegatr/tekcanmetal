<?php
/**
 * WordPress İçerik Aktarım Aracı
 *
 * Eski tekcanmetal.com WordPress sitesinden çıkarılmış JSON ve uploads zip'ini
 * yeni Tekcan Metal CMS'in tm_* tablolarına aktarır.
 *
 * Kullanım: admin/wp-import.php (admin paneli) üzerinden tetiklenir.
 *
 * Veri kaynakları:
 *   install/wp-content.json.gz  — sayfalar, yazılar, medya kayıtları
 *   install/wp-uploads.zip      — eski WP medya dosyaları (originalleri)
 *
 * Hedef tablolar:
 *   tm_pages        — Hakkımızda, KVKK, Fiyat Listeleri
 *   tm_blog_posts   — 47 demir-çelik blog yazısı
 *   tm_banks        — Halkbank + Ziraat
 *   tm_partners     — 12 çözüm ortağı (logolarıyla)
 *   tm_gallery_*    — Foto galerisi
 *
 * Hedef klasör: uploads/wp-imported/YYYY/MM/...
 */

declare(strict_types=1);

class WPImporter
{
    private \PDO $pdo;
    private array $data;
    private array $stats;
    private string $uploadsDir;
    private string $importDir;
    private array $log = [];
    private bool $dryRun;
    private array $opts;

    public function __construct(\PDO $pdo, array $data, string $uploadsDir, array $opts = [])
    {
        $this->pdo        = $pdo;
        $this->data       = $data;
        $this->uploadsDir = rtrim($uploadsDir, '/\\');
        $this->importDir  = $this->uploadsDir . '/wp-imported';
        $this->dryRun     = (bool)($opts['dry_run'] ?? false);
        $this->opts       = array_merge([
            'wipe_seed'       => true,   // Mevcut seed verilerini temizle
            'import_pages'    => true,
            'import_posts'    => true,
            'import_banks'    => true,
            'import_partners' => true,
            'import_gallery'  => true,
        ], $opts);

        $this->stats = [
            'pages'      => ['ok' => 0, 'skip' => 0, 'err' => 0],
            'posts'      => ['ok' => 0, 'skip' => 0, 'err' => 0],
            'banks'      => ['ok' => 0, 'skip' => 0, 'err' => 0],
            'partners'   => ['ok' => 0, 'skip' => 0, 'err' => 0],
            'gallery'    => ['ok' => 0, 'skip' => 0, 'err' => 0],
            'media'      => ['copied' => 0, 'skipped' => 0, 'err' => 0],
            'urls_rewritten' => 0,
        ];
    }

    public function run(): array
    {
        $this->info("WP içerik aktarımı başladı" . ($this->dryRun ? " [DRY RUN]" : ""));

        if (!$this->dryRun) {
            // 1. uploads/wp-imported/ klasörünü hazırla
            if (!is_dir($this->importDir)) {
                @mkdir($this->importDir, 0755, true);
                $this->info("Klasör oluşturuldu: $this->importDir");
            }
        }

        // 2. Sayfalar
        if ($this->opts['import_pages']) {
            $this->importPages();
        }

        // 3. Bloglar
        if ($this->opts['import_posts']) {
            $this->importPosts();
        }

        // 4. IBAN
        if ($this->opts['import_banks']) {
            $this->importBanks();
        }

        // 5. Partnerler
        if ($this->opts['import_partners']) {
            $this->importPartners();
        }

        // 6. Galeri
        if ($this->opts['import_gallery']) {
            $this->importGallery();
        }

        $this->info("Aktarım tamamlandı.");

        return [
            'stats' => $this->stats,
            'log'   => $this->log,
        ];
    }

    /* ============================================================
     * MEDYA YARDIMCI — eski WP URL'sinden yeni göreli yola çevir
     * ============================================================ */

    private function rewriteUrl(string $url): string
    {
        // https://www.tekcanmetal.com/wp-content/uploads/2024/09/halkbank.png
        //   → uploads/wp-imported/2024/09/halkbank.png
        $patterns = [
            '#https?://(?:www\.)?tekcanmetal\.com/wp-content/uploads/#i',
            '#http://demo\.safirtema\.com/koza/wp-content/uploads/#i',
            '#https?://(?:www\.)?tekcanmetal\.com/wp-content/uploads/#i',
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $url)) {
                return preg_replace($p, 'uploads/wp-imported/', $url);
            }
        }
        return $url;
    }

    /**
     * HTML içeriklerindeki tüm WP medya URL'lerini yeni yapıya çevir.
     * Aynı zamanda Gutenberg yorumlarını ve tema bloklarını temizler.
     */
    private function processContent(string $html): string
    {
        if ($html === '') return '';

        // 1. Gutenberg yorumlarını sil
        $html = preg_replace('#<!--\s*/?wp:[^>]*?-->#', '', $html);

        // 2. WP URL'lerini yeniden yaz (img src, a href, vs)
        $html = preg_replace_callback(
            '#https?://(?:www\.)?tekcanmetal\.com/wp-content/uploads/([^\s"\'<>]+)#i',
            function ($m) {
                $this->stats['urls_rewritten']++;
                return 'uploads/wp-imported/' . $m[1];
            },
            $html
        );

        // 3. demo.safirtema URL'leri (eski tema demo)
        $html = preg_replace_callback(
            '#https?://demo\.safirtema\.com/koza/wp-content/uploads/([^\s"\'<>]+)#i',
            function ($m) {
                $this->stats['urls_rewritten']++;
                return 'uploads/wp-imported/' . $m[1];
            },
            $html
        );

        // 4. Resim boyut varyantlarını original'a çevir (-300x200.jpg → .jpg)
        $html = preg_replace(
            '#(uploads/wp-imported/[^"\'<>\s]+?)-\d+x\d+(\.(?:jpg|jpeg|png|webp))#i',
            '$1$2',
            $html
        );

        // 5. -scaled varyantlarını da
        $html = preg_replace(
            '#(uploads/wp-imported/[^"\'<>\s]+?)-scaled(\.(?:jpg|jpeg|png|webp))#i',
            '$1$2',
            $html
        );

        // 6. Çoklu newline azalt
        $html = preg_replace('#\n{3,}#', "\n\n", trim($html));

        return $html;
    }

    /**
     * Verilen attachment_id için yeni göreli URL döndür (kapak görseli için).
     */
    private function attachmentToRelativePath(?int $attId): ?string
    {
        if (!$attId) return null;
        $atts = $this->data['attachments'] ?? [];
        $a = $atts[(string)$attId] ?? null;
        if (!$a || empty($a['guid'])) return null;
        $rel = $this->rewriteUrl($a['guid']);
        // Boyut varyantını originala çevir
        $rel = preg_replace('#(uploads/wp-imported/[^/]+?)-\d+x\d+(\.(?:jpg|jpeg|png|webp))#i', '$1$2', $rel);
        return $rel;
    }

    /* ============================================================
     * SAYFALAR
     * ============================================================ */

    private function importPages(): void
    {
        $this->section("📄 SAYFALAR");

        // Hangi WP sayfası → hangi yeni slug
        $mapping = [
            'hakkimizda' => [
                'new_slug' => 'hakkimizda',
                'new_title' => 'Hakkımızda',
                'subtitle' => '2005\'ten bu yana Konya\'da demir-çelik tedariği',
            ],
            'tekcan-metal-kisisel-verileri-koruma-politikasi' => [
                'new_slug' => 'kvkk',
                'new_title' => 'KVKK — Kişisel Verileri Koruma Politikası',
                'subtitle' => 'Müşterilerimizin ve iş ortaklarımızın kişisel verilerinin korunması',
            ],
            'urun-fiyat-listeleri' => [
                'new_slug' => 'urun-fiyat-listeleri',
                'new_title' => 'Ürün Fiyat Listeleri',
                'subtitle' => 'Üretici firmalarımızın güncel fiyat listeleri',
            ],
        ];

        // Atlanacak sayfalar
        $skip = ['iletisim', 'sikca-sorulan-sorular', 'malzeme-agirlik-hesaplama', 'foto-galeri', 'cozum-ortaklari'];

        foreach ($this->data['pages'] as $p) {
            $slug = $p['slug'] ?? '';

            if (in_array($slug, $skip, true)) {
                $this->log("  [skip]  $slug — manuel yönetilen sayfa");
                $this->stats['pages']['skip']++;
                continue;
            }

            if (!isset($mapping[$slug])) {
                $this->log("  [skip]  $slug — eşleştirme yok");
                $this->stats['pages']['skip']++;
                continue;
            }

            $map = $mapping[$slug];
            $newSlug = $map['new_slug'];
            $title = $map['new_title'];
            $subtitle = $map['subtitle'] ?? null;
            $content = $this->processContent($p['content_html'] ?? '');
            $hero = $this->attachmentToRelativePath($p['thumbnail_id'] ?? null);

            try {
                if (!$this->dryRun) {
                    $exists = $this->pdo->prepare("SELECT id FROM tm_pages WHERE slug=?");
                    $exists->execute([$newSlug]);
                    $row = $exists->fetch();

                    if ($row) {
                        $stmt = $this->pdo->prepare(
                            "UPDATE tm_pages SET title=?, subtitle=?, content=?, hero_image=COALESCE(?, hero_image), is_active=1 WHERE slug=?"
                        );
                        $stmt->execute([$title, $subtitle, $content, $hero, $newSlug]);
                        $this->log("  [upd]   $newSlug ($title) — " . strlen($content) . " karakter");
                    } else {
                        $stmt = $this->pdo->prepare(
                            "INSERT INTO tm_pages (slug, title, subtitle, content, hero_image, is_active) VALUES (?,?,?,?,?,1)"
                        );
                        $stmt->execute([$newSlug, $title, $subtitle, $content, $hero]);
                        $this->log("  [ins]   $newSlug ($title) — " . strlen($content) . " karakter");
                    }
                } else {
                    $this->log("  [dry]   $newSlug ($title) — " . strlen($content) . " karakter");
                }
                $this->stats['pages']['ok']++;
            } catch (\Throwable $e) {
                $this->log("  [err]   $newSlug — " . $e->getMessage());
                $this->stats['pages']['err']++;
            }
        }
    }

    /* ============================================================
     * BLOG YAZILARI
     * ============================================================ */

    private function importPosts(): void
    {
        $this->section("📝 BLOG YAZILARI");

        if (!$this->dryRun && $this->opts['wipe_seed']) {
            // Önce seed blog yazılarını temizle (sadece WP'den gelmeyenleri korumak istersek bayrak ekleriz; şimdi tabloyu boşaltıyoruz)
            try {
                $count = (int)$this->pdo->query("SELECT COUNT(*) FROM tm_blog_posts")->fetchColumn();
                $this->pdo->exec("DELETE FROM tm_blog_posts");
                $this->log("  [wipe]  Mevcut $count blog yazısı silindi (yerine WP içeriği gelecek)");
            } catch (\Throwable $e) {}
        }

        // Default kategori (gerekirse oluşturulacak)
        $defCatId = null;
        if (!$this->dryRun) {
            try {
                $defCatId = (int)$this->pdo->query(
                    "SELECT id FROM tm_blog_categories ORDER BY sort_order LIMIT 1"
                )->fetchColumn();
                if (!$defCatId) {
                    $this->pdo->exec(
                        "INSERT INTO tm_blog_categories (slug, name, sort_order) VALUES ('genel', 'Genel', 0)"
                    );
                    $defCatId = (int)$this->pdo->lastInsertId();
                }
            } catch (\Throwable $e) {}
        }

        foreach ($this->data['posts'] as $p) {
            $slug    = $this->ensureUniqueSlug($p['slug'] ?? '', $p['title'] ?? 'yazi');
            $title   = trim($p['title'] ?? '');
            $excerpt = mb_substr(trim(strip_tags($p['excerpt'] ?: $p['content_html'] ?? '')), 0, 480);
            $content = $this->processContent($p['content_html'] ?? '');
            $cover   = $this->attachmentToRelativePath($p['thumbnail_id'] ?? null);
            $date    = $p['date'] ?: date('Y-m-d H:i:s');

            // Kategori isminden eşleştir (yoksa default)
            $catId = $defCatId;
            if (!empty($p['terms'])) {
                foreach ($p['terms'] as $t) {
                    if (($t['taxonomy'] ?? '') === 'category') {
                        $catId = $this->getOrCreateBlogCategory($t['name'] ?? 'Genel', $t['slug'] ?? 'genel') ?: $defCatId;
                        break;
                    }
                }
            }

            try {
                if (!$this->dryRun) {
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO tm_blog_posts (category_id, slug, title, excerpt, content, cover_image, author, published_at, is_active)
                         VALUES (?,?,?,?,?,?,?,?,1)
                         ON DUPLICATE KEY UPDATE title=VALUES(title), content=VALUES(content), cover_image=VALUES(cover_image), excerpt=VALUES(excerpt)"
                    );
                    $stmt->execute([
                        $catId, $slug, $title, $excerpt, $content, $cover,
                        'Tekcan Metal', $date,
                    ]);
                }
                $this->log("  [ok]    $slug — $title");
                $this->stats['posts']['ok']++;
            } catch (\Throwable $e) {
                $this->log("  [err]   $slug — " . $e->getMessage());
                $this->stats['posts']['err']++;
            }
        }
    }

    private function getOrCreateBlogCategory(string $name, string $slug): ?int
    {
        if ($this->dryRun) return null;
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM tm_blog_categories WHERE slug=?");
            $stmt->execute([$slug]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
            $ins = $this->pdo->prepare("INSERT INTO tm_blog_categories (slug, name) VALUES (?,?)");
            $ins->execute([$slug, $name]);
            return (int)$this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private array $usedSlugs = [];
    private function ensureUniqueSlug(string $slug, string $title): string
    {
        $slug = $slug ?: $this->slugify($title);
        $orig = $slug;
        $i = 2;
        while (in_array($slug, $this->usedSlugs, true)) {
            $slug = $orig . '-' . $i++;
        }
        $this->usedSlugs[] = $slug;
        return $slug;
    }

    private function slugify(string $s): string
    {
        $tr = ['ç'=>'c','Ç'=>'c','ğ'=>'g','Ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','Ö'=>'o','ş'=>'s','Ş'=>'s','ü'=>'u','Ü'=>'u'];
        $s = strtr($s, $tr);
        $s = strtolower(trim($s));
        $s = preg_replace('#[^a-z0-9]+#', '-', $s);
        return trim($s, '-') ?: 'yazi';
    }

    /* ============================================================
     * BANKA / IBAN
     * ============================================================ */

    private function importBanks(): void
    {
        $this->section("🏦 BANKA / IBAN");

        // IBAN sayfasından çıkarılmış sabit veri
        $banks = [
            [
                'bank_name'      => 'Türkiye Halk Bankası',
                'branch'         => null,
                'account_holder' => 'TEKCAN METAL SANAYİ VE TİCARET LTD. ŞTİ.',
                'iban'           => 'TR58 0001 2001 3650 0009 1002 39',
                'logo'           => 'uploads/wp-imported/2024/09/halkbank.png',
                'sort_order'     => 1,
            ],
            [
                'bank_name'      => 'T.C. Ziraat Bankası',
                'branch'         => null,
                'account_holder' => 'TEKCAN METAL SANAYİ VE TİCARET LTD. ŞTİ.',
                'iban'           => 'TR39 0001 0021 9790 2118 7050 04',
                'logo'           => 'uploads/wp-imported/2025/01/ziraat.jpg',
                'sort_order'     => 2,
            ],
        ];

        if (!$this->dryRun && $this->opts['wipe_seed']) {
            try {
                $count = (int)$this->pdo->query("SELECT COUNT(*) FROM tm_banks")->fetchColumn();
                $this->pdo->exec("DELETE FROM tm_banks");
                $this->log("  [wipe]  Mevcut $count banka silindi");
            } catch (\Throwable $e) {}
        }

        foreach ($banks as $b) {
            try {
                if (!$this->dryRun) {
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO tm_banks (bank_name, branch, account_holder, iban, logo, sort_order, is_active)
                         VALUES (?,?,?,?,?,?,1)"
                    );
                    $stmt->execute([
                        $b['bank_name'], $b['branch'], $b['account_holder'], $b['iban'],
                        $b['logo'], $b['sort_order'],
                    ]);
                }
                $this->log("  [ok]    {$b['bank_name']} — {$b['iban']}");
                $this->stats['banks']['ok']++;
            } catch (\Throwable $e) {
                $this->log("  [err]   {$b['bank_name']} — " . $e->getMessage());
                $this->stats['banks']['err']++;
            }
        }
    }

    /* ============================================================
     * PARTNERLER (Çözüm Ortakları)
     * ============================================================ */

    private function importPartners(): void
    {
        $this->section("🤝 ÇÖZÜM ORTAKLARI");

        // 'cozum-ortaklari' sayfasından img URL'leri parse
        $partners = [
            ['name' => 'Atakaş',           'logo' => 'uploads/wp-imported/2022/05/atakas.jpg',           'sort_order' => 1],
            ['name' => 'Çayırova Boru',    'logo' => 'uploads/wp-imported/2022/05/cayirova-yeni.jpg',    'sort_order' => 2],
            ['name' => 'Işık Çelik',       'logo' => 'uploads/wp-imported/2022/05/isik-celik-1.png',     'sort_order' => 3],
            ['name' => 'Kroman Çelik',     'logo' => 'uploads/wp-imported/2022/05/kroman-yeni.jpg',      'sort_order' => 4],
            ['name' => 'RZK Çelik',        'logo' => 'uploads/wp-imported/2022/05/rzk-celik-yeni.jpg',   'sort_order' => 5],
            ['name' => 'Tosçelik',         'logo' => 'uploads/wp-imported/2022/05/toscelik-yeni.jpg',    'sort_order' => 6],
            ['name' => 'Yücel Boru',       'logo' => 'uploads/wp-imported/2022/05/yucel-boru-yeni.jpg',  'sort_order' => 7],
            ['name' => 'Mescier',          'logo' => 'uploads/wp-imported/2022/06/mescier.png',          'sort_order' => 8],
            ['name' => 'Ağır Hadde',       'logo' => 'uploads/wp-imported/2023/09/agir-hadde.png',       'sort_order' => 9],
            ['name' => 'Ege Toros',        'logo' => 'uploads/wp-imported/2023/09/egetoros.png',         'sort_order' => 10],
            ['name' => 'Kadri Toros',      'logo' => 'uploads/wp-imported/2023/09/kadri-toros.jpeg',     'sort_order' => 11],
        ];

        if (!$this->dryRun && $this->opts['wipe_seed']) {
            try {
                $count = (int)$this->pdo->query("SELECT COUNT(*) FROM tm_partners")->fetchColumn();
                $this->pdo->exec("DELETE FROM tm_partners");
                $this->log("  [wipe]  Mevcut $count partner silindi");
            } catch (\Throwable $e) {}
        }

        // tm_partners kolonları: name, logo, website, sort_order, is_active
        foreach ($partners as $p) {
            try {
                if (!$this->dryRun) {
                    // Tablonun gerçek kolonlarını alalım
                    $cols = $this->getTableColumns('tm_partners');

                    $fields = ['name' => $p['name'], 'sort_order' => $p['sort_order']];
                    if (in_array('logo', $cols, true))  $fields['logo'] = $p['logo'];
                    if (in_array('image', $cols, true)) $fields['image'] = $p['logo'];
                    if (in_array('is_active', $cols, true)) $fields['is_active'] = 1;

                    $colsList = implode(',', array_keys($fields));
                    $marks    = implode(',', array_fill(0, count($fields), '?'));
                    $stmt = $this->pdo->prepare("INSERT INTO tm_partners ($colsList) VALUES ($marks)");
                    $stmt->execute(array_values($fields));
                }
                $this->log("  [ok]    {$p['name']}");
                $this->stats['partners']['ok']++;
            } catch (\Throwable $e) {
                $this->log("  [err]   {$p['name']} — " . $e->getMessage());
                $this->stats['partners']['err']++;
            }
        }
    }

    /* ============================================================
     * GALERİ (Foto Galeri sayfasından)
     * ============================================================ */

    private function importGallery(): void
    {
        $this->section("🖼  FOTO GALERİ");

        // 'foto-galeri' (id=68) sayfasının içeriğinden img URL'leri çıkar
        $foto = null;
        foreach ($this->data['pages'] as $p) {
            if (($p['slug'] ?? '') === 'foto-galeri') {
                $foto = $p;
                break;
            }
        }

        if (!$foto) {
            $this->log("  [skip]  Foto Galeri sayfası bulunamadı");
            $this->stats['gallery']['skip']++;
            return;
        }

        $imgs = [];
        if (preg_match_all('#https?://(?:www\.)?tekcanmetal\.com/wp-content/uploads/([^"\'<>\s]+?)\.(jpg|jpeg|png|webp)#i',
                          $foto['content_html'] ?? '', $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $rel = 'uploads/wp-imported/' . $m[1] . '.' . strtolower($m[2]);
                // Boyut varyantını orijinala çevir
                $rel = preg_replace('#-\d+x\d+(\.[a-z]+)$#i', '$1', $rel);
                if (!in_array($rel, $imgs, true)) {
                    $imgs[] = $rel;
                }
            }
        }

        if (!$imgs) {
            $this->log("  [skip]  Galeri'de görsel bulunamadı");
            $this->stats['gallery']['skip']++;
            return;
        }

        $this->log("  Galeri'den " . count($imgs) . " görsel bulundu");

        if (!$this->dryRun) {
            // Albüm oluştur veya bul
            $albumId = null;
            try {
                $stmt = $this->pdo->prepare("SELECT id FROM tm_gallery_albums WHERE slug=?");
                $stmt->execute(['foto-galeri']);
                $albumId = $stmt->fetchColumn();

                if (!$albumId) {
                    $albCols = $this->getTableColumns('tm_gallery_albums');
                    $alb = ['slug' => 'foto-galeri', 'title' => 'Foto Galerimiz', 'sort_order' => 1];
                    if (in_array('description', $albCols, true)) {
                        $alb['description'] = 'Tekcan Metal ürün ve sevkiyat galerimiz';
                    }
                    if (in_array('cover_image', $albCols, true)) $alb['cover_image'] = $imgs[0];
                    if (in_array('is_active', $albCols, true)) $alb['is_active'] = 1;

                    $colsList = implode(',', array_keys($alb));
                    $marks    = implode(',', array_fill(0, count($alb), '?'));
                    $ins = $this->pdo->prepare("INSERT INTO tm_gallery_albums ($colsList) VALUES ($marks)");
                    $ins->execute(array_values($alb));
                    $albumId = (int)$this->pdo->lastInsertId();
                }

                if ($this->opts['wipe_seed']) {
                    $this->pdo->prepare("DELETE FROM tm_gallery_images WHERE album_id=?")->execute([$albumId]);
                }

                // Görselleri ekle
                $imgCols = $this->getTableColumns('tm_gallery_images');
                $imgCol = in_array('image', $imgCols, true) ? 'image' : (in_array('image_path', $imgCols, true) ? 'image_path' : 'file');

                foreach ($imgs as $i => $img) {
                    $row = ['album_id' => $albumId, $imgCol => $img, 'sort_order' => $i];
                    if (in_array('is_active', $imgCols, true)) $row['is_active'] = 1;
                    $colsList = implode(',', array_keys($row));
                    $marks    = implode(',', array_fill(0, count($row), '?'));
                    $stmt = $this->pdo->prepare("INSERT INTO tm_gallery_images ($colsList) VALUES ($marks)");
                    $stmt->execute(array_values($row));
                    $this->stats['gallery']['ok']++;
                }
                $this->log("  [ok]    Albüm: foto-galeri (" . count($imgs) . " görsel)");
            } catch (\Throwable $e) {
                $this->log("  [err]   Galeri ekleme: " . $e->getMessage());
                $this->stats['gallery']['err']++;
            }
        } else {
            $this->stats['gallery']['ok'] = count($imgs);
            $this->log("  [dry]   " . count($imgs) . " görsel hazırlandı");
        }
    }

    /* ============================================================
     * UPLOADS ZIP AÇMA — wp-uploads.zip → uploads/wp-imported/
     * ============================================================ */

    public function extractUploadsZip(string $zipPath): array
    {
        $this->section("📦 UPLOADS DOSYALARI");

        if (!file_exists($zipPath)) {
            $this->log("  [err]   Zip bulunamadı: $zipPath");
            return $this->stats['media'];
        }

        if ($this->dryRun) {
            $this->log("  [dry]   $zipPath açılacak (extract yapılmadı)");
            return $this->stats['media'];
        }

        if (!is_dir($this->importDir)) {
            @mkdir($this->importDir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->log("  [err]   Zip açılamadı: $zipPath");
            $this->stats['media']['err']++;
            return $this->stats['media'];
        }

        $total = $zip->numFiles;
        for ($i = 0; $i < $total; $i++) {
            $name = $zip->getNameIndex($i);
            if (!$name || str_ends_with($name, '/')) continue;

            // Path traversal koruması
            $name = ltrim($name, '/\\');
            if (str_contains($name, '..')) {
                $this->stats['media']['err']++;
                continue;
            }

            $dst = $this->importDir . '/' . $name;
            if (file_exists($dst) && filesize($dst) > 0) {
                $this->stats['media']['skipped']++;
                continue;
            }

            @mkdir(dirname($dst), 0755, true);

            $stream = $zip->getStream($name);
            if (!$stream) {
                $this->stats['media']['err']++;
                continue;
            }

            $out = @fopen($dst, 'wb');
            if (!$out) {
                fclose($stream);
                $this->stats['media']['err']++;
                continue;
            }

            $bytes = 0;
            while (!feof($stream)) {
                $chunk = fread($stream, 65536);
                if ($chunk === false) break;
                fwrite($out, $chunk);
                $bytes += strlen($chunk);
            }
            fclose($stream);
            fclose($out);

            $this->stats['media']['copied']++;
        }

        $zip->close();

        $this->log(sprintf(
            "  [ok]    %d dosya kopyalandı, %d zaten vardı, %d hata (toplam %d)",
            $this->stats['media']['copied'],
            $this->stats['media']['skipped'],
            $this->stats['media']['err'],
            $total
        ));

        return $this->stats['media'];
    }

    /* ============================================================
     * Yardımcılar
     * ============================================================ */

    private array $tableColumnsCache = [];

    private function getTableColumns(string $table): array
    {
        if (isset($this->tableColumnsCache[$table])) return $this->tableColumnsCache[$table];
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM `$table`");
            $cols = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'Field');
            return $this->tableColumnsCache[$table] = $cols;
        } catch (\Throwable $e) {
            return $this->tableColumnsCache[$table] = [];
        }
    }

    private function info(string $msg): void { $this->log[] = $msg; }
    private function section(string $title): void { $this->log[] = "\n=== $title ==="; }
    private function log(string $msg): void { $this->log[] = $msg; }

    /* ============================================================
     * Static loader: install/wp-content.json.gz dosyasından veri çek
     * ============================================================ */

    public static function loadDataFromGz(string $path): array
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("WP içerik dosyası bulunamadı: $path");
        }
        $gz = file_get_contents($path);
        if ($gz === false) {
            throw new \RuntimeException("Dosya okunamadı: $path");
        }
        $json = gzdecode($gz);
        if ($json === false) {
            throw new \RuntimeException("Dosya açılamadı (gzip): $path");
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException("JSON parse edilemedi");
        }
        return $data;
    }
}
