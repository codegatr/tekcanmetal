<?php
require __DIR__ . '/includes/db.php';
$cat_slug = $_GET['kategori'] ?? '';
$page_no  = max(1, (int)($_GET['s'] ?? 1));
$per_page = 9;
$offset = ($page_no - 1) * $per_page;

$cats = all("SELECT * FROM tm_blog_categories ORDER BY name");

$where = ["p.is_active=1"];
$params = [];
if ($cat_slug) {
    $where[] = "c.slug=?";
    $params[] = $cat_slug;
}
$whereSql = implode(' AND ', $where);

$total = (int)val("SELECT COUNT(*) FROM tm_blog_posts p LEFT JOIN tm_blog_categories c ON c.id=p.category_id WHERE $whereSql", $params);
$pages = max(1, (int)ceil($total / $per_page));

$posts = all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
              FROM tm_blog_posts p LEFT JOIN tm_blog_categories c ON c.id = p.category_id
              WHERE $whereSql
              ORDER BY p.published_at DESC, p.id DESC
              LIMIT $per_page OFFSET $offset", $params);

// Lead post (en yenisi) — sadece ilk sayfada ve filtre yokken
$leadPost = null;
if ($page_no === 1 && !$cat_slug && !empty($posts)) {
    $leadPost = $posts[0];
    $posts = array_slice($posts, 1);
}

$pageTitle = $cat_slug ? 'Haberler — ' . val("SELECT name FROM tm_blog_categories WHERE slug=?", [$cat_slug]) : 'Haberler & Basın';
$metaDesc  = 'Tekcan Metal — demir-çelik sektöründen haberler, ürün rehberleri, basın açıklamaları ve teknik yazılar.';
require __DIR__ . '/includes/header.php';

// Türkçe ay adı yardımcısı
function tr_date_long($date) {
    if (!$date) return '';
    $months = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
    $t = strtotime($date);
    return date('d', $t) . ' ' . $months[date('n', $t)-1] . ' ' . date('Y', $t);
}
?>

<style>
/* ═══════════════════════════════════════════════════════
   BLOG — NY TIMES MEYDAN MAGAZINE STYLE
   ═══════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&family=Source+Sans+3:wght@400;500;600;700&display=swap');

.blog-page{
  --nyt-ink:#121212;
  --nyt-ink-soft:#5a5a5a;
  --nyt-paper:#ffffff;
  --nyt-paper-2:#fcfaf6;
  --nyt-line:#e5e3dd;
  --nyt-line-2:#d4d2cc;
  --nyt-red:#c8102e;
  --nyt-blue:#1e4a9e;
  --nyt-serif:'Playfair Display', Georgia, serif;
  --nyt-sans:'Source Sans 3', -apple-system, sans-serif;
  background:var(--nyt-paper);
}

/* ═══ MASTHEAD — Newspaper banner ═══ */
.nyt-masthead{
  background:#fff;
  border-bottom:3px double var(--nyt-ink);
  padding:36px 0 28px;
  text-align:center;
  position:relative;
}
.nyt-masthead::before,
.nyt-masthead::after{
  content:'';
  position:absolute;
  left:50%;transform:translateX(-50%);
  width:120px;height:1px;
  background:var(--nyt-line-2);
}
.nyt-masthead::before{top:14px}
.nyt-masthead::after{bottom:14px}
.nyt-masthead-date{
  font-family:var(--nyt-sans);
  font-size:11px;
  font-weight:600;
  letter-spacing:1.5px;
  text-transform:uppercase;
  color:var(--nyt-ink-soft);
  margin-bottom:10px;
}
.nyt-masthead-title{
  font-family:var(--nyt-serif);
  font-weight:900;
  font-size:clamp(40px, 6vw, 72px);
  letter-spacing:-1.5px;
  margin:0 0 6px;
  color:var(--nyt-ink);
  line-height:1;
}
.nyt-masthead-tagline{
  font-family:var(--nyt-serif);
  font-style:italic;
  font-size:14px;
  color:var(--nyt-ink-soft);
  margin:0;
}

/* ═══ NAV BAR — Section navigation ═══ */
.nyt-nav{
  background:#fff;
  border-bottom:1px solid var(--nyt-line);
  padding:0;
  position:sticky;top:0;z-index:10;
}
.nyt-nav-inner{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:0;
  flex-wrap:wrap;
  padding:14px 0;
}
.nyt-nav a{
  font-family:var(--nyt-sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--nyt-ink-soft);
  text-decoration:none;
  padding:6px 16px;
  position:relative;
  transition:.15s;
  white-space:nowrap;
}
.nyt-nav a:hover,
.nyt-nav a.active{
  color:var(--nyt-ink);
}
.nyt-nav a.active::after{
  content:'';position:absolute;
  bottom:-14px;left:50%;transform:translateX(-50%);
  width:24px;height:3px;
  background:var(--nyt-red);
}
.nyt-nav-sep{
  display:inline-block;
  width:4px;height:4px;
  background:var(--nyt-line-2);
  border-radius:50%;
  margin:0 4px;
}

