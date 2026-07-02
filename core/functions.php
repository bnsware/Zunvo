<?php
/**
 * Zunvo Forum Sistemi
 * Genel Yardımcı Fonksiyonlar
 * 
 * Sistemde kullanılan yardımcı fonksiyonlar
 */

/**
 * XSS koruması için HTML escape
 * @param string $string Escape edilecek string
 * @return string Güvenli string
 */
function escape($string) {
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect fonksiyonu
 * @param string $url Yönlendirilecek URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function is_installed() {
    return file_exists(STORAGE_PATH . '/install.lock');
}

function redirect_to_install_if_needed() {
    if (is_installed()) {
        return;
    }
    header('Location: ' . rtrim(SITE_URL, '/') . '/install.php');
    exit;
}

function get_request_uri_path() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uri = strtok($uri, '?');
    $uri = str_replace('\\', '/', $uri);
    $base = BASE_PATH;
    if ($base !== '' && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }
    $uri = trim($uri, '/');
    if ($uri === 'index.php') {
        return '';
    }
    if (strpos($uri, 'index.php/') === 0) {
        $uri = substr($uri, strlen('index.php/'));
    }
    return trim($uri, '/');
}

function redirect_index_php_to_root() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $query = $_SERVER['QUERY_STRING'] ?? '';
    $path = strtok($uri, '?');
    $path = str_replace('\\', '/', $path);
    if (BASE_PATH !== '' && strpos($path, BASE_PATH) === 0) {
        $path = substr($path, strlen(BASE_PATH));
    }
    $path = trim($path, '/');
    if ($path !== 'index.php' && strpos($path, 'index.php/') !== 0) {
        return;
    }
    $target = rtrim(SITE_URL, '/');
    if (strpos($path, 'index.php/') === 0) {
        $target .= '/' . substr($path, strlen('index.php/'));
    }
    if ($query !== '') {
        $target .= '?' . $query;
    }
    header('Location: ' . $target, true, 301);
    exit;
}

function url($path = '') {
    $path = ltrim($path, '/');
    return $path === '' ? rtrim(SITE_URL, '/') : rtrim(SITE_URL, '/') . '/' . $path;
}

/**
 * Asset URL oluştur
 * @param string $path Asset path
 * @return string Asset URL
 */
function asset_mtime($file_path) {
    if (!is_string($file_path) || $file_path === '' || !is_file($file_path)) {
        return null;
    }
    return filemtime($file_path);
}

function versioned_url($url, $file_path) {
    $mtime = asset_mtime($file_path);
    if ($mtime === null) {
        return $url;
    }
    return $url . (strpos($url, '?') !== false ? '&' : '?') . 'v=' . $mtime;
}

function asset($path) {
    $path = ltrim($path, '/');
    $file = PUBLIC_PATH . '/' . $path;
    return versioned_url(url('public/' . $path), $file);
}

function static_file_url($url_path, $file_path) {
    return versioned_url(url(ltrim($url_path, '/')), $file_path);
}

/**
 * Slug oluştur (SEO friendly URL)
 * @param string $string String
 * @return string Slug
 */
function create_slug($string) {
    // Türkçe karakterleri değiştir
    $turkish = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
    $english = ['s', 's', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];
    $string = str_replace($turkish, $english, $string);
    
    // Küçük harfe çevir
    $string = strtolower($string);
    
    // Alfanumerik olmayan karakterleri tire ile değiştir
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    
    // Baştaki ve sondaki tireleri temizle
    $string = trim($string, '-');
    
    return $string;
}

/**
 * Rastgele token oluştur
 * @param int $length Token uzunluğu
 * @return string Token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Tarih formatla
 * @param string $date Tarih
 * @param string $format Format
 * @return string Formatlanmış tarih
 */
function format_date($date, $format = 'd.m.Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Göreli tarih (X dakika önce, Y saat önce)
 * @param string $date Tarih
 * @return string Göreli tarih
 */
function time_ago($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Az önce';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' dakika önce';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' saat önce';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' gün önce';
    } else {
        return format_date($date);
    }
}

/**
 * Metni kısalt
 * @param string $text Metin
 * @param int $limit Karakter limiti
 * @param string $suffix Sonuna eklenecek
 * @return string Kısaltılmış metin
 */
function truncate($text, $limit = 100, $suffix = '...') {
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . $suffix;
}

/**
 * Sayıyı formatla (1000 -> 1K, 1000000 -> 1M)
 * @param int $number Sayı
 * @return string Formatlanmış sayı
 */
function format_number($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return $number;
}

