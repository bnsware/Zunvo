<form method="get" action="<?php echo url('/admin/kullanicilar'); ?>" class="admin-toolbar">
    <div class="admin-toolbar-search">
        <input type="text" name="q" value="<?php echo escape($search); ?>" placeholder="Kullanıcı adı veya e-posta..." class="admin-input">
        <button type="submit" class="admin-btn admin-btn-primary">Ara</button>
        <?php if ($search): ?>
            <a href="<?php echo url('/admin/kullanicilar'); ?>" class="admin-btn admin-btn-outline">Temizle</a>
        <?php endif; ?>
    </div>
</form>

<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>Repütasyon</th>
                    <th>Durum</th>
                    <th>Kayıt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="admin-empty">Kullanıcı bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo (int)$u['id']; ?></td>
                            <td><?php echo escape($u['username']); ?></td>
                            <td><?php echo escape($u['email']); ?></td>
                            <td><span class="admin-badge-status"><?php echo escape($u['role']); ?></span></td>
                            <td><?php echo (int)$u['reputation']; ?></td>
                            <td>
                                <?php if ($u['is_banned']): ?>
                                    <span class="admin-badge-status admin-badge-banned">Yasaklı</span>
                                <?php else: ?>
                                    <span class="admin-badge-status admin-badge-active">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo format_date($u['created_at']); ?></td>
                            <td><a href="<?php echo url('/admin/kullanici/' . $u['id']); ?>" class="admin-btn admin-btn-sm">Düzenle</a></td>
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
        <a href="<?php echo url('/admin/kullanicilar?page=' . ($pagination['current_page'] - 1) . ($search ? '&q=' . urlencode($search) : '')); ?>" class="admin-btn admin-btn-outline">← Önceki</a>
    <?php endif; ?>
    <span class="admin-pagination-info">Sayfa <?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
    <?php if ($pagination['has_next']): ?>
        <a href="<?php echo url('/admin/kullanicilar?page=' . ($pagination['current_page'] + 1) . ($search ? '&q=' . urlencode($search) : '')); ?>" class="admin-btn admin-btn-outline">Sonraki →</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
