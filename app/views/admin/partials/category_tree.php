<?php
function admin_forum_node_payload(array $cat, $parent_name = '') {
    return [
        'id' => (int)$cat['id'],
        'name' => $cat['name'],
        'description' => $cat['description'] ?? '',
        'icon' => $cat['icon'] ?? 'folder',
        'color' => $cat['color'] ?? '#0d9488',
        'order_num' => (int)($cat['order_num'] ?? 0),
        'parent_id' => !empty($cat['parent_id']) ? (int)$cat['parent_id'] : '',
        'parent_name' => $parent_name,
        'forum_type' => $cat['forum_type'] ?? 'forum',
        'can_create_topic' => !empty($cat['can_create_topic']) ? 1 : 0,
    ];
}

function admin_render_forum_tree_nodes(array $nodes, $depth = 0, $parent_name = '') {
    foreach ($nodes as $node) {
        $is_section = ($node['forum_type'] ?? 'forum') === 'section';
        $stats = get_category_stats($node['id']);
        $payload = htmlspecialchars(json_encode(admin_forum_node_payload($node, $parent_name), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        $child_class = $depth > 0 ? ' is-child' : '';
        $section_class = $is_section ? ' is-section' : ' is-forum';
        ?>
        <article class="zv-forum-node<?php echo $section_class . $child_class; ?>" style="--zv-depth:<?php echo (int)$depth; ?>" data-category="<?php echo $payload; ?>">
            <div class="zv-forum-node-icon" style="color:<?php echo escape($node['color'] ?? '#0d9488'); ?>">
                <?php echo category_icon($node['icon'] ?? 'folder'); ?>
            </div>
            <div class="zv-forum-node-body">
                <div class="zv-forum-node-title">
                    <strong><?php echo escape($node['name']); ?></strong>
                    <span class="admin-pill"><?php echo $is_section ? 'Bölüm' : 'Forum'; ?></span>
                    <?php if (!$is_section && empty($node['can_create_topic'])): ?>
                        <span class="admin-pill admin-pill-muted">Sadece yönetici</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($node['description'])): ?>
                    <p class="zv-forum-node-desc"><?php echo escape($node['description']); ?></p>
                <?php endif; ?>
                <div class="zv-forum-node-meta">
                    <span><?php echo (int)$stats['topics']; ?> konu</span>
                    <span><?php echo (int)$stats['posts']; ?> mesaj</span>
                    <?php if ($parent_name !== ''): ?>
                        <span>Üst: <?php echo escape($parent_name); ?></span>
                    <?php endif; ?>
                    <a href="<?php echo url('/kategori/' . $node['slug']); ?>" class="admin-link" target="_blank">Görüntüle</a>
                </div>
            </div>
            <div class="zv-forum-node-actions">
                <button type="button" class="admin-btn admin-btn-sm admin-btn-outline" data-category-edit="<?php echo (int)$node['id']; ?>">Düzenle</button>
                <form method="post" action="<?php echo url('/admin/kategoriler'); ?>" class="admin-inline-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$node['id']; ?>">
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger" data-confirm="Bu kaydı silmek istediğinize emin misiniz?">Sil</button>
                </form>
            </div>
        </article>
        <?php
        if (!empty($node['children'])) {
            echo '<div class="zv-forum-children">';
            admin_render_forum_tree_nodes($node['children'], $depth + 1, $is_section ? $node['name'] : $parent_name);
            echo '</div>';
        } elseif ($is_section && $depth === 0) {
            echo '<div class="zv-forum-children zv-forum-children-empty"><p>Alt forum yok. Yeni forum eklerken bu bölümü üst olarak seçin.</p></div>';
        }
    }
}
