<?php
/**
 * Zunvo Forum Sistemi
 * User Model
 * 
 * Kullanıcı işlemleri için model fonksiyonları
 */

/**
 * ID'ye göre kullanıcı getir
 * @param int $user_id Kullanıcı ID
 * @return array|false Kullanıcı verisi
 */
function get_user_by_id($user_id) {
    return db_query_row("SELECT * FROM users WHERE id = ?", [$user_id]);
}

/**
 * Username'e göre kullanıcı getir
 * @param string $username Kullanıcı adı
 * @return array|false Kullanıcı verisi
 */
function get_user_by_username($username) {
    return db_query_row("SELECT * FROM users WHERE username = ?", [$username]);
}

/**
 * Email'e göre kullanıcı getir
 * @param string $email Email
 * @return array|false Kullanıcı verisi
 */
function get_user_by_email($email) {
    return db_query_row("SELECT * FROM users WHERE email = ?", [$email]);
}

/**
 * Yeni kullanıcı oluştur
 * @param array $data Kullanıcı verileri
 * @return int|false Yeni kullanıcı ID veya false
 */
function create_user($data) {
    // Validation
    $errors = validate_user_data($data);
    if (!empty($errors)) {
        return false;
    }
    
    // Şifreyi hash'le
    $password_hash = hash_password($data['password']);
    
    // Email doğrulama token'ı
    $verification_token = generate_token(64);
    
    $query = "INSERT INTO users (username, email, password, verification_token, created_at) 
              VALUES (?, ?, ?, ?, NOW())";
    
    $user_id = db_insert($query, [
        $data['username'],
        $data['email'],
        $password_hash,
        $verification_token
    ]);
    
    if ($user_id) {
        // Email doğrulama maili gönder
        send_verification_email($data['email'], $verification_token);
    }
    
    return $user_id;
}

/**
 * Kullanıcı verilerini validate et
 * @param array $data Kullanıcı verileri
 * @param bool $is_update Update mi? (bazı validasyonlar atlanır)
 * @return array Hata mesajları
 */
function validate_user_data($data, $is_update = false) {
    $errors = [];
    
    // Username kontrolü
    if (!$is_update || isset($data['username'])) {
        if (empty($data['username'])) {
            $errors['username'] = 'Kullanıcı adı boş olamaz.';
        } elseif (!validate_username($data['username'])) {
            $errors['username'] = 'Kullanıcı adı geçersiz. (3-20 karakter, sadece harf, rakam, - ve _)';
        } elseif (!$is_update && get_user_by_username($data['username'])) {
            $errors['username'] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }
    }
    
    // Email kontrolü
    if (!$is_update || isset($data['email'])) {
        if (empty($data['email'])) {
            $errors['email'] = 'Email boş olamaz.';
        } elseif (!validate_email($data['email'])) {
            $errors['email'] = 'Email adresi geçersiz.';
        } elseif (!$is_update && get_user_by_email($data['email'])) {
            $errors['email'] = 'Bu email adresi zaten kullanılıyor.';
        }
    }
    
    // Şifre kontrolü
    if (!$is_update || isset($data['password'])) {
        if (!$is_update && empty($data['password'])) {
            $errors['password'] = 'Şifre boş olamaz.';
        } elseif (!empty($data['password'])) {
            $password_check = validate_password_strength($data['password']);
            if (!$password_check['valid']) {
                $errors['password'] = implode(' ', $password_check['errors']);
            }
        }
    }
    
    return $errors;
}

/**
 * Kullanıcıyı güncelle
 * @param int $user_id Kullanıcı ID
 * @param array $data Güncellenecek veriler
 * @return bool Başarı durumu
 */
function update_user($user_id, $data) {
    $updates = [];
    $params = [];
    
    // Güncellenebilir alanlar
    $allowed_fields = ['username', 'email', 'avatar', 'biography'];
    
    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = ?";
            $params[] = $data[$field];
        }
    }
    
    // Şifre güncellemesi
    if (isset($data['password']) && !empty($data['password'])) {
        $updates[] = "password = ?";
        $params[] = hash_password($data['password']);
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $user_id;
    
    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    return db_execute($query, $params);
}

/**
 * Kullanıcı şifresini değiştir
 * @param int $user_id Kullanıcı ID
 * @param string $new_password Yeni şifre
 * @return bool Başarı durumu
 */
function change_user_password($user_id, $new_password) {
    $password_hash = hash_password($new_password);
    return db_execute("UPDATE users SET password = ? WHERE id = ?", [$password_hash, $user_id]);
}

/**
 * Email doğrula
 * @param string $token Doğrulama token'ı
 * @return bool Başarı durumu
 */
function verify_user_email($token) {
    $query = "UPDATE users SET email_verified = 1, verification_token = NULL 
              WHERE verification_token = ?";
    return db_execute($query, [$token]);
}

/**
 * Şifre sıfırlama token'ı oluştur
 * @param string $email Email
 * @return string|false Token veya false
 */
function create_password_reset_token($email) {
    $user = get_user_by_email($email);
    if (!$user) {
        return false;
    }
    
    $token = generate_token(64);
    $query = "UPDATE users SET reset_token = ? WHERE id = ?";
    
    if (db_execute($query, [$token, $user['id']])) {
        // Reset email'i gönder
        send_password_reset_email($email, $token);
        return $token;
    }
    
    return false;
}

/**
 * Şifre sıfırlama token'ını kontrol et
 * @param string $token Token
 * @return array|false Kullanıcı verisi veya false
 */
