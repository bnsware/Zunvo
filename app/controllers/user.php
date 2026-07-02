<?php

require_once APP_PATH . '/models/user.php';

function user_search() {
    if (!is_ajax()) {
        error_response('Geçersiz istek', 400);
        return;
    }

    if (!is_logged_in()) {
        error_response('Giriş yapmalısınız', 401);
        return;
    }

    $q = trim(get_param('q', ''));
    if (strlen($q) < 1) {
        success_response(['users' => []]);
        return;
    }

    $users = search_users($q, 8);
    foreach ($users as &$user) {
        $user['avatar_url'] = asset('uploads/avatars/' . ($user['avatar'] ?: 'default-avatar.png'));
        $user['profile_url'] = url('/profil/' . $user['username']);
    }

    success_response(['users' => $users]);
}
