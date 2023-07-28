<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadvertexOrdersController extends Controller
{
    public function store(Request $request)
    {
        $url = sprintf("%s/addOrder.html?token=%s", env('LEADVERTEX_API_URL'), env('TOKEN'));

        $request_body = [
            'fio' => $request->name,
            'phone' => $request->phone,
            'goods' => [
                [
                    'goodID' => 192811,
                    'quantity' => 1,
                    'price' => 6900,
                ],

            ],
        ];

        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);

        $responseData = $lv_response->json();
        $newRecordId = array_key_first($responseData);

        $webhookcontroller = new WebhookController();
        $webhookcontroller->mark_as_spam_on_leadvertex($newRecordId);

        return view('thankyou');

    }

    public function thankyou()
    {
        return view('thankyou');
    }
}
