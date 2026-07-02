# Zunvo

Open-source forum software built with plain PHP. No framework, no Composer — runs on shared hosting.

**English** | [Türkçe](README.tr.md) · [Documentation](docs/README.md) · [Türkçe dokümantasyon](docs-tr/README.md)

## Features

- Topics, categories, replies, tags, search
- Voting and reputation
- Notifications and user profiles
- Admin and moderation panels
- Plugin hooks and theme engine (ZIP upload)
- REST API and webhooks

## Requirements

PHP 8.0+, MySQL 5.7+, PDO, mbstring

## Quick install

```text
1. Upload files to your server
2. Open https://yoursite.com/install.php
3. Complete the wizard
4. Delete install.php
```

Full guide: **[docs/installation.md](docs/installation.md)**

## Documentation

| English (`docs/`) | Türkçe (`docs-tr/`) |
|-------------------|---------------------|
| [Hub](docs/README.md) | [Ana sayfa](docs-tr/README.md) |
| [Installation](docs/installation.md) | [Kurulum](docs-tr/installation.md) |
| [Architecture](docs/architecture.md) | [Mimari](docs-tr/architecture.md) |
| [Administration](docs/administration.md) | [Yönetim](docs-tr/administration.md) |
| [Themes](docs/themes.md) | [Temalar](docs-tr/themes.md) |
| [Plugins](docs/plugins.md) | [Pluginler](docs-tr/plugins.md) |
| [API](docs/api.md) | [API](docs-tr/api.md) |

## Layout (short)

```text
index.php          → Entry point and routes
core/              → Router, DB, theme, plugin
app/controllers/   → Business logic
themes/default/    → Frontend templates and CSS
plugins/           → Extensions
public/            → Static assets and uploads
```

## License

GPL v3 — see [LICENSE](LICENSE). Your themes and plugins may use a different license.
