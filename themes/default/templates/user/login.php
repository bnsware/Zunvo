<div class="auth-wrap">
<div class="auth-container">
    <h1 class="auth-title">Giriş Yap</h1>

    <?php if (isset($errors['login'])): ?>
        <div class="alert alert-danger">
            <?php echo escape($errors['login']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors['banned'])): ?>
        <div class="alert alert-danger">
            <?php echo escape($errors['banned']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors['rate_limit'])): ?>
        <div class="alert alert-danger">
            <?php echo escape($errors['rate_limit']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo url('/giris'); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label" for="username_or_email">Kullanıcı Adı veya Email</label>
            <input
                type="text"
                id="username_or_email"
                name="username_or_email"
                class="form-input"
                required
                placeholder="kullaniciadi veya email@ornek.com"
                autofocus
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Şifre</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input"
                required
                placeholder="Şifreniz"
            >
        </div>

        <div class="form-checkbox">
            <input type="checkbox" id="remember" name="remember" value="1">
            <label for="remember">Beni hatırla</label>
        </div>

        <button type="submit" class="form-button btn-with-icon">
            <?php echo icon('log-in', 'icon icon-sm'); ?> Giriş Yap
        </button>
    </form>

    <div class="form-footer">
        <a href="<?php echo url('/sifremi-unuttum'); ?>">Şifremi unuttum</a>
        <br><br>
        Hesabınız yok mu? <a href="<?php echo url('/kayit'); ?>">Kayıt Ol</a>
    </div>
</div>
</div>
