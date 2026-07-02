<?php

register_hook('before_post_create', function($data) {
    $settings = get_plugin_settings('gonderi-koruma');
    if (empty($settings['enabled'])) {
        return $data;
    }
    $user_id = (int)($data['user_id'] ?? 0);
    if (!$user_id || is_moderator()) {
        return $data;
    }
    $max = max(1, (int)($settings['max_per_minute'] ?? 5));
    $recent = (int)db_query_value(
        "SELECT COUNT(*) FROM posts WHERE user_id = ? AND is_deleted = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
        [$user_id]
    );
    if ($recent >= $max) {
        $data['blocked'] = true;
        $data['message'] = 'Çok hızlı gönderi yapıyorsunuz. Lütfen biraz bekleyin.';
        return $data;
    }
    if (!empty($settings['block_duplicates'])) {
        $content = trim($data['content'] ?? '');
        if ($content !== '') {
            $dup = db_query_row(
                "SELECT id FROM posts WHERE user_id = ? AND content = ? AND is_deleted = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1",
                [$user_id, $content]
            );
            if ($dup) {
                $data['blocked'] = true;
                $data['message'] = 'Bu içeriği kısa süre önce zaten gönderdiniz.';
                return $data;
            }
        }
    }
    return $data;
});
