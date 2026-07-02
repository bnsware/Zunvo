<?php

require_once APP_PATH . '/models/user.php';
require_once APP_PATH . '/models/category.php';
require_once APP_PATH . '/models/topic.php';
require_once APP_PATH . '/models/change_request.php';
require_once APP_PATH . '/models/award.php';

function admin_dashboard() {
    require_admin();

    $stats = [
        'users' => db_count('users'),
        'topics' => db_count('topics'),
        'posts' => db_count('posts'),
        'reports_pending' => db_count('reports', "status = 'pending'"),
        'approvals_pending' => function_exists('count_pending_change_requests') ? count_pending_change_requests() : 0
    ];

    $recent_topics = db_query_all(
        "SELECT t.id, t.title, t.slug, t.created_at, u.username
         FROM topics t
         JOIN users u ON t.user_id = u.id
         ORDER BY t.created_at DESC
         LIMIT 5"
    );

    $recent_users = db_query_all(
        "SELECT id, username, email, role, created_at
         FROM users
         ORDER BY created_at DESC
         LIMIT 5"
    );

    $recent_reports = db_query_all(
        "SELECT r.id, r.reason, r.status, r.created_at, u.username as reporter
         FROM reports r
         JOIN users u ON r.reporter_id = u.id
         ORDER BY r.created_at DESC
         LIMIT 5"
    );

    render('admin/dashboard', [
        'title' => 'Yönetim Paneli',
        'admin_page' => 'dashboard',
        'stats' => $stats,
        'recent_topics' => $recent_topics,
        'recent_users' => $recent_users,
        'recent_reports' => $recent_reports
    ], 'admin/layout');
}

function admin_users() {
    require_admin();

    $search = trim(get_param('q', ''));
    $page = max(1, (int)get_param('page', 1));
    $per_page = USERS_PER_PAGE;

    $where = '1=1';
    $params = [];

    if ($search !== '') {
        $where .= " AND (username LIKE ? OR email LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $total = db_query_value("SELECT COUNT(*) FROM users WHERE {$where}", $params);
    $pagination = get_pagination($total, $per_page, $page);

    $params[] = $per_page;
    $params[] = $pagination['offset'];

    $users = db_query_all(
        "SELECT id, username, email, role, reputation, is_banned, created_at, last_active
         FROM users
         WHERE {$where}
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?",
        $params
    );

    render('admin/users', [
        'title' => 'Kullanıcılar',
        'admin_page' => 'users',
        'users' => $users,
        'search' => $search,
        'pagination' => $pagination
    ], 'admin/layout');
}

function admin_user_edit($id) {
    require_admin();

    $id = (int)$id;
    $user = db_query_row("SELECT * FROM users WHERE id = ?", [$id]);

    if (!$user) {
        set_flash('error', 'Kullanıcı bulunamadı.');
        redirect(url('/admin/kullanicilar'));
    }

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/kullanici/' . $id));
        }

        $action = post_param('action');

        if ($action === 'update_role') {
            $role = post_param('role');
            if (change_user_role($id, $role)) {
                log_mod_action('role_change', 'user', $id, 'Rol: ' . $role);
                set_flash('success', 'Kullanıcı rolü güncellendi.');
            } else {
                set_flash('error', 'Rol güncellenemedi.');
            }
        } elseif ($action === 'ban') {
            if (ban_user($id, true)) {
                log_mod_action('ban', 'user', $id, 'Kullanıcı yasaklandı');
                set_flash('success', 'Kullanıcı yasaklandı.');
            } else {
                set_flash('error', 'Yasaklama başarısız.');
            }
        } elseif ($action === 'unban') {
            if (ban_user($id, false)) {
                log_mod_action('unban', 'user', $id, 'Yasak kaldırıldı');
                set_flash('success', 'Yasak kaldırıldı.');
            } else {
                set_flash('error', 'İşlem başarısız.');
            }
        }

        redirect(url('/admin/kullanici/' . $id));
    }

    $stats = get_user_stats($id);

    render('admin/user_edit', [
        'title' => 'Kullanıcı Düzenle',
        'admin_page' => 'users',
        'user' => $user,
        'stats' => $stats
    ], 'admin/layout');
}

