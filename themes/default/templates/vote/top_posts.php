<div class="page-header">
    <h1>En Popüler Gönderiler</h1>
</div>

<div class="filter-tabs">
    <a href="<?php echo url('/vote/top_posts?period=all'); ?>"
       class="filter-tab <?php echo ($period ?? 'all') === 'all' ? 'active' : ''; ?>">Tüm Zamanlar</a>
    <a href="<?php echo url('/vote/top_posts?period=month'); ?>"
       class="filter-tab <?php echo ($period ?? '') === 'month' ? 'active' : ''; ?>">Bu Ay</a>
    <a href="<?php echo url('/vote/top_posts?period=week'); ?>"
       class="filter-tab <?php echo ($period ?? '') === 'week' ? 'active' : ''; ?>">Bu Hafta</a>
</div>

<?php if (!empty($posts)): ?>
    <div class="topic-list">
        <?php foreach ($posts as $index => $post): ?>
            <?php
            $rank = $index + 1;
            $rank_class = $rank <= 3 ? 'top-' . $rank : '';
            $score = ($post['upvotes'] ?? 0) - ($post['downvotes'] ?? 0);
            ?>
            <div class="vote-list-item vote-list-row">
                <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>

                <img
                    src="<?php echo asset('uploads/avatars/' . $post['avatar']); ?>"
                    alt="<?php echo escape($post['username']); ?>"
                    class="topic-avatar"
                    onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
                >

                <div class="vote-list-body">
                    <div class="vote-list-header">
                        <div>
                            <a href="<?php echo url('/konu/' . $post['topic_slug']); ?>" class="topic-card-title">
                                <?php echo escape($post['topic_title']); ?>
                            </a>
                        </div>
                        <span class="vote-list-score vote-score <?php echo $score > 0 ? 'positive' : ($score < 0 ? 'negative' : ''); ?>">
                            <?php echo $score >= 0 ? '+' : ''; ?><?php echo $score; ?>
                        </span>
                    </div>

                    <div class="vote-list-content">
                        <?php echo escape(truncate(strip_tags($post['content']), 200)); ?>
                    </div>

                    <div class="vote-list-meta">
                        <strong><?php echo escape($post['username']); ?></strong>
                        &bull; <?php echo time_ago($post['created_at']); ?>
                        &bull;
                        <span class="vote-meta-stats">
                            <?php echo icon('thumbs-up', 'icon icon-sm'); ?> <?php echo $post['upvotes'] ?? 0; ?>
                            /
                            <?php echo icon('thumbs-down', 'icon icon-sm'); ?> <?php echo $post['downvotes'] ?? 0; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <h2>Henüz popüler gönderi yok</h2>
        <p>Beğenilen gönderiler burada listelenecek.</p>
    </div>
<?php endif; ?>
