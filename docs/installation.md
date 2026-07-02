# Installation

**English** | [Türkçe](../docs-tr/installation.md)

## Requirements

- PHP 8.0 or newer
- MySQL 5.7+ / MariaDB 10.2+
- PDO and mbstring extensions
- Apache (`mod_rewrite`) or Nginx URL rewriting
- Writable: `storage/`, `public/uploads/`

## Shared hosting (Hostinger, cPanel)

1. Upload all files to `public_html` or a subfolder
2. Create a MySQL database and user in your hosting panel
3. Open `https://yoursite.com/install.php` in your browser
4. Complete the wizard:
   - System check
   - Database credentials
   - Table creation
   - First admin account
5. When finished, **delete `install.php`**

`SITE_URL` and `BASE_PATH` are auto-detected in most environments.

## Manual installation

1. Copy `config/config.sample.php` → `config/config.php`
2. Copy `config/detect.sample.php` → `config/detect.php`
3. Edit database settings in `config/database.php`
4. Run `database/schema.sql` and `database/seed.sql` in phpMyAdmin
5. Ensure `storage/` and `public/uploads/avatars`, `public/uploads/attachments` are writable (typically `755`)
6. The wizard creates `storage/install.lock`; for manual setup, create it after schema import

## After installation

| URL | Description |
|-----|-------------|
| `/` | Home |
| `/giris` | Login (Turkish routes; UI is localizable) |
| `/admin` | Admin panel |
| `/mod` | Moderation panel |

Configure site name, registration, and categories under **Admin → Settings** and **Admin → Categories**.

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `detect.php not found` | Copy `config/detect.sample.php` → `config/detect.php` |
| 404 on all pages | Enable `.htaccess` and `mod_rewrite` |
| Database connection error | Verify `config/database.php` |
| Avatar upload fails | Check permissions on `public/uploads/avatars` |
| Blank page | Set `DEBUG_MODE` true in `config/config.php` temporarily; disable in production |
| Installer runs again | Ensure `storage/install.lock` exists |

## Security notes

- Turn off `DEBUG_MODE` in production
- Remove `install.php` after setup
- Keep `config/` out of public access (protected by `.htaccess`)
- Back up database and `public/uploads/` regularly
