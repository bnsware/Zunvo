# Modern Forum Sistemi - Detaylı Proje Planı

## 📋 Proje Genel Bakış

Saf PHP ile geliştirilecek, modern, genişletilebilir forum sistemi. Composer veya framework kullanılmayacak, sadece native PHP özellikleri kullanılacak.

---

## ✨ Temel Özellikler

### Kullanıcı Özellikleri
- ✅ Kayıt/Giriş/Çıkış sistemi
- ✅ Kullanıcı profilleri (avatar, biyografi, istatistikler)
- ✅ Email doğrulama
- ✅ Şifre sıfırlama
- ✅ Kullanıcı rolleri (User, Moderator, Admin)
- ✅ Reputasyon sistemi (upvote/downvote ile artış/azalış)
- ✅ Kullanıcı seviyeleri (Yeni, Aktif, Veteran vb.)

### Forum Özellikleri
- ✅ Kategori sistemi (sınırsız kategori)
- ✅ Konu açma/düzenleme/silme
- ✅ Yorum yapma/düzenleme/silme
- ✅ Upvote/Downvote sistemi
- ✅ En iyi yanıt seçme (çözüm işaretleme)
- ✅ Konu sabitleme (pin)
- ✅ Konu kilitleme
- ✅ Görüntülenme sayısı
- ✅ Arama sistemi (başlık, içerik, kullanıcı)
- ✅ Etiket sistemi (hashtag benzeri)

### Bildirim Sistemi
- ✅ Yeni yorum bildirimi
- ✅ Mention bildirimi (@kullanıcıadı)
- ✅ Upvote bildirimi
- ✅ Çözüm seçilme bildirimi
- ✅ Gerçek zamanlı bildirimler (AJAX polling)

### Moderasyon
- ✅ Yorum onay sistemi
- ✅ Kullanıcı yasaklama (ban)
- ✅ İçerik silme/gizleme
- ✅ Kullanıcı raporlama
- ✅ Moderasyon log kayıtları

### Yönetim Paneli
- ✅ Kullanıcı yönetimi
- ✅ Kategori yönetimi
- ✅ Site ayarları
- ✅ Tema yönetimi
- ✅ Plugin yönetimi
- ✅ İstatistikler (grafikler)

---

## 🏗️ Teknik Mimari

### Kullanılacak Teknolojiler
- **Backend:** Saf PHP 8.0+ (OOP yaklaşım)
- **Veritabanı:** MySQL/MariaDB (PDO ile)
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **AJAX:** Fetch API (gerçek zamanlı özellikler için)
- **Güvenlik:** PDO prepared statements, CSRF token, XSS filtreleme

### Mimari Yapı
**MVC Benzeri Yapı (Custom)**
```
Request → Router → Controller → Model → Database
                      ↓
                    View → Response
```

### URL Yapısı (SEO Friendly)
```
/ → Ana sayfa
/kategori/genel → Kategori sayfası
/konu/dizilla-bozuldu-123 → Konu detay
/profil/kullanici-adi → Profil sayfası
/admin → Yönetim paneli
```

---

## 📁 Klasör Yapısı

```
forum/
├── index.php                    # Ana giriş noktası (Router)
├── .htaccess                    # URL rewriting
│
├── config/
│   ├── database.php             # DB bağlantı ayarları
│   ├── config.php               # Genel ayarlar
│   └── routes.php               # Route tanımları
│
├── core/                        # Çekirdek sistem
│   ├── Router.php               # URL yönlendirme
│   ├── Database.php             # PDO wrapper class
│   ├── Controller.php           # Base controller
│   ├── Model.php                # Base model
│   ├── View.php                 # Template engine
│   ├── Session.php              # Oturum yönetimi
│   ├── Security.php             # Güvenlik fonksiyonları
│   ├── Validator.php            # Form validasyon
│   ├── Upload.php               # Dosya yükleme
│   ├── Pagination.php           # Sayfalama
│   ├── Plugin.php               # Plugin loader
│   └── Theme.php                # Tema loader
│
├── app/
│   ├── controllers/             # Controller dosyaları
│   │   ├── HomeController.php
│   │   ├── TopicController.php
│   │   ├── UserController.php
│   │   ├── AuthController.php
│   │   └── AdminController.php
│   │
│   ├── models/                  # Model dosyaları
│   │   ├── User.php
│   │   ├── Topic.php
│   │   ├── Post.php
│   │   ├── Category.php
│   │   ├── Vote.php
│   │   └── Notification.php
│   │
│   └── views/                   # View dosyaları
│       ├── layouts/
│       │   ├── header.php
│       │   ├── footer.php
│       │   └── sidebar.php
│       ├── home/
│       │   └── index.php
│       ├── topic/
│       │   ├── index.php
│       │   ├── show.php
│       │   └── create.php
│       ├── user/
│       │   ├── profile.php
│       │   └── settings.php
│       └── admin/
│           └── dashboard.php
│
├── public/                      # Genel erişilebilir dosyalar
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   ├── js/
│   │   ├── main.js
│   │   ├── ajax.js
│   │   └── admin.js
│   ├── images/
│   └── uploads/
│       ├── avatars/
│       └── attachments/
│
├── plugins/                     # Eklenti klasörü
│   ├── example-plugin/
│   │   ├── plugin.json          # Eklenti bilgileri
│   │   ├── PluginName.php       # Ana plugin dosyası
│   │   └── views/
│   └── another-plugin/
│
├── themes/                      # Tema klasörü
│   ├── default/
│   │   ├── theme.json           # Tema bilgileri
│   │   ├── style.css
│   │   └── views/               # Tema özel view'lar
│   └── dark-mode/
│
└── storage/                     # Geçici dosyalar
    ├── cache/
    ├── logs/
    └── sessions/
```

