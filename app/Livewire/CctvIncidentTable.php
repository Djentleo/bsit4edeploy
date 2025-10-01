<?php

namespace App\Livewire;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Incident;

class CctvIncidentTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $typeFilter = '';
    public $sortField = 'timestamp';
    public $sortDirection = 'desc';

    protected $updatesQueryString = ['search', 'typeFilter', 'sortField', 'sortDirection', 'page'];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingTypeFilter()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Incident::query()->where('source', 'cctv');
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('firebase_id', 'like', $s)
                    ->orWhere('type', 'like', $s)
                    ->orWhere('location', 'like', $s)
                    ->orWhere('department', 'like', $s)
                    ->orWhere('status', 'like', $s);
            });
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        $incidents = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        $types = Incident::query()->where('source', 'cctv')->distinct()->pluck('type');
        return view('livewire.cctv-incident-table', [
            'incidents' => $incidents,
            'types' => $types,
        ]);
    }
}
