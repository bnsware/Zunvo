<div class="admin-quick-links">
    <a href="<?php echo url('/admin/kategoriler'); ?>" class="admin-quick-link">
        <?php echo icon('folder', 'icon icon-sm'); ?>
        <span>Forum Yapısı</span>
    </a>
    <a href="<?php echo url('/admin/konular'); ?>" class="admin-quick-link">
        <?php echo icon('message', 'icon icon-sm'); ?>
        <span>Konular</span>
    </a>
    <a href="<?php echo url('/admin/kullanicilar'); ?>" class="admin-quick-link">
        <?php echo icon('users', 'icon icon-sm'); ?>
        <span>Kullanıcılar</span>
    </a>
    <a href="<?php echo url('/admin/raporlar'); ?>" class="admin-quick-link<?php echo $stats['reports_pending'] > 0 ? ' admin-quick-link-alert' : ''; ?>">
        <?php echo icon('alert', 'icon icon-sm'); ?>
        <span>Raporlar<?php if ($stats['reports_pending'] > 0): ?> (<?php echo (int)$stats['reports_pending']; ?>)<?php endif; ?></span>
    </a>
    <a href="<?php echo url('/admin/ayarlar'); ?>" class="admin-quick-link">
        <?php echo icon('settings', 'icon icon-sm'); ?>
        <span>Ayarlar</span>
    </a>
</div>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-value"><?php echo format_number($stats['users']); ?></div>
        <div class="admin-stat-label">Kullanıcı</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-value"><?php echo format_number($stats['topics']); ?></div>
        <div class="admin-stat-label">Konu</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-value"><?php echo format_number($stats['posts']); ?></div>
        <div class="admin-stat-label">Mesaj</div>
    </div>
    <div class="admin-stat-card<?php echo $stats['reports_pending'] > 0 ? ' admin-stat-warning' : ''; ?>">
        <div class="admin-stat-value"><?php echo format_number($stats['reports_pending']); ?></div>
        <div class="admin-stat-label">Bekleyen Rapor</div>
    </div>
</div>

<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Son Konular</h2>
            <a href="<?php echo url('/admin/konular'); ?>" class="admin-link">Tümü →</a>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recent_topics)): ?>
                <p class="admin-empty">Henüz konu yok.</p>
            <?php else: ?>
                <ul class="admin-activity-list">
                    <?php foreach ($recent_topics as $topic): ?>
                        <li>
                            <a href="<?php echo url('/konu/' . $topic['slug']); ?>"><?php echo escape($topic['title']); ?></a>
                            <span class="admin-meta"><?php echo escape($topic['username']); ?> · <?php echo time_ago($topic['created_at']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Yeni Kullanıcılar</h2>
            <a href="<?php echo url('/admin/kullanicilar'); ?>" class="admin-link">Tümü →</a>
        </div>
        <div class="admin-card-body">
            <?php if (empty($recent_users)): ?>
                <p class="admin-empty">Henüz kullanıcı yok.</p>
            <?php else: ?>
                <ul class="admin-activity-list">
                    <?php foreach ($recent_users as $u): ?>
                        <li>
                            <a href="<?php echo url('/admin/kullanici/' . $u['id']); ?>"><?php echo escape($u['username']); ?></a>
                            <span class="admin-meta"><?php echo escape($u['role']); ?> · <?php echo time_ago($u['created_at']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($recent_reports)): ?>
<div class="admin-card">
    <div class="admin-card-header">
        <h2>Son Raporlar</h2>
        <a href="<?php echo url('/admin/raporlar'); ?>" class="admin-link">Tümü →</a>
    </div>
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table admin-table-compact">
            <thead>
                <tr>
                    <th>Raporlayan</th>
                    <th>Sebep</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_reports as $report): ?>
                    <tr>
                        <td><?php echo escape($report['reporter']); ?></td>
                        <td><?php echo escape(truncate($report['reason'], 60)); ?></td>
                        <td><span class="admin-badge-status admin-badge-<?php echo escape($report['status']); ?>"><?php echo escape($report['status']); ?></span></td>
                        <td><?php echo time_ago($report['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
