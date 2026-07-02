<?php

$GLOBALS['zunvo_hooks'] = [];

function register_hook($name, $callback) {
    if (!isset($GLOBALS['zunvo_hooks'][$name])) {
        $GLOBALS['zunvo_hooks'][$name] = [];
    }
    $GLOBALS['zunvo_hooks'][$name][] = $callback;
}

function execute_hooks($name, $data = null) {
    if (!isset($GLOBALS['zunvo_hooks'][$name])) {
        return $data;
    }
    foreach ($GLOBALS['zunvo_hooks'][$name] as $callback) {
        $data = call_user_func($callback, $data);
    }
    return $data;
}

function get_active_plugins() {
    return db_query_all("SELECT * FROM plugins WHERE is_active = 1");
}

function get_plugin_meta($slug) {
    $json_file = PLUGIN_PATH . '/' . $slug . '/plugin.json';
    if (!file_exists($json_file)) {
        return null;
    }
    $meta = json_decode(file_get_contents($json_file), true);
    if (!is_array($meta)) {
        return null;
    }
    $meta['slug'] = $slug;
    return $meta;
}

function get_plugin_default_settings($slug) {
    $meta = get_plugin_meta($slug);
    if (!$meta || empty($meta['settings'])) {
        return [];
    }
    $defaults = [];
    foreach ($meta['settings'] as $key => $field) {
        if (($field['type'] ?? 'text') === 'info') {
            continue;
        }
        $defaults[$key] = (string)($field['default'] ?? '');
    }
    return $defaults;
}

function save_plugin_settings($slug, $settings, $is_active = null) {
    if ($is_active === null) {
        $row = db_query_row("SELECT is_active FROM plugins WHERE slug = ?", [$slug]);
        $is_active = !empty($row['is_active']);
    }
    $settings['enabled'] = $is_active ? '1' : '0';
    return db_execute(
        "UPDATE plugins SET settings = ?, is_active = ? WHERE slug = ?",
        [json_encode($settings, JSON_UNESCAPED_UNICODE), $is_active ? 1 : 0, $slug]
    );
}

function activate_plugin($slug) {
    $meta = get_plugin_meta($slug);
    if ($meta) {
        register_plugin_in_db($meta);
    }
    $settings = get_plugin_settings($slug);
    $settings['enabled'] = '1';
    return db_execute(
        "UPDATE plugins SET is_active = 1, settings = ? WHERE slug = ?",
        [json_encode($settings, JSON_UNESCAPED_UNICODE), $slug]
    );
}

function deactivate_plugin($slug) {
    $settings = get_plugin_settings($slug);
    $settings['enabled'] = '0';
    return db_execute(
        "UPDATE plugins SET is_active = 0, settings = ? WHERE slug = ?",
        [json_encode($settings, JSON_UNESCAPED_UNICODE), $slug]
    );
}

function plugin_boot() {
    if (!PLUGINS_ENABLED || !is_dir(PLUGIN_PATH) || !file_exists(STORAGE_PATH . '/install.lock')) {
        return;
    }
    $plugins = get_active_plugins();
    foreach ($plugins as $plugin) {
        $bootstrap = PLUGIN_PATH . '/' . $plugin['slug'] . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            require_once $bootstrap;
        }
    }
}

function scan_plugins() {
    $found = [];
    if (!is_dir(PLUGIN_PATH)) {
        return $found;
    }
    foreach (scandir(PLUGIN_PATH) as $dir) {
        if ($dir === '.' || $dir === '..') {
            continue;
        }
        $json_file = PLUGIN_PATH . '/' . $dir . '/plugin.json';
        if (file_exists($json_file)) {
            $meta = json_decode(file_get_contents($json_file), true);
            $meta['slug'] = $dir;
            $found[] = $meta;
        }
    }
    return $found;
}

function register_plugin_in_db($meta) {
    $exists = db_query_row("SELECT id FROM plugins WHERE slug = ?", [$meta['slug']]);
    if ($exists) {
        db_execute(
            "UPDATE plugins SET name = ?, version = ? WHERE slug = ?",
            [$meta['name'], $meta['version'] ?? '1.0.0', $meta['slug']]
        );
        return $exists['id'];
    }
    return db_insert(
        "INSERT INTO plugins (name, slug, version, is_active) VALUES (?, ?, ?, 0)",
        [$meta['name'], $meta['slug'], $meta['version'] ?? '1.0.0']
    );
}

function sync_plugins() {
    $scanned = scan_plugins();
    $slugs_on_disk = [];
    foreach ($scanned as $meta) {
        $slugs_on_disk[] = $meta['slug'];
        register_plugin_in_db($meta);
    }
    $rows = db_query_all("SELECT slug FROM plugins");
    foreach ($rows as $row) {
        if (!in_array($row['slug'], $slugs_on_disk, true)) {
            db_execute("DELETE FROM plugins WHERE slug = ?", [$row['slug']]);
        }
    }
    return $scanned;
}

function is_discord_webhook_url($url) {
    return (bool)preg_match('#^https://(discord\.com|discordapp\.com)/api/webhooks/\d+/[\w-]+#i', $url);
}

function send_webhook_to_url($url, $event_type, $payload, $headers = null) {
    $body = json_encode(['event' => $event_type, 'data' => $payload, 'timestamp' => time()], JSON_UNESCAPED_UNICODE);
    if ($headers === null) {
        $signature = hash_hmac('sha256', $body, WEBHOOK_SECRET);
        $headers = [
            'Content-Type: application/json',
            'X-Zunvo-Signature: ' . $signature,
            'X-Zunvo-Event: ' . $event_type
        ];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'response' => $response ?: '', 'error' => $error];
}

function send_test_webhook($url, $event_type) {
    if (is_discord_webhook_url($url)) {
        $body = json_encode([
            'embeds' => [[
                'title' => 'Zunvo Webhook Test',
                'description' => 'Olay: `' . $event_type . "`\nBu bir test mesajıdır.",
                'color' => 5814783
            ]]
        ], JSON_UNESCAPED_UNICODE);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $response = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        return ['code' => $code, 'response' => $response ?: '', 'error' => $error];
    }
    $payloads = [
        'topic.created' => ['topic_id' => 0, 'test' => true],
        'post.created' => ['post_id' => 0, 'topic_id' => 0, 'test' => true],
        'user.registered' => ['user_id' => 0, 'test' => true],
        'report.created' => ['report_id' => 0, 'test' => true]
    ];
    $payload = $payloads[$event_type] ?? ['test' => true];
    return send_webhook_to_url($url, $event_type, $payload);
}

function fire_webhook($event_type, $payload) {
    $hooks = db_query_all("SELECT * FROM webhooks WHERE event_type = ? AND is_active = 1", [$event_type]);
    foreach ($hooks as $hook) {
        $result = send_webhook_to_url($hook['url'], $event_type, $payload);
        if ($result['error'] !== '') {
            log_error("Webhook failed [{$hook['id']}]: {$result['error']}");
        } elseif ($result['code'] < 200 || $result['code'] >= 300) {
            log_error("Webhook failed [{$hook['id']}]: HTTP {$result['code']} - {$result['response']}");
        }
    }
}

function get_plugin_settings($slug) {
    $row = db_query_row("SELECT settings, is_active FROM plugins WHERE slug = ?", [$slug]);
    $saved = [];
    if ($row && $row['settings']) {
        $saved = json_decode($row['settings'], true) ?: [];
    }
    $settings = array_merge(get_plugin_default_settings($slug), $saved);
    if ($row) {
        $settings['enabled'] = !empty($row['is_active']) ? '1' : '0';
    }
    return $settings;
}
