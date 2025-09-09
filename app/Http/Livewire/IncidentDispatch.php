<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;

use App\Models\IncidentNote;
use Illuminate\Support\Facades\Auth;

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

    public function mount($incidentId)
    {
        $this->incidentId = $incidentId;
        // Only responders (role = 'responder')
        $this->allResponders = User::where('role', 'responder')->get();
        $this->additionalResponders = [];

    // Load notes for this incident
    $this->loadNotes();
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
    }

    public function render()
    {
    return view('livewire.incident-dispatch');
    }
}
