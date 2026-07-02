<div class="admin-card">
    <div class="admin-card-header">
        <h2>Stil Özellikleri</h2>
        <a href="<?php echo url('/admin/temalar/sablon'); ?>" class="admin-btn admin-btn-sm admin-btn-outline">Şablon Editörü</a>
    </div>
    <div class="admin-card-body">
        <form method="post">
            <?php echo csrf_field(); ?>
            <?php foreach ($style_properties as $var => $field): ?>
                <div class="admin-form-group">
                    <label><?php echo escape($field['label'] ?? $var); ?></label>
                    <input type="<?php echo ($field['type'] ?? '') === 'color' ? 'color' : 'text'; ?>"
                           name="style_props[<?php echo escape($var); ?>]"
                           class="admin-input"
                           value="<?php echo escape($props[$var] ?? $field['default'] ?? ''); ?>">
                </div>
            <?php endforeach; ?>
            <div class="admin-form-group">
                <label>Özel CSS</label>
                <textarea name="custom_css" class="admin-code-editor" rows="12"><?php echo escape($custom_css); ?></textarea>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
        </form>
    </div>
</div>
