# Kurulum ve Yükseltme

## Gereksinimler

- XenForo 2.3.0 veya üzeri
- PHP 8.1 veya üzeri
- XenForo arşiv kurucusunun etkin olması

## Doğrudan ACP kurulumu

1. GitHub ana dizinindeki `XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip` dosyasını indirin.
2. ZIP dosyasını çıkartmayın.
3. XenForo ACP → Add-ons → Install/upgrade from archive ekranını açın.
4. İndirdiğiniz ZIP dosyasını seçip yükleyin.
5. Kurulum işlemini tamamlayın.
6. Eklenti seçeneklerinden sistemi etkinleştirin ve durum eşiklerini ihtiyacınıza göre ayarlayın.
7. Kullanıcı grubu izinlerinden oy verme, oy değiştirme, kendi konusuna oy ve moderatör doğrulama yetkilerini düzenleyin.
8. Doğrulama kullanacak forumların düzenleme ekranında sistemi açın, bekleme gününü ve yaş hesabı yöntemini seçin.

Arşivden kurulum ekranı görünmüyorsa `src/config.php` içinde şu ayar etkin olmalıdır:

`$config['enableAddOnArchiveInstaller'] = true;`

## ZIP yapısı

Arşivin kökünde `upload/` klasörü bulunur. Eklenti dosyaları `upload/src/addons/WarextStudios/ThreadFreshness/` altında yer alır. `addon.json`, `_data` ve `hashes.json` kurulum paketinin içindedir.

## Yükseltme

- Aynı `XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip` paketini ACP üzerinden yükleyebilirsiniz.
- Manuel SQL çalıştırmayın. Gerekli kolon ve indeks işlemleri `Setup.php` üzerinden yürütülür.
- Eski geliştirme/alpha yapılarından gelen kurulumlarda gerekli migration adımları otomatik çalışır.
- Yeniden hesaplama gerektiren durumlarda eklenti ilgili job'u otomatik olarak kuyruğa alır.
- Forum ayarlarında önerilen yaş hesabı `Anlamlı güncelleme` modudur; gerekirse forum bazında `Her son mesaj` seçilebilir.

## Kaldırma

Standart XenForo eklenti kaldırma akışını kullanın. Eklentiye ait tablolar ve forum kolonları `Setup.php` tarafından temizlenir.
