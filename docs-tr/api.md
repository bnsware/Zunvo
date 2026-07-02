# API referansı

[English](../docs/api.md) | **Türkçe**

REST API, harici uygulamaların konu, yanıt, kullanıcı ve bildirim verilerine erişmesi içindir.

## Kimlik doğrulama

Tüm isteklerde API anahtarı gerekir:

- Header: `X-API-Key: YOUR_KEY`
- veya sorgu parametresi: `?api_key=YOUR_KEY`

Anahtarlar: **Admin → API** bölümünden oluşturulur.

## Endpoint'ler

| Method | URL | Açıklama |
|--------|-----|----------|
| GET | `/api/v1/topics` | Konu listesi |
| GET | `/api/v1/topics/{id}` | Konu detay ve yanıtlar |
| GET | `/api/v1/posts` | Yanıt listesi |
| POST | `/api/v1/posts` | Yanıt ekle |
| POST | `/api/v1/vote` | Oy ver (`post_id`, `vote_type`) |
| GET | `/api/v1/users/{username}` | Kullanıcı profili |
| GET | `/api/v1/notifications` | Bildirimler |
| GET | `/api/v1/search?q=` | Arama |
| POST | `/api/v1/preview-bbcode` | BBCode önizleme |

## Örnek

```bash
curl -H "X-API-Key: YOUR_KEY" https://siteniz.com/api/v1/topics
```

Yanıtlar JSON formatındadır.

## Webhook'lar

Admin → Webhook bölümünden URL tanımlayın. Olay örnekleri:

- `post.created`
- `topic.created`
- `user.registered`

## Güvenlik

- Anahtarları kaynak koduna gömmeyin
- HTTPS kullanın
- Kullanılmayan anahtarları silin
- Minimum yetki prensibi: sadece gerekli entegrasyonlar için anahtar açın
