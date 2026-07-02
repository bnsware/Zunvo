<div class="auth-wrap">
<div class="auth-container">
    <h1 class="auth-title">Yeni Şifre Belirle</h1>

    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['general']); ?></div>
    <?php endif; ?>

    <?php if (isset($errors['csrf'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['csrf']); ?></div>
    <?php endif; ?>

    <p class="text-muted-center">
        Hesabınız için yeni bir şifre belirleyin.
    </p>

    <form method="POST" action="<?php echo url('/sifre-sifirla/' . $token); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="password">Yeni Şifre</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                required
                minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                placeholder="Güçlü bir şifre seçin"
                autofocus
            >
            <div class="password-requirements">
                En az <?php echo PASSWORD_MIN_LENGTH; ?> karakter, büyük harf, küçük harf ve rakam içermelidir.
            </div>
            <?php if (isset($errors['password'])): ?>
                <div class="form-error"><?php echo escape($errors['password']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirm">Şifre Tekrar</label>
            <input
                type="password"
                id="password_confirm"
                name="password_confirm"
                class="form-input"
                required
                minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                placeholder="Şifrenizi tekrar girin"
            >
            <?php if (isset($errors['password_confirm'])): ?>
                <div class="form-error"><?php echo escape($errors['password_confirm']); ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="form-button">Şifreyi Sıfırla</button>
    </form>

    <div class="form-footer">
        <a href="<?php echo url('/giris'); ?>">Giriş sayfasına dön</a>
    </div>
</div>
</div>
