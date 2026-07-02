<div class="page-header">
    <h1>Oyladığım Gönderiler</h1>
</div>

<div class="filter-tabs">
    <a href="<?php echo url('/vote/user_votes?type=all'); ?>"
       class="filter-tab <?php echo ($vote_type ?? 'all') === 'all' ? 'active' : ''; ?>">Tümü</a>
    <a href="<?php echo url('/vote/user_votes?type=up'); ?>"
       class="filter-tab <?php echo ($vote_type ?? '') === 'up' ? 'active' : ''; ?> btn-with-icon">
        <?php echo icon('thumbs-up', 'icon icon-sm'); ?> Beğeniler
    </a>
    <a href="<?php echo url('/vote/user_votes?type=down'); ?>"
       class="filter-tab <?php echo ($vote_type ?? '') === 'down' ? 'active' : ''; ?> btn-with-icon">
        <?php echo icon('thumbs-down', 'icon icon-sm'); ?> Beğenmemeler
    </a>
</div>

<?php if (!empty($votes)): ?>
    <div class="topic-list">
        <?php foreach ($votes as $vote): ?>
            <div class="vote-list-item">
                <div class="vote-list-header">
                    <div class="topic-card-title">
                        <a href="<?php echo url('/konu/' . $vote['topic_slug']); ?>">
                            <?php echo escape($vote['topic_title']); ?>
                        </a>
                    </div>
                    <span class="badge <?php echo $vote['vote_type'] === 'up' ? 'badge-up' : 'badge-down'; ?> badge-with-icon">
                        <?php echo $vote['vote_type'] === 'up' ? icon('thumbs-up', 'icon icon-sm') . ' Beğeni' : icon('thumbs-down', 'icon icon-sm') . ' Beğenmeme'; ?>
                    </span>
                </div>

                <div class="vote-list-content">
                    <?php echo escape(truncate(strip_tags($vote['content']), 200)); ?>
                </div>

                <div class="vote-list-meta">
                    <strong><?php echo escape($vote['username']); ?></strong> tarafından
                    <?php echo time_ago($vote['post_date']); ?>
                    &bull; Oy verildi: <?php echo time_ago($vote['created_at']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_previous']): ?>
                <a href="<?php echo url('/vote/user_votes?type=' . urlencode($vote_type ?? 'all') . '&page=' . ($pagination['current_page'] - 1)); ?>">
                    Önceki
                </a>
            <?php endif; ?>

            <span class="active"><?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>

            <?php if ($pagination['has_next']): ?>
                <a href="<?php echo url('/vote/user_votes?type=' . urlencode($vote_type ?? 'all') . '&page=' . ($pagination['current_page'] + 1)); ?>">
                    Sonraki
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <h2>Henüz oy vermediniz</h2>
        <p>Beğendiğiniz veya beğenmediğiniz gönderiler burada listelenecek.</p>
        <a href="<?php echo url('/konular'); ?>" class="btn btn-primary mt-20">Konulara Göz At</a>
    </div>
<?php endif; ?>
