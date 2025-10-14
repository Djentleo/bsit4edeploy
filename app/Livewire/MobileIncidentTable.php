<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Incident;

class MobileIncidentTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $typeFilter = '';
    public $statusFilter = '';
    public $showHidden = false;
    public $sortField = 'severity';
    public $sortDirection = 'asc';

    public $selectedIncidents = [];
    public $selectAll = false;

    protected $updatesQueryString = ['search', 'typeFilter', 'statusFilter', 'sortField', 'sortDirection', 'page'];

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

    public function toggleShowHidden()
    {
        $this->showHidden = !$this->showHidden;
        $this->selectedIncidents = [];
        $this->selectAll = false;
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

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Only select IDs of incidents currently displayed (after filters, sorting, and pagination)
            $incidents = $this->getCurrentIncidents();
            $this->selectedIncidents = collect($incidents->items())->pluck('id')->toArray();
        } else {
            $this->selectedIncidents = [];
        }
    }

    /**
     * Get the current paginated incidents as displayed in the table (after filters, sorting, and pagination)
     */
    protected function getCurrentIncidents()
    {
        $query = Incident::query()->where('source', 'mobile');
        if ($this->showHidden) {
            $query->where('hidden', true);
        } else {
            $query->where('hidden', false);
        }
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('firebase_id', 'like', $s)
                    ->orWhere('type', 'like', $s)
                    ->orWhere('location', 'like', $s)
                    ->orWhere('reporter_name', 'like', $s)
                    ->orWhere('department', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhere('incident_description', 'like', $s)
                    ->orWhere('timestamp', 'like', $s)
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%M') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%Y') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%d') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%M %d, %Y') LIKE ?", [$s]);
            });
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        // Custom severity order: critical > high > medium > low, then timestamp desc
        if ($this->sortField === 'severity') {
            $query->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low') " . ($this->sortDirection === 'asc' ? 'ASC' : 'DESC'));
            $query->orderBy('timestamp', 'desc');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        return $query->paginate($this->perPage);
    }

    public function updatedSelectedIncidents()
    {
        $this->selectAll = false;
    }

    public function hideSelected()
    {
        if (!empty($this->selectedIncidents)) {
            Incident::whereIn('id', $this->selectedIncidents)->update(['hidden' => true]);
            $this->selectedIncidents = [];
            $this->selectAll = false;
            session()->flash('status', 'Selected incidents have been hidden.');
        }
    }

    public function unhideSelected()
    {
        if (!empty($this->selectedIncidents)) {
            Incident::whereIn('id', $this->selectedIncidents)->update(['hidden' => false]);
            $this->selectedIncidents = [];
            $this->selectAll = false;
            session()->flash('status', 'Selected incidents have been unhidden.');
        }
    }

    public function render()
    {
        $query = Incident::query()->where('source', 'mobile');
        if ($this->showHidden) {
            $query->where('hidden', true);
        } else {
            $query->where('hidden', false);
        }
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('firebase_id', 'like', $s)
                    ->orWhere('type', 'like', $s)
                    ->orWhere('location', 'like', $s)
                    ->orWhere('reporter_name', 'like', $s)
                    ->orWhere('department', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhere('incident_description', 'like', $s)
                    ->orWhere('timestamp', 'like', $s)
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%M') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%Y') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%d') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%M %d, %Y') LIKE ?", [$s]);
            });
        }
        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        // Custom severity order: critical > high > medium > low, then timestamp desc
        if ($this->sortField === 'severity') {
            $query->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low') " . ($this->sortDirection === 'asc' ? 'ASC' : 'DESC'));
            $query->orderBy('timestamp', 'desc');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        $incidents = $query->paginate($this->perPage);
        $types = Incident::query()->where('source', 'mobile')->distinct()->pluck('type');
        return view('livewire.mobile-incident-table', [
            'incidents' => $incidents,
            'types' => $types,
        ]);
    }
}
