# Theme system

**English** | [Türkçe](../docs-tr/themes.md)

Zunvo themes work like MyBB/XenForo: each theme is a folder under `themes/`, scanned and activated from the admin panel.

## Default theme

`themes/default/` — All public templates and main CSS. Other themes can extend it via `extends`.

Developer starter: `themes/_starter/` (`hidden: true` — copy and rename, do not activate as-is).

## Folder structure

```
themes/my-theme/
├── theme.json          # Required manifest
├── style.css           # Main CSS (required if inherit_styles: false)
├── partials.css        # Optional partial styles
├── bootstrap.php       # Optional hook entry
├── templates/          # Template overrides
│   └── layout/
│       ├── master.php
│       ├── header.php
│       └── footer.php
└── assets/             # Optional images/JS
```

## theme.json example

```json
{
    "name": "My Theme",
    "slug": "my-theme",
    "version": "1.0.0",
    "author": "Your name",
    "description": "Short description",
    "extends": "default",
    "inherit_styles": true,
    "body_class": "theme-my-theme"
}
```

| Field | Description |
|-------|-------------|
| `extends` | Parent theme slug (`default` recommended) |
| `inherit_styles` | `true`: load parent CSS; `false`: only own `style.css` |
| `hidden` | `true`: hidden from admin list (`_starter`) |
| `body_class` | `<body>` class name |

## Template overrides

Copy a file from the parent using the same path:

`themes/my-theme/templates/home.php` — overrides only the homepage.

Layout chain: `master.php` → header + content + footer.

Shared helpers: `theme_shell_head_open()`, `theme_shell_user_toolbar()`, `theme_partial()`.

## Admin: ZIP upload

1. Admin → Themes
2. Select ZIP file (max ~10 MB)
3. Optional: “Activate after upload”
4. **Upload**

ZIP layout:

```
my-theme.zip
└── my-theme/
    ├── theme.json
    ├── style.css
    └── templates/
```

Or `theme.json` at the ZIP root.

Rules:
- Folder name: lowercase letters, digits, hyphens (`a-z0-9-`)
- Cannot install `default` or slugs starting with `_`
- Cannot overwrite the active theme
- `theme.json` `slug` must match the folder name

## Template and style editor

From the active theme card in Admin → Themes:
- **Template editor** — edit `templates/` files
- **Style editor** — `custom.css` or theme variables

Prefer editing locally and deploying over live server edits in production.

## Shared CSS

`public/css/components.css` — Toolbar, notifications, profile layout. Loaded even when `inherit_styles: false`.

## Asset URLs

`theme_asset('img/logo.png')` and `asset('css/...')` append `?v=filemtime` automatically.
