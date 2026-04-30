<?php
require __DIR__ . '/includes/db.php';
$page = row("SELECT * FROM tm_pages WHERE slug='hakkimizda' AND is_active=1");
$pageTitle = tr_field($page, 'title') ?: t('about.title', 'Hakkımızda');
$metaDesc  = tr_field($page, 'meta_desc') ?: t_setting('site_description');

// Sayfa için ek veriler
$partners = all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order LIMIT 11");
$totalProducts = (int)val("SELECT COUNT(*) FROM tm_products WHERE is_active=1");
$totalCats = (int)val("SELECT COUNT(*) FROM tm_categories WHERE is_active=1");

// AKILLI İÇERİK YÜKLEME:
// Eğer DB'deki içerik yetersizse (3'ten az h3) WP yedeğinden oku
$dbContent = $page['content'] ?? '';
$h3Count = preg_match_all('/<h3[^>]*>/', $dbContent);
if ($h3Count < 3) {
    $wpFile = __DIR__ . '/install/wp-content-full.json.gz';
    if (file_exists($wpFile)) {
        $json = @gzdecode(@file_get_contents($wpFile));
        if ($json) {
            $wpData = @json_decode($json, true);
            if (is_array($wpData) && !empty($wpData['pages'])) {
                foreach ($wpData['pages'] as $wpPage) {
                    if (($wpPage['slug'] ?? '') === 'hakkimizda' && !empty($wpPage['content'])) {
                        $dbContent = $wpPage['content'];
                        break;
                    }
                }
            }
        }
    }
}
$page['content'] = $dbContent;

require __DIR__ . '/includes/header.php';
?>

<style>
/* ═══════════════════════════════════════════════════════════════════
   HAKKIMIZDA — EDITORIAL INDUSTRIAL HERITAGE
   Sayfa-özel stil — global style.css'e dokunmaz
   ═══════════════════════════════════════════════════════════════════ */

@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.about-page{
  --kral-navy:#050d24;
  --kral-navy-2:#0c1e44;
  --kral-navy-3:#143672;
  --kral-red:#c8102e;
  --kral-red-dark:#a00d24;
  --kral-gold:#c9a86b;
  --kral-paper:#fafaf7;
  --kral-paper-2:#f3f1ec;
  --kral-ink:#0a0e1a;
  --kral-ink-soft:#3a3f4f;
  --kral-line:#d7d4cc;
  --kral-serif:'Cormorant Garamond', Georgia, 'Times New Roman', serif;
  --kral-sans:'Inter', system-ui, sans-serif;
  background:var(--kral-paper);
}

/* ═══ 1. HERO — Heroic title with industrial backdrop ═══ */
.kral-hero{
  position:relative;
  background:
    linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.88) 100%),
    url('<?= h(img_url('uploads/pages/tekcan-metal-bina.jpg')) ?>');
  background-size:cover;
  background-position:center;
  color:#fff;
  padding:140px 0 110px;
  overflow:hidden;
  border-bottom:4px solid var(--kral-red);
}
.kral-hero::before{
  /* Engraved stripe pattern */
  content:'';
  position:absolute;inset:0;
  background-image:repeating-linear-gradient(
    -45deg,
    transparent 0,
    transparent 4px,
    rgba(255,255,255,.02) 4px,
    rgba(255,255,255,.02) 5px
  );
  pointer-events:none;
}
.kral-hero::after{
  /* Decorative monogram */
  content:'TM';
  position:absolute;
  bottom:-80px;right:-30px;
  font-family:var(--kral-serif);
  font-size:380px;
  font-weight:500;
  color:rgba(201,168,107,.06);
  letter-spacing:-20px;
  line-height:1;
  pointer-events:none;
  user-select:none;
}
.kral-hero .container{position:relative;z-index:2}
.kral-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--kral-sans);
  font-size:11px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;
  color:var(--kral-gold);
  margin-bottom:26px;
}
.kral-hero-eyebrow::before,
.kral-hero-eyebrow::after{
  content:'';width:40px;height:1px;background:var(--kral-gold);
}
.kral-hero h1{
  font-family:var(--kral-serif);
  font-size:clamp(48px, 7vw, 92px);
  font-weight:500;
  line-height:1;
  letter-spacing:-2px;
  margin:0 0 30px;
  color:#fff;
  max-width:980px;
}
.kral-hero h1 em{
  font-style:italic;
  color:var(--kral-gold);
  font-weight:400;
}
.kral-hero h1 strong{
  font-weight:700;
}
.kral-hero-lead{
  font-family:var(--kral-sans);
  font-size:17px;
  font-weight:400;
  line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:620px;
  margin:0;
}
.kral-hero-meta{
  display:flex;gap:60px;margin-top:60px;
  padding-top:40px;border-top:1px solid rgba(255,255,255,.12);
  flex-wrap:wrap;
}
.kral-hero-meta-item{
  font-family:var(--kral-sans);
}
.kral-hero-meta-label{
  font-size:10.5px;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;
  color:rgba(255,255,255,.5);margin-bottom:8px;
}
.kral-hero-meta-value{
  font-family:var(--kral-serif);
  font-size:32px;font-weight:500;
  color:#fff;
  letter-spacing:-.5px;
}
.kral-hero-meta-value em{color:var(--kral-gold);font-style:normal}

