<?php
/**
 * Zunvo Forum Sistemi
 * Profil Sayfası
 */
$current_user = current_user();
$is_own_profile = $current_user && $current_user['id'] === $profile_user['id'];
?>
<style>
    .profile-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    .profile-sidebar {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: fit-content;
    }
    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        margin: 0 auto 20px;
        display: block;
        object-fit: cover;
    }
    .profile-username {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    .profile-level {
        text-align: center;
        color: #007bff;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .profile-reputation {
        text-align: center;
        color: #28a745;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .profile-bio {
        padding: 15px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
        color: #666;
        line-height: 1.6;
    }
    .profile-stats {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-item {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }
    .stat-label {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .profile-meta {
        font-size: 13px;
        color: #999;
    }
    .profile-meta div {
        margin-bottom: 8px;
    }
    .profile-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .content-tabs {
        display: flex;
        gap: 20px;
        border-bottom: 2px solid #eee;
        margin-bottom: 20px;
    }
    .tab-button {
        padding: 10px 20px;
        background: none;
        border: none;
        color: #666;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.3s;
    }
    .tab-button.active {
        color: #007bff;
        border-bottom-color: #007bff;
    }
    .topic-item {
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    .topic-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .topic-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        text-decoration: none;
        display: block;
        margin-bottom: 8px;
    }
    .topic-title:hover {
        color: #007bff;
    }
    .topic-meta {
        font-size: 13px;
        color: #999;
    }
    .btn {
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-primary {
        background: #007bff;
        color: white;
        border: none;
    }
    .btn-primary:hover {
        background: #0056b3;
    }
    @media (max-width: 768px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-container">
    <aside class="profile-sidebar">
        <img 
            src="<?php echo asset('uploads/avatars/' . $profile_user['avatar']); ?>" 
            alt="<?php echo escape($profile_user['username']); ?>" 
            class="profile-avatar"
            onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
        >
        
        <h1 class="profile-username"><?php echo escape($profile_user['username']); ?></h1>
        
        <div class="profile-level"><?php echo escape($level); ?></div>
        
        <div class="profile-reputation">
            ⭐ <?php echo escape($profile_user['reputation']); ?> Reputasyon
        </div>
        
        <?php if (!empty($profile_user['biography'])): ?>
            <div class="profile-bio">
                <?php echo nl2br(escape($profile_user['biography'])); ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['topics']; ?></div>
                <div class="stat-label">Konu</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['posts']; ?></div>
                <div class="stat-label">Gönderi</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['solutions']; ?></div>
                <div class="stat-label">Çözüm</div>
            </div>
        </div>
        
        <div class="profile-meta">
            <div>📅 Üyelik: <?php echo format_date($profile_user['created_at'], 'd.m.Y'); ?></div>
            <?php if ($profile_user['last_active']): ?>
                <div>🕒 Son Görülme: <?php echo time_ago($profile_user['last_active']); ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($is_own_profile): ?>
            <a href="<?php echo url('/profil-duzenle'); ?>" class="btn btn-primary" style="width: 100%; margin-top: 20px; text-align: center;">
                Profili Düzenle
            </a>
        <?php endif; ?>
    </aside>
    
    <main class="profile-content">
        <div class="content-tabs">
            <button class="tab-button active">Son Konular</button>
            <button class="tab-button">Son Gönderiler</button>
            <button class="tab-button">Çözümler</button>
        </div>
        
        <div class="tab-content">
            <?php if (empty($recent_topics)): ?>
                <p style="color: #999; text-align: center; padding: 40px 0;">Henüz konu açılmamış.</p>
            <?php else: ?>
                <?php foreach ($recent_topics as $topic): ?>
                    <div class="topic-item">
                        <a href="<?php echo url('/konu/' . $topic['slug']); ?>" class="topic-title">
                            <?php echo escape($topic['title']); ?>
                        </a>
                        <div class="topic-meta">
                            👁️ <?php echo $topic['views']; ?> görüntülenme • 
                            🕒 <?php echo time_ago($topic['created_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>