<?php

function run_migrations() {
    if (!is_installed()) {
        return;
    }
    $version = (int)get_setting('db_version', 1);
    if ($version >= 2) {
        return;
    }
    migrate_to_v2();
    set_setting('db_version', '2');
}

function migrate_to_v2() {
    if (!db_column_exists('categories', 'parent_id')) {
        db_execute("ALTER TABLE categories ADD COLUMN parent_id INT UNSIGNED DEFAULT NULL AFTER id");
        db_execute("ALTER TABLE categories ADD INDEX idx_categories_parent (parent_id)");
    }
    if (!db_column_exists('categories', 'forum_type')) {
        db_execute("ALTER TABLE categories ADD COLUMN forum_type ENUM('section','forum','link') DEFAULT 'forum' AFTER color");
    }
    if (!db_column_exists('categories', 'topic_count')) {
        db_execute("ALTER TABLE categories ADD COLUMN topic_count INT UNSIGNED DEFAULT 0 AFTER forum_type");
    }
    if (!db_column_exists('categories', 'post_count')) {
        db_execute("ALTER TABLE categories ADD COLUMN post_count INT UNSIGNED DEFAULT 0 AFTER topic_count");
    }
    if (!db_column_exists('categories', 'can_create_topic')) {
        db_execute("ALTER TABLE categories ADD COLUMN can_create_topic TINYINT(1) DEFAULT 1 AFTER post_count");
    }

    if (!db_table_exists('topic_visits')) {
        db_execute("CREATE TABLE topic_visits (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            topic_id INT UNSIGNED NOT NULL,
            visited_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_visit (user_id, topic_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            INDEX idx_visits_user (user_id, visited_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (!db_table_exists('change_requests')) {
        db_execute("CREATE TABLE change_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL DEFAULT 'title_change',
            topic_id INT UNSIGNED NOT NULL,
            requested_by INT UNSIGNED NOT NULL,
            old_value TEXT,
            new_value TEXT NOT NULL,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            reviewed_by INT UNSIGNED DEFAULT NULL,
            reviewed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_change_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (!db_table_exists('moderator_permissions')) {
        db_execute("CREATE TABLE moderator_permissions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            permission_key VARCHAR(50) NOT NULL,
            UNIQUE KEY uniq_mod_perm (user_id, permission_key),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (!db_table_exists('awards')) {
        db_execute("CREATE TABLE awards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'award',
            criteria_type ENUM('manual','topic_count','post_count','reputation','solution_count','membership_days') DEFAULT 'manual',
            criteria_value INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (!db_table_exists('user_awards')) {
        db_execute("CREATE TABLE user_awards (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            award_id INT UNSIGNED NOT NULL,
            granted_by INT UNSIGNED DEFAULT NULL,
            granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_award (user_id, award_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (award_id) REFERENCES awards(id) ON DELETE CASCADE,
            FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    if (!db_table_exists('theme_template_overrides')) {
        db_execute("CREATE TABLE theme_template_overrides (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            template_key VARCHAR(150) NOT NULL UNIQUE,
            content MEDIUMTEXT NOT NULL,
            updated_by INT UNSIGNED DEFAULT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    db_execute("UPDATE categories SET forum_type = 'forum' WHERE forum_type IS NULL OR forum_type = ''");
    refresh_all_category_counts();
    seed_default_awards();
}

function seed_default_awards() {
    $defaults = [
        ['first_topic', 'İlk Konu', 'İlk konunuzu açtınız', 'message', 'topic_count', 1],
        ['first_solution', 'İlk Çözüm', 'İlk çözümünüz işaretlendi', 'check', 'solution_count', 1],
        ['hundred_upvotes', '100 Beğeni', 'Gönderileriniz 100 beğeni aldı', 'thumbs-up', 'reputation', 100],
        ['veteran', 'Veteran', '500+ itibar puanı', 'star', 'reputation', 500],
        ['legend', 'Efsane', '1000+ itibar puanı', 'award', 'reputation', 1000],
    ];
    foreach ($defaults as $row) {
        $exists = db_query_row("SELECT id FROM awards WHERE slug = ?", [$row[0]]);
        if (!$exists) {
            db_insert(
                "INSERT INTO awards (slug, name, description, icon, criteria_type, criteria_value) VALUES (?, ?, ?, ?, ?, ?)",
                $row
            );
        }
    }
}

function refresh_category_counts($category_id) {
    $topic_count = db_count('topics', 'category_id = ?', [$category_id]);
    $post_count = db_query_value(
        "SELECT COUNT(*) FROM posts p JOIN topics t ON p.topic_id = t.id WHERE t.category_id = ? AND p.is_deleted = 0",
        [$category_id]
    );
    db_execute("UPDATE categories SET topic_count = ?, post_count = ? WHERE id = ?", [$topic_count, $post_count ?: 0, $category_id]);
}

function refresh_all_category_counts() {
    $categories = db_query_all("SELECT id FROM categories");
    foreach ($categories as $cat) {
        refresh_category_counts($cat['id']);
    }
}