/* ═══ 2. MANIFESTO — Editorial pull-quote ═══ */
.kral-manifesto{
  background:var(--kral-paper);
  padding:120px 0;
  position:relative;
}
.kral-manifesto::before{
  content:'“';
  position:absolute;
  top:30px;left:50%;
  transform:translateX(-50%);
  font-family:var(--kral-serif);
  font-size:240px;
  color:var(--kral-red);
  opacity:.08;
  line-height:1;
  pointer-events:none;
}
.kral-manifesto-inner{
  max-width:880px;margin:0 auto;text-align:center;
  position:relative;z-index:2;
}
.kral-manifesto-eyebrow{
  font-family:var(--kral-sans);
  font-size:11px;font-weight:700;
  letter-spacing:3.5px;text-transform:uppercase;
  color:var(--kral-red);
  margin-bottom:30px;
  display:inline-flex;align-items:center;gap:14px;
}
.kral-manifesto-eyebrow::before,
.kral-manifesto-eyebrow::after{
  content:'';width:30px;height:1px;background:var(--kral-red);
}
.kral-manifesto-quote{
  font-family:var(--kral-serif);
  font-size:clamp(28px, 3.4vw, 44px);
  font-weight:400;
  font-style:italic;
  line-height:1.3;
  color:var(--kral-ink);
  margin:0 0 36px;
  letter-spacing:-.3px;
}
.kral-manifesto-quote strong{
  color:var(--kral-navy-3);
  font-style:normal;
  font-weight:600;
}
.kral-manifesto-sig{
  display:inline-block;
  font-family:var(--kral-sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;
  color:var(--kral-ink-soft);
  padding-top:20px;
  border-top:1px solid var(--kral-line);
}

/* ═══ 3. EDITORIAL MAGAZINE SPREAD ═══ */
.kral-editorial{
  background:#fff;
  padding:0;
  border-top:1px solid var(--kral-line);
}

/* Section opener — like a magazine cover page */
.kral-ed-opener{
  background:#fff;
  padding:90px 0 60px;
  text-align:center;
  position:relative;
  border-bottom:1px solid var(--kral-line);
}
.kral-ed-opener::before{
  content:'';position:absolute;
  top:30px;left:50%;transform:translateX(-50%);
  width:140px;height:1px;
  background:var(--kral-gold);
}
.kral-ed-opener-meta{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:20px;
  font-family:var(--kral-sans);
  font-size:10.5px;
  font-weight:700;
  letter-spacing:3px;
  text-transform:uppercase;
  color:var(--kral-ink-soft);
  margin-bottom:30px;
}
.kral-ed-opener-meta span{display:flex;align-items:center;gap:10px}
.kral-ed-opener-meta span::before{
  content:'';width:6px;height:6px;
  background:var(--kral-red);transform:rotate(45deg);
}
.kral-ed-opener-meta span:first-child::before{display:none}
.kral-ed-opener h2{
  font-family:var(--kral-serif);
  font-size:clamp(48px, 6.5vw, 78px);
  font-weight:500;
  line-height:1.05;
  letter-spacing:-1.5px;
  margin:0 0 24px;
  color:var(--kral-navy);
}
.kral-ed-opener h2 em{
  font-style:italic;color:var(--kral-red);
}
.kral-ed-opener-deck{
  font-family:var(--kral-serif);
  font-size:21px;
  font-style:italic;
  font-weight:400;
  line-height:1.45;
  color:var(--kral-ink-soft);
  max-width:680px;
  margin:0 auto;
}
.kral-ed-opener-deck strong{
  font-style:normal;
  font-weight:600;
  color:var(--kral-navy);
}

/* Lead article — first long-form section with drop cap */
.kral-ed-lead{
  background:#fff;
  padding:80px 0;
  border-bottom:1px solid var(--kral-line);
}
.kral-ed-lead-grid{
  max-width:1080px;margin:0 auto;
  display:grid;
  grid-template-columns:90px 1fr;
  gap:50px;
  align-items:start;
}
@media (max-width:900px){
  .kral-ed-lead-grid{grid-template-columns:1fr;gap:24px}
}
.kral-ed-lead-aside{
  position:sticky;top:120px;
  text-align:right;
}
@media (max-width:900px){
  .kral-ed-lead-aside{position:static;text-align:left;display:flex;align-items:center;gap:18px}
}
.kral-ed-lead-num{
  font-family:var(--kral-serif);
  font-size:72px;
  font-weight:600;
  line-height:1;
  color:var(--kral-red);
  letter-spacing:-3px;
}
.kral-ed-lead-label{
  font-family:var(--kral-sans);
  font-size:9.5px;
  font-weight:700;
  letter-spacing:2.5px;
  text-transform:uppercase;
  color:var(--kral-ink-soft);
  margin-top:6px;
}
.kral-ed-lead-content{
  font-family:var(--kral-sans);
  font-size:16.5px;
  line-height:1.8;
  color:var(--kral-ink-soft);
}
.kral-ed-lead-content h3{
  font-family:var(--kral-serif);
  font-size:30px;
  font-weight:600;
  line-height:1.15;
  letter-spacing:-.4px;
  color:var(--kral-navy);
  margin:0 0 22px;
}
.kral-ed-lead-content > p:first-of-type::first-letter{
  font-family:var(--kral-serif);
  font-size:96px;
  float:left;
  line-height:.85;
  margin:6px 14px 0 0;
  color:var(--kral-red);
  font-weight:600;
}
.kral-ed-lead-content p{margin:0 0 20px}
.kral-ed-lead-content strong{color:var(--kral-navy);font-weight:600}

/* Pull quote between sections */
.kral-ed-pullquote{
  background:linear-gradient(135deg, var(--kral-paper-2) 0%, #f0ede5 100%);
  padding:70px 0;
  border-top:1px solid var(--kral-line);
  border-bottom:1px solid var(--kral-line);
  position:relative;
}
.kral-ed-pullquote::before{
  content:'„';
  position:absolute;
  top:-20px;left:50%;transform:translateX(-50%);
  font-family:var(--kral-serif);
  font-size:200px;
  font-weight:600;
  color:var(--kral-red);
  opacity:.18;
  line-height:1;
  pointer-events:none;
}
.kral-ed-pullquote-inner{
  max-width:820px;margin:0 auto;text-align:center;
  position:relative;z-index:2;
  padding:0 20px;
}
.kral-ed-pullquote p{
  font-family:var(--kral-serif);
  font-size:clamp(26px, 3.4vw, 38px);
  font-weight:400;
  font-style:italic;
  line-height:1.3;
  color:var(--kral-navy);
  margin:0 0 24px;
  letter-spacing:-.3px;
}
.kral-ed-pullquote p strong{
  font-style:normal;
  font-weight:600;
  color:var(--kral-red);
}
.kral-ed-pullquote-attr{
  font-family:var(--kral-sans);
  font-size:11px;font-weight:700;
  letter-spacing:3px;
  text-transform:uppercase;
  color:var(--kral-ink-soft);
  display:inline-block;
  padding-top:18px;
  border-top:1px solid var(--kral-line);
}

/* Article spread — alternating columns */
.kral-ed-spread{
  background:#fff;
  padding:80px 0;
  border-bottom:1px solid var(--kral-line);
}
.kral-ed-section{
  max-width:1180px;margin:0 auto;padding:0 0 70px;
}
.kral-ed-section:last-child{padding-bottom:0}
.kral-ed-section-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:60px;
  align-items:start;
}
@media (max-width:900px){
  .kral-ed-section-grid{grid-template-columns:1fr;gap:24px}
}
/* Reverse layout for alternate sections */
.kral-ed-section.reverse .kral-ed-section-grid{
  direction:rtl;
}
.kral-ed-section.reverse .kral-ed-section-grid > *{direction:ltr}
@media (max-width:900px){
  .kral-ed-section.reverse .kral-ed-section-grid{direction:ltr}
}

.kral-ed-section-meta{
  display:flex;
  flex-direction:column;
  gap:24px;
  position:sticky;top:120px;
}
@media (max-width:900px){.kral-ed-section-meta{position:static}}
.kral-ed-section-num-row{
  display:flex;align-items:flex-end;gap:18px;
  padding-bottom:20px;
  border-bottom:1px solid var(--kral-line);
}
.kral-ed-section-num{
  font-family:var(--kral-serif);
  font-size:88px;
  font-weight:600;
  line-height:.85;
  color:var(--kral-red);
  letter-spacing:-3px;
}
.kral-ed-section-divider{
  flex:1;
  font-family:var(--kral-sans);
  font-size:9.5px;
  font-weight:700;
  letter-spacing:2.5px;
  text-transform:uppercase;
  color:var(--kral-ink-soft);
  padding-bottom:6px;
}
.kral-ed-section-icon{
  width:60px;height:60px;
  border:1.5px solid var(--kral-gold);
  display:flex;align-items:center;justify-content:center;
  color:var(--kral-gold);
}
.kral-ed-section-icon svg{width:28px;height:28px}
.kral-ed-section-tagline{
  font-family:var(--kral-serif);
  font-size:24px;
  font-style:italic;
  font-weight:500;
  line-height:1.25;
  color:var(--kral-navy);
  margin:0;
  letter-spacing:-.2px;
}
.kral-ed-section-tagline em{
  font-style:normal;
  color:var(--kral-red);
}

.kral-ed-section-content{
  font-family:var(--kral-sans);
  font-size:15.5px;
  line-height:1.8;
  color:var(--kral-ink-soft);
}
.kral-ed-section-content h3{
  font-family:var(--kral-serif);
  font-size:32px;
  font-weight:600;
  line-height:1.15;
  letter-spacing:-.4px;
  color:var(--kral-navy);
  margin:0 0 22px;
  padding-bottom:14px;
  border-bottom:3px solid var(--kral-red);
  display:inline-block;
}
.kral-ed-section-content p{margin:0 0 18px}
.kral-ed-section-content strong{color:var(--kral-navy);font-weight:600}
.kral-ed-section-content ul{
  margin:0 0 18px;padding:0;list-style:none;
}
.kral-ed-section-content ul li{
  position:relative;
  padding:8px 0 8px 26px;
  border-bottom:1px dashed var(--kral-line);
}
.kral-ed-section-content ul li::before{
  content:'';position:absolute;
  left:6px;top:18px;
  width:8px;height:8px;
  background:var(--kral-red);
  transform:rotate(45deg);
}

/* Section row separator */
.kral-ed-row-sep{
  display:flex;align-items:center;justify-content:center;
  gap:24px;
  margin:0 auto 60px;
  max-width:1180px;
}
.kral-ed-row-sep::before,
.kral-ed-row-sep::after{
  content:'';flex:1;height:1px;background:var(--kral-line);
}
.kral-ed-row-sep-mark{
  font-family:var(--kral-serif);
  font-size:18px;
  color:var(--kral-gold);
  font-style:italic;
}

/* CTA closing block within editorial */
.kral-ed-finale{
  background:linear-gradient(135deg, var(--kral-navy) 0%, var(--kral-navy-2) 100%);
  color:#fff;
  padding:70px 0;
  text-align:center;
  position:relative;
  overflow:hidden;
  border-top:3px solid var(--kral-gold);
}
.kral-ed-finale::before{
  content:'';position:absolute;
  inset:0;
  background-image:radial-gradient(circle at 50% 50%, rgba(201,168,107,.08) 0%, transparent 60%);
  pointer-events:none;
}
.kral-ed-finale-inner{
  max-width:780px;margin:0 auto;
  position:relative;z-index:2;
  padding:0 20px;
}
.kral-ed-finale-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--kral-sans);
  font-size:10.5px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;
  color:var(--kral-gold);
  margin-bottom:22px;
}
.kral-ed-finale-eyebrow::before,
.kral-ed-finale-eyebrow::after{
  content:'';width:30px;height:1px;background:var(--kral-gold);
}
.kral-ed-finale h3{
  font-family:var(--kral-serif);
  font-size:clamp(28px, 3.6vw, 42px);
  font-weight:500;
  font-style:italic;
  margin:0 0 22px;
  line-height:1.2;
  letter-spacing:-.3px;
}
.kral-ed-finale h3 strong{
  font-style:normal;font-weight:600;color:var(--kral-gold);
}

