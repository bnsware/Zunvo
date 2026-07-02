<?php

require_once APP_PATH . '/models/topic.php';
require_once APP_PATH . '/models/category.php';
require_once APP_PATH . '/models/change_request.php';
require_once APP_PATH . '/models/badge.php';

function mod_dashboard() {
    require_moderator_panel();
    $stats = [
        'reports_pending' => db_count('reports', "status = 'pending'"),
        'approvals_pending' => count_pending_change_requests(),
        'topics_today' => db_count('topics', 'DATE(created_at) = CURDATE()'),
    ];
    $recent_logs = db_query_all(
        "SELECT ml.*, u.username as moderator_name
         FROM mod_logs ml
         JOIN users u ON ml.moderator_id = u.id
         ORDER BY ml.created_at DESC LIMIT 10"
    );
    render('mod/dashboard', [
        'title' => 'Mod Paneli',
        'mod_page' => 'dashboard',
        'stats' => $stats,
        'recent_logs' => $recent_logs
    ], 'mod/layout');
}

function mod_topics() {
    require_moderator_panel();
    $page = max(1, (int)get_param('page', 1));
    $per_page = 30;
    $total = db_count('topics');
    $pagination = get_pagination($total, $per_page, $page);
    $topics = db_query_all(
        "SELECT t.*, u.username, c.name as category_name
         FROM topics t
         JOIN users u ON t.user_id = u.id
         JOIN categories c ON t.category_id = c.id
         ORDER BY t.updated_at DESC
         LIMIT ? OFFSET ?",
        [$per_page, $pagination['offset']]
    );
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $topic_id = (int)post_param('topic_id');
        $action = post_param('action');
        if ($action === 'pin' && can_mod('pin_topic')) {
            pin_topic($topic_id, true);
            log_mod_action('pin', 'topic', $topic_id, '');
        } elseif ($action === 'unpin' && can_mod('pin_topic')) {
            pin_topic($topic_id, false);
            log_mod_action('unpin', 'topic', $topic_id, '');
        } elseif ($action === 'lock' && can_mod('lock_topic')) {
            lock_topic($topic_id, true);
            log_mod_action('lock', 'topic', $topic_id, '');
        } elseif ($action === 'unlock' && can_mod('lock_topic')) {
            lock_topic($topic_id, false);
            log_mod_action('unlock', 'topic', $topic_id, '');
        } elseif ($action === 'delete' && can_mod('delete_topic')) {
            delete_topic($topic_id);
            log_mod_action('delete', 'topic', $topic_id, '');
        }
        set_flash('success', 'İşlem uygulandı.');
        redirect(url('/mod/konular'));
    }
    render('mod/topics', [
        'title' => 'Konu Yönetimi',
        'mod_page' => 'topics',
        'topics' => $topics,
        'pagination' => $pagination
    ], 'mod/layout');
}

function mod_reports() {
    require_moderator_panel();
    if (!can_mod('resolve_reports')) {
        set_flash('error', 'Rapor yönetimi yetkiniz yok.');
        redirect(url('/mod'));
        return;
    }
    $status = get_param('status', 'pending');
    $page = max(1, (int)get_param('page', 1));
    $per_page = 30;
    $where = $status !== 'all' ? "r.status = ?" : '1=1';
    $params = $status !== 'all' ? [$status] : [];
    $total = db_query_value("SELECT COUNT(*) FROM reports r WHERE {$where}", $params);
    $pagination = get_pagination($total, $per_page, $page);
    $params[] = $per_page;
    $params[] = $pagination['offset'];
    $reports = db_query_all(
        "SELECT r.*, u.username as reporter_name, ru.username as reported_username, p.content as post_content
         FROM reports r
         JOIN users u ON r.reporter_id = u.id
         LEFT JOIN users ru ON r.reported_user_id = ru.id
         LEFT JOIN posts p ON r.post_id = p.id
         WHERE {$where}
         ORDER BY r.created_at DESC LIMIT ? OFFSET ?",
        $params
    );
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $report_id = (int)post_param('report_id');
        $action = post_param('action');
        if (in_array($action, ['resolved', 'rejected'], true)) {
            db_execute("UPDATE reports SET status = ? WHERE id = ?", [$action, $report_id]);
            log_mod_action('report_' . $action, 'report', $report_id, '');
            set_flash('success', 'Rapor güncellendi.');
        }
        redirect(url('/mod/raporlar'));
    }
    render('mod/reports', [
        'title' => 'Raporlar',
        'mod_page' => 'reports',
        'reports' => $reports,
        'status' => $status,
        'pagination' => $pagination
    ], 'mod/layout');
}

function mod_approvals() {
    require_moderator_panel();
    if (!can_mod('approve_title_change')) {
        set_flash('error', 'Onay yetkiniz yok.');
        redirect(url('/mod'));
        return;
    }
    if (is_post() && validate_csrf_token(post_param(CSRF_TOKEN_NAME))) {
        $req_id = (int)post_param('request_id');
        $action = post_param('action');
        $user = current_user();
        if ($action === 'approve') {
            approve_change_request($req_id, $user['id']);
            log_mod_action('approve_title', 'change_request', $req_id, '');
            set_flash('success', 'Onaylandı.');
        } elseif ($action === 'reject') {
            reject_change_request($req_id, $user['id']);
            log_mod_action('reject_title', 'change_request', $req_id, '');
            set_flash('success', 'Reddedildi.');
        }
        redirect(url('/mod/onaylar'));
    }
    $requests = get_pending_change_requests();
    render('mod/approvals', [
        'title' => 'Onay Kuyruğu',
        'mod_page' => 'approvals',
        'requests' => $requests
    ], 'mod/layout');
}

function mod_log() {
    require_moderator_panel();
    $user = current_user();
    $page = max(1, (int)get_param('page', 1));
    $per_page = 50;
    $total = db_count('mod_logs', 'moderator_id = ?', [$user['id']]);
    $pagination = get_pagination($total, $per_page, $page);
    $logs = db_query_all(
        "SELECT * FROM mod_logs WHERE moderator_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
        [$user['id'], $per_page, $pagination['offset']]
    );
    render('mod/log', [
        'title' => 'Mod Log',
        'mod_page' => 'log',
        'logs' => $logs,
        'pagination' => $pagination
    ], 'mod/layout');
}
