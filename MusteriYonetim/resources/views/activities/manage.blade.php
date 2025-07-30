<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Faaliyet Yönetimi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (auth()->user()->role === 'admin')
                {{-- Admin için Livewire bileşeni --}}
                @livewire('activity-manager')

            @elseif (auth()->user()->role === 'observe')
                {{-- Observe için sadece liste göster --}}

                @php
                    $faaliyetler = app(\App\Services\FaaliyetManager::class)->getFaaliyetler();
                @endphp

                <div class="bg-white shadow rounded p-4">
                    <h3 class="text-lg font-semibold mb-4">Mevcut Faaliyetler</h3>
                    <ul class="list-disc list-inside text-gray-700">
                        @foreach ($faaliyetler as $faaliyet)
                            <li>{{ $faaliyet }}</li>
                        @endforeach
                    </ul>
                </div>

            @else
                <div class="text-red-600 font-semibold">Bu sayfaya erişim yetkiniz yok.</div>
            @endif

        </div>
    </div>
</x-app-layout>
