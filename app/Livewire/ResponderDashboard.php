<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Dispatch;
use App\Models\Incident;
use Carbon\Carbon;

class ResponderDashboard extends Component
{
    public static string $layout = 'layouts.app';
    public $incidents = [];
    public $search = '';
    public $statusFilter = '';
    public $filterType = '';
    public $perPage = 10;
    public $page = 1;
    public $total = 0;
    public $typeOptions = [];
    public $hasPrev = false;
    public $hasNext = false;
    // Sort fields mirrored from MobileIncidentTable with backward-compat
    // Allowed: firebase_id|type|location|reporter_name|status|department|timestamp (plus legacy reporter|time)
    public $sortField = 'timestamp';
    public $sortDirection = 'desc'; // asc|desc

    // Keep URL in sync with UI like the mobile table
    protected $updatesQueryString = ['search', 'filterType', 'sortField', 'sortDirection', 'page', 'perPage'];

    // Livewire hooks for updating search/filter/pagination
    // Only use updated* hooks for Livewire v3 best practice
    public function updatedSearch()
    {
        $this->page = 1;
        $this->fetchIncidents();
    }
    public function updatedFilterStatus()
    {
        $this->page = 1;
        $this->fetchIncidents();
    }
    public function updatedFilterType()
    {
        $this->page = 1;
        $this->fetchIncidents();
    }
    public function updatedPerPage()
    {
        $this->page = 1;
        $this->fetchIncidents();
    }
    public function updatedPage()
    {
        $this->fetchIncidents();
    }
    public function nextPage()
    {
        if ($this->page * $this->perPage < $this->total) {
            $this->page++;
            $this->fetchIncidents();
        }
    }
    public function prevPage()
    {
        if ($this->page > 1) {
            $this->page--;
            $this->fetchIncidents();
        }
    }
    public $statusOptions = [
        'dispatched' => 'Dispatched',
        'en_route' => 'En Route',
        'resolved' => 'Resolved',
    ];
    public $showModal = false;
    public $selectedIncident = null;
    public $incidentNotes = [];
    public $newNote = '';
    public $timeline = [];

    public function mount()
    {
        $this->fetchIncidents();
    }

    public function fetchIncidents()
    {
        $user = Auth::user();
        if (!$user) {
            $this->incidents = [];
            return;
        }
        $dispatches = Dispatch::where('responder_id', $user->id)->get();
        $all = $dispatches->map(function ($dispatch) {
            $incident = Incident::where('id', $dispatch->incident_id)
                ->orWhere('firebase_id', $dispatch->incident_id)
                ->first();
            $arr = $incident ? $incident->toArray() : null;
            $reporter = $arr['reporter_name'] ?? 'CCTV';
            $typeVal = $arr['type'] ?? ($arr['event'] ?? '');
            $timeTs = $this->parseIncidentTimestamp($arr);
            return [
                'dispatch_id' => $dispatch->id,
                'incident_id' => $dispatch->incident_id,
                'status' => $dispatch->status,
                'incident' => $arr,
                'sort' => [
                    // Legacy keys (used previously)
                    'reporter' => $reporter,
                    'type' => $typeVal,
                    'status' => $dispatch->status,
                    'time' => $timeTs,
                    // Keys to mirror MobileIncidentTable
                    'firebase_id' => $arr['firebase_id'] ?? (isset($arr['id']) ? (string)$arr['id'] : ''),
                    'location' => $arr['location'] ?? ($arr['camera_name'] ?? ''),
                    'reporter_name' => $reporter,
                    'department' => $arr['department'] ?? '',
                    'timestamp' => $timeTs,
                ],
            ];
        });

        // Build type options across all incidents (mobile uses 'type', CCTV uses 'event')
        $typesFromType = $all->pluck('incident.type')->filter();
        $typesFromEvent = $all->pluck('incident.event')->filter();
        $this->typeOptions = $typesFromType
            ->merge($typesFromEvent)
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Search and filter
        $filtered = $all->filter(function ($item) {
            $incident = $item['incident'];
            if (!$incident) return false;
            $matchesSearch = true;
            if ($this->search) {
                $search = strtolower($this->search);
                $matchesSearch = (
                    (isset($incident['firebase_id']) && str_contains(strtolower((string)$incident['firebase_id']), $search)) ||
                    (isset($incident['type']) && str_contains(strtolower($incident['type']), $search)) ||
                    (isset($incident['location']) && str_contains(strtolower($incident['location']), $search)) ||
                    (isset($incident['reporter_name']) && str_contains(strtolower($incident['reporter_name']), $search)) ||
                    (isset($incident['department']) && str_contains(strtolower($incident['department']), $search)) ||
                    (isset($incident['status']) && str_contains(strtolower($incident['status']), $search)) ||
                    (isset($incident['incident_description']) && str_contains(strtolower($incident['incident_description']), $search))
                );
            }
            $matchesStatus = $this->statusFilter ? $item['status'] === $this->statusFilter : true;
            // Match against either 'type' (mobile) or 'event' (CCTV)
            $matchesType = $this->filterType
                ? ((isset($incident['type']) && $incident['type'] === $this->filterType) || (isset($incident['event']) && $incident['event'] === $this->filterType))
                : true;
            return $matchesSearch && $matchesStatus && $matchesType;
        })->values();

        // Sorting
        $key = match ($this->sortField) {
            'firebase_id' => 'sort.firebase_id',
            'type' => 'sort.type',
            'location' => 'sort.location',
            'reporter_name' => 'sort.reporter_name',
            'reporter' => 'sort.reporter', // legacy
            'status' => 'sort.status',
            'department' => 'sort.department',
            'timestamp' => 'sort.timestamp',
            'time' => 'sort.time', // legacy
            default => 'sort.timestamp',
        };
        $sorted = $this->sortDirection === 'asc'
            ? $filtered->sortBy($key, SORT_NATURAL | SORT_FLAG_CASE)
            : $filtered->sortByDesc($key, SORT_NATURAL | SORT_FLAG_CASE);

        // Pagination
        $this->total = $sorted->count();
        $this->incidents = $sorted->forPage($this->page, $this->perPage)->values()->toArray();
        $this->hasPrev = $this->page > 1;
        $this->hasNext = ($this->page * $this->perPage) < $this->total;
    }

