<?php

define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

define('SITE_NAME', 'Zunvo Forum');
define('SITE_DESCRIPTION', 'Modern Forum Sistemi');
define('SITE_KEYWORDS', 'forum, community, discussion');

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PLUGIN_PATH', ROOT_PATH . '/plugins');
define('THEME_PATH', ROOT_PATH . '/themes');

require_once __DIR__ . '/../core/detect.php';
require_once __DIR__ . '/../core/icons.php';

define('BASE_PATH', zunvo_detect_base_path());
define('SITE_URL', zunvo_detect_site_url());
define('IS_HTTPS', zunvo_is_https());

define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('AVATAR_PATH', UPLOAD_PATH . '/avatars');
define('ATTACHMENT_PATH', UPLOAD_PATH . '/attachments');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_FILE_TYPES', ['application/pdf', 'application/zip', 'text/plain']);

define('SESSION_LIFETIME', 7200);
define('PASSWORD_MIN_LENGTH', 8);
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600);

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

define('POSTS_PER_PAGE', 20);
define('TOPICS_PER_PAGE', 30);
define('USERS_PER_PAGE', 50);
define('TOPIC_VIEW_COOLDOWN', 86400);

define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@example.com');
define('MAIL_PASSWORD', 'your-smtp-password');
define('MAIL_FROM_EMAIL', 'noreply@example.com');
define('MAIL_FROM_NAME', 'Zunvo Forum');
define('MAIL_ENCRYPTION', 'tls');

define('TIMEZONE', 'Europe/Istanbul');
date_default_timezone_set(TIMEZONE);

define('DEFAULT_LANGUAGE', 'tr');
define('SUPPORTED_LANGUAGES', ['tr', 'en']);

define('CACHE_ENABLED', true);
define('CACHE_PATH', STORAGE_PATH . '/cache');
define('CACHE_LIFETIME', 3600);

define('LOG_ENABLED', true);
define('LOG_PATH', STORAGE_PATH . '/logs');
define('LOG_FILE', LOG_PATH . '/zunvo_' . date('Y-m-d') . '.log');

define('REPUTATION_NEW_USER', 0);
define('REPUTATION_UPVOTE', 10);
define('REPUTATION_DOWNVOTE', -5);
define('REPUTATION_BEST_ANSWER', 50);
define('REPUTATION_POST_DOWNVOTED', -2);

define('USER_LEVELS', [
    'Yeni' => 0,
    'Aktif' => 100,
    'Veteran' => 500,
    'Efsane' => 1000
]);

define('PLUGINS_ENABLED', true);
define('THEMES_ENABLED', true);
define('DEFAULT_THEME', 'default');
define('WEBHOOK_SECRET', 'zunvo-webhook-secret-change-me');

require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    $session_path = BASE_PATH === '' ? '/' : BASE_PATH . '/';
    session_start([
        'cookie_lifetime' => SESSION_LIFETIME,
        'cookie_path' => $session_path,
        'cookie_httponly' => true,
        'cookie_secure' => IS_HTTPS,
        'use_strict_mode' => true,
        'sid_length' => 48,
        'sid_bits_per_character' => 6
    ]);
}
