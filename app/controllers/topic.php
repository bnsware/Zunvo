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
require_once APP_PATH . '/models/badge.php';
require_once APP_PATH . '/models/change_request.php';
require_once APP_PATH . '/models/notification.php';

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
        require_once APP_PATH . '/controllers/home.php';
        record_topic_visit(current_user()['id'], $topic['id']);
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

function topic_create() {
    require_login();
    $errors = [];
    $old_data = [];
    $categories = get_leaf_forums();
    if (is_post()) {
        $result = topic_process_create($errors, $old_data, null);
        if ($result) {
            return;
        }
    }
    render('topic/create', [
        'title' => 'Yeni Konu Oluştur',
        'categories' => $categories,
        'errors' => $errors,
        'old_data' => $old_data,
        'locked_category' => null
    ]);
}

function topic_create_in_category($slug) {
    require_login();
    $category = get_category_by_slug($slug);
    if (!$category || !category_allows_topics($category)) {
        set_flash('error', 'Bu forumda konu açılamaz.');
        redirect(url('/'));
        return;
    }
    if (!user_can_create_topic_in_category($category)) {
        set_flash('error', 'Bu forumda konu açma yetkiniz yok. Yalnızca yöneticiler konu açabilir.');
        redirect(url('/kategori/' . $category['slug']));
        return;
    }
    $errors = [];
    $old_data = ['category_id' => $category['id']];
    if (is_post()) {
        $result = topic_process_create($errors, $old_data, $category);
        if ($result) {
            return;
        }
    }
    render('topic/create', [
        'title' => 'Yeni Konu - ' . $category['name'],
        'categories' => [$category],
        'errors' => $errors,
        'old_data' => $old_data,
        'locked_category' => $category
    ]);
}

function topic_process_create(&$errors, &$old_data, $locked_category) {
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $errors['csrf'] = 'Geçersiz istek. Lütfen tekrar deneyin.';
        return false;
    }
    $category_id = $locked_category ? $locked_category['id'] : post_param('category_id');
    $title = post_param('title');
    $content = post_param('content');
    $tags = post_param('tags', '');
    $old_data = [
        'category_id' => $category_id,
        'title' => $title,
        'content' => $content,
        'tags' => $tags
    ];
    $category = get_category_by_id($category_id);
    if (empty($category_id) || !$category) {
        $errors['category_id'] = 'Geçersiz kategori.';
    } elseif (!user_can_create_topic_in_category($category)) {
        $errors['category_id'] = 'Bu forumda konu açma yetkiniz yok.';
    }
    if (empty($title)) {
        $errors['title'] = 'Başlık boş olamaz.';
    } elseif (strlen($title) < 10) {
        $errors['title'] = 'Başlık en az 10 karakter olmalı.';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Başlık en fazla 255 karakter olmalı.';
    }
    if (empty($content)) {
        $errors['content'] = 'İçerik boş olamaz.';
    } elseif (strlen($content) < 20) {
        $errors['content'] = 'İçerik en az 20 karakter olmalı.';
    }
    if (!empty($errors)) {
        return false;
    }
    $user = current_user();
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
        execute_hooks('after_topic_create', [
            'topic_id' => $topic_id,
            'user_id' => $user['id'],
            'title' => $title,
            'content' => $content,
            'category_id' => $category_id,
            'slug' => $topic['slug']
        ]);
        fire_webhook('topic.created', ['topic_id' => $topic_id]);
        check_user_badges($user['id']);
        set_flash('success', 'Konu başarıyla oluşturuldu!');
        redirect(url('/konu/' . $topic['slug']));
        return true;
    }
    $errors['general'] = 'Konu oluşturulurken bir hata oluştu.';
    return false;
}