function admin_categories() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/kategoriler'));
        }

        $action = post_param('action');

        if ($action === 'add') {
            $name = trim(post_param('name', ''));
            if ($name === '') {
                set_flash('error', 'Kategori adı gerekli.');
            } else {
                $norm = admin_normalize_category_post();
                if (!empty($norm['error'])) {
                    set_flash('error', $norm['error']);
                } else {
                $result = create_category([
                    'name' => $name,
                    'description' => trim(post_param('description', '')),
                    'icon' => trim(post_param('icon', 'folder')),
                    'color' => trim(post_param('color', '#0d9488')),
                    'order_num' => (int)post_param('order_num', 0),
                    'parent_id' => $norm['parent_id'],
                    'forum_type' => $norm['forum_type'],
                    'can_create_topic' => $norm['can_create_topic']
                ]);
                if ($result) {
                    log_mod_action('category_create', 'category', $result, $name);
                    set_flash('success', 'Kayıt eklendi.');
                } else {
                    set_flash('error', 'Kayıt eklenemedi.');
                }
                }
            }
        } elseif ($action === 'edit') {
            $cat_id = (int)post_param('id');
            $name = trim(post_param('name', ''));
            if (!$cat_id) {
                set_flash('error', 'Düzenlenecek kayıt bulunamadı.');
            } elseif ($name === '') {
                set_flash('error', 'Kategori adı gerekli.');
            } else {
                $norm = admin_normalize_category_post($cat_id);
                if (!empty($norm['error'])) {
                    set_flash('error', $norm['error']);
                } elseif (update_category($cat_id, [
                    'name' => $name,
                    'description' => trim(post_param('description', '')),
                    'icon' => trim(post_param('icon', 'folder')),
                    'color' => trim(post_param('color', '#0d9488')),
                    'order_num' => (int)post_param('order_num', 0),
                    'parent_id' => $norm['parent_id'],
                    'forum_type' => $norm['forum_type'],
                    'can_create_topic' => $norm['can_create_topic']
                ])) {
                    log_mod_action('category_update', 'category', $cat_id, $name);
                    set_flash('success', 'Kayıt güncellendi.');
                } else {
                    set_flash('error', 'Kayıt güncellenemedi.');
                }
            }
        } elseif ($action === 'delete') {
            $cat_id = (int)post_param('id');
            if (!$cat_id) {
                set_flash('error', 'Geçersiz kayıt.');
            } elseif (!empty(get_child_forums($cat_id))) {
                set_flash('error', 'Önce alt forumları silin.');
            } elseif (db_count('topics', 'category_id = ?', [$cat_id]) > 0) {
                set_flash('error', 'İçinde konu olan forum silinemez.');
            } elseif (delete_category($cat_id)) {
                log_mod_action('category_delete', 'category', $cat_id, '');
                set_flash('success', 'Kayıt silindi.');
            } else {
                set_flash('error', 'Kayıt silinemedi.');
            }
        }

        redirect(url('/admin/kategoriler'));
    }

    $categories = get_all_categories();
    $sections = admin_forum_parent_options($categories);
    $tree = admin_forum_display_tree($categories);

    render('admin/categories', [
        'title' => 'Forum Yapısı',
        'admin_page' => 'categories',
        'categories' => $categories,
        'sections' => $sections,
        'parent_options' => $sections,
        'tree' => $tree,
        'cat_map' => admin_category_map($categories),
    ], 'admin/layout');
}

