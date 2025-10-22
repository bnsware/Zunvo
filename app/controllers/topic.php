<?php
/**
 * Zunvo Forum Sistemi
 * Topic Controller
 * 
 * Konu işlemleri
 */

// Model dosyalarını dahil et
require_once APP_PATH . '/models/category.php';
require_once APP_PATH . '/models/topic.php';
require_once APP_PATH . '/models/post.php';

/**
 * Tüm konuları listele
 */
function topic_index() {
    $page = max(1, (int)get_param('page', 1));
    $search = get_param('search', '');
    
    $filters = [];
    if (!empty($search)) {
        $filters['search'] = $search;
    }
    
    // Konuları al
    $topics = get_all_topics($page, TOPICS_PER_PAGE, $filters);
    $total_topics = get_total_topics($filters);
    
    // Pagination bilgilerini hesapla
    $pagination = get_pagination($total_topics, TOPICS_PER_PAGE, $page);
    
    // View'ı göster
    render('topic/list', [
        'title' => 'Tüm Konular',
        'topics' => $topics,
        'pagination' => $pagination,
        'search' => $search
    ]);
}

/**
 * Konu detay sayfası
 * @param string $slug Konu slug
 */
function topic_show($slug) {
    // Konuyu al
    $topic = get_topic_by_slug($slug);
    
    if (!$topic) {
        set_flash('error', 'Konu bulunamadı.');
        redirect(url('/'));
        return;
    }
    
    // Görüntülenme sayısını artır
    increment_topic_views($topic['id']);
    
    // Sayfa numarası
    $page = max(1, (int)get_param('page', 1));
    
    // Postları al
    $posts = get_topic_posts($topic['id'], $page, POSTS_PER_PAGE);
    $total_posts = get_topic_post_count($topic['id']);
    
    // Pagination
    $pagination = get_pagination($total_posts, POSTS_PER_PAGE, $page);
    
    // Etiketleri al
    $tags = get_topic_tags($topic['id']);
    
    // Mevcut kullanıcının oylarını al
    $user_votes = [];
    if (is_logged_in()) {
        foreach ($posts as $post) {
            $vote = get_user_vote($post['id'], current_user()['id']);
            if ($vote) {
                $user_votes[$post['id']] = $vote;
            }
        }
    }
    
    // View'ı göster
    render('topic/detail', [
        'title' => $topic['title'],
        'topic' => $topic,
        'posts' => $posts,
        'tags' => $tags,
        'pagination' => $pagination,
        'user_votes' => $user_votes
    ]);
}

/**
 * Konu oluşturma sayfası
 */
function topic_create() {
    require_login();
    
    $errors = [];
    $old_data = [];
    
    // Kategorileri al
    $categories = get_all_categories();
    
    if (is_post()) {
        // CSRF kontrolü
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        } else {
            $category_id = post_param('category_id');
            $title = post_param('title');
            $content = post_param('content');
            $tags = post_param('tags', '');
            
            // Eski verileri sakla
            $old_data = [
                'category_id' => $category_id,
                'title' => $title,
                'content' => $content,
                'tags' => $tags
            ];
            
            // Validasyon
            if (empty($category_id)) {
                $errors['category_id'] = 'Kategori seçmelisiniz.';
            } elseif (!get_category_by_id($category_id)) {
                $errors['category_id'] = 'Geçersiz kategori.';
            }
            
            if (empty($title)) {
                $errors['title'] = 'Başlık boş olamaz.';
            } elseif (strlen($title) < 10) {
                $errors['title'] = 'Başlık en az 10 karakter olmalı.';
            } elseif (strlen($title) > 255) {
                $errors['title'] = 'Başlık en fazla 255 karakter olabilir.';
            }
            
            if (empty($content)) {
                $errors['content'] = 'İçerik boş olamaz.';
            } elseif (strlen($content) < 20) {
                $errors['content'] = 'İçerik en az 20 karakter olmalı.';
            }
            
            // Hata yoksa konuyu oluştur
            if (empty($errors)) {
                $user = current_user();
                
                // Etiketleri diziye çevir
                $tag_array = [];
                if (!empty($tags)) {
                    $tag_array = array_map('trim', explode(',', $tags));
                }
                
                $topic_id = create_topic([
                    'category_id' => $category_id,
                    'user_id' => $user['id'],
                    'title' => $title,
                    'content' => $content,
                    'tags' => $tag_array
                ]);
                
                if ($topic_id) {
                    $topic = get_topic_by_id($topic_id);
                    set_flash('success', 'Konu başarıyla oluşturuldu!');
                    redirect(url('/konu/' . $topic['slug']));
                } else {
                    $errors['general'] = 'Konu oluşturulurken bir hata oluştu.';
                }
            }
        }
    }
    
    // View'ı göster
    render('topic/create', [
        'title' => 'Yeni Konu Oluştur',
        'categories' => $categories,
        'errors' => $errors,
        'old_data' => $old_data
    ]);
}

