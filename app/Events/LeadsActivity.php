<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeadsActivity
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $activityType;
    public $details;
    public $leadId;

    /**
     * Create a new event instance.
     *
     * @param int $userId
     * @param string $activityType
     * @param array $details
     * @return void
     */
    public function __construct($userId, $leadId, $activityType, $details)
    {
        $this->userId = $userId;
        $this->leadId = $leadId;
        $this->activityType = $activityType;
        $this->details = $details;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }

    public function handle()
    {
        Activity::create([
            'user_id' => $this->userId, 
            'lead_id' => $this->leadId, 
            'activity_type' => $this->activityType,
            'details' => $this->details,
        ]);
    }
}
