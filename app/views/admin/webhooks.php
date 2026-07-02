<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header"><h2>Yeni Webhook</h2></div>
        <div class="admin-card-body">
            <form method="post" action="<?php echo url('/admin/webhooks'); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="create">
                <div class="admin-form-group">
                    <label for="name">Ad</label>
                    <input type="text" name="name" id="name" class="admin-input" required>
                </div>
                <div class="admin-form-group">
                    <label for="url">URL</label>
                    <input type="url" name="url" id="url" class="admin-input" placeholder="https://..." required>
                </div>
                <div class="admin-form-group">
                    <label for="event_type">Olay Türü</label>
                    <select name="event_type" id="event_type" class="admin-input">
                        <option value="topic.created">topic.created</option>
                        <option value="post.created">post.created</option>
                        <option value="user.registered">user.registered</option>
                        <option value="report.created">report.created</option>
                    </select>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Oluştur</button>
                    <button type="submit" name="action" value="test" class="admin-btn admin-btn-outline" formnovalidate>Test Gönder</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header"><h2>Mevcut Webhooklar</h2></div>
        <div class="admin-card-body admin-table-wrap">
            <?php if (empty($webhooks)): ?>
                <p class="admin-empty">Henüz webhook yok.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ad</th>
                            <th>URL</th>
                            <th>Olay</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($webhooks as $hook): ?>
                            <tr>
                                <td><?php echo escape($hook['name']); ?></td>
                                <td><?php echo escape(truncate($hook['url'], 40)); ?></td>
                                <td><code><?php echo escape($hook['event_type']); ?></code></td>
                                <td>
                                    <?php if ($hook['is_active']): ?>
                                        <span class="admin-badge-status admin-badge-active">Aktif</span>
                                    <?php else: ?>
                                        <span class="admin-badge-status">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($hook['created_at']); ?></td>
                                <td class="admin-actions">
                                    <form method="post" action="<?php echo url('/admin/webhooks'); ?>" class="admin-inline-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="test">
                                        <input type="hidden" name="id" value="<?php echo (int)$hook['id']; ?>">
                                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-outline">Test</button>
                                    </form>
                                    <form method="post" action="<?php echo url('/admin/webhooks'); ?>" class="admin-inline-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$hook['id']; ?>">
                                        <input type="hidden" name="is_active" value="<?php echo $hook['is_active'] ? '0' : '1'; ?>">
                                        <button type="submit" name="action" value="toggle" class="admin-btn admin-btn-sm admin-btn-outline">
                                            <?php echo $hook['is_active'] ? 'Devre Dışı' : 'Etkinleştir'; ?>
                                        </button>
                                    </form>
                                    <form method="post" action="<?php echo url('/admin/webhooks'); ?>" class="admin-inline-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$hook['id']; ?>">
                                        <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger" data-confirm="Bu webhook'u silmek istediğinize emin misiniz?">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header"><h2>Webhook Kullanımı</h2></div>
    <div class="admin-card-body">
        <p>Webhook'lar olay gerçekleştiğinde JSON POST gönderir. İmza doğrulama için <code>X-Zunvo-Signature</code> header (HMAC-SHA256) kullanılır. Test gönderiminde payload içinde <code>"test": true</code> bulunur. Discord webhook URL'leri için test mesajı Discord formatında gönderilir.</p>
        <p>Discord'a sürekli bildirim için <strong>Discord Bildirim</strong> eklentisini kullanın; admin webhook'ları genel JSON entegrasyonları içindir.</p>
        <h4>Payload örneği</h4>
        <pre class="admin-code-block">{"event":"topic.created","data":{"topic_id":1},"timestamp":1234567890}</pre>
        <h4>Olay türleri</h4>
        <ul class="admin-docs-list">
            <li><code>topic.created</code> — Yeni konu</li>
            <li><code>post.created</code> — Yeni gönderi</li>
            <li><code>user.registered</code> — Yeni üye</li>
            <li><code>report.created</code> — Yeni rapor</li>
        </ul>
    </div>
</div>
