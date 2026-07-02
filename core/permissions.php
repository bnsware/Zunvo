<?php

function get_mod_permission_keys() {
    return [
        'pin_topic' => 'Konu Sabitleme',
        'lock_topic' => 'Konu Kilitleme',
        'delete_topic' => 'Konu Silme',
        'edit_any_post' => 'Gönderi Düzenleme',
        'resolve_reports' => 'Rapor Yönetimi',
        'approve_title_change' => 'Başlık Onayı',
        'ban_user' => 'Kullanıcı Yasaklama',
        'move_topic' => 'Konu Taşıma',
    ];
}

function get_default_mod_permissions() {
    return array_keys(get_mod_permission_keys());
}

function can_mod($permission) {
    if (is_admin()) {
        return true;
    }
    if (!is_moderator()) {
        return false;
    }
    $user = current_user();
    if (!$user) {
        return false;
    }
    $row = db_query_row(
        "SELECT id FROM moderator_permissions WHERE user_id = ? AND permission_key = ?",
        [$user['id'], $permission]
    );
    if ($row) {
        return true;
    }
    $count = db_query_value(
        "SELECT COUNT(*) FROM moderator_permissions WHERE user_id = ?",
        [$user['id']]
    );
    if ((int)$count === 0) {
        return in_array($permission, get_default_mod_permissions(), true);
    }
    return false;
}

function require_mod($permission) {
    if (!can_mod($permission)) {
        set_flash('error', 'Bu işlem için yetkiniz yok.');
        redirect(url('/'));
        exit;
    }
}

function set_moderator_permissions($user_id, array $permissions) {
    db_execute("DELETE FROM moderator_permissions WHERE user_id = ?", [$user_id]);
    foreach ($permissions as $perm) {
        if (array_key_exists($perm, get_mod_permission_keys())) {
            db_insert(
                "INSERT INTO moderator_permissions (user_id, permission_key) VALUES (?, ?)",
                [$user_id, $perm]
            );
        }
    }
}

function get_moderator_permissions($user_id) {
    $rows = db_query_all(
        "SELECT permission_key FROM moderator_permissions WHERE user_id = ?",
        [$user_id]
    );
    if (empty($rows)) {
        return get_default_mod_permissions();
    }
    return array_column($rows, 'permission_key');
}

function log_mod_action($action, $target_type, $target_id, $details) {
    $user = current_user();
    if (!$user) {
        return false;
    }
    return db_insert(
        "INSERT INTO mod_logs (moderator_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)",
        [$user['id'], $action, $target_type, $target_id, $details]
    );
}

function require_moderator_panel() {
    if (!is_moderator()) {
        set_flash('error', 'Moderatör yetkisi gerekli.');
        redirect(url('/'));
        exit;
    }
}
