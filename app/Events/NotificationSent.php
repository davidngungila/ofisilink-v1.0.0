<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $userIds;
    public string $message;
    public string $link;

    /**
     * Create a new event instance.
     */
    public function __construct(array $userIds, string $message, ?string $link = null)
    {
        $this->userIds = $userIds;
        $this->message = $message;
        $this->link = $link;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->userIds as $userId) {
            $channels[] = new Channel('user.' . $userId);
        }
        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'link' => $this->link,
        ];
    }
}






