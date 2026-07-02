<?php

require_once APP_PATH . '/models/category.php';

function home_index() {
    $forum_tree = get_category_tree();
    $stats = [
        'total_users' => db_count('users'),
        'total_topics' => db_count('topics'),
        'total_posts' => db_count('posts'),
        'newest_user' => db_query_row("SELECT username FROM users ORDER BY created_at DESC LIMIT 1")
    ];
    $widget_enabled = get_setting('homepage_widget_enabled', '1') === '1';
    render('home', [
        'title' => 'Ana Sayfa',
        'forum_tree' => $forum_tree,
        'stats' => $stats,
        'hot_topics' => get_hot_topics(5),
        'trend_tags' => get_trending_tags(10),
        'widget_enabled' => $widget_enabled
    ]);
}

function home_set_theme() {
    $slug = trim((string)get_param('slug', ''));
    $redirect = (string)get_param('redirect', '/');
    if ($redirect === '' || $redirect[0] !== '/' || strpos($redirect, '://') !== false) {
        $redirect = '/';
    }
    if ($slug === '') {
        set_user_theme_preference(null);
    } else {
        if (!set_user_theme_preference($slug)) {
            set_flash('error', 'Geçersiz tema seçimi.');
            redirect(url(ltrim($redirect, '/')));
            return;
        }
    }
    redirect(url(ltrim($redirect, '/')));
}

function home_about() {
    $content = get_setting('about_content', 'Zunvo, modern ve açık kaynak forum yazılımıdır.');
    render('home/about', [
        'title' => 'Hakkımızda',
        'content' => $content
    ]);
}

function home_widget() {
    $tab = get_param('tab', 'recent');
    $page = max(1, (int)get_param('page', 1));
    $per_page = 15;
    $offset = ($page - 1) * $per_page;
    $user = current_user();

    $topics = [];
    $total = 0;

    if ($tab === 'recent') {
        $total = db_count('topics');
        $topics = db_query_all(
            "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug,
             (SELECT COUNT(*) FROM posts WHERE topic_id = t.id AND is_deleted = 0) - 1 as reply_count,
             (SELECT username FROM users WHERE id = (
                 SELECT user_id FROM posts WHERE topic_id = t.id AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1
             )) as last_poster
             FROM topics t
             JOIN users u ON t.user_id = u.id
             JOIN categories c ON t.category_id = c.id
             ORDER BY t.created_at DESC
             LIMIT ? OFFSET ?",
            [$per_page, $offset]
        );
    } elseif ($tab === 'replied') {
        $total = db_count('topics');
        $topics = db_query_all(
            "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug,
             (SELECT COUNT(*) FROM posts WHERE topic_id = t.id AND is_deleted = 0) - 1 as reply_count,
             (SELECT username FROM users WHERE id = (
                 SELECT user_id FROM posts WHERE topic_id = t.id AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1
             )) as last_poster
             FROM topics t
             JOIN users u ON t.user_id = u.id
             JOIN categories c ON t.category_id = c.id
             ORDER BY t.updated_at DESC
             LIMIT ? OFFSET ?",
            [$per_page, $offset]
        );
    } elseif ($tab === 'visited' && $user) {
        $total = db_query_value(
            "SELECT COUNT(*) FROM topic_visits WHERE user_id = ?",
            [$user['id']]
        );
        $topics = db_query_all(
            "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug,
             (SELECT COUNT(*) FROM posts WHERE topic_id = t.id AND is_deleted = 0) - 1 as reply_count,
             (SELECT username FROM users WHERE id = (
                 SELECT user_id FROM posts WHERE topic_id = t.id AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1
             )) as last_poster,
             tv.visited_at
             FROM topic_visits tv
             JOIN topics t ON tv.topic_id = t.id
             JOIN users u ON t.user_id = u.id
             JOIN categories c ON t.category_id = c.id
             WHERE tv.user_id = ?
             ORDER BY tv.visited_at DESC
             LIMIT ? OFFSET ?",
            [$user['id'], $per_page, $offset]
        );
    } elseif ($tab === 'popular') {
        $topics = get_hot_topics($per_page);
        $total = count($topics);
    } elseif (strpos($tab, 'cat_') === 0) {
        $cat_id = (int)substr($tab, 4);
        $total = db_count('topics', 'category_id = ?', [$cat_id]);
        $topics = db_query_all(
            "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug,
             (SELECT COUNT(*) FROM posts WHERE topic_id = t.id AND is_deleted = 0) - 1 as reply_count,
             (SELECT username FROM users WHERE id = (
                 SELECT user_id FROM posts WHERE topic_id = t.id AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1
             )) as last_poster
             FROM topics t
             JOIN users u ON t.user_id = u.id
             JOIN categories c ON t.category_id = c.id
             WHERE t.category_id = ?
             ORDER BY t.updated_at DESC
             LIMIT ? OFFSET ?",
            [$cat_id, $per_page, $offset]
        );
    }

    foreach ($topics as &$topic) {
        if (!isset($topic['reply_count']) || $topic['reply_count'] < 0) {
            $topic['reply_count'] = max(0, (int)($topic['post_count'] ?? 0) - 1);
        }
    }
    unset($topic);

    if (is_ajax()) {
        json_response([
            'success' => true,
            'topics' => $topics,
            'has_more' => ($page * $per_page) < $total,
            'page' => $page
        ]);
        return;
    }

    redirect(url('/'));
}

