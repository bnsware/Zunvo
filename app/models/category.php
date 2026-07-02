<?php

function get_all_categories() {
    return db_query_all("SELECT * FROM categories ORDER BY order_num ASC, name ASC");
}

function get_root_categories() {
    return db_query_all(
        "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY order_num ASC, name ASC"
    );
}

function get_child_forums($parent_id) {
    return db_query_all(
        "SELECT * FROM categories WHERE parent_id = ? ORDER BY order_num ASC, name ASC",
        [$parent_id]
    );
}

function get_category_tree() {
    $roots = get_root_categories();
    foreach ($roots as &$root) {
        $root['children'] = get_child_forums($root['id']);
        foreach ($root['children'] as &$child) {
            $child['last_topic'] = get_category_last_topic($child['id']);
        }
        unset($child);
    }
    unset($root);
    return $roots;
}

function get_category_breadcrumb($category) {
    $crumb = [$category];
    $parent_id = $category['parent_id'] ?? null;
    while ($parent_id) {
        $parent = get_category_by_id($parent_id);
        if (!$parent) {
            break;
        }
        array_unshift($crumb, $parent);
        $parent_id = $parent['parent_id'];
    }
    return $crumb;
}

function get_leaf_forums($user = null) {
    if ($user === null) {
        $user = current_user();
    }
    $forums = db_query_all(
        "SELECT c.* FROM categories c
         WHERE c.forum_type = 'forum'
         AND NOT EXISTS (SELECT 1 FROM categories ch WHERE ch.parent_id = c.id AND ch.forum_type = 'forum')
         ORDER BY c.order_num ASC, c.name ASC"
    );
    if ($user && ($user['role'] ?? '') === 'admin') {
        return $forums;
    }
    return array_values(array_filter($forums, function ($forum) {
        return !empty($forum['can_create_topic']);
    }));
}

function get_category_by_id($category_id) {
    return db_query_row("SELECT * FROM categories WHERE id = ?", [$category_id]);
}

function get_category_by_slug($slug) {
    return db_query_row("SELECT * FROM categories WHERE slug = ?", [$slug]);
}

function category_is_leaf_forum($category) {
    if (($category['forum_type'] ?? 'forum') === 'section') {
        return false;
    }
    if (($category['forum_type'] ?? 'forum') === 'link') {
        return false;
    }
    $children = get_child_forums($category['id']);
    foreach ($children as $child) {
        if ($child['forum_type'] === 'forum') {
            return false;
        }
    }
    return true;
}

function user_can_create_topic_in_category($category, $user = null) {
    if (!category_is_leaf_forum($category)) {
        return false;
    }
    if (!empty($category['can_create_topic'])) {
        return true;
    }
    if ($user === null) {
        $user = current_user();
    }
    return $user && ($user['role'] ?? '') === 'admin';
}

function category_allows_topics($category) {
    return category_is_leaf_forum($category);
}

function create_category($data) {
    $slug = create_slug($data['name']);
    $existing = get_category_by_slug($slug);
    if ($existing) {
        $slug = $slug . '-' . time();
    }
    $query = "INSERT INTO categories (parent_id, name, slug, description, icon, color, forum_type, can_create_topic, order_num)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    return db_insert($query, [
        !empty($data['parent_id']) ? $data['parent_id'] : null,
        $data['name'],
        $slug,
        $data['description'] ?? '',
        $data['icon'] ?? 'folder',
        $data['color'] ?? '#0d9488',
        $data['forum_type'] ?? 'forum',
        isset($data['can_create_topic']) ? (int)$data['can_create_topic'] : 1,
        $data['order_num'] ?? 0
    ]);
}

function update_category($category_id, $data) {
    $updates = [];
    $params = [];

    if (array_key_exists('parent_id', $data)) {
        $updates[] = "parent_id = ?";
        $params[] = $data['parent_id'];
    }
    if (isset($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
        $slug = create_slug($data['name']);
        $existing = db_query_row(
            "SELECT id FROM categories WHERE slug = ? AND id != ?",
            [$slug, $category_id]
        );
        if ($existing) {
            $slug = $slug . '-' . $category_id;
        }
        $updates[] = "slug = ?";
        $params[] = $slug;
    }
    if (isset($data['description'])) {
        $updates[] = "description = ?";
        $params[] = $data['description'];
    }
    if (isset($data['icon'])) {
        $updates[] = "icon = ?";
        $params[] = $data['icon'];
    }
    if (isset($data['color'])) {
        $updates[] = "color = ?";
        $params[] = $data['color'];
    }
    if (isset($data['forum_type'])) {
        $updates[] = "forum_type = ?";
        $params[] = $data['forum_type'];
    }
    if (isset($data['can_create_topic'])) {
        $updates[] = "can_create_topic = ?";
        $params[] = (int)$data['can_create_topic'];
    }
    if (isset($data['order_num'])) {
        $updates[] = "order_num = ?";
        $params[] = $data['order_num'];
    }

    if (empty($updates)) {
        return false;
    }

    $params[] = $category_id;
    $query = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
    return db_execute($query, $params);
}

function delete_category($category_id) {
    $children = get_child_forums($category_id);
    if (!empty($children)) {
        return false;
    }
    $topic_count = db_count('topics', 'category_id = ?', [$category_id]);
    if ($topic_count > 0) {
        return false;
    }
    return db_execute("DELETE FROM categories WHERE id = ?", [$category_id]);
}

function get_category_last_topic($category_id) {
    return db_query_row(
        "SELECT t.*, u.username
         FROM topics t
         JOIN users u ON t.user_id = u.id
         WHERE t.category_id = ?
         ORDER BY t.updated_at DESC
         LIMIT 1",
        [$category_id]
    );
}

function get_category_stats($category_id) {
    $category = get_category_by_id($category_id);
    if ($category && isset($category['topic_count'])) {
        $topic_count = (int)$category['topic_count'];
        $post_count = (int)$category['post_count'];
    } else {
        $topic_count = db_count('topics', 'category_id = ?', [$category_id]);
        $post_count = db_query_value(
            "SELECT COUNT(*) FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.category_id = ? AND p.is_deleted = 0",
            [$category_id]
        );
    }
    $last_topic = get_category_last_topic($category_id);
    return [
        'topics' => $topic_count ?: 0,
        'posts' => $post_count ?: 0,
        'last_topic' => $last_topic
    ];
}

function get_category_topics($category_id, $page = 1, $per_page = TOPICS_PER_PAGE) {
    $offset = ($page - 1) * $per_page;
    return db_query_all(
        "SELECT t.*, u.username, u.avatar,
         (SELECT COUNT(*) FROM posts WHERE topic_id = t.id AND is_deleted = 0) as post_count,
         (SELECT username FROM users WHERE id = (
             SELECT user_id FROM posts WHERE topic_id = t.id AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1
         )) as last_poster
         FROM topics t
         JOIN users u ON t.user_id = u.id
         WHERE t.category_id = ?
         ORDER BY t.is_pinned DESC, t.updated_at DESC
         LIMIT ? OFFSET ?",
        [$category_id, $per_page, $offset]
    );
}
