<?php

function theme_shell_site_name() {
    return get_setting('site_name', SITE_NAME);
}

function theme_shell_head_open($page_title = null, $fonts_url = null) {
    $site_name = theme_shell_site_name();
    $title = $page_title ? escape($page_title) . ' - ' . escape($site_name) : escape($site_name);
    ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo escape(get_setting('site_description', SITE_DESCRIPTION)); ?>">
    <?php if ($fonts_url): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="<?php echo escape($fonts_url); ?>" rel="stylesheet">
    <?php endif; ?>
    <script>
        (function() {
            var stored = localStorage.getItem('zunvo-theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
    <?php foreach (get_theme_stylesheets() as $theme_css): ?>
    <link rel="stylesheet" href="<?php echo $theme_css; ?>">
    <?php endforeach; ?>
    <?php $inline_theme_css = get_theme_inline_styles(); if ($inline_theme_css): ?>
    <style><?php echo $inline_theme_css; ?></style>
    <?php endif; ?>
    <script>
        window.ZUNVO_CONFIG = {
            baseUrl: <?php echo json_encode(rtrim(SITE_URL, '/')); ?>,
            basePath: <?php echo json_encode(BASE_PATH); ?>,
            csrfToken: <?php echo json_encode(generate_csrf_token()); ?>
        };
    </script>
    <script src="<?php echo asset('js/ajax.js'); ?>" defer></script>
    <script src="<?php echo asset('js/main.js'); ?>" defer></script>
    <script src="<?php echo asset('js/editor.js'); ?>" defer></script>
</head>
    <?php
}

function theme_shell_theme_toggle() {
    ?>
    <button type="button" id="theme-toggle" class="btn btn-icon nav-toolbar-theme" title="Tema" aria-label="Tema değiştir">
        <span class="theme-icon theme-icon-light"><?php echo icon('sun', 'icon'); ?></span>
        <span class="theme-icon theme-icon-dark"><?php echo icon('moon', 'icon'); ?></span>
    </button>
    <?php
}

function theme_shell_user_toolbar($compact = false) {
    $current_user = current_user();
    ?>
    <div class="nav-toolbar<?php echo $compact ? ' nav-toolbar-compact' : ''; ?>">
        <?php if ($current_user): ?>
        <div class="nav-toolbar-tools">
            <?php if ($compact): ?>
            <a href="<?php echo url('/konu/olustur'); ?>" class="btn btn-icon" title="Konu Aç" aria-label="Konu Aç"><?php echo icon('plus', 'icon'); ?></a>
            <?php else: ?>
            <a href="<?php echo url('/konu/olustur'); ?>" class="btn btn-primary btn-sm btn-with-icon">
                <?php echo icon('plus', 'icon icon-sm'); ?> <span>Konu Aç</span>
            </a>
            <?php endif; ?>
            <div class="notification-bell" id="notification-bell">
                <?php echo icon('bell', 'icon'); ?>
                <span class="notification-badge is-hidden" id="notification-badge">0</span>
                <div class="notification-dropdown" id="notification-dropdown">
                    <div class="notification-header">
                        <h3>Bildirimler</h3>
                        <a href="#" class="mark-all-read" id="mark-all-read">Tümünü okundu işaretle</a>
                    </div>
                    <div class="notification-list" id="notification-list">
                        <div class="notification-empty">Yükleniyor...</div>
                    </div>
                    <div class="notification-footer">
                        <a href="<?php echo url('/bildirimler'); ?>">Tüm bildirimleri gör</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="nav-toolbar-account">
            <button type="button" class="nav-user-trigger" id="nav-user-trigger" aria-expanded="false" aria-haspopup="true" aria-label="Hesap menüsü">
                <img src="<?php echo asset('uploads/avatars/' . $current_user['avatar']); ?>"
                     alt="<?php echo escape($current_user['username']); ?>"
                     class="user-avatar"
                     onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'">
                <?php if (!$compact): ?>
                <span class="nav-user-name"><?php echo escape($current_user['username']); ?></span>
                <?php endif; ?>
            </button>
            <div class="user-menu" id="user-menu">
                <div class="user-menu-header">
                    <div class="user-menu-name"><?php echo escape($current_user['username']); ?></div>
                </div>
                <ul class="user-menu-list">
                    <li><a href="<?php echo url('/profil/' . $current_user['username']); ?>" class="user-menu-link"><?php echo icon('user', 'icon icon-sm'); ?> Profilim</a></li>
                    <li><a href="<?php echo url('/profil-duzenle'); ?>" class="user-menu-link"><?php echo icon('edit', 'icon icon-sm'); ?> Profili Düzenle</a></li>
                    <li><a href="<?php echo url('/bildirim/ayarlar'); ?>" class="user-menu-link"><?php echo icon('bell', 'icon icon-sm'); ?> Bildirim Ayarları</a></li>
                    <li class="user-menu-divider"></li>
                    <li><a href="<?php echo url('/cikis'); ?>" class="user-menu-link danger"><?php echo icon('log-out', 'icon icon-sm'); ?> Çıkış</a></li>
                </ul>
            </div>
        </div>
        <?php theme_shell_theme_toggle(); ?>
        <?php else: ?>
        <div class="nav-toolbar-guest">
            <a href="<?php echo url('/giris'); ?>" class="btn btn-outline btn-sm<?php echo $compact ? ' btn-icon' : ' btn-with-icon'; ?>"<?php echo $compact ? ' title="Giriş" aria-label="Giriş"' : ''; ?>>
                <?php echo icon('log-in', 'icon icon-sm'); ?><?php if (!$compact): ?> <span>Giriş</span><?php endif; ?>
            </a>
            <a href="<?php echo url('/kayit'); ?>" class="btn btn-primary btn-sm">Kayıt Ol</a>
        </div>
        <?php theme_shell_theme_toggle(); ?>
        <?php endif; ?>
    </div>
    <?php
}

function theme_shell_footer_picker() {
    $themes = list_installed_themes();
    if (count($themes) < 2) {
        return;
    }
    $current = get_active_theme_slug();
    $has_user_choice = get_user_theme_slug() !== null;
    ?>
    <div class="footer-theme-picker">
        <label for="footer-theme-select" class="footer-theme-label">
            <?php echo icon('palette', 'icon icon-sm'); ?>
            <span>Tema</span>
        </label>
        <select id="footer-theme-select" class="footer-theme-select" data-base-url="<?php echo escape(url('/tema/sec')); ?>">
            <option value=""<?php echo !$has_user_choice ? ' selected' : ''; ?>>Site varsayılanı</option>
            <?php foreach ($themes as $theme): ?>
            <option value="<?php echo escape($theme['slug']); ?>"<?php echo ($has_user_choice && $current === $theme['slug']) ? ' selected' : ''; ?>><?php echo escape($theme['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

function theme_shell_nav_links() {
    $current_user = current_user();
    $links = [
        ['url' => '/', 'label' => 'Ana Sayfa', 'icon' => 'home'],
        ['url' => '/kategoriler', 'label' => 'Forumlar', 'icon' => 'folder'],
        ['url' => '/konular', 'label' => 'Konular', 'icon' => 'message'],
    ];
    foreach ($links as $link) {
        echo '<a href="' . url($link['url']) . '" class="theme-nav-link">';
        echo icon($link['icon'], 'icon icon-sm') . ' <span>' . escape($link['label']) . '</span></a>';
    }
    if ($current_user && is_moderator()) {
        echo '<a href="' . url('/mod') . '" class="theme-nav-link">' . icon('shield', 'icon icon-sm') . ' <span>Mod</span></a>';
    }
    if ($current_user && is_admin()) {
        echo '<a href="' . url('/admin') . '" class="theme-nav-link">' . icon('settings', 'icon icon-sm') . ' <span>Yönetim</span></a>';
    }
}
