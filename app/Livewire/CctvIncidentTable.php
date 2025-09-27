<?php

namespace App\Livewire;


use Livewire\Component;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class CctvIncidentTable extends Component
{
    public $incidents;
    public $search = '';
    public $filter = '';

    protected $queryString = ['filter'];

    public function mount()
    {
        $firebase = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();

        $reference = $firebase->getReference('incidents');
        $data = $reference->getValue();

        // Debug: log raw Firebase data structure
        try {
            Log::debug('CctvIncidentTable.raw_firebase', [
                'data_type' => gettype($data),
                'data_keys' => is_array($data) ? array_keys($data) : null,
                'data_sample' => is_array($data) ? array_slice($data, 0, 3) : $data,
            ]);
        } catch (\Exception $e) {
        }

        // Convert Firebase data to numerically-indexed array
        if (is_array($data)) {
            $incidentsArr = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value['firebase_id'] = $key;
                    // Ensure status is set to 'new' if missing or N/A
                    if (!isset($value['status']) || $value['status'] === 'N/A') {
                        $value['status'] = 'new';
                        // Update Firebase so status is reflected in DB
                        try {
                            $reference->getChild($key)->update(['status' => 'new']);
                        } catch (\Exception $e) {
                        }
                    }
                    $incidentsArr[] = $value;
                }
            }
        } elseif ($data instanceof \Traversable) {
            $incidentsArr = array_values(iterator_to_array($data));
        } else {
            $incidentsArr = [];
        }

        $this->incidents = collect($incidentsArr);
    }

    public function render()
    {
        $incidents = $this->incidents instanceof \Illuminate\Support\Collection
            ? $this->incidents
            : collect($this->incidents ?? []);

        $search = trim((string) $this->search);

        if ($search !== '') {
            $s = strtolower($search);
            $filtered = $incidents->filter(function ($incident) use ($s) {
                $incidentArr = is_array($incident) ? $incident : (array) $incident;
                $hay = collect($incidentArr)->implode(' ');
                return stripos($hay, $s) !== false;
            })->sortByDesc(function ($incident) {
                return $incident['timestamp'] ?? '';
            });
        } else {
            $filtered = $incidents->sortByDesc(function ($incident) {
                return $incident['timestamp'] ?? '';
            });
        }

        $displayIncidents = $filtered->values()->all();

        $incidentsForClient = collect($incidents)->map(function ($incident) {
            $incidentArr = is_array($incident) ? $incident : (array) $incident;
            try {
                $incidentArr['timestamp_formatted'] = isset($incidentArr['timestamp']) && $incidentArr['timestamp']
                    ? $incidentArr['timestamp']
                    : '';
            } catch (\Exception $e) {
                $incidentArr['timestamp_formatted'] = (string) ($incidentArr['timestamp'] ?? '');
            }
            return $incidentArr;
        })->values()->all();

        return view('livewire.cctv-incident-table', [
            'displayIncidents' => $displayIncidents,
            'incidents' => $incidentsForClient,
        ]);
    }
}
