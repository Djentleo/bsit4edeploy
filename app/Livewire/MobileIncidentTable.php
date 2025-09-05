<?php

namespace App\Livewire;

use Livewire\Component;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MobileIncidentTable extends Component
{
    // Map display filter values to data values
    protected $typeMap = [
        'Vehicular accident' => 'vehicle_crash',
        'Fire' => 'fire',
        'Medical emergency' => 'medical_emergency',
        'Disturbance' => 'disturbance',
    ];

    public $incidents;
    public $search = '';
    public $filter = '';

    // Persist only the filter in the URL. Avoid persisting search on every keystroke.
    protected $queryString = ['filter'];

    public function mount()
    {
        $firebase = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();

        $reference = $firebase->getReference('mobile_incidents');
        $data = $reference->getValue();

        // Debug: log raw Firebase data structure
        try {
            Log::debug('MobileIncidentTable.raw_firebase', [
                'data_type' => gettype($data),
                'data_keys' => is_array($data) ? array_keys($data) : null,
                'data_sample' => is_array($data) ? array_slice($data, 0, 3) : $data,
            ]);
        } catch (\Exception $e) {
        }

        // Convert Firebase data to numerically-indexed array
        if (is_array($data)) {
            // If keys are not numeric, get values only
            $incidentsArr = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $incidentsArr[] = $value;
                }
            }
        } elseif ($data instanceof \Traversable) {
            $incidentsArr = array_values(iterator_to_array($data));
        } else {
            $incidentsArr = [];
        }

        $this->incidents = collect($incidentsArr);

        // Normalize incoming filter (query string) so it matches the select display labels.
        if (!empty($this->filter)) {
            $incoming = (string) $this->filter;
            // if incoming matches a raw data key, convert to display label
            foreach ($this->typeMap as $label => $dataKey) {
                if (strtolower($incoming) === strtolower($dataKey)) {
                    $this->filter = $label;
                    break;
                }
                // also accept display labels with different cases
                if (strtolower($incoming) === strtolower($label)) {
                    $this->filter = $label;
                    break;
                }
            }
            // fallback: if incoming looks like 'vehicle crash' or similar, normalize spacing/casing
            if (!empty($this->filter) && strtolower($this->filter) !== strtolower($incoming)) {
                // already normalized above
            } elseif (!empty($incoming) && empty($this->filter)) {
                $candidate = ucwords(str_replace(['_', '-'], ' ', $incoming));
                if (isset($this->typeMap[$candidate])) {
                    $this->filter = $candidate;
                }
            }
        }
    }

    public function render()
    {
        // Ensure we have a Collection (Livewire may rehydrate public properties as arrays)
        $incidents = $this->incidents instanceof \Illuminate\Support\Collection
            ? $this->incidents
            : collect($this->incidents ?? []);

        // Normalize filter input: accept display labels or raw data keys
        $rawFilter = trim((string) $this->filter);
        $filterType = '';
        if ($rawFilter !== '') {
            // direct match to known mapping (case-insensitive)
            foreach ($this->typeMap as $label => $dataKey) {
                if (strtolower($label) === strtolower($rawFilter) || strtolower($dataKey) === strtolower($rawFilter)) {
                    $filterType = $dataKey;
                    break;
                }
            }

            // fallback: transform human label like "Vehicle crash" -> vehicle_crash
            if ($filterType === '') {
                $candidate = strtolower(str_replace(' ', '_', $rawFilter));
                $filterType = $candidate;
            }
        }

        $search = trim((string) $this->search);

        // If search box has input, perform search across all incidents (ignore filter)
        if ($search !== '') {
            $s = strtolower($search);
            $filtered = $incidents->filter(function ($incident) use ($s) {
                $incidentArr = is_array($incident) ? $incident : (array) $incident;
                $hay = collect($incidentArr)->map(function ($v, $k) {
                    if ($k === 'type') {
                        return (string) ucfirst(str_replace('_', ' ', $v));
                    }
                    return (string) $v;
                })->implode(' ');

                return stripos($hay, $s) !== false;
            })->sortByDesc(function ($incident) {
                return $incident['timestamp'] ?? '';
            });
        } elseif ($filterType !== '') {
            // No search, but a filter is selected — show only filtered incidents
            $filtered = $incidents->filter(function ($incident) use ($filterType) {
                $incidentArr = is_array($incident) ? $incident : (array) $incident;
                $type = strtolower($incidentArr['type'] ?? '');
                return $type === strtolower($filterType);
            })->sortByDesc(function ($incident) {
                return $incident['timestamp'] ?? '';
            });
        } else {
            // No search and no filter — show all
            $filtered = $incidents->sortByDesc(function ($incident) {
                return $incident['timestamp'] ?? '';
            });
        }

        // Build a local display list and avoid mutating the original public property
        $displayIncidents = $filtered->values()->all();

        // Prepare a client-friendly incidents array (add formatted timestamp) for Alpine
        $incidentsForClient = collect($incidents)->map(function ($incident) {
            $incidentArr = is_array($incident) ? $incident : (array) $incident;
            try {
                $incidentArr['timestamp_formatted'] = isset($incidentArr['timestamp']) && $incidentArr['timestamp']
                    ? Carbon::parse($incidentArr['timestamp'])->format('M d, Y H:i')
                    : '';
            } catch (\Exception $e) {
                $incidentArr['timestamp_formatted'] = (string) ($incidentArr['timestamp'] ?? '');
            }
            return $incidentArr;
        })->values()->all();

        // Log debug info to storage/logs/laravel.log for diagnosis (use the display list)
        try {
            $allTypes = collect($displayIncidents)->pluck('type')->all();
            Log::debug('MobileIncidentTable.filter', [
                'filterType' => $filterType,
                'search' => $search,
                'all_types' => $allTypes,
                'matched_ids' => collect($displayIncidents)->pluck('incident_id')->all(),
            ]);
        } catch (\Exception $e) {
            // ignore logging failures
        }

        // Pass the computed display list to the view instead of mutating the public prop.
        return view('livewire.mobile-incident-table', [
            'displayIncidents' => $displayIncidents,
            'incidents' => $incidentsForClient,
        ]);
    }
}
