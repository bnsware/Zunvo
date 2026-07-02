<p class="admin-toolbar-meta">Taranan eklenti: <strong><?php echo (int)$scanned_count; ?></strong></p>

<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ad</th>
                    <th>Slug</th>
                    <th>Versiyon</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($plugins)): ?>
                    <tr><td colspan="5" class="admin-empty">Plugin bulunamadı. <code>plugins/</code> klasörüne plugin ekleyin.</td></tr>
                <?php else: ?>
                    <?php foreach ($plugins as $plugin): ?>
                        <tr>
                            <td><?php echo escape($plugin['name']); ?></td>
                            <td><code><?php echo escape($plugin['slug']); ?></code></td>
                            <td><?php echo escape($plugin['version']); ?></td>
                            <td>
                                <?php if ($plugin['is_active']): ?>
                                    <span class="admin-badge-status admin-badge-active">Aktif</span>
                                <?php else: ?>
                                    <span class="admin-badge-status">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-actions">
                                <form method="post" action="<?php echo url('/admin/pluginler'); ?>" class="admin-inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="slug" value="<?php echo escape($plugin['slug']); ?>">
                                    <?php if ($plugin['is_active']): ?>
                                        <button type="submit" name="action" value="deactivate" class="admin-btn admin-btn-sm admin-btn-outline">Devre Dışı</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="activate" class="admin-btn admin-btn-sm admin-btn-primary">Etkinleştir</button>
                                    <?php endif; ?>
                                </form>
                                <a href="<?php echo url('/admin/pluginler/' . $plugin['slug'] . '/ayarlar'); ?>" class="admin-btn admin-btn-sm admin-btn-outline">Ayarlar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
