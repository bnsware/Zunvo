<?php

function get_theme_meta() {
    return get_theme_meta_for_slug(get_active_theme_slug());
}

function list_theme_templates() {
    $templates = [];
    foreach (get_theme_resolution_chain() as $slug) {
        $theme_dir = THEME_PATH . '/' . $slug . '/templates';
        if (!is_dir($theme_dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($theme_dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($theme_dir) + 1));
                $key = preg_replace('/\.php$/', '', $rel);
                $templates[$key] = true;
            }
        }
    }
    $keys = array_keys($templates);
    sort($keys);
    return $keys;
}

function get_template_source_path($key) {
    return resolve_theme_template_path($key);
}

function get_template_content($key) {
    $override = db_query_row("SELECT content FROM theme_template_overrides WHERE template_key = ?", [$key]);
    if ($override) {
        return $override['content'];
    }
    $path = get_template_source_path($key);
    return $path ? file_get_contents($path) : '';
}

function save_template_override($key, $content, $user_id) {
    $exists = db_query_row("SELECT id FROM theme_template_overrides WHERE template_key = ?", [$key]);
    if ($exists) {
        return db_execute(
            "UPDATE theme_template_overrides SET content = ?, updated_by = ?, updated_at = NOW() WHERE template_key = ?",
            [$content, $user_id, $key]
        );
    }
    return db_insert(
        "INSERT INTO theme_template_overrides (template_key, content, updated_by) VALUES (?, ?, ?)",
        [$key, $content, $user_id]
    );
}

function delete_template_override($key) {
    return db_execute("DELETE FROM theme_template_overrides WHERE template_key = ?", [$key]);
}

function resolve_view_path($view) {
    if (strpos($view, 'admin/') === 0 || strpos($view, 'mod/') === 0) {
        $app_file = APP_PATH . '/views/' . $view . '.php';
        if (!file_exists($app_file)) {
            die('View dosyası bulunamadı: ' . htmlspecialchars($view));
        }
        return $app_file;
    }
    $override = db_query_row("SELECT content FROM theme_template_overrides WHERE template_key = ?", [$view]);
    if ($override) {
        $tmp = STORAGE_PATH . '/cache/tpl_' . md5($view) . '.php';
        if (!is_dir(STORAGE_PATH . '/cache')) {
            mkdir(STORAGE_PATH . '/cache', 0755, true);
        }
        file_put_contents($tmp, $override['content']);
        return $tmp;
    }
    $path = resolve_theme_template_path($view);
    if ($path) {
        return $path;
    }
    die('Tema şablonu bulunamadı: ' . htmlspecialchars($view) . ' (aktif tema: ' . htmlspecialchars(get_active_theme_slug()) . ')');
}

function get_theme_style_vars_css() {
    $props = json_decode(get_setting('theme_style_props', '{}'), true);
    if (empty($props)) {
        return '';
    }
    $css = ':root{';
    foreach ($props as $k => $v) {
        $css .= $k . ':' . $v . ';';
    }
    $css .= '}';
    return $css;
}
