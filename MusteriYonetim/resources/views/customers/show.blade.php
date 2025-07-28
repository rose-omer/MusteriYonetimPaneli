<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Müşteri Detayı') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ $customer->hizmet_alan_isyeri_unvani }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><span class="font-semibold">Firma No:</span> {{ $customer->firma_no }}</p>
                            <p><span class="font-semibold">Görünür Ünvanı:</span> {{ $customer->gorunur_unvani }}</p>
                            <p><span class="font-semibold">Faaliyet:</span> {{ $customer->faaliyet }}</p>
                            <p><span class="font-semibold">Çalışan Sayısı:</span> {{ $customer->calisan_sayisi }}</p>
                            <p><span class="font-semibold">Tehlike Sınıfı:</span> {{ $customer->tehlike_sinifi }}</p>
                            <p><span class="font-semibold">Firma Sorumlusu:</span> {{ $customer->firma_sorumlusu }}</p>
                            <p><span class="font-semibold">Telefon Numarası:</span> {{ $customer->telefon_numarasi }}</p>
                            <p><span class="font-semibold">İl:</span> {{ $customer->il }}</p>
                            <p><span class="font-semibold">Mahalle:</span> {{ $customer->mahalle }}</p>
                            <p><span class="font-semibold">Konum URL:</span> <a href="{{ $customer->konum_url }}" target="_blank" class="text-blue-500 hover:underline">{{ $customer->konum_url }}</a></p>
                        </div>
                        <div>
                            <p><span class="font-semibold">Fatura Tipi:</span> {{ $customer->fatura_tipi }}</p>
                            <p><span class="font-semibold">Fatura Durumu:</span> {{ $customer->fatura_durumu }}</p>
                            <p><span class="font-semibold">Tutar:</span> {{ $customer->tutar }}</p>
                            <p><span class="font-semibold">Ödeme Durumu:</span> {{ $customer->odeme_durumu }}</p>
                            <p><span class="font-semibold">Sözleşme Onay Durumu:</span> {{ $customer->sozlesme_onay_durumu }}</p>
                            <p><span class="font-semibold">İGU:</span> {{ $customer->igu }}</p>
                            <p><span class="font-semibold">İH:</span> {{ $customer->ih }}</p>
                            <p><span class="font-semibold">Firma Ziyareti:</span> {{ $customer->firma_ziyareti }}</p>
                            <p><span class="font-semibold">Defter:</span> {{ $customer->defter }}</p>
                            <p><span class="font-semibold">RA:</span> {{ $customer->ra }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                            Düzenle
                        </a>
                        <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Geri Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>