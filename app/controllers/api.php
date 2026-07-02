<?php

require_once APP_PATH . '/models/user.php';
require_once APP_PATH . '/models/category.php';
require_once APP_PATH . '/models/topic.php';
require_once APP_PATH . '/models/post.php';

function api_authenticate() {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? get_param('api_key', '');
    if (empty($key)) {
        error_response('API key gerekli', 401);
    }
    $row = db_query_row("SELECT ak.*, u.id as user_id, u.username, u.role FROM api_keys ak JOIN users u ON ak.user_id = u.id WHERE ak.api_key = ? AND ak.is_active = 1", [$key]);
    if (!$row) {
        error_response('Geçersiz API key', 401);
    }
    return $row;
}

function api_topics() {
    $auth = api_authenticate();
    $page = max(1, (int)get_param('page', 1));
    $topics = get_all_topics($page, TOPICS_PER_PAGE);
    json_response(['success' => true, 'data' => $topics]);
}

function api_topic_show($id) {
    $auth = api_authenticate();
    $topic = get_topic_by_id((int)$id);
    if (!$topic) {
        error_response('Konu bulunamadı', 404);
    }
    $posts = get_topic_posts($topic['id'], 1, POSTS_PER_PAGE);
    json_response(['success' => true, 'data' => ['topic' => $topic, 'posts' => $posts]]);
}

function api_posts() {
    $auth = api_authenticate();
    if (is_post()) {
        $topic_id = (int)post_param('topic_id');
        $content = post_param('content');
        if (empty($topic_id) || empty($content)) {
            error_response('topic_id ve content gerekli');
        }
        $post_id = create_post(['topic_id' => $topic_id, 'user_id' => $auth['user_id'], 'content' => $content]);
        fire_webhook('post.created', ['post_id' => $post_id, 'topic_id' => $topic_id]);
        success_response(['post_id' => $post_id]);
    }
    $topic_id = (int)get_param('topic_id');
    if (!$topic_id) {
        error_response('topic_id gerekli');
    }
    $posts = get_topic_posts($topic_id, 1, POSTS_PER_PAGE);
    json_response(['success' => true, 'data' => $posts]);
}

function api_vote() {
    $auth = api_authenticate();
    if (!is_post()) {
        error_response('POST gerekli', 405);
    }
    $post_id = (int)post_param('post_id');
    $vote_type = post_param('vote_type');
    if (!vote_post($post_id, $auth['user_id'], $vote_type)) {
        error_response('Oy kaydedilemedi');
    }
    success_response(null, 'Oy kaydedildi');
}

function api_user_show($username) {
    api_authenticate();
    $user = get_user_by_username($username);
    if (!$user) {
        error_response('Kullanıcı bulunamadı', 404);
    }
    unset($user['password'], $user['verification_token'], $user['reset_token']);
    $user['stats'] = get_user_stats($user['id']);
    json_response(['success' => true, 'data' => $user]);
}

function api_notifications() {
    $auth = api_authenticate();
    $notifications = get_user_notifications($auth['user_id'], false, 50);
    json_response(['success' => true, 'data' => $notifications]);
}

function api_search() {
    api_authenticate();
    $q = get_param('q', '');
    if (empty($q)) {
        error_response('q parametresi gerekli');
    }
    $topics = search_topics($q, 1, TOPICS_PER_PAGE);
    json_response(['success' => true, 'data' => $topics]);
}

function api_preview_bbcode() {
    if (!is_post()) {
        error_response('POST gerekli', 405);
    }
    $content = post_param('content', '');
    if ($content === '' && is_array(get_json_body())) {
        $content = get_json_body()['content'] ?? '';
    }
    json_response(['success' => true, 'html' => parse_post_content($content)]);
}
