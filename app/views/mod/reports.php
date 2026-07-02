<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Raporlayan</th><th>Sebep</th><th>Durum</th><th>Tarih</th><th>İşlem</th></tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="5" class="admin-empty">Rapor yok</td></tr>
                <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo escape($report['reporter_name']); ?></td>
                            <td><?php echo escape(mb_strimwidth($report['reason'], 0, 80, '...')); ?></td>
                            <td><?php echo escape($report['status']); ?></td>
                            <td><?php echo time_ago($report['created_at']); ?></td>
                            <td>
                                <?php if ($report['status'] === 'pending'): ?>
                                <form method="post" class="admin-inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="report_id" value="<?php echo (int)$report['id']; ?>">
                                    <button type="submit" name="action" value="resolved" class="admin-btn admin-btn-sm admin-btn-primary">Çöz</button>
                                    <button type="submit" name="action" value="rejected" class="admin-btn admin-btn-sm admin-btn-outline">Reddet</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
