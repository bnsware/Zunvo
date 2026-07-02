<div class="form-container">
    <div class="form-header">
        <h1>Konu Düzenle</h1>
        <p><?php echo is_moderator() ? 'Başlığı doğrudan güncelleyebilirsiniz.' : 'Başlık değişikliği admin/mod onayına gönderilir. Kategori değiştirilemez.'; ?></p>
    </div>

    <?php if (isset($errors['csrf'])): ?>
        <div class="alert alert-danger"><?php echo escape($errors['csrf']); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo url('/konu/duzenle/' . $topic['slug']); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label form-label-strong">Kategori</label>
            <div class="form-static-value"><?php echo escape($topic['category_name']); ?></div>
        </div>

        <div class="form-group">
            <label class="form-label form-label-strong" for="title">Başlık</label>
            <input
                type="text"
                id="title"
                name="title"
                class="form-input"
                value="<?php echo escape($topic['title']); ?>"
                required
                minlength="10"
                maxlength="255"
            >
            <?php if (isset($errors['title'])): ?>
                <div class="form-error"><?php echo escape($errors['title']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary"><?php echo is_moderator() ? 'Güncelle' : 'Onaya Gönder'; ?></button>
            <a href="<?php echo url('/konu/' . $topic['slug']); ?>" class="btn btn-cancel">İptal</a>
        </div>
    </form>
</div>
