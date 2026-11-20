# Tasarım kuralları

Panel bir iş aracıdır (Operate). Hedef: hızlı taranan tablo, net durum rozetleri, sakin arayüz. Marka ayrıntıda yaşar; süs yok.

## Renk tokenleri

Tanım: `app/Providers/Filament/AdminPanelProvider.php` (`colors`). Hata sayfaları aynı değerleri `resources/css/app.css` içinde kullanır.

| Token | Değer | Kullanım |
|---|---|---|
| `primary` | Petrol mavisi, `oklch(0.495 0.085 218)` (600) | Birincil düğme, etkin menü, odak halkası, bağlantı, grafik çubuğu |
| `gray` | Slate | Yüzey, kenarlık, ikincil metin |
| `danger` | Red | Çok tehlikeli, ödenmedi, silme |
| `warning` | Amber | Tehlikeli, kısmi ödendi, sözleşme onaysız |
| `success` | Emerald | Az tehlikeli, ödendi |
| `info` | Sky | Düzenleyici rolü rozeti |

- Birincil renk yalnız eylem, seçili durum ve veri işaretinde kullanılır.
- Durum renkleri ayrılmıştır; her rozet metin de taşır, renk tek başına anlam taşımaz.
- Kontrast: primary-600 üzerinde beyaz metin 5.9:1, primary-500 üzerinde 4.7:1 (koyu temada düğme), koyu yüzeyde primary-400 metin 7.1:1.
- Mor-mavi gradyan, cam efekti ve dekoratif gölge kullanılmaz.

## Tipografi

- Yazı tipi: IBM Plex Sans Variable, yerelden sunulur (`@fontsource-variable/ibm-plex-sans`). Üçüncü taraf font isteği yok.
- Tek aile: başlık, etiket, veri aynı ailede; ağırlık basamakları 400 / 500 / 600.
- Sayısal veri `tabular-nums` ile hizalanır (tutar, sayaç, sayfalama, özet satırı).
- Başlıklarda hafif negatif tracking (`-0.018em`), `text-wrap: balance`.

## Bileşenler

- Tablolar Filament tablo dilini kullanır. Tutar sağa hizalı ve `₺` biçimli. Mobilde unvan, il ve ödeme kalır; diğer sütunlar `visibleFrom('md'/'lg')` ile gizlenir.
- Özel bileşenler `resources/css/filament/admin/theme.css` içinde `mtp-` önekiyle tanımlıdır: adım göstergesi, sayı özeti, önizleme tablosu, denetim zaman çizgisi.
- Numaralı adım yalnız gerçek sırada kullanılır (içe aktarım: dosya, önizleme, sonuç).

## Durumlar

- Boş: her tablo ve grafik ne yapılacağını söyler ("Henüz müşteri yok" + "Müşteri ekle" / "Excel'den içe aktar"; filtre boşsa "Filtreyi temizle").
- Yükleniyor: Filament iskeleti; gösterge kartları ilk yüklemede beklemeden gelir.
- Hata: alan altında doğrulama iletisi; içe aktarımda satır numarası; 403/404/419/500 sayfaları Türkçe.

## Hareket

- Süre 150–200 ms, `cubic-bezier(0.16, 1, 0.3, 1)`. Yalnız durum değişikliğinde.
- `prefers-reduced-motion: reduce` iken geçişler kapanır.

## Erişilebilirlik

- Bütün etkileşim klavyeyle yapılır; odak halkası görünür.
- Grafiklerin ekran okuyucu için tablo karşılığı vardır (`getChartAssistiveContent`).
- Önizleme tablosu kaydırılabilir bölge olarak etiketlidir ve odak alır.
