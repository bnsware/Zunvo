<?php

require_once __DIR__ . '/user.php';
require_once __DIR__ . '/notification.php';

function get_all_awards() {
    return db_query_all("SELECT * FROM awards ORDER BY name ASC");
}

function get_award_by_id($id) {
    return db_query_row("SELECT * FROM awards WHERE id = ?", [$id]);
}

function create_award($data) {
    $slug = $data['slug'] ?: create_slug($data['name']);
    return db_insert(
        "INSERT INTO awards (slug, name, description, icon, criteria_type, criteria_value) VALUES (?, ?, ?, ?, ?, ?)",
        [$slug, $data['name'], $data['description'] ?? '', $data['icon'] ?? 'award', $data['criteria_type'] ?? 'manual', $data['criteria_value'] ?? 0]
    );
}

function delete_award($id) {
    db_execute("DELETE FROM user_awards WHERE award_id = ?", [$id]);
    return db_execute("DELETE FROM awards WHERE id = ?", [$id]);
}

function grant_award($user_id, $award_id, $granted_by = null) {
    $exists = db_query_row("SELECT id FROM user_awards WHERE user_id = ? AND award_id = ?", [$user_id, $award_id]);
    if ($exists) {
        return false;
    }
    return db_insert(
        "INSERT INTO user_awards (user_id, award_id, granted_by) VALUES (?, ?, ?)",
        [$user_id, $award_id, $granted_by]
    );
}

function get_user_awards_list($user_id) {
    return db_query_all(
        "SELECT a.*, ua.granted_at FROM user_awards ua
         JOIN awards a ON ua.award_id = a.id
         WHERE ua.user_id = ? ORDER BY ua.granted_at DESC",
        [$user_id]
    );
}

function check_auto_awards($user_id) {
    $awards = db_query_all("SELECT * FROM awards WHERE is_active = 1 AND criteria_type != 'manual'");
    $user = get_user_by_id($user_id);
    if (!$user) {
        return;
    }
    foreach ($awards as $award) {
        $met = false;
        switch ($award['criteria_type']) {
            case 'topic_count':
                $met = db_count('topics', 'user_id = ?', [$user_id]) >= $award['criteria_value'];
                break;
            case 'post_count':
                $met = db_count('posts', 'user_id = ? AND is_deleted = 0', [$user_id]) >= $award['criteria_value'];
                break;
            case 'reputation':
                $met = $user['reputation'] >= $award['criteria_value'];
                break;
            case 'solution_count':
                $met = db_count('posts', 'user_id = ? AND is_solution = 1', [$user_id]) >= $award['criteria_value'];
                break;
            case 'membership_days':
                $days = db_query_value("SELECT DATEDIFF(NOW(), created_at) FROM users WHERE id = ?", [$user_id]);
                $met = $days >= $award['criteria_value'];
                break;
        }
        if ($met) {
            grant_award($user_id, $award['id']);
        }
    }
}
