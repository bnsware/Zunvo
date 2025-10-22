<?php
/**
 * Zunvo Forum Sistemi
 * Post Model
 * 
 * Gönderi/yorum işlemleri
 */

/**
 * ID'ye göre post getir
 * @param int $post_id Post ID
 * @return array|false Post verisi
 */
function get_post_by_id($post_id) {
    return db_query_row(
        "SELECT p.*, u.username, u.avatar, u.reputation, u.created_at as user_joined
         FROM posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.id = ?",
        [$post_id]
    );
}

/**
 * Konunun tüm postlarını getir
 * @param int $topic_id Konu ID
 * @param int $page Sayfa numarası
 * @param int $per_page Sayfa başına post
 * @return array Postlar
 */
function get_topic_posts($topic_id, $page = 1, $per_page = POSTS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    
    return db_query_all(
        "SELECT p.*, u.username, u.avatar, u.reputation, u.created_at as user_joined,
         (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as user_post_count
         FROM posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.topic_id = ? AND p.is_deleted = 0
         ORDER BY p.is_solution DESC, p.created_at ASC
         LIMIT ? OFFSET ?",
        [$topic_id, $per_page, $offset]
    );
}

/**
 * Konudaki toplam post sayısı
 * @param int $topic_id Konu ID
 * @return int Post sayısı
 */
function get_topic_post_count($topic_id) {
    return db_count('posts', 'topic_id = ? AND is_deleted = 0', [$topic_id]);
}

/**
 * Yeni post oluştur
 * @param array $data Post verileri (topic_id, user_id, content)
 * @return int|false Post ID veya false
 */
function create_post($data) {
    $query = "INSERT INTO posts (topic_id, user_id, content, created_at) 
              VALUES (?, ?, ?, NOW())";
    
    $post_id = db_insert($query, [
        $data['topic_id'],
        $data['user_id'],
        $data['content']
    ]);
    
    if ($post_id) {
        // Konunun updated_at'ini güncelle
        touch_topic($data['topic_id']);
        
        // Mention'ları işle
        process_mentions($data['content'], $post_id, $data['user_id']);
    }
    
    return $post_id;
}

/**
 * Post güncelle
 * @param int $post_id Post ID
 * @param string $content Yeni içerik
 * @return bool Başarı durumu
 */
function update_post($post_id, $content) {
    return db_execute(
        "UPDATE posts SET content = ?, updated_at = NOW() WHERE id = ?",
        [$content, $post_id]
    );
}

/**
 * Post sil (soft delete)
 * @param int $post_id Post ID
 * @return bool Başarı durumu
 */
function delete_post($post_id) {
    return db_execute("UPDATE posts SET is_deleted = 1 WHERE id = ?", [$post_id]);
}

/**
 * Postu kalıcı sil (hard delete)
 * @param int $post_id Post ID
 * @return bool Başarı durumu
 */
function hard_delete_post($post_id) {
    return db_execute("DELETE FROM posts WHERE id = ?", [$post_id]);
}

/**
 * Postu çözüm olarak işaretle
 * @param int $post_id Post ID
 * @param int $topic_id Konu ID
 * @return bool Başarı durumu
 */
function mark_as_solution($post_id, $topic_id) {
    db_begin_transaction();
    
    try {
        // Önce konudaki diğer çözümleri kaldır
        db_execute("UPDATE posts SET is_solution = 0 WHERE topic_id = ?", [$topic_id]);
        
        // Bu postu çözüm olarak işaretle
        db_execute("UPDATE posts SET is_solution = 1 WHERE id = ?", [$post_id]);
        
        // Post sahibine reputasyon ver
        $post = get_post_by_id($post_id);
        if ($post) {
            update_user_reputation($post['user_id'], REPUTATION_BEST_ANSWER);
            
            // Bildirim oluştur
            create_notification(
                $post['user_id'],
                'solution_marked',
                'Gönderiniz en iyi yanıt olarak seçildi!',
                "/konu/{$topic_id}#post-{$post_id}"
            );
        }
        
        db_commit();
        return true;
        
    } catch (Exception $e) {
        db_rollback();
        return false;
    }
}

/**
 * Çözüm işaretini kaldır
 * @param int $post_id Post ID
 * @return bool Başarı durumu
 */
function unmark_solution($post_id) {
    return db_execute("UPDATE posts SET is_solution = 0 WHERE id = ?", [$post_id]);
}

/**
 * Mention'ları işle (@username)
 * @param string $content İçerik
 * @param int $post_id Post ID
 * @param int $user_id Gönderen kullanıcı ID
 */
function process_mentions($content, $post_id, $user_id) {
    // @username formatındaki mention'ları bul
    preg_match_all('/@([a-zA-Z0-9_-]+)/', $content, $matches);
    
    if (empty($matches[1])) {
        return;
    }
    
    $mentioned_usernames = array_unique($matches[1]);
    
    foreach ($mentioned_usernames as $username) {
        $mentioned_user = get_user_by_username($username);
        
        if ($mentioned_user && $mentioned_user['id'] !== $user_id) {
            // Bildirim oluştur
            $sender = get_user_by_id($user_id);
            create_notification(
                $mentioned_user['id'],
                'mention',
                "{$sender['username']} sizi bir gönderide bahsetti",
                "/post/{$post_id}"
            );
        }
    }
}

/**
 * Kullanıcının son postlarını getir
 * @param int $user_id Kullanıcı ID
 * @param int $limit Limit
 * @return array Postlar
 */
function get_user_recent_posts($user_id, $limit = 10) {
    return db_query_all(
        "SELECT p.*, t.title as topic_title, t.slug as topic_slug
         FROM posts p
         JOIN topics t ON p.topic_id = t.id
         WHERE p.user_id = ? AND p.is_deleted = 0
         ORDER BY p.created_at DESC
         LIMIT ?",
        [$user_id, $limit]
    );
}

/**
 * Kullanıcının toplam post sayısı
 * @param int $user_id Kullanıcı ID
 * @return int Post sayısı
 */
function get_user_post_count($user_id) {
    return db_count('posts', 'user_id = ? AND is_deleted = 0', [$user_id]);
}

/**
 * Vote model fonksiyonları
 */

/**
 * Post'a oy ver
 * @param int $post_id Post ID
 * @param int $user_id Kullanıcı ID
 * @param string $vote_type 'up' veya 'down'
 * @return bool Başarı durumu
 */
function vote_post($post_id, $user_id, $vote_type) {
    // Önceki oyu kontrol et
    $existing_vote = db_query_row(
        "SELECT * FROM votes WHERE post_id = ? AND user_id = ?",
        [$post_id, $user_id]
    );
    
    db_begin_transaction();
    
    try {
        if ($existing_vote) {
            if ($existing_vote['vote_type'] === $vote_type) {
                // Aynı oy, kaldır
                db_execute("DELETE FROM votes WHERE id = ?", [$existing_vote['id']]);
                
                // Post vote sayısını güncelle
                $column = $vote_type === 'up' ? 'upvotes' : 'downvotes';
                db_execute("UPDATE posts SET {$column} = {$column} - 1 WHERE id = ?", [$post_id]);
                
                // Reputasyonu geri al
                $post = get_post_by_id($post_id);
                $rep_change = $vote_type === 'up' ? -REPUTATION_UPVOTE : REPUTATION_DOWNVOTE;
                update_user_reputation($post['user_id'], $rep_change);
                
            } else {
                // Farklı oy, değiştir
                db_execute(
                    "UPDATE votes SET vote_type = ? WHERE id = ?",
                    [$vote_type, $existing_vote['id']]
                );
                
                // Post vote sayılarını güncelle
                $old_column = $existing_vote['vote_type'] === 'up' ? 'upvotes' : 'downvotes';
                $new_column = $vote_type === 'up' ? 'upvotes' : 'downvotes';
                
                db_execute("UPDATE posts SET {$old_column} = {$old_column} - 1, {$new_column} = {$new_column} + 1 WHERE id = ?", [$post_id]);
                
                // Reputasyon değişikliği
                $post = get_post_by_id($post_id);
                $old_rep = $existing_vote['vote_type'] === 'up' ? -REPUTATION_UPVOTE : REPUTATION_DOWNVOTE;
                $new_rep = $vote_type === 'up' ? REPUTATION_UPVOTE : -REPUTATION_UPVOTE;
                update_user_reputation($post['user_id'], $old_rep + $new_rep);
            }
        } else {
            // Yeni oy
            db_insert(
                "INSERT INTO votes (post_id, user_id, vote_type) VALUES (?, ?, ?)",
                [$post_id, $user_id, $vote_type]
            );
            
            // Post vote sayısını güncelle
            $column = $vote_type === 'up' ? 'upvotes' : 'downvotes';
            db_execute("UPDATE posts SET {$column} = {$column} + 1 WHERE id = ?", [$post_id]);
            
            // Reputasyon ver
            $post = get_post_by_id($post_id);
            $rep_change = $vote_type === 'up' ? REPUTATION_UPVOTE : -REPUTATION_UPVOTE;
            update_user_reputation($post['user_id'], $rep_change);
            
            // Bildirim (sadece upvote için)
            if ($vote_type === 'up' && $post['user_id'] !== $user_id) {
                $voter = get_user_by_id($user_id);
                create_notification(
                    $post['user_id'],
                    'upvote',
                    "{$voter['username']} gönderinizi beğendi",
                    "/post/{$post_id}"
                );
            }
        }
        
        db_commit();
        return true;
        
    } catch (Exception $e) {
        db_rollback();
        log_error("Vote error: " . $e->getMessage());
        return false;
    }
}

/**
 * Kullanıcının bir post için oyunu getir
 * @param int $post_id Post ID
 * @param int $user_id Kullanıcı ID
 * @return string|null 'up', 'down' veya null
 */
function get_user_vote($post_id, $user_id) {
    $vote = db_query_row(
        "SELECT vote_type FROM votes WHERE post_id = ? AND user_id = ?",
        [$post_id, $user_id]
    );
    
    return $vote ? $vote['vote_type'] : null;
}

/**
 * Bildirim oluştur (helper)
 * @param int $user_id Kullanıcı ID
 * @param string $type Bildirim tipi
 * @param string $message Mesaj
 * @param string $link Link
 */
function create_notification($user_id, $type, $message, $link) {
    db_insert(
        "INSERT INTO notifications (user_id, type, message, link, created_at) VALUES (?, ?, ?, ?, NOW())",
        [$user_id, $type, $message, $link]
    );
}