/* ═══ 4. TIMELINE — Heritage milestones ═══ */
.kral-timeline{
  background:var(--kral-paper-2);
  padding:120px 0;
  position:relative;
}
.kral-timeline::before{
  content:'';position:absolute;
  top:0;left:0;right:0;height:1px;
  background:linear-gradient(to right, transparent 0%, var(--kral-gold) 50%, transparent 100%);
}
.kral-timeline-head{
  text-align:center;max-width:680px;margin:0 auto 80px;
}
.kral-section-eyebrow{
  font-family:var(--kral-sans);
  font-size:11px;font-weight:700;
  letter-spacing:3.5px;text-transform:uppercase;
  color:var(--kral-red);
  margin-bottom:18px;
  display:inline-block;
}
.kral-timeline-head h2{
  font-family:var(--kral-serif);
  font-size:clamp(36px, 4vw, 56px);
  font-weight:500;
  letter-spacing:-1px;
  color:var(--kral-navy);
  margin:0 0 18px;
  line-height:1.1;
}
.kral-timeline-head h2 em{font-style:italic;color:var(--kral-red)}
.kral-timeline-head p{
  font-family:var(--kral-sans);
  font-size:15px;
  color:var(--kral-ink-soft);
  line-height:1.6;
  margin:0;
}

