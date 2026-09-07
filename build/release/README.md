# Warext Studios - Konu Güncellik Sistemi

XenForo 2.3 için topluluk tabanlı konu ve çözüm güncellik doğrulama eklentisi.

## Sürüm

**1.0.0 Stable - Nihai Sürüm**

## Doğrudan XenForo ACP kurulumu

**Hazır ZIP:** `XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip`

**Tek tık indirme:** https://github.com/benjamin1734/Warext-Studios---Konu-G-ncellik-Sistemi/raw/refs/heads/main/XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip

Bu ZIP'i çıkartmadan doğrudan XenForo ACP → Add-ons → Install/upgrade from archive ekranından seçip yükleyin.

Arşiv kurucusunun açık olması gerekir:

`$config['enableAddOnArchiveInstaller'] = true;`

## Ana özellikler

- Forum bazlı etkinleştirme ve bekleme süresi
- Forum başına `Anlamlı güncelleme` veya `Her son mesaj` yaş hesabı
- Anlamlı güncellemede konu sahibinin mesajları, ilk mesaj düzenlemeleri ve seçilmiş soru çözüm gönderisini esas alma
- Çalıştı / çalışmadı topluluk oylaması
- Tek kullanıcı / tek oy, oy değiştirme ve yeni doğrulama döngüsünde yeniden oy kullanma
- Hesap yaşı ve mesaj sayısı koşulları
- Kendi konusuna oy için hem ACP anahtarı hem kullanıcı grubu izni
- Sürüm bazlı doğrulama sonuçları
- Olumsuz oy nedenleri ve neden dağılımı
- Seçilebilir güncel alternatif konu önerisi
- Konu sahibinin bağlayıcı olmayan “hâlâ geçerli” bildirimi
- Güncel, muhtemelen güncel, kararsız, şüpheli, çalışmıyor, yeniden doğrulanıyor ve doğrulanmamış durumları
- Ham oy sayısı + zaman ağırlıklı yüzde kullanan durum motoru
- ACP üzerinden değiştirilebilir durum eşikleri
- Eski oyları zamanla düşük ağırlıkla değerlendirme
- Otomatik yeniden doğrulama
- Konu listesi rozetleri ve durum filtreleri
- Moderatör doğrulaması ve topluluk sonucuna geri dönüş
- Kritik durum değişikliği bildirimleri ve web push
- Güncel çözüm konusu yönlendirmesi
- Güncel Çözümler arama sayfası
- ACP içerik sağlığı, kritik konu ve son olumsuz geri bildirim özeti
- Eşzamanlı oy kilitleme
- Orphan kayıt temizliği
- PHP 8.4 otomatik testleri
- Kurulabilir `_data` XML ve `hashes.json` içeren release paketi

## Gereksinimler

- XenForo 2.3.0+
- PHP 8.1+

## Kurulum

`XenForo-ACP-Direct-Install-Warext-Konu-Guncellik-1.0.0.zip` paketini XenForo yönetim panelindeki arşivden eklenti kurma/yükseltme ekranından yükleyin.

Kurulumdan sonra:

1. Eklenti seçeneklerinden sistemi etkinleştirin.
2. Kullanıcı grubu izinlerinden oy, oy değiştirme, kendi konusuna oy ve moderatör doğrulama izinlerini yapılandırın.
3. İlgili forumun düzenleme ekranından güncellik doğrulamasını açın.
4. Forum için bekleme süresini, yaş hesabı yöntemini ve gerekiyorsa sürüm listesini ayarlayın.
5. Gerekiyorsa durum hesaplama eşiklerini ACP seçeneklerinden özelleştirin.

## Sürüm notu

Bu paket resmi **1.0.0 Stable** sürümüdür. Denetim sırasında kullanılan dahili XenForo `version_id` değeri geriye çekilmemiştir; bu sayede daha önce test paketini kurmuş sistemlerde sürüm düşürme riski oluşturulmaz.

## Veri tabanı

Manuel SQL içe aktarma gerekmez. Kurulum, yükseltme ve kaldırma şema işlemleri `Setup.php` üzerinden yürütülür.
