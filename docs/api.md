# API reference

**English** | [Türkçe](../docs-tr/api.md)

The REST API lets external apps access topics, replies, users, and notifications.

## Authentication

Every request needs an API key:

- Header: `X-API-Key: YOUR_KEY`
- Or query: `?api_key=YOUR_KEY`

Create keys under **Admin → API**.

## Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/v1/topics` | Topic list |
| GET | `/api/v1/topics/{id}` | Topic detail and replies |
| GET | `/api/v1/posts` | Reply list |
| POST | `/api/v1/posts` | Add reply |
| POST | `/api/v1/vote` | Vote (`post_id`, `vote_type`) |
| GET | `/api/v1/users/{username}` | User profile |
| GET | `/api/v1/notifications` | Notifications |
| GET | `/api/v1/search?q=` | Search |
| POST | `/api/v1/preview-bbcode` | BBCode preview |

## Example

```bash
curl -H "X-API-Key: YOUR_KEY" https://yoursite.com/api/v1/topics
```

Responses are JSON.

## Webhooks

Define URLs under Admin → Webhooks. Example events:

- `post.created`
- `topic.created`
- `user.registered`

## Security

- Do not commit API keys to source control
- Use HTTPS
- Revoke unused keys
- Create keys only for integrations that need them
