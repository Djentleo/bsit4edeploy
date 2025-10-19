<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationBell extends Component
{
    protected $listeners = ['notificationUpdated' => 'fetchNotifications'];
    // ...existing code...

    public $notifications = [];
    public $unreadCount = 0;
    public $filter = 'all'; // all, incident, status, note
    public $sort = 'desc'; // desc, asc
    public $dropdownOpen = false;

    public function mount()
    {
        $this->fetchNotifications();
    }

    public function fetchNotifications()
    {
        $user = Auth::user();
        if (!$user) return;
        $query = $user->notifications();
        if ($this->filter !== 'all') {
            // Portable JSON filtering across drivers
            $query->where('data->type', $this->filter);
        }
        // Validate sort direction and override default ordering from relation
        $sortDirection = in_array($this->sort, ['asc', 'desc']) ? $this->sort : 'desc';
        // Replace the relation's default orderBy with our chosen direction
        $query->reorder('created_at', $sortDirection);
        $this->notifications = $query->limit(20)->get();
        $this->unreadCount = $user->unreadNotifications()->count();
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
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
            $this->fetchNotifications();
        }
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
        return view('livewire.admin-notification-bell');
    }

    public function deleteNotification($id)
    {
        $user = Auth::user();
        if ($user) {
            $notif = $user->notifications()->where('id', $id)->first();
            if ($notif) {
                $notif->delete();
                $this->fetchNotifications();
            }
        }
    }
}