/**
 * Flash mesaj ayarla
 * @param string $type Mesaj tipi (success, error, warning, info)
 * @param string $message Mesaj
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Flash mesajı al ve temizle
 * @return array|null Flash mesaj
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Flash mesajı HTML olarak göster
 * @return string HTML
 */
function display_flash() {
    $flash = get_flash();
    if ($flash) {
        $type_class = 'flash-' . ($flash['type'] ?? 'info');
        return '<div class="flash-message ' . escape($type_class) . '">' .
               escape($flash['message']) .
               '</div>';
    }
    return '';
}

/**
 * Mevcut kullanıcıyı al
 * @return array|null Kullanıcı verisi
 */
function current_user() {
    if (!file_exists(STORAGE_PATH . '/install.lock')) {
        return null;
    }
    if (isset($_SESSION['user_id'])) {
        return db_query_row("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    return null;
}

/**
 * Kullanıcı giriş yapmış mı?
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Kullanıcı admin mi?
 * @return bool
 */
function is_admin() {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

/**
 * Kullanıcı moderatör mü?
 * @return bool
 */
function is_moderator() {
    $user = current_user();
    return $user && ($user['role'] === 'moderator' || $user['role'] === 'admin');
}

/**
 * Giriş kontrolü (redirect ile)
 * @param string $redirect_url Yönlendirilecek URL
 */
function require_login($redirect_url = '/giris') {
    if (!is_logged_in()) {
        set_flash('error', 'Bu sayfaya erişmek için giriş yapmalısınız.');
        redirect(url($redirect_url));
    }
}

/**
 * Admin kontrolü (redirect ile)
 * @param string $redirect_url Yönlendirilecek URL
 */
function require_admin($redirect_url = '/') {
    if (!is_admin()) {
        set_flash('error', 'Bu sayfaya erişim yetkiniz yok.');
        redirect(url($redirect_url));
    }
}

function require_moderator($redirect_url = '/') {
    if (!is_moderator()) {
        set_flash('error', 'Bu sayfaya erişim yetkiniz yok.');
        redirect(url($redirect_url));
    }
}

function try_remember_login() {
    if (!file_exists(STORAGE_PATH . '/install.lock')) {
        return;
    }
    if (is_logged_in() || !isset($_COOKIE['remember'])) {
        return;
    }
    $parts = explode(':', $_COOKIE['remember'], 2);
    if (count($parts) !== 2) {
        return;
    }
    $row = db_query_row(
        "SELECT rt.*, u.username, u.role FROM remember_tokens rt JOIN users u ON rt.user_id = u.id WHERE rt.selector = ? AND rt.expires_at > NOW() AND u.is_banned = 0",
        [$parts[0]]
    );
    if (!$row || !hash_equals($row['token_hash'], hash('sha256', $parts[1]))) {
        setcookie('remember', '', time() - 3600, BASE_PATH === '' ? '/' : BASE_PATH . '/', '', IS_HTTPS, true);
        return;
    }
    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    $_SESSION['user_ip'] = get_client_ip();
    $_SESSION['user_agent'] = get_user_agent();
    db_execute("UPDATE users SET last_active = NOW() WHERE id = ?", [$row['user_id']]);
}

function send_email($to, $subject, $message) {
    $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Sayfalama bilgilerini hesapla
 * @param int $total_items Toplam öğe sayısı
 * @param int $items_per_page Sayfa başına öğe
 * @param int $current_page Mevcut sayfa
 * @return array Sayfalama bilgileri
 */
function get_pagination($total_items, $items_per_page, $current_page = 1) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Site ayarı al
 * @param string $key Ayar anahtarı
 * @param mixed $default Varsayılan değer
 * @return mixed Ayar değeri
 */
function get_setting($key, $default = null) {
    if (!file_exists(STORAGE_PATH . '/install.lock')) {
        return $default;
    }
    if (function_exists('cache_get')) {
        $cached = cache_get('setting_' . $key);
        if ($cached !== null) {
            return $cached;
        }
    }
    $result = db_query_row("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $value = $result ? $result['setting_value'] : $default;
    if (function_exists('cache_set')) {
        cache_set('setting_' . $key, $value, 300);
    }
    return $value;
}

/**
 * Site ayarı kaydet
 * @param string $key Ayar anahtarı
 * @param mixed $value Ayar değeri
 * @return bool
 */
function set_setting($key, $value) {
    if (function_exists('cache_delete')) {
        cache_delete('setting_' . $key);
    }
    $exists = db_query_row("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    
    if ($exists) {
        return db_execute("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
    } else {
        return db_execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
    }
}
