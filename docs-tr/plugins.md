# Plugin geliştirme

[English](../docs/plugins.md) | **Türkçe**

Eklentiler `plugins/` klasöründe yaşar. Admin panelinden taranır ve etkinleştirilir.

## Klasör yapısı

```
plugins/my-plugin/
├── plugin.json       # Manifest (zorunlu)
├── bootstrap.php     # Giriş noktası (zorunlu)
├── views/            # İsteğe bağlı admin/frontend parçaları
└── assets/           # İsteğe bağlı CSS/JS
```

## plugin.json

```json
{
    "name": "My Plugin",
    "slug": "my-plugin",
    "version": "1.0.0",
    "author": "Adınız",
    "description": "Ne yaptığını kısaca yazın"
}
```

## bootstrap.php

```php
<?php
register_hook('after_post_create', function ($data) {
    return $data;
});
```

## Hook noktaları

| Hook | Zaman |
|------|--------|
| `before_user_register` / `after_user_register` | Kayıt öncesi/sonrası |
| `before_topic_create` / `after_topic_create` | Konu oluşturma |
| `before_post_create` / `after_post_create` | Yanıt oluşturma |
| `before_render` | Sayfa render öncesi |
| `layout_banner` | Header altı banner alanı |
| `home_sidebar` | Ana sayfa yan sütun |
| `admin_menu` | Admin menü öğeleri |

Hook fonksiyonu veriyi alır; değiştirip `return` etmelidir (filtre davranışı).

## Etkinleştirme

1. Klasörü `plugins/` altına koyun
2. Admin → Pluginler
3. Listede görününce **Etkinleştir**

Örnek eklenti: `plugins/hello-world/`

## Ayar sayfası

Admin → Pluginler → ilgili eklenti → **Ayarlar** (eklenti admin menüsü veya route tanımlıyorsa).

## Cron

Bazı eklentiler `cron/plugins` route'u ile periyodik görev çalıştırır; sunucu cron'una bu URL eklenebilir.

## Lisans

Zunvo çekirdeği GPL v3'tür. Kendi eklentiniz için farklı lisans ve ücretli dağıtım mümkündür.
