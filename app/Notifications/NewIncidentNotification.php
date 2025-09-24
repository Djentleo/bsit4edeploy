<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\IncidentLog;

class NewIncidentNotification extends Notification
{
    use Queueable;

    protected $incident;

    // Accept array/object, not just IncidentLog
    public function __construct($incident)
    {
        $this->incident = is_object($incident) ? (array) $incident : $incident;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Incident Reported')
            ->greeting('Hello Admin!')
            ->line('A new incident has been reported:')
            ->line('Type: ' . ($this->incident['type'] ?? $this->incident['event'] ?? ''))
            ->line('Location: ' . ($this->incident['location'] ?? $this->incident['camera_name'] ?? ''))
            ->line('Reporter: ' . ($this->incident['reporter_name'] ?? 'CCTV'))
            ->line('Department: ' . ($this->incident['department'] ?? ''))
            ->line('Timestamp: ' . ($this->incident['timestamp'] ?? ''))
            ->action('View Incident', url('/incidents/' . ($this->incident['incident_id'] ?? $this->incident['firebase_id'] ?? '')))
            ->line('Please review and take necessary action.');
    }
}
