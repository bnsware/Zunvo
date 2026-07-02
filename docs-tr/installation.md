# Kurulum

[English](../docs/installation.md) | **Türkçe**

## Gereksinimler

- PHP 8.0 veya üzeri
- MySQL 5.7+ / MariaDB 10.2+
- PDO ve mbstring eklentileri
- Apache (`mod_rewrite`) veya Nginx URL rewrite
- Yazılabilir: `storage/`, `public/uploads/`

## Hostinger / paylaşımlı hosting

1. Tüm dosyaları `public_html` veya alt klasöre yükleyin
2. Hosting panelinden MySQL veritabanı ve kullanıcı oluşturun
3. Tarayıcıda `https://siteniz.com/install.php` adresine gidin
4. Sihirbaz adımlarını tamamlayın:
   - Sistem kontrolü
   - Veritabanı bilgileri
   - Tabloların oluşturulması
   - İlk admin hesabı
5. Kurulum bitince **`install.php` dosyasını silin**

`SITE_URL` ve `BASE_PATH` çoğu ortamda otomatik algılanır; alt klasör kurulumunda ek ayar gerekmez.

## Manuel kurulum

1. `config/config.sample.php` → `config/config.php` kopyalayın
2. `config/detect.sample.php` → `config/detect.php` kopyalayın
3. `config/database.php` içinde veritabanı bilgilerini düzenleyin
4. `database/schema.sql` ve `database/seed.sql` dosyalarını phpMyAdmin'de çalıştırın
5. `storage/` ve `public/uploads/avatars`, `public/uploads/attachments` klasörlerinin yazılabilir olduğundan emin olun (genelde `755`)
6. `storage/install.lock` oluşturulmuş olmalı (sihirbaz bunu yapar)

## Kurulum sonrası

| Adres | Açıklama |
|-------|----------|
| `/` | Ana sayfa |
| `/giris` | Giriş |
| `/admin` | Yönetim paneli |
| `/mod` | Moderasyon paneli |

İlk girişte admin hesabınızla `/admin` üzerinden site adı, kayıt ayarları ve kategorileri yapılandırın.

## Sorun giderme

| Sorun | Çözüm |
|-------|--------|
| `detect.php bulunamadı` | `config/detect.sample.php` → `config/detect.php` kopyalayın |
| 404 tüm sayfalarda | `.htaccess` aktif mi, `mod_rewrite` açık mı kontrol edin |
| Veritabanı bağlantı hatası | `config/database.php` bilgilerini doğrulayın |
| Avatar yüklenmiyor | `public/uploads/avatars` izinleri (755/775) |
| Beyaz sayfa | `config/config.php` içinde `DEBUG_MODE` true yapıp hatayı okuyun; üretimde false tutun |
| Kurulum tekrar açılıyor | `storage/install.lock` var mı kontrol edin |

## Güvenlik notları

- Üretimde `DEBUG_MODE` kapalı olsun
- `install.php` silinsin
- `config/database.php` ve `config/config.php` web üzerinden erişilemez olmalı (`.htaccess` ile korunur)
- Düzenli yedek: veritabanı + `public/uploads/`