function admin_topics() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/konular'));
        }

        $topic_id = (int)post_param('id');
        $action = post_param('action');

        if ($topic_id && $action === 'pin') {
            pin_topic($topic_id, true);
            log_mod_action('pin', 'topic', $topic_id, '');
            set_flash('success', 'Konu sabitlendi.');
        } elseif ($topic_id && $action === 'unpin') {
            pin_topic($topic_id, false);
            log_mod_action('unpin', 'topic', $topic_id, '');
            set_flash('success', 'Sabitleme kaldırıldı.');
        } elseif ($topic_id && $action === 'lock') {
            lock_topic($topic_id, true);
            log_mod_action('lock', 'topic', $topic_id, '');
            set_flash('success', 'Konu kilitlendi.');
        } elseif ($topic_id && $action === 'unlock') {
            lock_topic($topic_id, false);
            log_mod_action('unlock', 'topic', $topic_id, '');
            set_flash('success', 'Kilit kaldırıldı.');
        } elseif ($topic_id && $action === 'delete') {
            if (delete_topic($topic_id)) {
                log_mod_action('delete', 'topic', $topic_id, '');
                set_flash('success', 'Konu silindi.');
            } else {
                set_flash('error', 'Konu silinemedi.');
            }
        }

        redirect(url('/admin/konular'));
    }

    $search = trim(get_param('q', ''));
    $page = max(1, (int)get_param('page', 1));
    $per_page = TOPICS_PER_PAGE;

    $where = '1=1';
    $params = [];

    if ($search !== '') {
        $where .= " AND t.title LIKE ?";
        $params[] = '%' . $search . '%';
    }

    $total = db_query_value(
        "SELECT COUNT(*) FROM topics t WHERE {$where}",
        $params
    );
    $pagination = get_pagination($total, $per_page, $page);

    $params[] = $per_page;
    $params[] = $pagination['offset'];

    $topics = db_query_all(
        "SELECT t.*, u.username, c.name as category_name,
         (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as post_count
         FROM topics t
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         WHERE {$where}
         ORDER BY t.is_pinned DESC, t.updated_at DESC
         LIMIT ? OFFSET ?",
        $params
    );

    render('admin/topics', [
        'title' => 'Konular',
        'admin_page' => 'topics',
        'topics' => $topics,
        'search' => $search,
        'pagination' => $pagination
    ], 'admin/layout');
}

function admin_reports() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/raporlar'));
        }

        $report_id = (int)post_param('id');
        $action = post_param('action');

        if ($report_id && $action === 'resolve') {
            db_execute("UPDATE reports SET status = 'resolved' WHERE id = ?", [$report_id]);
            log_mod_action('report_resolve', 'report', $report_id, '');
            set_flash('success', 'Rapor çözüldü olarak işaretlendi.');
        } elseif ($report_id && $action === 'reject') {
            db_execute("UPDATE reports SET status = 'rejected' WHERE id = ?", [$report_id]);
            log_mod_action('report_reject', 'report', $report_id, '');
            set_flash('success', 'Rapor reddedildi.');
        }

        redirect(url('/admin/raporlar'));
    }

    $status = get_param('status', 'pending');
    $allowed_statuses = ['pending', 'resolved', 'rejected', 'all'];
    if (!in_array($status, $allowed_statuses)) {
        $status = 'pending';
    }

    $page = max(1, (int)get_param('page', 1));
    $per_page = 30;

    $where = '1=1';
    $params = [];

    if ($status !== 'all') {
        $where .= " AND r.status = ?";
        $params[] = $status;
    }

    $total = db_query_value("SELECT COUNT(*) FROM reports r WHERE {$where}", $params);
    $pagination = get_pagination($total, $per_page, $page);

    $params[] = $per_page;
    $params[] = $pagination['offset'];

    $reports = db_query_all(
        "SELECT r.*, u.username as reporter_name,
         ru.username as reported_username, p.content as post_content
         FROM reports r
         JOIN users u ON r.reporter_id = u.id
         LEFT JOIN users ru ON r.reported_user_id = ru.id
         LEFT JOIN posts p ON r.post_id = p.id
         WHERE {$where}
         ORDER BY r.created_at DESC
         LIMIT ? OFFSET ?",
        $params
    );

    render('admin/reports', [
        'title' => 'Raporlar',
        'admin_page' => 'reports',
        'reports' => $reports,
        'status' => $status,
        'pagination' => $pagination
    ], 'admin/layout');
}

