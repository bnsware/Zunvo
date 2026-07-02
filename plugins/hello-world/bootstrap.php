<?php

register_hook('after_user_register', function($data) {
    if (isset($data['user_id'])) {
        create_notification($data['user_id'], 'system', 'Zunvo forumuna hoş geldiniz!', '/');
    }
    return $data;
});
