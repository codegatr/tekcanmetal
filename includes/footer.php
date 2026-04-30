</main>

<footer class="site-footer">
  <div class="container">

    <!-- ── Footer Top: Brand + Newsletter strip ── -->
    <div class="footer-strip">
      <div class="footer-strip-brand">
        <?php $logo = settings('logo', 'assets/img/logo.png'); ?>
        <img src="<?= h(img_url($logo)) ?>" alt="<?= h(settings('site_name', 'Tekcan Metal')) ?>" class="footer-logo">
      </div>
      <div class="footer-strip-tagline">
        <span class="footer-strip-eyebrow"><?= h(t('footer.tagline', 'Demir adına Herşey…')) ?></span>
        <span class="footer-strip-slogan"><?= t('footer.about_short', 'Ticaret ile bitmeyen <em>dostluk</em>.') ?></span>
      </div>
      <div class="footer-strip-cta">
        <a href="<?= h(phone_link(settings('site_phone', '0 332 342 24 52'))) ?>" class="footer-strip-btn footer-strip-btn-primary">📞 <?= h(t('btn.call_now', 'Hemen Ara')) ?></a>
        <a href="<?= h(whatsapp_link(settings('site_whatsapp', '05548350226'))) ?>" target="_blank" rel="noopener" class="footer-strip-btn footer-strip-btn-ghost">💬 <?= h(t('btn.whatsapp', 'WhatsApp')) ?></a>
      </div>
    </div>

    <!-- ── Footer Grid ── -->
    <div class="footer-grid">

      <div class="footer-col footer-col-about">
        <p class="footer-about">
          <?= h(settings('site_description', 'Tekcan Metal — 2005\'ten bu yana Konya merkezli demir-çelik tedarikçisi. Sac, boru, profil, hadde ve özel çelik ürünlerinde Türkiye\'nin önde gelen üreticilerinden doğrudan tedarik.')) ?>
        </p>
        <div class="footer-social">
          <?php if ($f = settings('site_facebook')): ?>
          <a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5V12h3.6l.4-4h-4V6.3c0-1 .2-1.3 1.2-1.3H18V0h-3.8C10.6 0 9 1.6 9 4.7V8z"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($f = settings('site_instagram')): ?>
          <a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4z"/><line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($f = settings('site_linkedin')): ?>
          <a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 1 1 8.3 6.5a1.78 1.78 0 0 1-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0 0 13 14.19a.66.66 0 0 0 0 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 0 1 2.7-1.4c1.55 0 3.36.86 3.36 3.66z"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($f = settings('site_youtube')): ?>
          <a href="<?= h($f) ?>" target="_blank" rel="noopener" aria-label="YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.6 3.6 12 3.6 12 3.6s-7.6 0-9.4.5A3 3 0 0 0 .5 6.2C0 8 0 12 0 12s0 4 .5 5.8a3 3 0 0 0 2.1 2.1c1.8.5 9.4.5 9.4.5s7.6 0 9.4-.5a3 3 0 0 0 2.1-2.1c.5-1.8.5-5.8.5-5.8s0-4-.5-5.8zM9.6 15.6V8.4l6.4 3.6z"/></svg>
          </a>
          <?php endif; ?>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '05548350226'))) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.2-1.7-.8-2-.9-.3-.1-.5-.2-.7.2s-.8.9-1 1.1c-.2.2-.4.2-.7 0a8.4 8.4 0 0 1-2.5-1.5 9 9 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.6l.5-.6c.1-.2.2-.3.3-.5a.5.5 0 0 0 0-.5c-.1-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.8.4 3.4 3.4 0 0 0-1.1 2.6c0 1.5 1.1 3 1.3 3.2.1.2 2.2 3.4 5.3 4.7.7.3 1.3.5 1.8.6.7.2 1.4.2 2 .1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 22a10 10 0 0 1-5.1-1.4l-3.7 1 1-3.5A10 10 0 1 1 12 22"/></svg>
          </a>
        </div>
      </div>

      <div class="footer-col">
        <h4><?= h(t('header.menu.corporate', 'Kurumsal')) ?></h4>
        <ul>
          <li><a href="<?= h(url_lang('hakkimizda.php')) ?>"><?= h(t('header.menu.about', 'Hakkımızda')) ?></a></li>
          <li><a href="<?= h(url_lang('partnerler.php')) ?>"><?= h(t('header.menu.partners', 'Çözüm Ortakları')) ?></a></li>
          <li><a href="<?= h(url_lang('iban.php')) ?>"><?= h(t('header.menu.iban', 'IBAN Bilgilerimiz')) ?></a></li>
          <li><a href="<?= h(url_lang('sss.php')) ?>"><?= h(t('header.menu.faq', 'Sıkça Sorulan Sorular')) ?></a></li>
          <li><a href="<?= h(url_lang('mail-order.php')) ?>"><?= h(t('header.menu.mail_order', 'Mail Order Formu')) ?></a></li>
          <li><a href="<?= h(url_lang('sadakat.php')) ?>"><?= h(t('header.menu.loyalty', 'Sadakat Programı')) ?></a></li>
          <li><a href="<?= h(url_lang('sayfa.php?slug=kvkk')) ?>"><?= h(t('footer.kvkk', 'KVKK Aydınlatma')) ?></a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4><?= h(t('footer.quick_access', 'Hızlı Erişim')) ?></h4>
        <ul>
          <li><a href="<?= h(url_lang('urunler.php')) ?>"><?= h(t('footer.products', 'Tüm Ürünler')) ?></a></li>
          <li><a href="<?= h(url_lang('hizmetler.php')) ?>"><?= h(t('header.menu.services', 'Hizmetlerimiz')) ?></a></li>
          <li><a href="<?= h(url_lang('hesaplama.php')) ?>"><?= h(t('header.menu.calculator', 'Ağırlık Hesaplama')) ?></a></li>
          <li><a href="<?= h(url_lang('galeri.php')) ?>"><?= h(t('header.menu.gallery', 'Foto Galeri')) ?></a></li>
          <li><a href="<?= h(url_lang('blog.php')) ?>"><?= h(t('header.menu.news', 'Haberler & Basın')) ?></a></li>
          <li><a href="<?= h(url_lang('iletisim.php')) ?>"><?= h(t('header.menu.contact', 'İletişim')) ?></a></li>
        </ul>
      </div>

      <div class="footer-col footer-col-contact">
        <h4><?= h(t('footer.contact', 'İletişim')) ?></h4>
        <ul class="footer-contact-list">
          <li>
            <span class="footer-contact-ico">📍</span>
            <span>
              <?= h(settings('site_address', 'Fevziçakmak Mah. Gülistan Cad. Atiker 3, 2.Blok No:33 AS')) ?><br>
              <strong><?= h(settings('site_district', 'Karatay')) ?> / <?= h(settings('site_city', 'Konya')) ?></strong>
            </span>
          </li>
          <li>
            <span class="footer-contact-ico">📞</span>
            <span>
              <a href="<?= h(phone_link(settings('site_phone', '0 332 342 24 52'))) ?>"><?= h(settings('site_phone', '0 332 342 24 52')) ?></a><br>
              <a href="<?= h(phone_link(settings('site_mobile', '0 554 835 0 226'))) ?>" class="footer-contact-mobile"><?= h(settings('site_mobile', '0 554 835 0 226')) ?></a>
            </span>
          </li>
          <li>
            <span class="footer-contact-ico">✉</span>
            <span><a href="mailto:<?= h(settings('site_email', 'info@tekcanmetal.com')) ?>"><?= h(settings('site_email', 'info@tekcanmetal.com')) ?></a></span>
          </li>
          <li>
            <span class="footer-contact-ico">⏰</span>
            <span>
              <strong><?= h(t('footer.weekdays', 'Pazartesi – Cuma')) ?></strong> 08:00 – 18:00<br>
              <strong><?= h(t('footer.saturday', 'Cumartesi')) ?></strong> 08:00 – 13:00
            </span>
          </li>
        </ul>
      </div>

    </div>

    <!-- ── Footer Bottom ── -->
    <div class="footer-bottom">
      <div class="footer-bottom-left">
        © <?= date('Y') ?> <?= h(settings('site_name', 'Tekcan Metal Sanayi ve Ticaret Ltd. Şti.')) ?>. <?= h(t('footer.copyright', 'Tüm hakları saklıdır')) ?>.
      </div>
      <div class="footer-bottom-right">
        <a href="<?= h(url_lang('sayfa.php?slug=kvkk')) ?>"><?= h(t('footer.kvkk_short', 'KVKK')) ?></a>
        <span class="footer-sep">·</span>
        <a href="<?= h(url_lang('sayfa.php?slug=cerez-politikasi')) ?>"><?= h(t('footer.cookie_policy', 'Çerez Politikası')) ?></a>
        <span class="footer-sep">·</span>
        <span class="footer-vendor-wrap"><?= h(t('footer.design_by', 'Tasarım')) ?>: <a href="https://codega.com.tr" target="_blank" rel="noopener" class="footer-vendor">Codega</a></span>
      </div>
    </div>
  </div>
