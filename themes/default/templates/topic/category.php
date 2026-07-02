<?php
$breadcrumb = get_category_breadcrumb($category);
$is_section = ($category['forum_type'] ?? 'forum') === 'section';
$shows_topics = category_allows_topics($category);
$can_create = is_logged_in() && user_can_create_topic_in_category($category);
$has_children = !empty($child_forums);
?>
<div class="breadcrumb">
    <a href="<?php echo url('/'); ?>">Ana Sayfa</a>
    <?php foreach ($breadcrumb as $crumb): ?>
        <span>›</span>
        <?php if ($crumb['id'] === $category['id']): ?>
            <span><?php echo escape($crumb['name']); ?></span>
        <?php else: ?>
            <a href="<?php echo url('/kategori/' . $crumb['slug']); ?>"><?php echo escape($crumb['name']); ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="category-header-card">
    <div class="category-header-info">
        <h1><?php echo escape($category['name']); ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p><?php echo escape($category['description']); ?></p>
        <?php endif; ?>
    </div>
    <?php if (!$is_section && !$has_children): ?>
    <div class="category-stats-row">
        <div class="category-stat">
            <span class="stat-number"><?php echo format_number($stats['topics']); ?></span>
            <span class="stat-label">Konu</span>
        </div>
        <div class="category-stat">
            <span class="stat-number"><?php echo format_number($stats['posts']); ?></span>
            <span class="stat-label">Gönderi</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($has_children): ?>
    <div class="forum-table forum-table-compact forum-table-children" style="--section-color: <?php echo escape($category['color'] ?? '#0d9488'); ?>">
        <div class="forum-table-head">
            <span class="forum-col-forum">Alt Forum</span>
            <span class="forum-col-last">Son Mesaj</span>
            <span class="forum-col-stats">Konu / Mesaj</span>
        </div>
        <div class="forum-section-body">
        <?php foreach ($child_forums as $forum): ?>
            <div class="forum-row forum-row-child">
                <div class="forum-col-forum">
                    <div class="forum-row-icon"><?php echo category_icon($forum['icon']); ?></div>
                    <div class="forum-row-info">
                        <a href="<?php echo url('/kategori/' . $forum['slug']); ?>" class="forum-row-title">
                            <?php echo escape($forum['name']); ?>
                        </a>
                        <?php if (!empty($forum['description'])): ?>
                            <p class="forum-row-desc"><?php echo escape($forum['description']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="forum-col-last">
                    <?php if (!empty($forum['last_topic'])): ?>
                        <a href="<?php echo url('/konu/' . $forum['last_topic']['slug']); ?>" class="forum-last-topic">
                            <?php echo escape(mb_strimwidth($forum['last_topic']['title'], 0, 40, '...')); ?>
                        </a>
                        <span class="forum-last-meta">
                            <?php echo escape($forum['last_topic']['username']); ?> &bull;
                            <?php echo time_ago($forum['last_topic']['updated_at']); ?>
                        </span>
                    <?php else: ?>
                        <span class="forum-last-meta">Henüz konu yok</span>
                    <?php endif; ?>
                </div>
                <div class="forum-col-stats">
                    <span><?php echo format_number($forum['topic_count']); ?></span>
                    <span class="forum-stat-sep">/</span>
                    <span><?php echo format_number($forum['post_count']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php elseif ($is_section): ?>
    <div class="empty-state">
        <h2>Bu bölümde alt forum yok</h2>
        <p>Admin panelinden bu bölüme alt forum ekleyebilirsiniz.</p>
    </div>
<?php endif; ?>

<?php if ($shows_topics): ?>

<div class="page-header">
    <h2 class="page-head-subtitle">Konular</h2>
    <?php if ($can_create): ?>
        <a href="<?php echo url('/kategori/' . $category['slug'] . '/yeni-konu'); ?>" class="btn btn-primary btn-with-icon">
            <?php echo icon('plus', 'icon icon-sm'); ?> Yeni Konu
        </a>
    <?php endif; ?>
</div>

<?php if (is_logged_in() && !$can_create && empty($category['can_create_topic'])): ?>
    <div class="alert alert-info category-restricted-notice">
        Bu forumda yalnızca yöneticiler yeni konu açabilir.
    </div>
<?php endif; ?>

<?php if (!empty($topics)): ?>
    <div class="topic-list">
        <?php foreach ($topics as $topic): ?>
            <div class="topic-row">
                <img
                    src="<?php echo asset('uploads/avatars/' . $topic['avatar']); ?>"
                    alt="<?php echo escape($topic['username']); ?>"
                    class="topic-avatar"
                    onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
                >
                <div class="topic-content">
                    <?php if ($topic['is_pinned'] || $topic['is_locked']): ?>
                        <div class="topic-badges">
                            <?php if ($topic['is_pinned']): ?>
                                <span class="badge badge-pinned badge-with-icon"><?php echo icon('pin', 'icon icon-sm'); ?> SABİT</span>
                            <?php endif; ?>
                            <?php if ($topic['is_locked']): ?>
                                <span class="badge badge-locked badge-with-icon"><?php echo icon('lock', 'icon icon-sm'); ?> KİLİTLİ</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="topic-title">
                        <a href="<?php echo url('/konu/' . $topic['slug']); ?>">
                            <?php echo escape($topic['title']); ?>
                        </a>
                    </div>
                    <div class="topic-meta">
                        <strong><?php echo escape($topic['username']); ?></strong> tarafından
                        <?php echo time_ago($topic['created_at']); ?>
                        <?php if (!empty($topic['last_poster'])): ?>
                            &bull; Son: <strong><?php echo escape($topic['last_poster']); ?></strong>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="topic-stat">
                    <span class="stat-number"><?php echo $topic['post_count']; ?></span>
                    <span class="stat-label">Yanıt</span>
                </div>
                <div class="topic-stat">
                    <span class="stat-number"><?php echo format_number($topic['views']); ?></span>
                    <span class="stat-label">Görüntülenme</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_previous']): ?>
                <a href="<?php echo url('/kategori/' . $category['slug'] . '?page=' . ($pagination['current_page'] - 1)); ?>">Önceki</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= min($pagination['total_pages'], 5); $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="<?php echo url('/kategori/' . $category['slug'] . '?page=' . $i); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pagination['has_next']): ?>
                <a href="<?php echo url('/kategori/' . $category['slug'] . '?page=' . ($pagination['current_page'] + 1)); ?>">Sonraki</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <h2>Bu kategoride henüz konu yok</h2>
        <?php if ($can_create): ?>
            <p>İlk konuyu siz açarak tartışmayı başlatın.</p>
            <a href="<?php echo url('/kategori/' . $category['slug'] . '/yeni-konu'); ?>" class="btn btn-primary mt-20">Konu Aç</a>
        <?php else: ?>
            <p>Bu forumda henüz paylaşılmış bir konu bulunmuyor.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php endif; ?>
