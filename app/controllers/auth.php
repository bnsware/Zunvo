<?php
/**
 * Zunvo Forum Sistemi
 * Auth Controller
 * 
 * Kullanıcı kimlik doğrulama işlemleri
 */

// User model'i dahil et
require_once APP_PATH . '/models/user.php';

/**
 * Kayıt sayfası
 */
function auth_register() {
    // Zaten giriş yapmışsa ana sayfaya yönlendir
    if (is_logged_in()) {
        redirect(url('/'));
    }
    
    $errors = [];
    $old_data = [];
    
    if (is_post()) {
        // CSRF kontrolü
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        } else {
            $username = post_param('username');
            $email = post_param('email');
            $password = post_param('password');
            $password_confirm = post_param('password_confirm');
            
            // Eski verileri sakla
            $old_data = [
                'username' => $username,
                'email' => $email
            ];
            
            // Şifre eşleşme kontrolü
            if ($password !== $password_confirm) {
                $errors['password_confirm'] = 'Şifreler eşleşmiyor.';
            }
            
            // Kullanıcı verilerini validate et
            $validation_errors = validate_user_data([
                'username' => $username,
                'email' => $email,
                'password' => $password
            ]);
            
            $errors = array_merge($errors, $validation_errors);
            
            // Hata yoksa kullanıcı oluştur
            if (empty($errors)) {
                $user_id = create_user([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password
                ]);
                
                if ($user_id) {
                    set_flash('success', 'Kayıt başarılı! Email adresinize doğrulama linki gönderildi.');
                    redirect(url('/giris'));
                } else {
                    $errors['general'] = 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.';
                }
            }
        }
    }
    
    // View'ı göster
    render('user/register', [
        'title' => 'Kayıt Ol',
        'errors' => $errors,
        'old_data' => $old_data
    ]);
}

/**
 * Giriş sayfası
 */
function auth_login() {
    // Zaten giriş yapmışsa ana sayfaya yönlendir
    if (is_logged_in()) {
        redirect(url('/'));
    }
    
    $errors = [];
    
    if (is_post()) {
        // CSRF kontrolü
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        } else {
            $username_or_email = post_param('username_or_email');
            $password = post_param('password');
            $remember = post_param('remember') === '1';
            
            // Rate limiting kontrolü
            $client_ip = get_client_ip();
            if (!check_rate_limit($client_ip, 'login')) {
                $errors['rate_limit'] = 'Çok fazla başarısız giriş denemesi. Lütfen ' . 
                                        (LOGIN_LOCKOUT_TIME / 60) . ' dakika sonra tekrar deneyin.';
            } else {
                // Kullanıcıyı bul
                $user = get_user_by_username($username_or_email);
                if (!$user) {
                    $user = get_user_by_email($username_or_email);
                }
                
                // Kullanıcı bulundu mu ve şifre doğru mu?
                if (!$user || !verify_password($password, $user['password'])) {
                    record_rate_limit_attempt($client_ip, 'login');
                    $errors['login'] = 'Kullanıcı adı/email veya şifre hatalı.';
                } elseif ($user['is_banned']) {
                    $errors['banned'] = 'Hesabınız yasaklanmıştır.';
                } else {
                    // Giriş başarılı
                    reset_rate_limit($client_ip, 'login');
                    
                    // Session'a kaydet
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_ip'] = $client_ip;
                    $_SESSION['user_agent'] = get_user_agent();
                    
                    // Remember me
                    if ($remember) {
                        $token = generate_token(64);
                        setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 gün
                        // Token'ı veritabanına kaydetmek gerekir (opsiyonel)
                    }
                    
                    // Son aktiviteyi güncelle
                    update_user_activity($user['id']);
                    
                    // Session'ı yenile
                    regenerate_session();
                    
                    set_flash('success', 'Hoş geldiniz, ' . $user['username'] . '!');
                    redirect(url('/'));
                }
            }
        }
    }
    
    // View'ı göster
    render('user/login', [
        'title' => 'Giriş Yap',
        'errors' => $errors
    ]);
}

/**
 * Çıkış
 */
function auth_logout() {
    // Session'ı temizle
    session_destroy();
    
    // Remember me cookie'sini sil
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    set_flash('success', 'Başarıyla çıkış yaptınız.');
    redirect(url('/giris'));
}

/**
 * Email doğrulama
 * @param string $token Doğrulama token'ı
 */
function auth_verify($token) {
    if (verify_user_email($token)) {
        set_flash('success', 'Email adresiniz doğrulandı! Artık giriş yapabilirsiniz.');
    } else {
        set_flash('error', 'Geçersiz veya süresi dolmuş doğrulama linki.');
    }
    
    redirect(url('/giris'));
}

/**
 * Şifre sıfırlama isteği sayfası
 */
