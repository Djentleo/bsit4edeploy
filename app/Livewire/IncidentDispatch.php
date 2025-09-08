<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;

class IncidentDispatch extends Component
{
    public $incidentId;
    public $mainResponder = '';
    public $additionalResponders = [];
    public $allResponders = [];
    public $successMessage = '';
    public $errorMessage = '';

    public function mount($incidentId)
    {
        $this->incidentId = $incidentId;
        // Only responders (role = 'responder')
        $this->allResponders = User::where('role', 'responder')->get();
        $this->additionalResponders = [];
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

    public function render()
    {
        return view('livewire.incident-dispatch');
    }
}
