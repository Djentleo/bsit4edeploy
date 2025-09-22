<?php

namespace App\Livewire;

use Livewire\Component;
use Kreait\Firebase\Factory;

class IncidentTable extends Component
{
    public $search = '';
    public $incidents = [];

    public function mount()
    {
        $firebase = (new Factory)->withServiceAccount(config('firebase.credentials'))->createFirestore();
        $database = $firebase->database();
        $documents = $database->collection('incidents')->documents();
        $this->incidents = [];
        foreach ($documents as $doc) {
            $this->incidents[] = $doc->data();
        }
    }

    public function render()
    {
        $filteredIncidents = collect($this->incidents)->filter(function ($incident) {
            return str_contains(strtolower($incident['type']), strtolower($this->search)) ||
                   str_contains(strtolower($incident['location']), strtolower($this->search));
        });

        return view('livewire.incident-table', ['incidents' => $filteredIncidents]);
    }
}
