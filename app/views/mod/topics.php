<div class="admin-card">
    <div class="admin-card-body admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Konu</th><th>Kategori</th><th>Yazar</th><th>Durum</th><th>İşlemler</th></tr>
            </thead>
            <tbody>
                <?php foreach ($topics as $topic): ?>
                    <tr>
                        <td><a href="<?php echo url('/konu/' . $topic['slug']); ?>"><?php echo escape($topic['title']); ?></a></td>
                        <td><?php echo escape($topic['category_name']); ?></td>
                        <td><?php echo escape($topic['username']); ?></td>
                        <td>
                            <?php if ($topic['is_pinned']): ?><span class="admin-badge-status">Sabit</span><?php endif; ?>
                            <?php if ($topic['is_locked']): ?><span class="admin-badge-status admin-badge-banned">Kilitli</span><?php endif; ?>
                        </td>
                        <td class="admin-actions">
                            <form method="post" class="admin-inline-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="topic_id" value="<?php echo (int)$topic['id']; ?>">
                                <?php if (can_mod('pin_topic')): ?>
                                    <button type="submit" name="action" value="<?php echo $topic['is_pinned'] ? 'unpin' : 'pin'; ?>" class="admin-btn admin-btn-sm admin-btn-outline"><?php echo $topic['is_pinned'] ? 'Sabiti Kaldır' : 'Sabitle'; ?></button>
                                <?php endif; ?>
                                <?php if (can_mod('lock_topic')): ?>
                                    <button type="submit" name="action" value="<?php echo $topic['is_locked'] ? 'unlock' : 'lock'; ?>" class="admin-btn admin-btn-sm admin-btn-outline"><?php echo $topic['is_locked'] ? 'Kilidi Aç' : 'Kilitle'; ?></button>
                                <?php endif; ?>
                                <?php if (can_mod('delete_topic')): ?>
                                    <button type="submit" name="action" value="delete" class="admin-btn admin-btn-sm admin-btn-outline" onclick="return confirm('Silinsin mi?')">Sil</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
