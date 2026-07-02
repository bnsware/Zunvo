<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>İşlem</th><th>Hedef</th><th>Detay</th><th>Tarih</th></tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo escape($log['action']); ?></td>
                        <td><?php echo escape($log['target_type']); ?> #<?php echo (int)$log['target_id']; ?></td>
                        <td><?php echo escape($log['details']); ?></td>
                        <td><?php echo time_ago($log['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
