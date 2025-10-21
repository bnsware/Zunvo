<?php
/**
 * Zunvo Forum Sistemi
 * Ana Giriş Noktası (Front Controller)
 * 
 * Tüm istekler bu dosyadan geçer
 */

// Hata raporlamayı başlat
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Config dosyalarını yükle
require_once __DIR__ . '/config/config.php';

// Core dosyaları yükle
require_once CORE_PATH . '/database.php';
require_once CORE_PATH . '/functions.php';
require_once CORE_PATH . '/security.php';
require_once CORE_PATH . '/router.php';

// Session hijacking korumasını başlat
if (is_logged_in()) {
    prevent_session_hijacking();
}

// Özel route'ları tanımla (opsiyonel)
// Örnek: add_route('/konu/{slug}', 'topic', 'show');

// Route'u işle ve ilgili controller'ı çalıştır
handle_route();
