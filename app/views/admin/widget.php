<?php
$active_keys = $active_keys ?? ['recent', 'replied', 'popular'];
$active_cats = $active_cats ?? [];
$tab_options = [
    'recent' => 'Son açılan konular',
    'replied' => 'Son cevaplanan konular',
    'visited' => 'Son gezilen konular (giriş gerekir)',
    'popular' => 'Popüler konular',
];
?>
<div class="admin-card">
    <div class="admin-card-body">
        <form method="post" action="<?php echo url('/admin/widget'); ?>" class="admin-widget-form">
            <?php echo csrf_field(); ?>
            <div class="admin-form-group">
                <label class="admin-toggle">
                    <input type="checkbox" name="homepage_widget_enabled" value="1"<?php echo $enabled ? ' checked' : ''; ?>>
                    <span class="admin-toggle-ui"></span>
                    <span class="admin-toggle-label">Ana sayfada aktivite widget'ını göster</span>
                </label>
            </div>

            <div class="admin-form-section">
                <h3 class="admin-form-section-title">Sekmeler</h3>
                <p class="admin-form-section-desc">Ana sayfada hangi listelerin sekme olarak görüneceğini seçin.</p>
                <div class="admin-check-grid">
                    <?php foreach ($tab_options as $key => $label): ?>
                        <label class="admin-check-card">
                            <input type="checkbox" name="tab_keys[]" value="<?php echo escape($key); ?>"<?php echo in_array($key, $active_keys, true) ? ' checked' : ''; ?>>
                            <span class="admin-check-card-body">
                                <strong><?php echo escape($label); ?></strong>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($categories)): ?>
                <div class="admin-form-section">
                    <h3 class="admin-form-section-title">Kategori sekmeleri</h3>
                    <p class="admin-form-section-desc">Belirli bir forumun konularını ayrı sekme olarak ekleyin.</p>
                    <div class="admin-check-grid admin-check-grid-compact">
                        <?php foreach ($categories as $cat): ?>
                            <?php if (($cat['forum_type'] ?? 'forum') === 'section') continue; ?>
                            <label class="admin-check-card admin-check-card-sm">
                                <input type="checkbox" name="tab_categories[]" value="<?php echo (int)$cat['id']; ?>"<?php echo in_array((int)$cat['id'], $active_cats, true) ? ' checked' : ''; ?>>
                                <span class="admin-check-card-body">
                                    <strong><?php echo escape($cat['name']); ?></strong>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>
