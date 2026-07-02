<div class="admin-stats-grid">
    <div class="admin-stat-card<?php echo $stats['reports_pending'] > 0 ? ' admin-stat-warning' : ''; ?>">
        <div class="admin-stat-value"><?php echo (int)$stats['reports_pending']; ?></div>
        <div class="admin-stat-label">Bekleyen Rapor</div>
    </div>
    <div class="admin-stat-card<?php echo $stats['approvals_pending'] > 0 ? ' admin-stat-warning' : ''; ?>">
        <div class="admin-stat-value"><?php echo (int)$stats['approvals_pending']; ?></div>
        <div class="admin-stat-label">Bekleyen Onay</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-value"><?php echo (int)$stats['topics_today']; ?></div>
        <div class="admin-stat-label">Bugünkü Konu</div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header"><h2>Son Mod İşlemleri</h2></div>
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Moderatör</th><th>İşlem</th><th>Hedef</th><th>Tarih</th></tr>
            </thead>
            <tbody>
                <?php if (empty($recent_logs)): ?>
                    <tr><td colspan="4" class="admin-empty">Henüz kayıt yok</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td><?php echo escape($log['moderator_name']); ?></td>
                            <td><?php echo escape($log['action']); ?></td>
                            <td><?php echo escape($log['target_type']); ?> #<?php echo (int)$log['target_id']; ?></td>
                            <td><?php echo time_ago($log['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
