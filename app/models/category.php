<?php
/**
 * Zunvo Forum Sistemi
 * Category Model
 * 
 * Kategori işlemleri
 */

/**
 * Tüm kategorileri getir
 * @return array Kategoriler
 */
function get_all_categories() {
    return db_query_all("SELECT * FROM categories ORDER BY order_num ASC");
}

/**
 * ID'ye göre kategori getir
 * @param int $category_id Kategori ID
 * @return array|false Kategori verisi
 */
function get_category_by_id($category_id) {
    return db_query_row("SELECT * FROM categories WHERE id = ?", [$category_id]);
}

/**
 * Slug'a göre kategori getir
 * @param string $slug Kategori slug
 * @return array|false Kategori verisi
 */
function get_category_by_slug($slug) {
    return db_query_row("SELECT * FROM categories WHERE slug = ?", [$slug]);
}

/**
 * Yeni kategori oluştur
 * @param array $data Kategori verileri
 * @return int|false Kategori ID veya false
 */
function create_category($data) {
    $slug = create_slug($data['name']);
    
    // Slug benzersizliği kontrolü
    $existing = get_category_by_slug($slug);
    if ($existing) {
        $slug = $slug . '-' . time();
    }
    
    $query = "INSERT INTO categories (name, slug, description, icon, color, order_num) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    return db_insert($query, [
        $data['name'],
        $slug,
        $data['description'] ?? '',
        $data['icon'] ?? 'folder',
        $data['color'] ?? '#007bff',
        $data['order_num'] ?? 0
    ]);
}

/**
 * Kategori güncelle
 * @param int $category_id Kategori ID
 * @param array $data Güncellenecek veriler
 * @return bool Başarı durumu
 */
function update_category($category_id, $data) {
    $updates = [];
    $params = [];
    
    if (isset($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
        
        // Slug'ı da güncelle
        $updates[] = "slug = ?";
        $params[] = create_slug($data['name']);
    }
    
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
    }
    
    if (isset($data['icon'])) {
        $updates[] = "icon = ?";
        $params[] = $data['icon'];
    }
    
    if (isset($data['color'])) {
        $updates[] = "color = ?";
        $params[] = $data['color'];
    }
    
    if (isset($data['order_num'])) {
        $updates[] = "order_num = ?";
        $params[] = $data['order_num'];
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $category_id;
    
    $query = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
    return db_execute($query, $params);
}

/**
 * Kategori sil
 * @param int $category_id Kategori ID
 * @return bool Başarı durumu
 */
function delete_category($category_id) {
    // Kategoriye ait konular var mı?
    $topic_count = db_count('topics', 'category_id = ?', [$category_id]);
    
    if ($topic_count > 0) {
        // Konuları başka kategoriye taşımak gerekir
        return false;
    }
    
    return db_execute("DELETE FROM categories WHERE id = ?", [$category_id]);
}

/**
 * Kategori istatistiklerini getir
 * @param int $category_id Kategori ID
 * @return array İstatistikler
 */
function get_category_stats($category_id) {
    // Konu sayısı
    $topic_count = db_count('topics', 'category_id = ?', [$category_id]);
    
    // Post sayısı
    $post_count = db_query_value(
        "SELECT COUNT(*) FROM posts p 
         JOIN topics t ON p.topic_id = t.id 
         WHERE t.category_id = ?",
        [$category_id]
    );
    
    // Son konu
    $last_topic = db_query_row(
        "SELECT t.*, u.username 
         FROM topics t 
         JOIN users u ON t.user_id = u.id 
         WHERE t.category_id = ? 
         ORDER BY t.created_at DESC 
         LIMIT 1",
        [$category_id]
    );
    
    return [
        'topics' => $topic_count ?: 0,
        'posts' => $post_count ?: 0,
        'last_topic' => $last_topic
    ];
}

/**
 * Kategorideki konuları getir (pagination ile)
 * @param int $category_id Kategori ID
 * @param int $page Sayfa numarası
 * @param int $per_page Sayfa başına konu
 * @return array Konular
 */
function get_category_topics($category_id, $page = 1, $per_page = TOPICS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    
    return db_query_all(
        "SELECT t.*, u.username, u.avatar,
         (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as post_count,
         (SELECT username FROM users WHERE id = (
             SELECT user_id FROM posts WHERE topic_id = t.id ORDER BY created_at DESC LIMIT 1
         )) as last_poster
         FROM topics t
         JOIN users u ON t.user_id = u.id
         WHERE t.category_id = ?
         ORDER BY t.is_pinned DESC, t.updated_at DESC
         LIMIT ? OFFSET ?",
        [$category_id, $per_page, $offset]
    );
}