</footer>

<style>
/* ═══ FOOTER REFINED — v1.0.33 ═══ */
.site-footer{
  background:linear-gradient(180deg, #0c1e44 0%, #050d24 100%);
  color:rgba(255,255,255,.85);
  padding:0;
  font-size:13.5px;
  line-height:1.7;
  position:relative;
  margin-top:80px;
}
.site-footer::before{
  content:'';position:absolute;top:0;left:0;right:0;height:3px;
  background:linear-gradient(to right, var(--accent) 0%, var(--primary) 50%, var(--accent) 100%);
}

/* Strip — top brand bar */
.footer-strip{
  padding:36px 0 30px;
  display:grid;
  grid-template-columns:auto 1fr auto;
  gap:32px;
  align-items:center;
  border-bottom:1px solid rgba(255,255,255,.08);
}
@media (max-width:900px){
  .footer-strip{grid-template-columns:1fr;text-align:center;gap:20px}
}
.footer-strip-brand{
  display:flex;align-items:center;
}
.footer-logo{
  height:54px;width:auto;
  filter:brightness(1.1);
}
@media (max-width:900px){
  .footer-strip-brand{justify-content:center}
}
.footer-strip-tagline{
  display:flex;flex-direction:column;gap:2px;
}
.footer-strip-eyebrow{
  font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:var(--accent);
}
.footer-strip-slogan{
  font-family:Georgia, serif;
  font-size:20px;
  color:#fff;
  font-weight:500;
}
.footer-strip-slogan em{
  font-style:italic;color:#c9a86b;
}
.footer-strip-cta{
  display:flex;gap:10px;
}
@media (max-width:900px){
  .footer-strip-cta{justify-content:center}
}
.footer-strip-btn{
  padding:11px 20px;
  font-size:11.5px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;
  text-decoration:none;
  transition:.18s;border:1px solid transparent;
  display:inline-flex;align-items:center;gap:6px;
}
.footer-strip-btn-primary{
  background:var(--accent);color:#fff;border-color:var(--accent);
}
.footer-strip-btn-primary:hover{
  background:var(--accent-dark);transform:translateY(-1px);
}
.footer-strip-btn-ghost{
  background:transparent;color:#fff;border-color:rgba(255,255,255,.25);
}
.footer-strip-btn-ghost:hover{
  background:rgba(255,255,255,.06);border-color:#c9a86b;color:#c9a86b;
}

/* Grid — main columns */
.footer-grid{
  display:grid;
  grid-template-columns:1.7fr 1fr 1fr 1.5fr;
  gap:50px;
  padding:50px 0 40px;
  border-bottom:1px solid rgba(255,255,255,.08);
}
@media (max-width:900px){
  .footer-grid{grid-template-columns:1fr 1fr;gap:36px;padding:36px 0}
}
@media (max-width:600px){
  .footer-grid{grid-template-columns:1fr;gap:28px}
}
.footer-col h4{
  font-size:11.5px;
  font-weight:700;
  letter-spacing:2.5px;
  text-transform:uppercase;
  color:#fff;
  margin:0 0 22px;
  position:relative;
  padding-bottom:14px;
}
.footer-col h4::after{
  content:'';position:absolute;
  bottom:0;left:0;width:32px;height:2px;
  background:var(--accent);
}
.footer-col ul{
  list-style:none;padding:0;margin:0;
}
.footer-col li{
  margin-bottom:10px;
}
.footer-col a{
  color:rgba(255,255,255,.7);
  text-decoration:none;
  font-size:13.5px;
  transition:.18s;
}
.footer-col a:hover{
  color:#fff;
  padding-left:6px;
}

/* About column */
.footer-about{
  color:rgba(255,255,255,.7);
  font-size:13.5px;
  line-height:1.75;
  margin:0 0 22px;
  max-width:380px;
}
.footer-social{
  display:flex;gap:8px;
}
.footer-social a{
  width:36px;height:36px;
  display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.06);
  color:rgba(255,255,255,.7);
  text-decoration:none;
  transition:.2s;
  border:1px solid transparent;
}
.footer-social a:hover{
  background:var(--accent);
  color:#fff;
  border-color:var(--accent);
  transform:translateY(-2px);
  padding-left:0;
}

