<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header"><h2>Moderatör Seç</h2></div>
        <div class="admin-card-body">
            <ul class="admin-list">
                <?php foreach ($moderators as $mod): ?>
                    <li>
                        <a href="<?php echo url('/admin/moderatorler?user_id=' . $mod['id']); ?>" class="<?php echo $selected_id === (int)$mod['id'] ? 'active' : ''; ?>">
                            <?php echo escape($mod['username']); ?> (<?php echo escape($mod['role'] ?? 'user'); ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php if ($selected_id): ?>
    <div class="admin-card">
        <div class="admin-card-header"><h2>Yetkiler</h2></div>
        <div class="admin-card-body">
            <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo (int)$selected_id; ?>">
                <?php foreach ($permission_keys as $key => $label): ?>
                    <label class="admin-checkbox">
                        <input type="checkbox" name="permissions[]" value="<?php echo escape($key); ?>"
                            <?php echo in_array($key, $selected_perms, true) ? 'checked' : ''; ?>>
                        <?php echo escape($label); ?>
                    </label>
                <?php endforeach; ?>
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
