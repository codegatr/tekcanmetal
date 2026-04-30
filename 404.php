<?php
http_response_code(404);
header('X-Robots-Tag: noindex, nofollow', true);
require_once __DIR__ . '/includes/db.php';
$pageTitle = t('err404.title', 'Sayfa Bulunamadı (404)');
$metaDesc  = t('err404.lead', 'Aradığınız sayfa bulunamadı.');
require __DIR__ . '/includes/header.php';
?>
<section class="section section-404">
  <div class="container container-narrow text-center">
    <div class="err-code">404</div>
    <h1><?= h(t('err404.title', 'Aradığınız Sayfa Bulunamadı')) ?></h1>
    <p><?= h(t('err404.message', 'Bu sayfa kaldırılmış, taşınmış ya da hiç var olmamış olabilir. Anasayfaya dönüp aradığınızı oradan bulabilirsiniz.')) ?></p>
    <div class="cta-actions" style="justify-content:center;margin-top:24px">
      <a href="<?= h(url_lang('')) ?>" class="btn btn-primary"><?= h(t('err404.go_home', 'Anasayfa')) ?></a>
      <a href="<?= h(url_lang('urunler.php')) ?>" class="btn btn-ghost"><?= h(t('header.menu.products', 'Ürün Katalogu')) ?></a>
      <a href="<?= h(url_lang('iletisim.php')) ?>" class="btn btn-ghost"><?= h(t('header.menu.contact', 'İletişim')) ?></a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
