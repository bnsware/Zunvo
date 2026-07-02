<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Moderatör</th>
                    <th>İşlem</th>
                    <th>Hedef Türü</th>
                    <th>Hedef ID</th>
                    <th>Detay</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="admin-empty">Log kaydı bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo (int)$log['id']; ?></td>
                            <td><?php echo escape($log['moderator_name']); ?></td>
                            <td><span class="admin-badge-status"><?php echo escape($log['action']); ?></span></td>
                            <td><?php echo escape($log['target_type']); ?></td>
                            <td><?php echo $log['target_id'] ? (int)$log['target_id'] : '—'; ?></td>
                            <td><?php echo escape(truncate($log['details'] ?? '', 60)); ?></td>
                            <td><?php echo format_date($log['created_at']); ?></td>
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
        <a href="<?php echo url('/admin/mod-log?page=' . ($pagination['current_page'] - 1)); ?>" class="admin-btn admin-btn-outline">← Önceki</a>
    <?php endif; ?>
    <span class="admin-pagination-info">Sayfa <?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
    <?php if ($pagination['has_next']): ?>
        <a href="<?php echo url('/admin/mod-log?page=' . ($pagination['current_page'] + 1)); ?>" class="admin-btn admin-btn-outline">Sonraki →</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
