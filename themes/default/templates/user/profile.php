<?php
$current_user = current_user();
$is_own_profile = $current_user && $current_user['id'] === $profile_user['id'];
?>

<div class="profile-container">
    <aside class="profile-sidebar">
        <img
            src="<?php echo asset('uploads/avatars/' . $profile_user['avatar']); ?>"
            alt="<?php echo escape($profile_user['username']); ?>"
            class="profile-avatar"
            onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
        >

        <h1 class="profile-username"><?php echo escape($profile_user['username']); ?></h1>

        <div class="profile-level"><?php echo escape($level); ?></div>

        <div class="profile-reputation">
            <?php echo icon('star', 'icon icon-sm'); ?>
            <?php echo escape($profile_user['reputation']); ?> Reputasyon
        </div>

        <?php if (!empty($badges)): ?>
        <div class="profile-badges">
            <?php foreach ($badges as $badge): ?>
                <span class="badge badge-with-icon" title="<?php echo escape(get_badge_label($badge['badge_slug'])); ?>">
                    <?php echo icon('badge', 'icon icon-sm'); ?>
                    <?php echo escape(get_badge_label($badge['badge_slug'])); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($profile_user['biography'])): ?>
            <div class="profile-bio">
                <?php echo nl2br(escape($profile_user['biography'])); ?>
            </div>
        <?php endif; ?>

        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['topics']; ?></div>
                <div class="stat-label">Konu</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['posts']; ?></div>
                <div class="stat-label">Gönderi</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['solutions']; ?></div>
                <div class="stat-label">Çözüm</div>
            </div>
        </div>

        <div class="profile-meta">
            <div><?php echo icon('calendar', 'icon icon-sm'); ?> Üyelik: <?php echo format_date($profile_user['created_at'], 'd.m.Y'); ?></div>
            <?php if ($profile_user['last_active']): ?>
                <div><?php echo icon('clock', 'icon icon-sm'); ?> Son Görülme: <?php echo time_ago($profile_user['last_active']); ?></div>
            <?php endif; ?>
        </div>

        <?php if ($is_own_profile): ?>
            <a href="<?php echo url('/profil-duzenle'); ?>" class="btn btn-primary btn-block mt-20 btn-with-icon">
                <?php echo icon('edit', 'icon icon-sm'); ?> Profili Düzenle
            </a>
        <?php endif; ?>
    </aside>

    <main class="profile-content">
        <div class="content-tabs">
            <button class="tab-button active" type="button">Son Konular</button>
            <button class="tab-button" type="button">Son Gönderiler</button>
            <button class="tab-button" type="button">Çözümler</button>
        </div>

        <div class="tab-content">
            <?php if (empty($recent_topics)): ?>
                <p class="profile-empty">Henüz konu açılmamış.</p>
            <?php else: ?>
                <?php foreach ($recent_topics as $topic): ?>
                    <div class="profile-topic-item">
                        <a href="<?php echo url('/konu/' . $topic['slug']); ?>" class="topic-item-title">
                            <?php echo escape($topic['title']); ?>
                        </a>
                        <div class="topic-item-meta">
                            <span class="meta-inline"><?php echo icon('eye', 'icon icon-sm'); ?> <?php echo $topic['views']; ?> görüntülenme</span>
                            &bull;
                            <span class="meta-inline"><?php echo icon('clock', 'icon icon-sm'); ?> <?php echo time_ago($topic['created_at']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