function admin_settings() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/ayarlar'));
        }

        set_setting('site_name', trim(post_param('site_name', '')));
        set_setting('site_description', trim(post_param('site_description', '')));
        set_setting('about_content', trim(post_param('about_content', '')));
        set_setting('registration_enabled', post_param('registration_enabled') ? '1' : '0');

        log_mod_action('settings_update', 'settings', 0, '');
        set_flash('success', 'Ayarlar kaydedildi.');
        redirect(url('/admin/ayarlar'));
    }

    $stats = [
        'users' => db_count('users'),
        'topics' => db_count('topics'),
        'posts' => db_count('posts'),
        'categories' => db_count('categories'),
    ];

    render('admin/settings', [
        'title' => 'Site Ayarları',
        'admin_page' => 'settings',
        'site_name' => get_setting('site_name', SITE_NAME),
        'site_description' => get_setting('site_description', SITE_DESCRIPTION),
        'about_content' => get_setting('about_content', 'Zunvo, modern ve açık kaynak forum yazılımıdır.'),
        'registration_enabled' => get_setting('registration_enabled', '1'),
        'widget_enabled' => get_setting('homepage_widget_enabled', '1') === '1',
        'stats' => $stats,
    ], 'admin/layout');
}

function admin_mod_log() {
    require_admin();

    $page = max(1, (int)get_param('page', 1));
    $per_page = 50;

    $total = db_count('mod_logs');
    $pagination = get_pagination($total, $per_page, $page);

    $logs = db_query_all(
        "SELECT ml.*, u.username as moderator_name
         FROM mod_logs ml
         JOIN users u ON ml.moderator_id = u.id
         ORDER BY ml.created_at DESC
         LIMIT ? OFFSET ?",
        [$per_page, $pagination['offset']]
    );

    render('admin/mod_log', [
        'title' => 'Moderasyon Logları',
        'admin_page' => 'mod_log',
        'logs' => $logs,
        'pagination' => $pagination
    ], 'admin/layout');
}

function admin_plugins() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/pluginler'));
        }

        $slug = post_param('slug');
        $action = post_param('action');

        if ($slug && $action === 'activate') {
            activate_plugin($slug);
            log_mod_action('plugin_activate', 'plugin', 0, $slug);
            set_flash('success', 'Plugin etkinleştirildi.');
        } elseif ($slug && $action === 'deactivate') {
            deactivate_plugin($slug);
            log_mod_action('plugin_deactivate', 'plugin', 0, $slug);
            set_flash('success', 'Plugin devre dışı bırakıldı.');
        }

        redirect(url('/admin/pluginler'));
    }

    $scanned = sync_plugins();

    $plugins = db_query_all("SELECT * FROM plugins ORDER BY name ASC");

    render('admin/plugins', [
        'title' => 'Pluginler',
        'admin_page' => 'plugins',
        'plugins' => $plugins,
        'scanned_count' => count($scanned)
    ], 'admin/layout');
}