function topic_mod_action($slug) {
    require_login();
    if (!is_post()) {
        redirect(url('/konu/' . $slug));
        return;
    }
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        set_flash('error', 'Geçersiz istek.');
        redirect(url('/konu/' . $slug));
        return;
    }
    $topic = get_topic_by_slug($slug);
    if (!$topic) {
        set_flash('error', 'Konu bulunamadı.');
        redirect(url('/'));
        return;
    }
    $action = post_param('action');
    $allowed = [
        'pin' => 'pin_topic',
        'unpin' => 'pin_topic',
        'lock' => 'lock_topic',
        'unlock' => 'lock_topic',
        'delete' => 'delete_topic'
    ];
    if (!isset($allowed[$action]) || !can_mod($allowed[$action])) {
        set_flash('error', 'Bu işlem için yetkiniz yok.');
        redirect(url('/konu/' . $slug));
        return;
    }
    if ($action === 'pin') {
        pin_topic($topic['id'], true);
        log_mod_action('pin', 'topic', $topic['id'], '');
        set_flash('success', 'Konu sabitlendi.');
    } elseif ($action === 'unpin') {
        pin_topic($topic['id'], false);
        log_mod_action('unpin', 'topic', $topic['id'], '');
        set_flash('success', 'Sabitleme kaldırıldı.');
    } elseif ($action === 'lock') {
        lock_topic($topic['id'], true);
        log_mod_action('lock', 'topic', $topic['id'], '');
        set_flash('success', 'Konu kilitlendi.');
    } elseif ($action === 'unlock') {
        lock_topic($topic['id'], false);
        log_mod_action('unlock', 'topic', $topic['id'], '');
        set_flash('success', 'Kilit kaldırıldı.');
    } elseif ($action === 'delete') {
        delete_topic($topic['id']);
        log_mod_action('delete', 'topic', $topic['id'], '');
        set_flash('success', 'Konu silindi.');
        redirect(url('/kategori/' . $topic['category_slug']));
        return;
    }
    redirect(url('/konu/' . $slug));
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
    $is_mod_edit = is_moderator() && $topic['user_id'] !== $user['id'];

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            $errors['csrf'] = 'Geçersiz istek.';
        } else {
            $title = trim(post_param('title', ''));
            if (empty($title) || strlen($title) < 10) {
                $errors['title'] = 'Başlık en az 10 karakter olmalı.';
            }
            if (empty($errors)) {
                if ($title !== $topic['title'] && !is_moderator()) {
                    create_change_request('title_change', $topic['id'], $user['id'], $topic['title'], $title);
                    set_flash('success', 'Başlık değişikliği onay için gönderildi.');
                    redirect(url('/konu/' . $slug));
                    return;
                }
                if (is_moderator() && $title !== $topic['title']) {
                    update_topic($topic['id'], ['title' => $title]);
                    set_flash('success', 'Konu güncellendi.');
                    $updated_topic = get_topic_by_id($topic['id']);
                    redirect(url('/konu/' . $updated_topic['slug']));
                    return;
                }
                set_flash('info', 'Değişiklik yapılmadı.');
                redirect(url('/konu/' . $slug));
            }
        }
    }

    render('topic/edit', [
        'title' => 'Konu Düzenle',
        'topic' => $topic,
        'errors' => $errors,
        'is_mod_edit' => $is_mod_edit
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
    
    $user = current_user();
    $hook_data = execute_hooks('before_post_create', [
        'topic_id' => $topic_id,
        'user_id' => $user['id'],
        'content' => $content
    ]);
    if (!empty($hook_data['blocked'])) {
        error_response($hook_data['message'] ?? 'Gönderi engellendi');
        return;
    }
    $post_id = create_post([
        'topic_id' => $topic_id,
        'user_id' => $user['id'],
        'content' => $content
    ]);
    
    if ($post_id) {
        $post = get_post_by_id($post_id);
        execute_hooks('after_post_create', [
            'post_id' => $post_id,
            'topic_id' => $topic_id,
            'user_id' => $user['id'],
            'content' => $content
        ]);
        fire_webhook('post.created', ['post_id' => $post_id, 'topic_id' => $topic_id]);
        check_user_badges($user['id']);
        success_response([
            'post_id' => $post_id,
            'post' => $post
        ], 'Yorum eklendi');
    } else {
        error_response('Yorum eklenirken hata oluştu');
    }
}

function topic_edit_post() {
    require_login();
    
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        error_response('Geçersiz token', 403);
        return;
    }
    
    $post_id = post_param('post_id');
    $content = post_param('content');
    
    if (empty($post_id) || empty($content)) {
        error_response('Tüm alanları doldurun');
        return;
    }
    
    if (strlen($content) < 10) {
        error_response('Yorum en az 10 karakter olmalı');
        return;
    }
    
    $post = get_post_by_id($post_id);
    if (!$post) {
        error_response('Post bulunamadı');
        return;
    }
    
    $user = current_user();
    
    if ($post['user_id'] !== $user['id'] && !is_moderator()) {
        error_response('Bu postu düzenleme yetkiniz yok', 403);
        return;
    }
    
    if (update_post($post_id, $content)) {
        process_new_mentions($post['content'], $content, $post_id, $user['id']);
        success_response([
            'post' => get_post_by_id($post_id),
            'html' => parse_post_content($content)
        ], 'Post güncellendi');
    } else {
        error_response('Güncelleme sırasında hata oluştu');
    }
}