/* ═══ LEAD STORY — Big featured ═══ */
.nyt-lead{
  padding:48px 0 36px;
  border-bottom:1px solid var(--nyt-line);
  background:#fff;
}
.nyt-lead-grid{
  display:grid;
  grid-template-columns:1.1fr 1fr;
  gap:50px;
  align-items:center;
}
@media (max-width:900px){
  .nyt-lead-grid{grid-template-columns:1fr;gap:24px}
}
.nyt-lead-img-link{
  display:block;
  overflow:hidden;
  background:var(--nyt-paper-2);
  aspect-ratio:16/10;
}
.nyt-lead-img-link img{
  width:100%;height:100%;
  object-fit:cover;
  transition:.6s ease;
}
.nyt-lead-img-link:hover img{
  transform:scale(1.03);
}
.nyt-lead-img-link .nyt-placeholder{
  width:100%;height:100%;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg, #050d24 0%, #143672 100%);
  color:rgba(255,255,255,.4);
  font-family:var(--nyt-serif);
  font-size:120px;
  font-weight:700;
}
.nyt-lead-cat{
  font-family:var(--nyt-sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--nyt-red);
  margin-bottom:14px;
  display:inline-block;
}
.nyt-lead h2{
  font-family:var(--nyt-serif);
  font-size:clamp(28px, 3.6vw, 48px);
  font-weight:700;
  line-height:1.1;
  letter-spacing:-.5px;
  margin:0 0 18px;
  color:var(--nyt-ink);
}
.nyt-lead h2 a{
  color:inherit;text-decoration:none;
  background-image:linear-gradient(var(--nyt-ink), var(--nyt-ink));
  background-size:0% 1px;
  background-repeat:no-repeat;
  background-position:0 100%;
  transition:.4s;
}
.nyt-lead h2 a:hover{background-size:100% 1px}
.nyt-lead-excerpt{
  font-family:var(--nyt-serif);
  font-size:18px;
  line-height:1.55;
  color:var(--nyt-ink-soft);
  margin:0 0 22px;
  font-style:italic;
}
.nyt-lead-meta{
  font-family:var(--nyt-sans);
  font-size:12px;
  font-weight:600;
  color:var(--nyt-ink-soft);
  letter-spacing:.5px;
  display:flex;
  align-items:center;
  gap:14px;
}
.nyt-lead-meta::before{
  content:'';width:30px;height:1px;background:var(--nyt-line-2);
}
.nyt-lead-author{
  text-transform:uppercase;
  letter-spacing:1.5px;
  font-size:10.5px;
  font-weight:700;
}

/* ═══ SECTION HEADER ═══ */
.nyt-section-head{
  border-top:3px solid var(--nyt-ink);
  border-bottom:1px solid var(--nyt-line);
  padding:14px 0 12px;
  margin:48px 0 32px;
  display:flex;
  justify-content:space-between;
  align-items:baseline;
}
.nyt-section-head h3{
  font-family:var(--nyt-serif);
  font-size:24px;
  font-weight:700;
  font-style:italic;
  margin:0;
  color:var(--nyt-ink);
  letter-spacing:-.3px;
}
.nyt-section-head h3 em{
  font-style:normal;
  color:var(--nyt-red);
}
.nyt-section-head-meta{
  font-family:var(--nyt-sans);
  font-size:11px;
  font-weight:600;
  color:var(--nyt-ink-soft);
  letter-spacing:1px;
  text-transform:uppercase;
}

/* ═══ ARTICLE GRID ═══ */
.nyt-grid{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  border-top:1px solid var(--nyt-line);
  border-left:1px solid var(--nyt-line);
}
@media (max-width:900px){
  .nyt-grid{grid-template-columns:repeat(2, 1fr)}
}
@media (max-width:600px){
  .nyt-grid{grid-template-columns:1fr}
}