function admin_themes() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/temalar'));
        }
        $slug = post_param('slug');
        $action = post_param('action');
        if ($action === 'upload') {
            $activate_after = post_param('activate_after') === '1';
            $result = install_theme_from_zip($_FILES['theme_zip'] ?? [], $activate_after);
            if (!empty($result['ok'])) {
                $msg = '"' . ($result['name'] ?? $result['slug']) . '" teması yüklendi';
                if (!empty($result['replaced'])) {
                    $msg .= ' (mevcut sürümün üzerine yazıldı)';
                }
                if (!empty($result['activated'])) {
                    $msg .= ' ve etkinleştirildi';
                } elseif (!empty($result['activate_errors'])) {
                    $msg .= '. Etkinleştirilemedi: ' . implode(' ', $result['activate_errors']);
                }
                $msg .= '.';
                log_mod_action('theme_upload', 'theme', 0, $result['slug']);
                set_flash('success', $msg);
            } elseif (!empty($result['installed'])) {
                log_mod_action('theme_upload', 'theme', 0, $result['slug'] ?? '');
                set_flash('error', implode(' ', $result['errors'] ?? ['Tema yüklenemedi.']));
            } else {
                set_flash('error', implode(' ', $result['errors'] ?? ['Tema yüklenemedi.']));
            }
            redirect(url('/admin/temalar'));
        }
        if ($slug && $action === 'activate') {
            $result = activate_theme($slug);
            if ($result === true) {
                log_mod_action('theme_activate', 'theme', 0, $slug);
                set_flash('success', 'Tema etkinleştirildi.');
            } else {
                set_flash('error', 'Tema etkinleştirilemedi: ' . implode(' ', $result));
            }
        }
        redirect(url('/admin/temalar'));
    }

    $scanned = scan_themes();
    sync_theme_registry($scanned);

    $themes = db_query_all("SELECT * FROM themes ORDER BY name ASC");
    $active_slug = function_exists('get_active_theme_slug') ? get_active_theme_slug() : 'default';
    $theme_meta = [];
    $theme_validation = [];
    foreach ($scanned as $meta) {
        if (!empty($meta['hidden'])) {
            continue;
        }
        $theme_meta[$meta['slug']] = $meta;
        $theme_validation[$meta['slug']] = validate_theme($meta['slug']);
    }

    render('admin/themes', [
        'title' => 'Tema',
        'admin_page' => 'themes',
        'themes' => $themes,
        'theme_meta' => $theme_meta,
        'theme_validation' => $theme_validation,
        'active_slug' => $active_slug,
        'scanned_count' => count($theme_meta)
    ], 'admin/layout');
}

function admin_api_keys() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/api'));
        }

        $action = post_param('action');

        if ($action === 'create') {
            $name = trim(post_param('name', ''));
            $user = current_user();
            if ($name !== '' && $user) {
                $api_key = bin2hex(random_bytes(32));
                $id = db_insert(
                    "INSERT INTO api_keys (user_id, name, api_key) VALUES (?, ?, ?)",
                    [$user['id'], $name, $api_key]
                );
                if ($id) {
                    log_mod_action('api_key_create', 'api_key', $id, $name);
                    set_flash('success', 'API anahtarı oluşturuldu: ' . $api_key);
                }
            } else {
                set_flash('error', 'Anahtar adı gerekli.');
            }
        } elseif ($action === 'deactivate') {
            $key_id = (int)post_param('id');
            if ($key_id) {
                db_execute("UPDATE api_keys SET is_active = 0 WHERE id = ?", [$key_id]);
                log_mod_action('api_key_deactivate', 'api_key', $key_id, '');
                set_flash('success', 'API anahtarı devre dışı bırakıldı.');
            }
        } elseif ($action === 'delete') {
            $key_id = (int)post_param('id');
            if ($key_id) {
                db_execute("DELETE FROM api_keys WHERE id = ?", [$key_id]);
                log_mod_action('api_key_delete', 'api_key', $key_id, '');
                set_flash('success', 'API anahtarı silindi.');
            }
        }

        redirect(url('/admin/api'));
    }

    $api_keys = db_query_all(
        "SELECT ak.*, u.username
         FROM api_keys ak
         JOIN users u ON ak.user_id = u.id
         ORDER BY ak.created_at DESC"
    );

    render('admin/api_keys', [
        'title' => 'API Anahtarları',
        'admin_page' => 'api_keys',
        'api_keys' => $api_keys
    ], 'admin/layout');
}

