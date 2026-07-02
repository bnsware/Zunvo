<div class="auth-wrap">
<div class="auth-container">
    <h1 class="auth-title">Şifremi Unuttum</h1>

    <?php if (isset($errors['csrf'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['csrf']); ?></div>
    <?php endif; ?>

    <?php if (isset($errors['rate_limit'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['rate_limit']); ?></div>
    <?php endif; ?>

    <p class="text-muted-center">
        Kayıtlı email adresinizi girin, size şifre sıfırlama linki gönderelim.
    </p>

    <form method="POST" action="<?php echo url('/sifremi-unuttum'); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="email">Email Adresi</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input"
                required
                placeholder="email@ornek.com"
                autofocus
            >
            <?php if (isset($errors['email'])): ?>
                <div class="form-error"><?php echo escape($errors['email']); ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="form-button">Sıfırlama Linki Gönder</button>
    </form>

    <div class="form-footer">
        <a href="<?php echo url('/giris'); ?>">Giriş sayfasına dön</a>
    </div>
</div>
</div>
