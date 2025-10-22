<?php
/**
 * Zunvo Forum Sistemi
 * Konu Oluşturma Sayfası
 */
?>
<style>
    .create-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .create-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #eee;
    }
    .create-header h1 {
        font-size: 28px;
        color: #333;
        margin-bottom: 10px;
    }
    .create-header p {
        color: #666;
        font-size: 14px;
    }
    .form-group {
        margin-bottom: 25px;
    }
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    .form-label-optional {
        color: #999;
        font-weight: normal;
        font-size: 13px;
    }
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.3s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #007bff;
    }
    .form-textarea {
        min-height: 200px;
        resize: vertical;
        line-height: 1.6;
    }
    .form-hint {
        font-size: 13px;
        color: #666;
        margin-top: 5px;
    }
    .form-error {
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
    }
    .form-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .btn-cancel {
        background: #6c757d;
        color: white;
    }
    .btn-cancel:hover {
        background: #5a6268;
    }
    .writing-tips {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }
    .writing-tips h3 {
        font-size: 16px;
        color: #333;
        margin-bottom: 10px;
    }
    .writing-tips ul {
        margin: 0;
        padding-left: 20px;
    }
    .writing-tips li {
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="create-container">
    <div class="create-header">
        <h1>🎯 Yeni Konu Oluştur</h1>
        <p>Topluluğa sorularınızı sorun, deneyimlerinizi paylaşın veya tartışma başlatın</p>
    </div>
    
    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo escape($errors['general']); ?>
        </div>
    <?php endif; ?>
    
    <div class="writing-tips">
        <h3>💡 İyi Bir Konu Nasıl Açılır?</h3>
        <ul>
            <li>Açık ve anlaşılır bir başlık seçin</li>
            <li>Konuyu detaylı açıklayın, gerekli bağlamı verin</li>
            <li>İlgili kategoriyi seçin</li>
            <li>Uygun etiketler ekleyerek konunuzu bulunabilir yapın</li>
            <li>Saygılı ve yapıcı olun</li>
        </ul>
    </div>
    
    <form method="POST" action="<?php echo url('/konu/olustur'); ?>">
        <?php echo csrf_field(); ?>
        
        <div class="form-group">
            <label class="form-label" for="category_id">
                Kategori
            </label>
            <select 
                id="category_id" 
                name="category_id" 
                class="form-select"
                required
            >
                <option value="">Kategori seçin...</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"
                            <?php echo (isset($old_data['category_id']) && $old_data['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo escape($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category_id'])): ?>
                <div class="form-error"><?php echo escape($errors['category_id']); ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="title">
                Başlık
            </label>
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
            >
            <div class="form-hint">
                Konunuzu en iyi açıklayan kısa ve öz bir başlık yazın
            </div>
            <?php if (isset($errors['title'])): ?>
                <div class="form-error"><?php echo escape($errors['title']); ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="content">
                İçerik
            </label>
            <textarea 
                id="content" 
                name="content" 
                class="form-textarea"
                placeholder="Konunuzu detaylı olarak açıklayın...

• Sorununuzu veya konunuzu net bir şekilde anlatın
• Varsa hata mesajlarını ekleyin
• Denediğiniz çözümleri paylaşın
• Kod paylaşıyorsanız düzgün formatlayın

@kullaniciadi yazarak kullanıcıları bahsedebilirsiniz"
                required
                minlength="20"
            ><?php echo isset($old_data['content']) ? escape($old_data['content']) : ''; ?></textarea>
            <div class="form-hint">
                En az 20 karakter. Detaylı açıklama daha iyi yanıtlar almanızı sağlar.
            </div>
            <?php if (isset($errors['content'])): ?>
                <div class="form-error"><?php echo escape($errors['content']); ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="tags">
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
            <button type="submit" class="btn btn-primary">
                📝 Konuyu Yayınla
            </button>
            <a href="<?php echo url('/'); ?>" class="btn btn-cancel">
                ✕ İptal
            </a>
        </div>
    </form>
</div>

<script>
// Karakter sayacı (opsiyonel)
const titleInput = document.getElementById('title');
const contentTextarea = document.getElementById('content');

function updateCharCount(element, minChars) {
    const length = element.value.length;
    const hint = element.nextElementSibling;
    
    if (hint && hint.classList.contains('form-hint')) {
        if (length < minChars) {
            hint.textContent = `${minChars - length} karakter daha (en az ${minChars} karakter)`;
            hint.style.color = '#dc3545';
        } else {
            hint.style.color = '#28a745';
        }
    }
}

titleInput.addEventListener('input', function() {
    updateCharCount(this, 10);
});

contentTextarea.addEventListener('input', function() {
    updateCharCount(this, 20);
});
</script>