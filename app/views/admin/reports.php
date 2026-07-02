<div class="admin-toolbar">
    <div class="admin-tabs admin-tabs-inline">
        <a href="<?php echo url('/admin/raporlar?status=pending'); ?>" class="admin-tab<?php echo $status === 'pending' ? ' active' : ''; ?>">Bekleyen</a>
        <a href="<?php echo url('/admin/raporlar?status=resolved'); ?>" class="admin-tab<?php echo $status === 'resolved' ? ' active' : ''; ?>">Çözülen</a>
        <a href="<?php echo url('/admin/raporlar?status=rejected'); ?>" class="admin-tab<?php echo $status === 'rejected' ? ' active' : ''; ?>">Reddedilen</a>
        <a href="<?php echo url('/admin/raporlar?status=all'); ?>" class="admin-tab<?php echo $status === 'all' ? ' active' : ''; ?>">Tümü</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Raporlayan</th>
                    <th>Hedef</th>
                    <th>Sebep</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="7" class="admin-empty">Rapor bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo (int)$report['id']; ?></td>
                            <td><?php echo escape($report['reporter_name']); ?></td>
                            <td>
                                <?php if ($report['reported_username']): ?>
                                    <?php echo escape($report['reported_username']); ?>
                                <?php elseif ($report['post_content']): ?>
                                    <?php echo escape(truncate(strip_tags($report['post_content']), 40)); ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo escape(truncate($report['reason'], 80)); ?></td>
                            <td><span class="admin-badge-status admin-badge-<?php echo escape($report['status']); ?>"><?php echo escape($report['status']); ?></span></td>
                            <td><?php echo format_date($report['created_at']); ?></td>
                            <td class="admin-actions">
                                <?php if ($report['status'] === 'pending'): ?>
                                    <form method="post" action="<?php echo url('/admin/raporlar'); ?>" class="admin-inline-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$report['id']; ?>">
                                        <button type="submit" name="action" value="resolve" class="admin-btn admin-btn-sm admin-btn-success">Çöz</button>
                                        <button type="submit" name="action" value="reject" class="admin-btn admin-btn-sm admin-btn-danger">Reddet</button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
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
        <a href="<?php echo url('/admin/raporlar?status=' . urlencode($status) . '&page=' . ($pagination['current_page'] - 1)); ?>" class="admin-btn admin-btn-outline">← Önceki</a>
    <?php endif; ?>
    <span class="admin-pagination-info">Sayfa <?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
    <?php if ($pagination['has_next']): ?>
        <a href="<?php echo url('/admin/raporlar?status=' . urlencode($status) . '&page=' . ($pagination['current_page'] + 1)); ?>" class="admin-btn admin-btn-outline">Sonraki →</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
