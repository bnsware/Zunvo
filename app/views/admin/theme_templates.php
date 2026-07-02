<div class="admin-theme-editor">
    <div class="admin-theme-sidebar">
        <h3>Şablonlar</h3>
        <ul>
            <?php foreach ($templates as $tpl): ?>
                <li><a href="<?php echo url('/admin/temalar/sablon?key=' . urlencode($tpl)); ?>" class="<?php echo $selected === $tpl ? 'active' : ''; ?>"><?php echo escape($tpl); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <a href="<?php echo url('/admin/temalar/stil'); ?>" class="admin-btn admin-btn-sm admin-btn-outline">Stil Editörü</a>
    </div>
    <div class="admin-theme-editor-main">
        <form method="post">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="template_key" value="<?php echo escape($selected); ?>">
            <textarea name="content" class="admin-code-editor" rows="24"><?php echo escape($content); ?></textarea>
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
                <button type="submit" name="action" value="reset" class="admin-btn admin-btn-outline">Orijinale Dön</button>
            </div>
        </form>
    </div>
</div>
