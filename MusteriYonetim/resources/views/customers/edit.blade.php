<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Müşteriyi Düzenle') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="hizmet_alan_isyeri_unvani" :value="__('Hizmet Alan İşyeri Unvanı')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="hizmet_alan_isyeri_unvani" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="hizmet_alan_isyeri_unvani" :value="old('hizmet_alan_isyeri_unvani', $customer->hizmet_alan_isyeri_unvani)" required autofocus />
                            <x-input-error :messages="$errors->get('hizmet_alan_isyeri_unvani')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="firma_no" :value="__('Firma No')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="firma_no" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="firma_no" :value="old('firma_no', $customer->firma_no)" />
                            <x-input-error :messages="$errors->get('firma_no')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="gorunur_unvani" :value="__('Görünür Ünvanı')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="gorunur_unvani" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="gorunur_unvani" :value="old('gorunur_unvani', $customer->gorunur_unvani)" />
                            <x-input-error :messages="$errors->get('gorunur_unvani')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="faaliyet" :value="__('Faaliyet')" class="text-gray-900 dark:text-white" />
                            <select id="faaliyet" name="faaliyet" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Seçiniz</option>
                                @foreach($faaliyetler as $faaliyet)
                                    <option value="{{ $faaliyet }}" {{ old('faaliyet', $customer->faaliyet) == $faaliyet ? 'selected' : '' }}>{{ $faaliyet }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('faaliyet')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="calisan_sayisi" :value="__('Çalışan Sayısı')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="calisan_sayisi" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="number" name="calisan_sayisi" :value="old('calisan_sayisi', $customer->calisan_sayisi)" min="0" />
                            <x-input-error :messages="$errors->get('calisan_sayisi')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="tehlike_sinifi" :value="__('Tehlike Sınıfı')" class="text-gray-900 dark:text-white" />
                            <select id="tehlike_sinifi" name="tehlike_sinifi" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Seçiniz</option>
                                @foreach($tehlike_siniflari as $sinif)
                                    <option value="{{ $sinif }}" {{ old('tehlike_sinifi', $customer->tehlike_sinifi) == $sinif ? 'selected' : '' }}>{{ $sinif }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tehlike_sinifi')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="firma_sorumlusu" :value="__('Firma Sorumlusu')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="firma_sorumlusu" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="firma_sorumlusu" :value="old('firma_sorumlusu', $customer->firma_sorumlusu)" />
                            <x-input-error :messages="$errors->get('firma_sorumlusu')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="telefon_numarasi" :value="__('Telefon Numarası')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="telefon_numarasi" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="telefon_numarasi" :value="old('telefon_numarasi', $customer->telefon_numarasi)" />
                            <x-input-error :messages="$errors->get('telefon_numarasi')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="il" :value="__('İl')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="il" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="il" :value="old('il', $customer->il)" />
                            <x-input-error :messages="$errors->get('il')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="mahalle" :value="__('Mahalle')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="mahalle" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="mahalle" :value="old('mahalle', $customer->mahalle)" />
                            <x-input-error :messages="$errors->get('mahalle')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="konum_url" :value="__('Konum URL')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="konum_url" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="url" name="konum_url" :value="old('konum_url', $customer->konum_url)" />
                            <x-input-error :messages="$errors->get('konum_url')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="fatura_tipi" :value="__('Fatura Tipi')" class="text-gray-900 dark:text-white" />
                            <select id="fatura_tipi" name="fatura_tipi" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Seçiniz</option>
                                @foreach($fatura_tipleri as $tip)
                                    <option value="{{ $tip }}" {{ old('fatura_tipi', $customer->fatura_tipi) == $tip ? 'selected' : '' }}>{{ $tip }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('fatura_tipi')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="fatura_durumu" :value="__('Fatura Durumu')" class="text-gray-900 dark:text-white" />
                            <select id="fatura_durumu" name="fatura_durumu" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Seçiniz</option>
                                @foreach($fatura_durumlari as $durum)
                                    <option value="{{ $durum }}" {{ old('fatura_durumu', $customer->fatura_durumu) == $durum ? 'selected' : '' }}>{{ $durum }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('fatura_durumu')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="tutar" :value="__('Tutar')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="tutar" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="number" step="0.01" name="tutar" :value="old('tutar', $customer->tutar)" />
                            <x-input-error :messages="$errors->get('tutar')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="odeme_durumu" :value="__('Ödeme Durumu')" class="text-gray-900 dark:text-white" />
                            <select id="odeme_durumu" name="odeme_durumu" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Seçiniz</option>
                                @foreach($odeme_durumlari as $durum)
                                    <option value="{{ $durum }}" {{ old('odeme_durumu', $customer->odeme_durumu) == $durum ? 'selected' : '' }}>{{ $durum }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('odeme_durumu')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="sozlesme_onay_durumu" :value="__('Sözleşme Onay Durumu')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="sozlesme_onay_durumu" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="sozlesme_onay_durumu" :value="old('sozlesme_onay_durumu', $customer->sozlesme_onay_durumu)" />
                            <x-input-error :messages="$errors->get('sozlesme_onay_durumu')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="igu" :value="__('İGU')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="igu" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="igu" :value="old('igu', $customer->igu)" />
                            <x-input-error :messages="$errors->get('igu')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="ih" :value="__('İH')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="ih" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="ih" :value="old('ih', $customer->ih)" />
                            <x-input-error :messages="$errors->get('ih')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="firma_ziyareti" :value="__('Firma Ziyareti')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="firma_ziyareti" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="firma_ziyareti" :value="old('firma_ziyareti', $customer->firma_ziyareti)" />
                            <x-input-error :messages="$errors->get('firma_ziyareti')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="defter" :value="__('Defter')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="defter" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="defter" :value="old('defter', $customer->defter)" />
                            <x-input-error :messages="$errors->get('defter')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="ra" :value="__('RA')" class="text-gray-900 dark:text-white" />
                            <x-text-input id="ra" class="block mt-1 w-full text-gray-900 dark:text-gray-100" type="text" name="ra" :value="old('ra', $customer->ra)" />
                            <x-input-error :messages="$errors->get('ra')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Müşteriyi Güncelle') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```