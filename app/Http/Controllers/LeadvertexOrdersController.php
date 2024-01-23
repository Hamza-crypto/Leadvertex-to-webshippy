<?php

namespace App\Http\Controllers;

use App\Notifications\LeadVertexNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class LeadvertexOrdersController extends Controller
{
    public function store(Request $request)
    {
        $data['to'] = 'webshippy';

        $domain = env('LEADVERTEX_API_URL');
        if($request->has('domain')){
             $domain = env('LEADVERTEX_API_URL2');
        }

        $url = sprintf("%s/addOrder.html?token=%s", $domain, env('TOKEN'));

        $request_body = [
            'fio' => $request->name,
            'phone' => $request->phone,
            'goods' => [
                [
                    'goodID' => $request->product_id,
                    'quantity' => 1,
                    'price' => $request->price,
                ],

            ],
            'utm_term' => $request->utm_term ?? '',
            'webmasterID ' => $request->webmaster_id ?? 10
        ];
        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);

        $responseData = $lv_response->json();


        if (is_null($responseData)) {
            $data['msg'] = "Something went wrong with Leadvertex on product ID: " . $request->product_id;
            Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
            abort(500);
        }

        if(isset($responseData['error'])){
            return response()->json(['error' => 1]);
        }

        app('log')->channel('new_orders')->info($responseData);
        $newRecordId = array_key_first($responseData);

        // $webhookcontroller = new WebhookController();
        // $webhookcontroller->mark_as_spam_on_leadvertex($newRecordId);

        $data['msg'] = "New Order created with id: " . $newRecordId;
        Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));

        return response()->json(['success' => 1]);

        // return view('thankyou');

    }

    public function thankyou()
    {
        return view('thankyou');
    }
}
