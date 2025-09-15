<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;
use App\Models\IncidentNote;
use App\Models\IncidentTimeline;
use Illuminate\Support\Facades\Auth;
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
    public $notes = [];
    public $newNote = '';

    // Status
    public $status = '';
    public $statusOptions = [
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
        $this->additionalResponders = [];

        // Load notes for this incident
        $this->loadNotes();

        // Load current status from Firebase
        $this->loadStatus();

        // Load timeline
        $this->loadTimeline();
    }
    public function loadStatus()
    {
        $firebase = app(FirebaseService::class);
        $incident = $firebase->getIncidentById($this->incidentId);
        $this->status = $incident['status'] ?? '';
    }

    public function loadTimeline()
    {
        $this->timeline = IncidentTimeline::where('incident_id', $this->incidentId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus()
    {
        $firebase = app(FirebaseService::class);
        if ($this->status === 'resolved') {
            $firebase->moveToResolvedAndDelete($this->incidentId);
            $this->successMessage = 'Incident moved to resolved incidents.';
            // Optionally clear UI state
            $this->status = '';
            $this->notes = [];
            $this->timeline = [];
            // Optionally, you could redirect or emit an event to close the modal/page
            return;
        } else {
            $firebase->updateIncidentStatus($this->incidentId, $this->status);
            $this->successMessage = 'Status updated.';
            $this->loadStatus(); // Refresh status from Firebase
            // Log to timeline
            $user = Auth::user();
            IncidentTimeline::create([
                'incident_id' => $this->incidentId,
                'user_id' => $user ? $user->id : null,
                'action' => 'status_changed',
                'details' => $this->status,
            ]);
            $this->loadTimeline();
        }
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
            DB::commit();
            $this->successMessage = 'Dispatch successful!';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Dispatch failed: ' . $e->getMessage();
        }
    }

    public function loadNotes()
    {
        $this->notes = IncidentNote::where('incident_id', $this->incidentId)
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
        IncidentNote::create([
            'incident_id' => $this->incidentId,
            'user_id' => $user->id,
            'note' => $noteText,
        ]);
        $this->newNote = '';
        $this->loadNotes();
        $this->successMessage = 'Note added.';

        // Log to timeline
        IncidentTimeline::create([
            'incident_id' => $this->incidentId,
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
}
