<?php
/**
 * Zunvo Forum Sistemi
 * Veritabanı Bağlantı Ayarları
 * 
 * Bu dosya veritabanı bağlantı bilgilerini içerir.
 * Güvenlik için bu dosyayı .gitignore'a ekleyin!
 */

// Veritabanı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'zunvo_forum');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO bağlantı seçenekleri
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // Hata yönetimi
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Varsayılan fetch modu
    PDO::ATTR_EMULATE_PREPARES => false,                    // Gerçek prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"    // Karakter seti
]);

/**
 * Veritabanı bağlantısı oluştur
 * @return PDO|null PDO instance veya hata durumunda null
 */
function create_database_connection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        return $pdo;
    } catch (PDOException $e) {
        // Üretim ortamında detaylı hata mesajı gösterme!
        if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
            die("Veritabanı Bağlantı Hatası: " . $e->getMessage());
        } else {
            die("Veritabanı bağlantısı kurulamadı. Lütfen sistem yöneticisiyle iletişime geçin.");
        }
    }
}
