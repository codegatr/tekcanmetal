<?php
define('TM_ADMIN', true);
$adminTitle = 'Site Ayarları';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

// Tüm ayarları grup grup oku
$grouped = [];
foreach (all("SELECT * FROM tm_settings ORDER BY setting_group, setting_key") as $r) {
    $grouped[$r['setting_group'] ?: 'general'][$r['setting_key']] = $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    foreach ($_POST['settings'] ?? [] as $key => $value) {
        $group = $_POST['groups'][$key] ?? 'general';
        settings_set($key, (string)$value, $group);
    }
    // logo / favicon yüklemeleri
    foreach (['site_logo','site_logo_dark','favicon'] as $imgKey) {
        if (!empty($_FILES['file_'.$imgKey]['name'])) {
            $up = upload_image($_FILES['file_'.$imgKey], 'uploads/branding');
            if ($up) settings_set($imgKey, $up['path'], 'branding');
        }
    }
    log_activity('update', 'settings', null, 'Site ayarları güncellendi');
    adm_back_with('success', 'Ayarlar kaydedildi.', 'admin/settings.php');
}

$tabs = [
    'general'    => 'Genel',
    'contact'    => 'İletişim',
    'social'     => 'Sosyal Medya',
    'branding'   => 'Marka / Görseller',
    'mail'       => 'Mail / SMTP',
    'seo'        => 'SEO',
    'homepage'   => 'Anasayfa',
    'system'     => 'Sistem',
    'github'     => 'GitHub',
];
$active = $_GET['tab'] ?? 'general';
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>

  <div class="adm-tabs">
    <?php foreach ($tabs as $key => $label): ?>
      <a href="?tab=<?= h($key) ?>" class="<?= $active === $key ? 'active' : '' ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
  </div>

  <?php
  // Hangi alanlar hangi grupta?
  $fields = [
    'general' => [
      ['key'=>'site_name', 'label'=>'Tam Şirket Adı'],
      ['key'=>'site_short_name', 'label'=>'Kısa Ad (header/footer)'],
      ['key'=>'site_slogan', 'label'=>'Slogan'],
      ['key'=>'site_description', 'label'=>'Site Açıklaması', 'type'=>'textarea'],
      ['key'=>'site_keywords', 'label'=>'Anahtar Kelimeler (virgülle)'],
      ['key'=>'company_legal_name', 'label'=>'Yasal Şirket Adı'],
      ['key'=>'company_tax_office', 'label'=>'Vergi Dairesi'],
      ['key'=>'company_tax_id', 'label'=>'Vergi No'],
      ['key'=>'company_mersis', 'label'=>'MERSİS No'],
      ['key'=>'founded_year', 'label'=>'Kuruluş Yılı'],
    ],
    'contact' => [
      ['key'=>'contact_phone', 'label'=>'Telefon'],
      ['key'=>'contact_whatsapp', 'label'=>'WhatsApp Numarası'],
      ['key'=>'contact_email', 'label'=>'E-posta'],
      ['key'=>'contact_address', 'label'=>'Adres', 'type'=>'textarea'],
      ['key'=>'contact_hours', 'label'=>'Çalışma Saatleri', 'type'=>'textarea'],
      ['key'=>'contact_map_embed', 'label'=>'Harita Embed (iframe HTML)', 'type'=>'textarea'],
    ],
    'social' => [
      ['key'=>'social_facebook', 'label'=>'Facebook URL'],
      ['key'=>'social_instagram', 'label'=>'Instagram URL'],
      ['key'=>'social_twitter', 'label'=>'X / Twitter URL'],
      ['key'=>'social_linkedin', 'label'=>'LinkedIn URL'],
      ['key'=>'social_youtube', 'label'=>'YouTube URL'],
    ],
    'branding' => [
      ['key'=>'site_logo', 'label'=>'Logo (açık zemin için)', 'type'=>'image'],
      ['key'=>'site_logo_dark', 'label'=>'Logo (koyu zemin için)', 'type'=>'image'],
      ['key'=>'favicon', 'label'=>'Favicon', 'type'=>'image'],
    ],
    'mail' => [
      ['key'=>'mail_from_name', 'label'=>'Gönderici Adı'],
      ['key'=>'mail_from_email', 'label'=>'Gönderici E-posta'],
      ['key'=>'smtp_host', 'label'=>'SMTP Sunucu'],
      ['key'=>'smtp_port', 'label'=>'SMTP Port'],
      ['key'=>'smtp_user', 'label'=>'SMTP Kullanıcı'],
      ['key'=>'smtp_pass', 'label'=>'SMTP Şifre', 'type'=>'password'],
      ['key'=>'smtp_secure', 'label'=>'Şifreleme (tls/ssl)'],
    ],
    'seo' => [
      ['key'=>'analytics_code', 'label'=>'Google Analytics / GTM Kodu (head içine eklenir)', 'type'=>'textarea'],
      ['key'=>'meta_robots', 'label'=>'Robots Meta (örn: index, follow)'],
      ['key'=>'gsc_verification', 'label'=>'Google Search Console Verification Tag'],
    ],
    'homepage' => [
      ['key'=>'homepage_stat1_value', 'label'=>'İstatistik 1 — Değer'],
      ['key'=>'homepage_stat1_label', 'label'=>'İstatistik 1 — Etiket'],
      ['key'=>'homepage_stat2_value', 'label'=>'İstatistik 2 — Değer'],
      ['key'=>'homepage_stat2_label', 'label'=>'İstatistik 2 — Etiket'],
      ['key'=>'homepage_stat3_value', 'label'=>'İstatistik 3 — Değer'],
      ['key'=>'homepage_stat3_label', 'label'=>'İstatistik 3 — Etiket'],
      ['key'=>'homepage_stat4_value', 'label'=>'İstatistik 4 — Değer'],
      ['key'=>'homepage_stat4_label', 'label'=>'İstatistik 4 — Etiket'],
    ],
    'system' => [
      ['key'=>'maintenance_mode', 'label'=>'Bakım Modu (0/1)'],
      ['key'=>'cache_enabled', 'label'=>'Önbellek Aktif (0/1)'],
    ],
    'github' => [
      ['key'=>'github_repo', 'label'=>'Repo (örn: codegatr/tekcanmetal)'],
      ['key'=>'github_token', 'label'=>'Personal Access Token (gizli)', 'type'=>'password'],
      ['key'=>'auto_update_check', 'label'=>'Otomatik Güncelleme Kontrolü (0/1)'],
    ],
  ];
  ?>

  <div class="adm-panel">
    <div class="adm-panel-body">
      <?php foreach ($fields[$active] ?? [] as $f):
          $key = $f['key'];
          $type = $f['type'] ?? 'text';
          $val = settings($key, '');
          $existingImg = $val;
      ?>
        <div class="row">
          <label><?= h($f['label']) ?></label>
          <input type="hidden" name="groups[<?= h($key) ?>]" value="<?= h($active) ?>">
          <?php if ($type === 'textarea'): ?>
            <textarea name="settings[<?= h($key) ?>]" rows="4"><?= h($val) ?></textarea>
          <?php elseif ($type === 'password'): ?>
            <input type="password" name="settings[<?= h($key) ?>]" value="<?= h($val) ?>" autocomplete="new-password">
          <?php elseif ($type === 'image'): ?>
            <?php if ($existingImg): ?>
              <div style="margin-bottom:8px"><img src="<?= h(img_url($existingImg)) ?>" style="max-height:80px;background:#fff;padding:6px;border-radius:6px"></div>
            <?php endif; ?>
            <input type="file" name="file_<?= h($key) ?>" accept="image/*">
            <input type="hidden" name="settings[<?= h($key) ?>]" value="<?= h($val) ?>">
            <p class="help">Mevcut yol: <code><?= h($val ?: '—') ?></code></p>
          <?php else: ?>
            <input type="text" name="settings[<?= h($key) ?>]" value="<?= h($val) ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