.kral-timeline-list{
  position:relative;
  max-width:880px;margin:0 auto;
}
.kral-timeline-list::before{
  content:'';position:absolute;
  left:120px;top:20px;bottom:20px;
  width:2px;
  background:linear-gradient(to bottom,
    transparent 0%,
    var(--kral-gold) 8%,
    var(--kral-gold) 92%,
    transparent 100%);
}
@media (max-width:700px){
  .kral-timeline-list::before{left:30px}
}
.kral-tl-item{
  display:grid;
  grid-template-columns:120px 40px 1fr;
  gap:28px;
  padding:30px 0;
  align-items:start;
}
@media (max-width:700px){
  .kral-tl-item{
    grid-template-columns:30px 1fr;
    gap:18px;
  }
  .kral-tl-year{display:none}
}
.kral-tl-year{
  font-family:var(--kral-serif);
  font-size:46px;
  font-weight:500;
  line-height:1;
  color:var(--kral-navy);
  text-align:right;
  letter-spacing:-1.5px;
}
.kral-tl-marker{
  position:relative;
  display:flex;justify-content:center;
}
.kral-tl-marker::before{
  content:'';width:14px;height:14px;
  background:var(--kral-red);border:3px solid var(--kral-paper-2);
  margin-top:14px;
  box-shadow:0 0 0 2px var(--kral-red);
  z-index:2;
}
.kral-tl-content{
  background:#fff;
  padding:24px 30px;
  border:1px solid var(--kral-line);
  border-left:3px solid var(--kral-red);
  position:relative;
}
.kral-tl-content::before{
  /* Mobile-only year */
  content:attr(data-year);
  display:none;
  font-family:var(--kral-serif);
  font-size:24px;
  color:var(--kral-red);
  font-weight:500;
  margin-bottom:6px;
}
@media (max-width:700px){
  .kral-tl-content::before{display:block}
}
.kral-tl-title{
  font-family:var(--kral-serif);
  font-size:22px;
  font-weight:600;
  color:var(--kral-navy);
  margin:0 0 6px;
  letter-spacing:-.3px;
}
.kral-tl-desc{
  font-family:var(--kral-sans);
  font-size:14px;
  color:var(--kral-ink-soft);
  line-height:1.65;
  margin:0;
}

/* ═══ 5. VALUES — Three pillars ═══ */
.kral-values{
  background:var(--kral-navy);
  padding:120px 0;
  color:#fff;
  position:relative;
  overflow:hidden;
}
.kral-values::before{
  content:'';position:absolute;
  inset:0;
  background-image:
    radial-gradient(ellipse at 20% 0%, rgba(20,54,114,.4) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 100%, rgba(200,16,46,.15) 0%, transparent 50%);
  pointer-events:none;
}
.kral-values-head{
  text-align:center;max-width:680px;margin:0 auto 80px;
  position:relative;z-index:2;
}
.kral-values-head .kral-section-eyebrow{color:var(--kral-gold)}
.kral-values-head h2{
  font-family:var(--kral-serif);
  font-size:clamp(36px, 4vw, 56px);
  font-weight:500;
  letter-spacing:-1px;
  color:#fff;
  margin:0 0 18px;
  line-height:1.1;
}
.kral-values-head h2 em{font-style:italic;color:var(--kral-gold)}
.kral-values-grid{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  position:relative;z-index:2;
  border-top:1px solid rgba(255,255,255,.1);
  border-bottom:1px solid rgba(255,255,255,.1);
}
@media (max-width:900px){
  .kral-values-grid{grid-template-columns:1fr}
}
.kral-value{
  padding:50px 40px;
  border-right:1px solid rgba(255,255,255,.1);
  text-align:center;
  transition:.3s;
  position:relative;
}
.kral-value:last-child{border-right:0}
@media (max-width:900px){
  .kral-value{border-right:0;border-bottom:1px solid rgba(255,255,255,.1)}
  .kral-value:last-child{border-bottom:0}
}
.kral-value:hover{background:rgba(255,255,255,.02)}
.kral-value-icon{
  width:64px;height:64px;
  margin:0 auto 24px;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--kral-gold);
  color:var(--kral-gold);
}
.kral-value-icon svg{width:30px;height:30px}
.kral-value-num{
  font-family:var(--kral-serif);
  font-size:13px;
  font-style:italic;
  color:var(--kral-gold);
  margin-bottom:14px;
  letter-spacing:1px;
}
.kral-value-title{
  font-family:var(--kral-serif);
  font-size:26px;
  font-weight:500;
  color:#fff;
  margin:0 0 14px;
  letter-spacing:-.3px;
}
.kral-value-desc{
  font-family:var(--kral-sans);
  font-size:14px;
  color:rgba(255,255,255,.65);
  line-height:1.7;
  margin:0;
}

