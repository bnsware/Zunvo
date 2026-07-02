# Zunvo Dokümantasyon

[English](../docs/README.md) | **Türkçe**

Zunvo, saf PHP ile yazılmış açık kaynak forum yazılımıdır. Framework veya Composer bağımlılığı yoktur; paylaşımlı hosting (Hostinger vb.) üzerinde çalışacak şekilde tasarlanmıştır.

## Hızlı bağlantılar

| Doküman | Ne için? |
|---------|----------|
| [Kurulum](installation.md) | İlk kurulum, sunucu gereksinimleri, sorun giderme |
| [Mimari](architecture.md) | Klasör yapısı, istek akışı, temel bileşenler |
| [Yönetim paneli](administration.md) | Admin ve moderasyon araçları |
| [Tema sistemi](themes.md) | Tema yapısı, ZIP yükleme, şablon override |
| [Plugin geliştirme](plugins.md) | Eklenti yazma, hook noktaları |
| [API](api.md) | REST API ve webhook kullanımı |

## Zunvo nedir?

Topluluk forumu için konu/kategori/yorum, oy sistemi, bildirimler, moderasyon ve yönetim paneli sunar. MyBB/XenForo tarzı tema ve plugin genişletilebilirliği hedeflenir; çekirdek kod sade PHP + MySQL ile çalışır.

## Temel özellikler

- Kullanıcı kaydı, giriş, profil, avatar, e-posta doğrulama
- Kategori ve alt forum ağacı
- Konu, yanıt, çözüm işaretleme, sabitleme, kilitleme
- Upvote/downvote ve reputasyon
- Etiketler ve arama
- Bildirimler (mention, yanıt, oy vb.)
- Admin paneli ve moderatör araçları
- Plugin (hook) ve tema motoru
- REST API ve webhook

## Proje yapısı (özet)

```
Zunvo/
├── index.php          # Giriş noktası, route tanımları
├── install.php        # Kurulum sihirbazı
├── config/            # config.php, database.php, detect.php
├── core/              # Router, DB, güvenlik, tema, plugin
├── app/
│   ├── controllers/   # İstek işleyiciler
│   ├── models/        # Veritabanı katmanı
│   └── views/         # Admin ve mod şablonları
├── themes/
│   └── default/       # Varsayılan tema (şablonlar + CSS)
├── plugins/           # Eklentiler
├── public/            # CSS, JS, uploads (web kökü)
├── database/          # schema.sql, seed.sql
└── storage/           # cache, log, install.lock
```

Frontend şablonları `themes/default/templates/` altındadır. Admin arayüzü `app/views/admin/` içinde kalır.

## Gereksinimler

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.2+
- PDO, mbstring
- Apache `mod_rewrite` veya Nginx rewrite

## İlk adımlar

1. [Kurulum rehberini](installation.md) takip edin
2. Admin hesabıyla `/admin` paneline girin
3. Site ayarlarını ve kategorileri yapılandırın
4. İsteğe bağlı: [plugin](plugins.md) veya [tema](themes.md) ekleyin

## Lisans

Zunvo çekirdeği **GPL v3** altındadır. Geliştirdiğiniz temalar ve eklentiler kendi lisansınızla dağıtılabilir (WordPress modeli). Ayrıntılar: kök dizindeki `LICENSE` dosyası.
