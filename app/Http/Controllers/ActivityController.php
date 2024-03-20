<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Support\Carbon;

class ActivityController extends Controller
{
    protected $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function logActivity(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'date' => 'required|date',
        ]);
    
        // Parse the date from the request
        $date = Carbon::parse($request->input('date'));

        return $this->activityService->getActivity($date);
    }
}
