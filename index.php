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

// Auth route'ları
add_route('giris', 'auth', 'login');
add_route('kayit', 'auth', 'register');
add_route('cikis', 'auth', 'logout');
add_route('dogrula/{token}', 'auth', 'verify');
add_route('sifremi-unuttum', 'auth', 'forgot_password');
add_route('sifre-sifirla/{token}', 'auth', 'reset_password');
add_route('profil/{username}', 'auth', 'profile');
add_route('profil-duzenle', 'auth', 'edit_profile');

// Topic route'ları
add_route('konular', 'topic', 'index');
add_route('konu/olustur', 'topic', 'create');
add_route('konu/{slug}', 'topic', 'show');
add_route('konu/duzenle/{slug}', 'topic', 'edit');
add_route('kategori/{slug}', 'topic', 'category');
add_route('arama', 'topic', 'search');

// AJAX route'ları
add_route('topic/add-post', 'topic', 'add_post');
add_route('topic/edit-post', 'topic', 'edit_post');
add_route('topic/delete-post', 'topic', 'delete_post');
add_route('topic/mark-solution', 'topic', 'mark_solution');

// Vote route'ları
add_route('vote/submit', 'vote', 'submit');
add_route('vote/get', 'vote', 'get');
add_route('vote/stats', 'vote', 'stats');
add_route('vote/batch', 'vote', 'batch');

// Notification route'ları
add_route('notification/get', 'notification', 'get');
add_route('notification/unread-count', 'notification', 'unread_count');
add_route('notification/mark-read', 'notification', 'mark_read');
add_route('notification/mark-all-read', 'notification', 'mark_all_read');
add_route('notification/delete', 'notification', 'delete');
add_route('notification/poll', 'notification', 'poll');
add_route('bildirimler', 'notification', 'index');
add_route('bildirim/ayarlar', 'notification', 'settings');

// Route'u işle ve ilgili controller'ı çalıştır
handle_route();