<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header"><h2>Yeni API Anahtarı</h2></div>
        <div class="admin-card-body">
            <form method="post" action="<?php echo url('/admin/api'); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="create">
                <div class="admin-form-group">
                    <label for="name">Anahtar Adı</label>
                    <input type="text" name="name" id="name" class="admin-input" placeholder="Örn: Mobil Uygulama" required>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">Oluştur</button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2>Mevcut Anahtarlar</h2></div>
        <div class="admin-card-body admin-table-wrap">
            <?php if (empty($api_keys)): ?>
                <p class="admin-empty">Henüz API anahtarı yok.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ad</th>
                            <th>Anahtar</th>
                            <th>Oluşturan</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($api_keys as $key): ?>
                            <tr>
                                <td><?php echo escape($key['name']); ?></td>
                                <td><code class="admin-code"><?php echo escape(substr($key['api_key'], 0, 12)); ?>...</code></td>
                                <td><?php echo escape($key['username']); ?></td>
                                <td>
                                    <?php if ($key['is_active']): ?>
                                        <span class="admin-badge-status admin-badge-active">Aktif</span>
                                    <?php else: ?>
                                        <span class="admin-badge-status">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($key['created_at']); ?></td>
                                <td class="admin-actions">
                                    <?php if ($key['is_active']): ?>
                                        <form method="post" action="<?php echo url('/admin/api'); ?>" class="admin-inline-form">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="id" value="<?php echo (int)$key['id']; ?>">
                                            <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline">Devre Dışı</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?php echo url('/admin/api'); ?>" class="admin-inline-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$key['id']; ?>">
                                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger" data-confirm="Bu API anahtarını silmek istediğinize emin misiniz?">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header"><h2>API Kullanımı</h2></div>
    <div class="admin-card-body">
        <p>Tüm isteklerde <code>X-API-Key</code> header veya <code>api_key</code> query parametresi gerekir.</p>
        <ul class="admin-docs-list">
            <li><code>GET /api/v1/topics</code> — Konu listesi</li>
            <li><code>GET /api/v1/topics/{id}</code> — Konu detayı</li>
            <li><code>GET/POST /api/v1/posts</code> — Gönderiler</li>
            <li><code>POST /api/v1/vote</code> — Oy verme</li>
            <li><code>GET /api/v1/users/{username}</code> — Profil</li>
            <li><code>GET /api/v1/search?q=</code> — Arama</li>
        </ul>
        <pre class="admin-code-block">curl -H "X-API-Key: ANAHTARINIZ" <?php echo escape(rtrim(SITE_URL, '/')); ?>/api/v1/topics</pre>
    </div>
</div>
