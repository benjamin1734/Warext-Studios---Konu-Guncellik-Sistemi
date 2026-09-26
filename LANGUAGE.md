# Language support / Dil desteği

This add-on uses XenForo's native Phrase/Language system. User-facing add-on strings are phrase-backed and follow the language selected by the XenForo visitor.

## English
- Import `languages/English.xml` into your existing English language, or create a child language based on your normal English XenForo language.
- Users can switch languages with XenForo's normal language selector. The add-on changes language with the rest of the forum.
- Do not translate user-generated thread content automatically; this package translates only interface text.

## Türkçe
- `languages/Turkish.xml` dosyasını mevcut Türkçe XenForo dilinizin üzerine içe aktarabilir veya normal Türkçe dilinizi parent alan bir child language oluşturabilirsiniz.
- Kullanıcı XenForo'nun normal dil seçicisinden Türkçe/İngilizce seçtiğinde eklenti arayüzü de aynı anda değişir.
- Kullanıcıların yazdığı konu/içerikler otomatik çevrilmez; paket yalnızca sistem arayüz metinlerini çevirir.

## Development rule
New buttons, headings, descriptions, validation messages, alerts, ACP labels and JavaScript-visible strings must use XenForo phrases. Hard-coded user-facing Turkish/English strings must not be introduced.
