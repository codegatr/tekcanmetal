<?php
require __DIR__ . '/includes/db.php';

$brands = all("SELECT * FROM tm_price_lists WHERE is_active=1 ORDER BY sort_order, brand_name");

// Kategori sayıları
$catCounts = [];
foreach ($brands as $b) {
    $cat = $b['category'];
    $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
}

$categories = [
    'celik'      => ['label' => 'Çelik', 'icon' => '🔥', 'color' => '#c8102e'],
    'sac'        => ['label' => 'Sac', 'icon' => '📐', 'color' => '#0c1e44'],
    'boru'       => ['label' => 'Boru', 'icon' => '⭕', 'color' => '#143672'],
    'profil'     => ['label' => 'Profil', 'icon' => '▭', 'color' => '#a88a4a'],
    'paslanmaz'  => ['label' => 'Paslanmaz', 'icon' => '✨', 'color' => '#6b7280'],
];

$pageTitle  = 'Demir Çelik Fabrikaları Fiyat Listeleri | Güncel Rehber';
$metaDesc   = 'Türkiye\'nin önemli demir çelik üreticilerinin (Erdemir, Kardemir, Borusan, Yücel Boru, Tosyalı, İçdaş ve diğer ' . count($brands) . '+ fabrika) güncel fiyat listelerine tek sayfadan ulaşın. Tekcan Metal rehberi.';
$canonicalUrl = 'https://tekcanmetal.com/fiyat-listeleri.php';

require __DIR__ . '/includes/header.php';
?>

<style>
.fl-hero {
    background: linear-gradient(135deg, #050d24 0%, #0c1e44 50%, #143672 100%);
    color: white;
    padding: 60px 0 40px;
    border-bottom: 4px solid #c9a86b;
    position: relative;
    overflow: hidden;
}
.fl-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 50%, rgba(201,168,107,0.08) 0%, transparent 60%),
        radial-gradient(circle at 80% 50%, rgba(20,54,114,0.5) 0%, transparent 60%);
    pointer-events: none;
}
.fl-hero-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    position: relative;
}
.fl-eyebrow {
    display: inline-block;
    color: #e0c48a;
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 12px;
    border-bottom: 1px solid rgba(224,196,138,0.3);
    padding-bottom: 4px;
}
.fl-h1 {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 44px;
    font-weight: 700;
    margin: 0 0 12px;
    line-height: 1.15;
    color: #fff;
}
.fl-h1-accent { color: #e0c48a; }
.fl-lead {
    font-size: 17px;
    color: rgba(255,255,255,0.85);
    max-width: 720px;
    line-height: 1.6;
    margin: 0;
}
.fl-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 28px;
}
.fl-stat {
    display: flex;
    align-items: baseline;
    gap: 8px;
}
.fl-stat-num {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 36px;
    font-weight: 700;
    color: #e0c48a;
    line-height: 1;
}
.fl-stat-label {
    font-size: 13px;
    color: rgba(255,255,255,0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.fl-toolbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 20px 0;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.fl-toolbar-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.fl-search-wrap {
    position: relative;
}
.fl-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 18px;
}
.fl-search {
    width: 100%;
    padding: 14px 16px 14px 42px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 16px;
    font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.fl-search:focus {
    outline: none;
    border-color: #143672;
    box-shadow: 0 0 0 3px rgba(20,54,114,0.1);
}
.fl-cats {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.fl-chip {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #374151;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-family: inherit;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.fl-chip:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}
.fl-chip.active {
    background: #0c1e44;
    border-color: #0c1e44;
    color: white;
}
.fl-chip-count {
    background: rgba(0,0,0,0.06);
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}
.fl-chip.active .fl-chip-count {
    background: rgba(255,255,255,0.2);
}

.fl-results {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 24px 60px;
}
.fl-result-info {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 20px;
    font-family: 'JetBrains Mono', monospace;
}
.fl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.fl-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.fl-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #c9a86b 0%, #e0c48a 100%);
    transform: scaleX(0);
    transition: transform 0.3s;
    transform-origin: left;
}
.fl-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    border-color: #c9a86b;
}
.fl-card:hover::before { transform: scaleX(1); }
.fl-card.hidden { display: none; }
.fl-card-head {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 14px;
}
.fl-logo {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0c1e44, #143672);
    color: #e0c48a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 22px;
    font-weight: 700;
    flex-shrink: 0;
}
.fl-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}
.fl-card-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 20px;
    font-weight: 600;
    color: #050d24;
    margin: 0 0 4px;
    line-height: 1.2;
}
.fl-card-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #6b7280;
}
.fl-cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.fl-card-desc {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.5;
    margin-bottom: 16px;
    flex: 1;
}
.fl-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #f3f4f6;
}
.fl-card-city {
    font-size: 12px;
    color: #6b7280;
    font-family: 'JetBrains Mono', monospace;
    display: flex;
    align-items: center;
    gap: 4px;
}
.fl-cta {
    background: #0c1e44;
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
}
.fl-cta:hover {
    background: #143672;
    color: #e0c48a;
    text-decoration: none;
}
.fl-cta-arrow {
    transition: transform 0.15s;
}
.fl-cta:hover .fl-cta-arrow {
    transform: translateX(2px);
}

