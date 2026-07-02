<?php

require_once __DIR__ . '/config/config.php';
require_once CORE_PATH . '/functions.php';

redirect_index_php_to_root();
redirect_to_install_if_needed();

require_once CORE_PATH . '/database.php';
require_once CORE_PATH . '/security.php';
require_once CORE_PATH . '/cache.php';
require_once CORE_PATH . '/markdown.php';
require_once CORE_PATH . '/bbcode.php';
require_once CORE_PATH . '/plugin.php';
require_once CORE_PATH . '/theme.php';
require_once CORE_PATH . '/theme_shell.php';
require_once CORE_PATH . '/mail.php';
require_once CORE_PATH . '/router.php';
require_once CORE_PATH . '/migrate.php';
require_once CORE_PATH . '/permissions.php';
require_once CORE_PATH . '/admin_helpers.php';

try_remember_login();

if (is_logged_in()) {
    prevent_session_hijacking();
}

plugin_boot();
theme_boot();
run_migrations();

add_route('giris', 'auth', 'login');
add_route('kayit', 'auth', 'register');
add_route('cikis', 'auth', 'logout');
add_route('dogrula/{token}', 'auth', 'verify');
add_route('sifremi-unuttum', 'auth', 'forgot_password');
add_route('sifre-sifirla/{token}', 'auth', 'reset_password');
add_route('profil/{username}', 'auth', 'profile');
add_route('profil-duzenle', 'auth', 'edit_profile');

add_route('konular', 'topic', 'index');
add_route('konu/olustur', 'topic', 'create');
add_route('kategori/{slug}/yeni-konu', 'topic', 'create_in_category');
add_route('konu/{slug}', 'topic', 'show');
add_route('konu/{slug}/mod', 'topic', 'mod_action');
add_route('konu/duzenle/{slug}', 'topic', 'edit');
add_route('kategori/{slug}', 'topic', 'category');
add_route('kategoriler', 'topic', 'categories');
add_route('etiket/{slug}', 'topic', 'tag');
add_route('arama', 'topic', 'search');
add_route('hakkimizda', 'home', 'about');
add_route('tema/sec', 'home', 'set_theme');
add_route('home/widget', 'home', 'widget');

add_route('topic/add-post', 'topic', 'add_post');
add_route('topic/edit-post', 'topic', 'edit_post');
add_route('topic/delete-post', 'topic', 'delete_post');
add_route('topic/mark-solution', 'topic', 'mark_solution');
add_route('topic/report', 'topic', 'report');

add_route('vote/submit', 'vote', 'submit');
add_route('vote/get', 'vote', 'get');
add_route('vote/stats', 'vote', 'stats');
add_route('vote/batch', 'vote', 'batch');
add_route('vote/top', 'vote', 'top_posts');
add_route('vote/user/{username}', 'vote', 'user_votes');
add_route('vote/log', 'vote', 'log');

add_route('notification/get', 'notification', 'get');
add_route('notification/unread-count', 'notification', 'unread_count');
add_route('notification/mark-read', 'notification', 'mark_read');
add_route('notification/mark-all-read', 'notification', 'mark_all_read');
add_route('notification/delete', 'notification', 'delete');
add_route('notification/delete-all', 'notification', 'delete_all');
add_route('notification/poll', 'notification', 'poll');
add_route('notification/test', 'notification', 'test');
add_route('notification/cleanup', 'notification', 'cleanup');
add_route('bildirimler', 'notification', 'index');
add_route('bildirim/ayarlar', 'notification', 'settings');

add_route('cron/plugins', 'cron', 'plugins');

add_route('user/search', 'user', 'search');

add_route('mod', 'mod', 'dashboard');
add_route('mod/konular', 'mod', 'topics');
add_route('mod/raporlar', 'mod', 'reports');
add_route('mod/onaylar', 'mod', 'approvals');
add_route('mod/log', 'mod', 'log');

add_route('admin', 'admin', 'dashboard');
add_route('admin/kullanicilar', 'admin', 'users');
add_route('admin/kullanici/{id}', 'admin', 'user_edit');
add_route('admin/kategoriler', 'admin', 'categories');
add_route('admin/konular', 'admin', 'topics');
add_route('admin/raporlar', 'admin', 'reports');
add_route('admin/ayarlar', 'admin', 'settings');
add_route('admin/mod-log', 'admin', 'mod_log');
add_route('admin/pluginler', 'admin', 'plugins');
add_route('admin/temalar', 'admin', 'themes');
add_route('admin/api', 'admin', 'api_keys');
add_route('admin/moderatorler', 'admin', 'moderators');
add_route('admin/onaylar', 'admin', 'approvals');
add_route('admin/oduller', 'admin', 'awards');
add_route('admin/widget', 'admin', 'widget_settings');
add_route('admin/pluginler/{slug}/ayarlar', 'admin', 'plugin_settings');
add_route('admin/temalar/sablon', 'admin', 'theme_template');
add_route('admin/temalar/stil', 'admin', 'theme_style');

add_route('api/v1/topics', 'api', 'topics');
add_route('api/v1/topics/{id}', 'api', 'topic_show');
add_route('api/v1/posts', 'api', 'posts');
add_route('api/v1/vote', 'api', 'vote');
add_route('api/v1/users/{username}', 'api', 'user_show');
add_route('api/v1/notifications', 'api', 'notifications');
add_route('api/v1/search', 'api', 'search');
add_route('api/v1/preview-bbcode', 'api', 'preview_bbcode');
add_route('api/v1/preview-markdown', 'api', 'preview_bbcode');

handle_route();
