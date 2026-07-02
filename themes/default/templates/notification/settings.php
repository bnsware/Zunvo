<?php
$prefs = array_merge([
    'email_notifications' => false,
    'mention_notifications' => true,
    'reply_notifications' => true,
    'upvote_notifications' => true,
    'solution_notifications' => true
], $preferences ?? []);
?>
<div class="form-container form-container-narrow">
    <div class="form-header">
        <h1>Bildirim Ayarları</h1>
        <p>Hangi bildirimleri almak istediğinizi seçin</p>
    </div>

    <form method="POST" action="<?php echo url('/bildirim/ayarlar'); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-checkbox-group">
            <div class="form-checkbox-item">
                <input type="checkbox" id="email_notifications" name="email_notifications" value="1"
                    <?php echo !empty($prefs['email_notifications']) ? 'checked' : ''; ?>>
                <div class="form-checkbox-item-content">
                    <h4>Email Bildirimleri</h4>
                    <p>Önemli bildirimler email adresinize de gönderilsin.</p>
                </div>
            </div>

            <div class="form-checkbox-item">
                <input type="checkbox" id="mention_notifications" name="mention_notifications" value="1"
                    <?php echo !empty($prefs['mention_notifications']) ? 'checked' : ''; ?>>
                <div class="form-checkbox-item-content">
                    <h4><?php echo icon('mention', 'icon icon-sm'); ?> Bahsetmeler</h4>
                    <p>Birisi sizi @kullaniciadi ile bahsettiğinde bildirim alın.</p>
                </div>
            </div>

            <div class="form-checkbox-item">
                <input type="checkbox" id="reply_notifications" name="reply_notifications" value="1"
                    <?php echo !empty($prefs['reply_notifications']) ? 'checked' : ''; ?>>
                <div class="form-checkbox-item-content">
                    <h4><?php echo icon('reply', 'icon icon-sm'); ?> Yanıtlar</h4>
                    <p>Konularınıza veya gönderilerinize yanıt geldiğinde bildirim alın.</p>
                </div>
            </div>

            <div class="form-checkbox-item">
                <input type="checkbox" id="upvote_notifications" name="upvote_notifications" value="1"
                    <?php echo !empty($prefs['upvote_notifications']) ? 'checked' : ''; ?>>
                <div class="form-checkbox-item-content">
                    <h4><?php echo icon('thumbs-up', 'icon icon-sm'); ?> Beğeniler</h4>
                    <p>Gönderileriniz beğenildiğinde bildirim alın.</p>
                </div>
            </div>

            <div class="form-checkbox-item">
                <input type="checkbox" id="solution_notifications" name="solution_notifications" value="1"
                    <?php echo !empty($prefs['solution_notifications']) ? 'checked' : ''; ?>>
                <div class="form-checkbox-item-content">
                    <h4><?php echo icon('check', 'icon icon-sm'); ?> Çözüm İşaretlemeleri</h4>
                    <p>Gönderiniz çözüm olarak işaretlendiğinde bildirim alın.</p>
                </div>
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary">Ayarları Kaydet</button>
            <a href="<?php echo url('/bildirimler'); ?>" class="btn btn-outline">Bildirimlere Dön</a>
        </div>
    </form>
</div>
