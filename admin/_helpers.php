<?php
/**
 * Admin CRUD yardımcıları
 */

function adm_save(string $table, array $data, ?int $id = null): int {
    if ($id) {
        $sets = []; $params = [];
        foreach ($data as $k => $v) { $sets[] = "$k=?"; $params[] = $v; }
        $params[] = $id;
        q("UPDATE $table SET " . implode(',', $sets) . " WHERE id=?", $params);
        return $id;
    } else {
        $cols = array_keys($data);
        $ph = array_fill(0, count($cols), '?');
        q("INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', $ph) . ")", array_values($data));
        return (int)db()->lastInsertId();
    }
}

function adm_delete(string $table, int $id): void {
    q("DELETE FROM $table WHERE id=?", [$id]);
}

function adm_toggle(string $table, int $id, string $col): void {
    q("UPDATE $table SET $col = 1 - $col WHERE id=?", [$id]);
}

function adm_handle_image_upload(string $field, string $subdir, ?string $existing = null): ?string {
    if (!empty($_FILES[$field]['name'])) {
        $up = upload_image($_FILES[$field], $subdir);
        if ($up) return $up['path'];
    }
    return $existing;
}

function adm_back_with(string $type, string $msg, string $url): void {
    flash($type, $msg);
    redirect($url);
}

/* ========== i18n FIELD HELPERS (v1.0.86+) ==========
 * EN/AR/RU çevirileri için admin formlarında sekmeli alan render eder
 * ve POST'tan i18n alanlarını toplar.
 *
 * Kullanım:
 *   1) Form'da TR alandan sonra: <?= i18n_inputs($row, 'name') ?>
 *      veya textarea için:        <?= i18n_inputs($row, 'description', true, 8) ?>
 *
 *   2) POST handler'da:
 *      $data = i18n_post_merge($data, ['name', 'description', 'short_desc']);
 */

const I18N_LANGS_ADMIN = [
    'en' => ['flag' => '🇬🇧', 'name' => 'English'],
    'ar' => ['flag' => '🇸🇦', 'name' => 'Arabic',  'rtl' => true],
    'ru' => ['flag' => '🇷🇺', 'name' => 'Russian'],
];

/**
 * Bir alan için 3 dilli sekme HTML'i üretir (EN, AR, RU)
 *
 * @param array  $row       DB satırı (mevcut değerler)
 * @param string $field     Alan adı (örn: 'name', 'description')
 * @param bool   $textarea  Textarea mı (true) / input text mi (false)
 * @param int    $rows      Textarea rows (default 4)
 * @param string $label     Üst başlık etiketi (boşsa otomatik üretir)
 */
function i18n_inputs(array $row, string $field, bool $textarea = false, int $rows = 4, string $label = ''): string {
    if (!$label) {
        $label = '🌐 ' . ucfirst($field) . ' — Çeviriler (EN / AR / RU)';
    }
    $h = '<div class="i18n-tabs" data-field="' . htmlspecialchars($field) . '" style="margin-top:6px;border:1px solid #e5e7eb;border-radius:8px;padding:10px;background:#f9fafb">';
    $h .= '<div style="font-size:12px;color:#6b7280;margin-bottom:8px;font-weight:600">' . htmlspecialchars($label) . '</div>';
    $h .= '<div class="i18n-tab-buttons" style="display:flex;gap:4px;margin-bottom:8px">';
    foreach (I18N_LANGS_ADMIN as $code => $info) {
        $h .= '<button type="button" class="i18n-tab-btn" data-tab="' . $code . '" onclick="i18nTab(this)" style="padding:4px 10px;border:1px solid #d1d5db;background:#fff;border-radius:6px;cursor:pointer;font-size:12px">' . $info['flag'] . ' ' . $info['name'] . '</button>';
    }
    $h .= '</div>';
    foreach (I18N_LANGS_ADMIN as $code => $info) {
        $key = $field . '_' . $code;
        $val = $row[$key] ?? '';
        $rtl = !empty($info['rtl']) ? ' dir="rtl"' : '';
        $h .= '<div class="i18n-tab-pane" data-pane="' . $code . '" style="display:none">';
        if ($textarea) {
            $h .= '<textarea name="' . $key . '" rows="' . $rows . '"' . $rtl . ' style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-family:inherit">' . htmlspecialchars($val) . '</textarea>';
        } else {
            $h .= '<input type="text" name="' . $key . '" value="' . htmlspecialchars($val) . '"' . $rtl . ' style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px">';
        }
        $h .= '</div>';
    }
    $h .= '</div>';
    return $h;
}

/**
 * POST'tan i18n alanlarını toplayıp data array'ine ekler.
 * Boş gelen alanlar NULL olarak set edilir (DB'de null tutmak fallback için).
 *
 * @param array $data       Mevcut data array (TR alanlar)
 * @param array $base_fields Hangi base alanlar için i18n toplanacak ['name','description',...]
 */
function i18n_post_merge(array $data, array $base_fields): array {
    foreach ($base_fields as $field) {
        foreach (array_keys(I18N_LANGS_ADMIN) as $lang) {
            $key = $field . '_' . $lang;
            $val = trim($_POST[$key] ?? '');
            $data[$key] = ($val === '') ? null : $val;
        }
    }
    return $data;
}

/**
 * i18n sekme JS'ini sayfaya ekle (sayfa başına bir kere yeterli)
 */
function i18n_tabs_js(): string {
    return <<<'HTML'
<script>
(function(){
  // İlk sekmeyi varsayılan olarak göster (her tab grubu için)
  document.querySelectorAll('.i18n-tabs').forEach(function(tabs){
    var firstBtn = tabs.querySelector('.i18n-tab-btn');
    if (firstBtn) {
      firstBtn.classList.add('active');
      firstBtn.style.background = '#0c1e44';
      firstBtn.style.color = '#fff';
      var firstPane = tabs.querySelector('.i18n-tab-pane');
      if (firstPane) firstPane.style.display = 'block';
    }
  });
})();
function i18nTab(btn){
  var tabs = btn.closest('.i18n-tabs');
  var target = btn.getAttribute('data-tab');
  tabs.querySelectorAll('.i18n-tab-btn').forEach(function(b){
    b.classList.remove('active');
    b.style.background = '#fff';
    b.style.color = '#000';
  });
  btn.classList.add('active');
  btn.style.background = '#0c1e44';
  btn.style.color = '#fff';
  tabs.querySelectorAll('.i18n-tab-pane').forEach(function(p){
    p.style.display = (p.getAttribute('data-pane') === target) ? 'block' : 'none';
  });
}
</script>
HTML;
}
