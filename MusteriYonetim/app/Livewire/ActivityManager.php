<?php

namespace App\Livewire;

use App\Services\FaaliyetManager; // Bu satırın olduğundan emin olun!
use Livewire\Component;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ActivityManager extends Component
{
    public $editingActivityOriginalName = '';
    public $editingActivityNewName = '';
    public $activityName = '';

    // ÖNEMLİ DEĞİŞİKLİK: FaaliyetManager özelliğini artık bir sınıf özelliği olarak tutmuyoruz.
    // protected $faaliyetManager; // Bu satırı kaldırın veya yorum satırı yapın.

    // ÖNEMLİ DEĞİŞİKLİK: Constructor metodunu kaldırıyoruz.
    // Public function __construct(FaaliyetManager $faaliyetManager) { ... } // Bu metodu tamamen kaldırın.

    // Doğrulama kuralları
    protected function rules()
    {
        return [
            'activityName' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // ÖNEMLİ DÜZELTME: Doğrulama closure'ı içinde FaaliyetManager'ı doğrudan çözüyoruz.
                    $faaliyetManager = app(FaaliyetManager::class);
                    if (in_array($value, $faaliyetManager->getFaaliyetler())) {
                        $fail('Bu faaliyet adı zaten mevcut.');
                    }
                },
            ],
            'editingActivityNewName' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Aynı şekilde, düzenleme için de closure içinde doğrudan çözüyoruz.
                    $faaliyetManager = app(FaaliyetManager::class);
                    if ($value !== $this->editingActivityOriginalName && in_array($value, $faaliyetManager->getFaaliyetler())) {
                        $fail('Bu faaliyet adı zaten mevcut.');
                    }
                },
            ],
        ];
    }

    // Validasyon mesajları (AYNI KALACAK)
    protected $messages = [
        'activityName.required' => 'Faaliyet adı boş bırakılamaz.',
        'editingActivityNewName.required' => 'Faaliyet adı boş bırakılamaz.',
    ];


    public function createActivity()
    {
        // ÖNEMLİ DÜZELTME: FaaliyetManager'ı bu metod içinde doğrudan çözüyoruz.
        $faaliyetManager = app(FaaliyetManager::class);

        $this->validateOnly('activityName');

        if ($faaliyetManager->addFaaliyet($this->activityName)) {
            $this->activityName = '';
            session()->flash('activity_message', 'Faaliyet başarıyla eklendi!');
        } else {
            session()->flash('activity_error', 'Faaliyet eklenirken bir sorun oluştu veya zaten mevcut.');
        }
    }

    public function editActivity(string $activityName)
    {
        $this->editingActivityOriginalName = $activityName;
        $this->editingActivityNewName = $activityName;
    }

    public function updateActivity()
    {
        // ÖNEMLİ DÜZELTME: FaaliyetManager'ı bu metod içinde doğrudan çözüyoruz.
        $faaliyetManager = app(FaaliyetManager::class);

        $this->validateOnly('editingActivityNewName');

        if ($this->editingActivityOriginalName !== $this->editingActivityNewName) {
            $faaliyetManager->removeFaaliyet($this->editingActivityOriginalName);
            if ($faaliyetManager->addFaaliyet($this->editingActivityNewName)) {
                session()->flash('activity_message', 'Faaliyet başarıyla güncellendi!');
            } else {
                $faaliyetManager->addFaaliyet($this->editingActivityOriginalName);
                session()->flash('activity_error', 'Faaliyet güncellenirken bir sorun oluştu veya yeni ad zaten mevcut.');
            }
        } else {
            session()->flash('activity_message', 'Faaliyet adı değişmedi.');
        }

        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->editingActivityOriginalName = '';
        $this->editingActivityNewName = '';
    }

    public function deleteActivity(string $faaliyet)
    {
        // ÖNEMLİ DÜZELTME: FaaliyetManager'ı bu metod içinde doğrudan çözüyoruz.
        $faaliyetManager = app(FaaliyetManager::class);

        if ($faaliyetManager->removeFaaliyet($faaliyet)) {
            session()->flash('activity_message', 'Faaliyet başarıyla silindi!');
        } else {
            session()->flash('activity_error', 'Faaliyet silinirken bir sorun oluştu.');
        }
    }

    public function render()
    {
        // ÖNEMLİ DÜZELTME: FaaliyetManager'ı bu metod içinde doğrudan çözüyoruz.
        $faaliyetManager = app(FaaliyetManager::class);
        $allActivities = $faaliyetManager->getFaaliyetler();

        $perPage = 5;
        $currentPage = Paginator::resolveCurrentPage() ?: 1;

        $pagedData = array_slice($allActivities, ($currentPage - 1) * $perPage, $perPage);

        $activities = new LengthAwarePaginator(
            $pagedData,
            count($allActivities),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('livewire.activity-manager', compact('activities'));
    }
}