<?php

function zunvo_is_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
}

function zunvo_detect_base_path() {
    if (defined('BASE_PATH_OVERRIDE')) {
        return BASE_PATH_OVERRIDE;
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $base = rtrim(dirname($script), '/');
    if ($base === '/' || $base === '.') {
        return '';
    }
    return $base;
}

function zunvo_detect_site_url() {
    if (defined('SITE_URL_OVERRIDE')) {
        return SITE_URL_OVERRIDE;
    }
    $scheme = zunvo_is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . zunvo_detect_base_path();
}
