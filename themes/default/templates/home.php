<div class="home-page">
    <section class="hero">
        <h1><?php echo escape(theme_shell_site_name()); ?>'a Hoş Geldiniz</h1>
        <p>Sorularınızı sorun, deneyimlerinizi paylaşın ve toplulukla etkileşime geçin</p>
        <?php if (!is_logged_in()): ?>
            <a href="<?php echo url('/kayit'); ?>" class="btn btn-primary btn-hero">Hemen Katıl</a>
        <?php endif; ?>
    </section>

    <section class="stat-grid home-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo format_number($stats['total_topics']); ?></div>
            <div class="stat-label">Toplam Konu</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo format_number($stats['total_posts']); ?></div>
            <div class="stat-label">Toplam Gönderi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo format_number($stats['total_users']); ?></div>
            <div class="stat-label">Toplam Üye</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php echo $stats['newest_user'] ? escape($stats['newest_user']['username']) : '-'; ?>
            </div>
            <div class="stat-label">En Yeni Üye</div>
        </div>
    </section>

    <?php if (!empty($widget_enabled)): ?>
        <?php theme_partial('home/widget_activity'); ?>
    <?php endif; ?>

    <div class="home-layout">
        <div class="home-main">
            <div class="section-head">
                <h2><?php echo icon('folder', 'icon icon-sm'); ?> Forum</h2>
                <a href="<?php echo url('/kategoriler'); ?>" class="btn btn-ghost btn-sm">Tümünü Gör</a>
            </div>

            <?php theme_partial('partials/forum_tree_table'); ?>
        </div>

        <?php
        $plugin_sidebar = execute_hooks('home_sidebar', '');
        $has_sidebar = !empty($plugin_sidebar) || !empty($hot_topics) || !empty($trend_tags);
        ?>
        <?php if ($has_sidebar): ?>
        <aside class="home-sidebar">
            <?php if (!empty($plugin_sidebar)): ?>
                <?php echo $plugin_sidebar; ?>
            <?php endif; ?>
            <?php if (!empty($hot_topics)): ?>
            <div class="sidebar-card">
                <h3><?php echo icon('flame', 'icon icon-sm'); ?> Sıcak Konular</h3>
                <div class="hot-topics">
                    <?php foreach ($hot_topics as $ht): ?>
                        <div class="hot-topic-row">
                            <a href="<?php echo url('/konu/' . $ht['slug']); ?>"><?php echo escape($ht['title']); ?></a>
                            <span class="hot-topic-meta"><?php echo escape($ht['username']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($trend_tags)): ?>
            <div class="sidebar-card">
                <div class="trend-tags">
                    <h3>Trend Etiketler</h3>
                    <?php foreach ($trend_tags as $tg): ?>
                        <a href="<?php echo url('/etiket/' . $tg['slug']); ?>" class="tag">#<?php echo escape($tg['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
        <?php endif; ?>
    </div>
</div>
