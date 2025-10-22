<?php
/**
 * Zunvo Forum Sistemi
 * Notification Controller
 * 
 * AJAX ile bildirim işlemleri
 */

// Model dosyalarını dahil et
require_once APP_PATH . '/models/notification.php';

/**
 * Bildirimleri al (AJAX)
 */
function notification_get() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        error_response('Giriş yapmalısınız', 401);
        return;
    }
    
    $user = current_user();
    $unread_only = get_param('unread') === '1';
    $limit = min(50, max(1, (int)get_param('limit', 20)));
    
    $notifications = get_user_notifications($user['id'], $unread_only, $limit);
    
    // Zaman formatını düzenle
    foreach ($notifications as &$notif) {
        $notif['time_ago'] = time_ago($notif['created_at']);
        $notif['icon'] = get_notification_icon($notif['type']);
    }
    
    success_response([
        'notifications' => $notifications,
        'count' => count($notifications)
    ]);
}

/**
 * Okunmamış bildirim sayısı (AJAX)
 */
function notification_unread_count() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        json_response(['count' => 0]);
        return;
    }
    
    $user = current_user();
    $count = get_unread_notification_count($user['id']);
    
    success_response(['count' => $count]);
}

/**
 * Bildirimi okundu işaretle (AJAX)
 */
function notification_mark_read() {
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        error_response('Giriş yapmalısınız', 401);
        return;
    }
    
    $notification_id = (int)post_param('notification_id');
    
    if (empty($notification_id)) {
        error_response('Bildirim ID gerekli');
        return;
    }
    
    $user = current_user();
    
    if (mark_notification_read($notification_id, $user['id'])) {
        success_response(null, 'Bildirim okundu olarak işaretlendi');
    } else {
        error_response('İşlem başarısız');
    }
}

/**
 * Tüm bildirimleri okundu işaretle (AJAX)
 */
function notification_mark_all_read() {
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        error_response('Giriş yapmalısınız', 401);
        return;
    }
    
    $user = current_user();
    
    if (mark_all_notifications_read($user['id'])) {
        success_response(null, 'Tüm bildirimler okundu olarak işaretlendi');
    } else {
        error_response('İşlem başarısız');
    }
}

/**
 * Bildirimi sil (AJAX)
 */
function notification_delete() {
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        error_response('Giriş yapmalısınız', 401);
        return;
    }
    
    $notification_id = (int)post_param('notification_id');
    
    if (empty($notification_id)) {
        error_response('Bildirim ID gerekli');
        return;
    }
    
    $user = current_user();
    
    if (delete_notification($notification_id, $user['id'])) {
        success_response(null, 'Bildirim silindi');
    } else {
        error_response('İşlem başarısız');
    }
}

/**
 * Bildirim sayfası (tüm bildirimler listesi)
 */
function notification_index() {
    require_login();
    
    $user = current_user();
    $page = max(1, (int)get_param('page', 1));
    $per_page = 30;
    
    // Tüm bildirimleri al
    $notifications = get_user_notifications($user['id'], false, 1000);
    
    // Manuel pagination (basit)
    $total = count($notifications);
    $offset = ($page - 1) * $per_page;
    $notifications = array_slice($notifications, $offset, $per_page);
    
    $pagination = get_pagination($total, $per_page, $page);
    
    // İstatistikler
    $stats = get_notification_stats($user['id']);
    
    render('notification/index', [
        'title' => 'Bildirimler',
        'notifications' => $notifications,
        'pagination' => $pagination,
        'stats' => $stats
    ]);
}

/**
 * Bildirim ayarları sayfası
 */
function notification_settings() {
    require_login();
    
    $user = current_user();
    
    if (is_post()) {
        // Bildirim tercihlerini kaydet
        $preferences = [
            'email_notifications' => post_param('email_notifications') === '1',
            'mention_notifications' => post_param('mention_notifications') === '1',
            'reply_notifications' => post_param('reply_notifications') === '1',
            'upvote_notifications' => post_param('upvote_notifications') === '1',
            'solution_notifications' => post_param('solution_notifications') === '1'
        ];
        
        // JSON olarak kaydet (basit)
        $json = json_encode($preferences);
        
        // User tablosunda notification_preferences sütunu yoksa, settings tablosuna kaydet
        set_setting('notification_prefs_' . $user['id'], $json);
        
        set_flash('success', 'Bildirim ayarları güncellendi');
        redirect(url('/bildirim/ayarlar'));
        return;
    }
    
    // Mevcut ayarları al
    $prefs_json = get_setting('notification_prefs_' . $user['id'], '{}');
    $preferences = json_decode($prefs_json, true) ?: [];
    
    render('notification/settings', [
        'title' => 'Bildirim Ayarları',
        'preferences' => $preferences
    ]);
}

/**
 * Test bildirimi gönder (development için)
 */
function notification_test() {
    if (!DEBUG_MODE) {
        die('Bu özellik sadece development modda kullanılabilir');
    }
    
    require_login();
    
    $user = current_user();
    
    // Test bildirimleri oluştur
    create_notification(
        $user['id'],
        'mention',
        'Test mention bildirimi @' . $user['username'],
        '/test'
    );
    
    create_notification(
        $user['id'],
        'upvote',
        'Test upvote bildirimi - Gönderiniz beğenildi',
        '/test'
    );
    
    create_notification(
        $user['id'],
        'solution_marked',
        'Test solution bildirimi - Gönderiniz çözüm olarak işaretlendi',
        '/test'
    );
    
    set_flash('success', '3 test bildirimi oluşturuldu!');
    redirect(url('/bildirimler'));
}

/**
 * Eski bildirimleri temizle (cron job olarak çalıştırılabilir)
 */
function notification_cleanup() {
    // Sadece admin veya cron job çalıştırabilir
    if (!is_logged_in() || !is_admin()) {
        // Cron job için özel bir token kontrolü eklenebilir
        die('Yetkisiz erişim');
    }
    
    $deleted = cleanup_old_notifications();
    
    if (is_ajax()) {
        success_response([
            'deleted_count' => $deleted
        ], "{$deleted} eski bildirim temizlendi");
    } else {
        set_flash('success', "{$deleted} eski bildirim temizlendi");
        redirect(url('/admin'));
    }
}

/**
 * Gerçek zamanlı bildirim polling endpoint (AJAX)
 * Her 30 saniyede bir çağrılır
 */
function notification_poll() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        json_response([
            'logged_in' => false,
            'count' => 0,
            'notifications' => []
        ]);
        return;
    }
    
    $user = current_user();
    $last_check = get_param('last_check'); // ISO timestamp
    
    $where = 'user_id = ? AND is_read = 0';
    $params = [$user['id']];
    
    // Son kontrol zamanından sonraki bildirimleri al
    if ($last_check) {
        $where .= ' AND created_at > ?';
        $params[] = $last_check;
    }
    
    $new_notifications = db_query_all(
        "SELECT * FROM notifications 
         WHERE {$where} 
         ORDER BY created_at DESC 
         LIMIT 10",
        $params
    );
    
    // Format
    foreach ($new_notifications as &$notif) {
        $notif['time_ago'] = time_ago($notif['created_at']);
        $notif['icon'] = get_notification_icon($notif['type']);
    }
    
    $total_unread = get_unread_notification_count($user['id']);
    
    success_response([
        'logged_in' => true,
        'count' => $total_unread,
        'new_notifications' => $new_notifications,
        'has_new' => count($new_notifications) > 0
    ]);
}