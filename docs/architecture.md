# Architecture

**English** | [Türkçe](../docs-tr/architecture.md)

## Request flow

```
HTTP request → index.php → route match → controller → model → view → HTML
```

1. `index.php` loads configuration and boots plugin/theme engines
2. Routes defined with `add_route()` map to functions in `app/controllers/`
3. Controllers fetch data via models and output templates with `render()` or `load_view()`

## Directories

### `core/`

| File | Role |
|------|------|
| `router.php` | Routing, `render()`, `load_view()`, CSRF |
| `database.php` | PDO wrapper |
| `security.php` | Sessions, CSRF, XSS |
| `theme.php` | Theme scan, activation, ZIP install |
| `theme_shell.php` | Shared header/footer, toolbar |
| `plugin.php` | Plugin scan, hook execution |
| `functions.php` | `url()`, `asset()`, helpers |
| `migrate.php` | Schema migrations |

### `app/controllers/`

One file per area: `auth`, `topic`, `user`, `admin`, `mod`, `notification`, `vote`, `api`, `home`, `cron`.

### `app/models/`

Database queries: `user`, `topic`, `category`, `notification`, `theme`, etc.

### `app/views/`

**Admin** and **mod** UI only. Public pages use theme templates.

### `themes/default/`

| Part | Description |
|------|-------------|
| `theme.json` | Theme manifest |
| `style.css` | Main stylesheet |
| `partials.css` | Forum table, widget styles |
| `templates/` | PHP templates (layout, home, topic, user…) |
| `bootstrap.php` | Optional theme hooks |

### `public/`

- `css/components.css` — Theme-agnostic UI (toolbar, notifications, profile layout)
- `js/` — `main.js`, `ajax.js`, `editor.js`
- `uploads/` — Avatars and attachments

### `plugins/`

Each plugin: `plugin.json` + `bootstrap.php` (+ optional `views/`, `assets/`).

## Template resolution

An active theme may set `"extends": "default"` in `theme.json`. Templates are resolved:

1. Active theme `templates/`
2. Parent chain (e.g. `default`)

Use `theme_partial('partials/forum_tree_table')` for partials.

## Database (main tables)

`users`, `categories`, `topics`, `posts`, `votes`, `notifications`, `tags`, `topic_tags`, `reports`, `settings`, `plugins`, `themes`, `mod_logs`, `api_keys`, `webhooks`

Full schema: `database/schema.sql`

## Hooks

Plugins and themes call `register_hook('hook_name', callable)`. Examples: `after_post_create`, `home_sidebar`, `layout_banner`, `admin_menu`.

## Asset versioning

`asset('css/main.css')` and theme CSS URLs append `?v=filemtime` for cache busting.

## URL examples

| URL | Controller |
|-----|------------|
| `/konu/{slug}` | topic → show |
| `/kategori/{slug}` | topic → category |
| `/profil/{username}` | auth → profile |
| `/bildirimler` | notification → index |
| `/admin/temalar` | admin → themes |

Full route list: `index.php`