    public function sortBy($field)
    {
        $allowed = ['firebase_id', 'type', 'location', 'reporter_name', 'status', 'department', 'timestamp', 'reporter', 'time'];
        if (!in_array($field, $allowed)) {
            return;
        }
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            // Default to desc for time-based sorts, asc otherwise
            $this->sortDirection = in_array($field, ['timestamp', 'time']) ? 'desc' : 'asc';
        }
        $this->page = 1;
        $this->fetchIncidents();
    }

    private function parseIncidentTimestamp(?array $incident): int
    {
        if (!$incident) return 0;
        $candidates = [
            $incident['datetime'] ?? null,
            $incident['date_time'] ?? null,
            $incident['timestamp'] ?? null,
        ];
        foreach ($candidates as $value) {
            if (!$value) continue;
            // Try common formats
            $formats = ['Y-m-d H:i:s', 'h:i:s A Y-m-d', Carbon::DEFAULT_TO_STRING_FORMAT];
            foreach ($formats as $fmt) {
                try {
                    $dt = Carbon::createFromFormat($fmt, $value);
                    if ($dt) return $dt->timestamp;
                } catch (\Throwable $e) {
                    // ignore and try next
                }
            }
            try {
                $dt = Carbon::parse($value);
                if ($dt) return $dt->timestamp;
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return 0;
    }

    public function updateStatus($dispatchId, $status)
    {
        $dispatch = Dispatch::find($dispatchId);
        if ($dispatch && $dispatch->responder_id === Auth::id()) {
            $dispatch->status = $status;
            $dispatch->save();
            // If resolved, check if all responders are resolved or only one responder is assigned
            if ($status === 'resolved') {
                // Find the incident by id or firebase_id
                $incident = \App\Models\Incident::where('id', $dispatch->incident_id)
                    ->orWhere('firebase_id', $dispatch->incident_id)
                    ->first();
                if ($incident) {
                    $dispatchCount = Dispatch::where(function ($q) use ($dispatch) {
                        $q->where('incident_id', $dispatch->incident_id);
                    })->count();
                    if ($dispatchCount <= 1) {
                        // Only one responder assigned, resolve immediately
                        $incident->status = 'resolved';
                        $incident->save();
                    } else {
                        // Multiple responders: check if all are resolved
                        $allResolved = Dispatch::where(function ($q) use ($dispatch) {
                            $q->where('incident_id', $dispatch->incident_id);
                        })
                            ->where('status', '!=', 'resolved')
                            ->count() === 0;
                        if ($allResolved) {
                            $incident->status = 'resolved';
                            $incident->save();
                        }
                    }
                }
            }
            $this->fetchIncidents();
            if ($this->selectedIncident && $this->selectedIncident['dispatch_id'] == $dispatchId) {
                $this->selectedIncident['status'] = $status;
            }
        }
    }

    public function showIncident($dispatchId)
    {
        $incident = collect($this->incidents)->firstWhere('dispatch_id', $dispatchId);
        if (!$incident) return;
        $this->selectedIncident = $incident;
        $this->loadNotes($incident['incident_id']);
        $this->loadTimeline($incident['incident_id']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedIncident = null;
        $this->incidentNotes = [];
        $this->timeline = [];
    }

    public function loadNotes($incidentId)
    {
        $notes = \App\Models\IncidentNote::where('incident_id', $incidentId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'user_name' => $note->user->name ?? 'Unknown',
                    'created_at' => $note->created_at->diffForHumans(),
                ];
            })->toArray();
        $this->incidentNotes = $notes;
    }

    public function addNote()
    {
        if (!$this->selectedIncident) return;
        $incidentId = $this->selectedIncident['incident_id'] ?? null;
        if (!$incidentId) return;
        $user = Auth::user();
        if (!$user) return;
        $noteText = trim($this->newNote);
        if ($noteText === '') return;
        \App\Models\IncidentNote::create([
            'incident_id' => $incidentId,
            'user_id' => $user->id,
            'note' => $noteText,
        ]);
        $this->newNote = '';
        $this->loadNotes($incidentId);
    }

    public function loadTimeline($incidentId)
    {
        $timeline = \App\Models\IncidentTimeline::where('incident_id', $incidentId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'action' => $item->action,
                    'details' => $item->details,
                    'user_name' => $item->user->name ?? 'Unknown',
                    'created_at' => $item->created_at->diffForHumans(),
                ];
            })->toArray();
        $this->timeline = $timeline;
    }

    public function render()
    {
        $this->fetchIncidents();
        return view('livewire.responder-dashboard', [
            'incidents' => $this->incidents,
            'statusOptions' => $this->statusOptions,
        ]);
    }
}