---

## 🗄️ Veritabanı Şeması

### Tablolar

#### 1. users
```sql
id, username, email, password, avatar, biography,
reputation, role, email_verified, is_banned,
created_at, last_active
```

#### 2. categories
```sql
id, name, slug, description, icon, color,
order_num, created_at
```

#### 3. topics
```sql
id, category_id, user_id, title, slug,
is_pinned, is_locked, views, created_at, updated_at
```

#### 4. posts
```sql
id, topic_id, user_id, content,
upvotes, downvotes, is_solution,
created_at, updated_at
```

#### 5. votes
```sql
id, user_id, post_id, vote_type,
created_at
```

#### 6. notifications
```sql
id, user_id, type, message, link,
is_read, created_at
```

#### 7. tags
```sql
id, name, slug, usage_count
```

#### 8. topic_tags
```sql
topic_id, tag_id
```

#### 9. user_follows
```sql
id, follower_id, following_id, created_at
```

#### 10. reports
```sql
id, reporter_id, reported_user_id, post_id,
reason, status, created_at
```

#### 11. settings
```sql
id, setting_key, setting_value
```

#### 12. plugins
```sql
id, name, slug, version, is_active, settings
```

#### 13. themes
```sql
id, name, slug, is_active, settings
```

---

## ⚙️ Core Sistem Bileşenleri

### 1. Router (core/Router.php)
**Görev:** URL'leri parse edip ilgili controller'a yönlendirir
**Özellikler:**
- RESTful routing
- Dynamic route parametreleri (/topic/{id})
- Middleware desteği (auth kontrolü)
- 404 handling

### 2. Database (core/Database.php)
**Görev:** PDO wrapper, veritabanı işlemleri
**Özellikler:**
- CRUD metodları (select, insert, update, delete)
- Query builder benzeri metotlar
- Transaction desteği
- Prepared statements

### 3. Security (core/Security.php)
**Görev:** Güvenlik işlemleri
**Özellikler:**
- CSRF token oluşturma/doğrulama
- XSS filtreleme
- SQL injection koruması (PDO ile)
- Password hashing (bcrypt)
- Input sanitization

### 4. Session (core/Session.php)
**Görev:** Oturum yönetimi
**Özellikler:**
- Secure session başlatma
- Flash message sistemi
- Session hijacking koruması
- Remember me özelliği

### 5. Validator (core/Validator.php)
**Görev:** Form validasyon
**Özellikler:**
- Required, email, min/max length
- Unique kontrolü (DB)
- Custom rule desteği
- Hata mesajları

### 6. View (core/View.php)
**Görev:** Template rendering
**Özellikler:**
- Layout sistemi
- Data passing
- XSS escape metodları
- Partial view include

### 7. Plugin (core/Plugin.php)
**Görev:** Plugin yükleme ve çalıştırma
**Özellikler:**
- Hook sistemi (before_post, after_post vb.)
- Plugin aktivasyon/deaktivasyon
- Plugin settings yönetimi
- Auto-load

### 8. Theme (core/Theme.php)
**Görev:** Tema yönetimi
**Özellikler:**
- Tema yükleme
- CSS/JS injection
- View override
- Tema ayarları

---

## 🔌 Plugin Sistemi

