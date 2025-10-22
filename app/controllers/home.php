<?php
/**
 * Zunvo Forum Sistemi
 * Home Controller
 * 
 * Ana sayfa ve genel sayfalar
 */

/**
 * Ana sayfa
 */
function home_index() {
    // Kategorileri al
    $categories = db_query_all("SELECT * FROM categories ORDER BY order_num ASC");
    
    // Her kategori için son konuları al
    $categories_with_topics = [];
    foreach ($categories as $category) {
        $topics = db_query_all(
            "SELECT t.*, u.username, u.avatar,
             (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as post_count
             FROM topics t
             JOIN users u ON t.user_id = u.id
             WHERE t.category_id = ?
             ORDER BY t.is_pinned DESC, t.updated_at DESC
             LIMIT 5",
            [$category['id']]
        );
        
        $category['topics'] = $topics;
        $category['topic_count'] = db_count('topics', 'category_id = ?', [$category['id']]);
        $categories_with_topics[] = $category;
    }
    
    // Site istatistikleri
    $stats = [
        'total_users' => db_count('users'),
        'total_topics' => db_count('topics'),
        'total_posts' => db_count('posts'),
        'newest_user' => db_query_row("SELECT username FROM users ORDER BY created_at DESC LIMIT 1")
    ];
    
    // View'ı göster
    render('home', [
        'title' => 'Ana Sayfa',
        'categories' => $categories_with_topics,
        'stats' => $stats
    ]);
}