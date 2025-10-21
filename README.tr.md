<div align="center">

[🇬🇧 English](README.md) | [🇹🇷 Türkçe](README.tr.md)

# 🌊 Zunvo

**Modern, Açık Kaynak Forum Yazılımı**

*Topluluklar için yeni nesil tartışma platformu*

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Status](https://img.shields.io/badge/Status-Under%20Development-orange.svg)]()
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg?logo=php)]()
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)]()

[Özellikler](#-özellikler) • [Yol Haritası](#-yol-haritası) • [Kurulum](#-kurulum) • [Katkıda Bulunun](#-katkıda-bulunun) • [Lisans](#-lisans)

---

### ⚠️ Geliştirme Aşamasında

Zunvo şu anda aktif olarak geliştirilmektedir. İlk kararlı sürüm yakında yayınlanacak!

**🎯 Tahmini İlk Sürüm:** 2025 Q2

**⭐ Projeyi takip edin ve ilk sürümden haberdar olun!**

</div>

---

## 📖 Zunvo Nedir?

Zunvo, sıfırdan yazılmış, modern ve tamamen açık kaynaklı bir forum yazılımıdır. Saf PHP ile geliştirilmiş olup, herhangi bir framework veya composer bağımlılığı olmadan çalışır.

### 🎯 Vizyon

Modern forum yazılımları ya çok karmaşık, ya çok pahalı ya da özgürlüklerini kısıtlıyor. Zunvo, bu üç sorunu da çözmek için tasarlandı:

- ✅ **Basit & Güçlü:** Karmaşık yapılandırma olmadan kullanıma hazır
- ✅ **Tamamen Ücretsiz:** Hiçbir ücret, hiçbir kısıtlama
- ✅ **Tam Kontrol:** Kodunuzun %100'üne sahip olun

---

## ✨ Özellikler

### 🎨 Modern Kullanıcı Deneyimi
- **Responsive Tasarım** - Mobil, tablet ve masaüstünde kusursuz çalışır
- **Dark Mode** - Göz dostu karanlık tema desteği
- **Upvote/Downvote Sistemi** - Reddit tarzı oy mekanizması
- **Gerçek Zamanlı Bildirimler** - AJAX tabanlı anlık güncellemeler
- **Zengin Metin Editörü** - Gelişmiş içerik oluşturma araçları

### 💪 Güçlü Forum Özellikleri
- **Sınırsız Kategori & Alt Kategori** - Esnek organizasyon yapısı
- **Etiket Sistemi** - Konuları etiketlerle organize edin
- **İleri Arama** - Güçlü arama ve filtreleme
- **Moderasyon Araçları** - Kapsamlı içerik yönetimi
- **Kullanıcı Rolleri** - Özelleştirilebilir yetki sistemi
- **Reputasyon Sistemi** - Kullanıcı kredibilite puanları

### 🔌 Genişletilebilirlik
- **Plugin Sistemi** - Hook tabanlı modüler mimari
- **Tema Motoru** - Kolay tema özelleştirme
- **RESTful API** - Harici entegrasyonlar için API
- **Webhook Desteği** - Dış servislere bildirim gönderme

### 🔒 Güvenlik & Performans
- **CSRF Koruması** - Cross-site request forgery önleme
- **XSS Filtreleme** - Cross-site scripting koruması
- **SQL Injection Koruması** - PDO prepared statements
- **Rate Limiting** - Spam ve kötüye kullanım önleme
- **Cache Sistemi** - Yüksek performans için önbellekleme
- **Lazy Loading** - Hızlı sayfa yüklemeleri

### 🌍 Uluslararası
- **Çoklu Dil Desteği** - Kolayca yerelleştirilebilir
- **RTL Desteği** - Sağdan sola dil desteği
- **Zaman Dilimi Yönetimi** - Otomatik saat dönüşümü

---

## 🗺️ Yol Haritası

### ✅ Faz 1: Temel Altyapı (Tamamlandı)
- [x] Proje mimarisi tasarımı
- [x] MVC benzeri yapı oluşturma
- [x] Veritabanı şeması tasarımı
- [x] Core sistem bileşenleri
- [x] Routing sistemi

### 🔄 Faz 2: Kullanıcı Sistemi (Geliştiriliyor)
- [ ] Kayıt ve giriş sistemi
- [ ] Email doğrulama
- [ ] Şifre sıfırlama
- [ ] Kullanıcı profilleri
- [ ] Avatar yükleme

### 📋 Faz 3: Forum Özellikleri (Planlanıyor)
- [ ] Kategori yönetimi
- [ ] Konu oluşturma ve görüntüleme
- [ ] Yorum sistemi
- [ ] Upvote/Downvote mekanizması
- [ ] Arama fonksiyonu

### 🚀 Faz 4: İleri Özellikler (Planlanıyor)
- [ ] Bildirim sistemi
- [ ] Etiket sistemi
- [ ] Moderasyon araçları
- [ ] Kullanıcı rozetleri
- [ ] İstatistikler ve raporlar

### 🎨 Faz 5: Genişletilebilirlik (Planlanıyor)
- [ ] Plugin API
- [ ] Tema sistemi
- [ ] Widget sistemi
- [ ] RESTful API
- [ ] Webhook entegrasyonu

### 🔧 Faz 6: Optimizasyon & Yayın (Planlanıyor)
- [ ] Performans optimizasyonu
- [ ] SEO iyileştirmeleri
- [ ] Güvenlik denetimi
- [ ] Dokümantasyon
- [ ] **v1.0.0 Yayını** 🎉

---

## 🚀 Hızlı Başlangıç

> **Not:** Zunvo henüz geliştirme aşamasındadır. Aşağıdaki talimatlar ilk sürüm yayınlandığında geçerli olacaktır.

### Sistem Gereksinimleri

- PHP 8.0 veya üzeri
- MySQL 5.7+ veya MariaDB 10.2+
- Apache/Nginx web sunucusu
- 512 MB RAM (minimum)
- 100 MB disk alanı

### Kurulum

```bash
# 1. Repository'yi klonlayın
git clone https://github.com/yourusername/zunvo.git

# 2. Zunvo dizinine gidin
cd zunvo

# 3. Konfigürasyon dosyasını oluşturun
cp config/config.sample.php config/config.php

# 4. Veritabanı bilgilerinizi düzenleyin
nano config/config.php

# 5. Kurulum sihirbazını çalıştırın
# Tarayıcınızda: http://yourdomain.com/install.php
```

### Docker ile Kurulum

```bash
# Docker Compose ile hızlı kurulum
docker-compose up -d
```

---

## 📚 Dokümantasyon

Detaylı dokümantasyon yakında yayınlanacak:

- **Kurulum Rehberi** - Adım adım kurulum talimatları
- **Kullanıcı Kılavuzu** - Forum yönetimi ve kullanımı
- **Plugin Geliştirme** - Kendi eklentilerinizi oluşturun
- **Tema Geliştirme** - Özel temalar tasarlayın
- **API Referansı** - RESTful API dokümantasyonu

---

## 🤝 Katkıda Bulunun

Zunvo, topluluk katkılarıyla büyüyen açık kaynaklı bir projedir. Her türlü katkıya açığız!

### Nasıl Katkıda Bulunabilirsiniz?

- 🐛 **Bug Raporlayın** - Hata bildirimleri için issue açın
- 💡 **Öneride Bulunun** - Yeni özellik fikirleri paylaşın
- 📝 **Dokümantasyon** - Dokümantasyonu geliştirin
- 🌍 **Çeviri** - Yeni diller ekleyin
- 💻 **Kod Katkısı** - Pull request gönderin

### Geliştirme Ortamı Kurulumu

```bash
# Repository'yi fork edin ve klonlayın
git clone https://github.com/yourusername/zunvo.git
cd zunvo

# Yeni bir branch oluşturun
git checkout -b feature/amazing-feature

# Değişikliklerinizi yapın ve commit edin
git commit -m "feat: amazing new feature"

# Branch'inizi pushlayın
git push origin feature/amazing-feature

# Pull Request oluşturun
```

### Kod Standartları

- PSR-2 kod stili kullanın
- Anlamlı commit mesajları yazın
- Her özellik için test yazın
- Dokümantasyonu güncel tutun

---

## 🎨 Plugin & Tema Geliştirme

Zunvo'nun en güçlü özelliklerinden biri genişletilebilir mimarisidir.

### Plugin Geliştirme

```php
// plugins/my-plugin/MyPlugin.php
class MyPlugin {
    public function __construct() {
        // Hook'lara bağlanın
        Hook::register('before_post_create', [$this, 'beforePostCreate']);
    }
    
    public function beforePostCreate($data) {
        // Özel işlemlerinizi yapın
        return $data;
    }
}
```

### Tema Geliştirme

```
themes/my-theme/
├── theme.json          # Tema bilgileri
├── style.css          # Ana CSS
├── views/             # View dosyaları
│   ├── home.php
│   └── topic.php
└── assets/            # Görsel kaynaklar
```

**Eklenti ve tema geliştiricileri için:**
- ✅ Ücretli eklenti/tema satabilirsiniz
- ✅ Kendi lisansınızı kullanabilirsiniz
- ✅ Ticari projelerde kullanabilirsiniz

---

## 💬 Topluluk & Destek

- 💬 **Tartışmalar:** [GitHub Discussions](https://github.com/yourusername/zunvo/discussions)
- 🐛 **Hata Bildirimi:** [GitHub Issues](https://github.com/yourusername/zunvo/issues)
- 📧 **E-posta:** info@zunvo.org
- 🌐 **Website:** https://zunvo.org (yakında)

---

## 📊 Proje İstatistikleri

```
Satır Sayısı:    ~15,000 (hedef)
Dosya Sayısı:    ~150+
Diller:          PHP, JavaScript, CSS
Geliştirme Süresi: 6+ ay (devam ediyor)
Katkıda Bulunanlar: Bekliyor...
```

---

## 🙏 Teşekkürler

Zunvo, aşağıdaki harika açık kaynak projelerden ilham almıştır:

- **phpBB** - Açık kaynak forum öncüleri
- **MyBB** - Esnek ve özelleştirilebilir yapı
- **Flarum** - Modern kullanıcı deneyimi
- **Discourse** - İleri seviye özellikler

---

## 📜 Lisans

Zunvo, **GNU General Public License v3.0** altında lisanslanmıştır.

### Bu Ne Anlama Gelir?

✅ **İzin Verilenler:**
- Ticari kullanım
- Değiştirme
- Dağıtma
- Patent kullanımı
- Özel kullanım

❌ **Koşullar:**
- Değiştirilmiş versiyonlar da GPL v3 ile lisanslanmalıdır
- Kaynak kodu açık olmalıdır
- Değişiklikler belgelenmelidir
- Aynı lisansı kullanmalısınız

🔒 **Sınırlamalar:**
- Sorumluluk yok
- Garanti yok

**Önemli:** Zunvo'nun kendisi GPL v3 ile lisanslanmıştır, ancak sizin geliştirdiğiniz **eklentiler ve temalar** kendi lisansınızı kullanabilir ve ücretli olarak satılabilir. Bu, WordPress, Joomla ve benzeri sistemlerin kullandığı model ile aynıdır.

Lisansın tam metnini [LICENSE](LICENSE) dosyasında bulabilirsiniz.

---

## 🌟 Stargazers

Projeyi desteklemek için ⭐ vermeyi unutmayın!

[![Stargazers over time](https://starchart.cc/bnsware/Zunvo.svg?variant=adaptive)](https://starchart.cc/bnsware/Zunvo)

---

<div align="center">

**Zunvo ile topluluklar için güçlü platformlar oluşturun** 🚀

Made with ❤️ by the bnsware

[Website](https://zunvo.org) • [Twitter](https://twitter.com/zunvo) • [Discord](https://discord.gg/zunvo)

© 2025 Zunvo. GPL v3 License.

</div>
