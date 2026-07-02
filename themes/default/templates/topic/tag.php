<?php
$tag = $tag ?? [];
$topics = $topics ?? [];
$pagination = $pagination ?? [];
?>
<div class="page-header">
    <h1>#<?php echo escape($tag['name'] ?? ''); ?></h1>
    <p class="text-muted"><?php echo (int)($tag['usage_count'] ?? 0); ?> kullanım</p>
</div>
<?php if (empty($topics)): ?>
    <div class="empty-state">
        <p>Bu etikette konu bulunamadı.</p>
    </div>
<?php else: ?>
    <?php foreach ($topics as $topic): ?>
        <div class="topic-card">
            <div class="topic-card-title">
                <a href="<?php echo url('/konu/' . $topic['slug']); ?>"><?php echo escape($topic['title']); ?></a>
            </div>
            <span class="topic-card-meta"><?php echo escape($topic['username']); ?> &middot; <?php echo escape($topic['category_name']); ?></span>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