function admin_webhooks() {
    require_admin();

    if (is_post()) {
        if (!validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
            set_flash('error', 'Geçersiz istek.');
            redirect(url('/admin/webhooks'));
        }

        $action = post_param('action');

        if ($action === 'create') {
            $name = trim(post_param('name', ''));
            $url = trim(post_param('url', ''));
            $event_type = trim(post_param('event_type', ''));
            if ($name !== '' && $url !== '' && $event_type !== '') {
                $id = db_insert(
                    "INSERT INTO webhooks (name, url, event_type) VALUES (?, ?, ?)",
                    [$name, $url, $event_type]
                );
                if ($id) {
                    log_mod_action('webhook_create', 'webhook', $id, $name);
                    set_flash('success', 'Webhook oluşturuldu.');
                }
            } else {
                set_flash('error', 'Tüm alanlar gerekli.');
            }
        } elseif ($action === 'toggle') {
            $hook_id = (int)post_param('id');
            $active = (int)post_param('is_active', 0);
            if ($hook_id) {
                db_execute("UPDATE webhooks SET is_active = ? WHERE id = ?", [$active, $hook_id]);
                log_mod_action('webhook_toggle', 'webhook', $hook_id, $active ? 'active' : 'inactive');
                set_flash('success', 'Webhook durumu güncellendi.');
            }
        } elseif ($action === 'delete') {
            $hook_id = (int)post_param('id');
            if ($hook_id) {
                db_execute("DELETE FROM webhooks WHERE id = ?", [$hook_id]);
                log_mod_action('webhook_delete', 'webhook', $hook_id, '');
                set_flash('success', 'Webhook silindi.');
            }
        } elseif ($action === 'test') {
            $hook_id = (int)post_param('id');
            if ($hook_id) {
                $hook = db_query_row("SELECT * FROM webhooks WHERE id = ?", [$hook_id]);
                if (!$hook) {
                    set_flash('error', 'Webhook bulunamadı.');
                } else {
                    $result = send_test_webhook($hook['url'], $hook['event_type']);
                    if ($result['error'] !== '') {
                        set_flash('error', 'Bağlantı hatası: ' . $result['error']);
                    } elseif ($result['code'] >= 200 && $result['code'] < 300) {
                        set_flash('success', 'Test gönderildi (HTTP ' . $result['code'] . ').');
                    } else {
                        $detail = truncate($result['response'], 200);
                        set_flash('error', 'Test başarısız (HTTP ' . $result['code'] . ')' . ($detail !== '' ? ': ' . $detail : '') . '.');
                    }
                }
            } else {
                $url = trim(post_param('url', ''));
                $event_type = trim(post_param('event_type', ''));
                if ($url === '' || $event_type === '') {
                    set_flash('error', 'Test için URL ve olay türü gerekli.');
                } else {
                    $result = send_test_webhook($url, $event_type);
                    if ($result['error'] !== '') {
                        set_flash('error', 'Bağlantı hatası: ' . $result['error']);
                    } elseif ($result['code'] >= 200 && $result['code'] < 300) {
                        set_flash('success', 'Test gönderildi (HTTP ' . $result['code'] . ').');
                    } else {
                        $detail = truncate($result['response'], 200);
                        set_flash('error', 'Test başarısız (HTTP ' . $result['code'] . ')' . ($detail !== '' ? ': ' . $detail : '') . '.');
                    }
                }
            }
        }

        redirect(url('/admin/webhooks'));
    }

    $webhooks = db_query_all("SELECT * FROM webhooks ORDER BY created_at DESC");

    render('admin/webhooks', [
        'title' => 'Webhooks',
        'admin_page' => 'webhooks',
        'webhooks' => $webhooks
    ], 'admin/layout');
}

