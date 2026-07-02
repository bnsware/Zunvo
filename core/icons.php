<?php

require_once __DIR__ . '/icon-paths.php';

function icon($name, $class = 'icon', $size = 20) {
    $paths = get_icon_paths();
    $body = $paths[$name] ?? $paths['message'];
    $classAttr = $class ? ' class="' . escape($class) . '"' : '';
    return '<svg' . $classAttr . ' width="' . (int)$size . '" height="' . (int)$size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

function get_icon_catalog() {
    return [
        'Genel' => ['home', 'folder', 'layout', 'grid', 'list', 'layers', 'box', 'archive', 'inbox', 'package', 'search', 'filter', 'refresh', 'settings', 'menu', 'plus', 'minus', 'check', 'x', 'eye', 'clock', 'calendar', 'tag', 'hash', 'link', 'external-link', 'share'],
        'İletişim' => ['message', 'chat', 'mail', 'send', 'phone', 'megaphone', 'mention', 'reply', 'bell'],
        'Topluluk' => ['user', 'users', 'user-plus', 'heart', 'star', 'thumbs-up', 'thumbs-down', 'smile', 'gift'],
        'Teknoloji' => ['code', 'terminal', 'cpu', 'database', 'server', 'cloud', 'wifi', 'smartphone', 'monitor', 'laptop', 'bot', 'plugin'],
        'Medya' => ['image', 'video', 'music', 'headphones', 'mic', 'camera', 'film', 'radio', 'tv', 'play'],
        'Eğitim' => ['book', 'bookmark', 'newspaper', 'graduation-cap', 'library', 'brain'],
        'Konum' => ['globe', 'map', 'map-pin', 'compass', 'navigation'],
        'Ulaşım' => ['car', 'plane', 'truck', 'bike', 'ship'],
        'İş' => ['building', 'store', 'shopping-cart', 'dollar-sign', 'wallet', 'trending-up', 'bar-chart', 'activity', 'pie-chart'],
        'Güvenlik' => ['shield', 'shield-check', 'lock', 'key', 'ban'],
        'Ödül' => ['award', 'badge', 'trophy', 'medal', 'crown', 'flag', 'target', 'gem', 'sparkles'],
        'Oyun' => ['gamepad', 'puzzle', 'dice'],
        'Doğa' => ['sun', 'moon', 'cloud-rain', 'umbrella', 'droplet', 'flame', 'tree', 'zap', 'feather'],
        'Araçlar' => ['lightbulb', 'rocket', 'wrench', 'hammer', 'brush', 'palette', 'edit', 'trash', 'pin', 'scissors', 'paperclip', 'printer'],
        'Dosya' => ['file', 'file-text', 'download', 'upload'],
        'Sağlık' => ['stethoscope', 'pill', 'dumbbell'],
        'Diğer' => ['help', 'info', 'alert', 'log-in', 'log-out', 'loader', 'coffee', 'pizza'],
    ];
}

function icon_label($slug) {
    $labels = [
        'thumbs-up' => 'Beğeni', 'thumbs-down' => 'Beğenmeme', 'user-plus' => 'Kullanıcı Ekle',
        'log-in' => 'Giriş', 'log-out' => 'Çıkış', 'cloud-rain' => 'Yağmur', 'map-pin' => 'Konum İğnesi',
        'graduation-cap' => 'Mezuniyet', 'file-text' => 'Metin Dosyası', 'external-link' => 'Dış Link',
        'shield-check' => 'Korumalı', 'megaphone' => 'Duyuru', 'gamepad' => 'Oyun', 'wifi' => 'Wi-Fi',
    ];
    return $labels[$slug] ?? ucwords(str_replace('-', ' ', $slug));
}

function icon_picker_field($name, $value = 'folder', $id = null) {
    $paths = get_icon_paths();
    $value = preg_match('/^[a-z0-9-]+$/', (string)$value) ? $value : 'folder';
    if (!isset($paths[$value])) {
        $value = 'folder';
    }
    $field_id = $id ?: $name;
    if ($field_id === 'icon') {
        $field_id = 'icon';
    }
    return '<div class="admin-icon-picker" data-icon-picker>'
        . '<input type="hidden" name="' . escape($name) . '" id="' . escape($field_id) . '" value="' . escape($value) . '" data-icon-value>'
        . '<div class="admin-icon-picker-preview" data-icon-preview>' . icon($value, 'icon') . '</div>'
        . '<span class="admin-icon-picker-name" data-icon-name>' . escape(icon_label($value)) . '</span>'
        . '<button type="button" class="admin-btn admin-btn-outline admin-btn-sm" data-icon-open>İkon Seç</button>'
        . '</div>';
}

function icon_picker_modal() {
    static $rendered = false;
    if ($rendered) {
        return '';
    }
    $rendered = true;
    $catalog = get_icon_catalog();
    $paths = get_icon_paths();
    $html = '<div class="admin-icon-modal" id="admin-icon-modal" hidden>'
        . '<div class="admin-icon-modal-backdrop" data-icon-close></div>'
        . '<div class="admin-icon-modal-panel" role="dialog" aria-modal="true" aria-labelledby="admin-icon-modal-title">'
        . '<div class="admin-icon-modal-header">'
        . '<h2 id="admin-icon-modal-title">İkon Seç</h2>'
        . '<input type="search" class="admin-input admin-icon-search" data-icon-search placeholder="İkon ara...">'
        . '<button type="button" class="admin-icon-modal-close" data-icon-close aria-label="Kapat">' . icon('x', 'icon') . '</button>'
        . '</div>'
        . '<div class="admin-icon-modal-tabs" data-icon-tabs>';
    $first = true;
    foreach (array_keys($catalog) as $cat) {
        $html .= '<button type="button" class="admin-icon-tab' . ($first ? ' is-active' : '') . '" data-icon-category="' . escape($cat) . '">' . escape($cat) . '</button>';
        $first = false;
    }
    $html .= '<button type="button" class="admin-icon-tab" data-icon-category="__all">Tümü</button>';
    $html .= '</div><div class="admin-icon-modal-body" data-icon-grid>';
    foreach ($catalog as $category => $slugs) {
        $html .= '<div class="admin-icon-section" data-icon-section="' . escape($category) . '">';
        $html .= '<div class="admin-icon-section-title">' . escape($category) . '</div>';
        $html .= '<div class="admin-icon-grid">';
        foreach ($slugs as $slug) {
            if (!isset($paths[$slug])) {
                continue;
            }
            $label = icon_label($slug);
            $html .= '<button type="button" class="admin-icon-option" data-icon-option="' . escape($slug) . '" data-icon-label="' . escape($label) . '" data-icon-category="' . escape($category) . '" title="' . escape($label) . '">'
                . icon($slug, 'icon')
                . '<span>' . escape($label) . '</span>'
                . '</button>';
        }
        $html .= '</div></div>';
    }
    $html .= '</div></div></div>';
    return $html;
}

function category_icon($icon) {
    $map = [
        'genel' => 'chat', 'chat' => 'chat',
        'teknoloji' => 'code', 'code' => 'code',
        'yardim' => 'help', 'help' => 'help', 'help-circle' => 'help',
        'duyurular' => 'megaphone', 'megaphone' => 'megaphone',
        'folder' => 'folder',
    ];
    $name = $map[$icon] ?? (preg_match('/^[\x{1F300}-\x{1F9FF}]/u', $icon) ? 'folder' : $icon);
    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        $name = 'folder';
    }
    return icon($name, 'icon icon-lg');
}

function notification_icon_name($type) {
    $map = [
        'mention' => 'mention',
        'reply' => 'reply',
        'upvote' => 'thumbs-up',
        'downvote' => 'thumbs-down',
        'solution_marked' => 'check',
        'follow' => 'user',
        'new_topic' => 'message',
        'new_post' => 'message',
        'admin' => 'alert',
        'system' => 'bell',
        'title_approved' => 'check',
        'title_rejected' => 'x',
    ];
    return $map[$type] ?? 'bell';
}

function notification_type_label($type) {
    $map = [
        'mention' => 'Bahsetme',
        'reply' => 'Yanıt',
        'upvote' => 'Beğeni',
        'downvote' => 'Beğenmeme',
        'solution_marked' => 'Çözüm İşaretlendi',
        'follow' => 'Takip',
        'new_topic' => 'Yeni Konu',
        'new_post' => 'Yeni Yorum',
        'admin' => 'Yönetim',
        'system' => 'Sistem',
        'title_approved' => 'Başlık Onayı',
        'title_rejected' => 'Başlık Reddi',
    ];
    if (isset($map[$type])) {
        return $map[$type];
    }
    return ucwords(str_replace('_', ' ', $type));
}
