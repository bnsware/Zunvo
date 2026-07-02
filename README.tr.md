# Zunvo

Saf PHP ile yazılmış, açık kaynak forum yazılımı. Framework ve Composer yok; paylaşımlı hostingde çalışır.

[English](README.md) | **Türkçe** · [English docs](docs/README.md) · [Türkçe dokümantasyon](docs-tr/README.md)

## Özellikler

- Konu, kategori, yanıt, etiket, arama
- Oy sistemi ve reputasyon
- Bildirimler ve kullanıcı profilleri
- Admin ve moderasyon paneli
- Plugin (hook) ve tema motoru (ZIP yükleme)
- REST API ve webhook

## Gereksinimler

PHP 8.0+, MySQL 5.7+, PDO, mbstring

## Kurulum

```text
1. Dosyaları sunucuya yükleyin
2. https://siteniz.com/install.php adresini açın
3. Sihirbazı tamamlayın
4. install.php dosyasını silin
```

Ayrıntılı rehber: **[docs-tr/installation.md](docs-tr/installation.md)**

## Dokümantasyon

| English (`docs/`) | Türkçe (`docs-tr/`) |
|-------------------|---------------------|
| [Hub](docs/README.md) | [Ana sayfa](docs-tr/README.md) |
| [Installation](docs/installation.md) | [Kurulum](docs-tr/installation.md) |
| [Architecture](docs/architecture.md) | [Mimari](docs-tr/architecture.md) |
| [Administration](docs/administration.md) | [Yönetim](docs-tr/administration.md) |
| [Themes](docs/themes.md) | [Temalar](docs-tr/themes.md) |
| [Plugins](docs/plugins.md) | [Pluginler](docs-tr/plugins.md) |
| [API](docs/api.md) | [API](docs-tr/api.md) |

## Proje yapısı (kısa)

```text
index.php          → Route ve giriş
core/              → Router, DB, tema, plugin
app/controllers/   → İş mantığı
themes/default/    → Frontend şablonları ve CSS
plugins/           → Eklentiler
public/            → Statik dosyalar ve uploads
```

## Lisans

GPL v3 — ayrıntılar için [LICENSE](LICENSE). Kendi temalarınız ve eklentileriniz farklı lisansla dağıtılabilir.
