<?php
/**
 * Zunvo Forum Sistemi
 * Bildirimler Sayfası
 */
?>
<style>
    .notifications-container {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 20px;
        margin-top: 20px;
    }
    .notifications-sidebar {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        height: fit-content;
    }
    .notifications-sidebar h3 {
        font-size: 16px;
        margin-bottom: 15px;
        color: #333;
    }
    .stats-grid {
        display: grid;
        gap: 15px;
    }
    .stat-box {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        text-align: center;
    }
    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 13px;
        color: #666;
    }
    .notifications-main {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .notifications-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 2px solid #eee;
    }
    .notifications-header h1 {
        font-size: 24px;
        color: #333;
        margin: 0;
    }
    .notification-actions {
        display: flex;
        gap: 10px;
    }
    .notification-list {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .notification-item {
        display: flex;
        gap: 15px;
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s;
        cursor: pointer;
    }
    .notification-item:hover {
        background: #f8f9fa;
    }
    .notification-item:last-child {
        border-bottom: none;
    }
    .notification-item.unread {
        background: #e3f2fd;
    }
    .notification-icon {
        font-size: 32px;
        flex-shrink: 0;
    }
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    .notification-message {
        font-size: 15px;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.5;
    }
    .notification-meta {
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: #999;
    }
    .notification-type {
        padding: 3px 8px;
        background: #e9ecef;
        border-radius: 3px;
        font-size: 12px;
    }
    .notification-actions-btn {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .btn-icon {
        padding: 8px 12px;
        background: #f0f0f0;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
        font-size: 14px;
    }
    .btn-icon:hover {
        background: #e0e0e0;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }
    @media (max-width: 768px) {
        .notifications-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="notifications-container">
    <!-- Sidebar -->
    <aside class="notifications-sidebar">
        <h3>📊 İstatistikler</h3>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Toplam</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['unread']; ?></div>
                <div class="stat-label">Okunmamış</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['read']; ?></div>
                <div class="stat-label">Okunmuş</div>
            </div>
        </div>
        
        <?php if (!empty($stats['by_type'])): ?>
            <h3 style="margin-top: 25px;">📋 Tiplere Göre</h3>
            <div style="font-size: 13px;">
                <?php foreach ($stats['by_type'] as $type): ?>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                        <span><?php echo get_notification_icon($type['type']); ?> <?php echo ucfirst($type['type']); ?></span>
                        <span style="font-weight: 600;"><?php echo $type['count']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 25px;">
            <a href="<?php echo url('/bildirim/ayarlar'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                ⚙️ Ayarlar
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="notifications-main">
        <div class="notifications-header">
            <h1>🔔 Bildirimler</h1>
            <div class="notification-actions">
                <button class="btn btn-outline btn-small" id="mark-all-read-btn">
                    ✓ Tümünü Okundu İşaretle
                </button>
                <button class="btn btn-outline btn-small" id="delete-all-btn">
                    🗑️ Tümünü Sil
                </button>
            </div>
        </div>
        
        <?php if (!empty($notifications)): ?>
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                         data-notification-id="<?php echo $notification['id']; ?>"
                         data-link="<?php echo escape($notification['link']); ?>">
                        <div class="notification-icon">
                            <?php echo get_notification_icon($notification['type']); ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-message">
                                <?php echo escape($notification['message']); ?>
                            </div>
                            <div class="notification-meta">
                                <span class="notification-type"><?php echo escape($notification['type']); ?></span>
                                <span>🕒 <?php echo time_ago($notification['created_at']); ?></span>
                            </div>
                        </div>
                        <div class="notification-actions-btn">
                            <?php if (!$notification['is_read']): ?>
                                <button class="btn-icon mark-read-btn" 
                                        data-notification-id="<?php echo $notification['id']; ?>"
                                        title="Okundu işaretle">
                                    ✓
                                </button>
                            <?php endif; ?>
                            <button class="btn-icon delete-notification-btn" 
                                    data-notification-id="<?php echo $notification['id']; ?>"
                                    title="Sil">
                                🗑️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination" style="margin-top: 30px;">
                    <?php if ($pagination['has_previous']): ?>
                        <a href="<?php echo url('/bildirimler?page=' . ($pagination['current_page'] - 1)); ?>">
                            ← Önceki
                        </a>
                    <?php endif; ?>
                    
                    <span class="active"><?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>
                    
                    <?php if ($pagination['has_next']): ?>
                        <a href="<?php echo url('/bildirimler?page=' . ($pagination['current_page'] + 1)); ?>">
                            Sonraki →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2>Bildirim Yok</h2>
                <p>Henüz hiç bildiriminiz bulunmuyor.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
// Bildirim item'larına tıklandığında
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', async function(e) {
        // Buton tıklanmışsa link'e gitme
        if (e.target.classList.contains('btn-icon') || e.target.closest('.btn-icon')) {
            return;
        }
        
        const notificationId = this.dataset.notificationId;
        const link = this.dataset.link;
        
        // Okunmamışsa okundu işaretle
        if (this.classList.contains('unread')) {
            await NotificationAPI.markRead(notificationId);
        }
        
        // Link varsa yönlendir
        if (link && link !== 'null') {
            window.location.href = link;
        }
    });
});

// Tek tek okundu işaretle butonları
document.querySelectorAll('.mark-read-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        
        const notificationId = this.dataset.notificationId;
        
        try {
            const result = await NotificationAPI.markRead(notificationId);
            
            if (result.success) {
                const item = this.closest('.notification-item');
                item.classList.remove('unread');
                this.remove();
                
                Utils.showToast('Bildirim okundu olarak işaretlendi', 'success');
            }
        } catch (error) {
            Utils.showToast(error.message, 'error');
        }
    });
});

// Silme butonları
document.querySelectorAll('.delete-notification-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.stopPropagation();
        
        if (!confirm('Bu bildirimi silmek istediğinizden emin misiniz?')) {
            return;
        }
        
        const notificationId = this.dataset.notificationId;
        
        try {
            const result = await NotificationAPI.delete(notificationId);
            
            if (result.success) {
                const item = this.closest('.notification-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                Utils.showToast('Bildirim silindi', 'success');
            }
        } catch (error) {
            Utils.showToast(error.message, 'error');
        }
    });
});

// Tümünü okundu işaretle
document.getElementById('mark-all-read-btn')?.addEventListener('click', async function() {
    try {
        const result = await NotificationAPI.markAllRead();
        
        if (result.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            
            document.querySelectorAll('.mark-read-btn').forEach(btn => btn.remove());
            
            Utils.showToast('Tüm bildirimler okundu olarak işaretlendi', 'success');
        }
    } catch (error) {
        Utils.showToast(error.message, 'error');
    }
});

// Tümünü sil
document.getElementById('delete-all-btn')?.addEventListener('click', async function() {
    if (!confirm('Tüm bildirimleriniz silinecek. Emin misiniz?')) {
        return;
    }
    
    try {
        const result = await NotificationAPI.deleteAll();
        
        if (result.success) {
            window.location.reload();
        }
    } catch (error) {
        Utils.showToast(error.message, 'error');
    }
});
</script>