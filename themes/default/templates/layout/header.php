<?php
$site_name = theme_shell_site_name();
$current_user = current_user();
theme_shell_head_open($title ?? null, 'https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap');
?>
<body class="<?php echo escape(get_theme_body_class()); ?>">
<?php echo execute_hooks('layout_banner', ''); ?>
<header class="site-header">
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo url('/'); ?>" class="navbar-brand">
                <?php echo escape($site_name); ?>
            </a>
            <button type="button" class="navbar-toggle" id="navbar-toggle" aria-label="Menü">
                <?php echo icon('menu', 'icon'); ?>
            </button>
            <ul class="navbar-menu" id="navbar-menu">
                <li><a href="<?php echo url('/'); ?>"><?php echo icon('home', 'icon icon-sm'); ?> <span>Ana Sayfa</span></a></li>
                <li><a href="<?php echo url('/kategoriler'); ?>"><?php echo icon('folder', 'icon icon-sm'); ?> <span>Forumlar</span></a></li>
                <li><a href="<?php echo url('/konular'); ?>"><?php echo icon('message', 'icon icon-sm'); ?> <span>Konular</span></a></li>
                <?php if ($current_user && is_moderator()): ?>
                    <li><a href="<?php echo url('/mod'); ?>"><?php echo icon('shield', 'icon icon-sm'); ?> <span>Mod Paneli</span></a></li>
                <?php endif; ?>
                <?php if ($current_user && is_admin()): ?>
                    <li><a href="<?php echo url('/admin'); ?>"><?php echo icon('settings', 'icon icon-sm'); ?> <span>Forum Yönetimi</span></a></li>
                <?php endif; ?>
            </ul>
            <div class="navbar-actions">
                <?php theme_shell_user_toolbar(false); ?>
            </div>
        </div>
    </nav>
</header>
<div class="container">
    <?php echo display_flash(); ?>
