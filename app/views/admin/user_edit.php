<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header"><h2>Kullanıcı Bilgileri</h2></div>
        <div class="admin-card-body">
            <dl class="admin-dl">
                <dt>Kullanıcı Adı</dt>
                <dd><?php echo escape($user['username']); ?></dd>
                <dt>E-posta</dt>
                <dd><?php echo escape($user['email']); ?></dd>
                <dt>Repütasyon</dt>
                <dd><?php echo (int)$user['reputation']; ?></dd>
                <dt>Kayıt Tarihi</dt>
                <dd><?php echo format_date($user['created_at']); ?></dd>
                <dt>Son Aktivite</dt>
                <dd><?php echo $user['last_active'] ? format_date($user['last_active']) : '—'; ?></dd>
                <dt>Konular</dt>
                <dd><?php echo (int)$stats['topics']; ?></dd>
                <dt>Mesajlar</dt>
                <dd><?php echo (int)$stats['posts']; ?></dd>
            </dl>
            <a href="<?php echo url('/profil/' . $user['username']); ?>" class="admin-link" target="_blank">Profili Görüntüle →</a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2>Rol Değiştir</h2></div>
        <div class="admin-card-body">
            <form method="post" action="<?php echo url('/admin/kullanici/' . $user['id']); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="update_role">
                <div class="admin-form-group">
                    <label for="role">Rol</label>
                    <select name="role" id="role" class="admin-input">
                        <option value="user"<?php echo $user['role'] === 'user' ? ' selected' : ''; ?>>Kullanıcı</option>
                        <option value="moderator"<?php echo $user['role'] === 'moderator' ? ' selected' : ''; ?>>Moderatör</option>
                        <option value="admin"<?php echo $user['role'] === 'admin' ? ' selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">Rolü Güncelle</button>
            </form>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header"><h2>Yasaklama</h2></div>
    <div class="admin-card-body">
        <?php if ($user['is_banned']): ?>
            <p class="admin-text-warning">Bu kullanıcı şu anda yasaklı.</p>
            <form method="post" action="<?php echo url('/admin/kullanici/' . $user['id']); ?>" class="admin-inline-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="unban">
                <button type="submit" class="admin-btn admin-btn-success" data-confirm="Yasağı kaldırmak istediğinize emin misiniz?">Yasağı Kaldır</button>
            </form>
        <?php else: ?>
            <p>Bu kullanıcı aktif durumda.</p>
            <form method="post" action="<?php echo url('/admin/kullanici/' . $user['id']); ?>" class="admin-inline-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="ban">
                <button type="submit" class="admin-btn admin-btn-danger" data-confirm="Bu kullanıcıyı yasaklamak istediğinize emin misiniz?">Yasakla</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<p><a href="<?php echo url('/admin/kullanicilar'); ?>" class="admin-link">← Kullanıcı listesine dön</a></p>