/* Contact column */
.footer-contact-list{
  display:flex;flex-direction:column;gap:14px;
}
.footer-contact-list li{
  display:grid;
  grid-template-columns:24px 1fr;
  gap:12px;
  align-items:start;
  margin:0;
  font-size:13px;
  line-height:1.55;
  color:rgba(255,255,255,.75);
}
.footer-contact-ico{
  color:var(--accent);
  font-size:14px;
  margin-top:2px;
}
.footer-contact-list strong{
  color:#fff;font-weight:600;
}
.footer-contact-list a{
  color:rgba(255,255,255,.75);
}
.footer-contact-list a:hover{
  color:#fff;padding-left:0;
}
.footer-contact-mobile{
  font-size:12.5px;color:rgba(255,255,255,.55) !important;
}

/* Bottom */
.footer-bottom{
  display:flex;justify-content:space-between;align-items:center;
  padding:22px 0;
  font-size:12px;
  color:rgba(255,255,255,.5);
  flex-wrap:wrap;
  gap:14px;
}
.footer-bottom-right{
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.footer-bottom a{
  color:rgba(255,255,255,.55);
  text-decoration:none;
  transition:.18s;
}
.footer-bottom a:hover{
  color:#fff;
}
.footer-sep{color:rgba(255,255,255,.25)}
.footer-vendor{
  color:#c9a86b !important;
  font-weight:600;
}
.footer-vendor:hover{
  color:var(--accent) !important;
}
</style>

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-bottomnav" aria-label="Alt Menü">
  <div class="mobile-bottomnav-grid">
    <a href="<?= h(url('/')) ?>" class="<?= $current==='index'?'active':'' ?>"><i>🏠</i> Anasayfa</a>
    <a href="<?= h(url('urunler.php')) ?>" class="<?= in_array($current,['urunler','kategori','urun-detay'])?'active':'' ?>"><i>🏗️</i> Ürünler</a>
    <a href="<?= h(url('hesaplama.php')) ?>" class="<?= $current==='hesaplama'?'active':'' ?>"><i>📐</i> Hesapla</a>
    <a href="<?= h(whatsapp_link(settings('site_whatsapp', '05548350226'))) ?>" target="_blank" rel="noopener"><i>💬</i> WhatsApp</a>
    <a href="<?= h(url('iletisim.php')) ?>" class="<?= $current==='iletisim'?'active':'' ?>"><i>📞</i> İletişim</a>
  </div>
</nav>

<script>
window.addEventListener('scroll', function(){
  document.getElementById('siteHeader')?.classList.toggle('scrolled', window.scrollY > 10);
});
const mt = document.getElementById('mobileToggle');
const oc = document.getElementById('offcanvas');
const bd = document.getElementById('ocBackdrop');
const cl = document.getElementById('ocClose');
function ocOpen(){ oc.classList.add('open'); bd.classList.add('open'); document.body.style.overflow='hidden'; }
function ocClose(){ oc.classList.remove('open'); bd.classList.remove('open'); document.body.style.overflow=''; }
mt?.addEventListener('click', ocOpen);
bd?.addEventListener('click', ocClose);
cl?.addEventListener('click', ocClose);
document.querySelectorAll('.faq-q').forEach(b => {
  b.addEventListener('click', () => b.parentElement.classList.toggle('open'));
});
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
