<?php

function is_valid_theme_slug($slug) {
    if (!is_string($slug) || $slug === '' || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
        return false;
    }
    return is_dir(THEME_PATH . '/' . $slug) && file_exists(THEME_PATH . '/' . $slug . '/theme.json');
}

function list_installed_themes() {
    static $list = null;
    if ($list !== null) {
        return $list;
    }
    $list = [];
    foreach (scan_themes() as $meta) {
        if (!empty($meta['hidden'])) {
            continue;
        }
        $list[] = [
            'slug' => $meta['slug'],
            'name' => $meta['name'] ?? ucfirst($meta['slug']),
            'description' => $meta['description'] ?? '',
        ];
    }
    usort($list, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    return $list;
}

function scan_themes() {
    $found = [];
    if (!is_dir(THEME_PATH)) {
        return $found;
    }
    foreach (scandir(THEME_PATH) as $dir) {
        if ($dir === '.' || $dir === '..' || $dir[0] === '_') {
            continue;
        }
        $theme_dir = THEME_PATH . '/' . $dir;
        if (!is_dir($theme_dir)) {
            continue;
        }
        $json_file = $theme_dir . '/theme.json';
        if (!file_exists($json_file)) {
            continue;
        }
        $meta = json_decode(file_get_contents($json_file), true);
        if (!is_array($meta)) {
            continue;
        }
        $meta['slug'] = $dir;
        $meta['name'] = $meta['name'] ?? ucfirst($dir);
        $meta['version'] = $meta['version'] ?? '1.0.0';
        $found[] = $meta;
    }
    usort($found, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    return $found;
}

function register_theme_in_db($meta) {
    $slug = $meta['slug'] ?? '';
    if ($slug === '') {
        return null;
    }
    $exists = db_query_row("SELECT id FROM themes WHERE slug = ?", [$slug]);
    if ($exists) {
        db_execute(
            "UPDATE themes SET name = ? WHERE slug = ?",
            [$meta['name'] ?? $slug, $slug]
        );
        return $exists['id'];
    }
    return db_insert(
        "INSERT INTO themes (name, slug, is_active) VALUES (?, ?, 0)",
        [$meta['name'] ?? $slug, $slug]
    );
}

function sync_theme_registry(?array $scanned = null) {
    if ($scanned === null) {
        $scanned = scan_themes();
    }
    $valid_slugs = array_column($scanned, 'slug');
    foreach ($scanned as $meta) {
        if (empty($meta['hidden'])) {
            register_theme_in_db($meta);
        }
    }
    $legacy = ['zunvo', 'classic-board', 'magazine', 'compact-dock', 'dark', 'atlas', 'nova', 'horizon'];
    foreach ($legacy as $slug) {
        $row = db_query_row("SELECT id, is_active FROM themes WHERE slug = ?", [$slug]);
        if (!$row) {
            continue;
        }
        if (!empty($row['is_active']) && in_array('default', $valid_slugs, true)) {
            db_execute("UPDATE themes SET is_active = 0");
            db_execute("UPDATE themes SET is_active = 1 WHERE slug = 'default'");
        }
        db_execute("DELETE FROM themes WHERE slug = ?", [$slug]);
    }
    $rows = db_query_all("SELECT slug FROM themes");
    foreach ($rows as $row) {
        if (!in_array($row['slug'], $valid_slugs, true)) {
            db_execute("DELETE FROM themes WHERE slug = ?", [$row['slug']]);
        }
    }
    if (!db_query_row("SELECT id FROM themes WHERE is_active = 1")) {
        if (in_array('default', $valid_slugs, true)) {
            db_execute("UPDATE themes SET is_active = 1 WHERE slug = 'default'");
        } elseif (!empty($valid_slugs)) {
            db_execute("UPDATE themes SET is_active = 1 WHERE slug = ?", [$valid_slugs[0]]);
        }
    }
}

function validate_theme($slug) {
    $errors = [];
    if (!is_valid_theme_slug($slug)) {
        $errors[] = 'theme.json bulunamadı veya slug geçersiz';
        return $errors;
    }
    $meta = get_theme_meta_for_slug($slug);
    if (empty($meta['name'])) {
        $errors[] = 'theme.json içinde name zorunlu';
    }
    $parent = $meta['extends'] ?? '';
    if ($parent !== '') {
        if ($parent === $slug) {
            $errors[] = 'Tema kendisini extend edemez';
        } elseif (!is_valid_theme_slug($parent)) {
            $errors[] = 'extends: ' . $parent . ' bulunamadı';
        } else {
            $visited = [];
            $current = $slug;
            while ($current) {
                if (isset($visited[$current])) {
                    $errors[] = 'extends zincirinde döngü var';
                    break;
                }
                $visited[$current] = true;
                $current = get_theme_meta_for_slug($current)['extends'] ?? '';
                if ($current !== '' && !is_string($current)) {
                    $current = '';
                }
            }
        }
    }
    $has_master = file_exists(THEME_PATH . '/' . $slug . '/templates/layout/master.php');
    if (!$has_master && $parent === '') {
        $errors[] = 'templates/layout/master.php gerekli (veya extends kullanın)';
    } elseif (!$has_master && !resolve_theme_template_path('layout/master', $slug)) {
        $errors[] = 'layout/master.php tema veya parent zincirinde bulunamadı';
    }
    $inherits = array_key_exists('inherit_styles', $meta) ? (bool)$meta['inherit_styles'] : true;
    if (!$inherits && !file_exists(THEME_PATH . '/' . $slug . '/style.css')) {
        $errors[] = 'inherit_styles false ise style.css gerekli';
    }
    if ($parent === '' && !file_exists(THEME_PATH . '/' . $slug . '/style.css')) {
        $errors[] = 'Ana tema için style.css gerekli';
    }
    if ($parent === '' && !is_dir(THEME_PATH . '/' . $slug . '/templates')) {
        $errors[] = 'Ana tema için templates/ klasörü gerekli';
    }
    return $errors;
}

function activate_theme($slug) {
    $errors = validate_theme($slug);
    if (!empty($errors)) {
        return $errors;
    }
    $meta = get_theme_meta_for_slug($slug);
    $meta['slug'] = $slug;
    register_theme_in_db($meta);
    db_execute("UPDATE themes SET is_active = 0");
    db_execute("UPDATE themes SET is_active = 1 WHERE slug = ?", [$slug]);
    return true;
}

function theme_registry_boot() {
    static $done = false;
    if ($done || !file_exists(STORAGE_PATH . '/install.lock')) {
        return;
    }
    $done = true;
    if (!function_exists('db_query_row')) {
        return;
    }
    sync_theme_registry(scan_themes());
}

function theme_boot() {
    if (!file_exists(STORAGE_PATH . '/install.lock')) {
        return;
    }
    theme_registry_boot();
    foreach (array_reverse(get_theme_resolution_chain()) as $theme_slug) {
        $bootstrap = THEME_PATH . '/' . $theme_slug . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            require_once $bootstrap;
        }
    }
}

function count_theme_templates($slug = null) {
    if ($slug === null) {
        $slug = get_active_theme_slug();
    }
    $templates = [];
    foreach (get_theme_resolution_chain($slug) as $theme_slug) {
        $theme_dir = THEME_PATH . '/' . $theme_slug . '/templates';
        if (!is_dir($theme_dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($theme_dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($theme_dir) + 1));
                $key = preg_replace('/\.php$/', '', $rel);
                $templates[$key] = $theme_slug;
            }
        }
    }
    return count($templates);
}

