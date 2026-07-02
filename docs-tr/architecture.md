# Mimari

[English](../docs/architecture.md) | **Türkçe**

## İstek akışı

```
HTTP isteği → index.php → route eşleştirme → controller → model → view → HTML
```

1. `index.php` yapılandırmayı yükler, plugin ve tema motorunu başlatır
2. `add_route()` ile tanımlı URL'ler `app/controllers/` içindeki fonksiyonlara yönlenir
3. Controller veriyi model üzerinden alır, `render()` veya `load_view()` ile şablonu basar

## Klasörler

### `core/`

| Dosya | Görev |
|-------|--------|
| `router.php` | Route, `render()`, `load_view()`, CSRF |
| `database.php` | PDO sarmalayıcı |
| `security.php` | Oturum, CSRF, XSS |
| `theme.php` | Tema tarama, etkinleştirme, ZIP yükleme |
| `theme_shell.php` | Ortak header/footer, toolbar |
| `plugin.php` | Plugin tarama, hook çalıştırma |
| `functions.php` | `url()`, `asset()`, yardımcılar |
| `migrate.php` | Şema güncellemeleri |

### `app/controllers/`

Her dosya bir alan: `auth`, `topic`, `user`, `admin`, `mod`, `notification`, `vote`, `api`, `home`, `cron`.

### `app/models/`

Veritabanı sorguları: `user`, `topic`, `category`, `notification`, `theme`, vb.

### `app/views/`

Yalnızca **admin** ve **mod** arayüzü. Public sayfalar tema şablonlarındadır.

### `themes/default/`

| Parça | Açıklama |
|-------|----------|
| `theme.json` | Tema manifest |
| `style.css` | Ana stil |
| `partials.css` | Forum tablosu, widget stilleri |
| `templates/` | PHP şablonları (layout, home, topic, user…) |
| `bootstrap.php` | İsteğe bağlı tema hook'ları |

### `public/`

- `css/components.css` — Tema bağımsız UI (toolbar, bildirim, profil düzeni)
- `js/` — `main.js`, `ajax.js`, `editor.js`
- `uploads/` — Avatar ve ekler

### `plugins/`

Her eklenti: `plugin.json` + `bootstrap.php` (+ isteğe bağlı `views/`, `assets/`).

## Şablon çözümleme

Aktif tema `theme.json` içinde `"extends": "default"` ile parent alabilir. Şablon aranırken:

1. Aktif tema `templates/`
2. Parent zinciri (ör. `default`)

`theme_partial('partials/forum_tree_table')` partial render için kullanılır.

## Veritabanı (ana tablolar)

`users`, `categories`, `topics`, `posts`, `votes`, `notifications`, `tags`, `topic_tags`, `reports`, `settings`, `plugins`, `themes`, `mod_logs`, `api_keys`, `webhooks`

Tam şema: `database/schema.sql`

## Hook sistemi

Plugin ve temalar `register_hook('hook_adi', callable)` kullanır. Örnek noktalar: `after_post_create`, `home_sidebar`, `layout_banner`, `admin_menu`.

## Asset sürümleme

`asset('css/main.css')` ve tema CSS URL'leri dosya değişim zamanına göre `?v=timestamp` alır; tarayıcı önbelleği kırılır.

## URL örnekleri

| URL | Controller |
|-----|------------|
| `/konu/{slug}` | topic → show |
| `/kategori/{slug}` | topic → category |
| `/profil/{username}` | auth → profile |
| `/bildirimler` | notification → index |
| `/admin/temalar` | admin → themes |

Tüm route listesi: `index.php`
