<?php

require_once __DIR__ . '/award.php';

function award_badge($user_id, $badge_slug) {
    $exists = db_query_row("SELECT id FROM user_badges WHERE user_id = ? AND badge_slug = ?", [$user_id, $badge_slug]);
    if ($exists) {
        return false;
    }
    return db_insert("INSERT INTO user_badges (user_id, badge_slug) VALUES (?, ?)", [$user_id, $badge_slug]);
}

function get_user_badges($user_id) {
    return db_query_all("SELECT * FROM user_badges WHERE user_id = ? ORDER BY earned_at ASC", [$user_id]);
}

function get_badge_label($slug) {
    $labels = [
        'first_topic' => 'İlk Konu',
        'first_solution' => 'İlk Çözüm',
        'hundred_upvotes' => '100 Beğeni',
        'veteran' => 'Veteran',
        'legend' => 'Efsane'
    ];
    return $labels[$slug] ?? $slug;
}

function check_user_badges($user_id) {
    check_auto_awards($user_id);
    $topic_count = db_count('topics', 'user_id = ?', [$user_id]);
    if ($topic_count >= 1) {
        award_badge($user_id, 'first_topic');
    }
    $solution_count = db_count('posts', 'user_id = ? AND is_solution = 1', [$user_id]);
    if ($solution_count >= 1) {
        award_badge($user_id, 'first_solution');
    }
    $total_upvotes = db_query_value("SELECT COALESCE(SUM(upvotes),0) FROM posts WHERE user_id = ?", [$user_id]);
    if ($total_upvotes >= 100) {
        award_badge($user_id, 'hundred_upvotes');
    }
    $user = get_user_by_id($user_id);
    if ($user && $user['reputation'] >= 500) {
        award_badge($user_id, 'veteran');
    }
    if ($user && $user['reputation'] >= 1000) {
        award_badge($user_id, 'legend');
    }
}

function create_report($reporter_id, $reported_user_id, $post_id, $reason) {
    return db_insert(
        "INSERT INTO reports (reporter_id, reported_user_id, post_id, reason) VALUES (?, ?, ?, ?)",
        [$reporter_id, $reported_user_id, $post_id, $reason]
    );
}
