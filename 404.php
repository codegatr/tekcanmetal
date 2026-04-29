<?php
http_response_code(404);
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Sayfa Bulunamadı (404)';
$metaDesc  = 'Aradığınız sayfa bulunamadı.';
require __DIR__ . '/includes/header.php';
?>
<section class="section section-404">
  <div class="container container-narrow text-center">
    <div class="err-code">404</div>
    <h1>Aradığınız Sayfa Bulunamadı</h1>
    <p>Bu sayfa kaldırılmış, taşınmış ya da hiç var olmamış olabilir. Anasayfaya dönüp aradığınızı oradan bulabilirsiniz.</p>
    <div class="cta-actions" style="justify-content:center;margin-top:24px">
      <a href="<?= h(url('')) ?>" class="btn btn-primary">Anasayfa</a>
      <a href="<?= h(url('urunler.php')) ?>" class="btn btn-ghost">Ürün Katalogu</a>
      <a href="<?= h(url('iletisim.php')) ?>" class="btn btn-ghost">İletişim</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