function admin_moderators() {
    require_admin();
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $user_id = (int)post_param('user_id');
        $perms = post_param('permissions') ?? [];
        if (!is_array($perms)) {
            $perms = [];
        }
        set_moderator_permissions($user_id, $perms);
        change_user_role($user_id, 'moderator');
        log_mod_action('mod_permissions', 'user', $user_id, implode(',', $perms));
        set_flash('success', 'Moderatör yetkileri kaydedildi.');
        redirect(url('/admin/moderatorler'));
    }
    $moderators = db_query_all("SELECT id, username, email, role FROM users WHERE role IN ('moderator','admin') ORDER BY username ASC");
    $selected_id = (int)get_param('user_id', $moderators[0]['id'] ?? 0);
    $selected_perms = $selected_id ? get_moderator_permissions($selected_id) : [];
    render('admin/moderators', [
        'title' => 'Moderatörler',
        'admin_page' => 'moderators',
        'moderators' => $moderators,
        'permission_keys' => get_mod_permission_keys(),
        'selected_id' => $selected_id,
        'selected_perms' => $selected_perms
    ], 'admin/layout');
}

function admin_approvals() {
    require_admin();
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $req_id = (int)post_param('request_id');
        $action = post_param('action');
        $user = current_user();
        if ($action === 'approve') {
            approve_change_request($req_id, $user['id']);
        } elseif ($action === 'reject') {
            reject_change_request($req_id, $user['id']);
        }
        redirect(url('/admin/onaylar'));
    }
    render('admin/approvals', [
        'title' => 'Onay Kuyruğu',
        'admin_page' => 'approvals',
        'requests' => get_pending_change_requests()
    ], 'admin/layout');
}

function admin_awards() {
    require_admin();
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $action = post_param('action');
        if ($action === 'add') {
            create_award([
                'name' => trim(post_param('name', '')),
                'slug' => trim(post_param('slug', '')),
                'description' => trim(post_param('description', '')),
                'icon' => trim(post_param('icon', 'award')),
                'criteria_type' => post_param('criteria_type', 'manual'),
                'criteria_value' => (int)post_param('criteria_value', 0)
            ]);
            set_flash('success', 'Ödül eklendi.');
        } elseif ($action === 'grant') {
            grant_award((int)post_param('user_id'), (int)post_param('award_id'), current_user()['id']);
            set_flash('success', 'Ödül verildi.');
        } elseif ($action === 'delete') {
            delete_award((int)post_param('award_id'));
            set_flash('success', 'Ödül silindi.');
        }
        redirect(url('/admin/oduller'));
    }
    render('admin/awards', [
        'title' => 'Ödüller',
        'admin_page' => 'awards',
        'awards' => get_all_awards(),
        'users' => db_query_all("SELECT id, username FROM users ORDER BY username ASC LIMIT 200")
    ], 'admin/layout');
}

function admin_widget_settings() {
    require_admin();
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        set_setting('homepage_widget_enabled', post_param('homepage_widget_enabled') ? '1' : '0');
        $keys = post_param('tab_keys', []);
        $cats = post_param('tab_categories', []);
        if (!is_array($keys)) {
            $keys = [];
        }
        if (!is_array($cats)) {
            $cats = [];
        }
        $tabs = admin_build_widget_tabs($keys, $cats, get_all_categories());
        set_setting('homepage_widget_tabs', json_encode($tabs, JSON_UNESCAPED_UNICODE));
        set_flash('success', 'Widget ayarları kaydedildi.');
        redirect(url('/admin/widget'));
    }
    $tabs_json = get_setting('homepage_widget_tabs', '');
    $parsed = admin_parse_widget_tabs($tabs_json);
    render('admin/widget', [
        'title' => 'Ana Sayfa',
        'admin_page' => 'widget',
        'enabled' => get_setting('homepage_widget_enabled', '1') === '1',
        'active_keys' => $parsed['keys'],
        'active_cats' => $parsed['category_ids'],
        'categories' => get_all_categories()
    ], 'admin/layout');
}

