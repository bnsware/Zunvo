<div class="form-container">
    <div class="form-header">
        <h1>Yeni Konu Oluştur</h1>
        <p>Topluluğa sorularınızı sorun, deneyimlerinizi paylaşın veya tartışma başlatın</p>
    </div>

    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo escape($errors['general']); ?>
        </div>
    <?php endif; ?>

    <div class="writing-tips">
        <h3>İyi Bir Konu Nasıl Açılır?</h3>
        <ul>
            <li>Açık ve anlaşılır bir başlık seçin</li>
            <li>Konuyu detaylı açıklayın, gerekli bağlamı verin</li>
            <li>İlgili kategoriyi seçin</li>
            <li>Uygun etiketler ekleyerek konunuzu bulunabilir yapın</li>
            <li>Saygılı ve yapıcı olun</li>
        </ul>
    </div>

    <form method="POST" action="<?php echo !empty($locked_category) ? url('/kategori/' . $locked_category['slug'] . '/yeni-konu') : url('/konu/olustur'); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-group">
            <label class="form-label form-label-strong" for="category_id">Kategori</label>
            <?php if (!empty($locked_category)): ?>
                <input type="hidden" name="category_id" value="<?php echo (int)$locked_category['id']; ?>">
                <div class="form-static-value"><?php echo escape($locked_category['name']); ?></div>
            <?php else: ?>
            <select id="category_id" name="category_id" class="form-select" required>
                <option value="">Kategori seçin...</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                            <?php echo (isset($old_data['category_id']) && $old_data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo escape($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <?php if (isset($errors['category_id'])): ?>
                <div class="form-error"><?php echo escape($errors['category_id']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label form-label-strong" for="title">Başlık</label>
            <input
                type="text"
                id="title"
                name="title"
                class="form-input"
                value="<?php echo isset($old_data['title']) ? escape($old_data['title']) : ''; ?>"
                placeholder="Konunuzun başlığını girin (en az 10 karakter)"
                required
                minlength="10"
                maxlength="255"
                data-char-hint="title-hint"
                data-char-min="10"
            >
            <div class="form-hint" id="title-hint" data-default-text="Konunuzu en iyi açıklayan kısa ve öz bir başlık yazın">
                Konunuzu en iyi açıklayan kısa ve öz bir başlık yazın
            </div>
            <?php if (isset($errors['title'])): ?>
                <div class="form-error"><?php echo escape($errors['title']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label form-label-strong" for="content">
                İçerik
            </label>
            <?php echo bbcode_toolbar_html('content', isset($old_data['content']) ? $old_data['content'] : '', ['hint_id' => 'content-hint']); ?>
            <div class="form-hint" id="content-hint" data-default-text="En az 20 karakter. Biçimlendirme anında görünür; kayıtta BBCode olarak saklanır.">
                En az 20 karakter. Biçimlendirme anında görünür; kayıtta BBCode olarak saklanır.
            </div>
            <?php if (isset($errors['content'])): ?>
                <div class="form-error"><?php echo escape($errors['content']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label form-label-strong" for="tags">
                Etiketler <span class="form-label-optional">(Opsiyonel)</span>
            </label>
            <input
                type="text"
                id="tags"
                name="tags"
                class="form-input"
                value="<?php echo isset($old_data['tags']) ? escape($old_data['tags']) : ''; ?>"
                placeholder="php, javascript, react (virgülle ayırın)"
            >
            <div class="form-hint">
                Konunuzu kategorize etmek için etiketler ekleyin. Virgülle ayırarak birden fazla etiket ekleyebilirsiniz.
            </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn btn-primary btn-with-icon">
                <?php echo icon('send', 'icon icon-sm'); ?> Konuyu Yayınla
            </button>
            <a href="<?php echo url('/'); ?>" class="btn btn-cancel">İptal</a>
        </div>
    </form>
</div>
