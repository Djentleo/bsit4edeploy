<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Incident;
use App\Models\IncidentNote;
use App\Models\Dispatch;

class ResponderNotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $filter = 'all'; // all, incident, note
    public $sort = 'desc'; // desc, asc
    public $dropdownOpen = false;
    public $lastSeenAt; // timestamp to compute unread since last open
    protected $listeners = ['notificationUpdated' => 'fetchNotifications'];

    public function mount()
    {
        $user = Auth::user();
        // Initialize last seen timestamp from session or now
        $this->lastSeenAt = session('responder_bell_last_seen.' . ($user->id ?? 'guest'), Carbon::now());
        $this->fetchNotifications();
    }

    public function fetchNotifications()
    {
        $user = Auth::user();
        if (!$user) return;

        // Find assignments via Dispatch rows for this responder
        $dispatches = Dispatch::where('responder_id', $user->id)->latest('created_at')->get();
        if ($dispatches->isEmpty()) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        // Collect possible incident keys (some tables reference primary id, others firebase_id)
        $incidentIdsFromDispatch = $dispatches->pluck('incident_id')->unique()->values();
        $incidentsById = Incident::whereIn('id', $incidentIdsFromDispatch)->get()->keyBy('id');
        $incidentsByFirebase = Incident::whereIn('firebase_id', $incidentIdsFromDispatch)->get()->keyBy('firebase_id');

        // Build base notifications array
        $notifications = [];

        // Incident assignment notifications (one per dispatch row)
        if ($this->filter === 'all' || $this->filter === 'incident') {
            foreach ($dispatches as $dispatch) {
                $incident = $incidentsById->get($dispatch->incident_id) ?? $incidentsByFirebase->get($dispatch->incident_id);
                if ($incident) {
                    $title = $incident->title ?? null;
                    $summary = $title ?: ($incident->type ? ($incident->type . ($incident->location ? ' @ ' . $incident->location : '')) : null);
                    if (!$summary && !empty($incident->incident_description)) {
                        $summary = Str::limit($incident->incident_description, 80);
                    }
                    $notifications[] = [
                        'type' => 'incident',
                        'message' => 'Incident assigned' . ($summary ? (': ' . $summary) : ':'),
                        'created_at' => $dispatch->created_at, // time of assignment
                        'incident_id' => $incident->id,
                        'dispatch_id' => $dispatch->id,
                        'read' => false,
                    ];
                }
            }
        }

        // Fetch notes/logs for assigned incidents (support both id and firebase_id linkage)
        $notes = IncidentNote::query()
            ->whereIn('incident_id', $incidentsById->keys())
            ->orWhereIn('incident_id', $incidentsByFirebase->keys())
            ->latest('created_at')
            ->get();

        if ($this->filter === 'all' || $this->filter === 'note') {
            foreach ($notes as $note) {
                $notifications[] = [
                    'type' => 'note',
                    'message' => 'Note: ' . (string)($note->note ?? ''),
                    'created_at' => $note->created_at,
                    'incident_id' => $note->incident_id,
                    'read' => false,
                ];
            }
        }

        // Status notifications are intentionally omitted for responders per requirements

        // Sort and trim
        $sortDirection = in_array($this->sort, ['asc', 'desc']) ? $this->sort : 'desc';
        $notifications = collect($notifications)
            ->sortBy('created_at', SORT_REGULAR, $sortDirection === 'desc')
            ->values()
            ->take(20)
            ->all();

        $this->notifications = $notifications;
        // Unread = items created after lastSeenAt
        $lastSeen = $this->lastSeenAt instanceof Carbon ? $this->lastSeenAt : Carbon::parse($this->lastSeenAt);
        $this->unreadCount = collect($notifications)
            ->filter(fn($n) => Carbon::parse($n['created_at'])->gt($lastSeen))
            ->count();
    }

    public function setFilter($type)
    {
        $this->filter = $type;
        $this->fetchNotifications();
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
        $this->fetchNotifications();
    }

    public function markAllRead()
    {
        // When opening dropdown, consider all current items as seen
        $this->lastSeenAt = Carbon::now();
        $user = Auth::user();
        session(['responder_bell_last_seen.' . ($user->id ?? 'guest') => $this->lastSeenAt]);
        $this->unreadCount = 0; // will persist across polls until new items arrive
    }

    public function toggleDropdown()
    {
        $this->dropdownOpen = !$this->dropdownOpen;
        if ($this->dropdownOpen) {
            $this->markAllRead();
        }
    }

    public function render()
    {
        return view('livewire.responder-notification-bell');
    }

    public function deleteNotification($id)
    {
        // Implement delete logic if needed
        $this->notifications = array_filter($this->notifications, fn($n) => $n['incident_id'] !== $id);
        $this->unreadCount = count(array_filter($this->notifications, fn($n) => !$n['read']));
    }
}