function admin_plugin_settings($slug) {
    require_admin();
    $plugin = db_query_row("SELECT * FROM plugins WHERE slug = ?", [$slug]);
    if (!$plugin) {
        set_flash('error', 'Plugin bulunamadı.');
        redirect(url('/admin/pluginler'));
    }
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $action = post_param('action');
        if ($action === 'activate') {
            activate_plugin($slug);
            log_mod_action('plugin_activate', 'plugin', 0, $slug);
            set_flash('success', 'Plugin etkinleştirildi.');
            redirect(url('/admin/pluginler/' . $slug . '/ayarlar'));
        }
        if ($action === 'deactivate') {
            deactivate_plugin($slug);
            log_mod_action('plugin_deactivate', 'plugin', 0, $slug);
            set_flash('success', 'Plugin devre dışı bırakıldı.');
            redirect(url('/admin/pluginler/' . $slug . '/ayarlar'));
        }
        $meta = get_plugin_meta($slug) ?: [];
        $settings = [];
        foreach (($meta['settings'] ?? []) as $key => $field) {
            $type = $field['type'] ?? 'text';
            if ($type === 'info') {
                continue;
            }
            if ($type === 'toggle') {
                $settings[$key] = post_param('setting_' . $key) ? '1' : '0';
            } else {
                $settings[$key] = post_param('setting_' . $key, $field['default'] ?? '');
            }
        }
        $is_active = !empty($plugin['is_active']);
        if (array_key_exists('enabled', $settings)) {
            $is_active = $settings['enabled'] === '1';
        }
        save_plugin_settings($slug, $settings, $is_active);
        set_flash('success', 'Plugin ayarları kaydedildi.');
        redirect(url('/admin/pluginler/' . $slug . '/ayarlar'));
    }
    $meta = get_plugin_meta($slug) ?: [];
    $saved = get_plugin_settings($slug);
    $plugin = db_query_row("SELECT * FROM plugins WHERE slug = ?", [$slug]);
    render('admin/plugin_settings', [
        'title' => 'Plugin Ayarları',
        'admin_page' => 'plugins',
        'plugin' => $plugin,
        'meta' => $meta,
        'saved' => $saved
    ], 'admin/layout');
}

function admin_theme_template() {
    require_admin();
    require_once APP_PATH . '/models/theme.php';
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $key = post_param('template_key');
        $content = post_param('content', '');
        $action = post_param('action');
        if ($action === 'reset') {
            delete_template_override($key);
            set_flash('success', 'Şablon orijinale döndü.');
        } else {
            save_template_override($key, $content, current_user()['id']);
            set_flash('success', 'Şablon kaydedildi.');
        }
        redirect(url('/admin/temalar/sablon?key=' . urlencode($key)));
    }
    $templates = list_theme_templates();
    $selected = get_param('key', $templates[0] ?? 'home');
    render('admin/theme_templates', [
        'title' => 'Tema Şablonları',
        'admin_page' => 'themes',
        'templates' => $templates,
        'selected' => $selected,
        'content' => get_template_content($selected)
    ], 'admin/layout');
}

function admin_theme_style() {
    require_admin();
    require_once APP_PATH . '/models/theme.php';
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $props = post_param('style_props') ?? [];
        if (!is_array($props)) {
            $props = [];
        }
        set_setting('theme_style_props', json_encode($props, JSON_UNESCAPED_UNICODE));
        $custom_css = post_param('custom_css', '');
        $css_file = THEME_PATH . '/zunvo/custom.css';
        if (is_writable(dirname($css_file)) || file_exists($css_file)) {
            file_put_contents($css_file, $custom_css);
        }
        set_flash('success', 'Stil ayarları kaydedildi.');
        redirect(url('/admin/temalar/stil'));
    }
    $props = json_decode(get_setting('theme_style_props', '{}'), true) ?: [];
    $theme_meta = get_theme_meta();
    $custom_css = '';
    $css_file = THEME_PATH . '/zunvo/custom.css';
    if (file_exists($css_file)) {
        $custom_css = file_get_contents($css_file);
    }
    render('admin/theme_style', [
        'title' => 'Tema Stilleri',
        'admin_page' => 'themes',
        'style_properties' => $theme_meta['style_properties'] ?? [],
        'props' => $props,
        'custom_css' => $custom_css
    ], 'admin/layout');
}
