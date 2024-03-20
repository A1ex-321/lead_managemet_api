<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Activity;
use App\Events\LeadsActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogLeadsActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LeadsActivity $event): void
    {
        Log::info("User ID: " . gettype($event->userId));

        Activity::create([
            'user_id' =>   $event->userId, 
            'lead_id' => $event->leadId, 
            'activity_type' => $event->activityType,
            'details' => $event->details,
        ]);
    }
}
