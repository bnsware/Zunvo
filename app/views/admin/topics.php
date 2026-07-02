<form method="get" action="<?php echo url('/admin/konular'); ?>" class="admin-toolbar">
    <div class="admin-toolbar-search">
        <input type="text" name="q" value="<?php echo escape($search); ?>" placeholder="Konu başlığı ara..." class="admin-input">
        <button type="submit" class="admin-btn admin-btn-primary">Ara</button>
        <?php if ($search): ?>
            <a href="<?php echo url('/admin/konular'); ?>" class="admin-btn admin-btn-outline">Temizle</a>
        <?php endif; ?>
    </div>
</form>

<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Kategori</th>
                    <th>Yazar</th>
                    <th>Mesaj</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topics)): ?>
                    <tr><td colspan="7" class="admin-empty">Konu bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($topics as $topic): ?>
                        <tr>
                            <td>
                                <a href="<?php echo url('/konu/' . $topic['slug']); ?>" target="_blank"><?php echo escape(truncate($topic['title'], 50)); ?></a>
                            </td>
                            <td><?php echo escape($topic['category_name']); ?></td>
                            <td><?php echo escape($topic['username']); ?></td>
                            <td><?php echo (int)$topic['post_count']; ?></td>
                            <td>
                                <?php if ($topic['is_pinned']): ?><span class="admin-badge-status badge-with-icon"><?php echo icon('pin', 'icon icon-sm'); ?> Sabit</span><?php endif; ?>
                                <?php if ($topic['is_locked']): ?><span class="admin-badge-status admin-badge-banned badge-with-icon"><?php echo icon('lock', 'icon icon-sm'); ?> Kilitli</span><?php endif; ?>
                            </td>
                            <td><?php echo format_date($topic['created_at']); ?></td>
                            <td class="admin-actions">
                                <form method="post" action="<?php echo url('/admin/konular'); ?>" class="admin-inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$topic['id']; ?>">
                                    <?php if ($topic['is_pinned']): ?>
                                        <button type="submit" name="action" value="unpin" class="admin-btn admin-btn-sm admin-btn-outline">Sabiti Kaldır</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="pin" class="admin-btn admin-btn-sm admin-btn-outline">Sabitle</button>
                                    <?php endif; ?>
                                    <?php if ($topic['is_locked']): ?>
                                        <button type="submit" name="action" value="unlock" class="admin-btn admin-btn-sm admin-btn-outline">Kilidi Aç</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="lock" class="admin-btn admin-btn-sm admin-btn-outline">Kilitle</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="delete" class="admin-btn admin-btn-sm admin-btn-danger" data-confirm="Bu konuyu silmek istediğinize emin misiniz?">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
<nav class="admin-pagination">
    <?php if ($pagination['has_previous']): ?>
        <a href="<?php echo url('/admin/konular?page=' . ($pagination['current_page'] - 1) . ($search ? '&q=' . urlencode($search) : '')); ?>" class="admin-btn admin-btn-outline">← Önceki</a>
    <?php endif; ?>
    <span class="admin-pagination-info">Sayfa <?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
    <?php if ($pagination['has_next']): ?>
        <a href="<?php echo url('/admin/konular?page=' . ($pagination['current_page'] + 1) . ($search ? '&q=' . urlencode($search) : '')); ?>" class="admin-btn admin-btn-outline">Sonraki →</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
