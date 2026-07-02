<?php

register_hook('after_post_create', function($data) {
    bahset_bildir_handle($data);
    return $data;
});

register_hook('after_topic_create', function($data) {
    $topic_id = (int)($data['topic_id'] ?? 0);
    $user_id = (int)($data['user_id'] ?? 0);
    if (!$topic_id || !$user_id) {
        return $data;
    }
    $post = db_query_row(
        "SELECT id FROM posts WHERE topic_id = ? AND user_id = ? ORDER BY id ASC LIMIT 1",
        [$topic_id, $user_id]
    );
    if ($post) {
        $data['post_id'] = (int)$post['id'];
        bahset_bildir_handle($data);
    }
    return $data;
});

function bahset_bildir_handle($data) {
    $settings = get_plugin_settings('bahset-bildir');
    if (empty($settings['enabled'])) {
        return;
    }
    $content = $data['content'] ?? '';
    $sender_id = (int)($data['user_id'] ?? 0);
    $post_id = (int)($data['post_id'] ?? 0);
    if ($content === '' || !$sender_id || !$post_id) {
        return;
    }
    preg_match_all('/@([a-zA-Z0-9_-]{3,20})/', $content, $matches);
    if (empty($matches[1])) {
        return;
    }
    $sender = get_user_by_id($sender_id);
    if (!$sender) {
        return;
    }
    $template = $settings['message'] ?? '{sender} sizi bir gönderide bahsetti';
    $link = get_post_notification_link($post_id);
    foreach (array_unique($matches[1]) as $username) {
        $mentioned = get_user_by_username($username);
        if ($mentioned && (int)$mentioned['id'] !== $sender_id) {
            $message = str_replace('{sender}', $sender['username'], $template);
            create_notification((int)$mentioned['id'], 'mention', $message, $link);
        }
    }
}
