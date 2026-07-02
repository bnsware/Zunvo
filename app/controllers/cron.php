<?php

function cron_plugins() {
    $secret = get_param('key', '');
    $expected = get_setting('cron_secret', defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : '');
    if ($expected === '' || !hash_equals((string)$expected, (string)$secret)) {
        http_response_code(403);
        echo 'Forbidden';
        return;
    }
    execute_hooks('cron_run', ['timestamp' => time()]);
    echo 'OK';
}
