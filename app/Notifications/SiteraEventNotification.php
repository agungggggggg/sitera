<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SiteraEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $eventType,
        public string $title,
        public string $message,
        public int $reportId,
        public string $ticket,
        public ?string $result = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'report_id' => $this->reportId,
            'ticket' => $this->ticket,
            'hasil' => $this->result,
            'url' => route('reports.show', $this->reportId),
        ];
    }
}
