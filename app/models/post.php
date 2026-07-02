<?php

require_once __DIR__ . '/user.php';
require_once __DIR__ . '/notification.php';

function get_post_by_id($post_id) {
    return db_query_row(
        "SELECT p.*, u.username, u.avatar, u.reputation, u.created_at as user_joined
         FROM posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.id = ?",
        [$post_id]
    );
}

function get_topic_posts($topic_id, $page = 1, $per_page = POSTS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    return db_query_all(
        "SELECT p.*, u.username, u.avatar, u.reputation, u.created_at as user_joined,
         (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as user_post_count
         FROM posts p
         JOIN users u ON p.user_id = u.id
         WHERE p.topic_id = ? AND p.is_deleted = 0
         ORDER BY p.is_solution DESC, p.created_at ASC
         LIMIT ? OFFSET ?",
        [$topic_id, $per_page, $offset]
    );
}

function get_topic_post_count($topic_id) {
    return db_count('posts', 'topic_id = ? AND is_deleted = 0', [$topic_id]);
}

function create_post($data) {
    $query = "INSERT INTO posts (topic_id, user_id, content, created_at) 
              VALUES (?, ?, ?, NOW())";
    $post_id = db_insert($query, [
        $data['topic_id'],
        $data['user_id'],
        $data['content']
    ]);
    if ($post_id) {
        touch_topic($data['topic_id']);
        process_mentions($data['content'], $post_id, $data['user_id']);
        notify_topic_author($data['topic_id'], $post_id, $data['user_id']);
    }
    return $post_id;
}

function update_post($post_id, $content) {
    return db_execute(
        "UPDATE posts SET content = ?, updated_at = NOW() WHERE id = ?",
        [$content, $post_id]
    );
}

function delete_post($post_id) {
    return db_execute("UPDATE posts SET is_deleted = 1 WHERE id = ?", [$post_id]);
}

function hard_delete_post($post_id) {
    return db_execute("DELETE FROM posts WHERE id = ?", [$post_id]);
}

function mark_as_solution($post_id, $topic_id) {
    db_begin_transaction();
    try {
        db_execute("UPDATE posts SET is_solution = 0 WHERE topic_id = ?", [$topic_id]);
        db_execute("UPDATE posts SET is_solution = 1 WHERE id = ?", [$post_id]);
        $post = get_post_by_id($post_id);
        if ($post) {
            update_user_reputation($post['user_id'], REPUTATION_BEST_ANSWER);
            notify_solution_marked($post_id, $topic_id);
        }
        db_commit();
        return true;
    } catch (Exception $e) {
        db_rollback();
        return false;
    }
}

function unmark_solution($post_id) {
    return db_execute("UPDATE posts SET is_solution = 0 WHERE id = ?", [$post_id]);
}

function get_user_recent_posts($user_id, $limit = 10) {
    return db_query_all(
        "SELECT p.*, t.title as topic_title, t.slug as topic_slug
         FROM posts p
         JOIN topics t ON p.topic_id = t.id
         WHERE p.user_id = ? AND p.is_deleted = 0
         ORDER BY p.created_at DESC
         LIMIT ?",
        [$user_id, $limit]
    );
}

function get_user_post_count($user_id) {
    return db_count('posts', 'user_id = ? AND is_deleted = 0', [$user_id]);
}

function vote_post($post_id, $user_id, $vote_type) {
    $existing_vote = db_query_row(
        "SELECT * FROM votes WHERE post_id = ? AND user_id = ?",
        [$post_id, $user_id]
    );
    db_begin_transaction();
    try {
        if ($existing_vote) {
            if ($existing_vote['vote_type'] === $vote_type) {
                db_execute("DELETE FROM votes WHERE id = ?", [$existing_vote['id']]);
                $column = $vote_type === 'up' ? 'upvotes' : 'downvotes';
                db_execute("UPDATE posts SET {$column} = {$column} - 1 WHERE id = ?", [$post_id]);
                $post = get_post_by_id($post_id);
                $rep_change = $vote_type === 'up' ? -REPUTATION_UPVOTE : -REPUTATION_DOWNVOTE;
                update_user_reputation($post['user_id'], $rep_change);
            } else {
                db_execute(
                    "UPDATE votes SET vote_type = ? WHERE id = ?",
                    [$vote_type, $existing_vote['id']]
                );
                $old_column = $existing_vote['vote_type'] === 'up' ? 'upvotes' : 'downvotes';
                $new_column = $vote_type === 'up' ? 'upvotes' : 'downvotes';
                db_execute("UPDATE posts SET {$old_column} = {$old_column} - 1, {$new_column} = {$new_column} + 1 WHERE id = ?", [$post_id]);
                $post = get_post_by_id($post_id);
                $old_rep = $existing_vote['vote_type'] === 'up' ? -REPUTATION_UPVOTE : -REPUTATION_DOWNVOTE;
                $new_rep = $vote_type === 'up' ? REPUTATION_UPVOTE : REPUTATION_DOWNVOTE;
                update_user_reputation($post['user_id'], $old_rep + $new_rep);
                if ($vote_type === 'up') {
                    notify_upvote($post_id, $user_id);
                }
            }
        } else {
            db_insert(
                "INSERT INTO votes (post_id, user_id, vote_type) VALUES (?, ?, ?)",
                [$post_id, $user_id, $vote_type]
            );
            $column = $vote_type === 'up' ? 'upvotes' : 'downvotes';
            db_execute("UPDATE posts SET {$column} = {$column} + 1 WHERE id = ?", [$post_id]);
            $post = get_post_by_id($post_id);
            $rep_change = $vote_type === 'up' ? REPUTATION_UPVOTE : REPUTATION_DOWNVOTE;
            update_user_reputation($post['user_id'], $rep_change);
            if ($vote_type === 'up') {
                notify_upvote($post_id, $user_id);
            }
        }
        db_commit();
        return true;
    } catch (Exception $e) {
        db_rollback();
        log_error("Vote error: " . $e->getMessage());
        return false;
    }
}

function get_user_vote($post_id, $user_id) {
    $vote = db_query_row(
        "SELECT vote_type FROM votes WHERE post_id = ? AND user_id = ?",
        [$post_id, $user_id]
    );
    return $vote ? $vote['vote_type'] : null;
}