function get_site_theme_slug() {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $cached = 'default';
    if (function_exists('is_installed') && is_installed() && function_exists('db_query_row')) {
        $row = db_query_row("SELECT slug FROM themes WHERE is_active = 1 LIMIT 1");
        if ($row && !empty($row['slug']) && is_valid_theme_slug($row['slug'])) {
            $cached = $row['slug'];
        }
    }
    return $cached;
}

function get_user_theme_slug() {
    $cookie = $_COOKIE['zunvo-forum-theme'] ?? '';
    if ($cookie === '') {
        return null;
    }
    return is_valid_theme_slug($cookie) ? $cookie : null;
}

function set_user_theme_preference($slug) {
    $cookie_path = BASE_PATH === '' ? '/' : BASE_PATH . '/';
    if ($slug === null || $slug === '') {
        setcookie('zunvo-forum-theme', '', time() - 3600, $cookie_path, '', IS_HTTPS, true);
        unset($_COOKIE['zunvo-forum-theme']);
        return true;
    }
    if (!is_valid_theme_slug($slug)) {
        return false;
    }
    setcookie('zunvo-forum-theme', $slug, time() + (86400 * 365), $cookie_path, '', IS_HTTPS, true);
    $_COOKIE['zunvo-forum-theme'] = $slug;
    return true;
}

function get_active_theme_slug() {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $user_slug = get_user_theme_slug();
    if ($user_slug) {
        $cached = $user_slug;
        return $cached;
    }
    $cached = get_site_theme_slug();
    return $cached;
}

