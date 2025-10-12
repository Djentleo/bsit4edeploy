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
    public $selectedStatus; // New property for the dropdown
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
        $this->selectedStatus = $dispatch->status; // Initialize dropdown with current status
        $this->dispatchId = $dispatch->id;
        // Always use DB id for notes/timeline
        $dbId = $incident->id;
        $this->loadNotes($dbId);
        $this->loadTimeline($dbId);
    }

    public function updateStatus($status = null)
    {
        // Use the passed parameter or the selectedStatus property
        $newStatus = $status ?? $this->selectedStatus;

        // Basic allowlist validation
        if (! array_key_exists($newStatus, $this->statusOptions)) {
            $this->addError('status', 'Invalid status selected.');
            return;
        }

        $dispatch = Dispatch::find($this->dispatchId);
        if ($dispatch && $dispatch->responder_id === Auth::id()) {
            $dispatch->status = $newStatus;
            $dispatch->save();
            $this->status = $newStatus;
            $this->selectedStatus = $newStatus; // Keep dropdown in sync
            // Incident status logic (same as dashboard)
            $incident = Incident::where('id', $dispatch->incident_id)
                ->orWhere('firebase_id', $dispatch->incident_id)
                ->first();
            $shouldUpdateFirebase = false;
            // If responder sets status to en_route, update main incident status as well
            if ($newStatus === 'en_route' && $incident) {
                $incident->status = 'en_route';
                $incident->save();
                // Also update Firebase status
                if ($incident->firebase_id) {
                    try {
                        $firebase = new FirebaseService();
                        $firebase->updateIncidentStatus($incident->firebase_id, 'en_route');
                    } catch (\Throwable $e) {
                        // Optionally log error
                    }
                }
            }

            if ($newStatus === 'resolved') {
                if ($incident) {
                    $dispatchCount = Dispatch::where(function ($q) use ($dispatch) {
                        $q->where('incident_id', $dispatch->incident_id);
                    })->count();
                    $resolvedAt = null;
                    if ($dispatchCount <= 1) {
                        $resolvedAt = now();
                        $incident->status = 'resolved';
                        $incident->resolved_at = $resolvedAt;
                        $incident->save();
                        $shouldUpdateFirebase = true;
                    } else {
                        $allResolved = Dispatch::where(function ($q) use ($dispatch) {
                            $q->where('incident_id', $dispatch->incident_id);
                        })
                            ->where('status', '!=', 'resolved')
                            ->count() === 0;
                        if ($allResolved) {
                            $resolvedAt = now();
                            $incident->status = 'resolved';
                            $incident->resolved_at = $resolvedAt;
                            $incident->save();
                            $shouldUpdateFirebase = true;
                        }
                    }
                    // Always log to incident_logs if resolved
                    if ($incident->status === 'resolved' && $incident->firebase_id) {
                        try {
                            $firebase = new FirebaseService();
                            $firebase->logResolvedIncident($incident->firebase_id, $resolvedAt);
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

            // Log responder status change to timeline
            IncidentTimeline::create([
                'incident_id' => $incident ? $incident->id : null,
                'user_id' => Auth::id(),
                'action' => 'responder_status_changed',
                'details' => $newStatus,
            ]);

            // Show success message
            session()->flash('status', 'Status updated successfully to: ' . $this->statusOptions[$newStatus]);
        } else {
            $this->addError('status', 'You are not authorized to update this dispatch status.');
        }
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
