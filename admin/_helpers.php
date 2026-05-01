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
