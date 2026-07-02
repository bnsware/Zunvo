<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Konu</th><th>Eski Başlık</th><th>Yeni Başlık</th><th>İsteyen</th><th>İşlem</th></tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="5" class="admin-empty">Bekleyen onay yok</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><a href="<?php echo url('/konu/' . $req['slug']); ?>"><?php echo escape($req['current_title']); ?></a></td>
                            <td><?php echo escape($req['old_value']); ?></td>
                            <td><?php echo escape($req['new_value']); ?></td>
                            <td><?php echo escape($req['requester_name']); ?></td>
                            <td>
                                <form method="post" class="admin-inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="request_id" value="<?php echo (int)$req['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="admin-btn admin-btn-sm admin-btn-primary">Onayla</button>
                                    <button type="submit" name="action" value="reject" class="admin-btn admin-btn-sm admin-btn-outline">Reddet</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