.fl-empty {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
    background: white;
    border: 2px dashed #e5e7eb;
    border-radius: 12px;
}
.fl-empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.4;
}

.fl-bottom-cta {
    background: linear-gradient(135deg, #050d24 0%, #0c1e44 100%);
    color: white;
    padding: 50px 24px;
    text-align: center;
    border-top: 4px solid #c9a86b;
    margin-top: 40px;
}
.fl-bottom-cta h2 {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 32px;
    margin: 0 0 12px;
    color: #e0c48a;
}
.fl-bottom-cta p {
    font-size: 16px;
    color: rgba(255,255,255,0.85);
    max-width: 600px;
    margin: 0 auto 24px;
    line-height: 1.6;
}
.fl-bottom-btns {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
.fl-btn-primary, .fl-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.15s;
}
.fl-btn-primary {
    background: #c8102e;
    color: white;
}
.fl-btn-primary:hover {
    background: #a00d24;
    color: white;
    transform: translateY(-1px);
}
.fl-btn-secondary {
    background: rgba(255,255,255,0.1);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}
.fl-btn-secondary:hover {
    background: rgba(255,255,255,0.15);
    color: #e0c48a;
}

@media (max-width: 768px) {
    .fl-h1 { font-size: 32px; }
    .fl-lead { font-size: 15px; }
    .fl-stat-num { font-size: 28px; }
    .fl-grid { grid-template-columns: 1fr; }
    .fl-toolbar { position: relative; }
}
</style>

<section class="fl-hero">
    <div class="fl-hero-inner">
        <span class="fl-eyebrow">📋 B2B Rehberi</span>
        <h1 class="fl-h1">Demir Çelik Fabrikaları <span class="fl-h1-accent">Fiyat Listesi Rehberi</span></h1>
        <p class="fl-lead">
            Türkiye'nin önde gelen demir-çelik üreticilerinin güncel fiyat listelerine tek sayfadan ulaşın.
            Erdemir, Kardemir, Borusan, Yücel Boru, Tosyalı, İçdaş ve daha fazlası — kategoriye göre filtreleyin, anında bulun.
        </p>
        <div class="fl-stats">
            <div class="fl-stat">
                <span class="fl-stat-num"><?= count($brands) ?></span>
                <span class="fl-stat-label">Fabrika</span>
            </div>
            <div class="fl-stat">
                <span class="fl-stat-num"><?= count($catCounts) ?></span>
                <span class="fl-stat-label">Kategori</span>
            </div>
            <div class="fl-stat">
                <span class="fl-stat-num">7/24</span>
                <span class="fl-stat-label">Erişim</span>
            </div>
        </div>
    </div>
</section>

<div class="fl-toolbar">
    <div class="fl-toolbar-inner">
        <div class="fl-search-wrap">
            <span class="fl-search-icon">🔍</span>
            <input type="text" id="fl-search" class="fl-search"
                   placeholder="Fabrika veya şehir ara… (örn: Erdemir, Yücel Boru, İskenderun)"
                   autocomplete="off">
        </div>
        <div class="fl-cats">
            <button class="fl-chip active" data-cat="all" onclick="filterCat(this)">
                Tümü <span class="fl-chip-count"><?= count($brands) ?></span>
            </button>
            <?php foreach ($categories as $key => $cat): ?>
                <?php if (($catCounts[$key] ?? 0) > 0): ?>
                    <button class="fl-chip" data-cat="<?= htmlspecialchars($key) ?>" onclick="filterCat(this)">
                        <?= $cat['icon'] ?> <?= $cat['label'] ?> <span class="fl-chip-count"><?= $catCounts[$key] ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="fl-results">
    <div class="fl-result-info" id="fl-info">
        <span id="fl-count"><?= count($brands) ?></span> fabrika listeleniyor
    </div>

    <div class="fl-grid" id="fl-grid">
        <?php foreach ($brands as $b):
            $cat = $categories[$b['category']] ?? ['label' => $b['category'], 'icon' => '⚙', 'color' => '#6b7280'];
            $initials = mb_substr($b['brand_name'], 0, 1, 'UTF-8');
            $words = explode(' ', $b['brand_name']);
            if (count($words) > 1) {
                $initials .= mb_substr($words[1], 0, 1, 'UTF-8');
            }
            $searchData = mb_strtolower($b['brand_name'] . ' ' . ($b['city'] ?? '') . ' ' . ($b['region'] ?? '') . ' ' . ($b['description'] ?? ''), 'UTF-8');
        ?>
            <div class="fl-card"
                 data-cat="<?= htmlspecialchars($b['category']) ?>"
                 data-search="<?= htmlspecialchars($searchData) ?>">
                <div class="fl-card-head">
                    <div class="fl-logo">
                        <?php if (!empty($b['brand_logo'])): ?>
                            <img src="<?= htmlspecialchars($b['brand_logo']) ?>" alt="<?= htmlspecialchars($b['brand_name']) ?>">
                        <?php else: ?>
                            <?= htmlspecialchars($initials) ?>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <h3 class="fl-card-title"><?= htmlspecialchars($b['brand_name']) ?></h3>
                        <div class="fl-card-meta">
                            <span class="fl-cat-badge" style="color:<?= $cat['color'] ?>">
                                <?= $cat['icon'] ?> <?= $cat['label'] ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php if (!empty($b['description'])): ?>
                    <p class="fl-card-desc"><?= htmlspecialchars($b['description']) ?></p>
                <?php endif; ?>
                <div class="fl-card-footer">
                    <span class="fl-card-city">
                        <?= !empty($b['city']) ? '📍 ' . htmlspecialchars($b['city']) : '' ?>
                    </span>
                    <a href="<?= htmlspecialchars($b['list_url']) ?>"
                       target="_blank"
                       rel="nofollow noopener external"
                       class="fl-cta">
                        Fiyat Listesi
                        <span class="fl-cta-arrow">→</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="fl-empty" id="fl-empty" style="display:none">
        <div class="fl-empty-icon">🔍</div>
        <strong>Aradığınız fabrika bulunamadı.</strong><br>
        Farklı bir arama veya kategori deneyin.
    </div>
</div>

<section class="fl-bottom-cta">
    <h2>Tekcan Metal'den Doğrudan Teklif Alın</h2>
    <p>
        Yukarıdaki fabrikaların ürünlerine ek olarak, Tekcan Metal stokundan demir-çelik ürünleri
        için <strong>özel B2B fiyat teklifi</strong> almak ister misiniz? Aynı gün dönüş, hızlı sevkiyat.
    </p>
    <div class="fl-bottom-btns">
        <a href="iletisim.php" class="fl-btn-primary">📩 Teklif İste</a>
        <a href="urunler.php" class="fl-btn-secondary">🏭 Ürünlerimiz</a>
    </div>
</section>

<script>
(function(){
    var cards = document.querySelectorAll('.fl-card');
    var searchEl = document.getElementById('fl-search');
    var countEl = document.getElementById('fl-count');
    var emptyEl = document.getElementById('fl-empty');
    var gridEl = document.getElementById('fl-grid');
    var currentCat = 'all';
    var currentSearch = '';

    function applyFilters() {
        var visible = 0;
        cards.forEach(function(c) {
            var cat = c.getAttribute('data-cat');
            var search = c.getAttribute('data-search');
            var catOk = (currentCat === 'all' || cat === currentCat);
            var searchOk = (currentSearch === '' || search.indexOf(currentSearch) !== -1);
            var show = catOk && searchOk;
            c.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        countEl.textContent = visible;
        emptyEl.style.display = visible === 0 ? 'block' : 'none';
        gridEl.style.display = visible === 0 ? 'none' : 'grid';
    }

    window.filterCat = function(btn) {
        document.querySelectorAll('.fl-chip').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        currentCat = btn.getAttribute('data-cat');
        applyFilters();
    };

    searchEl.addEventListener('input', function(e) {
        currentSearch = e.target.value.toLowerCase().trim();
        applyFilters();
    });
})();
</script>

<!-- Structured data: ItemList for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Demir Çelik Fabrikaları Fiyat Listeleri",
    "description": "<?= addslashes($metaDesc) ?>",
    "numberOfItems": <?= count($brands) ?>,
    "itemListElement": [
        <?php foreach ($brands as $i => $b): ?>
        {
            "@type": "ListItem",
            "position": <?= $i + 1 ?>,
            "item": {
                "@type": "Organization",
                "name": "<?= addslashes($b['brand_name']) ?>",
                "url": "<?= addslashes($b['list_url']) ?>"<?php if (!empty($b['city'])): ?>,
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "<?= addslashes($b['city']) ?>",
                    "addressCountry": "TR"
                }<?php endif; ?>
            }
        }<?= $i < count($brands) - 1 ? ',' : '' ?>
        <?php endforeach; ?>
    ]
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
