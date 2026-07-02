<div class="admin-settings-layout">
    <div class="admin-settings-main">
        <form method="post" action="<?php echo url('/admin/ayarlar'); ?>" class="admin-settings-form">
            <?php echo csrf_field(); ?>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Genel bilgiler</h2>
                </div>
                <div class="admin-card-body">
                    <p class="admin-form-section-desc">Site başlığı ve arama motorlarında görünen açıklama.</p>
                    <div class="zv-form-row zv-form-row-2">
                        <div class="admin-form-group">
                            <label for="site_name">Site adı</label>
                            <input type="text" name="site_name" id="site_name" class="admin-input" value="<?php echo escape($site_name); ?>" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="site_description">Site açıklaması</label>
                            <input type="text" name="site_description" id="site_description" class="admin-input" value="<?php echo escape($site_description); ?>" placeholder="Kısa site açıklaması">
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Üyelik</h2>
                </div>
                <div class="admin-card-body">
                    <p class="admin-form-section-desc">Yeni kullanıcıların kayıt olup olamayacağını belirler.</p>
                    <div class="admin-settings-toggles">
                        <label class="admin-toggle">
                            <input type="checkbox" name="registration_enabled" value="1"<?php echo $registration_enabled === '1' ? ' checked' : ''; ?>>
                            <span class="admin-toggle-ui"></span>
                            <span class="admin-toggle-label">Yeni üye kaydına izin ver</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Hakkımızda sayfası</h2>
                    <a href="<?php echo url('/hakkimizda'); ?>" class="admin-link" target="_blank" rel="noopener">Önizle →</a>
                </div>
                <div class="admin-card-body">
                    <p class="admin-form-section-desc">/hakkimizda sayfasında gösterilen metin.</p>
                    <div class="admin-form-group admin-form-group-last">
                        <label for="about_content">İçerik</label>
                        <textarea name="about_content" id="about_content" class="admin-input admin-textarea admin-textarea-lg" rows="6" placeholder="Forumunuz hakkında kısa bir tanıtım yazın"><?php echo escape($about_content); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="admin-settings-form-foot">
                <button type="submit" class="admin-btn admin-btn-primary">Tüm ayarları kaydet</button>
            </div>
        </form>
    </div>

    <aside class="admin-settings-aside">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Site özeti</h2>
            </div>
            <div class="admin-card-body">
                <dl class="admin-settings-dl">
                    <div>
                        <dt>Adres</dt>
                        <dd><a href="<?php echo url('/'); ?>" target="_blank" rel="noopener"><?php echo escape(rtrim(SITE_URL, '/')); ?></a></dd>
                    </div>
                    <div>
                        <dt>Kayıt</dt>
                        <dd>
                            <span class="admin-pill<?php echo $registration_enabled === '1' ? ' admin-pill-success' : ' admin-pill-muted'; ?>">
                                <?php echo $registration_enabled === '1' ? 'Açık' : 'Kapalı'; ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Ana sayfa widget</dt>
                        <dd>
                            <span class="admin-pill<?php echo !empty($widget_enabled) ? ' admin-pill-success' : ' admin-pill-muted'; ?>">
                                <?php echo !empty($widget_enabled) ? 'Açık' : 'Kapalı'; ?>
                            </span>
                        </dd>
                    </div>
                </dl>
                <div class="admin-settings-mini-stats">
                    <div class="admin-settings-mini-stat">
                        <span class="admin-settings-mini-value"><?php echo format_number($stats['users']); ?></span>
                        <span class="admin-settings-mini-label">Üye</span>
                    </div>
                    <div class="admin-settings-mini-stat">
                        <span class="admin-settings-mini-value"><?php echo format_number($stats['topics']); ?></span>
                        <span class="admin-settings-mini-label">Konu</span>
                    </div>
                    <div class="admin-settings-mini-stat">
                        <span class="admin-settings-mini-value"><?php echo format_number($stats['posts']); ?></span>
                        <span class="admin-settings-mini-label">Mesaj</span>
                    </div>
                    <div class="admin-settings-mini-stat">
                        <span class="admin-settings-mini-value"><?php echo format_number($stats['categories']); ?></span>
                        <span class="admin-settings-mini-label">Forum</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Diğer ayarlar</h2>
            </div>
            <div class="admin-card-body admin-settings-links">
                <a href="<?php echo url('/admin/widget'); ?>" class="admin-settings-link">
                    <span class="admin-settings-link-icon"><?php echo icon('grid', 'icon'); ?></span>
                    <span class="admin-settings-link-body">
                        <strong>Ana sayfa widget</strong>
                        <span>Aktivite sekmeleri ve kategori listeleri</span>
                    </span>
                </a>
                <a href="<?php echo url('/admin/temalar'); ?>" class="admin-settings-link">
                    <span class="admin-settings-link-icon"><?php echo icon('palette', 'icon'); ?></span>
                    <span class="admin-settings-link-body">
                        <strong>Tema</strong>
                        <span>Görünüm, şablonlar ve stil özelleştirme</span>
                    </span>
                </a>
                <a href="<?php echo url('/admin/kategoriler'); ?>" class="admin-settings-link">
                    <span class="admin-settings-link-icon"><?php echo icon('folder', 'icon'); ?></span>
                    <span class="admin-settings-link-body">
                        <strong>Forum yapısı</strong>
                        <span>Bölümler, alt forumlar ve sıralama</span>
                    </span>
                </a>
                <a href="<?php echo url('/admin/pluginler'); ?>" class="admin-settings-link">
                    <span class="admin-settings-link-icon"><?php echo icon('plugin', 'icon'); ?></span>
                    <span class="admin-settings-link-body">
                        <strong>Pluginler</strong>
                        <span>Eklenti yönetimi ve yapılandırma</span>
                    </span>
                </a>
            </div>
        </div>
    </aside>
</div>
