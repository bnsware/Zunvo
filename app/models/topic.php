<?php
/**
 * Zunvo Forum Sistemi
 * Topic Model
 * 
 * Konu işlemleri
 */

/**
 * ID'ye göre konu getir
 * @param int $topic_id Konu ID
 * @return array|false Konu verisi
 */
function get_topic_by_id($topic_id) {
    return db_query_row(
        "SELECT t.*, u.username, u.avatar, u.reputation, c.name as category_name, c.slug as category_slug
         FROM topics t
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         WHERE t.id = ?",
        [$topic_id]
    );
}

/**
 * Slug'a göre konu getir
 * @param string $slug Konu slug
 * @return array|false Konu verisi
 */
function get_topic_by_slug($slug) {
    return db_query_row(
        "SELECT t.*, u.username, u.avatar, u.reputation, c.name as category_name, c.slug as category_slug
         FROM topics t
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         WHERE t.slug = ?",
        [$slug]
    );
}

/**
 * Yeni konu oluştur
 * @param array $data Konu verileri (title, category_id, user_id, content)
 * @return int|false Konu ID veya false
 */
function create_topic($data) {
    // Slug oluştur
    $slug = create_slug($data['title']);
    
    // Slug benzersizliği kontrolü
    $counter = 1;
    $original_slug = $slug;
    while (get_topic_by_slug($slug)) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    // Transaction başlat
    db_begin_transaction();
    
    try {
        // Konuyu oluştur
        $topic_query = "INSERT INTO topics (category_id, user_id, title, slug, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $topic_id = db_insert($topic_query, [
            $data['category_id'],
            $data['user_id'],
            $data['title'],
            $slug
        ]);
        
        if (!$topic_id) {
            throw new Exception("Konu oluşturulamadı");
        }
        
        // İlk postu oluştur (konu içeriği)
        $post_query = "INSERT INTO posts (topic_id, user_id, content, created_at) 
                       VALUES (?, ?, ?, NOW())";
        
        $post_id = db_insert($post_query, [
            $topic_id,
            $data['user_id'],
            $data['content']
        ]);
        
        if (!$post_id) {
            throw new Exception("Post oluşturulamadı");
        }
        
        // Etiketleri işle (varsa)
        if (!empty($data['tags'])) {
            process_topic_tags($topic_id, $data['tags']);
        }
        
        db_commit();
        return $topic_id;
        
    } catch (Exception $e) {
        db_rollback();
        log_error("Topic creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Konu güncelle
 * @param int $topic_id Konu ID
 * @param array $data Güncellenecek veriler
 * @return bool Başarı durumu
 */
function update_topic($topic_id, $data) {
    $updates = [];
    $params = [];
    
    if (isset($data['title'])) {
        $updates[] = "title = ?";
        $params[] = $data['title'];
        
        // Slug'ı da güncelle
        $updates[] = "slug = ?";
        $params[] = create_slug($data['title']);
    }
    
    if (isset($data['category_id'])) {
        $updates[] = "category_id = ?";
        $params[] = $data['category_id'];
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $updates[] = "updated_at = NOW()";
    $params[] = $topic_id;
    
    $query = "UPDATE topics SET " . implode(', ', $updates) . " WHERE id = ?";
    return db_execute($query, $params);
}

/**
 * Konu sil
 * @param int $topic_id Konu ID
 * @return bool Başarı durumu
 */
function delete_topic($topic_id) {
    // Cascade delete ayarlandığı için posts otomatik silinecek
    return db_execute("DELETE FROM topics WHERE id = ?", [$topic_id]);
}

/**
 * Konuyu sabitle/sabitliği kaldır
 * @param int $topic_id Konu ID
 * @param bool $pinned Sabit mi?
 * @return bool Başarı durumu
 */
function pin_topic($topic_id, $pinned = true) {
    $value = $pinned ? 1 : 0;
    return db_execute("UPDATE topics SET is_pinned = ? WHERE id = ?", [$value, $topic_id]);
}

/**
 * Konuyu kilitle/kilidi aç
 * @param int $topic_id Konu ID
 * @param bool $locked Kilitli mi?
 * @return bool Başarı durumu
 */
function lock_topic($topic_id, $locked = true) {
    $value = $locked ? 1 : 0;
    return db_execute("UPDATE topics SET is_locked = ? WHERE id = ?", [$value, $topic_id]);
}

/**
 * Konu görüntüleme sayısını artır
 * @param int $topic_id Konu ID
 */
function increment_topic_views($topic_id) {
    db_execute("UPDATE topics SET views = views + 1 WHERE id = ?", [$topic_id]);
}

/**
 * Konunun güncellenme zamanını güncelle
 * @param int $topic_id Konu ID
 */
function touch_topic($topic_id) {
    db_execute("UPDATE topics SET updated_at = NOW() WHERE id = ?", [$topic_id]);
}

/**
 * Tüm konuları getir (pagination ile)
 * @param int $page Sayfa numarası
 * @param int $per_page Sayfa başına konu
 * @param array $filters Filtreler (category_id, user_id, search)
 * @return array Konular
 */
function get_all_topics($page = 1, $per_page = TOPICS_PER_PAGE, $filters = []) {
    $offset = ($page - 1) * $per_page;
    
    $where = [];
    $params = [];
    
    if (isset($filters['category_id'])) {
        $where[] = "t.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (isset($filters['user_id'])) {
        $where[] = "t.user_id = ?";
        $params[] = $filters['user_id'];
    }
    
    if (isset($filters['search'])) {
        $where[] = "(t.title LIKE ? OR EXISTS (
            SELECT 1 FROM posts p WHERE p.topic_id = t.id AND p.content LIKE ?
        ))";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $params[] = $per_page;
    $params[] = $offset;
    
    return db_query_all(
        "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug,
         (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as post_count,
         (SELECT username FROM users WHERE id = (
             SELECT user_id FROM posts WHERE topic_id = t.id ORDER BY created_at DESC LIMIT 1
         )) as last_poster,
         (SELECT created_at FROM posts WHERE topic_id = t.id ORDER BY created_at DESC LIMIT 1) as last_post_time
         FROM topics t
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         {$where_clause}
         ORDER BY t.is_pinned DESC, t.updated_at DESC
         LIMIT ? OFFSET ?",
        $params
    );
}

/**
 * Toplam konu sayısı
 * @param array $filters Filtreler
 * @return int Konu sayısı
 */
function get_total_topics($filters = []) {
    $where = [];
    $params = [];
    
    if (isset($filters['category_id'])) {
        $where[] = "category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (isset($filters['user_id'])) {
        $where[] = "user_id = ?";
        $params[] = $filters['user_id'];
    }
    
    if (isset($filters['search'])) {
        $where[] = "(title LIKE ? OR EXISTS (
            SELECT 1 FROM posts p WHERE p.topic_id = topics.id AND p.content LIKE ?
        ))";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = !empty($where) ? implode(' AND ', $where) : '';
    
    return db_count('topics', $where_clause, $params);
}

/**
 * Konu etiketlerini işle
 * @param int $topic_id Konu ID
 * @param array $tags Etiket isimleri
 */
function process_topic_tags($topic_id, $tags) {
    // Önce mevcut etiketleri sil
    db_execute("DELETE FROM topic_tags WHERE topic_id = ?", [$topic_id]);
    
    foreach ($tags as $tag_name) {
        $tag_name = trim($tag_name);
        if (empty($tag_name)) continue;
        
        $slug = create_slug($tag_name);
        
        // Etiket var mı kontrol et
        $tag = db_query_row("SELECT id FROM tags WHERE slug = ?", [$slug]);
        
        if (!$tag) {
            // Yeni etiket oluştur
            $tag_id = db_insert("INSERT INTO tags (name, slug, usage_count) VALUES (?, ?, 1)", 
                [$tag_name, $slug]);
        } else {
            // Kullanım sayısını artır
            $tag_id = $tag['id'];
            db_execute("UPDATE tags SET usage_count = usage_count + 1 WHERE id = ?", [$tag_id]);
        }
        
        // İlişkiyi kur
        db_execute("INSERT INTO topic_tags (topic_id, tag_id) VALUES (?, ?)", 
            [$topic_id, $tag_id]);
    }
}

/**
 * Konunun etiketlerini getir
 * @param int $topic_id Konu ID
 * @return array Etiketler
 */
function get_topic_tags($topic_id) {
    return db_query_all(
        "SELECT t.* FROM tags t
         JOIN topic_tags tt ON t.id = tt.tag_id
         WHERE tt.topic_id = ?",
        [$topic_id]
    );
}

/**
 * Arama yap (başlık ve içerik)
 * @param string $query Arama sorgusu
 * @param int $page Sayfa
 * @param int $per_page Sayfa başına sonuç
 * @return array Sonuçlar
 */
function search_topics($query, $page = 1, $per_page = TOPICS_PER_PAGE) {
    return get_all_topics($page, $per_page, ['search' => $query]);
}