/**
 * Konu düzenleme sayfası
 * @param string $slug Konu slug
 */
function topic_edit($slug) {
    require_login();
    
    $topic = get_topic_by_slug($slug);
    
    if (!$topic) {
        set_flash('error', 'Konu bulunamadı.');
        redirect(url('/'));
        return;
    }
    
    $user = current_user();
    
    // Yetki kontrolü (sadece konu sahibi veya moderator)
    if ($topic['user_id'] !== $user['id'] && !is_moderator()) {
        set_flash('error', 'Bu konuyu düzenleme yetkiniz yok.');
        redirect(url('/konu/' . $slug));
        return;
    }
    
    $errors = [];
    $categories = get_all_categories();
    
    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek.';
        } else {
            $category_id = post_param('category_id');
            $title = post_param('title');
            
            // Validasyon
            if (empty($title) || strlen($title) < 10) {
                $errors['title'] = 'Başlık en az 10 karakter olmalı.';
            }
            
            if (empty($errors)) {
                if (update_topic($topic['id'], [
                    'title' => $title,
                    'category_id' => $category_id
                ])) {
                    set_flash('success', 'Konu güncellendi.');
                    $updated_topic = get_topic_by_id($topic['id']);
                    redirect(url('/konu/' . $updated_topic['slug']));
                } else {
                    $errors['general'] = 'Güncelleme sırasında hata oluştu.';
                }
            }
        }
    }
    
    render('topic/edit', [
        'title' => 'Konu Düzenle',
        'topic' => $topic,
        'categories' => $categories,
        'errors' => $errors
    ]);
}

/**
 * Yorum/Post ekleme (AJAX)
 */
function topic_add_post() {
    require_login();
    
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    // CSRF kontrolü
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        error_response('Geçersiz token', 403);
        return;
    }
    
    $topic_id = post_param('topic_id');
    $content = post_param('content');
    
    // Validasyon
    if (empty($topic_id) || empty($content)) {
        error_response('Tüm alanları doldurun');
        return;
    }
    
    if (strlen($content) < 10) {
        error_response('Yorum en az 10 karakter olmalı');
        return;
    }
    
    // Konu var mı ve kilitli değil mi?
    $topic = get_topic_by_id($topic_id);
    if (!$topic) {
        error_response('Konu bulunamadı');
        return;
    }
    
    if ($topic['is_locked'] && !is_moderator()) {
        error_response('Bu konu kilitli');
        return;
    }
    
    // Postu oluştur
    $user = current_user();
    $post_id = create_post([
        'topic_id' => $topic_id,
        'user_id' => $user['id'],
        'content' => $content
    ]);
    
    if ($post_id) {
        $post = get_post_by_id($post_id);
        success_response([
            'post_id' => $post_id,
            'post' => $post
        ], 'Yorum eklendi');
    } else {
        error_response('Yorum eklenirken hata oluştu');
    }
}

