<x-filament-panels::page>
    @php
        $steps = [1 => 'Dosya seç', 2 => 'Önizle', 3 => 'Sonuç'];
        $money = fn ($value) => is_numeric($value) ? \App\Support\TurkishFormat::tl($value, 2) : $value;
    @endphp

    <nav aria-label="İçe aktarım adımları">
        <ol class="mtp-steps">
            @foreach ($steps as $number => $label)
                <li @class([
                    'mtp-steps__item',
                    'is-done' => $step > $number,
                    'is-current' => $step === $number,
                ]) @if ($step === $number) aria-current="step" @endif>
                    <span class="mtp-steps__marker" aria-hidden="true">
                        @if ($step > $number)
                            <x-filament::icon icon="heroicon-m-check" class="size-4" />
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="mtp-steps__label">{{ $label }}</span>
                    @if ($step > $number)
                        <span class="sr-only">(tamamlandı)</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    @if ($step === 1)
        <x-filament::section>
            <x-slot name="heading">Excel dosyasını seçin</x-slot>
            <x-slot name="description">
                Aynı firma numarasıyla gelen satır mevcut kaydı günceller. Firma numarası olmayan satır yeni kayıt olur.
            </x-slot>

            <form wire:submit="buildPreview" class="mtp-stack">
                {{ $this->form }}

                <div class="mtp-hint">
                    <p>Şablonda 1. satır açıklama, 2. satır başlık, 3. satırdan sonrası veridir. Tutar "1.250,50", tarih "15.03.2025" biçiminde olabilir.</p>
                    {{ $this->templateAction }}
                </div>

                <div class="mtp-actions">
                    {{ $this->previewAction }}
                </div>
            </form>
        </x-filament::section>
    @endif

    @if ($step === 2)
        <x-filament::section>
            <x-slot name="heading">{{ $originalName }}</x-slot>
            <x-slot name="description">Dosya okundu. Henüz hiçbir kayıt değişmedi.</x-slot>

            <dl class="mtp-figures">
                <div>
                    <dt>Okunan satır</dt>
                    <dd>{{ number_format($preview['total'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>İçe aktarılacak</dt>
                    <dd class="is-success">{{ number_format($preview['valid'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Hatalı, atlanacak</dt>
                    <dd @class(['is-danger' => $preview['invalid'] > 0])>{{ number_format($preview['invalid'], 0, ',', '.') }}</dd>
                </div>
            </dl>

            <div class="mtp-table-wrap" tabindex="0" role="region" aria-label="İlk {{ count($preview['rows']) }} satırın önizlemesi">
                <table class="mtp-table">
                    <caption class="sr-only">İlk {{ count($preview['rows']) }} satır</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="is-num">Satır</th>
                            <th scope="col">Unvan</th>
                            <th scope="col">Firma no</th>
                            <th scope="col">İl</th>
                            <th scope="col" class="is-num">Tutar</th>
                            <th scope="col">Ödeme</th>
                            <th scope="col">Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['rows'] as $row)
                            <tr @class(['has-error' => $row['errors'] !== []])>
                                <td class="is-num">{{ $row['line'] }}</td>
                                <td class="is-strong">{{ $row['unvan'] ?? '—' }}</td>
                                <td>{{ $row['firma_no'] ?? '—' }}</td>
                                <td>{{ $row['il'] ?? '—' }}</td>
                                <td class="is-num">{{ $row['tutar'] === null ? '—' : $money($row['tutar']) }}</td>
                                <td>{{ $row['odeme'] ?? '—' }}</td>
                                <td>
                                    @if ($row['errors'] === [])
                                        <x-filament::badge color="success" icon="heroicon-m-check">Hazır</x-filament::badge>
                                    @else
                                        <x-filament::badge color="danger" icon="heroicon-m-exclamation-triangle">{{ count($row['errors']) }} hata</x-filament::badge>
                                        <ul class="mtp-errors">
                                            @foreach ($row['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($preview['total'] > count($preview['rows']))
                <p class="mtp-muted">İlk {{ count($preview['rows']) }} satır gösteriliyor. Hata sayısı bütün dosyayı kapsar.</p>
            @endif

            <div class="mtp-actions">
                {{ $this->restartAction }}
                {{ $this->importAction }}
            </div>
        </x-filament::section>
    @endif

    @if ($step === 3)
        <x-filament::section>
            <x-slot name="heading">İçe aktarım tamamlandı</x-slot>
            <x-slot name="description">{{ $originalName }}</x-slot>

            <dl class="mtp-figures" aria-live="polite">
                <div>
                    <dt>Eklendi</dt>
                    <dd class="is-success">{{ number_format($result['inserted'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Güncellendi</dt>
                    <dd>{{ number_format($result['updated'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>Atlandı</dt>
                    <dd @class(['is-danger' => $result['skipped'] > 0])>{{ number_format($result['skipped'], 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if ($result['failures'] !== [])
                <div class="mtp-table-wrap" tabindex="0" role="region" aria-label="Atlanan satırlar">
                    <table class="mtp-table">
                        <caption class="sr-only">Atlanan satırlar ve nedenleri</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="is-num">Satır</th>
                                <th scope="col">Alan</th>
                                <th scope="col">Neden</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($result['failures'], 0, 100) as $failure)
                                <tr>
                                    <td class="is-num">{{ $failure['line'] }}</td>
                                    <td>{{ $failure['field'] }}</td>
                                    <td>{{ $failure['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mtp-actions">
                {{ $this->restartAction }}
                {{ $this->downloadFailuresAction }}
                <x-filament::button tag="a" :href="\App\Filament\Resources\Customers\CustomerResource::getUrl('index')" icon="heroicon-m-building-office-2">
                    Müşteri listesine git
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

</x-filament-panels::page>
