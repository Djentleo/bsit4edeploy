<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AdminIncidentNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $incidentId;
    protected $message;
    protected $link;
    protected $extra;

    public function __construct($type, $incidentId, $message, $link, $extra = [])
    {
        $this->type = $type;
        $this->incidentId = $incidentId;
        $this->message = $message;
        $this->link = $link;
        $this->extra = $extra;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'incident_id' => $this->incidentId,
            'message' => $this->message,
            'link' => $this->link,
            'extra' => $this->extra,
        ];
    }
}
