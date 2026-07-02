<div class="form-container form-container-narrow">
    <div class="form-header">
        <h1>Profil Düzenle</h1>
        <p>Profil bilgilerinizi ve avatarınızı güncelleyin</p>
    </div>

    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['general']); ?></div>
    <?php endif; ?>

    <?php if (isset($errors['csrf'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['csrf']); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo url('/profil-duzenle'); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="avatar-upload">
            <img
                src="<?php echo asset('uploads/avatars/' . $user['avatar']); ?>"
                alt="<?php echo escape($user['username']); ?>"
                class="profile-avatar-preview"
                id="avatar-preview"
                onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
            >
            <div class="form-group">
                <label class="form-label" for="avatar">Profil Fotoğrafı</label>
                <input type="file" id="avatar" name="avatar" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp">
                <div class="form-hint">Maksimum 5MB. JPG, PNG, GIF veya WebP formatları desteklenir.</div>
                <?php if (isset($errors['avatar'])): ?>
                    <div class="form-error"><?php echo escape($errors['avatar']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label form-label-strong" for="username">Kullanıcı Adı</label>
            <input
                type="text"
                id="username"
                class="form-input"
                value="<?php echo escape($user['username']); ?>"
                disabled
            >
            <div class="form-hint">Kullanıcı adı değiştirilemez.</div>
        </div>

        <div class="form-group">
            <label class="form-label form-label-strong" for="biography">Biyografi</label>
            <textarea
                id="biography"
                name="biography"
                class="form-textarea form-textarea-large"
                placeholder="Kendinizden kısaca bahsedin..."
                maxlength="500"
            ><?php echo escape($user['biography'] ?? ''); ?></textarea>
            <div class="form-hint">En fazla 500 karakter.</div>
            <?php if (isset($errors['biography'])): ?>
                <div class="form-error"><?php echo escape($errors['biography']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="<?php echo url('/profil/' . $user['username']); ?>" class="btn btn-cancel">İptal</a>
        </div>
    </form>
</div>

<script>
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('avatar-preview').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
