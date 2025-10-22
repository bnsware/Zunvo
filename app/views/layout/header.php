<?php
/**
 * Zunvo Forum Sistemi
 * Header Layout
 */
$current_user = current_user();
$site_name = get_setting('site_name', SITE_NAME);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? escape($title) . ' - ' : ''; ?><?php echo escape($site_name); ?></title>
    <meta name="description" content="<?php echo escape(get_setting('site_description', SITE_DESCRIPTION)); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <script src="<?php echo asset('js/ajax.js'); ?>" defer></script>
    <script src="<?php echo asset('js/main.js'); ?>" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .navbar {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
        }
        .navbar-menu {
            display: flex;
            gap: 30px;
            align-items: center;
            list-style: none;
        }
        .navbar-menu a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }
        .navbar-menu a:hover {
            color: #007bff;
        }
        .navbar-user {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-outline {
            border: 1px solid #007bff;
            color: #007bff;
            background: white;
        }
        .btn-outline:hover {
            background: #007bff;
            color: white;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-dropdown {
            position: relative;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 20px;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.3s;
        }
        .notification-bell:hover {
            background: #f0f0f0;
        }
        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #dc3545;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            width: 380px;
            max-height: 500px;
            overflow: hidden;
            display: none;
            margin-top: 10px;
            z-index: 1000;
        }
        .notification-dropdown.show {
            display: block;
        }
        .notification-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-header h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .mark-all-read {
            color: #007bff;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .mark-all-read:hover {
            text-decoration: underline;
        }
        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .notification-item {
            display: flex;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notification-item:hover {
            background: #f8f9fa;
        }
        .notification-item.unread {
            background: #e3f2fd;
        }
        .notification-item.unread:hover {
            background: #d1e7fc;
        }
        .notification-icon {
            font-size: 24px;
            flex-shrink: 0;
        }
        .notification-content {
            flex: 1;
            min-width: 0;
        }
        .notification-content a,
        .notification-content span {
            color: #333;
            text-decoration: none;
            display: block;
            font-size: 14px;
            line-height: 1.4;
        }
        .notification-content a:hover {
            color: #007bff;
        }
        .notification-time {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #999;
        }
        .notification-footer {
            padding: 12px 20px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        .notification-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="<?php echo url('/'); ?>" class="navbar-brand">
                <?php echo escape($site_name); ?>
            </a>
            
            <ul class="navbar-menu">
                <li><a href="<?php echo url('/'); ?>">Ana Sayfa</a></li>
                <li><a href="<?php echo url('/kategoriler'); ?>">Kategoriler</a></li>
                <li><a href="<?php echo url('/konular'); ?>">Konular</a></li>
                <?php if ($current_user && is_admin()): ?>
                    <li><a href="<?php echo url('/admin'); ?>">Admin</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="navbar-user">
                <?php if ($current_user): ?>
                    <a href="<?php echo url('/konu/olustur'); ?>" class="btn btn-primary">+ Konu Aç</a>
                    
                    <!-- Bildirim Bell -->
                    <div class="notification-bell" id="notification-bell">
                        🔔
                        <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
                    </div>
                    
                    <!-- Bildirim Dropdown -->
                    <div class="notification-dropdown" id="notification-dropdown">
                        <div class="notification-header">
                            <h3>Bildirimler</h3>
                            <a href="#" class="mark-all-read" id="mark-all-read">Tümünü okundu işaretle</a>
                        </div>
                        <div class="notification-list" id="notification-list">
                            <div class="notification-empty">Yükleniyor...</div>
                        </div>
                        <div class="notification-footer">
                            <a href="<?php echo url('/bildirimler'); ?>">Tüm bildirimleri gör</a>
                        </div>
                    </div>
                    
                    <div class="user-dropdown">
                        <div class="user-info">
                            <img src="<?php echo asset('uploads/avatars/' . $current_user['avatar']); ?>" 
                                 alt="<?php echo escape($current_user['username']); ?>" 
                                 class="user-avatar"
                                 onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'">
                            <span><?php echo escape($current_user['username']); ?></span>
                        </div>
                    </div>
                    
                    <a href="<?php echo url('/cikis'); ?>" class="btn btn-outline">Çıkış</a>
                <?php else: ?>
                    <a href="<?php echo url('/giris'); ?>" class="btn btn-outline">Giriş</a>
                    <a href="<?php echo url('/kayit'); ?>" class="btn btn-primary">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php echo display_flash(); ?>