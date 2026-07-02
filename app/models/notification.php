<?php

require_once __DIR__ . '/user.php';

function get_post_notification_link($post_id) {
    $row = db_query_row(
        "SELECT t.slug FROM posts p JOIN topics t ON p.topic_id = t.id WHERE p.id = ?",
        [$post_id]
    );
    return $row ? "/konu/{$row['slug']}#post-{$post_id}" : '/';
}

function user_wants_notification($user_id, $type) {
    $type_map = [
        'mention' => 'mention_notifications',
        'reply' => 'reply_notifications',
        'upvote' => 'upvote_notifications',
        'solution_marked' => 'solution_notifications',
    ];
    if (!isset($type_map[$type])) {
        return true;
    }
    $prefs_json = get_setting('notification_prefs_' . $user_id, '{}');
    $prefs = json_decode($prefs_json, true) ?: [];
    $key = $type_map[$type];
    return !isset($prefs[$key]) || $prefs[$key] !== false;
}

function create_notification($user_id, $type, $message, $link = null) {
    if (!user_wants_notification($user_id, $type)) {
        return false;
    }
    $query = "INSERT INTO notifications (user_id, type, message, link, created_at) 
              VALUES (?, ?, ?, ?, NOW())";
    return db_insert($query, [$user_id, $type, $message, $link]);
}

/**
 * Kullanıcının bildirimlerini al
 * @param int $user_id Kullanıcı ID
 * @param bool $unread_only Sadece okunmamışlar mı?
 * @param int $limit Limit
 * @return array Bildirimler
 */
function get_user_notifications($user_id, $unread_only = false, $limit = 50) {
    $where = 'user_id = ?';
    $params = [$user_id];
    
    if ($unread_only) {
        $where .= ' AND is_read = 0';
    }
    
    $params[] = $limit;
    
    return db_query_all(
        "SELECT * FROM notifications 
         WHERE {$where} 
         ORDER BY created_at DESC 
         LIMIT ?",
        $params
    );
}

/**
 * Bildirimi okundu olarak işaretle
 * @param int $notification_id Bildirim ID
 * @param int $user_id Kullanıcı ID (güvenlik için)
 * @return bool
 */
function mark_notification_read($notification_id, $user_id) {
    return db_execute(
        "UPDATE notifications SET is_read = 1 
         WHERE id = ? AND user_id = ?",
        [$notification_id, $user_id]
    );
}

/**
 * Tüm bildirimleri okundu işaretle
 * @param int $user_id Kullanıcı ID
 * @return bool
 */
function mark_all_notifications_read($user_id) {
    return db_execute(
        "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
        [$user_id]
    );
}

/**
 * Okunmamış bildirim sayısı
 * @param int $user_id Kullanıcı ID
 * @return int
 */
function get_unread_notification_count($user_id) {
    return db_count('notifications', 'user_id = ? AND is_read = 0', [$user_id]);
}

/**
 * Bildirimi sil
 * @param int $notification_id Bildirim ID
 * @param int $user_id Kullanıcı ID (güvenlik için)
 * @return bool
 */
function delete_notification($notification_id, $user_id) {
    return db_execute(
        "DELETE FROM notifications WHERE id = ? AND user_id = ?",
        [$notification_id, $user_id]
    );
}

/**
 * Eski bildirimleri temizle (30 günden eski okunmuş bildirimler)
 * @return int Silinen bildirim sayısı
 */
function cleanup_old_notifications() {
    $query = "DELETE FROM notifications 
              WHERE is_read = 1 
              AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    db_execute($query);
    
    // Silinen satır sayısını döndür
    return db_query_value("SELECT ROW_COUNT()") ?: 0;
}

/**
 * Bildirim tipine göre filtrele
 * @param int $user_id Kullanıcı ID
 * @param string $type Bildirim tipi
 * @param int $limit Limit
 * @return array
 */
function get_notifications_by_type($user_id, $type, $limit = 20) {
    return db_query_all(
        "SELECT * FROM notifications 
         WHERE user_id = ? AND type = ? 
         ORDER BY created_at DESC 
         LIMIT ?",
        [$user_id, $type, $limit]
    );
}

/**
 * Toplu bildirim oluştur (aynı mesajı birden fazla kullanıcıya)
 * @param array $user_ids Kullanıcı ID'leri
 * @param string $type Bildirim tipi
 * @param string $message Mesaj
 * @param string $link Link
 * @return bool
 */
