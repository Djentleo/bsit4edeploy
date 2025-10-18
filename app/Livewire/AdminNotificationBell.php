<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationBell extends Component
{
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
            // Use JSON_EXTRACT to be explicit for MySQL
            $query->whereRaw("JSON_EXTRACT(data, '$.type') = ?", [$this->filter]);
        }
        $query->orderBy('created_at', $this->sort);
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
}
