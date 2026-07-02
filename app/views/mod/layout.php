<?php
$site_name = get_setting('site_name', SITE_NAME);
$mod_page = $mod_page ?? '';
$nav_counts = admin_nav_counts();

$nav_items = [
    'dashboard' => ['label' => 'Dashboard', 'url' => url('/mod'), 'icon' => 'layout'],
    'topics' => ['label' => 'Konular', 'url' => url('/mod/konular'), 'icon' => 'message'],
    'reports' => ['label' => 'Raporlar', 'url' => url('/mod/raporlar'), 'icon' => 'alert', 'badge' => $nav_counts['reports']],
    'approvals' => ['label' => 'Onaylar', 'url' => url('/mod/onaylar'), 'icon' => 'check', 'badge' => $nav_counts['approvals']],
    'log' => ['label' => 'Mod Log', 'url' => url('/mod/log'), 'icon' => 'shield'],
];

$mod_leads = [
    'dashboard' => 'Bekleyen işler ve son moderasyon aktivitesi.',
    'topics' => 'Konuları sabitleyin, kilitleyin veya silin.',
    'reports' => 'Kullanıcı raporlarını inceleyin ve sonuçlandırın.',
    'approvals' => 'Başlık değişikliği gibi bekleyen onayları yönetin.',
    'log' => 'Kendi moderasyon geçmişiniz.',
];
$page_lead = $mod_leads[$mod_page] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($title ?? 'Mod'); ?> - <?php echo escape($site_name); ?></title>
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
        <aside class="zv-aside" id="zv-aside" aria-label="Moderasyon menüsü">
            <div class="zv-aside-brand">
                <a href="<?php echo url('/mod'); ?>" class="zv-brand-link"><?php echo escape($site_name); ?></a>
                <span class="zv-brand-tag">Mod</span>
            </div>
            <nav class="zv-nav" id="zv-nav">
                <div class="zv-nav-group">
                    <?php foreach ($nav_items as $key => $item): ?>
                        <?php admin_render_nav_link($key, $item, $mod_page); ?>
                    <?php endforeach; ?>
                </div>
            </nav>
            <footer class="zv-aside-foot">
                <a href="<?php echo url('/'); ?>" class="zv-nav-item" data-nav-label="siteye dön">
                    <span class="zv-nav-icon"><?php echo icon('home', 'icon'); ?></span>
                    <span class="zv-nav-label">Siteye Dön</span>
                </a>
                <?php if (is_admin()): ?>
                <a href="<?php echo url('/admin'); ?>" class="zv-nav-item" data-nav-label="forum yönetimi">
                    <span class="zv-nav-icon"><?php echo icon('settings', 'icon'); ?></span>
                    <span class="zv-nav-label">Forum Yönetimi</span>
                </a>
                <?php endif; ?>
            </footer>
        </aside>
        <div class="zv-backdrop" id="zv-backdrop" hidden></div>
        <div class="zv-workspace">
            <header class="zv-topbar">
                <button type="button" class="zv-menu-btn" id="zv-menu-btn" aria-label="Menüyü aç"><?php echo icon('menu', 'icon'); ?></button>
                <div class="zv-topbar-head">
                    <h1 class="zv-page-title"><?php echo escape($title ?? 'Mod Paneli'); ?></h1>
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
</body>
</html>
