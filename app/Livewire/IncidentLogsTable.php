<?php

namespace App\Livewire;



use Livewire\Component;
use Livewire\WithPagination;
use App\Models\IncidentLog;


class IncidentLogsTable extends Component
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
        $query = IncidentLog::query();
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('incident_id', 'like', $s)
                    ->orWhere('type', 'like', $s)
                    ->orWhere('location', 'like', $s)
                    ->orWhere('reporter_name', 'like', $s)
                    ->orWhere('department', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhere('incident_description', 'like', $s);
            });
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        $logs = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        $types = IncidentLog::query()->distinct()->pluck('type');
        return view('livewire.incident-logs-table', [
            'logs' => $logs,
            'types' => $types,
        ]);
    }
}
