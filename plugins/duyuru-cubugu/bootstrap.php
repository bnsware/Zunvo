<?php

register_hook('layout_banner', function($html) {
    $settings = get_plugin_settings('duyuru-cubugu');
    if (empty($settings['enabled'])) {
        return $html;
    }
    $message = trim($settings['message'] ?? '');
    if ($message === '') {
        return $html;
    }
    $bg = duyuru_cubugu_color($settings['bg_color'] ?? '#2563eb');
    $color = duyuru_cubugu_color($settings['text_color'] ?? '#ffffff');
    $link = trim($settings['link_url'] ?? '');
    $html .= '<div class="maintenance-banner" style="background:' . escape($bg) . ';color:' . escape($color) . ';">';
    $html .= '<div class="maintenance-banner-inner">';
    if ($link !== '') {
        $html .= '<a href="' . escape($link) . '" style="color:inherit;">' . escape($message) . '</a>';
    } else {
        $html .= escape($message);
    }
    $html .= '</div></div>';
    return $html;
});

function duyuru_cubugu_color($color) {
    $color = trim($color);
    if (preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
        return $color;
    }
    return '#2563eb';
}
