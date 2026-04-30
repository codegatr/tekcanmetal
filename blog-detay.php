<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$post = row("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
             FROM tm_blog_posts p LEFT JOIN tm_blog_categories c ON c.id=p.category_id
             WHERE p.slug=? AND p.is_active=1", [$slug]);
if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
// görüntülenme sayacı (basit)
try { q("UPDATE tm_blog_posts SET view_count = view_count + 1 WHERE id=?", [$post['id']]); } catch (Throwable $e) {}

$related = $post['category_id']
    ? all("SELECT * FROM tm_blog_posts WHERE category_id=? AND id<>? AND is_active=1 ORDER BY published_at DESC LIMIT 3", [$post['category_id'], $post['id']])
    : all("SELECT * FROM tm_blog_posts WHERE id<>? AND is_active=1 ORDER BY published_at DESC LIMIT 3", [$post['id']]);

$pageTitle = tr_field($post, 'title') ?: $post['title'];
$metaDesc  = tr_field($post, 'meta_desc') ?: tr_field($post, 'excerpt') ?: excerpt(tr_field($post, 'content'), 160);

// SEO: Schema.org BlogPosting + Breadcrumb + FAQ markup
$siteUrl = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
$postUrl = $siteUrl . '/blog-detay.php?slug=' . urlencode($post['slug']);
$postImage = !empty($post['cover_image']) ? $siteUrl . '/' . $post['cover_image'] : $siteUrl . '/' . settings('logo', 'assets/img/logo.png');
$postContent = tr_field($post, 'content') ?: $post['content'];

// FAQ extraction (içerikte <h4>...</h4><p>...</p> patterni varsa)
$faqItems = [];
if (preg_match_all('#<h4>([^<]+)</h4>\s*<p>(.+?)</p>#s', $postContent, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $m) {
        $q = trim(strip_tags($m[1]));
        $a = trim(strip_tags($m[2]));
        // Sadece soru gibi görünenler (? ile bitiyor veya soru kelimesi içeriyor)
        if (mb_substr($q, -1) === '?' || preg_match('/(nedir|kaç|hangi|nasıl|var mı|kimdir)/iu', $q)) {
            if (mb_strlen($a) > 20 && mb_strlen($a) < 800) {
                $faqItems[] = ['q' => $q, 'a' => $a];
            }
        }
    }
}

// Build $schemaJson string before header include
$schemaItems = [];

// BlogPosting Schema
$schemaItems[] = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => tr_field($post, 'title') ?: $post['title'],
    'description' => $metaDesc,
    'image' => $postImage,
    'author' => ['@type' => 'Organization', 'name' => $post['author'] ?? 'Tekcan Metal', 'url' => $siteUrl],
    'publisher' => [
        '@type' => 'Organization',
        'name' => settings('site_short_name', 'Tekcan Metal'),
        'logo' => ['@type' => 'ImageObject', 'url' => $siteUrl . '/' . settings('logo', 'assets/img/logo.png')],
    ],
    'datePublished' => date('c', strtotime($post['published_at'] ?: 'now')),
    'dateModified' => date('c', strtotime($post['updated_at'] ?? $post['published_at'] ?? 'now')),
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $postUrl],
    'inLanguage' => current_lang(),
];

// Breadcrumb Schema
$schemaItems[] = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Anasayfa', 'item' => $siteUrl],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $siteUrl . '/blog.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => tr_field($post, 'title') ?: $post['title']],
    ],
];

// FAQ Schema (eğer içerikte SSS varsa)
if (count($faqItems) >= 2) {
    $faqEntities = [];
    foreach ($faqItems as $f) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ];
    }
    $schemaItems[] = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ];
}

require __DIR__ . '/includes/header.php';
?>
<?php foreach ($schemaItems as $sch): ?>
<script type="application/ld+json"><?= json_encode($sch, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?></script>
<?php endforeach; ?>
<article class="blog-detail">
  <header class="blog-detail-head">
    <div class="container container-narrow">
      <nav class="breadcrumb">
        <a href="<?= h(url_lang('')) ?>">Anasayfa</a> <span>›</span>
        <a href="<?= h(url_lang('blog.php')) ?>">Blog</a> <span>›</span>
        <span><?= h(tr_field($post, 'title') ?: $post['title']) ?></span>
      </nav>
      <?php if (!empty($post['cat_name'])): ?>
        <a href="<?= h(url('blog.php?kategori=' . urlencode($post['cat_slug']))) ?>" class="blog-cat-tag"><?= h($post['cat_name']) ?></a>
      <?php endif; ?>
      <h1><?= h(tr_field($post, 'title') ?: $post['title']) ?></h1>
      <div class="blog-meta">
        <span>📅 <?= h(tr_date($post['published_at'])) ?></span>
        <?php if (!empty($post['author_name'])): ?><span>✍ <?= h($post['author_name']) ?></span><?php endif; ?>
        <span>👁 <?= (int)$post['view_count'] ?> okunma</span>
      </div>
    </div>
  </header>

  <?php if (!empty($post['cover_image'])): ?>
  <div class="blog-detail-img">
    <div class="container container-narrow">
      <img src="<?= h(img_url($post['cover_image'])) ?>" alt="<?= h(tr_field($post, 'title') ?: $post['title']) ?>">
    </div>
  </div>
  <?php endif; ?>

  <div class="blog-detail-body">
    <div class="container container-narrow">
      <div class="content-prose"><?= tr_field($post, 'content') ?: $post['content'] ?></div>
    </div>
  </div>
</article>

<?php if ($related): ?>
<section class="section bg-alt">
  <div class="container">
    <h2 class="section-title">İlgili Yazılar</h2>
    <div class="blog-grid">
      <?php foreach ($related as $r): ?>
      <article class="blog-card">
        <a href="<?= h(url('blog-detay.php?slug=' . urlencode($r['slug']))) ?>" class="blog-img-link">
          <?php if (!empty($r['cover_image'])): ?>
            <img src="<?= h(img_url($r['cover_image'])) ?>" alt="<?= h($r['title']) ?>" loading="lazy">
          <?php else: ?>
            <div class="blog-placeholder">📝</div>
          <?php endif; ?>
        </a>
        <div class="blog-body">
          <h3><a href="<?= h(url('blog-detay.php?slug=' . urlencode($r['slug']))) ?>"><?= h($r['title']) ?></a></h3>
          <p><?= h($r['excerpt'] ?: excerpt($r['content'], 100)) ?></p>
          <div class="blog-meta">
            <span><?= h(tr_date($r['published_at'])) ?></span>
            <a href="<?= h(url('blog-detay.php?slug=' . urlencode($r['slug']))) ?>">Oku →</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
