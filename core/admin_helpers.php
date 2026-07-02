<?php

function admin_nav_counts() {
    static $counts = null;
    if ($counts !== null) {
        return $counts;
    }
    $counts = ['reports' => 0, 'approvals' => 0];
    if (!function_exists('db_query_value')) {
        return $counts;
    }
    try {
        $counts['reports'] = (int)db_query_value("SELECT COUNT(*) FROM reports WHERE status = 'pending'");
        $counts['approvals'] = (int)db_query_value("SELECT COUNT(*) FROM change_requests WHERE status = 'pending'");
    } catch (Throwable $e) {
    }
    return $counts;
}

function admin_page_lead($page) {
    $leads = [
        'dashboard' => 'Site özeti, hızlı erişim ve son aktiviteler.',
        'categories' => 'Ana bölümler ve alt forumları buradan yönetin.',
        'topics' => 'Konuları arayın, sabitleyin, kilitleyin veya silin.',
        'widget' => 'Ana sayfadaki aktivite sekmelerini seçin.',
        'users' => 'Üye hesaplarını görüntüleyin ve düzenleyin.',
        'moderators' => 'Moderatör yetkilerini atayın.',
        'reports' => 'Kullanıcı raporlarını inceleyin ve sonuçlandırın.',
        'approvals' => 'Başlık değişikliği gibi bekleyen onayları yönetin.',
        'awards' => 'Kullanıcılara verilecek ödülleri tanımlayın.',
        'settings' => 'Site bilgileri, üyelik ve içerik ayarları.',
        'themes' => 'Görünüm şablonları, ZIP yükleme ve stil dosyaları.',
        'mod_log' => 'Moderasyon işlemlerinin geçmişi.',
        'plugins' => 'Eklentileri etkinleştirin veya yapılandırın.',
        'api_keys' => 'Harici uygulamalar için API anahtarları.',
        'webhooks' => 'Olay bildirimleri için webhook adresleri.',
    ];
    return $leads[$page] ?? '';
}

function admin_nav_section_open($page, array $keys) {
    return in_array($page, $keys, true);
}

function admin_parse_widget_tabs($tabs_json) {
    $keys = [];
    $category_ids = [];
    if ($tabs_json === '') {
        return [
            'keys' => ['recent', 'replied', 'visited', 'popular'],
            'category_ids' => [],
        ];
    }
    $decoded = json_decode($tabs_json, true);
    if (!is_array($decoded)) {
        return ['keys' => ['recent', 'replied', 'popular'], 'category_ids' => []];
    }
    foreach ($decoded as $tab) {
        $key = $tab['key'] ?? '';
        if (strpos($key, 'cat_') === 0) {
            $category_ids[] = (int)substr($key, 4);
        } elseif ($key !== '') {
            $keys[] = $key;
        }
    }
    return ['keys' => $keys, 'category_ids' => $category_ids];
}

function admin_build_widget_tabs(array $keys, array $category_ids, array $categories = []) {
    $presets = [
        'recent' => 'Son Açılan',
        'replied' => 'Son Cevaplanan',
        'visited' => 'Son Gezilen',
        'popular' => 'Popüler',
    ];
    $tabs = [];
    foreach ($keys as $key) {
        if (!isset($presets[$key])) {
            continue;
        }
        $tab = ['key' => $key, 'label' => $presets[$key]];
        if ($key === 'visited') {
            $tab['login_required'] = true;
        }
        $tabs[] = $tab;
    }
    $cat_map = [];
    foreach ($categories as $cat) {
        $cat_map[(int)$cat['id']] = $cat['name'];
    }
    foreach ($category_ids as $cid) {
        $cid = (int)$cid;
        if ($cid <= 0) {
            continue;
        }
        $tabs[] = [
            'key' => 'cat_' . $cid,
            'label' => $cat_map[$cid] ?? ('Kategori #' . $cid),
        ];
    }
    return $tabs;
}

function admin_str_lower($text) {
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, 'UTF-8');
    }
    return strtolower($text);
}

function admin_page_toolbar_start() {
    echo '<div class="admin-toolbar">';
}

function admin_page_toolbar_end() {
    echo '</div>';
}

function admin_render_nav_link($key, $item, $admin_page) {
    $active = $admin_page === $key ? ' active' : '';
    $badge = '';
    if (!empty($item['badge'])) {
        $badge = '<span class="zv-nav-badge">' . (int)$item['badge'] . '</span>';
    }
    $icon = !empty($item['icon']) ? '<span class="zv-nav-icon">' . icon($item['icon'], 'icon') . '</span>' : '';
    echo '<a href="' . $item['url'] . '" class="zv-nav-item' . $active . '" data-nav-label="' . escape(admin_str_lower($item['label'])) . '">';
    echo $icon . '<span class="zv-nav-label">' . escape($item['label']) . '</span>' . $badge;
    echo '</a>';
}

