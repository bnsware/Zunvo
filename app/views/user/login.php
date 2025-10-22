<?php
/**
 * Zunvo Forum Sistemi
 * Giriş Sayfası
 */
?>
<style>
    .auth-container {
        max-width: 450px;
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
    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
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
    .alert {
        padding: 12px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

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
            <label for="remember" style="margin: 0; font-weight: normal;">Beni hatırla</label>
        </div>
        
        <button type="submit" class="form-button">Giriş Yap</button>
    </form>
    
    <div class="form-footer">
        <a href="<?php echo url('/sifremi-unuttum'); ?>">Şifremi unuttum</a>
        <br><br>
        Hesabınız yok mu? <a href="<?php echo url('/kayit'); ?>">Kayıt Ol</a>
    </div>
</div>