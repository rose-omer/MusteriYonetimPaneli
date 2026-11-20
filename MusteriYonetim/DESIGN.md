# Tasarım kuralları

Panel bir iş aracıdır (Operate). Tasarım dili: "05 · Harita ve Liste" (`tasarimlar/musteri-paneli/project/D05.dc.html`).
Hedef: hızlı taranan liste, sakin mavi-gri yüzey, tek vurgu rengi. Marka ayrıntıda yaşar; süs yok.

## Renk tokenleri

Basamaklar: `app/Providers/Filament/AdminPanelProvider.php` (`colors`). Yüzey ve harita tokenleri: `resources/css/filament/admin/theme.css` (`--mtp-*`). Hata sayfaları aynı değerleri `resources/css/app.css` içinde kullanır.

| Token | Açık tema | Kullanım |
|---|---|---|
| `primary` 600 | `#17323E` mürekkep | Birincil düğme, bağlantı, seçili segment, metin |
| `gray` 50 / 200 / 600 | `#EEF3F4` / `#D5DEE2` / `#4C6470` | Zemin / kenarlık / ikincil metin |
| `vurgu` 600 | `#6E2A55` erik | Açık künye, ödenmemiş, onay bekleyen, toplu seçim çubuğu, odak çizgisi |
| `tehlike-az` | `#CFE3E8` + mürekkep kenar | Az tehlikeli |
| `tehlike-orta` | `#427485` | Tehlikeli |
| `tehlike-cok` | `#133543` | Çok tehlikeli |
| `--mtp-deniz` / `--mtp-kara` | `#D3E3EA` / `#FBFBF8` | Harita zemini |
| `danger` 600 | `#B3261E` sönük kırmızı | Yalnız silme ve hata |

- Tehlike sınıfı tek tonlu, koyulaşan bir sıradır (renk körlüğünde de sıralanır). Koyu temada sıra açıklıkla kurulur: en açık = en tehlikeli.
- Ödeme: ödenmedi = vurgu, kısmi = primary, ödendi = gray. Sözleşme onaysız = vurgu.
- Her rozet metin taşır; renk tek başına anlam taşımaz.
- Kontrast (WCAG AA): mürekkep üstünde beyaz 12:1; `#427485` üstünde beyaz 5.2:1; gray-600 `#4C6470` zemin `#EEF3F4` üstünde 5.6:1; koyu temada vurgu çizgisi `#C792B1` kart üstünde 6.1:1.
- Koyu tema dolu düğme: `primary` 300 dolgu + 950 metin (zemin üstünde 9.7:1). Mürekkep dolgu koyu zeminde kaybolur, kullanılmaz. Harita ekranındaki dolu düğme ve seçili segment aynı kural (`#A9C6D2` + `#0B1920`).
- Yıkıcı düğme: açıkta `danger` 600 + beyaz (6.5:1), koyuda 400 + 950 metin (zemin üstünde 6.4:1).
- Gradyan, cam efekti ve dekoratif gölge yok. Kart: tek ince kenarlık, 10 px köşe.

## Tipografi

- Metin: Instrument Sans Variable. Sayı, tutar, telefon, firma no: Spline Sans Mono Variable, `tabular-nums`.
- İkisi de yerelden sunulur (`@fontsource-variable/instrument-sans`, `@fontsource-variable/spline-sans-mono`). Üçüncü taraf font isteği yok.
- Ağırlık: 400 / 500 / 600. Sayfa başlığı 22–24 px, 600.
- Tablo ve ayrıntıda mono alanlar: tutar, telefon, firma no, çalışan sayısı, ziyaret tarihi (`fontFamily(FontFamily::Mono)`).

## Para biçimi

- Tek yardımcı: `App\Support\TurkishFormat::tl()`. Biçim `213.800 ₺`: nokta binlik, virgül ondalık, sonda bölünmez boşluk + ₺.
- Tek kayıt tutarı ve tablo sütunu (toplam satırı dahil) 2 ondalık: `37.833,21 ₺`.
- Özet ve toplamlar ondalıksız: gösterge kartı, harita özet şeridi, il dairesi, toplu seçim özeti.
- Filament `money()` ve `Number::currency()` kullanılmaz.