.nyt-article{
  padding:28px;
  border-right:1px solid var(--nyt-line);
  border-bottom:1px solid var(--nyt-line);
  display:flex;
  flex-direction:column;
  background:#fff;
  transition:.18s;
}
.nyt-article:hover{
  background:var(--nyt-paper-2);
}
.nyt-article-img{
  display:block;
  margin-bottom:18px;
  overflow:hidden;
  aspect-ratio:4/3;
  background:var(--nyt-paper-2);
}
.nyt-article-img img{
  width:100%;height:100%;
  object-fit:cover;
  transition:.6s;
}
.nyt-article:hover .nyt-article-img img{
  transform:scale(1.05);
}
.nyt-article-img .nyt-placeholder{
  width:100%;height:100%;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg, var(--nyt-blue) 0%, #143672 100%);
  color:rgba(255,255,255,.5);
  font-family:var(--nyt-serif);
  font-size:48px;
  font-weight:600;
}
.nyt-article-cat{
  font-family:var(--nyt-sans);
  font-size:10px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--nyt-red);
  margin-bottom:10px;
  display:inline-block;
  text-decoration:none;
}
.nyt-article-cat:hover{color:var(--nyt-blue)}
.nyt-article h3{
  font-family:var(--nyt-serif);
  font-size:21px;
  font-weight:700;
  line-height:1.2;
  letter-spacing:-.2px;
  margin:0 0 12px;
  color:var(--nyt-ink);
}
.nyt-article h3 a{
  color:inherit;
  text-decoration:none;
  transition:.18s;
}
.nyt-article h3 a:hover{
  color:var(--nyt-red);
}
.nyt-article-excerpt{
  font-family:var(--nyt-serif);
  font-size:14.5px;
  line-height:1.55;
  color:var(--nyt-ink-soft);
  margin:0 0 16px;
  flex:1;
}
.nyt-article-meta{
  font-family:var(--nyt-sans);
  font-size:11px;
  font-weight:600;
  color:var(--nyt-ink-soft);
  letter-spacing:.3px;
  text-transform:uppercase;
  padding-top:14px;
  border-top:1px solid var(--nyt-line);
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.nyt-article-meta a{
  color:var(--nyt-red);
  text-decoration:none;
  font-weight:700;
}
.nyt-article-meta a:hover{
  text-decoration:underline;
}

/* ═══ EMPTY ═══ */
.nyt-empty{
  text-align:center;padding:80px 20px;
  font-family:var(--nyt-serif);
  font-size:24px;font-style:italic;
  color:var(--nyt-ink-soft);
}

/* ═══ PAGER ═══ */
.nyt-pager{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:6px;
  padding:50px 0;
  font-family:var(--nyt-sans);
}
.nyt-pager a{
  display:flex;align-items:center;justify-content:center;
  width:42px;height:42px;
  font-size:13px;font-weight:700;
  color:var(--nyt-ink);
  text-decoration:none;
  border:1px solid var(--nyt-line);
  background:#fff;
  transition:.15s;
}
.nyt-pager a:hover{
  background:var(--nyt-ink);
  color:#fff;border-color:var(--nyt-ink);
}
.nyt-pager a.active{
  background:var(--nyt-red);
  color:#fff;
  border-color:var(--nyt-red);
}

/* ═══ BREADCRUMB (top) ═══ */
.nyt-breadcrumb{
  font-family:var(--nyt-sans);
  font-size:11px;
  font-weight:600;
  letter-spacing:1.2px;
  text-transform:uppercase;
  color:var(--nyt-ink-soft);
  padding:14px 0;
  border-bottom:1px solid var(--nyt-line);
}
.nyt-breadcrumb a{
  color:var(--nyt-ink-soft);
  text-decoration:none;
}
.nyt-breadcrumb a:hover{color:var(--nyt-red)}

</style>

<div class="blog-page">

  <!-- MASTHEAD -->
  <div class="nyt-masthead">
    <div class="container">
      <div class="nyt-masthead-date"><?= tr_date_long(date('Y-m-d')) ?> · KONYA · TÜRKİYE</div>
      <h1 class="nyt-masthead-title">Haberler &amp; Basın</h1>
      <p class="nyt-masthead-tagline">Tekcan Metal'den sektör haberleri, basın açıklamaları, ürün rehberleri ve teknik içerikler</p>
    </div>
  </div>

  <!-- NAV (categories) -->
  <?php if ($cats): ?>
  <div class="nyt-nav">
    <div class="container">
      <div class="nyt-nav-inner">
        <a href="<?= h(url('blog.php')) ?>" class="<?= !$cat_slug ? 'active' : '' ?>">Tümü</a>
        <?php foreach ($cats as $i => $c): ?>
          <span class="nyt-nav-sep"></span>
          <a href="<?= h(url('blog.php?kategori=' . urlencode($c['slug']))) ?>" class="<?= $cat_slug === $c['slug'] ? 'active' : '' ?>"><?= h($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($cat_slug): ?>
  <div class="nyt-breadcrumb">
    <div class="container">
      <a href="<?= h(url('')) ?>">Anasayfa</a> &nbsp;·&nbsp;
      <a href="<?= h(url('blog.php')) ?>">Haberler</a> &nbsp;·&nbsp;
      <span><?= h(val("SELECT name FROM tm_blog_categories WHERE slug=?", [$cat_slug])) ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- LEAD STORY -->
  <?php if ($leadPost): ?>
  <section class="nyt-lead">
    <div class="container">
      <div class="nyt-lead-grid">
        <a href="<?= h(url('blog-detay.php?slug=' . urlencode($leadPost['slug']))) ?>" class="nyt-lead-img-link">
          <?php if (!empty($leadPost['cover_image'])): ?>
            <img src="<?= h(img_url($leadPost['cover_image'])) ?>" alt="<?= h($leadPost['title']) ?>">
          <?php else: ?>
            <div class="nyt-placeholder">T</div>
          <?php endif; ?>
        </a>
        <div>
          <?php if (!empty($leadPost['cat_name'])): ?>
            <span class="nyt-lead-cat"><?= h($leadPost['cat_name']) ?></span>
          <?php else: ?>
            <span class="nyt-lead-cat">Öne Çıkan</span>
          <?php endif; ?>
          <h2><a href="<?= h(url('blog-detay.php?slug=' . urlencode($leadPost['slug']))) ?>"><?= h($leadPost['title']) ?></a></h2>
          <p class="nyt-lead-excerpt">
            <?= h($leadPost['excerpt'] ?: excerpt(strip_tags($leadPost['content']), 220)) ?>
          </p>
          <div class="nyt-lead-meta">
            <span class="nyt-lead-author"><?= h($leadPost['author'] ?? 'Tekcan Metal') ?></span>
            <span><?= h(tr_date_long($leadPost['published_at'])) ?></span>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ARTICLE GRID -->
  <section style="padding-bottom:30px">
    <div class="container">

      <?php if (empty($posts) && empty($leadPost)): ?>
        <div class="nyt-empty">Henüz yayınlanmış bir haber bulunmuyor.</div>
      <?php elseif (!empty($posts)): ?>

        <div class="nyt-section-head">
          <h3>
            <?php if ($cat_slug): ?>
              <em><?= h(val("SELECT name FROM tm_blog_categories WHERE slug=?", [$cat_slug])) ?></em> bölümü
            <?php elseif ($leadPost): ?>
              Daha <em>fazla okuma</em>
            <?php else: ?>
              Tüm <em>yazılar</em>
            <?php endif; ?>
          </h3>
          <span class="nyt-section-head-meta"><?= $total ?> yazı</span>
        </div>

        <div class="nyt-grid">
          <?php foreach ($posts as $p): ?>
          <article class="nyt-article">
            <a href="<?= h(url('blog-detay.php?slug=' . urlencode($p['slug']))) ?>" class="nyt-article-img">
              <?php if (!empty($p['cover_image'])): ?>
                <img src="<?= h(img_url($p['cover_image'])) ?>" alt="<?= h($p['title']) ?>" loading="lazy">
              <?php else: ?>
                <div class="nyt-placeholder">T</div>
              <?php endif; ?>
            </a>
            <?php if (!empty($p['cat_name'])): ?>
              <a href="<?= h(url('blog.php?kategori=' . urlencode($p['cat_slug']))) ?>" class="nyt-article-cat"><?= h($p['cat_name']) ?></a>
            <?php endif; ?>
            <h3><a href="<?= h(url('blog-detay.php?slug=' . urlencode($p['slug']))) ?>"><?= h($p['title']) ?></a></h3>
            <p class="nyt-article-excerpt">
              <?= h($p['excerpt'] ?: excerpt(strip_tags($p['content']), 130)) ?>
            </p>
            <div class="nyt-article-meta">
              <span><?= h(tr_date_long($p['published_at'])) ?></span>
              <a href="<?= h(url('blog-detay.php?slug=' . urlencode($p['slug']))) ?>">Devamı →</a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

      <?php endif; ?>

      <?php if ($pages > 1): ?>
      <nav class="nyt-pager">
        <?php for ($i=1; $i<=$pages; $i++):
            $qs = ['s' => $i] + ($cat_slug ? ['kategori' => $cat_slug] : []);
        ?>
          <a href="?<?= http_build_query($qs) ?>" class="<?= $i === $page_no ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
      <?php endif; ?>

    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
