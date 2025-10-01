<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispatch;
use App\Models\Incident;

class ResponderDashboard extends Component
{
    public static string $layout = 'layouts.app';
    public $incidents = [];
    public $search = '';
    public $filterStatus = '';
    public $filterType = '';
    public $perPage = 10;
    public $page = 1;
    public $total = 0;

    // Livewire hooks for updating search/filter/pagination
    public function updatingSearch()
    {
        $this->page = 1;
    }
    public function updatingFilterStatus()
    {
        $this->page = 1;
    }
    public function updatingFilterType()
    {
        $this->page = 1;
    }
    public function updatedPage()
    {
        $this->fetchIncidents();
    }
    public function nextPage()
    {
        if ($this->page * $this->perPage < $this->total) {
            $this->page++;
            $this->fetchIncidents();
        }
    }
    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
            $this->fetchIncidents();
        }
    }
    public $statusOptions = [
        'dispatched' => 'Dispatched',
        'en_route' => 'En Route',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];
    public $showModal = false;
    public $selectedIncident = null;
    public $incidentNotes = [];
    public $newNote = '';
    public $timeline = [];

    public function mount()
    {
        $this->fetchIncidents();
    }

    public function fetchIncidents()
    {
        $user = Auth::user();
        if (!$user) {
            $this->incidents = [];
            return;
        }
        $dispatches = Dispatch::where('responder_id', $user->id)->get();
        $incidents = $dispatches->map(function ($dispatch) {
            $incident = Incident::where('id', $dispatch->incident_id)
                ->orWhere('firebase_id', $dispatch->incident_id)
                ->first();
            return [
                'dispatch_id' => $dispatch->id,
                'incident_id' => $dispatch->incident_id,
                'status' => $dispatch->status,
                'incident' => $incident ? $incident->toArray() : null,
            ];
        });

        // Search and filter
        $filtered = $incidents->filter(function ($item) {
            $incident = $item['incident'];
            if (!$incident) return false;
            $matchesSearch = true;
            if ($this->search) {
                $search = strtolower($this->search);
                $matchesSearch = (
                    (isset($incident['type']) && str_contains(strtolower($incident['type']), $search)) ||
                    (isset($incident['location']) && str_contains(strtolower($incident['location']), $search)) ||
                    (isset($incident['reporter_name']) && str_contains(strtolower($incident['reporter_name']), $search))
                );
            }
            $matchesStatus = $this->filterStatus ? $item['status'] === $this->filterStatus : true;
            $matchesType = $this->filterType ? (isset($incident['type']) && $incident['type'] === $this->filterType) : true;
            return $matchesSearch && $matchesStatus && $matchesType;
        })->values();

        // Pagination
        $this->total = $filtered->count();
        $this->incidents = $filtered->forPage($this->page, $this->perPage)->values()->toArray();
    }

    public function updateStatus($dispatchId, $status)
    {
        $dispatch = Dispatch::find($dispatchId);
        if ($dispatch && $dispatch->responder_id === Auth::id()) {
            $dispatch->status = $status;
            $dispatch->save();
            // If resolved, check if all responders are resolved or only one responder is assigned
            if ($status === 'resolved') {
                // Find the incident by id or firebase_id
                $incident = \App\Models\Incident::where('id', $dispatch->incident_id)
                    ->orWhere('firebase_id', $dispatch->incident_id)
                    ->first();
                if ($incident) {
                    $dispatchCount = Dispatch::where(function ($q) use ($dispatch) {
                        $q->where('incident_id', $dispatch->incident_id);
                    })->count();
                    if ($dispatchCount <= 1) {
                        // Only one responder assigned, resolve immediately
                        $incident->status = 'resolved';
                        $incident->save();
                    } else {
                        // Multiple responders: check if all are resolved
                        $allResolved = Dispatch::where(function ($q) use ($dispatch) {
                            $q->where('incident_id', $dispatch->incident_id);
                        })
                            ->where('status', '!=', 'resolved')
                            ->count() === 0;
                        if ($allResolved) {
                            $incident->status = 'resolved';
                            $incident->save();
                        }
                    }
                }
            }
            $this->fetchIncidents();
            if ($this->selectedIncident && $this->selectedIncident['dispatch_id'] == $dispatchId) {
                $this->selectedIncident['status'] = $status;
            }
        }
    }

    public function showIncident($dispatchId)
    {
        $incident = collect($this->incidents)->firstWhere('dispatch_id', $dispatchId);
        if (!$incident) return;
        $this->selectedIncident = $incident;
        $this->loadNotes($incident['incident_id']);
        $this->loadTimeline($incident['incident_id']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedIncident = null;
        $this->incidentNotes = [];
        $this->timeline = [];
    }

    public function loadNotes($incidentId)
    {
        $notes = \App\Models\IncidentNote::where('incident_id', $incidentId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user_name' => $note->user->name ?? 'Unknown',
                    'created_at' => $note->created_at->diffForHumans(),
                ];
            })->toArray();
        $this->incidentNotes = $notes;
    }

    public function addNote()
    {
        if (!$this->selectedIncident) return;
        $incidentId = $this->selectedIncident['incident_id'] ?? null;
        if (!$incidentId) return;
        $user = Auth::user();
        if (!$user) return;
        $noteText = trim($this->newNote);
        if ($noteText === '') return;
        \App\Models\IncidentNote::create([
            'incident_id' => $incidentId,
            'user_id' => $user->id,
            'note' => $noteText,
        ]);
        $this->newNote = '';
        $this->loadNotes($incidentId);
    }

    public function loadTimeline($incidentId)
    {
        $timeline = \App\Models\IncidentTimeline::where('incident_id', $incidentId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'action' => $item->action,
                    'details' => $item->details,
                    'user_name' => $item->user->name ?? 'Unknown',
                    'created_at' => $item->created_at->diffForHumans(),
                ];
            })->toArray();
        $this->timeline = $timeline;
    }

    public function render()
    {
        return view('livewire.responder-dashboard', [
            'incidents' => $this->incidents,
            'statusOptions' => $this->statusOptions,
        ]);
    }
}
