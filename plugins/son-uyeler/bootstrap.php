<?php

register_hook('home_sidebar', function($html) {
    $settings = get_plugin_settings('son-uyeler');
    if (empty($settings['enabled'])) {
        return $html;
    }
    $limit = max(1, min(20, (int)($settings['limit'] ?? 5)));
    $users = db_query_all(
        "SELECT id, username, created_at FROM users WHERE is_banned = 0 ORDER BY created_at DESC LIMIT ?",
        [$limit]
    );
    if (empty($users)) {
        return $html;
    }
    $show_date = !empty($settings['show_date']);
    $html .= '<div class="sidebar-card">';
    $html .= '<h3>' . icon('users', 'icon icon-sm') . ' Son Üyeler</h3>';
    $html .= '<ul class="plugin-user-list">';
    foreach ($users as $user) {
        $html .= '<li class="plugin-user-item">';
        $html .= '<a href="' . url('/uye/' . $user['username']) . '">' . escape($user['username']) . '</a>';
        if ($show_date) {
            $html .= '<span class="plugin-user-meta">' . time_ago($user['created_at']) . '</span>';
        }
        $html .= '</li>';
    }
    $html .= '</ul></div>';
    return $html;
});
