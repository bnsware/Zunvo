<?php
$active_meta = $theme_meta[$active_slug] ?? null;
$zip_max_mb = (int)ceil(theme_zip_max_bytes() / (1024 * 1024));
$zip_supported = class_exists('ZipArchive');
?>
<p class="admin-toolbar-meta">Taranan tema: <strong><?php echo (int)$scanned_count; ?></strong></p>

<div class="admin-card" style="margin-bottom:1.25rem;">
    <div class="admin-card-header"><h2>Tema yükle</h2></div>
    <div class="admin-card-body">
        <?php if (!$zip_supported): ?>
            <p class="admin-hint" style="color:var(--danger,#b91c1c);">Sunucuda ZipArchive eklentisi yok. Temayı <code>themes/</code> klasörüne manuel yükleyin.</p>
        <?php else: ?>
            <form method="post" action="<?php echo url('/admin/temalar'); ?>" enctype="multipart/form-data" class="admin-theme-upload-form">
                <?php echo csrf_field(); ?>
                <div class="admin-form-row admin-theme-upload-row">
                    <div class="admin-form-group admin-theme-upload-file">
                        <label for="theme_zip" class="admin-label">ZIP dosyası</label>
                        <input type="file" id="theme_zip" name="theme_zip" accept=".zip,application/zip" class="admin-input" required>
                        <p class="admin-hint">Maks. <?php echo $zip_max_mb; ?> MB · ZIP kökünde veya tek klasör içinde <code>theme.json</code> olmalı</p>
                    </div>
                    <div class="admin-form-group admin-theme-upload-options">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="activate_after" value="1">
                            Yükleme sonrası etkinleştir
                        </label>
                    </div>
                    <div class="admin-form-actions admin-theme-upload-actions">
                        <button type="submit" name="action" value="upload" class="admin-btn admin-btn-primary">Yükle</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ad</th>
                    <th>Rol</th>
                    <th>Versiyon</th>
                    <th>Parent</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($themes)): ?>
                    <tr><td colspan="6" class="admin-empty">Tema bulunamadı. <code>themes/</code> klasörüne tema ekleyin.</td></tr>
                <?php else: ?>
                    <?php foreach ($themes as $theme): ?>
                        <?php
                        $meta = $theme_meta[$theme['slug']] ?? [];
                        if (empty($meta)) {
                            continue;
                        }
                        $errors = $theme_validation[$theme['slug']] ?? [];
                        $is_valid = empty($errors);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($theme['name']); ?></strong>
                                <div class="admin-hint"><code><?php echo escape($theme['slug']); ?></code></div>
                                <?php if (!empty($meta['description'])): ?>
                                    <div class="admin-hint"><?php echo escape($meta['description']); ?></div>
                                <?php endif; ?>
                                <?php if (!$is_valid): ?>
                                    <div class="admin-hint" style="color:var(--danger,#b91c1c);"><?php echo escape(implode(' · ', $errors)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo escape($meta['role'] ?? ($meta['extends'] ?? false ? 'child' : 'parent')); ?></td>
                            <td><?php echo escape($meta['version'] ?? '1.0.0'); ?></td>
                            <td>
                                <?php if (!empty($meta['extends'])): ?>
                                    <code><?php echo escape($meta['extends']); ?></code>
                                <?php else: ?>
                                    <span class="admin-hint">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($theme['is_active']): ?>
                                    <span class="admin-badge-status admin-badge-active">Aktif</span>
                                <?php elseif ($is_valid): ?>
                                    <span class="admin-badge-status">Hazır</span>
                                <?php else: ?>
                                    <span class="admin-badge-status" style="color:var(--danger,#b91c1c);">Hatalı</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-actions">
                                <?php if (!$theme['is_active'] && $is_valid): ?>
                                    <form method="post" action="<?php echo url('/admin/temalar'); ?>" class="admin-inline-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="slug" value="<?php echo escape($theme['slug']); ?>">
                                        <button type="submit" name="action" value="activate" class="admin-btn admin-btn-sm admin-btn-primary">Etkinleştir</button>
                                    </form>
                                <?php elseif ($theme['is_active']): ?>
                                    <span class="admin-hint">Kullanımda</span>
                                <?php else: ?>
                                    <span class="admin-hint">Düzeltin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-theme-hub" style="margin-top:1.5rem;">
    <div class="admin-card admin-theme-hub-card">
        <div class="admin-card-body">
            <div class="admin-theme-hub-icon"><?php echo icon('palette', 'icon'); ?></div>
            <h2 class="admin-theme-hub-title"><?php echo escape($active_meta['name'] ?? 'Zunvo'); ?></h2>
            <p class="admin-theme-hub-desc">Aktif tema · <code>themes/<?php echo escape($active_slug); ?>/</code> · <?php echo (int)count_theme_templates($active_slug); ?> şablon</p>
            <div class="admin-form-actions">
                <a href="<?php echo url('/admin/temalar/sablon'); ?>" class="admin-btn admin-btn-primary">Şablon Editörü</a>
                <a href="<?php echo url('/admin/temalar/stil'); ?>" class="admin-btn admin-btn-outline">Stil Editörü</a>
            </div>
        </div>
    </div>
    <div class="admin-card">
        <div class="admin-card-header"><h2>Tema geliştirme</h2></div>
        <div class="admin-card-body">
            <ul class="admin-help-list">
                <li><strong>ZIP yükle</strong> — yukarıdan tema paketini seçin (MyBB tarzı)</li>
                <li><strong>Klasör oluştur</strong> — <code>themes/benim-temam/</code></li>
                <li><strong>theme.json</strong> — manifest dosyası (zorunlu)</li>
                <li><strong>bootstrap.php</strong> — isteğe bağlı hook girişi (plugin gibi)</li>
                <li><strong>style.css</strong> — tema stilleri</li>
                <li><strong>templates/</strong> — şablon override'ları</li>
            </ul>
            <p class="admin-hint">Hızlı başlangıç: <code>themes/_starter/</code> klasörünü kopyalayıp yeniden adlandırın. Parent tema için <code>"extends": "default"</code> kullanın.</p>
        </div>
    </div>
</div>
