<?php
$site_name = get_setting('site_name', SITE_NAME);
$admin_page = $admin_page ?? '';
$nav_counts = admin_nav_counts();
$page_lead = admin_page_lead($admin_page);

$nav_primary = [
    'dashboard' => ['label' => 'Genel Bakış', 'url' => url('/admin'), 'icon' => 'layout'],
    'categories' => ['label' => 'Forum Yapısı', 'url' => url('/admin/kategoriler'), 'icon' => 'folder'],
    'topics' => ['label' => 'Konular', 'url' => url('/admin/konular'), 'icon' => 'message'],
    'widget' => ['label' => 'Ana Sayfa', 'url' => url('/admin/widget'), 'icon' => 'grid'],
];

$nav_moderation = [
    'users' => ['label' => 'Kullanıcılar', 'url' => url('/admin/kullanicilar'), 'icon' => 'users'],
    'moderators' => ['label' => 'Moderatörler', 'url' => url('/admin/moderatorler'), 'icon' => 'shield'],
    'reports' => ['label' => 'Raporlar', 'url' => url('/admin/raporlar'), 'icon' => 'alert', 'badge' => $nav_counts['reports']],
    'approvals' => ['label' => 'Onaylar', 'url' => url('/admin/onaylar'), 'icon' => 'check', 'badge' => $nav_counts['approvals']],
    'awards' => ['label' => 'Ödüller', 'url' => url('/admin/oduller'), 'icon' => 'award'],
    'mod_log' => ['label' => 'Mod Log', 'url' => url('/admin/mod-log'), 'icon' => 'shield'],
];

$nav_site = [
    'settings' => ['label' => 'Ayarlar', 'url' => url('/admin/ayarlar'), 'icon' => 'settings'],
    'themes' => ['label' => 'Tema', 'url' => url('/admin/temalar'), 'icon' => 'palette'],
];

$nav_advanced = [
    'plugins' => ['label' => 'Pluginler', 'url' => url('/admin/pluginler'), 'icon' => 'plugin'],
    'api_keys' => ['label' => 'API', 'url' => url('/admin/api'), 'icon' => 'key'],
    'webhooks' => ['label' => 'Webhooks', 'url' => url('/admin/webhooks'), 'icon' => 'link'],
];

$mod_keys = array_keys($nav_moderation);
$site_keys = array_keys($nav_site);
$advanced_keys = array_keys($nav_advanced);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($title ?? 'Admin'); ?> - <?php echo escape($site_name); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    <script>
        window.ZUNVO_CONFIG = {
            baseUrl: <?php echo json_encode(rtrim(SITE_URL, '/')); ?>,
            csrfToken: <?php echo json_encode(generate_csrf_token()); ?>
        };
    </script>
    <script src="<?php echo asset('js/admin.js'); ?>" defer></script>
</head>
<body class="zv-admin">
    <div class="zv-shell">
        <aside class="zv-aside" id="zv-aside" aria-label="Yönetim menüsü">
            <div class="zv-aside-brand">
                <a href="<?php echo url('/admin'); ?>" class="zv-brand-link"><?php echo escape($site_name); ?></a>
                <span class="zv-brand-tag">Admin</span>
            </div>
            <div class="zv-aside-search">
                <input type="search" id="zv-nav-search" class="zv-search-input" placeholder="Menüde ara..." autocomplete="off">
            </div>
            <nav class="zv-nav" id="zv-nav">
                <div class="zv-nav-group" data-nav-group>
                    <?php foreach ($nav_primary as $key => $item): ?>
                        <?php admin_render_nav_link($key, $item, $admin_page); ?>
                    <?php endforeach; ?>
                </div>
                <details class="zv-nav-fold" data-nav-fold<?php echo admin_nav_section_open($admin_page, $mod_keys) ? ' open' : ''; ?>>
                    <summary>Moderasyon</summary>
                    <div class="zv-nav-fold-body">
                        <?php foreach ($nav_moderation as $key => $item): ?>
                            <?php admin_render_nav_link($key, $item, $admin_page); ?>
                        <?php endforeach; ?>
                    </div>
                </details>
                <details class="zv-nav-fold" data-nav-fold<?php echo admin_nav_section_open($admin_page, $site_keys) ? ' open' : ''; ?>>
                    <summary>Site</summary>
                    <div class="zv-nav-fold-body">
                        <?php foreach ($nav_site as $key => $item): ?>
                            <?php admin_render_nav_link($key, $item, $admin_page); ?>
                        <?php endforeach; ?>
                    </div>
                </details>
                <details class="zv-nav-fold zv-nav-fold-dim" data-nav-fold<?php echo admin_nav_section_open($admin_page, $advanced_keys) ? ' open' : ''; ?>>
                    <summary>Gelişmiş</summary>
                    <div class="zv-nav-fold-body">
                        <?php foreach ($nav_advanced as $key => $item): ?>
                            <?php admin_render_nav_link($key, $item, $admin_page); ?>
                        <?php endforeach; ?>
                    </div>
                </details>
            </nav>
            <footer class="zv-aside-foot">
                <a href="<?php echo url('/'); ?>" class="zv-nav-item" data-nav-label="siteye dön">
                    <span class="zv-nav-icon"><?php echo icon('home', 'icon'); ?></span>
                    <span class="zv-nav-label">Siteye Dön</span>
                </a>
            </footer>
        </aside>
        <div class="zv-backdrop" id="zv-backdrop" hidden></div>
        <div class="zv-workspace">
            <header class="zv-topbar">
                <button type="button" class="zv-menu-btn" id="zv-menu-btn" aria-label="Menüyü aç"><?php echo icon('menu', 'icon'); ?></button>
                <div class="zv-topbar-head">
                    <h1 class="zv-page-title"><?php echo escape($title ?? 'Yönetim Paneli'); ?></h1>
                    <?php if ($page_lead !== ''): ?>
                        <p class="zv-page-lead"><?php echo escape($page_lead); ?></p>
                    <?php endif; ?>
                </div>
                <?php $cu = current_user(); ?>
                <?php if ($cu): ?>
                    <div class="zv-topbar-user"><?php echo escape($cu['username']); ?></div>
                <?php endif; ?>
            </header>
            <main class="zv-main">
                <?php echo display_flash(); ?>
                <div class="zv-main-inner">
                    <?php echo $content; ?>
                </div>
            </main>
        </div>
    </div>
    <?php echo icon_picker_modal(); ?>
</body>
</html>
