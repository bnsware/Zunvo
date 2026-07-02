<?php

function cache_get($key) {
    if (!CACHE_ENABLED) {
        return null;
    }
    $file = CACHE_PATH . '/' . md5($key) . '.cache';
    if (!file_exists($file)) {
        return null;
    }
    $data = unserialize(file_get_contents($file));
    if ($data['expires'] < time()) {
        unlink($file);
        return null;
    }
    return $data['value'];
}

function cache_set($key, $value, $ttl = null) {
    if (!CACHE_ENABLED) {
        return false;
    }
    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0755, true);
    }
    $ttl = $ttl ?? CACHE_LIFETIME;
    $file = CACHE_PATH . '/' . md5($key) . '.cache';
    $data = ['expires' => time() + $ttl, 'value' => $value];
    return file_put_contents($file, serialize($data)) !== false;
}

function cache_delete($key) {
    $file = CACHE_PATH . '/' . md5($key) . '.cache';
    if (file_exists($file)) {
        unlink($file);
    }
}

function cache_flush() {
    if (!is_dir(CACHE_PATH)) {
        return;
    }
    foreach (glob(CACHE_PATH . '/*.cache') as $file) {
        unlink($file);
    }
}
