# Administration

**English** | [Türkçe](../docs-tr/administration.md)

Access the admin panel at `/admin` and moderation at `/mod`. Admins manage the whole site; moderators only use permissions assigned to them.

## Admin menu

| Section | Purpose |
|---------|---------|
| **Dashboard** | User, topic, and pending report counts |
| **Categories** | Forum sections and sub-forums (tree) |
| **Topics** | List, pin, lock, delete topics |
| **Widget** | Homepage activity tabs |
| **Users** | Member list, roles, edit |
| **Moderators** | Assign mod permissions |
| **Reports** | User reports |
| **Approvals** | Pending title changes, etc. |
| **Awards** | User badges/awards |
| **Settings** | Site name, description, registration on/off |
| **Themes** | Theme list, ZIP upload, template/style editor |
| **Plugins** | Enable and configure extensions |
| **API** | REST API keys |
| **Webhooks** | Outbound event URLs |
| **Mod log** | Moderation history |

## Common tasks

### New forum section

Admin → Categories → Add parent or sub-forum. Order, icon, and color can be set.

### Change theme

Admin → Themes → **Activate** from the list. Users can also pick a theme in the footer selector (or revert to site default).

### Upload theme ZIP

Admin → Themes → choose ZIP → Upload. Package must include `theme.json`. Details: [themes.md](themes.md)

### Install a plugin

Copy the folder into `plugins/`, then Admin → Plugins → **Activate**.

### API key

Admin → API → Create key. Send `X-API-Key` header on requests. Details: [api.md](api.md)

## Moderation panel (`/mod`)

Permission-based: topic management, reports, approval queue, mod log.

## Security

- Use strong passwords and HTTPS for `/admin`
- Delete unused API keys
- Grant moderators the minimum permissions needed