function get_active_theme() {
    $slug = get_active_theme_slug();
    $file = THEME_PATH . '/' . $slug . '/theme.json';
    if (file_exists($file)) {
        $meta = json_decode(file_get_contents($file), true);
        if (is_array($meta)) {
            return ['slug' => $slug, 'name' => $meta['name'] ?? $slug];
        }
    }
    return ['slug' => $slug, 'name' => ucfirst($slug)];
}

function get_theme_meta_for_slug($slug) {
    $file = THEME_PATH . '/' . $slug . '/theme.json';
    if (!file_exists($file)) {
        return [];
    }
    $meta = json_decode(file_get_contents($file), true);
    return is_array($meta) ? $meta : [];
}

function get_theme_resolution_chain($slug = null) {
    if ($slug === null) {
        $slug = get_active_theme_slug();
    }
    $chain = [];
    $visited = [];
    while ($slug && !isset($visited[$slug])) {
        if (!is_valid_theme_slug($slug)) {
            break;
        }
        $visited[$slug] = true;
        $chain[] = $slug;
        $parent = get_theme_meta_for_slug($slug)['extends'] ?? '';
        $slug = (is_string($parent) && $parent !== '') ? $parent : null;
    }
    return $chain;
}

function resolve_theme_template_path($view, $slug = null) {
    foreach (get_theme_resolution_chain($slug) as $theme_slug) {
        $path = THEME_PATH . '/' . $theme_slug . '/templates/' . $view . '.php';
        if (file_exists($path)) {
            return $path;
        }
    }
    return null;
}

function zunvo_set_view_vars($data) {
    $GLOBALS['zunvo_view_vars'] = is_array($data) ? $data : [];
}

function theme_partial($view, $data = []) {
    $vars = !empty($data) ? $data : ($GLOBALS['zunvo_view_vars'] ?? []);
    if (!empty($vars)) {
        extract($vars, EXTR_SKIP);
    }
    $path = resolve_theme_template_path($view);
    if ($path) {
        require $path;
    }
}

function theme_asset($path) {
    foreach (get_theme_resolution_chain() as $slug) {
        $rel = ltrim($path, '/');
        $file = THEME_PATH . '/' . $slug . '/assets/' . $rel;
        if (file_exists($file)) {
            return static_file_url('themes/' . $slug . '/assets/' . $rel, $file);
        }
    }
    return asset($path);
}

function get_theme_stylesheets() {
    $links = [];
    $seen = [];
    $slugs = get_theme_stylesheet_slugs();
    foreach ($slugs as $slug) {
        foreach (['style.css', 'partials.css', 'custom.css'] as $file) {
            $css = THEME_PATH . '/' . $slug . '/' . $file;
            $key = $slug . '/' . $file;
            if (isset($seen[$key]) || !file_exists($css) || filesize($css) <= 0) {
                continue;
            }
            $seen[$key] = true;
            $links[] = static_file_url('themes/' . $slug . '/' . $file, $css);
        }
    }
    return $links;
}

function theme_inherits_parent_styles($slug = null) {
    if ($slug === null) {
        $slug = get_active_theme_slug();
    }
    $meta = get_theme_meta_for_slug($slug);
    if (array_key_exists('inherit_styles', $meta)) {
        return (bool)$meta['inherit_styles'];
    }
    return true;
}

function get_theme_stylesheet_slugs() {
    $chain = array_reverse(get_theme_resolution_chain());
    if (empty($chain)) {
        return [];
    }
    if (!theme_inherits_parent_styles()) {
        return [get_active_theme_slug()];
    }
    return $chain;
}

function get_theme_inline_styles() {
    if (!function_exists('get_theme_style_vars_css')) {
        require_once APP_PATH . '/models/theme.php';
    }
    return get_theme_style_vars_css();
}

function get_theme_body_class() {
    $slug = get_active_theme_slug();
    $file = THEME_PATH . '/' . $slug . '/theme.json';
    if (file_exists($file)) {
        $meta = json_decode(file_get_contents($file), true);
        if (!empty($meta['body_class'])) {
            return $meta['body_class'];
        }
    }
    return 'theme-' . $slug;
}

function theme_zip_max_bytes() {
    if (defined('THEME_ZIP_MAX_SIZE')) {
        return (int)THEME_ZIP_MAX_SIZE;
    }
    if (defined('MAX_UPLOAD_SIZE')) {
        return max((int)MAX_UPLOAD_SIZE, 10 * 1024 * 1024);
    }
    return 10 * 1024 * 1024;
}

function theme_remove_directory($dir) {
    if (!is_dir($dir)) {
        return true;
    }
    $dir = rtrim(str_replace('\\', '/', $dir), '/');
    $items = scandir($dir);
    if ($items === false) {
        return false;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            if (!theme_remove_directory($path)) {
                return false;
            }
        } elseif (!@unlink($path)) {
            return false;
        }
    }
    return @rmdir($dir);
}