function topic_delete_post() {
    require_login();
    
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        error_response('Geçersiz token', 403);
        return;
    }
    
    $post_id = post_param('post_id');
    
    $post = get_post_by_id($post_id);
    if (!$post) {
        error_response('Post bulunamadı');
        return;
    }
    
    $user = current_user();
    
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
    
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        error_response('Geçersiz token', 403);
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

    $child_forums = get_child_forums($category['id']);
    foreach ($child_forums as &$forum) {
        $forum['last_topic'] = get_category_last_topic($forum['id']);
    }
    unset($forum);

    $page = max(1, (int)get_param('page', 1));
    $topics = [];
    $total_topics = 0;
    $stats = ['topics' => 0, 'posts' => 0];

    if (category_allows_topics($category)) {
        $topics = get_category_topics($category['id'], $page, TOPICS_PER_PAGE);
        $total_topics = db_count('topics', 'category_id = ?', [$category['id']]);
        $stats = get_category_stats($category['id']);
    } elseif (($category['forum_type'] ?? 'forum') !== 'section') {
        $stats = get_category_stats($category['id']);
    }

    $pagination = get_pagination($total_topics, TOPICS_PER_PAGE, $page);

    render('topic/category', [
        'title' => $category['name'],
        'category' => $category,
        'child_forums' => $child_forums,
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

function topic_categories() {
    $forum_tree = get_category_tree();
    render('topic/categories', [
        'title' => 'Forumlar',
        'forum_tree' => $forum_tree
    ]);
}

function topic_tag($slug) {
    $tag = db_query_row("SELECT * FROM tags WHERE slug = ?", [$slug]);
    if (!$tag) {
        set_flash('error', 'Etiket bulunamadı.');
        redirect(url('/'));
        return;
    }
    $page = max(1, (int)get_param('page', 1));
    $offset = ($page - 1) * TOPICS_PER_PAGE;
    $topics = db_query_all(
        "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug
         FROM topics t
         JOIN topic_tags tt ON t.id = tt.topic_id
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         WHERE tt.tag_id = ?
         ORDER BY t.updated_at DESC
         LIMIT ? OFFSET ?",
        [$tag['id'], TOPICS_PER_PAGE, $offset]
    );
    $total = db_count('topic_tags', 'tag_id = ?', [$tag['id']]);
    $pagination = get_pagination($total, TOPICS_PER_PAGE, $page);
    render('topic/tag', [
        'title' => '#' . $tag['name'],
        'tag' => $tag,
        'topics' => $topics,
        'pagination' => $pagination
    ]);
}

function topic_report() {
    require_login();
    if (!is_ajax() || !is_post()) {
        error_response('Geçersiz istek', 400);
        return;
    }
    if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        error_response('Geçersiz token', 403);
        return;
    }
    $post_id = (int)post_param('post_id');
    $reason = trim(post_param('reason', ''));
    if (empty($reason)) {
        error_response('Sebep belirtmelisiniz');
        return;
    }
    $post = get_post_by_id($post_id);
    if (!$post) {
        error_response('Gönderi bulunamadı', 404);
        return;
    }
    $user = current_user();
    create_report($user['id'], $post['user_id'], $post_id, $reason);
    success_response(null, 'Rapor gönderildi');
}