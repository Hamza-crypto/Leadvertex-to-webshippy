<?php

namespace App\Http\Controllers;

use App\Models\EmailSchedule;
use App\Models\EmailSchedules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    function store(Request $request)
    {
        $data = $request->all();
        dd($data);
        $email = $data['email'];
        $meeting = new \App\Models\Meeting();
        $meeting->email = $email;
        $meeting->save();

    }


}
