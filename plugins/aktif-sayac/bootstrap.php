<?php

register_hook('home_sidebar', function($html) {
    $settings = get_plugin_settings('aktif-sayac');
    if (empty($settings['enabled'])) {
        return $html;
    }
    $minutes = max(1, min(60, (int)($settings['minutes'] ?? 15)));
    $online_count = (int)db_query_value(
        "SELECT COUNT(*) FROM users WHERE is_banned = 0 AND last_active >= DATE_SUB(NOW(), INTERVAL ? MINUTE)",
        [$minutes]
    );
    $html .= '<div class="sidebar-card">';
    $html .= '<h3>' . icon('activity', 'icon icon-sm') . ' Çevrimiçi</h3>';
    $html .= '<p class="plugin-online-count"><strong>' . format_number($online_count) . '</strong> üye aktif</p>';
    if (!empty($settings['show_names']) && $online_count > 0) {
        $name_limit = max(1, min(20, (int)($settings['name_limit'] ?? 8)));
        $online_users = db_query_all(
            "SELECT username FROM users WHERE is_banned = 0 AND last_active >= DATE_SUB(NOW(), INTERVAL ? MINUTE) ORDER BY last_active DESC LIMIT ?",
            [$minutes, $name_limit]
        );
        if (!empty($online_users)) {
            $html .= '<div class="plugin-online-names">';
            $names = [];
            foreach ($online_users as $u) {
                $names[] = '<a href="' . url('/uye/' . $u['username']) . '">' . escape($u['username']) . '</a>';
            }
            $html .= implode(', ', $names);
            if ($online_count > count($online_users)) {
                $html .= ' <span class="plugin-user-meta">+' . ($online_count - count($online_users)) . ' kişi</span>';
            }
            $html .= '</div>';
        }
    }
    $html .= '</div>';
    return $html;
});
