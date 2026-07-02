<div class="page-header">
    <h1>Arama Sonuçları</h1>
</div>

<form method="GET" action="<?php echo url('/arama'); ?>" class="search-box">
    <input
        type="text"
        name="q"
        class="search-input"
        placeholder="Konu ara..."
        value="<?php echo escape($query); ?>"
    >
    <button type="submit" class="btn btn-primary btn-with-icon">
        <?php echo icon('search', 'icon icon-sm'); ?> Ara
    </button>
</form>

<p class="search-results-summary">
    "<strong><?php echo escape($query); ?></strong>" için arama sonuçları
    <?php if (!empty($topics)): ?>
        (<?php echo $pagination['total_items'] ?? count($topics); ?> sonuç)
    <?php endif; ?>
</p>

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

                    <div class="topic-badges">
                        <span class="badge badge-category">
                            <?php echo escape($topic['category_name']); ?>
                        </span>
                    </div>

                    <div class="topic-meta">
                        <strong><?php echo escape($topic['username']); ?></strong> tarafından
                        <?php echo time_ago($topic['created_at']); ?>
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
                <a href="<?php echo url('/arama?q=' . urlencode($query) . '&page=' . ($pagination['current_page'] - 1)); ?>">
                    Önceki
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= min($pagination['total_pages'], 5); $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="<?php echo url('/arama?q=' . urlencode($query) . '&page=' . $i); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagination['has_next']): ?>
                <a href="<?php echo url('/arama?q=' . urlencode($query) . '&page=' . ($pagination['current_page'] + 1)); ?>">
                    Sonraki
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <h2>Sonuç Bulunamadı</h2>
        <p>"<?php echo escape($query); ?>" için eşleşen konu bulunamadı.</p>
        <a href="<?php echo url('/konular'); ?>" class="btn btn-outline mt-20">Tüm Konulara Git</a>
    </div>
<?php endif; ?>