/* ═══ 6. STATS — In numbers ═══ */
.kral-stats{
  background:#fff;
  padding:100px 0;
  border-bottom:1px solid var(--kral-line);
}
.kral-stats-head{
  text-align:center;margin-bottom:70px;
}
.kral-stats-head h2{
  font-family:var(--kral-serif);
  font-size:clamp(34px, 3.5vw, 48px);
  font-weight:500;
  letter-spacing:-.8px;
  color:var(--kral-navy);
  margin:0;
  line-height:1.1;
}
.kral-stats-head h2 em{font-style:italic;color:var(--kral-red)}
.kral-stats-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
  border-top:2px solid var(--kral-navy);
  border-bottom:2px solid var(--kral-navy);
}
@media (max-width:900px){
  .kral-stats-grid{grid-template-columns:repeat(2,1fr)}
}
.kral-stat{
  padding:50px 32px;
  text-align:center;
  border-right:1px solid var(--kral-line);
  position:relative;
}
.kral-stat:last-child{border-right:0}
@media (max-width:900px){
  .kral-stat{border-bottom:1px solid var(--kral-line)}
  .kral-stat:nth-child(2){border-right:0}
  .kral-stat:nth-child(3),.kral-stat:nth-child(4){border-bottom:0}
}
.kral-stat-num{
  font-family:var(--kral-serif);
  font-size:80px;
  font-weight:500;
  line-height:1;
  color:var(--kral-navy);
  letter-spacing:-3px;
  margin-bottom:14px;
}
.kral-stat-num sup{
  font-size:42px;
  color:var(--kral-red);
  vertical-align:top;
  margin-left:2px;
  font-weight:600;
}
.kral-stat-label{
  font-family:var(--kral-sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:2.5px;
  text-transform:uppercase;
  color:var(--kral-ink-soft);
}

/* ═══ 7. PARTNERS — Royal stride ═══ */
.kral-partners{
  background:var(--kral-paper);
  padding:100px 0;
}
.kral-partners-head{
  text-align:center;max-width:680px;margin:0 auto 60px;
}
.kral-partners-head h2{
  font-family:var(--kral-serif);
  font-size:clamp(34px, 3.5vw, 48px);
  font-weight:500;
  letter-spacing:-.8px;
  color:var(--kral-navy);
  margin:0 0 14px;
}
.kral-partners-head h2 em{font-style:italic;color:var(--kral-red)}
.kral-partners-head p{
  font-family:var(--kral-sans);
  font-size:14.5px;
  color:var(--kral-ink-soft);
  line-height:1.65;
  margin:0;
}
.kral-partners-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
  gap:0;
  background:#fff;
  border:1px solid var(--kral-line);
}
.kral-partner{
  padding:30px 20px;
  display:flex;align-items:center;justify-content:center;
  border-right:1px solid var(--kral-line);
  border-bottom:1px solid var(--kral-line);
  transition:.25s;
  background:#fff;
}
.kral-partner:hover{
  background:var(--kral-paper);
  transform:translateY(-2px);
  box-shadow:0 8px 20px rgba(5,13,36,.08);
}
.kral-partner img{
  max-width:100%;max-height:60px;
  object-fit:contain;
  filter:grayscale(100%);
  opacity:.7;
  transition:.25s;
}
.kral-partner:hover img{
  filter:grayscale(0%);
  opacity:1;
}

/* ═══ 8. CLOSING CTA — Royal seal ═══ */
.kral-closing{
  background:linear-gradient(135deg, var(--kral-navy) 0%, var(--kral-navy-2) 100%);
  padding:100px 0;
  text-align:center;
  position:relative;
  overflow:hidden;
  border-top:4px solid var(--kral-red);
}
.kral-closing::before{
  content:'';position:absolute;
  inset:0;
  background-image:
    radial-gradient(circle at 50% 50%, rgba(201,168,107,.08) 0%, transparent 50%);
  pointer-events:none;
}
.kral-closing-inner{
  max-width:780px;margin:0 auto;
  position:relative;z-index:2;
}
.kral-closing-seal{
  width:80px;height:80px;
  margin:0 auto 30px;
  display:flex;align-items:center;justify-content:center;
  border:2px solid var(--kral-gold);
  color:var(--kral-gold);
  position:relative;
}
.kral-closing-seal::before,
.kral-closing-seal::after{
  content:'';position:absolute;
  width:8px;height:8px;
  border:1px solid var(--kral-gold);
}
.kral-closing-seal::before{top:-5px;left:-5px;border-right:0;border-bottom:0}
.kral-closing-seal::after{bottom:-5px;right:-5px;border-left:0;border-top:0}
.kral-closing-seal svg{width:36px;height:36px}
.kral-closing h2{
  font-family:var(--kral-serif);
  font-size:clamp(36px, 4vw, 54px);
  font-weight:500;
  font-style:italic;
  color:#fff;
  margin:0 0 22px;
  line-height:1.15;
  letter-spacing:-.5px;
}
.kral-closing h2 strong{font-style:normal;font-weight:600;color:var(--kral-gold)}
.kral-closing-sig{
  font-family:var(--kral-sans);
  font-size:11.5px;
  font-weight:700;
  letter-spacing:3px;
  text-transform:uppercase;
  color:rgba(255,255,255,.5);
  margin-bottom:50px;
}
.kral-closing-actions{
  display:flex;gap:16px;justify-content:center;flex-wrap:wrap;
}
.kral-btn{
  display:inline-block;
  padding:18px 38px;
  font-family:var(--kral-sans);
  font-size:11.5px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  text-decoration:none;
  transition:.2s;
  border:1px solid transparent;
}
.kral-btn-primary{
  background:var(--kral-red);
  color:#fff;
  border-color:var(--kral-red);
}
.kral-btn-primary:hover{
  background:var(--kral-red-dark);
  border-color:var(--kral-red-dark);
  transform:translateY(-2px);
  box-shadow:0 12px 24px rgba(200,16,46,.4);
}
.kral-btn-ghost{
  background:transparent;
  color:#fff;
  border:1px solid rgba(255,255,255,.3);
}
.kral-btn-ghost:hover{
  background:rgba(255,255,255,.05);
  border-color:var(--kral-gold);
  color:var(--kral-gold);
}

/* Reveal animation */
@media (prefers-reduced-motion: no-preference){
  .kral-reveal{
    opacity:0;
    transform:translateY(20px);
    animation:kralFadeUp .8s cubic-bezier(.2,.7,.3,1) forwards;
  }
  .kral-reveal-1{animation-delay:.1s}
  .kral-reveal-2{animation-delay:.25s}
  .kral-reveal-3{animation-delay:.4s}
  .kral-reveal-4{animation-delay:.55s}
  @keyframes kralFadeUp{
    to{opacity:1;transform:translateY(0)}
  }
}
</style>

