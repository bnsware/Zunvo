<?php
/**
 * Zunvo Forum Sistemi
 * Notification Controller
 * 
 * AJAX ile bildirim işlemleri
 */

// Direct access engelle
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

/**
 * Kullanıcının bildirimlerini getir (AJAX)
 */
function notification_get() {
    header('Content-Type: application/json');
    
    // Login kontrolü
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız']);
        exit;
    }
    
    $user_id = current_user_id();
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    try {
        // Bildirimleri getir (en yeni önce)
        $notifications = db_query_all(
            "SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$user_id, $limit, $offset]
        );
        
        // Okunmamış bildirim sayısı
        $unread_count = db_count(
            'notifications',
            'user_id = ? AND is_read = 0',
            [$user_id]
        );
        
        // Bildirimleri formatla
        $formatted = [];
        foreach ($notifications as $notif) {
            $formatted[] = [
                'id' => $notif['id'],
                'type' => $notif['type'],
                'message' => $notif['message'],
                'link' => $notif['link'],
                'is_read' => (bool)$notif['is_read'],
                'created_at' => $notif['created_at'],
                'time_ago' => time_ago($notif['created_at'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'notifications' => $formatted,
            'unread_count' => $unread_count
        ]);
        
    } catch (Exception $e) {
        log_error("Notification get error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Bildirimler yüklenemedi']);
    }
    
    exit;
}

/**
 * Bildirimi okundu olarak işaretle (AJAX)
 */
function notification_mark_read() {
    header('Content-Type: application/json');
    
    // Login kontrolü
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız']);
        exit;
    }
    
    // POST kontrolü
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit;
    }
    
    // CSRF kontrolü
    $data = json_decode(file_get_contents('php://input'), true);
    if (!validate_csrf_token($data['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    $notification_id = $data['notification_id'] ?? 0;
    $user_id = current_user_id();
    
    if (!$notification_id) {
        echo json_encode(['success' => false, 'error' => 'Geçersiz bildirim ID']);
        exit;
    }
    
    try {
        // Bildirimin kullanıcıya ait olduğunu doğrula
        $notification = db_query_row(
            "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
            [$notification_id, $user_id]
        );
        
        if (!$notification) {
            echo json_encode(['success' => false, 'error' => 'Bildirim bulunamadı']);
            exit;
        }
        
        // Okundu olarak işaretle
        db_execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ?",
            [$notification_id]
        );
        
        // Güncel okunmamış sayısı
        $unread_count = db_count(
            'notifications',
            'user_id = ? AND is_read = 0',
            [$user_id]
        );
        
        echo json_encode([
            'success' => true,
            'unread_count' => $unread_count
        ]);
        
    } catch (Exception $e) {
        log_error("Notification mark read error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'İşlem başarısız']);
    }
    
    exit;
}

/**
 * Tüm bildirimleri okundu olarak işaretle (AJAX)
 */
function notification_mark_all_read() {
    header('Content-Type: application/json');
    
    // Login kontrolü
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız']);
        exit;
    }
    
    // POST kontrolü
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit;
    }
    
    // CSRF kontrolü
    $data = json_decode(file_get_contents('php://input'), true);
    if (!validate_csrf_token($data['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    $user_id = current_user_id();
    
    try {
        // Tüm okunmamış bildirimleri güncelle
        db_execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$user_id]
        );
        
        echo json_encode([
            'success' => true,
            'unread_count' => 0
        ]);
        
    } catch (Exception $e) {
        log_error("Notification mark all read error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'İşlem başarısız']);
    }
    
    exit;
}

/**
 * Bildirimi sil (AJAX)
 */
function notification_delete() {
    header('Content-Type: application/json');
    
    // Login kontrolü
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız']);
        exit;
    }
    
    // POST kontrolü
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit;
    }
    
    // CSRF kontrolü
    $data = json_decode(file_get_contents('php://input'), true);
    if (!validate_csrf_token($data['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    $notification_id = $data['notification_id'] ?? 0;
    $user_id = current_user_id();
    
    if (!$notification_id) {
        echo json_encode(['success' => false, 'error' => 'Geçersiz bildirim ID']);
        exit;
    }
    
    try {
        // Bildirimin kullanıcıya ait olduğunu doğrula ve sil
        $result = db_execute(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?",
            [$notification_id, $user_id]
        );
        
        if ($result) {
            // Güncel okunmamış sayısı
            $unread_count = db_count(
                'notifications',
                'user_id = ? AND is_read = 0',
                [$user_id]
            );
            
            echo json_encode([
                'success' => true,
                'unread_count' => $unread_count
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Bildirim bulunamadı']);
        }
        
    } catch (Exception $e) {
        log_error("Notification delete error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'İşlem başarısız']);
    }
    
    exit;
}

/**
 * Okunmamış bildirim sayısını getir (AJAX)
 */
function notification_count() {
    header('Content-Type: application/json');
    
    // Login kontrolü
    if (!is_logged_in()) {
        echo json_encode(['success' => false, 'error' => 'Giriş yapmalısınız']);
        exit;
    }
    
    $user_id = current_user_id();
    
    try {
        $unread_count = db_count(
            'notifications',
            'user_id = ? AND is_read = 0',
            [$user_id]
        );
        
        echo json_encode([
            'success' => true,
            'unread_count' => $unread_count
        ]);
        
    } catch (Exception $e) {
        log_error("Notification count error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'İşlem başarısız']);
    }
    
    exit;
}