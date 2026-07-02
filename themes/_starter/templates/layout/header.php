<?php
$site_name = theme_shell_site_name();
$current_user = current_user();
theme_shell_head_open($title ?? null);
?>
<body class="<?php echo escape(get_theme_body_class()); ?>">
<?php echo execute_hooks('layout_banner', ''); ?>
<header class="str-header">
    <div class="str-header-inner">
        <a href="<?php echo url('/'); ?>" class="str-logo"><?php echo escape($site_name); ?></a>
        <nav class="str-nav">
            <a href="<?php echo url('/'); ?>">Ana Sayfa</a>
            <a href="<?php echo url('/kategoriler'); ?>">Forumlar</a>
            <a href="<?php echo url('/konular'); ?>">Konular</a>
            <?php if ($current_user && is_moderator()): ?>
                <a href="<?php echo url('/mod'); ?>">Mod</a>
            <?php endif; ?>
            <?php if ($current_user && is_admin()): ?>
                <a href="<?php echo url('/admin'); ?>">Yönetim</a>
            <?php endif; ?>
        </nav>
        <div class="str-header-actions">
            <?php theme_shell_user_toolbar(false); ?>
        </div>
    </div>
</header>
<main class="str-main">
    <?php echo display_flash(); ?>
