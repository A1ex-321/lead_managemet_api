<?php

namespace App\Services;

use App\Models\Activity;

class ActivityService
{
    public function getActivity($date)
    {

        $activities = Activity::with('user', 'lead')
        ->whereDate('created_at', $date)
        ->latest() // Get the latest activity of each type
        ->get()
        ->unique('activity_type') // Remove duplicates based on activity_type
        ->values();

        return response()->json(['activity' => $activities]);
    }

}