function theme_copy_directory($src, $dest) {
    if (!is_dir($src)) {
        return false;
    }
    if (!is_dir($dest) && !@mkdir($dest, 0755, true)) {
        return false;
    }
    $src = rtrim(str_replace('\\', '/', $src), '/');
    $dest = rtrim(str_replace('\\', '/', $dest), '/');
    $items = scandir($src);
    if ($items === false) {
        return false;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $from = $src . '/' . $item;
        $to = $dest . '/' . $item;
        if (is_dir($from)) {
            if (!theme_copy_directory($from, $to)) {
                return false;
            }
        } elseif (!@copy($from, $to)) {
            return false;
        }
    }
    return true;
}

function theme_zip_entry_is_safe($entry) {
    $entry = str_replace('\\', '/', $entry);
    if ($entry === '' || preg_match('#(^|/)\.\.(/|$)#', $entry)) {
        return false;
    }
    if ($entry[0] === '/' || preg_match('#^[a-zA-Z]:/#', $entry)) {
        return false;
    }
    return true;
}

function theme_zip_extract_safe($zip_path, $dest_dir) {
    if (!class_exists('ZipArchive')) {
        return ['ok' => false, 'errors' => ['Sunucuda ZipArchive eklentisi yok. Temayı manuel olarak themes/ klasörüne yükleyin.']];
    }
    $zip = new ZipArchive();
    if ($zip->open($zip_path) !== true) {
        return ['ok' => false, 'errors' => ['ZIP dosyası açılamadı.']];
    }
    if (!is_dir($dest_dir) && !@mkdir($dest_dir, 0755, true)) {
        $zip->close();
        return ['ok' => false, 'errors' => ['Geçici klasör oluşturulamadı.']];
    }
    $dest_dir = rtrim(str_replace('\\', '/', $dest_dir), '/');
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
        if ($entry === false || !theme_zip_entry_is_safe($entry)) {
            $zip->close();
            return ['ok' => false, 'errors' => ['ZIP içinde güvenli olmayan dosya yolu bulundu.']];
        }
        $entry = str_replace('\\', '/', $entry);
        if (str_ends_with($entry, '/')) {
            $target = $dest_dir . '/' . rtrim($entry, '/');
            if (!is_dir($target) && !@mkdir($target, 0755, true)) {
                $zip->close();
                return ['ok' => false, 'errors' => ['ZIP çıkarılırken klasör oluşturulamadı.']];
            }
            continue;
        }
        $target = $dest_dir . '/' . $entry;
        $parent = dirname($target);
        if (!is_dir($parent) && !@mkdir($parent, 0755, true)) {
            $zip->close();
            return ['ok' => false, 'errors' => ['ZIP çıkarılırken klasör oluşturulamadı.']];
        }
        $stream = $zip->getStream($entry);
        if ($stream === false) {
            $zip->close();
            return ['ok' => false, 'errors' => ['ZIP dosyası okunamadı.']];
        }
        $out = @fopen($target, 'wb');
        if ($out === false) {
            fclose($stream);
            $zip->close();
            return ['ok' => false, 'errors' => ['ZIP dosyası diske yazılamadı.']];
        }
        while (!feof($stream)) {
            $chunk = fread($stream, 8192);
            if ($chunk === false) {
                break;
            }
            fwrite($out, $chunk);
        }
        fclose($stream);
        fclose($out);
    }
    $zip->close();
    return ['ok' => true];
}

function resolve_extracted_theme_root($extract_dir) {
    $extract_dir = rtrim(str_replace('\\', '/', $extract_dir), '/');
    if (file_exists($extract_dir . '/theme.json')) {
        return $extract_dir;
    }
    $entries = array_diff(scandir($extract_dir) ?: [], ['.', '..']);
    $dirs = [];
    foreach ($entries as $entry) {
        if ($entry === '__MACOSX' || $entry === '.DS_Store') {
            continue;
        }
        $path = $extract_dir . '/' . $entry;
        if (is_dir($path)) {
            $dirs[] = $entry;
        }
    }
    if (count($dirs) === 1 && file_exists($extract_dir . '/' . $dirs[0] . '/theme.json')) {
        return $extract_dir . '/' . $dirs[0];
    }
    $best = null;
    $best_depth = PHP_INT_MAX;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extract_dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getFilename() !== 'theme.json') {
            continue;
        }
        $path = str_replace('\\', '/', $file->getPath());
        $rel = substr($path, strlen($extract_dir));
        $depth = substr_count(trim($rel, '/'), '/');
        if ($depth < $best_depth) {
            $best_depth = $depth;
            $best = $path;
        } elseif ($depth === $best_depth && $best !== $path) {
            return null;
        }
    }
    return $best;
}

