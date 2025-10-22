<?php
/**
 * Zunvo Forum Sistemi
 * Kayıt Sayfası
 */
?>
<style>
    .auth-container {
        max-width: 500px;
        margin: 50px auto;
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .auth-title {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
    }
    .form-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    .form-input:focus {
        outline: none;
        border-color: #007bff;
    }
    .form-error {
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
    }
    .form-button {
        width: 100%;
        padding: 12px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }
    .form-button:hover {
        background: #0056b3;
    }
    .form-footer {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    .form-footer a {
        color: #007bff;
        text-decoration: none;
    }
    .form-footer a:hover {
        text-decoration: underline;
    }
    .password-requirements {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
</style>

<div class="auth-container">
    <h1 class="auth-title">Kayıt Ol</h1>
    
    <?php if (isset($errors['general'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px;">
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