### Plugin Yapısı
```php
plugins/my-plugin/
├── plugin.json              # Metadata
├── MyPlugin.php             # Ana class
├── views/                   # Plugin view'ları
└── assets/                  # CSS/JS
```

### plugin.json Örneği
```json
{
  "name": "My Plugin",
  "slug": "my-plugin",
  "version": "1.0.0",
  "author": "Author Name",
  "description": "Plugin açıklaması",
  "hooks": ["before_post_create", "after_user_login"]
}
```

### Hook Sistemi
```php
// Plugin içinde
class MyPlugin {
    public function before_post_create($data) {
        // Post oluşturulmadan önce çalışır
        return $data;
    }
}

// Core'da kullanım
$data = Plugin::executeHook('before_post_create', $data);
```

---

## 🎨 Tema Sistemi

### Tema Yapısı
```php
themes/my-theme/
├── theme.json               # Metadata
├── style.css                # Ana CSS
├── views/                   # View override'lar
│   ├── home/
│   └── topic/
└── assets/
    ├── images/
    └── js/
```

### theme.json Örneği
```json
{
  "name": "My Theme",
  "slug": "my-theme",
  "version": "1.0.0",
  "author": "Author Name",
  "thumbnail": "screenshot.png",
  "settings": {
    "primary_color": "#007bff",
    "secondary_color": "#6c757d"
  }
}
```

---

## 🔒 Güvenlik Önlemleri

### Uygulanacak Güvenlik Katmanları

1. **SQL Injection Koruması**
   - PDO Prepared Statements kullanımı
   - Tüm user input'lar parametrize edilecek

2. **XSS Koruması**
   - htmlspecialchars() ile output escape
   - Content Security Policy header'ları

3. **CSRF Koruması**
   - Her formda unique token
   - Token validation

4. **Session Güvenliği**
   - Secure, HttpOnly cookies
   - Session regeneration
   - IP/User-Agent kontrolü

5. **Password Güvenliği**
   - bcrypt hashing
   - Minimum 8 karakter zorunluluğu
   - Brute force koruması (rate limiting)

6. **File Upload Güvenliği**
   - Mime type kontrolü
   - Extension whitelist
   - File size limit
   - Rename uploaded files

7. **Rate Limiting**
   - API/Form submission limitleri
   - IP bazlı throttling

---

## 🚀 Geliştirme Aşamaları

### Faz 1: Core Sistem (1. Hafta)
- [ ] Router sistemi
- [ ] Database wrapper
- [ ] MVC altyapısı
- [ ] Session yönetimi
- [ ] Security katmanı

### Faz 2: Kullanıcı Sistemi (2. Hafta)
- [ ] Kayıt/Giriş
- [ ] Email doğrulama
- [ ] Profil sistemi
- [ ] Rol yönetimi

### Faz 3: Forum Özellikleri (3. Hafta)
- [ ] Kategori sistemi
- [ ] Konu oluşturma
- [ ] Yorum sistemi
- [ ] Upvote/Downvote

### Faz 4: İleri Özellikler (4. Hafta)
- [ ] Bildirim sistemi
- [ ] Arama
- [ ] Etiketler
- [ ] Moderasyon

### Faz 5: Yönetim ve Genişletilebilirlik (5. Hafta)
- [ ] Admin paneli
- [ ] Plugin sistemi
- [ ] Tema sistemi
- [ ] API endpoints

### Faz 6: Optimizasyon ve Test (6. Hafta)
- [ ] Performans optimizasyonu
- [ ] Cache sistemi
- [ ] SEO optimizasyonu
- [ ] Test ve debug

---

## 📊 Özellik Detayları

### Upvote/Downvote Sistemi
- Her kullanıcı bir post'a sadece 1 oy verebilir
- Oyunu değiştirebilir (up'dan down'a)
- Post sahibi kendi postuna oy veremez
- Upvote: +1 reputation, Downvote: -1 reputation
- Top posts (en çok oylanan) sıralama

### Reputasyon Sistemi
- Upvote alınca: +10 puan
- Downvote alınca: -5 puan
- Çözüm seçilince: +15 puan
- Yeni konu açınca: +5 puan
- Seviyeler: Çaylak (0-50), Aktif (51-200), Veteran (201-500), Efsane (501+)

### Bildirim Sistemi
- AJAX ile 30 saniyede bir kontrol
- Dropdown bildirim listesi
- Okunmamış sayısı badge
- Bildirim türleri: yorum, mention, upvote, çözüm

### Arama Sistemi
- Başlık ve içerikte arama
- Kategori filtresi
- Kullanıcı filtresi
- Tarih aralığı filtresi
- Sıralama: En yeni, En çok görüntülenen, En çok oylanan

