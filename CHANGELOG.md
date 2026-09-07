# Değişiklik Günlüğü

## 1.0.2 Stable - Arayüz İyileştirmesi

- Konu güncellik alanı geniş mavi başlıklı panel görünümünden çıkarılarak 760px maksimum genişlikte kompakt karta dönüştürüldü.
- Başlık, durum rozeti ve konu sahibi doğrulama aksiyonu tek satırda/toplu alanda gösteriliyor.
- Oy istatistikleri yatay ve daha az yer kaplayan özet biçimine geçirildi.
- Ayrıntılı sürüm ve hata nedenleri gerektiğinde açılan “Doğrulama ayrıntıları” alanına taşındı.
- Güncel çözüm yönlendirme alanı büyük form görünümünden çıkarıldı; konu ID + Kaydet işlemi tek satırlık kompakt kontrole dönüştürüldü.
- Ön doğrulama görünümü de aynı kompakt tasarıma uyarlandı.

## 1.0.1 Stable - Hata Düzeltme

- Konu sahibi “hâlâ geçerli” işlemindeki `ThreadFreshness::$app` tanımsız property hatası giderildi.
- ACP forum düzenleme kaydı tek seferlik input filtreleme + `FormAction::basicEntitySave()` akışına taşındı.
- ACP kategori/forum toplu kaydında gereksiz tüm tablo güncellemesi kaldırıldı; yalnızca değişen forumlar yazılıyor ve hata halinde transaction rollback ediliyor.
- Konu sahibi doğrulama aksiyonundaki geniş submit alanı normal boyutlu ve daha anlaşılır bir butona dönüştürüldü.
- Eklenti sürümü `1.0.1` / `1010170` olarak yükseltildi.
- Paket üretimi sürüm bilgisini `addon.json` üzerinden dinamik okuyacak hale getirildi.
- Bu hatalar için kalıcı regresyon testi eklendi.

## 1.0.0 Stable - Nihai

- Forum bazlı `Anlamlı güncelleme` ve `Her son mesaj` yaş hesaplama modları tamamlandı.
- Anlamlı güncelleme hesabı konu sahibinin mesajları, ilk mesaj düzenlemeleri ve seçilmiş çözüm gönderisiyle uyumlu hale getirildi.
- Yeni doğrulama döngüsünde eski oyların, eski moderatör override durumlarının ve eski form verilerinin taşınması engellendi.
- Durum motoru ham oy sayısı + zaman ağırlıklı yüzde kullanacak şekilde düzeltildi ve eşikler ACP üzerinden yönetilebilir hale getirildi.
- Kendi konusuna oy, oy değiştirme ve moderatör doğrulama izinleri tam permission kontrolüne bağlandı.
- Olumsuz oy nedenleri whitelist doğrulamasına alındı; alternatif güncel konu önerisi ve neden dağılımı eklendi.
- Konu sahibi için topluluk sonucunu ezmeyen “hâlâ geçerli” bildirimi eklendi.
- Moderatör override, forum filtreleri, doğrulanmamış konu araması ve stale state senaryolarındaki tutarsızlıklar giderildi.
- Güncel Çözümler araması, durum rozetleri ve forum filtreleri referans tarihleriyle tutarlı hale getirildi.
- ACP içerik sağlığı ekranı etkin forumlar, mevcut doğrulama döngüsü ve son olumsuz geri bildirimlerle sınırlandırıldı.
- Yeniden hesaplama cron'u benzersiz job anahtarıyla kuyruğa alınarak üst üste job oluşması engellendi.
- Orphan kullanıcı/oy temizliği, stale aggregate state işaretleme ve gerekli DB indeksleri eklendi.
- Genel konu listelerinde N+1 sorgu riski bulk preload kontrolüyle kaldırıldı.
- Güncel Çözümler arama girdisi 100 karakterle sınırlandırıldı.
- XenForo 2.3.7+ template method kısıtlamaları için `get/is/has/can` sözleşmesine geçildi.
- Stable kaynakta eski `_output` geliştirme verileri kaldırıldı; `_data` XML ve `hashes.json` tek release kaynağı haline getirildi.
- PHP 8.4 lint, güvenlik, regresyon, release-data, release-static, XML, hash ve ZIP bütünlük kontrolleri CI'a bağlandı.
- Manuel SQL gerektirmeyen kurulum/yükseltme/kaldırma şema akışı `Setup.php` üzerinden doğrulandı.

> Not: Nihai ürün kullanıcıya **1.0.0** olarak sunulur. Denetim sürecinde kullanılan dahili XenForo `version_id` değeri geriye çekilmemiştir; bu, daha önce test paketi kurmuş sistemlerde downgrade davranışını önlemek içindir.

## 1.0.0 Alpha 6

- Kritik durum değişikliği bildirimleri, web push, yeniden hesaplama job'u ve orphan cleanup tamamlandı.

## 1.0.0 Alpha 5

- Konu listesi rozetleri, bulk preload, durum filtresi ve moderatör doğrulama sistemi tamamlandı.

## 1.0.0 Alpha 4

- Forum bazlı etkinleştirme, bekleme süresi, sürüm listesi ve yeniden doğrulama döngüsü tamamlandı.

## 1.0.0 Alpha 3

- Konu doğrulama paneli, oy endpoint'i, permission kontrolleri, neden ve sürüm alanları tamamlandı.

## 1.0.0 Alpha 2

- XenForo 2.3 uyumluluk, oy ağırlığı ve PHP 8.4 otomatik testleri tamamlandı.

## 1.0.0 Alpha 1

- İlk eklenti iskeleti, veritabanı tabloları, entity/repository/service/job katmanları ve durum motoru oluşturuldu.
