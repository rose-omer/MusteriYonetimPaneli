<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Müşteri Detayı') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">

                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        {{ $customer->hizmet_alan_isyeri_unvani ?: '-' }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Sol sütun --}}
                        <div class="space-y-2">
                            @foreach ([
                                'Firma No' => $customer->firma_no,
                                'Görünür Ünvanı' => $customer->gorunur_unvani,
                                'Faaliyet' => $customer->faaliyet,
                                'Çalışan Sayısı' => $customer->calisan_sayisi,
                                'Tehlike Sınıfı' => $customer->tehlike_sinifi,
                                'Firma Sorumlusu' => $customer->firma_sorumlusu,
                                'Telefon Numarası' => $customer->telefon_numarasi,
                                'İl' => $customer->il,
                                'Mahalle' => $customer->mahalle,
                            ] as $label => $value)
                                <div class="border-b pb-1">
                                    <span class="font-semibold">{{ $label }}:</span> {{ $value ?: '-' }}
                                </div>
                            @endforeach

                            <div class="border-b pb-1">
                                <span class="font-semibold">Konum URL:</span>
                                @if ($customer->konum_url)
                                    <a href="{{ $customer->konum_url }}" target="_blank" class="text-blue-500 hover:underline">{{ $customer->konum_url }}</a>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                        </div>

                        {{-- Sağ sütun --}}
                        <div class="space-y-2">
                            @foreach ([
                                'Fatura Tipi' => $customer->fatura_tipi,
                                'Fatura Durumu' => $customer->fatura_durumu,
                                'Tutar' => $customer->tutar ? number_format($customer->tutar, 2, ',', '.') : '-',
                                'Ödeme Durumu' => $customer->odeme_durumu,
                                'Sözleşme Onay Durumu' => $customer->sozlesme_onay_durumu ? 'Evet' : 'Hayır',
                                'İGU' => $customer->igu,
                                'İH' => $customer->ih,
                                'Firma Ziyareti' => $customer->firma_ziyareti ? \Carbon\Carbon::parse($customer->firma_ziyareti)->format('d.m.Y') : '-',
                                'Defter' => $customer->defter,
                                'RA' => $customer->ra,
                            ] as $label => $value)
                                <div class="border-b pb-1">
                                    <span class="font-semibold">{{ $label }}:</span> {{ $value ?: '-' }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Butonlar --}}
                    <div class="mt-6 flex justify-end space-x-2">
                        <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-md transition">
                            Düzenle
                        </a>
                        <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-md transition">
                            Geri Dön
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
