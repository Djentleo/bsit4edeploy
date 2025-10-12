<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;
use App\Models\IncidentNote;
use App\Models\IncidentTimeline;
use Illuminate\Support\Facades\Auth;
use App\Models\Incident;
use App\Services\FirebaseService;

class IncidentDispatch extends Component
{
    public $incidentId;
    public $mainResponder = '';
    public $additionalResponders = [];
    public $allResponders = [];
    public $successMessage = '';
    public $errorMessage = '';

    // Notes
    public $incidentNotes = [];
    public $newNote = '';

    // Status
    public $status = '';
    public $statusOptions = [
        'new' => 'New',
        'dispatched' => 'Dispatched',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];
    // Timeline
    public $timeline = [];

    public function mount($incidentId)
    {
        $this->incidentId = $incidentId;
        // Only responders (role = 'responder')
        $this->allResponders = User::where('role', 'responder')->get();

        // Load assigned responders from dispatches table
        $dispatches = Dispatch::where('incident_id', $incidentId)->get();
        if ($dispatches->count() > 0) {
            $this->mainResponder = $dispatches->first()->responder_id;
            $this->additionalResponders = $dispatches->skip(1)->pluck('responder_id')->toArray();
        } else {
            $this->mainResponder = '';
            $this->additionalResponders = [];
        }

        // Load notes for this incident
        $this->loadNotes();

        // Load current status from MySQL
        $this->loadStatus();

        // Load timeline
        $this->loadTimeline();
    }
    public function loadStatus()
    {
        $incident = Incident::where('firebase_id', $this->incidentId)
            ->orWhere('id', $this->incidentId)
            ->first();
        $this->status = $incident ? $incident->status : '';
    }

    public function loadTimeline()
    {
        // Always resolve to DB id for timeline consistency
        $incident = Incident::where('id', $this->incidentId)
            ->orWhere('firebase_id', $this->incidentId)
            ->first();
        $dbId = $incident ? $incident->id : $this->incidentId;
        $this->timeline = IncidentTimeline::where('incident_id', $dbId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus()
    {
        // Always save status as lowercase
        $status = strtolower($this->status);
        $incident = Incident::where('firebase_id', $this->incidentId)->orWhere('id', $this->incidentId)->first();
        if ($incident) {
            $incident->status = $status;
            // If resolved, set resolved_at timestamp
            $resolvedAt = null;
            if ($status === 'resolved') {
                $resolvedAt = now();
                $incident->resolved_at = $resolvedAt;
            }
            $incident->save();
            $this->successMessage = 'Status updated.';
        } else {
            $this->errorMessage = 'Incident not found.';
            return;
        }

        // Reflect status change in Firebase as well
        try {
            if (!empty($incident->firebase_id)) {
                $firebase = new FirebaseService();
                $firebase->updateIncidentStatus($incident->firebase_id, $status);
                // If resolved, also log to resolved_incidents for analytics
                if ($status === 'resolved') {
                    $firebase->logResolvedIncident($incident->firebase_id, $resolvedAt);
                }
            }
        } catch (\Throwable $e) {
            // Don't block UI on Firebase failure; surface a soft warning
            $this->errorMessage = 'Status updated locally, but failed to update Firebase: ' . $e->getMessage();
        }
        // Log to timeline
        $user = Auth::user();
        \App\Models\IncidentTimeline::create([
            'incident_id' => $incident->id,
            'user_id' => $user ? $user->id : null,
            'action' => 'status_changed',
            'details' => $this->status,
        ]);
        $this->loadTimeline();
    }

    public function addResponder()
    {
        $this->additionalResponders[] = '';
    }

    public function removeResponder($index)
    {
        unset($this->additionalResponders[$index]);
        $this->additionalResponders = array_values($this->additionalResponders);
    }

    public function dispatchIncident()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
        DB::beginTransaction();
        try {
            $responderIds = array_filter(array_merge([$this->mainResponder], $this->additionalResponders));
            if (empty($responderIds)) {
                $this->errorMessage = 'Please select at least one responder.';
                return;
            }
            foreach ($responderIds as $responderId) {
                Dispatch::updateOrCreate(
                    [
                        'incident_id' => $this->incidentId,
                        'responder_id' => $responderId,
                    ],
                    [
                        'status' => 'dispatched',
                    ]
                );
            }
            // Also update the incident status in the incidents table
            $incident = Incident::where('firebase_id', $this->incidentId)->orWhere('id', $this->incidentId)->first();
            if ($incident) {
                $incident->status = 'dispatched';
                $incident->save();
                // Reflect to Firebase as well
                try {
                    if (!empty($incident->firebase_id)) {
                        $firebase = new FirebaseService();
                        $firebase->updateIncidentStatus($incident->firebase_id, 'dispatched');
                    }
                } catch (\Throwable $e) {
                    // Soft warning only
                    $this->errorMessage = 'Dispatched locally, but failed to update Firebase: ' . $e->getMessage();
                }
            }
            DB::commit();
            $this->successMessage = 'Dispatch successful!';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Dispatch failed: ' . $e->getMessage();
        }
    }

    public function loadNotes()
    {
        // Always resolve to DB id for notes
        $incident = \App\Models\Incident::where('id', $this->incidentId)
            ->orWhere('firebase_id', $this->incidentId)
            ->first();
        $dbId = $incident ? $incident->id : $this->incidentId;
        $this->incidentNotes = IncidentNote::where('incident_id', $dbId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function addNote()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
        $noteText = trim($this->newNote);
        if ($noteText === '') {
            $this->errorMessage = 'Note cannot be empty.';
            return;
        }
        $user = Auth::user();
        if (!$user) {
            $this->errorMessage = 'You must be logged in to add a note.';
            return;
        }
        // Always resolve to DB id for notes
        $incident = \App\Models\Incident::where('id', $this->incidentId)
            ->orWhere('firebase_id', $this->incidentId)
            ->first();
        $dbId = $incident ? $incident->id : $this->incidentId;
        IncidentNote::create([
            'incident_id' => $dbId,
            'user_id' => $user->id,
            'note' => $noteText,
        ]);
        $this->newNote = '';
        $this->loadNotes();
        $this->successMessage = 'Note added.';

        // Log to timeline
        IncidentTimeline::create([
            'incident_id' => $dbId,
            'user_id' => $user->id,
            'action' => 'note_added',
            'details' => 'Note added',
        ]);
        $this->loadTimeline();
    }

    public function render()
    {
        return view('livewire.incident-dispatch');
    }

    public function pollUpdates()
    {
        $this->loadNotes();
        $this->loadStatus();
        $this->loadTimeline();
    }
}