function admin_category_map(array $categories) {
    $map = [];
    foreach ($categories as $cat) {
        $map[(int)$cat['id']] = $cat;
    }
    return $map;
}

function admin_category_parent_name($cat, array $map) {
    if (empty($cat['parent_id'])) {
        return '—';
    }
    $pid = (int)$cat['parent_id'];
    return isset($map[$pid]) ? $map[$pid]['name'] : '#' . $pid;
}

function admin_normalize_category_post($cat_id = null) {
    $forum_type = post_param('forum_type', 'forum');
    if (!in_array($forum_type, ['section', 'forum'], true)) {
        $forum_type = 'forum';
    }
    $parent_raw = post_param('parent_id', '');
    $parent_id = ($parent_raw !== '' && $parent_raw !== null) ? (int)$parent_raw : null;
    if ($forum_type === 'section') {
        $parent_id = null;
    }
    if ($cat_id) {
        $existing = get_category_by_id($cat_id);
        if ($existing) {
            if ($forum_type === 'forum' && !$parent_id && !empty($existing['parent_id'])) {
                $parent_id = (int)$existing['parent_id'];
            }
            if (($existing['forum_type'] ?? '') === 'section' && $forum_type === 'forum' && !empty(get_child_forums($cat_id))) {
                return ['error' => 'Alt forumu olan bölümün türü değiştirilemez.'];
            }
        }
    }
    if ($parent_id && $cat_id && $parent_id === (int)$cat_id) {
        return ['error' => 'Bir kayıt kendi üstü olamaz.'];
    }
    if ($forum_type === 'forum' && !$parent_id && !$cat_id) {
        return ['error' => 'Alt forum için üst bölüm seçin.'];
    }
    if ($parent_id) {
        $parent = get_category_by_id($parent_id);
        if (!$parent) {
            return ['error' => 'Üst bölüm bulunamadı.'];
        }
        if (!$cat_id && ($parent['forum_type'] ?? '') !== 'section') {
            return ['error' => 'Alt forumlar yalnızca bir ana bölüm altına eklenebilir.'];
        }
    }
    $can_create = ($forum_type === 'forum' && post_param('can_create_topic')) ? 1 : 0;
    return [
        'forum_type' => $forum_type,
        'parent_id' => $parent_id,
        'can_create_topic' => $can_create,
    ];
}

function admin_forum_display_tree(array $categories) {
    $children_map = [0 => []];
    foreach ($categories as $cat) {
        $pid = !empty($cat['parent_id']) ? (int)$cat['parent_id'] : 0;
        if (!isset($children_map[$pid])) {
            $children_map[$pid] = [];
        }
        $children_map[$pid][] = $cat;
    }
    $sort_nodes = function ($nodes) {
        usort($nodes, function ($a, $b) {
            $oa = (int)($a['order_num'] ?? 0);
            $ob = (int)($b['order_num'] ?? 0);
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }
            return strcmp($a['name'] ?? '', $b['name'] ?? '');
        });
        return $nodes;
    };
    $build = function ($parent_id) use (&$build, $children_map, $sort_nodes) {
        $nodes = $sort_nodes($children_map[$parent_id] ?? []);
        foreach ($nodes as $i => $node) {
            $nodes[$i]['children'] = $build((int)$node['id']);
        }
        return $nodes;
    };
    return $build(0);
}

function admin_forum_sections_list(array $categories) {
    return admin_forum_parent_options($categories);
}

function admin_forum_parent_options(array $categories) {
    $by_id = [];
    $cat_map = admin_category_map($categories);

    foreach ($categories as $cat) {
        $id = (int)$cat['id'];
        if (($cat['forum_type'] ?? 'forum') === 'section') {
            $by_id[$id] = $cat;
        }
    }

    foreach ($categories as $cat) {
        if (empty($cat['parent_id'])) {
            $id = (int)$cat['id'];
            if (!isset($by_id[$id])) {
                $by_id[$id] = $cat;
            }
        }
    }

    foreach ($categories as $cat) {
        if (empty($cat['parent_id'])) {
            continue;
        }
        $pid = (int)$cat['parent_id'];
        if (!isset($by_id[$pid]) && isset($cat_map[$pid])) {
            $by_id[$pid] = $cat_map[$pid];
        }
    }

    $list = array_values($by_id);
    usort($list, function ($a, $b) {
        $oa = (int)($a['order_num'] ?? 0);
        $ob = (int)($b['order_num'] ?? 0);
        if ($oa !== $ob) {
            return $oa <=> $ob;
        }
        return strcmp($a['name'] ?? '', $b['name'] ?? '');
    });
    return $list;
}