function theme_slug_is_reserved($slug) {
    if ($slug === 'default' || $slug === '') {
        return true;
    }
    return $slug[0] === '_';
}

function install_theme_from_zip(array $file, $activate_after = false) {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'errors' => ['ZIP dosyası seçilmedi.']];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Dosya sunucu limitini aşıyor.',
            UPLOAD_ERR_FORM_SIZE => 'Dosya form limitini aşıyor.',
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi.',
        ];
        return ['ok' => false, 'errors' => [$messages[$file['error']] ?? 'Dosya yüklenemedi.']];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'errors' => ['Geçersiz yükleme.']];
    }
    if ((int)$file['size'] > theme_zip_max_bytes()) {
        $mb = (int)ceil(theme_zip_max_bytes() / (1024 * 1024));
        return ['ok' => false, 'errors' => ['ZIP dosyası en fazla ' . $mb . ' MB olabilir.']];
    }
    $name = $file['name'] ?? '';
    if (!preg_match('/\.zip$/i', $name)) {
        return ['ok' => false, 'errors' => ['Yalnızca .zip dosyası yüklenebilir.']];
    }
    $temp_base = STORAGE_PATH . '/temp/theme-' . bin2hex(random_bytes(8));
    $temp_parent = STORAGE_PATH . '/temp';
    if (!is_dir($temp_parent) && !@mkdir($temp_parent, 0755, true)) {
        return ['ok' => false, 'errors' => ['Geçici klasör oluşturulamadı (storage/temp).']];
    }
    $extract_result = theme_zip_extract_safe($file['tmp_name'], $temp_base);
    if (empty($extract_result['ok'])) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => $extract_result['errors'] ?? ['ZIP çıkarılamadı.']];
    }
    $theme_root = resolve_extracted_theme_root($temp_base);
    if (!$theme_root || !file_exists($theme_root . '/theme.json')) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['ZIP içinde geçerli tema bulunamadı (theme.json gerekli).']];
    }
    $slug = basename(str_replace('\\', '/', $theme_root));
    if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['Tema klasör adı yalnızca küçük harf, rakam ve tire içerebilir.']];
    }
    if (theme_slug_is_reserved($slug)) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['Bu tema adı kullanılamaz: ' . $slug]];
    }
    $meta = json_decode(file_get_contents($theme_root . '/theme.json'), true);
    if (!is_array($meta) || empty($meta['name'])) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['theme.json geçersiz veya name alanı eksik.']];
    }
    if (!empty($meta['slug']) && $meta['slug'] !== $slug) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['theme.json slug değeri klasör adıyla eşleşmeli (' . $slug . ').']];
    }
    $dest = THEME_PATH . '/' . $slug;
    $replaced = is_dir($dest);
    if ($replaced && function_exists('get_active_theme_slug') && get_active_theme_slug() === $slug) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['Aktif tema üzerine yazılamaz. Önce başka bir temayı etkinleştirin.']];
    }
    if ($replaced && !theme_remove_directory($dest)) {
        theme_remove_directory($temp_base);
        return ['ok' => false, 'errors' => ['Mevcut tema klasörü silinemedi.']];
    }
    if (!@rename($theme_root, $dest)) {
        if (!theme_copy_directory($theme_root, $dest)) {
            theme_remove_directory($temp_base);
            if (is_dir($dest)) {
                theme_remove_directory($dest);
            }
            return ['ok' => false, 'errors' => ['Tema klasörüne kopyalanamadı.']];
        }
    }
    theme_remove_directory($temp_base);
    $meta['slug'] = $slug;
    register_theme_in_db($meta);
    sync_theme_registry(scan_themes());
    $errors = validate_theme($slug);
    if (!empty($errors)) {
        return [
            'ok' => false,
            'errors' => array_merge(['Tema yüklendi ancak doğrulama başarısız:'], $errors),
            'slug' => $slug,
            'installed' => true,
        ];
    }
    if ($activate_after) {
        $activated = activate_theme($slug);
        if ($activated !== true) {
            return [
                'ok' => true,
                'slug' => $slug,
                'name' => $meta['name'],
                'replaced' => $replaced,
                'activated' => false,
                'activate_errors' => $activated,
            ];
        }
    }
    return [
        'ok' => true,
        'slug' => $slug,
        'name' => $meta['name'],
        'replaced' => $replaced,
        'activated' => (bool)$activate_after,
    ];
}