/**
 * Post düzenleme
 */
function topic_edit_post() {
    require_login();
    
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    $post_id = post_param('post_id');
    $content = post_param('content');
    
    $post = get_post_by_id($post_id);
    if (!$post) {
        error_response('Post bulunamadı');
        return;
    }
    
    $user = current_user();
    
    // Yetki kontrolü
    if ($post['user_id'] !== $user['id'] && !is_moderator()) {
        error_response('Bu postu düzenleme yetkiniz yok', 403);
        return;
    }
    
    if (strlen($content) < 10) {
        error_response('İçerik en az 10 karakter olmalı');
        return;
    }
    
    if (update_post($post_id, $content)) {
        success_response(null, 'Post güncellendi');
    } else {
        error_response('Güncelleme sırasında hata oluştu');
    }
}

/**
 * Post silme
 */
function topic_delete_post() {
    require_login();
    
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    $post_id = post_param('post_id');
    
    $post = get_post_by_id($post_id);
    if (!$post) {
        error_response('Post bulunamadı');
        return;
    }
    
    $user = current_user();
    
    // Yetki kontrolü
    if ($post['user_id'] !== $user['id'] && !is_moderator()) {
        error_response('Bu postu silme yetkiniz yok', 403);
        return;
    }
    
    if (delete_post($post_id)) {
        success_response(null, 'Post silindi');
    } else {
        error_response('Silme sırasında hata oluştu');
    }
}

/**
 * Çözüm işaretle
 */
function topic_mark_solution() {
    require_login();
    
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    $post_id = post_param('post_id');
    $topic_id = post_param('topic_id');
    
    $topic = get_topic_by_id($topic_id);
    if (!$topic) {
        error_response('Konu bulunamadı');
        return;
    }
    
    $user = current_user();
    
    // Sadece konu sahibi veya moderator işaretleyebilir
    if ($topic['user_id'] !== $user['id'] && !is_moderator()) {
        error_response('Yetkiniz yok', 403);
        return;
    }
    
    if (mark_as_solution($post_id, $topic_id)) {
        success_response(null, 'Çözüm olarak işaretlendi');
    } else {
        error_response('İşaretleme sırasında hata oluştu');
    }
}

/**
 * Kategori sayfası
 * @param string $slug Kategori slug
 */
function topic_category($slug) {
    $category = get_category_by_slug($slug);
    
    if (!$category) {
        set_flash('error', 'Kategori bulunamadı.');
        redirect(url('/'));
        return;
    }
    
    $page = max(1, (int)get_param('page', 1));
    
    // Kategorideki konuları al
    $topics = get_category_topics($category['id'], $page, TOPICS_PER_PAGE);
    $total_topics = db_count('topics', 'category_id = ?', [$category['id']]);
    
    // Pagination
    $pagination = get_pagination($total_topics, TOPICS_PER_PAGE, $page);
    
    // Kategori istatistikleri
    $stats = get_category_stats($category['id']);
    
    render('topic/category', [
        'title' => $category['name'],
        'category' => $category,
        'topics' => $topics,
        'stats' => $stats,
        'pagination' => $pagination
    ]);
}

/**
 * Arama
 */
function topic_search() {
    $query = get_param('q', '');
    $page = max(1, (int)get_param('page', 1));
    
    if (empty($query)) {
        redirect(url('/'));
        return;
    }
    
    // Arama yap
    $topics = search_topics($query, $page, TOPICS_PER_PAGE);
    $total_topics = get_total_topics(['search' => $query]);
    
    // Pagination
    $pagination = get_pagination($total_topics, TOPICS_PER_PAGE, $page);
    
    render('topic/search', [
        'title' => 'Arama: ' . $query,
        'query' => $query,
        'topics' => $topics,
        'pagination' => $pagination
    ]);
}