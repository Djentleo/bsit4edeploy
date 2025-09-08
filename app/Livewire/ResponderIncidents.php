<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispatch;
use App\Services\FirebaseService;

class ResponderIncidents extends Component
{
    public $incidents = [];

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

    public function render()
    {
        return view('livewire.responder-incidents', [
            'incidents' => $this->incidents,
        ]);
    }
}
