<div class="bg-white p-6 rounded-lg shadow-md mt-6">
    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Faaliyetler Yönetimi</h2>

    @if (session()->has('activity_message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Bilgi!</strong>
            <span class="block sm:inline">{{ session('activity_message') }}</span>
        </div>
    @endif

    @if (session()->has('activity_error'))
        <div class="mt-4 p-4 bg-red-100 text-red-800 rounded-md" role="alert">
            <strong class="font-bold">Hata!</strong>
            <span class="block sm:inline">{{ session('activity_error') }}</span>
        </div>
    @endif

    @error('activityName')
        <div class="mt-4 p-4 bg-red-100 text-red-800 rounded-md">
            {{ $message }}
        </div>
    @enderror
    @error('editingActivityNewName')
        <div class="mt-4 p-4 bg-red-100 text-red-800 rounded-md">
            {{ $message }}
        </div>
    @enderror

   <div class="mb-6 border-b pb-4">
    <h3 class="text-xl font-medium text-gray-800 mb-3">Yeni Faaliyet Ekle</h3>
    <form wire:submit.prevent="createActivity" class="flex items-center space-x-2">
        <input type="text" wire:model.defer="activityName" placeholder="Yeni faaliyet adı" class="flex-1 border border-gray-300 rounded-md shadow-sm p-2 text-sm">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">Ekle</button>
    </form>
</div>
    <div>
        <h3 class="text-xl font-medium text-gray-800 mb-3">Mevcut Faaliyetler</h3>
        @if ($activities->isEmpty())
            <p class="text-gray-600">Henüz hiçbir faaliyet eklenmemiş.</p>
        @else
            <ul class="divide-y divide-gray-200">
                {{-- activities burada bir LengthAwarePaginator nesnesi. items() ile verilere erişin --}}
                @foreach ($activities->items() as $activityName)
                    <li class="py-3 flex items-center justify-between">
                        @if ($editingActivityOriginalName === $activityName) {{-- JSON'da ID yok, adı kullanıyoruz --}}
                            {{-- Düzenleme Modu --}}
                            <input type="text" wire:model.defer="editingActivityNewName" class="flex-1 border border-gray-300 rounded-md shadow-sm p-2 text-sm mr-2">
                            <div class="flex space-x-2">
                                <button wire:click="updateActivity" class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded text-xs">Kaydet</button>
                                <button wire:click="cancelEdit" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-1 px-3 rounded text-xs">İptal</button>
                            </div>
                        @else
                            {{-- Normal Görüntü Modu --}}
                            <span class="text-gray-900">{{ $activityName }}</span>
                            <div class="flex space-x-2">
                                <button wire:click="editActivity('{{ $activityName }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded text-xs">Düzenle</button>
                                <button wire:click="deleteActivity('{{ $activityName }}')" onclick="return confirm('Bu faaliyeti silmek istediğinizden emin misiniz?');" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-xs">Sil</button>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>