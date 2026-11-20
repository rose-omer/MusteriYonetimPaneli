<x-filament-panels::page>
    @php
        $ozet = $this->ozet;
        $baglanti = $this->ozetBaglantilari();
        $harita = $this->harita;
        $firma = $this->seciliFirma;
        $liste = $this->liste;
        $toplam = $this->listeToplam;
        $toplu = $this->topluIslemVar();
        $izgara = \App\Support\IlKoordinatlari::izgara();
        $bogaz = \App\Support\IlKoordinatlari::bogaz();
        $acikIl = $firma ? \App\Support\IlKoordinatlari::kanonik($firma->il) : null;
        $ilSecenekleri = collect($harita['iller'])->pluck('adet', 'ad')->sortKeys(SORT_LOCALE_STRING);
        $tehlikeSecenekleri = ['' => 'Tümü', 'az_tehlikeli' => 'Az', 'tehlikeli' => 'Tehlikeli', 'cok_tehlikeli' => 'Çok'];
        $yuklemeHedefi = 'arama,tehlike,il,odeme,tehlikeSec,ilSec,dahaFazla,siralamaDegistir,filtreleriTemizle';
    @endphp

    <div
        class="mtp-harita"
        x-data
        x-on:firma-secildi.window="
            setTimeout(() => {
                const kunye = document.getElementById('mtp-kunye');
                if (! kunye) return;
                const kutu = kunye.getBoundingClientRect();
                const ust = parseFloat(getComputedStyle(kunye).scrollMarginTop) || 0;
                if (kutu.top < ust || kutu.top > window.innerHeight - 80) {
                    const azHareket = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    kunye.scrollIntoView({ behavior: azHareket ? 'auto' : 'smooth', block: 'start' });
                    kunye.querySelector('h2')?.focus({ preventScroll: true });
                }
            }, 30)
        "
    >
        <div class="mtp-harita__ust">
            <h1 class="mtp-harita__baslik">
                <x-filament::icon icon="heroicon-o-map" class="mtp-harita__baslik-ikon" />
                Müşteri haritası
            </h1>

            <div class="mtp-harita__arama">
                <label for="mtp-arama" class="sr-only">Müşteri ara</label>
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input
                        id="mtp-arama"
                        type="search"
                        autocomplete="off"
                        wire:model.live.debounce.400ms="arama"
                        :placeholder="$this->telefonMaskeli() ? 'Unvan ya da firma no ara' : 'Unvan, firma no ya da telefon ara'"
                    />
                </x-filament::input.wrapper>
            </div>

            @if ($this->ekleAction->isVisible())
                <div class="mtp-harita__ekle">{{ $this->ekleAction }}</div>
            @endif
        </div>

        <div class="mtp-harita__izgara">
            <div class="mtp-harita__sol">
                <section class="mtp-kart mtp-kart--harita" aria-labelledby="mtp-harita-baslik">
                    <h2 id="mtp-harita-baslik" class="sr-only">Harita ve özet</h2>

                    <div class="mtp-ozet">
                        <a href="{{ $baglanti['acik'] }}" class="mtp-ozet__hucre">
                            <span class="mtp-ozet__etiket">Ödenmemiş tutar</span>
                            <span class="mtp-ozet__deger mtp-num">{{ $this->tl($ozet['acikTutar']) }}</span>
                            <span class="mtp-ozet__alt">{{ $ozet['acikFirma'] }} firmada</span>
                        </a>
                        <a href="{{ $baglanti['ziyaret'] }}" class="mtp-ozet__hucre">
                            <span class="mtp-ozet__etiket">Bu ay ziyaret</span>
                            <span class="mtp-ozet__deger mtp-num">{{ $ozet['ziyaret'] }}</span>
                            <span class="mtp-ozet__alt">{{ now()->locale('tr')->translatedFormat('F Y') }}</span>
                        </a>
                        <a href="{{ $baglanti['onaysiz'] }}" class="mtp-ozet__hucre">
                            <span class="mtp-ozet__etiket">Sözleşmesi bekleyen</span>
                            <span class="mtp-ozet__deger mtp-num">{{ $ozet['onaysiz'] }}</span>
                            <span class="mtp-ozet__alt">onay bekliyor</span>
                        </a>
                    </div>

                    <p class="mtp-map-ipucu" aria-hidden="true">
                        <x-filament::icon icon="heroicon-m-arrows-right-left" class="size-4" />
                        Haritayı yana kaydırın
                    </p>
                    <div
                        class="mtp-map-kap"
                        role="region"
                        aria-label="Harita, yana kaydırılabilir"
                        tabindex="0"
                        wire:loading.class="is-yukleniyor"
                        wire:target="{{ $yuklemeHedefi }}"
                    >
                        <svg
                            class="mtp-map"
                            viewBox="0 0 {{ \App\Support\IlKoordinatlari::GENISLIK }} {{ \App\Support\IlKoordinatlari::YUKSEKLIK }}"
                            role="group"
                            aria-labelledby="mtp-map-baslik"
                        >
                            <title id="mtp-map-baslik">Müşterilerin illere göre şematik haritası. Daire boyutu ildeki açık tutarı, tonu ildeki çoğunluk tehlike sınıfını gösterir. Bir ile basınca liste o ile süzülür.</title>
                            <rect class="mtp-map__deniz" x="0" y="0" width="860" height="380" />
                            <g class="mtp-map__izgara" aria-hidden="true">
                                @foreach ($izgara['yatay'] as $y)
                                    <path d="M0 {{ $y }} H860" />
                                @endforeach
                                @foreach ($izgara['dikey'] as $x)
                                    <path d="M{{ $x }} 0 V380" />
                                @endforeach
                            </g>
                            <polygon class="mtp-map__kara" points="{{ \App\Support\IlKoordinatlari::sinirNoktalari() }}" aria-hidden="true" />
                            <polygon class="mtp-map__ic-deniz" points="{{ \App\Support\IlKoordinatlari::marmaraNoktalari() }}" aria-hidden="true" />
                            <line class="mtp-map__bogaz" x1="{{ $bogaz['x1'] }}" y1="{{ $bogaz['y1'] }}" x2="{{ $bogaz['x2'] }}" y2="{{ $bogaz['y2'] }}" aria-hidden="true" />

                            @foreach ($harita['iller'] as $il)
                                @php
                                    $secili = $this->il === $il['ad'];
                                    $tanim = $il['ad'].': '.$il['adet'].' müşteri, açık tutar '.$this->tl($il['acik'])
                                        .($il['sinif'] ? ', çoğunluk '.mb_strtolower($il['sinif']->getLabel()) : '');
                                @endphp
                                <g
                                    wire:key="il-{{ $il['ad'] }}"
                                    class="mtp-map__il"
                                    role="button"
                                    tabindex="0"
                                    aria-pressed="{{ $secili ? 'true' : 'false' }}"
                                    aria-label="{{ $tanim }}"
                                    wire:click="ilSec(@js($il['ad']))"
                                    x-on:keydown.enter.prevent="$wire.ilSec(@js($il['ad']))"
                                    x-on:keydown.space.prevent="$wire.ilSec(@js($il['ad']))"
                                >
                                    <title>{{ $tanim }}</title>
                                    <circle class="mtp-map__odak" cx="{{ $il['x'] }}" cy="{{ $il['y'] }}" r="{{ $il['r'] + 8 }}" />
                                    @if ($acikIl === $il['ad'])
                                        <circle class="mtp-map__halka is-acik" cx="{{ $il['x'] }}" cy="{{ $il['y'] }}" r="{{ $il['r'] + 6 }}" />
                                    @endif
                                    @if ($secili)
                                        <circle class="mtp-map__halka is-secili" cx="{{ $il['x'] }}" cy="{{ $il['y'] }}" r="{{ $il['r'] + ($acikIl === $il['ad'] ? 10 : 5) }}" />
                                    @endif
                                    <circle class="mtp-map__daire mtp-hz-{{ $il['sinif']?->derece() ?? 0 }}" cx="{{ $il['x'] }}" cy="{{ $il['y'] }}" r="{{ $il['r'] }}" />
                                </g>
                            @endforeach

                            <g class="mtp-map__etiketler" aria-hidden="true">
                                @foreach ($harita['iller'] as $il)
                                    <text
                                        x="{{ $il['etiket']['x'] }}"
                                        y="{{ $il['etiket']['y'] }}"
                                        text-anchor="{{ $il['etiket']['yon'] }}"
                                        @class(['mtp-map__etiket', 'is-vurgu' => $acikIl === $il['ad'] || $this->il === $il['ad']])
                                    >{{ $il['ad'] }}</text>
                                @endforeach
                            </g>
                        </svg>
                    </div>

                    <div class="mtp-lejant">
                        <span class="mtp-lejant__grup">
                            <x-filament::badge color="tehlike-az">Az tehlikeli</x-filament::badge>
                            <x-filament::badge color="tehlike-orta">Tehlikeli</x-filament::badge>
                            <x-filament::badge color="tehlike-cok">Çok tehlikeli</x-filament::badge>
                        </span>
                        <span>Daire büyüdükçe açık tutar artar.</span>
                        <span class="mtp-lejant__grup">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18"><circle class="mtp-map__halka is-secili" cx="9" cy="9" r="7" /></svg>
                            Seçili il
                        </span>
                        <span class="mtp-lejant__grup">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18"><circle class="mtp-map__halka is-acik" cx="9" cy="9" r="7" /></svg>
                            Açık künye
                        </span>
                        <span class="mtp-lejant__not">
                            Şematik harita, konumlar il merkezidir.
                            @if ($harita['disarida'] > 0)
                                {{ $harita['disarida'] }} müşterinin ili tanınmadı.
                            @endif
                        </span>
                    </div>
                </section>

                @if ($firma)
                    <section id="mtp-kunye" class="mtp-kunye" aria-labelledby="mtp-kunye-baslik" wire:key="kunye-{{ $firma->id }}">
                        <div class="mtp-kunye__bas">
                            <div class="mtp-kunye__kimlik">
                                @if ($firma->firma_no)
                                    <span class="mtp-kunye__no mtp-num">{{ $firma->firma_no }}</span>
                                @endif
                                <h2 id="mtp-kunye-baslik" class="mtp-kunye__ad" tabindex="-1">{{ $firma->hizmet_alan_isyeri_unvani }}</h2>
                                <div class="mtp-kunye__meta">
                                    @if ($firma->tehlike_sinifi)
                                        <x-filament::badge :color="$firma->tehlike_sinifi->getColor()">{{ $firma->tehlike_sinifi->getLabel() }}</x-filament::badge>
                                    @endif
                                    @if ($firma->faaliyet || $firma->calisan_sayisi)
                                        <span>{{ collect([$firma->faaliyet?->isim, $firma->calisan_sayisi ? $firma->calisan_sayisi.' çalışan' : null])->filter()->join(', ') }}</span>
                                    @endif
                                    @if ($firma->il || $firma->mahalle)
                                        <span class="mtp-kunye__yer">
                                            <x-filament::icon icon="heroicon-o-map-pin" class="size-4" />
                                            {{ collect([$firma->mahalle, $firma->il])->filter()->join(', ') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mtp-kunye__eylemler">
                                @if ($firma->konum_url)
                                    <a href="{{ $firma->konum_url }}" target="_blank" rel="noopener noreferrer" class="mtp-dugme">
                                        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="size-4" />
                                        Haritada aç<span class="sr-only"> (yeni sekmede)</span>
                                    </a>
                                @endif
                                <a href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $firma]) }}" class="mtp-dugme">
                                    <x-filament::icon icon="heroicon-o-document-text" class="size-4" />
                                    Ayrıntı
                                </a>
                                @can('update', $firma)
                                    <a href="{{ \App\Filament\Resources\Customers\CustomerResource::getUrl('edit', ['record' => $firma]) }}" class="mtp-dugme is-dolu">
                                        <x-filament::icon icon="heroicon-o-pencil" class="size-4" />
                                        Düzenle
                                    </a>
                                @endcan
                                <button type="button" class="mtp-dugme is-ikon" wire:click="kapat" aria-label="Künyeyi kapat">
                                    <x-filament::icon icon="heroicon-o-x-mark" class="size-4" />
                                </button>
                            </div>
                        </div>

                        @php
                            $yok = '<span class="mtp-kunye__yok">Girilmedi</span>';
                            $telefon = $this->telefonGosterimi($firma->telefon_numarasi);
                            $ziyaret = $firma->firma_ziyareti;
                        @endphp
                        <dl class="mtp-kunye__alanlar">
                            <div><dt>Firma sorumlusu</dt><dd>{!! $firma->firma_sorumlusu ? e($firma->firma_sorumlusu) : $yok !!}</dd></div>
                            <div>
                                <dt>Telefon</dt>
                                <dd class="mtp-num">
                                    @if (! $telefon)
                                        {!! $yok !!}
                                    @elseif ($this->telefonMaskeli())
                                        {{ $telefon }}
                                    @else
                                        <a href="tel:{{ preg_replace('/\D/', '', (string) $firma->telefon_numarasi) }}">{{ $telefon }}</a>
                                    @endif
                                </dd>
                            </div>
                            <div><dt>Tutar</dt><dd class="mtp-num">{!! $firma->tutar !== null ? e($this->tl($firma->tutar, 2)) : $yok !!}</dd></div>
                            <div><dt>Ödeme</dt><dd>{!! $firma->odeme_durumu ? e($firma->odeme_durumu->getLabel()) : $yok !!}</dd></div>
                            <div><dt>Fatura</dt><dd>{!! ($firma->fatura_tipi || $firma->fatura_durumu) ? e(collect([$firma->fatura_tipi?->getLabel(), $firma->fatura_durumu ? mb_strtolower($firma->fatura_durumu->getLabel()) : null])->filter()->join(', ')) : $yok !!}</dd></div>
                            <div><dt>İGU</dt><dd>{!! $firma->igu ? e($firma->igu) : $yok !!}</dd></div>
                            <div><dt>İH</dt><dd>{!! $firma->ih ? e($firma->ih) : $yok !!}</dd></div>
                            <div>
                                <dt>Son ziyaret</dt>
                                <dd>
                                    @if ($ziyaret)
                                        @php $gun = (int) round($ziyaret->copy()->startOfDay()->diffInDays(today())); @endphp
                                        <span class="mtp-num">{{ $ziyaret->format('d.m.Y') }}</span>,
                                        {{ match (true) { $gun === 0 => 'bugün', $gun > 0 => $gun.' gün önce', default => abs($gun).' gün sonra' } }}
                                    @else
                                        <span class="mtp-kunye__yok">Ziyaret yok</span>
                                    @endif
                                </dd>
                            </div>
                            <div><dt>Defter / RA</dt><dd>{{ $firma->defter ?: 'Girilmedi' }} / {{ $firma->ra ?: 'Girilmedi' }}</dd></div>
                            <div><dt>Sözleşme</dt><dd>{{ $firma->sozlesme_onay_durumu ? 'Onaylı' : 'Onay bekliyor' }}</dd></div>
                        </dl>

                        @if ($this->telefonMaskeli())
                            <p class="mtp-kunye__not">Telefon KVKK gereği maskeli. Tam numarayı yönetici ve düzenleyici görür.</p>
                        @endif
                    </section>
                @else
                    <section id="mtp-kunye" class="mtp-kunye is-bos" aria-labelledby="mtp-kunye-bos">
                        <h2 id="mtp-kunye-bos" class="mtp-kunye__bos-baslik">Firma seçilmedi</h2>
                        <p class="mtp-kunye__bos-metin">Listeden bir firma seçin; künyesi burada açılır. Haritada bir ile basarsanız liste o ile süzülür.</p>
                    </section>
                @endif
            </div>

            <section class="mtp-kart mtp-liste" aria-labelledby="mtp-liste-baslik">
                <h2 id="mtp-liste-baslik" class="sr-only">Müşteri listesi</h2>

                <div class="mtp-liste__filtre">
                    <div class="mtp-etiket" id="mtp-tehlike-etiket">Tehlike sınıfı</div>
                    <div class="mtp-segment" role="group" aria-labelledby="mtp-tehlike-etiket">
                        @foreach ($tehlikeSecenekleri as $deger => $etiket)
                            <button
                                type="button"
                                wire:click="tehlikeSec(@js($deger))"
                                aria-pressed="{{ $this->tehlike === $deger ? 'true' : 'false' }}"
                                @class(['mtp-segment__dugme', 'is-secili' => $this->tehlike === $deger])
                            >{{ $etiket }}</button>
                        @endforeach
                    </div>

                    <div class="mtp-liste__secimler">
                        <div class="mtp-alan">
                            <label for="mtp-il" class="mtp-etiket">İl</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="mtp-il" wire:model.live="il">
                                    <option value="">Haritadaki bütün iller</option>
                                    @foreach ($ilSecenekleri as $ad => $adet)
                                        <option value="{{ $ad }}">{{ $ad }} ({{ $adet }})</option>
                                    @endforeach
                                    @if ($this->il !== '' && ! $ilSecenekleri->has($this->il))
                                        <option value="{{ $this->il }}">{{ $this->il }} (0)</option>
                                    @endif
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                        <div class="mtp-alan">
                            <label for="mtp-odeme" class="mtp-etiket">Ödeme durumu</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="mtp-odeme" wire:model.live="odeme">
                                    <option value="">Tümü</option>
                                    <option value="acik">Ödenmemiş ya da kısmi</option>
                                    @foreach (\App\Enums\OdemeDurumu::cases() as $durum)
                                        <option value="{{ $durum->value }}">{{ $durum->getLabel() }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </div>

                @if ($toplu && count($isaretli) > 0)
                    @php $secim = $this->isaretliOzet; @endphp
                    <div class="mtp-toplu" role="region" aria-label="Toplu işlemler">
                        <strong class="mtp-toplu__ozet">{{ $secim['adet'] }} seçili, <span class="mtp-num">{{ $this->tl($secim['tutar']) }}</span></strong>
                        <button type="button" class="mtp-toplu__dugme is-ikon is-cizgisiz" wire:click="secimiKaldir" aria-label="Seçimi kaldır" title="Seçimi kaldır">
                            <x-filament::icon icon="heroicon-o-x-mark" class="size-4" />
                        </button>
                        <div class="mtp-toplu__eylemler">
                        @if ($this->odemeAction->isVisible())
                            <button type="button" class="mtp-toplu__dugme" wire:click="mountAction('odeme')" title="Ödeme durumunu değiştir">
                                <x-filament::icon icon="heroicon-o-banknotes" class="size-4" />
                                Ödeme
                            </button>
                        @endif
                        @if ($this->faturaAction->isVisible())
                            <button type="button" class="mtp-toplu__dugme" wire:click="mountAction('fatura')" title="Fatura durumunu değiştir">
                                <x-filament::icon icon="heroicon-o-document-text" class="size-4" />
                                Fatura
                            </button>
                        @endif
                        @if ($this->excelAction->isVisible())
                            <button type="button" class="mtp-toplu__dugme" wire:click="mountAction('excel')" title="Seçilenleri Excel'e aktar">
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="size-4" />
                                Excel
                            </button>
                        @endif
                        @if ($this->silAction->isVisible())
                            <button type="button" class="mtp-toplu__dugme is-ikon" wire:click="mountAction('sil')" aria-label="Seçilenleri sil" title="Seçilenleri sil">
                                <x-filament::icon icon="heroicon-o-trash" class="size-4" />
                            </button>
                        @endif
                        </div>
                    </div>
                @endif

                <div class="mtp-liste__durum">
                    <p aria-live="polite">
                        <span class="mtp-num">{{ $toplam }}</span> müşteri{{ $this->il !== '' ? ', '.$this->il : '' }}
                        <span class="mtp-liste__yukleniyor" wire:loading wire:target="{{ $yuklemeHedefi }}">, yükleniyor</span>
                    </p>
                    <button type="button" class="mtp-sirala" wire:click="siralamaDegistir">
                        <x-filament::icon icon="heroicon-o-arrows-up-down" class="size-4" />
                        <span class="sr-only">Sıralama:</span>
                        {{ $this->sirala === 'tutar' ? 'Tutar' : 'Unvan' }}
                        <span class="sr-only">. {{ $this->sirala === 'tutar' ? 'Unvana' : 'Tutara' }} göre sıralamak için basın.</span>
                    </button>
                </div>

                <div
                    class="mtp-liste__govde"
                    wire:loading.class="is-yukleniyor"
                    wire:loading.attr="aria-busy"
                    wire:target="{{ $yuklemeHedefi }}"
                >
                    @if ($liste->isEmpty())
                        <div class="mtp-bos">
                            @if ($ozet['toplam'] === 0)
                                <p class="mtp-bos__baslik">Henüz müşteri yok</p>
                                <p class="mtp-bos__metin">Müşteriyi tek tek ekleyin ya da Excel listesini içe aktarın. Eklenen firmalar ilindeki dairede görünür.</p>
                                <div class="mtp-bos__eylemler">
                                    @if ($this->ekleAction->isVisible())
                                        {{ $this->ekleAction }}
                                    @endif
                                    @can('import', \App\Models\Customer::class)
                                        <x-filament::button tag="a" color="gray" icon="heroicon-o-arrow-up-tray" :href="\App\Filament\Pages\ImportCustomers::getUrl()">Excel'den içe aktar</x-filament::button>
                                    @endcan
                                </div>
                            @else
                                <p class="mtp-bos__baslik">Bu ölçütlere uyan müşteri yok</p>
                                <p class="mtp-bos__metin">Aramayı ya da süzgeçleri değiştirin.</p>
                                <div class="mtp-bos__eylemler">
                                    <x-filament::button color="gray" icon="heroicon-o-x-mark" wire:click="filtreleriTemizle">Filtreyi temizle</x-filament::button>
                                </div>
                            @endif
                        </div>
                    @else
                        <ul class="mtp-satirlar" role="list">
                            @foreach ($liste as $musteri)
                                @php
                                    $acik = $firma?->id === $musteri->id;
                                    $isaretliMi = in_array((string) $musteri->id, array_map('strval', $isaretli), true);
                                    $ziyaretMetni = $musteri->firma_ziyareti?->format('d.m.Y') ?? 'ziyaret yok';
                                    $meta = collect([
                                        $musteri->il,
                                        $musteri->tehlike_sinifi ? mb_strtolower($musteri->tehlike_sinifi->getLabel()) : null,
                                        $ziyaretMetni,
                                    ])->filter()->join(', ');
                                @endphp
                                <li
                                    wire:key="musteri-{{ $musteri->id }}"
                                    @class(['mtp-satir', 'has-kutu' => $toplu, 'is-isaretli' => $isaretliMi, 'is-acik' => $acik])
                                >
                                    @if ($toplu)
                                        <label class="mtp-satir__kutu">
                                            <input
                                                type="checkbox"
                                                value="{{ $musteri->id }}"
                                                wire:model.live="isaretli"
                                                aria-label="{{ $musteri->hizmet_alan_isyeri_unvani }} seç"
                                            />
                                        </label>
                                    @endif
                                    <div class="mtp-satir__ana">
                                        <button
                                            type="button"
                                            class="mtp-satir__ad"
                                            wire:click="sec({{ $musteri->id }})"
                                            @if ($acik) aria-current="true" @endif
                                        >{{ $musteri->hizmet_alan_isyeri_unvani }}</button>
                                        <div class="mtp-satir__meta">
                                            <svg aria-hidden="true" width="10" height="10" viewBox="0 0 10 10"><circle class="mtp-map__daire mtp-hz-{{ $musteri->tehlike_sinifi?->derece() ?? 0 }}" cx="5" cy="5" r="4" /></svg>
                                            <span>{{ $meta }}</span>
                                        </div>
                                    </div>
                                    <div class="mtp-satir__sag">
                                        <div class="mtp-num">{{ $musteri->tutar !== null ? $this->tl($musteri->tutar, 2) : '' }}</div>
                                        @if ($musteri->odeme_durumu)
                                            <div @class(['mtp-satir__odeme', 'is-vurgu' => $musteri->odeme_durumu === \App\Enums\OdemeDurumu::Odenmemis])>{{ $musteri->odeme_durumu->getLabel() }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if ($toplam > $liste->count())
                            <div class="mtp-liste__daha">
                                <x-filament::button color="gray" wire:click="dahaFazla" wire:target="dahaFazla">
                                    {{ min(\App\Filament\Pages\MusteriHaritasi::SAYFA, $toplam - $liste->count()) }} müşteri daha göster
                                </x-filament::button>
                            </div>
                        @endif
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
