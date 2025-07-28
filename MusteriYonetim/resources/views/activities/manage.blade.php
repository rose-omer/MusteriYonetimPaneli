<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Faaliyet Yönetimi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Buraya ActivityManager Livewire bileşenini ekliyoruz --}}
            @livewire('activity-manager')
        </div>
    </div>
</x-app-layout>