function get_hot_topics($limit = 5) {
    return db_query_all(
        "SELECT t.*, u.username, u.avatar, c.name as category_name, c.slug as category_slug,
         (SELECT COUNT(*) FROM posts WHERE topic_id = t.id AND is_deleted = 0) as post_count,
         (SELECT COALESCE(SUM(upvotes),0) FROM posts WHERE topic_id = t.id) as total_upvotes,
         TIMESTAMPDIFF(HOUR, t.created_at, NOW()) as hours_old
         FROM topics t
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         ORDER BY ((SELECT COUNT(*) FROM posts WHERE topic_id = t.id) + COALESCE((SELECT SUM(upvotes) FROM posts WHERE topic_id = t.id),0)) / GREATEST(TIMESTAMPDIFF(HOUR, t.created_at, NOW()), 1) DESC
         LIMIT ?",
        [$limit]
    );
}

function get_trending_tags($limit = 10) {
    return db_query_all(
        "SELECT tg.* FROM tags tg
         JOIN topic_tags tt ON tg.id = tt.tag_id
         JOIN topics t ON tt.topic_id = t.id
         WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
         GROUP BY tg.id
         ORDER BY COUNT(*) DESC
         LIMIT ?",
        [$limit]
    );
}

function record_topic_visit($user_id, $topic_id) {
    if (!$user_id || !$topic_id) {
        return;
    }
    $exists = db_query_row(
        "SELECT id FROM topic_visits WHERE user_id = ? AND topic_id = ?",
        [$user_id, $topic_id]
    );
    if ($exists) {
        db_execute(
            "UPDATE topic_visits SET visited_at = NOW() WHERE user_id = ? AND topic_id = ?",
            [$user_id, $topic_id]
        );
    } else {
        db_insert(
            "INSERT INTO topic_visits (user_id, topic_id) VALUES (?, ?)",
            [$user_id, $topic_id]
        );
    }
}

function get_homepage_widget_tabs() {
    $config = get_setting('homepage_widget_tabs', '');
    if ($config !== '') {
        $decoded = json_decode($config, true);
        if (is_array($decoded) && !empty($decoded)) {
            return $decoded;
        }
    }
    return [
        ['key' => 'recent', 'label' => 'Son Açılan'],
        ['key' => 'replied', 'label' => 'Son Cevaplanan'],
        ['key' => 'visited', 'label' => 'Son Gezilen', 'login_required' => true],
        ['key' => 'popular', 'label' => 'Popüler'],
    ];
}
