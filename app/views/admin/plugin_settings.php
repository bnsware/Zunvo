<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2><?php echo escape($plugin['name']); ?> Ayarları</h2>
            <?php if ($plugin['is_active']): ?>
                <span class="admin-badge-status admin-badge-active">Aktif</span>
            <?php else: ?>
                <span class="admin-badge-status">Pasif</span>
            <?php endif; ?>
        </div>
        <div class="admin-actions">
            <form method="post" class="admin-inline-form">
                <?php echo csrf_field(); ?>
                <?php if ($plugin['is_active']): ?>
                    <button type="submit" name="action" value="deactivate" class="admin-btn admin-btn-sm admin-btn-outline">Devre Dışı</button>
                <?php else: ?>
                    <button type="submit" name="action" value="activate" class="admin-btn admin-btn-sm admin-btn-primary">Etkinleştir</button>
                <?php endif; ?>
            </form>
            <a href="<?php echo url('/admin/pluginler'); ?>" class="admin-btn admin-btn-sm admin-btn-outline">Geri</a>
        </div>
    </div>
    <div class="admin-card-body">
        <form method="post">
            <?php echo csrf_field(); ?>
            <?php if (!empty($meta['settings'])): ?>
                <?php foreach ($meta['settings'] as $key => $field): ?>
                    <div class="admin-form-group">
                        <?php if (($field['type'] ?? 'text') === 'info'): ?>
                            <label><?php echo escape($field['label'] ?? $key); ?></label>
                            <p class="admin-plugin-info"><?php echo nl2br(escape($field['default'] ?? '')); ?></p>
                        <?php else: ?>
                            <label><?php echo escape($field['label'] ?? $key); ?></label>
                            <?php if (($field['type'] ?? 'text') === 'toggle'): ?>
                                <?php
                                $toggle_checked = $key === 'enabled'
                                    ? !empty($plugin['is_active'])
                                    : ($saved[$key] ?? $field['default'] ?? '') === '1';
                                ?>
                                <input type="checkbox" name="setting_<?php echo escape($key); ?>" value="1"
                                    <?php echo $toggle_checked ? 'checked' : ''; ?>>
                            <?php elseif (($field['type'] ?? 'text') === 'textarea'): ?>
                                <textarea name="setting_<?php echo escape($key); ?>" class="admin-input admin-textarea" rows="4"><?php echo escape($saved[$key] ?? $field['default'] ?? ''); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="setting_<?php echo escape($key); ?>" class="admin-input"
                                    value="<?php echo escape($saved[$key] ?? $field['default'] ?? ''); ?>">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="admin-empty">Bu plugin için yapılandırılabilir ayar tanımlanmamış.</p>
            <?php endif; ?>
            <?php if (!empty($meta['settings']) && array_filter($meta['settings'], function ($f) { return ($f['type'] ?? 'text') !== 'info'; })): ?>
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
            <?php endif; ?>
        </form>
    </div>
</div>
