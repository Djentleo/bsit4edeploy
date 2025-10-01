<?php

namespace App\Livewire\Responders;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispatch;
use App\Models\Incident;
use App\Models\IncidentNote;
use App\Models\IncidentTimeline;
use App\Services\FirebaseService;

#[LivewireLayout('layouts.app')]
class IncidentDetails extends Component
{
    public $incident;
    public $status;
    public $statusOptions = [
        'dispatched' => 'Dispatched',
        'en_route' => 'En Route',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];
    public $incidentNotes = [];
    public $newNote = '';
    public $timeline = [];
    public $dispatchId;

    public function mount($dispatchId)
    {
        $dispatch = Dispatch::findOrFail($dispatchId);
        $incident = Incident::where('id', $dispatch->incident_id)
            ->orWhere('firebase_id', $dispatch->incident_id)
            ->firstOrFail();
        $this->incident = $incident->toArray();
        $this->status = $dispatch->status;
        $this->dispatchId = $dispatch->id;
        // Always use DB id for notes/timeline
        $dbId = $incident->id;
        $this->loadNotes($dbId);
        $this->loadTimeline($dbId);
    }

    public function updateStatus($status)
    {
        // Basic allowlist validation
        if (! array_key_exists($status, $this->statusOptions)) {
            $this->addError('status', 'Invalid status selected.');
            return;
        }

        $dispatch = Dispatch::find($this->dispatchId);
        if ($dispatch && $dispatch->responder_id === Auth::id()) {
            $dispatch->status = $status;
            $dispatch->save();
            $this->status = $status;
            // Incident status logic (same as dashboard)
            $incident = Incident::where('id', $dispatch->incident_id)
                ->orWhere('firebase_id', $dispatch->incident_id)
                ->first();
            $shouldUpdateFirebase = false;
            if ($status === 'resolved') {
                if ($incident) {
                    $dispatchCount = Dispatch::where(function ($q) use ($dispatch) {
                        $q->where('incident_id', $dispatch->incident_id);
                    })->count();
                    if ($dispatchCount <= 1) {
                        $incident->status = 'resolved';
                        $incident->save();
                        $shouldUpdateFirebase = true;
                    } else {
                        $allResolved = Dispatch::where(function ($q) use ($dispatch) {
                            $q->where('incident_id', $dispatch->incident_id);
                        })
                            ->where('status', '!=', 'resolved')
                            ->count() === 0;
                        if ($allResolved) {
                            $incident->status = 'resolved';
                            $incident->save();
                            $shouldUpdateFirebase = true;
                        }
                    }
                    // Always log to incident_logs if resolved
                    if ($incident->status === 'resolved' && $incident->firebase_id) {
                        try {
                            $firebase = new FirebaseService();
                            $firebase->logResolvedIncident($incident->firebase_id);
                        } catch (\Throwable $e) {
                            // Optionally log error
                        }
                    }
                }
            }
            // Always reload the incident from the database after any status change
            $incident = Incident::where('id', $dispatch->incident_id)
                ->orWhere('firebase_id', $dispatch->incident_id)
                ->first();
            if ($incident) {
                $this->incident = $incident->toArray();
                // Sync status to Firebase if incident status is resolved
                if ($shouldUpdateFirebase && $incident->firebase_id) {
                    try {
                        $firebase = new FirebaseService();
                        $firebase->updateIncidentStatus($incident->firebase_id, 'resolved');
                    } catch (\Throwable $e) {
                        // Optionally log error
                    }
                }
            }
        } else {
            $this->addError('status', 'You are not authorized to update this dispatch status.');
        }
    }

    // React to dropdown changes automatically when bound with wire:model
    public function updatedStatus($value)
    {
        $this->updateStatus($value);
    }

    public function loadNotes($incidentId)
    {
        $notes = IncidentNote::where('incident_id', $incidentId)
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
        $incidentId = $this->incident['id'] ?? null;
        $user = Auth::user();
        $noteText = trim($this->newNote);
        if ($incidentId && $user && $noteText !== '') {
            // Always use DB id for notes
            IncidentNote::create([
                'incident_id' => $incidentId,
                'user_id' => $user->id,
                'note' => $noteText,
            ]);
            $this->newNote = '';
            $this->loadNotes($incidentId);
        }
    }

    public function loadTimeline($incidentId)
    {
        $timeline = IncidentTimeline::where('incident_id', $incidentId)
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
        return view('responders.incident-details', [
            'incident' => $this->incident,
            'status' => $this->status,
            'statusOptions' => $this->statusOptions,
            'incidentNotes' => $this->incidentNotes,
            'timeline' => $this->timeline,
        ]);
    }
}
