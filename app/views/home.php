<?php
/**
 * Zunvo Forum Sistemi
 * Ana Sayfa
 */
?>
<style>
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 20px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 30px;
    }
    .hero h1 {
        font-size: 48px;
        margin-bottom: 15px;
    }
    .hero p {
        font-size: 20px;
        margin-bottom: 30px;
        opacity: 0.9;
    }
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 36px;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 5px;
    }
    .stat-label {
        color: #666;
        font-size: 14px;
    }
    .categories-grid {
        display: grid;
        gap: 20px;
    }
    .category-card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .category-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .category-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }
    .category-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    .category-info h3 {
        margin: 0 0 5px 0;
        color: #333;
    }
    .category-info p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }
    .category-stats {
        display: flex;
        gap: 20px;
        padding: 15px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;
        font-size: 14px;
        color: #666;
    }
    .topics-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .topic-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        border-radius: 5px;
        transition: background 0.2s;
    }
    .topic-item:hover {
        background: #f8f9fa;
    }
    .topic-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
    .topic-info {
        flex: 1;
    }
    .topic-title {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        display: block;
        margin-bottom: 3px;
    }
    .topic-title:hover {
        color: #007bff;
    }
    .topic-meta {
        font-size: 12px;
        color: #999;
    }
    .topic-count {
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        color: #666;
    }
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }
</style>

<div class="hero">
    <h1>🎉 Zunvo'ya Hoş Geldiniz!</h1>
    <p>Sorularınızı sorun, deneyimlerinizi paylaşın ve toplulukla etkileşime geçin</p>
    <?php if (!is_logged_in()): ?>
        <a href="<?php echo url('/kayit'); ?>" class="btn btn-primary" style="background: white; color: #667eea; padding: 12px 30px; font-size: 16px;">
            Hemen Katıl
        </a>
    <?php endif; ?>
</div>

<div class="stats-container">
    <div class="stat-card">
        <div class="stat-number"><?php echo format_number($stats['total_topics']); ?></div>
        <div class="stat-label">Toplam Konu</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo format_number($stats['total_posts']); ?></div>
        <div class="stat-label">Toplam Gönderi</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo format_number($stats['total_users']); ?></div>
        <div class="stat-label">Toplam Üye</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <?php echo $stats['newest_user'] ? escape($stats['newest_user']['username']) : '-'; ?>
        </div>
        <div class="stat-label">En Yeni Üye</div>
    </div>
</div>

<div class="categories-grid">
    <?php foreach ($categories as $category): ?>
        <div class="category-card">
            <div class="category-header">
                <div class="category-icon" style="background: <?php echo escape($category['color']); ?>">
                    <?php echo $category['icon'] === 'chat' ? '💬' : 
                               ($category['icon'] === 'megaphone' ? '📢' : 
                               ($category['icon'] === 'help-circle' ? '❓' : '📁')); ?>
                </div>
                <div class="category-info">
                    <h3>
                        <a href="<?php echo url('/kategori/' . $category['slug']); ?>" 
                           style="color: inherit; text-decoration: none;">
                            <?php echo escape($category['name']); ?>
                        </a>
                    </h3>
                    <p><?php echo escape($category['description']); ?></p>
                </div>
            </div>
            
            <div class="category-stats">
                <span>📝 <?php echo $category['topic_count']; ?> Konu</span>
            </div>
            
            <?php if (!empty($category['topics'])): ?>
                <div class="topics-list">
                    <?php foreach ($category['topics'] as $topic): ?>
                        <div class="topic-item">
                            <img src="<?php echo asset('uploads/avatars/' . $topic['avatar']); ?>" 
                                 alt="<?php echo escape($topic['username']); ?>"
                                 class="topic-avatar"
                                 onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'">
                            <div class="topic-info">
                                <a href="<?php echo url('/konu/' . $topic['slug']); ?>" class="topic-title">
                                    <?php if ($topic['is_pinned']): ?>📌 <?php endif; ?>
                                    <?php echo escape($topic['title']); ?>
                                </a>
                                <div class="topic-meta">
                                    <?php echo escape($topic['username']); ?> • 
                                    <?php echo time_ago($topic['created_at']); ?>
                                </div>
                            </div>
                            <span class="topic-count"><?php echo $topic['post_count']; ?> 💬</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>Bu kategoride henüz konu yok</p>
                    <?php if (is_logged_in()): ?>
                        <a href="<?php echo url('/konu/olustur'); ?>" class="btn btn-primary">İlk Konuyu Siz Açın</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($categories)): ?>
    <div class="empty-state" style="background: white; border-radius: 10px; padding: 60px 20px;">
        <h2>Henüz kategori yok</h2>
        <p>Admin panelinden kategoriler oluşturabilirsiniz.</p>
    </div>
<?php endif; ?>