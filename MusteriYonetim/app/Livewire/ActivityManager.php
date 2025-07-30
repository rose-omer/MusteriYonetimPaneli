<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\FaaliyetManager;

class ActivitiesManage extends Component
{
    use WithPagination;

    public $activityName = '';
    public $editingActivityOriginalName = '';
    public $editingActivityNewName = '';

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'activityName' => 'required|string|min:2|max:255',
        'editingActivityNewName' => 'required|string|min:2|max:255',
    ];

    public function render()
    {
        $manager = new FaaliyetManager();
        $activities = collect($manager->getFaaliyetler())->sort()->values();

        // manuel paginate için:
        $page = request()->get('page', 1);
        $perPage = 10;
        $items = $activities->slice(($page - 1) * $perPage, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $activities->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.activities-manage', [
            'activities' => $paginator,
        ]);
    }

    public function createActivity()
    {
        $this->validateOnly('activityName');
        $manager = new FaaliyetManager();
        if ($manager->addFaaliyet($this->activityName)) {
            session()->flash('activity_message', 'Faaliyet başarıyla eklendi.');
            $this->activityName = '';
            $this->resetPage();
        } else {
            session()->flash('activity_error', 'Faaliyet eklenemedi. Aynı isimde olabilir.');
        }
    }

    public function editActivity($name)
    {
        $this->editingActivityOriginalName = $name;
        $this->editingActivityNewName = $name;
    }

    public function cancelEdit()
    {
        $this->editingActivityOriginalName = '';
        $this->editingActivityNewName = '';
    }

    public function updateActivity()
    {
        $this->validateOnly('editingActivityNewName');

        $manager = new FaaliyetManager();

        if ($this->editingActivityOriginalName !== $this->editingActivityNewName) {
            $manager->removeFaaliyet($this->editingActivityOriginalName);
            $manager->addFaaliyet($this->editingActivityNewName);
            session()->flash('activity_message', 'Faaliyet başarıyla güncellendi.');
        }

        $this->cancelEdit();
        $this->resetPage();
    }

    public function deleteActivity($name)
    {
        $manager = new FaaliyetManager();
        if ($manager->removeFaaliyet($name)) {
            session()->flash('activity_message', 'Faaliyet silindi.');
            $this->resetPage();
        } else {
            session()->flash('activity_error', 'Faaliyet silinemedi.');
        }
    }
}