<div class="about-page">

  <!-- ═══ 1. HERO ═══ -->
  <section class="kral-hero">
    <div class="container">
      <div class="kral-hero-eyebrow kral-reveal kral-reveal-1"><?= h(t('about.hero_eyebrow', 'Tekcan Metal · 2005')) ?></div>
      <h1 class="kral-reveal kral-reveal-2">
        <?= t('about.hero_h1', 'Demir adına <em>Herşey.</em><br><strong>Yarım asra yakın güven.</strong>') ?>
      </h1>
      <p class="kral-hero-lead kral-reveal kral-reveal-3">
        <?= h(t('about.hero_lead', "Konya'nın köklü demir-çelik tedarikçisi olarak, 2005'ten bu yana sanayi ve inşaat sektörünün her ölçekteki ihtiyacını karşılamak için çalışıyoruz. Türkiye'nin önde gelen üreticilerinin temsilciliği bizim, kalite ve güven sözümüz sizin.")) ?>
      </p>
      <div class="kral-hero-meta kral-reveal kral-reveal-4">
        <div class="kral-hero-meta-item">
          <div class="kral-hero-meta-label"><?= h(t('about.founded', 'Kuruluş')) ?></div>
          <div class="kral-hero-meta-value">2005</div>
        </div>
        <div class="kral-hero-meta-item">
          <div class="kral-hero-meta-label"><?= h(t('about.incorporated', 'Şirketleşme')) ?></div>
          <div class="kral-hero-meta-value">2017 <em>Ltd.</em></div>
        </div>
        <div class="kral-hero-meta-item">
          <div class="kral-hero-meta-label"><?= h(t('about.headquarters', 'Merkez')) ?></div>
          <div class="kral-hero-meta-value"><?= t('about.city_value', 'Karatay <em>·</em> Konya') ?></div>
        </div>
        <div class="kral-hero-meta-item">
          <div class="kral-hero-meta-label"><?= h(t('about.supply_network', 'Tedarik Ağı')) ?></div>
          <div class="kral-hero-meta-value"><?= t('about.network_value', 'Türkiye <em>geneli</em>') ?></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ 2. MANIFESTO ═══ -->
  <section class="kral-manifesto">
    <div class="container">
      <div class="kral-manifesto-inner">
        <div class="kral-manifesto-eyebrow"><?= h(t('about.philosophy_eyebrow', 'Kurum Felsefemiz')) ?></div>
        <p class="kral-manifesto-quote">
          <?= t('about.philosophy_quote', 'Bizim için ticaret yalnızca alım-satım değildir. Demir-çelik, asırlardır insanlığın inşasında yer almış asil bir malzemedir; <strong>onu doğru şekilde tedarik etmek de bir sorumluluktur.</strong> İşte bu yüzden her partide kaliteyi, her teslimatta sözümüzü, her müşteride dostluğu ararız.') ?>
        </p>
        <div class="kral-manifesto-sig"><?= h(t('about.management_sig', '— Tekcan Metal Yönetimi')) ?></div>
      </div>
    </div>
  </section>

  <!-- ═══ 3. EDITORIAL MAGAZINE SPREAD — Ana Hikaye ═══ -->
  <?php
  // DB'den gelen content'i bölümlere parse et
  $rawContent = $page['content'] ?? '';
  $sections = [];
  $leadHtml = '';
  $leadTitle = '';

  if ($rawContent) {
      // Önce: ilk h3'ten önceki kısım (lead text)
      $preH3 = '';
      if (preg_match('/^(.*?)(?=<h3)/s', $rawContent, $m)) {
          $preH3 = trim($m[1]);
      }

      // Sonra: tüm h3 + body parçaları
      $parts = preg_split('/<h3[^>]*>(.*?)<\/h3>/s', $rawContent, -1, PREG_SPLIT_DELIM_CAPTURE);
      // parts: [pre_text, title1, body1, title2, body2, ...]

      $allSections = [];
      for ($i = 1; $i < count($parts); $i += 2) {
          $title = trim(strip_tags($parts[$i] ?? ''));
          $body  = trim($parts[$i+1] ?? '');
          if ($title && $body) {
              $allSections[] = ['title' => $title, 'body' => $body];
          }
      }

      // İlk section LEAD, geri kalanı SPREAD olur
      if (!empty($allSections)) {
          $first = array_shift($allSections);
          $leadTitle = $first['title'];
          // pre-h3 metni varsa lead'in başına ekle, sonra ilk section'ın body'si
          $leadHtml = ($preH3 ? $preH3 : '') . $first['body'];
          $sections = $allSections;
      } else if ($preH3) {
          // h3 yoksa hepsi lead
          $leadHtml = $preH3;
          $leadTitle = 'Tekcan Metal\'i Yakından Tanıyın';
      }
  }

  // Her bölüm için ikon SVG (içerik anahtar kelimelerine göre eşleşir)
  $sectionIcons = [
      'dostluk' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
      'urun'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>',
      'danismanlik' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="9" y1="10" x2="15" y2="10"/></svg>',
      'kalite'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/><path d="M9 12l2 2 4-4"/></svg>',
      'hizmet' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
      'ortak'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
      'hedef'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
      'gelin'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h18"/><polyline points="12 5 19 12 12 19"/></svg>',
      'default' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>'
  ];

  function getSectionIcon($title, $icons) {
      $t = mb_strtolower($title, 'UTF-8');
      if (mb_strpos($t, 'dostluk') !== false || mb_strpos($t, 'felsefe') !== false) return $icons['dostluk'];
      if (mb_strpos($t, 'kaliteden') !== false || mb_strpos($t, 'kalite') !== false) return $icons['kalite'];
      if (mb_strpos($t, 'danışmanlık') !== false || mb_strpos($t, 'danismanlik') !== false) return $icons['danismanlik'];
      if (mb_strpos($t, 'ürün') !== false || mb_strpos($t, 'urun') !== false || mb_strpos($t, 'çeşit') !== false) return $icons['urun'];
      if (mb_strpos($t, 'hizmet') !== false) return $icons['hizmet'];
      if (mb_strpos($t, 'ortak') !== false || mb_strpos($t, 'çözüm') !== false || mb_strpos($t, 'tanıştır') !== false) return $icons['ortak'];
      if (mb_strpos($t, 'hedef') !== false) return $icons['hedef'];
      if (mb_strpos($t, 'gelin') !== false || mb_strpos($t, 'siz de') !== false) return $icons['gelin'];
      return $icons['default'];
  }

  function getSectionTagline($title) {
      $t = mb_strtolower($title, 'UTF-8');
      if (mb_strpos($t, 'dostluk') !== false) return 'Müşteri değil — <em>aile</em>.';
      if (mb_strpos($t, 'kaliteden') !== false || mb_strpos($t, 'kalite') !== false) return 'Sözümüz, <em>mührümüzdür</em>.';
      if (mb_strpos($t, 'danışmanlık') !== false || mb_strpos($t, 'danismanlik') !== false) return 'Sadece tedarik değil; <em>çözüm</em>.';
      if (mb_strpos($t, 'ürün') !== false || mb_strpos($t, 'urun') !== false || mb_strpos($t, 'çeşit') !== false) return 'Stoktan, <em>tek elden</em>.';
      if (mb_strpos($t, 'hizmet') !== false) return 'Sadece tedarik değil; <em>çözüm</em>.';
      if (mb_strpos($t, 'tanıştır') !== false) return 'Türkiye geneline <em>uzanan ağ</em>.';
      if (mb_strpos($t, 'hedef') !== false) return 'Çelik adımlarla <em>geleceğe</em>.';
      if (mb_strpos($t, 'gelin') !== false || mb_strpos($t, 'siz de') !== false) return 'Bizden <em>teklif almayı</em> unutmayın.';
      return 'Tekcan Metal <em>fark eder</em>.';
  }
  ?>

  <section class="kral-editorial">

    <!-- Section opener -->
    <div class="kral-ed-opener">
      <div class="container">
        <div class="kral-ed-opener-meta">
          <span><?= h(t('about.section_i', 'Bölüm I')) ?></span>
          <span><?= h(t('about.our_story', 'Hikâyemiz')) ?></span>
          <span><?= h(t('about.timeline_today', '2005 – Bugün')) ?></span>
        </div>
        <h2><?= t('about.opener_h2', "Seçkin Markalar <em>Tekcan Metal'de</em>") ?></h2>
        <p class="kral-ed-opener-deck">
          <?= t('about.opener_deck', '<strong>Yarım asra yakın güven, tek bir adreste.</strong> Sayfaları çevirdikçe; her partide kaliteyi, her teslimatta sözümüzü, her müşteride dostluğu arayan bir kurumun hikâyesi şekillenecek. <em>Hoşgeldiniz.</em>') ?>
        </p>
      </div>
    </div>

    <!-- Lead article — ilk h3 + body, drop cap ile -->
    <?php if (!empty($leadHtml)): ?>
    <div class="kral-ed-lead">
      <div class="container">
        <div class="kral-ed-lead-grid">
          <div class="kral-ed-lead-aside">
            <div class="kral-ed-lead-num">I</div>
            <div class="kral-ed-lead-label">Kuruluş<br>Hikâyesi</div>
          </div>
          <div class="kral-ed-lead-content">
            <?= $leadHtml ?>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Pull quote between lead and sections -->
    <div class="kral-ed-pullquote">
      <div class="kral-ed-pullquote-inner">
        <p>
          "Demir-çelik <strong>asırlardır</strong> insanlığın inşasında yer almıştır. Onu doğru şekilde tedarik etmek de bir <strong>sorumluluktur</strong>."
        </p>
        <span class="kral-ed-pullquote-attr">— Kurum Felsefemiz</span>
      </div>
    </div>

    <!-- Article spread — alternating sections -->
    <?php if (!empty($sections)): ?>
    <div class="kral-ed-spread">
      <div class="container">

        <?php foreach ($sections as $idx => $sec): ?>
          <?php
          $num = sprintf('%02d', $idx + 2); // 02, 03, 04...
          $isReverse = ($idx % 2 === 1);
          $icon = getSectionIcon($sec['title'], $sectionIcons);
          $tagline = getSectionTagline($sec['title']);
          ?>

          <article class="kral-ed-section <?= $isReverse ? 'reverse' : '' ?>">
            <div class="kral-ed-section-grid">

              <div class="kral-ed-section-meta">
                <div class="kral-ed-section-num-row">
                  <div class="kral-ed-section-num"><?= $num ?></div>
                  <div class="kral-ed-section-divider">— Bölüm <?= $num ?></div>
                </div>
                <div class="kral-ed-section-icon"><?= $icon ?></div>
                <p class="kral-ed-section-tagline"><?= $tagline ?></p>
              </div>

              <div class="kral-ed-section-content">
                <h3><?= h($sec['title']) ?></h3>
                <?= $sec['body'] ?>
              </div>

            </div>
          </article>

          <?php if ($idx < count($sections) - 1): ?>
            <div class="kral-ed-row-sep">
              <span class="kral-ed-row-sep-mark">◆ ◆ ◆</span>
            </div>
          <?php endif; ?>

        <?php endforeach; ?>

      </div>
    </div>
    <?php endif; ?>

    <!-- Editorial finale -->
    <div class="kral-ed-finale">
      <div class="container">
        <div class="kral-ed-finale-inner">
          <div class="kral-ed-finale-eyebrow">Bölüm Sonu · Tekcan Metal'i Seçin</div>
          <h3><?= t('about.cta_h3', 'Geleceğe atılan <strong>çelik adımlar</strong> için, bizden teklif almayı <strong>unutmayın</strong>.') ?></h3>
        </div>
      </div>
    </div>

  </section>

  <!-- ═══ 4. TIMELINE ═══ -->
  <section class="kral-timeline">
    <div class="container">
      <div class="kral-timeline-head">
        <div class="kral-section-eyebrow">Yolculuğumuz</div>
        <h2><?= t('about.timeline_h2', 'Yıllara Sığan <em>Bir Miras</em>') ?></h2>
        <p>Konya'nın küçük bir tedarik dükkânından, Türkiye'nin önde gelen demir-çelik tedarikçilerinden biri olmaya uzanan kurumsal yolculuğumuz.</p>
      </div>

      <div class="kral-timeline-list">

        <div class="kral-tl-item">
          <div class="kral-tl-year">2005</div>
          <div class="kral-tl-marker"></div>
          <div class="kral-tl-content" data-year="2005">
            <h3 class="kral-tl-title"><?= h(t('about.timeline_founding', 'Kuruluş')) ?></h3>
            <p class="kral-tl-desc">Demir-çelik sektörünün pazarlama ve dağıtım ihtiyacını karşılamak amacıyla şahıs şirketi olarak Konya'da kuruldu. İlk yıllarda sınırlı bir ürün portföyüyle Karatay sanayi bölgesine hizmet verdik.</p>
          </div>
        </div>

        <div class="kral-tl-item">
          <div class="kral-tl-year">2017</div>
          <div class="kral-tl-marker"></div>
          <div class="kral-tl-content" data-year="2017">
            <h3 class="kral-tl-title"><?= h(t('about.timeline_incorporation', 'Şirketleşme')) ?></h3>
            <p class="kral-tl-desc">Artan iş hacmi ve müşteri memnuniyetinin getirdiği güvenle <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong> unvanıyla kurumsal yapıya geçildi. Aynı yıl üretici temsilcilikleri portföyü genişletildi.</p>
          </div>
        </div>

        <div class="kral-tl-item">
          <div class="kral-tl-year">2020</div>
          <div class="kral-tl-marker"></div>
          <div class="kral-tl-content" data-year="2020">
            <h3 class="kral-tl-title"><?= h(t('about.timeline_services', 'Hizmet Çeşitlenmesi')) ?></h3>
            <p class="kral-tl-desc">Lazer kesim ve oksijen kesim hizmetleri devreye alındı. Mamul tedariğine ek olarak müşterilerin proje bazlı kesim ihtiyaçları tek elden karşılanmaya başladı.</p>
          </div>
        </div>

        <div class="kral-tl-item">
          <div class="kral-tl-year">2024</div>
          <div class="kral-tl-marker"></div>
          <div class="kral-tl-content" data-year="2024">
            <h3 class="kral-tl-title"><?= h(t('about.timeline_loyalty', 'Müşteri Sadakat Programı')) ?></h3>
            <p class="kral-tl-desc">Uzun yıllardır birlikte çalıştığımız müşterilerimize özel <em>Tekcan Metal Sadakat Programı</em> başlatıldı. Sürekli iş ortaklarımıza özel fiyat avantajı, sevkiyat önceliği ve davet usulü etkinlikler.</p>
          </div>
        </div>

        <div class="kral-tl-item">
          <div class="kral-tl-year">2026</div>
          <div class="kral-tl-marker"></div>
          <div class="kral-tl-content" data-year="2026">
            <h3 class="kral-tl-title"><?= h(t('about.timeline_digital', 'Dijital Dönüşüm')) ?></h3>
            <p class="kral-tl-desc">Yeni nesil <strong>tekcanmetal.com</strong> kurumsal sitesi ve <em>Android mobil uygulamamız</em> yayında. Müşterilerimiz artık ürün katalogları, anlık fiyat sorgulaması, mail-order talimatı ve ağırlık hesaplamasına dijital olarak erişebiliyor.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ═══ 5. VALUES ═══ -->
  <section class="kral-values">
    <div class="container">
      <div class="kral-values-head">
        <div class="kral-section-eyebrow">İlkelerimiz</div>
        <h2><?= t('about.values_h2', 'Üç Sütun Üzerinde <em>Yükseliriz</em>') ?></h2>
      </div>

      <div class="kral-values-grid">

        <div class="kral-value">
          <div class="kral-value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <div class="kral-value-num">— Birinci ilke —</div>
          <h3 class="kral-value-title"><?= h(t('about.value_kalite', 'Kalite')) ?></h3>
          <p class="kral-value-desc">Her partiyi Türkiye'nin önde gelen üreticilerinden tedarik ederiz. Standartların altında ürün, müşterimize değil; bize geri döner.</p>
        </div>

        <div class="kral-value">
          <div class="kral-value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div class="kral-value-num">— İkinci ilke —</div>
          <h3 class="kral-value-title"><?= h(t('about.value_soz', 'Söz')) ?></h3>
          <p class="kral-value-desc">Verdiğimiz teslim tarihi, taahhüt ettiğimiz fiyat, vaat ettiğimiz miktar. Sözümüz, mührümüzdür.</p>
        </div>

        <div class="kral-value">
          <div class="kral-value-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="kral-value-num">— Üçüncü ilke —</div>
          <h3 class="kral-value-title"><?= h(t('about.value_dostluk', 'Dostluk')) ?></h3>
          <p class="kral-value-desc">Bizim için müşteri yalnızca alıcı değil, yıllar süren bir yol arkadaşıdır. <em>Ticaret ile bitmeyen dostluk</em> sloganımız buradan gelir.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- ═══ 6. STATS ═══ -->
  <section class="kral-stats">
    <div class="container">
      <div class="kral-stats-head">
        <h2><?= t('about.stats_h2', 'Rakamlarla <em>Tekcan Metal</em>') ?></h2>
      </div>
      <div class="kral-stats-grid">
        <div class="kral-stat">
          <div class="kral-stat-num">21<sup>+</sup></div>
          <div class="kral-stat-label">Yıllık Tecrübe</div>
        </div>
        <div class="kral-stat">
          <div class="kral-stat-num"><?= $totalProducts ?><sup>+</sup></div>
          <div class="kral-stat-label">Ürün Çeşidi</div>
        </div>
        <div class="kral-stat">
          <div class="kral-stat-num"><?= count($partners) ?></div>
          <div class="kral-stat-label">Çözüm Ortağı</div>
        </div>
        <div class="kral-stat">
          <div class="kral-stat-num">81</div>
          <div class="kral-stat-label">İl Sevkiyat Ağı</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══ 7. PARTNERS ═══ -->
  <?php if (!empty($partners)): ?>
  <section class="kral-partners">
    <div class="container">
      <div class="kral-partners-head">
        <div class="kral-section-eyebrow">Çözüm Ortaklarımız</div>
        <h2><?= t('about.partners_h2', "Türkiye'nin Önde Gelen <em>Üreticileri</em>") ?></h2>
        <p>Demir-çelik sektörünün lider markalarından doğrudan tedarik gücümüzle; orijinal ürünleri, üretici sertifikasıyla ve rekabetçi fiyatlarla müşterilerimize ulaştırıyoruz.</p>
      </div>
      <div class="kral-partners-grid">
        <?php foreach ($partners as $p): ?>
          <div class="kral-partner" title="<?= h($p['name']) ?>">
            <?php if (!empty($p['logo'])): ?>
              <img src="<?= h(img_url($p['logo'])) ?>" alt="<?= h($p['name']) ?>">
            <?php else: ?>
              <span style="font-family:var(--kral-serif);font-size:18px;color:var(--kral-navy);font-weight:600;"><?= h($p['name']) ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ═══ 8. CLOSING CTA ═══ -->
  <section class="kral-closing">
    <div class="container">
      <div class="kral-closing-inner">
        <div class="kral-closing-seal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/>
          </svg>
        </div>
        <h2><?= t('about.philosophy_short', '"Ticaret ile bitmeyen <strong>Dostluk</strong>."') ?></h2>
        <div class="kral-closing-sig">— Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</div>
        <div class="kral-closing-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="kral-btn kral-btn-primary">İletişime Geç</a>
          <a href="<?= h(url('urunler.php')) ?>" class="kral-btn kral-btn-ghost">Ürünleri İncele</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
