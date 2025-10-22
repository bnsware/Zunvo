<?php
/**
 * Zunvo Forum Sistemi
 * Vote Controller
 * 
 * AJAX ile upvote/downvote işlemleri
 */

// Model dosyalarını dahil et
require_once APP_PATH . '/models/post.php';

/**
 * Post'a oy ver (AJAX)
 */
function vote_submit() {
    // Sadece AJAX ve POST isteklerine izin ver
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    // Giriş kontrolü
    if (!is_logged_in()) {
        error_response('Oy vermek için giriş yapmalısınız', 401);
        return;
    }
    
    // CSRF kontrolü
    if (!validate_csrf_token(post_param('csrf_token'))) {
        error_response('Geçersiz token', 403);
        return;
    }
    
    $post_id = (int)post_param('post_id');
    $vote_type = post_param('vote_type');
    
    // Validasyon
    if (empty($post_id)) {
        error_response('Post ID gerekli');
        return;
    }
    
    if (!in_array($vote_type, ['up', 'down'])) {
        error_response('Geçersiz oy tipi');
        return;
    }
    
    // Post var mı kontrol et
    $post = get_post_by_id($post_id);
    if (!$post) {
        error_response('Post bulunamadı', 404);
        return;
    }
    
    // Kendi postuna oy veremez
    $user = current_user();
    if ($post['user_id'] === $user['id']) {
        error_response('Kendi gönderinize oy veremezsiniz');
        return;
    }
    
    // Rate limiting (aynı post'a çok hızlı oy vermeyi engelle)
    $rate_key = 'vote_' . $user['id'] . '_' . $post_id;
    if (isset($_SESSION[$rate_key]) && (time() - $_SESSION[$rate_key]) < 2) {
        error_response('Çok hızlı oy veriyorsunuz. Lütfen bekleyin.');
        return;
    }
    
    // Oyu kaydet
    if (vote_post($post_id, $user['id'], $vote_type)) {
        $_SESSION[$rate_key] = time();
        
        // Güncel post bilgilerini al
        $updated_post = get_post_by_id($post_id);
        $user_vote = get_user_vote($post_id, $user['id']);
        
        success_response([
            'post_id' => $post_id,
            'upvotes' => $updated_post['upvotes'],
            'downvotes' => $updated_post['downvotes'],
            'user_vote' => $user_vote,
            'message' => $user_vote ? 'Oyunuz kaydedildi' : 'Oyunuz kaldırıldı'
        ], 'İşlem başarılı');
    } else {
        error_response('Oy kaydedilirken bir hata oluştu');
    }
}

/**
 * Kullanıcının bir post için oyunu al (AJAX)
 */
function vote_get() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        json_response(['vote' => null]);
        return;
    }
    
    $post_id = (int)get_param('post_id');
    
    if (empty($post_id)) {
        error_response('Post ID gerekli');
        return;
    }
    
    $user = current_user();
    $vote = get_user_vote($post_id, $user['id']);
    
    success_response(['vote' => $vote]);
}

/**
 * Post istatistiklerini al (AJAX)
 */
function vote_stats() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    $post_id = (int)get_param('post_id');
    
    if (empty($post_id)) {
        error_response('Post ID gerekli');
        return;
    }
    
    $post = get_post_by_id($post_id);
    
    if (!$post) {
        error_response('Post bulunamadı', 404);
        return;
    }
    
    success_response([
        'upvotes' => $post['upvotes'],
        'downvotes' => $post['downvotes'],
        'total' => $post['upvotes'] - $post['downvotes']
    ]);
}

/**
 * Toplu post oylarını al (AJAX) - Sayfa yüklendiğinde kullanıcının tüm oylarını alır
 */
