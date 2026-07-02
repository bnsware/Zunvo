<?php
function admin_render_category_fields($prefix, array $sections, $cat = null) {
    $id_prefix = $prefix === 'edit' ? 'edit-' : 'add-';
    $icon_val = $cat ? ($cat['icon'] ?? 'folder') : 'folder';
    $color_val = $cat ? ($cat['color'] ?? '#0d9488') : '#0d9488';
    $forum_type = $cat ? ($cat['forum_type'] ?? 'forum') : 'forum';
    $parent_id = $cat ? ($cat['parent_id'] ?? '') : '';
    $can_create = $cat ? !empty($cat['can_create_topic']) : true;
    ?>
    <div class="zv-form-row zv-form-row-2">
        <div class="admin-form-group">
            <label for="<?php echo $id_prefix; ?>name">Ad</label>
            <input type="text" name="name" id="<?php echo $id_prefix; ?>name" class="admin-input" value="<?php echo $cat ? escape($cat['name']) : ''; ?>" required>
        </div>
        <div class="admin-form-group">
            <label for="<?php echo $id_prefix; ?>forum_type">Tür</label>
            <select name="forum_type" id="<?php echo $id_prefix; ?>forum_type" class="admin-input" data-forum-type-select>
                <option value="section"<?php echo $forum_type === 'section' ? ' selected' : ''; ?>>Ana Bölüm</option>
                <option value="forum"<?php echo $forum_type !== 'section' ? ' selected' : ''; ?>>Alt Forum</option>
            </select>
        </div>
    </div>
    <div class="admin-form-group" data-parent-wrap>
        <label for="<?php echo $id_prefix; ?>parent_id">Üst Bölüm</label>
        <select name="parent_id" id="<?php echo $id_prefix; ?>parent_id" class="admin-input" data-parent-select<?php echo $prefix === 'add' ? ' required' : ''; ?>>
            <option value="">Seçin (alt forum için zorunlu)</option>
            <?php foreach ($sections as $sec): ?>
                <option value="<?php echo (int)$sec['id']; ?>"<?php echo (string)$parent_id === (string)$sec['id'] ? ' selected' : ''; ?>><?php echo escape($sec['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-form-group">
        <label for="<?php echo $id_prefix; ?>description">Açıklama</label>
        <input type="text" name="description" id="<?php echo $id_prefix; ?>description" class="admin-input" value="<?php echo $cat ? escape($cat['description'] ?? '') : ''; ?>" placeholder="Kısa açıklama">
    </div>
    <div class="zv-form-row zv-form-row-3">
        <div class="admin-form-group">
            <label>İkon</label>
            <?php echo icon_picker_field('icon', $icon_val, $id_prefix . 'icon'); ?>
        </div>
        <div class="admin-form-group">
            <label for="<?php echo $id_prefix; ?>color">Renk</label>
            <input type="color" name="color" id="<?php echo $id_prefix; ?>color" class="admin-input admin-color" value="<?php echo escape($color_val); ?>">
        </div>
        <div class="admin-form-group">
            <label for="<?php echo $id_prefix; ?>order_num">Sıra</label>
            <input type="number" name="order_num" id="<?php echo $id_prefix; ?>order_num" class="admin-input" value="<?php echo $cat ? (int)$cat['order_num'] : 0; ?>">
        </div>
    </div>
    <div class="admin-form-group" data-can-create-wrap>
        <label class="admin-toggle">
            <input type="checkbox" name="can_create_topic" value="1" data-can-create-check<?php echo $can_create ? ' checked' : ''; ?>>
            <span class="admin-toggle-ui"></span>
            <span class="admin-toggle-label">Üyeler konu açabilir (kapalıysa sadece yöneticiler)</span>
        </label>
    </div>
    <?php
}
