<?php

namespace App\Livewire;

use Livewire\Component;
use Kreait\Firebase\Factory;

class IncidentTable extends Component
{
    public $incidents;

    public function mount()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();

        $database = $firebase->getReference('incidents');

        $this->incidents = $database->getValue();
    }

    public function render()
    {
        return view('livewire.incident-table', [
            'incidents' => $this->incidents,
        ]);
    }
}