function vote_batch() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!is_logged_in()) {
        success_response(['votes' => []]);
        return;
    }
    
    $post_ids = post_param('post_ids');
    
    if (!is_array($post_ids) || empty($post_ids)) {
        error_response('Post ID\'leri gerekli');
        return;
    }
    
    $user = current_user();
    $votes = [];
    
    foreach ($post_ids as $post_id) {
        $post_id = (int)$post_id;
        $vote = get_user_vote($post_id, $user['id']);
        if ($vote) {
            $votes[$post_id] = $vote;
        }
    }
    
    success_response(['votes' => $votes]);
}

/**
 * En çok oylanan postları getir (leaderboard için)
 */
function vote_top_posts() {
    $limit = min(50, max(1, (int)get_param('limit', 10)));
    $period = get_param('period', 'all'); // all, week, month
    
    $where = 'is_deleted = 0';
    $params = [];
    
    // Zaman filtresi
    if ($period === 'week') {
        $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
    } elseif ($period === 'month') {
        $where .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
    }
    
    $query = "SELECT p.*, u.username, u.avatar, t.title as topic_title, t.slug as topic_slug,
              (p.upvotes - p.downvotes) as score
              FROM posts p
              JOIN users u ON p.user_id = u.id
              JOIN topics t ON p.topic_id = t.id
              WHERE {$where}
              ORDER BY score DESC, p.created_at DESC
              LIMIT ?";
    
    $params[] = $limit;
    
    $posts = db_query_all($query, $params);
    
    if (is_ajax()) {
        success_response(['posts' => $posts]);
    } else {
        render('vote/top_posts', [
            'title' => 'En Popüler Gönderiler',
            'posts' => $posts,
            'period' => $period
        ]);
    }
}

/**
 * Kullanıcının oy verdiği postları getir
 */
function vote_user_votes() {
    require_login();
    
    $user = current_user();
    $vote_type = get_param('type', 'all'); // all, up, down
    $page = max(1, (int)get_param('page', 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    $where = 'v.user_id = ?';
    $params = [$user['id']];
    
    if ($vote_type === 'up') {
        $where .= ' AND v.vote_type = "up"';
    } elseif ($vote_type === 'down') {
        $where .= ' AND v.vote_type = "down"';
    }
    
    $query = "SELECT v.*, p.content, p.created_at as post_date, 
              u.username, t.title as topic_title, t.slug as topic_slug
              FROM votes v
              JOIN posts p ON v.post_id = p.id
              JOIN users u ON p.user_id = u.id
              JOIN topics t ON p.topic_id = t.id
              WHERE {$where}
              ORDER BY v.created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $per_page;
    $params[] = $offset;
    
    $votes = db_query_all($query, $params);
    
    // Toplam sayı
    $total = db_query_value(
        "SELECT COUNT(*) FROM votes v WHERE {$where}",
        array_slice($params, 0, -2)
    );
    
    $pagination = get_pagination($total, $per_page, $page);
    
    render('vote/user_votes', [
        'title' => 'Oyladığım Gönderiler',
        'votes' => $votes,
        'vote_type' => $vote_type,
        'pagination' => $pagination
    ]);
}

/**
 * Vote log (moderator için)
 */
function vote_log() {
    require_admin();
    
    $page = max(1, (int)get_param('page', 1));
    $per_page = 50;
    $offset = ($page - 1) * $per_page;
    
    $query = "SELECT v.*, 
              u1.username as voter_username,
              u2.username as post_author_username,
              p.content as post_content,
              t.title as topic_title
              FROM votes v
              JOIN users u1 ON v.user_id = u1.id
              JOIN posts p ON v.post_id = p.id
              JOIN users u2 ON p.user_id = u2.id
              JOIN topics t ON p.topic_id = t.id
              ORDER BY v.created_at DESC
              LIMIT ? OFFSET ?";
    
    $votes = db_query_all($query, [$per_page, $offset]);
    $total = db_count('votes');
    
    $pagination = get_pagination($total, $per_page, $page);
    
    render('admin/vote_log', [
        'title' => 'Oy Logları',
        'votes' => $votes,
        'pagination' => $pagination
    ]);
}