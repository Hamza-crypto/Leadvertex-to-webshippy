<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\LeadVertexNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class ZappierController extends Controller
{
    public function store(Request $request)
    {
        $url = sprintf("%s/addOrder.html?token=%s", env('LEADVERTEX_API_URL'), env('TOKEN'));

        if ($request->has('source')) {
            $source = $request->source;

            if ($source != 'fb') return 0;


                $data['to'] = 'fb_leads';

                $request_body = [
                    'fio'         => $request->name,
                    'phone'       => $request->phone,
                    'goods'       => [
                        [
                            'quantity' => 1,
                            'goodID' => "",
                            'price' => "",
                        ],
                    ],
                    'additional1' => $request->ad_name ?? '',
                    'webmasterID' => 20, // FB_LEADS
                ];

                if ($request->ad_id == '120205252121790002') {
                    $request_body['goods'][0]['goodID'] = '193655';
                    $request_body['goods'][0]['price']  = '2500';
                }

    }

        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);

        $responseData = $lv_response->json();


        if (is_null($responseData)) {
            $data['msg'] = "Something went wrong with Leadvertex on product ID: " . $request->product_id;
            Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
            abort(500);
        }

        if(isset($responseData['error'])) {
            return response()->json(['error' => 1]);
        }

        app('log')->channel('zapier')->info($request_body);
        app('log')->channel('zapier')->info($responseData);
        $newRecordId = array_key_first($responseData);

        $data['msg'] = "New Lead from Facebook : LV ID: " . $newRecordId;
        Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));

        return response()->json(['success' => 1]);
    }
}
