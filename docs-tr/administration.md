# Yönetim paneli

[English](../docs/administration.md) | **Türkçe**

Admin paneline `/admin` adresinden, moderasyon paneline `/mod` adresinden erişilir. Admin rolü tüm site ayarlarına; moderatör rolü atanan yetkilere göre sınırlı araçlara erişir.

## Admin menüsü

| Bölüm | Ne işe yarar? |
|-------|----------------|
| **Özet** | Kullanıcı, konu, bekleyen rapor sayıları |
| **Kategoriler** | Forum bölümleri ve alt forumlar (ağaç yapı) |
| **Konular** | Konu listesi, sabitleme, kilitleme, silme |
| **Widget** | Ana sayfa aktivite sekmeleri |
| **Kullanıcılar** | Üye listesi, rol, düzenleme |
| **Moderatörler** | Mod yetkisi atama |
| **Raporlar** | Kullanıcı şikayetleri |
| **Onaylar** | Başlık değişikliği vb. bekleyen istekler |
| **Ödüller** | Kullanıcı rozet/ödül tanımları |
| **Ayarlar** | Site adı, açıklama, kayıt açık/kapalı |
| **Tema** | Tema listesi, ZIP yükleme, şablon/stil editörü |
| **Pluginler** | Eklenti etkinleştirme ve ayarları |
| **API** | REST API anahtarları |
| **Webhook** | Dış servis bildirim URL'leri |
| **Mod log** | Moderasyon işlem geçmişi |

## Sık kullanılan işlemler

### Yeni forum bölümü

Admin → Kategoriler → Üst kategori veya alt forum ekle. Sıra, ikon ve renk ayarlanabilir.

### Tema değiştirme

Admin → Tema → Listeden **Etkinleştir**. Kullanıcılar footer'daki tema seçiciden kendi tercihini de seçebilir (site varsayılanına dönebilir).

### Tema ZIP ile yükleme

Admin → Tema → ZIP seç → Yükle. Paket içinde `theme.json` zorunlu. Ayrıntı: [themes.md](themes.md)

### Plugin kurma

`plugins/` klasörüne kopyalayın. Admin → Pluginler → Etkinleştir.

### API anahtarı

Admin → API → Yeni anahtar. İsteklerde `X-API-Key` header kullanın. Ayrıntı: [api.md](api.md)

## Moderasyon paneli (`/mod`)

Yetkiye bağlı: konu yönetimi, raporlar, onay kuyruğu, mod log görüntüleme.

## Güvenlik

- Admin URL'lerini mümkünse güçlü şifre + HTTPS ile koruyun
- Kullanılmayan API anahtarlarını silin
- Mod yetkilerini minimum gerekli izinle verin
