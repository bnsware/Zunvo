# Tema sistemi

[English](../docs/themes.md) | **Türkçe**

Zunvo temaları MyBB/XenForo mantığında çalışır: her tema `themes/` altında bir klasördür; admin panelinden taranır, doğrulanır ve etkinleştirilir.

## Varsayılan tema

`themes/default/` — Tüm public şablonlar ve ana CSS burada. Diğer temalar bunu `extends` ile genişletebilir.

Geliştirici şablonu: `themes/_starter/` (`hidden: true`, etkinleştirmeyin; kopyalayıp başlayın).

## Klasör yapısı

```
themes/benim-tema/
├── theme.json          # Zorunlu manifest
├── style.css           # Ana CSS (inherit_styles: false ise zorunlu)
├── partials.css        # İsteğe bağlı parça stilleri
├── bootstrap.php       # İsteğe bağlı hook girişi
├── templates/          # Şablon override'ları
│   └── layout/
│       ├── master.php
│       ├── header.php
│       └── footer.php
└── assets/             # İsteğe bağlı görsel/JS
```

## theme.json örneği

```json
{
    "name": "Benim Tema",
    "slug": "benim-tema",
    "version": "1.0.0",
    "author": "Adınız",
    "description": "Kısa açıklama",
    "extends": "default",
    "inherit_styles": true,
    "body_class": "theme-benim-tema"
}
```

| Alan | Açıklama |
|------|----------|
| `extends` | Parent tema slug (`default` önerilir) |
| `inherit_styles` | `true`: parent CSS yüklenir; `false`: sadece kendi `style.css` |
| `hidden` | `true` ise admin listesinde görünmez (`_starter` gibi) |
| `body_class` | `<body>` sınıfı |

## Şablon override

Parent'ta olan bir dosyayı aynı yol ile kopyalayın:

`themes/benim-tema/templates/home.php` → sadece ana sayfayı değiştirir.

Layout zinciri: `master.php` → `header` + içerik + `footer`.

Ortak parçalar: `theme_shell_head_open()`, `theme_shell_user_toolbar()`, `theme_partial()`.

## Admin: ZIP ile yükleme

1. Admin → Tema
2. ZIP dosyası seç (max ~10 MB)
3. İsteğe bağlı: «Yükleme sonrası etkinleştir»
4. **Yükle**

ZIP yapısı:

```
benim-tema.zip
└── benim-tema/
    ├── theme.json
    ├── style.css
    └── templates/
```

veya kökte doğrudan `theme.json`.

Kurallar:
- Klasör adı: küçük harf, rakam, tire (`a-z0-9-`)
- `default` ve `_` ile başlayan slug yüklenemez
- Aktif tema üzerine yazılamaz
- `theme.json` içindeki `slug` klasör adıyla eşleşmeli

## Şablon ve stil editörü

Admin → Tema → aktif tema kartından:
- **Şablon editörü** — `templates/` dosyalarını düzenler
- **Stil editörü** — `custom.css` veya tema değişkenleri

Üretimde doğrudan sunucuda düzenleme yerine dosyaları indirip geliştirme ortamında çalışmak önerilir.

## Ortak CSS

`public/css/components.css` — Toolbar, bildirim, profil düzeni gibi tema bağımsız bileşenler. Child tema `inherit_styles: false` olsa bile bu dosya yüklenir.

## Asset URL

`theme_asset('img/logo.png')` ve `asset('css/...')` otomatik `?v=filemtime` ekler.
