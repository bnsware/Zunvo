<?php
require_once APP_PATH . '/views/admin/partials/category_fields.php';
require_once APP_PATH . '/views/admin/partials/category_tree.php';
?>
<div class="zv-forum-page">
    <section class="admin-card" id="category-add-card">
        <header class="admin-card-header">
            <h2>Yeni Bölüm veya Forum</h2>
        </header>
        <div class="admin-card-body">
            <form method="post" action="<?php echo url('/admin/kategoriler'); ?>" id="category-add-form" class="zv-form-grid">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <?php admin_render_category_fields('add', $sections, null); ?>
                <div class="zv-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </section>

    <section class="admin-card">
        <header class="admin-card-header">
            <h2>Mevcut Yapı</h2>
            <span class="admin-meta"><?php echo count($categories); ?> kayıt</span>
        </header>
        <div class="admin-card-body">
            <?php if (empty($tree) && empty($categories)): ?>
                <p class="admin-empty">Henüz forum yapısı yok. Yukarıdan ilk bölümünüzü ekleyin.</p>
            <?php else: ?>
                <div class="zv-forum-tree" id="forum-tree">
                    <?php admin_render_forum_tree_nodes($tree); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="zv-drawer" id="category-drawer" hidden>
    <div class="zv-drawer-backdrop" data-drawer-close></div>
    <div class="zv-drawer-panel" role="dialog" aria-labelledby="category-drawer-title">
        <header class="zv-drawer-header">
            <h2 id="category-drawer-title">Forum Düzenle</h2>
            <button type="button" class="zv-drawer-close" data-drawer-close aria-label="Kapat"><?php echo icon('x', 'icon'); ?></button>
        </header>
        <form method="post" action="<?php echo url('/admin/kategoriler'); ?>" id="category-edit-form" class="zv-drawer-body zv-form-grid">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-category-id" value="">
            <?php admin_render_category_fields('edit', $sections, null); ?>
            <div class="zv-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
                <button type="button" class="admin-btn admin-btn-outline" data-drawer-close>İptal</button>
            </div>
        </form>
    </div>
</div>
<script type="application/json" id="admin-parent-options"><?php echo json_encode(array_map(function ($s) {
    return ['id' => (int)$s['id'], 'name' => $s['name']];
}, $parent_options ?? $sections), JSON_UNESCAPED_UNICODE); ?></script>