function verify_password_reset_token($token) {
    return db_query_row("SELECT * FROM users WHERE reset_token = ?", [$token]);
}

/**
 * Token ile şifre sıfırla
 * @param string $token Reset token
 * @param string $new_password Yeni şifre
 * @return bool Başarı durumu
 */
function reset_password_with_token($token, $new_password) {
    $user = verify_password_reset_token($token);
    if (!$user) {
        return false;
    }
    
    $password_hash = hash_password($new_password);
    $query = "UPDATE users SET password = ?, reset_token = NULL WHERE id = ?";
    return db_execute($query, [$password_hash, $user['id']]);
}

/**
 * Kullanıcının son aktivite zamanını güncelle
 * @param int $user_id Kullanıcı ID
 */
function update_user_activity($user_id) {
    db_execute("UPDATE users SET last_active = NOW() WHERE id = ?", [$user_id]);
}

/**
 * Kullanıcıyı yasakla/yasağı kaldır
 * @param int $user_id Kullanıcı ID
 * @param bool $ban_status Yasak durumu
 * @return bool Başarı durumu
 */
function ban_user($user_id, $ban_status = true) {
    $status = $ban_status ? 1 : 0;
    return db_execute("UPDATE users SET is_banned = ? WHERE id = ?", [$status, $user_id]);
}

/**
 * Kullanıcının rolünü değiştir
 * @param int $user_id Kullanıcı ID
 * @param string $role Yeni rol (user, moderator, admin)
 * @return bool Başarı durumu
 */
function change_user_role($user_id, $role) {
    $allowed_roles = ['user', 'moderator', 'admin'];
    if (!in_array($role, $allowed_roles)) {
        return false;
    }
    
    return db_execute("UPDATE users SET role = ? WHERE id = ?", [$role, $user_id]);
}

/**
 * Kullanıcının reputasyonunu güncelle
 * @param int $user_id Kullanıcı ID
 * @param int $amount Eklenecek/çıkarılacak miktar
 * @return bool Başarı durumu
 */
function update_user_reputation($user_id, $amount) {
    return db_execute("UPDATE users SET reputation = reputation + ? WHERE id = ?", [$amount, $user_id]);
}

/**
 * Kullanıcı istatistiklerini getir
 * @param int $user_id Kullanıcı ID
 * @return array İstatistikler
 */
function get_user_stats($user_id) {
    // Konu sayısı
    $topic_count = db_query_value("SELECT COUNT(*) FROM topics WHERE user_id = ?", [$user_id]);
    
    // Post sayısı
    $post_count = db_query_value("SELECT COUNT(*) FROM posts WHERE user_id = ?", [$user_id]);
    
    // Best answer sayısı
    $solution_count = db_query_value(
        "SELECT COUNT(*) FROM posts WHERE user_id = ? AND is_solution = 1", 
        [$user_id]
    );
    
    return [
        'topics' => $topic_count,
        'posts' => $post_count,
        'solutions' => $solution_count
    ];
}

/**
 * Kullanıcı seviyesini hesapla
 * @param int $reputation Reputasyon
 * @return string Seviye adı
 */
function get_user_level($reputation) {
    $levels = USER_LEVELS;
    $current_level = 'Yeni';
    
    foreach ($levels as $level => $required_reputation) {
        if ($reputation >= $required_reputation) {
            $current_level = $level;
        }
    }
    
    return $current_level;
}

/**
 * Tüm kullanıcıları listele (pagination ile)
 * @param int $page Sayfa numarası
 * @param int $per_page Sayfa başına kayıt
 * @return array Kullanıcılar
 */
function get_all_users($page = 1, $per_page = USERS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    return db_query_all(
        "SELECT id, username, email, reputation, role, created_at, last_active 
         FROM users 
         ORDER BY created_at DESC 
         LIMIT ? OFFSET ?", 
        [$per_page, $offset]
    );
}

/**
 * Toplam kullanıcı sayısı
 * @return int Kullanıcı sayısı
 */
function get_total_users() {
    return db_count('users');
}

/**
 * Email doğrulama maili gönder
 * @param string $email Email
 * @param string $token Token
 */
function send_verification_email($email, $token) {
    $verify_url = url("/dogrula/{$token}");
    
    $subject = "Email Adresinizi Doğrulayın - " . SITE_NAME;
    $message = "
    <html>
    <body>
        <h2>Hoş Geldiniz!</h2>
        <p>Email adresinizi doğrulamak için aşağıdaki linke tıklayın:</p>
        <p><a href='{$verify_url}'>{$verify_url}</a></p>
        <p>Bu işlemi siz yapmadıysanız, bu emaili görmezden gelebilirsiniz.</p>
    </body>
    </html>
    ";
    
    send_email($email, $subject, $message);
}

/**
 * Şifre sıfırlama maili gönder
 * @param string $email Email
 * @param string $token Token
 */
function send_password_reset_email($email, $token) {
    $reset_url = url("/sifre-sifirla/{$token}");
    
    $subject = "Şifre Sıfırlama - " . SITE_NAME;
    $message = "
    <html>
    <body>
        <h2>Şifre Sıfırlama</h2>
        <p>Şifrenizi sıfırlamak için aşağıdaki linke tıklayın:</p>
        <p><a href='{$reset_url}'>{$reset_url}</a></p>
        <p>Bu işlemi siz yapmadıysanız, bu emaili görmezden gelebilirsiniz.</p>
        <p>Bu link 1 saat geçerlidir.</p>
    </body>
    </html>
    ";
    
    send_email($email, $subject, $message);
}
