<div class="auth-wrap">
<div class="auth-container auth-container-wide">
    <h1 class="auth-title">Kayıt Ol</h1>

    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo escape($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo url('/kayit'); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="username">Kullanıcı Adı</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-input"
                value="<?php echo isset($old_data['username']) ? escape($old_data['username']) : ''; ?>"
                required
                maxlength="20"
                pattern="[a-zA-Z0-9_-]{3,20}"
                placeholder="3-20 karakter (harf, rakam, - ve _)"
            >
            <?php if (isset($errors['username'])): ?>
                <div class="form-error"><?php echo escape($errors['username']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input"
                value="<?php echo isset($old_data['email']) ? escape($old_data['email']) : ''; ?>"
                required
                placeholder="email@ornek.com"
            >
            <?php if (isset($errors['email'])): ?>
                <div class="form-error"><?php echo escape($errors['email']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Şifre</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                required
                minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                placeholder="Güçlü bir şifre seçin"
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

        <button type="submit" class="form-button">Kayıt Ol</button>
    </form>

    <div class="form-footer">
        Zaten hesabınız var mı? <a href="<?php echo url('/giris'); ?>">Giriş Yap</a>
    </div>
</div>
</div>
