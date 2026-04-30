</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="footer-brand">
          <span class="logo-mark">T</span>
          <span class="footer-brand-text">Tekcan Metal</span>
        </div>
        <p class="footer-about">
          <?= h(settings('site_description', 'Demir adına Herşey... Sac, boru, profil, hadde ve özel çelik ürünleri ile inşaat, sanayi ve OEM müşterilerine 7/24 hizmet.')) ?>
        </p>
        <div class="footer-social">
          <?php if ($f = settings('site_facebook')): ?><a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="Facebook">f</a><?php endif; ?>
          <?php if ($f = settings('site_instagram')): ?><a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="Instagram">ig</a><?php endif; ?>
          <?php if ($f = settings('site_linkedin')): ?><a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">in</a><?php endif; ?>
          <?php if ($f = settings('site_youtube')): ?><a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="YouTube">yt</a><?php endif; ?>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp'))) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">w</a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Kurumsal</h4>
        <ul>
          <li><a href="<?= h(url('hakkimizda.php')) ?>">Hakkımızda</a></li>
          <li><a href="<?= h(url('partnerler.php')) ?>">Çözüm Ortakları</a></li>
          <li><a href="<?= h(url('iban.php')) ?>">IBAN Bilgilerimiz</a></li>
          <li><a href="<?= h(url('sss.php')) ?>">SSS</a></li>
          <li><a href="<?= h(url('mail-order.php')) ?>">Mail Order</a></li>
          <li><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>">KVKK Aydınlatma</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Hızlı Erişim</h4>
        <ul>
          <li><a href="<?= h(url('urunler.php')) ?>">Tüm Ürünler</a></li>
          <li><a href="<?= h(url('hizmetler.php')) ?>">Hizmetlerimiz</a></li>
          <li><a href="<?= h(url('hesaplama.php')) ?>">Ağırlık Hesaplama</a></li>
          <li><a href="<?= h(url('galeri.php')) ?>">Foto Galeri</a></li>
          <li><a href="<?= h(url('blog.php')) ?>">Blog</a></li>
          <li><a href="<?= h(url('iletisim.php')) ?>">İletişim</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>İletişim</h4>
        <ul>
          <li>📍 <?= h(settings('site_address')) ?></li>
          <li><?= h(settings('site_district')) ?> / <?= h(settings('site_city')) ?></li>
          <li>📞 <a href="<?= h(phone_link(settings('site_phone'))) ?>"><?= h(settings('site_phone')) ?></a></li>
          <li>📱 <a href="<?= h(phone_link(settings('site_mobile'))) ?>"><?= h(settings('site_mobile')) ?></a></li>
          <li>✉ <a href="mailto:<?= h(settings('site_email')) ?>"><?= h(settings('site_email')) ?></a></li>
          <li>⏰ <?= h(settings('working_hours')) ?></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>
        © <?= date('Y') ?> <?= h(settings('site_name')) ?>. Tüm hakları saklıdır.
      </div>
      <div>
        <a href="<?= h(url('sayfa.php?slug=kvkk')) ?>">KVKK</a>
        &nbsp;·&nbsp;
        <a href="<?= h(url('sayfa.php?slug=cerez-politikasi')) ?>">Çerez Politikası</a>
        &nbsp;·&nbsp;
        <span>Tasarım: <a href="https://codega.com.tr" target="_blank" rel="noopener" class="footer-vendor">Codega</a></span>
      </div>
    </div>
  </div>
</footer>

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-bottomnav" aria-label="Alt Menü">
  <div class="mobile-bottomnav-grid">
    <a href="<?= h(url('/')) ?>" class="<?= $current==='index'?'active':'' ?>"><i>🏠</i> Anasayfa</a>
    <a href="<?= h(url('urunler.php')) ?>" class="<?= in_array($current,['urunler','kategori','urun-detay'])?'active':'' ?>"><i>🏗️</i> Ürünler</a>
    <a href="<?= h(url('hesaplama.php')) ?>" class="<?= $current==='hesaplama'?'active':'' ?>"><i>📐</i> Hesapla</a>
    <a href="<?= h(whatsapp_link(settings('site_whatsapp'))) ?>" target="_blank" rel="noopener"><i>💬</i> WhatsApp</a>
    <a href="<?= h(url('iletisim.php')) ?>" class="<?= $current==='iletisim'?'active':'' ?>"><i>📞</i> İletişim</a>
  </div>
</nav>

<script>
// Header sticky shadow
window.addEventListener('scroll', function(){
  document.getElementById('siteHeader')?.classList.toggle('scrolled', window.scrollY > 10);
});
// Mobile menu
const mt = document.getElementById('mobileToggle');
const oc = document.getElementById('offcanvas');
const bd = document.getElementById('ocBackdrop');
const cl = document.getElementById('ocClose');
function ocOpen(){ oc.classList.add('open'); bd.classList.add('open'); document.body.style.overflow='hidden'; }
function ocClose(){ oc.classList.remove('open'); bd.classList.remove('open'); document.body.style.overflow=''; }
mt?.addEventListener('click', ocOpen);
bd?.addEventListener('click', ocClose);
cl?.addEventListener('click', ocClose);
// FAQ
document.querySelectorAll('.faq-q').forEach(b => {
  b.addEventListener('click', () => b.parentElement.classList.toggle('open'));
});
// IBAN copy
document.querySelectorAll('.iban-copy').forEach(b => {
  b.addEventListener('click', () => {
    const t = b.dataset.iban;
    if (t && navigator.clipboard) {
      navigator.clipboard.writeText(t).then(() => {
        const old = b.textContent;
        b.textContent = '✓ KOPYALANDI';
        setTimeout(() => b.textContent = old, 1600);
      });
    }
  });
});
</script>

</body>
</html>
