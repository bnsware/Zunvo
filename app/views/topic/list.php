<?php
/**
 * Zunvo Forum Sistemi
 * Konu Listesi
 */
?>
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .search-input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    .topic-list {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .topic-row {
        display: grid;
        grid-template-columns: 50px 1fr 120px 120px;
        gap: 15px;
        padding: 20px;
        border-bottom: 1px solid #eee;
        align-items: center;
        transition: background 0.2s;
    }
    .topic-row:hover {
        background: #f8f9fa;
    }
    .topic-row:last-child {
        border-bottom: none;
    }
    .topic-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    .topic-content {
        min-width: 0;
    }
    .topic-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .topic-title a {
        color: #333;
        text-decoration: none;
    }
    .topic-title a:hover {
        color: #007bff;
    }
    .topic-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 8px;
    }
    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pinned {
        background: #ffeaa7;
        color: #d63031;
    }
    .badge-locked {
        background: #dfe6e9;
        color: #636e72;
    }
    .badge-category {
        background: #e3f2fd;
        color: #1976d2;
    }
    .topic-meta {
        font-size: 13px;
        color: #999;
    }
    .topic-stat {
        text-align: center;
        font-size: 14px;
        color: #666;
    }
    .stat-number {
        font-size: 20px;
        font-weight: bold;
        color: #007bff;
        display: block;
    }
    .stat-label {
        font-size: 12px;
        color: #999;
    }
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
        align-items: center;
    }
    .pagination a, .pagination span {
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-decoration: none;
        color: #666;
        transition: all 0.3s;
    }
    .pagination a:hover {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .pagination .active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    @media (max-width: 768px) {
        .topic-row {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .topic-stat {
            display: none;
        }
    }
</style>

<div class="page-header">
    <h1><?php echo isset($search) && !empty($search) ? 'Arama Sonuçları' : 'Tüm Konular'; ?></h1>
    <?php if (is_logged_in()): ?>
        <a href="<?php echo url('/konu/olustur'); ?>" class="btn btn-primary">+ Yeni Konu</a>
    <?php endif; ?>
</div>

<form method="GET" action="<?php echo url('/konular'); ?>" class="search-box">
    <input 
        type="text" 
        name="search" 
        class="search-input" 
        placeholder="Konu ara..." 
        value="<?php echo isset($search) ? escape($search) : ''; ?>"
    >
    <button type="submit" class="btn btn-primary">🔍 Ara</button>
</form>

<?php if (!empty($topics)): ?>
    <div class="topic-list">
        <?php foreach ($topics as $topic): ?>
            <div class="topic-row">
                <img 
                    src="<?php echo asset('uploads/avatars/' . $topic['avatar']); ?>" 
                    alt="<?php echo escape($topic['username']); ?>"
                    class="topic-avatar"
                    onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
                >
                
                <div class="topic-content">
                    <?php if ($topic['is_pinned'] || $topic['is_locked']): ?>
                        <div class="topic-badges">
                            <?php if ($topic['is_pinned']): ?>
                                <span class="badge badge-pinned">📌 SABİT</span>
                            <?php endif; ?>
                            <?php if ($topic['is_locked']): ?>
                                <span class="badge badge-locked">🔒 KİLİTLİ</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="topic-title">
                        <a href="<?php echo url('/konu/' . $topic['slug']); ?>">
                            <?php echo escape($topic['title']); ?>
                        </a>
                    </div>
                    
                    <div class="topic-badges">
                        <span class="badge badge-category">
                            <?php echo escape($topic['category_name']); ?>
                        </span>
                    </div>
                    
                    <div class="topic-meta">
                        <strong><?php echo escape($topic['username']); ?></strong> tarafından
                        <?php echo time_ago($topic['created_at']); ?>
                        <?php if (isset($topic['last_poster']) && $topic['last_poster']): ?>
                            • Son: <strong><?php echo escape($topic['last_poster']); ?></strong>
                            <?php echo time_ago($topic['last_post_time']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="topic-stat">
                    <span class="stat-number"><?php echo $topic['post_count']; ?></span>
                    <span class="stat-label">Yanıt</span>
                </div>
                
                <div class="topic-stat">
                    <span class="stat-number"><?php echo format_number($topic['views']); ?></span>
                    <span class="stat-label">Görüntülenme</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="pagination">
            <?php if ($pagination['has_previous']): ?>
                <a href="<?php echo url('/konular?page=' . ($pagination['current_page'] - 1) . (isset($search) ? '&search=' . urlencode($search) : '')); ?>">
                    ← Önceki
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= min($pagination['total_pages'], 5); $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="<?php echo url('/konular?page=' . $i . (isset($search) ? '&search=' . urlencode($search) : '')); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($pagination['has_next']): ?>
                <a href="<?php echo url('/konular?page=' . ($pagination['current_page'] + 1) . (isset($search) ? '&search=' . urlencode($search) : '')); ?>">
                    Sonraki →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <h2>😔 Konu Bulunamadı</h2>
        <p>
            <?php if (isset($search) && !empty($search)): ?>
                "<?php echo escape($search); ?>" için sonuç bulunamadı.
            <?php else: ?>
                Henüz hiç konu açılmamış.
            <?php endif; ?>
        </p>
        <?php if (is_logged_in()): ?>
            <a href="<?php echo url('/konu/olustur'); ?>" class="btn btn-primary">İlk Konuyu Siz Açın</a>
        <?php endif; ?>
    </div>
<?php endif; ?>