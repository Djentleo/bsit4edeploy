?php

namespace App\Http\Livewire;

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
        $this->incidents = $database->collection('incidents')->documents()->map(function ($doc) {
            return $doc->data();
        });
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
