<?php

namespace App\Livewire\Responders; 

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Incident;
use App\Models\IncidentLog;
use Illuminate\Support\Facades\Auth;

class ResponderHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'timestamp';
    public $sortDirection = 'desc';

    protected $updatesQueryString = ['search', 'sortField', 'sortDirection', 'page'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

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
        $user = Auth::user();
        $query = Incident::query();
        // Only show resolved incidents assigned to this responder (via dispatches)
        if ($user && isset($user->id)) {
            $query->where('status', 'resolved')
                ->whereIn('firebase_id', function($sub) use ($user) {
                    $sub->select('incident_id')
                        ->from('dispatches')
                        ->where('responder_id', $user->id);
                });
        } else {
            $query->whereRaw('0=1');
        }
        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('firebase_id', 'like', $s)
                    ->orWhere('type', 'like', $s)
                    ->orWhere('location', 'like', $s)
                    ->orWhere('status', 'like', $s)
                    ->orWhere('incident_description', 'like', $s)
                    ->orWhere('timestamp', 'like', $s)
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%M') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%Y') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%d') LIKE ?", [$s])
                    ->orWhereRaw("DATE_FORMAT(timestamp, '%M %d, %Y') LIKE ?", [$s]);
            });
        }
        $incidents = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        return view('livewire.responder-history', [
            'incidents' => $incidents,
        ]);
    }
}
