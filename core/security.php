<?php
/**
 * Zunvo Forum Sistemi
 * Güvenlik Fonksiyonları
 * 
 * CSRF, XSS, SQL Injection ve diğer güvenlik önlemleri
 */

/**
 * CSRF token oluştur ve session'a kaydet
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token'ı doğrula
 * @param string $token Gelen token
 * @return bool Geçerli mi?
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Token zaman aşımı kontrolü
    if (isset($_SESSION['csrf_token_time']) && 
        (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME)) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF token input field'ı oluştur
 * @return string HTML input
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

/**
 * Password hash'le (bcrypt)
 * @param string $password Plain password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Password doğrula
 * @param string $password Plain password
 * @param string $hash Hashed password
 * @return bool Eşleşiyor mu?
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Email adresini doğrula
 * @param string $email Email adresi
 * @return bool Geçerli mi?
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Kullanıcı adını doğrula
 * @param string $username Kullanıcı adı
 * @return bool Geçerli mi?
 */
function validate_username($username) {
    // 3-20 karakter, sadece harf, rakam, alt çizgi ve tire
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username) === 1;
}

/**
 * Şifre güçlülüğünü kontrol et
 * @param string $password Şifre
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Şifre en az " . PASSWORD_MIN_LENGTH . " karakter olmalıdır.";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Şifre en az bir büyük harf içermelidir.";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Şifre en az bir küçük harf içermelidir.";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Şifre en az bir rakam içermelidir.";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Rate limiting kontrolü (başarısız giriş denemeleri için)
 * @param string $identifier IP veya username
 * @param string $type Olay tipi (login, register vb.)
 * @return bool İzin var mı?
 */
function check_rate_limit($identifier, $type = 'login') {
    $key = 'rate_limit_' . $type . '_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time()
        ];
    }
    
    $rate_data = $_SESSION[$key];
    
    // Zaman aşımı kontrolü
    if (time() - $rate_data['first_attempt'] > LOGIN_LOCKOUT_TIME) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time()
        ];
        return true;
    }
    
    // Deneme sayısı kontrolü
    if ($rate_data['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        return false;
    }
    
    return true;
}

/**
 * Rate limit denemesini kaydet
 * @param string $identifier IP veya username
 * @param string $type Olay tipi
 */
function record_rate_limit_attempt($identifier, $type = 'login') {
    $key = 'rate_limit_' . $type . '_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time()
        ];
    }
    
    $_SESSION[$key]['attempts']++;
}

/**
 * Rate limit'i sıfırla (başarılı giriş sonrası)
 * @param string $identifier IP veya username
 * @param string $type Olay tipi
 */
function reset_rate_limit($identifier, $type = 'login') {
    $key = 'rate_limit_' . $type . '_' . md5($identifier);
    unset($_SESSION[$key]);
}

/**
 * Kullanıcının IP adresini al
 * @return string IP adresi
 */
function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
            
            // Virgülle ayrılmış IP'ler (proxy)
            if (strpos($ip, ',') !== false) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * User agent al
 * @return string User agent
 */
function get_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Dosya uzantısını kontrol et
 * @param string $filename Dosya adı
 * @param array $allowed_extensions İzin verilen uzantılar
 * @return bool İzinli mi?
 */
function validate_file_extension($filename, $allowed_extensions) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowed_extensions);
}

/**
 * Dosya MIME type kontrolü
 * @param string $file_path Dosya yolu
 * @param array $allowed_mime_types İzin verilen MIME type'lar
 * @return bool İzinli mi?
 */
function validate_file_mime($file_path, $allowed_mime_types) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    return in_array($mime_type, $allowed_mime_types);
}

/**
 * Güvenli dosya adı oluştur
 * @param string $filename Orijinal dosya adı
 * @return string Güvenli dosya adı
 */
function sanitize_filename($filename) {
    // Uzantıyı ayır
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = pathinfo($filename, PATHINFO_FILENAME);
    
    // Güvenli hale getir
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
    $basename = substr($basename, 0, 50); // Maksimum 50 karakter
    
    // Unique yap
    $unique = substr(md5(uniqid()), 0, 8);
    
    return $basename . '_' . $unique . '.' . $extension;
}

/**
 * HTML içeriğini temizle (XSS koruması)
 * @param string $html HTML içerik
 * @return string Temizlenmiş HTML
 */
function sanitize_html($html) {
    // İzin verilen etiketler
    $allowed_tags = '<p><br><strong><b><em><i><u><ul><ol><li><a><blockquote><code><pre><h3><h4>';
    
    // Etiketleri temizle
    $html = strip_tags($html, $allowed_tags);
    
    // Tehlikeli attribute'ları kaldır
    $html = preg_replace('/<(\w+)[^>]*\s(on\w+)\s*=\s*["\']?[^"\']*["\']?/i', '<$1', $html);
    
    // Script etiketlerini kaldır
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    
    return $html;
}

/**
 * SQL Injection karakterlerini kontrol et (ekstra güvenlik)
 * @param string $string Kontrol edilecek string
 * @return bool Güvenli mi?
 */
function is_sql_safe($string) {
    $dangerous_patterns = [
        '/(\s|^)(DROP|DELETE|TRUNCATE|ALTER|CREATE|INSERT|UPDATE)\s/i',
        '/--/',
        '/\/\*.*\*\//',
        '/\bOR\b.*=.*=/i',
        '/\bAND\b.*=.*=/i'
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $string)) {
            return false;
        }
    }
    
    return true;
}

/**
 * XSS saldırılarını engelle
 * @param mixed $data Veri (string, array, object)
 * @return mixed Temizlenmiş veri
 */
function prevent_xss($data) {
    if (is_array($data)) {
        return array_map('prevent_xss', $data);
    }
    
    if (is_string($data)) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Session hijacking koruması
 */
function prevent_session_hijacking() {
    // IP ve User Agent kontrolü
    if (!isset($_SESSION['user_ip'])) {
        $_SESSION['user_ip'] = get_client_ip();
        $_SESSION['user_agent'] = get_user_agent();
    }
    
    // IP değişti mi?
    if ($_SESSION['user_ip'] !== get_client_ip()) {
        session_destroy();
        redirect(url('/giris'));
    }
    
    // User Agent değişti mi?
    if ($_SESSION['user_agent'] !== get_user_agent()) {
        session_destroy();
        redirect(url('/giris'));
    }
}

/**
 * Session'ı yenile (güvenlik için)
 */
function regenerate_session() {
    session_regenerate_id(true);
}