function create_bulk_notifications($user_ids, $type, $message, $link = null) {
    if (empty($user_ids)) {
        return false;
    }
    
    db_begin_transaction();
    
    try {
        foreach ($user_ids as $user_id) {
            create_notification($user_id, $type, $message, $link);
        }
        
        db_commit();
        return true;
    } catch (Exception $e) {
        db_rollback();
        log_error("Bulk notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Bildirim istatistikleri
 * @param int $user_id Kullanıcı ID
 * @return array
 */
function get_notification_stats($user_id) {
    $total = db_count('notifications', 'user_id = ?', [$user_id]);
    $unread = db_count('notifications', 'user_id = ? AND is_read = 0', [$user_id]);
    
    // Tiplere göre dağılım
    $types = db_query_all(
        "SELECT type, COUNT(*) as count 
         FROM notifications 
         WHERE user_id = ? 
         GROUP BY type",
        [$user_id]
    );
    
    return [
        'total' => $total,
        'unread' => $unread,
        'read' => $total - $unread,
        'by_type' => $types
    ];
}

/**
 * Son bildirimleri HTML olarak formatla
 * @param int $user_id Kullanıcı ID
 * @param int $limit Limit
 * @return string HTML
 */
function render_recent_notifications($user_id, $limit = 10) {
    $notifications = get_user_notifications($user_id, false, $limit);
    
    if (empty($notifications)) {
        return '<div class="notification-empty">Henüz bildirim yok</div>';
    }
    
    $html = '';
    foreach ($notifications as $notif) {
        $icon = icon(notification_icon_name($notif['type']), 'icon');
        $time = time_ago($notif['created_at']);
        $read_class = $notif['is_read'] ? 'read' : 'unread';
        
        $html .= '<div class="notification-item ' . $read_class . '" data-id="' . $notif['id'] . '">';
        $html .= '<div class="notification-icon">' . $icon . '</div>';
        $html .= '<div class="notification-content">';
        
        if ($notif['link']) {
            $html .= '<a href="' . url($notif['link']) . '">' . escape($notif['message']) . '</a>';
        } else {
            $html .= '<span>' . escape($notif['message']) . '</span>';
        }
        
        $html .= '<div class="notification-time">' . $time . '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return $html;
}

/**
 * Bildirim tipi için ikon al
 * @param string $type Bildirim tipi
 * @return string Icon emoji
 */
function get_notification_icon($type) {
    return notification_icon_name($type);
}

/**
 * Kullanıcıyı mention et ve bildirim oluştur
 * @param string $content İçerik (post/reply content)
 * @param int $post_id Post ID
 * @param int $sender_id Gönderen kullanıcı ID
 */
function process_new_mentions($old_content, $new_content, $post_id, $sender_id) {
    preg_match_all('/@([a-zA-Z0-9_-]{3,20})/', $old_content ?? '', $old_matches);
    preg_match_all('/@([a-zA-Z0-9_-]{3,20})/', $new_content ?? '', $new_matches);
    $old_usernames = array_flip(array_unique($old_matches[1] ?? []));
    $new_usernames = array_unique($new_matches[1] ?? []);
    if (empty($new_usernames)) {
        return;
    }
    $sender = get_user_by_id($sender_id);
    foreach ($new_usernames as $username) {
        if (isset($old_usernames[$username])) {
            continue;
        }
        $mentioned_user = get_user_by_username($username);
        if ($mentioned_user && $mentioned_user['id'] !== $sender_id) {
            create_notification(
                $mentioned_user['id'],
                'mention',
                "{$sender['username']} sizi bir gönderide bahsetti",
                get_post_notification_link($post_id)
            );
        }
    }
}

function process_mentions($content, $post_id, $sender_id) {
    // @username formatındaki mention'ları bul
    preg_match_all('/@([a-zA-Z0-9_-]{3,20})/', $content, $matches);
    
    if (empty($matches[1])) {
        return;
    }
    
    $mentioned_usernames = array_unique($matches[1]);
    $sender = get_user_by_id($sender_id);
    
    foreach ($mentioned_usernames as $username) {
        $mentioned_user = get_user_by_username($username);
        
        // Kullanıcı var mı ve kendine mention değil mi?
        if ($mentioned_user && $mentioned_user['id'] !== $sender_id) {
            create_notification(
                $mentioned_user['id'],
                'mention',
                "{$sender['username']} sizi bir gönderide bahsetti",
                get_post_notification_link($post_id)
            );
        }
    }
}

/**
 * Konu sahibine yeni yorum bildirimi gönder
 * @param int $topic_id Konu ID
 * @param int $post_id Post ID
 * @param int $commenter_id Yorum yapan kullanıcı ID
 */
function notify_topic_author($topic_id, $post_id, $commenter_id) {
    $topic = db_query_row("SELECT user_id, title, slug FROM topics WHERE id = ?", [$topic_id]);
    if (!$topic || $topic['user_id'] == $commenter_id) {
        return;
    }
    $commenter = get_user_by_id($commenter_id);
    create_notification(
        $topic['user_id'],
        'reply',
        "{$commenter['username']} konunuza yanıt verdi: {$topic['title']}",
        "/konu/{$topic['slug']}#post-{$post_id}"
    );
}

/**
 * Upvote bildirimi gönder
 * @param int $post_id Post ID
 * @param int $voter_id Oy veren kullanıcı ID
 */
function notify_upvote($post_id, $voter_id) {
    $post = db_query_row("SELECT user_id FROM posts WHERE id = ?", [$post_id]);
    if (!$post || $post['user_id'] == $voter_id) {
        return;
    }
    $voter = get_user_by_id($voter_id);
    create_notification(
        $post['user_id'],
        'upvote',
        "{$voter['username']} gönderinizi beğendi",
        get_post_notification_link($post_id)
    );
}

/**
 * Çözüm seçildiğinde bildirim gönder
 * @param int $post_id Post ID
 * @param int $topic_id Konu ID
 */
function notify_solution_marked($post_id, $topic_id) {
    $post = db_query_row("SELECT user_id FROM posts WHERE id = ?", [$post_id]);
    $topic = db_query_row("SELECT title, slug FROM topics WHERE id = ?", [$topic_id]);
    if (!$post || !$topic) {
        return;
    }
    create_notification(
        $post['user_id'],
        'solution_marked',
        "Gönderiniz \"{$topic['title']}\" konusunda çözüm olarak işaretlendi",
        "/konu/{$topic['slug']}#post-{$post_id}"
    );
}

/**
 * Sistem bildirimi gönder (tüm kullanıcılara)
 * @param string $message Mesaj
 * @param string $link Link (opsiyonel)
 * @return bool
 */
function send_system_notification($message, $link = null) {
    // Tüm aktif kullanıcıların ID'lerini al
    $user_ids = db_query_all(
        "SELECT id FROM users WHERE is_banned = 0",
        []
    );
    
    $ids = array_column($user_ids, 'id');
    
    return create_bulk_notifications($ids, 'system', $message, $link);
}