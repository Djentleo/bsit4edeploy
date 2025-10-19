<?php

namespace App\Livewire\Responders;

use Livewire\Component;
use Livewire\Attributes\Layout as LivewireLayout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
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
    ];
    public $incidentNotes = [];
    public $newNote = '';
    public $timeline = [];
    public $dispatchId;
    public $readOnly = false;

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
        // Set read-only mode ONLY when coming from history/logs
        $this->readOnly = request()->query('from') === 'logs';
        // Always use DB id for notes/timeline
        $dbId = $incident->id;
        $this->loadNotes($dbId);
        $this->loadTimeline($dbId);
    }

    public function updateStatus($status = null)
    {
        // In read-only mode, silently ignore updates (no inline alert)
        if ($this->readOnly) {
            return;
        }
        // Use the passed parameter or the selectedStatus property
        $newStatus = $status ?? $this->selectedStatus;

        // Basic allowlist validation
        if (! array_key_exists($newStatus, $this->statusOptions)) {
            $this->addError('status', 'Invalid status selected.');
            return;
        }

        $dispatch = Dispatch::find($this->dispatchId);
        if ($dispatch && $dispatch->responder_id === Auth::id()) {
            $previousStatus = $dispatch->status;
            $dispatch->status = $newStatus;
            $dispatch->save();
            $this->status = $newStatus;
            $this->selectedStatus = $newStatus; // Keep dropdown in sync
            // Incident status logic (same as dashboard)
            $incident = Incident::where('id', $dispatch->incident_id)
                ->orWhere('firebase_id', $dispatch->incident_id)
                ->first();
            $shouldUpdateFirebase = false;
            // If responder sets status to dispatched or en_route, update main incident status as well
            if (($newStatus === 'dispatched' || $newStatus === 'en_route') && $incident) {
                // Remove resolved incident record from Firebase and MySQL if previous status was resolved
                if ($previousStatus === 'resolved' && $incident->firebase_id) {
                    try {
                        $firebase = new FirebaseService();
                        $firebase->removeResolvedIncident($incident->firebase_id);
                    } catch (\Throwable $e) {
                        // Optionally log error
                    }
                    // Remove from MySQL incident_logs (match both DB id and Firebase id)
                    \App\Models\IncidentLog::where(function ($q) use ($incident) {
                        $q->where('incident_id', $incident->id)
                            ->orWhere('incident_id', $incident->firebase_id);
                    })
                        ->where('status', 'resolved')
                        ->delete();
                }
                $incident->status = $newStatus;
                $incident->save();
                // Also update Firebase status
                if ($incident->firebase_id) {
                    try {
                        $firebase = new FirebaseService();
                        $firebase->updateIncidentStatus($incident->firebase_id, $newStatus);
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

            // Notify all admins about status change
            try {
                $admins = \App\Models\User::where('role', 'admin')->get();
                if ($admins->count() > 0 && $incident) {
                    $msg = 'Responder changed status to ' . ucfirst($newStatus) . ' for incident ' . ($incident->type ?? '') . ' at ' . ($incident->location ?? '');
                    // Use relative link so it respects subfolder deployments
                    $link = 'dispatch?incident_id=' . ($incident->firebase_id ?? $incident->id);
                    Notification::send($admins, new \App\Notifications\AdminIncidentNotification(
                        'status',
                        ($incident->firebase_id ?? $incident->id),
                        $msg,
                        $link,
                        ['status' => $newStatus, 'by' => Auth::user()->name ?? 'Responder']
                    ));
                }
            } catch (\Throwable $e) {
                // Optionally log error
                Log::error('Failed to send responder status notification', ['error' => $e->getMessage()]);
            }

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
        // In read-only mode, silently ignore note submissions (no inline alert)
        if ($this->readOnly) {
            return;
        }
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

            // Notify all admins about new note
            try {
                $incident = \App\Models\Incident::find($incidentId);
                $admins = \App\Models\User::where('role', 'admin')->get();
                if ($admins->count() > 0 && $incident) {
                    $msg = 'Responder added a note to incident ' . ($incident->type ?? '') . ' at ' . ($incident->location ?? '') . ': "' . $noteText . '"';
                    // Use relative link so it respects subfolder deployments
                    $link = 'dispatch?incident_id=' . ($incident->firebase_id ?? $incident->id);
                    Notification::send($admins, new \App\Notifications\AdminIncidentNotification(
                        'note',
                        ($incident->firebase_id ?? $incident->id),
                        $msg,
                        $link,
                        ['note' => $noteText, 'by' => $user->name]
                    ));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send responder note notification', ['error' => $e->getMessage()]);
            }
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

    public function pollUpdates()
    {
        // Always reload from DB for real-time updates
        $incidentId = $this->incident['id'] ?? null;
        if ($incidentId) {
            $incident = Incident::where('id', $incidentId)
                ->orWhere('firebase_id', $incidentId)
                ->first();
            if ($incident) {
                $this->incident = $incident->toArray();
                $this->status = $incident->status;
                $this->loadNotes($incident->id);
                $this->loadTimeline($incident->id);
                // Do NOT reset $readOnly here; only set on mount
            }
        }
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