---

## 🎯 Benzersiz Özellikler (Dizilla'dan İlham)

1. **Sıcak Konular (Hot Topics)**
   - Son 24 saatte en çok etkileşim alan konular
   - Algoritma: (upvotes + comments) / time_since_created

2. **Kullanıcı Rozetleri**
   - İlk konu, İlk çözüm, 100 upvote gibi rozetler
   - Profilde görünür

3. **Trend Etiketler**
   - Son 7 günde en çok kullanılan etiketler
   - Sidebar widget

4. **Önerilen Konular**
   - Benzer etiketlere sahip konular
   - "Bunlar da ilginizi çekebilir" önerisi

5. **Katkı Grafiği**
   - Kullanıcı profilinde GitHub tarzı aktivite grafiği
   - Günlük post/yorum sayısı

---

## 📱 Responsive Tasarım

### Breakpoint'ler
- Desktop: 1200px+
- Tablet: 768px - 1199px
- Mobile: 320px - 767px

### Mobile Özellikler
- Hamburger menü
- Touch-friendly button'lar
- Swipe gesture'lar
- Pull-to-refresh
- Bottom navigation (opsiyonel)

---

## 🔧 Yapılandırma Dosyaları

### config/config.php
```php
define('SITE_NAME', 'Forum Adı');
define('SITE_URL', 'http://localhost/forum');
define('TIMEZONE', 'Europe/Istanbul');
define('POSTS_PER_PAGE', 20);
define('MAX_UPLOAD_SIZE', 2097152); // 2MB
```

### .htaccess
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

---

## 🎨 UI/UX Tasarım Prensipleri

1. **Minimalist ve Modern**
   - Gereksiz süslemelerden kaçınılacak
   - Flat design prensibi

2. **Dark Mode Desteği**
   - Toggle switch ile değiştirilebilir
   - Otomatik sistem temasını algılama

3. **Erişilebilirlik**
   - ARIA labels
   - Keyboard navigation
   - Screen reader uyumlu

4. **Hızlı Yükleme**
   - Lazy loading (images)
   - CSS/JS minification
   - GZIP compression

---

## 💾 Cache ve Performans

### Cache Stratejisi
- **Query Cache:** Sık kullanılan sorgular (kategoriler, hot topics)
- **Page Cache:** Statik sayfalar için full page cache
- **Object Cache:** User data, settings gibi nesneler
- **Cache TTL:** 5-60 dakika arası (veri tipine göre)

### Performans Optimizasyonları
- Database indexleme
- Lazy loading (görüntüler)
- Pagination
- AJAX ile partial loading
- CDN kullanımı (opsiyonel)

---

## 📝 Örnek API Endpoints (AJAX için)

```
POST /api/vote          → Oy verme
POST /api/post          → Yorum ekleme
GET  /api/notifications → Bildirimleri getir
POST /api/report        → Raporlama
GET  /api/search        → Arama
POST /api/follow        → Takip et
```

---

## ✅ Tamamlandığında Sistem Özellikleri

### Kullanıcı Deneyimi
- ✅ Hızlı ve responsive
- ✅ Kullanıcı dostu arayüz
- ✅ Kolay navigasyon
- ✅ Gerçek zamanlı bildirimler

### Yönetici Deneyimi
- ✅ Kapsamlı admin paneli
- ✅ Kolay içerik yönetimi
- ✅ Plugin/tema kurulumu
- ✅ Detaylı istatistikler

### Geliştirici Deneyimi
- ✅ Temiz kod yapısı
- ✅ Kolay plugin geliştirme
- ✅ Tema sistemine entegrasyon
- ✅ API documentation

---

## 🤔 Sık Sorulan Sorular

**S: Neden composer kullanmıyoruz?**
C: Proje gereksiniminde saf PHP kullanımı istendi. Tüm fonksiyonlar custom yazılacak.

**S: Performans nasıl olacak?**
C: Cache sistemi ve database optimizasyonları ile yüksek performans sağlanacak.

**S: Multi-language desteği var mı?**
C: İlk versiyonda Türkçe, daha sonra plugin ile çoklu dil eklenebilir.

**S: Real-time chat olacak mı?**
C: İlk versiyonda yok, ama plugin olarak eklenebilir (WebSocket ile).

---

## 🎉 Sonuç

Bu proje, modern bir forum sistemi geliştirmek için gerekli tüm bileşenleri içermektedir. Modüler yapısı sayesinde kolayca genişletilebilir ve özelleştirilebilir.
