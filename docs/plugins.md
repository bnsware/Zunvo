# Plugin development

**English** | [Türkçe](../docs-tr/plugins.md)

Plugins live under `plugins/` and are enabled from the admin panel.

## Folder structure

```
plugins/my-plugin/
├── plugin.json       # Manifest (required)
├── bootstrap.php     # Entry point (required)
├── views/            # Optional admin/frontend partials
└── assets/           # Optional CSS/JS
```

## plugin.json

```json
{
    "name": "My Plugin",
    "slug": "my-plugin",
    "version": "1.0.0",
    "author": "Your name",
    "description": "What it does"
}
```

## bootstrap.php

```php
<?php
register_hook('after_post_create', function ($data) {
    return $data;
});
```

## Hook points

| Hook | When |
|------|------|
| `before_user_register` / `after_user_register` | Registration |
| `before_topic_create` / `after_topic_create` | Topic creation |
| `before_post_create` / `after_post_create` | Reply creation |
| `before_render` | Before page render |
| `layout_banner` | Below header banner area |
| `home_sidebar` | Homepage sidebar |
| `admin_menu` | Admin menu items |

Hook callbacks receive data and should return it (filter behavior).

## Activation

1. Place the folder under `plugins/`
2. Admin → Plugins
3. **Activate** when it appears in the list

Example: `plugins/hello-world/`

## Settings page

Admin → Plugins → plugin → **Settings** (if the plugin registers admin routes or menu items).

## Cron

Some plugins use the `cron/plugins` route for scheduled tasks; add it to server cron if needed.

## License

Zunvo core is GPL v3. Your plugins may use a different license and be sold commercially.