function auth_forgot_password() {
    $errors = [];
    $success = false;
    
    if (is_post()) {
        // CSRF kontrolü
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        } else {
            $email = post_param('email');
            
            if (!validate_email($email)) {
                $errors['email'] = 'Geçerli bir email adresi girin.';
            } else {
                // Rate limiting
                $client_ip = get_client_ip();
                if (!check_rate_limit($client_ip, 'reset')) {
                    $errors['rate_limit'] = 'Çok fazla istek. Lütfen daha sonra tekrar deneyin.';
                } else {
                    // Token oluştur ve email gönder
                    $token = create_password_reset_token($email);
                    
                    if ($token) {
                        $success = true;
                        set_flash('success', 'Şifre sıfırlama linki email adresinize gönderildi.');
                    } else {
                        // Güvenlik için email bulunamadı mesajı verme
                        $success = true;
                        set_flash('success', 'Eğer bu email kayıtlıysa, şifre sıfırlama linki gönderildi.');
                    }
                    
                    record_rate_limit_attempt($client_ip, 'reset');
                }
            }
        }
    }
    
    // View'ı göster
    render('user/forgot_password', [
        'title' => 'Şifremi Unuttum',
        'errors' => $errors,
        'success' => $success
    ]);
}

/**
 * Şifre sıfırlama sayfası
 * @param string $token Reset token
 */
function auth_reset_password($token) {
    // Token'ı kontrol et
    $user = verify_password_reset_token($token);
    
    if (!$user) {
        set_flash('error', 'Geçersiz veya süresi dolmuş şifre sıfırlama linki.');
        redirect(url('/giris'));
        return;
    }
    
    $errors = [];
    
    if (is_post()) {
        // CSRF kontrolü
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        } else {
            $password = post_param('password');
            $password_confirm = post_param('password_confirm');
            
            // Şifre eşleşme kontrolü
            if ($password !== $password_confirm) {
                $errors['password_confirm'] = 'Şifreler eşleşmiyor.';
            }
            
            // Şifre gücü kontrolü
            $password_check = validate_password_strength($password);
            if (!$password_check['valid']) {
                $errors['password'] = implode(' ', $password_check['errors']);
            }
            
            // Hata yoksa şifreyi sıfırla
            if (empty($errors)) {
                if (reset_password_with_token($token, $password)) {
                    set_flash('success', 'Şifreniz başarıyla sıfırlandı. Artık giriş yapabilirsiniz.');
                    redirect(url('/giris'));
                } else {
                    $errors['general'] = 'Şifre sıfırlama sırasında bir hata oluştu.';
                }
            }
        }
    }
    
    // View'ı göster
    render('user/reset_password', [
        'title' => 'Şifre Sıfırla',
        'errors' => $errors,
        'token' => $token
    ]);
}

/**
 * Profil sayfası
 * @param string $username Kullanıcı adı
 */
function auth_profile($username) {
    $user = get_user_by_username($username);
    
    if (!$user) {
        set_flash('error', 'Kullanıcı bulunamadı.');
        redirect(url('/'));
        return;
    }
    
    // Kullanıcı istatistiklerini al
    $stats = get_user_stats($user['id']);
    
    // Seviyeyi hesapla
    $level = get_user_level($user['reputation']);
    
    // Kullanıcının son konularını al
    $recent_topics = db_query_all(
        "SELECT * FROM topics WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
        [$user['id']]
    );
    
    // View'ı göster
    render('user/profile', [
        'title' => $user['username'] . ' - Profil',
        'profile_user' => $user,
        'stats' => $stats,
        'level' => $level,
        'recent_topics' => $recent_topics
    ]);
}

/**
 * Profil düzenleme sayfası
 */
function auth_edit_profile() {
    require_login();
    
    $user = current_user();
    $errors = [];
    
    if (is_post()) {
        // CSRF kontrolü
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        } else {
            $biography = post_param('biography');
            
            // Avatar yükleme
            $avatar = $user['avatar'];
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploaded_avatar = upload_avatar($_FILES['avatar']);
                if ($uploaded_avatar['success']) {
                    $avatar = $uploaded_avatar['filename'];
                } else {
                    $errors['avatar'] = $uploaded_avatar['error'];
                }
            }
            
            // Güncelle
            if (empty($errors)) {
                if (update_user($user['id'], [
                    'biography' => $biography,
                    'avatar' => $avatar
                ])) {
                    set_flash('success', 'Profil başarıyla güncellendi.');
                    redirect(url('/profil/' . $user['username']));
                } else {
                    $errors['general'] = 'Profil güncellenirken bir hata oluştu.';
                }
            }
        }
    }
    
    // View'ı göster
    render('user/edit_profile', [
        'title' => 'Profil Düzenle',
        'user' => $user,
        'errors' => $errors
    ]);
}

/**
 * Avatar yükleme fonksiyonu
 * @param array $file Upload edilen dosya
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function upload_avatar($file) {
    // Boyut kontrolü
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'Dosya boyutu çok büyük. Maksimum 5MB.'];
    }
    
    // Mime type kontrolü
    if (!validate_file_mime($file['tmp_name'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Geçersiz dosya tipi. Sadece resim dosyaları yüklenebilir.'];
    }
    
    // Güvenli dosya adı oluştur
    $filename = sanitize_filename($file['name']);
    $upload_path = AVATAR_PATH . '/' . $filename;
    
    // Klasör yoksa oluştur
    if (!is_dir(AVATAR_PATH)) {
        mkdir(AVATAR_PATH, 0755, true);
    }
    
    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Dosya yüklenirken bir hata oluştu.'];
}
