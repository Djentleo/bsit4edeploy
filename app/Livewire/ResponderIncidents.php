<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispatch;
use App\Services\FirebaseService;

class ResponderIncidents extends Component
{
    public $incidents = [];
    public $showModal = false;
    public $selectedIncident = null;
    public $incidentNotes = [];
    public $incidentStatus = '';
    public $statusOptions = [
        'dispatched' => 'Dispatched',
        'en_route' => 'En Route',
        'on_scene' => 'On Scene',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];
    public $newNote = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->incidents = [];
            return;
        }

        // Get all dispatches for this responder
        $dispatches = Dispatch::where('responder_id', $user->id)->get();
        $incidentIds = $dispatches->pluck('incident_id')->unique()->toArray();

        // Fetch incident details from Firebase
        $firebase = app(FirebaseService::class);
        $incidents = [];
        foreach ($incidentIds as $incidentId) {
            $incident = $firebase->getIncidentById($incidentId);
            if ($incident) {
                $incidents[] = $incident;
            }
        }
        $this->incidents = $incidents;
    }

    public function showIncident($key)
    {
        $incident = $this->incidents[$key] ?? null;
        if (!$incident) return;
        $this->selectedIncident = $incident;
        $this->incidentStatus = $incident['status'] ?? '';
        $this->loadNotes($incident['incident_id'] ?? null);
        $this->showModal = true;
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

    public function updateStatus()
    {
        if (!$this->selectedIncident) return;
        $incidentId = $this->selectedIncident['incident_id'] ?? null;
        if (!$incidentId) return;
        $firebase = app(FirebaseService::class);
        $firebase->updateIncidentStatus($incidentId, $this->incidentStatus);
        // Update local state
        $this->selectedIncident['status'] = $this->incidentStatus;
        // Optionally reload incidents
        $this->mount();
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

    public function render()
    {
        return view('livewire.responder-incidents', [
            'incidents' => $this->incidents,
            'showModal' => $this->showModal,
            'selectedIncident' => $this->selectedIncident,
            'incidentNotes' => $this->incidentNotes,
            'incidentStatus' => $this->incidentStatus,
            'statusOptions' => $this->statusOptions,
            'newNote' => $this->newNote,
        ]);
    }
}
