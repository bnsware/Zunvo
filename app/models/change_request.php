<?php

require_once APP_PATH . '/models/notification.php';

function create_change_request($type, $topic_id, $user_id, $old_value, $new_value) {
    return db_insert(
        "INSERT INTO change_requests (type, topic_id, requested_by, old_value, new_value) VALUES (?, ?, ?, ?, ?)",
        [$type, $topic_id, $user_id, $old_value, $new_value]
    );
}

function get_pending_change_requests($limit = 50, $offset = 0) {
    return db_query_all(
        "SELECT cr.*, t.title as current_title, t.slug, u.username as requester_name
         FROM change_requests cr
         JOIN topics t ON cr.topic_id = t.id
         JOIN users u ON cr.requested_by = u.id
         WHERE cr.status = 'pending'
         ORDER BY cr.created_at ASC
         LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
}

function get_change_request($id) {
    return db_query_row(
        "SELECT cr.*, t.title as current_title, t.slug, t.category_id
         FROM change_requests cr
         JOIN topics t ON cr.topic_id = t.id
         WHERE cr.id = ?",
        [$id]
    );
}

function approve_change_request($id, $reviewer_id) {
    $req = get_change_request($id);
    if (!$req || $req['status'] !== 'pending') {
        return false;
    }
    if ($req['type'] === 'title_change') {
        update_topic($req['topic_id'], ['title' => $req['new_value']]);
        $updated = get_topic_by_id($req['topic_id']);
        create_notification(
            $req['requested_by'],
            'title_approved',
            'Başlık değişikliğiniz onaylandı: ' . $req['new_value'],
            url('/konu/' . ($updated['slug'] ?? $req['slug']))
        );
    }
    db_execute(
        "UPDATE change_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
        [$reviewer_id, $id]
    );
    return true;
}

function reject_change_request($id, $reviewer_id) {
    $req = get_change_request($id);
    if (!$req || $req['status'] !== 'pending') {
        return false;
    }
    db_execute(
        "UPDATE change_requests SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?",
        [$reviewer_id, $id]
    );
    create_notification(
        $req['requested_by'],
        'title_rejected',
        'Başlık değişikliğiniz reddedildi.',
        url('/konu/' . $req['slug'])
    );
    return true;
}

function count_pending_change_requests() {
    return db_count('change_requests', "status = 'pending'");
}