## Yerleşim

- Üst gezinme (`topNavigation`); mobilde menü çekmeceye iner.
- Harita ekranı (`/admin/harita`): solda özet şeridi + harita + künye, sağda 480 px liste. 1180 px altında tek sütun.
- Geniş ve yüksek ekranda sol sütun yapışkandır; liste kayarken harita ve künye görünür kalır.
- Harita çizimi 640 px altına inmez; dar ekranda kendi kutusunda yana kayar, sayfa taşmaz. Kutu dar olduğunda (673 px altı) haritanın üstünde "Haritayı yana kaydırın" ipucu görünür.
- Yapışkan üst çubuk payı `--mtp-ust-bosluk` (4.5 rem = çubuk + 8 px): yapışkan sol sütunun `top` değeri ve künyenin `scroll-margin-top` değeri. Künyeye kaydırılınca başlık çubuğun altında kalmaz.

## Bileşenler

- Rozet: hap biçimi (`border-radius: 999px`).
- Segment düğme grubu: tehlike sınıfı süzgeci; seçili parça mürekkep dolgulu, `aria-pressed`.
- Toplu işlem çubuğu: erik zemin, beyaz metin; özet ve "Seçimi kaldır" üstte, işlemler altta.
- Künye: üstte 3 px erik çizgi; alanlar `dl` ızgarası (5 sütun, mobilde 2).
- Özel bileşenler `theme.css` içinde `mtp-` önekiyle tanımlıdır.

## Harita

- Dış harita servisi ya da karo yok. İl merkezleri ve kaba sınır `app/Support/IlKoordinatlari.php` içinde sabit tablodur.
- Daire: il başına bir. Alan açık tutarla orantılı (yarıçap karekök). Ton: ildeki çoğunluk tehlike sınıfı (eşitlikte yüksek olan).
- Daireler büyükten küçüğe çizilir; küçük daire büyüğün üstünde kalır.
- Etiket yeri: önce tablodaki tercih, sonra 8 yön; başka daireye ve etikete binmeyen ilk yer seçilir, yoksa 8 birim uzakta yeniden denenir (`MusteriHaritasi::etiketleriYerlestir`).
- Kesik erik halka: seçili il (liste süzgeci). Düz erik halka: künyesi açık firmanın ili.
- Daire klavyeyle seçilir (`role="button"`, Enter/Boşluk, `aria-pressed`); odakta erik halka çizilir.

## Durumlar

- Boş: "Henüz müşteri yok" + "Müşteri ekle" / "Excel'den içe aktar"; süzgeç boşsa "Filtreyi temizle".
- Yükleniyor: liste ve harita soluklaşır, `aria-busy`; sayaç "yükleniyor" der.
- Firma seçilmedi: künye yerinde kısa yönlendirme.
- Hata: alan altında doğrulama iletisi; 403/404/419/500 Türkçe.
- Başarılı: toplu işlemden sonra bildirim ("N müşteri güncellendi").

## Hareket

- Süre 150–200 ms, `cubic-bezier(0.16, 1, 0.3, 1)`. Yalnız durum değişikliğinde.
- `prefers-reduced-motion: reduce` iken geçişler ve yumuşak kaydırma kapanır.

## Erişilebilirlik

- Bütün etkileşim klavyeyle yapılır; odak çizgisi görünür (erik, 2 px).
- Hedef boyutu en az 24 px (WCAG 2.2, 2.5.8). Filament tablo sıralama düğmesi, içerik yolu ve kopyalanabilir metin `theme.css` içinde 24 px'e çıkarıldı. Harita ekranı kontrolleri 40–44 px.
- Telefon izleyiciye maskeli (KVKK); izleyici telefonla arayamaz.
- Grafiklerin ekran okuyucu için tablo karşılığı vardır.
