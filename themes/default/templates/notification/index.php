<div class="notifications-page">
    <aside class="notifications-sidebar">
        <h3><?php echo icon('layout', 'icon icon-sm'); ?> İstatistikler</h3>
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
            <h3 class="section-spaced">Tiplere Göre</h3>
            <div class="type-breakdown">
                <?php foreach ($stats['by_type'] as $type): ?>
                    <div class="type-breakdown-row">
                        <span class="type-breakdown-label">
                            <?php echo icon(notification_icon_name($type['type']), 'icon icon-sm'); ?>
                            <?php echo escape(notification_type_label($type['type'])); ?>
                        </span>
                        <span class="type-breakdown-count"><?php echo $type['count']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-20">
            <a href="<?php echo url('/bildirim/ayarlar'); ?>" class="btn btn-outline btn-block btn-with-icon">
                <?php echo icon('settings', 'icon icon-sm'); ?> Ayarlar
            </a>
        </div>
    </aside>

    <main class="notifications-main">
        <div class="notifications-header">
            <h1><?php echo icon('bell', 'icon'); ?> Bildirimler</h1>
            <div class="notification-actions">
                <button class="btn btn-outline btn-small btn-with-icon" id="mark-all-read-btn" type="button">
                    <?php echo icon('check', 'icon icon-sm'); ?> Tümünü Okundu İşaretle
                </button>
                <button class="btn btn-outline btn-small btn-with-icon" id="delete-all-btn" type="button">
                    <?php echo icon('trash', 'icon icon-sm'); ?> Tümünü Sil
                </button>
            </div>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="notification-list notifications-list-page">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>"
                         data-notification-id="<?php echo $notification['id']; ?>"
                         data-link="<?php echo escape($notification['link']); ?>">
                        <div class="notification-icon">
                            <?php echo icon(notification_icon_name($notification['type']), 'icon'); ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-message">
                                <?php echo escape($notification['message']); ?>
                            </div>
                            <div class="notification-meta-row">
                                <span class="notification-type-label"><?php echo escape(notification_type_label($notification['type'])); ?></span>
                                <span class="meta-inline"><?php echo icon('clock', 'icon icon-sm'); ?> <?php echo time_ago($notification['created_at']); ?></span>
                            </div>
                        </div>
                        <div class="notification-actions-btn">
                            <?php if (!$notification['is_read']): ?>
                                <button class="btn-icon mark-read-btn btn-with-icon"
                                        data-notification-id="<?php echo $notification['id']; ?>"
                                        title="Okundu işaretle"
                                        type="button">
                                    <?php echo icon('check', 'icon icon-sm'); ?>
                                </button>
                            <?php endif; ?>
                            <button class="btn-icon delete-notification-btn btn-with-icon"
                                    data-notification-id="<?php echo $notification['id']; ?>"
                                    title="Sil"
                                    type="button">
                                <?php echo icon('trash', 'icon icon-sm'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination mt-30">
                    <?php if ($pagination['has_previous']): ?>
                        <a href="<?php echo url('/bildirimler?page=' . ($pagination['current_page'] - 1)); ?>">
                            Önceki
                        </a>
                    <?php endif; ?>

                    <span class="active"><?php echo $pagination['current_page']; ?> / <?php echo $pagination['total_pages']; ?></span>

                    <?php if ($pagination['has_next']): ?>
                        <a href="<?php echo url('/bildirimler?page=' . ($pagination['current_page'] + 1)); ?>">
                            Sonraki
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon"><?php echo icon('bell', 'icon icon-lg'); ?></div>
                <h2>Bildirim Yok</h2>
                <p>Henüz hiç bildiriminiz bulunmuyor.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', async function(e) {
        if (e.target.closest('.btn-icon')) {
            return;
        }

        const notificationId = this.dataset.notificationId;
        const link = this.dataset.link;

        if (this.classList.contains('unread')) {
            await NotificationAPI.markRead(notificationId);
        }

        if (link && link !== 'null') {
            window.location.href = link;
        }
    });
});

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
                item.classList.add('is-fading');
                setTimeout(() => item.remove(), 300);
                Utils.showToast('Bildirim silindi', 'success');
            }
        } catch (error) {
            Utils.showToast(error.message, 'error');
        }
    });
});

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
