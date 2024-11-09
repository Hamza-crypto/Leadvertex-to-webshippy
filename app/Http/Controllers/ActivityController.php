<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::all();

        return view('pages.activity_log.index', compact('activities'));
    }

    public function details($id)
    {
        $activity = Activity::find($id);
        $activityDetail = json_encode($activity->body, JSON_PRETTY_PRINT);

        return view('pages.activity_log.detail.detail', compact('activityDetail'));
    }
}
