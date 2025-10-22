<?php
/**
 * Zunvo Forum Sistemi
 * Konu Detay Sayfası
 */
$current_user = current_user();
$is_author = $current_user && $current_user['id'] === $topic['user_id'];
?>
<style>
    .topic-header {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .topic-title-area {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }
    .topic-title-area h1 {
        font-size: 32px;
        color: #333;
        margin: 0;
    }
    .topic-actions {
        display: flex;
        gap: 10px;
    }
    .breadcrumb {
        display: flex;
        gap: 8px;
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }
    .breadcrumb a {
        color: #007bff;
        text-decoration: none;
    }
    .topic-info {
        display: flex;
        gap: 20px;
        font-size: 14px;
        color: #666;
        margin-top: 15px;
    }
    .topic-tags {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 15px;
    }
    .tag {
        background: #e3f2fd;
        color: #1976d2;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 13px;
        text-decoration: none;
    }
    .tag:hover {
        background: #1976d2;
        color: white;
    }
    .post-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .post-item {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        display: grid;
        grid-template-columns: 200px 1fr;
    }
    .post-item.solution {
        border: 2px solid #28a745;
    }
    .post-sidebar {
        background: #f8f9fa;
        padding: 20px;
        text-align: center;
        border-right: 1px solid #eee;
    }
    .post-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 15px;
        object-fit: cover;
    }
    .post-username {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
        text-decoration: none;
        display: block;
    }
    .post-username:hover {
        color: #007bff;
    }
    .user-role {
        font-size: 12px;
        color: #999;
        margin-bottom: 10px;
    }
    .user-stats {
        font-size: 12px;
        color: #666;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
    }
    .user-stats div {
        margin-bottom: 5px;
    }
    .post-content-area {
        padding: 20px;
    }
    .post-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    .post-date {
        font-size: 13px;
        color: #999;
    }
    .post-actions {
        display: flex;
        gap: 10px;
    }
    .post-content {
        line-height: 1.8;
        color: #333;
        margin-bottom: 20px;
        word-wrap: break-word;
    }
    .post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    .vote-buttons {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    .vote-btn {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 15px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    .vote-btn:hover {
        background: #f8f9fa;
    }
    .vote-btn.active-up {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }
    .vote-btn.active-down {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
    }
    .solution-badge {
        background: #28a745;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        font-weight: 600;
        font-size: 14px;
    }
    .reply-box {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin-top: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .reply-textarea {
        width: 100%;
        min-height: 150px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
        margin-bottom: 15px;
    }
    .reply-textarea:focus {
        outline: none;
        border-color: #007bff;
    }
    .btn-small {
        padding: 6px 12px;
        font-size: 13px;
    }
    @media (max-width: 768px) {
        .post-item {
            grid-template-columns: 1fr;
        }
        .post-sidebar {
            border-right: none;
            border-bottom: 1px solid #eee;
        }
    }
</style>

<div class="breadcrumb">
    <a href="<?php echo url('/'); ?>">Ana Sayfa</a> / 
    <a href="<?php echo url('/kategori/' . $topic['category_slug']); ?>">
        <?php echo escape($topic['category_name']); ?>
    </a> / 
    <span><?php echo escape($topic['title']); ?></span>
</div>

<div class="topic-header">
    <div class="topic-title-area">
        <h1>
            <?php if ($topic['is_pinned']): ?>📌 <?php endif; ?>
            <?php if ($topic['is_locked']): ?>🔒 <?php endif; ?>
            <?php echo escape($topic['title']); ?>
        </h1>
        
        <?php if ($is_author || is_moderator()): ?>
            <div class="topic-actions">
                <a href="<?php echo url('/konu/duzenle/' . $topic['slug']); ?>" class="btn btn-outline btn-small">
                    ✏️ Düzenle
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="topic-info">
        <span>👁️ <?php echo format_number($topic['views']); ?> görüntülenme</span>
        <span>💬 <?php echo count($posts); ?> yanıt</span>
        <span>🕒 <?php echo time_ago($topic['created_at']); ?></span>
    </div>
    
    <?php if (!empty($tags)): ?>
        <div class="topic-tags">
            <?php foreach ($tags as $tag): ?>
                <a href="<?php echo url('/etiket/' . $tag['slug']); ?>" class="tag">
                    #<?php echo escape($tag['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="post-list">
    <?php foreach ($posts as $index => $post): ?>
        <div class="post-item <?php echo $post['is_solution'] ? 'solution' : ''; ?>" id="post-<?php echo $post['id']; ?>">
            <div class="post-sidebar">
                <img 
                    src="<?php echo asset('uploads/avatars/' . $post['avatar']); ?>"
                    alt="<?php echo escape($post['username']); ?>"
                    class="post-avatar"
                    onerror="this.src='<?php echo asset('images/default-avatar.png'); ?>'"
                >
                <a href="<?php echo url('/profil/' . $post['username']); ?>" class="post-username">
                    <?php echo escape($post['username']); ?>
                </a>
                <div class="user-role">
                    <?php 
                    $user = get_user_by_id($post['user_id']);
                    echo $user['role'] === 'admin' ? '👑 Admin' : 
                         ($user['role'] === 'moderator' ? '🛡️ Moderatör' : '👤 Üye');
                    ?>
                </div>
                <div class="user-stats">
                    <div>⭐ <?php echo $post['reputation']; ?> Reputasyon</div>
                    <div>💬 <?php echo $post['user_post_count']; ?> Gönderi</div>
                    <div>📅 <?php echo format_date($post['user_joined'], 'M Y'); ?></div>
                </div>
            </div>
            
            <div class="post-content-area">
                <div class="post-header">
                    <div class="post-date">
                        <?php echo format_date($post['created_at'], 'd.m.Y H:i'); ?>
                        <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                            <span style="color: #999;">(düzenlendi)</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($current_user && ($current_user['id'] === $post['user_id'] || is_moderator())): ?>
                        <div class="post-actions">
                            <button class="btn btn-outline btn-small">✏️ Düzenle</button>
                            <button class="btn btn-outline btn-small">🗑️ Sil</button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($post['is_solution']): ?>
                    <div style="margin-bottom: 15px;">
                        <span class="solution-badge">✓ Çözüm</span>
                    </div>
                <?php endif; ?>
                
                <div class="post-content">
                    <?php echo nl2br(escape($post['content'])); ?>
                </div>
                
                <div class="post-footer">
                    <div class="vote-buttons">
                        <?php if ($current_user): ?>
                            <button class="vote-btn <?php echo isset($user_votes[$post['id']]) && $user_votes[$post['id']] === 'up' ? 'active-up' : ''; ?>"
                                    data-post-id="<?php echo $post['id']; ?>"
                                    data-type="up">
                                👍 <?php echo $post['upvotes']; ?>
                            </button>
                            <button class="vote-btn <?php echo isset($user_votes[$post['id']]) && $user_votes[$post['id']] === 'down' ? 'active-down' : ''; ?>"
                                    data-post-id="<?php echo $post['id']; ?>"
                                    data-type="down">
                                👎 <?php echo $post['downvotes']; ?>
                            </button>
                        <?php else: ?>
                            <span>👍 <?php echo $post['upvotes']; ?></span>
                            <span>👎 <?php echo $post['downvotes']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($index > 0 && $is_author && !$post['is_solution']): ?>
                        <button class="btn btn-primary btn-small" 
                                data-mark-solution="<?php echo $post['id']; ?>">
                            ✓ Çözüm Olarak İşaretle
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
    <div class="pagination" style="margin-top: 30px;">
        <?php if ($pagination['has_previous']): ?>
            <a href="<?php echo url('/konu/' . $topic['slug'] . '?page=' . ($pagination['current_page'] - 1)); ?>">
                ← Önceki
            </a>
        <?php endif; ?>
        
        <span class="active"><?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
        
        <?php if ($pagination['has_next']): ?>
            <a href="<?php echo url('/konu/' . $topic['slug'] . '?page=' . ($pagination['current_page'] + 1)); ?>">
                Sonraki →
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($current_user && !$topic['is_locked']): ?>
    <div class="reply-box">
        <h3>Yanıt Yaz</h3>
        <form id="reply-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
            <textarea 
                name="content" 
                class="reply-textarea" 
                placeholder="Yanıtınızı buraya yazın... (@kullaniciadi ile bahsedebilirsiniz)"
                required
            ></textarea>
            <button type="submit" class="btn btn-primary">💬 Yanıt Gönder</button>
        </form>
    </div>
<?php elseif (!$current_user): ?>
    <div class="reply-box" style="text-align: center;">
        <p>Yanıt yazmak için <a href="<?php echo url('/giris'); ?>">giriş yapın</a> veya 
        <a href="<?php echo url('/kayit'); ?>">kayıt olun</a>.</p>
    </div>
<?php elseif ($topic['is_locked']): ?>
    <div class="reply-box" style="text-align: center;">
        <p>🔒 Bu konu kilitlenmiştir. Yeni yanıt yazılamaz.</p>
    </div>
<?php endif; ?>

<script>
// Vote butonları için basit AJAX
document.querySelectorAll('.vote-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // AJAX ile vote işlemi yapılacak (Faz 4'te)
        alert('Vote sistemi Faz 4\'te eklenecek');
    });
});
</script>