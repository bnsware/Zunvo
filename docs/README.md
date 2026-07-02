# Zunvo Documentation

**English** | [Türkçe](../docs-tr/README.md)

Zunvo is open-source forum software written in plain PHP. No framework or Composer required — designed for shared hosting (Hostinger, cPanel, etc.).

## Quick links

| Guide | Purpose |
|-------|---------|
| [Installation](installation.md) | First-time setup, requirements, troubleshooting |
| [Architecture](architecture.md) | Folder layout, request flow, core components |
| [Administration](administration.md) | Admin and moderation panels |
| [Themes](themes.md) | Theme structure, ZIP upload, template overrides |
| [Plugins](plugins.md) | Extension development, hooks |
| [API](api.md) | REST API and webhooks |

## What is Zunvo?

A community forum with topics, categories, replies, voting, notifications, moderation, and an admin panel. Theme and plugin extensibility similar to MyBB/XenForo; core runs on plain PHP + MySQL.

## Key features

- User registration, login, profiles, avatars, email verification
- Category and sub-forum tree
- Topics, replies, best answer, pin, lock
- Upvote/downvote and reputation
- Tags and search
- Notifications (mentions, replies, votes, etc.)
- Admin panel and moderator tools
- Plugin hooks and theme engine
- REST API and webhooks

## Project layout (summary)

```
Zunvo/
├── index.php          # Entry point, routes
├── install.php        # Installation wizard
├── config/            # config.php, database.php, detect.php
├── core/              # Router, DB, security, theme, plugin
├── app/
│   ├── controllers/   # Request handlers
│   ├── models/        # Database layer
│   └── views/         # Admin and mod UI only
├── themes/
│   └── default/       # Default theme (templates + CSS)
├── plugins/           # Extensions
├── public/            # CSS, JS, uploads (web root)
├── database/          # schema.sql, seed.sql
└── storage/           # cache, logs, install.lock
```

Public templates live in `themes/default/templates/`. The admin UI stays in `app/views/admin/`.

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.2+
- PDO, mbstring
- Apache `mod_rewrite` or Nginx rewrite

## Getting started

1. Follow the [installation guide](installation.md)
2. Log in to `/admin` with your admin account
3. Configure site settings and categories
4. Optionally add [plugins](plugins.md) or [themes](themes.md)

## License

Zunvo core is **GPL v3**. Themes and plugins you build may use their own license (WordPress-style). See the `LICENSE` file in the project root.
